(() => {
    const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let running = false;
    let queued = null;
    let navigating = false;

    function fallback(target, update) {
        if (!(target instanceof HTMLElement) || reducedMotion()) {
            update();
            return;
        }

        target.getAnimations().forEach(animation => animation.cancel());
        const oldHeight = target.getBoundingClientRect().height;
        const out = target.animate(
            [
                { opacity: 1, transform: 'translateY(0)' },
                { opacity: 0.35, transform: 'translateY(5px)' }
            ],
            { duration: 90, easing: 'ease-out', fill: 'forwards' }
        );

        out.finished.catch(() => {}).then(() => {
            update();
            const newHeight = target.getBoundingClientRect().height;
            target.getAnimations().forEach(animation => animation.cancel());
            target.animate(
                [
                    { opacity: 0.35, transform: 'translateY(-5px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ],
                { duration: 190, easing: 'cubic-bezier(.2,.8,.2,1)', fill: 'both' }
            );

            if (Math.abs(newHeight - oldHeight) > 2) {
                target.animate(
                    [{ minHeight: `${oldHeight}px` }, { minHeight: `${newHeight}px` }],
                    { duration: 220, easing: 'cubic-bezier(.2,.8,.2,1)' }
                );
            }
        });
    }

    function run(update, target = document.querySelector('.main-content .container-xl')) {
        if (typeof update !== 'function') return;
        if (running) {
            queued = { update, target };
            return;
        }
        if (reducedMotion()) {
            update();
            return;
        }

        const complete = () => {
            running = false;
            if (!queued) return;
            const next = queued;
            queued = null;
            requestAnimationFrame(() => run(next.update, next.target));
        };

        running = true;
        if (typeof document.startViewTransition === 'function') {
            const transition = document.startViewTransition(update);
            transition.finished.finally(complete);
            return;
        }

        fallback(target, () => {
            update();
            window.setTimeout(complete, 230);
        });
    }

    function transitionLayer() {
        let layer = document.getElementById('sgpiPageTransition');
        if (layer) return layer;

        layer = document.createElement('div');
        layer.id = 'sgpiPageTransition';
        layer.className = 'sgpi-page-transition';
        layer.setAttribute('aria-hidden', 'true');
        layer.innerHTML = `
            <div class="sgpi-page-transition-panel">
                <span class="sgpi-page-transition-mark"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                <div class="sgpi-page-transition-copy">
                    <span></span><span></span><span></span>
                </div>
            </div>`;
        document.body.appendChild(layer);
        return layer;
    }

    function canNavigate(anchor, event) {
        if (!(anchor instanceof HTMLAnchorElement)) return false;
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if (anchor.target && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download') || anchor.dataset.noPageTransition !== undefined) return false;
        if (anchor.matches('[data-bs-toggle], [data-open-login]')) return false;

        const rawHref = anchor.getAttribute('href') || '';
        if (!rawHref || rawHref === '#' || rawHref.startsWith('javascript:') || rawHref.startsWith('mailto:') || rawHref.startsWith('tel:')) return false;

        const destination = new URL(anchor.href, window.location.href);
        if (destination.origin !== window.location.origin) return false;
        if (destination.pathname === window.location.pathname && destination.search === window.location.search) return false;
        return true;
    }

    function navigate(url) {
        if (navigating) return;
        const destination = new URL(url, window.location.href);
        if (destination.origin !== window.location.origin || reducedMotion()) {
            window.location.assign(destination.href);
            return;
        }

        navigating = true;
        sessionStorage.setItem('sgpi-page-transition', '1');
        transitionLayer();
        document.documentElement.classList.add('sgpi-page-leaving');
        window.setTimeout(() => window.location.assign(destination.href), 170);
    }

    function preparePageEntry() {
        transitionLayer();
        const fromInternalNavigation = sessionStorage.getItem('sgpi-page-transition') === '1';
        sessionStorage.removeItem('sgpi-page-transition');
        if (!fromInternalNavigation || reducedMotion()) return;

        document.documentElement.classList.add('sgpi-page-entering');
        requestAnimationFrame(() => requestAnimationFrame(() => {
            document.documentElement.classList.add('sgpi-page-entered');
            window.setTimeout(() => {
                document.documentElement.classList.remove('sgpi-page-entering', 'sgpi-page-entered');
            }, 420);
        }));
    }

    window.SGPIViewTransition = {
        run,
        navigate
    };

    document.addEventListener('click', event => {
        const anchor = event.target.closest('a[href]');
        if (!canNavigate(anchor, event)) return;
        event.preventDefault();
        navigate(anchor.href);
    });

    preparePageEntry();
    window.addEventListener('pageshow', () => {
        navigating = false;
        document.documentElement.classList.remove('sgpi-page-leaving');
    });
})();
