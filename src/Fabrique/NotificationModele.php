<?php

namespace App\Fabrique;

use App\Core\FabriqueBase;

/**
 * Classe NotificationModele
 * 
 * Gère les opérations liées aux notifications dans la base de données
 */
class NotificationModele extends FabriqueBase
{
    /**
     * Constructeur du modèle NotificationModele
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'notifications', 'id_notification');
    }
    
    /**
     * Crée une notification pour l'utilisateur créateur d'un événement
     * lorsqu'un autre utilisateur s'inscrit à son événement
     *
     * @param int $idUtilisateurSource ID de l'utilisateur qui s'inscrit
     * @param int $idEvenement ID de l'événement
     * @return bool Succès ou échec de l'opération
     */
    public function creerNotificationInscription($idUtilisateurSource, $idEvenement)
    {
        try {
            // Récupérer l'ID du créateur de l'événement
            $sql = "SELECT id_utilisateur, nom_evenement FROM evenements WHERE id_evenement = :id_evenement";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_evenement' => $idEvenement]);
            $evenement = $stmt->fetch();
            
            if (!$evenement) {
                return false;
            }
            
            $idCreateur = $evenement['id_utilisateur'];
            $nomEvenement = $evenement['nom_evenement'];
            
            // Récupérer le pseudo de l'utilisateur source
            $sql = "SELECT pseudo FROM utilisateurs WHERE id_utilisateur = :id_utilisateur";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_utilisateur' => $idUtilisateurSource]);
            $utilisateur = $stmt->fetch();
            
            if (!$utilisateur) {
                return false;
            }
            
            $pseudoSource = $utilisateur['pseudo'];
            
            // Créer le contenu de la notification
            $contenu = "$pseudoSource s'est inscrit à votre événement : $nomEvenement";
            
            // Créer la notification
            return (bool) $this->creer([
                'id_utilisateur_destinataire' => $idCreateur,
                'id_utilisateur_source' => $idUtilisateurSource,
                'id_evenement' => $idEvenement,
                'contenu' => $contenu
            ]);
        } catch (\Exception $e) {
            error_log("Erreur lors de la création de la notification : " . $e->getMessage());
            return false;
        }
    }

    public function creerNotificationCreation($idUtilisateurSource, $idEvenement, $nomEvenement)
    {
        try {
            $contenu = "Nouvel événement créé: $nomEvenement";
            return $this->creer([
                'id_utilisateur_destinataire' => $idUtilisateurSource,
                'id_utilisateur_source' => $idUtilisateurSource,
                'id_evenement' => $idEvenement,
                'contenu' => $contenu
            ]);
        } catch (\Exception $e) {
            error_log("Erreur création notification événement: " . $e->getMessage());
            return false;
        }
    }

