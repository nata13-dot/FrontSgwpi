# Índice de funciones, métodos y eventos

Extracción estática por patrones, con ubicación en el corte documentado. Las declaraciones con nombre, métodos de clase y funciones flecha se enumeran cuando coinciden con los patrones indicados en el generador. Los eventos y llamadas se incluyen por separado; no son funciones adicionales. Para callbacks anónimos y expresiones complejas consultar [REFERENCIA.md](REFERENCIA.md). Los números de línea pertenecen al código original.

## assets/js/academic-console.js

[Fuente](../../assets/js/academic-console.js) · [Código completo](REFERENCIA.md#assets-js-academic-console-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 2 | función | `function mountAcademicConsole(root, api, user, initialTab = 'periods')` |
| 11 | flecha | `const el = (tag, text, attrs = {}) =&gt;` |
| 12 | flecha | `const error = (e) =&gt;` |
| 13 | flecha | `const panel = (title, parent) =&gt;` |
| 14 | flecha | `const button = (label, action, parent) =&gt;` |
| 19 | flecha | `const input = (form, title, name, type = 'text', value = '', required = true) =&gt;` |
| 24 | flecha | `const select = (form, title, name, options, value = '', required = true) =&gt;` |
| 31 | flecha | `const form = (parent, submitLabel, submit) =&gt;` |
| 33 | flecha | `const finish = () =&gt;` |
| 44 | flecha | `const periods = () =&gt;` |
| 45 | flecha | `const projects = () =&gt;` |
| 46 | flecha | `const members = (roles) =&gt;` |
| 47 | flecha | `const groups = (items = catalog.groups) =&gt;` |
| 48 | flecha | `const date = value =&gt;` |
| 49 | flecha | `const request = (method, url, body) =&gt;` |
| 58 | función | `async function renderPeriods()` |
| 82 | función | `async function promotion(period)` |
| 101 | función | `async function reconciliation(period)` |
| 116 | flecha | `const roomRows = (report.rooms &#124;&#124; []).map((r, i) =&gt;` |
| 126 | función | `async function renderCourses()` |
| 154 | función | `async function renderAuthorizations()` |
| 179 | función | `async function renderMemberships()` |
| 189 | función | `async function renderContinuity()` |
| 196 | función | `async function renderResidencies()` |
| 238 | función | `async function render()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 16 | `    node.onclick = async () =&gt; { node.disabled = true; try { await action() } catch (e) { status.textContent = error(e) } finally { node.disabled = false } }` |
| 35 | `      node.onsubmit = async (event) =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 59 | `const data = await request('GET', '/semester-management')` |
| 68 | `for (const [state, label] of [['abierto', 'Abrir'], ['en_cierre', 'Iniciar cierre'], ['cerrado', 'Cerrar']]) button(label, async () =&gt; { await request('POST', `/semester-management/periods/${p.id}/transition`, { state }); await render() }, row)` |
| 74 | `const [f, done] = form(p, 'Crear periodo', d =&gt; request('POST', '/semester-management/periods', { ...d, automatic_promotion: false }))` |
| 78 | `const [f, done] = form(p, 'Guardar matrícula', d =&gt; request('POST', '/academic-operations/enrollments', { student_id: d.student_id, group_id: Number(d.group_id) }))` |
| 83 | `const preview = await request('GET', `/semester-management/periods/${period.id}/promotion-preview`)` |
| 88 | `await request('POST', `/semester-management/periods/${period.id}/promote`, { operation_id: operationId, snapshot: preview.snapshot, movements })` |
| 102 | `const report = await request('GET', `/academic-operations/periods/${period.id}/reconciliation`)` |
| 107 | `await request('POST', `/academic-operations/periods/${period.id}/reconciliation`, { snapshot: report.snapshot,` |
| 127 | `const data = await request('GET', '/course-enrollments')` |
| 134 | `const [f, done] = form(p, 'Inscribirme', d =&gt; request('POST', `/course-enrollments/${c.id}`, d)); input(f, 'Clave del docente', 'password', 'password'); done()` |
| 137 | `const [f, done] = form(p, 'Solicitar baja directa', d =&gt; request('DELETE', `/course-enrollments/${c.id}`, d)); input(f, 'Motivo de baja', 'reason'); done()` |
| 140 | `const [f, done] = form(p, 'Abrir inscripción / renovar clave', d =&gt; request('PUT', `/courses/${c.id}/self-enrollment`, { enabled: true, password: d.password, password_confirmation: d.password, starts_at: d.start, ends_at: d.end }))` |
| 143 | `button('Cerrar autorregistro', async () =&gt; { await request('PUT', `/courses/${c.id}/self-enrollment`, { enabled: false }); await render() }, p)` |
| 145 | `const data = await request('GET', `/courses/${c.id}/students`)` |
| 148 | `if (academic) { const [f, done] = form(box, 'Registrar baja administrativa', d =&gt; request('DELETE', `/course-enrollments/${c.id}`, d)); select(f, 'Alumno', 'student_id', data.data.map(s =&gt; [s.id, `${s.id} ${s.nombres}`])); input(f, 'Motivo', 'reason'); done() }` |
| 155 | `const data = await request('GET', '/academic-operations/authorizations')` |
| 157 | `const [f, done] = form(p, 'Registrar solicitud', d =&gt; request('POST', '/academic-operations/authorizations', {` |
| 173 | `const [f, done] = form(box, 'Registrar resolución', d =&gt; request('PUT', `/semester-management/exceptions/${a.id}/decision`, { version: a.version, status: d.status, reason: d.reason, project_scope: d.scope === 'on' }))` |
| 182 | `const [f, done] = form(p, 'Asignar perfil', d =&gt; request('PUT', `/academic-operations/memberships/${d.membership}`, { profile_id: Number(d.profile_id) }))` |
| 185 | `button('Consultar compromisos', async () =&gt; { if (!field.value) return; const commitments = await request('GET', `/academic-operations/memberships/${field.value}/commitments`); const box = panel('Compromisos pendientes', p); for (const [key, ids] of Object.entries(commitments)) box.append(el('p', `${({ courses: 'Cursos', rooms: 'Salas', projects: 'Asesorías', residencies: 'Residencias' })[key]}: ${ids.join(', ') &#124;&#124; 'ninguno'}`)) }, p)` |
| 186 | `if (general) { const box = panel('Catálogo institucional de salones', content); const [f, done] = form(box, 'Agregar salón', d =&gt; request('POST', '/academic-operations/classrooms', d)); input(f, 'Nombre del salón', 'name'); done() }` |
| 191 | `const [f, done] = form(p, 'Registrar continuidad', d =&gt; request('POST', '/academic-operations/project-period', { project_id: Number(d.project_id), group_id: Number(d.group_id) }))` |
| 197 | `const data = await request('GET', '/residencies')` |
| 202 | `return request('POST', '/residencies', { ...d, student_id: student ? user.id : d.student_id, period_id: Number(d.period_id), project_id: Number(d.project_id), company_id: project?.empresa_id })` |
| 217 | `button('Descargar', () =&gt; request('DOWNLOAD', `/residencies/${r.id}/versions/${v.id}/download`, { name: v.nombre }), line)` |
| 219 | `const [f, done] = form(line, 'Guardar revisión', d =&gt; request('PUT', `/residencies/${r.id}/versions/${v.id}/review`, d)); select(f, 'Resultado', 'status', [['correcciones', 'Solicitar correcciones'], ['validada', 'Validar']]); input(f, 'Observaciones', 'observations'); done()` |
| 224 | `const [f, done] = form(sub, 'Subir nueva versión', async () =&gt; { const data = new FormData(); data.append('file', file.files[0]); await request('POST', `/residencies/${r.id}/requirements/${item.id}/versions`, data) }); const file = input(f, 'Documento PDF o Word', 'file', 'file'); file.accept = '.pdf,.doc,.docx'; done()` |
| 228 | `const [f, done] = form(box, 'Agregar requisito documental', d =&gt; request('POST', `/residencies/${r.id}/requirements`, { name: d.name, required: d.required === 'on', deadline: d.deadline &#124;&#124; null })); input(f, 'Nombre del requisito', 'name'); input(f, 'Obligatorio', 'required', 'checkbox', '', false).checked = true; input(f, 'Fecha límite opcional', 'deadline', 'datetime-local', '', false); done()` |
| 231 | `const [af, adone] = form(box, 'Reasignar asesor', d =&gt; request('PUT', `/residencies/${r.id}/advisor`, { advisor_id: d.advisor_id, version: r.version, reason: d.reason }))` |
| 233 | `const [f, done] = form(box, 'Cerrar seguimiento', d =&gt; request('POST', `/residencies/${r.id}/close`, { version: r.version, reason: d.reason })); input(f, 'Motivo del cierre', 'reason'); done()` |
| 242 | `catalog = await request('GET', '/academic-operations/catalog')` |

## assets/js/activity-notifications.js

[Fuente](../../assets/js/activity-notifications.js) · [Código completo](REFERENCIA.md#assets-js-activity-notifications-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 8 | función | `function escapeHtml(value)` |
| 14 | función | `function formatDate(value)` |
| 25 | función | `function render(response)` |
| 57 | función | `async function load()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 65 | `    document.addEventListener('DOMContentLoaded', () =&gt; {` |
| 71 | `        menu.addEventListener('shown.bs.dropdown', load);` |
| 72 | `        list.addEventListener('click', async event =&gt; {` |
| 82 | `        markAll.addEventListener('click', async () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 59 | `render(await api.get('/activity-notifications', { _fresh: true }));` |
| 77 | `await api.put(`/activity-notifications/${item.dataset.notificationId}/read`);` |
| 83 | `await api.put('/activity-notifications/read-all');` |

## assets/js/api.js

[Fuente](../../assets/js/api.js) · [Código completo](REFERENCIA.md#assets-js-api-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 7 | método | `constructor() {` |
| 61 | método | `async get(endpoint, params = {}) {` |
| 68 | método | `prefetchNextPage(endpoint, params = {}, pagination = {}) {` |
| 90 | método | `async post(endpoint, data = {}, params = {}) {` |
| 97 | método | `async put(endpoint, data = {}) {` |
| 104 | método | `async delete(endpoint, data = null) {` |
| 111 | método | `async request(method, endpoint, data = null, params = {}) {` |
| 172 | flecha | `const executeRequest = async (retryingAfterRefresh = false) =&gt;` |
| 267 | método | `clearCache() {` |
| 279 | método | `applyMutationToCache(method, endpoint, requestData, result) {` |
| 336 | método | `patchCachedValue(value, method, entity, mutationId, isCollectionMutation) {` |
| 367 | método | `patchArray(items, method, entity, mutationId, isCollectionMutation) {` |
| 399 | método | `mutationEntity(result, requestData, mutationId) {` |
| 433 | método | `cacheMatchesResource(cachedEndpoint, resource) {` |
| 438 | método | `isCollectionMutation(endpoint, resource) {` |
| 442 | método | `cacheAcceptsCreatedEntity(cacheKey) {` |
| 451 | método | `resourceEndpoint(endpoint) {` |
| 466 | método | `endpointId(endpoint) {` |
| 478 | método | `normalizeEndpoint(endpoint) {` |
| 482 | método | `entityId(entity) {` |
| 487 | método | `async refreshAuthToken() {` |
| 502 | método | `redirectToLogout(reason = 'unauthorized') {` |
| 510 | método | `defaultCacheTtl(endpoint) {` |
| 515 | método | `storageKey(cacheKey) {` |
| 520 | método | `cacheScope(value) {` |
| 530 | método | `readStoredCache(cacheKey) {` |
| 543 | método | `writeStoredCache(cacheKey, value) {` |
| 557 | método | `removeStoredResource(resource) {` |
| 572 | método | `pruneStoredCache(force = false) {` |
| 598 | método | `translateError(message) {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 62 | `return this.request('GET', endpoint, null, params);` |
| 91 | `return this.request('POST', endpoint, data, params);` |
| 98 | `return this.request('PUT', endpoint, data);` |
| 105 | `return this.request('DELETE', endpoint, data);` |
| 175 | `const response = await fetch(url, { ...options, credentials: 'include', signal: controller.signal })` |

## assets/js/app.js

[Fuente](../../assets/js/app.js) · [Código completo](REFERENCIA.md#assets-js-app-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 4 | función | `function showAlert(container, type, message, duration = 5000)` |
| 36 | función | `function validateForm(formId)` |
| 58 | función | `function formatDate(dateString)` |
| 70 | función | `function formatTime(dateString)` |
| 81 | función | `function debounce(func, wait)` |
| 83 | función | `function executedFunction(...args)` |
| 84 | flecha | `const later = () =&gt;` |
| 96 | función | `function handleImageUpload(inputId, previewId)` |
| 119 | función | `function copyToClipboard(text)` |
| 130 | función | `function validarCalificacion(valor)` |
| 138 | función | `function validarMimeType(mimeType)` |
| 162 | función | `function validarTamañoArchivo(tamaño)` |
| 171 | función | `function formatearTamaño(bytes)` |
| 182 | función | `async function descargarEntregable(deliverable_id, nombre = null, project_id = null)` |
| 225 | función | `async function validarFechaEntregable(competencia_id, fecha_limite)` |
| 255 | función | `async function subirArchivo(deliverable_id, project_id, file)` |
| 294 | función | `async function calificarEntregable(deliverable_id, project_id, calificacion)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 102 | `    input.addEventListener('change', (e) =&gt; {` |
| 321 | `document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 191 | `const response = await fetch(`${API_BASE_URL}/deliverables/${deliverable_id}/download${query}`, {` |
| 227 | `const response = await api.get(`/competencias/${competencia_id}`);` |
| 281 | `const data = await api.post(`/deliverables/${deliverable_id}/upload`, formData, { _timeout: 120000 });` |
| 307 | `const data = await api.post(`/deliverables/${deliverable_id}/calificar`, {` |

## assets/js/auth.js

[Fuente](../../assets/js/auth.js) · [Código completo](REFERENCIA.md#assets-js-auth-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 7 | método | `constructor() {` |
| 26 | método | `async login(id, password, remember = true) {` |
| 50 | método | `async logout() {` |
| 67 | método | `isAuthenticated() {` |
| 71 | método | `cookiesEnabled() {` |
| 86 | método | `getCurrentUser() {` |
| 93 | método | `isAdmin() {` |
| 101 | método | `isTeacher() {` |
| 108 | método | `isStudent() {` |
| 115 | método | `getToken() {` |
| 122 | método | `async refreshToken() {` |
| 179 | método | `configureSessionTimeout(minutes) {` |
| 184 | método | `startSessionMaintenance() {` |
| 194 | método | `async maintainSession() {` |
| 207 | método | `async heartbeat() {` |
| 226 | método | `scheduleHeartbeat() {` |
| 233 | método | `scheduleTokenRefresh() {` |
| 241 | método | `tokenExpiresWithin(seconds) {` |
| 246 | método | `tokenPayload() {` |
| 259 | método | `clearLocalSession() {` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 188 | `        window.addEventListener('focus', () =&gt; this.maintainSession(), { passive: true });` |
| 189 | `        document.addEventListener('visibilitychange', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 28 | `const response = await axios.post(`${API_BASE_URL}/auth/login`, {` |
| 52 | `await axios.post(`${API_BASE_URL}/auth/logout`, {}, {` |
| 128 | `this.refreshPromise = fetch(`${API_BASE_URL}/auth/refresh`, {` |
| 152 | `await fetch('/api/set-session.php', {` |
| 209 | `const response = await fetch(`${API_BASE_URL}/auth/heartbeat`, {` |

## assets/js/career-modules.js

[Fuente](../../assets/js/career-modules.js) · [Código completo](REFERENCIA.md#assets-js-career-modules-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 2 | función | `async function applyCareerModules()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 34 | `        document.addEventListener('DOMContentLoaded', applyCareerModules);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 6 | `const response = await fetch(`${window.SGPI_API_BASE_URL}/career/modules`, {` |

## assets/js/career-switcher.js

[Fuente](../../assets/js/career-switcher.js) · [Código completo](REFERENCIA.md#assets-js-career-switcher-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 2 | función | `async function persistSession(data)` |
| 22 | función | `async function switchCareer(button)` |
| 69 | función | `function initialize()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 71 | `            button.addEventListener('click', () =&gt; switchCareer(button));` |
| 76 | `        document.addEventListener('DOMContentLoaded', initialize);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 3 | `const response = await fetch('/api/set-session.php', {` |
| 31 | `const response = await fetch(`${window.SGPI_API_BASE_URL}/auth/switch-career`, {` |

## assets/js/global-search.js

[Fuente](../../assets/js/global-search.js) · [Código completo](REFERENCIA.md#assets-js-global-search-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 64 | función | `function item(title, description, url, icon, keywords = [])` |
| 68 | función | `function dashboardPath(currentRole)` |
| 72 | función | `function moduleForUrl(url)` |
| 83 | función | `function moduleAllowed(entry)` |
| 88 | función | `function normalize(value)` |
| 96 | función | `function searchCatalog(query)` |
| 118 | función | `function dynamicSearchItems(query)` |
| 152 | función | `function render(query = '')` |
| 174 | función | `function setActive(nextIndex)` |
| 185 | función | `function closeResults()` |
| 191 | función | `function openSearch()` |
| 241 | función | `function escapeHtml(value)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 59 | `    window.addEventListener('sgpi:career-modules', event =&gt; {` |
| 197 | `    input.addEventListener('focus', () =&gt; render(input.value));` |
| 198 | `    input.addEventListener('input', () =&gt; render(input.value));` |
| 199 | `    input.addEventListener('keydown', event =&gt; {` |
| 213 | `    form.addEventListener('submit', event =&gt; {` |
| 225 | `    resultsBox.addEventListener('mousemove', event =&gt; {` |
| 230 | `    document.addEventListener('keydown', event =&gt; {` |
| 237 | `    document.addEventListener('click', event =&gt; {` |

## assets/js/operational-notifications.js

[Fuente](../../assets/js/operational-notifications.js) · [Código completo](REFERENCIA.md#assets-js-operational-notifications-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 2 | flecha | `const escapeHtml = value =&gt;` |
| 6 | función | `function render(response)` |
| 40 | función | `async function load()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 49 | `    document.addEventListener('DOMContentLoaded', () =&gt; {` |
| 52 | `        document.addEventListener('visibilitychange', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 43 | `render(await api.get('/admin/operational-alerts/summary', {_fresh: true, _silent: true}));` |

## assets/js/responsive.js

[Fuente](../../assets/js/responsive.js) · [Código completo](REFERENCIA.md#assets-js-responsive-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 6 | flecha | `const isMobile = () =&gt;` |
| 9 | función | `function wrapTables()` |
| 25 | función | `function prepareTableWrapper(wrapper, table)` |
| 31 | función | `function normalizeHeaderText(header)` |
| 36 | función | `function enhanceResponsiveTables(root = document)` |
| 123 | función | `function initMobileTableDetails()` |
| 142 | función | `function scheduleEnhancements()` |
| 152 | función | `function closeNavbarOnSelection()` |
| 170 | función | `function improveMobileDialogs()` |
| 178 | función | `function markWideContent()` |
| 184 | función | `function syncMobileClass()` |
| 188 | función | `function initSidebarShell()` |
| 210 | flecha | `const syncMobileSidebar = open =&gt;` |
| 217 | flecha | `const closeMobileSidebar = () =&gt;` |
| 221 | flecha | `const clearCollapseTimer = () =&gt;` |
| 227 | flecha | `const expand = () =&gt;` |
| 235 | flecha | `const collapse = () =&gt;` |
| 243 | flecha | `const scheduleCollapse = (delay = 1400) =&gt;` |
| 249 | flecha | `const sync = () =&gt;` |
| 304 | función | `function initResponsiveHelpers()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 127 | `        document.addEventListener('click', event =&gt; {` |
| 157 | `            link.addEventListener('click', () =&gt; {` |
| 263 | `            button.addEventListener('click', () =&gt; {` |
| 278 | `        sidebar.addEventListener('mouseenter', expand, { passive: true });` |
| 279 | `        sidebar.addEventListener('mousemove', () =&gt; {` |
| 282 | `        sidebar.addEventListener('focusin', () =&gt; {` |
| 286 | `        sidebar.addEventListener('mouseleave', () =&gt; scheduleCollapse(900), { passive: true });` |
| 288 | `            link.addEventListener('click', closeMobileSidebar);` |
| 290 | `        backdrop.addEventListener('click', closeMobileSidebar);` |
| 291 | `        document.addEventListener('keydown', event =&gt; {` |
| 296 | `            desktopQuery.addEventListener('change', sync);` |
| 315 | `    document.addEventListener('DOMContentLoaded', initResponsiveHelpers);` |
| 316 | `    window.addEventListener('resize', syncMobileClass, { passive: true });` |
| 325 | `    document.addEventListener('DOMContentLoaded', () =&gt; {` |

## assets/js/router.js

[Fuente](../../assets/js/router.js) · [Código completo](REFERENCIA.md#assets-js-router-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 7 | método | `constructor() {` |
| 15 | método | `register(path, callback) {` |
| 22 | método | `async navigate(path) {` |
| 52 | método | `isPublicRoute(path) {` |

## assets/js/skeleton-loader.js

[Fuente](../../assets/js/skeleton-loader.js) · [Código completo](REFERENCIA.md#assets-js-skeleton-loader-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 5 | función | `function line(width = '100%', height = '0.85rem')` |
| 9 | función | `function tableCellSkeleton()` |
| 29 | función | `function cardSkeleton(count = 3)` |
| 53 | función | `function blockSkeleton()` |
| 69 | función | `function replaceLoadingElement(element)` |
| 93 | función | `function scan(root = document)` |
| 116 | función | `function setRequestState(delta)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 137 | `    document.addEventListener('sgpi:request-start', () =&gt; setRequestState(1));` |
| 138 | `    document.addEventListener('sgpi:request-end', () =&gt; setRequestState(-1));` |
| 139 | `    document.addEventListener('DOMContentLoaded', () =&gt; {` |

## assets/js/table.js

[Fuente](../../assets/js/table.js) · [Código completo](REFERENCIA.md#assets-js-table-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 5 | método | `constructor(containerId) {` |
| 15 | método | `async load(endpoint, filters = {}) {` |
| 33 | método | `render() {` |
| 67 | método | `goToPage(page) {` |
| 75 | método | `nextPage() {` |
| 83 | método | `prevPage() {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 17 | `const response = await api.get(endpoint, {` |

## assets/js/view-transitions.js

[Fuente](../../assets/js/view-transitions.js) · [Código completo](REFERENCIA.md#assets-js-view-transitions-js)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 2 | flecha | `const reducedMotion = () =&gt;` |
| 7 | función | `function fallback(target, update)` |
| 44 | función | `function run(update, target = document.querySelector('.main-content .container-xl')` |
| 55 | flecha | `const complete = () =&gt;` |
| 76 | función | `function transitionLayer()` |
| 100 | función | `function canNavigate(anchor, event)` |
| 116 | función | `function navigate(url)` |
| 131 | función | `function showGlobalNavigationLoader()` |
| 138 | función | `function preparePageEntry()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 158 | `    document.addEventListener('click', event =&gt; {` |
| 166 | `    window.addEventListener('beforeunload', showGlobalNavigationLoader);` |
| 167 | `    window.addEventListener('pageshow', () =&gt; {` |

## includes/alert.php

[Fuente](../../includes/alert.php) · [Código completo](REFERENCIA.md#includes-alert-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 9 | función | `function showAlert($type = 'info', $message = '', $dismissible = true)` |

## includes/config.php

[Fuente](../../includes/config.php) · [Código completo](REFERENCIA.md#includes-config-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 37 | función | `function sgpi_base64url_encode($value)` |
| 41 | función | `function sgpi_base64url_decode($value)` |
| 46 | función | `function sgpi_auth_signature($token, $encodedUser)` |
| 50 | función | `function sgpi_auth_signature_v2($token, $encodedUser, $remember)` |
| 54 | función | `function sgpi_request_is_secure()` |
| 60 | función | `function sgpi_cookie_options($expires = 0)` |
| 71 | función | `function persist_auth_session($token, $user, $remember = true)` |
| 95 | función | `function clear_auth_session_cookies()` |
| 96 | método | `foreach ([SGPI_AUTH_TOKEN_COOKIE, SGPI_AUTH_USER_COOKIE, SGPI_AUTH_REMEMBER_COOKIE, SGPI_AUTH_SIGNATURE_COOKIE, SGPI_COOKIE_SUPPORT_COOKIE] as $cookie) {` |
| 104 | función | `function restore_auth_session_from_cookies()` |
| 151 | función | `function is_authenticated()` |
| 156 | función | `function get_user_role()` |
| 162 | función | `function is_admin()` |
| 166 | función | `function is_general_admin()` |
| 170 | función | `function is_teacher()` |
| 174 | función | `function is_student()` |
| 178 | función | `function is_career_head()` |
| 182 | función | `function is_career_head_assistant()` |
| 186 | función | `function is_project_coordinator()` |
| 190 | función | `function can_manage_academics()` |
| 194 | función | `function can_manage_projects()` |
| 198 | función | `function is_management_staff()` |
| 202 | función | `function can_govern_users()` |
| 206 | función | `function profile_role_label()` |
| 219 | función | `function is_evaluation_manager()` |
| 222 | función | `function dashboard_url()` |
| 229 | función | `function active_career()` |
| 233 | función | `function active_career_id()` |
| 237 | función | `function available_careers()` |
| 241 | función | `function profile_photo_url($user = null)` |
| 250 | función | `function requireAuth($minRole = null)` |
| 265 | función | `function requireAdmin()` |
| 275 | función | `function requireTeacher()` |
| 282 | función | `function requireGuest()` |

## includes/footer.php

[Fuente](../../includes/footer.php) · [Código completo](REFERENCIA.md#includes-footer-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 15 | función | `async function loadPublicSettings(options = {})` |
| 45 | función | `function applySystemSettings(settings)` |
| 94 | función | `function systemSettingsSignature(settings)` |
| 109 | función | `function broadcastSystemSettings(settings)` |
| 120 | función | `function startSystemSettingsSync()` |
| 138 | flecha | `const refreshIfStale = () =&gt;` |
| 153 | función | `function placeGlobalSystemNotice(notice)` |
| 162 | función | `function syncGlobalNoticeOffset()` |
| 177 | función | `function startIdleLogoutTimer(minutes)` |
| 187 | flecha | `const resetTimer = () =&gt;` |
| 207 | función | `function currentAudienceContext()` |
| 223 | función | `function noticeMatchesCurrentAudience(notice, context)` |
| 232 | función | `function activeSystemNoticesForCurrentAudience(notices)` |
| 249 | función | `function renderNotificationMenu(notices)` |
| 290 | función | `function initNotificationMenu()` |
| 308 | función | `function readNotificationIds()` |
| 316 | función | `function writeNotificationIds(ids)` |
| 324 | función | `function notificationIcon(type)` |
| 334 | función | `function formatNotificationDate(value)` |
| 345 | función | `function queueSystemNoticeToasts(notices)` |
| 372 | función | `function showSystemNoticeToast(notice)` |
| 397 | función | `function noticeToastDuration(notice)` |
| 402 | función | `function noticeSeenKey(notice)` |
| 406 | función | `function readSeenNoticeIds()` |
| 414 | función | `function writeSeenNoticeIds(ids)` |
| 418 | función | `function escapeToastHtml(value)` |
| 428 | función | `function enhancePasswordVisibility()` |
| 494 | función | `function captureDialogViewport()` |
| 513 | función | `function restoreDialogViewport(viewport, restoreFocus = false)` |
| 538 | flecha | `const restore = restoreFocus =&gt;` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 124 | `    window.addEventListener('storage', event =&gt; {` |
| 143 | `    window.addEventListener('focus', refreshIfStale, { passive: true });` |
| 144 | `    document.addEventListener('visibilitychange', () =&gt; {` |
| 172 | `window.addEventListener('resize', syncGlobalNoticeOffset, { passive: true });` |
| 173 | `window.addEventListener('orientationchange', syncGlobalNoticeOffset, { passive: true });` |
| 198 | `            window.addEventListener(eventName, () =&gt; window.SGPI_RESET_IDLE_TIMER?.(), { passive: true });` |
| 296 | `    menu.addEventListener('shown.bs.dropdown', () =&gt; {` |
| 447 | `        button.addEventListener('click', () =&gt; {` |
| 456 | `document.addEventListener('DOMContentLoaded', enhancePasswordVisibility);` |
| 457 | `document.addEventListener('DOMContentLoaded', initNotificationMenu);` |
| 459 | `window.addEventListener('pageshow', function () {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 22 | `const response = await fetch(url.toString(), {` |

## includes/modal.php

[Fuente](../../includes/modal.php) · [Código completo](REFERENCIA.md#includes-modal-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 21 | función | `function openModal(title, content, callback)` |
| 28 | función | `function confirmModal()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 14 | `                &lt;button type="button" class="btn btn-primary" onclick="confirmModal()"&gt;Confirmar&lt;/button&gt;` |

## includes/navbar.php

[Fuente](../../includes/navbar.php) · [Código completo](REFERENCIA.md#includes-navbar-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 288 | flecha | `const setLabel = () =&gt;` |
| 313 | función | `async function logout()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 264 | `                            &lt;li&gt;&lt;a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"&gt;` |
| 282 | `document.addEventListener('DOMContentLoaded', () =&gt; {` |
| 293 | `        toggle.addEventListener('click', () =&gt; {` |
| 303 | `        link.addEventListener('click', event =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 326 | `fetch(`${apiBaseUrl}/auth/logout`, {` |

## includes/validations.php

[Fuente](../../includes/validations.php) · [Código completo](REFERENCIA.md#includes-validations-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 15 | función | `function validar_calificacion($calificacion)` |
| 28 | función | `function validar_mime_type($mime_type)` |
| 46 | función | `function validar_tamaño_archivo($tamaño_bytes)` |

## index.php

[Fuente](../../index.php) · [Código completo](REFERENCIA.md#index-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 674 | función | `function dashboardUrlForCurrentUser()` |
| 680 | función | `function escapeHtml(value)` |
| 684 | función | `function modalAlert(type, message)` |
| 688 | función | `function setButtonLoading(button, loadingText, originalText = null)` |
| 694 | función | `function restoreButton(button)` |
| 699 | función | `function openPasswordRecoveryModal()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 547 | `               &lt;button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="openPasswordRecoveryModal()"&gt;¿Olvidaste tu contraseña?&lt;/button&gt;` |
| 707 | `        document.addEventListener('DOMContentLoaded', () =&gt; {` |
| 725 | `            cookieNoticeAccept?.addEventListener('click', () =&gt; {` |
| 739 | `                link.addEventListener('click', event =&gt; {` |
| 756 | `        document.getElementById('passwordRecoveryForm').addEventListener('submit', async (event) =&gt; {` |
| 789 | `        document.getElementById('passwordTokenForm').addEventListener('submit', async (event) =&gt; {` |
| 818 | `        document.getElementById('passwordResetForm').addEventListener('submit', async (event) =&gt; {` |
| 870 | `        loginForm.addEventListener('submit', async (event) =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 773 | `await api.post('/auth/password/request-token', {` |
| 805 | `await api.post('/auth/password/verify-token', recoveryState);` |
| 840 | `const response = await api.post('/auth/password/reset', {` |
| 848 | `await axios.post('/api/set-session.php', {` |
| 903 | `await axios.post('/api/set-session.php', {` |

## pages/academic-workflows.php

[Fuente](../../pages/academic-workflows.php) · [Código completo](REFERENCIA.md#pages-academic-workflows-php)

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 21 | `const response = await api.get('/auth/me', { _cache_ttl: 0 });` |
| 24 | `const response = await fetch(API_BASE_URL + url, { headers: { Authorization: `Bearer ${auth.getToken()}` } });` |
| 30 | `return api.request(method, url, body, { _cache_ttl: 0 });` |

## pages/admin/advisors.php

[Fuente](../../pages/admin/advisors.php) · [Código completo](REFERENCIA.md#pages-admin-advisors-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 183 | función | `function escapeHtml(value)` |
| 187 | función | `function fullName(user)` |
| 191 | función | `function projectActiveAuthors(project, emptyText = 'Sin integrantes registrados')` |
| 200 | función | `function profileName(user)` |
| 204 | función | `function profileBadge(user)` |
| 210 | función | `function advisorRoleBadge(advisor)` |
| 218 | función | `function roleLabel(role)` |
| 222 | función | `function advisorByRole(project, role)` |
| 226 | función | `function activeAssignmentRoles()` |
| 230 | función | `function activeAssignmentProjects()` |
| 234 | función | `function advisorForTeacher(project, teacherId)` |
| 238 | función | `function projectsForTeacher(teacherId)` |
| 242 | función | `function setAdvisorView(view)` |
| 258 | función | `function teacherOptions(selectedId, blockedIds = [])` |
| 270 | función | `async function loadTeachers()` |
| 279 | función | `async function loadProjects()` |
| 309 | función | `function renderAssignmentTable(tableId, view, roles, rows, emptyText, colspan)` |
| 318 | flecha | `const blockedByRole = roleKey =&gt;` |
| 351 | función | `function renderTeacherAdvisorView()` |
| 448 | función | `function focusProjectAssignment(projectId, view = 'projects')` |
| 458 | función | `async function showProjectDetails(projectId)` |
| 487 | función | `function syncBlockedOptions(projectId)` |
| 503 | función | `function markAdvisorRow(projectId)` |
| 519 | función | `function changedAdvisorProjectIds()` |
| 531 | función | `function updateSaveAllButton()` |
| 555 | función | `function revertAdvisorRow(projectId)` |
| 568 | función | `async function assignAdvisor(projectId, role, userId, adminPassword)` |
| 577 | función | `async function removeAdvisor(projectId, userId, adminPassword)` |
| 581 | función | `async function saveAdvisors(projectId)` |
| 619 | función | `async function saveAllAdvisorChanges()` |
| 664 | función | `async function applyAdvisorChanges(projectId, adminPassword)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 38 | `                        &lt;select id="semesterFilter" class="form-select" onchange="loadProjects()"&gt;` |
| 50 | `                    &lt;button type="button" class="btn btn-primary" id="viewByProjectBtn" onclick="setAdvisorView('projects')"&gt;` |
| 53 | `                &lt;button type="button" class="btn btn-outline-secondary" id="viewThesisCommitteeBtn" onclick="setAdvisorView('thesis')"&gt;` |
| 56 | `                    &lt;button type="button" class="btn btn-outline-secondary" id="viewByTeacherBtn" onclick="setAdvisorView('teachers')"&gt;` |
| 59 | `                   &lt;button type="button" class="btn btn-success ms-md-auto" id="saveAllAdvisorsBtn" onclick="saveAllAdvisorChanges()" disabled&gt;` |
| 117 | `nput type="search" class="form-control" id="teacherSearch" placeholder="Nombre, apellidos o nómina" oninput="renderTeacherAdvisorView()"&gt;` |
| 122 | `                                    &lt;select class="form-select" id="teacherRoleFilter" onchange="renderTeacherAdvisorView()"&gt;` |
| 337 | `                          &lt;select class="form-select form-select-sm" id="${role.key}-${project.id}" onchange="syncBlockedOptions(${project.id}); markAdvisorRow(${project.id})"&gt;` |
| 343 | `                                &lt;button class="btn btn-outline-warning" onclick="revertAdvisorRow(${project.id})" title="Deshacer cambios"&gt;&lt;i class="bi bi-arrow-counterclockwise"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 344 | `                                &lt;button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver proyecto"&gt;&lt;i class="bi bi-folder2-open"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 405 | `                                &lt;button type="button" class="btn btn-sm btn-outline-primary" onclick="showProjectDetails(${project.id})"&gt;` |
| 408 | `                                &lt;button type="button" class="btn btn-sm btn-outline-secondary" onclick="focusProjectAssignment(${project.id}, 'projects')"&gt;` |
| 411 | `                ${project.is_thesis ? `&lt;button type="button" class="btn btn-sm btn-outline-success" onclick="focusProjectAssignment(${project.id}, 'thesis')"&gt;` |
| 686 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 272 | `api.get('/users', { perfil_id: 1, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 }),` |
| 273 | `api.get('/users', { perfil_id: 2, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 })` |
| 290 | `api.get('/projects', { ...commonParams, tipo_registro: 'proyecto' }),` |
| 291 | `api.get('/projects', { ...commonParams, tipo_registro: 'tesis' })` |
| 460 | `const project = await api.get(`/projects/${projectId}`);` |
| 570 | `await api.post(`/projects/${projectId}/advisors`, {` |
| 579 | `await api.delete(`/projects/${projectId}/advisors/${encodeURIComponent(userId)}`, { admin_password: adminPassword });` |

## pages/admin/asignaturas.php

[Fuente](../../pages/admin/asignaturas.php) · [Código completo](REFERENCIA.md#pages-admin-asignaturas-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 260 | función | `function escapeHtml(value)` |
| 264 | función | `function toDateInput(value)` |
| 268 | función | `function getEstado(fechaInicio, fechaFin)` |
| 278 | función | `async function loadAsignaturas(page = 1)` |
| 328 | función | `function renderAsignaturasPagination(pagination)` |
| 368 | función | `function changeAsignaturasPerPage(value)` |
| 373 | función | `function openAsignaturaModal(id = null)` |
| 408 | función | `async function openCompetenciasModal(asignaturaId)` |
| 415 | flecha | `const showManager = async () =&gt;` |
| 428 | función | `async function openCompetenciasLaúncher()` |
| 444 | función | `function renderCompetenciasSubjects()` |
| 471 | función | `async function loadCompetenciasByAsignatura()` |
| 503 | función | `function resetCompetenciaForm()` |
| 509 | función | `function editCompetencia(id)` |
| 542 | función | `async function deleteCompetencia(id)` |
| 557 | función | `function renderSubjectCheckboxes(selectedIds = [])` |
| 567 | función | `async function loadAllAsignaturas()` |
| 586 | función | `async function openGroupsModal()` |
| 616 | función | `async function loadSubjectGroups()` |
| 649 | función | `function editSubjectGroup(id)` |
| 660 | función | `async function saveSubjectGroup()` |
| 693 | función | `async function deleteSubjectGroup(id)` |
| 709 | función | `async function deleteAsignatura(id)` |
| 720 | función | `async function viewAsignaturaStructure(id)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 29 | `                        &lt;button class="btn btn-outline-primary" id="cargas" onclick="openGroupsModal()"&gt;` |
| 32 | `                        &lt;button class="btn btn-outline-primary" type="button" onclick="openCompetenciasLaúncher()"&gt;` |
| 35 | `                        &lt;button class="btn btn-primary" onclick="openAsignaturaModal()"&gt;` |
| 146 | `                           placeholder="Clave o nombre de la asignatura" oninput="renderCompetenciasSubjects()"&gt;` |
| 197 | `                            &lt;select class="form-select" id="groupSemestre" onchange="loadSubjectGroups()"&gt;` |
| 218 | `                            &lt;button class="btn btn-primary w-100" onclick="saveSubjectGroup()"&gt;&lt;i class="bi bi-save"&gt;&lt;/i&gt; Guardar&lt;/button&gt;` |
| 314 | `                                    &lt;button class="btn btn-outline-primary" onclick="openCompetenciasModal(${asignatura.id})" title="Gestionar competencias"&gt;&lt;i class="bi bi-star"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 315 | `                                    &lt;button class="btn btn-outline-info" onclick="viewAsignaturaStructure(${asignatura.id})" title="Ver estructura"&gt;&lt;i class="bi bi-diagram-3"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 316 | `                                    &lt;button class="btn btn-outline-secondary" onclick="openAsignaturaModal(${asignatura.id})" title="Editar asignatura"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 317 | `                                    &lt;button class="btn btn-outline-danger" onclick="deleteAsignatura(${asignatura.id})" title="Eliminar asignatura"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 342 | `                        &lt;button class="page-link" type="button" onclick="loadAsignaturas(${page})"&gt;${page}&lt;/button&gt;` |
| 350 | `  &lt;select class="form-select form-select-sm" style="width:auto" aria-label="Asignaturas por página" onchange="changeAsignaturasPerPage(this.value)"&gt;` |
| 357 | `                                &lt;button class="page-link" type="button" onclick="loadAsignaturas(${current - 1})" ${current &lt;= 1 ? 'disabled' : ''}&gt;Anterior&lt;/button&gt;` |
| 361 | `                                &lt;button class="page-link" type="button" onclick="loadAsignaturas(${current + 1})" ${current &gt;= last ? 'disabled' : ''}&gt;Siguiente&lt;/button&gt;` |
| 388 | `        document.getElementById('asignaturaForm').addEventListener('submit', async (event) =&gt; {` |
| 421 | `                laúncherElement.addEventListener('hidden.bs.modal', showManager, { once: true });` |
| 458 | `                        onclick="openCompetenciasModal(${Number(asignatura.id)})"&gt;` |
| 492 | `                                &lt;button class="btn btn-outline-secondary" onclick="editCompetencia(${competencia.id})" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 493 | `                                &lt;button class="btn btn-outline-danger" onclick="deleteCompetencia(${competencia.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 518 | `        document.getElementById('competenciaForm').addEventListener('submit', async event =&gt; {` |
| 639 | `                                &lt;button class="btn btn-outline-primary" onclick="editSubjectGroup(${group.id})" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 640 | `                                &lt;button class="btn btn-outline-danger" onclick="deleteSubjectGroup(${group.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 758 | `        document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 280 | `const response = await api.get('/asignaturas', {` |
| 398 | `if (id) await api.put(`/asignaturas/${id}`, data);` |
| 399 | `else await api.post('/asignaturas', data);` |
| 411 | `&#124;&#124; await api.get(`/asignaturas/${asignaturaId}`);` |
| 476 | `const response = await api.get('/competencias', { asignatura_id: currentAsignatura.id, per_page: 100 });` |
| 529 | `if (id) await api.put(`/competencias/${id}`, data);` |
| 530 | `else await api.post('/competencias', data);` |
| 545 | `await api.delete(`/competencias/${id}`);` |
| 573 | `const response = await api.get('/asignaturas', {` |
| 597 | `api.get('/settings/current', { _cache_ttl: 60000 })` |
| 622 | `subjectGroups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |
| 679 | `if (id) await api.put(`/subject-groups/${id}`, data);` |
| 680 | `else await api.post('/subject-groups', data);` |
| 702 | `await api.delete(`/subject-groups/${id}`);` |
| 712 | `await api.delete(`/asignaturas/${id}`);` |
| 726 | `const asignatura = await api.get(`/asignaturas/${id}`);` |

## pages/admin/career-audit.php

[Fuente](../../pages/admin/career-audit.php) · [Código completo](REFERENCIA.md#pages-admin-career-audit-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 69 | función | `function auditEscape(value)` |
| 70 | función | `function auditParams()` |
| 73 | función | `async function loadAudit()` |
| 99 | función | `async function initializeAudit()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 106 | `auditFilters.addEventListener('submit', event =&gt; { event.preventDefault(); auditPage = 1; loadAudit(); });` |
| 107 | `auditPrevious.addEventListener('click', () =&gt; { if (auditPage &gt; 1) { auditPage--; loadAudit(); } });` |
| 108 | `auditNext.addEventListener('click', () =&gt; { if (auditPage &lt; auditLastPage) { auditPage++; loadAudit(); } });` |
| 109 | `document.addEventListener('DOMContentLoaded', initializeAudit);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 76 | `const response = await api.get('/admin/audit', auditParams());` |
| 101 | `const response = await api.get('/admin/careers');` |

## pages/admin/career-integrity.php

[Fuente](../../pages/admin/career-integrity.php) · [Código completo](REFERENCIA.md#pages-admin-career-integrity-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 64 | función | `function integrityEscape(value)` |
| 67 | función | `async function loadIntegrity(store = false)` |
| 102 | función | `async function loadIntegrityHistory()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 120 | `document.getElementById('runIntegrityButton').addEventListener('click', () =&gt; loadIntegrity(true));` |
| 121 | `document.addEventListener('DOMContentLoaded', () =&gt; { loadIntegrity(); loadIntegrityHistory(); });` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 72 | `? await api.post('/admin/integrity/run', {})` |
| 73 | `: await api.get('/admin/integrity', {_fresh: true});` |
| 104 | `const response = await api.get('/admin/integrity/history', {_fresh: true});` |

## pages/admin/career-modules.php

[Fuente](../../pages/admin/career-modules.php) · [Código completo](REFERENCIA.md#pages-admin-career-modules-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 87 | flecha | `const moduleEsc = value =&gt;` |
| 89 | función | `function moduleMessage(type, text)` |
| 93 | función | `async function loadModules()` |
| 107 | función | `function renderModules()` |
| 131 | función | `function selectModule(name)` |
| 139 | función | `async function loadRecords()` |
| 161 | función | `function renderIndicators()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 119 | `    document.querySelectorAll('[data-select-module]').forEach(button =&gt; button.addEventListener('click', () =&gt; selectModule(button.dataset.selectModule)));` |
| 120 | `    document.querySelectorAll('[data-toggle-module]').forEach(input =&gt; input.addEventListener('change', async () =&gt; {` |
| 151 | `        document.querySelectorAll('[data-delete-record]').forEach(button =&gt; button.addEventListener('click', async () =&gt; {` |
| 171 | `    document.querySelectorAll('[data-indicator]').forEach(form =&gt; form.addEventListener('submit', async event =&gt; {` |
| 187 | `document.getElementById('recordForm').addEventListener('submit', async event =&gt; {` |
| 206 | `document.addEventListener('DOMContentLoaded', loadModules);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 95 | `const response = await api.get('/career/modules', {_fresh: true});` |
| 122 | `await api.put(`/career/modules/${input.dataset.toggleModule}`, {habilitado: input.checked});` |
| 144 | `const response = await api.get('/career/module-records', {modulo: moduleState.selected.modulo, _fresh: true});` |
| 153 | `await api.delete(`/career/module-records/${button.dataset.deleteRecord}`);` |
| 175 | `await api.put(`/career/indicators/${form.dataset.indicator}`, {` |
| 191 | `await api.post('/career/module-records', {` |

## pages/admin/career-setup.php

[Fuente](../../pages/admin/career-setup.php) · [Código completo](REFERENCIA.md#pages-admin-career-setup-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 90 | función | `function setupEscape(value)` |
| 94 | función | `function parseCsv(text)` |
| 118 | función | `async function readSetupFile(type, file)` |
| 137 | función | `function updateSetupSummary()` |
| 145 | función | `function downloadTemplate(type)` |
| 157 | función | `async function downloadCareerPackage()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 33 | `                &lt;button data-career-module="reportes" class="btn btn-outline-success" type="button" onclick="downloadCareerPackage()"&gt;&lt;i class="bi bi-file-earmark-zip"&gt;&lt;/i&gt; Exportar carrera&lt;/button&gt;` |
| 45 | `                            &lt;button class="btn btn-sm btn-outline-secondary mb-3" type="button" onclick="downloadTemplate('subjects')"&gt;&lt;i class="bi bi-download"&gt;&lt;/i&gt; Descargar plantilla&lt;/button&gt;` |
| 56 | `                            &lt;button class="btn btn-sm btn-outline-secondary mb-3" type="button" onclick="downloadTemplate('groups')"&gt;&lt;i class="bi bi-download"&gt;&lt;/i&gt; Descargar plantilla&lt;/button&gt;` |
| 174 | `document.getElementById('subjectsFile').addEventListener('change', event =&gt; event.target.files[0] && readSetupFile('subjects', event.target.files[0]));` |
| 175 | `document.getElementById('groupsFile').addEventListener('change', event =&gt; event.target.files[0] && readSetupFile('groups', event.target.files[0]));` |
| 176 | `document.getElementById('importButton').addEventListener('click', async () =&gt; {` |
| 189 | `document.getElementById('initializeRubricsButton').addEventListener('click', async () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 159 | `const response = await fetch(`${window.SGPI_API_BASE_URL}/career/export`, {` |
| 180 | `const response = await api.post('/career/setup/catalog', setupState);` |
| 193 | `const response = await api.post('/evaluations/rubrics/initialize', {});` |

## pages/admin/careers.php

[Fuente](../../pages/admin/careers.php) · [Código completo](REFERENCIA.md#pages-admin-careers-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 162 | función | `function careerEsc(value)` |
| 166 | función | `function careerMessage(type, message)` |
| 170 | función | `function renderCareerCards()` |
| 182 | función | `function renderCareerOverview()` |
| 216 | función | `function enterCareer(careerId)` |
| 225 | función | `function selectCareer(id)` |
| 241 | función | `async function loadCareers()` |
| 252 | función | `async function downloadInstitutionalSummary()` |
| 269 | función | `async function loadMemberships()` |
| 299 | función | `function bindMembershipActions()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 33 | `                    &lt;button class="btn btn-outline-primary" type="button" onclick="downloadInstitutionalSummary()"&gt;&lt;i class="bi bi-file-earmark-spreadsheet"&gt;&lt;/i&gt; Exportar resumen&lt;/button&gt;` |
| 178 | `    document.querySelectorAll('.career-admin-card').forEach(card =&gt; card.addEventListener('click', () =&gt; selectCareer(Number(card.dataset.id))));` |
| 213 | `    document.querySelectorAll('[data-enter-career]').forEach(button =&gt; button.addEventListener('click', () =&gt; enterCareer(Number(button.dataset.enterCareer))));` |
| 300 | `    document.querySelectorAll('[data-toggle-membership]').forEach(button =&gt; button.addEventListener('click', async () =&gt; {` |
| 304 | `    document.querySelectorAll('[data-delete-membership]').forEach(button =&gt; button.addEventListener('click', async () =&gt; {` |
| 311 | `document.getElementById('careerIdentityForm').addEventListener('submit', async event =&gt; {` |
| 330 | `document.getElementById('membershipForm').addEventListener('submit', async event =&gt; {` |
| 348 | `document.getElementById('membershipSearchForm').addEventListener('submit', event =&gt; {` |
| 352 | `document.addEventListener('DOMContentLoaded', loadCareers);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 243 | `const response = await api.get('/admin/careers', {_fresh: true});` |
| 254 | `const response = await fetch(`${window.SGPI_API_BASE_URL}/admin/careers/export`, {` |
| 274 | `const response = await api.get('/admin/career-memberships', {` |
| 301 | `await api.put(`/admin/career-memberships/${button.dataset.toggleMembership}`, {activo: button.dataset.active !== '1'});` |
| 306 | `await api.delete(`/admin/career-memberships/${button.dataset.deleteMembership}`);` |
| 315 | `await api.put(`/admin/careers/${id}`, {` |
| 333 | `await api.post('/admin/career-memberships', {` |

## pages/admin/dashboard.php

[Fuente](../../pages/admin/dashboard.php) · [Código completo](REFERENCIA.md#pages-admin-dashboard-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 300 | función | `function escapeHtml(value)` |
| 304 | función | `function percent(value, total)` |
| 308 | función | `function statusLabel(status)` |
| 326 | función | `function renderBarChart(containerId, data)` |
| 348 | función | `function renderStatusGrid(containerId, data)` |
| 365 | función | `function renderCareerIndicators(containerId, indicators)` |
| 380 | función | `function updateAdminNextAction(stats)` |
| 406 | función | `function setDashboardText(id, value)` |
| 411 | función | `function setDashboardWidth(id, value)` |
| 416 | función | `function formatDashboardDate(value)` |
| 427 | función | `function renderProjectOverview(response = {})` |
| 483 | función | `function renderProjectOverviewError()` |
| 494 | función | `async function loadDashboard()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 534 | `        document.addEventListener('DOMContentLoaded', loadDashboard);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 497 | `api.get('/dashboard/stats'),` |
| 498 | `api.get('/career/modules')` |

## pages/admin/database-backups.php

[Fuente](../../pages/admin/database-backups.php) · [Código completo](REFERENCIA.md#pages-admin-database-backups-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 73 | función | `function backupEscape(value)` |
| 76 | función | `function formatBackupSize(bytes)` |
| 83 | función | `async function loadBackupHealth()` |
| 113 | función | `async function loadBackups()` |
| 145 | función | `async function createBackup()` |
| 159 | función | `async function verifyBackup(id, button)` |
| 172 | función | `async function cleanupBackups(days)` |
| 183 | función | `async function downloadBackup(id, filename)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 104 | `ss="btn btn-sm btn-outline-danger" type="button" ${Number(health.eligible_count) ? '' : 'disabled'} onclick="cleanupBackups(${retentionDays})"&gt;` |
| 135 | `                    ? `&lt;button class="btn btn-sm btn-outline-success" type="button" onclick="verifyBackup(${Number(backup.id)}, this)"&gt;&lt;i class="bi bi-shield-check"&gt;&lt;/i&gt; Verificar&lt;/button&gt;` |
| 136 | `                       &lt;button class="btn btn-sm btn-outline-primary" type="button" onclick="downloadBackup(${Number(backup.id)}, '${backupEscape(backup.nombre_archivo)}')"&gt;&lt;i class="bi bi-download"&gt;&lt;/i&gt; Descargar&lt;/button&gt;`` |
| 204 | `document.getElementById('createBackupButton').addEventListener('click', createBackup);` |
| 205 | `document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 86 | `const health = await api.get('/admin/database-backups-health', {_fresh: true});` |
| 116 | `const response = await api.get('/admin/database-backups', {_fresh: true});` |
| 150 | `await api.post('/admin/database-backups', {}, {_timeout: 300000});` |
| 164 | `await api.post(`/admin/database-backups/${id}/verify`, {}, {_timeout: 300000});` |
| 177 | `await api.post('/admin/database-backups-cleanup', {retention_days: days, confirmation});` |
| 185 | `const response = await fetch(`${API_BASE_URL}/admin/database-backups/${id}/download`, {` |

## pages/admin/deliverables.php

[Fuente](../../pages/admin/deliverables.php) · [Código completo](REFERENCIA.md#pages-admin-deliverables-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 217 | función | `function escapeHtml(value)` |
| 221 | función | `async function loadCatalogs()` |
| 254 | función | `function renderCompetenceFilter()` |
| 262 | función | `function onSubjectFilterChange()` |
| 268 | función | `async function loadDeliverables(page = 1)` |
| 328 | función | `function openDeliverableModal(id = null)` |
| 375 | función | `async function deleteDeliverable(id)` |
| 386 | función | `function abrirCalificar(id, projectId)` |
| 393 | función | `async function guardarCalificacion()` |
| 402 | función | `function abrirSubirArchivo(id, projectId)` |
| 409 | función | `async function guardarArchivo()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 41 | `                        &lt;button class="btn btn-primary" onclick="openDeliverableModal()"&gt;` |
| 50 | `                        &lt;select class="form-select" id="subjectFilter" onchange="onSubjectFilterChange()"&gt;` |
| 56 | `                        &lt;select class="form-select" id="competenceFilter" onchange="loadDeliverables()"&gt;` |
| 170 | `                    &lt;button type="button" class="btn btn-primary" onclick="guardarCalificacion()"&gt;Calificar&lt;/button&gt;` |
| 191 | `                    &lt;button type="button" class="btn btn-primary" onclick="guardarArchivo()"&gt;Subir&lt;/button&gt;` |
| 301 | `                        ? `&lt;button class="btn btn-outline-info" onclick="descargarEntregable(${deliverable.id}, '${escapeHtml(deliverable.nombre)}', ${deliverable.project_id})" title="Descargar"&gt;&lt;i class="bi bi-download"&gt;&lt;/i&gt;&lt;/button&gt;`` |
| 314 | `                                    &lt;button class="btn btn-outline-primary" onclick="openDeliverableModal(${deliverable.id})" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 315 | `                                    &lt;button class="btn btn-outline-warning" onclick="abrirCalificar(${deliverable.id}, ${deliverable.project_id})" title="Calificar"&gt;&lt;i class="bi bi-star"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 317 | `                                    &lt;button class="btn btn-outline-success" onclick="abrirSubirArchivo(${deliverable.id}, ${deliverable.project_id})" title="Subir archivo"&gt;&lt;i class="bi bi-cloud-upload"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 318 | `                                    &lt;button class="btn btn-outline-danger" onclick="deleteDeliverable(${deliverable.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 350 | `        document.getElementById('deliverableForm').addEventListener('submit', async (event) =&gt; {` |
| 422 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 223 | `api.get('/projects', { compact: 1, per_page: 500, _cache_ttl: 30000 }),` |
| 224 | `api.get('/competencias', { per_page: 100, _cache_ttl: 60000 })` |
| 276 | `const response = await api.get('/deliverables', params);` |
| 365 | `if (id) await api.put(`/deliverables/${id}`, data);` |
| 366 | `else await api.post('/deliverables', data);` |
| 378 | `await api.delete(`/deliverables/${id}`);` |

## pages/admin/document-tags.php

[Fuente](../../pages/admin/document-tags.php) · [Código completo](REFERENCIA.md#pages-admin-document-tags-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 243 | función | `function escapeHtml(value)` |
| 247 | función | `function contrastColor(hex)` |
| 255 | función | `async function loadTags(page = 1, forceFresh = false)` |
| 317 | función | `function updateSummary(response)` |
| 327 | función | `function renderPagination()` |
| 345 | función | `function scheduleTagSearch()` |
| 350 | función | `function clearTagSearch()` |
| 355 | función | `function renderColorPresets(selected = '#1B396A')` |
| 361 | función | `function setTagColor(color)` |
| 368 | función | `function syncTagColorFromText()` |
| 377 | función | `function refreshTagPreview()` |
| 388 | función | `async function deleteTag(tagId)` |
| 402 | función | `function openTagModal(tagId = null)` |
| 415 | función | `async function saveTag(event)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 98 | `                    &lt;button class="btn btn-primary" type="button" onclick="openTagModal()"&gt;` |
| 132 | `        &lt;input type="search" class="form-control" id="tagSearch" placeholder="Nombre o descripción" oninput="scheduleTagSearch()"&gt;` |
| 133 | `                                &lt;button class="btn btn-outline-secondary" type="button" onclick="clearTagSearch()" title="Limpiar búsqueda"&gt;&lt;i class="bi bi-x-lg"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 138 | `                            &lt;select class="form-select" id="tagPerPage" onchange="loadTags(1)"&gt;` |
| 145 | `                            &lt;button class="btn btn-outline-primary" type="button" onclick="loadTags(currentPage, true)"&gt;` |
| 187 | `            &lt;form class="modal-content" id="tagForm" onsubmit="saveTag(event)"&gt;` |
| 197 | `                      &lt;input type="text" class="form-control" id="tagName" maxlength="100" required oninput="refreshTagPreview()"&gt;` |
| 206 | `" class="form-control form-control-color" id="tagColor" value="#1B396A" title="Selecciona un color" onchange="refreshTagPreview()"&gt;` |
| 207 | `            &lt;input type="text" class="form-control" id="tagColorText" maxlength="7" value="#1B396A" oninput="syncTagColorFromText()"&gt;` |
| 299 | `                                    &lt;button class="btn btn-outline-primary" onclick="openTagModal(${Number(tag.id)})" title="Editar"&gt;` |
| 302 | `                                    &lt;button class="btn btn-outline-danger" onclick="deleteTag('${tag.id}')" title="Eliminar"&gt;` |
| 336 | `                        &lt;button class="page-link" onclick="loadTags(${currentPage - 1})"&gt;Anterior&lt;/button&gt;` |
| 340 | `                        &lt;button class="page-link" onclick="loadTags(${currentPage + 1})"&gt;Siguiente&lt;/button&gt;` |
| 357 | `eset ${color.toLowerCase() === selected.toLowerCase() ? 'active' : ''}" style="background:${color}" onclick="setTagColor('${color}')" title="${color}"&gt;&lt;/button&gt;` |
| 446 | `        document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 265 | `const response = await api.get('/document-tags', params);` |
| 396 | `api.delete(`/document-tags/${tagId}`).then(() =&gt; {` |
| 434 | `await (tagId ? api.put(`/document-tags/${tagId}`, payload) : api.post('/document-tags', payload));` |

## pages/admin/evaluation-rooms.php

[Fuente](../../pages/admin/evaluation-rooms.php) · [Código completo](REFERENCIA.md#pages-admin-evaluation-rooms-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 437 | función | `function escapeHtml(value)` |
| 441 | función | `function projectSemester(project)` |
| 445 | función | `function fullName(user)` |
| 449 | función | `function roomDisplayIdentifier(room)` |
| 459 | función | `function roomDisplayName(room)` |
| 463 | función | `function projectActiveAuthors(project)` |
| 468 | función | `function projectCompany(project)` |
| 472 | función | `function normalizeRoomName(value)` |
| 479 | función | `function normalizeClassroom(value)` |
| 486 | función | `function parseRoomDate(value)` |
| 493 | función | `function formatLocalDateTimeInput(date)` |
| 494 | flecha | `const pad = value =&gt;` |
| 498 | función | `function nextMinuteInput()` |
| 505 | función | `function roomRange(room)` |
| 511 | función | `function rangesOverlap(startA, endA, startB, endB)` |
| 515 | función | `function orderedRoomProjects(room)` |
| 526 | función | `function roomForProject(projectId)` |
| 530 | función | `function currentSemesterFilter()` |
| 534 | función | `function searchText()` |
| 538 | función | `function filteredRooms()` |
| 555 | función | `function filteredProjects()` |
| 567 | función | `async function loadTeachers()` |
| 576 | función | `async function loadProjects()` |
| 580 | función | `async function loadRooms()` |
| 585 | función | `async function reloadRooms()` |
| 590 | función | `async function loadInitialData()` |
| 605 | función | `function setRoomView(view)` |
| 619 | función | `function renderCurrentView()` |
| 626 | función | `function renderSummary()` |
| 637 | función | `function renderRooms()` |
| 685 | función | `function roomTimerMarkup(room)` |
| 707 | función | `function tickRoomTimers()` |
| 717 | función | `function renderProjectRoomView()` |
| 746 | función | `function renderTeacherRoomView()` |
| 789 | función | `function conflictingRoomsForCurrentForm()` |
| 801 | función | `function busyRoomIds()` |
| 809 | función | `function currentRoomProjectState()` |
| 820 | función | `function normalizeProjectOrderMap(selectedIds, orderMap = {})` |
| 830 | función | `function scheduleRoomProjectsReorder()` |
| 838 | función | `async function updateRoomAvailability()` |
| 851 | función | `function renderRoomTeachers(selected = [])` |
| 866 | función | `function refreshResponsibleTeacherOptions(selected = null)` |
| 880 | función | `function loadRoomProjects(selected = [], orderMap = {})` |
| 918 | función | `function renderAvailabilityHint(busy = busyRoomIds()` |
| 939 | función | `function focusRoomForm()` |
| 945 | función | `async function startNewRoom()` |
| 954 | función | `async function editRoom(id)` |
| 977 | función | `function resetRoomForm()` |
| 988 | función | `function roomPayloadFromRoom(room, projectsOverride = null)` |
| 1007 | función | `async function saveRoom()` |
| 1071 | función | `async function saveProjectRoom(projectId)` |
| 1097 | función | `async function deleteRoom(id)` |
| 1110 | función | `async function archiveRoom(id)` |
| 1119 | función | `async function unarchiveRoom(id)` |
| 1128 | función | `async function lockRoomSequence(id)` |
| 1135 | función | `async function advanceRoom(id)` |
| 1151 | función | `async function controlRoomTimer(id, action)` |
| 1169 | función | `function openRoomSchedule(id)` |
| 1183 | función | `function toggleLateScheduleFields()` |
| 1227 | función | `function replaceRoomLocal(updatedRoom)` |
| 1235 | función | `function syncProjectRoomAssignments()` |
| 1246 | función | `async function downloadRoomReport(id)` |
| 1250 | función | `async function downloadRoomTeacherReport(id)` |
| 1254 | función | `async function downloadPdf(endpoint, filename, fallbackMessage)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 213 | `                        &lt;select class="form-select" id="semesterFilter" onchange="renderCurrentView()"&gt;` |
| 223 | `                        &lt;select class="form-select" id="roomStatusFilter" onchange="reloadRooms()"&gt;` |
| 232 | `ut type="search" class="form-control" id="roomSearch" placeholder="Sala, docente, proyecto o salon" oninput="renderCurrentView()"&gt;` |
| 238 | `                    &lt;button type="button" class="btn btn-primary" id="viewRoomsBtn" onclick="setRoomView('rooms')"&gt;` |
| 242 | `                    &lt;button type="button" class="btn btn-outline-secondary" id="viewProjectsBtn" onclick="setRoomView('projects')"&gt;` |
| 245 | `                    &lt;button type="button" class="btn btn-outline-secondary" id="viewTeachersBtn" onclick="setRoomView('teachers')"&gt;` |
| 248 | `                    &lt;button type="button" class="btn btn-success ms-md-auto" onclick="startNewRoom()"&gt;` |
| 295 | `                                            &lt;select class="form-select" id="roomSemester" onchange="loadRoomProjects()"&gt;` |
| 304 | `                                    &lt;input type="datetime-local" class="form-control" id="roomDate" onchange="updateRoomAvailability()"&gt;` |
| 309 | `                                 &lt;input type="datetime-local" class="form-control" id="roomEndDate" onchange="updateRoomAvailability()"&gt;` |
| 347 | `                                        &lt;button class="btn btn-outline-secondary" onclick="resetRoomForm()"&gt;Limpiar&lt;/button&gt;` |
| 348 | `                                        &lt;button class="btn btn-primary" onclick="saveRoom()"&gt;&lt;i class="bi bi-save"&gt;&lt;/i&gt; Guardar sala&lt;/button&gt;` |
| 409 | `-12"&gt;&lt;div class="form-check"&gt;&lt;input class="form-check-input" id="scheduleAllowLate" type="checkbox" onchange="toggleLateScheduleFields()"&gt;&lt;label class="form-check-label" for="scheduleAllowLate"&gt;Permitir evaluaciones fuera del horario ordinario&lt;/label&gt;&lt;/div&gt;&lt;/div&gt;` |
| 668 | `                                &lt;button class="btn btn-sm btn-outline-dark" onclick="downloadRoomReport(${room.id})" title="Reporte sala"&gt;&lt;i class="bi bi-file-earmark-pdf"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 669 | `                                &lt;button class="btn btn-sm btn-outline-info" onclick="downloadRoomTeacherReport(${room.id})" title="Reporte docentes"&gt;&lt;i class="bi bi-people"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 670 | `                 ${archived && CAN_MANAGE_ROOMS ? `&lt;button class="btn btn-sm btn-outline-secondary" onclick="unarchiveRoom(${room.id})" title="Restaurar"&gt;&lt;i class="bi bi-arrow-counterclockwise"&gt;&lt;/i&gt;&lt;/button&gt;` : !archived ? `` |
| 671 | `                             ${room.can_edit_room ? `&lt;button class="btn btn-sm btn-outline-primary" onclick="editRoom(${room.id})" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 672 | `                          ${room.can_control_room ? `&lt;button class="btn btn-sm btn-outline-primary" onclick="openRoomSchedule(${room.id})" title="Modificar horario"&gt;&lt;i class="bi bi-clock-history"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 673 | `      ${CAN_MANAGE_ROOMS && !room.sequence_locked ? `&lt;button class="btn btn-sm btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"&gt;&lt;i class="bi bi-lock"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 674 | `oom && room.sequence_locked && !room.completed_at ? `&lt;button class="btn btn-sm btn-outline-warning" onclick="advanceRoom(${room.id})" title="Siguiente proyecto"&gt;&lt;i class="bi bi-skip-forward"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 675 | `                             ${CAN_MANAGE_ROOMS ? `&lt;button class="btn btn-sm btn-outline-secondary" onclick="archiveRoom(${room.id})" title="Archivar"&gt;&lt;i class="bi bi-archive"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 676 | `                                    &lt;button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(${room.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 697 | `                        ? `&lt;button class="btn btn-sm btn-outline-warning" onclick="controlRoomTimer(${room.id}, 'pause')"&gt;&lt;i class="bi bi-pause"&gt;&lt;/i&gt; Pausar&lt;/button&gt;`` |
| 699 | `                            ? `&lt;button class="btn btn-sm btn-outline-success" onclick="controlRoomTimer(${room.id}, 'resume')"&gt;&lt;i class="bi bi-play"&gt;&lt;/i&gt; Reanudar&lt;/button&gt;`` |
| 700 | `                            : `&lt;button class="btn btn-sm btn-outline-success" onclick="controlRoomTimer(${room.id}, 'start')"&gt;&lt;i class="bi bi-play"&gt;&lt;/i&gt; Iniciar&lt;/button&gt;`}` |
| 701 | `                    &lt;button class="btn btn-sm btn-outline-secondary" onclick="controlRoomTimer(${room.id}, 'reset')"&gt;&lt;i class="bi bi-arrow-counterclockwise"&gt;&lt;/i&gt; Reiniciar&lt;/button&gt;` |
| 702 | `                    &lt;button class="btn btn-sm btn-outline-danger" onclick="controlRoomTimer(${room.id}, 'finish')"&gt;&lt;i class="bi bi-stop"&gt;&lt;/i&gt; Finalizar&lt;/button&gt;` |
| 739 | `                            &lt;button class="btn btn-sm btn-outline-primary" onclick="saveProjectRoom(${project.id})"&gt;&lt;i class="bi bi-save"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 740 | `                            ${currentRoom ? `&lt;button class="btn btn-sm btn-outline-secondary" onclick="editRoom(${currentRoom.id}); setRoomView('rooms')"&gt;&lt;i class="bi bi-door-open"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 856 | `"roomTeacher${escapeHtml(teacher.id)}" ${selectedIds.includes(String(teacher.id)) ? 'checked' : ''} onchange="refreshResponsibleTeacherOptions()"&gt;` |
| 907 | `ect.id}" id="roomProject${project.id}" ${selectedIds.includes(Number(project.id)) ? 'checked' : ''} onchange="scheduleRoomProjectsReorder()"&gt;` |
| 908 | `e="number" min="1" value="${normalizedOrderMap[project.id] &#124;&#124; ''}" style="width:76px" title="Orden" onchange="scheduleRoomProjectsReorder()"&gt;` |
| 1191 | `        document.getElementById('roomScheduleForm').addEventListener('submit', async event =&gt; {` |
| 1273 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 569 | `api.get('/users', { perfil_id: 1, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000, _timeout: 45000 }),` |
| 570 | `api.get('/users', { perfil_id: 2, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000, _timeout: 45000 })` |
| 577 | `projects = await api.get('/evaluations/projects', { _cache_ttl: 30000, _timeout: 45000 });` |
| 582 | `rooms = await api.get('/evaluations/rooms', { archived, _fresh: 1, _timeout: 30000 });` |
| 592 | `const catalog = await api.get('/academic-operations/catalog');` |
| 1060 | `? await api.put(`/evaluations/rooms/${id}`, payload)` |
| 1061 | `: await api.post('/evaluations/rooms', payload);` |
| 1081 | `const response = await api.put(`/evaluations/rooms/${currentRoom.id}`, roomPayloadFromRoom(currentRoom, nextProjects));` |
| 1087 | `const response = await api.put(`/evaluations/rooms/${targetRoom.id}`, roomPayloadFromRoom(targetRoom, existing));` |
| 1100 | `const response = await api.delete(`/evaluations/rooms/${id}`);` |
| 1112 | `await api.post(`/evaluations/rooms/${id}/archive`, {});` |
| 1121 | `await api.post(`/evaluations/rooms/${id}/unarchive`, {});` |
| 1130 | `const response = await api.post(`/evaluations/rooms/${id}/lock-sequence`, {});` |
| 1139 | `const response = await api.post(`/evaluations/rooms/${id}/advance`, {` |
| 1156 | `const response = await api.post(`/evaluations/rooms/${id}/timer`, {` |
| 1209 | `const response = await api.put(`/evaluations/rooms/${id}/schedule`, {` |
| 1256 | `const response = await fetch(`${API_BASE_URL}${endpoint}`, {` |

## pages/admin/evaluations.php

[Fuente](../../pages/admin/evaluations.php) · [Código completo](REFERENCIA.md#pages-admin-evaluations-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 457 | función | `function escapeHtml(value)` |
| 461 | función | `function projectSemester(project)` |
| 465 | función | `function fullName(user)` |
| 469 | función | `function roomDisplayIdentifier(room)` |
| 479 | función | `function roomDisplayName(room)` |
| 483 | función | `function projectActiveAuthors(project)` |
| 488 | función | `function evaluationPresentationIndicator(evaluation)` |
| 494 | función | `function evaluationMemberIndicators(evaluation, project)` |
| 511 | función | `function projectCompany(project)` |
| 515 | función | `function projectAdvisor(project)` |
| 519 | función | `function projectControlNumbers(project)` |
| 524 | función | `function projectDetailsRows(project)` |
| 536 | función | `function compactProjectMeta(project)` |
| 542 | función | `function projectKeyData(project)` |
| 555 | función | `function orderedRoomProjects(room)` |
| 566 | función | `function evaluationProject(evaluation)` |
| 570 | función | `function stageLabel(stage)` |
| 574 | función | `function groupCriteria()` |
| 582 | función | `function criteriaForEvaluation(evaluation)` |
| 593 | función | `function levelLabel(level)` |
| 607 | función | `function levelValue(level)` |
| 611 | función | `function levelPoints(level)` |
| 617 | función | `function levelText(level)` |
| 622 | función | `function rubricModeKey(semester)` |
| 626 | función | `function currentRubricMode(semester)` |
| 630 | función | `async function saveRubricScoreMode()` |
| 644 | función | `function numericLevelOptions()` |
| 649 | función | `function scoreDraftKey(evaluationId)` |
| 653 | función | `function readScoreDraft(evaluationId)` |
| 661 | función | `function collectScoreDraft()` |
| 673 | función | `function saveScoreDraft()` |
| 681 | función | `function clearScoreDraft(evaluationId)` |
| 685 | función | `async function refreshCriteria(fresh = false)` |
| 693 | función | `async function loadInitialData()` |
| 750 | función | `function renderRoomOptions()` |
| 754 | función | `function normalizeRoomName(value)` |
| 761 | función | `function normalizeClassroom(value)` |
| 768 | función | `function hasOpenEvaluationModal()` |
| 772 | función | `function startEvaluationsRealtime()` |
| 780 | función | `async function refreshEvaluationLiveData()` |
| 797 | función | `function evaluationRoomKey(evaluation)` |
| 801 | función | `function evaluationRoomGroups(items)` |
| 826 | función | `function cssEscape(value)` |
| 833 | función | `function toggleEvaluationRoom(roomKey)` |
| 851 | función | `function canUseArchiveSelection()` |
| 855 | función | `function archiveSelectionAction()` |
| 881 | función | `function archiveableEvaluations()` |
| 886 | función | `function archiveableEvaluationIdsForSemester(semesterKey)` |
| 892 | función | `function archiveableEvaluationIdsForRoom(roomKey)` |
| 898 | función | `function selectionStateForIds(ids)` |
| 906 | función | `function renderArchiveGroupCheckbox(type, key, ids, label)` |
| 918 | función | `function renderArchiveEvaluationCheckbox(evaluation)` |
| 925 | función | `function pruneArchiveSelection()` |
| 930 | función | `function updateArchiveSelectionControls()` |
| 943 | función | `function syncArchiveSelectionCheckboxes()` |
| 961 | función | `async function handleArchiveSelectionButton()` |
| 977 | función | `async function cancelArchiveSelectionMode()` |
| 984 | función | `function toggleArchiveEvaluationSelection(id, checked)` |
| 994 | función | `function toggleArchiveGroupSelection(type, key, checked)` |
| 1009 | función | `async function archiveSelectedEvaluations()` |
| 1037 | función | `async function loadEvaluations(showLoading = true, fresh = false, useLocal = false)` |
| 1211 | función | `function removeDuplicateEvaluations(items)` |
| 1224 | función | `function orderEvaluationsByRoomSequence(items)` |
| 1246 | función | `function evaluationSemesterKey(evaluation)` |
| 1250 | función | `function evaluationSemesterStats(items, semesterKey)` |
| 1264 | función | `function showProjectDetails(evaluationId)` |
| 1293 | función | `function openEvaluationModal()` |
| 1299 | función | `function applyRoomToEvaluationForm()` |
| 1329 | función | `function onRubricScopeChange()` |
| 1337 | función | `function openRubricModal(projectId = '')` |
| 1346 | función | `async function loadRooms(renderAfterLoad = true, fresh = false)` |
| 1354 | función | `async function openRoomsModal()` |
| 1363 | función | `function parseRoomDate(value)` |
| 1370 | función | `function formatLocalDateTimeInput(date)` |
| 1371 | flecha | `const pad = value =&gt;` |
| 1375 | función | `function roomRange(room)` |
| 1384 | función | `function rangesOverlap(startA, endA, startB, endB)` |
| 1388 | función | `function conflictingRoomsForCurrentForm()` |
| 1401 | función | `function busyRoomIds()` |
| 1411 | función | `function currentRoomProjectState()` |
| 1422 | función | `function normalizeProjectOrderMap(selectedIds, orderMap = {})` |
| 1432 | función | `function scheduleRoomProjectsReorder()` |
| 1440 | función | `async function updateRoomAvailability()` |
| 1453 | función | `function renderRoomTeachers(selected = [])` |
| 1469 | función | `function refreshResponsibleTeacherOptions(selected = null)` |
| 1479 | función | `async function loadRoomProjects(selected = [], orderMap = {})` |
| 1517 | función | `function renderAvailabilityHint(busy = busyRoomIds()` |
| 1541 | función | `function renderRooms()` |
| 1544 | flecha | `const roomActions = room =&gt;` |
| 1583 | función | `async function editRoom(id)` |
| 1602 | función | `function resetRoomForm()` |
| 1612 | función | `async function saveRoom()` |
| 1689 | función | `async function deleteRoom(id)` |
| 1707 | función | `async function archiveRoom(id)` |
| 1720 | función | `async function unarchiveRoom(id)` |
| 1733 | función | `async function lockRoomSequence(id)` |
| 1741 | función | `async function advanceRoom(id)` |
| 1758 | función | `async function markEvaluationCompleted(id)` |
| 1777 | función | `async function downloadRoomReport(id)` |
| 1781 | función | `async function downloadRoomTeacherReport(id)` |
| 1785 | función | `async function downloadEvaluationReport(id)` |
| 1789 | función | `async function downloadEvaluationTeacherReport(id)` |
| 1793 | función | `async function downloadPdf(endpoint, filename, fallbackMessage)` |
| 1794 | flecha | `const request = async (retryingAfterRefresh = false) =&gt;` |
| 1829 | función | `async function loadRubricCriteria(useLocal = false)` |
| 1869 | función | `async function addCriterion()` |
| 1891 | función | `async function updateCriterion(id)` |
| 1902 | función | `async function deleteCriterion(id)` |
| 1909 | función | `async function openScoreModal(evaluationId)` |
| 2020 | función | `function showBreakdown(evaluationId)` |
| 2025 | flecha | `const renderAptVote = value =&gt;` |
| 2030 | flecha | `const renderTeacherScores = teacher =&gt;` |
| 2085 | función | `async function saveRoomFeedback(evaluationId)` |
| 2096 | función | `function releaseModalFocus(modalId)` |
| 2103 | función | `function replaceEvaluationLocal(updatedEvaluation)` |
| 2111 | función | `async function deleteEvaluation(id)` |
| 2119 | función | `async function archiveEvaluation(id)` |
| 2131 | función | `async function unarchiveEvaluation(id)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 145 | `                        &lt;button class="btn btn-outline-secondary" id="archiveSelectionBtn" onclick="handleArchiveSelectionButton()"&gt;` |
| 148 | `                    &lt;button class="btn btn-outline-secondary d-none" id="archiveSelectionCancelBtn" onclick="cancelArchiveSelectionMode()"&gt;` |
| 153 | `                        &lt;button class="btn btn-outline-primary" onclick="openRubricModal()"&gt;` |
| 170 | `                        &lt;select class="form-select" id="semesterFilter" onchange="loadEvaluations()"&gt;` |
| 180 | `                        &lt;select class="form-select" id="projectFilter" onchange="loadEvaluations()"&gt;` |
| 217 | `          &lt;button type="button" class="btn-close" data-bs-dismiss="modal" onmousedown="this.blur()" onclick="this.blur()"&gt;&lt;/button&gt;` |
| 222 | `                        &lt;select class="form-select" id="evaluation_room_id" onchange="applyRoomToEvaluationForm()" required&gt;` |
| 263 | `          &lt;button type="button" class="btn-close" data-bs-dismiss="modal" onmousedown="this.blur()" onclick="this.blur()"&gt;&lt;/button&gt;` |
| 269 | `                            &lt;select class="form-select" id="rubricSemester" onchange="onRubricScopeChange()"&gt;` |
| 278 | `                            &lt;select class="form-select" id="rubricScoreMode" onchange="saveRubricScoreMode()"&gt;` |
| 285 | `                            &lt;select class="form-select" id="rubricProjectId" onchange="loadRubricCriteria()"&gt;` |
| 295 | `                            &lt;button type="button" class="btn btn-primary w-100" onclick="addCriterion()"&gt;` |
| 311 | `          &lt;button type="button" class="btn-close" data-bs-dismiss="modal" onmousedown="this.blur()" onclick="this.blur()"&gt;&lt;/button&gt;` |
| 363 | `="col-md-4"&gt;&lt;label class="form-label"&gt;Semestre&lt;/label&gt;&lt;select class="form-select" id="roomSemester" onchange="loadRoomProjects()"&gt;&lt;option value="5"&gt;5&lt;/option&gt;&lt;option value="6"&gt;6&lt;/option&gt;&lt;option value="7"&gt;7&lt;/option&gt;&lt;option value="8"&gt;8&lt;/option&gt;&lt;/select&gt;&lt;/div&gt;` |
| 364 | `"form-label"&gt;Inicio&lt;/label&gt;&lt;input type="datetime-local" class="form-control" id="roomDate" required onchange="updateRoomAvailability()"&gt;&lt;div class="form-text"&gt;Posterior a la fecha actual.&lt;/div&gt;&lt;/div&gt;` |
| 365 | `"form-label"&gt;Fin&lt;/label&gt;&lt;input type="datetime-local" class="form-control" id="roomEndDate" required onchange="updateRoomAvailability()"&gt;&lt;div class="form-text"&gt;Debe ser posterior al inicio.&lt;/div&gt;&lt;/div&gt;` |
| 376 | `                                    &lt;button class="btn btn-outline-secondary" onclick="resetRoomForm()"&gt;Limpiar&lt;/button&gt;` |
| 377 | `                                    &lt;button class="btn btn-primary" onclick="saveRoom()"&gt;&lt;i class="bi bi-save"&gt;&lt;/i&gt; Guardar sala&lt;/button&gt;` |
| 913 | `              &lt;input class="form-check-input" type="checkbox" data-archive-${type}-key="${safeKey}" onchange="toggleArchiveGroupSelection('${type}', this.dataset.${dataKeyName}, this.checked)" ${state.checked ? 'checked' : ''}&gt;` |
| 922 | `orm-check-input archive-selection-checkbox mt-1" type="checkbox" data-archive-evaluation-id="${id}" onchange="toggleArchiveEvaluationSelection(${id}, this.checked)" ${selectedArchiveEvaluationIds.has(id) ? 'checked' : ''} title="Seleccionar evaluación"&gt;`;` |
| 1109 | `utton type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2" onclick="downloadRoomReport(${room.id})" title="Reporte PDF de sala"&gt;&lt;i class="bi bi-file-earmark-pdf"&gt;&lt;/i&gt;&lt;span&gt;Reporte sala&lt;/span&gt;&lt;/button&gt;` |
| 1110 | `   &lt;button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-2" onclick="downloadRoomTeacherReport(${room.id})" title="Reporte PDF para docentes"&gt;&lt;i class="bi bi-people"&gt;&lt;/i&gt;&lt;span&gt;Reporte docentes&lt;/span&gt;&lt;/button&gt;`` |
| 1117 | `&lt;button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2" onclick="toggleEvaluationRoom('${escapeHtml(roomKey)}')"&gt;` |
| 1136 | `                           ${canAdvanceRoom ? `&lt;button type="button" class="btn btn-sm btn-warning" onclick="advanceRoom(${room.id})"&gt;&lt;i class="bi bi-skip-forward"&gt;&lt;/i&gt; Siguiente proyecto&lt;/button&gt;` : ''}` |
| 1179 | `               &lt;button class="btn btn-sm evaluation-average-btn evaluation-average-${averageColor}" onclick="showBreakdown(${evaluation.id})"&gt;` |
| 1187 | `                                &lt;button class="btn btn-sm btn-success evaluation-start-btn" onclick="openScoreModal(${evaluation.id})"` |
| 1192 | `                                    &lt;button class="btn btn-sm btn-outline-success" onclick="markEvaluationCompleted(${evaluation.id})"` |
| 1197 | `                                    &lt;button class="btn btn-sm btn-outline-secondary" onclick="showProjectDetails(${evaluation.id})" title="Detalles del proyecto"&gt;&lt;i class="bi bi-info-circle"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1198 | `                             &lt;button class="btn btn-sm btn-outline-secondary evaluation-report-btn" onclick="downloadEvaluationReport(${evaluation.id})" title="Reporte PDF"&gt;&lt;i class="bi bi-file-earmark-pdf"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1199 | `                                    &lt;button class="btn btn-sm btn-outline-info" onclick="downloadEvaluationTeacherReport(${evaluation.id})" title="Reporte PDF para docentes"&gt;&lt;i class="bi bi-people"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1200 | `evaluations && Number(evaluation.semestre) === 8) ? `&lt;button class="btn btn-sm btn-outline-primary" onclick="openRubricModal(${evaluation.project_id})" title="Rubrica personalizada"&gt;&lt;i class="bi bi-ui-checks-grid"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 1201 | `            ${evaluation.can_manage_evaluations ? `&lt;button class="btn btn-sm btn-outline-secondary" onclick="${IS_ARCHIVED_VIEW ? 'unarchiveEvaluation' : 'archiveEvaluation'}(${evaluation.id})" title="${IS_ARCHIVED_VIEW ? 'Restaurar a vista principal' : 'Archivar'}"&gt;&lt;i class="bi ${IS_ARCHIVED_VIEW ? 'bi-arr` |
| 1202 | `               ${evaluation.can_manage_evaluations ? `&lt;button class="btn btn-sm btn-outline-danger" onclick="deleteEvaluation(${evaluation.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 1308 | `        document.getElementById('evaluationForm').addEventListener('submit', async (event) =&gt; {` |
| 1459 | `"roomTeacher${escapeHtml(teacher.id)}" ${selectedIds.includes(String(teacher.id)) ? 'checked' : ''} onchange="refreshResponsibleTeacherOptions()"&gt;` |
| 1506 | `ect.id}" id="roomProject${project.id}" ${selectedIds.includes(Number(project.id)) ? 'checked' : ''} onchange="scheduleRoomProjectsReorder()"&gt;` |
| 1507 | `zedOrderMap[project.id] &#124;&#124; ''}" style="width:76px" title="Orden" aria-label="Orden de presentación" onchange="scheduleRoomProjectsReorder()"&gt;` |
| 1545 | `                            &lt;button class="btn btn-sm btn-outline-dark" onclick="downloadRoomReport(${room.id})" title="Reporte PDF de sala"&gt;&lt;i class="bi bi-file-earmark-pdf"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1546 | `                            &lt;button class="btn btn-sm btn-outline-info" onclick="downloadRoomTeacherReport(${room.id})" title="Reporte PDF para docentes"&gt;&lt;i class="bi bi-people"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1548 | `                            &lt;button class="btn btn-sm btn-outline-primary" onclick="editRoom(${room.id})" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1549 | `                            &lt;button class="btn btn-sm btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"&gt;&lt;i class="bi bi-lock"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1550 | `     ${room.sequence_locked && !room.completed_at ? `&lt;button class="btn btn-sm btn-outline-warning" onclick="advanceRoom(${room.id})" title="Siguiente proyecto"&gt;&lt;i class="bi bi-skip-forward"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 1551 | `                            &lt;button class="btn btn-sm btn-outline-secondary" onclick="${IS_ARCHIVED_VIEW ? 'unarchiveRoom' : 'archiveRoom'}(${room.id})" title="${IS_ARCHIVED_VIEW ? 'Restaurar sala' : 'Archivar sala'}"&gt;&lt;i class="bi ${IS_ARCHIVED_VIEW ? 'bi-arrow-counterclockwise' : 'bi` |
| 1552 | `                            &lt;button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(${room.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1862 | `                            &lt;button type="button" class="btn btn-sm btn-outline-primary" onclick="updateCriterion(${criterion.id})" title="Guardar"&gt;&lt;i class="bi bi-save"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1863 | `                            &lt;button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCriterion(${criterion.id})" title="Desactivar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 1969 | `                input.oninput = saveScoreDraft;` |
| 1970 | `                input.onchange = saveScoreDraft;` |
| 1972 | `            document.getElementById('generalEvaluationComment').oninput = saveScoreDraft;` |
| 1973 | `            document.getElementById('apto_titulacion').onchange = saveScoreDraft;` |
| 1977 | `        document.getElementById('scoreForm').addEventListener('submit', async (event) =&gt; {` |
| 2078 | `                        &lt;button class="btn btn-sm btn-primary" onclick="saveRoomFeedback(${evaluation.id})"&gt;&lt;i class="bi bi-save"&gt;&lt;/i&gt; Guardar retroalimentacion&lt;/button&gt;` |
| 2143 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |
| 2148 | `            document.getElementById('breakdownModal').addEventListener('hide.bs.modal', () =&gt; releaseModalFocus('breakdownModal'));` |
| 2151 | `            document.getElementById('scoreModal').addEventListener('hide.bs.modal', () =&gt; {` |
| 2156 | `            document.getElementById('scoreModal').addEventListener('hide.bs.modal', saveScoreDraft);` |
| 2157 | `            document.getElementById('roomDate')?.addEventListener('input', updateRoomAvailability);` |
| 2158 | `            document.getElementById('roomEndDate')?.addEventListener('input', updateRoomAvailability);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 636 | `const response = await api.put('/evaluations/rubric-score-modes', { semester: Number(semester), mode });` |
| 686 | `const criteriaResponse = await api.get('/evaluations/criteria', fresh ? { _fresh: 1, _timeout: 30000 } : { _cache_ttl: 60000, _timeout: 30000 });` |
| 695 | `api.get('/evaluations/projects', { _cache_ttl: 30000, _timeout: 45000 }),` |
| 712 | `const staffResponse = await api.get('/users', {` |
| 1021 | `await api.post(action.endpoint, { ids }, { _timeout: 45000 });` |
| 1051 | `const response = await api.get('/evaluations', params);` |
| 1057 | `const nextResponse = await api.get('/evaluations', { ...params, page: currentPage });` |
| 1320 | `await api.post('/evaluations', data);` |
| 1350 | `rooms = await api.get('/evaluations/rooms', params);` |
| 1481 | `roomProjects = await api.get('/evaluations/projects', { semestre: semester, _fresh: 1 });` |
| 1676 | `if (id) await api.put(`/evaluations/rooms/${id}`, payload);` |
| 1677 | `else await api.post('/evaluations/rooms', payload);` |
| 1696 | `const response = await api.delete(`/evaluations/rooms/${id}`);` |
| 1713 | `await api.post(`/evaluations/rooms/${id}/archive`, {});` |
| 1726 | `await api.post(`/evaluations/rooms/${id}/unarchive`, {});` |
| 1735 | `await api.post(`/evaluations/rooms/${id}/lock-sequence`, {});` |
| 1748 | `const response = await api.post(`/evaluations/rooms/${id}/advance`, { continue_next: true });` |
| 1768 | `const response = await api.post(`/evaluations/${id}/mark-completed`, {});` |
| 1795 | `const response = await fetch(`${API_BASE_URL}${endpoint}`, {` |
| 1831 | `const criteriaResponse = await api.get('/evaluations/criteria', { _fresh: 1, _timeout: 30000 });` |
| 1881 | `const response = await api.post('/evaluations/rubric-criteria', payload);` |
| 1893 | `const response = await api.put(`/evaluations/rubric-criteria/${id}`, {` |
| 1904 | `await api.delete(`/evaluations/rubric-criteria/${id}`);` |
| 2005 | `const response = await api.post(`/evaluations/${evaluationId}/score`, payload);` |
| 2086 | `const response = await api.post(`/evaluations/${evaluationId}/feedback`, {` |
| 2113 | `await api.delete(`/evaluations/${id}`);` |
| 2125 | `await api.post(`/evaluations/${id}/archive`, {});` |
| 2137 | `await api.post(`/evaluations/${id}/unarchive`, {});` |

## pages/admin/notices.php

[Fuente](../../pages/admin/notices.php) · [Código completo](REFERENCIA.md#pages-admin-notices-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 168 | función | `function esc(value)` |
| 172 | función | `function renderNotices()` |
| 201 | función | `async function loadNotices()` |
| 211 | función | `function openNoticeModal(index = null)` |
| 225 | función | `function editNotice(index)` |
| 229 | función | `async function saveNotices(message = 'Avisos guardados')` |
| 280 | función | `async function toggleNotice(index)` |
| 285 | función | `async function deleteNotice(index)` |
| 301 | función | `function formatValidity(notice)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 32 | `                    &lt;button class="btn btn-outline-primary" type="button" onclick="loadNotices()"&gt;&lt;i class="bi bi-arrow-clockwise"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 33 | `                    &lt;button class="btn btn-primary" type="button" onclick="openNoticeModal()"&gt;&lt;i class="bi bi-plus-circle"&gt;&lt;/i&gt; Nuevo aviso&lt;/button&gt;` |
| 192 | `                        &lt;button class="btn btn-outline-primary" type="button" onclick="editNotice(${index})"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 193 | `                        &lt;button class="btn btn-outline-secondary" type="button" onclick="toggleNotice(${index})"&gt;&lt;i class="bi ${notice.active ? 'bi-eye-slash' : 'bi-eye'}"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 194 | `                        &lt;button class="btn btn-outline-danger" type="button" onclick="deleteNotice(${index})"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 236 | `document.getElementById('noticeForm').addEventListener('submit', async event =&gt; {` |
| 296 | `document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 203 | `const response = await api.get('/notices');` |
| 230 | `const response = await api.put('/notices', { notices });` |

## pages/admin/operations.php

[Fuente](../../pages/admin/operations.php) · [Código completo](REFERENCIA.md#pages-admin-operations-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 101 | función | `function operationEscape(value)` |
| 104 | función | `function renderOperations(response)` |
| 136 | función | `async function loadOperations()` |
| 143 | función | `async function scanOperations()` |
| 156 | función | `async function loadContinuityHistory()` |
| 186 | función | `async function storeContinuityMeasurement()` |
| 201 | función | `async function loadContinuityPolicy()` |
| 212 | función | `async function saveContinuityPolicy(event)` |
| 236 | función | `async function acknowledgeAlert(id, button)` |
| 246 | función | `async function downloadContinuityReport()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 131 | `                ? `&lt;button class="btn btn-sm btn-outline-primary" type="button" onclick="acknowledgeAlert(${Number(alert.id)}, this)"&gt;&lt;i class="bi bi-person-check"&gt;&lt;/i&gt; Atender&lt;/button&gt;`` |
| 274 | `document.getElementById('scanButton').addEventListener('click', scanOperations);` |
| 275 | `document.getElementById('continuityReportButton').addEventListener('click', downloadContinuityReport);` |
| 276 | `document.getElementById('measureContinuityButton').addEventListener('click', storeContinuityMeasurement);` |
| 277 | `document.getElementById('continuityPolicyModal').addEventListener('show.bs.modal', loadContinuityPolicy);` |
| 278 | `document.getElementById('continuityPolicyForm').addEventListener('submit', saveContinuityPolicy);` |
| 279 | `document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 138 | `renderOperations(await api.get('/admin/operational-alerts', {_fresh: true}));` |
| 148 | `renderOperations(await api.post('/admin/operational-alerts/scan', {}, {_timeout: 300000}));` |
| 159 | `const response = await api.get('/admin/continuity-history', {limit: 30, _fresh: true});` |
| 192 | `await api.post('/admin/continuity-history', {}, {_timeout: 300000});` |
| 203 | `const policy = await api.get('/admin/continuity-policy', {_fresh: true});` |
| 226 | `await api.put('/admin/continuity-policy', payload);` |
| 228 | `renderOperations(await api.post('/admin/operational-alerts/scan', {}, {_timeout: 300000}));` |
| 239 | `await api.put(`/admin/operational-alerts/${id}/acknowledge`, {});` |
| 252 | `const response = await fetch(`${API_BASE_URL}/admin/continuity-report.pdf`, {` |

## pages/admin/project-create.php

[Fuente](../../pages/admin/project-create.php) · [Código completo](REFERENCIA.md#pages-admin-project-create-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 120 | función | `function escapeHtml(value)` |
| 124 | función | `function fullName(student)` |
| 128 | función | `async function loadStudents()` |
| 145 | función | `function renderStudentOptions()` |
| 154 | función | `function findStudentFromInput(value)` |
| 158 | función | `function renderSelectedStudents()` |
| 172 | función | `function addStudentFromSearch()` |
| 191 | función | `function removeStudent(studentId)` |
| 197 | función | `async function loadSubjectGroups()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 92 | `                                    &lt;button class="btn btn-outline-primary" type="button" onclick="addStudentFromSearch()"&gt;&lt;i class="bi bi-person-plus"&gt;&lt;/i&gt; Agregar&lt;/button&gt;` |
| 168 | `                    &lt;button type="button" class="btn-close btn-close-white" aria-label="Quitar" onclick="removeStudent('${escapeHtml(student.id)}')"&gt;&lt;/button&gt;` |
| 214 | `        form.addEventListener('submit', async (e) =&gt; {` |
| 243 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |
| 245 | `            document.getElementById('semestre').addEventListener('change', loadSubjectGroups);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 130 | `api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 }),` |
| 131 | `api.get('/projects', { compact: 1, per_page: 500, _cache_ttl: 30000 })` |
| 204 | `const groups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |
| 235 | `await api.post('/projects', formData);` |

## pages/admin/project-edit.php

[Fuente](../../pages/admin/project-edit.php) · [Código completo](REFERENCIA.md#pages-admin-project-edit-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 127 | función | `function escapeHtml(value)` |
| 131 | función | `function fullName(student)` |
| 135 | función | `async function loadStudents()` |
| 153 | función | `function renderStudentOptions()` |
| 162 | función | `function findStudentFromInput(value)` |
| 166 | función | `function renderSelectedStudents()` |
| 180 | función | `function addStudentFromSearch()` |
| 199 | función | `function removeStudent(studentId)` |
| 205 | función | `async function loadSubjectGroups(selectedId = null)` |
| 219 | función | `async function loadProject()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 98 | `                                    &lt;button class="btn btn-outline-primary" type="button" onclick="addStudentFromSearch()"&gt;&lt;i class="bi bi-person-plus"&gt;&lt;/i&gt; Agregar&lt;/button&gt;` |
| 176 | `                    &lt;button type="button" class="btn-close btn-close-white" aria-label="Quitar" onclick="removeStudent('${escapeHtml(student.id)}')"&gt;&lt;/button&gt;` |
| 239 | `        form.addEventListener('submit', async (e) =&gt; {` |
| 268 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |
| 269 | `            document.getElementById('semestre').addEventListener('change', () =&gt; loadSubjectGroups());` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 137 | `api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 }),` |
| 138 | `api.get('/projects', { compact: 1, per_page: 500, _cache_ttl: 30000 })` |
| 211 | `const groups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |
| 221 | `const project = await api.get(`/projects/${projectId}`);` |
| 260 | `await api.put(`/projects/${projectId}`, formData);` |

## pages/admin/projects.php

[Fuente](../../pages/admin/projects.php) · [Código completo](REFERENCIA.md#pages-admin-projects-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 332 | función | `function currentProjectModality()` |
| 336 | función | `function escapeHtml(value)` |
| 340 | función | `function projectActiveAuthors(project, emptyText = '-')` |
| 346 | función | `function advisorByThesisRole(project, role)` |
| 354 | función | `function advisorName(advisor)` |
| 358 | función | `function thesisRoleLabel(role)` |
| 362 | función | `function setProjectManagementView(view)` |
| 389 | función | `function renderSemesterFilter()` |
| 403 | función | `function renderThesisTable(projects)` |
| 445 | función | `async function loadProjects(page = 1)` |
| 556 | función | `function renderProjectsPagination()` |
| 587 | función | `function scheduleProjectsSearch()` |
| 592 | función | `function clearProjectsSearch()` |
| 597 | función | `function fullName(student)` |
| 601 | función | `function resetProjectModal()` |
| 614 | función | `function renderProjectSemesterOptions(selectedValue = '')` |
| 627 | función | `async function openProjectModal(projectId = null, asThesis = null)` |
| 667 | función | `async function loadProjectModalStudents(currentProjectId = null)` |
| 682 | función | `function renderProjectStudentOptions()` |
| 691 | función | `function renderProjectSelectedStudents()` |
| 703 | función | `function addProjectStudent()` |
| 716 | función | `function removeProjectStudent(studentId)` |
| 722 | función | `async function loadProjectModalGroups(selectedId = '')` |
| 735 | función | `async function saveProjectModal(event)` |
| 786 | función | `async function showProjectDetails(projectId)` |
| 818 | función | `async function openProjectSubjectsModal(projectId)` |
| 846 | función | `async function saveProjectSubjects(event)` |
| 861 | función | `async function toggleProjectThesis(projectId, isThesis)` |
| 880 | función | `async function deleteProject(projectId)` |
| 894 | función | `function downloadProjectsExcelTemplate()` |
| 916 | función | `function openProjectsImportModal()` |
| 923 | función | `async function importProjectsExcel(event)` |
| 947 | función | `function renderImportException(target, error)` |
| 963 | función | `function renderImportResult(target, result)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 31 | `                        &lt;button type="button" class="btn btn-outline-primary" onclick="downloadProjectsExcelTemplate()"&gt;` |
| 34 | `                        &lt;button type="button" class="btn btn-outline-success" onclick="openProjectsImportModal()"&gt;` |
| 37 | `                        &lt;button type="button" class="btn btn-primary" id="newProjectBtn" onclick="openProjectModal()"&gt;` |
| 47 | `                    &lt;button type="button" class="btn btn-primary" id="integratorViewBtn" onclick="setProjectManagementView('integrator')"&gt;` |
| 50 | `                    &lt;button type="button" class="btn btn-outline-secondary" id="dualViewBtn" onclick="setProjectManagementView('dual')"&gt;` |
| 53 | `                    &lt;button type="button" class="btn btn-outline-secondary" id="caseViewBtn" onclick="setProjectManagementView('case')"&gt;` |
| 56 | `                    &lt;button type="button" class="btn btn-outline-secondary" id="thesisViewBtn" onclick="setProjectManagementView('thesis')"&gt;` |
| 64 | `                        &lt;select id="semesterFilter" class="form-select" onchange="loadProjects(1)"&gt;` |
| 72 | `ss="form-control" id="projectSearchInput" placeholder="Proyecto, estudiante, asesor, empresa o año" oninput="scheduleProjectsSearch()"&gt;` |
| 73 | `                            &lt;button type="button" class="btn btn-outline-secondary" onclick="clearProjectsSearch()" title="Limpiar búsqueda"&gt;&lt;i class="bi bi-x-lg"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 138 | `            &lt;form class="modal-content" id="projectsImportForm" onsubmit="importProjectsExcel(event)"&gt;` |
| 160 | `                &lt;form id="projectModalForm" class="needs-validation" novalidate onsubmit="saveProjectModal(event)"&gt;` |
| 212 | `                                &lt;select class="form-select" id="projectSemester" required onchange="loadProjectModalGroups()"&gt;` |
| 238 | `                                &lt;button type="button" class="btn btn-outline-primary w-100" onclick="addProjectStudent()"&gt;&lt;i class="bi bi-person-plus"&gt;&lt;/i&gt; Agregar&lt;/button&gt;` |
| 255 | `            &lt;form class="modal-content" onsubmit="saveProjectSubjects(event)"&gt;` |
| 433 | `                                &lt;button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver detalles"&gt;&lt;i class="bi bi-eye"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 435 | `                                &lt;button type="button" class="btn btn-outline-info" onclick="openProjectSubjectsModal(${project.id})" title="Materias"&gt;&lt;i class="bi bi-book"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 436 | `                                &lt;button type="button" class="btn btn-outline-primary" onclick="openProjectModal(${project.id}, true)" title="Editar tesis"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 437 | `                                &lt;button type="button" class="btn btn-outline-warning" onclick="toggleProjectThesis(${project.id}, false)" title="Quitar de tesis"&gt;&lt;i class="bi bi-mortarboard-fill"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 438 | `                                &lt;button class="btn btn-outline-danger" onclick="deleteProject(${project.id})" title="Eliminar tesis"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 528 | `                        ? `&lt;button type="button" class="btn btn-outline-warning" onclick="toggleProjectThesis(${project.id}, false)" title="Quitar de tesis"&gt;&lt;i class="bi bi-mortarboard-fill"&gt;&lt;/i&gt;&lt;/button&gt;`` |
| 529 | `                        : `&lt;button type="button" class="btn btn-outline-success" onclick="toggleProjectThesis(${project.id}, true)" title="Marcar como tesis"&gt;&lt;i class="bi bi-mortarboard"&gt;&lt;/i&gt;&lt;/button&gt;`;` |
| 541 | `                                    &lt;button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver detalles"&gt;&lt;i class="bi bi-eye"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 543 | `                                    &lt;button type="button" class="btn btn-outline-info" onclick="openProjectSubjectsModal(${project.id})" title="Materias"&gt;&lt;i class="bi bi-book"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 544 | `                                    &lt;button type="button" class="btn btn-outline-primary" onclick="openProjectModal(${project.id})" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 545 | `                                    &lt;button class="btn btn-outline-danger" onclick="deleteProject(${project.id})" title="Eliminar"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` : ''}` |
| 572 | `                        &lt;button class="page-link" type="button" onclick="loadProjects(${projectsCurrentPage - 1})"&gt;` |
| 580 | `                        &lt;button class="page-link" type="button" onclick="loadProjects(${projectsCurrentPage + 1})"&gt;` |
| 698 | `                        &lt;button type="button" class="btn-close btn-close-white" aria-label="Quitar" onclick="removeProjectStudent('${escapeHtml(student.id)}')"&gt;&lt;/button&gt;` |
| 975 | `        document.addEventListener('DOMContentLoaded', async () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 467 | `const response = await api.get('/projects', params);` |
| 642 | `const project = await api.get(`/projects/${projectId}`);` |
| 669 | `api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 }),` |
| 670 | `api.get('/projects', { compact: 1, per_page: 500, _cache_ttl: 30000 })` |
| 727 | `const groups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |
| 770 | `await api.put(`/projects/${editingId}`, payload);` |
| 773 | `await api.post('/projects', payload);` |
| 788 | `const project = await api.get(`/projects/${projectId}`);` |
| 826 | `api.get(`/projects/${projectId}`),` |
| 827 | `api.get('/asignaturas', { per_page: 100, _cache_ttl: 60000 })` |
| 852 | `await api.post(`/projects/${projectId}/asignaturas`, { asignatura_ids: asignaturaIds });` |
| 872 | `await api.put(`/projects/${projectId}`, { is_thesis: isThesis });` |
| 888 | `api.delete(`/projects/${projectId}`).then(() =&gt; {` |
| 895 | `fetch(`${API_BASE_URL}/projects-template.xls`, {` |
| 936 | `const result = await api.post('/projects/import-excel', formData);` |

## pages/admin/proposal-config.php

[Fuente](../../pages/admin/proposal-config.php) · [Código completo](REFERENCIA.md#pages-admin-proposal-config-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 105 | flecha | `const esc = value =&gt;` |
| 107 | función | `async function loadConfig()` |
| 114 | función | `function renderSubjectOptions()` |
| 123 | función | `function teacherOptions()` |
| 129 | función | `function selectedSubject()` |
| 134 | función | `function groupHasSubject(group, subjectId)` |
| 138 | función | `function subjectAssignments(group, subjectId)` |
| 142 | función | `function filteredGroups()` |
| 157 | función | `function renderResponsibleTable()` |
| 206 | función | `function renderWindows()` |
| 242 | función | `async function assignTeacher(groupId)` |
| 276 | función | `async function deleteAssignment(id)` |
| 286 | función | `async function addWindow(groupId)` |
| 308 | función | `async function deleteWindow(id)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 27 | `                &lt;button class="btn btn-primary" onclick="loadConfig()"&gt;&lt;i class="bi bi-arrow-clockwise"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 42 | `                            &lt;select class="form-select" id="subjectFilter" onchange="renderResponsibleTable()" disabled&gt;&lt;/select&gt;` |
| 46 | `                            &lt;select class="form-select" id="semesterFilter" onchange="renderResponsibleTable()"&gt;` |
| 56 | `                      &lt;input class="form-control" id="groupSearch" placeholder="Ej. 5to A, B, 2026" oninput="renderResponsibleTable()"&gt;` |
| 198 | `                    &lt;button class="btn btn-sm btn-primary" onclick="assignTeacher(${group.id})" title="Asignar docente"&gt;&lt;i class="bi bi-person-plus"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 199 | `                    ${assignments.map(item =&gt; `&lt;button class="btn btn-sm btn-outline-danger" onclick="deleteAssignment('${item.id}')" title="Quitar ${esc(item.teacher?.nombres &#124;&#124; 'docente')}"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;`).join('')}` |
| 216 | `                &lt;button class="btn btn-sm btn-outline-danger float-end" onclick="deleteWindow(${windowItem.id})"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 233 | `                            &lt;div class="col-md-2 d-grid"&gt;&lt;button class="btn btn-sm btn-primary" onclick="addWindow(${group.id})"&gt;&lt;i class="bi bi-plus"&gt;&lt;/i&gt;&lt;/button&gt;&lt;/div&gt;` |
| 318 | `document.addEventListener('DOMContentLoaded', loadConfig);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 108 | `config = await api.get('/proposal/config');` |
| 255 | `const response = await api.post('/proposal/assignments', {` |
| 278 | `await api.delete(`/proposal/assignments/${id}`);` |
| 295 | `const response = await api.post('/proposal/windows', { subject_group_id: groupId, starts_at, ends_at, activo: true });` |
| 310 | `await api.delete(`/proposal/windows/${id}`);` |

## pages/admin/settings.php

[Fuente](../../pages/admin/settings.php) · [Código completo](REFERENCIA.md#pages-admin-settings-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 124 | función | `function esc(value)` |
| 128 | función | `function previewGrayscaleMode()` |
| 132 | función | `async function loadSettings()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 31 | `                &lt;button class="btn btn-outline-primary" onclick="loadSettings()"&gt;&lt;i class="bi bi-arrow-clockwise"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 100 | `                  &lt;input class="form-check-input" type="checkbox" role="switch" id="grayscale_mode" onchange="previewGrayscaleMode()"&gt;` |
| 149 | `document.getElementById('settingsForm').addEventListener('submit', async event =&gt; {` |
| 180 | `document.addEventListener('DOMContentLoaded', loadSettings);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 134 | `const settings = await api.get('/settings');` |
| 170 | `const response = await api.put('/settings', payload);` |

## pages/admin/user-create.php

[Fuente](../../pages/admin/user-create.php) · [Código completo](REFERENCIA.md#pages-admin-user-create-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 196 | función | `async function loadGroupsForSemester()` |
| 207 | función | `function toggleStudentFields()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 151 | `        form.addEventListener('submit', async (e) =&gt; {` |
| 212 | `        document.getElementById('perfil_id').addEventListener('change', toggleStudentFields);` |
| 213 | `        document.getElementById('semestre').addEventListener('change', loadGroupsForSemester);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 176 | `await api.post('/users', formData);` |
| 201 | `const groups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |

## pages/admin/user-edit.php

[Fuente](../../pages/admin/user-edit.php) · [Código completo](REFERENCIA.md#pages-admin-user-edit-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 158 | función | `async function loadUser()` |
| 260 | función | `async function loadGroupsForSemester(selected = '')` |
| 272 | función | `function toggleStudentFields()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 197 | `        form.addEventListener('submit', async (e) =&gt; {` |
| 259 | `        document.addEventListener('DOMContentLoaded', loadUser);` |
| 277 | `        document.getElementById('perfil_id').addEventListener('change', toggleStudentFields);` |
| 278 | `        document.getElementById('semestre').addEventListener('change', () =&gt; loadGroupsForSemester());` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 160 | `const response = await api.get(`/users/${userId}`);` |
| 239 | `await api.put(`/users/${userId}`, formData);` |
| 265 | `const groups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |

## pages/admin/users.php

[Fuente](../../pages/admin/users.php) · [Código completo](REFERENCIA.md#pages-admin-users-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 474 | función | `function escapeHtml(value)` |
| 478 | función | `function updateFilterButtons()` |
| 500 | función | `async function loadUsers(page = 1)` |
| 581 | función | `function toggleUserSelection(userId, checked)` |
| 591 | función | `function toggleVisibleUsers(checked)` |
| 605 | función | `function selectVisibleUsers()` |
| 609 | función | `function clearSelectedUsers()` |
| 615 | función | `function updateSelectedUsersUi()` |
| 653 | función | `function enableCredentialSelectionMode()` |
| 663 | función | `function cancelCredentialSelectionMode()` |
| 670 | función | `function renderPagination(pagination)` |
| 718 | función | `function pageButton(page, current)` |
| 724 | función | `function setStatusFilter(status)` |
| 730 | función | `function setProfileFilter(profile)` |
| 737 | función | `function scheduleUsersSearch()` |
| 742 | función | `function clearUsersSearch()` |
| 747 | función | `async function loadGroupsForFilter()` |
| 759 | función | `function resetUserModal()` |
| 774 | función | `async function openUserModal(userId = null)` |
| 810 | función | `function toggleUserStudentFields()` |
| 815 | función | `async function loadUserModalGroups(selected = '')` |
| 827 | función | `async function saveUserModal(event)` |
| 901 | función | `async function openGroupControl()` |
| 908 | función | `async function loadControlGroups()` |
| 952 | función | `async function createControlGroup()` |
| 956 | función | `async function editControlGroup(groupId)` |
| 962 | función | `function openGroupForm(group = null)` |
| 981 | función | `async function saveControlGroup(event)` |
| 1032 | función | `async function findDuplicateGroup(semester, groupCode, ignoreId = null)` |
| 1045 | función | `async function deleteControlGroup(groupId, membersCount)` |
| 1069 | función | `async function assignStudentToGroup(groupId)` |
| 1083 | función | `async function removeStudentFromGroup(studentId)` |
| 1100 | función | `async function toggleUserStatus(userId, adminPassword = null)` |
| 1110 | función | `async function protectedAdminAction(userId)` |
| 1120 | función | `function downloadUsersExcelTemplate()` |
| 1142 | función | `function openUsersImportModal()` |
| 1149 | función | `function currentUsersFilterPayload()` |
| 1167 | función | `function selectedUsersPayload()` |
| 1171 | función | `function selectedCredentialProfiles()` |
| 1176 | función | `function syncCredentialProfileChecksWithCurrentFilter()` |
| 1184 | función | `function describeCredentialScope(payload)` |
| 1198 | función | `function selectedCredentialScope()` |
| 1203 | función | `function currentCredentialMode()` |
| 1207 | función | `function refreshCredentialScopeText()` |
| 1214 | función | `async function openCredentialEmailModal()` |
| 1243 | función | `async function sendCredentialEmails(event)` |
| 1319 | función | `async function importUsersExcel(event)` |
| 1343 | función | `function renderImportException(target, error)` |
| 1359 | función | `function renderImportResult(target, result)` |
| 1373 | función | `function showError(message)` |
| 1390 | función | `function initUsersMobileOptions()` |
| 1403 | función | `function updateUsersMobileFilterSummary()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 32 | `                        &lt;button type="button" class="btn btn-outline-primary" onclick="downloadUsersExcelTemplate()"&gt;` |
| 35 | `                        &lt;button type="button" class="btn btn-outline-success" onclick="openUsersImportModal()"&gt;` |
| 38 | `                  &lt;button type="button" class="btn btn-outline-warning" id="credentialEmailOpenBtn" onclick="openCredentialEmailModal()"&gt;` |
| 41 | `                        &lt;button type="button" class="btn btn-primary" onclick="openUserModal()"&gt;` |
| 66 | `                        &lt;button type="button" class="btn btn-outline-primary" onclick="downloadUsersExcelTemplate()"&gt;` |
| 69 | `                        &lt;button type="button" class="btn btn-outline-success" onclick="openUsersImportModal()"&gt;` |
| 72 | `                        &lt;button type="button" class="btn btn-outline-warning" onclick="openCredentialEmailModal()"&gt;` |
| 75 | `                        &lt;button type="button" class="btn btn-primary" onclick="openUserModal()"&gt;` |
| 83 | `                            &lt;button type="button" class="btn btn-primary" id="filterActive" onclick="setStatusFilter('active')"&gt;&lt;i class="bi bi-check-circle"&gt;&lt;/i&gt; Activos&lt;/button&gt;` |
| 84 | `                        &lt;button type="button" class="btn btn-outline-secondary" id="filterInactive" onclick="setStatusFilter('inactive')"&gt;&lt;i class="bi bi-pause-circle"&gt;&lt;/i&gt; Inactivos&lt;/button&gt;` |
| 85 | `                            &lt;button type="button" class="btn btn-outline-secondary" id="filterAll" onclick="setStatusFilter('all')"&gt;&lt;i class="bi bi-list-ul"&gt;&lt;/i&gt; Todos&lt;/button&gt;` |
| 92 | `                            &lt;button type="button" class="btn btn-primary" id="profileAll" onclick="setProfileFilter('all')"&gt;&lt;i class="bi bi-list-ul"&gt;&lt;/i&gt; Todos los perfiles&lt;/button&gt;` |
| 93 | `                       &lt;button type="button" class="btn btn-outline-secondary" id="profileStudents" onclick="setProfileFilter('3')"&gt;&lt;i class="bi bi-mortarboard"&gt;&lt;/i&gt; Estudiantes&lt;/button&gt;` |
| 94 | `                       &lt;button type="button" class="btn btn-outline-secondary" id="profileTeachers" onclick="setProfileFilter('2')"&gt;&lt;i class="bi bi-person-workspace"&gt;&lt;/i&gt; Docentes&lt;/button&gt;` |
| 95 | `                         &lt;button type="button" class="btn btn-outline-secondary" id="profileAdmins" onclick="setProfileFilter('1')"&gt;&lt;i class="bi bi-shield-lock"&gt;&lt;/i&gt; Administrativos&lt;/button&gt;` |
| 105 | `trol" id="userSearchInput" placeholder="No. de Control, No. de empleado, nombre, correo o teléfono" oninput="scheduleUsersSearch()"&gt;` |
| 106 | `                            &lt;button type="button" class="btn btn-outline-secondary" onclick="clearUsersSearch()" title="Limpiar búsqueda"&gt;&lt;i class="bi bi-x-lg"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 114 | `                        &lt;select class="form-select" id="semesterFilter" onchange="loadGroupsForFilter(); loadUsers(1);"&gt;` |
| 125 | `                        &lt;select class="form-select" id="groupFilter" onchange="loadUsers(1)"&gt;` |
| 130 | `                        &lt;button class="btn btn-outline-primary" onclick="openGroupControl()"&gt;&lt;i class="bi bi-people"&gt;&lt;/i&gt; Control de grupos&lt;/button&gt;` |
| 140 | `                            &lt;button type="button" class="btn btn-outline-secondary" onclick="selectVisibleUsers()"&gt;` |
| 143 | `                            &lt;button type="button" class="btn btn-outline-secondary" onclick="clearSelectedUsers()"&gt;` |
| 146 | `                            &lt;button type="button" class="btn btn-outline-secondary" onclick="cancelCredentialSelectionMode()"&gt;` |
| 157 | `                    &lt;input class="form-check-input" type="checkbox" id="selectVisibleUsersCheckbox" onchange="toggleVisibleUsers(this.checked)" title="Seleccionar usuarios visibles"&gt;` |
| 183 | `            &lt;form class="modal-content" id="usersImportForm" onsubmit="importUsersExcel(event)"&gt;` |
| 204 | `            &lt;form class="modal-content" id="credentialEmailForm" onsubmit="sendCredentialEmails(event)"&gt;` |
| 234 | `      &lt;input class="form-check-input credential-profile-checkbox" type="checkbox" value="1" checked onchange="refreshCredentialScopeText()"&gt;` |
| 238 | `      &lt;input class="form-check-input credential-profile-checkbox" type="checkbox" value="2" checked onchange="refreshCredentialScopeText()"&gt;` |
| 242 | `      &lt;input class="form-check-input credential-profile-checkbox" type="checkbox" value="3" checked onchange="refreshCredentialScopeText()"&gt;` |
| 281 | `                &lt;form id="userModalForm" class="needs-validation" novalidate onsubmit="saveUserModal(event)"&gt;` |
| 304 | `                                &lt;select class="form-select" id="userProfile" required onchange="toggleUserStudentFields()"&gt;` |
| 317 | `                                &lt;select class="form-select" id="userSemester" onchange="loadUserModalGroups()"&gt;` |
| 382 | `                            &lt;select class="form-select" id="controlSemester" onchange="loadControlGroups()"&gt;` |
| 387 | `                            &lt;button type="button" class="btn btn-primary" onclick="createControlGroup()"&gt;` |
| 401 | `                &lt;form id="groupForm" onsubmit="saveControlGroup(event)"&gt;` |
| 545 | `                        : `&lt;button class="btn ${toggleClass}" onclick="toggleUserStatus('${escapeHtml(user.id)}')" title="${toggleTitle}"&gt;&lt;i class="bi ${toggleIcon}"&gt;&lt;/i&gt;&lt;/button&gt;`;` |
| 547 | `                        ? `&lt;button class="btn btn-outline-secondary" onclick="protectedAdminAction('${escapeHtml(user.id)}')" title="Acción protegida"&gt;&lt;i class="bi bi-shield-lock"&gt;&lt;/i&gt;&lt;/button&gt;`` |
| 553 | `&lt;input class="form-check-input user-select-checkbox" type="checkbox" value="${escapeHtml(user.id)}" onchange="toggleUserSelection('${escapeHtml(user.id)}', this.checked)" ${selectedUserIds.has(String(user.id)) ? 'checked' : ''} aria-label="Seleccionar usuario ${escapeHtml(user.id)}"&gt;` |
| 564 | `                                    &lt;button type="button" class="btn btn-outline-primary" onclick="openUserModal('${escapeHtml(user.id)}')" title="Editar"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 708 | `                            &lt;button class="page-link" type="button" onclick="loadUsers(${current - 1})"&gt;Anterior&lt;/button&gt;` |
| 712 | `                            &lt;button class="page-link" type="button" onclick="loadUsers(${current + 1})"&gt;Siguiente&lt;/button&gt;` |
| 721 | `                    &lt;button class="page-link" type="button" onclick="loadUsers(${page})"&gt;${page}&lt;/button&gt;` |
| 932 | `                                        &lt;button type="button" class="btn btn-light" onclick="editControlGroup(${group.id})" title="Editar grupo"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 933 | `                                        &lt;button type="button" class="btn btn-outline-light" onclick="deleteControlGroup(${group.id}, ${members.length})" title="Eliminar grupo"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 943 | `                                    &lt;button class="btn btn-outline-primary" onclick="assignStudentToGroup(${group.id})"&gt;&lt;i class="bi bi-plus"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 945 | `')}&lt;/span&gt;&lt;div class="btn-group btn-group-sm"&gt;&lt;button type="button" class="btn btn-outline-primary" onclick="openUserModal('${escapeHtml(student.id)}')"&gt;&lt;i class="bi bi-pencil"&gt;&lt;/i&gt;&lt;/button&gt;&lt;button class="btn btn-outline-warning" onclick="removeStudentFromGroup('${escapeHtml(student.id)}')" title="Quitar de` |
| 1313 | `        document.addEventListener('change', (event) =&gt; {` |
| 1383 | `        document.addEventListener('DOMContentLoaded', () =&gt; {` |
| 1397 | `            toggle.addEventListener('click', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 519 | `const response = await api.get('/users', params);` |
| 753 | `groups = await api.get('/subject-groups', { ...params, _cache_ttl: 60000 });` |
| 786 | `loadedModalUser = await api.get(`/users/${userId}`);` |
| 820 | `const modalGroups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |
| 885 | `await api.put(`/users/${editingId}`, payload);` |
| 888 | `await api.post('/users', payload);` |
| 913 | `api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 }),` |
| 914 | `api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 500, _cache_ttl: 30000 })` |
| 1009 | `await api.put(`/subject-groups/${editingGroupId}`, data);` |
| 1012 | `await api.post('/subject-groups', data);` |
| 1035 | `: await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });` |
| 1059 | `await api.delete(`/subject-groups/${groupId}`);` |
| 1074 | `await api.post(`/subject-groups/${groupId}/students`, { student_ids: [studentId] });` |
| 1091 | `await api.put(`/users/${studentId}`, { semestre: null, grupo: null });` |
| 1103 | `await api.post(`/users/${userId}/toggle-active`, payload);` |
| 1121 | `fetch(`${API_BASE_URL}/users-template.xls`, {` |
| 1231 | `const template = await api.get('/users/credential-email-template', { _cache_ttl: 300000 });` |
| 1292 | `const result = await api.post('/users/send-credentials', payload, { _timeout: 60000 });` |
| 1332 | `const result = await api.post('/users/import-excel', formData);` |

## pages/evaluation-documents.php

[Fuente](../../pages/evaluation-documents.php) · [Código completo](REFERENCIA.md#pages-evaluation-documents-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 93 | función | `async function loadDocuments(forceFresh = false)` |
| 113 | función | `function renderDocuments()` |
| 177 | función | `function setDocumentSection(section)` |
| 185 | función | `function renderEvaluationDelivery(project, type)` |
| 222 | función | `function renderReleaseReview(project, delivery)` |
| 248 | función | `function documentId(delivery)` |
| 252 | función | `function renderThesisUploadBox()` |
| 286 | función | `function renderThesisDocument(document)` |
| 321 | función | `async function uploadEvaluationDelivery(projectId, type)` |
| 363 | función | `async function saveReleaseReview(documentId)` |
| 380 | función | `async function uploadThesisDocument()` |
| 419 | función | `async function downloadRepositoryDocument(documentId, name)` |
| 438 | función | `async function toggleRepositoryPublication(documentId, makePublic)` |
| 466 | función | `function memberNames(members)` |
| 471 | función | `function subjectNames(subjects)` |
| 476 | función | `function fullName(user)` |
| 480 | función | `function esc(value)` |
| 486 | función | `function escAttr(value)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 60 | `                &lt;button class="btn btn-outline-primary" type="button" onclick="loadDocuments(true)"&gt;` |
| 85 | `document.addEventListener('DOMContentLoaded', () =&gt; {` |
| 150 | `     &lt;button class="btn ${state.section === 'evaluations' ? 'btn-primary' : 'btn-outline-primary'}" onclick="setDocumentSection('evaluations')"&gt;` |
| 153 | `          &lt;button class="btn ${state.section === 'thesis' ? 'btn-primary' : 'btn-outline-primary'}" onclick="setDocumentSection('thesis')"&gt;` |
| 205 | `                    &lt;button class="btn btn-sm btn-outline-secondary mt-2" onclick="downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')"&gt;` |
| 213 | `                    &lt;button class="btn btn-sm btn-primary mt-2" onclick="uploadEvaluationDelivery(${project.project.id}, '${type}')"&gt;` |
| 241 | `                &lt;button class="btn btn-sm btn-success mt-3" onclick="saveReleaseReview(${documentId(delivery)})"&gt;` |
| 277 | `                    &lt;button type="button" class="btn btn-sm btn-primary w-100" onclick="uploadThesisDocument()"&gt;` |
| 313 | `                    &lt;button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')"&gt;&lt;i class="bi bi-download"&gt;&lt;/i&gt; Descargar&lt;/button&gt;` |
| 314 | `ton" class="btn btn-sm ${document.visibility === 'public' ? 'btn-outline-warning' : 'btn-success'}" onclick="toggleRepositoryPublication(${document.id}, ${document.visibility === 'public' ? 'false' : 'true'})"&gt;&lt;i class="bi ${document.visibility === 'public' ? 'bi-eye-slash' : 'bi-globe2'}"&gt;&lt;/i&gt; ${document.v` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 99 | `api.get('/repositorio/evaluation-documents', forceFresh ? { _fresh: true } : {}),` |
| 100 | `api.get('/repositorio/thesis-documents', forceFresh ? { _fresh: true } : {})` |
| 353 | `const result = await api.post('/repositorio/evaluation-documents', formData, { _timeout: 120000 });` |
| 370 | `const result = await api.put(`/repositorio/evaluation-documents/${documentId}/release-status`, { students });` |
| 410 | `const result = await api.post('/repositorio/thesis-documents', formData, { _timeout: 120000 });` |
| 421 | `const response = await fetch(`${API_BASE_URL}/repositorio/${documentId}/download`, {` |
| 449 | `const result = await api.post(`/repositorio/${documentId}/publish`, { public: makePublic });` |

## pages/profile.php

[Fuente](../../pages/profile.php) · [Código completo](REFERENCIA.md#pages-profile-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 63 | función | `function photoUrl(path)` |
| 64 | función | `async function loadProfile()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 75 | `document.getElementById('profileForm').addEventListener('submit', async e =&gt; {` |
| 97 | `document.addEventListener('DOMContentLoaded', loadProfile);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 65 | `currentUser = await api.get('/profile');` |
| 84 | `const res = await api.post('/profile', data);` |
| 85 | `await fetch('/api/set-session.php', {` |

## pages/repositorio-detail.php

[Fuente](../../pages/repositorio-detail.php) · [Código completo](REFERENCIA.md#pages-repositorio-detail-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 47 | función | `function esc(value)` |
| 51 | función | `function repositoryDetailDate(value)` |
| 59 | función | `async function loadDocumento()` |
| 142 | función | `async function renderDocumentPreview(fileType, viewUrl, downloadUrl)` |
| 192 | función | `async function fetchPreviewBlobUrl(viewUrl)` |
| 210 | función | `function previewFallback(downloadUrl, message)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 220 | `        document.addEventListener('DOMContentLoaded', loadDocumento);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 69 | `const doc = await api.get(`/repositorio/${docId}`);` |
| 163 | `const response = await fetch(viewUrl, { credentials: 'include' });` |
| 174 | `const response = await fetch(viewUrl, { credentials: 'include' });` |
| 198 | `const response = await fetch(viewUrl, {` |

## pages/repositorio.php

[Fuente](../../pages/repositorio.php) · [Código completo](REFERENCIA.md#pages-repositorio-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 183 | función | `function escapeHtml(value)` |
| 187 | función | `function formatRepositoryDate(value)` |
| 196 | función | `async function loadDocumentos(page = 1)` |
| 273 | función | `function renderRepositoryPagination(response)` |
| 292 | función | `async function loadRepositoryAdminData(selectedTagIds = [])` |
| 309 | función | `async function openRepositoryUploadModal()` |
| 328 | función | `async function openRepositoryEditModal(documentId)` |
| 356 | función | `async function loadStudentRepositoryProjects()` |
| 366 | función | `function refreshRepositoryVisibilityHelp()` |
| 375 | función | `async function submitRepositoryDocument(event)` |
| 418 | función | `async function deleteRepositoryDocument(documentId)` |
| 462 | función | `function repositoryCategoryLabel(category)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 28 | `                &lt;button type="button" class="btn btn-primary" onclick="openRepositoryUploadModal()"&gt;` |
| 93 | `                &lt;form id="repositoryUploadForm" onsubmit="submitRepositoryDocument(event)"&gt;` |
| 224 | `                           &lt;button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="openRepositoryEditModal(${Number(doc.id)})"&gt;` |
| 227 | `                            &lt;button type="button" class="btn btn-sm btn-outline-danger flex-fill" onclick="deleteRepositoryDocument(${Number(doc.id)})"&gt;` |
| 269 | `rueba la conexión e intenta nuevamente.')}&lt;/p&gt;&lt;button class="btn btn-outline-primary" type="button" onclick="loadDocumentos(currentPage)"&gt;&lt;i class="bi bi-arrow-clockwise"&gt;&lt;/i&gt; Reintentar&lt;/button&gt;&lt;/div&gt;&lt;/div&gt;`;` |
| 286 | `== 1 ? 'disabled' : ''}"&gt;&lt;button class="page-link" type="button" ${current === 1 ? 'disabled' : ''} onclick="loadDocumentos(${current - 1})" aria-label="Página anterior"&gt;&lt;i class="bi bi-chevron-left"&gt;&lt;/i&gt;&lt;/button&gt;&lt;/li&gt;` |
| 287 | ` `&lt;li class="page-item ${page === current ? 'active' : ''}"&gt;&lt;button class="page-link" type="button" onclick="loadDocumentos(${page})"&gt;${page}&lt;/button&gt;&lt;/li&gt;`).join('')}` |
| 288 | `t ? 'disabled' : ''}"&gt;&lt;button class="page-link" type="button" ${current === last ? 'disabled' : ''} onclick="loadDocumentos(${current + 1})" aria-label="Página siguiente"&gt;&lt;i class="bi bi-chevron-right"&gt;&lt;/i&gt;&lt;/button&gt;&lt;/li&gt;` |
| 445 | `        document.getElementById('searchInput').addEventListener('input', (e) =&gt; {` |
| 451 | `        document.getElementById('sortFilter').addEventListener('change', (e) =&gt; {` |
| 456 | `        document.getElementById('categoryFilter').addEventListener('change', (e) =&gt; {` |
| 460 | `        document.getElementById('repoVisibility')?.addEventListener('change', refreshRepositoryVisibilityHelp);` |
| 475 | `        document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 201 | `const response = await api.get(endpoint, {` |
| 294 | `const tagsResponse = await api.get('/document-tags', { per_page: 100 });` |
| 342 | `const doc = await api.get(`/repositorio/${documentId}`);` |
| 359 | `const response = await api.get('/my-projects', { compact: 1, per_page: 100, _fresh: 1 });` |
| 406 | `await api.post(documentId ? `/repositorio/${documentId}` : '/repositorio', formData);` |
| 435 | `await api.delete(`/repositorio/${documentId}`);` |

## pages/student/dashboard.php

[Fuente](../../pages/student/dashboard.php) · [Código completo](REFERENCIA.md#pages-student-dashboard-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 209 | función | `function escapeHtml(value)` |
| 213 | función | `function fullName(user)` |
| 217 | función | `function projectActiveAdvisors(project)` |
| 222 | función | `function percent(value, total)` |
| 226 | función | `function statusLabel(status)` |
| 238 | función | `function renderBarChart(containerId, data)` |
| 258 | función | `function renderStatusGrid(containerId, data)` |
| 273 | función | `function projectProgress(project)` |
| 277 | función | `function updateStudentNextAction(stats)` |
| 301 | función | `async function loadDashboard()` |
| 348 | función | `async function loadEvaluationSchedule()` |
| 378 | función | `async function checkInitialProfile()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 387 | `        document.getElementById('initialProfileForm').addEventListener('submit', async (event) =&gt; {` |
| 404 | `        document.addEventListener('DOMContentLoaded', loadDashboard);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 304 | `const response = await api.get('/dashboard/student');` |
| 351 | `const schedule = await api.get('/student/evaluation-schedule');` |
| 379 | `const status = await api.get('/proposal/student-status');` |
| 390 | `const response = await api.post('/profile/complete-initial', new FormData(event.target));` |
| 391 | `await fetch('/api/set-session.php', {` |

## pages/student/my-deliverables.php

[Fuente](../../pages/student/my-deliverables.php) · [Código completo](REFERENCIA.md#pages-student-my-deliverables-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 100 | función | `function escapeHtml(value)` |
| 104 | función | `function fullName(user)` |
| 108 | función | `function gradedByName(deliverable)` |
| 113 | función | `async function loadDeliverables()` |
| 216 | función | `function abrirSubirArchivo(deliverableId, projectId, nombre)` |
| 227 | función | `async function guardarArchivo()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 82 | `                    &lt;button type="button" class="btn btn-primary" onclick="guardarArchivo()"&gt;Subir Archivo&lt;/button&gt;` |
| 145 | `                                    onclick="descargarEntregable(${deliverable.id}, '${deliverable.nombre}', ${deliverable.project_id})"&gt;` |
| 199 | `                                            onclick="abrirSubirArchivo(${deliverable.id}, ${deliverable.project_id}, '${deliverable.nombre}')"&gt;` |
| 242 | `        document.addEventListener('DOMContentLoaded', loadDeliverables);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 115 | `const response = await api.get('/my-deliverables');` |

## pages/student/proposal-register.php

[Fuente](../../pages/student/proposal-register.php) · [Código completo](REFERENCIA.md#pages-student-proposal-register-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 9 | flecha | `const esc=v=&gt;` |
| 10 | función | `function renderSelected()` |
| 11 | función | `async function loadStatus()` |
| 12 | función | `async function searchStudents()` |
| 13 | función | `function addSelectedStudent()` |
| 14 | función | `function showSavedProject(project)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 7 | `class="col-md-4 d-flex align-items-end"&gt;&lt;button type="button" class="btn btn-outline-primary w-100" onclick="addSelectedStudent()"&gt;&lt;i class="bi bi-person-plus"&gt;&lt;/i&gt; Agregar&lt;/button&gt;&lt;/div&gt;&lt;div class="col-12"&gt;&lt;div id="selectedStudents" class="d-flex flex-wrap gap-2"&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div class="text-end mt-4` |
| 10 | `{esc(s.id)} - ${esc(s.nombres)} ${esc(s.apa&#124;&#124;'')} &lt;button type="button" class="btn btn-sm p-0 ms-1" onclick="selected=selected.filter(x=&gt;x.id!=='${esc(s.id)}');renderSelected()"&gt;&lt;i class="bi bi-x"&gt;&lt;/i&gt;&lt;/button&gt;&lt;/span&gt;`).join('');}` |
| 15 | `document.getElementById('studentSearch').addEventListener('input',searchStudents);` |
| 16 | `document.getElementById('projectForm').addEventListener('submit',async e=&gt;{e.preventDefault();const data=Object.fromEntries(new FormData(e.target).entries());try{let response;if(editingProjectId){response=await api.put(`/projects/${editingProjectId}`,data)` |
| 17 | `document.addEventListener('DOMContentLoaded',loadStatus);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 11 | `async function loadStatus(){statusData=await api.get('/proposal/student-status');const box=document.getElementById('statusBox');if(statusData.profile_required){box.innerHTML='&lt;div class="alert alert-warning"&gt;Debes completar tu perfil inicial antes de registrar un proyecto.&lt;/div&gt;';return;}if(statusData.project){const p=statusData.project;const canEdit=p.proposal_status==='requiere_cambios'&&p.revision_allowed_until&&new Date(p.revision_allowed_until)&gt;new Date();box.innerHTML=`&lt;div class="alert ${canEdit?'alert-warning':'alert-info'}"&gt;&lt;strong&gt;Proyecto ligado:&lt;/strong&gt; ${esc(p.title)}&lt;br&gt;Estado: &lt;span class="badge bg-secondary"&gt;${esc(p.proposal_status)}&lt;/span&gt;${p.revision_allowed_until?`&lt;br&gt;Correcciones hasta: ${new Date(p.revision_allowed_until).toLocaleString('es-MX')}`:''}${p.proposal_review_comment?`&lt;br&gt;&lt;strong&gt;Observaciones:&lt;/strong&gt; ${esc(p.proposal_review_comment)}`:''}&lt;/div&gt;`;if(canEdit){editingProjectId=p.id;document.querySelector('[name="title"]').value=p.title&#124;&#124;'';document.querySelector('[name="description"]').value=p.description&#124;&#124;'';['company_name','company_giro','company_contact_name','company_contact_position','company_address'].forEach(k=&gt;document.querySelector(`[name="${k}"]`).value=p[k]&#124;&#124;'');document.getElementById('formCard').style.display='';document.querySelector('#projectForm button[type="submit"]').innerHTML='&lt;i class="bi bi-save"&gt;&lt;/i&gt; Enviar corrección';document.getElementById('studentSearch').closest('.col-md-8').style.display='none';document.getElementById('studentSearch').closest('.col-md-8').nextElementSibling.style.display='none';}return;}if(!statusData.subject_group){box.innerHTML='&lt;div class="alert alert-secondary"&gt;El registro corresponde a quinto semestre. Solicita una excepción al administrador para poder participar.&lt;/div&gt;';return;}if(!statusData.active_window){box.innerHTML=`&lt;div class="alert alert-secondary"&gt;El registro para &lt;strong&gt;${esc(statusData.default_subject?.nombre&#124;&#124;'Fundamentos de Ingeniería de Software')}&lt;/strong&gt; no está abierto para la carga ${esc(statusData.subject_group.nombre&#124;&#124;'asignada')}.&lt;/div&gt;`;return;}box.innerHTML=`&lt;div class="alert alert-success"&gt;&lt;strong&gt;${esc(statusData.default_subject?.nombre&#124;&#124;'Fundamentos de Ingeniería de Software')}&lt;/strong&gt;&lt;br&gt;${statusData.has_exception?'Acceso habilitado mediante excepción.&lt;br&gt;':''}Registro disponible hasta ${new Date(statusData.active_window.ends_at).toLocaleString('es-MX')}&lt;/div&gt;`;document.getElementById('formCard').style.display='';}` |
| 12 | `async function searchStudents(){const q=document.getElementById('studentSearch').value;if(q.length&lt;2)return;students=await api.get('/proposal/students/search',{q});const selectedIds=selected.map(s=&gt;String(s.id));document.getElementById('studentsList').innerHTML=students.filter(s=&gt;!selectedIds.includes(String(s.id))).map(s=&gt;`&lt;option value="${esc(s.id)} - ${esc(s.nombres)} ${esc(s.apa&#124;&#124;'')}"&gt;&lt;/option&gt;`).join('');}` |
| 16 | `document.getElementById('projectForm').addEventListener('submit',async e=&gt;{e.preventDefault();const data=Object.fromEntries(new FormData(e.target).entries());try{let response;if(editingProjectId){response=await api.put(`/projects/${editingProjectId}`,data);swalToast('Corrección enviada','success');}else{data.subject_group_id=statusData.subject_group.id;data.student_ids=selected.map(s=&gt;s.id);response=await api.post('/projects',data);swalToast('Proyecto registrado','success');}showSavedProject(response.project);}catch(err){Swal.fire('Error',err.message,'error');}});` |

## pages/teacher/dashboard.php

[Fuente](../../pages/teacher/dashboard.php) · [Código completo](REFERENCIA.md#pages-teacher-dashboard-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 173 | función | `function escapeHtml(value)` |
| 177 | función | `function percent(value, total)` |
| 181 | función | `function statusLabel(status)` |
| 193 | función | `function fullName(user)` |
| 197 | función | `function renderBarChart(containerId, data)` |
| 217 | función | `function renderStatusGrid(containerId, data)` |
| 232 | función | `function projectProgress(project)` |
| 236 | función | `function updateTeacherNextAction(stats)` |
| 260 | función | `async function loadDashboard()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 309 | `        document.addEventListener('DOMContentLoaded', loadDashboard);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 262 | `const response = await api.get('/dashboard/teacher');` |

## pages/teacher/my-deliverables.php

[Fuente](../../pages/teacher/my-deliverables.php) · [Código completo](REFERENCIA.md#pages-teacher-my-deliverables-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 98 | función | `function escapeHtml(value)` |
| 102 | función | `function fullName(user)` |
| 106 | función | `function debouncedLoad()` |
| 111 | función | `async function loadDeliverablesMatrix()` |
| 132 | función | `function renderSubjectFilter(projects)` |
| 147 | función | `function renderMatrix(projects)` |
| 169 | función | `function renderStudent(projectId, studentBlock, index)` |
| 211 | función | `function renderItem(item)` |
| 246 | función | `function openGradeModal(deliverableId, projectId, name, currentGrade = null)` |
| 256 | función | `async function saveGrade(event)` |
| 277 | función | `function replaceDeliverableInMatrix(updatedDeliverable)` |
| 279 | flecha | `const visit = value =&gt;` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 33 | `                    &lt;button class="btn btn-outline-primary" onclick="loadDeliverablesMatrix()"&gt;` |
| 43 | `="form-control" id="studentSearch" placeholder="No. de Control, No. de empleado, nombre o apellido" oninput="debouncedLoad()"&gt;` |
| 47 | `                        &lt;select class="form-select" id="subjectFilter" onchange="loadDeliverablesMatrix()"&gt;` |
| 64 | `            &lt;form class="modal-content" onsubmit="saveGrade(event)"&gt;` |
| 226 | `                ? `&lt;button class="btn btn-sm btn-outline-info" onclick="descargarEntregable(${deliverable.id}, '${escapeHtml(deliverable.nombre)}', ${deliverable.project_id})" title="Descargar"&gt;&lt;i class="bi bi-download"&gt;&lt;/i&gt;&lt;/button&gt;`` |
| 229 | `                ? `&lt;button class="btn btn-sm btn-outline-primary" onclick="openGradeModal(${deliverable.id}, ${deliverable.project_id}, '${escapeHtml(deliverable.nombre)}', ${grade === null &#124;&#124; grade === undefined ? 'null' : Number(grade)})" title="Calificar"&gt;&lt;i class="bi bi` |
| 296 | `        document.addEventListener('DOMContentLoaded', () =&gt; {` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 122 | `const response = await api.get('/teacher/deliverables-matrix', params);` |
| 267 | `const response = await api.post(`/deliverables/${id}/calificar`, { project_id: Number(projectId), calificacion: Number(grade) });` |

## pages/teacher/my-projects.php

[Fuente](../../pages/teacher/my-projects.php) · [Código completo](REFERENCIA.md#pages-teacher-my-projects-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 63 | función | `function escapeHtml(value)` |
| 67 | función | `function fullName(user)` |
| 71 | función | `function projectActiveAuthors(project)` |
| 76 | función | `async function loadProjects()` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 141 | `        document.addEventListener('DOMContentLoaded', loadProjects);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 78 | `const response = await api.get('/my-projects');` |

## pages/teacher/proposal-review.php

[Fuente](../../pages/teacher/proposal-review.php) · [Código completo](REFERENCIA.md#pages-teacher-proposal-review-php)

### Declaraciones

| Línea | Tipo | Nombre y declaración |
|---:|---|---|
| 53 | flecha | `const esc = value =&gt;` |
| 54 | flecha | `const badge = status =&gt;` |
| 60 | flecha | `const fullName = user =&gt;` |
| 61 | flecha | `const activeAuthors = project =&gt;` |
| 65 | función | `function renderProjects()` |
| 100 | función | `function renderWindowGroups()` |
| 132 | función | `async function loadPage()` |
| 141 | función | `async function addWindow(groupId)` |
| 159 | función | `async function deleteWindow(id)` |
| 167 | función | `async function review(id, status)` |

### Eventos y manejadores conectados

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 27 | `                &lt;button class="btn btn-primary" onclick="loadPage()"&gt;&lt;i class="bi bi-arrow-clockwise"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 91 | `                    &lt;button class="btn btn-sm btn-success" onclick="review(${project.id},'aprobado')"&gt;Aprobar&lt;/button&gt;` |
| 92 | `                    &lt;button class="btn btn-sm btn-warning" onclick="review(${project.id},'requiere_cambios')"&gt;Solicitar cambios&lt;/button&gt;` |
| 93 | `                    &lt;button class="btn btn-sm btn-danger" onclick="review(${project.id},'rechazado')"&gt;Rechazar&lt;/button&gt;` |
| 113 | `                &lt;button class="btn btn-sm btn-outline-danger float-end" onclick="deleteWindow(${item.id})" title="Eliminar periodo"&gt;&lt;i class="bi bi-trash"&gt;&lt;/i&gt;&lt;/button&gt;` |
| 125 | `                        &lt;div class="col-md-2 d-grid"&gt;&lt;button class="btn btn-sm btn-primary" onclick="addWindow(${group.id})"&gt;&lt;i class="bi bi-plus"&gt;&lt;/i&gt;&lt;/button&gt;&lt;/div&gt;` |
| 204 | `document.addEventListener('DOMContentLoaded', loadPage);` |

### Solicitudes y adaptadores de integración

Los fragmentos identifican el sitio de llamada; no constituyen un registro autoritativo de rutas de la API.

| Línea | Fragmento de implementación |
|---:|---|
| 134 | `api.get('/proposal/teacher-projects'),` |
| 135 | `api.get('/proposal/window-groups')` |
| 148 | `await api.post('/proposal/windows', {` |
| 154 | `windowData = await api.get('/proposal/window-groups', { _fresh: 1 });` |
| 161 | `await api.delete(`/proposal/windows/${id}`);` |
| 162 | `windowData = await api.get('/proposal/window-groups', { _fresh: 1 });` |
| 190 | `const response = await api.post(`/proposal/projects/${id}/review`, {` |
