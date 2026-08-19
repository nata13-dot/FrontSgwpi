<?php
$current_page = basename($_SERVER['PHP_SELF']);
$section = isset($_GET['section']) ? $_GET['section'] : '';
?>

<nav class="sidebar" id="appSidebar">
    <a href="<?= htmlspecialchars(dashboard_url()) ?>" class="sidebar-brand">
        <img src="/assets/img/ITSSMT/ITSSMT.webp" alt="ITSSMT">
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
    <?php endif; ?>
    <?php if (is_management_staff()): ?>
        <div class="sidebar-section-title" style="padding: 0 20px; margin-bottom: 20px;"><h6 class="text-muted text-uppercase" style="font-size: 0.85rem;">Administración</h6></div>
        <a href="/pages/admin/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <?php if (is_general_admin()): ?>
            <details class="sidebar-group" <?= in_array($current_page, ['operations.php', 'careers.php']) ? 'open' : '' ?>>
                <summary><i class="bi bi-buildings"></i><span>Gobierno institucional</span><i class="bi bi-chevron-down ms-auto"></i></summary>
                <a href="/pages/admin/operations.php" class="sidebar-item sidebar-subitem <?= $current_page == 'operations.php' ? 'active' : '' ?>"><i class="bi bi-activity"></i><span>Operaciones</span></a>
                <a href="/pages/admin/careers.php" class="sidebar-item sidebar-subitem <?= $current_page == 'careers.php' ? 'active' : '' ?>"><i class="bi bi-diagram-3"></i><span>Carreras y accesos</span></a>
            </details>
            <details class="sidebar-group" <?= in_array($current_page, ['career-audit.php', 'career-integrity.php', 'database-backups.php']) ? 'open' : '' ?>>
                <summary><i class="bi bi-shield-check"></i><span>Seguridad y continuidad</span><i class="bi bi-chevron-down ms-auto"></i></summary>
                <a href="/pages/admin/career-audit.php" class="sidebar-item sidebar-subitem <?= $current_page == 'career-audit.php' ? 'active' : '' ?>"><i class="bi bi-shield-lock"></i><span>Auditoría</span></a>
                <a href="/pages/admin/career-integrity.php" class="sidebar-item sidebar-subitem <?= $current_page == 'career-integrity.php' ? 'active' : '' ?>"><i class="bi bi-shield-check"></i><span>Integridad</span></a>
                <a href="/pages/admin/database-backups.php" class="sidebar-item sidebar-subitem <?= $current_page == 'database-backups.php' ? 'active' : '' ?>"><i class="bi bi-database-check"></i><span>Respaldos</span></a>
            </details>
        <?php endif; ?>
        <?php if (can_govern_users()): ?>
        <details class="sidebar-group" data-career-module="usuarios" <?= in_array($current_page, ['users.php', 'advisors.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-people"></i><span>Personas</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/users.php" class="sidebar-item sidebar-subitem <?= $current_page == 'users.php' ? 'active' : '' ?>"><i class="bi bi-people"></i><span>Usuarios</span></a>
            <a href="/pages/admin/advisors.php" class="sidebar-item sidebar-subitem <?= $current_page == 'advisors.php' ? 'active' : '' ?>"><i class="bi bi-person-check"></i><span>Asesores</span></a>
        </details>
        <?php else: ?>
        <a data-career-module="proyectos" href="/pages/admin/advisors.php" class="sidebar-item <?= $current_page == 'advisors.php' ? 'active' : '' ?>"><i class="bi bi-person-check"></i><span>Asesores</span></a>
        <?php endif; ?>
        <details class="sidebar-group" <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php', 'proposal-config.php', 'evaluations.php', 'evaluations-archived.php', 'evaluation-rooms.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-mortarboard"></i><span>Proyectos y tesis</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="proyectos" href="/pages/admin/projects.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php']) ? 'active' : '' ?>"><i class="bi bi-folder2-open"></i><span>Gestionar proyectos</span></a>
            <a data-career-module="proyectos" href="/pages/admin/proposal-config.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-config.php' ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i><span>Propuestas</span></a>
            <a data-career-module="evaluaciones" href="/pages/admin/evaluations.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['evaluations.php', 'evaluation-rooms.php']) ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i><span>Evaluaciones</span></a>
            <a data-career-module="evaluaciones" href="/pages/admin/evaluations-archived.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluations-archived.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Evaluaciones previas</span></a>
            <a data-career-module="evaluaciones" href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Evidencias</span></a>
            <?php if (!can_manage_academics()): ?>
                <a data-career-module="entregables" href="/pages/admin/deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-check"></i><span>Entregables</span></a>
            <?php endif; ?>
        </details>
        <?php if (can_manage_academics()): ?>
        <details class="sidebar-group" data-career-module="academico" <?= in_array($current_page, ['asignaturas.php', 'semesters.php', 'deliverables.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-book"></i><span>Académico</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/asignaturas.php" class="sidebar-item sidebar-subitem <?= $current_page == 'asignaturas.php' ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i><span>Asignaturas</span></a>
            <a href="/pages/admin/asignaturas.php#competencias" class="sidebar-item sidebar-subitem"><i class="bi bi-star"></i><span>Competencias</span></a>
            <a href="/pages/admin/semesters.php" class="sidebar-item sidebar-subitem <?= $current_page == 'semesters.php' ? 'active' : '' ?>"><i class="bi bi-calendar3"></i><span>Semestres y periodos</span></a>
            <a data-career-module="entregables" href="/pages/admin/deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-check"></i><span>Entregables</span></a>
        </details>
        <?php endif; ?>
        <details class="sidebar-group" <?= in_array($current_page, ['document-tags.php', 'notices.php', 'settings.php', 'repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <?php if (is_admin()): ?>
                <a data-career-module="repositorio" href="/pages/admin/document-tags.php" class="sidebar-item sidebar-subitem <?= $current_page == 'document-tags.php' ? 'active' : '' ?>"><i class="bi bi-tags"></i><span>Etiquetas</span></a>
                <a data-career-module="configuracion" href="/pages/admin/notices.php" class="sidebar-item sidebar-subitem <?= $current_page == 'notices.php' ? 'active' : '' ?>"><i class="bi bi-megaphone"></i><span>Avisos</span></a>
                <a data-career-module="configuracion" href="/pages/admin/settings.php" class="sidebar-item sidebar-subitem <?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="bi bi-sliders"></i><span>Ajustes</span></a>
                <a href="/pages/admin/career-modules.php" class="sidebar-item sidebar-subitem <?= $current_page == 'career-modules.php' ? 'active' : '' ?>"><i class="bi bi-grid-3x3-gap"></i><span>Módulos de carrera</span></a>
                <a data-career-module="academico" href="/pages/admin/career-setup.php" class="sidebar-item sidebar-subitem <?= $current_page == 'career-setup.php' ? 'active' : '' ?>"><i class="bi bi-cloud-arrow-up"></i><span>Carga inicial</span></a>
            <?php endif; ?>
            <a data-career-module="repositorio" href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= $current_page == 'repositorio.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php elseif (is_teacher()): ?>
        <div class="sidebar-section-title" style="padding: 0 20px; margin-bottom: 20px;"><h6 class="text-muted text-uppercase" style="font-size: 0.85rem;">Docente</h6></div>
        <a href="/pages/teacher/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <details class="sidebar-group" <?= in_array($current_page, ['my-projects.php', 'proposal-review.php', 'evaluations.php', 'evaluations-archived.php', 'evaluation-rooms.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-mortarboard"></i><span>Proyectos y tesis</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a data-career-module="proyectos" href="/pages/teacher/my-projects.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-projects.php' ? 'active' : '' ?>"><i class="bi bi-folder2"></i><span>Mis proyectos</span></a>
            <a data-career-module="proyectos" href="/pages/teacher/proposal-review.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-review.php' ? 'active' : '' ?>"><i class="bi bi-check2-square"></i><span>Revisar propuestas</span></a>
            <a data-career-module="evaluaciones" href="/pages/admin/evaluations.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluations.php' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i><span>Evaluaciones</span></a>
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
        <div class="sidebar-section-title" style="padding: 0 20px; margin-bottom: 20px;"><h6 class="text-muted text-uppercase" style="font-size: 0.85rem;">Estudiante</h6></div>
        <a href="/pages/student/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
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
        <img src="/assets/img/ITSSMT/ITSSMT.webp" alt="ITSSMT">
        <div>
            <strong>Gestión de Proyectos Integradores ITSSMT</strong>
            <small>Versión BETA 2.1.17</small>
        </div>
    </div>
    <button type="button" class="sidebar-collapse-control" data-sidebar-toggle aria-label="Contraer menú lateral">
        <i class="bi bi-chevron-double-left"></i>
    </button>
</nav>
