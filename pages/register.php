<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (is_authenticated()) { header('Location: ' . dashboard_url()); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta de alumno - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 640px">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT" width="52" height="52" class="rounded-3 bg-white p-1 shadow-sm">
                <div><h1 class="h4 mb-0">Crear cuenta de alumno</h1><small class="text-muted">SGPI ITSSMT</small></div>
            </div>
            <div id="registerAlert"></div>

            <form id="dataForm" class="d-none" novalidate>
                <p class="text-muted small" id="careerInfo"></p>
                <div class="row g-3">
                    <div class="col-12 d-none" id="careerField"><label class="form-label" for="carrera_id">Carrera</label><select class="form-select" id="carrera_id" required></select></div>
                    <div class="col-md-6"><label class="form-label" for="controlNumber">Número de control</label><input class="form-control" id="controlNumber" required autocomplete="username"><div class="form-text" id="ruleHint"></div></div>
                    <div class="col-md-6"><label class="form-label" for="email">Correo institucional</label><input class="form-control" id="email" type="email" required autocomplete="email"><div class="invalid-feedback" id="emailFeedback"></div><div class="form-text" id="emailHint"></div></div>
                    <div class="col-md-4"><label class="form-label" for="nombres">Nombre(s)</label><input class="form-control" id="nombres" required autocomplete="given-name"></div>
                    <div class="col-md-4"><label class="form-label" for="apa">Apellido paterno</label><input class="form-control" id="apa" required autocomplete="family-name"></div>
                    <div class="col-md-4"><label class="form-label" for="ama">Apellido materno</label><input class="form-control" id="ama"></div>
                    <div class="col-md-6"><label class="form-label" for="semestre">Semestre</label><select class="form-select" id="semestre" required></select></div>
                    <div class="col-md-6"><label class="form-label" for="grupo_id">Grupo</label><select class="form-select" id="grupo_id" required disabled></select></div>
                    <div class="col-md-6"><label class="form-label" for="password">Contraseña</label><input class="form-control" id="password" type="password" minlength="8" required autocomplete="new-password"><div class="form-text">Mínimo 8 caracteres.</div></div>
                    <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input class="form-control" id="password_confirmation" type="password" required autocomplete="new-password"></div>
                </div>
                <div class="d-grid mt-4"><button class="btn btn-primary btn-lg" id="sendBtn"><i class="bi bi-envelope"></i> Enviar código a mi correo</button></div>
            </form>

            <form id="codeForm" class="d-none" novalidate>
                <p class="text-muted"><i class="bi bi-envelope-check"></i> Enviamos un código de seis dígitos a <strong id="sentTo"></strong>. Vence en 15 minutos.</p>
                <label class="form-label" for="code">Código de seis dígitos</label>
                <input class="form-control form-control-lg text-center fw-bold" id="code" inputmode="numeric" maxlength="6" minlength="6" autocomplete="one-time-code" required style="letter-spacing: .5em">
                <div class="d-grid mt-3"><button class="btn btn-primary btn-lg" id="verifyBtn">Activar cuenta</button></div>
                <div class="d-flex justify-content-between mt-2">
                    <button type="button" class="btn btn-link btn-sm" id="backBtn">Corregir datos</button>
                    <button type="button" class="btn btn-link btn-sm" id="resendBtn"><i class="bi bi-arrow-clockwise"></i> Reenviar código</button>
                </div>
            </form>

            <div id="doneBox" class="d-none">
                <div class="alert alert-success"><i class="bi bi-check-circle"></i> Tu cuenta quedó activa. Inicia sesión con tu número de control <strong id="doneId"></strong> y tu contraseña.</div>
                <div class="d-grid"><a class="btn btn-primary btn-lg" href="/index.php#login">Iniciar sesión</a></div>
            </div>

            <p class="text-center mt-3 mb-0"><a href="/index.php#login">Ya tengo cuenta · Iniciar sesión</a></p>
        </div>
    </div>
</main>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script>
const $ = id => document.getElementById(id);
const esc = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[char]));
let careers = [];

const career = () => careers.find(item => String(item.id) === $('carrera_id').value) || (careers.length === 1 ? careers[0] : null);
const normalizedId = () => $('controlNumber').value.trim().toLowerCase();
const normalizedEmail = () => $('email').value.trim().toLowerCase();
// Mismo criterio que la API: correo = prefijo + número de control @ dominio de la carrera.
const expectedEmail = () => {
    const rule = career()?.rule;
    return rule && normalizedId() ? `${rule.email_prefix}${normalizedId()}@${rule.domain}` : '';
};

