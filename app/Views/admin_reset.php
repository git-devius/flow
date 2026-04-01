<?php
/**
 * VUE : RÉINITIALISATION DE LA BASE
 */
?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h2 mb-0 fw-bold">Réinitialisation de la base</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Administration</li>
                <li class="breadcrumb-item active" aria-current="page">Réinitialisation</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-0">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-danger bg-opacity-10 p-2 rounded-3 text-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h5 class="mb-0 fw-bold">Zone de danger</h5>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-danger border-0 rounded-4 p-4 mb-4">
            <h4 class="alert-heading fw-bold d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-shield-lock-fill"></i>
                Action irréversible
            </h4>
            <p class="mb-0">
                La réinitialisation supprimera <strong>définitivement</strong> :
            </p>
            <ul class="mt-3 mb-0">
                <li>Toutes les demandes (Investissements, Congés, Frais)</li>
                <li>Tous les historiques de validation et d'audit</li>
                <li>Tous les pôles et toutes les sociétés</li>
                <li>Tous les comptes utilisateurs <strong>sauf</strong> l'administrateur principal (admin.flow@groupesafo.com)</li>
            </ul>
        </div>

        <div class="text-center py-4">
            <p class="text-muted mb-4">Pour confirmer, veuillez cliquer sur le bouton ci-dessous.</p>
            <form method="POST" action="/admin/reset_db" onsubmit="return confirm('Êtes-vous ABSOLUMENT SÛR de vouloir tout réinitialiser ? Cette action est irréversible et supprimera TOUTES les données de la base.');">
                <?= \App\Helpers\CSRF::getField() ?>
                <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 py-3 shadow-sm hover-lift">
                    <i class="bi bi-trash3-fill me-2"></i>
                    Réinitialiser tout le système
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.hover-lift:hover {
    transform: translateY(-3px);
}
</style>
