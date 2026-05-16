<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/validations.php';

if (!is_authenticated() || !is_teacher()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Proyectos - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .card-proyecto {
            transition: transform 0.2s;
        }
        .card-proyecto:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <h1 class="mb-4">Mis Proyectos</h1>

                <div id="alertContainer" class="mb-3"></div>

                <div class="row g-4" id="projectsContainer">
                    <div class="col-12 text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando proyectos...</p>
                    </div>
                </div>
            </div>
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
        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function projectActiveAuthors(project) {
            const students = Array.isArray(project?.students) ? project.students : [];
            return students.map(student => fullName(student)).filter(Boolean).join(', ');
        }

        async function loadProjects() {
            try {
                const response = await api.get('/my-projects');
                const container = document.getElementById('projectsContainer');
                container.innerHTML = '';

                if (!response.data || response.data.length === 0) {
                    container.innerHTML = '<div class="col-12"><p class="text-center text-muted">No tienes proyectos asignados aún</p></div>';
                    return;
                }

                response.data.forEach(project => {
                    // Archivo
                    let archivoHTML = '<span class="text-muted small">-</span>';
                    let btnDescargar = '';
                    if (project.file_path) {
                        archivoHTML = '<i class="bi bi-file-earmark text-primary"></i> Presente';
                        btnDescargar = `
                            <button class="btn btn-sm btn-outline-info w-100 mt-2" 
                                    onclick="descargarEntregable(${project.id}, '${project.title}.pdf')">
                                <i class="bi bi-download"></i> Descargar Proyecto
                            </button>
                        `;
                    }

                    const activeAuthors = projectActiveAuthors(project);
                    const card = `
                        <div class="col-lg-6 col-md-12">
                            <div class="card h-100 card-proyecto border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">${escapeHtml(project.title)}</h5>
                                        <span class="badge bg-primary">${project.year || '-'}</span>
                                    </div>
                                    
                                    <p class="card-text text-muted mb-3">${escapeHtml(project.descripcion || project.description || 'Sin descripcion')}</p>
                                    
                                    <div class="mb-3 pb-3 border-bottom">
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-people"></i> ${project.students_count || 0} estudiantes
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-calendar"></i> Creado: ${new Date(project.created_at).toLocaleDateString('es-MX')}
                                        </small>
                                    </div>

                                    ${activeAuthors ? `
                                        <div class="mb-3">
                                            <strong class="d-block mb-2 small">Autores:</strong>
                                            <small>${escapeHtml(activeAuthors)}</small>
                                        </div>
                                    ` : ''}

                                    <div>
                                        <strong class="d-block mb-2 small">Archivo:</strong>
                                        ${archivoHTML}
                                    </div>
                                </div>
                                
                                <div class="card-footer border-0 bg-light">
                                    ${btnDescargar}
                                    <a href="/pages/admin/projects.php?id=${project.id}" class="btn btn-sm btn-primary w-100" style="${btnDescargar ? 'margin-top: 0.5rem;' : ''}">
                                        <i class="bi bi-eye"></i> Ver Proyecto
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.innerHTML += card;
                });
            } catch (error) {
                console.error('Error:', error);
                showAlert('#alertContainer', 'danger', 'Error cargando proyectos: ' + error.message);
            }
        }

        document.addEventListener('DOMContentLoaded', loadProjects);
    </script>
</body>
</html>
