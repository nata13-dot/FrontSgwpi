# Entradas PHP y navegación

El frontend es multipágina. Este catálogo usa archivos reales, no rutas hipotéticas del router JavaScript. Las guardias reproducidas se evalúan en PHP; la API conserva autorización sobre cada recurso. Las entradas de diagnóstico y sesión se distinguen de pantallas.

| Ruta | Clase de entrada | Guardia / comportamiento inicial | Parámetros GET leídos |
|---|---|---|---|
| [`/index.php`](../../index.php) | pantalla | Sin guardia de sesión explícita en la cabecera PHP. | — |
| [`/pages/academic-workflows.php`](../../pages/academic-workflows.php) | pantalla | if (!is_authenticated()) { header('Location: /pages/login.php'); exit; } | tab |
| [`/pages/admin/advisors.php`](../../pages/admin/advisors.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_manage_projects()) { header('Location: /index.php'); | project, view |
| [`/pages/admin/asignaturas.php`](../../pages/admin/asignaturas.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_manage_academics()) { header('Location: /index.php'); | — |
| [`/pages/admin/career-audit.php`](../../pages/admin/career-audit.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_general_admin()) { header('Location: ' . dashboard_url()); | /admin/careers |
| [`/pages/admin/career-integrity.php`](../../pages/admin/career-integrity.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_general_admin()) { header('Location: ' . dashboard_url()); | — |
| [`/pages/admin/career-modules.php`](../../pages/admin/career-modules.php) | pantalla | requireAdmin(); | valor_actual, valor_meta |
| [`/pages/admin/career-setup.php`](../../pages/admin/career-setup.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_admin()) { header('Location: ' . dashboard_url()); | — |
| [`/pages/admin/careers.php`](../../pages/admin/careers.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_general_admin()) { header('Location: ' . dashboard_url()); | — |
| [`/pages/admin/competencias.php`](../../pages/admin/competencias.php) | redirección | if (!is_authenticated() &#124;&#124; !can_manage_academics()) { header('Location: /index.php'); header('Location: /pages/admin/asignaturas.php#competencias'); | — |
| [`/pages/admin/dashboard.php`](../../pages/admin/dashboard.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_management_staff()) { header('Location: /index.php'); | /career/modules, /dashboard/stats |
| [`/pages/admin/database-backups.php`](../../pages/admin/database-backups.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_general_admin()) { header('Location: ' . dashboard_url()); | — |
| [`/pages/admin/deliverables.php`](../../pages/admin/deliverables.php) | pantalla | if (!is_authenticated()) { header('Location: /index.php'); | competencia_id |
| [`/pages/admin/document-tags.php`](../../pages/admin/document-tags.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_admin()) { header('Location: /index.php'); | — |
| [`/pages/admin/evaluation-rooms.php`](../../pages/admin/evaluation-rooms.php) | pantalla | if (!is_authenticated() &#124;&#124; (!can_manage_projects() && !is_teacher())) { header('Location: /index.php'); | /academic-operations/catalog |
| [`/pages/admin/evaluations-archived.php`](../../pages/admin/evaluations-archived.php) | alias por inclusión | require __DIR__ . '/evaluations.php'; | — |
| [`/pages/admin/evaluations.php`](../../pages/admin/evaluations.php) | pantalla | if (!is_authenticated() &#124;&#124; (!can_manage_projects() && !is_teacher())) { header('Location: /index.php'); | — |
| [`/pages/admin/notices.php`](../../pages/admin/notices.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_admin()) { header('Location: /index.php'); | /notices |
| [`/pages/admin/operations.php`](../../pages/admin/operations.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_general_admin()) { header('Location: ' . dashboard_url()); | — |
| [`/pages/admin/project-create.php`](../../pages/admin/project-create.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_manage_projects()) { header('Location: /index.php'); | — |
| [`/pages/admin/project-edit.php`](../../pages/admin/project-edit.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_manage_projects()) { header('Location: /index.php'); if (!$projectId) { header('Location: /pages/admin/projects.php'); | id |
| [`/pages/admin/projects.php`](../../pages/admin/projects.php) | pantalla | if (!is_authenticated()) { header('Location: /index.php'); | edit, id, q |
| [`/pages/admin/proposal-config.php`](../../pages/admin/proposal-config.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_manage_projects()) { header('Location: /index.php'); exit; } | /proposal/config |
| [`/pages/admin/semesters.php`](../../pages/admin/semesters.php) | alias por inclusión | require dirname(__DIR__) . '/academic-workflows.php'; | — |
| [`/pages/admin/settings.php`](../../pages/admin/settings.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_admin()) { header('Location: /index.php'); | /settings |
| [`/pages/admin/user-create.php`](../../pages/admin/user-create.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_govern_users()) { header('Location: /index.php'); | — |
| [`/pages/admin/user-edit.php`](../../pages/admin/user-edit.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_govern_users()) { header('Location: /index.php'); if (!$userId) { header('Location: /pages/admin/users.php'); | id |
| [`/pages/admin/users.php`](../../pages/admin/users.php) | pantalla | if (!is_authenticated() &#124;&#124; !can_govern_users()) { header('Location: /index.php'); | q |
| [`/pages/evaluation-documents.php`](../../pages/evaluation-documents.php) | pantalla | if (!is_authenticated()) { header('Location: /index.php'); | — |
| [`/pages/forgot-password.php`](../../pages/forgot-password.php) | redirección | header('Location: /index.php#recover'); | — |
| [`/pages/login.php`](../../pages/login.php) | redirección | if (is_authenticated()) { header('Location: ' . dashboard_url()); header('Location: /index.php#login'); | — |
| [`/pages/logout.php`](../../pages/logout.php) | salida y limpieza de sesión | if (ini_get('session.use_cookies')) { header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); header('Pragma: no-cache'); header('Expires: 0'); | — |
| [`/pages/profile.php`](../../pages/profile.php) | pantalla | if (!is_authenticated()) { header('Location: /index.php'); exit; } | /profile |
| [`/pages/repositorio-detail.php`](../../pages/repositorio-detail.php) | pantalla | Sin guardia de sesión explícita en la cabecera PHP. | id |
| [`/pages/repositorio.php`](../../pages/repositorio.php) | pantalla | Sin guardia de sesión explícita en la cabecera PHP. | q |
| [`/pages/reset-password.php`](../../pages/reset-password.php) | redirección | header('Location: /index.php#recover'); | — |
| [`/pages/student/dashboard.php`](../../pages/student/dashboard.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_student()) { header('Location: /index.php'); | /dashboard/student, /proposal/student-status, /student/evaluation-schedule |
| [`/pages/student/my-deliverables.php`](../../pages/student/my-deliverables.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_student()) { header('Location: /index.php'); | /my-deliverables |
| [`/pages/student/proposal-register.php`](../../pages/student/proposal-register.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_student()) { header('Location: /index.php'); exit; } | /proposal/student-status |
| [`/pages/teacher/dashboard.php`](../../pages/teacher/dashboard.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_teacher()) { header('Location: /index.php'); | /dashboard/teacher |
| [`/pages/teacher/my-deliverables.php`](../../pages/teacher/my-deliverables.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_teacher()) { header('Location: /index.php'); | — |
| [`/pages/teacher/my-projects.php`](../../pages/teacher/my-projects.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_teacher()) { header('Location: /index.php'); | /my-projects |
| [`/pages/teacher/proposal-review.php`](../../pages/teacher/proposal-review.php) | pantalla | if (!is_authenticated() &#124;&#124; !is_teacher()) { header('Location: /index.php'); exit; } | /proposal/teacher-projects, /proposal/window-groups |
| [`/api/set-session.php`](../../api/set-session.php) | POST JSON local, no pantalla | JSON con auth_token y user; persiste sesión. Devuelve 400 ante formato inválido. | — |
| [`/test-api.php`](../../test-api.php) | diagnóstico, fuera del flujo de negocio | Diagnóstico técnico; revisar directamente la fuente antes de exponerlo. | — |

## Fragmentos, filtros y destinos compartidos

- `/index.php#login`: abre acceso.
- `/index.php#recover`: inicia recuperación por modales.
- `/pages/admin/asignaturas.php#competencias`: acceso al gestor de competencias.
- `/pages/academic-workflows.php?tab=periods|courses|authorizations|memberships|residencies|continuity`: pestaña inicial; sólo se conserva si el rol la permite. Los valores se usan individualmente, no unidos por `|`.
- `q` en repositorio, proyectos y usuarios: preselección de búsqueda cuando la pantalla la implementa.
- `id` en páginas de edición/detalle: identifica el recurso; nunca sustituye la autorización de API.
- `reason` en salida: comunica causa de cierre en los flujos que lo envían.

La columna de parámetros se extrae estáticamente; puede incluir lecturas `.get()` no relacionadas con la URL. Los filtros internos de una tabla no necesariamente se reflejan en la barra de dirección. Los enlaces exactos, incluidos los generados por JavaScript, figuran en INTERFACES.md y REFERENCIA.md.

## Reescritura y rutas absolutas

`.htaccess` redirige recursos inexistentes a `index.php?path=...`; no constituye por sí mismo un catálogo de páginas amigables. La navegación usa principalmente `/pages/...` y `/assets/...`, por lo que alojar bajo un subdirectorio requiere verificar las rutas. El bloqueo de PHP declarado en ese mismo archivo se comenta en el manual.
