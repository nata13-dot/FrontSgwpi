<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.SGPI_SETTINGS = window.SGPI_SETTINGS || {};
window.SGPI_API_BASE_URL = '<?= API_BASE_URL ?>';
window.SGPI_SESSION = <?= json_encode([
    'authenticated' => is_authenticated(),
    'token' => $auth_token,
    'user' => $current_user,
    'remember' => $auth_remember
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.SGPI_SETTINGS_SYNC_INTERVAL = window.SGPI_SETTINGS_SYNC_INTERVAL || 5000;
window.SGPI_SETTINGS_SIGNATURE = null;

async function loadPublicSettings(options = {}) {
    const force = Boolean(options.force);
    try {
        const url = new URL(`${window.SGPI_API_BASE_URL}/settings/public`);
        if (force) url.searchParams.set('_', Date.now());

        const response = await fetch(url.toString(), {
            credentials: 'include',
            cache: force ? 'no-store' : 'default',
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) return;
        const settings = await response.json();
        const signature = systemSettingsSignature(settings);
        if (signature === window.SGPI_SETTINGS_SIGNATURE) return;
        window.SGPI_SETTINGS = settings;
        window.SGPI_SETTINGS_SIGNATURE = signature;
        applySystemSettings(window.SGPI_SETTINGS);
    } catch (error) {
        console.warn('No se pudieron cargar los ajustes generales', error);
    }
}

function applySystemSettings(settings) {
    settings = settings || {};
    window.SGPI_SETTINGS = settings;
    window.SGPI_SETTINGS_SIGNATURE = systemSettingsSignature(settings);

    localStorage.setItem('sgpi-public-settings', JSON.stringify({
        default_theme: settings.default_theme || 'system',
        grayscale_mode: Boolean(settings.grayscale_mode),
        font_scale: Number(settings.font_scale || 100),
        global_notice: settings.global_notice || '',
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
    renderNotificationMenu(settings.system_notices || []);
    queueSystemNoticeToasts(settings.system_notices || []);
}

function systemSettingsSignature(settings) {
    try {
        return JSON.stringify({
            default_theme: settings?.default_theme || 'system',
            grayscale_mode: Boolean(settings?.grayscale_mode),
            font_scale: Number(settings?.font_scale || 100),
            global_notice: settings?.global_notice || '',
            session_timeout_minutes: Number(settings?.session_timeout_minutes || 30),
            system_notices: settings?.system_notices || []
        });
    } catch (error) {
        return '';
    }
}

function broadcastSystemSettings(settings) {
    try {
        localStorage.setItem('sgpi-settings-updated', JSON.stringify({
            at: Date.now(),
            settings
        }));
    } catch (error) {
        // Si storage esta bloqueado, el polling mantiene la sincronizacion.
    }
}

function startSystemSettingsSync() {
    if (window.SGPI_SETTINGS_SYNC_READY) return;
    window.SGPI_SETTINGS_SYNC_READY = true;

    window.addEventListener('storage', event => {
        if (event.key !== 'sgpi-settings-updated' || !event.newValue) return;
        try {
            const payload = JSON.parse(event.newValue);
            if (payload?.settings) {
                applySystemSettings(payload.settings);
                return;
            }
        } catch (error) {
            // Si el evento no trae ajustes validos, pedimos una copia fresca.
        }
        loadPublicSettings({ force: true });
    });

    window.addEventListener('focus', () => loadPublicSettings({ force: true }), { passive: true });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) loadPublicSettings({ force: true });
    });

    setInterval(() => {
        if (!document.hidden) loadPublicSettings({ force: true });
    }, window.SGPI_SETTINGS_SYNC_INTERVAL);
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
let idleLogoutListenersReady = false;
function startIdleLogoutTimer(minutes) {
    if (!window.SGPI_SESSION?.authenticated || minutes <= 0) return;
    if (typeof auth !== 'undefined') {
        auth.configureSessionTimeout?.(minutes);
    }
    if (window.SGPI_SESSION?.remember) {
        clearTimeout(idleLogoutTimer);
        return;
    }
    const timeoutMs = minutes * 60 * 1000;
    const resetTimer = () => {
        clearTimeout(idleLogoutTimer);
        idleLogoutTimer = setTimeout(() => {
            window.location.replace('/pages/logout.php?reason=inactive');
        }, timeoutMs);
    };

    window.SGPI_RESET_IDLE_TIMER = resetTimer;
    if (!idleLogoutListenersReady) {
        idleLogoutListenersReady = true;
        ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach(eventName => {
            window.addEventListener(eventName, () => window.SGPI_RESET_IDLE_TIMER?.(), { passive: true });
        });
    }
    resetTimer();
}

loadPublicSettings();
startSystemSettingsSync();

function currentAudienceContext() {
    const user = window.SGPI_SESSION?.user || null;

    const roleId = Number(user?.perfil_id || 0);
    const pathname = window.location.pathname.replace(/\/+$/, '') || '/';
    const isIndex = pathname === '/' || pathname.endsWith('/index.php');
    const isDashboard = /\/pages\/(admin|teacher|student)\/dashboard\.php$/.test(pathname);

    return {
        isIndex,
        isDashboard,
        authenticated: Boolean(window.SGPI_SESSION?.authenticated && user),
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

function activeSystemNoticesForCurrentAudience(notices) {
    if (!Array.isArray(notices)) return [];
    const context = currentAudienceContext();
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return notices
        .filter(notice => notice && notice.active !== false && notice.message)
        .filter(notice => noticeMatchesCurrentAudience(notice, context))
        .filter(notice => {
            if (notice.starts_at && new Date(notice.starts_at) > today) return false;
            if (notice.ends_at && new Date(notice.ends_at) < today) return false;
            return true;
        })
        .sort((a, b) => String(b.updated_at || b.created_at || '').localeCompare(String(a.updated_at || a.created_at || '')));
}

function renderNotificationMenu(notices) {
    const badge = document.getElementById('notificationBadge');
    const list = document.getElementById('notificationList');
    const summary = document.getElementById('notificationSummary');
    if (!badge || !list || !summary) return;

    const applicable = activeSystemNoticesForCurrentAudience(notices);
    const readIds = readNotificationIds();
    const unreadCount = applicable.filter(notice => !readIds.has(noticeSeenKey(notice))).length;
    badge.hidden = unreadCount === 0;
    badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
    summary.textContent = applicable.length === 1
        ? '1 aviso disponible'
        : `${applicable.length} avisos disponibles`;

    if (!applicable.length) {
        list.innerHTML = `
            <div class="notification-empty">
                <i class="bi bi-bell-slash"></i>
                <span>No hay notificaciones nuevas.</span>
            </div>`;
        return;
    }

    list.innerHTML = applicable.slice(0, 8).map(notice => {
        const type = escapeToastHtml(notice.type || 'info');
        const title = escapeToastHtml(notice.title || 'Aviso del sistema');
        const message = escapeToastHtml(notice.message || '');
        const date = escapeToastHtml(formatNotificationDate(notice.updated_at || notice.created_at || notice.starts_at));
        return `
            <article class="notification-item notification-item-${type}">
                <span class="notification-icon"><i class="bi ${notificationIcon(type)}"></i></span>
                <div>
                    <strong>${title}</strong>
                    <p>${message}</p>
                    ${date ? `<small>${date}</small>` : ''}
                </div>
            </article>`;
    }).join('');
}

function initNotificationMenu() {
    const menu = document.getElementById('notificationMenu');
    const badge = document.getElementById('notificationBadge');
    if (!menu || menu.dataset.notificationReady) return;
    menu.dataset.notificationReady = '1';

    menu.addEventListener('shown.bs.dropdown', () => {
        const applicable = activeSystemNoticesForCurrentAudience(window.SGPI_SETTINGS?.system_notices || []);
        const readIds = readNotificationIds();
        applicable.forEach(notice => readIds.add(noticeSeenKey(notice)));
        writeNotificationIds(readIds);
        if (badge) {
            badge.hidden = true;
            badge.textContent = '0';
        }
    });
}

function readNotificationIds() {
    try {
        return new Set(JSON.parse(localStorage.getItem('sgpi-read-notifications') || '[]'));
    } catch (error) {
        return new Set();
    }
}

function writeNotificationIds(ids) {
    try {
        localStorage.setItem('sgpi-read-notifications', JSON.stringify([...ids]));
    } catch (error) {
        // Si storage esta bloqueado, el contador solo se limpia durante esta vista.
    }
}

function notificationIcon(type) {
    const icons = {
        danger: 'bi-exclamation-triangle',
        warning: 'bi-exclamation-circle',
        success: 'bi-check-circle',
        info: 'bi-info-circle'
    };
    return icons[type] || icons.info;
}

function formatNotificationDate(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function queueSystemNoticeToasts(notices) {
    if (!Array.isArray(notices) || !notices.length) return;

    const context = currentAudienceContext();
    if (!context.isIndex && !context.isDashboard) return;

    const seenNoticeIds = readSeenNoticeIds();
    const applicable = notices
        .filter(notice => notice && notice.active !== false && notice.message)
        .filter(notice => noticeMatchesCurrentAudience(notice, context))
        .filter(notice => !seenNoticeIds.has(noticeSeenKey(notice)));

    if (!applicable.length) return;

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
    return String(`${notice.id || 'notice'}:${notice.updated_at || notice.created_at || ''}:${notice.audience || 'all'}:${notice.title || ''}:${notice.message || ''}`);
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
document.addEventListener('DOMContentLoaded', initNotificationMenu);

window.addEventListener('pageshow', function () {
    const serverAuthenticated = <?= is_authenticated() ? 'true' : 'false' ?>;
    if (serverAuthenticated && !window.SGPI_SESSION?.token) {
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

function captureDialogViewport() {
    const activeElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    const scrollPositions = [...document.querySelectorAll('*')]
        .filter(element => element.scrollHeight > element.clientHeight || element.scrollWidth > element.clientWidth)
        .filter(element => element.scrollTop || element.scrollLeft)
        .map(element => ({
            element,
            top: element.scrollTop,
            left: element.scrollLeft
        }));

    return {
        activeElement,
        x: window.scrollX,
        y: window.scrollY,
        scrollPositions
    };
}

function restoreDialogViewport(viewport, restoreFocus = false) {
    if (!viewport) return;

    window.scrollTo({ left: viewport.x, top: viewport.y, behavior: 'auto' });
    viewport.scrollPositions.forEach(position => {
        if (!position.element?.isConnected) return;
        position.element.scrollLeft = position.left;
        position.element.scrollTop = position.top;
    });

    if (restoreFocus && viewport.activeElement?.isConnected) {
        try {
            viewport.activeElement.focus({ preventScroll: true });
        } catch (error) {
            // El control puede dejar de ser enfocable despues de actualizar la vista.
        }
    }
}

window.stableSwalFire = async function (options = {}) {
    if (!window.Swal) return null;

    const viewport = captureDialogViewport();
    const userDidOpen = options.didOpen;
    const userDidClose = options.didClose;
    const restore = restoreFocus => {
        restoreDialogViewport(viewport, restoreFocus);
        requestAnimationFrame(() => restoreDialogViewport(viewport, restoreFocus));
    };

    return Swal.fire({
        ...options,
        heightAuto: false,
        scrollbarPadding: false,
        returnFocus: false,
        didOpen: popup => {
            restore(false);
            if (typeof userDidOpen === 'function') userDidOpen(popup);
        },
        didClose: () => {
            restore(true);
            if (typeof userDidClose === 'function') userDidClose();
        }
    });
};

window.confirmAction = async function ({
    title = '¿Confirmar accion?',
    text = '',
    confirmButtonText = 'Si, continuar',
    icon = 'warning'
} = {}) {
    if (!window.Swal) return window.confirm(text || title);

    const result = await stableSwalFire({
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

    const result = await stableSwalFire({
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

    const result = await stableSwalFire({
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

    const result = await stableSwalFire({
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
<script src="/assets/js/skeleton-loader.js"></script>
<script src="/assets/js/view-transitions.js"></script>
<script src="/assets/js/responsive.js"></script>
