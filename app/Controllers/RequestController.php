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

  public static function updateRequest($id, $data) {
    $old = Request::find($id);
    if (!$old) throw new \Exception('Demande introuvable.');

    $user = AuthController::user();
    if (!AuthorizationService::canEdit($user, $old)) {
        throw new \Exception('Modification non autorisée.');
    }

    // Calcul du diff pour l'historique
    $diffs = [];
    $fields = [
        'pole_id' => 'Pôle',
        'company_id' => 'Société',
        'type' => ($old['workflow_type'] === 'vacation' ? 'Type d\'absence' : ($old['workflow_type'] === 'expense' ? 'Catégorie' : 'Titre')),
        'objective' => ($old['workflow_type'] === 'vacation' ? 'Commentaire' : ($old['workflow_type'] === 'expense' ? 'Description' : 'Objectif')),
        'start_date_duration' => ($old['workflow_type'] === 'vacation' ? 'Période' : ($old['workflow_type'] === 'expense' ? 'Date' : 'Date/Durée')),
        'amount' => ($old['workflow_type'] === 'vacation' ? 'Nombre de jours' : 'Montant'),
        'budget_planned' => 'Budget prévu'
    ];

    foreach ($fields as $key => $label) {
        if (isset($data[$key]) && $data[$key] != $old[$key]) {
            $oldVal = $old[$key];
            $newVal = $data[$key];
            
            // Formatage spécifique pour certains champs
            if ($key === 'amount' && $old['workflow_type'] !== 'vacation') {
                $oldVal = number_format((float)$oldVal, 2, ',', ' ') . ' €';
                $newVal = number_format((float)$newVal, 2, ',', ' ') . ' €';
            } elseif ($key === 'budget_planned') {
                $oldVal = $oldVal ? 'Oui' : 'Non';
            } elseif ($key === 'pole_id') {
                $pOld = \App\Models\Pole::find((int)$oldVal);
                $pNew = \App\Models\Pole::find((int)$newVal);
                $oldVal = $pOld['name'] ?? $oldVal;
                $newVal = $pNew['name'] ?? $newVal;
            } elseif ($key === 'company_id') {
                $cOld = \App\Models\Company::find((int)$oldVal);
                $cNew = \App\Models\Company::find((int)$newVal);
                $oldVal = $cOld['name'] ?? $oldVal;
                $newVal = $cNew['name'] ?? $newVal;
            }
            
            $diffs[] = "$label : $oldVal → $newVal";
        }
    }

    // On repasse en pending car la demande a été modifiée
    $data['status'] = 'pending';
    
    Request::update($id, $data);

    // Enregistrement dans l'historique (Table approvals)
    if (!empty($diffs)) {
        $comment = "Modification de la demande :\n- " . implode("\n- ", $diffs);
        // On utilise 'modified' comme décision (nécessite l'update de l'enum en BDD)
        Approval::add($id, $user['id'], $old['current_step'], 'modified', $comment);
    }

    \App\Models\Audit::log($user['id'], 'updated_request', [
        'request_id' => $id,
        'diffs' => $diffs
    ]);

    // On re-notifie les validateurs de l'étape car la demande est de nouveau prête
    NotificationService::notifyValidatorsModified($id, $old['current_step']);

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