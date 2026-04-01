<?php 
/**
 * VUE ADMIN UTILISATEURS - DESIGN FLOW
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <div>
        <h1 class="h4 fw-bold text-dark mb-0">
            <i class="bi bi-people text-primary me-2"></i>Utilisateurs
        </h1>
        <p class="text-muted" style="font-size: 0.7rem; margin-bottom: 0;">Gestion des accès et permissions.</p>
    </div>
    <div class="mt-2 mt-md-0">
        <button type="button" class="btn btn-primary btn-sm rounded-pill shadow-sm px-3 fw-bold" onclick="openModal('modalCreateUser')">
            <i class="bi bi-person-plus-fill me-1"></i>Nouveau
        </button>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
  <div class="alert alert-success border-0 shadow-sm border-start border-success border-4 rounded-3 fade show">
    <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if(isset($error)): ?>
  <div class="alert alert-danger border-0 shadow-sm border-start border-danger border-4 rounded-3 fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="bg-light text-secondary">
          <tr>
            <th class="ps-3 py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">Identité</th>
            <th class="py-2 text-uppercase fw-bold d-none d-sm-table-cell" style="font-size: 0.65rem;">Rôle</th>
            <th class="py-2 text-uppercase fw-bold" style="font-size: 0.65rem;">Statut</th> 
            <th class="py-2 text-uppercase fw-bold d-none d-lg-table-cell" style="font-size: 0.65rem;">Permissions</th>
            <th class="pe-3 py-2 text-end text-uppercase fw-bold" style="font-size: 0.65rem;">Actions</th>
          </tr>
        </thead>
        <tbody class="border-top-0">
          <?php foreach($users as $u): 
            $allowedWorkflows = $u['allowed_workflows_array'] ?? [];
            $isActive = (bool)($u['is_active'] ?? 1); 
          ?>
            <tr class="<?= $isActive ? '' : 'bg-light text-muted' ?>">
              <td class="ps-3 py-2">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-2 <?= $isActive ? 'bg-vibrant-indigo' : 'bg-vibrant-slate' ?> d-none d-md-flex">
                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <div class="text-truncate" style="max-width: 150px;">
                        <div class="fw-bold text-dark small text-truncate">
                            <?= htmlspecialchars($u['name']) ?>
                            <?php if($u['google_only']): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 rounded-pill ms-1" title="Google Auth Only" style="font-size: 0.55rem; padding: 1px 4px;">
                                    <i class="bi bi-google"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted d-none d-md-block" style="font-size: 0.65rem;"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                </div>
              </td>
              <td class="d-none d-sm-table-cell">
                <?php if($u['role'] === 'admin'): ?>
                  <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-10 rounded-pill" style="font-size: 0.6rem;">Admin</span>
                <?php else: ?>
                  <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill" style="font-size: 0.6rem;">User</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isActive): ?>
                  <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.6rem;"><i class="bi bi-check-circle-fill d-md-none"></i><span class="d-none d-md-inline">Actif</span></span>
                <?php else: ?>
                  <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill" style="font-size: 0.6rem;"><i class="bi bi-lock-fill d-md-none"></i><span class="d-none d-md-inline">Off</span></span>
                <?php endif; ?>
              </td>
              <td class="d-none d-lg-table-cell">
                <?php if (empty($allowedWorkflows)): ?>
                    <span class="text-muted fst-italic" style="font-size: 0.6rem;">Aucun</span>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($allowedWorkflows as $wf): ?>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.55rem;"><?= htmlspecialchars($workflowTypes[$wf] ?? $wf) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
              </td>
              <td class="pe-3 text-end">
                <div class="d-flex justify-content-end gap-1">
                    <button class="btn btn-sm btn-light border-0 text-primary p-1" title="Modifier" onclick="openModal('modalUserEdit<?= $u['id'] ?>')">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-light border-0 text-warning p-1 d-none d-sm-inline-block" title="<?= $isActive ? 'Désactiver' : 'Activer' ?>" onclick="openStatusModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', <?= $isActive ? 'true' : 'false' ?>)">
                        <i class="bi bi-<?= $isActive ? 'lock-fill' : 'unlock-fill' ?>"></i>
                    </button>
                    <button class="btn btn-sm btn-light border-0 text-danger p-1" title="Supprimer" onclick="openModal('modalUserDelete<?= $u['id'] ?>')">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if(empty($users)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-people fs-1 opacity-25"></i>
            <p class="mt-2">Aucun utilisateur trouvé.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalCreateUser" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Nouvel utilisateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <?php \App\Controllers\AuthController::getCsrfInput(); ?>
        <div class="modal-body py-3">
            
            <div class="mb-2">
                <label class="form-label small fw-bold">Nom complet</label>
                <input type="text" name="name" class="form-control form-control-sm" required>
            </div>
            
            <div class="mb-2">
                <label class="form-label small fw-bold">Adresse E-mail</label>
                <input type="email" name="email" autocomplete="username" class="form-control form-control-sm" required>
            </div>
            
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small fw-bold">Rôle</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Mot de passe</label>
                    <input type="password" name="password" id="new_pass" autocomplete="new-password" class="form-control form-control-sm" required>
                </div>
            </div>
            
            <div class="form-check form-switch mb-2 ms-1">
                <input class="form-check-input" type="checkbox" name="google_only" id="new_google_only" onchange="togglePassField('new_pass', this.checked)">
                <label class="form-check-label small fw-bold text-primary" for="new_google_only">
                    Google Only
                </label>
            </div>
            
            <label class="form-label small text-muted fw-bold ms-1" style="font-size: 0.65rem;">Permissions</label>
            <div class="bg-light p-2 rounded-3 mb-0">
                <div class="row g-1">
                    <?php foreach ($workflowTypes as $key => $name): ?>
                    <div class="col-6">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="allowed_workflows[]" value="<?= $key ?>" id="new_wf_<?= $key ?>" checked>
                            <label class="form-check-label" style="font-size: 0.7rem;" for="new_wf_<?= $key ?>"><?= htmlspecialchars($name) ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        <div class="modal-footer border-top-0 pt-0 pb-3 justify-content-center">
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" name="create_user" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Créer le compte</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php foreach($users as $u): 
    $allowedWorkflows = $u['allowed_workflows_array'] ?? [];
?>
    <div class="modal fade" id="modalUserEdit<?= $u['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post">
                <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Modifier <?= htmlspecialchars($u['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Nom complet</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($u['name']) ?>" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Rôle</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="user" <?= $u['role']=='user'?'selected':'' ?>>Utilisateur</option>
                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Administrateur</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mot de passe</label>
                            <input type="password" name="password" id="edit_pass_<?= $u['id'] ?>" autocomplete="new-password" class="form-control form-control-sm" placeholder="..." <?= $u['google_only'] ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2 ms-1">
                        <input class="form-check-input" type="checkbox" name="google_only" id="edit_google_only_<?= $u['id'] ?>" <?= $u['google_only'] ? 'checked' : '' ?> onchange="togglePassField('edit_pass_<?= $u['id'] ?>', this.checked)">
                        <label class="form-check-label small fw-bold text-primary" for="edit_google_only_<?= $u['id'] ?>">Google Only</label>
                    </div>

                    <label class="form-label small text-muted fw-bold ms-1" style="font-size: 0.65rem;">Permissions</label>
                    <div class="bg-light p-2 rounded-3">
                        <div class="row g-1">
                            <?php foreach ($workflowTypes as $key => $name): ?>
                            <div class="col-6">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="allowed_workflows[]" value="<?= $key ?>" 
                                           id="edit_wf_<?= $u['id'] ?>_<?= $key ?>" <?= in_array($key, $allowedWorkflows) ? 'checked' : '' ?>>
                                    <label class="form-check-label" style="font-size: 0.7rem;" for="edit_wf_<?= $u['id'] ?>_<?= $key ?>"><?= htmlspecialchars($name) ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mt-2 pt-2 border-top">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 small" style="font-size: 0.6rem;">API</span>
                            <input type="text" class="form-control bg-light font-monospace text-muted small" value="<?= htmlspecialchars($u['api_token'] ?? 'aucun') ?>" readonly style="font-size: 0.6rem;">
                            <button type="submit" name="regen_token" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-3 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" name="update_user" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Enregistrer</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalUserDelete<?= $u['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3"><i class="bi bi-exclamation-circle-fill fs-1"></i></div>
                <h5 class="fw-bold mb-2">Êtes-vous sûr ?</h5>
                <p class="text-muted small mb-4">Cette action supprimera définitivement <strong><?= htmlspecialchars($u['name']) ?></strong>.</p>
                <form method="post">
                    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <div class="d-grid gap-2">
                        <button type="submit" name="delete_user" class="btn btn-danger rounded-pill fw-bold">Supprimer</button>
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
      </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" id="modalUserStatus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form method="post">
          <?php \App\Controllers\AuthController::getCsrfInput(); ?>
          <input type="hidden" name="toggle_status" value="1">
          <input type="hidden" name="user_id" id="statusUserId">
          <input type="hidden" name="action_type" id="statusActionType">
          <div class="modal-header border-bottom-0">
              <h5 class="modal-title fw-bold">Changer le statut</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-0">
              <p id="statusModalMessage" class="mb-0 text-muted"></p>
          </div>
          <div class="modal-footer border-top-0 pb-4 px-4 mt-3">
              <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Confirmer</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function openModal(id){ new bootstrap.Modal(document.getElementById(id)).show(); }
function openStatusModal(id, name, isActive){
    document.getElementById('statusUserId').value = id;
    document.getElementById('statusActionType').value = isActive ? 'deactivate' : 'activate';
    document.getElementById('statusModalMessage').innerHTML = isActive 
        ? 'Voulez-vous vraiment désactiver <strong>' + name + '</strong> ? <br><small class="text-danger">L\'utilisateur ne pourra plus se connecter.</small>'
        : 'Voulez-vous réactiver <strong>' + name + '</strong> ?';
    new bootstrap.Modal(document.getElementById('modalUserStatus')).show();
}
function togglePassField(fieldId, isGoogleOnly) {
    const field = document.getElementById(fieldId);
    if (isGoogleOnly) {
        field.disabled = true;
        field.value = '';
        field.removeAttribute('required');
    } else {
        field.disabled = false;
        if (fieldId === 'new_pass') field.setAttribute('required', 'required');
    }
}
</script>