<?php

namespace App\Fabrique;

use App\Core\FabriqueBase;

/**
 * Classe SportModele
 * 
 * Gère les opérations liées aux sports dans la base de données
 */
class SportModele extends FabriqueBase
{
    /**
     * Constructeur du modèle SportModele
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'sports', 'id_sport');
    }

    /**
     * Récupère la liste des sports disponibles
     *
     * @return array Tableau contenant les noms des sports
     */
    public function RecupSports()
    {
        $sql = "SELECT nom_sport FROM {$this->table} ORDER BY nom_sport ASC";
        
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
     * @return bool Succès ou échec de l'opération
     */
    public function ajouterSport($nomSport)
    {
        try {
            return (bool) $this->creer(['nom_sport' => $nomSport]);
        } catch (\Exception $e) {
            error_log("Erreur lors de l'ajout du sport : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un sport existe déjà dans la base de données.
     *
     * @param string $nomSport Nom du sport à vérifier.
     * @return bool True si le sport existe, false sinon.
     */
    public function sportExiste($nomSport)
    {
        return $this->compter(['nom_sport' => $nomSport]) > 0;
    }

    /**
     * Obtient tous les sports avec leurs identifiants
     * 
     * @return array Liste des sports avec id_sport et nom_sport
     */
    public function obtenirSports()
    {
        return $this->obtenirTous('nom_sport ASC');
    }

    /**
     * Retourne la liste des sports en fonction du profil PMR de l'utilisateur.
     *
     * @param string $pmr 'oui' si l'utilisateur est PMR, 'non' sinon.
     * @return array La liste des sports correspondants.
     */
    public function obtenirSportsSelonUtilisateur($pmr)
    {
        $pmrValeur = ($pmr === 'oui') ? 1 : 0;
        return $this->obtenirParCriteres(['pmr' => $pmrValeur]);
    }
}