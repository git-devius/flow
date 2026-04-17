<?php
namespace App\Services;

use App\Models\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowStep; // NOUVEAU
use App\Mail\TemplateRenderer;

class NotificationService {

  public static function notifyRequesterCreated($request_id) {
    $inv = Request::find($request_id);
    if (!$inv) return;
    $requester = User::find($inv['requester_id']);
    if (!$requester) return;
    $data = ['investment' => $inv, 'requester' => $requester];
    $template = 'created_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $requester['email'], $requester['name'], $data);
  }

  /**
   * Notifie les validateurs d'une étape donnée.
   * MODIFIE pour utiliser WorkflowStep
   */
  public static function notifyValidators($request_id, $stepOrder) {
    $inv = Request::find($request_id);
    if (!$inv) return;

    $company = Company::find($inv['company_id']);

    // Récupération dynamique via la nouvelle table
    $validator_id = WorkflowStep::findValidator($inv['company_id'], $inv['workflow_type'], $stepOrder);
    
    if (!$validator_id) {
      $companyName = $company ? $company['name'] : 'ID ' . $inv['company_id'];
      error_log("Aucun validateur pour l'étape $stepOrder, société {$companyName}, workflow {$inv['workflow_type']}");
      return;
    }

    $validator = User::find($validator_id);
    if (!$validator) return;

    // 'level' dans le template email correspondra maintenant à l'étape
    $data = ['investment' => $inv, 'validator' => $validator, 'level' => $stepOrder];
    $template = 'notify_level_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $validator['email'], $validator['name'], $data);
  }

  /**
   * Notifie les validateurs qu'une demande a été modifiée par le demandeur.
   */
  public static function notifyValidatorsModified($request_id, $stepOrder) {
    $inv = Request::find($request_id);
    if (!$inv) return;

    $validator_id = WorkflowStep::findValidator($inv['company_id'], $inv['workflow_type'], $stepOrder);
    if (!$validator_id) return;

    $validator = User::find($validator_id);
    if (!$validator) return;

    $data = ['investment' => $inv, 'validator' => $validator, 'level' => $stepOrder];
    $template = 'request_modified_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $validator['email'], $validator['name'], $data);
  }
  
  public static function notifyRequesterApproved($request_id) {
    $inv = Request::find($request_id);
    if (!$inv) return;
    $requester = User::find($inv['requester_id']);
    if (!$requester) return;
    $data = ['investment' => $inv, 'requester' => $requester];
    $template = 'approved_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $requester['email'], $requester['name'], $data);
  }

  public static function notifyRequesterRejected($request_id) {
    $inv = Request::find($request_id);
    if (!$inv) return;
    $requester = User::find($inv['requester_id']);
    if (!$requester) return;
    $data = ['investment' => $inv, 'requester' => $requester];
    $template = 'rejected_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $requester['email'], $requester['name'], $data);
  }

  public static function notifyRequesterReturned($request_id) {
    $inv = Request::find($request_id);
    if (!$inv) return;
    $requester = User::find($inv['requester_id']);
    if (!$requester) return;
    $data = ['investment' => $inv, 'requester' => $requester];
    $template = 'returned_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $requester['email'], $requester['name'], $data);
  }

  public static function notifyRequesterCancelled($request_id, $actor) {
    $inv = Request::find($request_id);
    if (!$inv) return;
    $requester = User::find($inv['requester_id']);
    if (!$requester) return;
    $data = ['investment' => $inv, 'requester' => $requester, 'actor' => $actor];
    $template = 'cancelled_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $requester['email'], $requester['name'], $data);
  }

  public static function notifyValidatorCancelled($request_id, $actor, $level) {
    $inv = Request::find($request_id);
    if (!$inv) return;
    $validator_id = WorkflowStep::findValidator($inv['company_id'], $inv['workflow_type'], $level);
    if (!$validator_id) return;
    $validator = User::find($validator_id);
    if (!$validator) return;
    $data = ['investment' => $inv, 'validator' => $validator, 'actor' => $actor, 'level' => $level];
    $template = 'cancelled_validator_' . $inv['workflow_type'];
    TemplateRenderer::sendEmail($template, $validator['email'], $validator['name'], $data);
  }
}