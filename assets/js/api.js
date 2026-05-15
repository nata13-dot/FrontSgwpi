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
                throw new Error(this.translateError(result.message || result.error || validationMessage || 'Error en la solicitud'));
            }

            return result;
        } catch (error) {
            console.error('Error en la solicitud:', error);
            throw error;
        }
    }

    translateError(message) {
        const replacements = {
            'The semestre field must be an integer.': 'El semestre debe ser un numero valido.',
            'The grupo field must be a string.': 'El grupo debe ser un texto valido.',
            'The current password field must be a string.': 'La contraseña actual debe ser texto valido.',
            'The password field confirmation does not match.': 'La confirmacion de contraseña no coincide.',
            'The password field must be at least 6 characters.': 'La contraseña debe tener al menos 6 caracteres.',
            'The direccion field format is invalid.': 'La direccion debe incluir un domicilio valido, con numero y caracteres permitidos.',
            'The direccion field must be at least 10 characters.': 'La direccion debe tener al menos 10 caracteres.',
            'The nombre field is required.': 'El nombre es obligatorio.',
            'The fecha evaluacion field is required.': 'La fecha de evaluacion es obligatoria.',
            'The teacher ids field must be an array.': 'Selecciona docentes validos.',
            'The project ids field must be an array.': 'Selecciona proyectos validos.'
        };

        let translated = String(message || '');
        Object.entries(replacements).forEach(([from, to]) => {
            translated = translated.replaceAll(from, to);
        });
        translated = translated.replaceAll('The ', 'El campo ')
            .replaceAll(' field is required.', ' es obligatorio.')
            .replaceAll(' field must be an integer.', ' debe ser un numero valido.')
            .replaceAll(' field must be a string.', ' debe ser texto valido.')
            .replaceAll(' field format is invalid.', ' tiene un formato invalido.');
        return translated;
    }
}

// Instancia global
const api = new ApiClient();
