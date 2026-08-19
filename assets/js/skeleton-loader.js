(() => {
    const processedAttribute = 'data-skeleton-processed';
    let pendingRequests = 0;

    function line(width = '100%', height = '0.85rem') {
        return `<span class="skeleton-line" style="--skeleton-width:${width};--skeleton-height:${height}"></span>`;
    }

    function tableCellSkeleton() {
        return `
            <div class="skeleton-table" aria-label="Cargando contenido" role="status">
                ${Array.from({ length: 5 }, (_, index) => `
                    <div class="skeleton-table-row">
                        <span class="skeleton-avatar skeleton-avatar-sm"></span>
                        <div class="skeleton-table-copy">
                            ${line(index % 2 ? '68%' : '82%', '0.9rem')}
                            ${line(index % 2 ? '46%' : '58%', '0.68rem')}
                        </div>
                        ${line(index % 2 ? '62%' : '78%')}
                        <span class="skeleton-pill"></span>
                        <div class="skeleton-actions">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                `).join('')}
            </div>`;
    }

    function cardSkeleton(count = 3) {
        return `
            <div class="skeleton-card-grid" aria-label="Cargando contenido" role="status">
                ${Array.from({ length: count }, () => `
                    <div class="skeleton-card">
                        <div class="skeleton-card-heading">
                            <span class="skeleton-avatar"></span>
                            <div>
                                ${line('72%', '1rem')}
                                ${line('46%', '0.7rem')}
                            </div>
                        </div>
                        <span class="skeleton-card-media"></span>
                        ${line('94%')}
                        ${line('76%')}
                        <div class="skeleton-card-footer">
                            <span class="skeleton-pill"></span>
                            <span class="skeleton-button"></span>
                        </div>
                    </div>
                `).join('')}
            </div>`;
    }

    function blockSkeleton() {
        return `
            <div class="skeleton-block" aria-label="Cargando contenido" role="status">
                <div class="skeleton-block-heading">
                    <span class="skeleton-avatar"></span>
                    <div>
                        ${line('44%', '1.05rem')}
                        ${line('28%', '0.72rem')}
                    </div>
                </div>
                ${line('96%')}
                ${line('86%')}
                ${line('64%')}
            </div>`;
    }

    function replaceLoadingElement(element) {
        if (!(element instanceof HTMLElement) || element.hasAttribute(processedAttribute)) return;
        if (element.closest('[aria-busy="false"], [data-skeleton-disabled]')) return;
        if (element.closest('button, .btn, select, option, .swal2-container, .badge, .card-header, .modal-header, thead, th, nav, [role="tablist"]')) return;

        const tableCell = element.closest('td');
        if (tableCell) {
            tableCell.setAttribute(processedAttribute, '1');
            tableCell.setAttribute('aria-busy', 'true');
            tableCell.innerHTML = tableCellSkeleton();
            return;
        }

        const container = element.closest(
            '[data-loading-container], .dashboard-empty, .text-center.py-5, .text-center.py-4, .text-center.py-3'
        ) || element;
        if (!container || container.hasAttribute(processedAttribute)) return;

        container.setAttribute(processedAttribute, '1');
        container.setAttribute('aria-busy', 'true');
        const useCards = container.id && /(list|grid|cards|container|body)/i.test(container.id);
        container.innerHTML = useCards ? cardSkeleton() : blockSkeleton();
    }

    function scan(root = document) {
        if (!(root instanceof Document || root instanceof Element)) return;
        if (root instanceof Element && root.closest('[data-skeleton-disabled]')) return;

        const processed = [
            ...(root instanceof Element && root.hasAttribute(processedAttribute) ? [root] : []),
            ...root.querySelectorAll(`[${processedAttribute}]`)
        ];
        processed.forEach(element => {
            if (element.querySelector('.skeleton-line, .skeleton-card, .skeleton-table')) return;
            element.removeAttribute(processedAttribute);
            element.removeAttribute('aria-busy');
        });
        root.querySelectorAll('.spinner-border:not(.spinner-border-sm), .spinner-custom').forEach(replaceLoadingElement);
        root.querySelectorAll('p, div').forEach(element => {
            if (element.children.length > 1 || element.hasAttribute(processedAttribute)) return;
            const text = element.textContent.trim().replace(/\s+/g, ' ');
            if (/^cargando(?:\.\.\.|…)?$/i.test(text) || /^cargando\s+[\p{L}\s]+(?:\.\.\.|…)?$/iu.test(text)) {
                replaceLoadingElement(element);
            }
        });
    }

    function setRequestState(delta) {
        pendingRequests = Math.max(0, pendingRequests + delta);
        document.documentElement.classList.toggle('sgpi-api-loading', pendingRequests > 0);
    }

    window.SGPISkeleton = {
        lines: blockSkeleton,
        cards: cardSkeleton,
        table: tableCellSkeleton,
        show(element, type = 'block') {
            if (!(element instanceof HTMLElement)) return;
            const templates = { block: blockSkeleton, cards: cardSkeleton, table: tableCellSkeleton };
            element.innerHTML = (templates[type] || blockSkeleton)();
            element.setAttribute('aria-busy', 'true');
        },
        hide(element) {
            if (element instanceof HTMLElement) element.removeAttribute('aria-busy');
        },
        scan
    };

    document.addEventListener('sgpi:request-start', () => setRequestState(1));
    document.addEventListener('sgpi:request-end', () => setRequestState(-1));
    document.addEventListener('DOMContentLoaded', () => {
        scan();
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
                if (node instanceof Element) scan(node.parentElement || node);
            }));
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
