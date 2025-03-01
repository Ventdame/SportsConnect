<?php

namespace App\Controleur;

use App\Modeles\EvenementModele;
use App\Modeles\UtilisateurModele;
use App\Modeles\ReservationModele;
use App\Modeles\SportModele;
use App\Vues\ProfilVue;

class ProfilControleur
{
    private $utilisateurModele;
    private $reservationModele;
    private $sportModele;
    private $evenementModele;

    /**
     * Constructeur du contrôleur ProfilControleur
     *
     * @param PDO $pdo La connexion à la base de données via PDO.
     */
    public function __construct($pdo)
    {
        $this->utilisateurModele = new UtilisateurModele($pdo);
        $this->reservationModele = new ReservationModele($pdo);
        $this->sportModele = new SportModele($pdo);
        $this->evenementModele = new EvenementModele($pdo);
    }

    /**
     * Méthode principale pour afficher le profil de l'utilisateur
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['utilisateur'])) {
            header("Location: ?page=connexion");
            exit;
        }

        $utilisateur = $_SESSION['utilisateur'];

        try {
            // Récupérer les événements réservés par l'utilisateur
            $reservations = $this->reservationModele->getEvenementsParUtilisateur($utilisateur['id_utilisateur']);

            // Récupérer les événements créés par l'utilisateur
            $evenementsCrees = $this->evenementModele->recupererEvenementsParUtilisateur($utilisateur['id_utilisateur']);

            // Récupérer les sports disponibles
            $sportsDisponibles = $this->sportModele->obtenirSports();

            // Récupérer les localisations existantes
            $localisationsExistantes = $this->evenementModele->obtenirLocalisations();

            // Passer les données nécessaires à la vue
            $vue = new ProfilVue(
                "Profil - SportConnect",
                $utilisateur,
                $reservations,
                $sportsDisponibles,
                $localisationsExistantes,
                $evenementsCrees
            );
            $vue->afficher();
        } catch (\Exception $e) {
            echo "Erreur lors de la récupération des données : " . $e->getMessage();
        }
    }



}
