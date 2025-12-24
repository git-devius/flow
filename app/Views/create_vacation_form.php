<?php
/**
 * FORMULAIRE PARTIEL : CONGÉS
 */
?>

<h5 class="text-warning fw-bold mb-4 pb-2 border-bottom border-light">
    <i class="bi bi-sun me-2"></i>Période de congés
</h5>

<input type="hidden" name="pole_id" value="1"> 
<input type="hidden" name="company_id" value="1">
<input type="hidden" name="budget_planned" value="1">

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="form-floating">
            <select name="type" class="form-select bg-light border-0" required>
                <option value="Congés Payés">Congés Payés</option>
                <option value="RTT">RTT</option>
                <option value="Sans Solde">Sans Solde</option>
                <option value="Récupération">Récupération</option>
                <option value="Maladie">Maladie</option>
            </select>
            <label>Type d'absence</label>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-floating">
            <input type="number" step="0.5" name="amount" class="form-control bg-light border-0" placeholder="Nombre de jours" required>
            <label>Nombre de jours total</label>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="form-floating">
            <input type="text" name="start_date_duration" class="form-control" placeholder="Du... au..." required>
            <label>Dates (Du ... au ...)</label>
        </div>
    </div>
    
    <div class="col-12">
        <div class="form-floating">
            <textarea name="objective" class="form-control" style="height: 100px" placeholder="Motif (Optionnel)"></textarea>
            <label>Commentaire / Motif (facultatif)</label>
        </div>
    </div>
    
    <div class="col-12">
        <label class="form-label text-muted small fw-bold ms-1">Justificatif (Arrêt maladie, etc.)</label>
        <input type="file" name="attachment" class="form-control">
    </div>
</div>