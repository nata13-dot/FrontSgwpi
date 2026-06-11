(() => {
    const icons = {
        actividad_habilitada: 'bi-journal-plus',
        actividad_entregada: 'bi-cloud-check',
        evaluacion_realizada: 'bi-clipboard-check'
    };

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    function formatDate(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleString('es-MX', {
            day: '2-digit',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function render(response) {
        const list = document.getElementById('activityNotificationList');
        const badge = document.getElementById('activityNotificationBadge');
        const summary = document.getElementById('activityNotificationSummary');
        if (!list || !badge || !summary) return;

        const notifications = Array.isArray(response?.data) ? response.data : [];
        const unread = Number(response?.unread_count || 0);
        badge.hidden = unread === 0;
        badge.textContent = unread > 9 ? '9+' : String(unread);
        summary.textContent = unread === 1 ? '1 notificación sin leer' : `${unread} notificaciones sin leer`;

        if (!notifications.length) {
            list.innerHTML = '<div class="notification-empty"><i class="bi bi-envelope-open"></i><span>No hay actividad reciente.</span></div>';
            return;
        }

        list.innerHTML = notifications.map(notification => `
            <button type="button"
                class="notification-item activity-notification-item ${notification.leida_en ? '' : 'is-unread'}"
                data-notification-id="${notification.id}"
                data-notification-url="${escapeHtml(notification.url)}">
                <span class="notification-icon"><i class="bi ${icons[notification.tipo] || 'bi-info-circle'}"></i></span>
                <span>
                    <strong>${escapeHtml(notification.titulo)}</strong>
                    <p>${escapeHtml(notification.mensaje)}</p>
                    <small>${escapeHtml(formatDate(notification.creada_en))}</small>
                </span>
            </button>
        `).join('');
    }

    async function load() {
        try {
            render(await api.get('/activity-notifications', { _fresh: true }));
        } catch (error) {
            console.warn('No se pudieron cargar las notificaciones de actividad', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const menu = document.getElementById('activityNotificationMenu');
        const list = document.getElementById('activityNotificationList');
        const markAll = document.getElementById('markAllActivityNotifications');
        if (!menu || !list || !markAll || typeof api === 'undefined') return;

        menu.addEventListener('shown.bs.dropdown', load);
        list.addEventListener('click', async event => {
            const item = event.target.closest('[data-notification-id]');
            if (!item) return;
            const url = item.dataset.notificationUrl;
            try {
                await api.put(`/activity-notifications/${item.dataset.notificationId}/read`);
            } finally {
                if (url) window.location.href = url;
            }
        });
        markAll.addEventListener('click', async () => {
            await api.put('/activity-notifications/read-all');
            await load();
        });

        load();
        window.setInterval(load, 30000);
    });
})();
