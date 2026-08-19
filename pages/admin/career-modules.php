<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
requireAdmin();
$career = active_career() ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulos de carrera - <?= APP_NAME ?></title>
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
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                <div>
                    <span class="text-uppercase small fw-bold text-muted"><?= htmlspecialchars($career['clave'] ?? '') ?></span>
                    <h1 class="h2 mb-2">Módulos de <?= htmlspecialchars($career['nombre_corto'] ?? 'la carrera') ?></h1>
                    <p class="text-muted mb-0">Administra funciones particulares, registros operativos e indicadores.</p>
                </div>
                <?php if (is_general_admin()): ?>
                    <a href="/pages/admin/careers.php" class="btn btn-outline-primary"><i class="bi bi-diagram-3"></i> Carreras y accesos</a>
                <?php endif; ?>
            </div>
            <div id="moduleAlert"></div>

            <section class="mb-5">
                <h2 class="h5 mb-3">Módulos habilitados</h2>
                <div class="career-module-grid" id="moduleGrid"><div class="text-muted">Cargando...</div></div>
            </section>

            <div class="row g-4">
                <div class="col-xl-7">
                    <section class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Registros del módulo</h2>
                                    <small class="text-muted" id="recordModuleLabel">Selecciona un módulo.</small>
                                </div>
                                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#recordFormWrap"><i class="bi bi-plus-circle"></i> Nuevo registro</button>
                            </div>
                        </div>
                        <div class="collapse px-4" id="recordFormWrap">
                            <form class="career-record-form border rounded-3 p-3 mb-3" id="recordForm">
                                <div><label class="form-label" for="recordCode">Clave</label><input class="form-control" id="recordCode" maxlength="50"></div>
                                <div><label class="form-label" for="recordTitle">Título</label><input class="form-control" id="recordTitle" required maxlength="180"></div>
                                <div><label class="form-label" for="recordStatus">Estado</label><select class="form-select" id="recordStatus"><option value="activo">Activo</option><option value="planeado">Planeado</option><option value="en_proceso">En proceso</option><option value="completado">Completado</option></select></div>
                                <div class="career-record-description"><label class="form-label" for="recordDescription">Descripción</label><textarea class="form-control" id="recordDescription" rows="2"></textarea></div>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Guardar</button>
                            </form>
                        </div>
                        <div class="table-responsive px-4 pb-4">
                            <table class="table align-middle"><thead><tr><th>Registro</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody id="recordRows"><tr><td colspan="3" class="text-center text-muted py-4">Selecciona un módulo.</td></tr></tbody></table>
                        </div>
                    </section>
                </div>
                <div class="col-xl-5">
                    <section class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 p-4">
                            <h2 class="h5 mb-1">Indicadores</h2>
                            <small class="text-muted">Captura el valor real; las metas pueden ajustarse.</small>
                        </div>
                        <div class="card-body pt-0" id="indicatorList"><p class="text-muted">Cargando...</p></div>
                    </section>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/api.js"></script>
<script src="/assets/js/app.js"></script>
<script>
const moduleState = { modules: [], selected: null, indicators: [] };
const moduleEsc = value => String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));

function moduleMessage(type, text) {
    document.getElementById('moduleAlert').innerHTML = `<div class="alert alert-${type}">${moduleEsc(text)}</div>`;
}

async function loadModules() {
    try {
        const response = await api.get('/career/modules', {_fresh: true});
        moduleState.modules = response.modules || [];
        moduleState.indicators = response.indicators || [];
        renderModules();
        renderIndicators();
        const preferred = moduleState.selected?.modulo || moduleState.modules.find(item => item.habilitado && !['usuarios','proyectos','academico','entregables','evaluaciones','repositorio','reportes','configuracion'].includes(item.modulo))?.modulo;
        if (preferred) selectModule(preferred);
    } catch (error) {
        moduleMessage('danger', error.message);
    }
}

function renderModules() {
    document.getElementById('moduleGrid').innerHTML = moduleState.modules.map(item => `
        <article class="career-module-card ${moduleState.selected?.modulo === item.modulo ? 'active' : ''} ${item.habilitado ? '' : 'disabled'}" data-module="${moduleEsc(item.modulo)}">
            <button type="button" class="career-module-select" data-select-module="${moduleEsc(item.modulo)}">
                <i class="bi ${moduleEsc(item.icon)}"></i>
                <span><strong>${moduleEsc(item.label)}</strong><small>${Number(item.records_count || 0)} registros</small></span>
            </button>
            <label class="form-check form-switch m-0" title="Habilitar módulo">
                <input class="form-check-input" type="checkbox" data-toggle-module="${item.id}" ${item.habilitado ? 'checked' : ''}>
            </label>
        </article>
    `).join('');
    document.querySelectorAll('[data-select-module]').forEach(button => button.addEventListener('click', () => selectModule(button.dataset.selectModule)));
    document.querySelectorAll('[data-toggle-module]').forEach(input => input.addEventListener('change', async () => {
        try {
            await api.put(`/career/modules/${input.dataset.toggleModule}`, {habilitado: input.checked});
            loadModules();
        } catch (error) {
            input.checked = !input.checked;
            moduleMessage('danger', error.message);
        }
    }));
}

