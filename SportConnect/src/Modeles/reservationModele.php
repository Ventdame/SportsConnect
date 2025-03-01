<?php

namespace App\Modeles;

class ReservationModele
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Recherche des réservations en fonction des critères donnés.
     *
     * @param string|null $ville
     * @param string|null $sport
     * @param string|null $date
     * @return array
     * @throws \Exception
     */
    public function rechercherReservations($ville = null, $sport = null, $date = null)
    {
        $sql = "SELECT 
                    e.id_evenement, -- Ajout de l'identifiant de l'événement
                    e.nom_evenement AS evenement,
                    DATE_FORMAT(e.date_evenement, '%d.%m.%Y') AS date,
                    e.description AS description,
                    CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation,
                    CASE 
                        WHEN e.pmr_accessible = 1 THEN 'Oui' 
                        ELSE 'Non' 
                    END AS pmr_accessible,
                    e.MONTANT AS prix
                FROM evenements e
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                JOIN sports s ON e.id_sport = s.id_sport
                WHERE 1=1";

        $params = [];

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

        // Filtrer par date si spécifié
        if (!empty($date)) {
            $sql .= " AND DATE(e.date_evenement) = :date";
            $params[':date'] = $date;
        }

        $sql .= " ORDER BY e.date_evenement ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute une réservation pour un utilisateur à un événement.
     *
     * @param int $idEvenement
     * @param int $idUtilisateur
     * @throws \Exception
     */
    public function ajouterReservation($idEvenement, $idUtilisateur)
    {
        try {
            $sql = "INSERT INTO participants_evenement (id_evenement, id_utilisateur) 
                    VALUES (:id_evenement, :id_utilisateur)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_evenement', $idEvenement, \PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $idUtilisateur, \PDO::PARAM_INT);

            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de l'ajout de la réservation : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un utilisateur est déjà inscrit à un événement.
     *
     * @param int $idEvenement
     * @param int $idUtilisateur
     * @return bool
     * @throws \Exception
     */
    public function estDejaInscrit($idUtilisateur, $idEvenement)
    {
        $sql = "SELECT COUNT(*) FROM participants_evenement 
            WHERE id_utilisateur = :id_utilisateur AND id_evenement = :id_evenement";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':id_evenement' => $idEvenement,
            ]);
            return $stmt->fetchColumn() > 0; // Retourne true si une réservation existe déjà
        } catch (\PDOException $e) {
            error_log("Erreur lors de la vérification de l'inscription : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Permet de retourner tout les evenements reserver par l'utilisateur en question
     * @param mixed $idUtilisateur
     * @return mixed
     */
    public function getEvenementsParUtilisateur($idUtilisateur)
    {
        $sql = "SELECT DISTINCT
                    e.id_evenement AS id_evenement, -- Ajoutez cette ligne
                    s.nom_sport AS sport,
                    e.nom_evenement AS nom_evenement,
                    DATE_FORMAT(e.date_evenement, '%d.%m.%Y') AS date_evenement,
                    e.description AS description,
                    CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation
                FROM participants_evenement p
                JOIN evenements e ON p.id_evenement = e.id_evenement
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                JOIN sports s ON e.id_sport = s.id_sport
                WHERE p.id_utilisateur = :id_utilisateur
                ORDER BY e.date_evenement ASC";
    
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_utilisateur' => $idUtilisateur]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des événements : " . $e->getMessage());
            return [];
        }
    }
    

    public function supprimerReservation($idUtilisateur, $idEvenement)
{
    $sql = "DELETE FROM participants_evenement
            WHERE id_utilisateur = :id_utilisateur AND id_evenement = :id_evenement";

    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_utilisateur' => $idUtilisateur,
            ':id_evenement' => $idEvenement,
        ]);
        return $stmt->rowCount() > 0; // Retourne true si une ligne a été supprimée
    } catch (\PDOException $e) {
        error_log("Erreur lors de la suppression de la réservation : " . $e->getMessage());
        return false;
    }
}

    
    
    
    
    
    
}
