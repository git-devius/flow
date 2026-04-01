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