<?php
declare(strict_types=1);

/**
 * Classe de connexion à la base de données
 * Singleton PDO pour MariaDB/MySQL
 */

namespace App;

use PDO;
use PDOException;
use Exception;

class Database {
    
    private static ?PDO $pdo = null;
    
    /**
     * Récupère l'instance PDO (singleton)
     * @return PDO
     * @throws Exception
     */
    public static function get(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        
        $host = Config::get('DB_HOST');
        $dbname = Config::get('DB_NAME');
        $user = Config::get('DB_USER');
        $pass = Config::get('DB_PASS');
        $port = Config::get('DB_PORT', '3306');
        
        if (!$host || !$dbname || !$user) {
            // Log interne pour le débug sans exposer l'info à l'utilisateur
            error_log("Database configuration error: Check .env file.");
            throw new Exception("Configuration base de données incomplète.");
        }

        // Ajout explicite du charset dans le DSN pour la sécurité et l'encodage
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Sécurité importante contre les injections SQL
                PDO::ATTR_STRINGIFY_FETCHES => false,
                // Désactiver les connexions persistantes pour éviter les états inattendus
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            self::$pdo = new PDO($dsn, $user, $pass, $options);
            
        } catch (PDOException $e) {
            // En prod, ne jamais afficher l'erreur brute SQL contenant le mot de passe
            // On loggue l'erreur technique
            error_log("Erreur critique SQL : " . $e->getMessage());
            // On affiche un message générique à l'utilisateur
            throw new Exception("Service temporairement indisponible (Erreur de connexion BDD).");
        }
        
        return self::$pdo;
    }
    
    /**
     * Ferme la connexion
     */
    public static function close(): void {
        self::$pdo = null;
    }
    
    /**
     * Vérifie si la connexion est active
     * @return bool
     */
    public static function isConnected(): bool {
        return self::$pdo !== null;
    }
}