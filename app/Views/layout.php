<?php
/**
 * LAYOUT PRINCIPAL - MODERN REFINED THEME
 */
use App\Models\Request; 

$user = $_SESSION['user'] ?? null; 
$isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
$workflowTypes = Request::WORKFLOW_TYPES ?? []; 

// Génération des initiales
$userInitials = 'U';
if ($user && !empty($user['name'])) {
    $parts = explode(' ', trim($user['name']));
    $userInitials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $userInitials .= strtoupper(substr(end($parts), 0, 1));
    }
}

// Détection de la page active pour le menu
$current_page = $_SERVER['REQUEST_URI'];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Flow | Workspace</title>
  <link rel="icon" type="image/png" href="/favicon.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div id="app-wrapper">
  
  <?php if($user): ?>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
  <!-- SIDEBAR -->
  <aside id="sidebar">
    <a href="/dashboard" class="sidebar-brand">
        <img src="/favicon.png" alt="Logo" width="28" height="28" class="rounded">
        <span>Flow</span>
    </a>
    
    <nav class="nav-menu">
      <small>Menu Principal</small>
      <a href="/dashboard" class="nav-link-refined <?= $current_page === '/dashboard' ? 'active' : '' ?>" title="Dashboard" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-grid"></i> <span>Dashboard</span>
      </a>
      <a href="/list?workflow_type=all" class="nav-link-refined <?= strpos($current_page, '/list') !== false ? 'active' : '' ?>" title="Toutes les demandes" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-files"></i> <span>Toutes les demandes</span>
      </a>

      <small>Workflows</small>
      <?php foreach ($workflowTypes as $id => $label): 
        if (!\App\Services\AuthorizationService::canCreateRequest($user, $id)) continue;
      ?>
      <a href="/list?workflow_type=<?= $id ?>" class="nav-link-refined <?= strpos($current_page, 'workflow_type='.$id) !== false ? 'active' : '' ?>" title="<?= htmlspecialchars($label) ?>" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-record-circle"></i> <span><?= htmlspecialchars($label) ?></span>
      </a>
      <?php endforeach; ?>

      <?php if($isAdmin): ?>
      <small>Administration</small>
      <a href="/admin/users" class="nav-link-refined <?= strpos($current_page, '/admin/users') !== false ? 'active' : '' ?>" title="Utilisateurs" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-people"></i> <span>Utilisateurs</span>
      </a>
      <a href="/admin/poles" class="nav-link-refined <?= strpos($current_page, '/admin/poles') !== false ? 'active' : '' ?>" title="Pôles" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-diagram-3"></i> <span>Pôles</span>
      </a>
      <a href="/admin/companies" class="nav-link-refined <?= strpos($current_page, '/admin/companies') !== false ? 'active' : '' ?>" title="Sociétés" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-building"></i> <span>Sociétés</span>
      </a>
      <a href="/admin/workflow_validators" class="nav-link-refined <?= strpos($current_page, '/admin/workflow_validators') !== false ? 'active' : '' ?>" title="Validateurs" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-person-check"></i> <span>Validateurs</span>
      </a>
      <a href="/admin/emails" class="nav-link-refined <?= strpos($current_page, '/admin/emails') !== false ? 'active' : '' ?>" title="Logs Emails" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-envelope-at"></i> <span>Logs Emails</span>
      </a>
      <a href="/admin/templates" class="nav-link-refined <?= strpos($current_page, '/admin/templates') !== false ? 'active' : '' ?>" title="Templates Mail" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-file-earmark-code"></i> <span>Templates Mail</span>
      </a>
      <a href="/admin/reset_db" class="nav-link-refined <?= strpos($current_page, '/admin/reset_db') !== false ? 'active' : '' ?> text-danger" title="Réinitialisation" data-bs-toggle="tooltip" data-bs-placement="right">
        <i class="bi bi-trash"></i> <span>Réinitialisation</span>
      </a>
      <?php endif; ?>
    </nav>

    <div class="mt-auto p-3 border-top bg-light bg-opacity-50 overflow-hidden">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar-circle bg-vibrant-purple text-white flex-shrink-0">
          <?= $userInitials ?>
        </div>
        <div class="flex-grow-1 overflow-hidden userInfoContainer">
          <div class="fw-bold small text-truncate" style="font-size: 0.8rem;"><?= htmlspecialchars($user['name']) ?></div>
          <div class="text-muted" style="font-size: 0.7rem;"><?= ucfirst($user['role']) ?></div>
        </div>
        <a href="/logout" class="text-muted flex-shrink-0" title="Déconnexion"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    </div>
  </aside>
  <?php endif; ?>

  <!-- MAIN CONTENT -->
  <main id="main-content">
    
    <?php if($user): ?>
    <header id="top-header">
       <div class="d-flex align-items-center w-100 h-100">
         <button class="btn btn-light border-0 shadow-none me-3" type="button" id="sidebarToggle">
           <i class="bi bi-list fs-5"></i>
         </button>
       </div>
    </header>
    <?php endif; ?>

    <div class="flex-grow-1 px-3 px-lg-5 py-4">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 px-4 py-3 mb-4">
            <div class="d-flex align-items-center">
              <i class="bi bi-check-circle-fill fs-4 me-3"></i>
              <div><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4 px-4 py-3 mb-4">
            <div class="d-flex align-items-center">
              <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
              <div><?= htmlspecialchars($_SESSION['warning']); unset($_SESSION['warning']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 px-4 py-3 mb-4">
            <div class="d-flex align-items-center">
              <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
              <div><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php 
      if (isset($content)) {
          echo $content;
      } elseif (function_exists('the_view_content')) {
          the_view_content(); 
      }
      ?>
    </div>

    <footer class="p-4 border-top text-center bg-white mt-auto">
      <span class="text-muted small">Flow &copy; <?= date('Y') ?> &bull; Tous droits réservés</span>
    </footer>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const body = document.body;

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(item => new bootstrap.Tooltip(item, { trigger: 'hover' }));
    
    const updateTooltips = () => {
        const isCollapsed = body.classList.contains('sidebar-collapsed') && window.innerWidth >= 992;
        tooltipList.forEach(t => isCollapsed ? t.enable() : t.disable());
    };

    const toggleMobileSidebar = () => {
        sidebar.classList.toggle('show');
        if (backdrop) backdrop.classList.toggle('show');
    };

    if (toggle && sidebar) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.innerWidth >= 992) {
                body.classList.toggle('sidebar-collapsed');
                updateTooltips();
            } else {
                toggleMobileSidebar();
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', toggleMobileSidebar);
    }

    updateTooltips();
});
</script>
</body>
</html>