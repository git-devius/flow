<?php
/**
 * Configuration de l'application flow
 * 
 * Ce fichier charge les variables d'environnement depuis .env
 * et fournit une classe utilitaire pour y accéder.
 */

namespace App;

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Charger les variables d'environnement depuis .env si le fichier existe
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

/**
 * Classe de configuration
 * Permet d'accéder aux variables d'environnement avec des valeurs par défaut
 */
class Config {
    
    /**
     * Récupère une variable de configuration
     * 
     * @param string $key Nom de la variable
     * @param mixed $default Valeur par défaut si la variable n'existe pas
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Vérifie si une variable de configuration existe
     * 
     * @param string $key Nom de la variable
     * @return bool
     */
    public static function has($key) {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }
    
    /**
     * Récupère toutes les variables de configuration
     * 
     * @return array
     */
    public static function all() {
        return array_merge($_ENV, getenv());
    }
    
    /**
     * Récupère une variable de configuration en tant que booléen
     * 
     * @param string $key Nom de la variable
     * @param bool $default Valeur par défaut
     * @return bool
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }
    
    /**
     * Récupère une variable de configuration en tant qu'entier
     * 
     * @param string $key Nom de la variable
     * @param int $default Valeur par défaut
     * @return int
     */
    public static function getInt($key, $default = 0) {
        return (int) self::get($key, $default);
    }
}
