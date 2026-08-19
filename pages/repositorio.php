<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="repository-shell">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <!-- Hero -->
    <div class="hero-gradient repository-page-hero">
        <div class="container-xl">
            <span class="repository-hero-kicker"><i class="bi bi-journals"></i> Acervo institucional</span>
            <h1 class="display-4 fw-bold mb-3">Repositorio Digital</h1>
            <p class="lead mb-0">Consulta proyectos, tesis y documentos académicos publicados por el instituto.</p>
        </div>
    </div>

    <main class="container-xl pb-5 repository-content">
        <?php if (is_authenticated() && (is_admin() || is_student())): ?>
            <div class="d-flex justify-content-end mt-4 repository-upload-action">
                <button type="button" class="btn btn-primary" onclick="openRepositoryUploadModal()">
                    <i class="bi bi-cloud-arrow-up"></i> <?= is_student() ? 'Subir documento de proyecto' : 'Agregar documento' ?>
                </button>
            </div>
        <?php endif; ?>

        <section class="repository-toolbar mt-4" aria-label="Buscar y filtrar documentos">
            <div class="repository-toolbar-heading">
                <div>
                    <span class="repository-section-kicker">Catálogo público</span>
                    <h2 class="h4 mb-1">Documentos disponibles</h2>
                </div>
                <span class="repository-results-summary" id="repositoryResultsSummary" aria-live="polite">Cargando documentos…</span>
            </div>
            <div class="row g-3">
            <div class="col-lg-5 col-md-12 repository-search-field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input 
                    type="text" 
                    class="form-control form-control-lg"
                    id="searchInput"
                    placeholder="Buscar documentos..."
                    aria-label="Buscar documentos por título, descripción o autor"
                >
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select form-select-lg" id="categoryFilter">
                    <option value="">Todas las categorías</option>
                    <option value="general">General</option>
                    <option value="desarrollo">Desarrollo de proyecto</option>
                    <option value="tesis">Tesis</option>
                    <option value="residencias">Residencias</option>
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <select class="form-select form-select-lg" id="sortFilter">
                    <option value="reciente">Más recientes</option>
                    <option value="antiguo">Más antiguos</option>
                    <option value="nombre_asc">Nombre A-Z</option>
                    <option value="nombre_desc">Nombre Z-A</option>
                </select>
            </div>
            </div>
        </section>

        <!-- Documentos -->
        <div class="row g-4 repository-grid" id="documentosContainer">
            <div class="col-12 text-center">
                <div class="spinner-custom"></div>
                <p class="mt-2">Cargando documentos...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="row mt-5">
            <div class="col-12">
                <nav id="paginationContainer"></nav>
            </div>
        </div>
    </main>

    <?php if (is_authenticated() && (is_admin() || is_student())): ?>
    <div class="modal fade" id="repositoryUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="repositoryUploadForm" onsubmit="submitRepositoryDocument(event)">
                    <input type="hidden" id="repoDocumentId" name="document_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="repositoryModalTitle"><i class="bi bi-cloud-arrow-up"></i> Agregar documento al repositorio</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="repositoryUploadAlert"></div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="repoNombre">Nombre del documento</label>
                                <input type="text" class="form-control" id="repoNombre" name="nombre" maxlength="255" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="repoDescripcion">Descripción</label>
                                <textarea class="form-control" id="repoDescripcion" name="descripcion" rows="3" maxlength="5000" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="repoAutores">Autores</label>
                                <input type="text" class="form-control" id="repoAutores" name="autores" maxlength="1000" required>
                            </div>
                            <?php if (is_student()): ?>
                            <div class="col-md-6">
                                <label class="form-label" for="repoProjectId">Proyecto asignado</label>
                                <select class="form-select" id="repoProjectId" name="project_id" required>
                                    <option value="">Cargando proyectos...</option>
                                </select>
                                <div class="form-text">El documento quedará privado hasta que administración lo publique.</div>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label" for="repoArchivo">Archivo</label>
                                <input type="file" class="form-control" id="repoArchivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,.jpg,.jpeg,.png,.webp,.epub" required>
                                <div class="form-text" id="repoArchivoHelp">Permitidos: PDF, Word, Excel, ZIP, TXT, imagenes JPG/PNG/WebP y EPUB.</div>
                            </div>
                            <?php if (is_admin()): ?>
                            <div class="col-md-6">
                                <label class="form-label" for="repoVisibility">Visibilidad</label>
                                <select class="form-select" id="repoVisibility" name="visibility">
                                    <option value="public">Público: aparece para todos</option>
                                    <option value="private">Privado: solo docentes y administradores</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado de publicación</label>
                                <div class="border rounded px-3 py-2 small text-muted" id="repoVisibilityHelp">
                                    El documento será visible en el repositorio público.
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (is_student()): ?>
                            <input type="hidden" name="visibility" value="private">
                            <?php endif; ?>
                            <?php if (is_admin()): ?>
                            <div class="col-12">
                                <label class="form-label">Etiquetas</label>
                                <div id="repoTags" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="repoUploadBtn">
                            <i class="bi bi-cloud-arrow-up"></i> Subir documento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const API_BASE_URL = '<?= API_BASE_URL ?>';
        const CAN_MANAGE_REPOSITORY = <?= (is_authenticated() && is_admin()) ? 'true' : 'false' ?>;
        const CAN_UPLOAD_REPOSITORY = <?= (is_authenticated() && (is_admin() || is_student())) ? 'true' : 'false' ?>;
        const IS_STUDENT = <?= (is_authenticated() && is_student()) ? 'true' : 'false' ?>;
        let currentPage = 1;
        let filters = {
            buscar: '',
            categoria: '',
            ordenar: 'reciente'
        };
        let repositoryUploadModal;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function formatRepositoryDate(value) {
            if (!value) return 'Fecha no disponible';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return 'Fecha no disponible';
            return new Intl.DateTimeFormat('es-MX', {
                day: '2-digit', month: 'short', year: 'numeric'
            }).format(date);
        }

        async function loadDocumentos(page = 1) {
            try {
                const endpoint = CAN_MANAGE_REPOSITORY
                    ? '/repositorio/admin/list'
                    : (IS_STUDENT ? '/repositorio/student/list' : '/repositorio');
                const response = await api.get(endpoint, {
                    ...filters,
                    page: page
                });

                const container = document.getElementById('documentosContainer');
                container.innerHTML = '';

                if (!response.data || response.data.length === 0) {
                    container.innerHTML = '<div class="col-12"><div class="repository-empty-state"><i class="bi bi-search"></i><h3>No encontramos documentos</h3><p>Prueba con otra búsqueda o cambia los filtros seleccionados.</p></div></div>';
                    document.getElementById('repositoryResultsSummary').textContent = '0 documentos';
                    renderRepositoryPagination(response);
                    return;
                }

                response.data.forEach(doc => {
                    const categoryLabel = repositoryCategoryLabel(doc.document_category);
                    const isPrivate = ['private', 'privado'].includes(String(doc.visibility || doc.visibilidad || '').toLowerCase());
                    const visibilityBadge = (CAN_MANAGE_REPOSITORY || IS_STUDENT)
                        ? `<span class="badge ${isPrivate ? 'text-bg-warning' : 'text-bg-success'}">${isPrivate ? 'Privado' : 'Público'}</span>`
                        : '';
                    const adminActions = CAN_MANAGE_REPOSITORY ? `
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="openRepositoryEditModal(${Number(doc.id)})">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger flex-fill" onclick="deleteRepositoryDocument(${Number(doc.id)})">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </div>
                    ` : '';
                    const card = `
                        <div class="col-xl-4 col-md-6">
                            <article class="card h-100 repository-document-card">
                                <div class="card-body">
                                    <div class="repository-card-badges"><span class="badge repository-category-badge">${escapeHtml(categoryLabel)}</span>${visibilityBadge}</div>
                                    <h3 class="h5 card-title">${escapeHtml(doc.nombre || doc.titulo || 'Documento sin título')}</h3>
                                    <p class="card-text text-muted">${escapeHtml(doc.descripcion || 'Sin descripción disponible.')}</p>
                                    <div class="repository-tag-list">
                                        ${doc.tags ? doc.tags.map(tag => 
                                            `<span class="badge" style="--tag-color: ${escapeHtml(tag.color || '#2d5a96')}">${escapeHtml(tag.nombre)}</span>`
                                        ).join('') : ''}
                                    </div>
                                    <div class="repository-card-meta">
                                        <span><i class="bi bi-people"></i> ${escapeHtml(doc.autores || doc.autor_nombre || 'Autores no especificados')}</span>
                                        <span><i class="bi bi-calendar3"></i> ${escapeHtml(formatRepositoryDate(doc.created_at || doc.creado_en))}</span>
                                        ${doc.file_available === false ? '<span class="repository-file-missing"><i class="bi bi-exclamation-circle"></i> Archivo pendiente de recuperación</span>' : ''}
                                    </div>
                                </div>
                                <div class="card-footer repository-card-actions">
                                    <a href="/pages/repositorio-detail.php?id=${encodeURIComponent(doc.id)}" class="btn btn-primary w-100">
                                        <i class="bi bi-eye"></i> Ver detalles
                                    </a>
                                    ${adminActions}
                                </div>
                            </article>
                        </div>
                    `;
                    container.innerHTML += card;
                });

                currentPage = page;
                const total = Number(response.total ?? response.data.length);
                document.getElementById('repositoryResultsSummary').textContent = `${total} documento${total === 1 ? '' : 's'}`;
                renderRepositoryPagination(response);
            } catch (error) {
                console.error('Error al cargar documentos:', error);
                document.getElementById('repositoryResultsSummary').textContent = 'No disponible';
                document.getElementById('documentosContainer').innerHTML = `<div class="col-12"><div class="repository-empty-state repository-error-state"><i class="bi bi-exclamation-triangle"></i><h3>No fue posible cargar el repositorio</h3><p>${escapeHtml(error.message || 'Comprueba la conexión e intenta nuevamente.')}</p><button class="btn btn-outline-primary" type="button" onclick="loadDocumentos(currentPage)"><i class="bi bi-arrow-clockwise"></i> Reintentar</button></div></div>`;
            }
        }

        function renderRepositoryPagination(response) {
            const container = document.getElementById('paginationContainer');
            const current = Number(response.current_page || 1);
            const last = Number(response.last_page || 1);
            if (last <= 1) {
                container.innerHTML = '';
                return;
            }
            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);
            const pages = [];
            for (let page = start; page <= end; page++) pages.push(page);
            container.innerHTML = `<ul class="pagination justify-content-center">
                <li class="page-item ${current === 1 ? 'disabled' : ''}"><button class="page-link" type="button" ${current === 1 ? 'disabled' : ''} onclick="loadDocumentos(${current - 1})" aria-label="Página anterior"><i class="bi bi-chevron-left"></i></button></li>
                ${pages.map(page => `<li class="page-item ${page === current ? 'active' : ''}"><button class="page-link" type="button" onclick="loadDocumentos(${page})">${page}</button></li>`).join('')}
                <li class="page-item ${current === last ? 'disabled' : ''}"><button class="page-link" type="button" ${current === last ? 'disabled' : ''} onclick="loadDocumentos(${current + 1})" aria-label="Página siguiente"><i class="bi bi-chevron-right"></i></button></li>
            </ul>`;
        }

        async function loadRepositoryAdminData(selectedTagIds = []) {
            if (!document.getElementById('repositoryUploadModal')) return;
            const tagsResponse = await api.get('/document-tags', { per_page: 100 });

            const tagsBox = document.getElementById('repoTags');
            const tags = tagsResponse.data || [];
            const selected = selectedTagIds.map(id => String(id));
            tagsBox.innerHTML = tags.length
                ? tags.map(tag => `
                    <label class="form-check form-check-inline border rounded px-2 py-1">
                        <input class="form-check-input repo-tag" type="checkbox" value="${escapeHtml(tag.id)}" ${selected.includes(String(tag.id)) ? 'checked' : ''}>
                        <span class="form-check-label">${escapeHtml(tag.nombre)}</span>
                    </label>
                `).join('')
                : '<span class="text-muted small">Sin etiquetas creadas.</span>';
        }

        async function openRepositoryUploadModal() {
            if (!repositoryUploadModal) {
                repositoryUploadModal = new bootstrap.Modal(document.getElementById('repositoryUploadModal'));
            }
            document.getElementById('repositoryUploadForm').reset();
            document.getElementById('repositoryUploadAlert').innerHTML = '';
            document.getElementById('repoDocumentId').value = '';
            document.getElementById('repositoryModalTitle').innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Agregar documento al repositorio';
            document.getElementById('repoArchivo').required = true;
            document.getElementById('repoArchivoHelp').textContent = 'Permitidos: PDF, Word, Excel, ZIP, TXT, imagenes JPG/PNG/WebP y EPUB.';
            document.getElementById('repoUploadBtn').innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Subir documento';
            if (document.getElementById('repoVisibility')) document.getElementById('repoVisibility').value = 'public';
            refreshRepositoryVisibilityHelp();
            document.querySelectorAll('.repo-tag').forEach(input => input.checked = false);
            if (CAN_MANAGE_REPOSITORY) await loadRepositoryAdminData();
            if (IS_STUDENT) await loadStudentRepositoryProjects();
            repositoryUploadModal.show();
        }

        async function openRepositoryEditModal(documentId) {
            if (!CAN_MANAGE_REPOSITORY) return;
            if (!repositoryUploadModal) {
                repositoryUploadModal = new bootstrap.Modal(document.getElementById('repositoryUploadModal'));
            }

            document.getElementById('repositoryUploadForm').reset();
            document.getElementById('repositoryUploadAlert').innerHTML = '';
            document.getElementById('repositoryModalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Editar documento del repositorio';
            document.getElementById('repoArchivo').required = false;
            document.getElementById('repoArchivoHelp').textContent = 'Opcional: selecciona un archivo solo si deseas reemplazar el actual.';
            document.getElementById('repoUploadBtn').innerHTML = '<i class="bi bi-save"></i> Guardar cambios';

            try {
                const doc = await api.get(`/repositorio/${documentId}`);
                document.getElementById('repoDocumentId').value = doc.id;
                document.getElementById('repoNombre').value = doc.nombre || '';
                document.getElementById('repoDescripcion').value = doc.descripcion || '';
                document.getElementById('repoAutores').value = doc.autores || '';
                if (document.getElementById('repoVisibility')) document.getElementById('repoVisibility').value = doc.visibility === 'private' ? 'private' : 'public';
                refreshRepositoryVisibilityHelp();
                await loadRepositoryAdminData((doc.tags || []).map(tag => tag.id));
                repositoryUploadModal.show();
            } catch (error) {
                swalToast('error', error.message || 'No fue posible cargar el documento');
            }
        }

        async function loadStudentRepositoryProjects() {
            const select = document.getElementById('repoProjectId');
            if (!select) return;
            const response = await api.get('/my-projects', { compact: 1, per_page: 100, _fresh: 1 });
            const projects = response.data || [];
            select.innerHTML = projects.length
                ? '<option value="">Selecciona un proyecto</option>' + projects.map(project => `<option value="${Number(project.id)}">${escapeHtml(project.title || `Proyecto ${project.id}`)}</option>`).join('')
                : '<option value="">No tienes proyectos asignados</option>';
        }

        function refreshRepositoryVisibilityHelp() {
            const visibility = document.getElementById('repoVisibility')?.value || 'public';
            const help = document.getElementById('repoVisibilityHelp');
            if (!help) return;
            help.innerHTML = visibility === 'public'
                ? '<i class="bi bi-globe2 text-success"></i> El documento será visible para visitantes, estudiantes, docentes y administradores.'
                : '<i class="bi bi-lock text-warning"></i> El documento no aparecerá en el repositorio público; solo docentes y administradores podrán consultarlo.';
        }

        async function submitRepositoryDocument(event) {
            event.preventDefault();
            const documentId = document.getElementById('repoDocumentId').value;
            const file = document.getElementById('repoArchivo').files[0];
            if (!document.getElementById('repoNombre').value.trim() || !document.getElementById('repoDescripcion').value.trim() || !document.getElementById('repoAutores').value.trim()) {
                showAlert('#repositoryUploadAlert', 'danger', 'Ningun campo del documento puede quedar vacio.');
                return;
            }
            const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt', 'jpg', 'jpeg', 'png', 'webp', 'epub'];
            const extension = file?.name.split('.').pop().toLowerCase();
            if (!documentId && !file) {
                showAlert('#repositoryUploadAlert', 'danger', 'Selecciona un archivo para subir.');
                return;
            }
            if (file && !allowedExtensions.includes(extension)) {
                showAlert('#repositoryUploadAlert', 'danger', 'Selecciona un archivo permitido: PDF, Word, Excel, ZIP, TXT, JPG, PNG, WebP o EPUB.');
                return;
            }

            const formData = new FormData(event.target);
            formData.delete('document_id');
            if (!file) {
                formData.delete('archivo');
            }
            document.querySelectorAll('.repo-tag:checked').forEach(input => formData.append('tag_ids[]', input.value));

            const button = document.getElementById('repoUploadBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${documentId ? 'Guardando...' : 'Subiendo...'}`;
            try {
                await api.post(documentId ? `/repositorio/${documentId}` : '/repositorio', formData);
                repositoryUploadModal.hide();
                swalToast('success', documentId ? 'Documento actualizado' : 'Documento agregado al repositorio');
                await loadDocumentos(currentPage);
            } catch (error) {
                showAlert('#repositoryUploadAlert', 'danger', error.message || 'Error guardando el documento');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        async function deleteRepositoryDocument(documentId) {
            if (!CAN_MANAGE_REPOSITORY) return;
            const confirmed = window.Swal
                ? await stableSwalFire({
                    title: 'Eliminar documento',
                    text: 'Esta acción quitará el documento del repositorio.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545'
                }).then(result => result.isConfirmed)
                : window.confirm('Eliminar este documento del repositorio?');

            if (!confirmed) return;

            try {
                await api.delete(`/repositorio/${documentId}`);
                swalToast('success', 'Documento eliminado');
                await loadDocumentos(currentPage);
            } catch (error) {
                swalToast('error', error.message || 'No fue posible eliminar el documento');
            }
        }

        // Event listeners
        let repositorySearchTimer;
        document.getElementById('searchInput').addEventListener('input', (e) => {
            filters.buscar = e.target.value;
            clearTimeout(repositorySearchTimer);
            repositorySearchTimer = setTimeout(() => loadDocumentos(1), 280);
        });

        document.getElementById('sortFilter').addEventListener('change', (e) => {
            filters.ordenar = e.target.value;
            loadDocumentos(1);
        });

        document.getElementById('categoryFilter').addEventListener('change', (e) => {
            filters.categoria = e.target.value;
            loadDocumentos(1);
        });
        document.getElementById('repoVisibility')?.addEventListener('change', refreshRepositoryVisibilityHelp);

        function repositoryCategoryLabel(category) {
            return {
                repository: 'General',
                repositorio: 'General',
                evaluation_document: 'Desarrollo de proyecto',
                evaluacion: 'Desarrollo de proyecto',
                thesis_general: 'Tesis',
                thesis_residency: 'Residencias',
                tesis: 'Tesis'
            }[category] || 'General';
        }

        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            const search = new URLSearchParams(window.location.search).get('q');
            if (search) {
                filters.buscar = search;
                document.getElementById('searchInput').value = search;
            }
            loadDocumentos(1);
        });
    </script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
