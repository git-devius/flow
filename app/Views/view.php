<?php 
// Plus de fonction wrapper, on utilise directement les variables du contrôleur
// $investment, $approvals, $user, $error sont disponibles ici.

// Helper pour les badges de statut
if (!function_exists('status_badge')) {
    function status_badge($status, $step = 1) {
        switch($status) {
            case 'pending':
                return '<span class="badge bg-warning text-dark">En attente (Étape '.$step.')</span>';
            case 'approved':
                return '<span class="badge bg-success">Approuvée</span>';
            case 'rejected':
                return '<span class="badge bg-danger">Rejetée</span>';
            case 'draft':
                return '<span class="badge bg-secondary">Brouillon</span>';
            case 'cancelled':
                return '<span class="badge bg-dark">Annulée</span>';
            default:
                return '<span class="badge bg-secondary">'.htmlspecialchars($status).'</span>';
        }
    }
}

// Vérification des droits de validation via le Service (Architecture N-Niveaux)
$currentStep = $investment['current_step'] ?? 1;
$canValidate = false;

if ($investment['status'] === 'pending') {
    $canValidate = \App\Services\AuthorizationService::canValidate($user, $investment, $currentStep);
}
?>

<?php if(isset($_SESSION['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($_SESSION['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?= htmlspecialchars($_SESSION['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/list?workflow_type=<?= $investment['workflow_type'] ?? 'investment' ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h1 class="h3 mb-0">Demande #<?= $investment['id'] ?> <span class="text-muted fs-5 mx-2">|</span> <?= htmlspecialchars($investment['type']) ?></h1>
        <p class="text-muted small mb-0">
            Créée le <?= date('d/m/Y à H:i', strtotime($investment['created_at'])) ?> 
            par <strong><?= htmlspecialchars($investment['requester']) ?></strong>
        </p>
    </div>
    
    <?php if(\App\Services\AuthorizationService::canCancel($user, $investment)): ?>
    <form method="post" action="/cancel" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?');">
        <?php \App\Controllers\AuthController::getCsrfInput(); ?>
        <input type="hidden" name="id" value="<?= $investment['id'] ?>">
        <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-x-circle"></i> Annuler la demande
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-4 shadow-sm border-0">
      <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-file-text me-2"></i>Détails de la demande</h6>
        <?= status_badge($investment['status'], $investment['current_step']) ?>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">Workflow</dt>
          <dd class="col-sm-8 fw-medium"><?= ucfirst($investment['workflow_type'] ?? 'Investissement') ?></dd>

          <dt class="col-sm-4 text-muted">Pôle</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($investment['pole_name']) ?></dd>
          
          <dt class="col-sm-4 text-muted">Société</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($investment['company_name']) ?></dd>
          
          <dt class="col-sm-4 text-muted">Prévu au budget</dt>
          <dd class="col-sm-8">
            <?= $investment['budget_planned'] ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Oui</span>' : '<span class="text-secondary">Non</span>' ?>
          </dd>
          
          <dt class="col-sm-4 text-muted">Montant</dt>
          <dd class="col-sm-8"><strong class="text-primary fs-5"><?= number_format($investment['amount'], 2, ',', ' ') ?> €</strong></dd>
          
          <dt class="col-sm-4 text-muted">Objet</dt>
          <dd class="col-sm-8 bg-light p-3 rounded"><?= nl2br(htmlspecialchars($investment['objective'])) ?></dd>
          
          <dt class="col-sm-4 text-muted mt-2">Début & durée</dt>
          <dd class="col-sm-8 mt-2"><?= htmlspecialchars($investment['start_date_duration']) ?></dd>
          
          <?php if($investment['file_path']): ?>
            <dt class="col-sm-4 text-muted mt-2">Pièce jointe</dt>
            <dd class="col-sm-8 mt-2">
              <a href="/uploads/<?= htmlspecialchars($investment['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-paperclip"></i> Télécharger le document
              </a>
            </dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <h5 class="fw-bold mb-3">Historique des validations</h5>
    <?php if(empty($approvals)): ?>
      <div class="alert alert-light border shadow-sm text-center py-4">
        <i class="bi bi-clock-history fs-4 text-muted mb-2 d-block"></i>
        Aucune validation enregistrée pour le moment.
      </div>
    <?php else: ?>
      <div class="timeline">
      <?php foreach($approvals as $appr): ?>
        <div class="card shadow-sm mb-3 border-0 border-start border-4 border-<?= $appr['decision'] === 'approved' ? 'success' : 'danger' ?>">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1 fw-bold">Étape <?= $appr['level'] ?> : <?= htmlspecialchars($appr['validator_name'] ?? 'Inconnu') ?></h6>
                <small class="text-muted">
                  <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y à H:i', strtotime($appr['decision_at'])) ?>
                </small>
              </div>
              <span class="badge bg-<?= $appr['decision'] === 'approved' ? 'success' : 'danger' ?> rounded-pill">
                <?= $appr['decision'] === 'approved' ? 'Approuvé' : 'Rejeté' ?>
              </span>
            </div>
            <?php if($appr['comment']): ?>
              <div class="mt-3 p-2 bg-light rounded fst-italic border-start border-3">
                "<?= nl2br(htmlspecialchars($appr['comment'])) ?>"
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <?php if($canValidate): ?>
      <div class="card shadow border-0 mb-4 sticky-top" style="top: 100px; z-index: 100;">
        <div class="card-header bg-primary text-white py-3">
          <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Validation requise</h6>
        </div>
        <div class="card-body bg-light">
          <p class="small text-muted mb-3">
            Vous êtes invité à valider cette demande en tant que validateur de l'<strong>étape <?= $currentStep ?></strong>.
          </p>
          
          <form method="post" action="/view?id=<?= $investment['id'] ?>">
            <?php \App\Controllers\AuthController::getCsrfInput(); ?>
            
            <input type="hidden" name="level" value="<?= $currentStep ?>">
            
            <div class="mb-3">
              <label class="form-label fw-bold">Votre décision</label>
              <div class="d-grid gap-2">
                  <input type="radio" class="btn-check" name="decision" id="approve" value="approved" required>
                  <label class="btn btn-outline-success" for="approve"><i class="bi bi-check-lg me-2"></i>Approuver</label>

                  <input type="radio" class="btn-check" name="decision" id="reject" value="rejected">
                  <label class="btn btn-outline-danger" for="reject"><i class="bi bi-x-lg me-2"></i>Rejeter</label>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Commentaire (Facultatif)</label>
              <textarea name="comment" class="form-control" rows="3" placeholder="Raison du rejet ou remarque..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
              Confirmer la décision
            </button>
          </form>
        </div>
      </div>
    <?php elseif(in_array($investment['status'], ['approved', 'rejected', 'cancelled'])): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
            <?php if($investment['status'] === 'approved'): ?>
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-2 text-success fw-bold">Demande Approuvée</h5>
                <p class="text-muted small">Le processus de validation est terminé.</p>
            <?php elseif($investment['status'] === 'rejected'): ?>
                <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                <h5 class="mt-2 text-danger fw-bold">Demande Rejetée</h5>
                <p class="text-muted small">Le processus a été arrêté.</p>
            <?php else: ?>
                <i class="bi bi-slash-circle-fill text-dark" style="font-size: 3rem;"></i>
                <h5 class="mt-2 text-dark fw-bold">Demande Annulée</h5>
            <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="card border-0 shadow-sm bg-light">
        <div class="card-body text-center py-4">
            <i class="bi bi-hourglass-split text-warning mb-2" style="font-size: 2rem;"></i>
            <h6 class="fw-bold text-muted">En attente de validation</h6>
            <p class="small text-muted mb-0">La demande est actuellement à l'étape <?= $currentStep ?>.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>