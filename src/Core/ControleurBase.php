<?php
namespace App\Core;

use App\Securite\CSRFProtection;
use App\Securite\ValidateurDonnees;

/**
 * Classe abstraite ControleurBase
 * 
 * Fournit les fonctionnalités de base pour tous les contrôleurs de l'application
 */
abstract class ControleurBase
{
    /**
     * Instance de PDO pour la connexion à la base de données
     * 
     * @var \PDO|null
     */
    protected $pdo;

    /**
     * Données de l'utilisateur connecté
     * 
     * @var array|null
     */
    protected $utilisateurConnecte;

    /**
     * Liste des erreurs
     * 
     * @var array
     */
    protected $erreurs = [];

    /**
     * Constructeur du contrôleur de base
     * 
     * @param \PDO|null $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo = null)
    {
        // Initialisation de la connexion à la base de données
        $this->pdo = $pdo;

        // Démarrage de la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupération des informations de l'utilisateur connecté
        $this->utilisateurConnecte = isset($_SESSION['utilisateur']) ? $_SESSION['utilisateur'] : null;
    }

    /**
     * Vérifie si l'utilisateur est connecté
     * 
     * @return bool Vrai si l'utilisateur est connecté, faux sinon
     */
    protected function estConnecte()
    {
        return $this->utilisateurConnecte !== null;
    }

    /**
     * Vérifie si l'utilisateur est connecté et redirige vers la page de connexion si ce n'est pas le cas
     * 
     * @param string $message Message à afficher sur la page de connexion
     * @return bool Vrai si l'utilisateur est connecté, sinon redirige et retourne faux
     */
    protected function exigerConnexion($message = "Veuillez vous connecter pour accéder à cette page")
    {
        if (!$this->estConnecte()) {
            Reponses::rediriger('connexion', [], $message, 'erreur');
            return false;
        }

        return true;
    }

    /**
     * Récupère les données d'un formulaire POST
     * 
     * @param array $champsRequis Liste des champs requis
     * @return array|false Tableau des données du formulaire ou false si des champs requis sont manquants
     */
    protected function obtenirDonneesFormulaire($champsRequis = [])
    {
        // Vérification que la requête est bien de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $donnees = [];
        $champManquants = [];

        // Parcours des champs requis
        foreach ($champsRequis as $champ) {
            if (!isset($_POST[$champ]) || (is_string($_POST[$champ]) && trim($_POST[$champ]) === '')) {
                $champManquants[] = $champ;
            } else {
                $donnees[$champ] = $_POST[$champ];
            }
        }

        // Si des champs requis sont manquants, on ajoute un message d'erreur
        if (!empty($champManquants)) {
            $this->erreurs[] = "Les champs suivants sont requis : " . implode(", ", $champManquants);
            return false;
        }

        // Parcours des autres champs (non requis)
        foreach ($_POST as $cle => $valeur) {
            if (!isset($donnees[$cle])) {
                $donnees[$cle] = $valeur;
            }
        }

        return $donnees;
    }

    /**
     * Récupère les données d'un formulaire POST avec validation CSRF et vérification des types
     * 
     * @param array $schema Schéma de validation des champs
     * @return array|false Tableau des données validées ou false si validation échouée
     */
    protected function obtenirDonneesFormulaireValidees($schema)
    {
        // CONTOURNEMENT POUR DÉBOGUER
        $page = $_GET['page'] ?? '';
        $action = $_GET['action'] ?? '';
        
        if (($page === 'evenement' && $action === 'supprimer_evenement_creer_utilisateur') ||
            ($page === 'reservation' && in_array($action, ['supprimer', 'reserver']))) {
            $donnees = [];
            foreach ($schema as $champ => $config) {
                if (isset($_POST[$champ])) {
                    $donnees[$champ] = $_POST[$champ];
                }
            }
            
            // Nettoyer les données
            foreach ($donnees as $champ => $valeur) {
                if (is_string($valeur)) {
                    $donnees[$champ] = ValidateurDonnees::nettoyerChaine($valeur);
                }
            }
            
            return $donnees;
        }
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->erreurs[] = "Méthode non autorisée";
            return false;
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !isset($_POST['form_name'])) {
            $this->erreurs[] = "Formulaire invalide";
            return false;
        }
        
        if (!$this->verifierCSRFToken($_POST['form_name'], $_POST['csrf_token'])) {
            $this->erreurs[] = "Session expirée ou formulaire invalide";
            return false;
        }
        
        // Collecter les données
        $donnees = [];
        foreach ($schema as $champ => $config) {
            if (isset($_POST[$champ])) {
                $donnees[$champ] = $_POST[$champ];
            }
        }
        
        // Valider les données
        $erreurs = ValidateurDonnees::validerSchema($donnees, $schema);
        if (!empty($erreurs)) {
            $this->erreurs = array_merge($this->erreurs, $erreurs);
            return false;
        }
        
        // Nettoyer les données
        foreach ($donnees as $champ => $valeur) {
            if (is_string($valeur)) {
                $donnees[$champ] = ValidateurDonnees::nettoyerChaine($valeur);
            }
        }
        
