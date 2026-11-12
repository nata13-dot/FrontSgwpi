/**
 * Pulso de cambios (GET /live, igual que LiveSync.jsx de la app): cada 10 s con la pestaña visible y al volver a ella.
 * Cuando cambia la huella de un canal se olvida del caché lo que quedó viejo y se avisa con el evento `sgpi:live`
 * ({ detail: { channels } }); cada página recarga sólo su sección, sin vaciar la vista.
 */
(function () {
    const PULSE_MS = 10000;
    // Rutas del caché de api.js que dependen de cada canal.
    const RESOURCES = {
        notifications: ['/activity-notifications'],
        notices: ['/settings/public', '/settings/current', '/notices'],
        registration_requests: ['/self-registration/requests', '/period-center'],
        proposals: ['/proposal/projects', '/proposal/student-status', '/projects', '/period-center', '/dashboard'],
        join_requests: ['/proposal/student-status', '/proposal/projects'],
        projects: ['/projects', '/dashboard'],
        deliverables: ['/deliverables', '/my-deliverables', '/teacher/deliverables-matrix', '/dashboard']
    };
    let last = null;
    let busy = false;

    async function pulse() {
        if (busy || document.hidden || typeof api === 'undefined' || !auth?.getToken?.()) return;
        busy = true;
        try {
            const response = await api.get('/live', { _cache_ttl: 0, _fresh: true, _silent: true });
            const channels = response?.channels || {};
            const previous = last;
            last = channels;
            if (!previous) return;
            const changed = Object.keys(channels).filter(channel => previous[channel] !== undefined && previous[channel] !== channels[channel]);
            if (!changed.length) return;
            api.forgetEndpoints([...new Set(changed.flatMap(channel => RESOURCES[channel] || []))]);
            window.dispatchEvent(new CustomEvent('sgpi:live', { detail: { channels: changed } }));
        } catch (error) {
            // El siguiente pulso reintenta.
        } finally {
            busy = false;
        }
    }

    // Con un modal o alerta abiertos, o escribiendo en un campo, se espera para no interrumpir.
    const interacting = () => Boolean(document.querySelector('.modal.show, .swal2-container'))
        || Boolean(document.activeElement?.matches?.('input, textarea, select') && document.activeElement.closest('main'));

    /** Para las páginas: llama `reload` cuando cambie alguno de estos canales, conservando la posición de la vista. */
    window.onLiveChange = function (channels, reload) {
        let waiting = false;
        const run = async () => {
            if (interacting()) {
                if (!waiting) { waiting = true; window.setTimeout(() => { waiting = false; run(); }, 3000); }
                return;
            }
            const scroll = window.scrollY;
            try { await reload(); } catch (error) { /* El siguiente cambio reintenta. */ }
            window.scrollTo({ top: scroll, behavior: 'instant' });
        };
        window.addEventListener('sgpi:live', event => {
            if ((event.detail?.channels || []).some(channel => channels.includes(channel))) run();
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        pulse();
        window.setInterval(pulse, PULSE_MS);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) pulse(); });
    });
})();
