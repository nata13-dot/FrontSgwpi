<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
$home_url = is_authenticated() ? dashboard_url() : '/index.php';
$management_pages = [
    'users.php', 'advisors.php', 'projects.php', 'project-create.php', 'project-edit.php',
    'proposal-config.php', 'deliverables.php', 'evaluations.php', 'asignaturas.php',
    'document-tags.php', 'notices.php', 'settings.php', 'semesters.php', 'my-projects.php',
    'proposal-review.php', 'proposal-register.php', 'my-deliverables.php', 'evaluation-documents.php', 'repositorio.php',
    'period-center.php', 'academic-workflows.php', 'competencias.php', 'career-setup.php', 'registration-requests.php', 'archive.php',
    'evaluation-rooms.php', 'evaluations-archived.php', 'career-modules.php', 'user-create.php', 'user-edit.php'
];
?>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top <?= is_authenticated() ? 'navbar-authenticated' : 'navbar-public' ?>">
    <div class="container-xl">
        <a href="<?= $home_url ?>" class="navbar-brand">
            <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT">
            <div class="navbar-brand-text">
                <span>Gestión de Proyectos</span>
                <span>Integradores ITSSMT</span>
            </div>
        </a>

        <?php if (is_authenticated()): ?>
            <button type="button" class="navbar-sidebar-toggle" data-sidebar-toggle aria-label="Alternar menú lateral">
                <i class="bi bi-list"></i>
            </button>
            <a href="<?= $home_url ?>" class="navbar-mobile-emblem" aria-label="Ir al inicio">
                <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT">
            </a>
            <div class="global-search-shell" data-global-search data-role="<?= is_general_admin() ? 'general_admin' : (is_management_staff() ? 'admin' : (is_teacher() ? 'teacher' : 'student')) ?>"
                 data-permissions="<?= htmlspecialchars(implode(' ', array_keys(array_filter([
                     'govern_users' => can_govern_users(),
                     'admin' => is_admin(),
                     'manage_academics' => can_manage_academics(),
                     'manage_projects' => can_manage_projects(),
                 ])))) ?>">
                <form class="global-search" role="search" action="/pages/repositorio.php" method="get" autocomplete="off">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        type="search"
                        name="q"
                        placeholder="Buscar pantallas y registros..."
                        aria-label="Buscar páginas, opciones o registros en el sistema"
                        aria-controls="globalSearchResults"
                        aria-expanded="false"
                    >
                    <kbd>Ctrl + K</kbd>
                </form>
                <div class="global-search-results" id="globalSearchResults" role="listbox" hidden></div>
            </div>
        <?php endif; ?>
        
        <?php if (is_authenticated()): ?>
            <button type="button" class="global-search-open" data-global-search-open aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
        <?php endif; ?>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir navegación">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="<?= $home_url ?>" class="nav-link <?= ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'active' : '' ?>" title="Inicio">
                        <i class="bi bi-house" aria-hidden="true"></i> <span class="nav-text">Inicio</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pages/repositorio.php" class="nav-link <?= $current_page == 'repositorio.php' ? 'active' : '' ?>" title="Repositorio">
                        <i class="bi bi-archive" aria-hidden="true"></i> <span class="nav-text">Repositorio</span>
                    </a>
                </li>
                
                <?php if (is_authenticated()): ?>
                    <?php if (!view_as_session() && (is_general_admin() || count(available_careers()) > 1)): ?>
                        <li class="nav-item dropdown career-switcher">
                            <button class="nav-link dropdown-toggle career-switcher-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-diagram-3"></i>
                                <span><?= htmlspecialchars(active_career()['nombre_corto'] ?? 'Seleccionar carrera') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end career-switcher-menu">
                                <li><h6 class="dropdown-header">Administrar como</h6></li>
                                <?php foreach (available_careers() as $careerOption): ?>
                                    <li>
                                        <button
                                            type="button"
                                            class="dropdown-item career-switch-option <?= (int) ($careerOption['id'] ?? 0) === (int) active_career_id() ? 'active' : '' ?>"
                                            data-career-id="<?= (int) ($careerOption['id'] ?? 0) ?>"
                                        >
                                            <span class="career-color-dot" style="--dot-color: <?= htmlspecialchars($careerOption['color_acento'] ?? $careerOption['color_primario'] ?? '#1B396A') ?>"></span>
                                            <span>
                                                <strong><?= htmlspecialchars($careerOption['nombre_corto'] ?? $careerOption['nombre'] ?? '') ?></strong>
                                                <small><?= htmlspecialchars($careerOption['clave'] ?? '') ?></small>
                                            </span>
                                            <?php if ((int) ($careerOption['id'] ?? 0) === (int) active_career_id()): ?>
                                                <i class="bi bi-check2 ms-auto"></i>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (is_general_admin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/pages/admin/careers.php"><i class="bi bi-sliders"></i> Gestionar carreras</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (is_general_admin() || view_as_session()): ?>
                        <?php $levelOptions = [5 => 'Jefe de carrera', 6 => 'Asistente de jefatura', 7 => 'Coordinador de proyectos integradores', 2 => 'Docente', 3 => 'Estudiante']; ?>
                        <li class="nav-item dropdown level-switcher">
                            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ver el sistema con otro nivel de perfil">
                                <i class="bi bi-layers"></i>
                                <span><?= htmlspecialchars(view_as_session() ? profile_role_label() : 'Administrador general') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Ver <?= htmlspecialchars(active_career()['nombre_corto'] ?? 'la carrera') ?> como</h6></li>
                                <li>
                                    <button type="button" class="dropdown-item <?= view_as_session() ? '' : 'active' ?>" <?= view_as_session() ? 'onclick="exitViewAs(this)"' : 'disabled' ?>>
                                        <span class="level-number">1</span> Administrador general
                                    </button>
                                </li>
                                <?php $level = 2; foreach ($levelOptions as $profileId => $label): ?>
                                    <li>
                                        <button type="button" class="dropdown-item <?= view_as_session() && (int) get_user_role() === $profileId ? 'active' : '' ?>" onclick="startLevelView(<?= $profileId ?>, this)">
                                            <span class="level-number"><?= $level++ ?></span> <?= htmlspecialchars($label) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><span class="dropdown-item-text small text-muted">Simulación de sólo lectura con tu cuenta;<br>no muestra datos personales de nadie.</span></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (is_management_staff()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($current_page, $management_pages) ? 'active' : '' ?>" href="#" id="managementMenu" role="button" data-bs-toggle="dropdown" title="Gestiones">
                                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> <span class="nav-text">Gestiones</span>
                                <span class="badge rounded-pill text-bg-danger d-none" data-management-badge="total"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end management-menu" aria-labelledby="managementMenu">
                                <?php // Mismos grupos que el menú lateral y que la app (React_Swgpi/src/lib/navigation.js). ?>
                                <div class="management-menu-grid management-menu-grid-5">
                                    <div>
                                        <h6 class="dropdown-header">Periodo</h6>
                                        <a class="dropdown-item <?= in_array($current_page, ['period-center.php']) ? 'active' : '' ?>" href="/pages/admin/period-center.php"><i class="bi bi-flag"></i> Operación del periodo</a>
                                        <a class="dropdown-item <?= in_array($current_page, ['academic-workflows.php']) ? 'active' : '' ?>" href="/pages/academic-workflows.php"><i class="bi bi-calendar3"></i> Seguimiento académico</a>
                                        <?php if (can_manage_academics()): ?>
                                            <a class="dropdown-item <?= in_array($current_page, ['asignaturas.php', 'competencias.php']) ? 'active' : '' ?>" href="/pages/admin/asignaturas.php"><i class="bi bi-book"></i> Asignaturas y competencias</a>
                                        <?php endif; ?>
                                        <?php if (is_admin()): ?>
                                            <a class="dropdown-item <?= in_array($current_page, ['career-setup.php']) ? 'active' : '' ?>" href="/pages/admin/career-setup.php"><i class="bi bi-cloud-arrow-up"></i> Carga inicial</a>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Personas</h6>
                                        <?php if (can_govern_users()): ?>
                                            <a class="dropdown-item <?= in_array($current_page, ['users.php', 'user-create.php', 'user-edit.php']) ? 'active' : '' ?>" href="/pages/admin/users.php"><i class="bi bi-people"></i> Usuarios</a>
                                        <?php endif; ?>
                                        <?php if (can_manage_academics()): ?>
                                            <a class="dropdown-item <?= in_array($current_page, ['registration-requests.php']) ? 'active' : '' ?>" href="/pages/admin/registration-requests.php"><i class="bi bi-person-plus"></i> Solicitudes de cuenta <span class="badge rounded-pill text-bg-danger ms-auto d-none" data-management-badge="registration_requests"></span></a>
                                        <?php endif; ?>
                                        <a class="dropdown-item <?= in_array($current_page, ['advisors.php']) ? 'active' : '' ?>" href="/pages/admin/advisors.php"><i class="bi bi-person-check"></i> Asesores</a>
                                        <?php if (is_admin()): ?>
                                            <a class="dropdown-item <?= in_array($current_page, ['archive.php']) ? 'active' : '' ?>" href="/pages/admin/archive.php"><i class="bi bi-archive-fill"></i> Archivo histórico</a>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Proyectos</h6>
                                        <a class="dropdown-item <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php']) ? 'active' : '' ?>" href="/pages/admin/projects.php"><i class="bi bi-diagram-3"></i> Proyectos y tesis</a>
                                        <a class="dropdown-item <?= in_array($current_page, ['proposal-config.php']) ? 'active' : '' ?>" href="/pages/admin/proposal-config.php"><i class="bi bi-calendar-check"></i> Propuestas <span class="badge rounded-pill text-bg-danger ms-auto d-none" data-management-badge="pending_proposals"></span></a>
                                        <a class="dropdown-item <?= in_array($current_page, ['deliverables.php']) ? 'active' : '' ?>" href="/pages/admin/deliverables.php"><i class="bi bi-file-earmark"></i> Entregables</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Evaluación</h6>
                                        <a class="dropdown-item <?= in_array($current_page, ['evaluations.php']) ? 'active' : '' ?>" href="/pages/admin/evaluations.php"><i class="bi bi-clipboard-check"></i> Evaluaciones</a>
                                        <a class="dropdown-item <?= in_array($current_page, ['evaluation-rooms.php']) ? 'active' : '' ?>" href="/pages/admin/evaluation-rooms.php"><i class="bi bi-door-open"></i> Salas y turnos</a>
                                        <a class="dropdown-item <?= in_array($current_page, ['evaluation-documents.php']) ? 'active' : '' ?>" href="/pages/evaluation-documents.php"><i class="bi bi-file-earmark-ppt"></i> Evidencias</a>
                                        <a class="dropdown-item <?= in_array($current_page, ['evaluations-archived.php']) ? 'active' : '' ?>" href="/pages/admin/evaluations-archived.php"><i class="bi bi-clock-history"></i> Evaluaciones previas</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Sistema</h6>
                                        <?php if (is_admin()): ?>
                                            <a class="dropdown-item <?= in_array($current_page, ['settings.php']) ? 'active' : '' ?>" href="/pages/admin/settings.php"><i class="bi bi-sliders"></i> Ajustes</a>
                                            <a class="dropdown-item <?= in_array($current_page, ['notices.php']) ? 'active' : '' ?>" href="/pages/admin/notices.php"><i class="bi bi-megaphone"></i> Avisos</a>
                                            <a class="dropdown-item <?= in_array($current_page, ['document-tags.php']) ? 'active' : '' ?>" href="/pages/admin/document-tags.php"><i class="bi bi-tags"></i> Etiquetas</a>
                                            <a class="dropdown-item <?= in_array($current_page, ['career-modules.php']) ? 'active' : '' ?>" href="/pages/admin/career-modules.php"><i class="bi bi-grid-3x3-gap"></i> Módulos de carrera</a>
                                        <?php endif; ?>
                                        <a class="dropdown-item <?= in_array($current_page, ['repositorio.php']) ? 'active' : '' ?>" href="/pages/repositorio.php"><i class="bi bi-archive"></i> Repositorio</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <script>
                        // Pendientes en Gestiones: solicitudes de cuenta y propuestas. Se actualizan al llegar algo (live.js).
                        document.addEventListener('DOMContentLoaded', () => {
                            const paint = counts => {
                                // Sólo suma lo que esta persona ve en el menú (cada contador aparece arriba y en el lateral).
                                const shown = new Set();
                                document.querySelectorAll('[data-management-badge]').forEach(badge => {
                                    const key = badge.dataset.managementBadge;
                                    if (key === 'total') return;
                                    shown.add(key);
                                    const value = Number(counts[key] || 0);
                                    badge.textContent = value > 99 ? '99+' : String(value);
                                    badge.classList.toggle('d-none', !value);
                                });
                                const total = [...shown].reduce((sum, key) => sum + Number(counts[key] || 0), 0);
                                const head = document.querySelector('[data-management-badge="total"]');
                                if (head) { head.textContent = total > 99 ? '99+' : String(total); head.classList.toggle('d-none', !total); }
                            };
                            const refresh = (fresh = false) => api.get('/period-center/badges', { _cache_ttl: 60000, _fresh: fresh, _silent: true }).then(paint).catch(() => {});
                            refresh();
                            window.onLiveChange?.(['registration_requests', 'proposals'], () => refresh(true));
                        });
                        </script>
                    <?php elseif (is_teacher()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($current_page, $management_pages) ? 'active' : '' ?>" href="#" id="managementMenu" role="button" data-bs-toggle="dropdown" title="Gestiones">
                                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> <span class="nav-text">Gestiones</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end management-menu" aria-labelledby="managementMenu">
                                <div class="management-menu-grid">
                                    <div>
                                        <h6 class="dropdown-header">Proyectos</h6>
                                        <a class="dropdown-item <?= $current_page == 'my-projects.php' ? 'active' : '' ?>" href="/pages/teacher/my-projects.php"><i class="bi bi-folder2"></i> Mis proyectos</a>
                                        <a class="dropdown-item <?= $current_page == 'proposal-review.php' ? 'active' : '' ?>" href="/pages/teacher/proposal-review.php"><i class="bi bi-check2-square"></i> Revisar propuestas</a>
                                        <a class="dropdown-item <?= $current_page == 'evaluations.php' ? 'active' : '' ?>" href="/pages/admin/evaluations.php"><i class="bi bi-clipboard-check"></i> Evaluaciones</a>
                                        <a class="dropdown-item <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>" href="/pages/evaluation-documents.php"><i class="bi bi-file-earmark-ppt"></i> Documentos de evaluación</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Entregables</h6>
                                        <a class="dropdown-item <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>" href="/pages/teacher/my-deliverables.php"><i class="bi bi-file-earmark"></i> Entregables</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Sistema</h6>
                                        <a class="dropdown-item <?= $current_page == 'repositorio.php' ? 'active' : '' ?>" href="/pages/repositorio.php"><i class="bi bi-archive"></i> Repositorio</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php elseif (is_student()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($current_page, $management_pages) ? 'active' : '' ?>" href="#" id="managementMenu" role="button" data-bs-toggle="dropdown" title="Gestiones">
                                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> <span class="nav-text">Gestiones</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end management-menu" aria-labelledby="managementMenu">
                                <div class="management-menu-grid">
                                    <div>
                                        <h6 class="dropdown-header">Proyectos</h6>
                                        <a class="dropdown-item <?= $current_page == 'proposal-register.php' ? 'active' : '' ?>" href="/pages/student/proposal-register.php"><i class="bi bi-pencil-square"></i> Mi proyecto</a>
                                        <a class="dropdown-item <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>" href="/pages/evaluation-documents.php"><i class="bi bi-file-earmark-ppt"></i> Documentos de evaluación</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Entregables</h6>
                                        <a class="dropdown-item <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>" href="/pages/student/my-deliverables.php"><i class="bi bi-file-earmark"></i> Mis entregables</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Sistema</h6>
                                        <a class="dropdown-item <?= $current_page == 'repositorio.php' ? 'active' : '' ?>" href="/pages/repositorio.php"><i class="bi bi-archive"></i> Repositorio</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (is_general_admin()): ?>
                    <li class="nav-item nav-icon-item dropdown">
                        <button class="nav-link nav-icon-link" type="button" id="operationalNotificationMenu" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Alertas operativas">
                            <i class="bi bi-shield-exclamation"></i>
                            <span class="nav-icon-badge" id="operationalNotificationBadge" hidden>0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="operationalNotificationMenu">
                            <div class="notification-menu-header">
                                <div>
                                    <strong>Estado operativo</strong>
                                    <small id="operationalNotificationSummary">Sin alertas activas</small>
                                </div>
                                <a href="/pages/admin/operations.php" class="btn btn-sm btn-outline-primary">Ver centro</a>
                            </div>
                            <div class="notification-list" id="operationalNotificationList">
                                <div class="notification-empty">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Operación institucional saludable.</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item nav-icon-item dropdown">
                        <button class="nav-link nav-icon-link" type="button" id="notificationMenu" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notificaciones">
                            <i class="bi bi-bell"></i>
                            <span class="nav-icon-badge" id="notificationBadge" hidden>0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationMenu">
                            <div class="notification-menu-header">
                                <div>
                                    <strong>Notificaciones</strong>
                                    <small id="notificationSummary">Avisos del sistema</small>
                                </div>
                                <?php if (is_admin()): ?>
                                    <a href="/pages/admin/notices.php" class="btn btn-sm btn-outline-primary">Gestionar</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="notification-empty">
                                    <i class="bi bi-bell-slash"></i>
                                    <span>No hay notificaciones nuevas.</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item nav-icon-item dropdown">
                        <button class="nav-link nav-icon-link" type="button" id="activityNotificationMenu" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Actividad reciente">
                            <i class="bi bi-envelope"></i>
                            <span class="nav-icon-badge" id="activityNotificationBadge" hidden>0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="activityNotificationMenu">
                            <div class="notification-menu-header">
                                <div>
                                    <strong>Actividad reciente</strong>
                                    <small id="activityNotificationSummary">Sin actividad nueva</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="markAllActivityNotifications">Marcar leídas</button>
                            </div>
                            <div class="notification-list" id="activityNotificationList">
                                <div class="notification-empty">
                                    <i class="bi bi-envelope-open"></i>
                                    <span>No hay actividad reciente.</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-nav-link" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <img src="<?= htmlspecialchars(profile_photo_url($current_user)) ?>" class="profile-thumb" alt="Perfil">
                            <span class="user-nav-name">
                                <strong><?= isset($current_user['nombres']) ? htmlspecialchars($current_user['nombres']) : 'Perfil' ?></strong>
                                <small><?= htmlspecialchars(profile_role_label()) ?></small>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= dashboard_url() ?>"><i class="bi bi-house"></i> Inicio</a></li>
                            <li><a class="dropdown-item" href="/pages/profile.php"><i class="bi bi-person-circle"></i> Mi perfil</a></li>
                            <li><button class="dropdown-item" type="button" id="themeToggle"><i class="bi bi-moon-stars"></i> Modo oscuro</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/index.php#login" class="nav-link" data-open-login>
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php if ($viewAs = view_as_session()): ?>
<div class="view-as-banner" role="status">
    <div class="container-xl d-flex flex-wrap align-items-center gap-2">
        <i class="bi bi-eye"></i>
        <span>
            Estás viendo <strong><?= htmlspecialchars(active_career()['nombre_corto'] ?? '') ?></strong> como <strong><?= htmlspecialchars(profile_role_label()) ?></strong>.
            Es una simulación de sólo lectura con tu cuenta: los cambios están bloqueados y no se muestran datos personales de nadie.
        </span>
        <button type="button" class="btn btn-sm btn-dark ms-auto" onclick="exitViewAs(this)"><i class="bi bi-box-arrow-left"></i> Volver a administrador general</button>
    </div>
</div>

<?php endif; ?>

<?php if (is_general_admin() || view_as_session()): ?>
<script>
async function exitViewAs(button) {
    button.disabled = true;
    // Al volver se queda en la misma carrera: en la página de administración actual o en su inicio.
    const returnTo = <?= json_encode(str_starts_with($_SERVER['PHP_SELF'] ?? '', '/pages/admin/')) ?> ? location.pathname + location.search : '/pages/admin/dashboard.php';
    const response = await fetch('/api/view-as.php', { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ action: 'exit', return_to: returnTo }) });
    const data = await response.json().catch(() => ({}));
    try { sessionStorage.clear(); } catch (error) { /* la recarga basta */ }
    window.location.replace(data.dashboard_url || data.redirect || '/index.php');
}

async function startLevelView(profileId, button) {
    button.disabled = true;
    try {
        const response = await fetch('/api/view-as.php', { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ action: 'start', perfil_id: profileId }) });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (data.redirect) return window.location.replace(data.redirect);
            throw new Error(data.error || 'No se pudo abrir la simulación.');
        }
        try { sessionStorage.clear(); } catch (error) { /* la recarga basta */ }
        window.location.replace(data.dashboard_url || '/index.php');
    } catch (error) {
        button.disabled = false;
        if (window.Swal) Swal.fire('No se cambió el nivel', error.message, 'error'); else window.alert(error.message);
    }
}
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // El de la barra superior y el del menú lateral en el teléfono.
    const toggles = document.querySelectorAll('#themeToggle, [data-theme-toggle]');
    const saveAppearance = () => api.put('/appearance', { theme: SGPIAppearance.theme(), font_scale: SGPIAppearance.fontScale() }).catch(() => {});
    window.SGPISaveAppearance = saveAppearance;

    if (toggles.length) {
        const setLabel = () => {
            const dark = document.documentElement.dataset.theme === 'dark';
            const label = dark ? 'Modo claro' : 'Modo oscuro';
            toggles.forEach(toggle => {
                toggle.innerHTML = toggle.hasAttribute('data-theme-toggle')
                    ? `<i class="bi ${dark ? 'bi-sun' : 'bi-moon-stars'}"></i><span>${label}</span>`
                    : `<i class="bi ${dark ? 'bi-sun' : 'bi-moon-stars'}"></i> ${label}`;
            });
        };
        setLabel();
        window.addEventListener('sgpi:appearance', setLabel);
        toggles.forEach(toggle => toggle.addEventListener('click', () => {
            SGPIAppearance.set({ theme: document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark' });
            if (!document.querySelector('.view-as-banner')) saveAppearance();
        }));
    }

    // La apariencia de la cuenta se consulta una vez por pestaña; mientras tanto se usa la copia de este navegador.
    <?php if (is_authenticated()): ?>
    if (sessionStorage.getItem('sgpi-appearance-loaded') !== <?= json_encode((string) ($current_user['id'] ?? '')) ?>) {
        api.get('/appearance', { _fresh: true, _silent: true }).then(response => {
            sessionStorage.setItem('sgpi-appearance-loaded', <?= json_encode((string) ($current_user['id'] ?? '')) ?>);
            if (response.data) SGPIAppearance.set({ theme: response.data.theme, fontScale: response.data.font_scale });
        }).catch(() => {});
    }
    <?php endif; ?>

    document.querySelectorAll('[data-open-login]').forEach(link => {
        link.addEventListener('click', event => {
            const modal = document.getElementById('loginModal');
            if (!modal || !window.bootstrap) return;
            event.preventDefault();
            bootstrap.Modal.getOrCreateInstance(modal).show();
            history.replaceState(null, '', '/index.php#login');
        });
    });
});

