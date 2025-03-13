<?php

namespace App\Fabrique;

use App\Core\FabriqueBase;

/**
 * Classe EvenementModele
 * 
 * Gère les opérations liées aux événements dans la base de données
 */
class EvenementModele extends FabriqueBase
{
    /**
     * Constructeur du modèle
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'evenements', 'id_evenement');
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
        
        if ($idLocalisation) {
            try {
                
                $localisationFabrique = new FabriqueBase($this->pdo, 'localisations_evenements', 'id_localisation');
                
            // Mettre à jour le statut PMR de la localisation
                $localisationFabrique->mettreAJour($idLocalisation, ['PMR' => $pmrAccessible]);
            } catch (\Exception $e) {
                // Journaliser l'erreur mais continuer
                error_log("Erreur lors de la mise à jour du statut PMR de la localisation: " . $e->getMessage());
            }
        }
        
        
        $donnees = [
            'nom_evenement' => $nomEvenement,
            'date_evenement' => $dateEvenement,
            'description' => $description ?: null,
            'id_localisation' => $idLocalisation,
            'id_sport' => $idSport,
            'montant' => $montant,
            'id_utilisateur' => $idUtilisateur,
        ];
        
        $result = $this->creer($donnees);
        
        if (!$result) {
            throw new \Exception("Erreur lors de la création de l'événement.");
        }
        
        return $result;
    }

    /**
     * Supprime un événement créé par un utilisateur
     * 
     * @param int $idEvenement Identifiant de l'événement
     * @param int $idUtilisateur Identifiant de l'utilisateur
     * @return bool Succès ou échec de l'opération
     */
    public function supprimerEvenementCreerParUtilisateur($idEvenement, $idUtilisateur)
    {
        $sql = "DELETE FROM {$this->table} WHERE id_evenement = :id_evenement AND id_utilisateur = :id_utilisateur";

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
        // Créer une instance temporaire de FabriqueBase pour la table des localisations
        $localisationFabrique = new FabriqueBase($this->pdo, 'localisations_evenements', 'id_localisation');
        
        $donnees = [
            'nom_localisation_evenement' => $nomLocalisation,
            'ville' => $ville,
            'adresse' => $adresse ?: null,
            'code_postal' => $codePostal ?: null,
        ];
        
        $result = $localisationFabrique->creer($donnees);
        
        if (!$result) {
            throw new \Exception("Erreur lors de la création de la localisation.");
        }
        
        return $result;
    }

    /**
     * Récupère toutes les localisations disponibles
     *
     * @return array
     */
    public function obtenirLocalisations()
    {
        // Créer une instance temporaire de FabriqueBase pour la table des localisations
        $localisationFabrique = new FabriqueBase($this->pdo, 'localisations_evenements', 'id_localisation');
        
        return $localisationFabrique->obtenirTous('nom_localisation_evenement ASC');
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
                FROM {$this->table} e
                JOIN sports s ON e.id_sport = s.id_sport
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                WHERE e.id_utilisateur = :id_utilisateur";

        return $this->requetePersonnalisee($sql, [':id_utilisateur' => $idUtilisateur]);
    }

    /**
     * Méthode pour obtenir les détails d'un événement par son ID
     *
     * @param int $idEvenement ID de l'événement à récupérer
     * @return array|false Les détails de l'événement ou false s'il n'existe pas
     */
    public function obtenirEvenementParId($idEvenement)
    {
        $sql = "SELECT e.*, s.nom_sport AS sport, CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation,
                        u.pseudo AS organisateur
                FROM {$this->table} e
                JOIN sports s ON e.id_sport = s.id_sport
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                JOIN utilisateurs u ON e.id_utilisateur = u.id_utilisateur
                WHERE e.id_evenement = :id_evenement";

        return $this->requetePersonnalisee($sql, [':id_evenement' => $idEvenement], false);
    }
    
    /**
     * Obtient les événements à venir
     * 
     * @param int $limite Nombre maximum d'événements à récupérer
     * @return array Liste des événements à venir
     */
    public function obtenirEvenementsAVenir($limite = 5)
    {
        $sql = "SELECT e.*, s.nom_sport, CONCAT(le.nom_localisation_evenement, ' - ', le.ville) AS localisation
                FROM {$this->table} e
                JOIN sports s ON e.id_sport = s.id_sport
                JOIN localisations_evenements le ON e.id_localisation = le.id_localisation
                WHERE e.date_evenement >= CURDATE()
                ORDER BY e.date_evenement ASC
                LIMIT :limite";
                
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limite', $limite, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des événements à venir : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Met à jour un événement existant
     * 
     * @param int $idEvenement Identifiant de l'événement
     * @param array $donnees Données à mettre à jour
     * @return bool Succès ou échec de l'opération
     */
    public function mettreAJourEvenement($idEvenement, $donnees)
    {
        return $this->mettreAJour($idEvenement, $donnees);
    }
}