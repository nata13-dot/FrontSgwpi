<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_admin()) {
    header('Location: /index.php');
    exit;
}

$userId = $_GET['id'] ?? null;
if (!$userId) {
    header('Location: /pages/admin/users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <h1 class="mb-4">Editar Usuario</h1>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div id="alertBox"></div>

                        <form id="userForm" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="direccion" class="form-label">Direccion</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" minlength="10" pattern="(?=.*\d)[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\s#.,\-\/]+" placeholder="Calle, numero, colonia, municipio">
                                    <div class="form-text">Debe incluir calle y numero. Ej. Av. Reforma 123, Col. Centro.</div>
                                    <div class="invalid-feedback">Ingresa un domicilio valido con al menos un numero.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3 student-group-field d-none">
                                    <label for="semestre" class="form-label">Semestre</label>
                                    <select class="form-select" id="semestre" name="semestre">
                                        <option value="">Seleccionar...</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 student-group-field d-none">
                                    <label for="grupo" class="form-label">Grupo</label>
                                    <select class="form-select" id="grupo" name="grupo">
                                        <option value="">Selecciona un semestre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="id" class="form-label">Matrícula/Nómina</label>
                                    <input type="text" class="form-control" id="id" name="id" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="telefonos" class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="telefonos" name="telefonos" maxlength="200" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="perfil_id" class="form-label">Perfil</label>
                                    <select class="form-select" id="perfil_id" name="perfil_id" required disabled>
                                        <option value="1">Administrador</option>
                                        <option value="2">Docente</option>
                                        <option value="3">Estudiante</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="activo" class="form-label">Estado</label>
                                    <select class="form-select" id="activo" name="activo" required>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="apa" class="form-label">Apellido Paterno</label>
                                    <input type="text" class="form-control" id="apa" name="apa">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="ama" class="form-label">Apellido Materno</label>
                                    <input type="text" class="form-control" id="ama" name="ama">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña (dejar vacío para no cambiar)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                                <a href="/pages/admin/users.php" class="btn btn-secondary">
                                    <i class="bi bi-x"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'https://swapi-production-8341.up.railway.app/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>

    <script>
        const form = document.getElementById('userForm');
        const alertBox = document.getElementById('alertBox');
        const userId = '<?= htmlspecialchars($userId) ?>';
        let loadedUser = null;

        async function loadUser() {
            try {
                const response = await api.get(`/users/${userId}`);
                // El API retorna el objeto directamente, no dentro de .data
                const user = response;
                loadedUser = user;

                document.getElementById('id').value = user.id || '';
                document.getElementById('email').value = user.email || '';
                document.getElementById('perfil_id').value = user.perfil_id || '3';
                document.getElementById('activo').value = user.activo ? '1' : '0';
                document.getElementById('nombres').value = user.nombres || '';
                document.getElementById('apa').value = user.apa || '';
                document.getElementById('ama').value = user.ama || '';
                document.getElementById('telefonos').value = user.telefonos || '';
                document.getElementById('direccion').value = user.direccion || '';
                document.getElementById('semestre').value = user.semestre || '';
                await loadGroupsForSemester(user.grupo || '');
                toggleStudentFields();

                if (Number(user.perfil_id) === 1) {
                    const stateHelp = document.createElement('div');
                    stateHelp.className = 'form-text text-warning';
                    stateHelp.textContent = 'Administrador protegido: para desactivarlo o cambiar su perfil se pedira la contraseña del administrador actual.';
                    document.getElementById('activo').closest('.mb-3').appendChild(stateHelp);
                }
                
                console.log('Usuario cargado:', user);
            } catch (error) {
                console.error('Error al cargar usuario:', error);
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Error al cargar los datos del usuario
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const formData = {
                email: document.getElementById('email').value,
                perfil_id: document.getElementById('perfil_id').value,
                activo: document.getElementById('activo').value === '1',
                semestre: document.getElementById('semestre').value || null,
                grupo: document.getElementById('grupo').value || null,
                nombres: document.getElementById('nombres').value,
                apa: document.getElementById('apa').value,
                ama: document.getElementById('ama').value
                ,direccion: document.getElementById('direccion').value.trim() || null,
                telefonos: document.getElementById('telefonos').value.trim()
            };

            const changesProtectedAdmin = loadedUser && Number(loadedUser.perfil_id) === 1
                && ((Number(formData.perfil_id) !== 1) || !formData.activo);

            if (changesProtectedAdmin) {
                const adminPassword = await promptPassword({
                    title: 'Administrador protegido',
                    inputPlaceholder: 'Contraseña del administrador actual',
                    confirmButtonText: 'Autorizar cambio'
                });
                if (!adminPassword) return;
                formData.admin_password = adminPassword;
            }

            // Incluir contraseña solo si está llena
            if (document.getElementById('password').value) {
                if (document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                    swalToast('danger', 'La nueva contraseña y su confirmacion no coinciden');
                    return;
                }
                formData.password = document.getElementById('password').value;
                formData.password_confirmation = document.getElementById('password_confirmation').value;
            }

            try {
                await api.put(`/users/${userId}`, formData);
                alertBox.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Usuario actualizado exitosamente
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                setTimeout(() => {
                    window.location.href = '/pages/admin/users.php';
                }, 1500);
            } catch (error) {
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> ${error.message || 'Error al actualizar usuario'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        });

        document.addEventListener('DOMContentLoaded', loadUser);
        async function loadGroupsForSemester(selected = '') {
            const semester = document.getElementById('semestre').value;
            const select = document.getElementById('grupo');
            select.innerHTML = semester ? '<option value="">Seleccionar grupo...</option>' : '<option value="">Selecciona un semestre</option>';
            if (!semester) return;
            const groups = await api.get('/subject-groups', { semestre: semester });
            groups.forEach(group => {
                select.innerHTML += `<option value="${group.grupo}">${group.semestre} ${group.grupo} - ${group.nombre}</option>`;
            });
            select.value = selected || '';
        }

        function toggleStudentFields() {
            const isStudent = document.getElementById('perfil_id').value === '3';
            document.querySelectorAll('.student-group-field').forEach(el => el.classList.toggle('d-none', !isStudent));
        }

        document.getElementById('perfil_id').addEventListener('change', toggleStudentFields);
        document.getElementById('semestre').addEventListener('change', () => loadGroupsForSemester());
    </script>
</body>
</html>