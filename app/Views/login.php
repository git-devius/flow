<?php 
use App\Config;
/**
 * VUE LOGIN - DESIGN MODERNE
 */
?>

<div class="row justify-content-center align-items-center min-vh-75 mt-4">
    <div class="col-lg-10 col-xl-9">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="row g-0">
                
                <div class="col-md-6 bg-primary text-white p-5 position-relative d-none d-md-flex flex-column justify-content-center align-items-center text-center">
                    <i class="bi bi-shield-lock-fill position-absolute top-50 start-50 translate-middle text-white opacity-10" style="font-size: 15rem;"></i>
                    <!-- <i class="bi bi-graph-up-arrow position-absolute bottom-0 start-0 mb-4 ms-4 text-white opacity-25" style="font-size: 3rem;"></i> -->
                    
                    <!-- <div class="position-relative z-1">
                        <div class="mb-4">
                            <i class="bi bi-briefcase-fill fs-1 bg-white text-primary p-3 rounded-circle shadow-sm"></i>
                        </div>
                        <h2 class="fw-bold display-6">Bienvenue sur Flow</h2>
                        <p class="lead opacity-75 mt-3">
                        </p>
                    </div> -->
                    
                    <div class="mt-5 small opacity-50">
                        <!-- &copy; <?= date('Y') ?> Flow System -->
                    </div>
                </div>

                <div class="col-md-6 bg-white p-4 p-lg-5">
                    <div class="d-flex align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-secondary">Connexion</h3>
                    </div>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4">
                            <i class="bi bi-exclamation-octagon-fill me-2"></i>
                            <div><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (Config::get('GOOGLE_CLIENT_ID')): ?>
                        <div class="d-grid mb-4">
                            <a href="/google/login" class="btn btn-outline-dark btn-lg py-2 rounded-3 hover-shadow transition-all position-relative">
                                <i class="bi bi-google text-danger position-absolute start-0 ms-3"></i>
                                <span class="fw-semibold"> Google</span>
                            </a>
                        </div>
                        
                        <div class="d-flex align-items-center mb-4">
                            <hr class="flex-grow-1 m-0 text-muted">
                            <span class="px-3 text-muted small text-uppercase fw-bold">Ou avec email</span>
                            <hr class="flex-grow-1 m-0 text-muted">
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="/login">
                        <?php \App\Controllers\AuthController::getCsrfInput(); ?>
                        
                        <div class="form-floating mb-3">
                            <input type="email" name="email" id="email" class="form-control rounded-3" placeholder="nom@exemple.com" required>
                            <label for="email" class="text-muted"><i class="bi bi-envelope me-1"></i> Adresse Email</label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="password" name="password" id="password" class="form-control rounded-3" placeholder="Mot de passe" required>
                            <label for="password" class="text-muted"><i class="bi bi-key me-1"></i> Mot de passe</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-3 shadow-sm transition-all">
                                Se connecter <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted small">
                
            </p>
        </div>
    </div>
</div>

<style>
    /* Petits ajustements CSS inline pour la page de login */
    .min-vh-75 { min-height: 75vh; }
    .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; background-color: #f8f9fa; }
    .transition-all { transition: all 0.3s ease; }
    .opacity-10 { opacity: 0.1; }
    .opacity-25 { opacity: 0.25; }
    /* Animation douce sur le bouton submit */
    .btn-primary:hover { transform: translateY(-2px); }
</style>