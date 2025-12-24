<?php
namespace App\Services;

use App\Database;

class AuthorizationService {

    public static function canViewRequest($user, $request) {
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;
        if ($request['requester_id'] == $user['id']) return true;
        
        // MODIFIE : Vérifie si l'user est validateur pour N'IMPORTE QUELLE étape de ce workflow
        $pdo = Database::get();
        // On utilise bien la nouvelle table workflow_steps
        $stmt = $pdo->prepare('
            SELECT 1 FROM workflow_steps 
            WHERE company_id = ? AND workflow_type = ? 
            AND validator_user_id = ?
        ');
        $stmt->execute([
            $request['company_id'], 
            $request['workflow_type'], 
            $user['id']
        ]);
        
        return $stmt->fetchColumn() ? true : false;
    }

    /**
     * Vérifie si l'utilisateur peut valider l'étape courante de la demande
     */
    public static function canValidate($user, $request, $currentStep) {
        if (!$user) return false;
        if ($user['role'] === 'admin') return true; 
        
        // Vérifie si l'user est le validateur assigné à CETTE étape précise
        $pdo = Database::get();
        $stmt = $pdo->prepare('
            SELECT 1 FROM workflow_steps 
            WHERE company_id = ? AND workflow_type = ? 
            AND step_order = ? AND validator_user_id = ?
        ');
        $stmt->execute([
            $request['company_id'], 
            $request['workflow_type'], 
            $currentStep,
            $user['id']
        ]);
        
        return $stmt->fetchColumn() ? true : false;
    }

    public static function canCancel($user, $request) {
        if ($user['role'] === 'admin') return true;
        // On autorise l'annulation tant que c'est "pending" (peu importe l'étape)
        return $request['requester_id'] == $user['id'] && $request['status'] === 'pending';
    }

    public static function canCreateRequest($user, $workflowType) {
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;
        if (empty($user['allowed_workflows'])) return false;
        $allowed = explode(',', $user['allowed_workflows']);
        return in_array($workflowType, $allowed);
    }
}