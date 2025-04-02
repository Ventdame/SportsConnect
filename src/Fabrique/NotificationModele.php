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
     * Cache pour les informations sur les utilisateurs et événements
     */
    private $cacheUtilisateurs = [];
    private $cacheEvenements = [];

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
     * Obtient les informations d'un utilisateur avec mise en cache
     * 
     * @param int $idUtilisateur ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    private function getUtilisateur($idUtilisateur)
    {
        // Vérifier si l'utilisateur est déjà en cache
        if (isset($this->cacheUtilisateurs[$idUtilisateur])) {
            return $this->cacheUtilisateurs[$idUtilisateur];
        }
        
        // Sinon, le récupérer depuis la base de données
        $sql = "SELECT id_utilisateur, pseudo FROM utilisateurs WHERE id_utilisateur = :id_utilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_utilisateur' => $idUtilisateur]);
        $utilisateur = $stmt->fetch();
        
        // Mettre en cache si trouvé
        if ($utilisateur) {
            $this->cacheUtilisateurs[$idUtilisateur] = $utilisateur;
        }
        
        return $utilisateur;
    }
    
    /**
     * Obtient les informations d'un événement avec mise en cache
     * 
     * @param int $idEvenement ID de l'événement
     * @return array|false Données de l'événement ou false si non trouvé
     */
    private function getEvenement($idEvenement)
    {
        // Vérifier si l'événement est déjà en cache
        if (isset($this->cacheEvenements[$idEvenement])) {
            return $this->cacheEvenements[$idEvenement];
        }
        
        // Sinon, le récupérer depuis la base de données
        $sql = "SELECT id_evenement, id_utilisateur, nom_evenement FROM evenements WHERE id_evenement = :id_evenement";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_evenement' => $idEvenement]);
        $evenement = $stmt->fetch();
        
        // Mettre en cache si trouvé
        if ($evenement) {
            $this->cacheEvenements[$idEvenement] = $evenement;
        }
        
        return $evenement;
    }
    
    /**
     * Crée une notification généralisée
     * 
     * @param int $idDestinataire ID du destinataire
     * @param int $idSource ID de la source
     * @param int $idEvenement ID de l'événement
     * @param string $contenu Contenu de la notification
     * @return int|false ID de la notification créée ou false en cas d'échec
     */
    private function creerNotificationBase($idDestinataire, $idSource, $idEvenement, $contenu)
    {
        return $this->creer([
            'id_utilisateur_destinataire' => $idDestinataire,
            'id_utilisateur_source' => $idSource,
            'id_evenement' => $idEvenement,
            'contenu' => $contenu
        ]);
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
            // Récupérer les informations de l'événement
            $evenement = $this->getEvenement($idEvenement);
            if (!$evenement) {
                return false;
            }
            
            // Récupérer les informations de l'utilisateur source
            $utilisateur = $this->getUtilisateur($idUtilisateurSource);
            if (!$utilisateur) {
                return false;
            }
            
            // Créer le contenu de la notification
            $contenu = "{$utilisateur['pseudo']} s'est inscrit à votre événement : {$evenement['nom_evenement']}";
            
            // Créer la notification
            return (bool) $this->creerNotificationBase(
                $evenement['id_utilisateur'],
                $idUtilisateurSource,
                $idEvenement,
                $contenu
            );
        } catch (\Exception $e) {
            error_log("Erreur lors de la création de la notification : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée une notification pour la création d'un événement
     *
     * @param int $idUtilisateurSource ID du créateur de l'événement
     * @param int $idEvenement ID de l'événement
     * @param string $nomEvenement Nom de l'événement
     * @return bool Succès ou échec de l'opération
     */
    public function creerNotificationCreation($idUtilisateurSource, $idEvenement, $nomEvenement)
    {
        try {
            $contenu = "Nouvel événement créé: $nomEvenement";
            return (bool) $this->creerNotificationBase(
                $idUtilisateurSource,
                $idUtilisateurSource,
                $idEvenement,
                $contenu
            );
        } catch (\Exception $e) {
            error_log("Erreur création notification événement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée des notifications pour tous les participants lors de la modification d'un événement
     *
     * @param int $idUtilisateurSource ID du modifieur de l'événement
     * @param int $idEvenement ID de l'événement
     * @param string $nomEvenement Nom de l'événement
     * @return bool Succès ou échec de l'opération
     */
    public function creerNotificationsModification($idUtilisateurSource, $idEvenement, $nomEvenement)
    {
        try {
            $participants = $this->obtenirParticipantsEvenement($idEvenement);
            $contenu = "L'événement $nomEvenement a été modifié";
            $success = true;
            
            foreach ($participants as $participant) {
                $result = $this->creerNotificationBase(
                    $participant['id_utilisateur'],
                    $idUtilisateurSource,
                    $idEvenement,
                    $contenu
                );
                
                if (!$result) {
                    $success = false;
                }
            }
            
            return $success;
        } catch (\Exception $e) {
            error_log("Erreur notifications modification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère tous les participants d'un événement
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
            // Récupérer les informations de l'utilisateur source
            $utilisateur = $this->getUtilisateur($idUtilisateurSource);
            if (!$utilisateur) {
                return false;
            }
            
            // Récupérer tous les participants à l'événement sauf le créateur
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
            $contenu = "{$utilisateur['pseudo']} a supprimé l'événement : $nomEvenement auquel vous étiez inscrit";
            
            // Créer une notification pour chaque participant
            $success = true;
            foreach ($participants as $participant) {
                $result = $this->creerNotificationBase(
                    $participant['id_utilisateur'],
                    $idUtilisateurSource,
                    $idEvenement,
                    $contenu
                );
                
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
            // Récupérer les informations de l'événement
            $evenement = $this->getEvenement($idEvenement);
            if (!$evenement) {
                return false;
            }
            
            // Récupérer les informations de l'utilisateur source
            $utilisateur = $this->getUtilisateur($idUtilisateurSource);
            if (!$utilisateur) {
                return false;
            }
            
            // Créer le contenu de la notification
            $contenu = "{$utilisateur['pseudo']} a annulé sa participation à votre événement : {$evenement['nom_evenement']}";
            
            // Créer la notification
            return (bool) $this->creerNotificationBase(
                $evenement['id_utilisateur'],
                $idUtilisateurSource,
                $idEvenement,
                $contenu
            );
        } catch (\Exception $e) {
            error_log("Erreur lors de la création de la notification d'annulation : " . $e->getMessage());
            return false;
        }
    }
}