<?php
namespace App\Controllers;

use App\Models\Request;
use App\Controllers\AuthController;

class HomeController {
    
    /**
     * Affiche le tableau de bord principal
     */
    public function dashboard() {
        if (!AuthController::check()) {
            header('Location: /login');
            exit;
        }

        $user = AuthController::user();
        
        // Récupération des données
        $kpis = Request::getKpis($user); 
        $workflowTypes = Request::WORKFLOW_TYPES;
        
        // RENDU DE LA VUE (Pattern Buffering)
        // 1. On démarre l'enregistrement
        ob_start();
        // 2. On inclut la vue partielle (elle accède à $kpis et $workflowTypes car incluse ici)
        include __DIR__.'/../Views/dashboard.php';
        // 3. On récupère le contenu HTML dans une variable
        $content = ob_get_clean();
        
        // 4. On inclut le layout principal qui affichera $content
        include __DIR__.'/../Views/layout.php';
    }
}