function showStep(step) {
    $('dataForm').classList.toggle('d-none', step !== 'data');
    $('codeForm').classList.toggle('d-none', step !== 'code');
    $('doneBox').classList.toggle('d-none', step !== 'done');
}

function renderCareer() {
    const current = career();
    $('careerInfo').textContent = current ? `${current.nombre} · periodo ${current.period} · registro abierto hasta ${new Date(current.closes_at).toLocaleString('es-MX')}` : '';
    $('ruleHint').textContent = current?.rule.description || '';
    const semesters = [...new Set((current?.groups || []).map(group => group.semestre))];
    $('semestre').innerHTML = '<option value="">Elige tu semestre</option>' + semesters.map(value => `<option value="${value}">${value}° semestre</option>`).join('');
    renderGroups();
    checkEmail();
}

function renderGroups() {
    const groups = (career()?.groups || []).filter(group => String(group.semestre) === $('semestre').value);
    $('grupo_id').innerHTML = '<option value="">Elige tu grupo</option>' + groups.map(group => `<option value="${group.id}">${esc(group.grupo)} · ${esc(group.nombre)}</option>`).join('');
    $('grupo_id').disabled = !groups.length;
}

function checkEmail() {
    const expected = expectedEmail();
    $('emailHint').textContent = expected || career()?.rule.example || '';
    const mismatch = Boolean(normalizedEmail() && expected && normalizedEmail() !== expected);
    $('email').classList.toggle('is-invalid', mismatch);
    $('emailFeedback').textContent = mismatch ? `Debe ser ${expected}` : '';
    return !mismatch;
}

async function loadCareers() {
    try {
        const data = await api.get('/self-registration', { _fresh: true });
        careers = data.careers || [];
    } catch (error) {
        $('registerAlert').innerHTML = `<div class="alert alert-danger">${esc(error.message)}</div>`;
        return;
    }
    if (!careers.length) {
        $('registerAlert').innerHTML = '<div class="alert alert-secondary">El registro de cuentas de alumnos está cerrado. Consulta a la jefatura de tu carrera.</div>';
        return;
    }
    $('carrera_id').innerHTML = (careers.length > 1 ? '<option value="">Elige tu carrera</option>' : '') + careers.map(item => `<option value="${item.id}">${esc(item.nombre)}</option>`).join('');
    $('careerField').classList.toggle('d-none', careers.length < 2);
    renderCareer();
    showStep('data');
}

async function sendData() {
    const button = $('sendBtn');
    button.disabled = true;
    try {
        const response = await api.post('/self-registration', {
            carrera_id: career().id, id: normalizedId(), email: normalizedEmail(), nombres: $('nombres').value.trim(), apa: $('apa').value.trim(),
            ama: $('ama').value.trim(), grupo_id: Number($('grupo_id').value), password: $('password').value, password_confirmation: $('password_confirmation').value
        });
        swalToast('success', response.message);
        $('sentTo').textContent = normalizedEmail();
        showStep('code');
        $('code').focus();
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    } finally {
        button.disabled = false;
    }
}

$('carrera_id').addEventListener('change', renderCareer);
$('semestre').addEventListener('change', renderGroups);
$('controlNumber').addEventListener('input', checkEmail);
$('email').addEventListener('input', checkEmail);
$('code').addEventListener('input', () => { $('code').value = $('code').value.replace(/\D/g, ''); });
$('backBtn').addEventListener('click', () => showStep('data'));
$('resendBtn').addEventListener('click', sendData);

$('dataForm').addEventListener('submit', event => {
    event.preventDefault();
    const form = event.target;
    if (!form.checkValidity() || !checkEmail()) {
        form.classList.add('was-validated');
        if (!checkEmail()) Swal.fire('Revisa tu correo', `Tu correo institucional debe ser ${expectedEmail()}.`, 'warning');
        return;
    }
    if ($('password').value !== $('password_confirmation').value) {
        Swal.fire('Revisa tu contraseña', 'Las contraseñas no coinciden.', 'warning');
        return;
    }
    sendData();
});

$('codeForm').addEventListener('submit', async event => {
    event.preventDefault();
    const button = $('verifyBtn');
    button.disabled = true;
    try {
        await api.post('/self-registration/verify', { email: normalizedEmail(), code: $('code').value });
        $('doneId').textContent = normalizedId();
        showStep('done');
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    } finally {
        button.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', loadCareers);
</script>
</body>
</html>
