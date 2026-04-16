<?php
use App\Config;

?>

<div class="row justify-content-center align-items-center min-vh-50 mt-4 fade-in-slide">
    <div class="col-lg-8 col-xl-7">
        <div class="card-refined overflow-hidden border-0 shadow-lg bg-white" style="border-radius: 24px;">
            <div class="row g-0">

                <!-- Left Decorative Column -->
                <div class="col-md-5 d-none d-md-flex flex-column justify-content-center align-items-center text-center p-4 text-white"
                    style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);">

                    <div class="position-relative z-1 mb-4">
                        <img src="/favicon.png" alt="Logo" width="70" height="70"
                            class="bg-white p-3 rounded-circle shadow-lg mb-3">
                        <h2 class="fw-bold h2">Flow</h2>
                    </div>

                    <div class="mt-4 border-top border-white border-opacity-25 pt-3">
                        <small class="opacity-50 fw-bold text-uppercase" style="letter-spacing: 2px;">SAFO</small>
                    </div>
                </div>

                <!-- Right Form Column -->
                <div class="col-md-7 p-4 p-lg-4" style="background-color: #e2e8f0; border-left: 1px solid rgba(0,0,0,0.1);">
                    <div class="text-center text-md-start mb-3">
                        <h3 class="fw-bold h4 mb-1 text-dark">Connectez-vous</h3>
                        <p class="text-secondary small mb-0">Accédez à votre espace sécurisé.</p>
                    </div>

                    <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 py-2 mb-3 small">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?= htmlspecialchars($error)?>
                    </div>
                    <?php
endif; ?>

                    <?php if (Config::get('GOOGLE_CLIENT_ID')): ?>
                    <div class="d-grid mb-2">
                        <a href="/google/login"
                            class="btn bg-white border-0 py-2 rounded-3 shadow-sm fw-bold small transition-all text-dark">
                            <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" width="16"
                                class="me-2" alt="">
                            Continuer avec Google
                        </a>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <hr class="flex-grow-1 border-muted opacity-25 my-0">
                        <span class="px-2 text-muted small text-uppercase fw-bold"
                            style="font-size: 0.55rem; letter-spacing: 1px;">ou</span>
                        <hr class="flex-grow-1 border-muted opacity-25 my-0">
                    </div>
                    <?php
endif; ?>

                    <form id="login-form" method="post">
                        <div class="mb-2">
                            <label for="login" class="form-label small fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.65rem;">Identifiant</label>
                            <input type="text" name="login" id="login" autocomplete="username"
                                class="form-control py-2 px-3 rounded-3 border-0 shadow bg-white small"
                                placeholder="Email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.65rem;">Mot de passe</label>
                            <input type="password" name="password" id="password" autocomplete="current-password"
                                class="form-control py-2 px-3 rounded-3 border-0 shadow bg-white small"
                                placeholder="••••••••" required>
                        </div>

                        <?php \App\Controllers\AuthController::getCsrfInput(); ?>

                        <div class="d-grid">
                            <input type="submit" value="Se connecter" class="btn btn-primary-refined py-2 rounded-3 fw-bold shadow-lg">
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .min-vh-50 {
        min-height: 50vh;
    }

    .transition-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    }
</style>