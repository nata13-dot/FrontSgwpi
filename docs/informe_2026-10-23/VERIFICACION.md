# Verificación y límites de evidencia

Fecha: 23 de octubre de 2026. Se verificó el corte local sin cambiar lógica de aplicación ni llamar a operaciones institucionales.

| Comprobación | Cantidad | Resultado | Qué demuestra |
|---|---:|---|---|
| `php -l` | 56 archivos PHP | Aprobada | Sintaxis PHP de páginas, includes, diagnóstico y prueba. |
| `node --input-type=module --check` | 14 módulos JS externos | Aprobada | Sintaxis de los archivos JavaScript externos; no ejecuta el DOM. |
| `php tests/academic-page-smoke.php <perfil>` | 7 perfiles, 1–7 | Aprobada | La plantilla emite el contenedor y montaje de la consola con sesión sintética. |

**Entorno de verificación:** PHP 8.5.11 (cli) (built: Sep 22 2026 13:32:06) (NTS gcc x86_64); v22.23.1. Composer declara PHP 8.3.*: la versión de esta comprobación no prueba por sí sola compatibilidad con todas las versiones de despliegue.

La prueba de humo sólo comprueba dos marcadores HTML; no ejecuta el JavaScript de pestañas ni demuestra permisos completos para cada rol. El análisis de sintaxis de JS no incluye la ejecución de scripts inline interpolados por PHP. No se añadió una nueva suite funcional.

No se probó: navegador autenticado, llamadas reales a la API, correos, cargas/descargas privadas, importaciones institucionales, promociones, cierres, diagnósticos remotos, creación/limpieza de respaldos, Apache real, responsive visual o accesibilidad completa. Los hallazgos del manual son estáticos o se identifican expresamente como contraste con el informe previo de API.

## Resultados individuales

