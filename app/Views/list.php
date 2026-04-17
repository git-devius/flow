<?php 
/**
 * Vue Liste des demandes - REFINED
 */

$currentWorkflow = $filters['workflow_type'] ?? 'investment';
$orderBy = $_GET['order_by'] ?? 'created_at';
$orderDir = $_GET['order_dir'] ?? 'DESC';

if (!function_exists('status_badge_refined')) {
    function status_badge_refined($status, $step = null) {
        if ($status === 'pending') {
            $stepLabel = $step ? ' N' . $step : '';
            return '<span class="badge bg-warning-light text-warning border border-warning border-opacity-10 px-2 py-1">Attente' . $stepLabel . '</span>';
        }
        $badges = [
            'approved' => '<span class="badge bg-success-light text-success border border-success border-opacity-10 px-2 py-1">Approuvée</span>',
            'rejected' => '<span class="badge bg-danger-light text-danger border border-danger border-opacity-10 px-2 py-1">Rejetée</span>',
            'returned' => '<span class="badge bg-info-light text-info border border-info border-opacity-10 px-2 py-1">Renvoyée</span>',
            'cancelled' => '<span class="badge bg-light text-muted border px-2 py-1">Annulée</span>',
            'draft' => '<span class="badge bg-light text-muted border px-2 py-1">Brouillon</span>'
        ];
        return $badges[$status] ?? '<span class="badge bg-light border">'.htmlspecialchars($status).'</span>';
    }
}

if (!function_exists('buildUrl')) {
    function buildUrl($params = array()) {
        $queryParams = $_GET;
        unset($queryParams['page']);
        $finalParams = array_merge($queryParams, $params);
        return '/list?' . http_build_query($finalParams);
    }
}

