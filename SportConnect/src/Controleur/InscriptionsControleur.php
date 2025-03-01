<?php

namespace App\Controleur;

use App\Modeles\UtilisateurModele;
use App\Vues\InscriptionVue;

class InscriptionsControleur
{
    private $utilisateurModele;

    /**
     * Constructeur du contrôleur des inscriptions.
     *
     * Initialise le modèle de l'utilisateur et démarre la session si elle n'est pas déjà active.
     *
     * @param PDO $pdo Objet de connexion à la base de données.
     */
    public function __construct($pdo)
    {
        // Initialisation du modèle utilisateur pour accéder aux données de l'utilisateur
        $this->utilisateurModele = new UtilisateurModele($pdo);

        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Démarre une session PHP pour l'utilisateur
        }
    }

    /**
     * Méthode pour afficher le formulaire d'inscription.
     */
    public function index()
    {
        // Crée une instance de la vue d'inscription et l'affiche
        $vue = new InscriptionVue("Inscription - SportConnect");
        $vue->afficher();
    }

    /**
     * Méthode pour traiter les données soumises lors de l'inscription.
     */
    public function traiter()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = $_POST['pseudo'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $motDePasse = $_POST['mot_de_passe'] ?? '';
            $motDePasseConfirmation = $_POST['mot_de_passe_confirmation'] ?? '';
            $pmr = isset($_POST['pmr']) ? 1 : 0;

            $erreurs = $this->validerDonnees($pseudo, $prenom, $email, $motDePasse, $motDePasseConfirmation);

            if (empty($erreurs)) {
                try {
                    $userId = $this->utilisateurModele->creerUtilisateur($pseudo, $prenom, $email, $motDePasse, $pmr);

                    $_SESSION['id_utilisateur'] = $userId;
                    $_SESSION['pseudo'] = $pseudo;
                    $_SESSION['email'] = $email;

                    // Ajouter le message de succès dans la session
                    $_SESSION['messageReussite'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";

                    // Redirection après succès
                    header("Location: ?page=inscriptions");
                    exit;
                } catch (\Exception $e) {
                    $erreurs[] = "Erreur lors de l'inscription : " . $e->getMessage();
                }
            }

            // Réafficher le formulaire avec les erreurs
            $vue = new InscriptionVue("Inscription - SportConnect", $erreurs);
            $vue->afficher();
        } else {
            header("Location: ?page=inscriptions");
            exit;
        }
    }





    /**
     * Valide les données soumises par l'utilisateur dans le formulaire d'inscription.
     */
    private function validerDonnees($pseudo, $prenom, $email, $motDePasse, $motDePasseConfirmation)
    {
        $erreurs = []; // Tableau pour stocker les erreurs de validation

        // Vérifier que le pseudo est d'au moins 3 caractères
        if (strlen($pseudo) < 3) {
            $erreurs[] = "Le pseudo doit contenir au moins 3 caractères !";
        }

        // Vérifier que le pseudo n'est pas utilisé dans la DB
        if ($this->utilisateurModele->pseudoExistant($pseudo)) {
            $erreurs[] = "Attention, ce pseudo est déjà utilisé !";
        }

        // Vérifier que le prénom est d'au moins 2 caractères
        if (strlen($prenom) < 2) {
            $erreurs[] = "Le prénom doit contenir au moins 2 caractères !";
        }

        // Vérifier que le prénom ne dépasse pas 14 caractères
        if (strlen($prenom) > 14) {
            $erreurs[] = "Le prénom ne doit pas contenir plus de 14 caractères !";
        }

        // Vérifier la validité de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "L'adresse email n'est pas valide !";
        }

        // Vérifier que le mot de passe est d'au moins 8 caractères
        if (strlen($motDePasse) < 8) {
            $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères !";
        }

        // Vérifier que les deux mots de passe correspondent
        if ($motDePasse !== $motDePasseConfirmation) {
            $erreurs[] = "Les deux mots de passe ne correspondent pas !";
        }

        // Vérifier que l'email n'est pas déjà utilisé dans la base de données
        if ($this->utilisateurModele->emailExistant($email)) {
            $erreurs[] = "Cet email est déjà utilisé !";
        }

        // Retourner les erreurs de validation
        return $erreurs;
    }
}
