/**
 * Módulo de Autenticación
 * Gestiona login, logout y verificación de sesión
 */

class AuthManager {
    constructor() {
        if (window.axios) {
            window.axios.defaults.withCredentials = true;
        }

        const serverSession = window.SGPI_SESSION || {};
        this.token = serverSession.token || null;
        this.user = serverSession.user || null;
        this.remember = Boolean(serverSession.remember);
        this.refreshPromise = null;
        this.refreshTimer = null;
        this.heartbeatTimer = null;
        this.sessionTimeoutMinutes = 30;
        this.startSessionMaintenance();
    }

    /**
     * Iniciar sesión
     */
    async login(id, password, remember = true) {
        try {
            const response = await axios.post(`${API_BASE_URL}/auth/login`, {
                id,
                password,
                remember
            });

            this.token = response.data.access_token;
            this.user = response.data.user;
            this.remember = Boolean(response.data.remember ?? remember);

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
            window.location.href = '/pages/logout.php';
        }
    }

    /**
     * Verificar si está autenticado
     */
    isAuthenticated() {
        return !!this.token;
    }

    cookiesEnabled() {
        try {
            const secure = window.location.protocol === 'https:' ? '; Secure' : '';
            document.cookie = `sgpi_cookie_test=1; Path=/; Max-Age=60; SameSite=Lax${secure}`;
            const enabled = document.cookie.split(';').some(cookie => cookie.trim() === 'sgpi_cookie_test=1');
            document.cookie = `sgpi_cookie_test=; Path=/; Max-Age=0; SameSite=Lax${secure}`;
            return enabled && navigator.cookieEnabled !== false;
        } catch (error) {
            return false;
        }
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
        if (this.refreshPromise) return this.refreshPromise;
        if (!this.token) {
            throw new Error('No hay token para refrescar.');
        }

        this.refreshPromise = fetch(`${API_BASE_URL}/auth/refresh`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.token}`,
                'X-SGPI-Remember': this.remember ? '1' : '0'
            }
        }).then(async response => {
            if (!response.ok) {
                const error = new Error(
                    response.status >= 500
                        ? 'El servidor no pudo renovar la sesion temporalmente.'
                        : 'La sesion ya no puede renovarse.'
                );
                error.status = response.status;
                error.authTerminal = response.status === 401 || response.status === 403;
                throw error;
            }
            const data = await response.json();
            if (!data.access_token) throw new Error('Respuesta de sesion invalida.');
            this.token = data.access_token;
            if (data.user) this.user = data.user;
            await fetch('/api/set-session.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    auth_token: this.token,
                    user: this.user,
                    remember: this.remember
                })
            });
            if (window.SGPI_SESSION) {
                window.SGPI_SESSION.token = this.token;
                window.SGPI_SESSION.user = this.user;
                window.SGPI_SESSION.remember = this.remember;
            }
            this.scheduleTokenRefresh();
            return this.token;
        }).finally(() => {
            this.refreshPromise = null;
        });

        return this.refreshPromise;
    }

    configureSessionTimeout(minutes) {
        this.sessionTimeoutMinutes = Math.max(1, Number(minutes || 30));
        this.scheduleHeartbeat();
    }

    startSessionMaintenance() {
        if (!this.token) return;
        this.scheduleTokenRefresh();
        this.scheduleHeartbeat();
        window.addEventListener('focus', () => this.maintainSession(), { passive: true });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) this.maintainSession();
        });
    }

    async maintainSession() {
        if (!this.token || document.hidden) return;
        try {
            if (this.tokenExpiresWithin(5 * 60)) {
                await this.refreshToken();
            } else {
                await this.heartbeat();
            }
        } catch (error) {
            // ApiClient intentara una renovacion adicional si una solicitud protegida recibe 401.
        }
    }

    async heartbeat() {
        if (!this.token || document.hidden) return;
        const response = await fetch(`${API_BASE_URL}/auth/heartbeat`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.token}`,
                'X-SGPI-Remember': this.remember ? '1' : '0'
            }
        });
        if (response.status === 401) {
            await this.refreshToken();
            return;
        }
        if (!response.ok) throw new Error('No se pudo mantener activa la sesion.');
    }

    scheduleHeartbeat() {
        clearInterval(this.heartbeatTimer);
        if (!this.token) return;
        const interval = Math.max(30000, Math.min(300000, this.sessionTimeoutMinutes * 20000));
        this.heartbeatTimer = setInterval(() => this.maintainSession(), interval);
    }

    scheduleTokenRefresh() {
        clearTimeout(this.refreshTimer);
        const payload = this.tokenPayload();
        if (!payload?.exp) return;
        const delay = Math.max(30000, (payload.exp * 1000) - Date.now() - (5 * 60 * 1000));
        this.refreshTimer = setTimeout(() => this.refreshToken().catch(() => {}), delay);
    }

    tokenExpiresWithin(seconds) {
        const payload = this.tokenPayload();
        return !payload?.exp || payload.exp * 1000 <= Date.now() + (seconds * 1000);
    }

    tokenPayload() {
        try {
            const payload = this.token?.split('.')[1];
            if (!payload) return null;
            const normalized = payload.replace(/-/g, '+').replace(/_/g, '/');
            const padded = normalized.padEnd(Math.ceil(normalized.length / 4) * 4, '=');
            const bytes = Uint8Array.from(atob(padded), char => char.charCodeAt(0));
            return JSON.parse(new TextDecoder().decode(bytes));
        } catch (error) {
            return null;
        }
    }

    clearLocalSession() {
        this.token = null;
        this.user = null;
        this.remember = false;
        clearTimeout(this.refreshTimer);
        clearInterval(this.heartbeatTimer);
        try {
            sessionStorage.clear();
        } catch (error) {
            // Ignorar navegadores que bloquean storage.
        }
    }
}

// Instancia global
const auth = new AuthManager();
