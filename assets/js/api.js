/**
 * Cliente de API
 * Gestiona todas las llamadas a la API REST
 */

class ApiClient {
    constructor() {
        this.baseURL = API_BASE_URL;
        this.timeout = 10000;
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        return this.request('GET', endpoint, null, params);
    }

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request('POST', endpoint, data);
    }

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return this.request('PUT', endpoint, data);
    }

    /**
     * DELETE request
     */
    async delete(endpoint, data = null) {
        return this.request('DELETE', endpoint, data);
    }

    /**
     * Hacer petición HTTP
     */
    async request(method, endpoint, data = null, params = {}) {
        const url = new URL(`${this.baseURL}${endpoint}`);

        // Agregar parámetros
        Object.keys(params).forEach(key => {
            url.searchParams.append(key, params[key]);
        });

        const options = {
            method,
            headers: {
                'Accept': 'application/json'
            },
            timeout: this.timeout
        };

        // Agregar token si existe
        if (auth.getToken()) {
            options.headers.Authorization = `Bearer ${auth.getToken()}`;
        }

        // Agregar body si existe
        if (data) {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        } else {
            options.headers['Content-Type'] = 'application/json';
        }

        try {
            const response = await fetch(url, options);

            // Si no está autenticado (401)
            if (response.status === 401) {
                auth.token = null;
                auth.user = null;
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.replace('/index.php');
                return;
            }

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validationMessage = result.errors ? Object.values(result.errors).flat().join(' ') : '';
                throw new Error(result.message || result.error || validationMessage || 'Error en la solicitud');
            }

            return result;
        } catch (error) {
            console.error('Error en la solicitud:', error);
            throw error;
        }
    }
}

// Instancia global
const api = new ApiClient();
