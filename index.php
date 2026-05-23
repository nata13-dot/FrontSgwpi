<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$serverAuthenticated = is_authenticated();
$serverDashboardUrl = dashboard_url();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Inicio</title>
    <?php if ($serverAuthenticated): ?>
    <script>
        (function () {
            window.location.replace('<?= $serverDashboardUrl ?>');
        })();
    </script>
    <?php endif; ?>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .login-modal {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .login-brand img {
            height: 54px;
            object-fit: contain;
        }

        .index-hero {
            background: url('/assets/img/ITSSMT/fondo.jpg');
            background-size: cover;
            background-position: center;
            min-height: 540px;
            padding: 96px 0 72px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .index-hero .overlay {
            background: linear-gradient(90deg, rgba(8, 26, 54, 0.88), rgba(8, 26, 54, 0.55), rgba(8, 26, 54, 0.22));
        }

        .index-hero-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
        }

        .repository-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .repository-spotlight {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        [data-theme="dark"] .repository-spotlight {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        }

        .repository-showcase {
            border: 1px solid rgba(27, 57, 106, 0.14);
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        [data-theme="dark"] .repository-showcase {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-showcase-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(27, 57, 106, 0.12);
            background: rgba(27, 57, 106, 0.05);
        }

        [data-theme="dark"] .repository-showcase-toolbar {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
        }

        .repository-search-preview {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
            color: #64748b;
            background: #ffffff;
            border: 1px solid rgba(100, 116, 139, 0.22);
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
            font-size: 0.92rem;
        }

        [data-theme="dark"] .repository-search-preview {
            color: #cbd5e1;
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-showcase-body {
            display: grid;
            grid-template-columns: 0.85fr 1.15fr;
            min-height: 310px;
        }

        .repository-list-preview {
            border-right: 1px solid rgba(27, 57, 106, 0.12);
            padding: 0.85rem;
        }

        [data-theme="dark"] .repository-list-preview {
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-file-row {
            display: flex;
            gap: 0.7rem;
            padding: 0.75rem;
            border-radius: 8px;
            align-items: flex-start;
        }

        .repository-file-row.active {
            background: rgba(27, 57, 106, 0.08);
        }

        [data-theme="dark"] .repository-file-row.active {
            background: rgba(96, 165, 250, 0.12);
        }

        .repository-file-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1B396A;
            color: #ffffff;
            flex: 0 0 auto;
        }

        .repository-reader-preview {
            padding: 1.1rem;
            background: linear-gradient(135deg, rgba(27, 57, 106, 0.08), rgba(255, 255, 255, 0));
        }

        [data-theme="dark"] .repository-reader-preview {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.12), rgba(17, 24, 39, 0));
        }

        .repository-reader-page {
            background: #ffffff;
            border: 1px solid rgba(100, 116, 139, 0.18);
            border-radius: 8px;
            min-height: 250px;
            padding: 1.1rem;
        }

        [data-theme="dark"] .repository-reader-page {
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-line {
            height: 10px;
            border-radius: 999px;
            background: #dbe4ef;
            margin-bottom: 0.7rem;
        }

        [data-theme="dark"] .repository-line {
            background: #334155;
        }

        .repository-benefit {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .repository-benefit i {
            color: #1B396A;
            font-size: 1.35rem;
            line-height: 1.2;
        }

        [data-theme="dark"] .repository-benefit i {
            color: #93c5fd;
        }

        .cookie-notice {
            position: fixed;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 1080;
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid rgba(27, 57, 106, 0.14);
            border-radius: 8px;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.18);
            padding: 1rem;
        }

        [data-theme="dark"] .cookie-notice {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.12);
            color: #f8fafc;
        }

        .cookie-notice[hidden] {
            display: none !important;
        }

        .cookie-notice p {
            margin: 0;
            color: inherit;
        }

        @media (min-width: 768px) {
            .cookie-notice {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .cookie-notice .btn {
                flex: 0 0 auto;
            }
        }

        @media (max-width: 991.98px) {
            .index-hero {
                min-height: 480px;
                padding: 76px 0 56px;
            }

            .repository-showcase-body {
                grid-template-columns: 1fr;
            }

            .repository-list-preview {
                border-right: 0;
                border-bottom: 1px solid rgba(27, 57, 106, 0.12);
            }
        }

        @media (max-width: 575.98px) {
            .index-hero {
                min-height: auto;
                padding: 64px 0 48px;
            }

            .index-hero h1 {
                font-size: 2.35rem;
            }

            .hero-actions .btn {
                width: 100%;
            }

            .repository-showcase-toolbar {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="index-hero">
        <div class="overlay"></div>
        <div class="container-xl">
            <div class="index-hero-content">
                <div class="repository-hero-kicker">
                    <i class="bi bi-archive"></i>
                    Repositorio institucional de proyectos integradores
                </div>
                <h1 class="display-3 fw-bold text-white mb-4">Consulta, conserva y comparte el conocimiento generado en cada proyecto</h1>
                <p class="lead text-white mb-4">Accede a documentos, entregables y evidencias academicas desde un repositorio central para estudiantes, docentes y evaluadores.</p>
                <div class="hero-actions">
                    <a href="/pages/repositorio.php" class="btn btn-light btn-lg">
                        <i class="bi bi-search"></i> Explorar repositorio
                    </a>
                    <button type="button" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar sesion
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="section repository-spotlight">
        <div class="container-xl">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <h2 class="section-title mb-3">Repositorio Digital</h2>
                    <p class="section-subtitle mb-4">Un espacio para encontrar proyectos, revisar entregables y mantener disponible la memoria academica del instituto.</p>
                    <div class="d-grid gap-3 mb-4">
                        <div class="repository-benefit">
                            <i class="bi bi-file-earmark-text"></i>
                            <div>
                                <h5 class="mb-1">Documentos organizados</h5>
                                <p class="text-muted mb-0">Consulta archivos por proyecto, categoria, periodo y datos clave.</p>
                            </div>
                        </div>
                        <div class="repository-benefit">
                            <i class="bi bi-eye"></i>
                            <div>
                                <h5 class="mb-1">Lectura rapida</h5>
                                <p class="text-muted mb-0">Previsualiza evidencias y materiales sin perder el contexto del sistema.</p>
                            </div>
                        </div>
                        <div class="repository-benefit">
                            <i class="bi bi-mortarboard"></i>
                            <div>
                                <h5 class="mb-1">Referencia academica</h5>
                                <p class="text-muted mb-0">Facilita que nuevas generaciones conozcan proyectos previos y buenas practicas.</p>
                            </div>
                        </div>
                    </div>
                    <a href="/pages/repositorio.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-archive"></i> Ir al repositorio
                    </a>
                </div>
                <div class="col-lg-7">
                    <div class="repository-showcase" aria-label="Vista previa del repositorio digital">
                        <div class="repository-showcase-toolbar">
                            <div class="repository-search-preview">
                                <i class="bi bi-search"></i>
                                Buscar por proyecto, autor, empresa o documento
                            </div>
                            <span class="badge bg-primary">Repositorio</span>
                        </div>
                        <div class="repository-showcase-body">
                            <div class="repository-list-preview">
                                <div class="repository-file-row active">
                                    <span class="repository-file-icon"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <div>
                                        <div class="fw-semibold">Proyecto integrador</div>
                                        <div class="text-muted small">Reporte final · Evidencia academica</div>
                                    </div>
                                </div>
                                <div class="repository-file-row">
                                    <span class="repository-file-icon"><i class="bi bi-file-earmark-slides"></i></span>
                                    <div>
                                        <div class="fw-semibold">Presentacion</div>
                                        <div class="text-muted small">Material de exposicion</div>
                                    </div>
                                </div>
                                <div class="repository-file-row">
                                    <span class="repository-file-icon"><i class="bi bi-tags"></i></span>
                                    <div>
                                        <div class="fw-semibold">Etiquetas y categorias</div>
                                        <div class="text-muted small">Busqueda por areas y entregables</div>
                                    </div>
                                </div>
                            </div>
                            <div class="repository-reader-preview">
                                <div class="repository-reader-page">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge bg-light text-dark">Vista de lectura</span>
                                        <span class="text-muted small">Documento consultable</span>
                                    </div>
                                    <div class="repository-line" style="width: 76%;"></div>
                                    <div class="repository-line" style="width: 92%;"></div>
                                    <div class="repository-line" style="width: 64%;"></div>
                                    <div class="repository-line mt-4" style="width: 88%;"></div>
                                    <div class="repository-line" style="width: 82%;"></div>
                                    <div class="repository-line" style="width: 54%;"></div>
                                    <div class="d-flex flex-wrap gap-2 mt-4">
                                        <span class="badge bg-secondary">PDF</span>
                                        <span class="badge bg-secondary">Entregable</span>
                                        <span class="badge bg-secondary">Proyecto</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="container-xl">
            <h2 class="section-title text-center mb-5">Gestion academica conectada al repositorio</h2>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-folder2" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Gestion de proyectos</h5>
                            <p class="text-muted">Administra proyectos integradores y conserva sus evidencias en un mismo flujo.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Evaluacion documentada</h5>
                            <p class="text-muted">Relaciona entregables, rubricas y resultados para dar seguimiento academico.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Colaboracion</h5>
                            <p class="text-muted">Facilita el trabajo entre estudiantes, docentes y responsables de evaluacion.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cookie-notice" id="cookieNotice" role="status" aria-live="polite" hidden>
        <p class="small">
            Este sistema usa cookies necesarias para mantener tu sesion iniciada y proteger el acceso a tu cuenta.
        </p>
        <button type="button" class="btn btn-primary btn-sm mt-3 mt-md-0" id="cookieNoticeAccept">
            Entendido
        </button>
    </div>

    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal p-3">
                <div class="modal-body">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div class="login-brand text-center mb-3">
                        <img src="/assets/img/ITSSMT/ITSSMT.png" alt="ITSSMT">
                        <h4 class="mt-2" id="loginModalLabel">Bienvenido</h4>
                        <p class="text-muted mb-0">Ingresa tus credenciales para continuar</p>
                    </div>

                    <div id="loginMessageContainer" class="mb-3"></div>

                    <form id="loginForm" class="needs-validation" novalidate>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="loginUserId" name="id" placeholder="No. de Control, No. de empleado" required autocomplete="username">
                            <label for="loginUserId">Numero de control / empleado</label>
                            <div class="invalid-feedback">Ingresa tu numero de control o empleado.</div>
                        </div>

                        <div class="mb-3 form-floating">
                            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Contraseña" required autocomplete="current-password">
                            <label for="loginPassword">Contraseña</label>
                            <div class="invalid-feedback">Ingresa tu contraseña.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="rememberCheck" checked>
                                <label class="form-check-label text-muted" for="rememberCheck">Recuerdame</label>
                            </div>
                            <button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="openPasswordRecoveryModal()">¿Olvidaste tu contraseña?</button>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginSubmitBtn">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted">Acceso exclusivo para usuarios registrados del sistema.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordRecoveryModal" tabindex="-1" aria-labelledby="passwordRecoveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordRecoveryModalLabel"><i class="bi bi-shield-lock"></i> Recuperar contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Ingresa tu No. de Control o No. de empleado y el correo registrado en tu perfil.</p>
                    <div id="passwordRecoveryMessage" class="mb-3"></div>
                    <form id="passwordRecoveryForm" class="needs-validation" novalidate>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="recoveryUserId" placeholder="No. de Control, No. de empleado" required autocomplete="username">
                            <label for="recoveryUserId">No. de Control, No. de empleado</label>
                            <div class="invalid-feedback">Ingresa tu No. de Control o No. de empleado.</div>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="email" class="form-control" id="recoveryEmail" placeholder="correo@dominio.com" required autocomplete="email">
                            <label for="recoveryEmail">Correo registrado</label>
                            <div class="invalid-feedback">Ingresa un correo valido.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="passwordRecoverySubmitBtn">
                                <i class="bi bi-send"></i> Enviar token
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordTokenModal" tabindex="-1" aria-labelledby="passwordTokenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordTokenModalLabel"><i class="bi bi-envelope-check"></i> Validar token</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Escribe el token de 6 digitos que enviamos al correo registrado.</p>
                    <div id="passwordTokenMessage" class="mb-3"></div>
                    <form id="passwordTokenForm" class="needs-validation" novalidate>
                        <div class="mb-3 form-floating">
                            <input type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" class="form-control text-center fs-4" id="recoveryToken" placeholder="000000" required autocomplete="one-time-code">
                            <label for="recoveryToken">Token</label>
                            <div class="invalid-feedback">Ingresa el token de 6 digitos.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="passwordTokenSubmitBtn">
                                <i class="bi bi-check2-circle"></i> Validar token
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordResetModalLabel"><i class="bi bi-person-lock"></i> Nueva contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Define tu nueva contraseña antes de entrar al sistema.</p>
                    <div id="passwordResetMessage" class="mb-3"></div>
                    <form id="passwordResetForm" class="needs-validation" novalidate>
                        <div class="mb-3 form-floating">
                            <input type="password" class="form-control" id="newRecoveryPassword" placeholder="Nueva contraseña" minlength="6" required autocomplete="new-password">
                            <label for="newRecoveryPassword">Nueva contraseña</label>
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="password" class="form-control" id="newRecoveryPasswordConfirmation" placeholder="Confirmar contraseña" minlength="6" required autocomplete="new-password">
                            <label for="newRecoveryPasswordConfirmation">Confirmar contraseña</label>
                            <div class="invalid-feedback">Confirma tu nueva contraseña.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="passwordResetSubmitBtn">
                                <i class="bi bi-save"></i> Cambiar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/responsive.js"></script>
    <script>
        const loginForm = document.getElementById('loginForm');
        const loginSubmitBtn = document.getElementById('loginSubmitBtn');
        const loginMessageContainer = document.getElementById('loginMessageContainer');
        const passwordRecoveryModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('passwordRecoveryModal'));
        const passwordTokenModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('passwordTokenModal'));
        const passwordResetModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('passwordResetModal'));
        const recoveryState = { id: '', email: '', token: '' };

        function dashboardUrlForCurrentUser() {
            if (auth.isAdmin()) return '/pages/admin/dashboard.php';
            if (auth.isTeacher()) return '/pages/teacher/dashboard.php';
            return '/pages/student/dashboard.php';
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function modalAlert(type, message) {
            return `<div class="alert alert-${type} py-2 mb-0" role="alert">${escapeHtml(message)}</div>`;
        }

        function setButtonLoading(button, loadingText, originalText = null) {
            if (!button.dataset.originalText) button.dataset.originalText = originalText || button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
        }

        function restoreButton(button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }

        function openPasswordRecoveryModal() {
            bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide();
            document.getElementById('passwordRecoveryMessage').innerHTML = '';
            document.getElementById('passwordRecoveryForm').classList.remove('was-validated');
            document.getElementById('passwordRecoveryForm').reset();
            passwordRecoveryModal.show();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const cookieNotice = document.getElementById('cookieNotice');
            const cookieNoticeAccept = document.getElementById('cookieNoticeAccept');

            try {
                if (cookieNotice && localStorage.getItem('sgpi-cookie-notice-seen') !== '1') {
                    cookieNotice.hidden = false;
                }
            } catch (error) {
                if (cookieNotice) cookieNotice.hidden = false;
            }

            cookieNoticeAccept?.addEventListener('click', () => {
                cookieNotice.hidden = true;
                try {
                    localStorage.setItem('sgpi-cookie-notice-seen', '1');
                } catch (error) {
                    // El aviso ya quedo cerrado en la pagina actual.
                }
            });

            document.querySelectorAll('[data-open-login]').forEach(link => {
                link.addEventListener('click', event => {
                    event.preventDefault();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal')).show();
                    history.replaceState(null, '', '/index.php#login');
                });
            });

            if (window.location.hash === '#login') {
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }

            if (window.location.hash === '#recover') {
                openPasswordRecoveryModal();
            }
        });

        document.getElementById('passwordRecoveryForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.target;
            const button = document.getElementById('passwordRecoverySubmitBtn');
            const message = document.getElementById('passwordRecoveryMessage');
            message.innerHTML = '';

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            recoveryState.id = document.getElementById('recoveryUserId').value.trim();
            recoveryState.email = document.getElementById('recoveryEmail').value.trim();

            try {
                setButtonLoading(button, 'Enviando...');
                await api.post('/auth/password/request-token', {
                    id: recoveryState.id,
                    email: recoveryState.email
                }, { _timeout: 20000 });
                passwordRecoveryModal.hide();
                document.getElementById('passwordTokenForm').reset();
                document.getElementById('passwordTokenForm').classList.remove('was-validated');
                document.getElementById('passwordTokenMessage').innerHTML = modalAlert('success', 'Token enviado. Revisa tu correo registrado.');
                passwordTokenModal.show();
            } catch (error) {
                message.innerHTML = modalAlert('danger', error.message || 'No se pudo enviar el token.');
            } finally {
                restoreButton(button);
            }
        });

        document.getElementById('passwordTokenForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.target;
            const button = document.getElementById('passwordTokenSubmitBtn');
            const message = document.getElementById('passwordTokenMessage');
            message.innerHTML = '';

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            recoveryState.token = document.getElementById('recoveryToken').value.trim();

            try {
                setButtonLoading(button, 'Validando...');
                await api.post('/auth/password/verify-token', recoveryState);
                passwordTokenModal.hide();
                document.getElementById('passwordResetForm').reset();
                document.getElementById('passwordResetForm').classList.remove('was-validated');
                document.getElementById('passwordResetMessage').innerHTML = '';
                passwordResetModal.show();
            } catch (error) {
                message.innerHTML = modalAlert('danger', error.message || 'Token no valido.');
            } finally {
                restoreButton(button);
            }
        });

        document.getElementById('passwordResetForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.target;
            const button = document.getElementById('passwordResetSubmitBtn');
            const message = document.getElementById('passwordResetMessage');
            message.innerHTML = '';

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const password = document.getElementById('newRecoveryPassword').value;
            const confirmation = document.getElementById('newRecoveryPasswordConfirmation').value;

            if (password !== confirmation) {
                message.innerHTML = modalAlert('danger', 'La nueva contraseña y su confirmacion deben ser identicas.');
                return;
            }

            try {
                setButtonLoading(button, 'Actualizando...');
                const response = await api.post('/auth/password/reset', {
                    ...recoveryState,
                    password,
                    password_confirmation: confirmation
                });

                auth.token = response.access_token;
                auth.user = response.user;
                await axios.post('/api/set-session.php', {
                    auth_token: auth.getToken(),
                    user: auth.getCurrentUser(),
                    remember: true
                });

                await Swal.fire({
                    icon: 'success',
                    title: 'Contraseña actualizada',
                    text: 'Entrando al sistema...',
                    timer: 900,
                    showConfirmButton: false
                });

                window.location.replace(dashboardUrlForCurrentUser());
            } catch (error) {
                message.innerHTML = modalAlert('danger', error.message || 'No se pudo cambiar la contraseña.');
            } finally {
                restoreButton(button);
            }
        });

        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            loginMessageContainer.innerHTML = '';

            if (!loginForm.checkValidity()) {
                loginForm.classList.add('was-validated');
                return;
            }

            const id = document.getElementById('loginUserId').value.trim();
            const password = document.getElementById('loginPassword').value;

            loginSubmitBtn.disabled = true;
            loginSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...';

            const result = await auth.login(id, password);

            if (!result.success) {
                loginMessageContainer.innerHTML = `
                    <div class="alert alert-danger py-2 mb-0" role="alert">
                        <i class="bi bi-exclamation-circle"></i> ${result.error}
                    </div>`;
                swalToast('danger', result.error);
                loginSubmitBtn.disabled = false;
                loginSubmitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Iniciar sesión';
                return;
            }

            await axios.post('/api/set-session.php', {
                auth_token: auth.getToken(),
                user: auth.getCurrentUser(),
                remember: document.getElementById('rememberCheck')?.checked !== false
            });

            await Swal.fire({
                icon: 'success',
                title: 'Bienvenido',
                text: 'Sesion iniciada correctamente',
                timer: 900,
                showConfirmButton: false
            });

            window.location.replace(dashboardUrlForCurrentUser());
        });
    </script>
</body>
</html>
