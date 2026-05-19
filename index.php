<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (is_authenticated()) {
    if (is_admin()) {
        header('Location: /pages/admin/dashboard.php');
    } elseif (is_teacher()) {
        header('Location: /pages/teacher/dashboard.php');
    } else {
        header('Location: /pages/student/dashboard.php');
    }
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Inicio</title>
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
                            <a href="/pages/forgot-password.php" class="text-muted small">¿Olvidaste tu contraseña?</a>
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

        function dashboardUrlForCurrentUser() {
            if (auth.isAdmin()) return '/pages/admin/dashboard.php';
            if (auth.isTeacher()) return '/pages/teacher/dashboard.php';
            return '/pages/student/dashboard.php';
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