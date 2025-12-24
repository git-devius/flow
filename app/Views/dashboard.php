<?php 
/**
 * VUE DASHBOARD - DESIGN MODERNE
 */

// 1. Préparation des données (Icons, Couleurs, Calculs)
$totalRequests = 0;
$totalPending = 0;
$totalApproved = 0;

// Calcul rapide des totaux globaux
if (!empty($kpis['total_by_status'])) {
    foreach($kpis['total_by_status'] as $stat) {
        $cnt = (int)$stat['count'];
        $totalRequests += $cnt;
        if (in_array($stat['status'], ['pending_lv1', 'pending_lv2'])) {
            $totalPending += $cnt;
        }
        if ($stat['status'] === 'approved') {
            $totalApproved += $cnt;
        }
    }
}

// Configuration visuelle par type de workflow
$wfConfig = [
    'investment' => ['icon' => 'graph-up-arrow', 'color' => 'primary', 'label' => 'Investissements'],
    'vacation'   => ['icon' => 'sun-fill',       'color' => 'warning', 'label' => 'Congés'],
    'expense'    => ['icon' => 'receipt',        'color' => 'success', 'label' => 'Notes de frais'],
    'default'    => ['icon' => 'folder2-open',   'color' => 'secondary','label' => 'Autre']
];
?>

<div class="bg-primary text-white p-4 rounded-4 mb-4 shadow-sm position-relative overflow-hidden">
    <div class="position-relative z-1">
        <h2 class="fw-bold mb-1">Bonjour, <?= htmlspecialchars($user['name']) ?></h2>
        <!-- <p class="mb-0 opacity-75">Bienvenue sur votre espace Flow. Voici un aperçu de vos activités.</p> -->
    </div>
    <i class="bi bi-activity position-absolute top-50 end-0 translate-middle-y me-4 text-white opacity-10" style="font-size: 10rem;"></i>
</div>

<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4">
      <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-danger border-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row g-4">
    
    <div class="col-lg-8">
        
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-files fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small text-uppercase fw-bold">Total</h6>
                            <h3 class="mb-0 fw-bold"><?= $totalRequests ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small text-uppercase fw-bold">En Attente</h6>
                            <h3 class="mb-0 fw-bold"><?= $totalPending ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small text-uppercase fw-bold">Validés</h6>
                            <h3 class="mb-0 fw-bold"><?= $totalApproved ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-grid-fill me-2"></i>Applications disponibles</h5>
        <div class="row g-3">
            <?php 
            if (!empty($workflowTypes)):
                foreach ($workflowTypes as $typeKey => $typeName): 
                    // Vérification des droits
                    if (!\App\Services\AuthorizationService::canCreateRequest($user, $typeKey)) continue;

                    // Config visuelle
                    $conf = $wfConfig[$typeKey] ?? $wfConfig['default'];
                    
                    // Calculs spécifiques à ce workflow
                    $wfCount = 0;
                    $wfPending = 0;
                    if (!empty($kpis['total_by_type'])) {
                        foreach ($kpis['total_by_type'] as $item) {
                            if ($item['workflow_type'] === $typeKey) $wfCount += (int)$item['count'];
                        }
                    }
                    if (!empty($kpis['total_by_status'])) {
                        foreach ($kpis['total_by_status'] as $item) {
                            if ($item['workflow_type'] === $typeKey && in_array($item['status'], ['pending_lv1', 'pending_lv2'])) {
                                $wfPending += (int)$item['count'];
                            }
                        }
                    }
            ?>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-effect">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-<?= $conf['color'] ?> bg-opacity-10 text-<?= $conf['color'] ?> rounded-circle p-3 d-inline-flex">
                                <i class="bi bi-<?= $conf['icon'] ?> fs-3"></i>
                            </div>
                            <?php if ($wfPending > 0): ?>
                                <span class="badge bg-danger rounded-pill">
                                    <?= $wfPending ?> à valider
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($typeName) ?></h5>
                        <p class="text-muted small mb-4">Gérez vos demandes de <?= strtolower($typeName) ?>.</p>
                        
                        <div class="d-flex gap-2">
                            <a href="/create?workflow_type=<?= urlencode($typeKey) ?>" class="btn btn-<?= $conf['color'] ?> flex-grow-1">
                                <i class="bi bi-plus-lg"></i> Nouvelle
                            </a>
                            <a href="/list?workflow_type=<?= urlencode($typeKey) ?>" class="btn btn-light text-secondary border">
                                <i class="bi bi-list"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-2">
                        <small class="text-muted"><i class="bi bi-folder2 me-1"></i> Total dossiers : <strong><?= $wfCount ?></strong></small>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title fw-bold mb-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Vue d'ensemble</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php 
                    $statusMap = [
                        'pending_lv1' => ['label' => 'En attente N1', 'color' => 'warning', 'icon' => 'hourglass'],
                        'pending_lv2' => ['label' => 'En attente N2', 'color' => 'info', 'icon' => 'hourglass-split'],
                        'approved'    => ['label' => 'Approuvées',    'color' => 'success', 'icon' => 'check-circle'],
                        'rejected'    => ['label' => 'Rejetées',      'color' => 'danger', 'icon' => 'x-circle'],
                        'draft'       => ['label' => 'Brouillons',    'color' => 'secondary', 'icon' => 'pencil'],
                        'cancelled'   => ['label' => 'Annulées',      'color' => 'dark', 'icon' => 'slash-circle'],
                    ];

                    foreach ($statusMap as $key => $data):
                        $count = 0;
                        if (!empty($kpis['total_by_status'])) {
                            foreach($kpis['total_by_status'] as $item) {
                                if($item['status'] === $key) $count += (int)$item['count'];
                            }
                        }
                        if ($count === 0) continue; // On masque les statuts vides
                        
                        // Calcul pourcentage pour la barre
                        $percent = ($totalRequests > 0) ? round(($count / $totalRequests) * 100) : 0;
                    ?>
                    <div class="list-group-item border-0 px-0 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-muted">
                                <i class="bi bi-<?= $data['icon'] ?> text-<?= $data['color'] ?> me-2"></i><?= $data['label'] ?>
                            </span>
                            <span class="badge bg-<?= $data['color'] ?> rounded-pill"><?= $count ?></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-<?= $data['color'] ?>" role="progressbar" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if ($totalRequests === 0): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Aucune donnée pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
            <div class="card-footer bg-light border-0 p-3">
                <div class="d-grid">
                    <a href="/admin/users" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-gear-fill me-2"></i>Administration
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Petit ajout CSS inline pour l'effet de survol (optionnel) */
.hover-effect { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-effect:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>