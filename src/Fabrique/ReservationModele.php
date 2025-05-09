<?php

namespace App\Fabrique;

use App\Core\FabriqueBase;

/**
 * Classe ReservationModele
 * 
 * Gère les opérations liées aux réservations dans la base de données
 */
class ReservationModele extends FabriqueBase
{
    /**
     * Constructeur du modèle ReservationModele
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'participants_evenement', 'id_participant');
    }

    /**
     * Recherche des réservations en fonction des critères donnés.
     *
     * @param string|null $ville
     * @param string|null $sport
     * @param string|null $date
     * @param string|int $pmrValue Valeur pour filtrer par statut PMR
     * @return array
     */
    public function rechercherReservations($ville = null, $sport = null, $date = null, $pmrValue = 0, $sexe = 'H')
    {
        $sql = "SELECT 
                e.id_evenement,
                e.nom_evenement AS evenement,
                DATE_FORMAT(e.date_evenement, '%d.%m.%Y') AS date,
                e.description,
                CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation,
                CASE WHEN s.pmr = 1 THEN 'Oui' ELSE 'Non' END AS pmr_accessible,
                s.genre AS genre_sport,
                e.montant AS prix
            FROM evenements e
            JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
            JOIN sports s ON e.id_sport = s.id_sport
            WHERE 1=1";
        
        // Initialiser le tableau de paramètres
        $params = [];
        
        // Ajouter le filtre de genre seulement si un sexe est spécifié
        if ($sexe && $sexe !== 'A') {
            $sql .= " AND (s.genre = :sexe OR s.genre = 'Mixte')";
            $params[':sexe'] = $sexe;
        }
    
        // Filtrer par ville si spécifié
        if (!empty($ville)) {
            $sql .= " AND LOWER(le.ville) LIKE :ville";
            $params[':ville'] = '%' . strtolower($ville) . '%';
        }
    
        // Filtrer par sport si spécifié
        if (!empty($sport)) {
            $sql .= " AND LOWER(s.nom_sport) = :sport";
            $params[':sport'] = strtolower($sport);
        }
    
        // Filtrer par date si spécifiée
        if (!empty($date)) {
            $sql .= " AND DATE(e.date_evenement) = :date";
            $params[':date'] = $date;
        }
    
        // Ajuster pmrValue si nécessaire
        if ($pmrValue === 'oui') {
            $pmrValue = 1;
        } elseif ($pmrValue === 'non') {
            $pmrValue = 0;
        }
    
        // Filtrer par PMR seulement si c'est spécifié
        $sql .= " AND s.pmr = :pmrValue";
        $params[':pmrValue'] = $pmrValue;
        
        $sql .= " ORDER BY e.date_evenement ASC";
    
        return $this->requetePersonnalisee($sql, $params);
    }

    /**
     * Ajoute une réservation pour un utilisateur à un événement.
     *
     * @param int $idEvenement
     * @param int $idUtilisateur
     * @return bool Succès ou échec de l'opération
     */
    public function ajouterReservation($idEvenement, $idUtilisateur)
    {
        try {
            return (bool) $this->creer([
                'id_evenement' => $idEvenement,
                'id_utilisateur' => $idUtilisateur
            ]);
        } catch (\Exception $e) {
            error_log("Erreur lors de l'ajout de la réservation : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur est déjà inscrit à un événement.
     *
     * @param int $idUtilisateur
     * @param int $idEvenement
     * @return bool
     */
    public function estDejaInscrit($idUtilisateur, $idEvenement)
    {
        return $this->compter([
            'id_utilisateur' => $idUtilisateur,
            'id_evenement' => $idEvenement
        ]) > 0;
    }

    /**
     * Récupère tous les événements réservés par un utilisateur
     * 
     * @param int $idUtilisateur Identifiant de l'utilisateur
     * @return array Liste des événements réservés
     */
    public function getEvenementsParUtilisateur($idUtilisateur)
    {
        $sql = "SELECT DISTINCT
                    e.id_evenement AS id_evenement, 
                    s.nom_sport AS sport,
                    e.nom_evenement AS nom_evenement,
                    DATE_FORMAT(e.date_evenement, '%d.%m.%Y') AS date_evenement,
                    e.description AS description,
                    CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation
                FROM {$this->table} p
                JOIN evenements e ON p.id_evenement = e.id_evenement
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                JOIN sports s ON e.id_sport = s.id_sport
                WHERE p.id_utilisateur = :id_utilisateur
                ORDER BY e.date_evenement ASC";

        return $this->requetePersonnalisee($sql, [':id_utilisateur' => $idUtilisateur]);
    }

    /**
     * Supprime une réservation
     * 
     * @param int $idUtilisateur Identifiant de l'utilisateur
     * @param int $idEvenement Identifiant de l'événement
     * @return bool Succès ou échec de l'opération
     */
    public function supprimerReservation($idUtilisateur, $idEvenement)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE id_utilisateur = :id_utilisateur AND id_evenement = :id_evenement";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':id_evenement' => $idEvenement
            ]);

            return true;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la suppression de la réservation : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourne le nombre de participants pour un événement donné.
     *
     * @param int $idEvenement L'identifiant de l'événement.
     * @return mixed Le nombre de participants ou un message par défaut s'il n'y en a aucun.
     */
    public function obtenirParticipants($idEvenement)
    {
        $total = $this->compter(['id_evenement' => $idEvenement]);

        if ($total > 0) {
            return $total;
        } else {
            return "Aucun participant à cet événement";
        }
    }
}
