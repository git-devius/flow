<?php
namespace App\Services;

use App\Models\Request;
use App\Models\WorkflowStep;
use App\Services\NotificationService;

class WorkflowEngine {

    /**
     * Traite une décision de validation (Approve/Reject)
     */
    public static function processDecision($requestId, $validatorId, $currentStep, $decision) {
        $req = Request::find($requestId);
        if (!$req) throw new \Exception("Demande introuvable");

        // Cas 1 : Rejet
        if ($decision === 'rejected') {
            Request::updateStatus($requestId, 'rejected');
            NotificationService::notifyRequesterRejected($requestId);
            return;
        }

        // Cas 2 : Approbation
        // Vérifier s'il y a une étape suivante
        $nextStep = WorkflowStep::getNextStep($req['company_id'], $req['workflow_type'], $currentStep);

        if ($nextStep) {
            // On avance le curseur
            Request::updateStep($requestId, $nextStep);
            NotificationService::notifyValidators($requestId, $nextStep);
        } else {
            // Fin du jeu : tout est validé
            Request::updateStatus($requestId, 'approved');
            NotificationService::notifyRequesterApproved($requestId);
        }
    }
}