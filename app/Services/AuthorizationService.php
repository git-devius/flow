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
        if (!$user) return false;
        
        // Même logique que pour la modification : admin ou propriétaire sous conditions
        $isOwner = ($request['requester_id'] == $user['id']);
        $isAdmin = ($user['role'] === 'admin');
        if (!$isOwner && !$isAdmin) return false;

        // Statuts autorisés pour l'annulation
        if ($request['status'] === 'draft') return true;
        if ($request['status'] === 'returned') return true;
        if ($request['status'] === 'pending' && (!isset($request['current_step']) || $request['current_step'] <= 1)) return true;

        return false;
    }

    public static function canEdit($user, $request) {
        if (!$user) return false;
        
        // Seul le demandeur (ou un admin s'il respecte les statuts) peut modifier
        // On retire le bypass "return true" de l'admin pour forcer le respect du workflow
        $isOwner = ($request['requester_id'] == $user['id']);
        $isAdmin = ($user['role'] === 'admin');

        if (!$isOwner && !$isAdmin) return false;

        // Cas autorisés :
        // 1. Statut Brouillon
        if ($request['status'] === 'draft') return true;
        // 2. Statut Renvoyée par un validateur
        if ($request['status'] === 'returned') return true;
        // 3. Statut En attente ET encore au Niveau 1 (pas encore validé du tout)
        if ($request['status'] === 'pending' && (!isset($request['current_step']) || $request['current_step'] <= 1)) return true;

        return false;
    }

    public static function canCreateRequest($user, $workflowType) {
        if (!$user) return false;
        
        // Si allowed_workflows est vide, on garde le comportement par défaut (admin voit tout, user rien)
        if (empty($user['allowed_workflows'])) {
            return ($user['role'] === 'admin');
        }

        // Si des workflows sont spécifiés, on filtre selon la liste, même pour un admin
        $allowed = explode(',', $user['allowed_workflows']);
        return in_array($workflowType, $allowed);
    }
}