<?php

namespace App\Modeles;

class EvenementModele
{
    private $pdo;

    /**
     * Constructeur du modèle
     * Initialise la connexion à la base de données via PDO.
     *
     * @param \PDO $pdo
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouvel événement
     *
     * @param string $nomEvenement
     * @param string $dateEvenement
     * @param string|null $description
     * @param int $idLocalisation
     * @param int $idSport
     * @param int $pmrAccessible
     * @param float $montant
     * @param int $idUtilisateur
     * @return int L'ID de l'événement créé
     * @throws \Exception
     */
    public function creerEvenement($nomEvenement, $dateEvenement, $description, $idLocalisation, $idSport, $pmrAccessible, $montant, $idUtilisateur)
    {
        $sql = "INSERT INTO evenements (nom_evenement, date_evenement, description, id_localisation, id_sport, pmr_accessible, montant, id_utilisateur)
                VALUES (:nom_evenement, :date_evenement, :description, :id_localisation, :id_sport, :pmr_accessible, :montant, :id_utilisateur)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom_evenement' => $nomEvenement,
                ':date_evenement' => $dateEvenement,
                ':description' => $description ?: null,
                ':id_localisation' => $idLocalisation,
                ':id_sport' => $idSport,
                ':pmr_accessible' => $pmrAccessible,
                ':montant' => $montant,
                ':id_utilisateur' => $idUtilisateur,
            ]);

            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la création de l'événement.");
        }
    }

    /**
     * Méthode permettant de supprimer un Evenement creer par un utilisateur
     * @param mixed $idEvenement
     * @param mixed $idUtilisateur
     * @return bool
     */
    public function supprimerEvenementCreerParUtilisateur($idEvenement, $idUtilisateur)
    {
        $sql = "DELETE FROM evenements WHERE id_evenement = :id_evenement AND id_utilisateur = :id_utilisateur";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_evenement' => $idEvenement,
                ':id_utilisateur' => $idUtilisateur,
            ]);

            return $stmt->rowCount() > 0; // Retourne true si une ligne a été supprimée
        } catch (\PDOException $e) {
            error_log("Erreur lors de la suppression de l'événement : " . $e->getMessage());
            return false;
        }
    }



    /**
     * Crée une nouvelle localisation
     *
     * @param string $nomLocalisation
     * @param string $ville
     * @param string|null $adresse
     * @param string|null $codePostal
     * @return int L'ID de la localisation créée
     * @throws \Exception
     */
    public function creerLocalisation($nomLocalisation, $ville, $adresse = null, $codePostal = null)
    {
        $sql = "INSERT INTO localisations_evenements (nom_localisation_evenement, ville, adresse, code_postal)
                VALUES (:nom_localisation_evenement, :ville, :adresse, :code_postal)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom_localisation_evenement' => $nomLocalisation,
                ':ville' => $ville,
                ':adresse' => $adresse ?: null,
                ':code_postal' => $codePostal ?: null,
            ]);

            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Erreur lors de la création de la localisation : " . $e->getMessage());
            throw new \Exception("Erreur lors de la création de la localisation.");
        }
    }

    /**
     * Récupère toutes les localisations disponibles
     *
     * @return array
     */
    public function obtenirLocalisations()
    {
        $sql = "SELECT id_localisation, nom_localisation_evenement, ville, adresse, code_postal 
                FROM localisations_evenements
                ORDER BY nom_localisation_evenement ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des localisations : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les événements créés par un utilisateur
     *
     * @param int $idUtilisateur
     * @return array
     */
    public function recupererEvenementsParUtilisateur($idUtilisateur)
    {
        $sql = "SELECT e.*, s.nom_sport AS sport, CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation
                FROM evenements e
                JOIN sports s ON e.id_sport = s.id_sport
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                WHERE e.id_utilisateur = :id_utilisateur";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_utilisateur' => $idUtilisateur]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des événements : " . $e->getMessage());
            return [];
        }
    }


}
