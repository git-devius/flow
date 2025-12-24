<?php 
$title = "Gestion des validateurs"; 
ob_start(); 
?>

<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Configurer le workflow</h5>
        </div>
        <div class="card-body">
            
            <form method="GET" action="/admin/workflow_validators" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Société</label>
                    <select name="company_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choisir une société --</option>
                        <?php foreach($companies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($company_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type de Workflow</label>
                    <select name="workflow_type" class="form-select" onchange="this.form.submit()">
                        <option value="investment" <?= ($workflow_type == 'investment') ? 'selected' : '' ?>>Investissement</option>
                        <option value="vacation" <?= ($workflow_type == 'vacation') ? 'selected' : '' ?>>Congés</option>
                        <option value="expense" <?= ($workflow_type == 'expense') ? 'selected' : '' ?>>Notes de Frais</option>
                    </select>
                </div>
            </form>

            <hr>

            <?php if ($company_id): ?>
            <form method="POST" action="/admin/workflow_validators">
                <input type="hidden" name="company_id" value="<?= $company_id ?>">
                <input type="hidden" name="workflow_type" value="<?= $workflow_type ?>">

                <div id="steps-container">
                    <?php if (empty($currentSteps)): ?>
                        <div class="step-item mb-3">
                            <label class="form-label fw-bold">Étape 1</label>
                            <select name="validators[]" class="form-select">
                                <option value="">-- Sélectionner un validateur --</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <?php foreach($currentSteps as $k => $step): ?>
                            <div class="step-item mb-3">
                                <label class="form-label fw-bold">Étape <?= $step['step_order'] ?></label>
                                <div class="input-group">
                                    <select name="validators[]" class="form-select">
                                        <option value="">-- Sélectionner un validateur --</option>
                                        <?php foreach($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= ($u['id'] == $step['validator_user_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-danger" onclick="removeStep(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addStep()">
                        <i class="bi bi-plus-circle"></i> Ajouter une étape
                    </button>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer le circuit</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-info">Veuillez sélectionner une société pour configurer ses validateurs.</div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function addStep() {
    const container = document.getElementById('steps-container');
    const count = container.querySelectorAll('.step-item').length + 1;
    
    const html = `
        <div class="step-item mb-3">
            <label class="form-label fw-bold">Étape ${count}</label>
            <div class="input-group">
                <select name="validators[]" class="form-select">
                    <option value="">-- Sélectionner un validateur --</option>
                    <?php foreach($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= addslashes($u['name']) ?> (<?= $u['role'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-danger" onclick="removeStep(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeStep(btn) {
    btn.closest('.step-item').remove();
    document.querySelectorAll('#steps-container .step-item').forEach((el, index) => {
        el.querySelector('label').innerText = 'Étape ' + (index + 1);
    });
}
</script>

<?php 
$content = ob_get_clean(); 
require 'layout.php'; 
?>