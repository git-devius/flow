<?php
/**
 * FORMULAIRE PARTIEL : NOTES DE FRAIS
 */
?>

<h5 class="text-success fw-bold mb-4 pb-2 border-bottom border-light">
    <i class="bi bi-receipt me-2"></i>Détails des frais
</h5>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Pôle d'imputation</label>
        <select name="pole_id" id="pole_id" class="form-select" required>
            <option value="">Choisir...</option>
            <?php foreach($poles as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (isset($investment['pole_id']) && $investment['pole_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Société</label>
        <select name="company_id" id="company_id" class="form-select" required>
            <option value="">Choisir...</option>
            <?php foreach($companies as $c): ?>
                <option value="<?= $c['id'] ?>" data-pole="<?= $c['pole_id'] ?>" <?= (isset($investment['company_id']) && $investment['company_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label class="form-label">Catégorie de dépense</label>
        <select name="type" class="form-select" required>
            <?php 
                $categories = [
                    'Déplacement' => 'Déplacement (Train, Avion...)',
                    'Restauration' => 'Restauration / Repas client',
                    'Hébergement' => 'Hébergement / Hôtel',
                    'Carburant' => 'Carburant / Péage',
                    'Fournitures' => 'Fournitures / Divers'
                ];
                foreach($categories as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= (isset($investment['type']) && $investment['type'] === $val) ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Date de la dépense</label>
        <input type="text" name="start_date_duration" class="form-control" placeholder="JJ/MM/AAAA" value="<?= htmlspecialchars($investment['start_date_duration'] ?? '') ?>" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description & Invités</label>
    <textarea name="objective" class="form-control" style="height: 60px" placeholder="..." required><?= htmlspecialchars($investment['objective'] ?? '') ?></textarea>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Montant TTC (€)</label>
        <input type="number" step="0.01" name="amount" class="form-control fw-bold text-success" placeholder="0.00" value="<?= htmlspecialchars($investment['amount'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Justificatif <?= (isset($investment['file_path']) && $investment['file_path']) ? '<span class="text-success small">(Présent)</span>' : '' ?></label>
        <input type="file" name="attachment" class="form-control form-control-sm" <?= (isset($investment['file_path']) && $investment['file_path']) ? '' : 'required' ?>>
        <div class="form-text text-danger" style="font-size: 0.65rem;">Obligatoire. PDF, JPG, PNG, Word, Excel, CSV</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poleSelect = document.getElementById('pole_id');
    const companySelect = document.getElementById('company_id');
    if(poleSelect && companySelect){
        const originalOptions = Array.from(companySelect.options);
        poleSelect.addEventListener('change', function() {
            const selectedPole = this.value;
            companySelect.innerHTML = '<option value="">Choisir...</option>';
            originalOptions.forEach(opt => {
                if (opt.value === "") return;
                if (!selectedPole || opt.getAttribute('data-pole') == selectedPole) {
                    companySelect.add(opt.cloneNode(true));
                }
            });
            companySelect.value = "";
        });
    }
});
</script>