<?php
/**
 * FORMULAIRE PARTIEL : NOTES DE FRAIS
 */
?>

<h5 class="text-success fw-bold mb-4 pb-2 border-bottom border-light">
    <i class="bi bi-receipt me-2"></i>Détails des frais
</h5>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="form-floating">
            <select name="pole_id" id="pole_id" class="form-select" required>
                <option value="">Choisir...</option>
                <?php foreach($poles as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Pôle d'imputation</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <select name="company_id" id="company_id" class="form-select" required>
                <option value="">Choisir...</option>
                <?php foreach($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" data-pole="<?= $c['pole_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Société</label>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="form-floating">
            <select name="type" class="form-select bg-light border-0" required>
                <option value="Déplacement">Déplacement (Train, Avion...)</option>
                <option value="Restauration">Restauration / Repas client</option>
                <option value="Hébergement">Hébergement / Hôtel</option>
                <option value="Carburant">Carburant / Péage</option>
                <option value="Fournitures">Fournitures / Divers</option>
            </select>
            <label>Catégorie de dépense</label>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="form-floating">
            <input type="text" name="start_date_duration" class="form-control bg-light border-0" placeholder="Date" required>
            <label>Date de la dépense</label>
        </div>
    </div>
    
    <div class="col-12">
        <div class="form-floating">
            <textarea name="objective" class="form-control" style="height: 80px" placeholder="Description" required></textarea>
            <label>Description détaillée & Invités (si repas)</label>
        </div>
    </div>
</div>

<h5 class="text-success fw-bold mb-4 pb-2 border-bottom border-light mt-5">
    <i class="bi bi-currency-exchange me-2"></i>Remboursement
</h5>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted small fw-bold">Montant TTC</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-white text-success border-end-0"><i class="bi bi-cash-stack"></i></span>
            <input type="number" step="0.01" name="amount" class="form-control border-start-0 ps-0 fw-bold text-success" placeholder="0.00" required>
        </div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label text-muted small fw-bold">Justificatif (Reçu/Facture)</label>
        <input type="file" name="attachment" class="form-control form-control-lg" required>
        <div class="form-text text-danger small"><i class="bi bi-asterisk"></i> Obligatoire pour remboursement</div>
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