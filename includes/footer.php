<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script>
window.SGPI_SETTINGS = window.SGPI_SETTINGS || {};
window.SGPI_API_BASE_URL = '<?= API_BASE_URL ?>';
window.SGPI_SESSION = <?= json_encode([
    'authenticated' => is_authenticated(),
    'token' => $auth_token,
    'user' => $current_user,
    'remember' => $auth_remember
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.SGPI_SETTINGS_SYNC_INTERVAL = window.SGPI_SETTINGS_SYNC_INTERVAL || 60000;
window.SGPI_SETTINGS_SIGNATURE = null;
window.SGPI_SETTINGS_LAST_SYNC = 0;

// Al crear o modificar un aviso, el pulso de cambios (live.js) recarga los ajustes y lo muestra al momento.
document.addEventListener('DOMContentLoaded', () => window.onLiveChange?.(['notices'], () => loadPublicSettings({ force: true })));

async function loadPublicSettings(options = {}) {
    const force = Boolean(options.force);
    try {
        const settingsEndpoint = window.SGPI_SESSION?.authenticated ? '/settings/current' : '/settings/public';
        const url = new URL(`${window.SGPI_API_BASE_URL}${settingsEndpoint}`);
        if (force) url.searchParams.set('_', Date.now());

        const response = await fetch(url.toString(), {
            credentials: 'include',
            cache: force ? 'no-store' : 'default',
            headers: {
                'Accept': 'application/json',
                ...(window.SGPI_SESSION?.authenticated
                    ? {'Authorization': `Bearer ${window.SGPI_SESSION.token || ''}`}
                    : {})
            }
        });
        if (!response.ok) return;
        const settings = await response.json();
        const signature = systemSettingsSignature(settings);
        if (signature === window.SGPI_SETTINGS_SIGNATURE) return;
        window.SGPI_SETTINGS = settings;
        window.SGPI_SETTINGS_SIGNATURE = signature;
        window.SGPI_SETTINGS_LAST_SYNC = Date.now();
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
        grayscale_mode: Boolean(settings.grayscale_mode),
        global_notice: settings.global_notice || '',
        system_notices: settings.system_notices || []
    }));

    document.documentElement.classList.toggle('grayscale-mode', Boolean(settings.grayscale_mode));

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
            grayscale_mode: Boolean(settings?.grayscale_mode),
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
        // Si storage está bloqueado, el polling mantiene la sincronización.
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
            // Si el evento no trae ajustes válidos, pedimos una copia fresca.
        }
        loadPublicSettings({ force: true });
    });

    const refreshIfStale = () => {
        if (Date.now() - window.SGPI_SETTINGS_LAST_SYNC >= window.SGPI_SETTINGS_SYNC_INTERVAL) {
            loadPublicSettings({ force: true });
        }
    };
    window.addEventListener('focus', refreshIfStale, { passive: true });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshIfStale();
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

    const roleId = Number(user?.active_profile_id || user?.perfil_id || 0);
    const pathname = <?= json_encode($_SERVER['PHP_SELF'] ?? '/') ?>;
    const isIndex = pathname === '/' || pathname.endsWith('/index.php');
    const isDashboard = /\/pages\/(admin|teacher|student)\/dashboard\.php$/.test(pathname);

    return {
        isIndex,
        isDashboard,
        authenticated: Boolean(window.SGPI_SESSION?.authenticated && user),
        role: [1, 4].includes(roleId) ? 'admin' : (roleId === 2 ? 'teacher' : (roleId === 3 ? 'student' : 'public'))
    };
}

// Una fecha sola ("2026-11-12") es un día local; new Date() la tomaría como medianoche UTC (el día anterior en México).
function noticeDay(value) {
    return /^\d{4}-\d{2}-\d{2}$/.test(String(value || '')) ? new Date(`${value}T00:00:00`) : new Date(value);
}

// Vigencia completa: "Vigente del 12 nov al 13 nov 2026", "desde…", "hasta…" o sin fechas.
function noticeValidityText(notice) {
    const day = value => noticeDay(value).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
    if (notice.starts_at && notice.ends_at) return `Vigente del ${day(notice.starts_at)} al ${day(notice.ends_at)}`;
    if (notice.ends_at) return `Vigente hasta el ${day(notice.ends_at)}`;
    if (notice.starts_at) return `Vigente desde el ${day(notice.starts_at)}, sin fecha de cierre`;
    return 'Sin fecha de cierre';
}

