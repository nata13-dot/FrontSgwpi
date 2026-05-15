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
    <title>Avisos del Sistema - <?= APP_NAME ?></title>
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
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Avisos del Sistema</h1>
                    <p class="text-muted mb-0">Define mensajes tipo toast por audiencia.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" type="button" onclick="loadNotices()"><i class="bi bi-arrow-clockwise"></i></button>
                    <button class="btn btn-primary" type="button" onclick="openNoticeModal()"><i class="bi bi-plus-circle"></i> Nuevo aviso</button>
                </div>
            </div>

            <div id="alertContainer"></div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Titulo</th>
                                    <th>Mensaje</th>
                                    <th>Audiencia</th>
                                    <th>Tipo</th>
                                    <th>Vigencia</th>
                                    <th>Duracion</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="noticesTable">
                                <tr><td colspan="8" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer small text-muted">
                    Si hay mas de 3 avisos aplicables a una vista, el sistema los mostrara en 3 rondas automaticas.
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="noticeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="noticeForm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-megaphone"></i> Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="noticeId">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="noticeTitle">Titulo</label>
                        <input type="text" class="form-control" id="noticeTitle" maxlength="90" placeholder="Ej. Entrega proxima">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="noticeAudience">Audiencia</label>
                        <select class="form-select" id="noticeAudience" required>
                            <option value="all">Todo el sistema</option>
                            <option value="index">Solo index publico</option>
                            <option value="authenticated">Solo inicio de sesion</option>
                            <option value="academic">Estudiantes y docentes</option>
                            <option value="teacher">Solo docentes</option>
                            <option value="student">Solo estudiantes</option>
                            <option value="admin">Solo administradores</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="noticeType">Tipo</label>
                        <select class="form-select" id="noticeType" required>
                            <option value="info">Informativo</option>
                            <option value="success">Exito</option>
                            <option value="warning">Advertencia</option>
                            <option value="danger">Importante</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="noticeDuration">Autocierre</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="noticeDuration" min="2" max="30" value="4" required>
                            <span class="input-group-text">s</span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="noticeActive" checked>
                            <label class="form-check-label" for="noticeActive">Aviso activo</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="noticeStartsAt">Mostrar desde</label>
                        <input type="date" class="form-control" id="noticeStartsAt">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="noticeEndsAt">Eliminar despues de</label>
                        <input type="date" class="form-control" id="noticeEndsAt">
                        <div class="form-text">Al pasar esta fecha, el aviso se elimina automaticamente.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="noticeMessage">Mensaje</label>
                        <textarea class="form-control" id="noticeMessage" rows="4" maxlength="500" required></textarea>
                        <div class="form-text">Maximo 500 caracteres. El toast se cerrara automaticamente.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar aviso</button>
            </div>
        </form>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = 'https://swapi-production-8341.up.railway.app/api';</script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/api.js"></script>
<script src="/assets/js/app.js"></script>
<script>
let notices = [];
let noticeModal = null;

const audienceLabels = {
    all: 'Todo',
    index: 'Index publico',
    authenticated: 'Inicio de sesion',
    academic: 'Estudiantes y docentes',
    teacher: 'Docentes',
    student: 'Estudiantes',
    admin: 'Administradores'
};

const typeLabels = {
    info: ['Informativo', 'bg-info text-dark'],
    success: ['Exito', 'bg-success'],
    warning: ['Advertencia', 'bg-warning text-dark'],
    danger: ['Importante', 'bg-danger']
};

function esc(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
}

function renderNotices() {
    const tbody = document.getElementById('noticesTable');
    if (!notices.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay avisos configurados.</td></tr>';
        return;
    }

    tbody.innerHTML = notices.map((notice, index) => {
        const type = typeLabels[notice.type] || typeLabels.info;
        return `
            <tr>
                <td><strong>${esc(notice.title || 'Sin titulo')}</strong></td>
                <td style="max-width:360px;white-space:normal;">${esc(notice.message)}</td>
                <td><span class="badge bg-secondary">${esc(audienceLabels[notice.audience] || notice.audience)}</span></td>
                <td><span class="badge ${type[1]}">${type[0]}</span></td>
                <td>${formatValidity(notice)}</td>
                <td>${Number(notice.duration_seconds || 4)} s</td>
                <td>${notice.active ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-light text-dark">Inactivo</span>'}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" type="button" onclick="editNotice(${index})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-outline-secondary" type="button" onclick="toggleNotice(${index})"><i class="bi ${notice.active ? 'bi-eye-slash' : 'bi-eye'}"></i></button>
                        <button class="btn btn-outline-danger" type="button" onclick="deleteNotice(${index})"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>`;
    }).join('');
}

