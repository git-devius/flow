<?php
namespace App\Controllers;

use App\Database;
use App\Models\User;
use App\Models\Company;
use App\Models\Request;
use App\Models\WorkflowStep; // NOUVEAU: Import du modèle des étapes

class AdminController {
    
    public static function requireAdmin(){
        if(empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
            header('Location: index.php?action=login');
            exit;
        }
    }
    
    // ===================================
    // GESTION DES UTILISATEURS
    // ===================================
    
    public static function listUsers(){
        return User::all();
    }
    
    public static function createUser($email, $password, $name, $role, $allowedWorkflows = ''){
        if(!in_array($role, ['admin', 'user'])){
            throw new \Exception('Rôle invalide. Seuls "admin" et "user" sont autorisés.');
        }
        
        $pdo = Database::get();
        
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if($stmt->fetch()){
            throw new \Exception('Un utilisateur avec cet email existe déjà');
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16));
        
        $stmt = $pdo->prepare('
            INSERT INTO users (email, password, name, role, allowed_workflows, api_token, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([$email, $hashedPassword, $name, $role, $allowedWorkflows, $token]);
        
        return $pdo->lastInsertId();
    }
    
    public static function updateUser($id, $name, $role, $password = null, $allowedWorkflows = ''){
        if(!in_array($role, ['admin', 'user'])){
            throw new \Exception('Rôle invalide. Seuls "admin" et "user" sont autorisés.');
        }
        
        $pdo = Database::get();
        
        if($password){
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET name = ?, role = ?, allowed_workflows = ?, password = ? WHERE id = ?');
            $stmt->execute([$name, $role, $allowedWorkflows, $hashedPassword, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, role = ?, allowed_workflows = ? WHERE id = ?');
            $stmt->execute([$name, $role, $allowedWorkflows, $id]);
        }
    }
    
    public static function deleteUser($id){
        $pdo = Database::get();
        
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM requests WHERE requester_id = ?');
        $stmt->execute([$id]);
        if($stmt->fetchColumn() > 0){
            throw new \Exception('Impossible de supprimer cet utilisateur car il a créé des demandes.');
        }
        
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM approvals WHERE validator_id = ?');
        $stmt->execute([$id]);
        if($stmt->fetchColumn() > 0){
            throw new \Exception('Impossible de supprimer cet utilisateur car il est lié à des validations. Veuillez le désactiver.');
        }
        
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
    
    public static function toggleUserStatus(int $userId, string $actionType): bool {
        if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === $userId && $actionType === 'deactivate') {
            throw new \Exception("Vous ne pouvez pas désactiver votre propre compte d'administrateur.");
        }
        $isActive = ($actionType === 'activate');
        if (User::toggleStatus($userId, $isActive)) {
            return true;
        }
        throw new \Exception("Erreur lors de la mise à jour du statut.");
    }
    
    // ===================================
    // GESTION DES PÔLES
    // ===================================
    public static function poles(){
        $pdo = Database::get();
        return $pdo->query('SELECT * FROM poles ORDER BY name')->fetchAll();
    }
    
    public static function createPole($name){
        $pdo = Database::get();
        $stmt = $pdo->prepare('INSERT INTO poles (name, created_at) VALUES (?, NOW())');
        $stmt->execute([$name]);
        return $pdo->lastInsertId();
    }
    
    public static function updatePole($id, $name){
        $pdo = Database::get();
        $stmt = $pdo->prepare('UPDATE poles SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }
    
    public static function deletePole($id){
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM companies WHERE pole_id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if($result['count'] > 0){
            throw new \Exception('Impossible de supprimer ce pôle car il contient des sociétés.');
        }
        $stmt = $pdo->prepare('DELETE FROM poles WHERE id = ?');
        $stmt->execute([$id]);
    }
    
    // ===================================
    // GESTION DES SOCIÉTÉS
    // ===================================
    public static function companies(){
        $pdo = Database::get();
        $stmt = $pdo->query('
            SELECT c.*, p.name as pole_name 
            FROM companies c 
            LEFT JOIN poles p ON c.pole_id = p.id 
            ORDER BY p.name, c.name
        ');
        return $stmt->fetchAll();
    }
    
    public static function createCompany($poleId, $name){
        $pdo = Database::get();
        $stmt = $pdo->prepare('INSERT INTO companies (pole_id, name, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$poleId, $name]);
        return $pdo->lastInsertId();
    }
    
    public static function updateCompany($id, $poleId, $name){
        $pdo = Database::get();
        $stmt = $pdo->prepare('UPDATE companies SET pole_id = ?, name = ? WHERE id = ?');
        $stmt->execute([$poleId, $name, $id]);
    }
    
    public static function deleteCompany($id){
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM requests WHERE company_id = ?');
        $stmt->execute([$id]);
        if($stmt->fetch()['count'] > 0){
            throw new \Exception('Impossible de supprimer cette société car elle contient des demandes.');
        }
        $stmt = $pdo->prepare('DELETE FROM companies WHERE id = ?');
        $stmt->execute([$id]);
    }
    
    // ===================================
    // NOUVEAU : GESTION DYNAMIQUE DES VALIDEURS (N NIVEAUX)
    // ===================================
    
    public static function validators() {
        // 1. Récupération des filtres
        $company_id = $_GET['company_id'] ?? null;
        $workflow_type = $_GET['workflow_type'] ?? 'investment';

        // 2. Traitement du formulaire (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $company_id = $_POST['company_id'];
            $workflow_type = $_POST['workflow_type'];
            
            // a. On nettoie les étapes existantes pour cette société/workflow
            WorkflowStep::deleteAllForCompany($company_id, $workflow_type);

            // b. On recrée les étapes envoyées par le formulaire
            if (!empty($_POST['validators']) && is_array($_POST['validators'])) {
                foreach ($_POST['validators'] as $index => $user_id) {
                    if (!empty($user_id)) {
                        // L'index + 1 devient le numéro d'étape (step_order)
                        WorkflowStep::create($company_id, $workflow_type, $index + 1, $user_id);
                    }
                }
            }
            
            $_SESSION['success'] = "Circuit de validation mis à jour.";
            // CORRECTION: Redirection vers la bonne route définie dans index.php
            header("Location: /admin/workflow_validators?company_id=$company_id&workflow_type=$workflow_type");
            exit;
        }

        // 3. Affichage (GET)
        $companies = Company::all();
        $users = User::all(); 
        
        $currentSteps = [];
        if ($company_id) {
            $currentSteps = WorkflowStep::findAll($company_id, $workflow_type);
        }

        // Chargement de la vue mise à jour (admin_workflow_validators.php)
        require __DIR__ . '/../Views/admin_workflow_validators.php';
    }
}