<?php
/**
 * FORMULAIRE PARTIEL : CONGÉS
 */
?>

<h5 class="text-warning fw-bold mb-4 pb-2 border-bottom border-light">
    <i class="bi bi-sun me-2"></i>Période de congés
</h5>

<input type="hidden" name="pole_id" value="<?= $investment['pole_id'] ?? 1 ?>"> 
<input type="hidden" name="company_id" value="<?= $investment['company_id'] ?? 1 ?>">
<input type="hidden" name="budget_planned" value="1">

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Type d'absence</label>
        <select name="type" class="form-select" required>
            <?php 
                $types = ['Congés Payés', 'RTT', 'Sans Solde', 'Récupération', 'Maladie'];
                foreach($types as $t): ?>
                <option value="<?= $t ?>" <?= (isset($investment['type']) && $investment['type'] === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Nombre de jours</label>
        <input type="number" step="0.5" name="amount" class="form-control" placeholder="0.0" value="<?= htmlspecialchars($investment['amount'] ?? '') ?>" required>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Période (Dates)</label>
        <input type="text" name="start_date_duration" class="form-control" placeholder="Du... au..." value="<?= htmlspecialchars($investment['start_date_duration'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted">Justificatif <?= (isset($investment['file_path']) && $investment['file_path']) ? '<span class="text-success small">(Présent)</span>' : '(Optionnel)' ?></label>
        <input type="file" name="attachment" class="form-control form-control-sm">
    </div>
</div>

<div class="mb-0">
    <label class="form-label">Commentaire (facultatif)</label>
    <textarea name="objective" class="form-control" style="height: 60px" placeholder="..."><?= htmlspecialchars($investment['objective'] ?? '') ?></textarea>
</div>