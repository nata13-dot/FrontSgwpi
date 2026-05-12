<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio - <?= APP_NAME ?></title>
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
        <!-- Filters -->
        <div class="row mb-4 mt-4">
            <div class="col-md-6">
                <input 
                    type="text" 
                    class="form-control form-control-lg"
                    id="searchInput"
                    placeholder="Buscar documentos..."
                >
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-lg" id="projectFilter">
                    <option value="">Todos los proyectos</option>
                </select>
            </div>
            <div class="col-md-3">
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

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const API_BASE_URL = 'http://127.0.0.1:8000/api';
        let currentPage = 1;
        let filters = {
            buscar: '',
            proyecto_id: '',
            ordenar: 'reciente'
        };

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
                                    <h6 class="card-title">${doc.nombre}</h6>
                                    <p class="card-text text-muted small">${doc.descripcion || ''}</p>
                                    <div class="mb-3">
                                        ${doc.tags ? doc.tags.map(tag => 
                                            `<span class="badge" style="background-color: ${tag.color}">${tag.nombre}</span>`
                                        ).join('') : ''}
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-folder"></i> ${doc.project?.title || 'N/A'}
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

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', (e) => {
            filters.buscar = e.target.value;
            loadDocumentos(1);
        });

        document.getElementById('projectFilter').addEventListener('change', (e) => {
            filters.proyecto_id = e.target.value;
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
</body>
</html>