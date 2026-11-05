(() => {
    const icons = {
        actividad_habilitada: 'bi-journal-plus',
        actividad_entregada: 'bi-cloud-check',
        evaluacion_realizada: 'bi-clipboard-check',
        entrega_calificada: 'bi-patch-check',
        recordatorio_entrega: 'bi-alarm',
        recordatorio_evaluacion: 'bi-calendar-event',
        sala_asignada: 'bi-door-open',
        presentacion_asignada: 'bi-easel',
        sala_reprogramada: 'bi-calendar-x',
        turno_proximo: 'bi-megaphone',
        solicitud_cuenta: 'bi-person-plus',
        alerta_sistema: 'bi-exclamation-triangle',
        resumen_semanal: 'bi-bar-chart',
        resumen_revision: 'bi-list-check'
    };

    // Los avisos usan las rutas de la app; aquí se traducen a las páginas de este front según el perfil.
    function pageFor(url) {
        const profile = Number(window.SGPI_SESSION?.user?.active_profile_id ?? window.SGPI_SESSION?.user?.perfil_id);
        const role = profile === 3 ? 'student' : (profile === 2 ? 'teacher' : 'admin');
        const pages = {
            '/dashboard': `/pages/${role}/dashboard.php`,
            '/deliverables': { student: '/pages/student/my-deliverables.php', teacher: '/pages/teacher/my-deliverables.php', admin: '/pages/admin/deliverables.php' }[role],
            '/proposals': { student: '/pages/student/proposal-register.php', teacher: '/pages/teacher/proposal-review.php', admin: '/pages/admin/proposal-config.php' }[role],
            '/projects': '/pages/admin/projects.php',
            '/evaluation-rooms': '/pages/admin/evaluation-rooms.php',
            '/evaluations': '/pages/admin/evaluations.php',
            '/companies': '/pages/admin/projects.php',
            '/registration-requests': '/pages/admin/registration-requests.php',
            '/semesters': '/pages/academic-workflows.php?tab=periods',
            '/academic-workflows': '/pages/academic-workflows.php',
            '/operations': '/pages/admin/operations.php'
        };
        return pages[url] || url;
    }

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
                if (url) window.location.href = pageFor(url);
            }
        });
        markAll.addEventListener('click', async () => {
            await api.put('/activity-notifications/read-all');
            await load();
        });

        load();
        // Sin consultas mientras la pestaña está oculta; al volver se actualiza.
        window.setInterval(() => {
            if (!document.hidden) load();
        }, 30000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) load();
        });
    });
})();
