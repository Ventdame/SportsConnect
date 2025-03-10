<?php

namespace App\Core;

/**
 * Classe FabriqueBase
 * 
 * Classe de base pour tous les modèles de l'application.
 * Fournit des méthodes génériques pour interagir avec la base de données.
 */
class FabriqueBase 
{
    /**
     * Instance de PDO pour la connexion à la base de données
     * 
     * @var \PDO
     */
    protected $pdo;
    
    /**
     * Nom de la table associée au modèle
     * 
     * @var string
     */
    protected $table;
    
    /**
     * Clé primaire de la table
     * 
     * @var string
     */
    protected $clePrimaire;

    /**
     * Constructeur du modèle de base
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     * @param string $table Nom de la table associée au modèle
     * @param string $clePrimaire Nom de la clé primaire de la table
     */
    public function __construct($pdo, $table, $clePrimaire = 'id') 
    {
        if (!$pdo instanceof \PDO) {
            throw new \Exception("L'objet PDO est invalide");
        }
        
        $this->pdo = $pdo;
        $this->table = $table;
        $this->clePrimaire = $clePrimaire;
    }
    
    /**
     * Récupère l'instance PDO
     * 
     * @return \PDO Instance PDO utilisée par la fabrique
     */
    public function getPdo()
    {
        return $this->pdo;
    }
    
    /**
     * Récupère tous les enregistrements de la table
     * 
     * @param string $ordre Ordre de tri
     * @param int $limite Nombre maximum d'enregistrements à récupérer
     * @param int $offset Décalage pour la pagination
     * @return array Liste des enregistrements
     */
    public function obtenirTous($ordre = null, $limite = null, $offset = null) 
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($ordre !== null) {
            $sql .= " ORDER BY {$ordre}";
        }
        
        if ($limite !== null) {
            $sql .= " LIMIT {$limite}";
            
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un enregistrement par sa clé primaire
     * 
     * @param mixed $id Valeur de la clé primaire
     * @return array|false Données de l'enregistrement ou false s'il n'existe pas
     */
    public function obtenirParId($id) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->clePrimaire} = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère des enregistrements selon des critères spécifiques
     * 
     * @param array $criteres Critères de recherche (exemple : ['nom' => 'Dupont', 'age' => 25])
     * @param string $ordre Ordre de tri
     * @return array Liste des enregistrements correspondants
     */
    public function obtenirParCriteres($criteres, $ordre = null) 
    {
        $conditions = [];
        $params = [];
        
        foreach ($criteres as $colonne => $valeur) {
            if ($valeur === null) {
                $conditions[] = "{$colonne} IS NULL";
            } else {
                $paramName = ":{$colonne}";
                $conditions[] = "{$colonne} = {$paramName}";
                $params[$paramName] = $valeur;
            }
        }
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if ($ordre !== null) {
            $sql .= " ORDER BY {$ordre}";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un nouvel enregistrement
     * 
     * @param array $donnees Données de l'enregistrement à créer
     * @return int|false ID de l'enregistrement créé ou false en cas d'échec
     */
    public function creer($donnees) 
    {
        $colonnes = array_keys($donnees);
        $params = array_map(function($colonne) {
            return ":{$colonne}";
        }, $colonnes);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $colonnes) . ") 
                VALUES (" . implode(', ', $params) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($donnees as $colonne => $valeur) {
            $stmt->bindValue(":{$colonne}", $valeur);
        }
        
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Met à jour un enregistrement existant
     * 
     * @param mixed $id Valeur de la clé primaire
     * @param array $donnees Données à mettre à jour
     * @return bool Succès ou échec de la mise à jour
     */
    public function mettreAJour($id, $donnees) 
    {
        $updates = [];
        
        foreach ($donnees as $colonne => $valeur) {
            $updates[] = "{$colonne} = :{$colonne}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " 
                WHERE {$this->clePrimaire} = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        foreach ($donnees as $colonne => $valeur) {
            $stmt->bindValue(":{$colonne}", $valeur);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Supprime un enregistrement
     * 
     * @param mixed $id Valeur de la clé primaire
     * @return bool Succès ou échec de la suppression
     */
    public function supprimer($id) 
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->clePrimaire} = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Exécute une requête SQL personnalisée
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @param bool $fetchAll Si true, récupère tous les résultats, sinon seulement le premier
     * @return mixed Résultat de la requête
     */
    public function requetePersonnalisee($sql, $params = [], $fetchAll = true) 
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($fetchAll) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * Compte le nombre d'enregistrements selon des critères
     * 
     * @param array $criteres Critères de recherche
     * @return int Nombre d'enregistrements
     */
    public function compter($criteres = []) 
    {
        $conditions = [];
        $params = [];
        
        foreach ($criteres as $colonne => $valeur) {
            if ($valeur === null) {
                $conditions[] = "{$colonne} IS NULL";
            } else {
                $paramName = ":{$colonne}";
                $conditions[] = "{$colonne} = {$paramName}";
                $params[$paramName] = $valeur;
            }
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
}