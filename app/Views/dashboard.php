<?php 
/**
 * VUE DASHBOARD - MODERN REFINED
 */

$totalRequests = 0;
$totalPending = 0;
$totalApproved = 0;

if (!empty($kpis['total_by_status'])) {
    foreach($kpis['total_by_status'] as $stat) {
        $cnt = (int)$stat['count'];
        $totalRequests += $cnt;
        if ($stat['status'] === 'pending') $totalPending += $cnt;
        if ($stat['status'] === 'approved') $totalApproved += $cnt;
    }
}

$wfConfig = [
    'investment' => ['icon' => 'graph-up-arrow', 'color' => 'primary', 'label' => 'Investissements'],
    'vacation'   => ['icon' => 'sun',            'color' => 'warning', 'label' => 'Congés'],
    'expense'    => ['icon' => 'receipt',        'color' => 'success', 'label' => 'Notes de frais'],
    'default'    => ['icon' => 'folder2-open',   'color' => 'secondary','label' => 'Autre']
];
?>

<div class="row g-2 fade-in-slide">
    <!-- APPLICATIONS -->
    <div class="col-12 mb-2">
        <div class="card-refined overflow-hidden">
            <div class="card-header-refined py-2 px-3 d-flex justify-content-between align-items-center bg-light bg-opacity-50">
                <h5 class="mb-0 fw-bold text-uppercase text-muted" style="letter-spacing: 0.05em; font-size: 0.7rem;">
                    <i class="bi bi-grid-fill me-2 text-primary opacity-50"></i>Applications
                </h5>
                <a href="/list?workflow_type=all" class="text-primary text-decoration-none fw-bold" style="font-size: 0.7rem;">Tout voir <i class="bi bi-arrow-right small"></i></a>
            </div>
            <div class="card-body p-2 p-md-3">
                <div class="row g-2">
                    <?php 
                    if (!empty($workflowTypes)):
                        foreach ($workflowTypes as $typeKey => $typeName): 
                            if (!\App\Services\AuthorizationService::canCreateRequest($user, $typeKey)) continue;
                            $conf = $wfConfig[$typeKey] ?? $wfConfig['default'];
                            $gradient = match($typeKey) {
                                'investment' => 'linear-gradient(135deg, #6366f1, #4f46e5)',
                                'vacation'   => 'linear-gradient(135deg, #f59e0b, #d97706)',
                                'expense'    => 'linear-gradient(135deg, #10b981, #059669)',
                                default      => 'linear-gradient(135deg, #94a3b8, #64748b)'
                            };
                            $wfCount = 0; $wfPending = 0;
                            if (!empty($kpis['total_by_type'])) {
                                foreach ($kpis['total_by_type'] as $item) {
                                    if ($item['workflow_type'] === $typeKey) $wfCount += (int)$item['count'];
                                }
                            }
                            if (!empty($kpis['total_by_status'])) {
                                foreach ($kpis['total_by_status'] as $item) {
                                    if ($item['workflow_type'] === $typeKey && $item['status'] === 'pending') $wfPending += (int)$item['count'];
                                }
                            }
                    ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="card-refined h-100 p-3 d-flex flex-column transition-all bg-white border-0 shadow-sm rounded-4 position-relative overflow-hidden group">
                            <?php if ($wfPending > 0): ?>
                                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-danger shadow-sm border border-white border-2 px-2 py-1 flash-soft" style="font-size: 0.6rem; z-index: 2;">
                                    <?= $wfPending ?>
                                </span>
                            <?php endif; ?>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="kpi-icon shadow-sm flex-shrink-0 d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px; background: <?= $gradient ?>; border-radius: 12px; color: white;">
                                    <i class="bi bi-<?= $conf['icon'] ?> fs-5"></i>
                                </div>
                                <div class="ms-3 overflow-hidden">
                                    <h6 class="fw-bold mb-0 text-dark small text-truncate" style="letter-spacing: -0.01em;"><?= htmlspecialchars($typeName) ?></h6>
                                    <span class="text-muted d-block mt-0" style="font-size: 0.6rem;"><?= $wfCount ?> dossier(s)</span>
                                </div>
                            </div>

                            <div class="mt-auto d-flex gap-1 justify-content-between">
                                <a href="/list?workflow_type=<?= urlencode($typeKey) ?>" class="btn btn-light btn-sm flex-grow-1 border-0 rounded-pill fw-bold text-muted transition-all" style="font-size: 0.65rem; background: #f8fafc;">
                                    Liste
                                </a>
                                <a href="/create?workflow_type=<?= urlencode($typeKey) ?>" class="btn btn-primary btn-sm flex-grow-1 rounded-pill fw-bold shadow-sm transition-all" style="font-size: 0.65rem;">
                                    + Créer
                                </a>
                            </div>
                            
                            <!-- Subtle decorative element -->
                            <div class="position-absolute bottom-0 start-0 w-100 h-1 bg-primary opacity-0 transition-all group-hover-opacity-100" style="height: 3px; background: <?= $gradient ?> !important;"></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- REPARTITION COMPACTE -->
    <div class="col-12">
        <div class="card-refined overflow-hidden">
            <div class="card-header-refined py-2 px-3 bg-light bg-opacity-50">
                <h5 class="mb-0 fw-bold text-uppercase text-muted" style="letter-spacing: 0.05em; font-size: 0.7rem;">
                    <i class="bi bi-pie-chart me-2 text-primary opacity-50"></i>État des dossiers
                </h5>
            </div>
            <div class="card-body p-2 p-md-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php 
                    $countsByPseudoStatus = [];
                    if (!empty($kpis['total_by_status'])) {
                        foreach($kpis['total_by_status'] as $item) {
                            $pseudoKey = ($item['status'] === 'pending') ? 'pending_' . $item['current_step'] : $item['status'];
                            if (!isset($countsByPseudoStatus[$pseudoKey])) $countsByPseudoStatus[$pseudoKey] = 0;
                            $countsByPseudoStatus[$pseudoKey] += (int)$item['count'];
                        }
                    }
                    ksort($countsByPseudoStatus);

                    foreach ($countsByPseudoStatus as $key => $count):
                        if ($count === 0) continue;
                        if (strpos($key, 'pending_') === 0) {
                            $step = str_replace('pending_', '', $key);
                            $data = ['label' => 'Valid. N' . $step, 'color' => 'warning', 'icon' => 'clock-history'];
                            $bgSoft = 'rgba(245, 158, 11, 0.1)';
                        } else {
                            $map = [
                                'approved'  => ['label' => 'Approuvées', 'color' => 'success', 'icon' => 'check-circle', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                                'rejected'  => ['label' => 'Rejetées', 'color' => 'danger', 'icon' => 'x-circle', 'bg' => 'rgba(239, 68, 68, 0.1)'],
                                'draft'     => ['label' => 'Brouillons', 'color' => 'secondary', 'icon' => 'pencil', 'bg' => 'rgba(107, 114, 128, 0.1)'],
                                'cancelled' => ['label' => 'Annulées', 'color' => 'secondary', 'icon' => 'slash-circle', 'bg' => 'rgba(107, 114, 128, 0.1)'],
                            ];
                            if (!isset($map[$key])) continue; 
                            $data = $map[$key];
                            $bgSoft = $data['bg'];
                        }
                        
                        $statusRaw = (strpos($key, 'pending_') === 0) ? 'pending' : $key;
                        $stepParam = (strpos($key, 'pending_') === 0) ? '&step=' . str_replace('pending_', '', $key) : '';
                        $listUrl = '/list?workflow_type=all&status=' . $statusRaw . $stepParam;
                    ?>
                    <a href="<?= $listUrl ?>" class="card-refined text-decoration-none transition-all bg-white border shadow-sm rounded-4 p-2 d-flex align-items-center" style="min-width: 140px; border-left: 3px solid var(--bs-<?= $data['color'] ?>) !important;">
                        <div class="kpi-icon d-flex align-items-center justify-content-center flex-shrink-0" 
                             style="width: 28px; height: 28px; background: <?= $bgSoft ?>; border-radius: 8px; color: var(--bs-<?= $data['color'] ?>);">
                            <i class="bi bi-<?= $data['icon'] ?> small"></i>
                        </div>
                        <div class="ms-2">
                            <div class="text-muted fw-bold text-uppercase" style="font-size: 0.55rem; letter-spacing: 0.05em;"><?= $data['label'] ?></div>
                            <div class="fw-bold text-dark h6 mb-0"><?= $count ?> <span class="text-muted small" style="font-size: 0.6rem;">dossier(s)</span></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    
                    <?php if (empty($countsByPseudoStatus) || array_sum($countsByPseudoStatus) === 0): ?>
                        <div class="text-center w-100 py-3 text-muted small opacity-50 fst-italic">
                            Aucune donnée trouvée
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Utilities specifically for dashboard layout */
.fw-500 { font-weight: 500; }
.bg-danger-light { background: #fef2f2; color: #ef4444; }
.transition-all:hover { border-color: var(--primary); transform: translateY(-3px); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important; }
</style>