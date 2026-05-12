<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Repositorio - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <!-- Hero -->
    <div class="hero-gradient">
        <div class="container-xl">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="background: rgba(255,255,255,0.1); border-radius: 5px; padding: 10px 15px;">
                    <li class="breadcrumb-item"><a href="/pages/repositorio.php" style="color: white;">Repositorio</a></li>
                    <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.7)">Detalles</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container-xl mt-5 pb-5">
        <div id="detailsContainer">
            <div class="text-center">
                <div class="spinner-custom"></div>
                <p class="mt-2">Cargando detalles...</p>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'http://127.0.0.1:8000/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>

    <script>
        async function loadDocumento() {
            const params = new URLSearchParams(window.location.search);
            const docId = params.get('id');

            if (!docId) {
                window.location.href = '/pages/repositorio.php';
                return;
            }

            try {
                const response = await api.get(`/repositorio/${docId}`);
                const doc = response.data;

                const html = `
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h2 class="mb-3">${doc.nombre}</h2>
                                    <p class="text-muted lead">${doc.descripcion || ''}</p>
                                    
                                    <div class="mb-4">
                                        <h6>Información</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Proyecto:</strong> ${doc.project?.title || 'N/A'}</li>
                                            <li><strong>Fecha:</strong> ${new Date(doc.created_at).toLocaleDateString()}</li>
                                            <li><strong>Creado por:</strong> ${doc.creator?.nombres || 'N/A'}</li>
                                        </ul>
                                    </div>

                                    <div class="mb-4">
                                        <h6>Etiquetas</h6>
                                        <div>
                                            ${doc.tags ? doc.tags.map(tag => 
                                                `<span class="badge" style="background-color: ${tag.color}">${tag.nombre}</span>`
                                            ).join('') : '<span class="text-muted">Sin etiquetas</span>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header" style="background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); color: white; border: 0;">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Detalles</h6>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-primary w-100 mb-2">
                                        <i class="bi bi-download"></i> Descargar
                                    </button>
                                    <a href="/pages/repositorio.php" class="btn btn-secondary w-100">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detailsContainer').innerHTML = html;
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('detailsContainer').innerHTML = '<p class="text-danger">Error al cargar el documento</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', loadDocumento);
    </script>
</body>
</html>