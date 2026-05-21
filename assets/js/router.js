/**
 * Router - Gestión de navegación
 * Controla la navegación sin recargar la página
 */

class Router {
    constructor() {
        this.routes = {};
        this.currentPage = null;
    }

    /**
     * Registrar ruta
     */
    register(path, callback) {
        this.routes[path] = callback;
    }

    /**
     * Navegar a una ruta
     */
    async navigate(path) {
        // Si no está autenticado y no es ruta pública
        if (!auth.isAuthenticated() && !this.isPublicRoute(path)) {
            window.location.href = '/';
            return;
        }

        // Si está autenticado y es ruta de login
        if (auth.isAuthenticated() && (path === '/' || path === '/index.php')) {
            if (auth.isAdmin()) {
                window.location.href = '/admin';
            } else if (auth.isTeacher()) {
                window.location.href = '/docente';
            } else {
                window.location.href = '/estudiante';
            }
            return;
        }

        // Ejecutar callback de la ruta
        if (this.routes[path]) {
            await this.routes[path]();
        }

        window.history.pushState({}, '', path);
    }

    /**
     * Verificar si es ruta pública
     */
    isPublicRoute(path) {
        const publicRoutes = [
            '/',
            '/index.php',
            '/repositorio'
        ];
        return publicRoutes.includes(path);
    }
}

// Instancia global
const router = new Router();
