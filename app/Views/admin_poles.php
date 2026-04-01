<?php /** VUE ADMIN POLES - DESIGN FLOW */ ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <div>
        <h1 class="h4 fw-bold text-dark mb-0"><i class="bi bi-diagram-3 text-primary me-2"></i>Pôles</h1>
        <p class="text-muted" style="margin-bottom: 0; font-size: 0.7rem;">Unités organisationnelles.</p>
    </div>
    <div class="mt-2 mt-md-0">
        <button type="button" class="btn btn-primary btn-sm rounded-pill shadow-sm px-3 fw-bold" onclick="openModal('modalCreatePole')">
            <i class="bi bi-plus-lg me-1"></i>Nouveau
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
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="bg-light text-secondary">
          <tr>
            <th class="ps-3 py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">ID</th>
            <th class="py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">Nom du Pôle</th>
            <th class="pe-3 py-2 text-end text-uppercase fw-bold" style="font-size: 0.65rem;">Actions</th>
          </tr>
        </thead>
        <tbody class="border-top-0">
          <?php foreach($poles as $p): ?>
            <tr>
              <td class="ps-3 py-2 text-muted small" style="font-size: 0.7rem;">#<?= $p['id'] ?></td>
              <td class="fw-bold text-dark small"><?= htmlspecialchars($p['name']) ?></td>
              <td class="pe-3 text-end">
                <button class="btn btn-sm btn-white text-primary p-1 me-1" onclick="openModal('modalPoleEdit<?= $p['id'] ?>')"><i class="bi bi-pencil-fill"></i></button>
                <button class="btn btn-sm btn-white text-danger p-1" onclick="openModal('modalPoleDelete<?= $p['id'] ?>')"><i class="bi bi-trash-fill"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if(empty($poles)): ?><div class="text-center py-5 text-muted">Aucun pôle défini.</div><?php endif; ?>
</div>

<div class="modal fade" id="modalCreatePole" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post">
                <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Créer un pôle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <label class="form-label small fw-bold">Nom du pôle</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="..." required>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-3 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="create_pole" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach($poles as $p): ?>
<div class="modal fade" id="modalPoleEdit<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><form method="post">
    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <div class="modal-header border-bottom-0 pb-0"><h6 class="modal-title fw-bold">Modifier le pôle</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body py-3">
        <label class="form-label small fw-bold">Nom du pôle</label>
        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($p['name']) ?>" required>
    </div>
    <div class="modal-footer border-top-0 pt-0 pb-3 justify-content-center"><button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Annuler</button><button type="submit" name="update_pole" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Enregistrer</button></div>
</form></div></div></div>

<div class="modal fade" id="modalPoleDelete<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content border-0 shadow-lg rounded-4"><form method="post">
    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <div class="modal-body text-center p-4">
        <div class="text-danger mb-3"><i class="bi bi-trash-fill fs-1"></i></div>
        <h5 class="fw-bold mb-2">Supprimer ?</h5>
        <p class="small text-muted mb-4">Pôle : <strong><?= htmlspecialchars($p['name']) ?></strong></p>
        <div class="d-grid gap-2"><button type="submit" name="delete_pole" class="btn btn-danger rounded-pill fw-bold">Confirmer</button><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button></div>
    </div>
</form></div></div></div>
<?php endforeach; ?>

<script>function openModal(id){new bootstrap.Modal(document.getElementById(id)).show();}</script>