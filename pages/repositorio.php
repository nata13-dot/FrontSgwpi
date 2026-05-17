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
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <!-- Hero -->
    <div class="hero-gradient">
        <div class="container-xl">
            <h1 class="display-4 fw-bold mb-3">Repositorio Digital</h1>
            <p class="lead">Explora nuestro repositorio de proyectos y documentos</p>
        </div>
    </div>

    <div class="container-xl pb-5">
        <?php if (is_authenticated() && is_admin()): ?>
            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-primary" onclick="openRepositoryUploadModal()">
                    <i class="bi bi-cloud-arrow-up"></i> Agregar documento
                </button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="row mb-4 mt-4">
            <div class="col-md-8">
                <input 
                    type="text" 
                    class="form-control form-control-lg"
                    id="searchInput"
                    placeholder="Buscar documentos..."
                >
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-lg" id="sortFilter">
                    <option value="reciente">Más recientes</option>
                    <option value="antiguo">Más antiguos</option>
                    <option value="nombre_asc">Nombre A-Z</option>
                    <option value="nombre_desc">Nombre Z-A</option>
                </select>
            </div>
        </div>

        <!-- Documentos -->
        <div class="row g-4" id="documentosContainer">
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
    </div>

    <?php if (is_authenticated() && is_admin()): ?>
    <div class="modal fade" id="repositoryUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="repositoryUploadForm" onsubmit="submitRepositoryDocument(event)">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-cloud-arrow-up"></i> Agregar documento al repositorio</h5>
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
                            <div class="col-md-6">
                                <label class="form-label" for="repoArchivo">Archivo</label>
                                <input type="file" class="form-control" id="repoArchivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,.jpg,.jpeg,.png,.epub" required>
                                <div class="form-text">Permitidos: PDF, Word, Excel, ZIP, TXT, imagenes JPG/PNG y EPUB.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Etiquetas</label>
                                <div id="repoTags" class="d-flex flex-wrap gap-2"></div>
                            </div>
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
        let currentPage = 1;
        let filters = {
            buscar: '',
            ordenar: 'reciente'
        };
        let repositoryUploadModal;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        async function loadDocumentos(page = 1) {
            try {
                const response = await api.get('/repositorio', {
                    ...filters,
                    page: page
                });

                const container = document.getElementById('documentosContainer');
                container.innerHTML = '';

                if (!response.data || response.data.length === 0) {
                    container.innerHTML = '<div class="col-12"><p class="text-center text-muted">No se encontraron documentos</p></div>';
                    return;
                }

                response.data.forEach(doc => {
                    const card = `
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">${escapeHtml(doc.nombre)}</h6>
                                    <p class="card-text text-muted small">${escapeHtml(doc.descripcion || '')}</p>
                                    <div class="mb-3">
                                        ${doc.tags ? doc.tags.map(tag => 
                                            `<span class="badge" style="background-color: ${escapeHtml(tag.color)}">${escapeHtml(tag.nombre)}</span>`
                                        ).join('') : ''}
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-people"></i> ${escapeHtml(doc.autores || 'Autores no especificados')}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar"></i> ${new Date(doc.created_at).toLocaleDateString()}
                                    </small>
                                </div>
                                <div class="card-footer border-0">
                                    <a href="/pages/repositorio-detail.php?id=${doc.id}" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-eye"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.innerHTML += card;
                });

                currentPage = page;
            } catch (error) {
                console.error('Error al cargar documentos:', error);
            }
        }

        async function loadRepositoryAdminData() {
            if (!document.getElementById('repositoryUploadModal')) return;
            const tagsResponse = await api.get('/document-tags', { per_page: 100 });

            const tagsBox = document.getElementById('repoTags');
            const tags = tagsResponse.data || [];
            tagsBox.innerHTML = tags.length
                ? tags.map(tag => `
                    <label class="form-check form-check-inline border rounded px-2 py-1">
                        <input class="form-check-input repo-tag" type="checkbox" value="${escapeHtml(tag.id)}">
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
            document.querySelectorAll('.repo-tag').forEach(input => input.checked = false);
            await loadRepositoryAdminData();
            repositoryUploadModal.show();
        }

        async function submitRepositoryDocument(event) {
            event.preventDefault();
            const file = document.getElementById('repoArchivo').files[0];
            if (!document.getElementById('repoNombre').value.trim() || !document.getElementById('repoDescripcion').value.trim() || !document.getElementById('repoAutores').value.trim()) {
                showAlert('#repositoryUploadAlert', 'danger', 'Ningun campo del documento puede quedar vacio.');
                return;
            }
            const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt', 'jpg', 'jpeg', 'png', 'epub'];
            const extension = file?.name.split('.').pop().toLowerCase();
            if (!file || !allowedExtensions.includes(extension)) {
                showAlert('#repositoryUploadAlert', 'danger', 'Selecciona un archivo permitido: PDF, Word, Excel, ZIP, TXT, JPG, PNG o EPUB.');
                return;
            }

            const formData = new FormData(event.target);
            document.querySelectorAll('.repo-tag:checked').forEach(input => formData.append('tag_ids[]', input.value));

            const button = document.getElementById('repoUploadBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Subiendo...';
            try {
                await api.post('/repositorio', formData);
                repositoryUploadModal.hide();
                swalToast('success', 'Documento agregado al repositorio');
                await loadDocumentos(1);
            } catch (error) {
                showAlert('#repositoryUploadAlert', 'danger', error.message || 'Error subiendo el documento');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', (e) => {
            filters.buscar = e.target.value;
            loadDocumentos(1);
        });

        document.getElementById('sortFilter').addEventListener('change', (e) => {
            filters.ordenar = e.target.value;
            loadDocumentos(1);
        });

        // Cargar al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            loadDocumentos(1);
        });
    </script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
