<?php 
/**
 * Vue Détails de la demande - REFINED
 */

if (!function_exists('status_badge_refined_view')) {
    function status_badge_refined_view($status, $step = 1) {
        if ($status === 'pending') {
            return '<span class="badge bg-warning-light text-warning border border-warning border-opacity-10 px-3 py-2">Attente N' . $step . '</span>';
        }
        $badges = [
            'approved' => '<span class="badge bg-success-light text-success border border-success border-opacity-10 px-3 py-2">Approuvée</span>',
            'rejected' => '<span class="badge bg-danger-light text-danger border border-danger border-opacity-10 px-3 py-2">Rejetée</span>',
            'returned' => '<span class="badge bg-info-light text-info border border-info border-opacity-10 px-3 py-2">Renvoyée</span>',
            'cancelled' => '<span class="badge bg-light text-muted border px-3 py-2">Annulée</span>',
            'draft' => '<span class="badge bg-light text-muted border px-3 py-2">Brouillon</span>'
        ];
        return $badges[$status] ?? '<span class="badge bg-light border px-3 py-2">'.htmlspecialchars($status).'</span>';
    }
}

$currentStep = $investment['current_step'] ?? 1;
$canValidate = false;
if ($investment['status'] === 'pending') {
    $canValidate = \App\Services\AuthorizationService::canValidate($user, $investment, $currentStep);
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-5 fade-in-slide">
    <div class="d-flex align-items-center gap-3">
        <a href="/list?workflow_type=<?= $investment['workflow_type'] ?? 'investment' ?>" class="btn btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-1 text-muted small fw-500">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/list?workflow_type=all" class="text-decoration-none">Demandes</a></li>
                <li class="breadcrumb-item active" aria-current="page">#<?= $investment['id'] ?></li>
              </ol>
            </nav>
            <h1 class="h3 mb-0"><?= htmlspecialchars($investment['objective'] ?: $investment['type']) ?></h1>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        <?php if(\App\Services\AuthorizationService::canEdit($user, $investment)): ?>
            <a href="/edit?id=<?= $investment['id'] ?>" class="btn btn-warning text-dark border-0 rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-pencil me-1"></i> Modifier
            </a>
        <?php endif; ?>
        <?php if(\App\Services\AuthorizationService::canCancel($user, $investment)): ?>
        <form method="post" action="/cancel" onsubmit="return confirm('Annuler cette demande ?');">
            <?php \App\Controllers\AuthController::getCsrfInput(); ?>
            <input type="hidden" name="id" value="<?= $investment['id'] ?>">
            <button type="submit" class="btn btn-light text-danger border rounded-pill px-4 shadow-sm bg-white">
                <i class="bi bi-x-circle me-1"></i> Annuler
            </button>
        </form>
        <?php endif; ?>
        <button onclick="window.print()" class="btn btn-light border rounded-pill px-4 shadow-sm bg-white"><i class="bi bi-printer me-1"></i> Imprimer</button>
    </div>
</div>

<div class="row g-4 fade-in-slide" style="animation-delay: 0.1s;">
  <div class="col-lg-8">
    <div class="card-refined p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="h6 mb-0 fw-bold text-uppercase text-muted" style="letter-spacing: 0.05em;">Informations générales</h5>
        <?= status_badge_refined_view($investment['status'], $investment['current_step']) ?>
      </div>
      
      <div class="row g-4">
        <div class="col-md-6">
            <div class="p-3 bg-light bg-opacity-50 rounded-4 border border-light">
                <span class="text-muted small d-block mb-1">Montant total</span>
                <span class="h4 fw-bold text-dark"><?= number_format($investment['amount'], 2, ',', ' ') ?> €</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 bg-light bg-opacity-50 rounded-4 border border-light h-100 d-flex flex-column justify-content-center">
                <span class="text-muted small d-block mb-1">Demandeur</span>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle avatar-sm bg-vibrant-indigo"><?= strtoupper(substr($investment['requester'] ?? 'U', 0, 1)) ?></div>
                    <span class="small fw-bold"><?= htmlspecialchars($investment['requester']) ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-12 mt-2">
            <div class="row small g-3">
                <div class="col-6 col-md-4">
                    <span class="text-muted d-block">Workflow</span>
                    <span class="fw-bold"><?= ucfirst($investment['workflow_type'] ?? 'Investissement') ?></span>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted d-block">Pôle</span>
                    <span class="fw-bold"><?= htmlspecialchars($investment['pole_name']) ?></span>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted d-block">Société</span>
                    <span class="fw-bold"><?= htmlspecialchars($investment['company_name']) ?></span>
                </div>
                <div class="col-12 mt-3">
                    <span class="text-muted d-block mb-2">Description / Objectif</span>
                    <div class="p-3 border rounded-4 bg-white" style="line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($investment['objective'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if($investment['file_path']): ?>
        <div class="col-12 mt-4">
            <a href="/uploads/<?= htmlspecialchars($investment['file_path']) ?>" target="_blank" class="btn btn-light border w-100 py-3 rounded-4 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-paperclip fs-5"></i> Voir le document joint
            </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2 mb-3">
        <h5 class="h6 mb-0 fw-bold text-uppercase text-muted" style="letter-spacing: 0.05em;">Historique du workflow</h5>
        <span class="badge bg-light text-muted border rounded-pill"><?= count($approvals) ?></span>
    </div>

    <?php if(empty($approvals)): ?>
      <div class="card-refined p-5 text-center bg-light bg-opacity-25 border-dashed">
        <i class="bi bi-clock-history fs-2 text-muted mb-3 d-block"></i>
        <p class="text-muted small mb-0">En attente de la première approbation...</p>
      </div>
    <?php else: ?>
      <div class="timeline-refined">
          <?php foreach($approvals as $appr): ?>
          <div class="card-refined p-3 mb-3 border-0 bg-white shadow-sm d-flex flex-row align-items-center gap-3">
              <div class="kpi-icon <?php 
                if($appr['decision'] === 'approved') echo 'bg-s-light';
                elseif($appr['decision'] === 'returned') echo 'bg-info-light';
                elseif($appr['decision'] === 'modified') echo 'bg-warning-light';
                else echo 'bg-danger-light';
              ?>" style="width: 40px; height: 40px;">
                  <i class="bi bi-<?php 
                    if($appr['decision'] === 'approved') echo 'check-lg';
                    elseif($appr['decision'] === 'returned') echo 'arrow-left';
                    elseif($appr['decision'] === 'modified') echo 'pencil';
                    else echo 'x-lg';
                  ?>"></i>
              </div>
              <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start">
                      <div>
                          <h6 class="mb-0 fw-bold h6">
                            <?php if($appr['decision'] === 'modified'): ?>
                                Modification par <?= htmlspecialchars($appr['validator_name'] ?? 'le demandeur') ?>
                            <?php else: ?>
                                Étape <?= $appr['level'] ?> : <?= htmlspecialchars($appr['validator_name'] ?? 'Inconnu') ?>
                            <?php endif; ?>
                          </h6>
                          <div class="text-muted" style="font-size: 0.7rem;"><?= date('d/m/Y à H:i', strtotime($appr['decision_at'])) ?></div>
                      </div>
                      <span class="badge <?php 
                        if($appr['decision'] === 'approved') echo 'bg-success-light text-success';
                        elseif($appr['decision'] === 'returned') echo 'bg-info-light text-info';
                        elseif($appr['decision'] === 'modified') echo 'bg-warning-light text-warning';
                        else echo 'bg-danger-light text-danger';
                      ?> rounded-pill" style="font-size: 0.65rem;">
                          <?php 
                            if($appr['decision'] === 'approved') echo 'Approuvé';
                            elseif($appr['decision'] === 'returned') echo 'Renvoyé';
                            elseif($appr['decision'] === 'modified') echo 'Modifié';
                            else echo 'Rejeté';
                          ?>
                      </span>
                  </div>
                  <?php if($appr['comment']): ?>
                  <div class="mt-2 small text-muted fst-italic border-start border-2 ps-3 py-1">"<?= nl2br(htmlspecialchars($appr['comment'])) ?>"</div>
                  <?php endif; ?>
              </div>
          </div>
          <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <?php if($canValidate): ?>
      <div class="card-refined p-4 sticky-top border-primary border-opacity-25 bg-white shadow-lg" style="top: 100px; z-index: 100;">
          <h5 class="h6 mb-4 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Votre Validation</h5>
          
          <form method="post" action="/view?id=<?= $investment['id'] ?>">
            <?php \App\Controllers\AuthController::getCsrfInput(); ?>
            <input type="hidden" name="level" value="<?= $currentStep ?>">
            
            <div class="mb-4">
              <div class="d-grid gap-2">
                  <input type="radio" class="btn-check" name="decision" id="approve" value="approved" required>
                  <label class="btn btn-outline-success border rounded-3 py-2 d-flex align-items-center justify-content-center gap-2" for="approve" style="font-size: 0.85rem;">
                      <i class="bi bi-check2-circle"></i> Approuver
                  </label>

                  <input type="radio" class="btn-check" name="decision" id="returned" value="returned">
                  <label class="btn btn-outline-info border rounded-3 py-2 d-flex align-items-center justify-content-center gap-2" for="returned" style="font-size: 0.85rem;">
                      <i class="bi bi-arrow-left-circle"></i> Renvoyer au demandeur
                  </label>

                  <input type="radio" class="btn-check" name="decision" id="reject" value="rejected">
                  <label class="btn btn-outline-danger border rounded-3 py-2 d-flex align-items-center justify-content-center gap-2" for="reject" style="font-size: 0.85rem;">
                      <i class="bi bi-x-circle"></i> Rejeter
                  </label>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="form-label small fw-bold text-muted">COMMENTAIRES</label>
              <textarea name="comment" class="form-control rounded-4 bg-light bg-opacity-50 border-0 p-3" rows="4" placeholder="Optionnel..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary-refined w-100 py-2 shadow-sm">
              Confirmer la décision
            </button>
          </form>
      </div>
    <?php elseif(in_array($investment['status'], ['approved', 'rejected', 'cancelled'])): ?>
      <div class="card-refined p-5 text-center bg-white shadow-sm">
            <?php if($investment['status'] === 'approved'): ?>
                <div class="kpi-icon bg-s-light mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;"><i class="bi bi-check-all"></i></div>
                <h5 class="fw-bold mb-1">Dossier Validé</h5>
                <p class="text-muted small">Processus terminé avec succès.</p>
            <?php elseif($investment['status'] === 'rejected'): ?>
                <div class="kpi-icon bg-danger-light mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem;"><i class="bi bi-x-circle"></i></div>
                <h5 class="fw-bold mb-1 text-danger">Dossier Rejeté</h5>
                <p class="text-muted small">Le dossier a été refusé par un valideur.</p>
            <?php else: ?>
                <div class="kpi-icon bg-light text-muted mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem;"><i class="bi bi-slash-circle"></i></div>
                <h5 class="fw-bold mb-1">Dossier Annulé</h5>
                <p class="text-muted small">L'initiateur a annulé sa demande.</p>
            <?php endif; ?>
      </div>
    <?php elseif($investment['status'] === 'returned'): ?>
      <div class="card-refined p-5 text-center bg-white shadow-sm border-info border-opacity-25">
            <div class="kpi-icon bg-info-light mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem;"><i class="bi bi-arrow-left-circle text-info"></i></div>
            <h6 class="fw-bold mb-1 text-info">Renvoyé pour modification</h6>
            <p class="small text-muted mb-0">En attente de correction par le demandeur.</p>
      </div>
    <?php else: ?>
      <div class="card-refined p-5 text-center bg-white shadow-sm">
            <div class="spinner-grow text-warning mb-3" style="width: 2rem; height: 2rem;"></div>
            <h6 class="fw-bold mb-1">Validation en cours</h6>
            <p class="small text-muted mb-0">Actuellement à l'étape <?= $currentStep ?> du workflow.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.border-dashed { border: 2px dashed var(--border-color) !important; }
.timeline-refined::before {
    content: '';
    position: absolute;
    left: 40px;
    height: 100%;
    width: 2px;
    background: var(--border-color);
}
</style>