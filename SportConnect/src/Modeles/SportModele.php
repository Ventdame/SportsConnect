<?php

namespace App\Modeles;

class SportModele
{
    private $pdo;

    public function __construct($pdo)
    {
        if (!$pdo) {
            throw new \Exception("Erreur : La connexion à la base de données est invalide.");
        }
        $this->pdo = $pdo;
    }

    /**
     * Récupère la liste des sports disponibles.
     *
     * @return array Tableau contenant les noms des sports.
     * @throws \Exception
     */
    public function RecupSports()
    {
        $sql = "SELECT nom_sport FROM sports ORDER BY nom_sport ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            // Récupère uniquement la colonne `nom_sport` sous forme de tableau simple
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            return [];
        }
    }


    /**
     * Ajoute un nouveau sport dans la base de données.
     *
     * @param string $nomSport Nom du sport à ajouter.
     * @throws \Exception
     */
    public function ajouterSport($nomSport)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO sports (nom_sport) VALUES (:nom_sport)");
            $stmt->bindParam(':nom_sport', $nomSport, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de l'ajout du sport : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un sport existe déjà dans la base de données.
     *
     * @param string $nomSport Nom du sport à vérifier.
     * @return bool True si le sport existe, false sinon.
     * @throws \Exception
     */
    public function sportExiste($nomSport)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sports WHERE nom_sport = :nom_sport");
            $stmt->bindParam(':nom_sport', $nomSport, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification du sport : " . $e->getMessage());
        }
    }

    public function obtenirSports()
    {
        $sql = "SELECT id_sport, nom_sport FROM sports ORDER BY nom_sport ASC";
    
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC); // Retourne un tableau associatif
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des sports : " . $e->getMessage());
            return [];
        }
    }
    
}