$workflowTitleMap = ['investment' => 'Investissements', 'vacation' => 'Congés', 'expense' => 'Notes de frais', 'all' => 'Toutes'];
$workflowTitle = $workflowTitleMap[$currentWorkflow] ?? ucfirst($currentWorkflow);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 fade-in-slide" style="position: relative; z-index: 1050;">
  <div>
    <h1 class="h3 mb-1">Liste des demandes</h1>
    <p class="text-muted small mb-0">Catégorie : <strong><?= htmlspecialchars($workflowTitle) ?></strong> &bull; Total : <?= $pagination->getTotalItems() ?></p>
  </div>
  
  <div class="d-flex gap-2">
      <button class="btn btn-light border shadow-sm btn-sm px-3" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
          <i class="bi bi-funnel me-1"></i> Filtres
      </button>
      <?php if($currentWorkflow === 'all'): ?>
          <div class="dropdown">
            <button class="btn btn-primary-refined btn-sm px-4 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-plus-lg me-1"></i> Créer
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                <?php foreach(\App\Models\Request::WORKFLOW_TYPES as $type => $label): 
                    if (!\App\Services\AuthorizationService::canCreateRequest($user, $type)) continue;
                ?>
                <li><a class="dropdown-item py-2" href="/create?workflow_type=<?= $type ?>"><?= htmlspecialchars($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
          </div>
      <?php else: ?>
          <a class="btn btn-primary-refined btn-sm px-4 shadow-sm" href="/create?workflow_type=<?= urlencode($currentWorkflow) ?>">
            <i class="bi bi-plus-lg me-1"></i> Créer une demande
          </a>
      <?php endif; ?>
  </div>
</div>

<div class="collapse mb-4" id="filterCollapse">
    <div class="card-refined p-4">
        <form method="get" action="/list" id="filterForm">
          <?php if($currentWorkflow !== 'all'): ?><input type="hidden" name="workflow_type" value="<?= htmlspecialchars($currentWorkflow) ?>"><?php endif; ?>
          <input type="hidden" name="filter_submitted" value="1">
          
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted">Recherche</label>
              <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="ID, Objet, Demandeur...">
            </div>
            
            <?php if($currentWorkflow === 'all'): ?>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted">Type de Workflow</label>
              <select name="workflow_type" class="form-select">
                <option value="all" <?= $currentWorkflow === 'all' ? 'selected' : '' ?>>Tous les workflows</option>
                <?php foreach(\App\Models\Request::WORKFLOW_TYPES as $type => $label): 
                    if (!\App\Services\AuthorizationService::canCreateRequest($user, $type)) continue;
                ?>
                <option value="<?= $type ?>" <?= $currentWorkflow === $type ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php elseif($currentWorkflow !== 'vacation'): ?>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted">Libellé</label>
              <select name="type" class="form-select">
                <option value="">Tous les libellés</option>
                <?php foreach($types as $t): ?>
                  <option value="<?= htmlspecialchars($t) ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3">
              <label class="form-label small fw-bold text-muted d-flex justify-content-between">
                <span>Statuts</span>
                <span>
                  <a href="#" class="text-decoration-none small fw-normal" onclick="selectAllStatuses(true); return false;">Tous</a> | 
                  <a href="#" class="text-decoration-none small fw-normal text-muted" onclick="selectAllStatuses(false); return false;">Aucun</a>
                </span>
              </label>
              <select name="status[]" id="statusSelect" class="form-select" multiple size="3" style="min-height: 45px;">
                <?php 
                $selectedStatuses = is_array($filters['status']) ? $filters['status'] : ($filters['status'] ? [$filters['status']] : []);
                ?>
                <option value="pending" <?= in_array('pending', $selectedStatuses) ? 'selected' : '' ?>>En attente</option>
                <option value="returned" <?= in_array('returned', $selectedStatuses) ? 'selected' : '' ?>>Renvoyée</option>
                <option value="approved" <?= in_array('approved', $selectedStatuses) ? 'selected' : '' ?>>Approuvée</option>
                <option value="rejected" <?= in_array('rejected', $selectedStatuses) ? 'selected' : '' ?>>Rejetée</option>
                <option value="cancelled" <?= in_array('cancelled', $selectedStatuses) ? 'selected' : '' ?>>Annulée</option>
              </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary-refined w-100 py-2">Appliquer</button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <a href="/list?workflow_type=<?= urlencode($currentWorkflow) ?>" class="btn btn-light border w-100 py-2" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
          </div>
        </form>
    </div>
</div>

<!-- MOBILE VIEW (CARDS) -->
<div class="d-lg-none mt-4 fade-in-slide">
    <?php if(empty($investments)): ?>
        <div class="card-refined p-5 text-center text-muted">Aucun dossier trouvé</div>
    <?php else: ?>
        <?php foreach($investments as $inv): ?>
            <div class="card-refined mb-3 p-3 position-relative" onclick="window.location='/view?id=<?= $inv['id'] ?>'" style="cursor:pointer;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="small fw-bold text-muted me-2">#<?= $inv['id'] ?></span>
                        <span class="small text-muted me-2"><?= date('d/m/y', strtotime($inv['created_at'])) ?></span>
                        <a class="text-secondary opacity-75 d-inline-block px-1" role="button" data-bs-toggle="popover" data-bs-trigger="click" data-bs-html="true" data-bs-title="Historique #<?= $inv['id'] ?>" data-bs-content="<?= htmlspecialchars(\App\Helpers\HistoryHelper::getTimelineHtml($inv), ENT_QUOTES) ?>" data-bs-animation="false" onclick="event.stopPropagation();">
                            <i class="bi bi-clock-history small"></i>
                        </a>
                    </div>
                    <?php if($currentWorkflow === 'all'): ?>
                        <?php 
                        $wfColors = ['investment' => 'primary', 'vacation' => 'warning', 'expense' => 'success'];
                        $color = $wfColors[$inv['workflow_type']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> rounded-pill px-2 small">
                            <?= ucfirst($inv['workflow_type']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($inv['objective'] ?: $inv['type']) ?></h6>
                <div class="text-muted small mb-3"><?= htmlspecialchars($inv['type']) ?></div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle bg-light text-secondary" style="width: 24px; height: 24px; font-size: 0.6rem;"><?= substr($inv['requester'],0,1) ?></div>
                        <span class="small"><?= htmlspecialchars($inv['requester']) ?></span>
                    </div>
                    <div class="text-end">
                        <?php if($currentWorkflow !== 'vacation'): ?>
                            <div class="fw-bold text-dark mb-1"><?= number_format($inv['amount'], 2, ',', ' ') ?> €</div>
                        <?php endif; ?>
                        <?= status_badge_refined($inv['status'], $inv['current_step']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- DESKTOP VIEW (TABLE) -->
<div class="card-refined overflow-hidden fade-in-slide d-none d-lg-block" style="animation-delay: 0.1s;">
  <div class="table-responsive">
    <table class="table-refined">
      <thead>
        <tr>
          <th>ID</th>
          <th><a href="<?= buildUrl(['order_by' => 'created_at', 'order_dir' => ($orderBy == 'created_at' && $orderDir == 'ASC') ? 'DESC' : 'ASC']) ?>" class="text-decoration-none text-secondary">Date</a></th>
          <?php if($currentWorkflow === 'all'): ?><th>Workspace</th><?php endif; ?>
          <th>Libellé</th>
          <?php if($currentWorkflow !== 'vacation'): ?><th>Montant</th><?php endif; ?>
          <th>Demandeur</th>
          <th>Statut</th>
          <th class="text-center" style="width: 50px;">Hist.</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($investments)): ?>
          <tr><td colspan="10" class="text-center py-5 text-muted">Aucun dossier trouvé</td></tr>
        <?php else: ?>
          <?php foreach($investments as $inv): ?>
            <tr onclick="window.location='/view?id=<?= $inv['id'] ?>'" style="cursor:pointer;" class="align-middle">
              <td class="small fw-bold text-muted">#<?= $inv['id'] ?></td>
              <td class="small"><?= date('d/m/y', strtotime($inv['created_at'])) ?></td>
              <?php if($currentWorkflow === 'all'): ?>
              <td>
                  <?php 
                  $wfLabels = ['investment' => 'Invest.', 'vacation' => 'Congés', 'expense' => 'Frais'];
                  $wfColors = ['investment' => 'primary', 'vacation' => 'warning', 'expense' => 'success'];
                  $color = $wfColors[$inv['workflow_type']] ?? 'secondary';
                  ?>
                  <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?> border-opacity-10 rounded-pill px-2" style="font-size: 0.65rem;">
                      <?= $wfLabels[$inv['workflow_type']] ?? $inv['workflow_type'] ?>
                  </span>
              </td>
              <?php endif; ?>
              <td>
                  <div class="fw-bold text-dark mb-0" style="font-size: 0.85rem;"><?= htmlspecialchars($inv['objective'] ?: $inv['type']) ?></div>
                  <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($inv['type']) ?></div>
              </td>
              <?php if($currentWorkflow !== 'vacation'): ?>
                  <td class="fw-600"><?= number_format($inv['amount'], 2, ',', ' ') ?> €</td>
              <?php endif; ?>
              <td>
                  <div class="d-flex align-items-center gap-2">
                      <div class="avatar-circle bg-light text-secondary" style="width: 24px; height: 24px; font-size: 0.6rem;"><?= substr($inv['requester'],0,1) ?></div>
                      <span class="small"><?= htmlspecialchars($inv['requester']) ?></span>
                  </div>
              </td>
              <td><?= status_badge_refined($inv['status'], $inv['current_step']) ?></td>
              <td class="text-center" onclick="event.stopPropagation();">
                <a class="text-secondary opacity-75 custom-hover-opacity d-inline-block" style="width: 24px; text-align: center; line-height: 1; vertical-align: middle;" role="button" data-bs-toggle="popover" data-bs-trigger="click" data-bs-html="true" data-bs-title="Historique #<?= $inv['id'] ?>" data-bs-content="<?= htmlspecialchars(\App\Helpers\HistoryHelper::getTimelineHtml($inv), ENT_QUOTES) ?>" data-bs-animation="false">
                    <i class="bi bi-clock-history fs-5"></i>
                </a>
              </td>
              <td class="text-end">
                <a href="/view?id=<?= $inv['id'] ?>" class="btn btn-light btn-sm rounded-circle border p-0 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-chevron-right small"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($pagination->getTotalPages() > 1): ?>
<nav class="mt-4">
  <ul class="pagination pagination-sm justify-content-center border-0">
    <li class="page-item <?= ($pagination->getPage() <= 1) ? 'disabled' : '' ?>">
      <a class="page-link shadow-none border me-2 px-3" style="border-radius: 8px;" href="<?= buildUrl(['page' => $pagination->getPage() - 1]) ?>">Précédent</a>
    </li>
    <?php for ($i = 1; $i <= $pagination->getTotalPages(); $i++): ?>
      <li class="page-item <?= ($i == $pagination->getPage()) ? 'active' : '' ?>">
        <a class="page-link shadow-none border mx-1" style="width: 32px; height:32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;" href="<?= buildUrl(['page' => $i]) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= ($pagination->getPage() >= $pagination->getTotalPages()) ? 'disabled' : '' ?>">
      <a class="page-link shadow-none border ms-2 px-3" style="border-radius: 8px;" href="<?= buildUrl(['page' => $pagination->getPage() + 1]) ?>">Suivant</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<style>
.fw-600 { font-weight: 600; }
.pagination .page-item.active .page-link { background-color: var(--primary); border-color: var(--primary); color: white; }
.custom-hover-opacity:hover { opacity: 1 !important; color: var(--primary) !important; }
@media (min-width: 992px) {
    .table-responsive { overflow: visible !important; }
}
.popover { max-width: 350px !important; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; }
.popover-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 0.85rem; padding: 12px 16px; border-radius: 12px 12px 0 0; }
.popover-body { max-height: 250px; overflow-y: auto; padding: 16px; }
/* Personnalisation de la scrollbar du popover */
.popover-body::-webkit-scrollbar { width: 4px; }
.popover-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body',
            boundary: 'viewport'
        });
    });

    // Gestion de la fermeture au clic ailleurs
    document.addEventListener('click', function (e) {
        if (!popoverList) return;
        if (e.target.closest('[data-bs-toggle="popover"]') || e.target.closest('.popover')) {
            return;
        }
        popoverList.forEach(p => p.hide());
    });
});
function selectAllStatuses(state) {
    const select = document.getElementById('statusSelect');
    for (let i = 0; i < select.options.length; i++) {
        select.options[i].selected = state;
    }
}
</script>