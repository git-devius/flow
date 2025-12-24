<?php
/**
 * FORMULAIRE PARTIEL : INVESTISSEMENT
 * Design: Floating Labels & Sections claires
 */
?>

<h5 class="text-primary fw-bold mb-4 pb-2 border-bottom border-light">
    <i class="bi bi-geo-alt me-2"></i>Contexte de la demande
</h5>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="form-floating">
            <select name="pole_id" id="pole_id" class="form-select bg-light border-0" required>
                <option value="">Sélectionner un pôle...</option>
                <?php foreach($poles as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="pole_id">Pôle concerné</label>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-floating">
            <select name="company_id" id="company_id" class="form-select bg-light border-0" required>
                <option value="">Sélectionner une société...</option>
                <?php foreach($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" data-pole="<?= $c['pole_id'] ?>">
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="company_id">Société bénéficiaire</label>
        </div>
    </div>
</div>

<h5 class="text-primary fw-bold mb-4 pb-2 border-bottom border-light mt-5">
    <i class="bi bi-info-circle me-2"></i>Détails du projet
</h5>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="form-floating">
            <input type="text" name="type" id="type" class="form-control" placeholder="Ex: Achat matériel informatique" required>
            <label for="type">Titre / Nature de l'investissement</label>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="form-floating">
            <select name="budget_planned" id="budget_planned" class="form-select" required>
                <option value="1">Oui, prévu au budget</option>
                <option value="0">Non, hors budget</option>
            </select>
            <label for="budget_planned">Prévu au budget ?</label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-floating">
            <textarea name="objective" id="objective" class="form-control" style="height: 120px" placeholder="Décrivez l'objectif..." required></textarea>
            <label for="objective">Objectif et justification détaillée</label>
        </div>
    </div>
</div>

<h5 class="text-primary fw-bold mb-4 pb-2 border-bottom border-light mt-5">
    <i class="bi bi-calendar-check me-2"></i>Chiffrage & Planning
</h5>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted small fw-bold ms-1">Montant total (HT)</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-currency-euro"></i></span>
            <input type="number" step="0.01" name="amount" class="form-control border-start-0 ps-0" placeholder="0.00" required>
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label text-muted small fw-bold ms-1">Date de démarrage souhaitée</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-calendar-event"></i></span>
            <input type="text" name="start_date_duration" class="form-control border-start-0 ps-0" placeholder="Ex: Début Mai 2024 (2 semaines)" required>
        </div>
    </div>
    
    <div class="col-12 mt-4">
        <label class="form-label text-muted small fw-bold ms-1">Pièce jointe (Devis, etc.)</label>
        <input type="file" name="attachment" class="form-control form-control-lg">
        <div class="form-text small"><i class="bi bi-paperclip"></i> Formats acceptés : PDF, JPG, PNG, DOCX (Max 10 Mo)</div>
    </div>
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