<?php 
/**
 * VUE CRÉATION - DESIGN MODERNE
 */

// Récupération des variables
$currentWorkflowType = $workflowType ?? 'investment';
$workflowName = \App\Models\Request::WORKFLOW_TYPES[$currentWorkflowType] ?? 'Nouvelle Demande';

// Icônes par type pour l'en-tête
$icons = [
    'investment' => 'graph-up-arrow',
    'vacation' => 'sun',
    'expense' => 'receipt',
    'default' => 'pencil-square'
];
$currentIcon = $icons[$currentWorkflowType] ?? $icons['default'];

// Mappage des templates
$templateMap = [
    'investment' => __DIR__.'/create_investment_form.php',
    'vacation'   => __DIR__.'/create_vacation_form.php',
    'expense'    => __DIR__.'/create_expense_form.php',
];
$currentForm = $templateMap[$currentWorkflowType] ?? null;
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">
            <i class="bi bi-<?= $currentIcon ?> text-primary me-2"></i><?= htmlspecialchars($workflowName) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small bg-transparent p-0">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none text-muted">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nouveau dossier</li>
            </ol>
        </nav>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-lg me-1"></i> Annuler
        </a>
    </div>
</div>

<?php if(isset($error) && $error): ?>
  <div class="alert alert-danger shadow-sm border-0 border-start border-danger border-4 d-flex align-items-center">
    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
    <div><?= htmlspecialchars($error) ?></div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                
                <?php if($currentForm && file_exists($currentForm)): ?>
                    
                    <form method="post" action="/create" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                        <input type="hidden" name="workflow_type" value="<?= htmlspecialchars($currentWorkflowType) ?>">
                        
                        <?php include $currentForm; ?>

                        <div class="d-flex justify-content-end align-items-center gap-3 mt-5 pt-4 border-top">
                            <a href="/dashboard" class="text-secondary text-decoration-none small fw-bold">Annuler</a>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm transition-all">
                                <i class="bi bi-send-fill me-2"></i>Soumettre la demande
                            </button>
                        </div>
                    </form>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="text-muted mb-3"><i class="bi bi-cone-striped fs-1"></i></div>
                        <h4 class="fw-bold">Formulaire introuvable</h4>
                        <p class="text-muted">Le formulaire pour "<?= htmlspecialchars($currentWorkflowType) ?>" n'est pas encore configuré.</p>
                        <a href="/dashboard" class="btn btn-primary btn-sm mt-2">Retour au tableau de bord</a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<style>
    /* Style pour les champs flottants personnalisés si besoin */
    .form-floating > .form-control:focus,
    .form-floating > .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    /* Transition bouton */
    .btn-primary:hover { transform: translateY(-1px); }
</style>