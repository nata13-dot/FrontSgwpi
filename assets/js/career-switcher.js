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

        if (!response.ok) {
            throw new Error('No se pudo guardar el nuevo contexto de carrera.');
        }
    }

    async function switchCareer(button) {
        const careerId = Number(button.dataset.careerId || 0);
        const currentId = Number(window.SGPI_SESSION?.user?.active_career?.id || 0);
        if (!careerId || careerId === currentId || button.disabled) return;

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

            await persistSession(data);
            try {
                sessionStorage.clear();
            } catch (error) {
                // La recarga completa evita reutilizar el estado anterior.
            }
            window.location.replace('/pages/admin/dashboard.php');
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

    function initialize() {
        document.querySelectorAll('.career-switch-option[data-career-id]').forEach(button => {
            button.addEventListener('click', () => switchCareer(button));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
