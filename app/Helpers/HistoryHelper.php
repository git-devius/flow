<?php
namespace App\Helpers;

use App\Models\Approval;
use App\Models\WorkflowStep;
use App\Models\User;

class HistoryHelper {
    public static function getTimelineHtml($inv) {
        $history = Approval::forInvestment($inv['id']);
        $timelineHtml = '<div class="small text-start">';
        
        // Statut étendu
        $statusHtml = status_badge_refined($inv['status'], $inv['current_step']);
        if ($inv['status'] === 'pending') {
            $vId = WorkflowStep::findValidator($inv['company_id'], $inv['workflow_type'], $inv['current_step']);
            if ($vId) {
                $vUser = User::find($vId);
                if ($vUser) {
                    $statusHtml .= '<br><span class="text-muted" style="font-size:0.8em;">Attendu de : <strong>' . htmlspecialchars($vUser['name']) . '</strong></span>';
                }
            }
        }
        
        $timelineHtml .= '<div class="mb-3 pb-2 border-bottom">' . $statusHtml . '</div>';
        $timelineHtml .= '<div class="fw-bold mb-2 small text-uppercase text-muted" style="font-size:0.65em; letter-spacing:0.05em;">Historique des actions</div>';
        
        if (empty($history)) {
            $timelineHtml .= '<div class="text-muted fst-italic">Aucune action</div>';
        } else {
            foreach ($history as $h) {
                $icon = 'bi-check-circle text-success';
                $decLabels = ['approved' => 'Approuvé', 'rejected' => 'Refusé', 'returned' => 'Renvoyé', 'modified' => 'Modifié'];
                if ($h['decision'] === 'rejected') $icon = 'bi-x-circle text-danger';
                elseif ($h['decision'] === 'returned') $icon = 'bi-arrow-left-circle text-info';
                elseif ($h['decision'] === 'modified') $icon = 'bi-pencil text-warning';
                
                $dateStr = date('d/m H:i', strtotime($h['decision_at']));
                $valName = htmlspecialchars($h['validator_name'] ?: 'Système');
                $dec = $decLabels[$h['decision']] ?? htmlspecialchars($h['decision']);
                $levelBadge = ($h['level'] > 0) ? '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 ms-1" style="font-size:0.7em;">N'.$h['level'].'</span>' : '';
                $commentHtml = !empty($h['comment']) ? '<div class="mt-1 ps-3 border-start text-dark" style="font-size:0.9em; font-style: italic;">' . htmlspecialchars($h['comment']) . '</div>' : '';
                
                $timelineHtml .= '<div class="mb-3"><i class="bi '.$icon.'"></i> <strong>'.$valName.'</strong> '.$levelBadge.' <span class="badge bg-light text-dark border">'.$dec.'</span><br><span class="text-muted" style="font-size:0.75em;"><i class="bi bi-clock me-1"></i>'.$dateStr.'</span>'.$commentHtml.'</div>';
            }
        }
        $timelineHtml .= '</div>';
        return $timelineHtml;
    }
}
