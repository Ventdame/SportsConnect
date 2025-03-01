<?php

namespace App\Controleur;

use App\Modeles\ReservationModele;
use App\Vues\ReservationVue;

class ReservationControleur
{
    private $reservationModele;

    /**
     * Constructeur du contrôleur ReservationControleur
     * 
     * Initialise le modèle de réservation avec la connexion PDO pour accéder aux données des réservations.
     * Démarre également la session si elle n'est pas déjà active.
     *
     * @param PDO $pdo La connexion à la base de données via PDO.
     */
    public function __construct($pdo)
    {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Instancier le modèle de réservation pour manipuler les réservations
        $this->reservationModele = new ReservationModele($pdo);
    }

    /**
     * Méthode principale pour afficher les réservations
     * 
     * Cette méthode récupère les paramètres de recherche de la requête GET, 
     * recherche les réservations en fonction de ces critères, puis affiche les résultats via la vue.
     * Elle passe également la liste des sports disponibles à la vue.
     */
    public function index()
    {
        // Récupérer les valeurs du formulaire (ville, sport, date)
        $ville = $_GET['ville'] ?? null;
        $sport = $_GET['sport'] ?? null;
        $date = $_GET['date'] ?? null;

        // Debugging : Affichage dans les logs des paramètres reçus
        error_log("Recherche : Ville = $ville, Sport = $sport, Date = $date");

        try {
            // Récupérer les réservations filtrées depuis le modèle de réservation
            $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date);

            // Récupérer la liste des sports disponibles via le modèle SportModele
            $sportModele = new \App\Modeles\SportModele($this->reservationModele->getPdo());
            $sports = $sportModele->RecupSports();


            // Afficher la vue avec les résultats des réservations et la liste des sports
            $vue = new ReservationVue("Réservations - SportsConnect", $reservations, $sports, $_SESSION['utilisateur'] ?? null);
            $vue->afficher();
        } catch (\Exception $e) {
            // En cas d'erreur, afficher le message d'exception
            echo "Erreur : " . $e->getMessage();
        }
    }

    /**
     * Méthode pour réserver un événement
     * 
     * Vérifie si l'utilisateur est connecté avant de procéder à la réservation.
     * Si les données sont envoyées par POST, la réservation est ajoutée via le modèle.
     * Après la réussite, l'utilisateur est redirigé vers la page des réservations.
     */
    public function reserver()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur'])) {
            header("Location: ?page=connexion&message=connectez-vous");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupérer l'id de l'événement à réserver et l'id de l'utilisateur
                $idEvenement = $_POST['id_evenement'] ?? null;
                $idUtilisateur = $_SESSION['utilisateur']['id_utilisateur'];

                if ($idEvenement) {
                    // Vérifier si l'utilisateur est déjà inscrit à cet événement
                    if ($this->reservationModele->estDejaInscrit($idUtilisateur, $idEvenement)) {
                        $_SESSION['messageErreur'] = "Vous vous êtes déjà inscrit à cet événement.";
                    } else {
                        // Ajouter la réservation via le modèle
                        $this->reservationModele->ajouterReservation($idEvenement, $idUtilisateur);

                        // Stocker le message de succès dans la session
                        $_SESSION['messageReussite'] = "Vous vous êtes bien inscrit à l'événement, retrouvez-le de suite dans votre profil !";
                    }

                    // Rediriger vers la page de réservation
                    header("Location: ?page=reservation");
                    exit;
                } else {
                    throw new \Exception("ID d'événement manquant.");
                }
            } catch (\Exception $e) {
                // Afficher le message d'erreur si une exception se produit
                $_SESSION['messageErreur'] = "Erreur lors de la réservation : " . $e->getMessage();
                header("Location: ?page=reservation");
                exit;
            }
        } else {
            // Rediriger vers la page de réservation si la méthode n'est pas POST
            header("Location: ?page=reservation");
            exit;
        }
    }


    public function supprimer()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur'])) {
            header("Location: ?page=connexion");
            exit;
        }

        // Récupérer l'utilisateur connecté
        $idUtilisateur = $_SESSION['utilisateur']['id_utilisateur'];

        // Vérifier si un ID d'événement a été envoyé
        if (isset($_POST['id_evenement'])) {
            $idEvenement = $_POST['id_evenement'];

            try {
                // Appeler la méthode pour supprimer la réservation
                $reservationSupprimee = $this->reservationModele->supprimerReservation($idUtilisateur, $idEvenement);

                if ($reservationSupprimee) {
                    // Ajouter un message de succès dans la session
                    $_SESSION['messageReussite'] = "La réservation a été supprimée avec succès.";
                } else {
                    $_SESSION['messageErreur'] = "Impossible de supprimer la réservation. Veuillez réessayer.";
                }
            } catch (\Exception $e) {
                $_SESSION['messageErreur'] = "Erreur lors de la suppression : " . $e->getMessage();
            }
        } else {
            $_SESSION['messageErreur'] = "Aucun ID d'événement spécifié.";
        }

        // Rediriger vers la page de profil
        header("Location: ?page=profil");
        exit;
    }


    /**      
     * Méthode de recherche des réservations via AJAX
     *
     * Cette méthode traite les requêtes AJAX pour rechercher les réservations en fonction des critères fournis.
     * Elle retourne les résultats sous forme de JSON.
     */
    public function rechercheAjax()
    {
        // Lire les données envoyées via AJAX
        $data = json_decode(file_get_contents("php://input"), true);

        // Vérification des données reçues
        if (!$data || (!isset($data['ville']) && !isset($data['sport']) && !isset($data['date']))) {
            // Retourner un message d'erreur en JSON si aucun critère n'est fourni
            header('Content-Type: application/json');
            echo json_encode(["error" => "Aucun critère de recherche fourni (ville, sport, ou date)."], JSON_PRETTY_PRINT);
            return;
        }

        // Extraire les critères de recherche
        $ville = $data['ville'] ?? null;
        $sport = $data['sport'] ?? null;
        $date = $data['date'] ?? null;

        try {
            // Rechercher les réservations en fonction des critères fournis
            $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date);

            // Retourner les résultats en format JSON
            header('Content-Type: application/json');
            echo json_encode($reservations, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner un code d'erreur et le message d'exception sous forme de JSON
            header('Content-Type: application/json');
            echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }
}
