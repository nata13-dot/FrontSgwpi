<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated()) { header('Location: /index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 520px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-2"><i class="bi bi-shield-lock"></i> Cambia tu contraseña</h1>
            <p class="text-muted mb-4">
                Hola <?= htmlspecialchars($_SESSION['user']['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>. Entraste con una contraseña temporal
                (tu matrícula o número de empleado). Para continuar, elige una contraseña nueva.
            </p>
            <div id="alertContainer"></div>
            <form id="changePasswordForm" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="newPassword">Nueva contraseña</label>
                    <input type="password" class="form-control" id="newPassword" minlength="8" maxlength="72" autocomplete="new-password" required>
                    <div class="form-text">Mínimo 8 caracteres y distinta de tu matrícula.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="newPasswordConfirmation">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="newPasswordConfirmation" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <a class="btn btn-outline-secondary" href="/pages/logout.php"><i class="bi bi-box-arrow-left"></i> Salir</a>
                    <button class="btn btn-primary" type="submit" id="changePasswordBtn"><i class="bi bi-check2-circle"></i> Guardar y continuar</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script>
    document.getElementById('changePasswordForm').addEventListener('submit', async event => {
        event.preventDefault();
        const password = document.getElementById('newPassword').value;
        const confirmation = document.getElementById('newPasswordConfirmation').value;
        if (password.length < 8) return showAlert('#alertContainer', 'warning', 'La contraseña debe tener al menos 8 caracteres.');
        if (password !== confirmation) return showAlert('#alertContainer', 'warning', 'La confirmación no coincide.');
        const button = document.getElementById('changePasswordBtn');
        button.disabled = true;
        try {
            const response = await api.post('/auth/password/change-initial', { password, password_confirmation: confirmation });
            auth.user = { ...auth.user, ...response.user, must_change_password: false };
            // La sesión PHP se vuelve a validar con /auth/me, que ya no exige el cambio.
            const session = await auth.persistSession();
            window.location.href = session.dashboard_url || '/index.php';
        } catch (error) {
            showAlert('#alertContainer', 'danger', error.message || 'No se pudo cambiar la contraseña.');
            button.disabled = false;
        }
    });
</script>
</body>
</html>
