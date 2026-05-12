<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
$home_url = is_authenticated() ? dashboard_url() : '/index.php';
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
                        <li class="nav-item">
                            <a href="/pages/admin/users.php" class="nav-link">
                                <i class="bi bi-people"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/pages/admin/document-tags.php" class="nav-link">
                                <i class="bi bi-tags"></i> Etiquetas
                            </a>
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

    if (token && typeof API_BASE_URL !== 'undefined') {
        fetch(`${API_BASE_URL}/auth/logout`, {
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
