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
            try {
                const token = localStorage.getItem('auth_token');
                const user = JSON.parse(localStorage.getItem('user') || 'null');
                if (token && user) {
                    window.location.replace('<?= $serverDashboardUrl ?>');
                    return;
                }
            } catch (error) {
                // Si el estado local esta corrupto, se limpia la sesion completa abajo.
            }
            window.location.replace('/pages/logout.php?reason=session_mismatch');
        })();
    </script>
    <?php else: ?>
    <script>
        (async function () {
            const token = localStorage.getItem('auth_token');
            let user = null;

            try {
                user = JSON.parse(localStorage.getItem('user') || 'null');
            } catch (error) {
                user = null;
            }

            if (!token || !user) return;

            const dashboardUrl = user.perfil_id === 1
                ? '/pages/admin/dashboard.php'
                : (user.perfil_id === 2 ? '/pages/teacher/dashboard.php' : '/pages/student/dashboard.php');

            try {
                const response = await fetch('<?= API_BASE_URL ?>/auth/me', {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`
                    }
                });

                if (!response.ok) throw new Error('invalid_token');

                const sessionResponse = await fetch('/api/set-session.php', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ auth_token: token, user })
                });

                if (!sessionResponse.ok) throw new Error('session_restore_failed');
                window.location.replace(dashboardUrl);
            } catch (error) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                sessionStorage.clear();
                if (window.location.hash !== '#login') {
                    history.replaceState(null, '', '/index.php#login');
                }
            }
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
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div style="background: url('/assets/img/ITSSMT/fondo.jpg'); background-size: cover; background-position: center; padding: 100px 0; position: relative;">
        <div class="overlay"></div>
        <div class="container-xl" style="position: relative; z-index: 1;">
            <h1 class="display-3 fw-bold text-white mb-4">Sistema de Gestión de Proyectos Integradores</h1>
            <p class="lead text-white mb-4">Instituto Tecnológico Superior de San Martín Texmelucan</p>
            <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
            </button>
        </div>
    </div>

    <div class="section">
        <div class="container-xl">
            <h2 class="section-title text-center mb-5">Características Principales</h2>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-folder2" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Gestión de Proyectos</h5>
                            <p class="text-muted">Administra tus proyectos integradores de forma centralizada</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-archive" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Repositorio Digital</h5>
                            <p class="text-muted">Almacena y organiza todos tus documentos y entregables</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-people" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Colaboración</h5>
                            <p class="text-muted">Trabaja con docentes y compañeros de forma eficiente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                });
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
                localStorage.setItem('auth_token', auth.token);
                localStorage.setItem('user', JSON.stringify(auth.user));

                await axios.post('/api/set-session.php', {
                    auth_token: auth.getToken(),
                    user: auth.getCurrentUser()
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
                user: auth.getCurrentUser()
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
