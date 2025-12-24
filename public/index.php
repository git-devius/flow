<?php
/**
 * InvestFlow - Point d'entrée principal (Routeur Bramus)
 */

// Sécurisation des cookies de session
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
// Si HTTPS est disponible, décommenter la ligne suivante :
// ini_set('session.cookie_secure', '1');

session_start();

// Chargement des dépendances avec vérification
$paths = [
    __DIR__ . '/../app/Config.php',
    __DIR__ . '/../app/Database.php',
    __DIR__ . '/../vendor/autoload.php'
];

foreach ($paths as $path) {
    if (!file_exists($path)) {
        die("Erreur critique: Fichier manquant " . basename($path));
    }
    require $path;
}

use Bramus\Router\Router;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\RequestController;
use App\Models\Request;
use App\Models\Pole;
use App\Models\Company;
use App\Models\Approval;
use App\Services\AuthorizationService;
use App\Queue\EmailQueue;

// =============================================
// GESTION DE LA RETRO-COMPATIBILITÉ (Legacy Redirects)
// =============================================
/**
 * Cette section gère la redirection des anciennes URLs type ?action=login
 * vers les nouvelles routes RESTful.
 */
function handleLegacyRoutes() {
    $uri = $_SERVER['REQUEST_URI'];
    
    // Si on détecte un paramètre 'action', c'est une ancienne route
    if (isset($_GET['action'])) {
        $map = [
            'login' => '/login',
            'logout' => '/logout',
            'dashboard' => '/dashboard',
            'home' => '/dashboard',
            'google_login' => '/google/login',
            'google_callback' => '/google/callback',
            'list' => '/list',
            'create' => '/create',
            'view' => '/view',
            'export_csv' => '/export_csv',
            'admin_users' => '/admin/users',
            'admin_poles' => '/admin/poles',
            'admin_companies' => '/admin/companies',
            'admin_workflow_validators' => '/admin/workflow_validators', 
            'admin_emails' => '/admin/emails',
            'admin_templates' => '/admin/templates'
        ];

        $action = $_GET['action'];
        if (isset($map[$action])) {
            $newUrl = $map[$action];
            $queryParams = $_GET;
            unset($queryParams['action']); // On retire 'action'
            
            if (!empty($queryParams)) {
                $newUrl .= '?' . http_build_query($queryParams);
            }
            
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $newUrl);
            exit;
        }
    }

    // Redirection propre de /index.php vers la racine pour éviter le duplicate content
    if (strpos($uri, '/index.php') !== false) { 
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /"); 
        exit; 
    }
}

handleLegacyRoutes();

// =============================================
// INITIALISATION DU ROUTEUR
// =============================================
$router = new Router();

// =============================================
// MIDDLEWARES DE SÉCURITÉ
// =============================================

// Protection Admin
$router->before('GET|POST', '/admin/.*', function() {
    if (!AuthController::check()) { 
        header('Location: /login'); 
        exit; 
    }
    if (!AuthController::isAdmin()) {
        $_SESSION['error'] = "Accès non autorisé.";
        header('Location: /dashboard');
        exit;
    }
});

// Protection Utilisateur Connecté
$router->before('GET|POST', '/(dashboard|request|list|create|view|cancel|export).*', function() {
    if (!AuthController::check()) { 
        $_SESSION['error'] = "Veuillez vous connecter.";
        header('Location: /login'); 
        exit; 
    }
});

// =============================================
// ROUTES : AUTHENTIFICATION
// =============================================
$router->get('/', function() { 
    if(AuthController::check()) { header('Location: /dashboard'); } else { header('Location: /login'); }
});
$router->get('/login', 'App\Controllers\AuthController@showLogin');
$router->post('/login', 'App\Controllers\AuthController@processLogin');
$router->get('/logout', 'App\Controllers\AuthController@logoutAction');
$router->get('/google/login', 'App\Controllers\AuthController@startGoogleAuth');
$router->get('/google/callback', 'App\Controllers\AuthController@processGoogleCallback');

// =============================================
// ROUTES : APPLICATION
// =============================================
$router->get('/dashboard', 'App\Controllers\HomeController@dashboard');

