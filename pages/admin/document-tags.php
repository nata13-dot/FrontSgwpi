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
    <title>Gestión de Etiquetas - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .tags-toolbar {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
        }
        .tag-summary-card {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            height: 100%;
        }
        .tag-summary-card strong {
            color: var(--primary-blue);
            display: block;
            font-size: 1.7rem;
            line-height: 1;
        }
        .tag-summary-card span {
            color: var(--text-muted);
            font-size: .86rem;
            font-weight: 600;
        }
        .tag-chip-preview {
            align-items: center;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-weight: 700;
            gap: 8px;
            min-width: 124px;
            padding: 8px 12px;
        }
        .tag-color-dot {
            border: 1px solid rgba(255,255,255,.62);
            border-radius: 999px;
            display: inline-block;
            height: 16px;
            width: 16px;
        }
        .tag-preset {
            border: 2px solid transparent;
            border-radius: 999px;
            height: 30px;
            width: 30px;
        }
        .tag-preset.active {
            border-color: var(--text-dark);
            box-shadow: 0 0 0 3px rgba(27,57,106,.14);
        }
        .tag-description {
            max-width: 520px;
            white-space: normal;
        }
        .tag-color-code {
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-dark);
            display: inline-block;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h1 class="mb-1">Gestión de Etiquetas de Documentos</h1>
                        <p class="text-muted mb-0">Organiza los documentos del repositorio y entregables con etiquetas reutilizables.</p>
                    </div>
                    <button class="btn btn-primary" type="button" onclick="openTagModal()">
                        <i class="bi bi-plus-circle"></i> Nueva etiqueta
                    </button>
                </div>

                <div id="alertContainer"></div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="tag-summary-card">
                            <strong id="tagTotal">0</strong>
                            <span>Etiquetas activas</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tag-summary-card">
                            <strong id="tagUsed">0</strong>
                            <span>Etiquetas en uso en esta página</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tag-summary-card">
                            <strong id="tagDocuments">0</strong>
                            <span>Relaciones visibles en esta página</span>
                        </div>
                    </div>
                </div>

                <div class="tags-toolbar mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-7">
                            <label class="form-label" for="tagSearch">Buscar etiqueta</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control" id="tagSearch" placeholder="Nombre o descripción" oninput="scheduleTagSearch()">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearTagSearch()" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="tagPerPage">Mostrar</label>
                            <select class="form-select" id="tagPerPage" onchange="loadTags(1)">
                                <option value="10">10 por página</option>
                                <option value="15" selected>15 por página</option>
                                <option value="30">30 por página</option>
                            </select>
                        </div>
                        <div class="col-lg-2 d-grid">
                            <button class="btn btn-outline-primary" type="button" onclick="loadTags(currentPage, true)">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span><i class="bi bi-tags"></i> Etiquetas registradas</span>
                        <span class="badge bg-light text-primary" id="tagPageInfo">Cargando...</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Color</th>
                                        <th>Descripción</th>
                                        <th>Documentos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tagsTable">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-custom"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <nav id="paginationContainer" class="mt-4"></nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tagModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="tagForm" onsubmit="saveTag(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="tagModalTitle"><i class="bi bi-tag"></i> Nueva etiqueta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="tagModalAlert"></div>
                    <input type="hidden" id="tagId">
                    <div class="mb-3">
                        <label class="form-label" for="tagName">Nombre</label>
                        <input type="text" class="form-control" id="tagName" maxlength="100" required oninput="refreshTagPreview()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tagDescription">Descripción</label>
                        <textarea class="form-control" id="tagDescription" rows="3" maxlength="1000" placeholder="Uso recomendado de la etiqueta"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tagColor">Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" class="form-control form-control-color" id="tagColor" value="#1B396A" title="Selecciona un color" onchange="refreshTagPreview()">
                            <input type="text" class="form-control" id="tagColorText" maxlength="7" value="#1B396A" oninput="syncTagColorFromText()">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="tagColorPresets"></div>
                    <div>
                        <span class="tag-chip-preview" id="tagPreview" style="background-color: #1B396A;">
                            <span class="tag-color-dot"></span>
                            <span>Etiqueta</span>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="tagSaveBtn"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>

    <script>
        let currentPage = 1;
        let lastPage = 1;
        let tagModal;
        let tagSearchTimer;
        let currentTags = [];
        const colorPresets = ['#1B396A', '#2D5A96', '#218838', '#b38600', '#b42318', '#6f42c1', '#0f766e', '#475569'];

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
        }

        function contrastColor(hex) {
            const clean = String(hex || '#1B396A').replace('#', '');
            const r = parseInt(clean.substring(0, 2), 16) || 0;
            const g = parseInt(clean.substring(2, 4), 16) || 0;
            const b = parseInt(clean.substring(4, 6), 16) || 0;
            return ((r * 299 + g * 587 + b * 114) / 1000) > 150 ? '#111827' : '#ffffff';
        }

        async function loadTags(page = 1, forceFresh = false) {
            const tbody = document.getElementById('tagsTable');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-custom"></div><p class="text-muted mt-2 mb-0">Cargando etiquetas...</p></td></tr>';
            try {
                const params = {
                    page,
                    per_page: document.getElementById('tagPerPage').value,
                    q: document.getElementById('tagSearch').value.trim()
                };
                if (forceFresh) params._fresh = true;
                const response = await api.get('/document-tags', params);
                currentTags = response.data || [];
                currentPage = Number(response.current_page || page);
                lastPage = Number(response.last_page || 1);
                tbody.innerHTML = '';
                updateSummary(response);
                renderPagination();

                if (!currentTags.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-tags display-5 d-block mb-2"></i>No hay etiquetas con esos filtros.</td></tr>';
                    return;
                }

                currentTags.forEach(tag => {
                    const color = tag.color || '#1B396A';
                    const textColor = contrastColor(color);
                    const documentsCount = Number(tag.documents_count ?? ((tag.deliverables_count || 0) + (tag.repository_documents_count || 0)));
                    const row = `
                        <tr>
                            <td>
                                <span class="tag-chip-preview" style="background-color: ${escapeHtml(color)}; color: ${textColor};">
                                    <span class="tag-color-dot"></span>
                                    <span>${escapeHtml(tag.nombre)}</span>
                                </span>
                            </td>
                            <td><code class="tag-color-code">${escapeHtml(color)}</code></td>
                            <td class="tag-description">${escapeHtml(tag.descripcion || 'Sin descripción')}</td>
                            <td>
                                <span class="badge bg-primary">${documentsCount}</span>
                                <span class="text-muted small d-block">${Number(tag.repository_documents_count || 0)} repo · ${Number(tag.deliverables_count || 0)} entregables</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="openTagModal(${Number(tag.id)})" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteTag('${tag.id}')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } catch (error) {
                console.error('Error:', error);
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${escapeHtml(error.message || 'No se pudieron cargar las etiquetas.')}</td></tr>`;
            }
        }

        function updateSummary(response) {
            const total = Number(response.total || currentTags.length);
            const used = currentTags.filter(tag => Number(tag.documents_count ?? ((tag.deliverables_count || 0) + (tag.repository_documents_count || 0))) > 0).length;
            const relations = currentTags.reduce((sum, tag) => sum + Number(tag.documents_count ?? ((tag.deliverables_count || 0) + (tag.repository_documents_count || 0))), 0);
            document.getElementById('tagTotal').textContent = total;
            document.getElementById('tagUsed').textContent = used;
            document.getElementById('tagDocuments').textContent = relations;
            document.getElementById('tagPageInfo').textContent = `Página ${currentPage} de ${lastPage}`;
        }

        function renderPagination() {
            const nav = document.getElementById('paginationContainer');
            if (lastPage <= 1) {
                nav.innerHTML = '';
                return;
            }
            nav.innerHTML = `
                <ul class="pagination justify-content-center">
                    <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                        <button class="page-link" onclick="loadTags(${currentPage - 1})">Anterior</button>
                    </li>
                    <li class="page-item disabled"><span class="page-link">${currentPage} / ${lastPage}</span></li>
                    <li class="page-item ${currentPage >= lastPage ? 'disabled' : ''}">
                        <button class="page-link" onclick="loadTags(${currentPage + 1})">Siguiente</button>
                    </li>
                </ul>`;
        }

        function scheduleTagSearch() {
            clearTimeout(tagSearchTimer);
            tagSearchTimer = setTimeout(() => loadTags(1), 350);
        }

        function clearTagSearch() {
            document.getElementById('tagSearch').value = '';
            loadTags(1);
        }

        function renderColorPresets(selected = '#1B396A') {
            document.getElementById('tagColorPresets').innerHTML = colorPresets.map(color => `
                <button type="button" class="tag-preset ${color.toLowerCase() === selected.toLowerCase() ? 'active' : ''}" style="background:${color}" onclick="setTagColor('${color}')" title="${color}"></button>
            `).join('');
        }

        function setTagColor(color) {
            document.getElementById('tagColor').value = color;
            document.getElementById('tagColorText').value = color;
            renderColorPresets(color);
            refreshTagPreview();
        }

        function syncTagColorFromText() {
            const value = document.getElementById('tagColorText').value.trim();
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                document.getElementById('tagColor').value = value;
                renderColorPresets(value);
                refreshTagPreview();
            }
        }

        function refreshTagPreview() {
            const name = document.getElementById('tagName').value.trim() || 'Etiqueta';
            const color = document.getElementById('tagColor').value || '#1B396A';
            document.getElementById('tagColorText').value = color;
            const preview = document.getElementById('tagPreview');
            preview.style.backgroundColor = color;
            preview.style.color = contrastColor(color);
            preview.querySelector('span:last-child').textContent = name;
            renderColorPresets(color);
        }

        async function deleteTag(tagId) {
            const confirmed = await confirmAction({
                title: 'Eliminar etiqueta',
                text: '¿Estas seguro de eliminar esta etiqueta?',
                confirmButtonText: 'Si, eliminar'
            });
            if (!confirmed) return;

            api.delete(`/document-tags/${tagId}`).then(() => {
                loadTags(currentPage, true);
                swalToast('success', 'Etiqueta eliminada');
            }).catch(error => showAlert('#alertContainer', 'danger', error.message || 'No se pudo eliminar la etiqueta'));
        }

        function openTagModal(tagId = null) {
            if (!tagModal) tagModal = new bootstrap.Modal(document.getElementById('tagModal'));
            const tag = tagId ? currentTags.find(item => Number(item.id) === Number(tagId)) : null;
            document.getElementById('tagForm').reset();
            document.getElementById('tagModalAlert').innerHTML = '';
            document.getElementById('tagId').value = tag?.id || '';
            document.getElementById('tagModalTitle').innerHTML = tag ? '<i class="bi bi-pencil-square"></i> Editar etiqueta' : '<i class="bi bi-tag"></i> Nueva etiqueta';
            document.getElementById('tagName').value = tag?.nombre || '';
            document.getElementById('tagDescription').value = tag?.descripcion || '';
            setTagColor(tag?.color || '#1B396A');
            tagModal.show();
        }

        async function saveTag(event) {
            event.preventDefault();
            const tagId = document.getElementById('tagId').value;
            const payload = {
                nombre: document.getElementById('tagName').value.trim(),
                descripcion: document.getElementById('tagDescription').value.trim(),
                color: document.getElementById('tagColorText').value.trim()
            };

            if (!payload.nombre || !/^#[0-9A-Fa-f]{6}$/.test(payload.color)) {
                document.getElementById('tagModalAlert').innerHTML = '<div class="alert alert-warning">Nombre y color hexadecimal válido son obligatorios.</div>';
                return;
            }

            const button = document.getElementById('tagSaveBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
            try {
                await (tagId ? api.put(`/document-tags/${tagId}`, payload) : api.post('/document-tags', payload));
                tagModal.hide();
                swalToast('success', tagId ? 'Etiqueta actualizada' : 'Etiqueta creada');
                await loadTags(currentPage, true);
            } catch (error) {
                document.getElementById('tagModalAlert').innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message || 'No se pudo guardar la etiqueta')}</div>`;
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderColorPresets();
            loadTags();
        });
    </script>
</body>
</html>
