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
        try {
            // Contrôleur et méthode par défaut
            $nomControleur = 'AccueilControleur';
            $nomMethode = 'index';

            // Récupérer le contrôleur depuis l'URL
            if (isset($_GET['page'])) {
                $pageRequise = strtolower($_GET['page']);
                // Vérification qu'aucun caractère indésirable n'est présent dans le nom de la page
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pageRequise)) {
                    throw new \Exception("Page introuvable");
                }
                $nomControleur = ucfirst($pageRequise) . 'Controleur';
            }

            // Récupérer l'action (méthode) depuis l'URL
            if (isset($_GET['action'])) {
                $actionRequise = $_GET['action'];
                // Vérification qu'aucun caractère indésirable n'est présent dans le nom de l'action
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $actionRequise)) {
                    throw new \Exception("Action non valide");
                }
                $nomMethode = $actionRequise;
                
                // Protection contre les appels récursifs ou méthodes réservées
                if ($nomMethode === 'traiter' || $nomMethode === '__construct' || $nomMethode === '__destruct' || 
                    substr($nomMethode, 0, 2) === '__') {
                    throw new \Exception("Action non autorisée");
                }
            }

            // Chemin générique pour les contrôleurs
            $cheminControleur = __DIR__ . '/../Controleur/' . $nomControleur . '.php';

            // Vérifier si le fichier du contrôleur existe
            if (!file_exists($cheminControleur)) {
                // Message d'erreur sans exposer le chemin du fichier
                throw new \Exception("Page introuvable");
            }

            // Inclure le fichier du contrôleur
            require_once $cheminControleur;
            $classeControleur = "App\\Controleur\\$nomControleur";

            // Vérifier si la classe existe
            if (!class_exists($classeControleur)) {
                throw new \Exception("Page introuvable");
            }

            // Instancier le contrôleur
            $controleur = new $classeControleur($this->pdo);

            // Vérifier si la méthode existe dans le contrôleur
            if (!method_exists($controleur, $nomMethode)) {
                throw new \Exception("Page introuvable");
            }

            // Vérifier que la méthode n'est pas privée ou protégée
            $reflection = new \ReflectionMethod($classeControleur, $nomMethode);
            if (!$reflection->isPublic()) {
                throw new \Exception("Accès refusé");
            }

            // Appeler la méthode demandée
            $controleur->$nomMethode();

        } catch (\Exception $e) {
            // Gérer les erreurs de manière propre, sans exposer de détails techniques
            $this->afficherPageErreur($e->getMessage());
        }
    }

    /**
     * Affiche une page d'erreur conviviale
     *
     * @param string $message Message d'erreur à afficher
     */
    private function afficherPageErreur($message)
    {
        // Définir le code HTTP approprié
        http_response_code(404);
        
        // Journaliser l'erreur (pour l'administrateur), mais ne pas l'afficher à l'utilisateur
        error_log($message);
        
        // Afficher une page d'erreur conviviale
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Page introuvable - SportConnect</title>
            <link rel="stylesheet" href="CSS/style.css">
        </head>
        <body>
            <div class="error-container" style="text-align: center; padding: 50px; max-width: 800px; margin: 0 auto;">
                <h1>Page introuvable</h1>
                <p>Désolé, la page que vous recherchez n\'existe pas ou n\'est pas accessible.</p>
                <p><a href="?page=accueil" class="btn">Retour à l\'accueil</a></p>
            </div>
        </body>
        </html>';
        exit;
    }
}