// Cuándo se publicó y, si se modificó después, cuándo (con hora).
function noticePublishedText(notice) {
    const moment = value => new Date(value).toLocaleString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    if (!notice.created_at && !notice.updated_at) return '';
    if (notice.updated_at && notice.created_at && notice.updated_at !== notice.created_at) return `Publicado ${moment(notice.created_at)} · actualizado ${moment(notice.updated_at)}`;
    return `Publicado ${moment(notice.updated_at || notice.created_at)}`;
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
            if (notice.starts_at && noticeDay(notice.starts_at) > today) return false;
            if (notice.ends_at && noticeDay(notice.ends_at) < today) return false;
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
        return `
            <article class="notification-item notification-item-${type}">
                <span class="notification-icon"><i class="bi ${notificationIcon(type)}"></i></span>
                <div>
                    <strong>${title}</strong>
                    <p>${message}</p>
                    <small>${escapeToastHtml(noticeValidityText(notice))}</small>
                    ${noticePublishedText(notice) ? `<small>${escapeToastHtml(noticePublishedText(notice))}</small>` : ''}
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
        // Si storage está bloqueado, el contador solo se limpia durante esta vista.
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


function queueSystemNoticeToasts(notices) {
    if (!Array.isArray(notices) || !notices.length) return;

    // Cada aviso nuevo o modificado (otra marca de actualización) se muestra una vez, en cualquier pantalla y dentro de
    // su vigencia; el aviso de cambios en vivo (live.js) lo trae al momento en que se publica.
    const seenNoticeIds = readSeenNoticeIds();
    const applicable = activeSystemNoticesForCurrentAudience(notices)
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
    const validity = `<br><small class="text-muted">${escapeToastHtml(noticeValidityText(notice))}</small>`;
    Swal.fire({
        toast: true,
        position: 'top-end',
        customClass: {
            popup: 'system-notice-toast'
        },
        icon: iconMap[notice.type] || 'info',
        html: `${title}<span>${escapeToastHtml(notice.message)}</span>${validity}`,
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
    title = '¿Confirmar acción?',
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

// Archivo histórico: lo archivado deja de verse hasta restaurarlo en Sistema › Archivo histórico.
window.sendToArchive = async function ({ tipo, ids, name }) {
    if (!window.Swal) return false;
    const result = await stableSwalFire({
        title: 'Enviar al archivo histórico',
        text: `${name} dejará de aparecer en el sistema hasta que lo restaures desde el archivo histórico. Lo publicado en el repositorio público sigue disponible.`,
        input: 'textarea',
        inputPlaceholder: 'Motivo (opcional), por ejemplo: generación 2026 egresada',
        showCancelButton: true,
        confirmButtonText: 'Archivar',
        cancelButtonText: 'Cancelar'
    });
    if (!result.isConfirmed) return false;
    try {
        const response = await api.post('/archive', { tipo, ids: ids.map(String), motivo: String(result.value || '').trim() || null });
        if (typeof swalToast === 'function') swalToast('success', response.message || 'Se envió al archivo histórico.');
        return true;
    } catch (error) {
        Swal.fire('No se archivó', error.message, 'error');
        return false;
    }
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
        text: 'Selecciona la acción que deseas autorizar.',
        icon: 'warning',
        input: 'select',
        inputOptions: {
            DESACTIVAR: 'Desactivar usuario'
        },
        inputPlaceholder: 'Selecciona una acción',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        inputValidator: value => value ? undefined : 'Selecciona una acción'
    });

    return result.isConfirmed ? result.value : null;
};
</script>
<footer class="footer mt-5">
    <div class="container-xl">
        <div class="row">
            <div class="col-lg-8">
                <p class="mb-2"><strong>© <?= date('Y') ?> SGPI ITSSMT</strong></p>
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
<script>
/**
 * Aviso de carga fallida en tableros: sustituye los valores iniciales (0) por
 * un guion para no mostrarlos como datos reales y ofrece reintentar.
 */
window.showDashboardLoadError = function (retry, message = 'No se pudieron cargar los datos del panel. Revisa tu conexión e inténtalo de nuevo.') {
    const anchor = document.querySelector('.dashboard-action-panel');
    if (!anchor) return;
    document.querySelectorAll('.dashboard-stat-value').forEach(value => { value.textContent = '—'; });
    let alert = document.getElementById('dashboardLoadError');
    if (!alert) {
        alert = document.createElement('div');
        alert.id = 'dashboardLoadError';
        alert.className = 'alert alert-danger d-flex flex-wrap align-items-center gap-3 mb-4';
        alert.setAttribute('role', 'alert');
        anchor.before(alert);
    }
    alert.replaceChildren();
    const text = document.createElement('span');
    text.className = 'flex-grow-1';
    text.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>';
    text.append(document.createTextNode(message));
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-outline-danger';
    button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Reintentar';
    button.addEventListener('click', async () => {
        button.disabled = true;
        alert.remove();
        await retry();
    });
    alert.append(text, button);
};
window.clearDashboardLoadError = function () {
    document.getElementById('dashboardLoadError')?.remove();
};
</script>
<script src="<?= asset('/assets/js/skeleton-loader.js') ?>"></script>
<script src="<?= asset('/assets/js/view-transitions.js') ?>"></script>
<script src="<?= asset('/assets/js/responsive.js') ?>"></script>
