<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Verificar autenticación y rol
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
                <div class="row g-4 mb-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card dashboard-stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="dashboard-stat-label">Mis proyectos</div>
                                        <div class="dashboard-stat-value mt-2" id="myProjects">0</div>
                                        <div class="dashboard-stat-note mt-2"><span id="pendingProposals">0</span> propuestas pendientes</div>
                                    </div>
                                    <span class="dashboard-stat-icon"><i class="bi bi-folder2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card dashboard-stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="dashboard-stat-label">Estudiantes asociados</div>
                                        <div class="dashboard-stat-value mt-2" id="students">0</div>
                                        <div class="dashboard-stat-note mt-2">En proyectos y grupos activos</div>
                                    </div>
                                    <span class="dashboard-stat-icon"><i class="bi bi-people"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card dashboard-stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="w-100">
                                        <div class="dashboard-stat-label">Entregables pendientes</div>
                                        <div class="dashboard-stat-value mt-2" id="pendingDeliverables">0</div>
                                        <div class="dashboard-progress-track mt-3"><div class="dashboard-progress-fill" id="completionProgress" style="width: 0%;"></div></div>
                                    </div>
                                    <span class="dashboard-stat-icon"><i class="bi bi-file-earmark"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-lg-4">
                        <div class="dashboard-insight-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="bi bi-activity"></i> Avance de revisión</h5>
                                <span class="badge bg-primary" id="completionBadge">0%</span>
                            </div>
                            <div class="dashboard-progress-track mb-3"><div class="dashboard-progress-fill" id="completionProgressLarge" style="width: 0%;"></div></div>
                            <p class="text-muted mb-0">Entregables aprobados dentro de tus proyectos activos.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="dashboard-insight-card p-4">
                            <h5 class="mb-3"><i class="bi bi-file-earmark-check"></i> Entregables</h5>
                            <div class="dashboard-status-grid" id="deliverableStatusGrid"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="dashboard-insight-card p-4">
                            <h5 class="mb-3"><i class="bi bi-kanban"></i> Propuestas</h5>
                            <div id="proposalStatusChart"><p class="dashboard-empty"><i class="bi bi-hourglass-split"></i> Cargando...</p></div>
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
                            <div class="card-body" id="projectsList">
                                <p class="dashboard-empty"><i class="bi bi-hourglass-split"></i> Cargando...</p>
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
        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function percent(value, total) {
            return total > 0 ? Math.round((Number(value || 0) / total) * 100) : 0;
        }

        function statusLabel(status) {
            const labels = {
                pendiente: 'Pendiente',
                enviado: 'Enviado',
                revisado: 'Revisado',
                aprobado: 'Aprobado',
                requiere_cambios: 'Requiere cambios',
                rechazado: 'Rechazado'
            };
            return labels[status] || status;
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function renderBarChart(containerId, data) {
            const container = document.getElementById(containerId);
            const entries = Object.entries(data || {});
            const total = entries.reduce((sum, [, value]) => sum + Number(value || 0), 0);
            if (!entries.length || total === 0) {
                container.innerHTML = '<p class="dashboard-empty"><i class="bi bi-inbox"></i> Sin datos para mostrar.</p>';
                return;
            }
            container.innerHTML = entries.map(([label, value]) => {
                const width = percent(value, total);
                return `
                    <div class="dashboard-chart-row">
                        <div class="dashboard-chart-label">${escapeHtml(statusLabel(label))}</div>
                        <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: ${width}%;"></div></div>
                        <div class="dashboard-chart-value">${value}</div>
                    </div>
                `;
            }).join('');
        }

        function renderStatusGrid(containerId, data) {
            document.getElementById(containerId).innerHTML = Object.entries(data || {}).map(([status, value]) => `
                <div class="dashboard-status-pill">
                    <strong>${value || 0}</strong>
                    <span>${escapeHtml(statusLabel(status))}</span>
                </div>
            `).join('');
        }

        function projectProgress(project) {
            return percent(project.approved_deliverables_count || 0, project.deliverables_count || 0);
        }

        async function loadDashboard() {
            try {
                const response = await api.get('/dashboard/teacher');
                
                document.getElementById('myProjects').textContent = response.stats.my_projects;
                document.getElementById('students').textContent = response.stats.students;
                document.getElementById('pendingDeliverables').textContent = response.stats.pending_deliverables;
                document.getElementById('pendingProposals').textContent = response.stats.pending_proposals || 0;
                const completionRate = response.stats.deliverable_completion_rate || 0;
                document.getElementById('completionBadge').textContent = `${completionRate}%`;
                document.getElementById('completionProgress').style.width = `${completionRate}%`;
                document.getElementById('completionProgressLarge').style.width = `${completionRate}%`;
                renderStatusGrid('deliverableStatusGrid', response.charts?.deliverables_by_status || {});
                renderBarChart('proposalStatusChart', response.charts?.projects_by_proposal_status || {});

                // Cargar proyectos
                const projectsList = document.getElementById('projectsList');
                projectsList.innerHTML = '';

                if (response.recent_projects && response.recent_projects.length > 0) {
                    response.recent_projects.forEach(project => {
                        const progress = projectProgress(project);
                        const group = project.subject_group ? `${project.subject_group.semestre || ''}${project.subject_group.grupo ? ' ' + project.subject_group.grupo : ''}` : 'Grupo pendiente';
                        const students = Array.isArray(project.students) ? project.students.map(fullName).filter(Boolean).join(', ') : '';
                        const item = `
                            <a href="/pages/teacher/my-projects.php" class="dashboard-project-card">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <div class="dashboard-project-title mb-0">${escapeHtml(project.title)}</div>
                                    <span class="badge bg-primary">${progress}%</span>
                                </div>
                                <div class="dashboard-progress-track mb-2"><div class="dashboard-progress-fill" style="width: ${progress}%;"></div></div>
                                <div class="small text-muted">
                                    <i class="bi bi-people"></i> ${escapeHtml(students || 'Sin estudiantes asignados')} ·
                                    <i class="bi bi-collection"></i> ${escapeHtml(group)} ·
                                    <i class="bi bi-calendar"></i> ${new Date(project.created_at).toLocaleDateString('es-MX')}
                                </div>
                            </a>
                        `;
                        projectsList.innerHTML += item;
                    });
                } else {
                    projectsList.innerHTML = '<p class="dashboard-empty"><i class="bi bi-inbox"></i> No hay proyectos aún.</p>';
                }
            } catch (error) {
                console.error('Error al cargar dashboard:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>
