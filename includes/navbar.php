<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
$home_url = is_authenticated() ? dashboard_url() : '/index.php';
$management_pages = [
    'users.php', 'advisors.php', 'projects.php', 'project-create.php', 'project-edit.php',
    'proposal-config.php', 'deliverables.php', 'evaluations.php', 'asignaturas.php',
    'document-tags.php', 'notices.php', 'settings.php', 'my-projects.php',
    'proposal-review.php', 'proposal-register.php', 'my-deliverables.php', 'repositorio.php'
];
?>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-xl">
        <a href="<?= $home_url ?>" class="navbar-brand">
            <img src="/assets/img/ITSSMT/ITSSMT.png" alt="ITSSMT">
            <div class="navbar-brand-text">
                <span>Gestión de Proyectos</span>
                <span>Integradores ITSSMT</span>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="<?= $home_url ?>" class="nav-link <?= ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'active' : '' ?>">
                        <i class="bi bi-house"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pages/repositorio.php" class="nav-link <?= $current_page == 'repositorio.php' ? 'active' : '' ?>">
                        <i class="bi bi-archive"></i> Repositorio
                    </a>
                </li>
                
                <?php if (is_authenticated()): ?>
                    <?php if (is_admin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($current_page, $management_pages) ? 'active' : '' ?>" href="#" id="managementMenu" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-grid-3x3-gap"></i> Gestiones
                            </a>
                            <div class="dropdown-menu dropdown-menu-end management-menu" aria-labelledby="managementMenu">
                                <div class="management-menu-grid">
                                    <div>
                                        <h6 class="dropdown-header">Personas</h6>
                                        <a class="dropdown-item <?= $current_page == 'users.php' ? 'active' : '' ?>" href="/pages/admin/users.php"><i class="bi bi-people"></i> Usuarios</a>
                                        <a class="dropdown-item <?= $current_page == 'advisors.php' ? 'active' : '' ?>" href="/pages/admin/advisors.php"><i class="bi bi-person-check"></i> Asesores</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Proyectos</h6>
                                        <a class="dropdown-item <?= in_array($current_page, ['projects.php', 'project-create.php', 'project-edit.php']) ? 'active' : '' ?>" href="/pages/admin/projects.php"><i class="bi bi-diagram-3"></i> Proyectos</a>
                                        <a class="dropdown-item <?= $current_page == 'proposal-config.php' ? 'active' : '' ?>" href="/pages/admin/proposal-config.php"><i class="bi bi-calendar-check"></i> Propuestas</a>
                                        <a class="dropdown-item <?= $current_page == 'deliverables.php' ? 'active' : '' ?>" href="/pages/admin/deliverables.php"><i class="bi bi-file-earmark"></i> Entregables</a>
                                        <a class="dropdown-item <?= $current_page == 'evaluations.php' ? 'active' : '' ?>" href="/pages/admin/evaluations.php"><i class="bi bi-clipboard-check"></i> Evaluaciones</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Académico</h6>
                                        <a class="dropdown-item <?= $current_page == 'asignaturas.php' ? 'active' : '' ?>" href="/pages/admin/asignaturas.php"><i class="bi bi-book"></i> Asignaturas, cargas y competencias</a>
                                    </div>
                                    <div>
                                        <h6 class="dropdown-header">Sistema</h6>
                                        <a class="dropdown-item <?= $current_page == 'document-tags.php' ? 'active' : '' ?>" href="/pages/admin/document-tags.php"><i class="bi bi-tags"></i> Etiquetas</a>
                                        <a class="dropdown-item <?= $current_page == 'notices.php' ? 'active' : '' ?>" href="/pages/admin/notices.php"><i class="bi bi-megaphone"></i> Avisos</a>
                                        <a class="dropdown-item <?= $current_page == 'settings.php' ? 'active' : '' ?>" href="/pages/admin/settings.php"><i class="bi bi-sliders"></i> Ajustes</a>
                                        <a class="dropdown-item <?= $current_page == 'repositorio.php' ? 'active' : '' ?>" href="/pages/repositorio.php"><i class="bi bi-archive"></i> Repositorio</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php elseif (is_teacher()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($current_page, $management_pages) ? 'active' : '' ?>" href="#" id="managementMenu" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-grid-3x3-gap"></i> Gestiones
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="managementMenu">
                                <li><h6 class="dropdown-header">Docente</h6></li>
                                <li><a class="dropdown-item <?= $current_page == 'my-projects.php' ? 'active' : '' ?>" href="/pages/teacher/my-projects.php"><i class="bi bi-folder2"></i> Mis proyectos</a></li>
                                <li><a class="dropdown-item <?= $current_page == 'proposal-review.php' ? 'active' : '' ?>" href="/pages/teacher/proposal-review.php"><i class="bi bi-check2-square"></i> Revisar propuestas</a></li>
                                <li><a class="dropdown-item <?= $current_page == 'evaluations.php' ? 'active' : '' ?>" href="/pages/admin/evaluations.php"><i class="bi bi-clipboard-check"></i> Evaluaciones</a></li>
                                <li><a class="dropdown-item <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>" href="/pages/teacher/my-deliverables.php"><i class="bi bi-file-earmark"></i> Entregables</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item <?= $current_page == 'repositorio.php' ? 'active' : '' ?>" href="/pages/repositorio.php"><i class="bi bi-archive"></i> Repositorio</a></li>
                            </ul>
                        </li>
                    <?php elseif (is_student()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($current_page, $management_pages) ? 'active' : '' ?>" href="#" id="managementMenu" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-grid-3x3-gap"></i> Gestiones
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="managementMenu">
                                <li><h6 class="dropdown-header">Estudiante</h6></li>
                                <li><a class="dropdown-item <?= $current_page == 'proposal-register.php' ? 'active' : '' ?>" href="/pages/student/proposal-register.php"><i class="bi bi-pencil-square"></i> Registrar proyecto</a></li>
                                <li><a class="dropdown-item <?= $current_page == 'my-deliverables.php' ? 'active' : '' ?>" href="/pages/student/my-deliverables.php"><i class="bi bi-file-earmark"></i> Mis entregables</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item <?= $current_page == 'repositorio.php' ? 'active' : '' ?>" href="/pages/repositorio.php"><i class="bi bi-archive"></i> Repositorio</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-nav-link" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <img src="<?= htmlspecialchars(profile_photo_url($current_user)) ?>" class="profile-thumb" alt="Perfil">
                            <?= isset($current_user['nombres']) ? htmlspecialchars($current_user['nombres']) : 'Perfil' ?>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('themeToggle');
    const storedTheme = localStorage.getItem('sgpi-theme') || document.documentElement.dataset.theme || 'light';
    document.documentElement.dataset.theme = storedTheme;

    if (toggle) {
        const setLabel = () => {
            const dark = document.documentElement.dataset.theme === 'dark';
            toggle.innerHTML = `<i class="bi ${dark ? 'bi-sun' : 'bi-moon-stars'}"></i> ${dark ? 'Modo claro' : 'Modo oscuro'}`;
        };
        setLabel();
        toggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = nextTheme;
            document.documentElement.style.colorScheme = nextTheme;
            localStorage.setItem('sgpi-theme', nextTheme);
            setLabel();
        });
    }

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
        title: 'Cerrar sesion',
        text: '¿Estas seguro que deseas cerrar sesion?',
        confirmButtonText: 'Si, cerrar sesion'
    });
    if (!confirmed) return;

    const token = localStorage.getItem('auth_token');
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');

    const apiBaseUrl = typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : window.SGPI_API_BASE_URL;

    if (token && apiBaseUrl) {
        fetch(`${apiBaseUrl}/auth/logout`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` }
        }).catch(() => {}).finally(() => {
            window.location.replace('/pages/logout.php');
        });
        return;
    }

    window.location.replace('/pages/logout.php');
}
</script>
