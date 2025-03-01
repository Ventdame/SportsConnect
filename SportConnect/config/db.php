<?php
// Configuration des informations de connexion à la base de données
$host = 'localhost';       // Adresse du serveur de base de données
$dbname = 'sportsconnect'; // Nom de la base de données
$username = 'root';        // Nom d'utilisateur MySQL
$password = 'Anthony1430'; // Mot de passe de l'utilisateur MySQL

try {
    // Création d'une instance PDO pour se connecter à la base de données
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    // Configuration des attributs PDO pour la gestion des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Message de débogage (optionnel, peut être supprimé en production)
    // echo "Connexion à la base de données réussie.";
} catch (PDOException $e) {
    // En cas d'échec de connexion, arrêter le script et afficher un message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
