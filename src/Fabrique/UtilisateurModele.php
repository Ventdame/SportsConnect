<?php

namespace App\Fabrique;

use App\Core\FabriqueBase;

/**
 * Classe UtilisateurModele
 * 
 * Gère les opérations liées aux utilisateurs dans la base de données
 */
class UtilisateurModele extends FabriqueBase
{
    /**
     * Constructeur du modèle UtilisateurModele
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'utilisateurs', 'id_utilisateur');
    }

    /**
     * Crée un nouvel utilisateur dans la base de données.
     *
     * @param string $pseudo
     * @param string $prenom
     * @param string $email
     * @param string $motDePasse
     * @param int $pmr (1 pour PMR, 0 sinon)
     * @return int ID de l'utilisateur créé.
     * @throws \Exception
     */
    public function creerUtilisateur($pseudo, $prenom, $email, $motDePasse, $pmr, $sexe ='A')
    {
        try {
            $donnees = [
                'pseudo' => $pseudo,
                'prenom' => $prenom,
                'email' => $email,
                'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
                'pmr' => $pmr,
                'sexe' => $sexe,
            ];
            
            return $this->creer($donnees);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un email existe déjà dans la base de données.
     *
     * @param string $email
     * @return bool
     */
    public function emailExistant($email)
    {
        return $this->compter(['email' => $email]) > 0;
    }

    /**
     * Récupère un utilisateur par email.
     *
     * @param string $email
     * @return array|false
     */
    public function RecupUtilisateurParEmail($email)
    {
        $sql = "SELECT id_utilisateur, pseudo, prenom, email, mot_de_passe, pmr, sexe, role 
                 FROM {$this->table} WHERE email = :email";
        
        // Debug - afficher la requête
        echo "Requête SQL: " . $sql . " avec email = " . $email;
        
        return $this->requetePersonnalisee($sql, [':email' => $email], false);
    }

    /**
 * Récupère un utilisateur à partir de son pseudo.
 *
 * @param string $pseudo Le pseudo de l'utilisateur.
 * @return array|null Tableau associatif contenant les informations de l'utilisateur (ou null si aucun).
 */
public function RecupUtilisateurParPseudo($pseudo)
{
    return $this->requetePersonnalisee(
        "SELECT id_utilisateur, pseudo, prenom, email, mot_de_passe, pmr, sexe, role
         FROM {$this->table}
         WHERE pseudo = :pseudo",
        [':pseudo' => $pseudo],
        false
    );
}


    /**
     * Vérifie les informations de connexion d'un utilisateur.
     *
     * @param string $email
     * @param string $motDePasse
     * @return array|null
     */
    public function verifierConnexion($identifiant, $motDePasse)
    {
        try {
            // Si c'est un email (vérification par filter_var), on cherche par email
            if (filter_var($identifiant, FILTER_VALIDATE_EMAIL)) {
                $utilisateur = $this->RecupUtilisateurParEmail($identifiant);
            } else {
                // Sinon, on considère que c'est un pseudo
                $utilisateur = $this->RecupUtilisateurParPseudo($identifiant);
            }
    
            // Vérifier mot de passe
            if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                return $utilisateur;
            }
    
            return null;
        } catch (\Exception $e) {
            error_log("Erreur lors de la vérification des informations de connexion : " . $e->getMessage());
            return null;
        }
    }
    
    

    /**
     * Vérifie si un pseudo existe déjà dans la base de données.
     *
     * @param string $pseudo
     * @return bool
     */
    public function pseudoExistant($pseudo)
    {
        return $this->compter(['pseudo' => $pseudo]) > 0;
    }
    
    /**
     * Met à jour les informations d'un utilisateur
     * 
     * @param int $idUtilisateur Identifiant de l'utilisateur
     * @param array $donnees Données à mettre à jour
     * @return bool Succès ou échec de l'opération
     */
    public function mettreAJourUtilisateur($idUtilisateur, $donnees)
    {
        // Si le mot de passe est dans les données, le hacher
        if (isset($donnees['mot_de_passe'])) {
            $donnees['mot_de_passe'] = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);
        }
        
        return $this->mettreAJour($idUtilisateur, $donnees);
    }
}