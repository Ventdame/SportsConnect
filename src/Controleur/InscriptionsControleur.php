<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\UtilisateurModele;
use App\Vues\InscriptionVue;

/**
 * Contrôleur pour la gestion des inscriptions d'utilisateurs
 */
class InscriptionsControleur extends ControleurBase
{
    /**
     * Instance du modèle UtilisateurModele
     * 
     * @var UtilisateurModele
     */
    private $utilisateurModele;

    /**
     * Constructeur du contrôleur InscriptionsControleur
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->utilisateurModele = new UtilisateurModele($pdo);
    }

    /**
     * Méthode pour afficher le formulaire d'inscription
     */
    public function index()
    {
        // Interdire l'accès si l'utilisateur est déjà connecté
        if (!$this->interdireAccesSiConnecte("Vous êtes déjà connecté. Déconnectez-vous pour créer un nouveau compte.")) {
            return;
        }

        $vue = new InscriptionVue("Inscription - SportConnect");
        $vue->afficher();
    }

    /**
     * Méthode pour traiter les données soumises lors de l'inscription
     */
    public function traiterInscription()
    {
        // Interdire l'accès si l'utilisateur est déjà connecté
        if (!$this->interdireAccesSiConnecte()) {
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération des données du formulaire
            $donnees = $this->obtenirDonneesFormulaire([
                'pseudo',
                'prenom',
                'email',
                'mot_de_passe',
                'mot_de_passe_confirmation'
            ]);

            if ($donnees) {
                $pseudo = $donnees['pseudo'];
                $prenom = $donnees['prenom'];
                $email = $donnees['email'];
                $motDePasse = $donnees['mot_de_passe'];
                $motDePasseConfirmation = $donnees['mot_de_passe_confirmation'];
                $pmr = isset($_POST['pmr']) ? 1 : 0;

                // Validation des données
                $erreurs = $this->validerDonnees($pseudo, $prenom, $email, $motDePasse, $motDePasseConfirmation);

                if (empty($erreurs)) {
                    try {
                        // Création de l'utilisateur
                        $userId = $this->utilisateurModele->creerUtilisateur($pseudo, $prenom, $email, $motDePasse, $pmr);

                        // Ajout du message de succès et redirection
                        $this->ajouterMessageReussite("Inscription réussie ! Vous pouvez maintenant vous connecter.");
                        Reponses::rediriger('connexion');
                    } catch (\Exception $e) {
                        $erreurs[] = "Erreur lors de l'inscription : " . $e->getMessage();
                    }
                }

                // S'il y a des erreurs, réaffichage du formulaire avec les erreurs
                if (!empty($erreurs)) {
                    $vue = new InscriptionVue("Inscription - SportConnect", $erreurs);
                    $vue->afficher();
                }
            } else {
                // Si des champs requis sont manquants
                $vue = new InscriptionVue("Inscription - SportConnect", $this->erreurs);
                $vue->afficher();
            }
        } else {
            // Si la méthode de la requête n'est pas POST
            Reponses::rediriger('inscriptions');
        }
    }

    /**
     * Valide les données soumises par l'utilisateur dans le formulaire d'inscription
     * 
     * @param string $pseudo
     * @param string $prenom
     * @param string $email
     * @param string $motDePasse
     * @param string $motDePasseConfirmation
     * @return array Liste des erreurs de validation
     */
    private function validerDonnees($pseudo, $prenom, $email, $motDePasse, $motDePasseConfirmation)
    {
        $erreurs = [];

        // Vérification du pseudo
        if (strlen($pseudo) < 3) {
            $erreurs[] = "Le pseudo doit contenir au moins 3 caractères !";
        }
        if ($this->utilisateurModele->pseudoExistant($pseudo)) {
            $erreurs[] = "Attention, ce pseudo est déjà utilisé !";
        }

        // Vérification du prénom
        if (strlen($prenom) < 2) {
            $erreurs[] = "Le prénom doit contenir au moins 2 caractères !";
        }
        if (strlen($prenom) > 14) {
            $erreurs[] = "Le prénom ne doit pas contenir plus de 14 caractères !";
        }

        // Vérification de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "L'adresse email n'est pas valide !";
        }
        if ($this->utilisateurModele->emailExistant($email)) {
            $erreurs[] = "Cet email est déjà utilisé !";
        }

        // Vérification du mot de passe
        if (strlen($motDePasse) < 8) {
            $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères !";
        }
        if ($motDePasse !== $motDePasseConfirmation) {
            $erreurs[] = "Les deux mots de passe ne correspondent pas !";
        }

        return $erreurs;
    }
}
