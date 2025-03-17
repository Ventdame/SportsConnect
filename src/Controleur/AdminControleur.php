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
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
    /**
     * Instance du modèle EvenementModele
     * 
     * @var EvenementModele
     */
    private $evenementModele;
    
    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
    /**
     * Instance du modèle UtilisateurModele
     * 
     * @var UtilisateurModele
     */
    private $utilisateurModele;
    
    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
    /**
     * Instance du modèle NotificationModele
     * 
     * @var NotificationModele
     */
    private $notificationModele;

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
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
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
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
        echo "<p>Bienvenue, " . $this->utilisateurConnecte['pseudo'] . "!</p>";
        
        // Liens vers les différentes fonctionnalités administrateur
        echo "<ul>";
        echo "<li><a href='" . Reponses::url('admin/evenements') . "'>Gestion des événements</a></li>";
        echo "<li><a href='" . Reponses::url('admin/utilisateurs') . "'>Gestion des utilisateurs</a></li>";
        echo "<li><a href='" . Reponses::url('admin/feedbacks') . "'>Gestion des feedbacks</a></li>";
        echo "</ul>";
    }

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
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

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
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
            echo "<td>" . $evenement['id_evenement'] . "</td>";
            echo "<td>" . $evenement['nom_evenement'] . "</td>";
            echo "<td>" . $evenement['date_evenement'] . "</td>";
            echo "<td>" . ($evenement['statut'] ?? 'En attente') . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/approuver_evenement/' . $evenement['id_evenement']) . "'>Approuver</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_evenement/' . $evenement['id_evenement']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
    /**
     * Méthode pour approuver un événement
     * 
     * @param int $idEvenement ID de l'événement à approuver
     */
    public function approuver_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Approuver l'événement
        $resultat = $this->evenementModele->approuverEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été approuvé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de l'approbation de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
    /**
     * Méthode pour afficher le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function refuser_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Récupérer les informations de l'événement
        $evenement = $this->evenementModele->obtenirEvenementParId($idEvenement);
        
        if (!$evenement) {
            $this->ajouterMessageErreur("L'événement demandé n'existe pas.");
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Afficher le formulaire de refus
        echo "<h1>Refuser l'événement : " . $evenement['nom_evenement'] . "</h1>";
        echo "<form method='post' action='" . Reponses::url('admin/traiter_refus_evenement/' . $idEvenement) . "'>";
        echo "<label for='motif_refus'>Motif du refus :</label><br>";
        echo "<textarea name='motif_refus' id='motif_refus' rows='5' cols='50' required></textarea><br><br>";
        echo "<input type='submit' value='Refuser l\'événement'>";
        echo "</form>";
    }

    /**
     * Méthode pour traiter le formulaire de refus d'un événement
     * 
     * @param int $idEvenement ID de l'événement à refuser
     */
    public function traiter_refus_evenement($idEvenement)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Vérifier si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Reponses::rediriger('admin/evenements');
            return;
        }

        // Récupérer le motif du refus
        $motifRefus = $_POST['motif_refus'] ?? '';
        
        if (empty($motifRefus)) {
            $this->ajouterMessageErreur("Le motif du refus est obligatoire.");
            Reponses::rediriger('admin/refuser_evenement/' . $idEvenement);
            return;
        }

        // Refuser l'événement
        $resultat = $this->evenementModele->refuserEvenement($idEvenement, $this->utilisateurConnecte['id_utilisateur'], $motifRefus);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'événement a été refusé avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du refus de l'événement.");
        }
        
        Reponses::rediriger('admin/evenements');
    }

    /**
     * Méthode pour gérer les utilisateurs (liste, modification des rôles)
     */
    public function promouvoirAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function retirerAdmin($idUtilisateur)
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'utilisateur']);
        Reponses::redirect('admin/utilisateurs');
    }

    public function utilisateurs()
    {
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        $utilisateurs = $this->utilisateurModele->obtenirTous();
        $vue = new AdminVue();
        Reponses::render($vue->renderUtilisateurs($utilisateurs));
    }

    /**
     * Méthode pour promouvoir un utilisateur au rôle d'administrateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur à promouvoir
     */
    public function promouvoir_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Promouvoir l'utilisateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'administrateur']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("L'utilisateur a été promu administrateur avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors de la promotion de l'utilisateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
    }

    /**
     * Méthode pour retirer les droits d'administrateur à un utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     */
    public function retirer_admin($idUtilisateur)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
            return;
        }

        // Empêcher de retirer ses propres droits d'administrateur
        if ($idUtilisateur == $this->utilisateurConnecte['id_utilisateur']) {
            $this->ajouterMessageErreur("Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            Reponses::rediriger('admin/utilisateurs');
            return;
        }

        // Retirer les droits d'administrateur
        $resultat = $this->utilisateurModele->mettreAJourUtilisateur($idUtilisateur, ['role' => 'user']);
        
        if ($resultat) {
            $this->ajouterMessageSucces("Les droits d'administrateur ont été retirés avec succès.");
        } else {
            $this->ajouterMessageErreur("Une erreur est survenue lors du retrait des droits d'administrateur.");
        }
        
        Reponses::rediriger('admin/utilisateurs');
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
            echo "<td>" . $feedback['id_feedback'] . "</td>";
            echo "<td>" . $feedback['pseudo'] . "</td>";
            echo "<td>" . $feedback['contenu'] . "</td>";
            echo "<td>" . $feedback['date_feedback'] . "</td>";
            echo "<td>" . $feedback['statut'] . "</td>";
            echo "<td>";
            echo "<a href='" . Reponses::url('admin/valider_feedback/' . $feedback['id_feedback']) . "'>Valider</a> | ";
            echo "<a href='" . Reponses::url('admin/refuser_feedback/' . $feedback['id_feedback']) . "'>Refuser</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Méthode pour valider un feedback
     * 
     * @param int $idFeedback ID du feedback à valider
     */
    public function valider_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }

    /**
     * Méthode pour refuser un feedback
     * 
     * @param int $idFeedback ID du feedback à refuser
     */
    public function refuser_feedback($idFeedback)
    {
        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$this->exigerConnexion() || !$this->exigerRoleAdmin()) {
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
        
        Reponses::rediriger('admin/feedbacks');
    }
    