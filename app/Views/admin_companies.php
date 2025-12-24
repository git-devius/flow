<?php /** VUE ADMIN SOCIETES - DESIGN FLOW */ ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1"><i class="bi bi-building text-primary me-2"></i>Sociétés</h1>
        <p class="text-muted small mb-0">Entités juridiques rattachées aux pôles.</p>
    </div>
    <div class="mt-3 mt-md-0">
        <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold" onclick="openModal('modalCreateCompany')">
            <i class="bi bi-plus-lg me-2"></i>Nouvelle société
        </button>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
  <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 rounded-3 fade show"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?><button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if(isset($error)): ?>
  <div class="alert alert-danger border-0 shadow-sm border-start border-danger border-4 rounded-3 fade show"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-secondary">
          <tr>
            <th class="ps-4 py-3 text-uppercase small fw-bold">Nom de la Société</th>
            <th class="py-3 text-uppercase small fw-bold">Pôle de rattachement</th>
            <th class="pe-4 py-3 text-end text-uppercase small fw-bold">Actions</th>
          </tr>
        </thead>
        <tbody class="border-top-0">
          <?php foreach($companies as $c): ?>
            <tr>
              <td class="ps-4 py-3 fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></td>
              <td><span class="badge bg-light text-secondary border rounded-pill px-3"><?= htmlspecialchars($c['pole_name'] ?? 'N/A') ?></span></td>
              <td class="pe-4 text-end">
                <button class="btn btn-sm btn-white text-primary hover-shadow rounded-circle me-1" onclick="openModal('modalEdit<?= $c['id'] ?>')"><i class="bi bi-pencil-fill"></i></button>
                <button class="btn btn-sm btn-white text-danger hover-shadow rounded-circle" onclick="openModal('modalDelete<?= $c['id'] ?>')"><i class="bi bi-trash-fill"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if(empty($companies)): ?><div class="text-center py-5 text-muted">Aucune société définie.</div><?php endif; ?>
</div>

<div class="modal fade" id="modalCreateCompany" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><form method="post">
    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
    <div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title fw-bold">Nouvelle société</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body pt-4">
        <div class="form-floating mb-3">
            <input type="text" name="name" id="new_comp_name" class="form-control rounded-3" placeholder="Nom" required>
            <label for="new_comp_name">Raison sociale</label>
        </div>
        <div class="form-floating">
            <select name="pole_id" id="new_comp_pole" class="form-select rounded-3" required>
                <option value="" disabled selected>Choisir un pôle...</option>
                <?php foreach($poles as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option><?php endforeach; ?>
            </select>
            <label for="new_comp_pole">Pôle de rattachement</label>
        </div>
    </div>
    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" name="create_company" class="btn btn-success rounded-pill px-4 fw-bold">Créer</button>
    </div>
</form></div></div></div>

<?php foreach($companies as $c): ?>
<div class="modal fade" id="modalEdit<?= $c['id'] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><form method="post">
    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
    <input type="hidden" name="id" value="<?= $c['id'] ?>">
    <div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title fw-bold">Modifier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body pt-4">
        <div class="form-floating mb-3">
            <input type="text" name="name" class="form-control rounded-3" value="<?= htmlspecialchars($c['name']) ?>" required>
            <label>Raison sociale</label>
        </div>
        <div class="form-floating">
            <select name="pole_id" class="form-select rounded-3" required>
                <?php foreach($poles as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id']==$c['pole_id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Pôle</label>
        </div>
    </div>
    <div class="modal-footer border-top-0 pt-0 pb-4 px-4"><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button><button type="submit" name="update_company" class="btn btn-primary rounded-pill px-4 fw-bold">Enregistrer</button></div>
</form></div></div></div>

<div class="modal fade" id="modalDelete<?= $c['id'] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content border-0 shadow-lg rounded-4"><form method="post">
    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
    <input type="hidden" name="id" value="<?= $c['id'] ?>">
    <div class="modal-body text-center p-4">
        <div class="text-danger mb-3"><i class="bi bi-trash-fill fs-1"></i></div>
        <h5 class="fw-bold mb-2">Supprimer ?</h5>
        <p class="small text-muted mb-4">Société : <strong><?= htmlspecialchars($c['name']) ?></strong></p>
        <div class="d-grid gap-2"><button type="submit" name="delete_company" class="btn btn-danger rounded-pill fw-bold">Confirmer</button><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button></div>
    </div>
</form></div></div></div>
<?php endforeach; ?>

<script>function openModal(id){new bootstrap.Modal(document.getElementById(id)).show();}</script>