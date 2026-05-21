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
    localStorage.setItem('sgpi-public-settings', JSON.stringify({
        default_theme: settings.default_theme || 'system',
        grayscale_mode: Boolean(settings.grayscale_mode),
        font_scale: Number(settings.font_scale || 100),
        system_notices: settings.system_notices || []
    }));

    const storedTheme = localStorage.getItem('sgpi-theme');
    let theme = storedTheme || settings.default_theme || 'light';
    if (theme === 'system') {
        theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.dataset.theme = theme;
    document.documentElement.style.colorScheme = theme;
    document.documentElement.classList.toggle('grayscale-mode', Boolean(settings.grayscale_mode));
    document.documentElement.style.fontSize = `${settings.font_scale || 100}%`;

    const globalNoticeText = String(settings.global_notice || '').trim();
    const existing = document.getElementById('globalSystemNotice');
    if (globalNoticeText) {
        if (!existing && document.body) {
            const notice = document.createElement('div');
            notice.id = 'globalSystemNotice';
            notice.className = 'global-system-notice';
            notice.innerHTML = `<i class="bi bi-megaphone"></i><span>${escapeToastHtml(globalNoticeText)}</span>`;
            placeGlobalSystemNotice(notice);
        } else if (existing) {
            existing.querySelector('span').textContent = globalNoticeText;
            placeGlobalSystemNotice(existing);
        }
        syncGlobalNoticeOffset();
    } else {
        if (existing) {
            existing.remove();
        }
        syncGlobalNoticeOffset();
    }

    startIdleLogoutTimer(Number(settings.session_timeout_minutes || 30));
    queueSystemNoticeToasts(settings.system_notices || []);
}

function placeGlobalSystemNotice(notice) {
    const navbar = document.querySelector('.navbar');
    if (navbar && navbar.parentNode) {
        navbar.insertAdjacentElement('beforebegin', notice);
        return;
    }
    document.body.prepend(notice);
}

function syncGlobalNoticeOffset() {
    const notice = document.getElementById('globalSystemNotice');
    const navbar = document.querySelector('.navbar');
    const height = notice && notice.textContent.trim() ? notice.offsetHeight : 0;
    document.documentElement.style.setProperty('--sgpi-global-notice-height', `${height}px`);
    if (navbar) {
        navbar.classList.toggle('has-global-system-notice', height > 0);
    }
}

window.addEventListener('resize', syncGlobalNoticeOffset, { passive: true });
window.addEventListener('orientationchange', syncGlobalNoticeOffset, { passive: true });

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

function currentAudienceContext() {
    let user = null;
    try {
        user = JSON.parse(localStorage.getItem('user') || 'null');
    } catch (error) {
        user = null;
    }

    const roleId = Number(user?.perfil_id || 0);
    const pathname = window.location.pathname.replace(/\/+$/, '') || '/';
    const isIndex = pathname === '/' || pathname.endsWith('/index.php');
    const isDashboard = /\/pages\/(admin|teacher|student)\/dashboard\.php$/.test(pathname);

    return {
        isIndex,
        isDashboard,
        authenticated: Boolean(localStorage.getItem('auth_token') && user),
        role: roleId === 1 ? 'admin' : (roleId === 2 ? 'teacher' : (roleId === 3 ? 'student' : 'public'))
    };
}

function noticeMatchesCurrentAudience(notice, context) {
    const audience = notice.audience || 'all';
    if (audience === 'all') return true;
    if (audience === 'index') return context.isIndex;
    if (audience === 'authenticated') return context.authenticated;
    if (audience === 'academic') return ['teacher', 'student'].includes(context.role);
    return audience === context.role;
}

function queueSystemNoticeToasts(notices) {
    if (!Array.isArray(notices) || !notices.length || window.SGPI_NOTICES_SHOWN) return;

    const context = currentAudienceContext();
    if (!context.isIndex && !context.isDashboard) return;

    const seenNoticeIds = readSeenNoticeIds();
    const applicable = notices
        .filter(notice => notice && notice.active !== false && notice.message)
        .filter(notice => noticeMatchesCurrentAudience(notice, context))
        .filter(notice => !seenNoticeIds.has(noticeSeenKey(notice)));

    if (!applicable.length) return;

    window.SGPI_NOTICES_SHOWN = true;
    applicable.forEach(notice => seenNoticeIds.add(noticeSeenKey(notice)));
    writeSeenNoticeIds(seenNoticeIds);

    const rounds = applicable.length > 3 ? 3 : 1;
    const queue = Array.from({ length: rounds }, () => applicable).flat();

    let delay = 0;
    queue.forEach(notice => {
        setTimeout(() => showSystemNoticeToast(notice), delay);
        delay += noticeToastDuration(notice) + 600;
    });
}

function showSystemNoticeToast(notice) {
    if (!window.Swal) return;

    const iconMap = {
        danger: 'error',
        warning: 'warning',
        success: 'success',
        info: 'info'
    };

    const title = notice.title ? `<strong>${escapeToastHtml(notice.title)}</strong><br>` : '';
    Swal.fire({
        toast: true,
        position: 'top-end',
        customClass: {
            popup: 'system-notice-toast'
        },
        icon: iconMap[notice.type] || 'info',
        html: `${title}<span>${escapeToastHtml(notice.message)}</span>`,
        showConfirmButton: false,
        timer: noticeToastDuration(notice),
        timerProgressBar: true
    });
}

function noticeToastDuration(notice) {
    const seconds = Number(notice.duration_seconds || 4);
    return Math.min(Math.max(seconds, 2), 30) * 1000;
}

function noticeSeenKey(notice) {
    return String(notice.id || `${notice.audience || 'all'}:${notice.title || ''}:${notice.message || ''}`);
}

function readSeenNoticeIds() {
    try {
        return new Set(JSON.parse(sessionStorage.getItem('sgpi-seen-notices') || '[]'));
    } catch (error) {
        return new Set();
    }
}

function writeSeenNoticeIds(ids) {
    sessionStorage.setItem('sgpi-seen-notices', JSON.stringify([...ids]));
}

function escapeToastHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[char]));
}

function enhancePasswordVisibility() {
    document.querySelectorAll('input[type="password"]').forEach(input => {
        if (input.dataset.passwordToggleReady) return;
        if (input.closest('.form-floating')) return;
        input.dataset.passwordToggleReady = '1';

        if (input.parentElement && input.parentElement.classList.contains('input-group')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'input-group';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary';
        button.title = 'Mostrar u ocultar contraseña';
        button.innerHTML = '<i class="bi bi-eye"></i>';
        button.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
        wrapper.appendChild(button);
    });
}

document.addEventListener('DOMContentLoaded', enhancePasswordVisibility);

window.addEventListener('pageshow', function () {
    const serverAuthenticated = <?= is_authenticated() ? 'true' : 'false' ?>;
    if (serverAuthenticated && !localStorage.getItem('auth_token')) {
        window.location.replace('/pages/logout.php?reason=session_mismatch');
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
        customClass: {
            popup: 'system-notice-toast'
        },
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
            DESACTIVAR: 'Desactivar usuario'
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
<script src="/assets/js/responsive.js"></script>
