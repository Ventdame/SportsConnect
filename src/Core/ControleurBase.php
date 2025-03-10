<?php

namespace App\Core;

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
     * Méthode abstraite à implémenter dans chaque contrôleur pour définir l'action par défaut
     */
    abstract public function index();
}
