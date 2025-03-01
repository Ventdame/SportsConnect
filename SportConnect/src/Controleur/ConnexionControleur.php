<?php
namespace App\Controleur;

use App\Modeles\UtilisateurModele;
use App\Vues\ConnexionVue;

class ConnexionControleur
{
    private $utilisateurModele; // Instance du modèle UtilisateurModele pour interagir avec les données utilisateur

    /**
     * Constructeur de la classe ConnexionControleur.
     *
     * Ce constructeur permet d'initialiser l'objet `UtilisateurModele` avec la connexion PDO.
     * Ce modèle est utilisé pour interagir avec les données utilisateur, notamment pour vérifier la connexion.
     *
     * @param PDO $pdo Connexion à la base de données.
     */
    public function __construct($pdo)
    {
        $this->utilisateurModele = new UtilisateurModele($pdo);  // Initialisation du modèle UtilisateurModele
    }

    /**
     * Méthode pour afficher la vue de connexion.
     *
     * Cette méthode est responsable de l'affichage de la page de connexion.
     * Elle appelle la vue `ConnexionVue` et l'affiche.
     */
    public function index()
    {
        $vue = new ConnexionVue("Connexion - SportConnect");  // Création de l'objet vue avec un titre spécifique
        $vue->afficher();  // Appel de la méthode `afficher()` de la vue pour rendre la page
    }

    /**
     * Méthode pour traiter la soumission du formulaire de connexion.
     *
     * Cette méthode est appelée lorsqu'un utilisateur soumet ses informations de connexion.
     * Elle vérifie que la requête est de type POST et ensuite vérifie les informations de l'utilisateur
     * en les comparant avec la base de données. Si les informations sont correctes, l'utilisateur est redirigé
     * vers son profil. En cas d'erreur, un message d'erreur est affiché.
     */
    public function traiterConnexion()
    {
        // Vérification si la méthode de la requête est POST (ce qui signifie que le formulaire a été soumis)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération des données du formulaire
            $email = $_POST['email'] ?? '';  // Email de l'utilisateur
            $motDePasse = $_POST['mot_de_passe'] ?? '';  // Mot de passe de l'utilisateur

            // Vérification des informations d'identification de l'utilisateur via le modèle
            if ($this->utilisateurModele->verifierConnexion($email, $motDePasse)) {
                // Si l'utilisateur est authentifié avec succès
                $utilisateur = $this->utilisateurModele->RecupUtilisateurParEmail($email);  // Récupérer les informations complètes de l'utilisateur

                // Démarrer la session et stocker les informations de l'utilisateur dans la session
                session_start();
                $_SESSION['utilisateur'] = [
                    'id_utilisateur' => $utilisateur['id_utilisateur'],
                    'pseudo' => $utilisateur['pseudo'],
                    'prenom' => $utilisateur['prenom'],
                    'email' => $utilisateur['email'],
                    'pmr' => $utilisateur['pmr'] ?? null  // Vérifier si l'utilisateur est PMR (Personne à Mobilité Réduite)
                ];

                // Redirection vers la page de profil de l'utilisateur après la connexion
                header("Location: ?page=profil");
                exit;  // Arrêt de l'exécution du script après la redirection
            } else {
                // Si l'authentification échoue, afficher un message d'erreur
                $erreurs = ["Email ou mot de passe incorrect."];  // Message d'erreur à afficher
                $vue = new ConnexionVue("Connexion - SportConnect", $erreurs);  // Créer l'objet vue avec l'erreur
                $vue->afficher();  // Afficher la vue de connexion avec l'erreur
            }
        } else {
            // Si la méthode de la requête n'est pas POST (par exemple, si l'utilisateur tente d'accéder à cette page sans soumettre le formulaire)
            header("Location: ?page=connexion");  // Rediriger l'utilisateur vers la page de connexion
            exit;  // Arrêt de l'exécution du script après la redirection
        }
    }
}