    public function creerNotificationsModification($idUtilisateurSource, $idEvenement, $nomEvenement)
    {
        try {
            $participants = $this->obtenirParticipantsEvenement($idEvenement);
            foreach ($participants as $participant) {
                $contenu = "L'événement $nomEvenement a été modifié";
                $this->creer([
                    'id_utilisateur_destinataire' => $participant['id_utilisateur'],
                    'id_utilisateur_source' => $idUtilisateurSource,
                    'id_evenement' => $idEvenement,
                    'contenu' => $contenu
                ]);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Erreur notifications modification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère tous les participants d'un événement sauf le créateur
     *
     * @param int $idEvenement ID de l'événement
     * @return array Liste des participants
     */
    public function obtenirParticipantsEvenement($idEvenement)
    {
        $sql = "SELECT id_utilisateur FROM participants_evenement WHERE id_evenement = :id_evenement";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_evenement' => $idEvenement]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les notifications non lues d'un utilisateur
     *
     * @param int $idUtilisateur ID de l'utilisateur
     * @return array Liste des notifications non lues
     */
    public function obtenirNotificationsNonLues($idUtilisateur)
    {
        $sql = "SELECT n.*, u.pseudo as pseudo_source, e.nom_evenement 
                FROM {$this->table} n
                LEFT JOIN utilisateurs u ON n.id_utilisateur_source = u.id_utilisateur
                LEFT JOIN evenements e ON n.id_evenement = e.id_evenement
                WHERE n.id_utilisateur_destinataire = :id_utilisateur AND n.lu = 0
                ORDER BY n.date_notification DESC";
        
        return $this->requetePersonnalisee($sql, [':id_utilisateur' => $idUtilisateur]);
    }
    
    /**
     * Marque une notification comme lue
     *
     * @param int $idNotification ID de la notification
     * @return bool Succès ou échec de l'opération
     */
    public function marquerCommeLue($idNotification)
    {
        return $this->mettreAJour($idNotification, ['lu' => 1]);
    }
    
    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     *
     * @param int $idUtilisateur ID de l'utilisateur
     * @return bool Succès ou échec de l'opération
     */
    public function marquerToutesCommeLues($idUtilisateur)
    {
        $sql = "UPDATE {$this->table} SET lu = 1 WHERE id_utilisateur_destinataire = :id_utilisateur";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_utilisateur' => $idUtilisateur]);
            return true;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la mise à jour des notifications : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crée des notifications pour tous les participants d'un événement
     * lorsque l'événement est supprimé par son créateur
     *
     * @param int $idUtilisateurSource ID de l'utilisateur qui supprime l'événement (créateur)
     * @param int $idEvenement ID de l'événement
     * @param string $nomEvenement Nom de l'événement
     * @return bool Succès ou échec de l'opération
     */
    public function creerNotificationsSuppressionEvenement($idUtilisateurSource, $idEvenement, $nomEvenement)
    {
        try {
            // Récupérer le pseudo de l'utilisateur source (créateur de l'événement)
            $sql = "SELECT pseudo FROM utilisateurs WHERE id_utilisateur = :id_utilisateur";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_utilisateur' => $idUtilisateurSource]);
            $utilisateur = $stmt->fetch();
            
            if (!$utilisateur) {
                return false;
            }
            
            $pseudoSource = $utilisateur['pseudo'];
            
            // Récupérer tous les participants à l'événement
            $sql = "SELECT id_utilisateur FROM participants_evenement WHERE id_evenement = :id_evenement AND id_utilisateur != :id_createur";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_evenement' => $idEvenement,
                ':id_createur' => $idUtilisateurSource
            ]);
            $participants = $stmt->fetchAll();
            
            if (empty($participants)) {
                return true; // Pas de participants à notifier
            }
            
            // Créer le contenu de la notification
            $contenu = "$pseudoSource a supprimé l'événement : $nomEvenement auquel vous étiez inscrit";
            
            // Créer une notification pour chaque participant
            $success = true;
            foreach ($participants as $participant) {
                $result = $this->creer([
                    'id_utilisateur_destinataire' => $participant['id_utilisateur'],
                    'id_utilisateur_source' => $idUtilisateurSource,
                    'id_evenement' => $idEvenement,
                    'contenu' => $contenu
                ]);
                
                if (!$result) {
                    $success = false;
                }
            }
            
            return $success;
        } catch (\Exception $e) {
            error_log("Erreur lors de la création des notifications de suppression : " . $e->getMessage());
            return false;
        }
    }

    /**
 * Crée une notification pour l'utilisateur créateur d'un événement
 * lorsqu'un utilisateur annule sa participation à un événement
 *
 * @param int $idUtilisateurSource ID de l'utilisateur qui annule sa participation
 * @param int $idEvenement ID de l'événement
 * @return bool Succès ou échec de l'opération
 */
public function creerNotificationAnnulation($idUtilisateurSource, $idEvenement)
{
    try {
        // Récupérer l'ID du créateur de l'événement
        $sql = "SELECT id_utilisateur, nom_evenement FROM evenements WHERE id_evenement = :id_evenement";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_evenement' => $idEvenement]);
        $evenement = $stmt->fetch();
        
        if (!$evenement) {
            return false;
        }
        
        $idCreateur = $evenement['id_utilisateur'];
        $nomEvenement = $evenement['nom_evenement'];
        
        // Récupérer le pseudo de l'utilisateur source
        $sql = "SELECT pseudo FROM utilisateurs WHERE id_utilisateur = :id_utilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_utilisateur' => $idUtilisateurSource]);
        $utilisateur = $stmt->fetch();
        
        if (!$utilisateur) {
            return false;
        }
        
        $pseudoSource = $utilisateur['pseudo'];
        
        // Créer le contenu de la notification
        $contenu = "$pseudoSource a annulé sa participation à votre événement : $nomEvenement";
        
        // Créer la notification
        return (bool) $this->creer([
            'id_utilisateur_destinataire' => $idCreateur,
            'id_utilisateur_source' => $idUtilisateurSource,
            'id_evenement' => $idEvenement,
            'contenu' => $contenu
        ]);
    } catch (\Exception $e) {
        error_log("Erreur lors de la création de la notification d'annulation : " . $e->getMessage());
        return false;
    }
}
}