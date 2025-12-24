<?php
/**
 * LAYOUT PRINCIPAL - THÈME "BLUE FLOW"
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
?>
<!doctype html>
<html lang="fr" class="h-100">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Flow</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">

  <style>
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #f8f9fa; 
    }
    
    /* NAVIGATION PERSONNALISÉE */
    .navbar {
        /* Couleur Primaire avec un léger dégradé pour la profondeur */
        background: linear-gradient(135deg, var(--bs-primary) 0%, #0d6efd 100%);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }

    /* Le Logo en Blanc/Bleu inversé */
    .brand-icon {
        background-color: white;
        color: var(--bs-primary);
        width: 32px; 
        height: 32px;
    }

    /* Liens de navigation */
    .navbar-dark .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }
    .navbar-dark .navbar-nav .nav-link:hover, 
    .navbar-dark .navbar-nav .nav-link.active {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Dropdowns */
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
        border-radius: 0.75rem;
        margin-top: 0.5rem !important;
    }
    .dropdown-item {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin: 0 0.5rem;
        width: auto;
    }
    
    /* Avatar inversé (Fond blanc, texte bleu) */
    .avatar-circle {
        width: 38px;
        height: 38px;
        background-color: white;
        color: var(--bs-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        border: 2px solid rgba(255,255,255,0.2);
    }
  </style>
</head>
<body class="d-flex flex-column h-100">

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    
    <a class="navbar-brand d-flex align-items-center fw-bold text-white" href="/dashboard">
      <!-- <div class="brand-icon rounded p-1 me-2 d-flex align-items-center justify-content-center">
          <i class="bi bi-briefcase-fill small"></i>
      </div> -->
      Flow
    </a>
    
    <button class="navbar-toggler border-0 bg-white bg-opacity-10" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="nav">
      <?php if($user): ?>
        
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
          <li class="nav-item">
            <a class="nav-link px-3" href="/dashboard">
              <i class="bi bi-grid-fill me-1"></i> Accueil
            </a>
          </li>
          
          <?php if($isAdmin): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle px-3" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-sliders me-1"></i> Administration
              </a>
              <ul class="dropdown-menu shadow-lg">
                <li><span class="dropdown-header text-uppercase small fw-bold text-primary">Organisation</span></li>
                <li><a class="dropdown-item" href="/admin/users"><i class="bi bi-people me-2 text-muted"></i>Utilisateurs</a></li>
                <li><a class="dropdown-item" href="/admin/poles"><i class="bi bi-diagram-3 me-2 text-muted"></i>Pôles</a></li>
                <li><a class="dropdown-item" href="/admin/companies"><i class="bi bi-building me-2 text-muted"></i>Sociétés</a></li>

                <?php if (!empty($workflowTypes)): ?>
                  <li><hr class="dropdown-divider my-2"></li>
                  <li><span class="dropdown-header text-uppercase small fw-bold text-primary">Configuration Workflows</span></li>
                  <?php foreach ($workflowTypes as $key => $name): ?>
                    <li><a class="dropdown-item" href="/admin/workflow_validators?workflow_type=<?= urlencode($key) ?>">
                      <i class="bi bi-check-all me-2 text-muted"></i>Valideurs <?= htmlspecialchars($name) ?>
                    </a></li>
                  <?php endforeach; ?>
                <?php endif; ?>
                
                <li><hr class="dropdown-divider my-2"></li>
                <li><span class="dropdown-header text-uppercase small fw-bold text-primary">Système</span></li>
                <li><a class="dropdown-item" href="/admin/emails"><i class="bi bi-envelope me-2 text-muted"></i>File d'attente E-mails</a></li>
                <li><a class="dropdown-item" href="/admin/templates"><i class="bi bi-code-square me-2 text-muted"></i>Templates HTML</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
        
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center py-0" href="#" data-bs-toggle="dropdown">
              <div class="text-end me-3 d-none d-lg-block text-white">
                  <div class="fw-bold small lh-1"><?= htmlspecialchars($user['name']) ?></div>
                  <div class="opacity-75 small" style="font-size: 0.75rem;">
                      <?php
                      $roles = [
                        'admin' => 'Administrateur',
                        'user' => 'Utilisateur',
                        'validator_lv1' => 'Valideur N1',
                        'validator_lv2' => 'Valideur N2'
                      ];
                      echo $roles[$user['role']] ?? 'Membre';
                      ?>
                  </div>
              </div>
              <div class="avatar-circle shadow-sm">
                  <?= htmlspecialchars($userInitials) ?>
              </div>
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg">
                <li class="d-lg-none">
                    <span class="dropdown-header"><?= htmlspecialchars($user['email']) ?></span>
                </li>
              <li>
                  <a class="dropdown-item text-danger rounded" href="/logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                  </a>
              </li>
            </ul>
          </li>
        </ul>
        
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="flex-shrink-0 mb-5">
    <div class="container py-4">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-danger border-4 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
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
</main>

<footer class="footer mt-auto py-4 bg-white border-top text-center">
  <div class="container">
    <span class="text-muted small">
        <!-- <i class="bi bi-briefcase-fill text-primary"></i>  -->
        <strong>Flow</strong> &copy; <?= date('Y') ?>
    </span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>