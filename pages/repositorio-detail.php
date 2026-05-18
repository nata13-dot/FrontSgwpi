<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Repositorio - <?= APP_NAME ?></title>
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
    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.8.0/mammoth.browser.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>

    <script>
        function esc(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        async function loadDocumento() {
            const params = new URLSearchParams(window.location.search);
            const docId = params.get('id');

            if (!docId) {
                window.location.href = '/pages/repositorio.php';
                return;
            }

            try {
                const doc = await api.get(`/repositorio/${docId}`);
                const downloadUrl = `${API_BASE_URL}/repositorio/${encodeURIComponent(doc.id)}/download`;
                const viewUrl = `${API_BASE_URL}/repositorio/${encodeURIComponent(doc.id)}/view`;
                const fileType = String(doc.archivo_tipo || '').toLowerCase();

                const html = `
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h1 class="mb-1">${esc(doc.nombre)}</h1>
                            <p class="text-muted mb-0">${esc(doc.descripcion || '')}</p>
                        </div>
                        <a href="${downloadUrl}" class="btn btn-primary">
                            <i class="bi bi-download"></i> Descargar documento
                        </a>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4 repository-reader-card">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <h6 class="mb-0"><i class="bi bi-book"></i> Vista previa</h6>
                                    <span class="badge bg-light text-primary">${esc(fileType.toUpperCase() || 'DOCUMENTO')}</span>
                                </div>
                                <div class="card-body">
                                    <div id="documentReader" class="repository-reader">
                                        <div class="text-center py-5">
                                            <div class="spinner-custom"></div>
                                            <p class="mt-2 text-muted">Preparando vista previa...</p>
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
                                    <ul class="list-unstyled small mb-4">
                                        <li class="mb-2"><strong>Autores:</strong><br>${esc(doc.autores || 'No especificados')}</li>
                                        <li class="mb-2"><strong>Tipo:</strong><br>${esc((doc.archivo_tipo || 'documento').toUpperCase())}</li>
                                        <li class="mb-2"><strong>Fecha:</strong><br>${new Date(doc.created_at).toLocaleDateString()}</li>
                                        <li class="mb-2"><strong>Subido por:</strong><br>${esc(doc.uploader?.nombres || 'N/A')}</li>
                                    </ul>
                                    <h6>Etiquetas</h6>
                                    <div class="mb-4">
                                        ${doc.tags && doc.tags.length ? doc.tags.map(tag =>
                                            `<span class="badge me-1 mb-1" style="background-color: ${esc(tag.color)}">${esc(tag.nombre)}</span>`
                                        ).join('') : '<span class="text-muted">Sin etiquetas</span>'}
                                    </div>
                                    <a href="/pages/repositorio.php" class="btn btn-secondary w-100">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detailsContainer').innerHTML = html;
                renderDocumentPreview(fileType, viewUrl, downloadUrl);
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('detailsContainer').innerHTML = '<p class="text-danger">Error al cargar el documento</p>';
            }
        }

        async function renderDocumentPreview(fileType, viewUrl, downloadUrl) {
            const reader = document.getElementById('documentReader');
            if (!reader) return;

            if (fileType === 'pdf') {
                reader.innerHTML = `<iframe src="${viewUrl}" class="repository-reader-frame" title="Vista previa PDF"></iframe>`;
                return;
            }

            if (['jpg', 'jpeg', 'png'].includes(fileType)) {
                reader.innerHTML = `<img src="${viewUrl}" class="img-fluid rounded" alt="Vista previa de imagen">`;
                return;
            }

            if (fileType === 'txt') {
                try {
                    const response = await fetch(viewUrl);
                    const text = await response.text();
                    reader.innerHTML = `<pre class="repository-text-preview">${esc(text)}</pre>`;
                } catch (error) {
                    reader.innerHTML = previewFallback(downloadUrl, 'No fue posible leer este archivo de texto en el navegador.');
                }
                return;
            }

            if (fileType === 'docx') {
                try {
                    const response = await fetch(viewUrl);
                    const buffer = await response.arrayBuffer();
                    const result = await mammoth.convertToHtml({ arrayBuffer: buffer });
                    reader.innerHTML = `<article class="repository-word-preview">${result.value || '<p class="text-muted">El documento no contiene texto visible.</p>'}</article>`;
                } catch (error) {
                    reader.innerHTML = previewFallback(downloadUrl, 'No fue posible leer este archivo Word en el navegador.');
                }
                return;
            }

            if (fileType === 'epub') {
                reader.innerHTML = previewFallback(downloadUrl, 'La vista previa EPUB no está disponible en el navegador.');
                return;
            }

            reader.innerHTML = previewFallback(downloadUrl, 'La vista previa para este tipo de archivo no está disponible en el navegador.');
        }

        function previewFallback(downloadUrl, message) {
            return `
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted">${esc(message)}</p>
                    <a href="${downloadUrl}" class="btn btn-primary"><i class="bi bi-download"></i> Descargar para leer</a>
                </div>
            `;
        }

        document.addEventListener('DOMContentLoaded', loadDocumento);
    </script>
</body>
</html>
