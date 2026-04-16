<?php
/**
 * CLI Migration Script for Flow
 * Usage: php bin/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger l'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'flowdb';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

echo "--- Migration Auto-Flow ---\n";
echo "Connexion à la base : $db sur $host...\n";

try {
    // Connexion sans base d'abord pour vérifier/créer
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db` text-dark");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    $migrationDir = __DIR__ . '/../migrations';
    $files = glob($migrationDir . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);
        echo "Exécution de $filename... ";
        
        $sql = file_get_contents($file);
        if (!$sql) {
            echo "ERREUR : Fichier vide\n";
            continue;
        }

        // Exécution multiple (Apache/PHP PDO ne supporte pas exec() multi-query nativement de façon fiable sans loop)
        // Mais pour de l'import SQL brut, on peut utiliser le buffer si MySQL le permet
        try {
            $pdo->exec($sql);
            echo "OK\n";
        } catch (Exception $e) {
            echo "ERREUR : " . $e->getMessage() . "\n";
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Félicitations ! Les migrations sont terminées.\n";

} catch (Exception $e) {
    echo "ERREUR FATALE : " . $e->getMessage() . "\n";
    exit(1);
}
