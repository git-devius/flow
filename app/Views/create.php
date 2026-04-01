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

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <div>
        <h1 class="h4 fw-bold text-dark mb-0">
            <i class="bi bi-<?= $currentIcon ?> text-primary me-2"></i><?= htmlspecialchars($workflowName) ?>
        </h1>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-x-lg me-1"></i> Fermer
        </a>
    </div>
</div>

<?php if(isset($error) && $error): ?>
  <div class="alert alert-danger shadow-sm border-0 border-start border-danger border-4 d-flex align-items-center py-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div class="small fw-bold"><?= htmlspecialchars($error) ?></div>
    <button type="button" class="btn-close ms-auto small" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-sm rounded-4 form-contrast-container" style="background-color: #e2e8f0;">
            <div class="card-body p-3 p-md-4">
                
                <?php if($currentForm && file_exists($currentForm)): ?>
                    
                    <form method="post" action="/create" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                        <input type="hidden" name="workflow_type" value="<?= htmlspecialchars($currentWorkflowType) ?>">
                        
                        <?php include $currentForm; ?>

                        <div class="d-flex justify-content-end align-items-center gap-3 mt-5 pt-4 border-top border-secondary border-opacity-25">
                            <a href="/dashboard" class="text-secondary text-decoration-none small fw-bold">Annuler</a>
                            <button type="submit" class="btn btn-primary px-4 py-3 rounded-pill fw-bold shadow-lg transition-all">
                                <i class="bi bi-send-fill me-2"></i>Soumettre la demande
                            </button>
                        </div>
                    </form>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="text-muted mb-3"><i class="bi bi-cone-striped fs-1"></i></div>
                        <h4 class="fw-bold text-dark">Formulaire introuvable</h4>
                        <p class="text-secondary">Le formulaire pour "<?= htmlspecialchars($currentWorkflowType) ?>" n'est pas encore configuré.</p>
                        <a href="/dashboard" class="btn btn-primary btn-sm mt-2 shadow-sm">Retour au tableau de bord</a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<style>
    /* Application du style dynamique de contraste (type Login) */
    .form-contrast-container .form-control:not(.input-group > *),
    .form-contrast-container .form-select {
        background-color: #ffffff;
        border: none !important;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
        border-radius: 0.75rem;
    }

    /* Style unifié pour les inputs groupés (ex: avec Euro) */
    .form-contrast-container .input-group {
        background-color: #ffffff;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .form-contrast-container .input-group > .form-control,
    .form-contrast-container .input-group > .input-group-text {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    
    .form-contrast-container .form-floating > label,
    .form-contrast-container .form-label {
        color: #6c757d !important; /* text-secondary */
        font-weight: bold !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px;
    }

    .form-contrast-container .form-section-title {
        color: #212529;
        font-weight: 800;
        margin-bottom: 1rem;
        border-bottom: 2px solid rgba(0,0,0,0.05);
        padding-bottom: 0.5rem;
        margin-top: 2rem;
    }

    /* Style interactif au focus */
    .form-contrast-container .form-floating > .form-control:focus,
    .form-contrast-container .form-floating > .form-select:focus {
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.25) !important;
    }

    /* Ajustement spécifique au select */
    .form-contrast-container .form-select {
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }

    /* Transition bouton */
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,.1); }
</style>