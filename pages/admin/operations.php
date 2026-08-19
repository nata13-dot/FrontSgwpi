<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_general_admin()) {
    header('Location: ' . dashboard_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de operaciones - <?= APP_NAME ?></title>
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
        <div class="container-xl py-4 py-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <span class="text-uppercase small fw-bold text-muted">Supervisión institucional</span>
                    <h1 class="h2 mb-2">Centro de operaciones</h1>
                    <p class="text-muted mb-0">Alertas de aislamiento multicarrera, respaldos y capacidad de recuperación.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" id="continuityPolicyButton" type="button" data-bs-toggle="modal" data-bs-target="#continuityPolicyModal"><i class="bi bi-sliders"></i> Política</button>
                    <button class="btn btn-outline-success" id="measureContinuityButton" type="button"><i class="bi bi-graph-up-arrow"></i> Guardar medición</button>
                    <button class="btn btn-outline-primary" id="continuityReportButton" type="button"><i class="bi bi-file-earmark-pdf"></i> Reporte de continuidad</button>
                    <button class="btn btn-primary" id="scanButton" type="button"><i class="bi bi-arrow-clockwise"></i> Analizar ahora</button>
                </div>
            </div>

            <div class="row g-3 mb-4" id="operationSummary">
                <div class="col-12 text-center text-muted py-4">Cargando estado operativo...</div>
            </div>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h2 class="h5 mb-1"><i class="bi bi-graph-up"></i> Tendencia de continuidad</h2>
                        <p class="text-muted small mb-0">Últimas mediciones manuales, programadas o ejecutadas desde consola.</p>
                    </div>
                    <div id="continuityTrendSummary" class="text-muted small">Calculando variación...</div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Fecha</th><th>Origen</th><th>Índice</th><th>Controles</th><th>Respaldos</th><th>Alertas</th><th>Medido por</th></tr></thead>
                        <tbody id="continuityHistory"><tr><td colspan="7" class="text-center text-muted py-4">Cargando mediciones...</td></tr></tbody>
                    </table>
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 p-4">
                    <h2 class="h5 mb-1">Historial de alertas</h2>
                    <p class="text-muted small mb-0">Las alertas atendidas permanecen activas hasta que el siguiente análisis confirme su resolución.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Severidad</th><th>Alerta</th><th>Estado</th><th>Detección</th><th>Atendida por</th><th></th></tr></thead>
                        <tbody id="operationAlerts"><tr><td colspan="6" class="text-center text-muted py-4">Sin alertas registradas.</td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
<div class="modal fade" id="continuityPolicyModal" tabindex="-1" aria-labelledby="continuityPolicyTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="continuityPolicyForm">
            <div class="modal-header">
                <div><h2 class="modal-title fs-5" id="continuityPolicyTitle">Política de continuidad</h2><small class="text-muted">Umbrales institucionales compartidos por todas las carreras.</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="form-label" for="policyTarget">Objetivo (%)</label><input class="form-control" id="policyTarget" type="number" min="50" max="100" required></div>
                    <div class="col-6"><label class="form-label" for="policyCritical">Umbral crítico (%)</label><input class="form-control" id="policyCritical" type="number" min="1" max="99" required></div>
                    <div class="col-6"><label class="form-label" for="policyBackupAge">Máxima antigüedad (horas)</label><input class="form-control" id="policyBackupAge" type="number" min="1" max="168" required></div>
                    <div class="col-6"><label class="form-label" for="policyRetention">Retención (días)</label><input class="form-control" id="policyRetention" type="number" min="7" max="365" required></div>
                </div>
                <div class="alert alert-light border small mt-3 mb-0">El umbral crítico debe ser menor que el objetivo. Los cambios se aplican inmediatamente al monitoreo institucional.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" id="savePolicyButton" type="submit">Guardar política</button></div>
        </form>
    </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/api.js"></script>
<script src="/assets/js/app.js"></script>
<script>
function operationEscape(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
}
function renderOperations(response) {
    const cards = [
        ['Alertas abiertas', response.open, response.open ? 'danger' : 'success', response.open ? 'exclamation-circle' : 'check-circle'],
        ['En atención', response.acknowledged, response.acknowledged ? 'warning' : 'secondary', 'person-check'],
        ['Críticas activas', response.critical, response.critical ? 'danger' : 'success', response.critical ? 'shield-exclamation' : 'shield-check']
    ];
    document.getElementById('operationSummary').innerHTML = cards.map(card => `
        <div class="col-md-4">
            <article class="card border-0 shadow-sm h-100"><div class="card-body p-4 d-flex align-items-center gap-3">
                <span class="fs-2 text-${card[2]}"><i class="bi bi-${card[3]}"></i></span>
                <div><div class="small text-muted">${card[0]}</div><strong class="h3 mb-0">${Number(card[1])}</strong></div>
            </div></article>
        </div>`).join('');

    const severity = {critica: ['danger', 'Crítica'], advertencia: ['warning', 'Advertencia'], informativa: ['info', 'Informativa']};
    const state = {abierta: ['danger', 'Abierta'], atendida: ['warning', 'Atendida'], resuelta: ['success', 'Resuelta']};
    document.getElementById('operationAlerts').innerHTML = (response.data || []).map(alert => {
        const severityData = severity[alert.severidad] || ['secondary', alert.severidad];
        const stateData = state[alert.estado] || ['secondary', alert.estado];
        const actor = [alert.actor_nombres, alert.actor_apellido].filter(Boolean).join(' ') || '—';
        return `<tr>
            <td><span class="badge text-bg-${severityData[0]}">${operationEscape(severityData[1])}</span></td>
            <td><strong>${operationEscape(alert.titulo)}</strong><div class="small text-muted">${operationEscape(alert.detalle)}</div></td>
            <td><span class="badge text-bg-${stateData[0]}">${operationEscape(stateData[1])}</span></td>
            <td>${operationEscape(new Date(alert.detectada_en).toLocaleString('es-MX'))}</td>
            <td>${operationEscape(actor)}</td>
            <td class="text-end">${alert.estado === 'abierta'
                ? `<button class="btn btn-sm btn-outline-primary" type="button" onclick="acknowledgeAlert(${Number(alert.id)}, this)"><i class="bi bi-person-check"></i> Atender</button>`
                : ''}</td>
        </tr>`;
    }).join('') || '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-check-circle text-success"></i> No existen alertas operativas.</td></tr>';
}
async function loadOperations() {
    try {
        renderOperations(await api.get('/admin/operational-alerts', {_fresh: true}));
    } catch (error) {
        document.getElementById('operationAlerts').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${operationEscape(error.message)}</td></tr>`;
    }
}
async function scanOperations() {
    const button = document.getElementById('scanButton');
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Analizando...';
    try {
        renderOperations(await api.post('/admin/operational-alerts/scan', {}, {_timeout: 300000}));
    } catch (error) {
        alert(error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Analizar ahora';
    }
}
async function loadContinuityHistory() {
    const body = document.getElementById('continuityHistory');
    try {
        const response = await api.get('/admin/continuity-history', {limit: 30, _fresh: true});
        const trend = response.trend || {};
        const direction = trend.direction === 'down'
            ? ['danger', 'arrow-down', `${trend.delta} puntos`]
            : (trend.direction === 'up'
                ? ['success', 'arrow-up', `+${trend.delta} puntos`]
                : ['secondary', 'dash', 'Sin variación']);
        document.getElementById('continuityTrendSummary').innerHTML = `
            <span class="badge text-bg-${direction[0]}"><i class="bi bi-${direction[1]}"></i> ${direction[2]}</span>
            <span class="ms-2">Actual ${trend.current ?? '—'}% · Objetivo ${trend.target ?? 100}%</span>`;
        body.innerHTML = (response.data || []).map(measurement => {
            const score = Number(measurement.indice_preparacion);
            const actor = [measurement.actor_nombres, measurement.actor_apellido].filter(Boolean).join(' ') || 'Sistema';
            return `<tr>
                <td>${operationEscape(new Date(measurement.creado_en).toLocaleString('es-MX'))}</td>
                <td><span class="badge text-bg-light">${operationEscape(measurement.origen)}</span></td>
                <td style="min-width:150px"><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-${score === 100 ? 'success' : (score >= 75 ? 'warning' : 'danger')}" style="width:${score}%"></div></div><strong>${score}%</strong></div></td>
                <td>${Number(measurement.controles_correctos)}/${Number(measurement.controles_totales)}</td>
                <td>${Number(measurement.respaldos_disponibles)} disponibles · ${Number(measurement.respaldos_verificados)} verificadas</td>
                <td>${Number(measurement.alertas_activas)} activas · ${Number(measurement.alertas_criticas)} críticas</td>
                <td>${operationEscape(actor)}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="7" class="text-center text-muted py-4">Aún no hay mediciones históricas.</td></tr>';
    } catch (error) {
        body.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${operationEscape(error.message)}</td></tr>`;
    }
}
async function storeContinuityMeasurement() {
    const button = document.getElementById('measureContinuityButton');
    button.disabled = true;
    const original = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Midiendo...';
    try {
        await api.post('/admin/continuity-history', {}, {_timeout: 300000});
        await loadContinuityHistory();
    } catch (error) {
        alert(error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}
async function loadContinuityPolicy() {
    try {
        const policy = await api.get('/admin/continuity-policy', {_fresh: true});
        document.getElementById('policyTarget').value = policy.target_readiness;
        document.getElementById('policyCritical').value = policy.critical_readiness;
        document.getElementById('policyBackupAge').value = policy.max_backup_age_hours;
        document.getElementById('policyRetention').value = policy.backup_retention_days;
    } catch (error) {
        alert(error.message);
    }
}
async function saveContinuityPolicy(event) {
    event.preventDefault();
    const button = document.getElementById('savePolicyButton');
    button.disabled = true;
    try {
        const payload = {
            target_readiness: Number(document.getElementById('policyTarget').value),
            critical_readiness: Number(document.getElementById('policyCritical').value),
            max_backup_age_hours: Number(document.getElementById('policyBackupAge').value),
            backup_retention_days: Number(document.getElementById('policyRetention').value)
        };
        if (payload.critical_readiness >= payload.target_readiness) {
            throw new Error('El umbral crítico debe ser menor que el objetivo.');
        }
        await api.put('/admin/continuity-policy', payload);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('continuityPolicyModal')).hide();
        renderOperations(await api.post('/admin/operational-alerts/scan', {}, {_timeout: 300000}));
        await loadContinuityHistory();
    } catch (error) {
        alert(error.message);
    } finally {
        button.disabled = false;
    }
}
async function acknowledgeAlert(id, button) {
    button.disabled = true;
    try {
        await api.put(`/admin/operational-alerts/${id}/acknowledge`, {});
        await loadOperations();
    } catch (error) {
        button.disabled = false;
        alert(error.message);
    }
}
async function downloadContinuityReport() {
    const button = document.getElementById('continuityReportButton');
    button.disabled = true;
    const original = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando...';
    try {
        const response = await fetch(`${API_BASE_URL}/admin/continuity-report.pdf`, {
            headers: {Authorization: `Bearer ${auth.getToken()}`, Accept: 'application/pdf'},
            credentials: 'include'
        });
        if (!response.ok) {
            const result = await response.json().catch(() => ({}));
            throw new Error(result.message || 'No se pudo generar el reporte de continuidad.');
        }
        const link = document.createElement('a');
        link.href = URL.createObjectURL(await response.blob());
        link.download = `continuidad_operativa_${new Date().toISOString().slice(0, 10)}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(link.href);
    } catch (error) {
        alert(error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}
document.getElementById('scanButton').addEventListener('click', scanOperations);
document.getElementById('continuityReportButton').addEventListener('click', downloadContinuityReport);
document.getElementById('measureContinuityButton').addEventListener('click', storeContinuityMeasurement);
document.getElementById('continuityPolicyModal').addEventListener('show.bs.modal', loadContinuityPolicy);
document.getElementById('continuityPolicyForm').addEventListener('submit', saveContinuityPolicy);
document.addEventListener('DOMContentLoaded', () => {
    loadOperations();
    loadContinuityHistory();
});
</script>
</body>
</html>
