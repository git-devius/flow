<?php
require __DIR__ . '/../app/Config.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Queue\EmailQueue;
use App\Mail\Mailer;

echo "[" . date('Y-m-d H:i:s') . "] Worker demarre\n";

$processedCount = 0;
$errorCount = 0;

while (true) {
  $job = EmailQueue::pop();
  
  if (!$job) {
    usleep(300000);
    continue;
  }
  
  $file = $job['file'];
  $payload = $job['payload'];
  
  echo "[" . date('Y-m-d H:i:s') . "] Traitement: " . basename($file) . "\n";
  
  try {
    // Validation
    if (empty($payload['to'])) {
      throw new Exception('Destinataire manquant');
    }
    
    // Validation email directement avec filter_var
    if (!filter_var($payload['to'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception('Email invalide: ' . $payload['to']);
    }
    
    $ok = Mailer::sendRaw(
      $payload['to'],
      $payload['toName'] ?? 'User',
      $payload['subject'] ?? 'Notification',
      $payload['html'] ?? '<p>Message</p>'
    );
    
    if ($ok) {
      EmailQueue::done($file);
      $processedCount++;
      echo "Email envoye avec succes\n";
    } else {
      EmailQueue::fail($file, 'Echec envoi');
      $errorCount++;
      echo "Echec de l'envoi\n";
    }
    
  } catch (Exception $e) {
    EmailQueue::fail($file, $e->getMessage());
    $errorCount++;
    echo "Exception: " . $e->getMessage() . "\n";
  }
}