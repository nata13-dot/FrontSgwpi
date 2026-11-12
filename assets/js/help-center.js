// Asistente de ayuda y guía de inicio. Los temas y la comparación de preguntas viven en la API
// (resources/help), así que la app y la web responden lo mismo; no intervienen servicios externos.
// Sin sesión (data-guest en el script) solo ofrece los temas de acceso y registro.
(() => {
    const TARGETS = {
        menu: ['#appSidebar', '.navbar-sidebar-toggle'],
        search: ['.global-search', '[data-global-search-open]'],
        career: ['.career-switcher'],
        notifications: ['#activityNotificationMenu'],
        account: ['#userMenu'],
        help: ['.help-fab']
    };
    // La página real, no la URL visible: los temas se identifican por su archivo.
    const page = window.SGPI_PAGE || window.location.pathname;
    const guest = Boolean(document.currentScript?.dataset.guest);
    const endpoint = guest ? '/help/public' : '/help';
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
            <div><strong>Asistente de ayuda</strong><small>Elige un tema para ver cómo hacerlo.</small></div>
            <button type="button" class="btn-close" aria-label="Cerrar" data-help="close"></button>
        </header>
        <div class="help-chat" aria-live="polite"></div>
        <footer class="help-footer">
            <button type="button" class="btn btn-outline-primary btn-sm" data-help="menu">Ver todos los temas</button>
            ${guest ? '' : '<button type="button" class="btn btn-outline-primary btn-sm" data-help="search"><i class="bi bi-search" aria-hidden="true"></i> Buscar pantallas</button>'}
        </footer>`;
    // Sin escritura libre: se navega por temas; el pie lleva de vuelta a la lista completa o al buscador.
    const chat = panel.querySelector('.help-chat');

    let categories = [];
    const choices = list => `<div class="help-suggestions">${list.map(item => `<button type="button" data-${item.topics ? 'category' : 'topic'}="${escapeHtml(item.id)}" data-label="${escapeHtml(item.question || item.title)}">${escapeHtml(item.question || item.title)}</button>`).join('')}</div>`;
    const caption = text => `<small class="d-block mt-2 text-muted">${text}</small>`;

    function addBubble(from, html) {
        const bubble = document.createElement('div');
        bubble.className = `help-bubble ${from}`;
        bubble.innerHTML = html;
        chat.append(bubble);
        bubble.scrollIntoView({ block: 'nearest' });
        return bubble;
    }
    const addUser = text => addBubble('user', `<p>${escapeHtml(text)}</p>`);

    function answerHtml(topic, withTitle = false) {
        const link = topic.web && !topic.here && topic.web !== page
            ? `<a class="btn btn-outline-primary btn-sm" href="${escapeHtml(topic.web)}">Ir a la pantalla <i class="bi bi-arrow-right" aria-hidden="true"></i></a>` : '';
        const body = topic.personal
            ? topic.steps.map(line => `<p>${escapeHtml(line)}</p>`).join('')
            : `<ol class="help-steps">${topic.steps.map(step => `<li>${escapeHtml(step)}</li>`).join('')}</ol>`;
        return `${withTitle ? `<strong>${escapeHtml(topic.question)}</strong>` : ''}${body}${link}
            ${topic.next ? `${caption('¿Qué sigue?')}${choices([topic.next])}` : ''}
            ${topic.related?.length ? `${caption('También puede servirte')}${choices(topic.related)}` : ''}`;
    }

    async function respond(params, render) {
        const waiting = addBubble('bot', '<p class="text-muted mb-0">Buscando...</p>');
        try {
            waiting.innerHTML = render(await api.get(endpoint, { ...params, page, _silent: true, _fresh: true }));
        } catch {
            waiting.innerHTML = '<p>No se pudo consultar la ayuda. Intenta de nuevo.</p>';
        }
        waiting.scrollIntoView({ block: 'start' });
    }
    const openTopic = (id, label) => {
        addUser(label);
        respond({ topic: id }, data => answerHtml(data.topic));
    };

    let loaded = false;
    async function loadHelp() {
        const data = await api.get(endpoint, { page, _silent: true, _cache_ttl: 300000 });
        tour = data.tour || [];
        categories = data.categories || [];
        if (!loaded) {
            loaded = true;
            const first = guest ? '' : (currentUser().nombres || '').split(' ')[0];
            addBubble('bot', `<p>Hola${first ? `, ${escapeHtml(first)}` : ''}. ¿En qué te ayudo?</p>
                ${choices(data.suggested || [])}
                ${caption('Todos los temas')}${choices(categories)}
                ${guest ? '' : '<button type="button" class="btn btn-outline-primary btn-sm" data-help="tour"><i class="bi bi-compass" aria-hidden="true"></i> Ver la guía de inicio</button>'}`);
        }
        return data;
    }

    function openPanel() {
        panel.hidden = false;
        loadHelp().catch(() => { if (!loaded) addBubble('bot', '<p>No se pudo cargar la ayuda. Intenta de nuevo.</p>'); });
        panel.querySelector('[data-help="close"]').focus();
    }
    function closePanel() { panel.hidden = true; }

    fab.addEventListener('click', () => (panel.hidden ? openPanel() : closePanel()));
    panel.addEventListener('click', event => {
        const action = event.target.closest('[data-help]')?.dataset.help;
        if (action === 'close') closePanel();
        if (action === 'tour') startTour();
        if (action === 'search') { closePanel(); window.SGPIGlobalSearch?.open(); }
        if (action === 'menu') { addUser('Ver todos los temas'); addBubble('bot', `<p>Elige una sección:</p>${choices(categories)}`); }
        const topic = event.target.closest('[data-topic]');
        if (topic) openTopic(topic.dataset.topic, topic.dataset.label);
        const categoryButton = event.target.closest('[data-category]');
        const category = categories.find(item => item.id === categoryButton?.dataset.category);
        if (category) {
            addUser(category.title);
            addBubble('bot', `<p>Temas de ${escapeHtml(category.title)}:</p>${choices(category.topics)}`);
        }
    });
    panel.addEventListener('keydown', event => { if (event.key === 'Escape') { closePanel(); fab.focus(); } });


    document.addEventListener('DOMContentLoaded', async () => {
        document.body.append(fab, panel);
        // La primera vez que entra con este perfil en este navegador; la vista simulada no la activa.
        if (guest || document.querySelector('.view-as-banner') || tourSeen()) return;
        try {
            await loadHelp();
            if (tour.length) startTour();
        } catch { /* La ayuda es opcional; el botón puede volver a intentarlo. */ }
    });
})();
