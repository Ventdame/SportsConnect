<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\EvenementModele;
use App\Fabrique\UtilisateurModele;
use App\Fabrique\NotificationModele;
use App\Vues\AdminVue;

/**
 * Contrôleur pour la gestion des fonctionnalités administrateur
 */
class AdminControleur extends ControleurBase
{
    /**
     * Instance du modèle EvenementModele
     * 
     * @var EvenementModele
     */
    private $evenementModele;
    
    /**
     * Instance du modèle UtilisateurModele
     * 
     * @var UtilisateurModele
     */
    private $utilisateurModele;
    
    /**
     * Instance du modèle NotificationModele
     * 
     * @var NotificationModele
     */
    private $notificationModele;

    /**
     * Constructeur du contrôleur AdminControleur
     *
     * @param \PDO $pdo Connexion PDO
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->evenementModele = new EvenementModele($pdo);
        $this->utilisateurModele = new UtilisateurModele($pdo);
        $this->notificationModele = new NotificationModele($pdo);
    }

    /**
     * Méthode par défaut - Tableau de bord administrateur
     */
    public function index()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Rediriger vers la gestion des utilisateurs par défaut
        Reponses::rediriger('admin', ['action' => 'utilisateurs']);
    }

    /**
     * Méthode pour gérer les utilisateurs
     */
    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }
        
        // Récupérer la liste des utilisateurs
        $utilisateurs = $this->utilisateurModele->obtenirTous();
        
        // Afficher la vue
        $vue = new AdminVue(
            "Gestion des utilisateurs - Admin",
            $this->utilisateurConnecte,
            $utilisateurs,
            activeTab: 'utilisateurs'
        );
        $vue->afficher();
    }

    /**
     * Méthode pour gérer les événements (liste, approbation, refus)
     */
    public function evenements()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer tous les événements
        $evenements = $this->evenementModele->obtenirTous();
        
        // Afficher la vue
        $vue = new AdminVue(
            "Gestion des événements - Admin",
            $this->utilisateurConnecte,
            $evenements,
            'evenements'
        );
        $vue->afficher();
    }

    /**
     * Méthode pour approuver un événement
     */
    public function approuver_evenement()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer l'ID de l'événement depuis les paramètres GET
        $idEvenement = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($idEvenement <= 0) {
            $this->ajouterMessageErreur("ID d'événement invalide.");
            Reponses::rediriger('admin', ['action' => 'evenements']);
            return;
        }

        // Approuver l'événement
        $resultat = $this->evenementModele->approuverEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur']);
        
        if ($resultat) {
            $this->ajouterMessageReussite("L'événement a été approuvé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de l'approbation de l'événement.");
        }
        
        Reponses::rediriger('admin', ['action' => 'evenements']);
    }

    /**
     * Méthode pour afficher le formulaire de refus d'un événement
     */
    public function refuser_evenement()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer l'ID de l'événement depuis les paramètres GET
        $idEvenement = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($idEvenement <= 0) {
            $this->ajouterMessageErreur("ID d'événement invalide.");
            Reponses::rediriger('admin', ['action' => 'evenements']);
            return;
        }

        // Récupérer les informations de l'événement
        $evenement = $this->evenementModele->obtenirEvenementParId($idEvenement);
        
        if (!$evenement) {
            $this->ajouterMessageErreur("L'événement demandé n'existe pas.");
            Reponses::rediriger('admin', ['action' => 'evenements']);
            return;
        }

        // Afficher la vue avec le formulaire de refus
        $vue = new AdminVue(
            "Refuser un événement - Admin",
            $this->utilisateurConnecte,
            $evenement,
            'refuser_evenement'
        );
        $vue->afficher();
    }

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     */
    public function traiter_refus_evenement()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin', ['action' => 'evenements']);
            return;
        }

        // Récupérer l'ID de l'événement et le motif du refus
        $idEvenement = isset($_POST['id_evenement']) ? intval($_POST['id_evenement']) : 0;
        $motifRefus = isset($_POST['motif_refus']) ? trim($_POST['motif_refus']) : '';
        
        if ($idEvenement <= 0) {
            $this->ajouterMessageErreur("ID d'événement invalide.");
            Reponses::rediriger('admin', ['action' => 'evenements']);
            return;
        }
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin', ['action' => 'refuser_evenement', 'id' => $idEvenement]);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageReussite("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin', ['action' => 'evenements']);
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     */
    public function promouvoir_admin()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer l'ID de l'utilisateur depuis les paramètres GET
        $idUtilisateur = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($idUtilisateur <= 0) {
            $this->ajouterMessageErreur("ID d'utilisateur invalide.");
            Reponses::rediriger('admin', ['action' => 'utilisateurs']);
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'admin']);
        
        if ($resultat) {
            $this->ajouterMessageReussite("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin', ['action' => 'utilisateurs']);
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     */
    public function retirer_admin()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer l'ID de l'utilisateur depuis les paramètres GET
        $idUtilisateur = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($idUtilisateur <= 0) {
            $this->ajouterMessageErreur("ID d'utilisateur invalide.");
            Reponses::rediriger('admin', ['action' => 'utilisateurs']);
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin', ['action' => 'utilisateurs']);
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageReussite("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin', ['action' => 'utilisateurs']);
    }

    /**
     * Méthode pour gérer les feedbacks et suggestions
     */
    public function feedbacks()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        try {
            // Récupérer tous les feedbacks directement avec PDO
            $sql = "SELECT f.*, u.pseudo 
                    FROM feedbacks_suggestions f
                    JOIN utilisateurs u ON f.id_utilisateur = u.id_utilisateur
                    ORDER BY f.date_feedback DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $feedbacks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Afficher la vue
            $vue = new AdminVue(
                "Gestion des feedbacks - Admin",
                $this->utilisateurConnecte,
                $feedbacks,
                'feedbacks'
            );
            $vue->afficher();
            
        } catch (\PDOException $e) {
            // Journaliser l'erreur
            error_log("Erreur lors de la récupération des feedbacks: " . $e->getMessage());
            $this->ajouterMessageErreur("Une erreur est survenue lors de la récupération des feedbacks.");
            Reponses::rediriger('admin');
        }
    }

    /**
     * Méthode pour valider un feedback
     */
    public function valider_feedback()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer l'ID du feedback depuis les paramètres GET
        $idFeedback = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($idFeedback <= 0) {
            $this->ajouterMessageErreur("ID de feedback invalide.");
            Reponses::rediriger('admin', ['action' => 'feedbacks']);
            return;
        }

        try {
            // Exécuter directement la requête avec PDO
            $stmt = $this->pdo->prepare("UPDATE feedbacks_suggestions SET statut = 'Valider' WHERE id_feedback = :id_feedback");
            $stmt->bindParam(':id_feedback', $idFeedback, \PDO::PARAM_INT);
            $resultat = $stmt->execute();
            
            if ($resultat) {
                $this->ajouterMessageReussite("Le feedback a été validé avec succès.");
            } else {
                $this->ajouterMessageErreur("Une erreur est survenue lors de la validation du feedback.");
            }
        } catch (\PDOException $e) {
            // Journaliser l'erreur
            error_log("Erreur lors de la validation du feedback: " . $e->getMessage());
            $this->ajouterMessageErreur("Une erreur de base de données est survenue lors de la validation du feedback.");
        }
        
        Reponses::rediriger('admin', ['action' => 'feedbacks']);
    }

    /**
     * Méthode pour refuser un feedback
     */
    public function refuser_feedback()
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer l'ID du feedback depuis les paramètres GET
        $idFeedback = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($idFeedback <= 0) {
            $this->ajouterMessageErreur("ID de feedback invalide.");
            Reponses::rediriger('admin', ['action' => 'feedbacks']);
            return;
        }

        try {
            // Exécuter directement la requête avec PDO
            $stmt = $this->pdo->prepare("UPDATE feedbacks_suggestions SET statut = 'Refuser' WHERE id_feedback = :id_feedback");
            $stmt->bindParam(':id_feedback', $idFeedback, \PDO::PARAM_INT);
            $resultat = $stmt->execute();
            
            if ($resultat) {
                $this->ajouterMessageReussite("Le feedback a été refusé avec succès.");
            } else {
                $this->ajouterMessageErreur("Une erreur est survenue lors du refus du feedback.");
            }
        } catch (\PDOException $e) {
            // Journaliser l'erreur
            error_log("Erreur lors du refus du feedback: " . $e->getMessage());
            $this->ajouterMessageErreur("Une erreur de base de données est survenue lors du refus du feedback.");
        }
        
        Reponses::rediriger('admin', ['action' => 'feedbacks']);
    }

    /**
     * Méthode pour vérifier si l'utilisateur connecté est un administrateur
     * 
     * @return bool True si l'utilisateur est un administrateur, false sinon
     */
    private function exigerRoleAdmin()
    {
        if (!isset($this->utilisateurConnecte['role']) || empty($this->utilisateurConnecte['role']) || $this->utilisateurConnecte['role'] !== 'admin') {
            $this->ajouterMessageErreur("Vous n'avez pas les droits d'accès à cette page.");
            Reponses::rediriger('accueil');
            return false;
        }
        return true;
    }
}