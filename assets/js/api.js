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
        this.localMutationWindow = 10000;
        this.maxStoredCacheEntries = 50;
        this.resourceEndpoints = [
            '/evaluations/rubric-criteria',
            '/evaluations/rooms',
            '/proposal/assignments',
            '/proposal/exceptions',
            '/proposal/windows',
            '/proposal/projects',
            '/subject-groups',
            '/document-tags',
            '/evaluation-managers',
            '/deliverables',
            '/evaluations',
            '/competencias',
            '/asignaturas',
            '/repositorio',
            '/projects',
            '/notices',
            '/settings',
            '/semester-management',
            '/profile',
            '/users'
        ];
        this.cachePrefix = `sgpi-api-cache:v3:${this.baseURL}:`;
        this.defaultCacheTtls = [
            { pattern: /^\/dashboard\/(stats|teacher|student)$/, ttl: 20000 },
            { pattern: /^\/users(?:\/[^/]+)?$/, ttl: 30000 },
            { pattern: /^\/projects(?:\/[^/]+)?$/, ttl: 30000 },
            { pattern: /^\/asignaturas(?:\/[^/]+)?$/, ttl: 120000 },
            { pattern: /^\/competencias(?:\/[^/]+)?$/, ttl: 60000 },
            { pattern: /^\/subject-groups(?:\/[^/]+)?$/, ttl: 120000 },
            { pattern: /^\/document-tags(?:\/[^/]+)?$/, ttl: 60000 },
            { pattern: /^\/repositorio(?:\/[^/]+)?$/, ttl: 30000 },
            { pattern: /^\/deliverables(?:\/[^/]+)?$/, ttl: 20000 },
            { pattern: /^\/evaluations(?:\/.*)?$/, ttl: 20000 },
            { pattern: /^\/proposal\/student-status$/, ttl: 20000 },
            { pattern: /^\/student\/evaluation-schedule$/, ttl: 30000 },
            { pattern: /^\/settings\/public$/, ttl: 300000 },
            { pattern: /^\/settings$/, ttl: 60000 },
            { pattern: /^\/semester-management$/, ttl: 45000 },
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
     * Precarga la siguiente página de una respuesta paginada.
     */
    prefetchNextPage(endpoint, params = {}, pagination = {}) {
        const currentPage = Number(pagination.current_page || params.page || 1);
        const lastPage = Number(pagination.last_page || 1);
        if (currentPage >= lastPage) return;

        const nextParams = {
            ...params,
            page: currentPage + 1,
            _silent: true
        };
        if (nextParams._cache_ttl === undefined) {
            nextParams._cache_ttl = this.defaultCacheTtl(endpoint);
        }

        this.get(endpoint, nextParams).catch(() => {
            // La precarga es opcional; la página se solicitará normalmente si falla.
        });
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
        const silent = Boolean(normalizedParams._silent);
        delete normalizedParams._cache_ttl;
        delete normalizedParams._fresh;
        delete normalizedParams._timeout;
        delete normalizedParams._silent;

        // Agregar parámetros
        Object.keys(normalizedParams).forEach(key => {
            const value = normalizedParams[key];
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.append(key, value);
            }
        });
        const cacheKey = `${method}:${url.toString()}`;

        if (method === 'GET') {
            const cached = this.cache.get(cacheKey) || this.readStoredCache(cacheKey);
            const locallyUpdated = cached?.locallyUpdatedUntil > Date.now();
            const normallyValid = !forceFresh && cached?.expiresAt > Date.now();
            if (cached && (locallyUpdated || normallyValid)) {
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
            if (auth.remember) options.headers['X-SGPI-Remember'] = '1';
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
                        if (refreshError?.authTerminal) {
                            this.redirectToLogout('expired');
                        }
                        throw refreshError;
                    }
                }

                this.redirectToLogout('unauthorized');
                throw new Error('Sesión expirada. Inicia sesión nuevamente.');
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

            if (method === 'GET') {
                const cachedValue = {
                    value: result,
                    expiresAt: cacheTtl > 0 ? Date.now() + cacheTtl : 0,
                    locallyUpdatedUntil: 0,
                    endpoint: this.normalizeEndpoint(endpoint),
                    storedAt: Date.now()
                };
                this.cache.set(cacheKey, cachedValue);
                if (cacheTtl > 0) {
                    this.writeStoredCache(cacheKey, cachedValue);
                }
            } else if (method !== 'GET') {
                this.applyMutationToCache(method, endpoint, data, result);
            }

            return result;
        };

        if (!silent) {
            document.dispatchEvent(new CustomEvent('sgpi:request-start', {
                detail: { method, endpoint }
            }));
        }
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
            if (!silent) {
                console.error('Error en la solicitud:', error);
            }
            throw error;
        } finally {
            if (!silent) {
                document.dispatchEvent(new CustomEvent('sgpi:request-end', {
                    detail: { method, endpoint }
                }));
            }
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
            // La limpieza en memoria ya evita reutilizar datos dentro de la página actual.
        }
    }

    applyMutationToCache(method, endpoint, requestData, result) {
        const resource = this.resourceEndpoint(endpoint);
        const mutationId = this.endpointId(endpoint);
        const entity = this.mutationEntity(result, requestData, mutationId);
        const now = Date.now();
        const retained = [];

        // Proyectos tiene colecciones mutuamente excluyentes por modalidad y
        // tipo. Parchar todas las cachés haría aparecer temporalmente una alta
        // o un cambio de modalidad en pestañas que no le corresponden.
        if (resource === '/projects') {
            this.cache.forEach((cached, cacheKey) => {
                if (this.cacheMatchesResource(cached?.endpoint, resource)) this.cache.delete(cacheKey);
            });
            this.removeStoredResource(resource);
            window.dispatchEvent(new CustomEvent('sgpi:api-mutated', {
                detail: { method, endpoint: this.normalizeEndpoint(endpoint), resource, id: this.endpointId(endpoint), result }
            }));
            return;
        }

        this.cache.forEach((cached, cacheKey) => {
            if (!cached || !this.cacheMatchesResource(cached.endpoint, resource)) return;

            const patched = this.patchCachedValue(
                cached.value,
                method,
                entity,
                mutationId,
                this.isCollectionMutation(endpoint, resource)
                    && this.cacheAcceptsCreatedEntity(cacheKey)
            );
            if (!patched.changed) {
                this.cache.delete(cacheKey);
                return;
            }

            cached.value = patched.value;
            cached.locallyUpdatedUntil = now + this.localMutationWindow;
            this.cache.set(cacheKey, cached);
            retained.push([cacheKey, cached]);
        });
        this.removeStoredResource(resource);
        retained.forEach(([cacheKey, cached]) => this.writeStoredCache(cacheKey, cached));

        window.dispatchEvent(new CustomEvent('sgpi:api-mutated', {
            detail: {
                method,
                endpoint: this.normalizeEndpoint(endpoint),
                resource,
                id: entity?.id ?? mutationId ?? null,
                entity,
                result
            }
        }));
    }

    patchCachedValue(value, method, entity, mutationId, isCollectionMutation) {
        if (Array.isArray(value)) {
            return this.patchArray(value, method, entity, mutationId, isCollectionMutation);
        }

        if (!value || typeof value !== 'object') {
            return { value, changed: false };
        }

        if (Array.isArray(value.data)) {
            const patched = this.patchArray(value.data, method, entity, mutationId, isCollectionMutation);
            if (!patched.changed) return { value, changed: false };

            const next = { ...value, data: patched.value };
            if (Number.isFinite(Number(next.total))) {
                const delta = patched.value.length - value.data.length;
                next.total = Math.max(0, Number(next.total) + delta);
            }
            return { value: next, changed: true };
        }

        const valueId = this.entityId(value);
        const targetId = this.entityId(entity) ?? mutationId;
        if (valueId !== null && targetId !== null && String(valueId) === String(targetId)) {
            if (method === 'DELETE') return { value: null, changed: true };
            return { value: { ...value, ...entity }, changed: true };
        }

        return { value, changed: false };
    }

    patchArray(items, method, entity, mutationId, isCollectionMutation) {
        const targetId = this.entityId(entity) ?? mutationId;
        const index = targetId === null
            ? -1
            : items.findIndex(item => String(this.entityId(item)) === String(targetId));

        if (method === 'DELETE') {
            if (index < 0) return { value: items, changed: false };
            return { value: items.filter((_, itemIndex) => itemIndex !== index), changed: true };
        }

        if (!entity || typeof entity !== 'object') {
            return { value: items, changed: false };
        }

        if (index >= 0) {
            const meaningfulKeys = Object.keys(entity).filter(key => !['id', 'ID'].includes(key));
            if (meaningfulKeys.length === 0) {
                return { value: items, changed: false };
            }
            const next = [...items];
            next[index] = { ...next[index], ...entity };
            return { value: next, changed: true };
        }

        if (method === 'POST' && isCollectionMutation && this.entityId(entity) !== null) {
            return { value: [entity, ...items], changed: true };
        }

        return { value: items, changed: false };
    }

    mutationEntity(result, requestData, mutationId) {
        const preferredKeys = [
            'user', 'project', 'deliverable', 'evaluation', 'room', 'group',
            'asignatura', 'competencia', 'tag', 'document', 'notice',
            'exception', 'assignment', 'window', 'criterion', 'period'
        ];
        for (const key of preferredKeys) {
            if (result?.[key] && typeof result[key] === 'object' && !Array.isArray(result[key])) {
                return result[key];
            }
        }

        if (result && typeof result === 'object' && !Array.isArray(result) && this.entityId(result) !== null) {
            return result;
        }

        const requestObject = requestData instanceof FormData
            ? Object.fromEntries(requestData.entries())
            : requestData;
        const fallback = requestObject && typeof requestObject === 'object' && !Array.isArray(requestObject)
            ? { ...requestObject }
            : {};

        if (result && typeof result === 'object' && !Array.isArray(result)) {
            Object.entries(result).forEach(([key, value]) => {
                if (!['message', 'errors', 'error'].includes(key) && typeof value !== 'object') {
                    fallback[key] = value;
                }
            });
        }
        if (mutationId !== null && fallback.id === undefined) fallback.id = mutationId;
        return Object.keys(fallback).length ? fallback : null;
    }

    cacheMatchesResource(cachedEndpoint, resource) {
        if (!cachedEndpoint || !resource) return false;
        return cachedEndpoint === resource;
    }

    isCollectionMutation(endpoint, resource) {
        return this.normalizeEndpoint(endpoint) === resource;
    }

    cacheAcceptsCreatedEntity(cacheKey) {
        try {
            const url = new URL(String(cacheKey).replace(/^[A-Z]+:/, ''));
            return Number(url.searchParams.get('page') || 1) <= 1;
        } catch (error) {
            return true;
        }
    }

    resourceEndpoint(endpoint) {
        const normalized = this.normalizeEndpoint(endpoint);
        const knownResource = this.resourceEndpoints.find(resource => (
            normalized === resource || normalized.startsWith(`${resource}/`)
        ));
        if (knownResource) return knownResource;

        const segments = normalized.split('/').filter(Boolean);
        const idIndex = segments.findIndex(segment => /^\d+$/.test(segment));
        if (idIndex >= 0) {
            return `/${segments.slice(0, idIndex).join('/')}`;
        }
        return normalized;
    }

    endpointId(endpoint) {
        const normalized = this.normalizeEndpoint(endpoint);
        const resource = this.resourceEndpoint(normalized);
        const remainder = normalized.slice(resource.length).split('/').filter(Boolean);
        const first = remainder[0] || null;
        const actionNames = new Set([
            'archive-selected', 'unarchive-selected', 'import-excel',
            'send-credentials', 'apply-semester-change'
        ]);
        return first && !actionNames.has(first) ? first : null;
    }

    normalizeEndpoint(endpoint) {
        return `/${String(endpoint || '').split('?')[0].replace(/^\/+|\/+$/g, '')}`;
    }

    entityId(entity) {
        if (!entity || typeof entity !== 'object') return null;
        return entity.id ?? entity.ID ?? entity.user_id ?? entity.project_id ?? null;
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
        return `${this.cachePrefix}${this.cacheScope(token)}:${cacheKey}`;
    }

    cacheScope(value) {
        let hash = 2166136261;
        const input = String(value || 'guest');
        for (let index = 0; index < input.length; index++) {
            hash ^= input.charCodeAt(index);
            hash = Math.imul(hash, 16777619);
        }
        return (hash >>> 0).toString(36);
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
            this.pruneStoredCache();
        } catch (error) {
            this.pruneStoredCache(true);
            try {
                sessionStorage.setItem(this.storageKey(cacheKey), JSON.stringify(value));
            } catch (retryError) {
                // Si el navegador no permite storage, la cache en memoria sigue funcionando.
            }
        }
    }

    removeStoredResource(resource) {
        try {
            Object.keys(sessionStorage)
                .filter(key => key.startsWith(this.cachePrefix))
                .forEach(key => {
                    const cached = JSON.parse(sessionStorage.getItem(key) || 'null');
                    if (this.cacheMatchesResource(cached?.endpoint, resource)) {
                        sessionStorage.removeItem(key);
                    }
                });
        } catch (error) {
            // La invalidacion en memoria sigue activa aunque storage no este disponible.
        }
    }

    pruneStoredCache(force = false) {
        try {
            const now = Date.now();
            const entries = Object.keys(sessionStorage)
                .filter(key => key.startsWith(this.cachePrefix))
                .map(key => {
                    const value = JSON.parse(sessionStorage.getItem(key) || 'null');
                    return { key, value };
                })
                .filter(entry => entry.value);

            entries
                .filter(entry => entry.value.expiresAt <= now)
                .forEach(entry => sessionStorage.removeItem(entry.key));

            const active = entries
                .filter(entry => entry.value.expiresAt > now)
                .sort((a, b) => Number(a.value.storedAt || 0) - Number(b.value.storedAt || 0));
            const keep = force ? Math.floor(this.maxStoredCacheEntries / 2) : this.maxStoredCacheEntries;
            active.slice(0, Math.max(0, active.length - keep))
                .forEach(entry => sessionStorage.removeItem(entry.key));
        } catch (error) {
            // No es necesario interrumpir una solicitud por mantenimiento de cache.
        }
    }

    translateError(message) {
        const replacements = {
            'The semestre field must be an integer.': 'El semestre debe ser un número válido.',
            'The grupo field must be a string.': 'El grupo debe ser un texto válido.',
            'The current password field must be a string.': 'La contraseña actual debe ser texto válido.',
            'The password field confirmation does not match.': 'La confirmación de contraseña no coincide.',
            'The password field must be at least 6 characters.': 'La contraseña debe tener al menos 6 caracteres.',
            'The direccion field format is invalid.': 'La dirección debe incluir un domicilio válido, con número y caracteres permitidos.',
            'The direccion field must be at least 10 characters.': 'La dirección debe tener al menos 10 caracteres.',
            'The nombre field is required.': 'El nombre es obligatorio.',
            'The fecha evaluacion field is required.': 'La fecha de evaluación es obligatoria.',
            'The teacher ids field must be an array.': 'Selecciona docentes válidos.',
            'The project ids field must be an array.': 'Selecciona proyectos válidos.'
        };

        let translated = String(message || '');
        Object.entries(replacements).forEach(([from, to]) => {
            translated = translated.replaceAll(from, to);
        });
        translated = translated.replaceAll('The ', 'El campo ')
            .replaceAll(' field is required.', ' es obligatorio.')
            .replaceAll(' field must be an integer.', ' debe ser un número válido.')
            .replaceAll(' field must be a string.', ' debe ser texto válido.')
            .replaceAll(' field format is invalid.', ' tiene un formato invalido.');
        return translated;
    }
}

// Instancia global
const api = new ApiClient();
