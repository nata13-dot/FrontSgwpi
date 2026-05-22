<?php
$current_page = basename($_SERVER['PHP_SELF']);
$section = isset($_GET['section']) ? $_GET['section'] : '';
?>

<nav class="sidebar" id="appSidebar">
    <?php if (is_authenticated()): ?>
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars(profile_photo_url($current_user ?? null)) ?>" class="sidebar-profile-photo" alt="Perfil">
            <div>
                <strong><?= htmlspecialchars($current_user['nombres'] ?? 'Usuario') ?></strong>
                <small><?= is_admin() ? 'Administrador' : (is_teacher() ? 'Docente' : 'Estudiante') ?></small>
            </div>
        </div>
    <?php endif; ?>
    <?php if (is_admin()): ?>
        <div class="sidebar-section-title" style="padding: 0 20px; margin-bottom: 20px;"><h6 class="text-muted text-uppercase" style="font-size: 0.85rem;">Administracion</h6></div>
        <a href="/pages/admin/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <details class="sidebar-group" <?= in_array($current_page, ['users.php', 'advisors.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-people"></i><span>Personas</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/users.php" class="sidebar-item sidebar-subitem <?= $current_page == 'users.php' ? 'active' : '' ?>"><i class="bi bi-people"></i><span>Usuarios</span></a>
            <a href="/pages/admin/advisors.php" class="sidebar-item sidebar-subitem <?= $current_page == 'advisors.php' ? 'active' : '' ?>"><i class="bi bi-person-check"></i><span>Asesores</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php', 'proposal-config.php', 'deliverables.php', 'evaluations.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-diagram-3"></i><span>Proyectos</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/projects.php" class="sidebar-item sidebar-subitem <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php']) ? 'active' : '' ?>"><i class="bi bi-diagram-3"></i><span>Proyectos</span></a>
            <a href="/pages/admin/proposal-config.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-config.php' ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i><span>Propuestas</span></a>
            <a href="/pages/admin/deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark"></i><span>Entregables</span></a>
            <a href="/pages/admin/evaluations.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluations.php' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i><span>Evaluaciones</span></a>
            <a href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Documentos de evaluación</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['asignaturas.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-book"></i><span>Académico</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/asignaturas.php" class="sidebar-item sidebar-subitem <?= $current_page == 'asignaturas.php' ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i><span>Asignaturas</span></a>
            <a href="/pages/admin/asignaturas.php#competencias" class="sidebar-item sidebar-subitem"><i class="bi bi-star"></i><span>Competencias</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['document-tags.php', 'notices.php', 'settings.php', 'repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/admin/document-tags.php" class="sidebar-item sidebar-subitem <?= $current_page == 'document-tags.php' ? 'active' : '' ?>"><i class="bi bi-tags"></i><span>Etiquetas</span></a>
            <a href="/pages/admin/notices.php" class="sidebar-item sidebar-subitem <?= $current_page == 'notices.php' ? 'active' : '' ?>"><i class="bi bi-megaphone"></i><span>Avisos</span></a>
            <a href="/pages/admin/settings.php" class="sidebar-item sidebar-subitem <?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="bi bi-sliders"></i><span>Ajustes</span></a>
            <a href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= $current_page == 'repositorio.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php elseif (is_teacher()): ?>
        <div class="sidebar-section-title" style="padding: 0 20px; margin-bottom: 20px;"><h6 class="text-muted text-uppercase" style="font-size: 0.85rem;">Docente</h6></div>
        <a href="/pages/teacher/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <details class="sidebar-group" <?= in_array($current_page, ['my-projects.php', 'proposal-review.php', 'evaluations.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-diagram-3"></i><span>Proyectos</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/teacher/my-projects.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-projects.php' ? 'active' : '' ?>"><i class="bi bi-folder2"></i><span>Mis proyectos</span></a>
            <a href="/pages/teacher/proposal-review.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-review.php' ? 'active' : '' ?>"><i class="bi bi-check2-square"></i><span>Revisar propuestas</span></a>
            <a href="/pages/admin/evaluations.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluations.php' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i><span>Evaluaciones</span></a>
            <a href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Documentos de evaluación</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['my-deliverables.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-file-earmark"></i><span>Entregables</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/teacher/my-deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-check"></i><span>Entregables</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= $current_page == 'repositorio.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php else: ?>
        <div class="sidebar-section-title" style="padding: 0 20px; margin-bottom: 20px;"><h6 class="text-muted text-uppercase" style="font-size: 0.85rem;">Estudiante</h6></div>
        <a href="/pages/student/dashboard.php" class="sidebar-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
        <details class="sidebar-group" <?= in_array($current_page, ['proposal-register.php', 'evaluation-documents.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-diagram-3"></i><span>Proyectos</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/student/proposal-register.php" class="sidebar-item sidebar-subitem <?= $current_page == 'proposal-register.php' ? 'active' : '' ?>"><i class="bi bi-pencil-square"></i><span>Registrar proyecto</span></a>
            <a href="/pages/evaluation-documents.php" class="sidebar-item sidebar-subitem <?= $current_page == 'evaluation-documents.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark-ppt"></i><span>Documentos de evaluación</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['my-deliverables.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-file-earmark"></i><span>Entregables</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/student/my-deliverables.php" class="sidebar-item sidebar-subitem <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>"><i class="bi bi-file-earmark"></i><span>Mis entregables</span></a>
        </details>
        <details class="sidebar-group" <?= in_array($current_page, ['repositorio.php']) ? 'open' : '' ?>>
            <summary><i class="bi bi-sliders"></i><span>Sistema</span><i class="bi bi-chevron-down ms-auto"></i></summary>
            <a href="/pages/repositorio.php" class="sidebar-item sidebar-subitem <?= $current_page == 'repositorio.php' ? 'active' : '' ?>"><i class="bi bi-archive"></i><span>Repositorio</span></a>
        </details>
        <a href="/pages/profile.php" class="sidebar-item <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Mi Perfil</span></a>
    <?php endif; ?>
</nav>
