<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\EvenementModele;
use App\Fabrique\ReservationModele;
use App\Fabrique\SportModele;
use App\Fabrique\NotificationModele;
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
     * Instance du modèle NotificationModele
     * 
     * @var NotificationModele
     */
    private $notificationModele;

    private $EvenementModele;
    /**
     * Constructeur du contrôleur ReservationControleur
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->reservationModele = new ReservationModele($pdo);
        $this->notificationModele = new NotificationModele($pdo);
        $this->EvenementModele = new EvenementModele($pdo);
    }

    /**
     * Méthode principale pour afficher les réservations.
     */
    public function index()
    {
        $ville = $_GET['ville'] ?? null;
        $sport = $_GET['sport'] ?? null;
        $date = $_GET['date'] ?? null;

        // Récupérer le statut PMR et le sexe de l'utilisateur
        $pmrSession = $this->utilisateurConnecte['pmr'] ?? 'non';
        $sexeUtilisateur = $this->utilisateurConnecte['sexe'] ?? 'A';

        try {
            // Récupérer les réservations avec tous les paramètres
            $reservations = $this->reservationModele->rechercherReservations(
                $ville,
                $sport,
                $date,
                $pmrSession,
                $sexeUtilisateur
            );

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
            $sports = $sportModele->obtenirSportsSelonUtilisateur($pmrSession, $sexeUtilisateur);

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
                $idEvenement = null;

                if ($secure_token) {
                    // Récupérer l'ID de l'événement à partir du token sécurisé
                    $idEvenement = $this->recupererIDSecurise($secure_token, 'evenement');
                } else if (isset($_POST['id_evenement'])) {
                    // Backup: utiliser l'ID directement si fourni
                    $idEvenement = intval($_POST['id_evenement']);
                } else {
                    throw new \Exception("Identifiant d'événement manquant.");
                }

                // Vérifier que l'ID est valide
                if (!$idEvenement || $idEvenement <= 0) {
                    throw new \Exception("Identifiant d'événement invalide.");
                }

                // Récupérer l'ID de l'utilisateur connecté
                $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];

                // Vérifier si l'utilisateur est déjà inscrit
                if ($this->reservationModele->estDejaInscrit($idUtilisateur, $idEvenement)) {
                    $this->ajouterMessageErreur("Vous êtes déjà inscrit à cet événement.");
                } else {
                    // Ajouter la réservation
                    $success = $this->reservationModele->ajouterReservation($idEvenement, $idUtilisateur);
                    
                    if ($success) {
                        // Créer une notification pour le créateur de l'événement
                        $this->notificationModele->creerNotificationInscription($idUtilisateur, $idEvenement);
                        $this->ajouterMessageReussite("Inscription réussie !");
                    } else {
                        $this->ajouterMessageErreur("Erreur lors de l'inscription.");
                    }
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
                        $this->ajouterMessageErreur("Impossible d'identifier l'événement à supprimer.");
                        Reponses::rediriger('profil');
                        return;
                    }
                }
    
                // Vérifier que l'ID est valide
                if ($idEvenement <= 0) {
                    throw new \Exception("Identifiant d'événement invalide.");
                }
    
                $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];
                
                // Vérifier si l'utilisateur est bien inscrit à cet événement
                if (!$this->reservationModele->estDejaInscrit($idUtilisateur, $idEvenement)) {
                    $this->ajouterMessageErreur("Vous n'êtes pas inscrit à cet événement.");
                    Reponses::rediriger('profil');
                    return;
                }
    
                // Récupérer les informations de l'événement pour la notification
                $evenement = $this->EvenementModele->obtenirEvenementParId($idEvenement);
                
                // Procéder à la suppression
                $resultat = $this->reservationModele->supprimerReservation($idUtilisateur, $idEvenement);
    
                if ($resultat) {
                    // Créer une notification pour le créateur de l'événement
                    if (isset($this->notificationModele) && $evenement) {
                        $this->notificationModele->creerNotificationAnnulation(
                            $idUtilisateur,
                            $idEvenement
                        );
                    }
                    
                    $this->ajouterMessageReussite("Votre réservation a été annulée avec succès.");
                } else {
                    $this->ajouterMessageErreur("Impossible d'annuler votre réservation.");
                }
                
                Reponses::rediriger('profil');
                
            } catch (\Exception $e) {
                $this->ajouterMessageErreur("Erreur lors de l'annulation : " . $e->getMessage());
                Reponses::rediriger('profil');
            }
        } else {
            // Si la méthode n'est pas POST, rediriger vers la page de profil
            Reponses::rediriger('profil');
        }
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

        // Récupérer statut PMR et sexe de l'utilisateur avec valeurs par défaut sécurisées
        $pmr = isset($_SESSION['utilisateur']['pmr']) ? $_SESSION['utilisateur']['pmr'] : 'non';
        $sexe = isset($_SESSION['utilisateur']['sexe']) ? $_SESSION['utilisateur']['sexe'] : 'A';

        // Appeler le modèle avec des paramètres validés
        $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date, $pmr, $sexe);

        // Réponse JSON sécurisée
        header('Content-Type: application/json');
        echo json_encode($reservations);
        exit;
    }
}