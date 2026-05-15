<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_admin()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - <?= APP_NAME ?></title>
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
                <h1 class="mb-4">Crear Nuevo Usuario</h1>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div id="alertBox"></div>

                        <form id="userForm" class="needs-validation" novalidate>
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
                                    <input type="text" class="form-control" id="id" name="id" required>
                                    <div class="invalid-feedback">La matricula o nomina es obligatoria.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="invalid-feedback">Ingresa un correo valido.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="telefonos" class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="telefonos" name="telefonos" maxlength="200" required>
                                    <div class="invalid-feedback">Ingresa un telefono.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="perfil_id" class="form-label">Perfil</label>
                                    <select class="form-select" id="perfil_id" name="perfil_id" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Docente</option>
                                        <option value="3">Estudiante</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona un perfil.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
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
                                <div class="col-12 mb-3">
                                    <label for="direccion" class="form-label">Direccion</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" minlength="10" pattern="(?=.*\d)[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\s#.,\-\/]+" placeholder="Calle, numero, colonia, municipio">
                                    <div class="form-text">Debe incluir calle y numero. Ej. Av. Reforma 123, Col. Centro.</div>
                                    <div class="invalid-feedback">Ingresa un domicilio valido con al menos un numero.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="invalid-feedback">La contraseña es obligatoria.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                    <div class="invalid-feedback">Confirma la contraseña.</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar
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

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;
            if (document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                swalToast('danger', 'La nueva contraseña y su confirmacion no coinciden');
                return;
            }

            const formData = {
                id: document.getElementById('id').value,
                email: document.getElementById('email').value,
                perfil_id: document.getElementById('perfil_id').value,
                semestre: document.getElementById('semestre').value || null,
                grupo: document.getElementById('grupo').value || null,
                nombres: document.getElementById('nombres').value,
                apa: document.getElementById('apa').value,
                ama: document.getElementById('ama').value,
                direccion: document.getElementById('direccion').value.trim() || null,
                telefonos: document.getElementById('telefonos').value.trim(),
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };

            try {
                await api.post('/users', formData);
                alertBox.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Usuario creado exitosamente
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                setTimeout(() => {
                    window.location.href = '/pages/admin/users.php';
                }, 1500);
            } catch (error) {
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> ${error.message || 'Error al crear usuario'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        });

        async function loadGroupsForSemester() {
            const semester = document.getElementById('semestre').value;
            const select = document.getElementById('grupo');
            select.innerHTML = semester ? '<option value="">Seleccionar grupo...</option>' : '<option value="">Selecciona un semestre</option>';
            if (!semester) return;
            const groups = await api.get('/subject-groups', { semestre: semester });
            groups.forEach(group => {
                select.innerHTML += `<option value="${group.grupo}">${group.semestre} ${group.grupo} - ${group.nombre}</option>`;
            });
        }

        function toggleStudentFields() {
            const isStudent = document.getElementById('perfil_id').value === '3';
            document.querySelectorAll('.student-group-field').forEach(el => el.classList.toggle('d-none', !isStudent));
        }

        document.getElementById('perfil_id').addEventListener('change', toggleStudentFields);
        document.getElementById('semestre').addEventListener('change', loadGroupsForSemester);
    </script>
</body>
</html>