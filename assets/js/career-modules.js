(() => {
    async function applyCareerModules() {
        if (!window.SGPI_SESSION?.token || !window.SGPI_API_BASE_URL) return;

        try {
            const response = await fetch(`${window.SGPI_API_BASE_URL}/career/modules`, {
                headers: {
                    Accept: 'application/json',
                    Authorization: `Bearer ${window.SGPI_SESSION.token}`
                }
            });
            if (!response.ok) return;

            const payload = await response.json();
            const modules = Array.isArray(payload) ? payload : (payload.data || payload.modules || []);
            const enabled = new Map(modules.map(module => [module.modulo, Boolean(module.habilitado)]));

            document.querySelectorAll('[data-career-module]').forEach(element => {
                const moduleNames = element.dataset.careerModule.split(',').map(value => value.trim());
                const visible = moduleNames.some(module => enabled.get(module) !== false);
                element.hidden = !visible;
                element.classList.toggle('d-none', !visible);
            });

            window.dispatchEvent(new CustomEvent('sgpi:career-modules', {
                detail: {modules, enabled}
            }));
        } catch (error) {
            // El backend conserva la protección autoritativa.
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyCareerModules);
    } else {
        applyCareerModules();
    }
})();
