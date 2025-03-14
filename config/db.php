<?php
// Variables stockées dans le htaccess
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'sportsconnect';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

// Options de PDO pour améliorer la sécurité et les performances
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // Journaliser l'erreur sans exposer de détails sensibles
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    
    // Message d'erreur générique pour l'utilisateur
    die("Une erreur est survenue lors de la connexion à la base de données. Veuillez contacter l'administrateur.");
}
?>