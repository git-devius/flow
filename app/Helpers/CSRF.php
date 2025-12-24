<?php
namespace App\Helpers;

class CSRF {
  
  /**
   * Génère et stocke un jeton CSRF s'il n'existe pas.
   */
  public static function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }
  
  /**
   * Valide un jeton CSRF.
   * @param string $token Le jeton reçu du formulaire.
   * @return bool
   */
  public static function validateToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
      return false;
    }
    // On ne supprime PAS le jeton ici pour qu'il reste valide pour la session.
    return true;
  }
  
  /**
   * Valide le jeton CSRF reçu par POST et lance une exception si la validation échoue.
   * Cette méthode est ajoutée pour supporter la signature utilisée dans les contrôleurs.
   * @throws \Exception
   */
  public static function validateCsrfToken() {
    if (!isset($_POST['csrf_token'])) {
        throw new \Exception('Jeton CSRF manquant dans le formulaire.');
    }
    
    if (!self::validateToken($_POST['csrf_token'])) {
        throw new \Exception('Jeton CSRF invalide.');
    }
  }

  /**
   * Génère un champ de formulaire caché avec le jeton.
   * @return string
   */
  public static function getField() {
    $token = self::generateToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
  }
}