<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Audit;
use App\Config;
use App\Helpers\CSRF;
use League\OAuth2\Client\Provider\Google;
use Exception;

class AuthController {
  
  // ==================================================
  // LOGIQUE MÉTIER (STATIQUE)
  // ==================================================

  /**
   * Tente de connecter l'utilisateur avec protection brute-force basique
   */
  public static function attempt(string $email, string $password): bool { 
    // Protection basique contre le brute-force via session
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) {
        if (time() - $_SESSION['last_attempt_time'] < 60) {
            return false; // Bloqué pour 1 minute
        }
        // Reset après timeout
        $_SESSION['login_attempts'] = 0;
    }

    $u = User::findByEmail($email); 
    
    // Vérification générique pour ne pas dévoiler si l'email existe
    if (!$u || !isset($u['password']) || !password_verify($password, $u['password'])) {
        self::incrementAttempts($email);
        return false;
    }
    
    // Vérification si le compte est actif
    if (isset($u['is_active']) && (int)$u['is_active'] === 0) {
        return false; 
    }
    
    // Succès
    unset($_SESSION['login_attempts']);
    self::createSession($u, 'login');
    return true;
  }

  private static function incrementAttempts(string $email): void {
      if (!isset($_SESSION['login_attempts'])) {
          $_SESSION['login_attempts'] = 0;
      }
      $_SESSION['login_attempts']++;
      $_SESSION['last_attempt_time'] = time();
      error_log("Failed login attempt for: $email");
  }

  /**
   * Crée la session utilisateur de manière sécurisée
   */
  private static function createSession(array $user, string $authMethod = 'login'): void {
      session_regenerate_id(true);
      
      $_SESSION['user'] = array(
        'id' => (int)$user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role'],
        'allowed_workflows' => $user['allowed_workflows'] ?? '' 
      );
      
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      $_SESSION['last_activity'] = time();
      $_SESSION['created_at'] = time();
      $_SESSION['ip_address'] = self::getClientIP();
      $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
      
      // Audit log si la classe existe
      if (class_exists(Audit::class)) {
          Audit::log((int)$user['id'], $authMethod, ['ip' => $_SESSION['ip_address']]);
      }
  }
  
  public static function logout(): void { 
    if(isset($_SESSION['user']) && class_exists(Audit::class)) {
      Audit::log((int)$_SESSION['user']['id'], 'logout', null);
    }
    
    unset($_SESSION['csrf_token']);
    $_SESSION = array();
    
    if(ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
      );
    }
    session_destroy();
  }
  
  public static function check(): bool { 
    if(!isset($_SESSION['user'])) return false;
    
    // Timeout d'inactivité (30 min)
    if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
      self::logout(); return false;
    }
    $_SESSION['last_activity'] = time();
    
    // Rotation forcée de session (2h)
    if(isset($_SESSION['created_at']) && (time() - $_SESSION['created_at']) > 7200) {
      self::logout(); return false;
    }

    // Vérification IP anti-vol de session (optionnel mais recommandé)
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== self::getClientIP()) {
        self::logout(); return false;
    }
    
    return true;
  }
  
  public static function user(): ?array { 
    return $_SESSION['user'] ?? null;
  }
  
  private static function getClientIP(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
  }
  
  public static function hasRole(string $role): bool {
    $user = self::user();
    return $user && ($user['role'] === $role);
  }
  
  public static function isAdmin(): bool {
    return self::hasRole('admin');
  }

  // ==================================================
  // HELPERS (CSRF & GOOGLE)
  // ==================================================

  public static function getCsrfToken(): string {
    return CSRF::generateToken();
  }

  public static function getCsrfInput(): void {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getCsrfToken()) . '">';
  }

  public static function validateCsrfToken(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    
    if (!isset($_POST['csrf_token'])) {
        throw new Exception('Jeton de sécurité manquant.');
    }

    if (!CSRF::validateToken($_POST['csrf_token'])) {
        throw new Exception('Session expirée ou invalide (CSRF). Veuillez rafraîchir la page.');
    }
  }

  private static function getGoogleProvider(): Google {
      return new Google([
          'clientId'     => Config::get('GOOGLE_CLIENT_ID'),
          'clientSecret' => Config::get('GOOGLE_CLIENT_SECRET'),
          'redirectUri'  => Config::get('GOOGLE_REDIRECT_URI'),
          'hostedDomain' => Config::get('GOOGLE_HOSTED_DOMAIN', null),
      ]);
  }

  // ==================================================
  // MÉTHODES POUR LE ROUTEUR (HTTP)
  // ==================================================

  public function showLogin(): void {
      if (self::check()) {
          header('Location: /dashboard');
          exit;
      }
      ob_start();
      include __DIR__.'/../Views/login.php';
      $content = ob_get_clean();
      include __DIR__.'/../Views/layout.php';
  }

  public function processLogin(): void {
      try {
          self::validateCsrfToken();
          if (self::attempt($_POST['email'] ?? '', $_POST['password'] ?? '')) {
              header('Location: /dashboard');
              exit;
          } else {
              $error = 'Identifiants invalides';
              // Re-render login with error
              ob_start();
              include __DIR__.'/../Views/login.php';
              $content = ob_get_clean();
              include __DIR__.'/../Views/layout.php';
          }
      } catch (Exception $e) {
          $error = $e->getMessage();
          ob_start();
          include __DIR__.'/../Views/login.php';
          $content = ob_get_clean();
          include __DIR__.'/../Views/layout.php';
      }
  }

  public function logoutAction(): void {
      self::logout();
      header('Location: /login');
      exit;
  }

  public function startGoogleAuth(): void {
      $provider = self::getGoogleProvider();
      $authUrl = $provider->getAuthorizationUrl(['scope' => ['email', 'profile']]);
      $_SESSION['oauth2state'] = $provider->getState();
      header('Location: ' . $authUrl);
      exit;
  }

  public function processGoogleCallback(): void {
      if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
          if (isset($_SESSION['oauth2state'])) unset($_SESSION['oauth2state']);
          die('État OAuth invalide (CSRF possible). Réessayez.');
      }

      try {
          $provider = self::getGoogleProvider();
          $token = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
          $ownerDetails = $provider->getResourceOwner($token);
          $email = $ownerDetails->getEmail();
          
          $u = User::findByEmail($email); 
          if(!$u) {
             $_SESSION['error'] = "L'adresse Google $email n'est pas autorisée dans l'application.";
             header('Location: /login'); exit;
          }
          
          self::createSession($u, 'login_google');
          header('Location: /dashboard');
          exit;
      } catch (Exception $e) {
          $_SESSION['error'] = 'Erreur authentification Google: ' . $e->getMessage();
          header('Location: /login');
          exit;
      }
  }
}