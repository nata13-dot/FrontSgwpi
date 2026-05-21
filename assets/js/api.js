/**
 * Cliente de API
 * Gestiona todas las llamadas a la API REST
 */

class ApiClient {
    constructor() {
        this.baseURL = API_BASE_URL;
        this.timeout = 10000;
        this.cache = new Map();
        this.pending = new Map();
        this.refreshPromise = null;
        this.cachePrefix = `sgpi-api-cache:${this.baseURL}:`;
        this.defaultCacheTtls = [
            { pattern: /^\/dashboard\/(stats|teacher|student)$/, ttl: 20000 },
            { pattern: /^\/proposal\/student-status$/, ttl: 20000 },
            { pattern: /^\/student\/evaluation-schedule$/, ttl: 30000 },
            { pattern: /^\/settings\/public$/, ttl: 300000 },
            { pattern: /^\/settings$/, ttl: 60000 },
            { pattern: /^\/proposal\/config$/, ttl: 30000 }
        ];
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
    async post(endpoint, data = {}, params = {}) {
        return this.request('POST', endpoint, data, params);
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
        const normalizedParams = { ...params };
        const requestedCacheTtl = normalizedParams._cache_ttl;
        const requestTimeout = Number(normalizedParams._timeout || this.timeout);
        const cacheTtl = Number(requestedCacheTtl ?? (method === 'GET' ? this.defaultCacheTtl(endpoint) : 0));
        const forceFresh = Boolean(normalizedParams._fresh);
        delete normalizedParams._cache_ttl;
        delete normalizedParams._fresh;
        delete normalizedParams._timeout;

        // Agregar parámetros
        Object.keys(normalizedParams).forEach(key => {
            const value = normalizedParams[key];
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.append(key, value);
            }
        });
        const cacheKey = `${method}:${url.toString()}`;

        if (method === 'GET' && cacheTtl > 0 && !forceFresh) {
            const cached = this.cache.get(cacheKey) || this.readStoredCache(cacheKey);
            if (cached && cached.expiresAt > Date.now()) {
                this.cache.set(cacheKey, cached);
                return cached.value;
            }
        }

        if (method === 'GET' && this.pending.has(cacheKey)) {
            return this.pending.get(cacheKey);
        }

        const options = {
            method,
            headers: {
                'Accept': 'application/json'
            }
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

        const executeRequest = async (retryingAfterRefresh = false) => {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), requestTimeout);
            const response = await fetch(url, { ...options, credentials: 'include', signal: controller.signal })
                .finally(() => clearTimeout(timeoutId));

            // Si no está autenticado (401)
            if (response.status === 401) {
                const canRefresh = auth.getToken()
                    && endpoint !== '/auth/refresh'
                    && endpoint !== '/auth/logout'
                    && !retryingAfterRefresh;

                if (canRefresh) {
                    try {
                        const freshToken = await this.refreshAuthToken();
                        options.headers.Authorization = `Bearer ${freshToken}`;
                        return executeRequest(true);
                    } catch (refreshError) {
                        this.redirectToLogout('expired');
                        throw refreshError;
                    }
                }

                this.redirectToLogout('unauthorized');
                throw new Error('Sesion expirada. Inicia sesion nuevamente.');
            }

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validationMessage = result.errors ? Object.values(result.errors).flat().join(' ') : '';
                const error = new Error(this.translateError(result.message || result.error || validationMessage || 'Error en la solicitud'));
                error.status = response.status;
                error.result = result;
                error.errors = result.errors || null;
                throw error;
            }

            if (method === 'GET' && cacheTtl > 0) {
                const cachedValue = {
                    value: result,
                    expiresAt: Date.now() + cacheTtl
                };
                this.cache.set(cacheKey, cachedValue);
                this.writeStoredCache(cacheKey, cachedValue);
            } else if (method !== 'GET') {
                this.clearCache();
            }

            return result;
        };

        try {
            if (method === 'GET') {
                const pendingRequest = executeRequest().finally(() => this.pending.delete(cacheKey));
                this.pending.set(cacheKey, pendingRequest);
                return await pendingRequest;
            }

            return await executeRequest();
        } catch (error) {
            if (method === 'GET') {
                this.pending.delete(cacheKey);
            }
            if (error.name === 'AbortError') {
                const timeoutError = new Error('La solicitud tardo demasiado. Intenta nuevamente.');
                timeoutError.name = 'AbortError';
                error = timeoutError;
            }
            console.error('Error en la solicitud:', error);
            throw error;
        }
    }

    clearCache() {
        this.cache.clear();
        this.pending.clear();
        try {
            Object.keys(sessionStorage)
                .filter(key => key.startsWith(this.cachePrefix))
                .forEach(key => sessionStorage.removeItem(key));
        } catch (error) {
            // La limpieza en memoria ya evita reutilizar datos dentro de la pagina actual.
        }
    }

    async refreshAuthToken() {
        if (!this.refreshPromise) {
            this.refreshPromise = auth.refreshToken()
                .then(token => {
                    this.clearCache();
                    return token;
                })
                .finally(() => {
                    this.refreshPromise = null;
                });
        }

        return this.refreshPromise;
    }

    redirectToLogout(reason = 'unauthorized') {
        if (window.SGPI_AUTH_REDIRECTING) return;
        window.SGPI_AUTH_REDIRECTING = true;
        this.clearCache();
        auth.clearLocalSession();
        window.location.replace(`/pages/logout.php?reason=${encodeURIComponent(reason)}`);
    }

    defaultCacheTtl(endpoint) {
        const match = this.defaultCacheTtls.find(item => item.pattern.test(endpoint));
        return match ? match.ttl : 0;
    }

    storageKey(cacheKey) {
        const token = auth.getToken() || 'guest';
        return `${this.cachePrefix}${token.slice(0, 18)}:${cacheKey}`;
    }

    readStoredCache(cacheKey) {
        try {
            const cached = JSON.parse(sessionStorage.getItem(this.storageKey(cacheKey)) || 'null');
            if (!cached || cached.expiresAt <= Date.now()) {
                sessionStorage.removeItem(this.storageKey(cacheKey));
                return null;
            }
            return cached;
        } catch (error) {
            return null;
        }
    }

    writeStoredCache(cacheKey, value) {
        try {
            sessionStorage.setItem(this.storageKey(cacheKey), JSON.stringify(value));
        } catch (error) {
            // Si el navegador no permite storage, la cache en memoria sigue funcionando.
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
