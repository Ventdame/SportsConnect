<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\ReservationModele;
use App\Fabrique\SportModele;
use App\Vues\ReservationVue;
use App\Securite\CSRFProtection;

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

            // Sécuriser les réservations et ajouter les informations sur les participants
            foreach ($reservations as &$reservation) {
                $reservation['Participants'] = $this->reservationModele->obtenirParticipants($reservation['id_evenement']);

                // Sécuriser l'ID de l'événement avec un token
                $reservation['secure_token'] = $this->securiserID('evenement', $reservation['id_evenement']);

                // Supprimer l'ID réel pour ne pas l'exposer dans le HTML
                unset($reservation['id_evenement']);
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
                // Récupérer directement le token sécurisé sans validation complète
                $secure_token = $_POST['secure_token'] ?? null;

                if (!$secure_token) {
                    throw new \Exception("Token de sécurité manquant.");
                }

                // Récupérer l'ID de l'événement à partir du token sécurisé
                $idEvenement = $this->recupererIDSecurise($secure_token, 'evenement');

                // Si l'ID n'est pas trouvé dans la session courante, il a peut-être expiré
                // ou la session a été réinitialisée
                if ($idEvenement === false) {
                    // On pourrait essayer de récupérer l'ID directement si on a une façon de le faire
                    // Pour l'instant, on signale juste l'erreur
                    throw new \Exception("Session expirée ou requête invalide. Veuillez réessayer de réserver.");
                }

                // Récupérer l'ID de l'utilisateur connecté
                $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];

                // Vérifier si l'utilisateur est déjà inscrit
                if ($this->reservationModele->estDejaInscrit($idUtilisateur, $idEvenement)) {
                    $this->ajouterMessageErreur("Vous vous êtes déjà inscrit à cet événement.");
                } else {
                    // Ajouter la réservation
                    $this->reservationModele->ajouterReservation($idEvenement, $idUtilisateur);
                    $this->ajouterMessageReussite("Inscription réussie !");
                }

                Reponses::rediriger('reservation');
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
            try {
                // Récupérer directement l'ID de l'événement sans validation complexe
                $idEvenement = null;

                // Essayer d'abord de récupérer depuis secure_token si disponible
                if (isset($_POST['secure_token'])) {
                    $idEvenement = $this->recupererIDSecurise($_POST['secure_token'], 'evenement');
                }

                // Si aucun ID n'a été trouvé via le token, essayer de le récupérer directement
                if ($idEvenement === false || $idEvenement === null) {
                    if (isset($_POST['id_evenement'])) {
                        $idEvenement = intval($_POST['id_evenement']);
                    } else {
                        throw new \Exception("ID de l'événement introuvable.");
                    }
                }

                $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];

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
        }

        Reponses::rediriger('profil');
    }

    /**
     * Méthode de recherche des réservations via AJAX.
     */
    public function rechercheAjax()
    {
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        // Valider et nettoyer les entrées
        $ville = isset($data['ville']) ? filter_var($data['ville'], FILTER_SANITIZE_STRING) : null;
        $sport = isset($data['sport']) ? filter_var($data['sport'], FILTER_SANITIZE_STRING) : null;
        $date = isset($data['date']) ? (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date']) ? $data['date'] : null) : null;

        // Récupérer statut PMR de l'utilisateur avec valeur par défaut sécurisée
        $pmr = $_SESSION['utilisateur']['pmr'] ?? 'non';

        // Appeler le modèle avec des paramètres validés
        $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date, $pmr);

        // Réponse JSON sécurisée
        header('Content-Type: application/json');
        echo json_encode($reservations);
        exit;
    }
}
