<?php
// index.php

// Démarrer la session au tout début
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir le gestionnaire d'erreurs personnalisé
set_error_handler('handleError');
set_exception_handler('handleException');

// Charger l'autoloader
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Routeur;
use App\Securite\SecurityMiddleware;

try {
    // Ajouter les en-têtes de sécurité
    SecurityMiddleware::ajouterEnTetes();
    
    // Vérifier la requête
    if (!SecurityMiddleware::verifierRequete()) {
        // Rediriger vers la page d'accueil avec un message d'erreur
        $_SESSION['messageErreur'] = "Requête invalide. Veuillez réessayer.";
        header("Location: ?page=accueil");
        exit;
    }
    
    // Chargement de la connexion PDO
    require_once __DIR__ . '/config/db.php';
    
    if (!isset($pdo) || !$pdo instanceof \PDO) {
        throw new Exception("Erreur de connexion à la base de données");
    }
    
    // Exécution du routeur
    $routeur = new Routeur($pdo);
    $routeur->executer();
    
} catch (Exception $e) {
    // Journaliser l'erreur
    error_log("Erreur application: " . $e->getMessage());
    
    // Afficher une page d'erreur conviviale
    displayErrorPage();
}

/**
 * Fonction de gestion des erreurs
 */
function handleError($errno, $errstr, $errfile, $errline) {
    // Enregistrer l'erreur dans les logs mais ne pas l'afficher
    error_log("Erreur PHP [$errno]: $errstr dans $errfile à la ligne $errline");
    
    // Afficher une page d'erreur conviviale pour les erreurs graves
    if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_CORE_ERROR || 
        $errno == E_COMPILE_ERROR || $errno == E_USER_ERROR) {
        displayErrorPage();
        exit;
    }
    
    // Ne pas exécuter le gestionnaire d'erreurs PHP interne
    return true;
}

/**
 * Fonction de gestion des exceptions
 */
function handleException($exception) {
    // Enregistrer l'exception dans les logs mais ne pas l'afficher
    error_log('Exception: ' . $exception->getMessage() . ' dans ' . $exception->getFile() . ' à la ligne ' . $exception->getLine());
    
    // Afficher une page d'erreur conviviale
    displayErrorPage();
}

/**
 * Affiche une page d'erreur conviviale
 */
function displayErrorPage() {
    // Définir le code HTTP approprié
    http_response_code(404);
    
    // Afficher une page d'erreur HTML
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
            <p><a href="?page=accueil" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;">Retour à l\'accueil</a></p>
        </div>
    </body>
    </html>';
    exit;
}