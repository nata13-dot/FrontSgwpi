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

    function initSidebarShell() {
        if (window.SGPI_SIDEBAR_READY) return;

        const sidebar = document.getElementById('appSidebar');
        if (!sidebar) return;

        window.SGPI_SIDEBAR_READY = true;
        const desktopQuery = window.matchMedia('(min-width: 769px)');
        const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
        let collapseTimer = null;

        sidebar.querySelectorAll('.sidebar-item, .sidebar-group summary').forEach(item => {
            const label = item.querySelector('span')?.textContent?.trim();
            if (label && !item.title) item.title = label;
        });

        const clearCollapseTimer = () => {
            if (!collapseTimer) return;
            window.clearTimeout(collapseTimer);
            collapseTimer = null;
        };

        const expand = () => {
            if (!desktopQuery.matches) return;
            clearCollapseTimer();
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
            document.documentElement.classList.remove('sgpi-sidebar-collapsed');
        };

        const collapse = () => {
            if (!desktopQuery.matches) return;
            clearCollapseTimer();
            sidebar.classList.add('sidebar-collapsed');
            sidebar.classList.remove('sidebar-expanded');
            document.documentElement.classList.add('sgpi-sidebar-collapsed');
        };

        const scheduleCollapse = (delay = 1400) => {
            if (!desktopQuery.matches) return;
            clearCollapseTimer();
            collapseTimer = window.setTimeout(collapse, delay);
        };

        const sync = () => {
            clearCollapseTimer();
            if (!desktopQuery.matches) {
                sidebar.classList.remove('sidebar-collapsed', 'sidebar-expanded');
                document.documentElement.classList.remove('sgpi-sidebar-collapsed');
                return;
            }

            collapse();
        };

        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (!desktopQuery.matches) {
                    sidebar.classList.toggle('show');
                    return;
                }

                if (sidebar.classList.contains('sidebar-collapsed')) {
                    expand();
                    scheduleCollapse(4500);
                } else {
                    collapse();
                }
            });
        });

        sidebar.addEventListener('mouseenter', expand, { passive: true });
        sidebar.addEventListener('mousemove', () => {
            if (sidebar.classList.contains('sidebar-expanded')) scheduleCollapse(4500);
        }, { passive: true });
        sidebar.addEventListener('focusin', () => {
            expand();
            scheduleCollapse(4500);
        });
        sidebar.addEventListener('mouseleave', () => scheduleCollapse(900), { passive: true });

        if (desktopQuery.addEventListener) {
            desktopQuery.addEventListener('change', sync);
        } else {
            desktopQuery.addListener(sync);
        }

        sync();
    }

    function initResponsiveHelpers() {
        wrapTables();
        closeNavbarOnSelection();
        improveMobileDialogs();
        markWideContent();
        syncMobileClass();
        initSidebarShell();
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