async function loadNotices() {
    try {
        const response = await api.get('/notices');
        notices = response.data || [];
        renderNotices();
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error cargando avisos');
    }
}

function openNoticeModal(index = null) {
    const notice = index === null ? null : notices[index];
    document.getElementById('noticeId').value = index ?? '';
    document.getElementById('noticeTitle').value = notice?.title || '';
    document.getElementById('noticeAudience').value = notice?.audience || 'all';
    document.getElementById('noticeType').value = notice?.type || 'info';
    document.getElementById('noticeDuration').value = notice?.duration_seconds || 4;
    document.getElementById('noticeStartsAt').value = notice?.starts_at || '';
    document.getElementById('noticeEndsAt').value = notice?.ends_at || '';
    document.getElementById('noticeActive').checked = notice?.active ?? true;
    document.getElementById('noticeMessage').value = notice?.message || '';
    noticeModal.show();
}

function editNotice(index) {
    openNoticeModal(index);
}

async function saveNotices(message = 'Avisos guardados') {
    const response = await api.put('/notices', { notices });
    notices = response.data || notices;
    renderNotices();
    swalToast('success', response.message || message);
}

document.getElementById('noticeForm').addEventListener('submit', async event => {
    event.preventDefault();
    const indexValue = document.getElementById('noticeId').value;
    const notice = {
        id: indexValue !== '' ? notices[Number(indexValue)]?.id : undefined,
        title: document.getElementById('noticeTitle').value.trim(),
        audience: document.getElementById('noticeAudience').value,
        type: document.getElementById('noticeType').value,
        duration_seconds: Number(document.getElementById('noticeDuration').value || 4),
        starts_at: document.getElementById('noticeStartsAt').value || null,
        ends_at: document.getElementById('noticeEndsAt').value || null,
        active: document.getElementById('noticeActive').checked,
        message: document.getElementById('noticeMessage').value.trim()
    };

    if (!notice.message) {
        showAlert('#alertContainer', 'danger', 'El mensaje del aviso es obligatorio.');
        return;
    }

    if (notice.duration_seconds < 2 || notice.duration_seconds > 30) {
        showAlert('#alertContainer', 'danger', 'La duracion debe estar entre 2 y 30 segundos.');
        return;
    }

    if (notice.starts_at && notice.ends_at && notice.ends_at < notice.starts_at) {
        showAlert('#alertContainer', 'danger', 'La fecha final debe ser igual o posterior a la fecha inicial.');
        return;
    }

    if (indexValue === '') {
        notices.push(notice);
    } else {
        notices[Number(indexValue)] = notice;
    }

    try {
        await saveNotices();
        noticeModal.hide();
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error guardando aviso');
    }
});

async function toggleNotice(index) {
    notices[index].active = !notices[index].active;
    await saveNotices('Aviso actualizado');
}

async function deleteNotice(index) {
    const confirmed = await confirmAction({
        title: 'Eliminar aviso',
        text: 'Este aviso dejara de mostrarse en el sistema.',
        confirmButtonText: 'Si, eliminar'
    });
    if (!confirmed) return;
    notices.splice(index, 1);
    await saveNotices('Aviso eliminado');
}

document.addEventListener('DOMContentLoaded', () => {
    noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));
    loadNotices();
});

function formatValidity(notice) {
    if (!notice.starts_at && !notice.ends_at) return '<span class="text-muted small">Sin limite</span>';
    const start = notice.starts_at ? esc(notice.starts_at) : 'Hoy';
    const end = notice.ends_at ? esc(notice.ends_at) : 'Sin fin';
    return `<span class="small">${start}<br>${end}</span>`;
}
</script>
</body>
</html>
