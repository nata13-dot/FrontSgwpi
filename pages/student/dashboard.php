<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Verificar autenticación y rol
if (!is_authenticated() || !is_student()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - <?= APP_NAME ?></title>
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
                        <h1 class="display-4 fw-bold text-white mb-0">Panel del Estudiante</h1>
                    </div>
                    
                    <!-- Subtítulo -->
                    <p class="text-white opacity-90 mb-3" style="font-size: 1.1rem;">
                        <strong>Bienvenido, <?= htmlspecialchars($current_user['nombres']) ?></strong> | Gestión de proyectos y entregas
                    </p>
                    
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb" style="background: rgba(255,255,255,0.15); border-radius: 5px; padding: 8px 12px; margin: 0;">
                            <li class="breadcrumb-item"><a href="/index.php" class="text-white text-decoration-none">Inicio</a></li>
                            <li class="breadcrumb-item active text-white opacity-75">Panel Estudiante</li>
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
                                    <div class="w-100">
                                        <div class="dashboard-stat-label">Entregables completados</div>
                                        <div class="dashboard-stat-value mt-2" id="completedDeliverables">0</div>
                                        <div class="dashboard-progress-track mt-3"><div class="dashboard-progress-fill" id="completionProgress" style="width: 0%;"></div></div>
                                    </div>
                                    <span class="dashboard-stat-icon" style="color: #218838;"><i class="bi bi-file-earmark-check"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card dashboard-stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="dashboard-stat-label">Entregables pendientes</div>
                                        <div class="dashboard-stat-value mt-2" id="pendingDeliverables">0</div>
                                        <div class="dashboard-stat-note mt-2">Por enviar o revisar</div>
                                    </div>
                                    <span class="dashboard-stat-icon" style="color: #b38600;"><i class="bi bi-clock"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-lg-4">
                        <div class="dashboard-insight-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="bi bi-activity"></i> Mi avance</h5>
                                <span class="badge bg-primary" id="completionBadge">0%</span>
                            </div>
                            <div class="dashboard-progress-track mb-3"><div class="dashboard-progress-fill" id="completionProgressLarge" style="width: 0%;"></div></div>
                            <p class="text-muted mb-0">Avance calculado con entregables aprobados de tus proyectos.</p>
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
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Mis Proyectos Activos</h5>
                            </div>
                            <div class="card-body" id="projectsList">
                                <p class="dashboard-empty"><i class="bi bi-hourglass-split"></i> Cargando...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-door-open"></i> Presentaciones programadas</h5>
                            </div>
                            <div class="card-body">
                                <div id="evaluationScheduleList">
                                    <p class="text-muted">Cargando...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="initialProfileModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="initialProfileForm" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-check"></i> Completar perfil inicial</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Nombre</label><input class="form-control" name="nombres" id="initialNombres" required></div>
                        <div class="col-md-6"><label class="form-label">Apellido paterno</label><input class="form-control" name="apa" id="initialApa" required></div>
                        <div class="col-md-6"><label class="form-label">Apellido materno</label><input class="form-control" name="ama" id="initialAma"></div>
                        <div class="col-md-6"><label class="form-label">Semestre</label><select class="form-select" name="semestre" required><option value="">Selecciona</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option></select></div>
                        <div class="col-md-6"><label class="form-label">Grupo</label><input class="form-control" name="grupo" placeholder="A, B, 5to A" required></div>
                        <div class="col-12"><label class="form-label">Foto de perfil</label><input type="file" class="form-control" name="photo" accept="image/*"></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar y continuar</button></div>
            </form>
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

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function projectActiveAdvisors(project) {
            const advisors = Array.isArray(project?.advisors) ? project.advisors : [];
            return advisors.map(advisor => fullName(advisor)).filter(Boolean).join(', ') || 'Sin asesores activos';
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
                await checkInitialProfile();
                const response = await api.get('/dashboard/student');
                
                document.getElementById('myProjects').textContent = response.stats.my_projects;
                document.getElementById('completedDeliverables').textContent = response.stats.completed_deliverables;
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
                        const item = `
                            <a href="/pages/student/my-deliverables.php" class="dashboard-project-card">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <div class="dashboard-project-title mb-0">${escapeHtml(project.title)}</div>
                                    <span class="badge bg-primary">${progress}%</span>
                                </div>
                                <div class="dashboard-progress-track mb-2"><div class="dashboard-progress-fill" style="width: ${progress}%;"></div></div>
                                <div class="small text-muted">
                                    <i class="bi bi-person"></i> Docente: ${escapeHtml(projectActiveAdvisors(project))}
                                </div>
                            </a>
                        `;
                        projectsList.innerHTML += item;
                    });
                } else {
                    projectsList.innerHTML = '<p class="dashboard-empty"><i class="bi bi-inbox"></i> No hay proyectos asignados.</p>';
                }
                await loadEvaluationSchedule();
            } catch (error) {
                console.error('Error al cargar dashboard:', error);
            }
        }

        async function loadEvaluationSchedule() {
            const box = document.getElementById('evaluationScheduleList');
            try {
                const schedule = await api.get('/student/evaluation-schedule');
                if (!schedule.length) {
                    box.innerHTML = '<p class="text-muted mb-0">Aun no tienes presentaciones programadas.</p>';
                    return;
                }

                box.innerHTML = schedule.map(item => `
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>
                                <strong>${item.project_title || 'Proyecto'}</strong>
                                <div class="small text-muted">${item.room_name || 'Sala pendiente'} · ${item.classroom || 'Salon pendiente'}</div>
                            </div>
                            <span class="badge bg-primary">${item.date ? new Date(item.date).toLocaleString('es-MX') : 'Fecha pendiente'}</span>
                        </div>
                        <div class="small mt-2">
                            Tiempo de exposicion: <strong>${item.presentation_minutes || '-'} min</strong>
                            ${item.evaluation_minutes ? ` · Tiempo de evaluacion docente: <strong>${item.evaluation_minutes} min</strong>` : ''}
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                box.innerHTML = '<p class="text-danger mb-0">No se pudo cargar la agenda de evaluaciones.</p>';
            }
        }


        async function checkInitialProfile() {
            const status = await api.get('/proposal/student-status');
            if (!status.profile_required) return;
            document.getElementById('initialNombres').value = status.student.nombres || '';
            document.getElementById('initialApa').value = status.student.apa || '';
            document.getElementById('initialAma').value = status.student.ama || '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('initialProfileModal')).show();
        }

        document.getElementById('initialProfileForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            try {
                const response = await api.post('/profile/complete-initial', new FormData(event.target));
                await fetch('/api/set-session.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ auth_token: auth.getToken(), user: response.user })
                });
                bootstrap.Modal.getInstance(document.getElementById('initialProfileModal')).hide();
                swalToast('success', 'Perfil completado');
                loadDashboard();
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        });
        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>
