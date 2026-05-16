<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

/**
 * Página de prueba para verificar que la API está siendo consumida
 * Abre la consola del navegador (F12) para ver los logs
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API - SWGPI</title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; }
        .log-box { 
            background: #1e1e1e; 
            color: #0f0; 
            font-family: monospace; 
            padding: 15px; 
            border-radius: 5px;
            height: 300px;
            overflow-y: auto;
        }
        .log-entry { 
            padding: 5px 0;
            border-bottom: 1px solid #333;
        }
        .log-success { color: #0f0; }
        .log-error { color: #f00; }
        .log-info { color: #0ff; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🧪 Test de API - SWGPI</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Acciones de Test</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary w-100 mb-2" onclick="testApiConnection()">
                            🔗 Test Conexión API
                        </button>
                        <button class="btn btn-success w-100 mb-2" onclick="testAuthLogin()">
                            🔑 Test Login (Demo)
                        </button>
                        <button class="btn btn-info w-100 mb-2" onclick="testGetDeliverables()">
                            📦 Test GET Deliverables
                        </button>
                        <button class="btn btn-info w-100 mb-2" onclick="testGetProjects()">
                            📁 Test GET Projects
                        </button>
                        <button class="btn btn-warning w-100 mb-2" onclick="clearLogs()">
                            🗑️ Limpiar Logs
                        </button>
                        <button class="btn btn-danger w-100" onclick="checkLocalStorage()">
                            💾 Ver LocalStorage
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5>Información</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>URL API:</strong> <code id="apiUrl"><?= API_BASE_URL ?></code></p>
                        <p><strong>Token:</strong> <code id="tokenStatus">No definido</code></p>
                        <p><strong>Usuario:</strong> <code id="userStatus">No definido</code></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5>📊 Console de Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="log-box" id="logBox">
                            <div class="log-entry log-info">Ready para pruebas...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const API_BASE_URL = '<?= API_BASE_URL ?>';
        const logBox = document.getElementById('logBox');

        function addLog(message, type = 'info') {
            const entry = document.createElement('div');
            entry.className = `log-entry log-${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            logBox.appendChild(entry);
            logBox.scrollTop = logBox.scrollHeight;
        }

        function clearLogs() {
            logBox.innerHTML = '';
            addLog('Logs limpiados', 'info');
        }

        function updateStatus() {
            const token = localStorage.getItem('auth_token');
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            
            document.getElementById('tokenStatus').textContent = token ? token.substring(0, 20) + '...' : 'No definido';
            document.getElementById('userStatus').textContent = user ? `${user.id} (${user.nombre})` : 'No definido';
        }

        async function testApiConnection() {
            addLog('🔗 Probando conexión a API...', 'info');
            try {
                const response = await axios.get(`${API_BASE_URL}/auth/me`, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    timeout: 5000
                });
                addLog('✅ API respondió (sin auth): ' + JSON.stringify(response.data).substring(0, 50), 'success');
            } catch (error) {
                if (error.response?.status === 401) {
                    addLog('✅ API accesible (requiere token)', 'success');
                } else {
                    addLog('❌ Error: ' + (error.message || 'Sin respuesta'), 'error');
                }
            }
        }

        async function testAuthLogin() {
            addLog('🔑 Intentando login de prueba...', 'info');
            try {
                const response = await axios.post(`${API_BASE_URL}/auth/login`, {
                    id: 'admin',
                    password: 'password'
                });
                
                localStorage.setItem('auth_token', response.data.access_token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
                
                addLog('✅ Login exitoso', 'success');
                addLog('Token guardado en localStorage', 'success');
                updateStatus();
            } catch (error) {
                addLog('❌ Login fallido: ' + (error.response?.data?.error || error.message), 'error');
            }
        }

        async function testGetDeliverables() {
            addLog('📦 GET /deliverables...', 'info');
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    addLog('❌ No hay token. Haz login primero.', 'error');
                    return;
                }

                addLog(`Token encontrado: ${token.substring(0, 20)}...`, 'info');
                
                const response = await axios.get(`${API_BASE_URL}/deliverables`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                addLog(`✅ GET /deliverables OK (${response.data.data?.length || 0} items)`, 'success');
                addLog('Response: ' + JSON.stringify(response.data).substring(0, 100), 'info');
            } catch (error) {
                addLog(`❌ GET /deliverables FAILED: ${error.response?.status || error.code}`, 'error');
                addLog('Error: ' + (error.response?.data?.message || error.message), 'error');
            }
        }

        async function testGetProjects() {
            addLog('📁 GET /projects...', 'info');
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    addLog('❌ No hay token. Haz login primero.', 'error');
                    return;
                }

                const response = await axios.get(`${API_BASE_URL}/projects`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                addLog(`✅ GET /projects OK (${response.data.data?.length || 0} items)`, 'success');
                addLog('Response: ' + JSON.stringify(response.data).substring(0, 100), 'info');
            } catch (error) {
                addLog(`❌ GET /projects FAILED: ${error.response?.status || error.code}`, 'error');
                addLog('Error: ' + (error.response?.data?.message || error.message), 'error');
            }
        }

        function checkLocalStorage() {
            addLog('💾 LocalStorage contents:', 'info');
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('user');
            
            if (token) {
                addLog(`auth_token: ${token.substring(0, 50)}...`, 'success');
            } else {
                addLog('auth_token: NO ENCONTRADO', 'error');
            }
            
            if (user) {
                try {
                    const userData = JSON.parse(user);
                    addLog(`user: ${JSON.stringify(userData)}`, 'success');
                } catch (e) {
                    addLog(`user: INVÁLIDO - ${e.message}`, 'error');
                }
            } else {
                addLog('user: NO ENCONTRADO', 'error');
            }
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            addLog('Página de test cargada', 'info');
            updateStatus();
            
            // Interceptar requests de Axios para ver en los logs
            axios.interceptors.request.use(config => {
                addLog(`→ ${config.method.toUpperCase()} ${config.url}`, 'info');
                return config;
            });
            
            axios.interceptors.response.use(
                response => {
                    addLog(`← ${response.status} ${response.config.url}`, 'success');
                    return response;
                },
                error => {
                    addLog(`← ${error.response?.status || 'ERROR'} ${error.config?.url}`, 'error');
                    return Promise.reject(error);
                }
            );
        });
    </script>
</body>
</html>
