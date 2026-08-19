(() => {
    if (window.SGPI_RESPONSIVE_READY) return;
    window.SGPI_RESPONSIVE_READY = true;

    const MOBILE_QUERY = '(max-width: 768px)';
    const isMobile = () => window.matchMedia(MOBILE_QUERY).matches;
    let enhancementFrame = null;

    function wrapTables() {
        document.querySelectorAll('table').forEach(table => {
            const currentWrapper = table.closest('.table-responsive');
            if (currentWrapper) {
                prepareTableWrapper(currentWrapper, table);
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            prepareTableWrapper(wrapper, table);
        });
    }

    function prepareTableWrapper(wrapper, table) {
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', table.getAttribute('aria-label') || 'Tabla de datos');
        wrapper.tabIndex = 0;
    }

    function normalizeHeaderText(header) {
        if (!header || header.classList.contains('d-none')) return '';
        return header.textContent.replace(/\s+/g, ' ').trim();
    }

    function enhanceResponsiveTables(root = document) {
        root.querySelectorAll('table').forEach(table => {
            const mode = table.dataset.mobileTable || 'cards';
            const wrapper = table.closest('.table-responsive');
            if (mode === 'scroll') {
                table.classList.add('mobile-scroll-table');
                table.classList.remove('mobile-card-table');
                wrapper?.classList.add('mobile-scroll-region');
                return;
            }

            const headers = [...table.querySelectorAll('thead th')].map(normalizeHeaderText);
            if (!headers.some(Boolean)) {
                table.classList.add('mobile-scroll-table');
                wrapper?.classList.add('mobile-scroll-region');
                return;
            }

            table.classList.add('mobile-card-table');
            table.classList.remove('mobile-scroll-table');
            wrapper?.classList.remove('mobile-scroll-region');

            table.querySelectorAll('tbody tr').forEach(row => {
                const cells = [...row.children].filter(cell => (
                    cell.tagName === 'TD' && !cell.classList.contains('mobile-details-control')
                ));
                const isStatusRow = cells.length === 1 && Number(cells[0].colSpan || 1) > 1;
                row.classList.toggle('mobile-table-status-row', isStatusRow);
                let summaryCells = 0;
                let detailCells = 0;

                cells.forEach((cell, index) => {
                    cell.classList.remove(
                        'mobile-cell-unlabelled',
                        'mobile-table-actions',
                        'mobile-table-primary',
                        'mobile-table-summary',
                        'mobile-table-detail'
                    );
                    if (isStatusRow) {
                        cell.removeAttribute('data-label');
                        return;
                    }

                    const label = headers[index] || '';
                    if (label) {
                        cell.dataset.label = label;
                    } else {
                        cell.classList.add('mobile-cell-unlabelled');
                    }

                    if (/acciones?/i.test(label)) {
                        cell.classList.add('mobile-table-actions');
                        return;
                    }

                    if (label && summaryCells < 2) {
                        cell.classList.add(summaryCells === 0 ? 'mobile-table-primary' : 'mobile-table-summary');
                        summaryCells++;
                    } else if (label) {
                        cell.classList.add('mobile-table-detail');
                        detailCells++;
                    }
                });

                if (isStatusRow || !detailCells) {
                    row.querySelector('.mobile-details-control')?.remove();
                    row.classList.remove('mobile-details-expanded');
                    return;
                }

                let controlCell = row.querySelector('.mobile-details-control');
                if (!controlCell) {
                    controlCell = document.createElement('td');
                    controlCell.className = 'mobile-details-control';
                    controlCell.colSpan = Math.max(headers.length, 1);
                    controlCell.innerHTML = `
                        <button type="button" class="mobile-details-toggle" aria-expanded="false">
                            <span><i class="bi bi-eye"></i> Ver detalles</span>
                            <i class="bi bi-chevron-down mobile-details-chevron" aria-hidden="true"></i>
                        </button>`;
                    row.appendChild(controlCell);
                }
            });
        });
    }

    function initMobileTableDetails() {
        if (window.SGPI_MOBILE_TABLE_DETAILS_READY) return;
        window.SGPI_MOBILE_TABLE_DETAILS_READY = true;

        document.addEventListener('click', event => {
            const button = event.target.closest('.mobile-details-toggle');
            if (!button) return;

            const row = button.closest('tr');
            if (!row) return;

            const expanded = row.classList.toggle('mobile-details-expanded');
            button.setAttribute('aria-expanded', String(expanded));
            button.querySelector('span').innerHTML = expanded
                ? '<i class="bi bi-eye-slash"></i> Ocultar detalles'
                : '<i class="bi bi-eye"></i> Ver detalles';
        });
    }

    function scheduleEnhancements() {
        if (enhancementFrame) return;
        enhancementFrame = window.requestAnimationFrame(() => {
            enhancementFrame = null;
            wrapTables();
            enhanceResponsiveTables();
            markWideContent();
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
        const backdrop = document.createElement('button');
        let collapseTimer = null;

        backdrop.type = 'button';
        backdrop.className = 'sidebar-mobile-backdrop';
        backdrop.setAttribute('aria-label', 'Cerrar menu lateral');
        document.body.appendChild(backdrop);

        sidebar.querySelectorAll('.sidebar-item, .sidebar-group summary').forEach(item => {
            const label = item.querySelector('span')?.textContent?.trim();
            if (label && !item.title) item.title = label;
        });

        const syncMobileSidebar = open => {
            sidebar.classList.toggle('show', open);
            backdrop.classList.toggle('show', open);
            document.body.classList.toggle('sidebar-mobile-open', open);
            toggleButtons.forEach(button => button.setAttribute('aria-expanded', String(open)));
        };

        const closeMobileSidebar = () => {
            if (!desktopQuery.matches) syncMobileSidebar(false);
        };

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
                syncMobileSidebar(false);
                return;
            }

            syncMobileSidebar(false);
            collapse();
        };

        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (!desktopQuery.matches) {
                    syncMobileSidebar(!sidebar.classList.contains('show'));
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
        sidebar.querySelectorAll('a.sidebar-item').forEach(link => {
            link.addEventListener('click', closeMobileSidebar);
        });
        backdrop.addEventListener('click', closeMobileSidebar);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeMobileSidebar();
        });

        if (desktopQuery.addEventListener) {
            desktopQuery.addEventListener('change', sync);
        } else {
            desktopQuery.addListener(sync);
        }

        sync();
    }

    function initResponsiveHelpers() {
        wrapTables();
        enhanceResponsiveTables();
        closeNavbarOnSelection();
        improveMobileDialogs();
        markWideContent();
        syncMobileClass();
        initSidebarShell();
        initMobileTableDetails();
    }

    document.addEventListener('DOMContentLoaded', initResponsiveHelpers);
    window.addEventListener('resize', syncMobileClass, { passive: true });

    const observer = new MutationObserver(mutations => {
        if (!mutations.some(mutation => mutation.addedNodes.length)) return;
        scheduleEnhancements();
        closeNavbarOnSelection();
        improveMobileDialogs();
    });

    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