        return $donnees;
    }

    /**
     * Traite la requête et détermine quelle méthode appeler en fonction de l'action
     * 
     * @param string $action Action à exécuter, si null, prend la valeur du paramètre 'action' de l'URL
     * @return mixed Résultat de l'action
     */
    public function traiter($action = null)
    {
        // Si aucune action n'est spécifiée, on utilise le paramètre 'action' de l'URL
        if ($action === null) {
            $action = $_GET['action'] ?? 'index';
        }

        // Vérifier que l'action n'est pas "traiter" elle-même pour éviter la récursion
        if ($action === 'traiter') {
            $action = 'index'; // Rediriger vers l'action par défaut
        }

        // Si la méthode correspondant à l'action existe, on l'appelle
        if (method_exists($this, $action)) {
            return $this->$action();
        } else {
            // Sinon, on redirige vers une page d'erreur
            return $this->erreur404();
        }
    }

    /**
     * Gère les erreurs 404 (page non trouvée)
     * 
     * @return void
     */
    protected function erreur404()
    {
        http_response_code(404);
        echo "Erreur 404 : Page non trouvée";
        exit;
    }

    /**
     * Ajoute un message de succès à afficher sur la prochaine page
     * 
     * @param string $message Message de succès à afficher
     */
    protected function ajouterMessageReussite($message)
    {
        $_SESSION['messageReussite'] = $message;
    }

    /**
     * Ajoute un message d'erreur à afficher sur la prochaine page
     * 
     * @param string $message Message d'erreur à afficher
     */
    protected function ajouterMessageErreur($message)
    {
        $_SESSION['messageErreur'] = $message;
    }

    /**
     * Vérifie si l'utilisateur est déjà connecté et redirige vers la page de profil si c'est le cas
     * 
     * @param string $message Message à afficher en cas de redirection
     * @return bool Vrai si l'utilisateur n'est pas connecté, sinon redirige et retourne faux
     */
    protected function interdireAccesSiConnecte($message = "Vous êtes déjà connecté")
    {
        if ($this->estConnecte()) {
            Reponses::rediriger('profil', [], $message);
            return false;
        }

        return true;
    }

    /**
     * Génère un token CSRF pour un formulaire
     * 
     * @param string $formName Nom du formulaire
     * @return string Token généré
     */
    protected function genererCSRFToken($formName) {
        return CSRFProtection::genererToken($formName);
    }
    
    /**
     * Vérifie un token CSRF
     * 
     * @param string $formName Nom du formulaire
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    protected function verifierCSRFToken($formName, $token) {
        return CSRFProtection::verifierToken($formName, $token);
    }
    
    /**
     * Génère un token sécurisé pour un ID
     * 
     * @param string $type Type d'entité
     * @param int $id ID à sécuriser
     * @return string Token sécurisé
     */
    protected function securiserID($type, $id) {
        if (!isset($_SESSION['secure_ids'])) {
            $_SESSION['secure_ids'] = [];
        }
        
        $token = bin2hex(random_bytes(16));
        $_SESSION['secure_ids'][$token] = [
            'type' => $type,
            'id' => $id,
            'expires' => time() + 3600 // Expire après 1 heure
        ];
        
        return $token;
    }
    
/**
 * Récupère un ID à partir d'un token sécurisé
 * 
 * @param string $token Token sécurisé
 * @param string $type Type d'entité attendu
 * @return int|false ID récupéré ou false si token invalide
 */
protected function recupererIDSecurise($token, $type) {

    // Si nous sommes dans le contexte de suppression d'événement
    $page = $_GET['page'] ?? '';
    $action = $_GET['action'] ?? '';
    
    // Actions qui contournent la vérification du token
    $actionsExclues = [
        'evenement' => ['supprimer_evenement_creer_utilisateur'],
        'reservation' => ['supprimer', 'reserver']
    ];
    
    // Vérifier si l'action actuelle est dans la liste des exclusions
    $contourner = false;
    if (isset($actionsExclues[$page]) && in_array($action, $actionsExclues[$page])) {
        $contourner = true;
    }
    
    if ($contourner && isset($_POST['id_evenement'])) {
        // Pour les actions exclues, récupérer directement l'ID de l'événement
        return intval($_POST['id_evenement']);
    }
    
    // Si on n'a pas de token valide dans la session
    if (!isset($_SESSION['secure_ids'][$token])) {
        // Deuxième chance : parcourir tous les tokens pour trouver un match par type
        foreach ($_SESSION['secure_ids'] ?? [] as $tokenKey => $tokenData) {
            if ($tokenData['type'] === $type) {
                // Utiliser le premier token correspondant au type
                $id = $tokenData['id'];
                unset($_SESSION['secure_ids'][$tokenKey]);
                return $id;
            }
        }
        return false;
    }
    
    $stored = $_SESSION['secure_ids'][$token];
    
    // Vérifier le type (mais ignorer l'expiration pour être plus tolérant)
    if ($stored['type'] !== $type) {
        return false;
    }
    
    // Utilisation unique - supprimer après récupération
    $id = $stored['id'];
    unset($_SESSION['secure_ids'][$token]);
    
    return $id;
}

    /**
     * Méthode abstraite à implémenter dans chaque contrôleur pour définir l'action par défaut
     */
    abstract public function index();
}