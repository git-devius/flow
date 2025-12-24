<?php
/**
 * User Model - Modèle utilisateur
 * Standardisé et nettoyé.
 */

namespace App\Models;
use App\Database;

class User {
    
    public static function find($id){
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function findByEmail($email){
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    // Récupérer tous les utilisateurs
    public static function all(){
        $pdo = Database::get();
        $stmt = $pdo->query('SELECT u.*, u.is_active FROM users u ORDER BY name'); 
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($users as &$user) {
            $user['allowed_workflows_array'] = $user['allowed_workflows'] ? explode(',', $user['allowed_workflows']) : [];
        }
        
        return $users;
    }
    
    public static function allUsers(){
        $pdo = Database::get();
        $stmt = $pdo->query('SELECT * FROM users ORDER BY name');
        return $stmt->fetchAll();
    }
    
    public static function isAdmin($userId){
        $user = self::find($userId);
        return $user && $user['role'] === 'admin';
    }
    
    public static function toggleStatus(int $userId, bool $isActive): bool 
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        $statusValue = $isActive ? 1 : 0;
        return $stmt->execute([':is_active' => $statusValue, ':id' => $userId]);
    }
    
    /**
     * Vérifier si un utilisateur est validateur N1 pour une société
     */
    public static function isValidatorLv1ForCompany($userId, $companyId, $workflowType = null){
        $pdo = Database::get();
        
        if ($workflowType) {
            $stmt = $pdo->prepare('SELECT 1 FROM workflow_validators WHERE company_id = ? AND workflow_type = ? AND validator_lv1_user_id = ?');
            $stmt->execute([$companyId, $workflowType, $userId]);
        } else {
            $stmt = $pdo->prepare('SELECT 1 FROM workflow_validators WHERE company_id = ? AND validator_lv1_user_id = ?');
            $stmt->execute([$companyId, $userId]);
        }
        
        return (bool)$stmt->fetch();
    }
    
    /**
     * Vérifier si un utilisateur est validateur N2 pour une société
     */
    public static function isValidatorLv2ForCompany($userId, $companyId, $workflowType = null){
        $pdo = Database::get();
        
        if ($workflowType) {
            $stmt = $pdo->prepare('SELECT 1 FROM workflow_validators WHERE company_id = ? AND workflow_type = ? AND validator_lv2_user_id = ?');
            $stmt->execute([$companyId, $workflowType, $userId]);
        } else {
            $stmt = $pdo->prepare('SELECT 1 FROM workflow_validators WHERE company_id = ? AND validator_lv2_user_id = ?');
            $stmt->execute([$companyId, $userId]);
        }
        
        return (bool)$stmt->fetch();
    }
    
    public static function getCompaniesAsValidatorLv1($userId, $workflowType = null){
        $pdo = Database::get();
        $query = '
            SELECT wv.*, c.name as company_name, c.pole_id, p.name as pole_name
            FROM workflow_validators wv
            JOIN companies c ON wv.company_id = c.id
            JOIN poles p ON c.pole_id = p.id
            WHERE wv.validator_lv1_user_id = ?
        ';
        
        if ($workflowType) {
            $query .= ' AND wv.workflow_type = ? ORDER BY wv.workflow_type, c.name';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$userId, $workflowType]);
        } else {
            $query .= ' ORDER BY wv.workflow_type, c.name';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll();
    }
    
    public static function getCompaniesAsValidatorLv2($userId, $workflowType = null){
        $pdo = Database::get();
        $query = '
            SELECT wv.*, c.name as company_name, c.pole_id, p.name as pole_name
            FROM workflow_validators wv
            JOIN companies c ON wv.company_id = c.id
            JOIN poles p ON c.pole_id = p.id
            WHERE wv.validator_lv2_user_id = ?
        ';
        
        if ($workflowType) {
            $query .= ' AND wv.workflow_type = ? ORDER BY wv.workflow_type, c.name';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$userId, $workflowType]);
        } else {
            $query .= ' ORDER BY wv.workflow_type, c.name';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll();
    }
    
    public static function getRolesSummary($userId){
        $user = self::find($userId);
        if(!$user) return null;
        
        $companiesLv1 = self::getCompaniesAsValidatorLv1($userId);
        $companiesLv2 = self::getCompaniesAsValidatorLv2($userId);
        
        $byWorkflow = [];
        foreach ($companiesLv1 as $assignment) {
            $wf = $assignment['workflow_type'];
            if (!isset($byWorkflow[$wf])) $byWorkflow[$wf] = ['lv1' => [], 'lv2' => []];
            $byWorkflow[$wf]['lv1'][] = $assignment;
        }
        foreach ($companiesLv2 as $assignment) {
            $wf = $assignment['workflow_type'];
            if (!isset($byWorkflow[$wf])) $byWorkflow[$wf] = ['lv1' => [], 'lv2' => []];
            $byWorkflow[$wf]['lv2'][] = $assignment;
        }
        
        return [
            'is_admin' => $user['role'] === 'admin',
            'allowed_workflows' => !empty($user['allowed_workflows']) ? explode(',', $user['allowed_workflows']) : [],
            'validator_lv1_count' => count($companiesLv1),
            'validator_lv2_count' => count($companiesLv2),
            'validator_lv1_assignments' => $companiesLv1,
            'validator_lv2_assignments' => $companiesLv2,
            'by_workflow' => $byWorkflow,
        ];
    }
    
    public static function getValidatorWorkflows($userId) {
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT DISTINCT workflow_type FROM workflow_validators WHERE validator_lv1_user_id = ? OR validator_lv2_user_id = ? ORDER BY workflow_type');
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    public static function isValidatorForWorkflow($userId, $workflowType) {
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT 1 FROM workflow_validators WHERE workflow_type = ? AND (validator_lv1_user_id = ? OR validator_lv2_user_id = ?)');
        $stmt->execute([$workflowType, $userId, $userId]);
        return (bool)$stmt->fetch();
    }
    
    public static function regenToken($userId){
        $pdo = Database::get();
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare('UPDATE users SET api_token = ? WHERE id = ?');
        $stmt->execute([$token, $userId]);
        return $token;
    }
    
    public static function findByAllowedWorkflow($workflowType) {
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE FIND_IN_SET(?, allowed_workflows) > 0 ORDER BY name');
        $stmt->execute([$workflowType]);
        return $stmt->fetchAll();
    }
}