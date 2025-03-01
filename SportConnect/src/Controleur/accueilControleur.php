<?php
namespace App\Controleur;

use App\Modeles\SportModele;
use App\Vues\AccueilVue;

class AccueilControleur
{
    private $sportModele;  // Instance du modèle SportModele pour accéder aux données liées aux sports

    /**
     * Constructeur du contrôleur AccueilControleur.
     *
     * Vérifie si la session est démarrée, et si ce n'est pas le cas, elle est lancée. 
     * Initialise également le modèle SportModele pour récupérer les sports disponibles.
     *
     * @param PDO|null $pdo L'objet de connexion à la base de données (optionnel).
     */
    public function __construct($pdo = null)
    {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Instanciation du modèle pour accéder aux données des sports
        $this->sportModele = new SportModele($pdo);
    }

    /**
     * Méthode pour afficher la page d'accueil avec les sports disponibles et les informations sur l'utilisateur connecté.
     *
     * Cette méthode récupère les sports disponibles depuis le modèle, vérifie si un utilisateur est connecté
     * et affiche la vue correspondante.
     */
    public function index()
    {
        try {
            // Récupérer la liste des sports via le modèle
            $sports = $this->sportModele->RecupSports();  // Récupère tous les sports disponibles

            // Vérifier si un utilisateur est connecté
            $utilisateurConnecte = null;  // Variable pour stocker les informations de l'utilisateur connecté
            if (isset($_SESSION['utilisateur']) && is_array($_SESSION['utilisateur'])) {
                $utilisateurConnecte = $_SESSION['utilisateur'];  // Si l'utilisateur est connecté, on récupère ses informations depuis la session
            }

            // Instanciation de la vue avec les données récupérées
            // La vue est initialisée avec le titre, la liste des sports et les informations de l'utilisateur connecté
            $vue = new AccueilVue("Bienvenue sur SportsConnect", $sports, $utilisateurConnecte);
            // Affiche la vue de la page d'accueil
            $vue->afficher();
        } catch (\Exception $e) {
            // Gère les erreurs potentielles en cas de problème avec la récupération des sports
            echo "Erreur lors de la récupération des sports : " . $e->getMessage();
        }
    }
}
