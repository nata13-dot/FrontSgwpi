<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.SGPI_SETTINGS = window.SGPI_SETTINGS || {};
window.SGPI_API_BASE_URL = '<?= API_BASE_URL ?>';

async function loadPublicSettings() {
    try {
        const response = await fetch(`${window.SGPI_API_BASE_URL}/settings/public`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) return;
        window.SGPI_SETTINGS = await response.json();
        applySystemSettings(window.SGPI_SETTINGS);
    } catch (error) {
        console.warn('No se pudieron cargar los ajustes generales', error);
    }
}

function applySystemSettings(settings) {
    const storedTheme = localStorage.getItem('sgpi-theme');
    let theme = storedTheme || settings.default_theme || 'light';
    if (theme === 'system') {
        theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.dataset.theme = theme;
    document.documentElement.style.fontSize = `${settings.font_scale || 100}%`;

    const existing = document.getElementById('globalSystemNotice');
    if (settings.global_notice) {
        if (!existing && document.body) {
            const notice = document.createElement('div');
            notice.id = 'globalSystemNotice';
            notice.className = 'global-system-notice';
            notice.innerHTML = `<i class="bi bi-megaphone"></i><span>${String(settings.global_notice).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]))}</span>`;
            document.body.prepend(notice);
        } else if (existing) {
            existing.querySelector('span').textContent = settings.global_notice;
        }
    } else if (existing) {
        existing.remove();
    }

    startIdleLogoutTimer(Number(settings.session_timeout_minutes || 30));
}

let idleLogoutTimer = null;
function startIdleLogoutTimer(minutes) {
    if (!localStorage.getItem('auth_token') || minutes <= 0) return;
    const timeoutMs = minutes * 60 * 1000;
    const resetTimer = () => {
        clearTimeout(idleLogoutTimer);
        idleLogoutTimer = setTimeout(() => {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.replace('/pages/logout.php?reason=inactive');
        }, timeoutMs);
    };

    ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach(eventName => {
        window.addEventListener(eventName, resetTimer, { passive: true });
    });
    resetTimer();
}

loadPublicSettings();

window.addEventListener('pageshow', function () {
    const serverAuthenticated = <?= is_authenticated() ? 'true' : 'false' ?>;
    if (serverAuthenticated && !localStorage.getItem('auth_token')) {
        window.location.replace('/index.php');
    }
});

window.swalToast = function (type, message, timer = 3500) {
    if (!window.Swal) return false;

    const iconMap = {
        danger: 'error',
        error: 'error',
        success: 'success',
        warning: 'warning',
        info: 'info',
        primary: 'info',
        secondary: 'info'
    };

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: iconMap[type] || 'info',
        title: message,
        showConfirmButton: false,
        timer,
        timerProgressBar: true
    });
    return true;
};

window.confirmAction = async function ({
    title = '¿Confirmar accion?',
    text = '',
    confirmButtonText = 'Si, continuar',
    icon = 'warning'
} = {}) {
    if (!window.Swal) return window.confirm(text || title);

    const result = await Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true
    });

    return result.isConfirmed;
};

window.promptText = async function ({
    title = 'Ingresa la informacion',
    inputPlaceholder = '',
    confirmButtonText = 'Continuar',
    inputValidator = null
} = {}) {
    if (!window.Swal) return window.prompt(title);

    const result = await Swal.fire({
        title,
        input: 'text',
        inputPlaceholder,
        inputValidator,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancelar'
    });

    return result.isConfirmed ? result.value : null;
};

window.promptPassword = async function ({
    title = 'Contraseña requerida',
    inputPlaceholder = 'Contraseña',
    confirmButtonText = 'Confirmar'
} = {}) {
    if (!window.Swal) return window.prompt(title);

    const result = await Swal.fire({
        title,
        input: 'password',
        inputPlaceholder,
        inputAttributes: {
            autocapitalize: 'off',
            autocomplete: 'current-password'
        },
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancelar'
    });

    return result.isConfirmed ? result.value : null;
};

window.promptAdminAction = async function () {
    if (!window.Swal) return window.prompt('Usuario administrador protegido. Escribe DESACTIVAR o ELIMINAR para continuar:');

    const result = await Swal.fire({
        title: 'Usuario administrador protegido',
        text: 'Selecciona la accion que deseas autorizar.',
        icon: 'warning',
        input: 'select',
        inputOptions: {
            DESACTIVAR: 'Desactivar usuario',
            ELIMINAR: 'Eliminar usuario'
        },
        inputPlaceholder: 'Selecciona una accion',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        inputValidator: value => value ? undefined : 'Selecciona una accion'
    });

    return result.isConfirmed ? result.value : null;
};
</script>
<footer class="footer mt-5">
    <div class="container-xl">
        <div class="row">
            <div class="col-lg-8">
                <p class="mb-2"><strong>© 2025 SGPI ITSSMT</strong></p>
                <p class="small mb-0">
                    Sistema de Gestión de Proyectos Integradores del Instituto Tecnológico Superior de San Martín Texmelucan
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <p class="small mb-0">Versión HTML | Bootstrap 5.3</p>
            </div>
        </div>
    </div>
</footer>
