#!/usr/bin/env php
<?php
/**
 * Script de test des corrections de sécurité
 * Usage: docker compose exec web php bin/test-security.php
 */

require __DIR__ . '/../vendor/autoload.php';

echo "====================================\n";
echo "🔒 Test des corrections de sécurité\n";
echo "====================================\n\n";

$errors = 0;
$warnings = 0;

// Test 1 : Vérifier que la classe FileUpload existe
echo "1️⃣  Test classe FileUpload...\n";
if(class_exists('App\Helpers\FileUpload')) {
    echo "   ✅ Classe FileUpload trouvée\n";
    
    // Tester les méthodes
    $methods = array('upload', 'delete', 'validate', 'getMaxSizeMB', 'getAllowedExtensions');
    foreach($methods as $method) {
        if(method_exists('App\Helpers\FileUpload', $method)) {
            echo "   ✅ Méthode $method() existe\n";
        } else {
            echo "   ❌ Méthode $method() manquante\n";
            $errors++;
        }
    }
    
    // Tester getMaxSizeMB
    $maxSize = \App\Helpers\FileUpload::getMaxSizeMB();
    echo "   ℹ️  Taille max : {$maxSize}MB\n";
    
    // Tester getAllowedExtensions
    $extensions = \App\Helpers\FileUpload::getAllowedExtensions();
    echo "   ℹ️  Extensions autorisées : " . implode(', ', $extensions) . "\n";
    
} else {
    echo "   ❌ Classe FileUpload non trouvée\n";
    echo "   → Créez le fichier app/Helpers/FileUpload.php\n";
    $errors++;
}
echo "\n";

// Test 2 : Vérifier AuthController
echo "2️⃣  Test AuthController amélioré...\n";
if(class_exists('App\Controllers\AuthController')) {
    echo "   ✅ Classe AuthController trouvée\n";
    
    // Vérifier les nouvelles méthodes
    $reflection = new ReflectionClass('App\Controllers\AuthController');
    
    // Vérifier getClientIP (devrait être privée)
    if($reflection->hasMethod('getClientIP')) {
        echo "   ✅ Méthode getClientIP() existe\n";
    } else {
        echo "   ⚠️  Méthode getClientIP() manquante\n";
        $warnings++;
    }
    
    // Vérifier hasRole
    if($reflection->hasMethod('hasRole')) {
        echo "   ✅ Méthode hasRole() existe\n";
    } else {
        echo "   ⚠️  Méthode hasRole() manquante\n";
        $warnings++;
    }
    
    // Vérifier isAdmin
    if($reflection->hasMethod('isAdmin')) {
        echo "   ✅ Méthode isAdmin() existe\n";
    } else {
        echo "   ⚠️  Méthode isAdmin() manquante\n";
        $warnings++;
    }
    
} else {
    echo "   ❌ Classe AuthController non trouvée\n";
    $errors++;
}
echo "\n";

// Test 3 : Vérifier EmailQueue
echo "3️⃣  Test EmailQueue corrigé...\n";
if(class_exists('App\Queue\EmailQueue')) {
    echo "   ✅ Classe EmailQueue trouvée\n";
    
    // Vérifier la méthode delete
    $reflection = new ReflectionClass('App\Queue\EmailQueue');
    $method = $reflection->getMethod('delete');
    
    // Lire le code source (si disponible)
    $filename = $reflection->getFileName();
    if($filename && file_exists($filename)) {
        $source = file_get_contents($filename);
        
        // Vérifier que le bug + est corrigé
        if(strpos($source, '$base+') !== false) {
            echo "   ❌ Bug de concaténation toujours présent (+ au lieu de .)\n";
            $errors++;
        } else {
            echo "   ✅ Bug de concaténation corrigé\n";
        }
        
        // Vérifier qu'on utilise bien array() et pas []
        if(strpos($source, 'return [];') !== false || strpos($source, 'return [') !== false) {
            echo "   ⚠️  Syntaxe [] détectée (peut causer des problèmes)\n";
            $warnings++;
        } else {
            echo "   ✅ Syntaxe array() utilisée\n";
        }
    }
    
} else {
    echo "   ❌ Classe EmailQueue non trouvée\n";
    $errors++;
}
echo "\n";

// Test 4 : Vérifier la configuration PHP pour les sessions
echo "4️⃣  Vérification configuration sessions PHP...\n";

$sessionConfig = array(
    'session.cookie_httponly' => '1',
    'session.use_strict_mode' => '1'
);

foreach($sessionConfig as $key => $expectedValue) {
    $currentValue = ini_get($key);
    if($currentValue == $expectedValue) {
        echo "   ✅ $key = $currentValue\n";
    } else {
        echo "   ⚠️  $key = $currentValue (recommandé: $expectedValue)\n";
        $warnings++;
    }
}

// Cookie secure (devrait être 1 en production)
$cookieSecure = ini_get('session.cookie_secure');
if($cookieSecure == '1') {
    echo "   ✅ session.cookie_secure = 1 (HTTPS)\n";
} else {
    echo "   ℹ️  session.cookie_secure = 0 (OK pour développement, HTTPS requis en production)\n";
}

echo "\n";

// Test 5 : Vérifier les répertoires uploads
echo "5️⃣  Vérification répertoires uploads...\n";

$uploadDirs = array(
    __DIR__ . '/../uploads',
    __DIR__ . '/../queue/emails',
    __DIR__ . '/../queue/emails/failed'
);

foreach($uploadDirs as $dir) {
    if(is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "   ✅ " . basename($dir) . " existe (permissions: $perms)\n";
        
        if(!is_writable($dir)) {
            echo "      ⚠️  Pas d'accès en écriture !\n";
            $warnings++;
        }
    } else {
        echo "   ⚠️  " . basename($dir) . " n'existe pas (sera créé automatiquement)\n";
    }
}

echo "\n";

// Test 6 : Simuler une validation de fichier
echo "6️⃣  Test simulation validation fichier...\n";

if(class_exists('App\Helpers\FileUpload')) {
    // Créer un faux fichier pour tester la validation
    $fakeFile = array(
        'name' => 'test.pdf',
        'type' => 'application/pdf',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
        'size' => 0
    );
    
    $validation = \App\Helpers\FileUpload::validate($fakeFile);
    
    if(!$validation['valid']) {
        echo "   ✅ Validation détecte correctement l'erreur : " . $validation['error'] . "\n";
    } else {
        echo "   ⚠️  La validation devrait échouer pour un fichier vide\n";
        $warnings++;
    }
} else {
    echo "   ⏭️  FileUpload non disponible, test ignoré\n";
}

echo "\n";

// Résumé
echo "====================================\n";
echo "📊 Résumé des tests\n";
echo "====================================\n";
echo "Erreurs : $errors\n";
echo "Avertissements : $warnings\n";
echo "\n";

if($errors > 0) {
    echo "❌ Certains fichiers nécessaires sont manquants ou incorrects.\n";
    echo "   Consultez BUGS_FIXES.md pour les instructions d'installation.\n";
    exit(1);
} elseif($warnings > 0) {
    echo "⚠️  Tests passés avec quelques avertissements.\n";
    echo "   L'application devrait fonctionner, mais certaines optimisations sont recommandées.\n";
    exit(0);
} else {
    echo "✅ Tous les tests sont passés !\n";
    echo "   Les corrections de sécurité sont correctement installées.\n";
    exit(0);
}
