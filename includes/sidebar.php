<?php
$current_page = basename($_SERVER['PHP_SELF']);
$section = isset($_GET['section']) ? $_GET['section'] : '';
?>

<nav class="sidebar" id="appSidebar">
    <a href="<?= htmlspecialchars(dashboard_url()) ?>" class="sidebar-brand">
        <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT">
        <div>
            <strong>Gestión de Proyectos</strong>
            <strong><?= htmlspecialchars(active_career()['nombre_corto'] ?? 'Integradores ITSSMT') ?></strong>
            <small><?= htmlspecialchars(active_career()['clave'] ?? 'Sistema Institucional') ?></small>
        </div>
    </a>
    <?php if (is_authenticated()): ?>
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars(profile_photo_url($current_user ?? null)) ?>" class="sidebar-profile-photo" alt="Perfil">
            <div>
                <strong><?= htmlspecialchars($current_user['nombres'] ?? 'Usuario') ?></strong>
                <small><?= htmlspecialchars(profile_role_label()) ?></small>
                <span class="sidebar-online"><i></i> En línea</span>
            </div>
        </div>
        <?php // En el teléfono el menú de la cuenta queda oculto en la barra superior: sus comandos se repiten aquí. ?>
        <div class="sidebar-account-actions">
            <button type="button" class="sidebar-item" data-theme-toggle><i class="bi bi-moon-stars"></i><span>Modo oscuro</span></button>
            <button type="button" class="sidebar-item text-danger" onclick="logout()"><i class="bi bi-box-arrow-right"></i><span>Cerrar sesión</span></button>
        </div>
    <?php endif; ?>
    <?php $academicWorkflowsLink = '<a href="/pages/academic-workflows.php" class="sidebar-item ' . ($current_page == 'academic-workflows.php' ? 'active' : '') . '"><i class="bi bi-calendar3"></i><span>Seguimiento académico</span></a>'; ?>
    <?php if (is_management_staff()): ?>
        <?php if (is_general_admin()): ?>
            <?php // El administrador general tiene su sección propia, separada de la carrera que administra en este momento. ?>
            <div class="sidebar-section-title"><h6 class="text-uppercase">Administración general</h6></div>
            <a href="/pages/admin/general.php" class="sidebar-item <?= $current_page == 'general.php' ? 'active' : '' ?>"><i class="bi bi-buildings"></i><span>Panel institucional</span></a>
            <a href="/pages/admin/careers.php" class="sidebar-item <?= $current_page == 'careers.php' ? 'active' : '' ?>"><i class="bi bi-diagram-3"></i><span>Carreras y accesos</span></a>
            <a href="/pages/admin/operations.php" class="sidebar-item <?= $current_page == 'operations.php' ? 'active' : '' ?>"><i class="bi bi-activity"></i><span>Operaciones</span></a>
            <details class="sidebar-group" <?= in_array($current_page, ['career-audit.php', 'career-integrity.php', 'database-backups.php', 'sent-emails.php']) ? 'open' : '' ?>>
                <summary><i class="bi bi-shield-check"></i><span>Seguridad y continuidad</span><i class="bi bi-chevron-down ms-auto"></i></summary>
                <a href="/pages/admin/career-audit.php" class="sidebar-item sidebar-subitem <?= $current_page == 'career-audit.php' ? 'active' : '' ?>"><i class="bi bi-shield-lock"></i><span>Auditoría</span></a>
                <a href="/pages/admin/career-integrity.php" class="sidebar-item sidebar-subitem <?= $current_page == 'career-integrity.php' ? 'active' : '' ?>"><i class="bi bi-shield-check"></i><span>Integridad</span></a>
                <a href="/pages/admin/database-backups.php" class="sidebar-item sidebar-subitem <?= $current_page == 'database-backups.php' ? 'active' : '' ?>"><i class="bi bi-database-check"></i><span>Respaldos</span></a>
                <a href="/pages/admin/sent-emails.php" class="sidebar-item sidebar-subitem <?= $current_page == 'sent-emails.php' ? 'active' : '' ?>"><i class="bi bi-envelope-check"></i><span>Correos enviados</span></a>
            </details>
            <div class="sidebar-section-title"><h6 class="text-uppercase">Carrera: <?= htmlspecialchars(active_career()['nombre_corto'] ?? 'sin seleccionar') ?></h6></div>
            <a href="/pages/admin/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio de la carrera</span></a>
        <?php else: ?>
            <div class="sidebar-section-title"><h6 class="text-uppercase">Administración</h6></div>
            <a href="/pages/admin/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <?php endif; ?>
        <?php // Mismos grupos que Gestiones (navbar.php) y que la app (React_Swgpi/src/lib/navigation.js). ?>
        <details class="sidebar-group" <?= in_array($current_page, ['period-center.php', 'academic-workflows.php', 'semesters.php', 'asignaturas.php', 'competencias.php', 'career-setup.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-flag"></i><span>Periodo</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/period-center.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['period-center.php']) ? 'active' : '' ?>"><i class="bi bi-flag"></i><span>Operación del periodo</span></a>
            <a href="/pages/academic-workflows.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['academic-workflows.php']) ? 'active' : '' ?>"><i class="bi bi-calendar3"></i><span>Seguimiento académico</span></a>
            <?php if (can_manage_academics()): ?>
                <a data-career-module="academico" href="/pages/admin/semesters.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['semesters.php']) ? 'active' : '' ?>"><i class="bi bi-calendar-range"></i><span>Semestres y periodos</span></a>
                <a data-career-module="academico" href="/pages/admin/asignaturas.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['asignaturas.php', 'competencias.php']) ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i><span>Asignaturas y competencias</span></a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
                <a data-career-module="academico" href="/pages/admin/career-setup.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['career-setup.php']) ? 'active' : '' ?>"><i class="bi bi-cloud-arrow-up"></i><span>Carga inicial</span></a>
            <?php endif; ?>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['users.php', 'user-create.php', 'user-edit.php', 'registration-requests.php', 'advisors.php', 'archive.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-people"></i><span>Personas</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <?php if (can_view_users()): ?>
                <a data-career-module="usuarios" href="/pages/admin/users.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['users.php', 'user-create.php', 'user-edit.php']) ? 'active' : '' ?>"><i class="bi bi-people"></i><span>Usuarios</span></a>
            <?php endif; ?>
            <?php if (can_manage_academics()): ?>
                <a href="/pages/admin/registration-requests.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['registration-requests.php']) ? 'active' : '' ?>"><i class="bi bi-person-plus"></i><span>Solicitudes de cuenta</span><span class="badge rounded-pill text-bg-danger ms-auto d-none" data-management-badge="registration_requests"></span></a>
            <?php endif; ?>
            <a data-career-module="proyectos" href="/pages/admin/advisors.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['advisors.php']) ? 'active' : '' ?>"><i class="bi bi-person-check"></i><span>Asesores</span></a>
            <?php if (is_admin()): ?>
                <a href="/pages/admin/archive.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['archive.php']) ? 'active' : '' ?>"><i class="bi bi-archive-fill"></i><span>Archivo histórico</span></a>
            <?php endif; ?>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php', 'proposal-config.php', 'deliverables.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-mortarboard"></i><span>Proyectos</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="proyectos" href="/pages/admin/projects.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php']) ? 'active' : '' ?>"><i class="bi bi-diagram-3"></i><span>Proyectos y tesis</span></a>
            <a data-career-module="proyectos" href="/pages/admin/proposal-config.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['proposal-config.php']) ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i><span>Propuestas</span><span class="badge rounded-pill text-bg-danger ms-auto d-none" data-management-badge="pending_proposals"></span></a>
            <a data-career-module="entregables" href="/pages/admin/deliverables.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['deliverables.php']) ? 'active' : '' ?>"><i class="bi bi-file-earmark-check"></i><span>Entregables</span></a>
        </details>
        <details class="sidebar-group" data-career-module="evaluaciones" <?= in_array($current_page, ['evaluations.php', 'evaluation-rooms.php', 'evaluation-documents.php', 'evaluations-archived.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-clipboard-check"></i><span>Evaluación</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/evaluations.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['evaluations.php']) ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i><span>Evaluaciones</span></a>
            <a href="/pages/admin/evaluation-rooms.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['evaluation-rooms.php']) ? 'active' : '' ?>"><i class="bi bi-door-open"></i><span>Salas y turnos</span></a>
            <a href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['evaluation-documents.php']) ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Evidencias</span></a>
            <a href="/pages/admin/evaluations-archived.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['evaluations-archived.php']) ? 'active' : '' ?>"><i class="bi bi-clock-history"></i><span>Evaluaciones previas</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['settings.php', 'notices.php', 'document-tags.php', 'career-modules.php', 'repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <?php if (is_admin()): ?>
                <a data-career-module="configuracion" href="/pages/admin/settings.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['settings.php']) ? 'active' : '' ?>"><i class="bi bi-sliders"></i><span>Ajustes</span></a>
                <a data-career-module="configuracion" href="/pages/admin/notices.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['notices.php']) ? 'active' : '' ?>"><i class="bi bi-megaphone"></i><span>Avisos</span></a>
                <a data-career-module="repositorio" href="/pages/admin/document-tags.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['document-tags.php']) ? 'active' : '' ?>"><i class="bi bi-tags"></i><span>Etiquetas</span></a>
                <a href="/pages/admin/career-modules.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['career-modules.php']) ? 'active' : '' ?>"><i class="bi bi-grid-3x3-gap"></i><span>Módulos de carrera</span></a>
            <?php endif; ?>
            <a data-career-module="repositorio" href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['repositorio.php']) ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php elseif (is_teacher()): ?>
        <div class="sidebar-section-title"><h6 class="text-uppercase">Docente</h6></div>
        <a href="/pages/teacher/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <?= $academicWorkflowsLink ?>
        <details class="sidebar-group" <?= in_array($current_page, ['my-projects.php', 'proposal-review.php', 'evaluations.php', 'evaluations-archived.php', 'evaluation-rooms.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-mortarboard"></i><span>Proyectos y tesis</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="proyectos" href="/pages/teacher/my-projects.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-projects.php' ? 'active' : '' ?>"><i class="bi bi-folder2"></i><span>Mis proyectos</span></a>
            <a data-career-module="proyectos" href="/pages/teacher/proposal-review.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-review.php' ? 'active' : '' ?>"><i class="bi bi-check2-square"></i><span>Revisar propuestas</span></a>
            <a data-career-module="evaluaciones" href="/pages/admin/evaluations.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluations.php' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i><span>Evaluaciones</span></a>
            <a data-career-module="evaluaciones" href="/pages/admin/evaluation-rooms.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-rooms.php' ? 'active' : '' ?>"><i class="bi bi-door-open"></i><span>Salas y turnos</span></a>
            <a data-career-module="evaluaciones" href="/pages/admin/evaluations-archived.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluations-archived.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Evaluaciones previas</span></a>
            <a data-career-module="evaluaciones" href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Evidencias</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['my-deliverables.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-book"></i><span>Académico</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="entregables" href="/pages/teacher/my-deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-check"></i><span>Entregables</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="repositorio" href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= $current_page == 'repositorio.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php else: ?>
        <div class="sidebar-section-title"><h6 class="text-uppercase">Estudiante</h6></div>
        <a href="/pages/student/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <?= $academicWorkflowsLink ?>
        <details class="sidebar-group" <?= in_array($current_page, ['proposal-register.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-mortarboard"></i><span>Proyectos y tesis</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="proyectos" href="/pages/student/proposal-register.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-register.php' ? 'active' : '' ?>"><i class="bi bi-pencil-square"></i><span>Registrar propuesta</span></a>
            <a data-career-module="evaluaciones" href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Evidencias</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['my-deliverables.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-book"></i><span>Académico</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="entregables" href="/pages/student/my-deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark"></i><span>Mis entregables</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="repositorio" href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= $current_page == 'repositorio.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php endif; ?>
    <div class="sidebar-footer-brand">
        <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT">
        <div>
            <strong>Gestión de Proyectos Integradores ITSSMT</strong>
            <small>Versión BETA 2.1.17</small>
        </div>
    </div>
    <button type="button" class="sidebar-collapse-control" data-sidebar-toggle aria-label="Contraer menú lateral">
        <i class="bi bi-chevron-double-left"></i>
    </button>
</nav>
