/**
 * Módulo de Autenticación
 * Gestiona login, logout y verificación de sesión
 */

class AuthManager {
    constructor() {
        if (window.axios) {
            window.axios.defaults.withCredentials = true;
        }

        this.token = localStorage.getItem('auth_token');
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
    }

    /**
     * Iniciar sesión
     */
    async login(id, password) {
        try {
            const response = await axios.post(`${API_BASE_URL}/auth/login`, {
                id,
                password
            });

            this.token = response.data.access_token;
            this.user = response.data.user;

            localStorage.setItem('auth_token', this.token);
            localStorage.setItem('user', JSON.stringify(this.user));

            return { success: true, data: response.data };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.error || 'Error al iniciar sesión'
            };
        }
    }

    /**
     * Cerrar sesión
     */
    async logout() {
        try {
            await axios.post(`${API_BASE_URL}/auth/logout`, {}, {
                headers: { Authorization: `Bearer ${this.token}` }
            });
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
        } finally {
            this.token = null;
            this.user = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/pages/logout.php';
        }
    }

    /**
     * Verificar si está autenticado
     */
    isAuthenticated() {
        return !!this.token;
    }

    /**
     * Obtener usuario actual
     */
    getCurrentUser() {
        return this.user;
    }

    /**
     * Verificar si es admin
     */
    isAdmin() {
        return this.user?.perfil_id === 1;
    }

    /**
     * Verificar si es profesor
     */
    isTeacher() {
        return this.user?.perfil_id === 2;
    }

    /**
     * Verificar si es estudiante
     */
    isStudent() {
        return this.user?.perfil_id === 3;
    }

    /**
     * Obtener token
     */
    getToken() {
        return this.token;
    }

    /**
     * Refrescar token JWT sin sacar al usuario de la pantalla actual.
     */
    async refreshToken() {
        if (!this.token) {
            throw new Error('No hay token para refrescar.');
        }

        const response = await fetch(`${API_BASE_URL}/auth/refresh`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.token}`
            }
        });

        if (!response.ok) {
            throw new Error('No se pudo refrescar la sesion.');
        }

        const data = await response.json();
        if (!data.access_token) {
            throw new Error('Respuesta de sesion invalida.');
        }

        this.token = data.access_token;
        localStorage.setItem('auth_token', this.token);

        try {
            await fetch('/api/set-session.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ auth_token: this.token, user: this.user })
            });
        } catch (error) {
            // La sesion PHP se revalidara al navegar; el token del API ya quedo actualizado.
        }

        return this.token;
    }

    clearLocalSession() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        try {
            sessionStorage.clear();
        } catch (error) {
            // Ignorar navegadores que bloquean storage.
        }
    }
}

// Instancia global
const auth = new AuthManager();
