<?php
namespace App\Models;

use App\Database;

class WorkflowStep {
    
    /**
     * Trouve le validateur pour une société, un type et une étape donnés.
     */
    public static function findValidator($companyId, $workflowType, $stepOrder) {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT validator_user_id 
            FROM workflow_steps 
            WHERE company_id = ? AND workflow_type = ? AND step_order = ?
        ');
        $stmt->execute([$companyId, $workflowType, $stepOrder]);
        return $stmt->fetchColumn();
    }

    /**
     * Vérifie s'il existe une étape après l'étape actuelle.
     * Retourne le numéro de l'étape suivante ou null.
     */
    public static function getNextStep($companyId, $workflowType, $currentStep) {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT step_order 
            FROM workflow_steps 
            WHERE company_id = ? AND workflow_type = ? AND step_order > ? 
            ORDER BY step_order ASC 
            LIMIT 1
        ');
        $stmt->execute([$companyId, $workflowType, $currentStep]);
        return $stmt->fetchColumn() ?: null;
    }
    public static function findAll($company_id, $workflow_type) {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT ws.*, u.name as validator_name 
            FROM workflow_steps ws 
            LEFT JOIN users u ON ws.validator_user_id = u.id 
            WHERE ws.company_id = ? AND ws.workflow_type = ? 
            ORDER BY ws.step_order ASC
        ');
        $stmt->execute([$company_id, $workflow_type]);
        return $stmt->fetchAll();
    }

    public static function create($company_id, $workflow_type, $step_order, $validator_user_id) {
        $db = Database::get();
        $stmt = $db->prepare('
            INSERT INTO workflow_steps (company_id, workflow_type, step_order, validator_user_id) 
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$company_id, $workflow_type, $step_order, $validator_user_id]);
    }

    public static function delete($id) {
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM workflow_steps WHERE id = ?');
        $stmt->execute([$id]);
    }
    
    public static function deleteAllForCompany($company_id, $workflow_type) {
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM workflow_steps WHERE company_id = ? AND workflow_type = ?');
        $stmt->execute([$company_id, $workflow_type]);
    }
}