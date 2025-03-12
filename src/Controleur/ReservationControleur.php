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
                // Schéma de validation pour la réservation
                $schema = [
                    'secure_token' => ['type' => 'string', 'required' => true],
                    'csrf_token' => ['type' => 'string', 'required' => true],
                    'form_name' => ['type' => 'string', 'required' => true]
                ];
                
                // Valider et récupérer les données du formulaire
                $donnees = $this->obtenirDonneesFormulaireValidees($schema);
                
                if (!$donnees) {
                    // Erreurs de validation
                    $this->ajouterMessageErreur("Formulaire invalide ou session expirée.");
                    Reponses::rediriger('reservation');
                    return;
                }
                
                // Récupérer l'ID de l'événement à partir du token sécurisé
                $idEvenement = $this->recupererIDSecurise($donnees['secure_token'], 'evenement');
                
                if ($idEvenement === false) {
                    throw new \Exception("Session expirée ou requête invalide.");
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
                // Schéma de validation
                $schema = [
                    'secure_token' => ['type' => 'string', 'required' => true],
                    'csrf_token' => ['type' => 'string', 'required' => true],
                    'form_name' => ['type' => 'string', 'required' => true]
                ];
                
                // Valider et récupérer les données du formulaire
                $donnees = $this->obtenirDonneesFormulaireValidees($schema);
                
                if (!$donnees) {
                    $this->ajouterMessageErreur("Formulaire invalide ou session expirée.");
                    Reponses::rediriger('profil');
                    return;
                }
                
                // Récupérer l'ID de l'événement à partir du token sécurisé
                $idEvenement = $this->recupererIDSecurise($donnees['secure_token'], 'evenement');
                
                if ($idEvenement === false) {
                    throw new \Exception("Session expirée ou requête invalide.");
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
        $data = json_decode(file_get_contents("php://input"), true);
        $ville = $data['ville'] ?? null;
        $sport = $data['sport'] ?? null;
        $date = $data['date'] ?? null;
    
        // Récupérer statut PMR de l'utilisateur
        $pmr = $_SESSION['utilisateur']['pmr'] ?? 'non';
    
        // Appeler une méthode du modèle qui filtre déjà par PMR
        $reservations = $this->reservationModele->rechercherReservations($ville, $sport, $date, $pmr);
        
        // Sécuriser les IDs avant d'envoyer la réponse JSON
        foreach ($reservations as &$reservation) {
            $reservation['secure_token'] = $this->securiserID('evenement', $reservation['id_evenement']);
            // Ne pas exposer l'ID réel dans la réponse JSON
            unset($reservation['id_evenement']);
        }
    
        header('Content-Type: application/json');
        echo json_encode($reservations);
    }
}