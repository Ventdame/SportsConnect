<?php

// Chargement de l'autoloader pour gérer le chargement automatique des classes

require_once __DIR__ . '/vendor/autoload.php';

// Chargement de la connexion PDO à la base de données
require_once __DIR__ . '/config/db.php'; // Charge la connexion PDO

use App\Core\Routeur;

/**
 * Vérification si la connexion PDO est correctement initialisée.
 * Si la connexion PDO n'est pas valide, l'exécution du script est arrêtée.
 */
if (!isset($pdo) || !$pdo instanceof \PDO) {
    die("Erreur : L'objet PDO n'a pas été correctement initialisé.");
}


try {
    /**
     * Initialisation du routeur avec la connexion PDO.
     * Le routeur est responsable du routage des requêtes vers les contrôleurs appropriés.
     */
    $routeur = new Routeur($pdo);
    $routeur->executer(); // Exécuter le routage

} catch (Exception $e) {
    /**
     * Gestion des exceptions globales.
     * Si une exception est levée, l'erreur est enregistrée et une réponse HTTP 500 est envoyée au client.
     */
    error_log("Erreur dans le routeur : " . $e->getMessage());
    http_response_code(500); // Réponse HTTP 500 en cas d'erreur critique
    echo "Une erreur interne est survenue. Veuillez réessayer plus tard."; // Message d'erreur générique pour l'utilisateur
}
?>
