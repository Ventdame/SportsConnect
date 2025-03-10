<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\ReservationModele;
use App\Fabrique\SportModele;
use App\Vues\ReservationVue;

/**
 * Contrôleur pour la gestion des réservations
 */
class ReservationControleur extends ControleurBase
{
    /**
     * Instance du modèle ReservationModele
     * 
     * @var ReservationModele
     */
    private $reservationModele;

    /**
     * Constructeur du contrôleur ReservationControleur
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->reservationModele = new ReservationModele($pdo);
    }

    /**
     * Méthode principale pour afficher les réservations.
     */
    public function index()
    {
        $ville = $_GET['ville'] ?? null;
        $sport = $_GET['sport'] ?? null;
        $date = $_GET['date'] ?? null;
    
        // Récupérer le statut PMR de l'utilisateur
        $pmrSession = $this->utilisateurConnecte['pmr'] ?? 'non';
    
        try {
            // Récupérer les réservations
            $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date, $pmrSession);
    
            // Ajouter les informations sur les participants
            foreach ($reservations as &$reservation) {
                $reservation['Participants'] = $this->reservationModele->obtenirParticipants($reservation['id_evenement']);
            }
    
            // Récupérer les sports disponibles
            $sportModele = new SportModele($this->pdo);
            $sports = $sportModele->obtenirSportsSelonUtilisateur($pmrSession);
    
            // Afficher la vue
            $vue = new ReservationVue(
                "Réservations - SportsConnect",
                $reservations,
                $sports,
                $this->utilisateurConnecte
            );
            $vue->afficher();
        } catch (\Exception $e) {
            $this->ajouterMessageErreur("Erreur : " . $e->getMessage());
            Reponses::rediriger('accueil');
        }
    }
    
    /**
     * Méthode pour réserver un événement.
     */
    public function reserver()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->exigerConnexion("Connectez-vous pour réserver un événement")) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idEvenement = $_POST['id_evenement'] ?? null;
                $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];

                if ($idEvenement) {
                    // Vérifier si l'utilisateur est déjà inscrit
                    if ($this->reservationModele->estDejaInscrit($idUtilisateur, $idEvenement)) {
                        $this->ajouterMessageErreur("Vous vous êtes déjà inscrit à cet événement.");
                    } else {
                        // Ajouter la réservation
                        $this->reservationModele->ajouterReservation($idEvenement, $idUtilisateur);
                        $this->ajouterMessageReussite("Inscription réussie !");
                    }

                    Reponses::rediriger('reservation');
                } else {
                    throw new \Exception("ID d'événement manquant.");
                }
            } catch (\Exception $e) {
                $this->ajouterMessageErreur("Erreur lors de la réservation : " . $e->getMessage());
                Reponses::rediriger('reservation');
            }
        } else {
            Reponses::rediriger('reservation');
        }
    }

    /**
     * Méthode pour supprimer une réservation.
     */
    public function supprimer()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->exigerConnexion()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];
            $idEvenement = $_POST['id_evenement'] ?? null;

            if ($idEvenement) {
                try {
                    // Supprimer la réservation
                    $reservationSupprimee = $this->reservationModele->supprimerReservation($idUtilisateur, $idEvenement);
                    
                    if ($reservationSupprimee) {
                        $this->ajouterMessageReussite("La réservation a été supprimée avec succès.");
                    } else {
                        $this->ajouterMessageErreur("Impossible de supprimer la réservation.");
                        
                    }
                } catch (\Exception $e) {
                    $this->ajouterMessageErreur("Erreur lors de la suppression : " . $e->getMessage());
                }
            } else {
                $this->ajouterMessageErreur("Aucun ID d'événement spécifié.");
            }
        }

        Reponses::rediriger('profil');
    }

    /**
     * Méthode de recherche des réservations via AJAX.
     */
    public function rechercheAjax()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $ville = $data['ville'] ?? null;
        $sport = $data['sport'] ?? null;
        $date = $data['date'] ?? null;
    
        // Récupérer statut PMR de l'utilisateur
        $pmr = $_SESSION['utilisateur']['pmr'] ?? 'non'; // 'oui' ou 'non'
    
        // Appeler une méthode du modèle qui filtre déjà par PMR
        $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date, $pmr);
    
        header('Content-Type: application/json');
        echo json_encode($reservations);
    }
    
}