| Archivo / ejecución | Estado | Salida |
|---|---|---|
| `Frontend_Swgpi/api/set-session.php` | OK | No syntax errors detected in Frontend_Swgpi/api/set-session.php |
| `Frontend_Swgpi/includes/alert.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/alert.php |
| `Frontend_Swgpi/includes/config.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/config.php |
| `Frontend_Swgpi/includes/footer.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/footer.php |
| `Frontend_Swgpi/includes/head-responsive.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/head-responsive.php |
| `Frontend_Swgpi/includes/modal.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/modal.php |
| `Frontend_Swgpi/includes/navbar.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/navbar.php |
| `Frontend_Swgpi/includes/scripts-responsive.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/scripts-responsive.php |
| `Frontend_Swgpi/includes/sidebar.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/sidebar.php |
| `Frontend_Swgpi/includes/validations.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/validations.php |
| `Frontend_Swgpi/includes/visual-preferences.php` | OK | No syntax errors detected in Frontend_Swgpi/includes/visual-preferences.php |
| `Frontend_Swgpi/index.php` | OK | No syntax errors detected in Frontend_Swgpi/index.php |
| `Frontend_Swgpi/pages/academic-workflows.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/academic-workflows.php |
| `Frontend_Swgpi/pages/admin/advisors.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/advisors.php |
| `Frontend_Swgpi/pages/admin/asignaturas.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/asignaturas.php |
| `Frontend_Swgpi/pages/admin/career-audit.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/career-audit.php |
| `Frontend_Swgpi/pages/admin/career-integrity.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/career-integrity.php |
| `Frontend_Swgpi/pages/admin/career-modules.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/career-modules.php |
| `Frontend_Swgpi/pages/admin/career-setup.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/career-setup.php |
| `Frontend_Swgpi/pages/admin/careers.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/careers.php |
| `Frontend_Swgpi/pages/admin/competencias.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/competencias.php |
| `Frontend_Swgpi/pages/admin/dashboard.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/dashboard.php |
| `Frontend_Swgpi/pages/admin/database-backups.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/database-backups.php |
| `Frontend_Swgpi/pages/admin/deliverables.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/deliverables.php |
| `Frontend_Swgpi/pages/admin/document-tags.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/document-tags.php |
| `Frontend_Swgpi/pages/admin/evaluation-rooms.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/evaluation-rooms.php |
| `Frontend_Swgpi/pages/admin/evaluations-archived.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/evaluations-archived.php |
| `Frontend_Swgpi/pages/admin/evaluations.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/evaluations.php |
| `Frontend_Swgpi/pages/admin/notices.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/notices.php |
| `Frontend_Swgpi/pages/admin/operations.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/operations.php |
| `Frontend_Swgpi/pages/admin/project-create.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/project-create.php |
| `Frontend_Swgpi/pages/admin/project-edit.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/project-edit.php |
| `Frontend_Swgpi/pages/admin/projects.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/projects.php |
| `Frontend_Swgpi/pages/admin/proposal-config.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/proposal-config.php |
| `Frontend_Swgpi/pages/admin/semesters.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/semesters.php |
| `Frontend_Swgpi/pages/admin/settings.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/settings.php |
| `Frontend_Swgpi/pages/admin/user-create.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/user-create.php |
| `Frontend_Swgpi/pages/admin/user-edit.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/user-edit.php |
| `Frontend_Swgpi/pages/admin/users.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/admin/users.php |
| `Frontend_Swgpi/pages/evaluation-documents.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/evaluation-documents.php |
| `Frontend_Swgpi/pages/forgot-password.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/forgot-password.php |
| `Frontend_Swgpi/pages/login.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/login.php |
| `Frontend_Swgpi/pages/logout.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/logout.php |
| `Frontend_Swgpi/pages/profile.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/profile.php |
| `Frontend_Swgpi/pages/repositorio-detail.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/repositorio-detail.php |
| `Frontend_Swgpi/pages/repositorio.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/repositorio.php |
| `Frontend_Swgpi/pages/reset-password.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/reset-password.php |
| `Frontend_Swgpi/pages/student/dashboard.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/student/dashboard.php |
| `Frontend_Swgpi/pages/student/my-deliverables.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/student/my-deliverables.php |
| `Frontend_Swgpi/pages/student/proposal-register.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/student/proposal-register.php |
| `Frontend_Swgpi/pages/teacher/dashboard.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/teacher/dashboard.php |
| `Frontend_Swgpi/pages/teacher/my-deliverables.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/teacher/my-deliverables.php |
| `Frontend_Swgpi/pages/teacher/my-projects.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/teacher/my-projects.php |
| `Frontend_Swgpi/pages/teacher/proposal-review.php` | OK | No syntax errors detected in Frontend_Swgpi/pages/teacher/proposal-review.php |
| `Frontend_Swgpi/test-api.php` | OK | No syntax errors detected in Frontend_Swgpi/test-api.php |
| `Frontend_Swgpi/tests/academic-page-smoke.php` | OK | No syntax errors detected in Frontend_Swgpi/tests/academic-page-smoke.php |
| `Frontend_Swgpi/assets/js/academic-console.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/activity-notifications.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/api.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/app.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/auth.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/career-modules.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/career-switcher.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/global-search.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/operational-notifications.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/responsive.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/router.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/skeleton-loader.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/table.js` | OK | Sintaxis válida |
| `Frontend_Swgpi/assets/js/view-transitions.js` | OK | Sintaxis válida |
| `academic-page-smoke perfil 1` | OK | PHP academic page profile 1: OK |
| `academic-page-smoke perfil 2` | OK | PHP academic page profile 2: OK |
| `academic-page-smoke perfil 3` | OK | PHP academic page profile 3: OK |
| `academic-page-smoke perfil 4` | OK | PHP academic page profile 4: OK |
| `academic-page-smoke perfil 5` | OK | PHP academic page profile 5: OK |
| `academic-page-smoke perfil 6` | OK | PHP academic page profile 6: OK |
| `academic-page-smoke perfil 7` | OK | PHP academic page profile 7: OK |
