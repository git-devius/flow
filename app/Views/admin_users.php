<?php 
/**
 * VUE ADMIN UTILISATEURS - DESIGN FLOW
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">
            <i class="bi bi-people text-primary me-2"></i>Utilisateurs
        </h1>
        <p class="text-muted small mb-0">Gérez les accès et les permissions de l'équipe.</p>
    </div>
    <div class="mt-3 mt-md-0">
        <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold" onclick="openModal('modalCreateUser')">
            <i class="bi bi-person-plus-fill me-2"></i>Nouvel utilisateur
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
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-secondary">
          <tr>
            <th class="ps-4 py-3 text-uppercase small fw-bold">Identité</th>
            <th class="py-3 text-uppercase small fw-bold">Rôle</th>
            <th class="py-3 text-uppercase small fw-bold">Statut</th> 
            <th class="py-3 text-uppercase small fw-bold">Workflows Autorisés</th>
            <th class="pe-4 py-3 text-end text-uppercase small fw-bold">Actions</th>
          </tr>
        </thead>
        <tbody class="border-top-0">
          <?php foreach($users as $u): 
            $allowedWorkflows = $u['allowed_workflows_array'] ?? [];
            $isActive = (bool)($u['is_active'] ?? 1); 
          ?>
            <tr class="<?= $isActive ? '' : 'bg-light text-muted' ?>">
              <td class="ps-4 py-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3 <?= $isActive ? 'bg-primary text-white' : 'bg-secondary text-white' ?>" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                </div>
              </td>
              <td>
                <?php if($u['role'] === 'admin'): ?>
                  <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-10 rounded-pill px-3">Admin</span>
                <?php else: ?>
                  <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-3">Utilisateur</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isActive): ?>
                  <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><i class="bi bi-dot"></i> Actif</span>
                <?php else: ?>
                  <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill"><i class="bi bi-lock-fill"></i> Désactivé</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (empty($allowedWorkflows)): ?>
                    <span class="text-muted small fst-italic">Aucun</span>
                <?php else: ?>
                    <?php foreach ($allowedWorkflows as $wf): ?>
                        <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($workflowTypes[$wf] ?? $wf) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
              </td>
              <td class="pe-4 text-end">
                <div class="btn-group">
                    <button class="btn btn-sm btn-white text-primary hover-shadow rounded-circle me-1" title="Modifier" onclick="openModal('modalUserEdit<?= $u['id'] ?>')">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-white text-warning hover-shadow rounded-circle me-1" title="<?= $isActive ? 'Désactiver' : 'Activer' ?>" onclick="openStatusModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', <?= $isActive ? 'true' : 'false' ?>)">
                        <i class="bi bi-<?= $isActive ? 'lock-fill' : 'unlock-fill' ?>"></i>
                    </button>
                    <button class="btn btn-sm btn-white text-danger hover-shadow rounded-circle" title="Supprimer" onclick="openModal('modalUserDelete<?= $u['id'] ?>')">
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
        <div class="modal-body pt-4">
            
            <div class="form-floating mb-3">
                <input type="text" name="name" id="new_name" class="form-control rounded-3" placeholder="Nom" required>
                <label for="new_name">Nom complet</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="email" name="email" id="new_email" class="form-control rounded-3" placeholder="Email" required>
                <label for="new_email">Adresse E-mail</label>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" name="password" id="new_pass" class="form-control rounded-3" placeholder="Mot de passe" required>
                        <label for="new_pass">Mot de passe</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="role" id="new_role" class="form-select rounded-3">
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                        <label for="new_role">Rôle</label>
                    </div>
                </div>
            </div>
            
            <label class="form-label small text-muted fw-bold ms-1">Permissions de création</label>
            <div class="bg-light p-3 rounded-3 mb-3">
                <div class="row">
                    <?php foreach ($workflowTypes as $key => $name): ?>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="allowed_workflows[]" value="<?= $key ?>" id="new_wf_<?= $key ?>" checked>
                            <label class="form-check-label small" for="new_wf_<?= $key ?>"><?= htmlspecialchars($name) ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" name="create_user" class="btn btn-primary rounded-pill px-4 fw-bold">Créer le compte</button>
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
                <div class="modal-body pt-4">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($u['email']) ?>" disabled readonly>
                        <label>E-mail (Non modifiable)</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="name" class="form-control rounded-3" value="<?= htmlspecialchars($u['name']) ?>" required>
                        <label>Nom complet</label>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" name="password" class="form-control rounded-3" placeholder="Nouveau mot de passe">
                                <label>Mot de passe (optionnel)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="role" class="form-select rounded-3">
                                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>Utilisateur</option>
                                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Administrateur</option>
                                </select>
                                <label>Rôle</label>
                            </div>
                        </div>
                    </div>
                    <label class="form-label small text-muted fw-bold ms-1">Permissions</label>
                    <div class="bg-light p-3 rounded-3">
                        <div class="row">
                            <?php foreach ($workflowTypes as $key => $name): ?>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allowed_workflows[]" value="<?= $key ?>" 
                                           id="edit_wf_<?= $u['id'] ?>_<?= $key ?>" <?= in_array($key, $allowedWorkflows) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="edit_wf_<?= $u['id'] ?>_<?= $key ?>"><?= htmlspecialchars($name) ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <label class="small text-muted mb-1">Token API</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-light font-monospace text-muted" value="<?= htmlspecialchars($u['api_token'] ?? 'Aucun') ?>" readonly>
                            <button type="submit" name="regen_token" class="btn btn-outline-secondary" title="Régénérer"><i class="bi bi-arrow-repeat"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" name="update_user" class="btn btn-primary rounded-pill px-4 fw-bold">Enregistrer</button>
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
</script>