$router->get('/list', function() {
    $user = AuthController::user();
    $workflowType = $_GET['workflow_type'] ?? 'investment';
    $filterSubmitted = isset($_GET['filter_submitted']);
    
    // Assainissement basique des entrées GET
    $filters = [
        'workflow_type' => $workflowType,
        'search' => strip_tags($_GET['search'] ?? ''),
        'type' => strip_tags($_GET['type'] ?? ''),
        'status' => strip_tags($_GET['status'] ?? ''), 
        'pole_id' => filter_var($_GET['pole_id'] ?? '', FILTER_SANITIZE_NUMBER_INT),
        'company_id' => filter_var($_GET['company_id'] ?? '', FILTER_SANITIZE_NUMBER_INT),
        'min_amount' => filter_var($_GET['min_amount'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'max_amount' => filter_var($_GET['max_amount'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'open_only' => $filterSubmitted ? (isset($_GET['open_only']) ? 1 : 0) : 1,
        'not_cancelled_only' => $filterSubmitted ? (isset($_GET['not_cancelled_only']) ? 1 : 0) : 1
    ];
    
    if (!empty($filters['status'])) { 
        $filters['open_only'] = 0; 
        $filters['not_cancelled_only'] = 0; 
    }

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $totalItems = Request::count($user, $filters);
    $pagination = new \App\Helpers\Pagination($totalItems, 20, $page);
    
    $orderBy = in_array($_GET['order_by'] ?? '', ['created_at', 'amount', 'status']) ? $_GET['order_by'] : 'created_at';
    $orderDir = ($_GET['order_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $investments = Request::search($user, $filters, $orderBy, $orderDir, $pagination->getLimit(), $pagination->getOffset());
    
    $poles = Pole::all(); 
    $companies = Company::all(); 
    $types = Request::getInvestmentTypes(); 
    
    ob_start(); 
    include __DIR__.'/../app/Views/list.php'; 
    $content = ob_get_clean(); 
    include __DIR__.'/../app/Views/layout.php';
});

// ROUTES : GESTION DES DEMANDES
// CREATE
$router->match('GET|POST', '/create', function() {
    $user = AuthController::user();
    $workflowType = $_GET['workflow_type'] ?? 'investment';
    
    // Vérification stricte des droits
    if (!AuthorizationService::canCreateRequest($user, $workflowType)) {
        $_SESSION['error'] = "Droits insuffisants pour créer ce type de demande.";
        header('Location: /dashboard'); exit;
    }
    
    $poles = Pole::all();
    $companies = Company::all();
    
    if($_SERVER['REQUEST_METHOD']==='POST'){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            
            $postWorkflowType = $_POST['workflow_type'] ?? 'investment';
            // Double vérification sur le type envoyé en POST
            if ($postWorkflowType !== $workflowType && !AuthorizationService::canCreateRequest($user, $postWorkflowType)) {
                throw new Exception("Tentative de modification de workflow non autorisée.");
            }
            
            $file_path = null;
            if(!empty($_FILES['attachment']['name'])){
                $uploadDir = \App\Config::get('UPLOAD_DIR', __DIR__.'/../uploads');
                $file_path = \App\Helpers\FileUpload::upload($_FILES['attachment'], $uploadDir);
            }
            
            $data = [
                'pole_id' => (int)$_POST['pole_id'],
                'company_id' => (int)$_POST['company_id'],
                'workflow_type' => $postWorkflowType,
                'type' => strip_tags($_POST['type']),
                'budget_planned' => (int)($_POST['budget_planned'] ?? 0),
                'objective' => strip_tags($_POST['objective']),
                'start_date_duration' => strip_tags($_POST['start_date_duration']),
                'amount' => (float)$_POST['amount'],
                'file_path' => $file_path
            ];
            
            $newId = RequestController::createRequest($data, $user['id']);
            $_SESSION['success'] = 'Demande créée avec succès';
            header('Location: /view?id='.$newId); exit;
        } catch(Exception $e){ 
            $error = $e->getMessage(); 
        }
    }
    ob_start(); include __DIR__.'/../app/Views/create.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// VIEW
$router->match('GET|POST', '/view', function() {
    $id = (int)($_GET['id'] ?? 0);
    $investment = Request::find($id);
    
    if(!$investment){
        $_SESSION['error'] = 'Demande introuvable'; 
        header('Location: /dashboard'); exit;
    }
    
    $user = AuthController::user();
    if (!AuthorizationService::canViewRequest($user, $investment)) {
        $_SESSION['error'] = 'Accès refusé.'; 
        header('Location: /dashboard'); exit;
    }
    
    $approvals = Approval::forInvestment($id);
    
    if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['decision'])){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            RequestController::approve(
                $id, 
                $user['id'], 
                (int)$_POST['level'], 
                $_POST['decision'], 
                strip_tags($_POST['comment'] ?? '')
            );
            $_SESSION['success'] = 'Décision enregistrée';
            header('Location: /view?id='.$id); exit;
        } catch(Exception $e){ 
            $error = $e->getMessage(); 
        }
    }
    ob_start(); include __DIR__.'/../app/Views/view.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// CANCEL
$router->post('/cancel', function() {
    try {
        \App\Helpers\CSRF::validateCsrfToken();
        RequestController::handleCancel((int)($_POST['id'] ?? 0));
        // La méthode handleCancel fait déjà un redirect, mais au cas où :
        header('Location: /dashboard'); exit;
    } catch(Exception $e){
        $_SESSION['error'] = $e->getMessage();
        if(isset($_POST['id'])) { 
            header('Location: /view?id='.(int)$_POST['id']); 
        } else { 
            header('Location: /dashboard'); 
        }
        exit;
    }
});

// EXPORT CSV
$router->get('/export_csv', function() {
    // Utilisation directe du contrôleur pour centraliser la logique
    // RequestController utilise le nouveau ExportService
    RequestController::export();
    exit;
});

// =============================================
// ROUTES : ADMINISTRATION
// =============================================

// USERS
$router->match('GET|POST', '/admin/users', function() {
    $users = AdminController::listUsers();
    $workflowTypes = defined('\App\Models\Request::WORKFLOW_TYPES') ? \App\Models\Request::WORKFLOW_TYPES : ['investment', 'expense', 'vacation'];
    
    if($_SERVER['REQUEST_METHOD']==='POST'){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            
            if(isset($_POST['create_user'])) {
                $allowed = isset($_POST['allowed_workflows']) ? implode(',', $_POST['allowed_workflows']) : '';
                AdminController::createUser(
                    $_POST['email'], 
                    $_POST['password'], 
                    $_POST['name'], 
                    $_POST['role'], 
                    $allowed
                );
                $_SESSION['success'] = 'Utilisateur créé';
                
            } elseif(isset($_POST['update_user'])){
                $pass = !empty($_POST['password']) ? $_POST['password'] : null;
                $allowed = isset($_POST['allowed_workflows']) ? implode(',', $_POST['allowed_workflows']) : '';
                AdminController::updateUser(
                    (int)$_POST['id'], 
                    $_POST['name'], 
                    $_POST['role'], 
                    $pass, 
                    $allowed
                );
                $_SESSION['success'] = 'Utilisateur mis à jour';
                
            } elseif(isset($_POST['delete_user'])){
                AdminController::deleteUser((int)$_POST['id']);
                $_SESSION['success'] = 'Utilisateur supprimé';
                
            } elseif(isset($_POST['regen_token'])){
                $newToken = \App\Models\User::regenToken((int)$_POST['id']);
                $_SESSION['success'] = 'Token régénéré: '.$newToken;
                
            } elseif(isset($_POST['toggle_status'])){
                AdminController::toggleUserStatus((int)$_POST['user_id'], $_POST['action_type']);
                $_SESSION['success'] = 'Statut utilisateur mis à jour.';
            }
            
            header('Location: /admin/users'); exit;
        } catch(\Exception $e){ 
            $error = $e->getMessage(); 
        }
    }
    ob_start(); include __DIR__.'/../app/Views/admin_users.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// POLES
$router->match('GET|POST', '/admin/poles', function() {
    $poles = AdminController::poles();
    if($_SERVER['REQUEST_METHOD']==='POST'){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            if(isset($_POST['create_pole'])){
                AdminController::createPole($_POST['name']);
                $_SESSION['success'] = 'Pôle créé';
            } elseif(isset($_POST['update_pole'])){
                AdminController::updatePole((int)$_POST['id'], $_POST['name']);
                $_SESSION['success'] = 'Pôle mis à jour';
            } elseif(isset($_POST['delete_pole'])){
                AdminController::deletePole((int)$_POST['id']);
                $_SESSION['success'] = 'Pôle supprimé';
            }
            header('Location: /admin/poles'); exit;
        } catch(\Exception $e){ $error = $e->getMessage(); }
    }
    ob_start(); include __DIR__.'/../app/Views/admin_poles.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// COMPANIES
$router->match('GET|POST', '/admin/companies', function() {
    $companies = AdminController::companies();
    $poles = AdminController::poles();
    if($_SERVER['REQUEST_METHOD']==='POST'){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            if(isset($_POST['create_company'])){
                AdminController::createCompany((int)$_POST['pole_id'], $_POST['name']);
                $_SESSION['success'] = 'Société créée';
            } elseif(isset($_POST['update_company'])){
                AdminController::updateCompany((int)$_POST['id'], (int)$_POST['pole_id'], $_POST['name']);
                $_SESSION['success'] = 'Société mise à jour';
            } elseif(isset($_POST['delete_company'])){
                AdminController::deleteCompany((int)$_POST['id']);
                $_SESSION['success'] = 'Société supprimée';
            }
            header('Location: /admin/companies'); exit;
        } catch(\Exception $e){ $error = $e->getMessage(); }
    }
    ob_start(); include __DIR__.'/../app/Views/admin_companies.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// WORKFLOW VALIDATORS
$router->match('GET|POST', '/admin/workflow_validators', function() {
    AdminController::validators();
});

// EMAILS
$router->match('GET|POST', '/admin/emails', function() {
    $queued = EmailQueue::listQueued();
    $failed = EmailQueue::listFailed();
    if($_SERVER['REQUEST_METHOD']==='POST'){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            if(isset($_POST['retry'])){
                EmailQueue::retry($_POST['filename']);
                $_SESSION['success'] = 'Email remis en queue';
            } elseif(isset($_POST['delete'])){
                EmailQueue::delete($_POST['filename']);
                $_SESSION['success'] = 'Email supprimé';
            }
            header('Location: /admin/emails'); exit;
        } catch(\Exception $e){ $error = $e->getMessage(); }
    }
    ob_start(); include __DIR__.'/../app/Views/admin_emails.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// TEMPLATES
$router->match('GET|POST', '/admin/templates', function() {
    $templates_dir = __DIR__.'/../app/Mail/templates';
    $templates = [];
    if(is_dir($templates_dir)){
        foreach(glob($templates_dir.'/*.html') as $file){
            $templates[basename($file)] = file_get_contents($file);
        }
    }
    if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_template'])){
        try {
            \App\Helpers\CSRF::validateCsrfToken();
            $filename = basename($_POST['template_name']); // Sécurité Path Traversal
            if(preg_match('/^[a-z_]+\.html$/', $filename) && file_exists($templates_dir.'/'.$filename)){
                file_put_contents($templates_dir.'/'.$filename, $_POST['content']);
                $_SESSION['success'] = 'Template enregistré';
                header('Location: /admin/templates'); exit;
            } else {
                throw new Exception("Nom de template invalide ou inexistant.");
            }
        } catch(\Exception $e){ $error = $e->getMessage(); }
    }
    ob_start(); include __DIR__.'/../app/Views/admin_templates.php'; $content = ob_get_clean(); include __DIR__.'/../app/Views/layout.php';
});

// 404 Handler
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo '<div style="margin:20px;font-family:sans-serif;"><h1>404 - Page introuvable</h1><p>Route inconnue : '.htmlspecialchars($_SERVER['REQUEST_URI']).'</p><a href="/dashboard">Retour au tableau de bord</a></div>';
});

$router->run();