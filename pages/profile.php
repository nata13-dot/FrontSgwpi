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
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
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
                                <img id="profilePhoto" src="/assets/img/ITSSMT/ISC-256.webp" class="rounded-circle border mb-3" style="width:140px;height:140px;object-fit:cover;" alt="Foto de perfil">
                                <input type="file" class="form-control" name="photo" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control" id="nombres" disabled></div>
                                <div class="col-md-4"><label class="form-label">Apellido paterno</label><input class="form-control" id="apa" disabled></div>
                                <div class="col-md-4"><label class="form-label">Apellido materno</label><input class="form-control" id="ama" disabled></div>
                                <div class="col-md-6"><label class="form-label">Correo</label><input class="form-control" id="email" disabled></div>
                                <div class="col-md-3 student-only"><label class="form-label">Semestre</label><input class="form-control" name="semestre" id="semestre" readonly></div>
                                <div class="col-md-3 student-only"><label class="form-label">Grupo</label><input class="form-control" name="grupo" id="grupo"></div>
                                <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefonos" id="telefonos"></div>
                                <div class="col-md-6 staff-only"><label class="form-label">Especialidad</label><input class="form-control" name="especialidad" id="especialidad" maxlength="255"></div>
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
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script><script src="<?= asset('/assets/js/api.js') ?>"></script>
<script>
let currentUser = null;
function photoUrl(path) { return path ? `<?= API_ORIGIN_URL ?>/storage/${path}` : '/assets/img/ITSSMT/ISC-256.webp'; }
async function loadProfile() {
    currentUser = await api.get('/profile');
    ['nombres','apa','ama','email','semestre','grupo','telefonos','especialidad'].forEach(id => { const el = document.getElementById(id); if (el) el.value = currentUser[id] || ''; });
    document.getElementById('profilePhoto').src = photoUrl(currentUser.photo_path);
    const isStudent = Number(currentUser.perfil_id) === 3;
    document.querySelectorAll('.student-only').forEach(el => el.style.display = isStudent ? '' : 'none');
    document.querySelectorAll('.staff-only').forEach(el => el.style.display = isStudent ? 'none' : '');
    ['semestre', 'grupo'].forEach(id => {
        const field = document.getElementById(id);
        if (field) field.disabled = true;
    });
}
document.getElementById('profileForm').addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData(e.target);
    data.delete('semestre');
    data.delete('grupo');
    if (Number(currentUser?.perfil_id) === 3) data.delete('especialidad');
    ['current_password', 'password', 'password_confirmation'].forEach(field => {
        if (!String(data.get(field) || '').trim()) data.delete(field);
    });
    try {
        const res = await api.post('/profile', data);
        auth.user = res.user;
        await auth.persistSession();
        currentUser = res.user;
        if (window.SGPI_SESSION) window.SGPI_SESSION.user = res.user;
        swalToast('success', 'Perfil actualizado');
        await loadProfile();
    } catch (error) { Swal.fire('Error', error.message, 'error'); }
});
async function loadNotificationPreferences() {
    let items = [];
    try { items = (await api.get('/notifications/preferences', { _fresh: true })).data || []; } catch (error) { return; }
    if (!items.length) return;
    document.getElementById('notificationPreferences')?.remove();
    document.getElementById('profileForm').insertAdjacentHTML('afterend', `<section class="card border-0 shadow-sm mt-4" id="notificationPreferences"><div class="card-body">
        <h2 class="h5">Avisos</h2>
        <p class="text-muted small">Los avisos sobre algo que requiere tu atención (calificaciones, cambios de sala, solicitudes) siempre llegan. De 22:00 a 7:00 sólo llegan cambios urgentes.</p>
        ${items.map(item => `<div class="form-check form-switch mb-2"><input class="form-check-input notification-preference" type="checkbox" role="switch" id="pref-${item.key}" data-event="${item.key}" ${item.enabled ? 'checked' : ''}><label class="form-check-label" for="pref-${item.key}">${item.label}<small class="d-block text-muted">${item.hint}</small></label></div>`).join('')}
    </div></section>`);
    document.querySelectorAll('.notification-preference').forEach(input => input.addEventListener('change', async () => {
        try {
            await api.put('/notifications/preferences', { event: input.dataset.event, enabled: input.checked });
        } catch (error) {
            input.checked = !input.checked;
            swalToast('error', error.message);
        }
    }));
}
document.addEventListener('DOMContentLoaded', loadProfile);
document.addEventListener('DOMContentLoaded', loadNotificationPreferences);
</script>
</body>
</html>
