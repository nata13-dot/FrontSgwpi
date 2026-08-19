(() => {
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    }[character]));

    function render(response) {
        const badge = document.getElementById('operationalNotificationBadge');
        const list = document.getElementById('operationalNotificationList');
        const summary = document.getElementById('operationalNotificationSummary');
        if (!badge || !list || !summary) return;

        const total = Number(response?.total || 0);
        const critical = Number(response?.critical || 0);
        const alerts = Array.isArray(response?.data) ? response.data : [];
        badge.hidden = total === 0;
        badge.textContent = total > 9 ? '9+' : String(total);
        summary.textContent = total === 0
            ? 'Sin alertas activas'
            : `${total} activa${total === 1 ? '' : 's'}${critical ? ` · ${critical} crítica${critical === 1 ? '' : 's'}` : ''}`;

        if (!alerts.length) {
            list.innerHTML = '<div class="notification-empty"><i class="bi bi-shield-check"></i><span>Operación institucional saludable.</span></div>';
            return;
        }

        list.innerHTML = alerts.map(alert => {
            const severity = alert.severidad === 'critica' ? 'danger' : (alert.severidad === 'advertencia' ? 'warning' : 'info');
            const icon = alert.severidad === 'critica' ? 'bi-exclamation-octagon' : 'bi-exclamation-triangle';
            return `<a class="notification-item notification-item-${severity}" href="/pages/admin/operations.php">
                <span class="notification-icon"><i class="bi ${icon}"></i></span>
                <div>
                    <strong>${escapeHtml(alert.titulo)}</strong>
                    <p>${escapeHtml(alert.detalle)}</p>
                    <small>${alert.estado === 'atendida' ? 'En atención' : 'Requiere atención'}</small>
                </div>
            </a>`;
        }).join('');
    }

    async function load() {
        if (typeof api === 'undefined') return;
        try {
            render(await api.get('/admin/operational-alerts/summary', {_fresh: true, _silent: true}));
        } catch (error) {
            console.warn('No se pudo cargar el estado operativo', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        load();
        window.setInterval(load, 60000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) load();
        });
    });
})();
