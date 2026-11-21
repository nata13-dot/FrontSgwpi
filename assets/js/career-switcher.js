(function () {
    async function persistSession(data) {
        const response = await fetch('/api/set-session.php', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                auth_token: data.access_token,
                user: data.user,
                remember: Boolean(data.remember ?? window.SGPI_SESSION?.remember)
            })
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(result.error || 'No se pudo guardar el nuevo contexto de carrera.');
        }
        return result;
    }

    async function switchCareer(button) {
        const careerId = Number(button.dataset.careerId || 0);
        const currentId = Number(window.SGPI_SESSION?.user?.active_career?.id || 0);
        // El administrador general entra al tablero de la carrera; su inicio es el panel institucional.
        const careerHome = window.SGPI_SESSION?.user?.is_general_admin ? '/pages/admin/dashboard.php' : null;
        const target = button.dataset.redirect || careerHome;
        // Fuera de una carrera, elegir la activa también es «entrar» (pide un token nuevo).
        const outside = window.SGPI_SESSION?.user?.is_general_admin && !window.SGPI_SESSION?.user?.career_entered;
        if (careerId && careerId === currentId && target && !outside) {
            window.location.href = target;
            return;
        }
        if (!careerId || (careerId === currentId && !outside) || button.disabled) return;

        button.disabled = true;
        button.classList.add('is-switching');

        try {
            const response = await fetch(`${window.SGPI_API_BASE_URL}/auth/switch-career`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${window.SGPI_SESSION?.token || ''}`,
                    'X-SGPI-Remember': window.SGPI_SESSION?.remember ? '1' : '0'
                },
                body: JSON.stringify({ carrera_id: careerId })
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.error || 'No fue posible cambiar de carrera.');
            }

            const session = await persistSession(data);
            try {
                sessionStorage.clear();
            } catch (error) {
                // La recarga completa evita reutilizar el estado anterior.
            }
            // El tablero depende del perfil que tenga la persona en la nueva carrera.
            window.location.replace(target || session.dashboard_url || '/index.php');
        } catch (error) {
            button.disabled = false;
            button.classList.remove('is-switching');
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se cambió la carrera',
                    text: error.message
                });
            } else {
                window.alert(error.message);
            }
        }
    }

    // El administrador general vuelve a lo institucional: ya no puede modificar la carrera hasta entrar otra vez.
    async function leaveCareer(button) {
        if (button.disabled) return;
        button.disabled = true;
        try {
            const response = await fetch(`${window.SGPI_API_BASE_URL}/auth/leave-career`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${window.SGPI_SESSION?.token || ''}`,
                    'X-SGPI-Remember': window.SGPI_SESSION?.remember ? '1' : '0'
                }
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.error || 'No fue posible salir de la carrera.');
            await persistSession(data);
            try { sessionStorage.clear(); } catch (error) { /* la recarga basta */ }
            window.location.replace('/pages/admin/general.php');
        } catch (error) {
            button.disabled = false;
            if (window.Swal) Swal.fire({ icon: 'error', title: 'No se salió de la carrera', text: error.message });
            else window.alert(error.message);
        }
    }

    // Usado por el panel institucional: entra a una carrera y abre la página indicada.
    window.SGPICareerSwitch = (careerId, redirect = null) => {
        const button = document.createElement('button');
        button.dataset.careerId = String(careerId);
        if (redirect) button.dataset.redirect = redirect;
        return switchCareer(button);
    };

    function initialize() {
        document.querySelectorAll('.career-switch-option[data-career-id]').forEach(button => {
            button.addEventListener('click', () => switchCareer(button));
        });
        document.querySelectorAll('[data-leave-career]').forEach(button => {
            button.addEventListener('click', () => leaveCareer(button));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
