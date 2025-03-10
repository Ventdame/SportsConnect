<?php
$host = 'localhost'; // Adresse du serveur
$dbname = 'sportsconnect'; // Nom de la base de données
$username = 'root'; // Utilisateur MySQL
$password = 'Anthony1430'; // Mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
