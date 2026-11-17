/**
 * Paginación común de todas las listas: «Anterior 1 2 3 … 10 Siguiente», con el resumen «Mostrando 1–25 de 240».
 * Uso: SgpiPager.render(contenedor, { current, last, total, from, to, noun: 'usuarios', onPage: (page) => ... })
 * En el teléfono se reduce a anterior · página actual de total · siguiente.
 */
(function () {
    function pages(current, last) {
        const set = new Set([1, last, current - 1, current, current + 1]);
        if (current <= 3) [2, 3, 4].forEach(page => set.add(page));
        if (current >= last - 2) [last - 1, last - 2, last - 3].forEach(page => set.add(page));
        const list = [...set].filter(page => page >= 1 && page <= last).sort((a, b) => a - b);
        const result = [];
        list.forEach((page, index) => {
            if (index && page - list[index - 1] > 1) result.push(page - list[index - 1] === 2 ? page - 1 : null);
            result.push(page);
        });
        return result;
    }

    function render(container, options) {
        const el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;
        const current = Math.max(1, Number(options.current || 1));
        const last = Math.max(1, Number(options.last || 1));
        const total = Number(options.total || 0);
        const from = Number(options.from || (total ? (current - 1) * Number(options.perPage || 0) + 1 : 0));
        const to = Number(options.to || (from && options.perPage ? Math.min(total, from + Number(options.perPage) - 1) : 0));
        const noun = options.noun || 'registros';
        const summary = total
            ? (from && to ? `Mostrando <strong>${from}–${to}</strong> de <strong>${total}</strong> ${noun}` : `<strong>${total}</strong> ${noun}`)
            : '';
        if (last <= 1) {
            el.innerHTML = summary ? `<div class="sgpi-pager single"><span class="sgpi-pager-info">${summary}</span></div>` : '';
            return;
        }
        const buttons = pages(current, last).map(page => page === null
            ? '<span class="sgpi-pager-gap" aria-hidden="true">…</span>'
            : `<button type="button" class="sgpi-pager-num ${page === current ? 'active' : ''}" data-page="${page}" ${page === current ? 'aria-current="page"' : ''} aria-label="Página ${page}">${page}</button>`).join('');
        el.innerHTML = `
            <nav class="sgpi-pager" aria-label="Paginación">
                <span class="sgpi-pager-info">${summary}</span>
                <div class="sgpi-pager-controls">
                    <button type="button" class="sgpi-pager-step" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}><i class="bi bi-chevron-left" aria-hidden="true"></i><span>Anterior</span></button>
                    <span class="sgpi-pager-pages">${buttons}</span>
                    <span class="sgpi-pager-compact">${current} / ${last}</span>
                    <button type="button" class="sgpi-pager-step" data-page="${current + 1}" ${current >= last ? 'disabled' : ''}><span>Siguiente</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </nav>`;
        el.querySelectorAll('[data-page]').forEach(button => button.addEventListener('click', () => {
            const page = Number(button.dataset.page);
            if (page >= 1 && page <= last && page !== current && typeof options.onPage === 'function') options.onPage(page);
        }));
    }

    window.SgpiPager = { render, pages };
})();
