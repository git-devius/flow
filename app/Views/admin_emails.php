<?php /** Vue Queue Email */ ?>
<h1 class="mb-4"><i class="bi bi-envelope"></i> File d'attente Emails</h1>

<?php if(isset($_SESSION['success'])): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success']); endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark"><strong>En attente / Échoués</strong></div>
            <ul class="list-group list-group-flush">
                <?php if(empty($queued) && empty($failed)): ?>
                    <li class="list-group-item text-muted">Aucun email en attente.</li>
                <?php else: ?>
                    <?php foreach($failed as $f): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-danger">Échec</span> 
                                <small><?= htmlspecialchars(basename($f)) ?></small>
                            </div>
                            <form method="post" class="d-inline">
                                <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                                <input type="hidden" name="filename" value="<?= htmlspecialchars(basename($f)) ?>">
                                <button type="submit" name="retry" class="btn btn-sm btn-outline-primary" title="Réessayer"><i class="bi bi-arrow-clockwise"></i></button>
                                <button type="submit" name="delete" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php foreach($queued as $q): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><span class="badge bg-info">Queue</span> <small><?= htmlspecialchars(basename($q)) ?></small></div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>