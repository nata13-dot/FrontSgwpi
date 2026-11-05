// Asistente de ayuda y guía de inicio. Los textos y la comparación de preguntas viven en la API
// (config/help.php), así que la app y la web responden lo mismo; no intervienen servicios externos.
(() => {
    const TARGETS = {
        menu: ['#appSidebar', '.navbar-sidebar-toggle'],
        search: ['.global-search', '[data-global-search-open]'],
        career: ['.career-switcher'],
        notifications: ['#activityNotificationMenu'],
        account: ['#userMenu'],
        help: ['.help-fab']
    };
    const page = window.location.pathname;
    let tour = [];

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character]));
    const currentUser = () => (typeof auth !== 'undefined' ? auth.getCurrentUser?.() : null) || {};
    const tourKey = () => `sgpi-tour-v1-${currentUser().id}-${currentUser().active_profile_id ?? currentUser().perfil_id}`;
    const tourSeen = () => { try { return localStorage.getItem(tourKey()) === '1'; } catch { return true; } };
    const markSeen = () => { try { localStorage.setItem(tourKey(), '1'); } catch { /* Sin almacenamiento, la guía sólo se repite. */ } };

    function visibleTarget(target) {
        // El menú lateral del teléfono existe, pero queda fuera de la pantalla hasta abrirlo.
        return (TARGETS[target] || []).map(selector => document.querySelector(selector)).find(element => {
            const box = element?.getBoundingClientRect();
            return box && box.width > 0 && box.right > 0 && box.left < window.innerWidth;
        });
    }

    // ---------- Guía de inicio
    function startTour() {
        const steps = tour.filter(step => !step.target || visibleTarget(step.target));
        if (!steps.length) return;
        closePanel();

        let index = 0;
        const layer = document.createElement('div');
        layer.className = 'help-tour-layer';
        layer.setAttribute('role', 'dialog');
        layer.setAttribute('aria-modal', 'true');
        layer.setAttribute('aria-label', 'Guía de inicio');
        document.body.append(layer);

        const finish = () => {
            markSeen();
            layer.remove();
            document.removeEventListener('keydown', onKey);
            window.removeEventListener('resize', draw);
        };
        const onKey = event => { if (event.key === 'Escape') finish(); };

        function draw() {
            const step = steps[index];
            const last = index === steps.length - 1;
            const box = step.target ? visibleTarget(step.target)?.getBoundingClientRect() : null;
            const pad = 6;
            let cardStyle = '';
            if (box) {
                const tall = box.height > window.innerHeight * 0.5;
                const below = box.top + box.height / 2 < window.innerHeight / 2;
                cardStyle = tall ? `top:24px;left:${Math.min(box.right + 18, window.innerWidth - 336)}px`
                    : below ? `top:${box.bottom + 16}px;right:16px`
                        : `bottom:${window.innerHeight - box.top + 16}px;right:16px`;
            }
            layer.classList.toggle('centered', !box);
            layer.innerHTML = `
                ${box ? `<div class="help-tour-spotlight" style="top:${box.top - pad}px;left:${box.left - pad}px;width:${box.width + pad * 2}px;height:${box.height + pad * 2}px"></div>` : '<div class="help-tour-backdrop"></div>'}
                <section class="help-tour-card" tabindex="-1" style="${cardStyle}">
                    <small>Paso ${index + 1} de ${steps.length}</small>
                    <h3>${escapeHtml(step.title)}</h3>
                    <p>${escapeHtml(step.text)}</p>
                    <footer>
                        <button type="button" class="btn btn-link btn-sm text-muted p-0" data-tour="close">${last ? 'Cerrar' : 'Omitir guía'}</button>
                        <span>
                            ${index > 0 ? '<button type="button" class="btn btn-outline-secondary btn-sm" data-tour="back">Atrás</button>' : ''}
                            <button type="button" class="btn btn-primary btn-sm" data-tour="next">${last ? 'Terminar' : 'Siguiente'}</button>
                        </span>
                    </footer>
                </section>`;
            layer.querySelector('.help-tour-card').focus();
        }

        layer.addEventListener('click', event => {
            const action = event.target.closest('[data-tour]')?.dataset.tour;
            if (action === 'close' || (action === 'next' && index === steps.length - 1)) finish();
            else if (action === 'next') { index += 1; draw(); }
            else if (action === 'back') { index -= 1; draw(); }
        });
        document.addEventListener('keydown', onKey);
        window.addEventListener('resize', draw);
        draw();
    }

    // ---------- Asistente
    const fab = document.createElement('button');
    fab.type = 'button';
    fab.className = 'help-fab';
    fab.setAttribute('aria-label', 'Ayuda');
    fab.title = 'Ayuda';
    fab.innerHTML = '<i class="bi bi-question-lg" aria-hidden="true"></i>';

    const panel = document.createElement('section');
    panel.className = 'help-panel';
    panel.hidden = true;
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Asistente de ayuda');
    panel.innerHTML = `
        <header>
            <div><strong>Asistente de ayuda</strong><small>Respuestas sobre cómo usar el SGPI.</small></div>
            <button type="button" class="btn-close" aria-label="Cerrar" data-help="close"></button>
        </header>
        <div class="help-chat" aria-live="polite"></div>
        <form class="help-compose">
            <input class="form-control" maxlength="200" placeholder="Escribe tu pregunta..." aria-label="Escribe tu pregunta">
            <button class="btn btn-primary" type="submit" aria-label="Enviar pregunta"><i class="bi bi-send" aria-hidden="true"></i></button>
        </form>`;
    const chat = panel.querySelector('.help-chat');
    const form = panel.querySelector('form');
    const field = form.querySelector('input');
    const topics = new Map();

    const remember = topic => { topics.set(topic.id, topic); return topic; };
    const suggestions = list => `<div class="help-suggestions">${list.map(topic => `<button type="button" data-topic="${escapeHtml(remember(topic).id)}">${escapeHtml(topic.question)}</button>`).join('')}</div>`;

    function addBubble(from, html) {
        const bubble = document.createElement('div');
        bubble.className = `help-bubble ${from}`;
        bubble.innerHTML = html;
        chat.append(bubble);
        bubble.scrollIntoView({ block: 'nearest' });
        return bubble;
    }

    function answerHtml(topic, withTitle = false) {
        const link = topic.web && !topic.here && topic.web !== page
            ? `<a class="btn btn-outline-primary btn-sm" href="${escapeHtml(topic.web)}">Ir a la pantalla <i class="bi bi-arrow-right" aria-hidden="true"></i></a>` : '';
        return `${withTitle ? `<strong>${escapeHtml(topic.question)}</strong>` : ''}<ol class="help-steps">${topic.steps.map(step => `<li>${escapeHtml(step)}</li>`).join('')}</ol>${link}`;
    }

    function showTopic(topic) {
        addBubble('user', `<p>${escapeHtml(topic.question)}</p>`);
        addBubble('bot', answerHtml(topic));
    }

    let loaded = false;
    async function loadHelp() {
        const data = await api.get('/help', { page, _silent: true, _cache_ttl: 300000 });
        tour = data.tour || [];
        if (!loaded) {
            loaded = true;
            const first = (currentUser().nombres || '').split(' ')[0];
            addBubble('bot', `<p>Hola${first ? `, ${escapeHtml(first)}` : ''}. Pregúntame cómo hacer algo o elige un tema:</p>
                ${suggestions(data.topics || [])}
                <button type="button" class="btn btn-outline-primary btn-sm" data-help="tour"><i class="bi bi-compass" aria-hidden="true"></i> Ver la guía de inicio</button>`);
        }
        return data;
    }

    function openPanel() {
        panel.hidden = false;
        loadHelp().catch(() => { if (!loaded) addBubble('bot', '<p>No se pudo cargar la ayuda. Intenta de nuevo.</p>'); });
        field.focus();
    }
    function closePanel() { panel.hidden = true; }

    fab.addEventListener('click', () => (panel.hidden ? openPanel() : closePanel()));
    panel.addEventListener('click', event => {
        const action = event.target.closest('[data-help]')?.dataset.help;
        if (action === 'close') closePanel();
        if (action === 'tour') startTour();
        if (action === 'search') { closePanel(); window.SGPIGlobalSearch?.open(); }
        const topic = topics.get(event.target.closest('[data-topic]')?.dataset.topic);
        if (topic) showTopic(topic);
    });
    panel.addEventListener('keydown', event => { if (event.key === 'Escape') { closePanel(); fab.focus(); } });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const question = field.value.trim();
        if (!question) return;
        field.value = '';
        addBubble('user', `<p>${escapeHtml(question)}</p>`);
        const waiting = addBubble('bot', '<p class="text-muted mb-0">Buscando...</p>');
        try {
            const data = await api.get('/help', { q: question, page, _silent: true, _fresh: true });
            const [best, ...others] = data.topics || [];
            waiting.innerHTML = best
                ? `${answerHtml(best, true)}${others.length ? `<small class="d-block mt-2 text-muted">También puede servirte:</small>${suggestions(others.slice(0, 3))}` : ''}`
                : `<p>No encontré una respuesta para eso. Intenta con otras palabras, o búscalo en el sistema. Si es un problema con tu cuenta o tus datos, acude a tu jefatura.</p>
                   <button type="button" class="btn btn-outline-primary btn-sm" data-help="search"><i class="bi bi-search" aria-hidden="true"></i> Abrir el buscador</button>`;
        } catch {
            waiting.innerHTML = '<p>No se pudo consultar la ayuda. Intenta de nuevo.</p>';
        }
        waiting.scrollIntoView({ block: 'start' });
    });

    document.addEventListener('DOMContentLoaded', async () => {
        document.body.append(fab, panel);
        // La primera vez que entra con este perfil en este navegador; la vista simulada no la activa.
        if (document.querySelector('.view-as-banner') || tourSeen()) return;
        try {
            await loadHelp();
            if (tour.length) startTour();
        } catch { /* La ayuda es opcional; el botón puede volver a intentarlo. */ }
    });
})();
