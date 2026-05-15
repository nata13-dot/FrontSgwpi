(() => {
    if (window.SGPI_RESPONSIVE_READY) return;
    window.SGPI_RESPONSIVE_READY = true;

    const MOBILE_QUERY = '(max-width: 768px)';
    const isMobile = () => window.matchMedia(MOBILE_QUERY).matches;

    function wrapTables() {
        document.querySelectorAll('table').forEach(table => {
            if (table.closest('.table-responsive')) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    }

    function closeNavbarOnSelection() {
        document.querySelectorAll('.navbar-collapse .nav-link:not(.dropdown-toggle), .navbar-collapse .dropdown-item').forEach(link => {
            if (link.dataset.responsiveCloseReady) return;
            link.dataset.responsiveCloseReady = '1';

            link.addEventListener('click', () => {
                const collapse = link.closest('.navbar-collapse.show');
                const bootstrapCollapse = window.bootstrap && collapse
                    ? window.bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false })
                    : null;

                if (bootstrapCollapse) {
                    bootstrapCollapse.hide();
                }
            });
        });
    }

    function improveMobileDialogs() {
        document.querySelectorAll('.modal-dialog').forEach(dialog => {
            if (dialog.classList.contains('modal-xl') || dialog.classList.contains('modal-lg')) {
                dialog.classList.add('modal-fullscreen-sm-down');
            }
        });
    }

    function markWideContent() {
        document.querySelectorAll('.card, .list-group-item, .modal-body').forEach(element => {
            element.style.minWidth = '0';
        });
    }

    function syncMobileClass() {
        document.documentElement.classList.toggle('is-mobile-layout', isMobile());
    }

    function initResponsiveHelpers() {
        wrapTables();
        closeNavbarOnSelection();
        improveMobileDialogs();
        markWideContent();
        syncMobileClass();
    }

    document.addEventListener('DOMContentLoaded', initResponsiveHelpers);
    window.addEventListener('resize', syncMobileClass, { passive: true });

    const observer = new MutationObserver(mutations => {
        if (!mutations.some(mutation => mutation.addedNodes.length)) return;
        wrapTables();
        closeNavbarOnSelection();
        improveMobileDialogs();
        markWideContent();
    });

    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
