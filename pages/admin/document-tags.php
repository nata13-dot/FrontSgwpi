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
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h1>Gestión de Etiquetas de Documentos</h1>
                    <button class="btn btn-primary" onclick="openNewTagModal()">
                        <i class="bi bi-plus-circle"></i> Nueva Etiqueta
                    </button>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
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
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'https://apiswgpi-production-0e59.up.railway.app/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>

    <script>
        async function loadTags(page = 1) {
            try {
                const response = await api.get('/document-tags', { page });
                const tbody = document.getElementById('tagsTable');
                tbody.innerHTML = '';

                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay etiquetas</td></tr>';
                    return;
                }

                response.data.forEach(tag => {
                    const row = `
                        <tr>
                            <td><strong>${tag.nombre}</strong></td>
                            <td><span class="badge" style="background-color: ${tag.color}">${tag.color}</span></td>
                            <td>${tag.descripcion || '-'}</td>
                            <td>${tag.documents_count || 0}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTag('${tag.id}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteTag(tagId) {
            const confirmed = await confirmAction({
                title: 'Eliminar etiqueta',
                text: '¿Estas seguro de eliminar esta etiqueta?',
                confirmButtonText: 'Si, eliminar'
            });
            if (!confirmed) return;

            api.delete(`/document-tags/${tagId}`).then(() => {
                loadTags();
                swalToast('success', 'Etiqueta eliminada');
            });
        }

        function openNewTagModal() {
            swalToast('info', 'Funcionalidad en desarrollo');
        }
        document.addEventListener('DOMContentLoaded', () => loadTags());
    </script>
</body>
</html>