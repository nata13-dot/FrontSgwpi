<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated()) { header('Location: /index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
<div class="d-flex content-wrapper">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
    <main class="main-content flex-grow-1">
        <div class="container-xl mt-5 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Mi Perfil</h1>
                <span class="badge bg-primary"><i class="bi bi-person-circle"></i> Datos personales</span>
            </div>
            <div class="card border-0 shadow-sm">
                <form id="profileForm" class="card-body" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <img id="profilePhoto" src="/assets/img/ITSSMT/ISC.png" class="rounded-circle border mb-3" style="width:140px;height:140px;object-fit:cover;" alt="Foto de perfil">
                                <input type="file" class="form-control" name="photo" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control" id="nombres" disabled></div>
                                <div class="col-md-4"><label class="form-label">Apellido paterno</label><input class="form-control" id="apa" disabled></div>
                                <div class="col-md-4"><label class="form-label">Apellido materno</label><input class="form-control" id="ama" disabled></div>
                                <div class="col-md-6"><label class="form-label">Correo</label><input class="form-control" id="email" disabled></div>
                                <div class="col-md-3 student-only"><label class="form-label">Semestre</label><select class="form-select" name="semestre" id="semestre"><option value="">-</option><option>5</option><option>6</option><option>7</option><option>8</option></select></div>
                                <div class="col-md-3 student-only"><label class="form-label">Grupo</label><input class="form-control" name="grupo" id="grupo"></div>
                                <div class="col-md-6"><label class="form-label">Telefono</label><input class="form-control" name="telefonos" id="telefonos"></div>
                                <div class="col-md-6"><label class="form-label">Direccion</label><input class="form-control" name="direccion" id="direccion"></div>
                                <div class="col-md-4"><label class="form-label">Contraseña actual</label><input type="password" class="form-control" name="current_password"></div>
                                <div class="col-md-4"><label class="form-label">Nueva contraseña</label><input type="password" class="form-control" name="password"></div>
                                <div class="col-md-4"><label class="form-label">Confirmar contraseña</label><input type="password" class="form-control" name="password_confirmation"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-4"><button class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button></div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = 'https://apiswgpi-production-0e59.up.railway.app/api';</script>
<script src="/assets/js/auth.js"></script><script src="/assets/js/api.js"></script>
<script>
let currentUser = null;
function photoUrl(path) { return path ? `https://apiswgpi-production-0e59.up.railway.app/storage/${path}` : '/assets/img/ITSSMT/ISC.png'; }
async function loadProfile() {
    currentUser = await api.get('/profile');
    ['nombres','apa','ama','email','semestre','grupo','telefonos','direccion'].forEach(id => { const el = document.getElementById(id); if (el) el.value = currentUser[id] || ''; });
    document.getElementById('profilePhoto').src = photoUrl(currentUser.photo_path);
    document.querySelectorAll('.student-only').forEach(el => el.style.display = Number(currentUser.perfil_id) === 3 ? '' : 'none');
}
document.getElementById('profileForm').addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData(e.target);
    try {
        const res = await api.post('/profile', data);
        localStorage.setItem('user', JSON.stringify(res.user));
        await fetch('/api/set-session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ auth_token: auth.getToken(), user: res.user })
        });
        swalToast('success', 'Perfil actualizado');
        loadProfile();
        setTimeout(() => window.location.reload(), 700);
    } catch (error) { Swal.fire('Error', error.message, 'error'); }
});
document.addEventListener('DOMContentLoaded', loadProfile);
</script>
</body>
</html>