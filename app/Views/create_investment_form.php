<?php
/**
 * FORMULAIRE PARTIEL : INVESTISSEMENT
 * Design: Floating Labels & Sections claires
 */
?>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Pôle concerné</label>
        <select name="pole_id" id="pole_id" class="form-select" required>
            <option value="">Choisir...</option>
            <?php foreach($poles as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (isset($investment['pole_id']) && $investment['pole_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Société bénéficiaire</label>
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
        <label class="form-label">Titre / Nature de l'investissement</label>
        <input type="text" name="type" id="type" class="form-control" placeholder="Ex: Matériel informatique" value="<?= htmlspecialchars($investment['type'] ?? '') ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Budget prévu ?</label>
        <select name="budget_planned" id="budget_planned" class="form-select" required>
            <option value="1" <?= (isset($investment['budget_planned']) && $investment['budget_planned'] == 1) ? 'selected' : '' ?>>Oui</option>
            <option value="0" <?= (isset($investment['budget_planned']) && $investment['budget_planned'] == 0) ? 'selected' : '' ?>>Non</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Justification détaillée</label>
    <textarea name="objective" id="objective" class="form-control" style="height: 70px" placeholder="..." required><?= htmlspecialchars($investment['objective'] ?? '') ?></textarea>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Montant (HT) €</label>
        <input type="number" step="0.01" name="amount" class="form-control fw-bold" placeholder="0.00" value="<?= htmlspecialchars($investment['amount'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Date souhaitée</label>
        <input type="text" name="start_date_duration" class="form-control" placeholder="Mai 2024" value="<?= htmlspecialchars($investment['start_date_duration'] ?? '') ?>" required>
    </div>
</div>

<div class="mb-0">
    <label class="form-label">Pièce jointe (Devis) <?= (isset($investment['file_path']) && $investment['file_path']) ? '<span class="text-success small">(Un fichier est déjà présent)</span>' : '' ?></label>
    <input type="file" name="attachment" class="form-control form-control-sm">
    <div class="form-text mt-1" style="font-size: 0.65rem;">PDF, Images, Word, Excel, CSV (Max 10Mo)</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poleSelect = document.getElementById('pole_id');
    const companySelect = document.getElementById('company_id');
    
    // Sauvegarder les options originales
    const originalOptions = Array.from(companySelect.options);
    
    poleSelect.addEventListener('change', function() {
        const selectedPole = this.value;
        
        // Vider sauf le placeholder
        companySelect.innerHTML = '<option value="">Sélectionner une société...</option>';
        
        originalOptions.forEach(opt => {
            if (opt.value === "") return; // Skip placeholder original
            // Si pas de pôle sélectionné, on affiche tout, sinon on filtre
            if (!selectedPole || opt.getAttribute('data-pole') == selectedPole) {
                companySelect.add(opt.cloneNode(true));
            }
        });
        
        // Reset la sélection
        companySelect.value = "";
    });
});
</script>