function selectModule(name) {
    moduleState.selected = moduleState.modules.find(item => item.modulo === name) || null;
    if (!moduleState.selected) return;
    renderModules();
    document.getElementById('recordModuleLabel').textContent = moduleState.selected.label;
    loadRecords();
}

async function loadRecords() {
    if (!moduleState.selected) return;
    const body = document.getElementById('recordRows');
    body.innerHTML = '<tr><td colspan="3" class="text-center py-4">Cargando...</td></tr>';
    try {
        const response = await api.get('/career/module-records', {modulo: moduleState.selected.modulo, _fresh: true});
        const rows = response.data || [];
        body.innerHTML = rows.length ? rows.map(record => `<tr>
            <td><strong>${moduleEsc(record.titulo)}</strong><small class="d-block text-muted">${moduleEsc(record.clave || record.descripcion || 'Sin descripción')}</small></td>
            <td><span class="badge text-bg-light">${moduleEsc(record.estado)}</span></td>
            <td class="text-end"><button class="btn btn-sm btn-outline-danger" data-delete-record="${record.id}"><i class="bi bi-trash"></i></button></td>
        </tr>`).join('') : '<tr><td colspan="3" class="text-center text-muted py-4">Todavía no hay registros reales en este módulo.</td></tr>';
        document.querySelectorAll('[data-delete-record]').forEach(button => button.addEventListener('click', async () => {
            if (!confirm('¿Desactivar este registro?')) return;
            await api.delete(`/career/module-records/${button.dataset.deleteRecord}`);
            await loadModules();
        }));
    } catch (error) {
        body.innerHTML = `<tr><td colspan="3" class="text-danger py-4">${moduleEsc(error.message)}</td></tr>`;
    }
}

function renderIndicators() {
    const list = document.getElementById('indicatorList');
    list.innerHTML = moduleState.indicators.length ? moduleState.indicators.map(indicator => `
        <form class="career-indicator-editor" data-indicator="${indicator.id}" style="--indicator-color:${moduleEsc(indicator.color || '#1B396A')}">
            <div class="career-indicator-title"><i class="bi ${moduleEsc(indicator.icono || 'bi-bar-chart')}"></i><span><strong>${moduleEsc(indicator.nombre)}</strong><small>${moduleEsc(indicator.modulo)}</small></span></div>
            <label>Actual <input class="form-control form-control-sm" name="valor_actual" type="number" step="0.01" value="${indicator.valor_actual ?? ''}" placeholder="Sin captura"></label>
            <label>Meta <input class="form-control form-control-sm" name="valor_meta" type="number" step="0.01" value="${indicator.valor_meta ?? ''}"></label>
            <button class="btn btn-sm btn-outline-primary" type="submit">Guardar</button>
        </form>
    `).join('') : '<p class="text-muted">No hay indicadores configurados.</p>';
    document.querySelectorAll('[data-indicator]').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const data = new FormData(form);
        try {
            await api.put(`/career/indicators/${form.dataset.indicator}`, {
                valor_actual: data.get('valor_actual') === '' ? null : Number(data.get('valor_actual')),
                valor_meta: data.get('valor_meta') === '' ? null : Number(data.get('valor_meta'))
            });
            moduleMessage('success', 'Indicador actualizado.');
            loadModules();
        } catch (error) {
            moduleMessage('danger', error.message);
        }
    }));
}

document.getElementById('recordForm').addEventListener('submit', async event => {
    event.preventDefault();
    if (!moduleState.selected?.habilitado) return moduleMessage('warning', 'Habilita el módulo antes de registrar información.');
    try {
        await api.post('/career/module-records', {
            modulo: moduleState.selected.modulo,
            clave: document.getElementById('recordCode').value.trim() || null,
            titulo: document.getElementById('recordTitle').value.trim(),
            descripcion: document.getElementById('recordDescription').value.trim() || null,
            estado: document.getElementById('recordStatus').value,
            activo: true
        });
        event.target.reset();
        moduleMessage('success', 'Registro guardado correctamente.');
        loadModules();
    } catch (error) {
        moduleMessage('danger', error.message);
    }
});
document.addEventListener('DOMContentLoaded', loadModules);
</script>
</body>
</html>
