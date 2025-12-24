<?php
namespace App\Mail;
use App\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  
  /**
   * Envoie un email en utilisant PHPMailer avec gestion UTF-8
   * * @param string $to Email du destinataire
   * @param string $toName Nom du destinataire
   * @param string $subject Sujet de l'email
   * @param string $html Corps HTML de l'email
   * @param int $attempts Nombre de tentatives d'envoi
   * @return bool Succès ou échec de l'envoi
   */
  public static function sendRaw($to, $toName, $subject, $html, $attempts = 3) {
    $m = new PHPMailer(true);
    
    // Configuration SMTP
    $m->isSMTP();
    $m->Host = Config::get('SMTP_HOST');
    $m->SMTPAuth = true;
    $m->Username = Config::get('SMTP_USER');
    $m->Password = Config::get('SMTP_PASS');
    $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $m->Port = Config::get('SMTP_PORT');
    
    // ✅ IMPORTANT : Configuration de l'encodage UTF-8
    $m->CharSet = PHPMailer::CHARSET_UTF8;
    $m->Encoding = 'base64';
    
    // Configuration de l'expéditeur
    $m->setFrom(
      Config::get('SMTP_FROM'), 
      Config::get('SMTP_FROM_NAME', 'Flow')
    );
    
    // Format HTML
    $m->isHTML(true);
    
    // Tentatives d'envoi avec retry
    $last = null;
    for ($i = 0; $i < $attempts; $i++) {
      try {
        $m->clearAddresses();
        $m->addAddress($to, $toName);
        $m->Subject = $subject;
        $m->Body = $html;
        
        // Version texte alternative (optionnel mais recommandé)
        $m->AltBody = strip_tags($html);
        
        $m->send();
        return true;
        
      } catch (Exception $e) {
        $last = $m->ErrorInfo ?: $e->getMessage();
        
        // Attendre avant de réessayer
        if ($i < $attempts - 1) {
          sleep(1 + $i * 2);
        }
      }
    }
    
    error_log('Mail failed after ' . $attempts . ' attempts: ' . $last);
    return false;
  }
}