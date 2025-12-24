<?php /** Vue Templates */ ?>
<h1 class="mb-4"><i class="bi bi-file-earmark-code"></i> Templates HTML</h1>

<?php if(isset($_SESSION['success'])): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success']); endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="list-group">
            <?php foreach($templates as $name => $content): ?>
                <a href="#" class="list-group-item list-group-item-action" onclick="loadTemplate('<?= htmlspecialchars($name) ?>', `<?= base64_encode($content) ?>`)">
                    <i class="bi bi-file-earmark"></i> <?= htmlspecialchars($name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Éditeur</div>
            <div class="card-body">
                <form method="post">
                    <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                    <input type="hidden" name="template_name" id="template_name">
                    <div class="mb-3">
                        <label class="form-label">Fichier : <strong id="display_name">-</strong></label>
                        <textarea name="content" id="template_content" class="form-control font-monospace" rows="15"></textarea>
                    </div>
                    <button type="submit" name="save_template" class="btn btn-primary" id="btn_save" disabled>Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadTemplate(name, contentBase64) {
    document.getElementById('template_name').value = name;
    document.getElementById('display_name').innerText = name;
    document.getElementById('template_content').value = atob(contentBase64);
    document.getElementById('btn_save').disabled = false;
}
</script>