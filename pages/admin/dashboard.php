<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Verificar autenticación y rol
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
    <title>Dashboard Admin - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <!-- Page Header -->
    <div style="background: url('/assets/img/ITSSMT/Sistema2.jpeg'); background-size: cover; background-position: center; padding: 80px 0; position: relative;">
        <div class="overlay"></div>
        <div class="container-xl" style="position: relative; z-index: 1;">
            <!-- Logo y Título -->
            <div class="d-flex align-items-center gap-3 mb-2">
                <img src="/assets/img/ITSSMT/ITSSMT.png" alt="ITSSMT" style="height: 50px;">
                <h1 class="display-4 fw-bold text-white mb-0">Panel de Administrador</h1>
            </div>
            
            <!-- Subtítulo -->
            <p class="text-white opacity-90 mb-3" style="font-size: 1.1rem;">
                <strong>Bienvenido, <?= htmlspecialchars($current_user['nombres']) ?></strong> | Gestión integral del sistema
            </p>
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="background: rgba(255,255,255,0.15); border-radius: 5px; padding: 8px 12px; margin: 0;">
                    <li class="breadcrumb-item"><a href="/index.php" class="text-white text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active text-white opacity-75">Panel Administrativo</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="container-xl mt-5">
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <a href="/pages/admin/users.php" class="text-decoration-none d-block" aria-label="Ir a gestion de usuarios">
                    <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="dashboard-stat-label">Total de usuarios</div>
                                    <div class="dashboard-stat-value mt-2" id="totalUsers">0</div>
                                    <div class="dashboard-stat-note mt-2"><span id="inactiveUsers">0</span> inactivos</div>
                                </div>
                                <span class="dashboard-stat-icon"><i class="bi bi-people"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="/pages/admin/users.php" class="text-decoration-none d-block" aria-label="Ir a usuarios activos">
                    <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="w-100">
                                    <div class="dashboard-stat-label">Usuarios activos</div>
                                    <div class="dashboard-stat-value mt-2" id="activeUsers">0</div>
                                    <div class="dashboard-progress-track mt-3"><div class="dashboard-progress-fill" id="activeUsersProgress" style="width: 0%;"></div></div>
                                </div>
                                <span class="dashboard-stat-icon" style="color: #218838;"><i class="bi bi-check-circle"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="/pages/admin/projects.php" class="text-decoration-none d-block" aria-label="Ir a gestion de proyectos">
                    <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="dashboard-stat-label">Proyectos</div>
                                    <div class="dashboard-stat-value mt-2" id="totalProjects">0</div>
                                    <div class="dashboard-stat-note mt-2"><span id="pendingProposals">0</span> propuestas pendientes</div>
                                </div>
                                <span class="dashboard-stat-icon"><i class="bi bi-folder2-open"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="/pages/admin/asignaturas.php" class="text-decoration-none d-block" aria-label="Ir a gestion de asignaturas">
                    <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="dashboard-stat-label">Asignaturas</div>
                                    <div class="dashboard-stat-value mt-2" id="totalAsignaturas">0</div>
                                    <div class="dashboard-stat-note mt-2">Catálogo académico</div>
                                </div>
                                <span class="dashboard-stat-icon"><i class="bi bi-mortarboard"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="dashboard-insight-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-activity"></i> Avance global</h5>
                        <span class="badge bg-primary" id="globalCompletionBadge">0%</span>
                    </div>
                    <div class="dashboard-progress-track mb-3"><div class="dashboard-progress-fill" id="globalCompletionProgress" style="width: 0%;"></div></div>
                    <p class="text-muted mb-0">Porcentaje de entregables aprobados sobre el total activo.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="dashboard-insight-card p-4">
                    <h5 class="mb-3"><i class="bi bi-person-badge"></i> Usuarios por rol</h5>
                    <div id="usersRoleChart"><p class="dashboard-empty"><i class="bi bi-hourglass-split"></i> Cargando...</p></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="dashboard-insight-card p-4">
                    <h5 class="mb-3"><i class="bi bi-file-earmark-check"></i> Entregables</h5>
                    <div class="dashboard-status-grid" id="deliverableStatusGrid"></div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="dashboard-insight-card p-4">
                    <h5 class="mb-3"><i class="bi bi-kanban"></i> Estado de propuestas</h5>
                    <div id="proposalStatusChart"><p class="dashboard-empty"><i class="bi bi-hourglass-split"></i> Cargando...</p></div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <!-- Quick Actions -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); color: white; border: 0;">
                        <h5 class="mb-0" style="color: white;"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="/pages/admin/users.php" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center gap-3">
                                <i class="bi bi-people-fill" style="font-size: 1.5rem; color: #2D5A96;"></i>
                                <div>
                                    <h6 class="mb-0">Gestionar Usuarios</h6>
                                    <small class="text-muted">Ver, editar y crear usuarios</small>
                                </div>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>
                            
                            <a href="/pages/admin/projects.php" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center gap-3">
                                <i class="bi bi-diagram-3" style="font-size: 1.5rem; color: #1B396A;"></i>
                                <div>
                                    <h6 class="mb-0">Proyectos</h6>
                                    <small class="text-muted">Gestionar proyectos</small>
                                </div>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>

                            <a href="/pages/admin/deliverables.php" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center gap-3">
                                <i class="bi bi-file-earmark" style="font-size: 1.5rem; color: #1B396A;"></i>
                                <div>
                                    <h6 class="mb-0">Entregables</h6>
                                    <small class="text-muted">Gestionar entregables</small>
                                </div>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>

                            <a href="/pages/admin/document-tags.php" class="list-group-item list-group-item-action px-4 py-3 d-flex align-items-center gap-3">
                                <i class="bi bi-tags" style="font-size: 1.5rem; color: #1B396A;"></i>
                                <div>
                                    <h6 class="mb-0">Etiquetas</h6>
                                    <small class="text-muted">Gestionar etiquetas de documentos</small>
                                </div>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Projects -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); color: white; border: 0;">
                        <h5 class="mb-0" style="color: white;"><i class="bi bi-clock-history"></i> Proyectos Recientes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3" id="recentProjectsList">
                            <p class="dashboard-empty"><i class="bi bi-hourglass-split"></i> Cargando...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const API_BASE_URL = '<?= API_BASE_URL ?>';
    </script>
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
            const container = document.getElementById(containerId);
            container.innerHTML = Object.entries(data || {}).map(([status, value]) => `
                <div class="dashboard-status-pill">
                    <strong>${value || 0}</strong>
                    <span>${escapeHtml(statusLabel(status))}</span>
                </div>
            `).join('');
        }

        async function loadDashboard() {
            try {
                const response = await api.get('/dashboard/stats');
                
                document.getElementById('totalUsers').textContent = response.stats.total_users;
                document.getElementById('activeUsers').textContent = response.stats.active_users;
                document.getElementById('inactiveUsers').textContent = response.stats.inactive_users || 0;
                document.getElementById('totalProjects').textContent = response.stats.total_projects;
                document.getElementById('totalAsignaturas').textContent = response.stats.total_asignaturas;
                document.getElementById('pendingProposals').textContent = response.stats.pending_proposals || 0;
                const activeRate = percent(response.stats.active_users, response.stats.total_users);
                document.getElementById('activeUsersProgress').style.width = `${activeRate}%`;
                const completionRate = response.stats.deliverable_completion_rate || 0;
                document.getElementById('globalCompletionBadge').textContent = `${completionRate}%`;
                document.getElementById('globalCompletionProgress').style.width = `${completionRate}%`;
                renderBarChart('usersRoleChart', response.charts?.users_by_role || {});
                renderBarChart('proposalStatusChart', response.charts?.projects_by_proposal_status || {});
                renderStatusGrid('deliverableStatusGrid', response.charts?.deliverables_by_status || {});

                // Cargar proyectos recientes
                const projectsList = document.getElementById('recentProjectsList');
                projectsList.innerHTML = '';

                if (response.recent_projects && response.recent_projects.length > 0) {
                    response.recent_projects.forEach(project => {
                        const item = `
                            <a href="/pages/admin/projects.php?edit=${project.id}" class="dashboard-project-card">
                                <div class="dashboard-project-title">${escapeHtml(project.title)}</div>
                                <div class="small text-muted">
                                    <i class="bi bi-person"></i> ${escapeHtml(project.creator?.nombres || 'N/A')} | 
                                    ${new Date(project.created_at).toLocaleDateString()}
                                </div>
                            </a>
                        `;
                        projectsList.innerHTML += item;
                    });
                } else {
                    projectsList.innerHTML = '<p class="dashboard-empty"><i class="bi bi-inbox"></i> No hay proyectos recientes.</p>';
                }
            } catch (error) {
                console.error('Error al cargar dashboard:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>
