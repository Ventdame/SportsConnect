<?php
namespace App\Modeles;

class UtilisateurModele
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
    public function creerUtilisateur($pseudo, $prenom, $email, $motDePasse, $pmr)
    {
        try {
            $sql = "INSERT INTO utilisateurs (pseudo, prenom, email, mot_de_passe, pmr) 
                    VALUES (:pseudo, :prenom, :email, :mot_de_passe, :pmr)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':pseudo' => $pseudo,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT), 
                ':pmr' => $pmr,
            ]);

            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un email existe déjà dans la base de données.
     *
     * @param string $email
     * @return bool
     * @throws \Exception
     */
    public function emailExistant($email)
    {
        try 
        {
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification de l'email : " . $e->getMessage());
        }
    }

    /**
     * Récupère un utilisateur par email.
     *
     * @param string $email
     * @return array|false
     * @throws \Exception
     */
    public function RecupUtilisateurParEmail($email)
    {
        try {
            $sql = "SELECT id_utilisateur, pseudo, prenom, email, mot_de_passe, pmr 
                    FROM utilisateurs WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Vérifie les informations de connexion d'un utilisateur.
     *
     * @param string $email
     * @param string $motDePasse
     * @return array|null
     * @throws \Exception
     */
    public function verifierConnexion($email, $motDePasse)
    {
        try {
            $utilisateur = $this->RecupUtilisateurParEmail($email);

            if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                return $utilisateur;
            }

            return null;
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la vérification des informations de connexion : " . $e->getMessage());
        }
    }

    public function pseudoExistant($pseudo)
    {
        try 
        {
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE pseudo = :pseudo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pseudo' => $pseudo]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification du pseudo ! : " . $e->getMessage());
        }
    } 

    
}
