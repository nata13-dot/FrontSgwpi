<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Verificar autenticación y rol
if (!is_authenticated() || !is_teacher()) {
    header('Location: /index.php');
    exit;
}
$career = active_career() ?? [];
$careerCode = $career['clave'] ?? 'ISC';
// Misma portada de la carrera que el panel administrativo (pages/admin/dashboard.php).
$careerHeroImage = $career['portada_ruta'] ?? ($careerCode === 'ISC' ? '/assets/img/ITSSMT/fondochido2.webp' : null);
$careerHeroStyle = $careerHeroImage ? "background-image: url('" . htmlspecialchars($careerHeroImage) . "');" : '';
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
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <!-- Portada de la carrera: igual que el panel administrativo. -->
            <div class="page-image-header dashboard-hero career-dashboard-hero career-<?= htmlspecialchars(strtolower($careerCode)) ?>" style="<?= $careerHeroStyle ?>" data-career="<?= htmlspecialchars($careerCode) ?>">
                <div class="overlay"></div>
                <div class="container-xl" style="position: relative; z-index: 1;">
                    <h1 class="display-4 fw-bold text-white mb-3">Panel del Docente</h1>
                    <p class="text-white opacity-90 mb-0 dashboard-hero-subtitle">
                        <strong>Bienvenido, <?= htmlspecialchars($current_user['nombres']) ?></strong> | Asesorías, entregables y evaluaciones
                    </p>
                    <span class="dashboard-hero-line"></span>
                    <p class="dashboard-career-name"><?= htmlspecialchars($career['nombre'] ?? 'Ingeniería en Sistemas Computacionales') ?></p>
                    <p class="dashboard-career-motto"><?= htmlspecialchars($career['lema'] ?? 'Tecnología, innovación y transformación digital.') ?></p>
                    <p class="dashboard-hero-tag">#OrgulloHalcón</p>
                </div>
            </div>

            <div class="container-xl mt-5 mb-5">
                <section class="dashboard-mobile-shortcuts" aria-labelledby="mobileShortcutsTitle">
                    <div class="dashboard-mobile-shortcuts-heading">
                        <div>
                            <span>Acceso directo</span>
                            <h2 id="mobileShortcutsTitle">Mis accesos</h2>
                        </div>
                        <i class="bi bi-grid"></i>
                    </div>
                    <div class="dashboard-mobile-shortcuts-grid">
                    <a href="/pages/teacher/my-projects.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-folder2"></i>
                        <span>Mis proyectos</span>
                    </a>
                    <a href="/pages/teacher/proposal-review.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-check2-square"></i>
                        <span>Propuestas</span>
                    </a>
                    <a href="/pages/teacher/my-deliverables.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-file-earmark-check"></i>
                        <span>Entregables</span>
                    </a>
                    <a href="/pages/admin/evaluations.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Evaluaciones</span>
                    </a>
                    <a href="/pages/admin/evaluation-rooms.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-door-open"></i>
                        <span>Salas y turnos</span>
                    </a>
                    <a href="/pages/evaluation-documents.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-file-earmark-ppt"></i>
                        <span>Evidencias</span>
                    </a>
                    <a href="/pages/repositorio.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-archive"></i>
                        <span>Repositorio</span>
                    </a>
                    <a href="/pages/academic-workflows.php" class="dashboard-mobile-shortcut">
                        <i class="bi bi-calendar3"></i>
                        <span>Seguimiento</span>
                    </a>
                    </div>
                </section>

                <section class="dashboard-action-panel" aria-live="polite">
                    <span class="dashboard-action-icon" aria-hidden="true"><i class="bi bi-person-workspace"></i><b>!</b></span>
                    <div>
                        <div class="dashboard-action-kicker">Siguiente paso</div>
                        <div class="dashboard-action-title" id="teacherNextActionTitle">Revisa tu carga de trabajo</div>
                        <p class="dashboard-action-text" id="teacherNextActionText">Cuando carguen los datos te mostraremos la acción que requiere más atención.</p>
                    </div>
                    <a href="/pages/teacher/my-deliverables.php" class="dashboard-action-link" id="teacherNextActionLink">
                        <i class="bi bi-arrow-right-circle"></i>
                        <span>Ver entregables</span>
                    </a>
                </section>

                <!-- Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-4 col-md-6">
                        <a href="/pages/teacher/my-projects.php" class="text-decoration-none d-block dashboard-stat-link" aria-label="Ir a mis proyectos">
                            <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                                <div class="card-body">
                                    <div class="dashboard-stat-layout">
                                        <span class="dashboard-stat-icon dashboard-stat-icon-users"><i class="bi bi-folder2"></i></span>
                                        <div>
                                            <div class="dashboard-stat-label">Mis proyectos</div>
                                            <div class="dashboard-stat-value mt-2" id="myProjects">0</div>
                                            <div class="dashboard-stat-note mt-2"><span id="pendingProposals">0</span> propuestas pendientes</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <a href="/pages/teacher/my-projects.php" class="text-decoration-none d-block dashboard-stat-link" aria-label="Ir a estudiantes asociados por proyecto">
                            <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                                <div class="card-body">
                                    <div class="dashboard-stat-layout">
                                        <span class="dashboard-stat-icon dashboard-stat-icon-active"><i class="bi bi-people"></i></span>
                                        <div>
                                            <div class="dashboard-stat-label">Estudiantes asociados</div>
                                            <div class="dashboard-stat-value mt-2" id="students">0</div>
                                            <div class="dashboard-stat-note mt-2">En proyectos y grupos activos</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <a href="/pages/teacher/my-deliverables.php" class="text-decoration-none d-block dashboard-stat-link" aria-label="Ir a entregables pendientes">
                            <div class="card dashboard-stat-card border-0 shadow-sm" style="cursor: pointer;">
                                <div class="card-body">
                                    <div class="dashboard-stat-layout">
                                        <span class="dashboard-stat-icon dashboard-stat-icon-projects"><i class="bi bi-file-earmark"></i></span>
                                        <div class="w-100">
                                            <div class="dashboard-stat-label">Entregables pendientes</div>
                                            <div class="dashboard-stat-value mt-2" id="pendingDeliverables">0</div>
                                            <div class="dashboard-progress-track mt-3"><div class="dashboard-progress-fill" id="completionProgress" style="width: 0%;"></div></div>
                                        </div>
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
                            <div class="card-header">
                                <div class="dashboard-card-header-actions">
                                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Mis Proyectos Recientes</h5>
                                    <a href="/pages/teacher/my-projects.php">Ver todos <i class="bi bi-arrow-right"></i></a>
                                </div>
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
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="<?= asset('/assets/js/auth.js') ?>"></script>
    <script src="<?= asset('/assets/js/api.js') ?>"></script>

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
            const container = document.getElementById(containerId);
            const entries = Object.entries(data || {});
            if (!entries.length) {
                container.innerHTML = '<p class="dashboard-empty"><i class="bi bi-inbox"></i> Sin datos para mostrar.</p>';
                return;
            }
            container.innerHTML = entries.map(([status, value]) => `
                <div class="dashboard-status-pill">
                    <strong>${value || 0}</strong>
                    <span>${escapeHtml(statusLabel(status))}</span>
                </div>
            `).join('');
        }

        function projectProgress(project) {
            return percent(project.approved_deliverables_count || 0, project.deliverables_count || 0);
        }

        function updateTeacherNextAction(stats) {
            const title = document.getElementById('teacherNextActionTitle');
            const text = document.getElementById('teacherNextActionText');
            const link = document.getElementById('teacherNextActionLink');
            if ((stats.pending_proposals || 0) > 0) {
                title.textContent = `${stats.pending_proposals} propuestas por revisar`;
                text.textContent = 'Empieza por aprobar o solicitar cambios en las propuestas pendientes.';
                link.href = '/pages/teacher/proposal-review.php';
                link.innerHTML = '<i class="bi bi-check2-square"></i><span>Revisar propuestas</span>';
                return;
            }
            if ((stats.pending_deliverables || 0) > 0) {
                title.textContent = `${stats.pending_deliverables} entregables pendientes`;
                text.textContent = 'Revisa entregables para que los estudiantes sepan si deben corregir o continuar.';
                link.href = '/pages/teacher/my-deliverables.php';
                link.innerHTML = '<i class="bi bi-file-earmark-check"></i><span>Revisar entregables</span>';
                return;
            }
            title.textContent = 'Tu panel está al día';
            text.textContent = 'Puedes consultar tus proyectos recientes o preparar evaluaciones.';
            link.href = '/pages/teacher/my-projects.php';
            link.innerHTML = '<i class="bi bi-folder2"></i><span>Ver proyectos</span>';
        }

        async function loadDashboard() {
            try {
                const response = await api.get('/dashboard/teacher');
                
                document.getElementById('myProjects').textContent = response.stats.my_projects;
                document.getElementById('students').textContent = response.stats.students;
                document.getElementById('pendingDeliverables').textContent = response.stats.pending_deliverables;
                document.getElementById('pendingProposals').textContent = response.stats.pending_proposals || 0;
                updateTeacherNextAction(response.stats);
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
                showDashboardLoadError(loadDashboard);
            }
        }

        document.addEventListener('DOMContentLoaded', loadDashboard);
        window.onLiveChange?.(['deliverables', 'projects', 'proposals'], loadDashboard);
    </script>
</body>
</html>
