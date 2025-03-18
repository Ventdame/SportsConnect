<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\EvenementModele;
use App\Fabrique\UtilisateurModele;
use App\Fabrique\NotificationModele;

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

        // Afficher le tableau de bord administrateur
        // TODO: Créer une vue pour le tableau de bord administrateur
        echo "<h1>Tableau de bord administrateur</h1>";
        echo "<p>Bienvenue, " . htmlspecialchars($this->utilisateurConnecte['pseudo']) . "!</p>";
        
        // Liens vers les différentes fonctionnalités administrateur
        echo "<ul>";
        echo "<li><a href='?page=admin&action=evenements'>Gestion des événements</a></li>";
        echo "<li><a href='?page=admin&action=utilisateurs'>Gestion des utilisateurs</a></li>";
        echo "<li><a href='?page=admin&action=feedbacks'>Gestion des feedbacks</a></li>";
        echo "</ul>";
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

        // TODO: Créer une vue pour la gestion des événements
        echo "<h1>Gestion des événements</h1>";
        
        // Récupérer tous les événements
        $evenements = $this->evenementModele->obtenirTous();
        
        // Afficher les événements
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Date</th><th>Statut</th><th>Actions</th></tr>";
        
        foreach ($evenements as $evenement) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($evenement['id_evenement']) . "</td>";
            echo "<td>" . htmlspecialchars($evenement['nom_evenement']) . "</td>";
            echo "<td>" . htmlspecialchars($evenement['date_evenement']) . "</td>";
            echo "<td>" . htmlspecialchars($evenement['statut'] ?? 'En attente') . "</td>";
            echo "<td>";
            echo "<a href='?page=admin&action=approuver_evenement&id=" . $evenement['id_evenement'] . "'>Approuver</a> | ";
            echo "<a href='?page=admin&action=refuser_evenement&id=" . $evenement['id_evenement'] . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour approuver un événement
     * 
     * @param int $idEvenement ID de l'événement à approuver
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
            $this->ajouterMessageSucces("L'événement a été approuvé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de l'approbation de l'événement.");
        }
        
        Reponses::rediriger('admin', ['action' => 'evenements']);
    }

    /**
     * Méthode pour afficher le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
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

        // Afficher le formulaire de refus
        echo "<h1>Refuser l'événement : " . htmlspecialchars($evenement['nom_evenement']) . "</h1>";
        echo "<form method='post' action='?page=admin&action=traiter_refus_evenement'>";
        echo "<input type='hidden' name='id_evenement' value='" . $idEvenement . "'>";
        echo "<label for='motif_refus'>Motif du refus :</label><br>";
        echo "<textarea name='motif_refus' id='motif_refus' rows='5' cols='50' required></textarea><br><br>";
        echo "<input type='submit' value='Refuser l\'événement'>";
        echo "</form>";
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
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin', ['action' => 'evenements']);
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer la liste des utilisateurs
        $utilisateurs = $this->utilisateurModele->obtenirTous();
        
        // Afficher les utilisateurs
        echo "<h1>Gestion des utilisateurs</h1>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Rôle</th><th>Actions</th></tr>";
        
        foreach ($utilisateurs as $utilisateur) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($utilisateur['id_utilisateur']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['pseudo']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['email']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['role'] ?? 'user') . "</td>";
            echo "<td>";
            
            if ($utilisateur['role'] === 'administrateur') {
                echo "<a href='?page=admin&action=retirer_admin&id=" . $utilisateur['id_utilisateur'] . "' 
                      onclick=\"return confirm('Êtes-vous sûr de retirer les droits admin ?');\">
                      Retirer admin</a>";
            } else {
                echo "<a href='?page=admin&action=promouvoir_admin&id=" . $utilisateur['id_utilisateur'] . "' 
                      onclick=\"return confirm('Êtes-vous sûr de promouvoir cet utilisateur ?');\">
                      Promouvoir admin</a>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
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
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
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
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
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

        // TODO: Créer une vue pour la gestion des feedbacks
        echo "<h1>Gestion des feedbacks et suggestions</h1>";
        
        // Récupérer tous les feedbacks
        $feedbacks = $this->requetePersonnalisee(
            "SELECT f.*, u.pseudo 
             FROM feedbacks_suggestions f
             JOIN utilisateurs u ON f.id_utilisateur = u.id_utilisateur
             ORDER BY f.date_feedback DESC"
        );
        
        // Afficher les feedbacks
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Utilisateur</th><th>Contenu</th><th>Date</th><th>Statut</th><th>Actions</th></tr>";
        
        foreach ($feedbacks as $feedback) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($feedback['id_feedback']) . "</td>";
            echo "<td>" . htmlspecialchars($feedback['pseudo']) . "</td>";
            echo "<td>" . htmlspecialchars($feedback['contenu']) . "</td>";
            echo "<td>" . htmlspecialchars($feedback['date_feedback']) . "</td>";
            echo "<td>" . htmlspecialchars($feedback['statut']) . "</td>";
            echo "<td>";
            echo "<a href='?page=admin&action=valider_feedback&id=" . $feedback['id_feedback'] . "'>Valider</a> | ";
            echo "<a href='?page=admin&action=refuser_feedback&id=" . $feedback['id_feedback'] . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
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

        // Valider le feedback
        $resultat = $this->requetePersonnalisee(
            "UPDATE feedbacks_suggestions SET statut = 'Valider' WHERE id_feedback = :id_feedback",
            [':id_feedback' => $idFeedback],
            false
        );
        
        if ($resultat) {
            $this->ajouterMessageSucces("Le feedback a été validé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la validation du feedback.");
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

        // Refuser le feedback
        $resultat = $this->requetePersonnalisee(
            "UPDATE feedbacks_suggestions SET statut = 'Refuser' WHERE id_feedback = :id_feedback",
            [':id_feedback' => $idFeedback],
            false
        );
        
        if ($resultat) {
            $this->ajouterMessageSucces("Le feedback a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus du feedback.");
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
        if (!isset($this->utilisateurConnecte['role']) || $this->utilisateurConnecte['role'] !== 'administrateur') {
            $this->ajouterMessageErreur("Vous n'avez pas les droits d'accès à cette page.");
            Reponses::rediriger('accueil');
            return false;
        }
        return true;
    }
}