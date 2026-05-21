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
            window.location.href = '/index.php';
            return;
        }

        // Si está autenticado y es ruta de login
        if (auth.isAuthenticated() && path === '/index.php') {
            if (auth.isAdmin()) {
                window.location.href = '/pages/admin/dashboard.php';
            } else if (auth.isTeacher()) {
                window.location.href = '/pages/teacher/dashboard.php';
            } else {
                window.location.href = '/pages/student/dashboard.php';
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
            '/index.php',
            '/index.php',
            '/pages/forgot-password.php',
            '/pages/repositorio.php'
        ];
        return publicRoutes.includes(path);
    }
}

// Instancia global
const router = new Router();
