<?php
// ----------------------------------------------------
// 1. Définition des paramètres de connexion (Local Laragon/MAMP)
// ----------------------------------------------------
// Ces informations sont utilisées pour établir la connexion.
$host     = 'localhost';        // Hôte de l'hébergement local
$dbname   = 'super_defi_photos'; // Nom de la BDD que vous avez créée dans phpMyAdmin
$username = 'root';             // Identifiant par défaut
$password = 'root';             // Mot de passe par défaut

// Data Source Name (DSN) : chaîne de connexion complète
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// ----------------------------------------------------
// 2. Création de l'objet PDO
// ----------------------------------------------------
try {
    // Crée l'objet $pdo, représentant la connexion à la base de données
    $pdo = new PDO($dsn, $username, $password);
    
    // Configuration pour que PDO lance des exceptions en cas d'erreur SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // En cas d'échec de la connexion, le script s'arrête et affiche l'erreur
    die("Échec de la connexion locale : " . $e->getMessage());
}

// L'objet $pdo est maintenant prêt à être utilisé par tous les autres fichiers PHP.
// Vos camarades pourront inclure ce fichier en utilisant require_once 'connect.php';
?>