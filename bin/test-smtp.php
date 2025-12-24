#!/usr/bin/env php
<?php
require __DIR__ . '/../app/Config.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Mail\Mailer;
use App\Config;

echo "=== TEST DE CONNEXION SMTP ===\n\n";

// Afficher la configuration
echo "Configuration SMTP:\n";
echo "- Host: " . Config::get('SMTP_HOST') . "\n";
echo "- Port: " . Config::get('SMTP_PORT') . "\n";
echo "- User: " . Config::get('SMTP_USER') . "\n";
echo "- From: " . Config::get('SMTP_FROM') . "\n";
echo "- Pass: " . (Config::get('SMTP_PASS') ? '***SET***' : 'NOT SET') . "\n\n";

// Test de connexion
echo "=== Test de connexion ===\n";
Mailer::testConnection();

echo "\n=== Test d'envoi d'email ===\n";
$testEmail = $argv[1] ?? 'test@example.com';
echo "Envoi vers: $testEmail\n\n";

$result = Mailer::sendRaw(
    $testEmail,
    'Test User',
    'Test depuis Flow',
    '<h1>Test</h1><p>Ceci est un email de test depuis Flow</p>'
);

if ($result) {
    echo "\n✅ Email envoyé avec succès!\n";
} else {
    echo "\n❌ Échec de l'envoi\n";
}
