<?php
// =============================================
// SCRIPT DE PURGE DES DEMANDES DE WORKFLOW
// A exécuter via la ligne de commande (CLI)
// php bin/purge_requests.php
// =============================================

// Vérifie que le script est exécuté via CLI
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté via la ligne de commande (CLI).");
}

// Configuration et autoload (utilise la structure de index.php)
$baseDir = dirname(__DIR__);

// Assurez-vous que ces fichiers existent ou ajustez le chemin
require $baseDir . '/app/Config.php';
require $baseDir . '/app/Database.php';
require $baseDir . '/vendor/autoload.php';

use App\Database;

echo "================================================\n";
echo " PURGE DES DEMANDES DE WORKFLOW ET APPROBATIONS \n";
echo "================================================\n";

// DEMANDE DE CONFIRMATION
fwrite(STDOUT, "ATTENTION: Ceci va supprimer TOUTES les demandes et les validations associées.\nVoulez-vous continuer ? (oui/non) : ");
$confirmation = strtolower(trim(fgets(STDIN)));

if ($confirmation !== 'oui') {
    echo "Opération annulée.\n";
    exit(0);
}

try {
    $pdo = Database::get();
    $pdo->beginTransaction();

    echo "Démarrage de la purge...\n";

    // 1. Suppression des enregistrements dans la table ENFANT (approvals)
    // C'est nécessaire car 'approvals' a une clé étrangère sur 'requests'.
    $stmt = $pdo->exec("DELETE FROM approvals");
    echo "- $stmt enregistrement(s) d'approbation(s) supprimé(s).\n";

    // 2. Suppression des enregistrements dans la table PRINCIPALE (requests)
    $stmt = $pdo->exec("DELETE FROM requests");
    echo "- $stmt demande(s) supprimée(s).\n";
    
    // OPTIONNEL: Si vous avez d'autres tables liées (ex: audit, logs d'emails), ajoutez-les ici:
    // $stmt = $pdo->exec("DELETE FROM audit WHERE request_id IS NOT NULL");
    // $pdo->exec("TRUNCATE TABLE email_queue"); 

    $pdo->commit();
    
    echo "================================================\n";
    echo "SUCCÈS: Toutes les demandes et validations ont été purgées.\n";
    echo "L'auto-incrément des tables 'approvals' et 'requests' n'a pas été réinitialisé (utilisez TRUNCATE si vous le souhaitez).\n";
    echo "================================================\n";

} catch (\PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "================================================\n";
    echo "ERREUR PDO: La purge a échoué. Transaction annulée.\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "================================================\n";
    exit(1);
} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}