async function logout() {
    const confirmed = await confirmAction({
        title: 'Cerrar sesión',
        text: '¿Estás seguro de que deseas cerrar sesión?',
        confirmButtonText: 'Sí, cerrar sesión'
    });
    if (!confirmed) return;

    const token = window.SGPI_SESSION?.token || null;

    const apiBaseUrl = typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : window.SGPI_API_BASE_URL;

    if (token && apiBaseUrl) {
        fetch(`${apiBaseUrl}/auth/logout`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Authorization': `Bearer ${token}` }
        }).catch(() => {}).finally(() => {
            window.location.replace('/pages/logout.php');
        });
        return;
    }

    window.location.replace('/pages/logout.php');
}
</script>
<?php if (is_authenticated()): ?>
    <script src="<?= asset('/assets/js/global-search.js') ?>"></script>
    <script>window.SGPI_PAGE = <?= json_encode($_SERVER['PHP_SELF'] ?? '') ?>;</script>
    <script src="<?= asset('/assets/js/upload-guard.js') ?>"></script>
    <script src="<?= asset('/assets/js/help-center.js') ?>"></script>
    <script src="<?= asset('/assets/js/live.js') ?>"></script>
    <script type="module">
    // Guía "Cómo usar esta sección" (section-guide.js, la misma de la app), debajo del título de la página.
    import { guideKeyFor, mountSectionGuide } from '<?= asset('/assets/js/section-guide.js') ?>';
    const key = guideKeyFor(window.SGPI_PAGE || location.pathname, location.search);
    const heading = key && document.querySelector('.main-content h1, main h1');
    if (heading) {
        let anchor = heading;
        while (anchor.parentElement && !anchor.parentElement.matches('.container-xl, .container, .container-fluid, main, .main-content')) anchor = anchor.parentElement;
        const root = document.createElement('div');
        root.className = 'section-guide';
        anchor.after(root);
        mountSectionGuide(root, { user: (typeof auth !== 'undefined' && auth.getCurrentUser?.()) || {}, key });
    }
    </script>
    <script src="<?= asset('/assets/js/activity-notifications.js') ?>"></script>
    <?php if (is_general_admin()): ?>
        <script src="<?= asset('/assets/js/operational-notifications.js') ?>"></script>
    <?php endif; ?>
    <script src="<?= asset('/assets/js/career-modules.js') ?>"></script>
    <script src="<?= asset('/assets/js/career-switcher.js') ?>"></script>
<?php endif; ?>
