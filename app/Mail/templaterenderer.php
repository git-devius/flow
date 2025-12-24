<?php
namespace App\Mail;

use App\Config;
use App\Queue\EmailQueue;

class TemplateRenderer 
{
  /**
   * Rend un template d'email avec les données fournies
   * 
   * @param string $templateName Nom du fichier template (sans .html)
   * @param array $data Données à injecter dans le template
   * @return array ['subject' => string, 'html' => string]
   * @throws \Exception Si le template n'existe pas
   */
  public static function render($templateName, $data = []) 
  {
    $templatePath = __DIR__ . '/templates/' . $templateName . '.html';
    
    if (!file_exists($templatePath)) {
      throw new \Exception("Template email introuvable: $templateName (chemin: $templatePath)");
    }
    
    $content = file_get_contents($templatePath);
    
    // Extraire le sujet du template (première ligne: <!-- SUBJECT: ... -->)
    $subject = 'Notification Flow';
    if (preg_match('/<!-- SUBJECT: (.+?) -->/', $content, $matches)) {
      $subject = trim($matches[1]);
    }
    
    // Ajouter l'URL de base aux données
    $data['base_url'] = Config::get('BASE_URL', 'http://localhost:8000');
    
    // Remplacer les placeholders {{variable}} et {{object.property}}
    $html = preg_replace_callback('/\{\{([a-zA-Z0-9_.]+)\}\}/', function($matches) use ($data) {
      $key = $matches[1];
      
      // Support pour les propriétés imbriquées (ex: investment.id)
      if (strpos($key, '.') !== false) {
        $parts = explode('.', $key);
        $value = $data;
        foreach ($parts as $part) {
          if (is_array($value) && isset($value[$part])) {
            $value = $value[$part];
          } else {
            return $matches[0]; // Retourner le placeholder si non trouvé
          }
        }
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
      }
      
      // Propriété simple
      return htmlspecialchars($data[$key] ?? '', ENT_QUOTES, 'UTF-8');
    }, $content);
    
    // Remplacer aussi le sujet
    $subject = preg_replace_callback('/\{\{([a-zA-Z0-9_.]+)\}\}/', function($matches) use ($data) {
      $key = $matches[1];
      
      if (strpos($key, '.') !== false) {
        $parts = explode('.', $key);
        $value = $data;
        foreach ($parts as $part) {
          if (is_array($value) && isset($value[$part])) {
            $value = $value[$part];
          } else {
            return $matches[0];
          }
        }
        return $value ?? '';
      }
      
      return $data[$key] ?? '';
    }, $subject);
    
    return [
      'subject' => $subject,
      'html' => $html
    ];
  }
  
  /**
   * Rend et envoie un email via la queue
   * 
   * @param string $templateName Nom du template
   * @param string $to Email destinataire
   * @param string $toName Nom du destinataire
   * @param array $data Données pour le template
   * @return string Chemin du fichier dans la queue
   * @throws \Exception Si le template n'existe pas ou si l'email est invalide
   */
  public static function sendEmail($templateName, $to, $toName, $data = []) 
  {
    // Valider l'email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
      throw new \Exception("Email destinataire invalide: $to");
    }
    
    // Rendre le template
    $rendered = self::render($templateName, $data);
    
    // Créer le payload pour la queue
    $payload = [
      'to' => $to,
      'toName' => $toName,
      'subject' => $rendered['subject'],
      'html' => $rendered['html']
    ];
    
    // Ajouter à la queue
    $queueFile = EmailQueue::push($payload);
    
    error_log("Email ajouté à la queue: $templateName pour $to (fichier: $queueFile)");
    
    return $queueFile;
  }
}
