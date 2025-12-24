<?php 
/**
 * Vue Liste des demandes
 */

// ==================================================
// INITIALISATION DES VARIABLES MANQUANTES
// ==================================================
$currentWorkflow = $filters['workflow_type'] ?? 'investment';
// Correction du Warning: on définit les variables de tri avec des valeurs par défaut
$orderBy = $_GET['order_by'] ?? 'created_at';
$orderDir = $_GET['order_dir'] ?? 'DESC';

// ==================================================
// FONCTIONS LOCALES
// ==================================================

if (!function_exists('status_badge')) {
    function status_badge($status) {
        $badges = array(
            'pending_lv1' => '<span class="badge bg-warning text-dark">En attente N1</span>',
            'pending_lv2' => '<span class="badge bg-info text-dark">En attente N2</span>',
            'approved' => '<span class="badge bg-success">Approuvée</span>',
            'rejected' => '<span class="badge bg-danger">Rejetée</span>',
            'cancelled' => '<span class="badge bg-secondary">Annulée</span>',
            'draft' => '<span class="badge bg-secondary">Brouillon</span>'
        );
        return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">'.htmlspecialchars($status).'</span>';
    }
}

if (!function_exists('buildUrl')) {
    function buildUrl($params = array()) {
        $queryParams = $_GET;
        unset($queryParams['page']); // La pagination gère sa propre page
        
        $finalParams = array_merge($queryParams, $params);
        return '/list?' . http_build_query($finalParams);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>
    <i class="bi bi-list-ul"></i> Liste des demandes : <?= htmlspecialchars(ucfirst($currentWorkflow)) ?>
    <span class="badge bg-secondary"><?= $pagination->getTotalItems() ?></span>
  </h1>
  <a class="btn btn-success" href="/create?workflow_type=<?= urlencode($currentWorkflow) ?>">
    <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Créer une demande</span>
  </a>
</div>

<div class="d-md-none mb-3">
    <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
        <i class="bi bi-funnel"></i> Afficher / Masquer les filtres
    </button>
</div>

<div class="collapse d-md-block mb-3" id="filterCollapse">
    <div class="card shadow-sm">
      <div class="card-body">
        <form method="get" action="/list" id="filterForm">
          <input type="hidden" name="workflow_type" value="<?= htmlspecialchars($currentWorkflow) ?>">
          <input type="hidden" name="filter_submitted" value="1">
          
          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
              <label class="form-label small">Recherche</label>
              <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="ID, Objet, Demandeur...">
            </div>
            
            <?php if($currentWorkflow !== 'vacation'): ?>
            <div class="col-md-3">
              <label class="form-label small">Type</label>
              <select name="type" class="form-select">
                <option value="">Tous les types</option>
                <?php foreach($types as $t): ?>
                  <option value="<?= htmlspecialchars($t) ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3">
              <label class="form-label small">Statut</label>
              <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="pending_lv1" <?= $filters['status'] === 'pending_lv1' ? 'selected' : '' ?>>En attente N1</option>
                <option value="pending_lv2" <?= $filters['status'] === 'pending_lv2' ? 'selected' : '' ?>>En attente N2</option>
                <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approuvée</option>
                <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
                <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
              </select>
            </div>

            <?php if($currentWorkflow === 'investment'): ?>
            <div class="col-md-2">
              <label class="form-label small">Pôle</label>
              <select name="pole_id" class="form-select">
                <option value="">Tous les pôles</option>
                <?php foreach($poles as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= $filters['pole_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>

            <div class="col-md-1">
              <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
          </div>

          <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="open_only" id="open_only" value="1" 
                       <?= !empty($filters['open_only']) ? 'checked' : '' ?> 
                       onchange="this.form.submit();">
                <label class="form-check-label" for="open_only">
                  Afficher uniquement les demandes ouvertes
                </label>
              </div>
              
              <a href="/list?workflow_type=<?= urlencode($currentWorkflow) ?>" class="text-muted small text-decoration-none">
                  <i class="bi bi-x-circle"></i> Réinitialiser les filtres
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
</div>

<div class="table-responsive d-none d-md-block shadow-sm">
  <table class="table table-hover align-middle bg-white mb-0">
    <thead class="table-dark">
      <tr>
        <th>
            <a href="<?= buildUrl(['order_by' => 'created_at', 'order_dir' => ($orderBy == 'created_at' && $orderDir == 'ASC') ? 'DESC' : 'ASC']) ?>" class="text-white text-decoration-none">
                Date <?php if($orderBy == 'created_at'): ?><i class="bi bi-caret-<?= $orderDir == 'ASC' ? 'up' : 'down' ?>-fill"></i><?php endif; ?>
            </a>
        </th>
        <th>Libellé / Type</th>
        <?php if($currentWorkflow !== 'vacation'): ?>
            <th>
                <a href="<?= buildUrl(['order_by' => 'amount', 'order_dir' => ($orderBy == 'amount' && $orderDir == 'ASC') ? 'DESC' : 'ASC']) ?>" class="text-white text-decoration-none">
                    Montant <?php if($orderBy == 'amount'): ?><i class="bi bi-caret-<?= $orderDir == 'ASC' ? 'up' : 'down' ?>-fill"></i><?php endif; ?>
                </a>
            </th>
        <?php endif; ?>
        <th>Société</th>
        <th>Demandeur</th>
        <th>Statut</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($investments)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">Aucune demande trouvée.</td></tr>
      <?php else: ?>
        <?php foreach($investments as $inv): ?>
          <tr onclick="window.location='/view?id=<?= $inv['id'] ?>'" style="cursor:pointer;">
            <td>
                <small class="text-muted"><?= date('d/m/Y', strtotime($inv['created_at'])) ?></small><br>
                <strong>#<?= $inv['id'] ?></strong>
            </td>
            <td>
                <span class="fw-bold text-primary"><?= htmlspecialchars($inv['objective'] ?: $inv['type']) ?></span><br>
                <small class="text-muted"><?= htmlspecialchars($inv['type']) ?></small>
            </td>
            <?php if($currentWorkflow !== 'vacation'): ?>
                <td class="font-monospace fw-bold"><?= number_format($inv['amount'], 2, ',', ' ') ?> €</td>
            <?php endif; ?>
            <td><?= htmlspecialchars($inv['company_name']) ?></td>
            <td><?= htmlspecialchars($inv['requester']) ?></td>
            <td><?= status_badge($inv['status']) ?></td>
            <td class="text-end">
              <a href="/view?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-eye"></i> Détails
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="d-md-none">
    <?php if(empty($investments)): ?>
        <div class="alert alert-info text-center">Aucune demande trouvée.</div>
    <?php else: ?>
        <?php foreach($investments as $inv): ?>
            <div class="card mb-3 shadow-sm border-start border-4 <?= ($inv['status'] === 'approved' ? 'border-success' : ($inv['status'] === 'rejected' ? 'border-danger' : 'border-warning')) ?>">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small">#<?= $inv['id'] ?> - <?= date('d/m/y', strtotime($inv['created_at'])) ?></span>
                        <?= status_badge($inv['status']) ?>
                    </div>
                    <h5 class="card-title mb-1 text-primary"><?= htmlspecialchars($inv['objective'] ?: $inv['type']) ?></h5>
                    <p class="card-text mb-2 text-secondary small">
                        <strong>Société :</strong> <?= htmlspecialchars($inv['company_name']) ?><br>
                        <strong>Par :</strong> <?= htmlspecialchars($inv['requester']) ?><br>
                        <?php if($currentWorkflow !== 'vacation'): ?>
                            <strong>Montant :</strong> <?= number_format($inv['amount'], 2) ?> €
                        <?php endif; ?>
                    </p>
                    <div class="d-grid">
                        <a href="/view?id=<?= $inv['id'] ?>" class="btn btn-outline-primary btn-sm">Voir les détails</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($pagination->getTotalPages() > 1): ?>
<nav class="mt-4">
  <ul class="pagination justify-content-center">
    <li class="page-item <?= ($pagination->getPage() <= 1) ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= buildUrl(['page' => $pagination->getPage() - 1]) ?>">Précédent</a>
    </li>
    <?php for ($i = 1; $i <= $pagination->getTotalPages(); $i++): ?>
      <li class="page-item <?= ($i == $pagination->getPage()) ? 'active' : '' ?>">
        <a class="page-link" href="<?= buildUrl(['page' => $i]) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= ($pagination->getPage() >= $pagination->getTotalPages()) ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= buildUrl(['page' => $pagination->getPage() + 1]) ?>">Suivant</a>
    </li>
  </ul>
</nav>
<?php endif; ?>