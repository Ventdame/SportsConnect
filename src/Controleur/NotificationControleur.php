<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\NotificationModele;

/**
 * Contrôleur pour la gestion des notifications
 */
class NotificationControleur extends ControleurBase
{
    /**
     * Instance du modèle NotificationModele
     * 
     * @var NotificationModele
     */
    private $notificationModele;

    /**
     * Constructeur du contrôleur NotificationControleur
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->notificationModele = new NotificationModele($pdo);
    }

    /**
     * Méthode par défaut qui redirige vers l'accueil
     */
    public function index()
    {
        Reponses::rediriger('accueil');
    }

    /**
     * Méthode pour obtenir les notifications non lues via AJAX
     */
    public function obtenirNotificationsAjax()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->estConnecte()) {
            echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
            return;
        }

        $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];
        
        try {
            // Récupérer les notifications non lues
            $notifications = $this->notificationModele->obtenirNotificationsNonLues($idUtilisateur);
            
            // Retourner les notifications au format JSON
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur lors de la récupération des notifications: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Méthode pour marquer une notification comme lue via AJAX
     */
    public function marquerCommeLueAjax()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->estConnecte()) {
            echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
            return;
        }
        
        try {
            // Récupérer les données JSON envoyées
            $jsonData = file_get_contents("php://input");
            $data = json_decode($jsonData, true);
            
            if (!$data || !isset($data['id_notification'])) {
                echo json_encode(['success' => false, 'error' => 'Données invalides']);
                return;
            }
            
            $idNotification = intval($data['id_notification']);
            
            if ($idNotification <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID de notification invalide']);
                return;
            }
            
            // Marquer la notification comme lue
            $success = $this->notificationModele->marquerCommeLue($idNotification);
            
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur lors du traitement: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Méthode pour marquer toutes les notifications d'un utilisateur comme lues via AJAX
     */
    public function marquerToutesCommeLuesAjax()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->estConnecte()) {
            echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
            return;
        }
        
        try {
            $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];
            
            // Marquer toutes les notifications comme lues
            $success = $this->notificationModele->marquerToutesCommeLues($idUtilisateur);
            
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur lors du traitement: ' . $e->getMessage()
            ]);
        }
    }
}