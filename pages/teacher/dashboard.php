<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Verificar autenticación y rol
if (!is_authenticated() || !is_teacher()) {
    redirect_to('/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Docente - <?= APP_NAME ?></title>
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
            <!-- Hero -->
            <div style="background: url('/assets/img/ITSSMT/Sistema2.jpeg'); background-size: cover; background-position: center; padding: 80px 0; position: relative;">
                <div class="overlay"></div>
                <div class="container-xl" style="position: relative; z-index: 1;">
                    <!-- Logo y Título -->
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="/assets/img/ITSSMT/ITSSMT.png" alt="ITSSMT" style="height: 50px;">
                        <h1 class="display-4 fw-bold text-white mb-0">Panel del Docente</h1>
                    </div>
                    
                    <!-- Subtítulo -->
                    <p class="text-white opacity-90 mb-3" style="font-size: 1.1rem;">
                        <strong>Bienvenido, <?= htmlspecialchars($current_user['nombres']) ?></strong> | Gestión de cursos y evaluaciones
                    </p>
                    
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb" style="background: rgba(255,255,255,0.15); border-radius: 5px; padding: 8px 12px; margin: 0;">
                            <li class="breadcrumb-item"><a href="/index.php" class="text-white text-decoration-none">Inicio</a></li>
                            <li class="breadcrumb-item active text-white opacity-75">Panel Docente</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="container-xl mt-5 mb-5">
                <!-- Stats -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-folder2" style="font-size: 3rem; color: #1B396A;"></i>
                                <h6 class="text-muted mt-3 mb-1">Mis Proyectos</h6>
                                <h2 class="mb-0" id="myProjects" style="color: #1B396A; font-weight: 600;">0</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-people" style="font-size: 3rem; color: #2D5A96;"></i>
                                <h6 class="text-muted mt-3 mb-1">Estudiantes Asociados</h6>
                                <h2 class="mb-0" id="students" style="color: #2D5A96; font-weight: 600;">0</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark" style="font-size: 3rem; color: #1B396A;"></i>
                                <h6 class="text-muted mt-3 mb-1">Entregables Pendientes</h6>
                                <h2 class="mb-0" id="pendingDeliverables" style="color: #1B396A; font-weight: 600;">0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); color: white; border: 0;">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Mis Proyectos Recientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group" id="projectsList">
                                    <p class="text-muted">Cargando...</p>
                                </div>
                            </div>
                        </div>
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

    <script>
        async function loadDashboard() {
            try {
                const response = await api.get('/dashboard/teacher');
                
                document.getElementById('myProjects').textContent = response.stats.my_projects;
                document.getElementById('students').textContent = response.stats.students;
                document.getElementById('pendingDeliverables').textContent = response.stats.pending_deliverables;

                // Cargar proyectos
                const projectsList = document.getElementById('projectsList');
                projectsList.innerHTML = '';

                if (response.recent_projects && response.recent_projects.length > 0) {
                    response.recent_projects.forEach(project => {
                        const item = `
                            <a href="/pages/admin/projects.php?id=${project.id}" class="list-group-item list-group-item-action">
                                <h6 class="mb-1">${project.title}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> ${new Date(project.created_at).toLocaleDateString()}
                                </small>
                            </a>
                        `;
                        projectsList.innerHTML += item;
                    });
                } else {
                    projectsList.innerHTML = '<p class="text-muted">No hay proyectos aún</p>';
                }
            } catch (error) {
                console.error('Error al cargar dashboard:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>