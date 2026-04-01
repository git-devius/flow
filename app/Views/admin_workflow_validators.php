<?php 
$title = "Gestion des validateurs"; 
ob_start(); 
?>

<div class="container py-3">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-2 px-3 border-bottom-0">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Circuit de validation</h6>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="/admin/workflow_validators" class="row g-2 mb-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold mb-1">Société</label>
                    <select name="company_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Choisir une société --</option>
                        <?php foreach($companies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($company_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold mb-1">Type de Workflow</label>
                    <select name="workflow_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="investment" <?= ($workflow_type == 'investment') ? 'selected' : '' ?>>Investissement</option>
                        <option value="vacation" <?= ($workflow_type == 'vacation') ? 'selected' : '' ?>>Congés</option>
                        <option value="expense" <?= ($workflow_type == 'expense') ? 'selected' : '' ?>>Notes de Frais</option>
                    </select>
                </div>
            </form>

            <hr class="my-3 opacity-10">

            <?php if ($company_id): ?>
            <form method="POST" action="/admin/workflow_validators">
                <input type="hidden" name="company_id" value="<?= $company_id ?>">
                <input type="hidden" name="workflow_type" value="<?= $workflow_type ?>">

                <div id="steps-container">
                    <?php if (empty($currentSteps)): ?>
                        <div class="step-item mb-2 bg-light p-2 rounded-3">
                            <label class="form-label small fw-bold mb-1">Étape 1</label>
                            <select name="validators[]" class="form-select form-select-sm">
                                <option value="">-- Sélectionner un validateur --</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <?php foreach($currentSteps as $k => $step): ?>
                            <div class="step-item mb-2 bg-light p-2 rounded-3">
                                <label class="form-label small fw-bold mb-1">Étape <?= $step['step_order'] ?></label>
                                <div class="input-group input-group-sm">
                                    <select name="validators[]" class="form-select">
                                        <option value="">-- Sélectionner un validateur --</option>
                                        <?php foreach($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= ($u['id'] == $step['validator_user_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u['name']) ?>
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

                <div class="mt-2 text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addStep()" style="font-size: 0.7rem;">
                        <i class="bi bi-plus-circle me-1"></i> Ajouter une étape
                    </button>
                </div>

                <div class="mt-3 border-top pt-3 text-end">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">Enregistrer le circuit</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-info py-2 small border-0 shadow-none bg-light text-primary"><i class="bi bi-info-circle me-2"></i>Choisissez une société pour configurer ses validateurs.</div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function addStep() {
    const container = document.getElementById('steps-container');
    const count = container.querySelectorAll('.step-item').length + 1;
    
    const html = `
        <div class="step-item mb-2 bg-light p-2 rounded-3">
            <label class="form-label small fw-bold mb-1">Étape ${count}</label>
            <div class="input-group input-group-sm">
                <select name="validators[]" class="form-select">
                    <option value="">-- Sélectionner un validateur --</option>
                    <?php foreach($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= addslashes($u['name']) ?></option>
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