<?php
namespace App\Controllers;

use App\Models\Request; 
use App\Models\Company;
use App\Models\User;
use App\Models\Approval;
use App\Services\NotificationService;
use App\Services\AuthorizationService;
use App\Services\WorkflowEngine; 
use App\Services\ExportService; // NOUVEAU

class RequestController { 
  
  public static function export() {
    $filters = [
      'search' => $_GET['search'] ?? '',
      'type' => $_GET['type'] ?? '',
      'status' => $_GET['status'] ?? '',
      'pole_id' => $_GET['pole_id'] ?? '',
      'company_id' => $_GET['company_id'] ?? '',
      'min_amount' => $_GET['min_amount'] ?? '',
      'max_amount' => $_GET['max_amount'] ?? '',
      'open_only' => isset($_GET['open_only']) ? 1 : 0,
      'not_cancelled_only' => isset($_GET['not_cancelled_only']) ? 1 : 0,
      'workflow_type' => $_GET['workflow_type'] ?? 'investment'
    ];
    
    $user = AuthController::user();
    
    // Récupération des données via le modèle
    // Note: 999999 est une limite arbitraire pour l'export, à paginer si trop gros
    $requests = Request::search($user, $filters, 'created_at', 'DESC', 999999, 0);
    
    // Délégation de l'affichage au service
    ExportService::generateCsv($requests, $filters['workflow_type']);
    exit;
  }

  public static function createRequest($data, $requester_id) {
    if (!Company::find($data['company_id'])) {
      throw new \Exception('Société inconnue.');
    }
    
    $id = Request::create(array_merge($data, ['requester_id' => $requester_id]));
    
    \App\Models\Audit::log($requester_id, 'created_request', [
        'request_id' => $id, 
        'workflow_type' => $data['workflow_type'] ?? 'investment'
    ]);
    
    NotificationService::notifyRequesterCreated($id);
    NotificationService::notifyValidators($id, 1);
    
    return $id;
  }
  
  public static function approve($request_id, $validator_id, $level, $decision, $comment = null) {
    $req = Request::find($request_id);
    if (!$req) throw new \Exception('Demande introuvable.');
    
    $user = User::find($validator_id);
    
    // Vérification via le nouveau système d'étape
    if (!AuthorizationService::canValidate($user, $req, $level)) {
      throw new \Exception('Droits insuffisants pour valider cette étape.');
    }
    
    Approval::add($request_id, $validator_id, $level, $decision, $comment);
    
    // Utilisation du moteur pour décider la suite
    WorkflowEngine::processDecision($request_id, $validator_id, $level, $decision);
  }

  public static function handleCancel($id) {
    $user = AuthController::user();
    $request = Request::find($id);
    
    if (!$request) throw new \Exception('Demande introuvable.');
    
    $status_before = $request['status'];

    if (!AuthorizationService::canCancel($user, $request)) {
        throw new \Exception('Action non autorisée.');
    }
    
    \App\Models\Audit::log($user['id'], 'cancelled_request', [
      'request_id' => $id, 
      'status_before' => $status_before,
      'workflow_type' => $request['workflow_type']
    ]);
    
    Request::updateStatus($id, 'cancelled');
    
    NotificationService::notifyRequesterCancelled($id, $user);
    // Notification au validateur de l'étape courante
    NotificationService::notifyValidators($id, $request['current_step']);
    
    $_SESSION['success'] = 'La demande #'.$id.' a été annulée.';
    header('Location: index.php?action=list&workflow_type=' . $request['workflow_type']);
    exit;
  }
}