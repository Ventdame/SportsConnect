<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\UtilisateurModele;
use App\Vues\ConnexionVue;

/**
 * Contrôleur pour la gestion de la connexion des utilisateurs
 */
class ConnexionControleur extends ControleurBase
{
    /**
     * Instance du modèle UtilisateurModele
     * 
     * @var UtilisateurModele
     */
    private $utilisateurModele;

    /**
     * Constructeur du contrôleur ConnexionControleur
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->utilisateurModele = new UtilisateurModele($pdo);
    }

    /**
     * Méthode pour afficher la page de connexion
     */
    public function index()
    {
        // Interdire l'accès si l'utilisateur est déjà connecté
        if (!$this->interdireAccesSiConnecte("Vous êtes déjà connecté. Déconnectez-vous pour vous connecter avec un autre compte.")) {
            return;
        }

        $vue = new ConnexionVue("Connexion - SportConnect");
        $vue->afficher();
    }

    /**
     * Méthode pour traiter la soumission du formulaire de connexion
     */
    public function traiterConnexion()
    {
        // Interdire l'accès si l'utilisateur est déjà connecté
        if (!$this->interdireAccesSiConnecte()) {
            return;
        }
        
        // Vérification si la méthode de la requête est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $donnees = $this->obtenirDonneesFormulaire(champsRequis: ['identifiant', 'mot_de_passe']);

            if ($donnees) {
                // Récupérer l'identifiant (pseudo ou email) et le mot de passe
                $identifiant = $donnees['identifiant'];
                $motDePasse = $donnees['mot_de_passe'];

                // Vérifier la connexion via le modèle (logique interne : email OU pseudo)
                $utilisateur = $this->utilisateurModele->verifierConnexion($identifiant, $motDePasse);

                if ($utilisateur) {
                    // Si l'utilisateur est authentifié avec succès,
                    // on stocke les informations de l'utilisateur dans la session
                    session_start();
                    $_SESSION['utilisateur'] = [
                        'id_utilisateur' => $utilisateur['id_utilisateur'],
                        'pseudo'        => $utilisateur['pseudo'],
                        'prenom'        => $utilisateur['prenom'],
                        'email'         => $utilisateur['email'],
                        'pmr'           => $utilisateur['pmr'] ?? null,
                        'sexe'          => $utilisateur['sexe'] ?? 'A',
                        'role'          => $utilisateur['role'] ?? 'user'
                    ];

                    // Redirection vers la page de profil
                    Reponses::rediriger('profil', [], "Connexion réussie !");
                    return;
                } else {
                    // Si l'authentification échoue
                    $erreurs = ["Identifiant ou mot de passe incorrect."];
                    $vue = new ConnexionVue("Connexion - SportConnect", $erreurs);
                    $vue->afficher();
                }
            } else {
                // Si des champs requis sont manquants
                $vue = new ConnexionVue("Connexion - SportConnect", $this->erreurs);
                $vue->afficher();
            }
        } else {
            // Si la méthode de la requête n'est pas POST, on redirige vers la page de connexion
            Reponses::rediriger('connexion');
        }
    }
}
