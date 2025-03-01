<?php
namespace App\Core;

/**
 * Classe Routeur gère la navigation vers les contrôleurs et leurs méthodes.
 */
class Routeur
{
    private $pdo;

    /**
     * Constructeur du routeur, initialisation avec la connexion PDO.
     *
     * @param \PDO $pdo Connexion à la base de données.
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Méthode principale qui exécute la logique de routage.
     *
     * Cette méthode détermine quel contrôleur et méthode exécuter en fonction des paramètres de l'URL.
     * Si aucune URL n'est fournie, un contrôleur et une méthode par défaut sont utilisés.
     */
    public function executer()
    {
        // Contrôleur et méthode par défaut
        $nomControleur = 'AccueilControleur';
        $nomMethode = 'index';

        // Récupérer le contrôleur depuis l'URL
        if (isset($_GET['page'])) {
            $nomControleur = ucfirst($_GET['page']) . 'Controleur';
        }

        // Récupérer l'action (méthode) depuis l'URL
        if (isset($_GET['action'])) {
            $nomMethode = $_GET['action'];
        }

        // Chemin générique pour les contrôleurs
        $cheminControleur = __DIR__ . '/../Controleur/' . $nomControleur . '.php';

        try {
            // Vérifier si le fichier du contrôleur existe
            if (!file_exists($cheminControleur)) {
                throw new \Exception("Erreur 404 : Le fichier du contrôleur '$cheminControleur' est introuvable.");
            }

            // Inclure le fichier du contrôleur
            require_once $cheminControleur;
            $classeControleur = "App\\Controleur\\$nomControleur";

            // Vérifier si la classe existe
            if (!class_exists($classeControleur)) {
                throw new \Exception("Erreur 404 : La classe '$classeControleur' n'existe pas.");
            }

            // Instancier le contrôleur
            $controleur = new $classeControleur($this->pdo);

            // Vérifier si la méthode existe dans le contrôleur
            if (!method_exists($controleur, $nomMethode)) {
                throw new \Exception("Erreur 404 : La méthode '$nomMethode' n'existe pas dans le contrôleur '$nomControleur'.");
            }

            // Appeler la méthode demandée
            $controleur->$nomMethode();

        } catch (\Exception $e) {
            // Gérer les erreurs de manière propre
            http_response_code(404);
            echo $e->getMessage();
        }
    }
}
