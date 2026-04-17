<?php /** Vue Templates */ ?>
<h1 class="mb-4"><i class="bi bi-file-earmark-code"></i> Templates HTML</h1>

<?php if(isset($_SESSION['success'])): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success']); endif; ?>

<div class="row">
<div class="row mb-3">
    <div class="col-md-4">
        <select class="form-select form-select-sm shadow-sm mb-3" id="templateFilter">
            <option value="all">Tous les workflows</option>
            <option value="investment">Investissement</option>
            <option value="vacation">Congés</option>
            <option value="expense">Notes de frais</option>
            <option value="general">Général (Sans suffixe)</option>
        </select>
        <div class="list-group shadow-sm" id="templateList">
            <?php foreach($templates as $name => $content): 
                $wfClass = 'general';
                if (strpos($name, '_investment') !== false) $wfClass = 'investment';
                elseif (strpos($name, '_vacation') !== false) $wfClass = 'vacation';
                elseif (strpos($name, '_expense') !== false) $wfClass = 'expense';
            ?>
                <a href="#" class="list-group-item list-group-item-action template-item" 
                   data-workflow="<?= $wfClass ?>"
                   onclick="loadTemplate('<?= htmlspecialchars($name) ?>', `<?= base64_encode($content) ?>`)">
                    <i class="bi bi-file-earmark-code me-2 text-primary opacity-50"></i> <?= htmlspecialchars($name) ?>
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
document.getElementById('templateFilter').addEventListener('change', function() {
    const filter = this.value;
    const items = document.querySelectorAll('.template-item');
    items.forEach(item => {
        if (filter === 'all' || item.getAttribute('data-workflow') === filter) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function loadTemplate(name, contentBase64) {
    document.getElementById('template_name').value = name;
    document.getElementById('display_name').innerText = name;
    document.getElementById('template_content').value = atob(contentBase64);
    document.getElementById('btn_save').disabled = false;
    
    // Activer l'élément visuellement
    document.querySelectorAll('.template-item').forEach(i => i.classList.remove('active', 'bg-primary', 'text-white'));
    event.currentTarget.classList.add('active', 'bg-primary', 'text-white');
}
</script>