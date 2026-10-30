# Catálogo de interfaces y controles

Catálogo estático de etiquetas HTML y llamadas del constructor de la consola académica. Incluye elementos en plantillas JavaScript y ramas PHP; su presencia aquí no garantiza visibilidad simultánea ni permiso. Se conserva la expresión dinámica cuando no existe un texto fijo. Los atributos y el código exacto están en [REFERENCIA.md](REFERENCIA.md). Las etiquetas que contienen PHP pueden quedar fragmentadas por el extractor; consultar la fuente enlazada en esos casos.

## assets/js/academic-console.js

[Fuente](../../assets/js/academic-console.js) · [Código completo](REFERENCIA.md#assets-js-academic-console-js)

### Controles generados por la consola académica

Incluye pestañas, paneles, formularios y acciones dinámicas. Sus condiciones de rol se explican en el capítulo 10 del manual.

| Línea | Construcción del control |
|---:|---|
| 56 | `for (const [id, label] of tabs) button(label, async () =&gt; { tab = id; await render() }, nav)` |
| 62 | `const section = panel('Periodos por carrera', content)` |
| 67 | `button('Conciliar', () =&gt; reconciliation(p), row)` |
| 68 | `for (const [state, label] of [['abierto', 'Abrir'], ['en_cierre', 'Iniciar cierre'], ['cerrado', 'Cerrar']]) button(label, async () =&gt; { await request('POST', `/semester-management/periods/${p.id}/transition`, { state }); await render() }, row)` |
| 70 | `button('Preparar promoción', () =&gt; promotion(p), row); section.append(row)` |
| 73 | `const p = panel('Calendario institucional', content)` |
| 74 | `const [f, done] = form(p, 'Crear periodo', d =&gt; request('POST', '/semester-management/periods', { ...d, automatic_promotion: false }))` |
| 75 | `input(f, 'Nombre', 'name'); input(f, 'Inicio', 'starts_at', 'date'); input(f, 'Fin', 'ends_at', 'date'); done()` |
| 77 | `const p = panel('Asignar matrícula del periodo abierto', content)` |
| 78 | `const [f, done] = form(p, 'Guardar matrícula', d =&gt; request('POST', '/academic-operations/enrollments', { student_id: d.student_id, group_id: Number(d.group_id) }))` |
| 79 | `select(f, 'Alumno', 'student_id', members([3])); select(f, 'Grupo', 'group_id', groups()); done()` |
| 84 | `content.replaceChildren(); const p = panel(`Promoción a ${period.nombre}`, content)` |
| 86 | `const [f, done] = form(p, manager ? 'Confirmar lote' : 'Sólo preparación: requiere jefatura o administrador', async () =&gt; {` |
| 92 | `const box = panel(`${m.student_id} · ${m.from}° → ${m.to}°`, f)` |
| 94 | `return { student: m.student_id, action: select(box, 'Movimiento', `action${i}`, [['avance', 'Avance'], ['permanencia', 'Permanencia'], ['salida', 'Salida del seguimiento']], m.action), group: select(box, 'Grupo destino', `group${i}`, groups(preview.groups), m.group_id, false), reason: input(box, 'Motivo', `reason${i}`) }` |
| 98 | `button('Volver', render, p)` |
| 103 | `content.replaceChildren(); const p = panel(`Conciliación · ${period.nombre}`, content)` |
| 106 | `const [f, done] = form(p, 'Aplicar conciliación', async d =&gt; {` |
| 112 | `const box = panel(`${s.name} · ${s.student_id}`, f)` |
| 114 | `return { student: s.student_id, group: select(box, 'Grupo confirmado', `group${i}`, groups(report.groups), s.group_id, false), reason: input(box, 'Motivo / fuente verificada', `reason${i}`) }` |
| 117 | `const box = panel(`Sala histórica #${r.id} · ${r.nombre &#124;&#124; r.salon}`, f)` |
| 119 | `return { id: r.id, period: select(box, 'Periodo confirmado', `roomPeriod${i}`, catalog.periods.map(p =&gt; [p.id, p.nombre]), r.periodo_id),` |
| 120 | `classroom: select(box, 'Salón institucional', `roomClassroom${i}`, catalog.rooms.map(c =&gt; [c.id, c.nombre]), r.salon_id), reason: input(box, 'Fuente de verificación', `roomReason${i}`) }` |
| 122 | `input(f, 'He revisado las excepciones históricas y entiendo que requieren nueva autorización', 'ack', 'checkbox', '', report.legacy_exceptions.length &gt; 0)` |
| 123 | `done(); button('Volver', render, p)` |
| 130 | `const p = panel(`${c.subject?.nombre &#124;&#124; 'Materia'} · ${c.group?.semestre}° ${c.group?.grupo &#124;&#124; ''}`, content)` |
| 134 | `const [f, done] = form(p, 'Inscribirme', d =&gt; request('POST', `/course-enrollments/${c.id}`, d)); input(f, 'Clave del docente', 'password', 'password'); done()` |
| 137 | `const [f, done] = form(p, 'Solicitar baja directa', d =&gt; request('DELETE', `/course-enrollments/${c.id}`, d)); input(f, 'Motivo de baja', 'reason'); done()` |
| 140 | `const [f, done] = form(p, 'Abrir inscripción / renovar clave', d =&gt; request('PUT', `/courses/${c.id}/self-enrollment`, { enabled: true, password: d.password, password_confirmation: d.password, starts_at: d.start, ends_at: d.end }))` |
| 141 | `input(f, 'Nueva clave (mínimo 6 caracteres)', 'password', 'password').minLength = 6` |
| 142 | `input(f, 'Apertura', 'start', 'datetime-local'); input(f, 'Cierre', 'end', 'datetime-local'); done()` |
| 143 | `button('Cerrar autorregistro', async () =&gt; { await request('PUT', `/courses/${c.id}/self-enrollment`, { enabled: false }); await render() }, p)` |
| 144 | `button('Ver inscritos', async () =&gt; {` |
| 146 | `const box = panel('Matrícula del curso', p)` |
| 148 | `if (academic) { const [f, done] = form(box, 'Registrar baja administrativa', d =&gt; request('DELETE', `/course-enrollments/${c.id}`, d)); select(f, 'Alumno', 'student_id', data.data.map(s =&gt; [s.id, `${s.id} ${s.nombres}`])); input(f, 'Motivo', 'reason'); done() }` |
| 156 | `const p = panel('Solicitar autorización', content)` |
| 157 | `const [f, done] = form(p, 'Registrar solicitud', d =&gt; request('POST', '/academic-operations/authorizations', {` |
| 163 | `select(f, 'Periodo', 'period_id', periods()); select(f, 'Tipo', 'type', [['presentacion_semestre', 'Presentación en otra etapa'], ['reprogramacion', 'Reprogramación'], ['reintento', 'Nuevo intento'], ...(manager ? [['inscripcion_semestre', 'Inscripción en otro semestre']] : [])])` |
| 164 | `select(f, 'Proyecto (obligatorio para evaluaciones)', 'project_id', projects(), '', false)` |
| 165 | `select(f, 'Alumno involucrado (no autoriza automáticamente al equipo)', 'student_id', members([3]), '', false)` |
| 166 | `select(f, 'Etapa de presentación', 'presentation_semester', [5, 6, 7, 8].map(n =&gt; [n, `${n}°`]), '', false)` |
| 167 | `select(f, 'Evaluación de origen', 'evaluation_id', (catalog.evaluations &#124;&#124; []).map(e =&gt; [e.id, `${e.project?.title &#124;&#124; e.project?.titulo &#124;&#124; e.project_id} · ${e.semestre}° · ${e.estado} (#${e.id})`]), '', false)` |
| 168 | `select(f, 'Materia (para inscripción excepcional)', 'course_id', (catalog.courses &#124;&#124; []).map(c =&gt; [c.id, `${c.subject?.nombre} · ${c.group?.semestre}° ${c.group?.grupo &#124;&#124; ''}`]), '', false)` |
| 169 | `input(f, 'Motivo', 'reason'); input(f, 'Vigencia hasta', 'expires_at', 'datetime-local'); done()` |
| 171 | `const box = panel(`${a.tipo} · ${a.estado}`, content); box.append(el('p', `Proyecto ${a.proyecto_id &#124;&#124; '—'} · ${a.motivo} · vigencia ${date(a.vigente_hasta)}`))` |
| 173 | `const [f, done] = form(box, 'Registrar resolución', d =&gt; request('PUT', `/semester-management/exceptions/${a.id}/decision`, { version: a.version, status: d.status, reason: d.reason, project_scope: d.scope === 'on' }))` |
| 174 | `select(f, 'Decisión', 'status', a.estado === 'solicitada' ? [['aprobada', 'Aprobar'], ['rechazada', 'Rechazar']] : [['revocada', 'Revocar']]); input(f, 'Motivo de resolución', 'reason'); input(f, 'Confirmo el alcance sobre todo el proyecto al aprobar', 'scope', 'checkbox', '', false); done()` |
| 180 | `const p = panel('Perfiles operativos de miembros actuales', content)` |
| 182 | `const [f, done] = form(p, 'Asignar perfil', d =&gt; request('PUT', `/academic-operations/memberships/${d.membership}`, { profile_id: Number(d.profile_id) }))` |
| 183 | `const field = select(f, 'Persona', 'membership', catalog.members.filter(m =&gt; [2, 3, 6, 7].includes(Number(m.perfil_id))).map(m =&gt; [m.id, `${m.nombres} ${m.apellido_paterno} · perfil ${m.perfil_id}`]))` |
| 184 | `select(f, 'Perfil', 'profile_id', [[2, 'Docente'], [3, 'Estudiante'], [6, 'Asistencia'], [7, 'Coordinación']]); done()` |
| 185 | `button('Consultar compromisos', async () =&gt; { if (!field.value) return; const commitments = await request('GET', `/academic-operations/memberships/${field.value}/commitments`); const box = panel('Compromisos pendientes', p); for (const [key, ids] of Object.entries(commitments)) box.append(el('p', `${({ courses: 'Cursos', rooms: 'Salas', projects: 'Asesorías', residencies: 'Residencias' })[key]}: ${ids.join(', ') &#124;&#124; 'ninguno'}`)) }, p)` |
| 186 | `if (general) { const box = panel('Catálogo institucional de salones', content); const [f, done] = form(box, 'Agregar salón', d =&gt; request('POST', '/academic-operations/classrooms', d)); input(f, 'Nombre del salón', 'name'); done() }` |
| 190 | `const p = panel('Seguimiento del proyecto en el periodo abierto', content)` |
| 191 | `const [f, done] = form(p, 'Registrar continuidad', d =&gt; request('POST', '/academic-operations/project-period', { project_id: Number(d.project_id), group_id: Number(d.group_id) }))` |
| 192 | `select(f, 'Proyecto', 'project_id', projects()); select(f, 'Grupo del periodo', 'group_id', groups()); done()` |
| 199 | `const p = panel('Nuevo expediente individual de residencia', content)` |
| 200 | `const [f, done] = form(p, 'Crear expediente', d =&gt; {` |
| 204 | `if (!student) select(f, 'Residente', 'student_id', members([3]))` |
| 205 | `select(f, 'Periodo', 'period_id', periods()); select(f, 'Proyecto asociado', 'project_id', projects())` |
| 206 | `select(f, 'Origen', 'origin', [['empresa', 'Proyecto nuevo de empresa'], ['continuacion', 'Continuación del integrador']])` |
| 207 | `select(f, 'Asesor asignado al proyecto', 'advisor_id', student ? (catalog.advisors &#124;&#124; []).map(a =&gt; [a.id, `${a.nombres} ${a.apellido_paterno}`]) : members([1, 2, 5, 6, 7]))` |
| 208 | `input(f, 'Alcance y cambios propuestos (mínimo 20 caracteres)', 'changes').minLength = 20; done()` |
| 211 | `const box = panel(`Residencia · ${r.estudiante_id} · ${r.estado}`, content)` |
| 214 | `const sub = panel(`${item.nombre}${item.obligatorio ? ' · obligatorio' : ''}`, box)` |
| 217 | `button('Descargar', () =&gt; request('DOWNLOAD', `/residencies/${r.id}/versions/${v.id}/download`, { name: v.nombre }), line)` |
| 219 | `const [f, done] = form(line, 'Guardar revisión', d =&gt; request('PUT', `/residencies/${r.id}/versions/${v.id}/review`, d)); select(f, 'Resultado', 'status', [['correcciones', 'Solicitar correcciones'], ['validada', 'Validar']]); input(f, 'Observaciones', 'observations'); done()` |
| 224 | `const [f, done] = form(sub, 'Subir nueva versión', async () =&gt; { const data = new FormData(); data.append('file', file.files[0]); await request('POST', `/residencies/${r.id}/requirements/${item.id}/versions`, data) }); const file = input(f, 'Documento PDF o Word', 'file', 'file'); file.accept = '.pdf,.doc,.docx'; done()` |
| 228 | `const [f, done] = form(box, 'Agregar requisito documental', d =&gt; request('POST', `/residencies/${r.id}/requirements`, { name: d.name, required: d.required === 'on', deadline: d.deadline &#124;&#124; null })); input(f, 'Nombre del requisito', 'name'); input(f, 'Obligatorio', 'required', 'checkbox', '', false).checked = true; input(f, 'Fecha límite opcional', 'deadline', 'datetime-local', '', false); done()` |
| 231 | `const [af, adone] = form(box, 'Reasignar asesor', d =&gt; request('PUT', `/residencies/${r.id}/advisor`, { advisor_id: d.advisor_id, version: r.version, reason: d.reason }))` |
| 232 | `select(af, 'Nuevo asesor asignado al proyecto', 'advisor_id', members([1, 2, 5, 6, 7]), r.asesor_id); input(af, 'Motivo de reasignación', 'reason'); adone()` |
| 233 | `const [f, done] = form(box, 'Cerrar seguimiento', d =&gt; request('POST', `/residencies/${r.id}/close`, { version: r.version, reason: d.reason })); input(f, 'Motivo del cierre', 'reason'); done()` |
| 246 | `} catch (e) { if (!disposed) { content.replaceChildren(el('p', error(e))); button('Reintentar', render, content) } }` |

## assets/js/activity-notifications.js

[Fuente](../../assets/js/activity-notifications.js) · [Código completo](REFERENCIA.md#assets-js-activity-notifications-js)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 43 | button | ${escapeHtml(notification.titulo)} ${escapeHtml(notification.mensaje)} ${escapeHtml(formatDate(notification.creada_en))} | {"type": "button", "data-notification-id": "${notification.id}", "data-notification-url": "${escapeHtml(notification.url)}"} |

## assets/js/app.js

[Fuente](../../assets/js/app.js) · [Código completo](REFERENCIA.md#assets-js-app-js)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 14 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |

## assets/js/global-search.js

[Fuente](../../assets/js/global-search.js) · [Código completo](REFERENCIA.md#assets-js-global-search-js)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 162 | a | ${escapeHtml(entry.title)} ${escapeHtml(entry.description)} ${entry.type === 'search' ? 'Filtrar' : 'Abrir'} | {"href": "${escapeHtml(entry.url)}", "role": "option", "aria-selected": "${index === activeIndex}", "data-search-index": "${index}"} |

## assets/js/operational-notifications.js

[Fuente](../../assets/js/operational-notifications.js) · [Código completo](REFERENCIA.md#assets-js-operational-notifications-js)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 29 | a | ${escapeHtml(alert.titulo)} ${escapeHtml(alert.detalle)} ${alert.estado === 'atendida' ? 'En atención' : 'Requiere atención'} | {"href": "/pages/admin/operations.php"} |

## assets/js/responsive.js

[Fuente](../../assets/js/responsive.js) · [Código completo](REFERENCIA.md#assets-js-responsive-js)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 113 | button | Ver detalles | {"type": "button", "aria-expanded": "false"} |

## assets/js/table.js

[Fuente](../../assets/js/table.js) · [Código completo](REFERENCIA.md#assets-js-table-js)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 56 | button | Editar | {} |

## includes/alert.php

[Fuente](../../includes/alert.php) · [Código completo](REFERENCIA.md#includes-alert-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 17 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |

## includes/modal.php

[Fuente](../../includes/modal.php) · [Código completo](REFERENCIA.md#includes-modal-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 6 | h5 | Modal | {} |
| 7 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 13 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal"} |
| 14 | button | Confirmar | {"type": "button", "onclick": "confirmModal()"} |

## includes/navbar.php

[Fuente](../../includes/navbar.php) · [Código completo](REFERENCIA.md#includes-navbar-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 15 | a | " class="navbar-brand"&gt; Gestión de Proyectos Integradores ITSSMT | {} |
| 24 | button | Alternar menú lateral | {"type": "button", "aria-label": "Alternar menú lateral"} |
| 27 | a | " class="navbar-mobile-emblem" aria-label="Ir al inicio"&gt; | {} |
| 31 | form | — | {"role": "search", "action": "/pages/repositorio.php", "method": "get", "autocomplete": "off"} |
| 33 | input | Buscar páginas, opciones o registros en el sistema | {"type": "search", "name": "q", "placeholder": "Buscar páginas, opciones o registros...", "aria-label": "Buscar páginas, opciones o registros en el sistema", "aria-controls": "globalSearchResults", "aria-expanded": "false"} |
| 47 | button | Abrir navegación | {"type": "button", "data-bs-toggle": "collapse", "data-bs-target": "#navbarNav", "aria-controls": "navbarNav", "aria-expanded": "false", "aria-label": "Abrir navegación"} |
| 54 | a | " class="nav-link "&gt; Inicio | {} |
| 59 | a | "&gt; Repositorio | {"href": "/pages/repositorio.php"} |
| 67 | button | — | {"type": "button", "data-bs-toggle": "dropdown", "aria-expanded": "false"} |
| 72 | h6 | Administrar como | {} |
| 75 | button | " data-career-id=" " &gt; "&gt; | {"type": "button"} |
| 93 | a | Gestionar carreras | {"href": "/pages/admin/careers.php"} |
| 100 | a | " href="#" id="managementMenu" role="button" data-bs-toggle="dropdown"&gt; Gestiones | {} |
| 106 | h6 | Personas | {} |
| 107 | a | " href="/pages/admin/users.php"&gt; Usuarios | {} |
| 108 | a | " href="/pages/admin/advisors.php"&gt; Asesores | {} |
| 111 | h6 | Proyectos | {} |
| 112 | a | " href="/pages/admin/projects.php"&gt; Proyectos/Tesis | {} |
| 113 | a | " href="/pages/admin/proposal-config.php"&gt; Propuestas | {} |
| 114 | a | " href="/pages/admin/deliverables.php"&gt; Entregables | {} |
| 115 | a | " href="/pages/admin/evaluations.php"&gt; Evaluaciones | {} |
| 116 | a | " href="/pages/evaluation-documents.php"&gt; Documentos de evaluación | {} |
| 119 | h6 | Académico | {} |
| 120 | a | " href="/pages/admin/asignaturas.php"&gt; Asignaturas, cargas y competencias | {} |
| 121 | a | " href="/pages/admin/semesters.php"&gt; Semestres y periodos | {} |
| 124 | h6 | Sistema | {} |
| 125 | a | " href="/pages/admin/document-tags.php"&gt; Etiquetas | {} |
| 126 | a | " href="/pages/admin/notices.php"&gt; Avisos | {} |
| 127 | a | " href="/pages/admin/settings.php"&gt; Ajustes | {} |
| 128 | a | " href="/pages/admin/career-modules.php"&gt; Módulos de carrera | {} |
| 129 | a | " href="/pages/repositorio.php"&gt; Repositorio | {} |
| 136 | a | " href="#" id="managementMenu" role="button" data-bs-toggle="dropdown"&gt; Gestiones | {} |
| 142 | h6 | Proyectos | {} |
| 143 | a | " href="/pages/teacher/my-projects.php"&gt; Mis proyectos | {} |
| 144 | a | " href="/pages/teacher/proposal-review.php"&gt; Revisar propuestas | {} |
| 145 | a | " href="/pages/admin/evaluations.php"&gt; Evaluaciones | {} |
| 146 | a | " href="/pages/evaluation-documents.php"&gt; Documentos de evaluación | {} |
| 149 | h6 | Entregables | {} |
| 150 | a | " href="/pages/teacher/my-deliverables.php"&gt; Entregables | {} |
| 153 | h6 | Sistema | {} |
| 154 | a | " href="/pages/repositorio.php"&gt; Repositorio | {} |
| 161 | a | " href="#" id="managementMenu" role="button" data-bs-toggle="dropdown"&gt; Gestiones | {} |
| 167 | h6 | Proyectos | {} |
| 168 | a | " href="/pages/student/proposal-register.php"&gt; Registrar proyecto | {} |
| 169 | a | " href="/pages/evaluation-documents.php"&gt; Documentos de evaluación | {} |
| 172 | h6 | Entregables | {} |
| 173 | a | " href="/pages/student/my-deliverables.php"&gt; Mis entregables | {} |
| 176 | h6 | Sistema | {} |
| 177 | a | " href="/pages/repositorio.php"&gt; Repositorio | {} |
| 186 | button | 0 | {"type": "button", "id": "operationalNotificationMenu", "data-bs-toggle": "dropdown", "data-bs-auto-close": "outside", "aria-expanded": "false", "aria-label": "Alertas operativas"} |
| 196 | a | Ver centro | {"href": "/pages/admin/operations.php"} |
| 208 | button | 0 | {"type": "button", "id": "notificationMenu", "data-bs-toggle": "dropdown", "data-bs-auto-close": "outside", "aria-expanded": "false", "aria-label": "Notificaciones"} |
| 219 | a | Gestionar | {"href": "/pages/admin/notices.php"} |
| 231 | button | 0 | {"type": "button", "id": "activityNotificationMenu", "data-bs-toggle": "dropdown", "data-bs-auto-close": "outside", "aria-expanded": "false", "aria-label": "Actividad reciente"} |
| 241 | button | Marcar leídas | {"type": "button", "id": "markAllActivityNotifications"} |
| 252 | a | " class="profile-thumb" alt="Perfil"&gt; | {"href": "#", "id": "userMenu", "role": "button", "data-bs-toggle": "dropdown"} |
| 260 | a | "&gt; Inicio | {} |
| 261 | a | Mi perfil | {"href": "/pages/profile.php"} |
| 262 | button | Modo oscuro | {"type": "button", "id": "themeToggle"} |
| 264 | a | Cerrar Sesión | {"href": "#", "onclick": "logout(); return false;"} |
| 271 | a | Iniciar Sesión | {"href": "/index.php#login"} |

## includes/sidebar.php

[Fuente](../../includes/sidebar.php) · [Código completo](REFERENCIA.md#includes-sidebar-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 7 | a | " class="sidebar-brand"&gt; Gestión de Proyectos | {} |
| 25 | a | Seguimiento académico y residencias | {"href": "/pages/academic-workflows.php"} |
| 27 | h6 | Administración | {} |
| 28 | a | "&gt; Inicio | {"href": "/pages/admin/dashboard.php"} |
| 32 | a | "&gt; Operaciones | {"href": "/pages/admin/operations.php"} |
| 33 | a | "&gt; Carreras y accesos | {"href": "/pages/admin/careers.php"} |
| 37 | a | "&gt; Auditoría | {"href": "/pages/admin/career-audit.php"} |
| 38 | a | "&gt; Integridad | {"href": "/pages/admin/career-integrity.php"} |
| 39 | a | "&gt; Respaldos | {"href": "/pages/admin/database-backups.php"} |
| 45 | a | "&gt; Usuarios | {"href": "/pages/admin/users.php"} |
| 46 | a | "&gt; Asesores | {"href": "/pages/admin/advisors.php"} |
| 49 | a | "&gt; Asesores | {"data-career-module": "proyectos", "href": "/pages/admin/advisors.php"} |
| 53 | a | "&gt; Gestionar proyectos | {"data-career-module": "proyectos", "href": "/pages/admin/projects.php"} |
| 54 | a | "&gt; Propuestas | {"data-career-module": "proyectos", "href": "/pages/admin/proposal-config.php"} |
| 55 | a | "&gt; Evaluaciones | {"data-career-module": "evaluaciones", "href": "/pages/admin/evaluations.php"} |
| 56 | a | "&gt; Salas y turnos | {"data-career-module": "evaluaciones", "href": "/pages/admin/evaluation-rooms.php"} |
| 57 | a | "&gt; Evaluaciones previas | {"data-career-module": "evaluaciones", "href": "/pages/admin/evaluations-archived.php"} |
| 58 | a | "&gt; Evidencias | {"data-career-module": "evaluaciones", "href": "/pages/evaluation-documents.php"} |
| 60 | a | "&gt; Entregables | {"data-career-module": "entregables", "href": "/pages/admin/deliverables.php"} |
| 66 | a | "&gt; Asignaturas | {"href": "/pages/admin/asignaturas.php"} |
| 67 | a | Competencias | {"href": "/pages/admin/asignaturas.php#competencias"} |
| 68 | a | "&gt; Semestres y periodos | {"href": "/pages/admin/semesters.php"} |
| 69 | a | "&gt; Entregables | {"data-career-module": "entregables", "href": "/pages/admin/deliverables.php"} |
| 75 | a | "&gt; Etiquetas | {"data-career-module": "repositorio", "href": "/pages/admin/document-tags.php"} |
| 76 | a | "&gt; Avisos | {"data-career-module": "configuracion", "href": "/pages/admin/notices.php"} |
| 77 | a | "&gt; Ajustes | {"data-career-module": "configuracion", "href": "/pages/admin/settings.php"} |
| 78 | a | "&gt; Módulos de carrera | {"href": "/pages/admin/career-modules.php"} |
| 79 | a | "&gt; Carga inicial | {"data-career-module": "academico", "href": "/pages/admin/career-setup.php"} |
| 81 | a | "&gt; Repositorio | {"data-career-module": "repositorio", "href": "/pages/repositorio.php"} |
| 83 | a | "&gt; Mi Perfil | {"href": "/pages/profile.php"} |
| 85 | h6 | Docente | {} |
| 86 | a | "&gt; Inicio | {"href": "/pages/teacher/dashboard.php"} |
| 89 | a | "&gt; Mis proyectos | {"data-career-module": "proyectos", "href": "/pages/teacher/my-projects.php"} |
| 90 | a | "&gt; Revisar propuestas | {"data-career-module": "proyectos", "href": "/pages/teacher/proposal-review.php"} |
| 91 | a | "&gt; Evaluaciones | {"data-career-module": "evaluaciones", "href": "/pages/admin/evaluations.php"} |
| 92 | a | "&gt; Salas y turnos | {"data-career-module": "evaluaciones", "href": "/pages/admin/evaluation-rooms.php"} |
| 93 | a | "&gt; Evaluaciones previas | {"data-career-module": "evaluaciones", "href": "/pages/admin/evaluations-archived.php"} |
| 94 | a | "&gt; Evidencias | {"data-career-module": "evaluaciones", "href": "/pages/evaluation-documents.php"} |
| 98 | a | "&gt; Entregables | {"data-career-module": "entregables", "href": "/pages/teacher/my-deliverables.php"} |
| 102 | a | "&gt; Repositorio | {"data-career-module": "repositorio", "href": "/pages/repositorio.php"} |
| 104 | a | "&gt; Mi Perfil | {"href": "/pages/profile.php"} |
| 106 | h6 | Estudiante | {} |
| 107 | a | "&gt; Inicio | {"href": "/pages/student/dashboard.php"} |
| 110 | a | "&gt; Registrar propuesta | {"data-career-module": "proyectos", "href": "/pages/student/proposal-register.php"} |
| 111 | a | "&gt; Evidencias | {"data-career-module": "evaluaciones", "href": "/pages/evaluation-documents.php"} |
| 115 | a | "&gt; Mis entregables | {"data-career-module": "entregables", "href": "/pages/student/my-deliverables.php"} |
| 119 | a | "&gt; Repositorio | {"data-career-module": "repositorio", "href": "/pages/repositorio.php"} |
| 121 | a | "&gt; Mi Perfil | {"href": "/pages/profile.php"} |
| 130 | button | Contraer menú lateral | {"type": "button", "aria-label": "Contraer menú lateral"} |

## index.php

[Fuente](../../index.php) · [Código completo](REFERENCIA.md#index-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 363 | h1 | Explora los trabajos y evidencias académicas desarrolladas en el instituto | {} |
| 366 | a | Explorar repositorio | {"href": "/pages/repositorio.php"} |
| 369 | button | Iniciar sesión | {"type": "button", "data-bs-toggle": "modal", "data-bs-target": "#loginModal"} |
| 381 | h2 | Repositorio Digital | {} |
| 387 | h5 | Documentos organizados | {} |
| 394 | h5 | Lectura rápida | {} |
| 401 | h5 | Referencia académica | {} |
| 406 | a | Ir al repositorio | {"href": "/pages/repositorio.php"} |
| 471 | h2 | Gestión académica conectada al repositorio | {} |
| 478 | h5 | Gestión de proyectos | {} |
| 488 | h5 | Evaluación documentada | {} |
| 498 | h5 | Colaboracion | {} |
| 511 | button | Entendido | {"type": "button", "id": "cookieNoticeAccept"} |
| 520 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal", "aria-label": "Cerrar"} |
| 523 | h4 | Bienvenido | {"id": "loginModalLabel"} |
| 529 | form | — | {"id": "loginForm"} |
| 531 | label | Número de control o empleado | {"for": "loginUserId"} |
| 532 | input | Ejemplo: 20260001 | {"type": "text", "id": "loginUserId", "name": "username", "placeholder": "Ejemplo: 20260001", "autocomplete": "username", "autocapitalize": "none", "spellcheck": "false", "required": true} |
| 537 | label | Contraseña | {"for": "loginPassword"} |
| 538 | input | Escribe tu contraseña | {"type": "password", "id": "loginPassword", "name": "password", "placeholder": "Escribe tu contraseña", "autocomplete": "current-password", "required": true} |
| 544 | input | 1 | {"type": "checkbox", "value": "1", "id": "rememberCheck", "checked": true} |
| 545 | label | Recuerdame | {"for": "rememberCheck"} |
| 547 | button | ¿Olvidaste tu contraseña? | {"type": "button", "onclick": "openPasswordRecoveryModal()"} |
| 551 | button | Iniciar sesión | {"type": "submit", "id": "loginSubmitBtn"} |
| 569 | h5 | Recuperar contraseña | {"id": "passwordRecoveryModalLabel"} |
| 570 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal", "aria-label": "Cerrar"} |
| 575 | form | — | {"id": "passwordRecoveryForm"} |
| 577 | label | Número de control o empleado | {"for": "recoveryUserId"} |
| 578 | input | Ejemplo: 20260001 | {"type": "text", "id": "recoveryUserId", "name": "username", "placeholder": "Ejemplo: 20260001", "autocomplete": "username", "autocapitalize": "none", "spellcheck": "false", "required": true} |
| 582 | label | Correo registrado | {"for": "recoveryEmail"} |
| 583 | input | correo@dominio.com | {"type": "email", "id": "recoveryEmail", "name": "email", "placeholder": "correo@dominio.com", "autocomplete": "email", "autocapitalize": "none", "spellcheck": "false", "required": true} |
| 587 | button | Enviar token | {"type": "submit", "id": "passwordRecoverySubmitBtn"} |
| 601 | h5 | Validar token | {"id": "passwordTokenModalLabel"} |
| 602 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal", "aria-label": "Cerrar"} |
| 607 | form | — | {"id": "passwordTokenForm"} |
| 609 | label | Token de verificación | {"for": "recoveryToken"} |
| 610 | input | 000000 | {"type": "text", "inputmode": "numeric", "maxlength": "6", "pattern": "[0-9]{6}", "id": "recoveryToken", "name": "one-time-code", "placeholder": "000000", "autocomplete": "one-time-code", "required": true} |
| 614 | button | Validar token | {"type": "submit", "id": "passwordTokenSubmitBtn"} |
| 628 | h5 | Nueva contraseña | {"id": "passwordResetModalLabel"} |
| 629 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal", "aria-label": "Cerrar"} |
| 634 | form | — | {"id": "passwordResetForm"} |
| 636 | label | Nueva contraseña | {"for": "newRecoveryPassword"} |
| 637 | input | Mínimo 6 caracteres | {"type": "password", "id": "newRecoveryPassword", "name": "new-password", "placeholder": "Mínimo 6 caracteres", "minlength": "6", "autocomplete": "new-password", "required": true} |
| 641 | label | Confirmar contraseña | {"for": "newRecoveryPasswordConfirmation"} |
| 642 | input | Repite la contraseña | {"type": "password", "id": "newRecoveryPasswordConfirmation", "name": "new-password-confirmation", "placeholder": "Repite la contraseña", "minlength": "6", "autocomplete": "new-password", "required": true} |
| 646 | button | Cambiar contraseña | {"type": "submit", "id": "passwordResetSubmitBtn"} |

## pages/academic-workflows.php

[Fuente](../../pages/academic-workflows.php) · [Código completo](REFERENCIA.md#pages-academic-workflows-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 12 | h1 | Seguimiento académico | {} |

## pages/admin/advisors.php

[Fuente](../../pages/admin/advisors.php) · [Código completo](REFERENCIA.md#pages-admin-advisors-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Asignación de Asesores | {} |
| 37 | label | Filtrar por semestre | {"for": "semesterFilter"} |
| 38 | select | — | {"id": "semesterFilter", "onchange": "loadProjects()"} |
| 50 | button | Por proyecto | {"type": "button", "id": "viewByProjectBtn", "onclick": "setAdvisorView('projects')"} |
| 53 | button | Comité de tesis | {"type": "button", "id": "viewThesisCommitteeBtn", "onclick": "setAdvisorView('thesis')"} |
| 56 | button | Por asesor | {"type": "button", "id": "viewByTeacherBtn", "onclick": "setAdvisorView('teachers')"} |
| 59 | button | Guardar cambios | {"type": "button", "id": "saveAllAdvisorsBtn", "onclick": "saveAllAdvisorChanges()", "disabled": true} |
| 114 | label | Buscar asesor | {"for": "teacherSearch"} |
| 117 | input | Nombre, apellidos o nómina | {"type": "search", "id": "teacherSearch", "placeholder": "Nombre, apellidos o nómina", "oninput": "renderTeacherAdvisorView()"} |
| 121 | label | Filtrar por rol | {"for": "teacherRoleFilter"} |
| 122 | select | — | {"id": "teacherRoleFilter", "onchange": "renderTeacherAdvisorView()"} |
| 337 | select | — | {"id": "${role.key}-${project.id}", "onchange": "syncBlockedOptions(${project.id}); markAdvisorRow(${project.id})"} |
| 343 | button | Deshacer cambios | {"onclick": "revertAdvisorRow(${project.id})", "title": "Deshacer cambios"} |
| 344 | button | Ver proyecto | {"onclick": "showProjectDetails(${project.id})", "title": "Ver proyecto"} |
| 394 | h6 | ${escapeHtml(project.title)} | {} |
| 405 | button | Ver detalles | {"type": "button", "onclick": "showProjectDetails(${project.id})"} |
| 408 | button | Ajustar asesores | {"type": "button", "onclick": "focusProjectAssignment(${project.id}, 'projects')"} |
| 411 | button | Ajustar comité | {"type": "button", "onclick": "focusProjectAssignment(${project.id}, 'thesis')"} |

## pages/admin/asignaturas.php

[Fuente](../../pages/admin/asignaturas.php) · [Código completo](REFERENCIA.md#pages-admin-asignaturas-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 27 | h1 | Gestión de Asignaturas | {} |
| 29 | button | Gestionar Cargas | {"id": "cargas", "onclick": "openGroupsModal()"} |
| 32 | button | Gestionar Competencias | {"type": "button", "onclick": "openCompetenciasLaúncher()"} |
| 35 | button | Nueva Asignatura | {"onclick": "openAsignaturaModal()"} |
| 70 | h5 | Estructura de asignatura | {"id": "structureTitle"} |
| 71 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 85 | h5 | Competencias de asignatura | {"id": "competenciasTitle"} |
| 88 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 92 | form | — | {"id": "competenciaForm"} |
| 93 | input | — | {"type": "hidden", "id": "competenciaId"} |
| 96 | label | Competencia | {"for": "competenciaNombre"} |
| 97 | input | — | {"type": "text", "id": "competenciaNombre", "required": true} |
| 100 | label | Fecha inicio | {"for": "competenciaInicio"} |
| 101 | input | — | {"type": "date", "id": "competenciaInicio"} |
| 104 | label | Fecha fin | {"for": "competenciaFin"} |
| 105 | input | — | {"type": "date", "id": "competenciaFin"} |
| 108 | button | Guardar competencia | {"type": "submit", "title": "Guardar competencia"} |
| 138 | h5 | Gestionar competencias | {} |
| 141 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 144 | label | Buscar asignatura | {"for": "competenciasSubjectSearch"} |
| 145 | input | Clave o nombre de la asignatura | {"id": "competenciasSubjectSearch", "type": "search", "placeholder": "Clave o nombre de la asignatura", "oninput": "renderCompetenciasSubjects()"} |
| 157 | form | — | {"id": "asignaturaForm"} |
| 159 | h5 | Nueva Asignatura | {"id": "asignaturaModalTitle"} |
| 160 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 163 | input | — | {"type": "hidden", "id": "asignaturaId"} |
| 165 | label | Clave | {"for": "clave"} |
| 166 | input | — | {"type": "text", "id": "clave", "maxlength": "50"} |
| 169 | label | Nombre | {"for": "nombre"} |
| 170 | input | — | {"type": "text", "id": "nombre", "required": true} |
| 173 | label | Descripción | {"for": "descripción"} |
| 174 | textarea | — | {"id": "descripción", "rows": "3"} |
| 178 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 179 | button | Guardar | {"type": "submit"} |
| 190 | h5 | Cargas de asignaturas por semestre y grupo | {} |
| 191 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 196 | label | Semestre | {"for": "groupSemestre"} |
| 197 | select | — | {"id": "groupSemestre", "onchange": "loadSubjectGroups()"} |
| 206 | label | Grupo | {"for": "groupCode"} |
| 207 | input | A | {"type": "text", "id": "groupCode", "maxlength": "20", "placeholder": "A"} |
| 210 | label | Nombre visible | {"for": "groupName"} |
| 211 | input | Ej. 5to A | {"type": "text", "id": "groupName", "placeholder": "Ej. 5to A"} |
| 214 | label | Periodo | {"for": "groupPeriod"} |
| 215 | select | — | {"id": "groupPeriod", "required": true} |
| 218 | button | Guardar | {"onclick": "saveSubjectGroup()"} |
| 221 | input | — | {"type": "hidden", "id": "groupId"} |
| 224 | h6 | Asignaturas del grupo | {} |
| 228 | h6 | Cargas registradas | {} |
| 314 | button | Gestionar competencias | {"onclick": "openCompetenciasModal(${asignatura.id})", "title": "Gestionar competencias"} |
| 315 | button | Ver estructura | {"onclick": "viewAsignaturaStructure(${asignatura.id})", "title": "Ver estructura"} |
| 316 | button | Editar asignatura | {"onclick": "openAsignaturaModal(${asignatura.id})", "title": "Editar asignatura"} |
| 317 | button | Eliminar asignatura | {"onclick": "deleteAsignatura(${asignatura.id})", "title": "Eliminar asignatura"} |
| 342 | button | ${page} | {"type": "button", "onclick": "loadAsignaturas(${page})"} |
| 350 | select | Asignaturas por página | {"aria-label": "Asignaturas por página", "onchange": "changeAsignaturasPerPage(this.value)"} |
| 357 | button | Anterior | {"type": "button", "onclick": "loadAsignaturas(${current - 1})", "disabled": true} |
| 361 | button | = last ? 'disabled' : ''}&gt;Siguiente | {"type": "button", "onclick": "loadAsignaturas(${current + 1})"} |
| 457 | button | ${escapeHtml(asignatura.nombre)} ${escapeHtml(asignatura.clave &#124;&#124; 'Sin clave')} ${Number(asignatura.competencias_count &#124;&#124; 0)} | {"type": "button", "onclick": "openCompetenciasModal(${Number(asignatura.id)})"} |
| 491 | a | Gestionar entregables | {"href": "/pages/admin/deliverables.php?competencia_id=${competencia.id}", "title": "Gestionar entregables"} |
| 492 | button | Editar | {"onclick": "editCompetencia(${competencia.id})", "title": "Editar"} |
| 493 | button | Eliminar | {"onclick": "deleteCompetencia(${competencia.id})", "title": "Eliminar"} |
| 562 | input | ${asignatura.id} | {"type": "checkbox", "value": "${asignatura.id}", "id": "groupSubject${asignatura.id}", "checked": true} |
| 563 | label | ${escapeHtml(asignatura.nombre)} | {"for": "groupSubject${asignatura.id}"} |
| 639 | button | Editar | {"onclick": "editSubjectGroup(${group.id})", "title": "Editar"} |
| 640 | button | Eliminar | {"onclick": "deleteSubjectGroup(${group.id})", "title": "Eliminar"} |
| 743 | a | Entregables | {"href": "/pages/admin/deliverables.php?competencia_id=${competencia.id}"} |

## pages/admin/career-audit.php

[Fuente](../../pages/admin/career-audit.php) · [Código completo](REFERENCIA.md#pages-admin-career-audit-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Auditoría multicarrera | {} |
| 35 | form | — | {"id": "auditFilters"} |
| 36 | label | Carrera | {} |
| 36 | select | — | {"id": "auditCareer"} |
| 37 | label | Actor | {} |
| 37 | input | ID de usuario | {"id": "auditActor", "maxlength": "20", "placeholder": "ID de usuario"} |
| 38 | label | Método | {} |
| 38 | select | — | {"id": "auditMethod"} |
| 39 | label | Estado HTTP | {} |
| 39 | input | Ej. 200 | {"id": "auditStatus", "type": "number", "min": "100", "max": "599", "placeholder": "Ej. 200"} |
| 40 | label | Desde | {} |
| 40 | input | — | {"id": "auditFrom", "type": "date"} |
| 41 | label | Hasta | {} |
| 41 | input | — | {"id": "auditTo", "type": "date"} |
| 42 | button | Aplicar filtros | {"type": "submit"} |
| 54 | button | — | {"id": "auditPrevious"} |
| 54 | button | — | {"id": "auditNext"} |

## pages/admin/career-integrity.php

[Fuente](../../pages/admin/career-integrity.php) · [Código completo](REFERENCIA.md#pages-admin-career-integrity-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Integridad multicarrera | {} |
| 31 | button | Ejecutar diagnóstico | {"id": "runIntegrityButton", "type": "button"} |
| 41 | h2 | Historial de ejecuciones | {} |
| 78 | h2 | ${report.healthy ? 'Integridad correcta' : 'Se requiere revisión'} | {} |
| 88 | h3 | ${integrityEscape(check.name)} | {} |

## pages/admin/career-modules.php

[Fuente](../../pages/admin/career-modules.php) · [Código completo](REFERENCIA.md#pages-admin-career-modules-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 26 | h1 | Módulos de | {} |
| 30 | a | Carreras y accesos | {"href": "/pages/admin/careers.php"} |
| 36 | h2 | Módulos habilitados | {} |
| 46 | h2 | Registros del módulo | {} |
| 49 | button | Nuevo registro | {"type": "button", "data-bs-toggle": "collapse", "data-bs-target": "#recordFormWrap"} |
| 53 | form | — | {"id": "recordForm"} |
| 54 | label | Clave | {"for": "recordCode"} |
| 54 | input | — | {"id": "recordCode", "maxlength": "50"} |
| 55 | label | Título | {"for": "recordTitle"} |
| 55 | input | — | {"id": "recordTitle", "maxlength": "180", "required": true} |
| 56 | label | Estado | {"for": "recordStatus"} |
| 56 | select | — | {"id": "recordStatus"} |
| 57 | label | Descripción | {"for": "recordDescription"} |
| 57 | textarea | — | {"id": "recordDescription", "rows": "2"} |
| 58 | button | Guardar | {"type": "submit"} |
| 69 | h2 | Indicadores | {} |
| 110 | button | ${moduleEsc(item.label)} ${Number(item.records_count &#124;&#124; 0)} registros | {"type": "button", "data-select-module": "${moduleEsc(item.modulo)}"} |
| 114 | label | Habilitar módulo | {"title": "Habilitar módulo"} |
| 115 | input | — | {"type": "checkbox", "data-toggle-module": "${item.id}", "checked": true} |
| 149 | button | — | {"data-delete-record": "${record.id}"} |
| 164 | form | — | {"data-indicator": "${indicator.id}"} |
| 166 | label | Actual | {} |
| 166 | input | Sin captura | {"name": "valor_actual", "type": "number", "step": "0.01", "value": "${indicator.valor_actual ?? ''}", "placeholder": "Sin captura"} |
| 167 | label | Meta | {} |
| 167 | input | ${indicator.valor_meta ?? ''} | {"name": "valor_meta", "type": "number", "step": "0.01", "value": "${indicator.valor_meta ?? ''}"} |
| 168 | button | Guardar | {"type": "submit"} |

## pages/admin/career-setup.php

[Fuente](../../pages/admin/career-setup.php) · [Código completo](REFERENCIA.md#pages-admin-career-setup-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 29 | h1 | Carga académica de | {} |
| 33 | button | Exportar carrera | {"data-career-module": "reportes", "type": "button", "onclick": "downloadCareerPackage()"} |
| 34 | a | Ver catálogo | {"href": "/pages/admin/asignaturas.php"} |
| 43 | h2 | 1. Asignaturas | {} |
| 45 | button | Descargar plantilla | {"type": "button", "onclick": "downloadTemplate('subjects')"} |
| 46 | input | — | {"type": "file", "id": "subjectsFile", "accept": ".csv,text/csv"} |
| 54 | h2 | 2. Grupos y cargas | {} |
| 56 | button | Descargar plantilla | {"type": "button", "onclick": "downloadTemplate('groups')"} |
| 57 | input | — | {"type": "file", "id": "groupsFile", "accept": ".csv,text/csv"} |
| 66 | button | Importar catálogo | {"id": "importButton", "type": "button", "disabled": true} |
| 72 | h2 | 3. Rúbricas de evaluación | {} |
| 75 | button | Inicializar rúbricas | {"id": "initializeRubricsButton", "type": "button"} |

## pages/admin/careers.php

[Fuente](../../pages/admin/careers.php) · [Código completo](REFERENCIA.md#pages-admin-careers-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 29 | h1 | Carreras y accesos | {} |
| 33 | button | Exportar resumen | {"type": "button", "onclick": "downloadInstitutionalSummary()"} |
| 47 | h2 | Puesta en marcha | {} |
| 67 | h2 | Identidad de carrera | {} |
| 68 | form | — | {"id": "careerIdentityForm"} |
| 69 | input | — | {"type": "hidden", "id": "identityCareerId"} |
| 71 | label | Nombre | {"for": "careerName"} |
| 72 | input | — | {"id": "careerName", "maxlength": "180", "required": true} |
| 75 | label | Nombre corto | {"for": "careerShortName"} |
| 76 | input | — | {"id": "careerShortName", "maxlength": "100", "required": true} |
| 80 | label | Principal | {"for": "careerPrimary"} |
| 81 | input | — | {"type": "color", "id": "careerPrimary"} |
| 84 | label | Secundario | {"for": "careerSecondary"} |
| 85 | input | — | {"type": "color", "id": "careerSecondary"} |
| 88 | label | Acento | {"for": "careerAccent"} |
| 89 | input | — | {"type": "color", "id": "careerAccent"} |
| 93 | label | Lema | {"for": "careerMotto"} |
| 94 | textarea | — | {"id": "careerMotto", "rows": "3", "maxlength": "255"} |
| 96 | button | Guardar identidad | {"type": "submit"} |
| 107 | h2 | Membresías | {} |
| 110 | form | — | {"id": "membershipSearchForm"} |
| 111 | input | Buscar persona... | {"id": "membershipSearch", "placeholder": "Buscar persona..."} |
| 112 | button | — | {"type": "submit"} |
| 117 | form | — | {"id": "membershipForm"} |
| 119 | label | No. de control o empleado | {"for": "membershipUserId"} |
| 120 | input | — | {"id": "membershipUserId", "maxlength": "20", "required": true} |
| 123 | label | Rol en esta carrera | {"for": "membershipRole"} |
| 124 | select | — | {"id": "membershipRole", "required": true} |
| 133 | label | Carrera principal | {} |
| 134 | input | — | {"type": "checkbox", "id": "membershipPrimary"} |
| 137 | button | Asignar | {"type": "submit"} |
| 172 | button | ${careerEsc(career.clave)} ${careerEsc(career.nombre_corto)} ${Number(career.members_count &#124;&#124; 0)} integrantes · ${Number(career.readiness?.percentage &#124;&#124; 0)}% preparada | {"type": "button", "data-id": "${career.id}"} |
| 210 | button | Administrar | {"type": "button", "data-enter-career": "${Number(career.id)}"} |
| 288 | button | Cambiar estado | {"data-toggle-membership": "${item.id}", "data-active": "${item.activo ? 1 : 0}", "title": "Cambiar estado"} |
| 289 | button | Eliminar | {"data-delete-membership": "${item.id}", "title": "Eliminar"} |

## pages/admin/dashboard.php

[Fuente](../../pages/admin/dashboard.php) · [Código completo](REFERENCIA.md#pages-admin-dashboard-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 44 | h1 | Panel Administrativo | {} |
| 61 | h2 | Gestiones del sistema | {"id": "mobileShortcutsTitle"} |
| 66 | a | Usuarios | {"href": "/pages/admin/users.php"} |
| 70 | a | Proyectos y tesis | {"href": "/pages/admin/projects.php"} |
| 74 | a | Propuestas | {"href": "/pages/admin/proposal-config.php"} |
| 78 | a | Entregables | {"href": "/pages/admin/deliverables.php"} |
| 82 | a | Evaluaciones | {"href": "/pages/admin/evaluations.php"} |
| 86 | a | Académico | {"href": "/pages/admin/asignaturas.php"} |
| 90 | a | Semestres | {"href": "/pages/admin/semesters.php"} |
| 94 | a | Repositorio | {"href": "/pages/repositorio.php"} |
| 108 | a | Ir a proyectos | {"href": "/pages/admin/projects.php", "id": "adminNextActionLink"} |
| 116 | a | Total de usuarios 0 0 inactivos | {"href": "/pages/admin/users.php", "aria-label": "Ir a gestión de usuarios"} |
| 133 | a | Usuarios activos 0 | {"href": "/pages/admin/users.php", "aria-label": "Ir a usuarios activos"} |
| 150 | a | Proyectos 0 0 propuestas pendientes | {"href": "/pages/admin/projects.php", "aria-label": "Ir a gestión de proyectos"} |
| 168 | a | " class="text-decoration-none d-block dashboard-stat-link" aria-label="Ir a "&gt; "&gt; 0 | {} |
| 189 | h5 | Avance global | {} |
| 198 | h5 | Usuarios por rol | {} |
| 204 | h5 | "&gt; | {} |
| 212 | h5 | Estado de propuestas | {} |
| 222 | h5 | Acciones Rápidas | {} |
| 226 | a | Gestionar Usuarios Ver, editar y crear usuarios | {"href": "/pages/admin/users.php"} |
| 229 | h6 | Gestionar Usuarios | {} |
| 235 | a | Proyectos Gestionar proyectos | {"href": "/pages/admin/projects.php"} |
| 238 | h6 | Proyectos | {} |
| 244 | a | Entregables Gestionar entregables | {"href": "/pages/admin/deliverables.php"} |
| 247 | h6 | Entregables | {} |
| 253 | a | Etiquetas Gestionar etiquetas de documentos | {"href": "/pages/admin/document-tags.php"} |
| 256 | h6 | Etiquetas | {} |
| 271 | h5 | Resumen de proyectos | {} |
| 272 | a | Ver todos | {"href": "/pages/admin/projects.php"} |
| 450 | a | ${escapeHtml(project.title &#124;&#124; project.titulo &#124;&#124; `Proyecto ${project.id}`)} ${escapeHtml(project.creator?.nombres &#124;&#124; project.creador?.nombres &#124;&#124; 'Sin responsable')} &#124; ${formatDashboardDate(project.created_at &#124;&#124; project.creado_en)} | {"href": "/pages/admin/projects.php?edit=${Number(project.id)}"} |
| 473 | a | Nuevo | {"href": "/pages/admin/project-create.php"} |

## pages/admin/database-backups.php

[Fuente](../../pages/admin/database-backups.php) · [Código completo](REFERENCIA.md#pages-admin-database-backups-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Respaldos de base de datos | {} |
| 31 | button | Crear respaldo ahora | {"id": "createBackupButton", "type": "button"} |
| 49 | h2 | Historial de respaldos | {} |
| 92 | h2 | Almacenamiento ${health.healthy ? 'saludable' : 'requiere revisión'} | {} |
| 104 | button | Aplicar retención | {"type": "button", "onclick": "cleanupBackups(${retentionDays})", "disabled": true} |
| 135 | button | Verificar | {"type": "button", "onclick": "verifyBackup(${Number(backup.id)}, this)"} |
| 136 | button | Descargar | {"type": "button", "onclick": "downloadBackup(${Number(backup.id)}, '${backupEscape(backup.nombre_archivo)}')"} |

## pages/admin/deliverables.php

[Fuente](../../pages/admin/deliverables.php) · [Código completo](REFERENCIA.md#pages-admin-deliverables-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 34 | h1 | Gestión de Entregables | {} |
| 38 | a | Quitar filtro | {"href": "/pages/admin/deliverables.php", "id": "clearFilterBtn"} |
| 41 | button | Nuevo Entregable | {"onclick": "openDeliverableModal()"} |
| 49 | label | Materia | {} |
| 50 | select | — | {"id": "subjectFilter", "onchange": "onSubjectFilterChange()"} |
| 55 | label | Competencia | {} |
| 56 | select | — | {"id": "competenceFilter", "onchange": "loadDeliverables()"} |
| 89 | form | — | {"id": "deliverableForm"} |
| 91 | h5 | Nuevo Entregable | {"id": "deliverableModalTitle"} |
| 92 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 95 | input | — | {"type": "hidden", "id": "deliverableId"} |
| 98 | label | Proyecto | {"for": "project_id"} |
| 99 | select | — | {"id": "project_id"} |
| 103 | label | Asignatura | {"for": "competencia_id"} |
| 104 | select | — | {"id": "competencia_id", "required": true} |
| 110 | label | Nombre | {"for": "nombre"} |
| 111 | input | — | {"type": "text", "id": "nombre", "required": true} |
| 114 | label | Descripción | {"for": "descripcion"} |
| 115 | textarea | — | {"id": "descripcion", "rows": "3"} |
| 119 | label | Tipo | {"for": "tipo_documento"} |
| 120 | select | — | {"id": "tipo_documento"} |
| 130 | label | Estado | {"for": "estado"} |
| 131 | select | — | {"id": "estado"} |
| 139 | label | Rama asociada | {"for": "rama_asociada"} |
| 140 | input | — | {"type": "text", "id": "rama_asociada"} |
| 144 | label | Autores | {"for": "autores"} |
| 145 | input | — | {"type": "text", "id": "autores"} |
| 149 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 150 | button | Guardar | {"type": "submit"} |
| 160 | h5 | Calificar Entregable | {} |
| 161 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 164 | input | — | {"type": "hidden", "id": "deliverableIdCalificar"} |
| 165 | label | Calificación (0-100) | {"for": "calificacionInput"} |
| 166 | input | — | {"type": "number", "id": "calificacionInput", "min": "0", "max": "100", "step": "0.01"} |
| 169 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 170 | button | Calificar | {"type": "button", "onclick": "guardarCalificacion()"} |
| 180 | h5 | Subir Archivo | {} |
| 181 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 184 | input | — | {"type": "hidden", "id": "deliverableIdSubir"} |
| 185 | label | Seleccionar archivo | {"for": "archivoInput"} |
| 186 | input | — | {"type": "file", "id": "archivoInput", "accept": ".pdf,.doc,.docx,.xls,.xlsx,.zip"} |
| 190 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 191 | button | Subir | {"type": "button", "onclick": "guardarArchivo()"} |
| 301 | button | Descargar | {"onclick": "descargarEntregable(${deliverable.id}, '${escapeHtml(deliverable.nombre)}', ${deliverable.project_id})", "title": "Descargar"} |
| 314 | button | Editar | {"onclick": "openDeliverableModal(${deliverable.id})", "title": "Editar"} |
| 315 | button | Calificar | {"onclick": "abrirCalificar(${deliverable.id}, ${deliverable.project_id})", "title": "Calificar"} |
| 317 | button | Subir archivo | {"onclick": "abrirSubirArchivo(${deliverable.id}, ${deliverable.project_id})", "title": "Subir archivo"} |
| 318 | button | Eliminar | {"onclick": "deleteDeliverable(${deliverable.id})", "title": "Eliminar"} |

## pages/admin/document-tags.php

[Fuente](../../pages/admin/document-tags.php) · [Código completo](REFERENCIA.md#pages-admin-document-tags-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 95 | h1 | Gestión de Etiquetas de Documentos | {} |
| 98 | button | Nueva etiqueta | {"type": "button", "onclick": "openTagModal()"} |
| 129 | label | Buscar etiqueta | {"for": "tagSearch"} |
| 132 | input | Nombre o descripción | {"type": "search", "id": "tagSearch", "placeholder": "Nombre o descripción", "oninput": "scheduleTagSearch()"} |
| 133 | button | Limpiar búsqueda | {"type": "button", "onclick": "clearTagSearch()", "title": "Limpiar búsqueda"} |
| 137 | label | Mostrar | {"for": "tagPerPage"} |
| 138 | select | — | {"id": "tagPerPage", "onchange": "loadTags(1)"} |
| 145 | button | Actualizar | {"type": "button", "onclick": "loadTags(currentPage, true)"} |
| 187 | form | — | {"id": "tagForm", "onsubmit": "saveTag(event)"} |
| 189 | h5 | Nueva etiqueta | {"id": "tagModalTitle"} |
| 190 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 194 | input | — | {"type": "hidden", "id": "tagId"} |
| 196 | label | Nombre | {"for": "tagName"} |
| 197 | input | — | {"type": "text", "id": "tagName", "maxlength": "100", "oninput": "refreshTagPreview()", "required": true} |
| 200 | label | Descripción | {"for": "tagDescription"} |
| 201 | textarea | Uso recomendado de la etiqueta | {"id": "tagDescription", "rows": "3", "maxlength": "1000", "placeholder": "Uso recomendado de la etiqueta"} |
| 204 | label | Color | {"for": "tagColor"} |
| 206 | input | Selecciona un color | {"type": "color", "id": "tagColor", "value": "#1B396A", "title": "Selecciona un color", "onchange": "refreshTagPreview()"} |
| 207 | input | #1B396A | {"type": "text", "id": "tagColorText", "maxlength": "7", "value": "#1B396A", "oninput": "syncTagColorFromText()"} |
| 219 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 220 | button | Guardar | {"type": "submit", "id": "tagSaveBtn"} |
| 299 | button | Editar | {"onclick": "openTagModal(${Number(tag.id)})", "title": "Editar"} |
| 302 | button | Eliminar | {"onclick": "deleteTag('${tag.id}')", "title": "Eliminar"} |
| 336 | button | Anterior | {"onclick": "loadTags(${currentPage - 1})"} |
| 340 | button | Siguiente | {"onclick": "loadTags(${currentPage + 1})"} |
| 357 | button | ${color} | {"type": "button", "onclick": "setTagColor('${color}')", "title": "${color}"} |

## pages/admin/evaluation-rooms.php

[Fuente](../../pages/admin/evaluation-rooms.php) · [Código completo](REFERENCIA.md#pages-admin-evaluation-rooms-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 200 | h1 | Salas de evaluación | {} |
| 203 | a | Volver a evaluaciones | {"href": "/pages/admin/evaluations.php"} |
| 212 | label | Semestre | {"for": "semesterFilter"} |
| 213 | select | — | {"id": "semesterFilter", "onchange": "renderCurrentView()"} |
| 222 | label | Estado | {"for": "roomStatusFilter"} |
| 223 | select | — | {"id": "roomStatusFilter", "onchange": "reloadRooms()"} |
| 229 | label | Buscar | {"for": "roomSearch"} |
| 232 | input | Sala, docente, proyecto o salon | {"type": "search", "id": "roomSearch", "placeholder": "Sala, docente, proyecto o salon", "oninput": "renderCurrentView()"} |
| 238 | button | Por sala | {"type": "button", "id": "viewRoomsBtn", "onclick": "setRoomView('rooms')"} |
| 242 | button | Por proyecto | {"type": "button", "id": "viewProjectsBtn", "onclick": "setRoomView('projects')"} |
| 245 | button | Por evaluador | {"type": "button", "id": "viewTeachersBtn", "onclick": "setRoomView('teachers')"} |
| 248 | button | Crear sala | {"type": "button", "onclick": "startNewRoom()"} |
| 267 | h5 | Configuración de sala | {} |
| 280 | input | — | {"type": "hidden", "id": "roomId"} |
| 284 | label | Sala | {"for": "roomName"} |
| 285 | input | Sala 1 | {"id": "roomName", "placeholder": "Sala 1"} |
| 289 | label | Salon | {"for": "roomClassroom"} |
| 290 | select | — | {"id": "roomClassroom"} |
| 294 | label | Semestre | {"for": "roomSemester"} |
| 295 | select | — | {"id": "roomSemester", "onchange": "loadRoomProjects()"} |
| 303 | label | Inicio | {"for": "roomDate"} |
| 304 | input | — | {"type": "datetime-local", "id": "roomDate", "onchange": "updateRoomAvailability()"} |
| 308 | label | Fin | {"for": "roomEndDate"} |
| 309 | input | — | {"type": "datetime-local", "id": "roomEndDate", "onchange": "updateRoomAvailability()"} |
| 315 | label | Eval. docente | {"for": "teacherMinutes"} |
| 316 | input | 15 | {"type": "number", "id": "teacherMinutes", "min": "1", "max": "240", "value": "15"} |
| 319 | label | Presentación | {"for": "presentationMinutes"} |
| 320 | input | 20 | {"type": "number", "id": "presentationMinutes", "min": "1", "max": "240", "value": "20"} |
| 323 | label | Oportunidades | {"for": "maxAttempts"} |
| 324 | input | 1 | {"type": "number", "id": "maxAttempts", "min": "1", "max": "10", "value": "1"} |
| 333 | label | Responsable de sala | {"for": "responsibleTeacher"} |
| 334 | select | — | {"id": "responsibleTeacher"} |
| 347 | button | Limpiar | {"onclick": "resetRoomForm()"} |
| 348 | button | Guardar sala | {"onclick": "saveRoom()"} |
| 357 | h5 | Salas registradas | {} |
| 370 | h5 | Proyectos por sala | {} |
| 402 | form | — | {"id": "roomScheduleForm"} |
| 403 | h5 | Horario y evaluación fuera de tiempo | {} |
| 403 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 405 | input | — | {"type": "hidden", "id": "scheduleRoomId"} |
| 407 | label | Inicio | {"for": "scheduleStart"} |
| 407 | input | — | {"id": "scheduleStart", "type": "datetime-local", "required": true} |
| 408 | label | Fin ordinario | {"for": "scheduleEnd"} |
| 408 | input | — | {"id": "scheduleEnd", "type": "datetime-local", "required": true} |
| 409 | input | — | {"id": "scheduleAllowLate", "type": "checkbox", "onchange": "toggleLateScheduleFields()"} |
| 409 | label | Permitir evaluaciones fuera del horario ordinario | {"for": "scheduleAllowLate"} |
| 410 | label | Autorizada hasta | {"for": "scheduleLateUntil"} |
| 410 | input | — | {"id": "scheduleLateUntil", "type": "datetime-local"} |
| 411 | label | Motivo | {"for": "scheduleLateReason"} |
| 411 | textarea | — | {"id": "scheduleLateReason", "rows": "3", "minlength": "10", "maxlength": "500"} |
| 415 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 415 | button | Guardar horario | {"type": "submit"} |
| 668 | button | Reporte sala | {"onclick": "downloadRoomReport(${room.id})", "title": "Reporte sala"} |
| 669 | button | Reporte docentes | {"onclick": "downloadRoomTeacherReport(${room.id})", "title": "Reporte docentes"} |
| 670 | button | Restaurar | {"onclick": "unarchiveRoom(${room.id})", "title": "Restaurar"} |
| 671 | button | Editar | {"onclick": "editRoom(${room.id})", "title": "Editar"} |
| 672 | button | Modificar horario | {"onclick": "openRoomSchedule(${room.id})", "title": "Modificar horario"} |
| 673 | button | Bloquear orden | {"onclick": "lockRoomSequence(${room.id})", "title": "Bloquear orden"} |
| 674 | button | Siguiente proyecto | {"onclick": "advanceRoom(${room.id})", "title": "Siguiente proyecto"} |
| 675 | button | Archivar | {"onclick": "archiveRoom(${room.id})", "title": "Archivar"} |
| 676 | button | Eliminar | {"onclick": "deleteRoom(${room.id})", "title": "Eliminar"} |
| 695 | label | Minutos | {} |
| 695 | input | ${minutes} | {"id": "timerMinutes${room.id}", "type": "number", "min": "1", "max": "240", "value": "${minutes}", "disabled": true} |
| 697 | button | Pausar | {"onclick": "controlRoomTimer(${room.id}, 'pause')"} |
| 699 | button | Reanudar | {"onclick": "controlRoomTimer(${room.id}, 'resume')"} |
| 700 | button | Iniciar | {"onclick": "controlRoomTimer(${room.id}, 'start')"} |
| 701 | button | Reiniciar | {"onclick": "controlRoomTimer(${room.id}, 'reset')"} |
| 702 | button | Finalizar | {"onclick": "controlRoomTimer(${room.id}, 'finish')"} |
| 732 | select | — | {"id": "projectRoom${project.id}"} |
| 737 | input | ${escapeHtml(currentOrder)} | {"id": "projectOrder${project.id}", "type": "number", "min": "1", "value": "${escapeHtml(currentOrder)}"} |
| 739 | button | — | {"onclick": "saveProjectRoom(${project.id})"} |
| 740 | button | — | {"onclick": "editRoom(${currentRoom.id}); setRoomView('rooms')"} |
| 855 | label | ${escapeHtml(fullName(teacher))} ${Number(teacher.perfil_id) === 1 ? 'Administrativo' : 'Docente'} | {"for": "roomTeacher${escapeHtml(teacher.id)}"} |
| 856 | input | ${escapeHtml(teacher.id)} | {"type": "checkbox", "value": "${escapeHtml(teacher.id)}", "id": "roomTeacher${escapeHtml(teacher.id)}", "onchange": "refreshResponsibleTeacherOptions()", "checked": true} |
| 907 | input | ${project.id} | {"type": "checkbox", "value": "${project.id}", "id": "roomProject${project.id}", "onchange": "scheduleRoomProjectsReorder()", "checked": true} |
| 908 | input | Orden | {"data-project-id": "${project.id}", "type": "number", "min": "1", "value": "${normalizedOrderMap[project.id] &#124;&#124; ''}", "title": "Orden", "onchange": "scheduleRoomProjectsReorder()"} |
| 909 | label | ${escapeHtml(project.title)} ${escapeHtml(projectActiveAuthors(project) &#124;&#124; 'Sin integrantes registrados')} ${projectCompany(project) ? ` Empresa: ${escapeHtml(projectCompany(project))} ` : ''} | {"for": "roomProject${project.id}"} |

## pages/admin/evaluations.php

[Fuente](../../pages/admin/evaluations.php) · [Código completo](REFERENCIA.md#pages-admin-evaluations-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 137 | h1 | — | {} |
| 141 | a | "&gt; "&gt; | {} |
| 145 | button | "&gt; | {"id": "archiveSelectionBtn", "onclick": "handleArchiveSelectionButton()"} |
| 148 | button | Cancelar | {"id": "archiveSelectionCancelBtn", "onclick": "cancelArchiveSelectionMode()"} |
| 153 | button | Gestionar Rúbrica | {"onclick": "openRubricModal()"} |
| 158 | a | Gestionar salas | {"href": "/pages/admin/evaluation-rooms.php"} |
| 169 | label | Semestre | {"for": "semesterFilter"} |
| 170 | select | — | {"id": "semesterFilter", "onchange": "loadEvaluations()"} |
| 179 | label | Proyecto | {"for": "projectFilter"} |
| 180 | select | — | {"id": "projectFilter", "onchange": "loadEvaluations()"} |
| 214 | form | — | {"id": "evaluationForm"} |
| 216 | h5 | Nueva Evaluación | {} |
| 217 | button | — | {"type": "button", "data-bs-dismiss": "modal", "onmousedown": "this.blur()", "onclick": "this.blur()"} |
| 221 | label | Sala configurada | {"for": "evaluation_room_id"} |
| 222 | select | — | {"id": "evaluation_room_id", "onchange": "applyRoomToEvaluationForm()", "required": true} |
| 227 | label | Proyecto | {"for": "project_id"} |
| 228 | select | — | {"id": "project_id", "required": true} |
| 232 | label | Semestre | {"for": "semestre"} |
| 233 | select | — | {"id": "semestre", "required": true} |
| 241 | label | Sala | {"for": "sala"} |
| 242 | input | Sala 1 | {"type": "text", "id": "sala", "placeholder": "Sala 1"} |
| 246 | label | Fecha de exposición | {"for": "fecha_exposicion"} |
| 247 | input | — | {"type": "datetime-local", "id": "fecha_exposicion"} |
| 251 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 252 | button | Crear | {"type": "submit"} |
| 262 | h5 | Gestionar Rúbrica por Semestre | {} |
| 263 | button | — | {"type": "button", "data-bs-dismiss": "modal", "onmousedown": "this.blur()", "onclick": "this.blur()"} |
| 268 | label | Semestre | {"for": "rubricSemester"} |
| 269 | select | — | {"id": "rubricSemester", "onchange": "onRubricScopeChange()"} |
| 277 | label | Método | {"for": "rubricScoreMode"} |
| 278 | select | — | {"id": "rubricScoreMode", "onchange": "saveRubricScoreMode()"} |
| 284 | label | Proyecto 8vo | {"for": "rubricProjectId"} |
| 285 | select | — | {"id": "rubricProjectId", "onchange": "loadRubricCriteria()"} |
| 291 | label | Nueva pregunta | {"for": "newCriterionText"} |
| 292 | input | — | {"type": "text", "id": "newCriterionText", "maxlength": "255"} |
| 295 | button | — | {"type": "button", "onclick": "addCriterion()"} |
| 308 | form | — | {"id": "scoreForm"} |
| 310 | h5 | Rúbrica de Evaluación | {} |
| 311 | button | — | {"type": "button", "data-bs-dismiss": "modal", "onmousedown": "this.blur()", "onclick": "this.blur()"} |
| 314 | input | — | {"type": "hidden", "id": "scoreEvaluationId"} |
| 319 | label | Comentarios o puntos de mejora | {"for": "generalEvaluationComment"} |
| 320 | textarea | Comentario final visible en el desglose | {"id": "generalEvaluationComment", "rows": "3", "placeholder": "Comentario final visible en el desglose"} |
| 323 | label | ¿Recomienda continuar el proyecto? (No acredita titulación) | {} |
| 324 | select | — | {"id": "apto_titulacion"} |
| 333 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 334 | button | Guardar Rúbrica | {"type": "submit"} |
| 345 | h5 | Salas de Evaluación | {} |
| 348 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 355 | h6 | Configuración de sala | {} |
| 358 | input | — | {"type": "hidden", "id": "roomId"} |
| 361 | label | Sala | {} |
| 361 | input | Sala 1 | {"id": "roomName", "placeholder": "Sala 1", "required": true} |
| 362 | label | Salon | {} |
| 362 | input | Salon/Laboratorio | {"id": "roomClassroom", "placeholder": "Salon/Laboratorio", "required": true} |
| 363 | label | Semestre | {} |
| 363 | select | — | {"id": "roomSemester", "onchange": "loadRoomProjects()"} |
| 364 | label | Inicio | {} |
| 364 | input | — | {"type": "datetime-local", "id": "roomDate", "onchange": "updateRoomAvailability()", "required": true} |
| 365 | label | Fin | {} |
| 365 | input | — | {"type": "datetime-local", "id": "roomEndDate", "onchange": "updateRoomAvailability()", "required": true} |
| 367 | label | Eval. docente | {} |
| 367 | input | 15 | {"type": "number", "id": "teacherMinutes", "min": "1", "max": "240", "value": "15"} |
| 368 | label | Presentación | {} |
| 368 | input | 20 | {"type": "number", "id": "presentationMinutes", "min": "1", "max": "240", "value": "20"} |
| 369 | label | Oportunidades | {} |
| 369 | input | 1 | {"type": "number", "id": "maxAttempts", "min": "1", "max": "10", "value": "1"} |
| 371 | label | Responsable de sala | {} |
| 371 | select | — | {"id": "responsibleTeacher"} |
| 376 | button | Limpiar | {"onclick": "resetRoomForm()"} |
| 377 | button | Guardar sala | {"onclick": "saveRoom()"} |
| 385 | h6 | Salas registradas | {} |
| 401 | h5 | Desglose por Docente | {} |
| 402 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 413 | h5 | Detalles del Proyecto | {} |
| 414 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 912 | label | ${escapeHtml(label)} | {} |
| 913 | input | — | {"type": "checkbox", "-key": "${safeKey}", "onchange": "toggleArchiveGroupSelection('${type}', this.dataset.${dataKeyName}, this.checked)", "checked": true} |
| 922 | input | Seleccionar evaluación | {"type": "checkbox", "data-archive-evaluation-id": "${id}", "onchange": "toggleArchiveEvaluationSelection(${id}, this.checked)", "title": "Seleccionar evaluación", "checked": true} |
| 1109 | button | Reporte sala | {"type": "button", "onclick": "downloadRoomReport(${room.id})", "title": "Reporte PDF de sala"} |
| 1110 | button | Reporte docentes | {"type": "button", "onclick": "downloadRoomTeacherReport(${room.id})", "title": "Reporte PDF para docentes"} |
| 1117 | button | ${isExpanded ? 'Ocultar' : 'Desplegar'} | {"type": "button", "onclick": "toggleEvaluationRoom('${escapeHtml(roomKey)}')"} |
| 1136 | button | Siguiente proyecto | {"type": "button", "onclick": "advanceRoom(${room.id})"} |
| 1179 | button | ${evaluation.evaluators_count &gt; 0 ? `${evaluation.global_average}% · ${evaluation.evaluators_count}/${evaluation.expected_evaluators_count &#124;&#124; 0} evaluadores` : `Sin calificaciones · 0/${evaluation.expected_evaluators_count &#124;&#124; 0} evaluadores`} | {"onclick": "showBreakdown(${evaluation.id})"} |
| 1187 | button | Evaluar | {"onclick": "openScoreModal(${evaluation.id})", "title": "${evaluation.document_readiness?.all_students_released ? 'Evaluar' : 'No se puede evaluar: hay alumnos sin liberar'}", "disabled": true} |
| 1192 | button | Marcar evaluada | {"onclick": "markEvaluationCompleted(${evaluation.id})", "title": "Marcar esta evaluación como completada con los docentes presentes"} |
| 1197 | button | Detalles del proyecto | {"onclick": "showProjectDetails(${evaluation.id})", "title": "Detalles del proyecto"} |
| 1198 | button | Reporte PDF | {"onclick": "downloadEvaluationReport(${evaluation.id})", "title": "Reporte PDF"} |
| 1199 | button | Reporte PDF para docentes | {"onclick": "downloadEvaluationTeacherReport(${evaluation.id})", "title": "Reporte PDF para docentes"} |
| 1200 | button | Rubrica personalizada | {"onclick": "openRubricModal(${evaluation.project_id})", "title": "Rubrica personalizada"} |
| 1201 | button | ${IS_ARCHIVED_VIEW ? 'Restaurar a vista principal' : 'Archivar'} | {"onclick": "${IS_ARCHIVED_VIEW ? 'unarchiveEvaluation' : 'archiveEvaluation'}(${evaluation.id})", "title": "${IS_ARCHIVED_VIEW ? 'Restaurar a vista principal' : 'Archivar'}"} |
| 1202 | button | Eliminar | {"onclick": "deleteEvaluation(${evaluation.id})", "title": "Eliminar"} |
| 1270 | h5 | ${escapeHtml(project?.title &#124;&#124; 'N/A')} | {} |
| 1458 | label | ${escapeHtml(fullName(teacher))} ${Number(teacher.perfil_id) === 1 ? 'Administrativo' : 'Docente'} | {"for": "roomTeacher${escapeHtml(teacher.id)}"} |
| 1459 | input | ${escapeHtml(teacher.id)} | {"type": "checkbox", "value": "${escapeHtml(teacher.id)}", "id": "roomTeacher${escapeHtml(teacher.id)}", "onchange": "refreshResponsibleTeacherOptions()", "checked": true} |
| 1506 | input | ${project.id} | {"type": "checkbox", "value": "${project.id}", "id": "roomProject${project.id}", "onchange": "scheduleRoomProjectsReorder()", "checked": true} |
| 1507 | input | Orden de presentación | {"data-project-id": "${project.id}", "type": "number", "min": "1", "value": "${normalizedOrderMap[project.id] &#124;&#124; ''}", "title": "Orden", "aria-label": "Orden de presentación", "onchange": "scheduleRoomProjectsReorder()"} |
| 1508 | label | ${escapeHtml(project.title)} ${escapeHtml(projectActiveAuthors(project) &#124;&#124; 'Sin integrantes registrados')} ${projectCompany(project) ? ` Empresa: ${escapeHtml(projectCompany(project))} ` : ''} | {"for": "roomProject${project.id}"} |
| 1545 | button | Reporte PDF de sala | {"onclick": "downloadRoomReport(${room.id})", "title": "Reporte PDF de sala"} |
| 1546 | button | Reporte PDF para docentes | {"onclick": "downloadRoomTeacherReport(${room.id})", "title": "Reporte PDF para docentes"} |
| 1548 | button | Editar | {"onclick": "editRoom(${room.id})", "title": "Editar"} |
| 1549 | button | Bloquear orden | {"onclick": "lockRoomSequence(${room.id})", "title": "Bloquear orden"} |
| 1550 | button | Siguiente proyecto | {"onclick": "advanceRoom(${room.id})", "title": "Siguiente proyecto"} |
| 1551 | button | ${IS_ARCHIVED_VIEW ? 'Restaurar sala' : 'Archivar sala'} | {"onclick": "${IS_ARCHIVED_VIEW ? 'unarchiveRoom' : 'archiveRoom'}(${room.id})", "title": "${IS_ARCHIVED_VIEW ? 'Restaurar sala' : 'Archivar sala'}"} |
| 1552 | button | Eliminar | {"onclick": "deleteRoom(${room.id})", "title": "Eliminar"} |
| 1855 | input | ${criterion.orden &#124;&#124; 0} | {"type": "number", "value": "${criterion.orden &#124;&#124; 0}", "min": "0"} |
| 1858 | input | ${escapeHtml(criterion.label)} | {"type": "text", "value": "${escapeHtml(criterion.label)}", "maxlength": "255"} |
| 1862 | button | Guardar | {"type": "button", "onclick": "updateCriterion(${criterion.id})", "title": "Guardar"} |
| 1863 | button | Desactivar | {"type": "button", "onclick": "deleteCriterion(${criterion.id})", "title": "Desactivar"} |
| 1925 | h6 | ${escapeHtml(project?.title &#124;&#124; 'N/A')} | {} |
| 1956 | label | ${escapeHtml(criterion.label)} ${criterion.project_id ? ' Proyecto ' : ' General '} | {} |
| 1957 | select | — | {"required": true} |
| 1965 | textarea | Comentario opcional, no afecta el puntaje | {"rows": "2", "placeholder": "Comentario opcional, no afecta el puntaje"} |
| 2065 | h6 | ${escapeHtml(teacher.teacher_name)} | {} |
| 2074 | h6 | Retroalimentación de evaluación | {} |
| 2077 | textarea | — | {"id": "roomFeedbackText", "rows": "3"} |
| 2078 | button | Guardar retroalimentacion | {"onclick": "saveRoomFeedback(${evaluation.id})"} |

## pages/admin/notices.php

[Fuente](../../pages/admin/notices.php) · [Código completo](REFERENCIA.md#pages-admin-notices-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Avisos del Sistema | {} |
| 32 | button | — | {"type": "button", "onclick": "loadNotices()"} |
| 33 | button | Nuevo aviso | {"type": "button", "onclick": "openNoticeModal()"} |
| 71 | form | — | {"id": "noticeForm"} |
| 73 | h5 | Aviso | {} |
| 74 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal", "aria-label": "Cerrar"} |
| 77 | input | — | {"type": "hidden", "id": "noticeId"} |
| 80 | label | Titulo | {"for": "noticeTitle"} |
| 81 | input | Ej. Entrega proxima | {"type": "text", "id": "noticeTitle", "maxlength": "90", "placeholder": "Ej. Entrega proxima"} |
| 84 | label | Audiencia | {"for": "noticeAudience"} |
| 85 | select | — | {"id": "noticeAudience", "required": true} |
| 96 | label | Tipo | {"for": "noticeType"} |
| 97 | select | — | {"id": "noticeType", "required": true} |
| 105 | label | Autocierre | {"for": "noticeDuration"} |
| 107 | input | 4 | {"type": "number", "id": "noticeDuration", "min": "2", "max": "30", "value": "4", "required": true} |
| 113 | input | — | {"type": "checkbox", "role": "switch", "id": "noticeActive", "checked": true} |
| 114 | label | Aviso activo | {"for": "noticeActive"} |
| 118 | label | Mostrar desde | {"for": "noticeStartsAt"} |
| 119 | input | — | {"type": "date", "id": "noticeStartsAt"} |
| 122 | label | Eliminar despues de | {"for": "noticeEndsAt"} |
| 123 | input | — | {"type": "date", "id": "noticeEndsAt"} |
| 127 | label | Mensaje | {"for": "noticeMessage"} |
| 128 | textarea | — | {"id": "noticeMessage", "rows": "4", "maxlength": "500", "required": true} |
| 134 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 135 | button | Guardar aviso | {"type": "submit"} |
| 192 | button | — | {"type": "button", "onclick": "editNotice(${index})"} |
| 193 | button | — | {"type": "button", "onclick": "toggleNotice(${index})"} |
| 194 | button | — | {"type": "button", "onclick": "deleteNotice(${index})"} |

## pages/admin/operations.php

[Fuente](../../pages/admin/operations.php) · [Código completo](REFERENCIA.md#pages-admin-operations-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Centro de operaciones | {} |
| 32 | button | Política | {"id": "continuityPolicyButton", "type": "button", "data-bs-toggle": "modal", "data-bs-target": "#continuityPolicyModal"} |
| 33 | button | Guardar medición | {"id": "measureContinuityButton", "type": "button"} |
| 34 | button | Reporte de continuidad | {"id": "continuityReportButton", "type": "button"} |
| 35 | button | Analizar ahora | {"id": "scanButton", "type": "button"} |
| 46 | h2 | Tendencia de continuidad | {} |
| 61 | h2 | Historial de alertas | {} |
| 76 | form | — | {"id": "continuityPolicyForm"} |
| 78 | h2 | Política de continuidad | {"id": "continuityPolicyTitle"} |
| 79 | button | Cerrar | {"type": "button", "data-bs-dismiss": "modal", "aria-label": "Cerrar"} |
| 83 | label | Objetivo (%) | {"for": "policyTarget"} |
| 83 | input | — | {"id": "policyTarget", "type": "number", "min": "50", "max": "100", "required": true} |
| 84 | label | Umbral crítico (%) | {"for": "policyCritical"} |
| 84 | input | — | {"id": "policyCritical", "type": "number", "min": "1", "max": "99", "required": true} |
| 85 | label | Máxima antigüedad (horas) | {"for": "policyBackupAge"} |
| 85 | input | — | {"id": "policyBackupAge", "type": "number", "min": "1", "max": "168", "required": true} |
| 86 | label | Retención (días) | {"for": "policyRetention"} |
| 86 | input | — | {"id": "policyRetention", "type": "number", "min": "7", "max": "365", "required": true} |
| 90 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 90 | button | Guardar política | {"id": "savePolicyButton", "type": "submit"} |
| 131 | button | Atender | {"type": "button", "onclick": "acknowledgeAlert(${Number(alert.id)}, this)"} |

## pages/admin/project-create.php

[Fuente](../../pages/admin/project-create.php) · [Código completo](REFERENCIA.md#pages-admin-project-create-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 26 | h1 | Crear Nuevo Proyecto | {} |
| 30 | form | — | {"id": "projectForm"} |
| 32 | label | Titulo del Proyecto | {"for": "title"} |
| 33 | input | — | {"type": "text", "id": "title", "name": "title", "required": true} |
| 36 | label | Descripción | {"for": "descripcion"} |
| 37 | textarea | — | {"id": "descripcion", "name": "descripcion", "rows": "4", "required": true} |
| 39 | h5 | Empresa beneficiada | {} |
| 42 | label | Nombre de la empresa o negocio | {"for": "company_name"} |
| 43 | input | — | {"type": "text", "id": "company_name", "maxlength": "255", "required": true} |
| 46 | label | Giro | {"for": "company_giro"} |
| 47 | input | — | {"type": "text", "id": "company_giro", "maxlength": "255", "required": true} |
| 50 | label | Persona a cargo | {"for": "company_contact_name"} |
| 51 | input | — | {"type": "text", "id": "company_contact_name", "maxlength": "255", "required": true} |
| 54 | label | Puesto de la persona a cargo | {"for": "company_contact_position"} |
| 55 | input | — | {"type": "text", "id": "company_contact_position", "maxlength": "255", "required": true} |
| 58 | label | Dirección de la empresa o negocio | {"for": "company_address"} |
| 59 | textarea | — | {"id": "company_address", "rows": "2", "maxlength": "1000", "required": true} |
| 66 | label | Semestre | {"for": "semestre"} |
| 67 | select | — | {"id": "semestre", "name": "semestre", "required": true} |
| 76 | label | Año | {"for": "year"} |
| 77 | input | — | {"type": "number", "id": "year", "name": "year", "min": "2000", "max": "2100", "required": true} |
| 80 | label | Grupo / carga de asignaturas | {"for": "subject_group_id"} |
| 81 | select | — | {"id": "subject_group_id", "name": "subject_group_id", "required": true} |
| 87 | label | Integrantes estudiantes | {"for": "studentSearch"} |
| 89 | select | — | {"id": "studentSearch"} |
| 92 | button | Agregar | {"type": "button", "onclick": "addStudentFromSearch()"} |
| 97 | button | Guardar | {"type": "submit"} |
| 98 | a | Cancelar | {"href": "/pages/admin/projects.php"} |
| 168 | button | Quitar | {"type": "button", "aria-label": "Quitar", "onclick": "removeStudent('${escapeHtml(student.id)}')"} |

## pages/admin/project-edit.php

[Fuente](../../pages/admin/project-edit.php) · [Código completo](REFERENCIA.md#pages-admin-project-edit-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 32 | h1 | Editar Proyecto | {} |
| 36 | form | — | {"id": "projectForm"} |
| 38 | label | Titulo del Proyecto | {"for": "title"} |
| 39 | input | — | {"type": "text", "id": "title", "name": "title", "required": true} |
| 42 | label | Descripción | {"for": "description"} |
| 43 | textarea | — | {"id": "description", "name": "description", "rows": "4", "required": true} |
| 45 | h5 | Empresa beneficiada | {} |
| 48 | label | Nombre de la empresa o negocio | {"for": "company_name"} |
| 49 | input | — | {"type": "text", "id": "company_name", "maxlength": "255", "required": true} |
| 52 | label | Giro | {"for": "company_giro"} |
| 53 | input | — | {"type": "text", "id": "company_giro", "maxlength": "255", "required": true} |
| 56 | label | Persona a cargo | {"for": "company_contact_name"} |
| 57 | input | — | {"type": "text", "id": "company_contact_name", "maxlength": "255", "required": true} |
| 60 | label | Puesto de la persona a cargo | {"for": "company_contact_position"} |
| 61 | input | — | {"type": "text", "id": "company_contact_position", "maxlength": "255", "required": true} |
| 64 | label | Dirección de la empresa o negocio | {"for": "company_address"} |
| 65 | textarea | — | {"id": "company_address", "rows": "2", "maxlength": "1000", "required": true} |
| 72 | label | Semestre | {"for": "semestre"} |
| 73 | select | — | {"id": "semestre", "name": "semestre", "required": true} |
| 82 | label | Año | {"for": "year"} |
| 83 | input | — | {"type": "number", "id": "year", "name": "year", "min": "2000", "max": "2100", "required": true} |
| 86 | label | Grupo / carga de asignaturas | {"for": "subject_group_id"} |
| 87 | select | — | {"id": "subject_group_id", "name": "subject_group_id", "required": true} |
| 93 | label | Integrantes estudiantes | {"for": "studentSearch"} |
| 95 | select | — | {"id": "studentSearch"} |
| 98 | button | Agregar | {"type": "button", "onclick": "addStudentFromSearch()"} |
| 103 | button | Guardar Cambios | {"type": "submit"} |
| 104 | a | Cancelar | {"href": "/pages/admin/projects.php"} |
| 176 | button | Quitar | {"type": "button", "aria-label": "Quitar", "onclick": "removeStudent('${escapeHtml(student.id)}')"} |

## pages/admin/projects.php

[Fuente](../../pages/admin/projects.php) · [Código completo](REFERENCIA.md#pages-admin-projects-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Gestión de Proyectos y Tesis | {} |
| 31 | button | Plantilla Excel | {"type": "button", "onclick": "downloadProjectsExcelTemplate()"} |
| 34 | button | Cargar Excel | {"type": "button", "onclick": "openProjectsImportModal()"} |
| 37 | button | Nuevo Proyecto | {"type": "button", "id": "newProjectBtn", "onclick": "openProjectModal()"} |
| 47 | button | Proyecto integrador | {"type": "button", "id": "integratorViewBtn", "onclick": "setProjectManagementView('integrator')"} |
| 50 | button | Modalidad Dual | {"type": "button", "id": "dualViewBtn", "onclick": "setProjectManagementView('dual')"} |
| 53 | button | Caso integrador | {"type": "button", "id": "caseViewBtn", "onclick": "setProjectManagementView('case')"} |
| 56 | button | Tesis | {"type": "button", "id": "thesisViewBtn", "onclick": "setProjectManagementView('thesis')"} |
| 63 | label | Filtrar por semestre | {"for": "semesterFilter"} |
| 64 | select | — | {"id": "semesterFilter", "onchange": "loadProjects(1)"} |
| 69 | label | Buscar proyecto | {"for": "projectSearchInput"} |
| 72 | input | Proyecto, estudiante, asesor, empresa o año | {"type": "search", "id": "projectSearchInput", "placeholder": "Proyecto, estudiante, asesor, empresa o año", "oninput": "scheduleProjectsSearch()"} |
| 73 | button | Limpiar búsqueda | {"type": "button", "onclick": "clearProjectsSearch()", "title": "Limpiar búsqueda"} |
| 138 | form | — | {"id": "projectsImportForm", "onsubmit": "importProjectsExcel(event)"} |
| 140 | h5 | Cargar proyectos desde Excel | {} |
| 141 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 145 | label | Archivo de plantilla | {"for": "projectsImportFile"} |
| 146 | input | — | {"type": "file", "id": "projectsImportFile", "accept": ".xls,.xlsx,.csv", "required": true} |
| 150 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 151 | button | Importar | {"type": "submit", "id": "projectsImportBtn"} |
| 160 | form | — | {"id": "projectModalForm", "onsubmit": "saveProjectModal(event)"} |
| 162 | h5 | Nuevo proyecto | {"id": "projectModalTitle"} |
| 163 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 167 | input | — | {"type": "hidden", "id": "projectModalEditingId"} |
| 170 | label | Titulo del proyecto | {"for": "projectTitle"} |
| 171 | input | — | {"type": "text", "id": "projectTitle", "required": true} |
| 174 | label | Descripción | {"for": "projectDescription"} |
| 175 | textarea | — | {"id": "projectDescription", "rows": "4", "required": true} |
| 178 | label | Empresa o negocio | {"for": "projectCompanyName"} |
| 179 | input | — | {"type": "text", "id": "projectCompanyName", "maxlength": "255", "required": true} |
| 182 | label | RFC de la empresa | {"for": "projectCompanyRfc"} |
| 183 | input | — | {"type": "text", "id": "projectCompanyRfc", "maxlength": "13", "pattern": "[A-Za-z&amp;Ññ]{3,4}[0-9]{6}[A-Za-z0-9]{3}", "required": true} |
| 186 | label | Giro | {"for": "projectCompanyGiro"} |
| 187 | input | — | {"type": "text", "id": "projectCompanyGiro", "maxlength": "255", "required": true} |
| 190 | label | Persona a cargo | {"for": "projectCompanyContact"} |
| 191 | input | — | {"type": "text", "id": "projectCompanyContact", "maxlength": "255", "required": true} |
| 194 | label | Puesto | {"for": "projectCompanyPosition"} |
| 195 | input | — | {"type": "text", "id": "projectCompanyPosition", "maxlength": "255", "required": true} |
| 198 | label | Dirección de empresa | {"for": "projectCompanyAddress"} |
| 199 | textarea | — | {"id": "projectCompanyAddress", "rows": "2", "maxlength": "1000", "required": true} |
| 203 | label | Modalidad | {"for": "projectModality"} |
| 204 | select | — | {"id": "projectModality", "required": true} |
| 211 | label | Semestre | {"for": "projectSemester"} |
| 212 | select | — | {"id": "projectSemester", "onchange": "loadProjectModalGroups()", "required": true} |
| 222 | label | Año | {"for": "projectYear"} |
| 223 | input | — | {"type": "number", "id": "projectYear", "min": "2000", "max": "2100", "required": true} |
| 226 | label | Grupo / carga de asignaturas | {"for": "projectSubjectGroup"} |
| 227 | select | — | {"id": "projectSubjectGroup", "required": true} |
| 232 | label | Integrantes estudiantes sin proyecto | {"for": "projectStudentSelect"} |
| 233 | select | — | {"id": "projectStudentSelect"} |
| 238 | button | Agregar | {"type": "button", "onclick": "addProjectStudent()"} |
| 246 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 247 | button | Guardar | {"type": "submit", "id": "projectModalSaveBtn"} |
| 255 | form | — | {"onsubmit": "saveProjectSubjects(event)"} |
| 257 | h5 | Materias del proyecto | {} |
| 258 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 261 | input | — | {"type": "hidden", "id": "projectSubjectsId"} |
| 268 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 269 | button | Guardar materias | {"type": "submit"} |
| 433 | button | Ver detalles | {"onclick": "showProjectDetails(${project.id})", "title": "Ver detalles"} |
| 434 | a | Asignar comité | {"href": "/pages/admin/advisors.php?project=${project.id}&view=thesis", "title": "Asignar comité"} |
| 435 | button | Materias | {"type": "button", "onclick": "openProjectSubjectsModal(${project.id})", "title": "Materias"} |
| 436 | button | Editar tesis | {"type": "button", "onclick": "openProjectModal(${project.id}, true)", "title": "Editar tesis"} |
| 437 | button | Quitar de tesis | {"type": "button", "onclick": "toggleProjectThesis(${project.id}, false)", "title": "Quitar de tesis"} |
| 438 | button | Eliminar tesis | {"onclick": "deleteProject(${project.id})", "title": "Eliminar tesis"} |
| 528 | button | Quitar de tesis | {"type": "button", "onclick": "toggleProjectThesis(${project.id}, false)", "title": "Quitar de tesis"} |
| 529 | button | Marcar como tesis | {"type": "button", "onclick": "toggleProjectThesis(${project.id}, true)", "title": "Marcar como tesis"} |
| 541 | button | Ver detalles | {"onclick": "showProjectDetails(${project.id})", "title": "Ver detalles"} |
| 543 | button | Materias | {"type": "button", "onclick": "openProjectSubjectsModal(${project.id})", "title": "Materias"} |
| 544 | button | Editar | {"type": "button", "onclick": "openProjectModal(${project.id})", "title": "Editar"} |
| 545 | button | Eliminar | {"onclick": "deleteProject(${project.id})", "title": "Eliminar"} |
| 572 | button | Anterior | {"type": "button", "onclick": "loadProjects(${projectsCurrentPage - 1})"} |
| 580 | button | Siguiente | {"type": "button", "onclick": "loadProjects(${projectsCurrentPage + 1})"} |
| 698 | button | Quitar | {"type": "button", "aria-label": "Quitar", "onclick": "removeProjectStudent('${escapeHtml(student.id)}')"} |
| 834 | input | ${subject.id} | {"type": "checkbox", "value": "${subject.id}", "id": "projectSubject${subject.id}", "checked": true} |
| 835 | label | ${escapeHtml(subject.nombre)} ${escapeHtml(subject.clave &#124;&#124; '')} | {"for": "projectSubject${subject.id}"} |

## pages/admin/proposal-config.php

[Fuente](../../pages/admin/proposal-config.php) · [Código completo](REFERENCIA.md#pages-admin-proposal-config-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 24 | h1 | Gestión de Propuestas | {} |
| 27 | button | — | {"onclick": "loadConfig()"} |
| 33 | h5 | Docentes responsables por materia | {} |
| 41 | label | Materia a supervisar | {} |
| 42 | select | — | {"id": "subjectFilter", "onchange": "renderResponsibleTable()", "disabled": true} |
| 45 | label | Semestre | {} |
| 46 | select | — | {"id": "semesterFilter", "onchange": "renderResponsibleTable()"} |
| 55 | label | Buscar grupo | {} |
| 56 | input | Ej. 5to A, B, 2026 | {"id": "groupSearch", "placeholder": "Ej. 5to A, B, 2026", "oninput": "renderResponsibleTable()"} |
| 84 | a | Gestionar en Operación académica | {"href": "../academic-workflows.php"} |
| 88 | h5 | Ventanas de registro por grupo | {} |
| 192 | select | — | {"id": "teacher-${group.id}"} |
| 198 | button | Asignar docente | {"onclick": "assignTeacher(${group.id})", "title": "Asignar docente"} |
| 199 | button | Quitar ${esc(item.teacher?.nombres &#124;&#124; 'docente')} | {"onclick": "deleteAssignment('${item.id}')", "title": "Quitar ${esc(item.teacher?.nombres &#124;&#124; 'docente')}"} |
| 216 | button | — | {"onclick": "deleteWindow(${windowItem.id})"} |
| 228 | h6 | Ventanas de registro | {} |
| 231 | input | — | {"type": "datetime-local", "id": "start-${group.id}"} |
| 232 | input | — | {"type": "datetime-local", "id": "end-${group.id}"} |
| 233 | button | — | {"onclick": "addWindow(${group.id})"} |

## pages/admin/settings.php

[Fuente](../../pages/admin/settings.php) · [Código completo](REFERENCIA.md#pages-admin-settings-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Ajustes Generales | {} |
| 31 | button | — | {"onclick": "loadSettings()"} |
| 38 | form | — | {"id": "settingsForm"} |
| 40 | h5 | Configuración del sistema | {} |
| 45 | label | Inactividad antes de cerrar sesión | {"for": "session_timeout_minutes"} |
| 47 | input | — | {"type": "number", "id": "session_timeout_minutes", "min": "1", "max": "480", "required": true} |
| 52 | label | Tema por defecto | {"for": "default_theme"} |
| 53 | select | — | {"id": "default_theme", "required": true} |
| 60 | label | Tamaño de fuente general | {"for": "font_scale"} |
| 62 | input | — | {"type": "number", "id": "font_scale", "min": "85", "max": "125", "step": "5", "required": true} |
| 67 | label | Tamaño máximo de archivo | {"for": "max_file_size_mb"} |
| 69 | input | — | {"type": "number", "id": "max_file_size_mb", "min": "1", "max": "200", "required": true} |
| 74 | label | Máximo de integrantes por proyecto | {"for": "max_project_members"} |
| 75 | input | — | {"type": "number", "id": "max_project_members", "min": "1", "max": "10", "required": true} |
| 78 | label | Tipos de archivo permitidos | {} |
| 82 | input | — | {"type": "checkbox"} |
| 83 | label | "&gt; | {} |
| 89 | label | Aviso global del sistema | {"for": "global_notice"} |
| 90 | textarea | Mensaje visible para usuarios en la parte superior del sistema | {"id": "global_notice", "rows": "3", "maxlength": "1000", "placeholder": "Mensaje visible para usuarios en la parte superior del sistema"} |
| 94 | input | — | {"type": "checkbox", "role": "switch", "id": "proposal_registration_enabled"} |
| 95 | label | Activar registro de propuestas | {"for": "proposal_registration_enabled"} |
| 100 | input | — | {"type": "checkbox", "role": "switch", "id": "grayscale_mode", "onchange": "previewGrayscaleMode()"} |
| 101 | label | Mostrar el sistema en escala de grises | {"for": "grayscale_mode"} |
| 108 | button | Guardar ajustes | {} |

## pages/admin/user-create.php

[Fuente](../../pages/admin/user-create.php) · [Código completo](REFERENCIA.md#pages-admin-user-create-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Crear Nuevo Usuario | {} |
| 34 | form | — | {"id": "userForm"} |
| 37 | label | Semestre | {"for": "semestre"} |
| 38 | select | — | {"id": "semestre", "name": "semestre"} |
| 48 | label | Grupo | {"for": "grupo"} |
| 49 | select | — | {"id": "grupo", "name": "grupo"} |
| 57 | label | No. de Control, No. de empleado | {"for": "id"} |
| 58 | input | — | {"type": "text", "id": "id", "name": "id", "required": true} |
| 62 | label | Email | {"for": "email"} |
| 63 | input | — | {"type": "email", "id": "email", "name": "email", "required": true} |
| 67 | label | Teléfono | {"for": "telefonos"} |
| 68 | input | — | {"type": "tel", "id": "telefonos", "name": "telefonos", "maxlength": "200", "required": true} |
| 72 | label | Perfil | {"for": "perfil_id"} |
| 73 | select | — | {"id": "perfil_id", "name": "perfil_id", "required": true} |
| 88 | label | Nombres | {"for": "nombres"} |
| 89 | input | — | {"type": "text", "id": "nombres", "name": "nombres", "required": true} |
| 93 | label | Apellido Paterno | {"for": "apa"} |
| 94 | input | — | {"type": "text", "id": "apa", "name": "apa"} |
| 97 | label | Apellido Materno | {"for": "ama"} |
| 98 | input | — | {"type": "text", "id": "ama", "name": "ama"} |
| 104 | label | Dirección | {"for": "direccion"} |
| 105 | input | Calle, número, colonia, municipio | {"type": "text", "id": "direccion", "name": "direccion", "minlength": "10", "pattern": "(?=.*\\d)[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\\s#.,\\-\\/]+", "placeholder": "Calle, número, colonia, municipio"} |
| 113 | label | Contraseña | {"for": "password"} |
| 114 | input | — | {"type": "password", "id": "password", "name": "password", "required": true} |
| 118 | label | Confirmar Contraseña | {"for": "password_confirmation"} |
| 119 | input | — | {"type": "password", "id": "password_confirmation", "name": "password_confirmation", "required": true} |
| 125 | button | Guardar | {"type": "submit"} |
| 128 | a | Cancelar | {"href": "/pages/admin/users.php"} |
| 180 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |
| 190 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |

## pages/admin/user-edit.php

[Fuente](../../pages/admin/user-edit.php) · [Código completo](REFERENCIA.md#pages-admin-user-edit-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 34 | h1 | Editar Usuario | {} |
| 40 | form | — | {"id": "userForm"} |
| 43 | label | Dirección | {"for": "direccion"} |
| 44 | input | Calle, número, colonia, municipio | {"type": "text", "id": "direccion", "name": "direccion", "minlength": "10", "pattern": "(?=.*\\d)[A-Za-zÁÉÍÓÚbÜñáéíóúüñ0-9\\s#.,\\-\\/]+", "placeholder": "Calle, número, colonia, municipio"} |
| 52 | label | Semestre | {"for": "semestre"} |
| 53 | select | — | {"id": "semestre", "name": "semestre"} |
| 63 | label | Grupo | {"for": "grupo"} |
| 64 | select | — | {"id": "grupo", "name": "grupo"} |
| 72 | label | No. de Control, No. de empleado | {"for": "id"} |
| 73 | input | — | {"type": "text", "id": "id", "name": "id", "readonly": true} |
| 76 | label | Email | {"for": "email"} |
| 77 | input | — | {"type": "email", "id": "email", "name": "email", "required": true} |
| 80 | label | Teléfono | {"for": "telefonos"} |
| 81 | input | — | {"type": "tel", "id": "telefonos", "name": "telefonos", "maxlength": "200", "required": true} |
| 84 | label | Perfil | {"for": "perfil_id"} |
| 85 | select | — | {"id": "perfil_id", "name": "perfil_id", "required": true} |
| 95 | label | Estado | {"for": "activo"} |
| 96 | select | — | {"id": "activo", "name": "activo", "required": true} |
| 105 | label | Nombres | {"for": "nombres"} |
| 106 | input | — | {"type": "text", "id": "nombres", "name": "nombres", "required": true} |
| 109 | label | Apellido Paterno | {"for": "apa"} |
| 110 | input | — | {"type": "text", "id": "apa", "name": "apa"} |
| 113 | label | Apellido Materno | {"for": "ama"} |
| 114 | input | — | {"type": "text", "id": "ama", "name": "ama"} |
| 120 | label | Contraseña (dejar vacío para no cambiar) | {"for": "password"} |
| 121 | input | — | {"type": "password", "id": "password", "name": "password"} |
| 124 | label | Confirmar Contraseña | {"for": "password_confirmation"} |
| 125 | input | — | {"type": "password", "id": "password_confirmation", "name": "password_confirmation"} |
| 130 | button | Guardar Cambios | {"type": "submit"} |
| 133 | a | Cancelar | {"href": "/pages/admin/users.php"} |
| 191 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |
| 243 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |
| 253 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |

## pages/admin/users.php

[Fuente](../../pages/admin/users.php) · [Código completo](REFERENCIA.md#pages-admin-users-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 28 | h1 | Gestión de Usuarios | {} |
| 32 | button | Plantilla Excel | {"type": "button", "onclick": "downloadUsersExcelTemplate()"} |
| 35 | button | Cargar Excel | {"type": "button", "onclick": "openUsersImportModal()"} |
| 38 | button | Enviar credenciales | {"type": "button", "id": "credentialEmailOpenBtn", "onclick": "openCredentialEmailModal()"} |
| 41 | button | Nuevo Usuario | {"type": "button", "onclick": "openUserModal()"} |
| 47 | button | Opciones y filtros Activos · Todos los perfiles | {"type": "button", "id": "usersMobileOptionsToggle", "aria-controls": "usersMobileOptions", "aria-expanded": "false"} |
| 66 | button | Plantilla Excel | {"type": "button", "onclick": "downloadUsersExcelTemplate()"} |
| 69 | button | Cargar Excel | {"type": "button", "onclick": "openUsersImportModal()"} |
| 72 | button | Credenciales | {"type": "button", "onclick": "openCredentialEmailModal()"} |
| 75 | button | Nuevo Usuario | {"type": "button", "onclick": "openUserModal()"} |
| 83 | button | Activos | {"type": "button", "id": "filterActive", "onclick": "setStatusFilter('active')"} |
| 84 | button | Inactivos | {"type": "button", "id": "filterInactive", "onclick": "setStatusFilter('inactive')"} |
| 85 | button | Todos | {"type": "button", "id": "filterAll", "onclick": "setStatusFilter('all')"} |
| 92 | button | Todos los perfiles | {"type": "button", "id": "profileAll", "onclick": "setProfileFilter('all')"} |
| 93 | button | Estudiantes | {"type": "button", "id": "profileStudents", "onclick": "setProfileFilter('3')"} |
| 94 | button | Docentes | {"type": "button", "id": "profileTeachers", "onclick": "setProfileFilter('2')"} |
| 95 | button | Administrativos | {"type": "button", "id": "profileAdmins", "onclick": "setProfileFilter('1')"} |
| 102 | label | Buscar usuario | {"for": "userSearchInput"} |
| 105 | input | No. de Control, No. de empleado, nombre, correo o teléfono | {"type": "search", "id": "userSearchInput", "placeholder": "No. de Control, No. de empleado, nombre, correo o teléfono", "oninput": "scheduleUsersSearch()"} |
| 106 | button | Limpiar búsqueda | {"type": "button", "onclick": "clearUsersSearch()", "title": "Limpiar búsqueda"} |
| 113 | label | Semestre | {"for": "semesterFilter"} |
| 114 | select | — | {"id": "semesterFilter", "onchange": "loadGroupsForFilter(); loadUsers(1);"} |
| 124 | label | Grupo | {"for": "groupFilter"} |
| 125 | select | — | {"id": "groupFilter", "onchange": "loadUsers(1)"} |
| 130 | button | Control de grupos | {"onclick": "openGroupControl()"} |
| 140 | button | Seleccionar página | {"type": "button", "onclick": "selectVisibleUsers()"} |
| 143 | button | Limpiar selección | {"type": "button", "onclick": "clearSelectedUsers()"} |
| 146 | button | Cancelar | {"type": "button", "onclick": "cancelCredentialSelectionMode()"} |
| 157 | input | Seleccionar usuarios visibles | {"type": "checkbox", "id": "selectVisibleUsersCheckbox", "onchange": "toggleVisibleUsers(this.checked)", "title": "Seleccionar usuarios visibles", "checked": true} |
| 183 | form | — | {"id": "usersImportForm", "onsubmit": "importUsersExcel(event)"} |
| 185 | h5 | Cargar usuarios desde Excel | {} |
| 186 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 190 | label | Archivo de plantilla | {"for": "usersImportFile"} |
| 191 | input | — | {"type": "file", "id": "usersImportFile", "accept": ".xls,.xlsx,.csv", "required": true} |
| 195 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 196 | button | Importar | {"type": "submit", "id": "usersImportBtn"} |
| 204 | form | — | {"id": "credentialEmailForm", "onsubmit": "sendCredentialEmails(event)"} |
| 206 | h5 | Enviar credenciales | {} |
| 207 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 216 | label | Destinatarios | {} |
| 218 | label | Solo usuarios seleccionados Selecciona usuarios en la tabla para usar esta opción. | {} |
| 219 | input | selected | {"type": "radio", "name": "credentialEmailMode", "id": "credentialEmailModeSelected", "value": "selected"} |
| 223 | label | Todos los usuarios del filtro actual Usa perfil, estado, búsqueda, semestre y grupo actuales. | {} |
| 224 | input | filtered | {"type": "radio", "name": "credentialEmailMode", "id": "credentialEmailModeFiltered", "value": "filtered", "checked": true} |
| 231 | label | Perfiles para envío por filtro | {} |
| 233 | label | Administrativos | {} |
| 234 | input | 1 | {"type": "checkbox", "value": "1", "onchange": "refreshCredentialScopeText()", "checked": true} |
| 237 | label | Docentes | {} |
| 238 | input | 2 | {"type": "checkbox", "value": "2", "onchange": "refreshCredentialScopeText()", "checked": true} |
| 241 | label | Estudiantes | {} |
| 242 | input | 3 | {"type": "checkbox", "value": "3", "onchange": "refreshCredentialScopeText()", "checked": true} |
| 249 | label | Asunto | {"for": "credentialEmailSubject"} |
| 250 | input | — | {"type": "text", "id": "credentialEmailSubject", "maxlength": "150", "required": true} |
| 253 | label | Base del correo | {"for": "credentialEmailBody"} |
| 254 | textarea | — | {"id": "credentialEmailBody", "rows": "9", "maxlength": "5000", "required": true} |
| 258 | input | — | {"type": "checkbox", "id": "credentialEmailRotatePassword", "checked": true} |
| 259 | label | Generar contraseña temporal nueva para cada destinatario | {"for": "credentialEmailRotatePassword"} |
| 264 | label | Contraseña del administrador actual | {"for": "credentialAdminPassword"} |
| 265 | input | — | {"type": "password", "id": "credentialAdminPassword", "autocomplete": "current-password"} |
| 271 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 272 | button | Enviar correos | {"type": "submit", "id": "credentialEmailBtn"} |
| 281 | form | — | {"id": "userModalForm", "onsubmit": "saveUserModal(event)"} |
| 283 | h5 | Nuevo usuario | {"id": "userModalTitle"} |
| 284 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 288 | input | — | {"type": "hidden", "id": "userModalEditingId"} |
| 291 | label | No. de Control, No. de empleado | {"for": "userId"} |
| 292 | input | — | {"type": "text", "id": "userId", "maxlength": "10", "required": true} |
| 295 | label | Email | {"for": "userEmail"} |
| 296 | input | — | {"type": "email", "id": "userEmail", "required": true} |
| 299 | label | Telefono | {"for": "userPhone"} |
| 300 | input | — | {"type": "tel", "id": "userPhone", "maxlength": "200", "required": true} |
| 303 | label | Perfil | {"for": "userProfile"} |
| 304 | select | — | {"id": "userProfile", "onchange": "toggleUserStudentFields()", "required": true} |
| 316 | label | Semestre | {"for": "userSemester"} |
| 317 | select | — | {"id": "userSemester", "onchange": "loadUserModalGroups()"} |
| 323 | label | Grupo | {"for": "userGroup"} |
| 324 | select | — | {"id": "userGroup"} |
| 329 | label | Estado | {"for": "userActive"} |
| 330 | select | — | {"id": "userActive", "required": true} |
| 336 | label | Nombres | {"for": "userNames"} |
| 337 | input | — | {"type": "text", "id": "userNames", "required": true} |
| 340 | label | Apellido paterno | {"for": "userApa"} |
| 341 | input | — | {"type": "text", "id": "userApa"} |
| 344 | label | Apellido materno | {"for": "userAma"} |
| 345 | input | — | {"type": "text", "id": "userAma"} |
| 348 | label | Dirección | {"for": "userAddress"} |
| 349 | input | Calle, número, colonia, municipio | {"type": "text", "id": "userAddress", "minlength": "10", "pattern": "(?=.*\\d)[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\\s#.,\\-\\/]+", "placeholder": "Calle, número, colonia, municipio"} |
| 353 | label | Contraseña | {"for": "userPassword"} |
| 354 | input | — | {"type": "password", "id": "userPassword", "minlength": "6"} |
| 357 | label | Confirmar contraseña | {"for": "userPasswordConfirmation"} |
| 358 | input | — | {"type": "password", "id": "userPasswordConfirmation", "minlength": "6"} |
| 363 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 364 | button | Guardar | {"type": "submit", "id": "userModalSaveBtn"} |
| 375 | h5 | Control de grupos de estudiantes | {} |
| 376 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 381 | label | Semestre | {} |
| 382 | select | — | {"id": "controlSemester", "onchange": "loadControlGroups()"} |
| 387 | button | Nuevo grupo | {"type": "button", "onclick": "createControlGroup()"} |
| 401 | form | — | {"id": "groupForm", "onsubmit": "saveControlGroup(event)"} |
| 403 | h5 | Nuevo grupo | {"id": "groupFormTitle"} |
| 404 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 409 | label | Nombre | {"for": "groupFormName"} |
| 410 | input | Ej. Desarrollo de proyectos 5C | {"type": "text", "id": "groupFormName", "maxlength": "255", "placeholder": "Ej. Desarrollo de proyectos 5C", "required": true} |
| 414 | label | Semestre | {"for": "groupFormSemester"} |
| 415 | select | — | {"id": "groupFormSemester", "required": true} |
| 424 | label | Grupo | {"for": "groupFormCode"} |
| 425 | input | Ej. C | {"type": "text", "id": "groupFormCode", "maxlength": "20", "placeholder": "Ej. C", "required": true} |
| 429 | label | Periodo | {"for": "groupFormPeriod"} |
| 430 | input | Ej. Enero-Junio 2026 | {"type": "text", "id": "groupFormPeriod", "maxlength": "100", "placeholder": "Ej. Enero-Junio 2026"} |
| 437 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 438 | button | Guardar | {"type": "submit", "id": "groupFormSaveBtn"} |
| 544 | button | Administrador protegido | {"title": "Administrador protegido", "disabled": true} |
| 545 | button | ${toggleTitle} | {"onclick": "toggleUserStatus('${escapeHtml(user.id)}')", "title": "${toggleTitle}"} |
| 547 | button | Acción protegida | {"onclick": "protectedAdminAction('${escapeHtml(user.id)}')", "title": "Acción protegida"} |
| 553 | input | Seleccionar usuario ${escapeHtml(user.id)} | {"type": "checkbox", "value": "${escapeHtml(user.id)}", "onchange": "toggleUserSelection('${escapeHtml(user.id)}', this.checked)", "aria-label": "Seleccionar usuario ${escapeHtml(user.id)}", "checked": true} |
| 564 | button | Editar | {"type": "button", "onclick": "openUserModal('${escapeHtml(user.id)}')", "title": "Editar"} |
| 659 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |
| 708 | button | Anterior | {"type": "button", "onclick": "loadUsers(${current - 1})"} |
| 712 | button | Siguiente | {"type": "button", "onclick": "loadUsers(${current + 1})"} |
| 721 | button | ${page} | {"type": "button", "onclick": "loadUsers(${page})"} |
| 932 | button | Editar grupo | {"type": "button", "onclick": "editControlGroup(${group.id})", "title": "Editar grupo"} |
| 933 | button | Eliminar grupo | {"type": "button", "onclick": "deleteControlGroup(${group.id}, ${members.length})", "title": "Eliminar grupo"} |
| 939 | select | — | {"id": "addStudent-${group.id}"} |
| 943 | button | — | {"onclick": "assignStudentToGroup(${group.id})"} |
| 945 | button | — | {"type": "button", "onclick": "openUserModal('${escapeHtml(student.id)}')"} |
| 945 | button | Quitar del grupo | {"onclick": "removeStudentFromGroup('${escapeHtml(student.id)}')", "title": "Quitar del grupo"} |
| 1379 | button | — | {"type": "button", "data-bs-dismiss": "alert"} |

## pages/evaluation-documents.php

[Fuente](../../pages/evaluation-documents.php) · [Código completo](REFERENCIA.md#pages-evaluation-documents-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 57 | h1 | Documentos de evaluación | {} |
| 60 | button | Actualizar | {"type": "button", "onclick": "loadDocuments(true)"} |
| 124 | h4 | Semestre ${semester} | {} |
| 130 | h5 | ${esc(project.project.title)} | {} |
| 146 | h5 | Sin proyectos disponibles | {} |
| 150 | button | Entregas de evaluación | {"onclick": "setDocumentSection('evaluations')"} |
| 153 | button | Tesis y residencias | {"onclick": "setDocumentSection('thesis')"} |
| 159 | h3 | Entregas de evaluación | {} |
| 167 | h3 | Tesis y residencias de 9no | {} |
| 172 | h5 | Sin avances cargados | {} |
| 198 | h5 | ${title} | {} |
| 205 | button | Descargar | {"onclick": "downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')"} |
| 211 | input | — | {"type": "file", "id": "${type}-file-${project.project.id}", "accept": "${accepted}"} |
| 213 | button | ${delivery.uploaded ? 'Reemplazar archivo' : 'Subir archivo'} | {"onclick": "uploadEvaluationDelivery(${project.project.id}, '${type}')"} |
| 229 | h6 | Alumnos liberados | {} |
| 231 | label | ${esc(fullName(student))} | {} |
| 234 | input | — | {"type": "checkbox", "data-student-id": "${escAttr(student.id)}", "disabled": true, "checked": true} |
| 241 | button | Guardar liberacion | {"onclick": "saveReleaseReview(${documentId(delivery)})"} |
| 255 | h6 | Subir avance de 9no | {} |
| 258 | select | — | {"id": "thesisTipo"} |
| 264 | input | Nombre del avance | {"id": "thesisName", "maxlength": "255", "placeholder": "Nombre del avance"} |
| 267 | input | Descripción breve | {"id": "thesisDesc", "maxlength": "5000", "placeholder": "Descripción breve"} |
| 270 | input | Autores | {"id": "thesisAuthors", "maxlength": "1000", "placeholder": "Autores"} |
| 273 | input | — | {"type": "file", "id": "thesisFile", "accept": ".pdf,.doc,.docx"} |
| 277 | button | Guardar avance | {"type": "button", "onclick": "uploadThesisDocument()"} |
| 301 | h6 | ${esc(document.nombre)} | {} |
| 313 | button | Descargar | {"type": "button", "onclick": "downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')"} |
| 314 | button | ${document.visibility === 'public' ? 'Privado' : 'Publicar'} | {"type": "button", "onclick": "toggleRepositoryPublication(${document.id}, ${document.visibility === 'public' ? 'false' : 'true'})"} |

## pages/profile.php

[Fuente](../../pages/profile.php) · [Código completo](REFERENCIA.md#pages-profile-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 23 | h1 | Mi Perfil | {} |
| 27 | form | — | {"id": "profileForm", "enctype": "multipart/form-data"} |
| 32 | input | — | {"type": "file", "name": "photo", "accept": "image/*"} |
| 37 | label | Nombre | {} |
| 37 | input | — | {"id": "nombres", "disabled": true} |
| 38 | label | Apellido paterno | {} |
| 38 | input | — | {"id": "apa", "disabled": true} |
| 39 | label | Apellido materno | {} |
| 39 | input | — | {"id": "ama", "disabled": true} |
| 40 | label | Correo | {} |
| 40 | input | — | {"id": "email", "disabled": true} |
| 41 | label | Semestre | {} |
| 41 | select | — | {"name": "semestre", "id": "semestre"} |
| 42 | label | Grupo | {} |
| 42 | input | — | {"name": "grupo", "id": "grupo"} |
| 43 | label | Teléfono | {} |
| 43 | input | — | {"name": "telefonos", "id": "telefonos"} |
| 44 | label | Dirección | {} |
| 44 | input | — | {"name": "direccion", "id": "direccion"} |
| 45 | label | Contraseña actual | {} |
| 45 | input | — | {"type": "password", "name": "current_password"} |
| 46 | label | Nueva contraseña | {} |
| 46 | input | — | {"type": "password", "name": "password"} |
| 47 | label | Confirmar contraseña | {} |
| 47 | input | — | {"type": "password", "name": "password_confirmation"} |
| 51 | button | Guardar cambios | {} |

## pages/repositorio-detail.php

[Fuente](../../pages/repositorio-detail.php) · [Código completo](REFERENCIA.md#pages-repositorio-detail-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 21 | a | Repositorio | {"href": "/pages/repositorio.php"} |
| 77 | h1 | ${esc(doc.nombre &#124;&#124; doc.titulo &#124;&#124; 'Documento sin título')} | {} |
| 82 | a | Descargar documento | {"href": "${downloadUrl}"} |
| 89 | h6 | Vista previa | {} |
| 106 | h6 | Detalles | {} |
| 115 | h6 | Etiquetas | {} |
| 121 | a | Volver | {"href": "/pages/repositorio.php"} |
| 138 | h2 | Documento no disponible | {} |
| 138 | a | Volver al repositorio | {"href": "/pages/repositorio.php"} |
| 215 | a | Descargar para leer | {"href": "${downloadUrl}"} |

## pages/repositorio.php

[Fuente](../../pages/repositorio.php) · [Código completo](REFERENCIA.md#pages-repositorio-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 20 | h1 | Repositorio Digital | {} |
| 28 | button | — | {"type": "button", "onclick": "openRepositoryUploadModal()"} |
| 38 | h2 | Documentos disponibles | {} |
| 45 | input | Buscar documentos por título, descripción o autor | {"type": "text", "id": "searchInput", "placeholder": "Buscar documentos...", "aria-label": "Buscar documentos por título, descripción o autor"} |
| 54 | select | — | {"id": "categoryFilter"} |
| 63 | select | — | {"id": "sortFilter"} |
| 93 | form | — | {"id": "repositoryUploadForm", "onsubmit": "submitRepositoryDocument(event)"} |
| 94 | input | — | {"type": "hidden", "id": "repoDocumentId", "name": "document_id"} |
| 96 | h5 | Agregar documento al repositorio | {"id": "repositoryModalTitle"} |
| 97 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 103 | label | Nombre del documento | {"for": "repoNombre"} |
| 104 | input | — | {"type": "text", "id": "repoNombre", "name": "nombre", "maxlength": "255", "required": true} |
| 107 | label | Descripción | {"for": "repoDescripcion"} |
| 108 | textarea | — | {"id": "repoDescripcion", "name": "descripcion", "rows": "3", "maxlength": "5000", "required": true} |
| 111 | label | Autores | {"for": "repoAutores"} |
| 112 | input | — | {"type": "text", "id": "repoAutores", "name": "autores", "maxlength": "1000", "required": true} |
| 116 | label | Proyecto asignado | {"for": "repoProjectId"} |
| 117 | select | — | {"id": "repoProjectId", "name": "project_id", "required": true} |
| 124 | label | Archivo | {"for": "repoArchivo"} |
| 125 | input | — | {"type": "file", "id": "repoArchivo", "name": "archivo", "accept": ".pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,.jpg,.jpeg,.png,.webp,.epub", "required": true} |
| 130 | label | Visibilidad | {"for": "repoVisibility"} |
| 131 | select | — | {"id": "repoVisibility", "name": "visibility"} |
| 137 | label | Estado de publicación | {} |
| 144 | input | private | {"type": "hidden", "name": "visibility", "value": "private"} |
| 148 | label | Etiquetas | {} |
| 155 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 156 | button | Subir documento | {"type": "submit", "id": "repoUploadBtn"} |
| 210 | h3 | No encontramos documentos | {} |
| 224 | button | Editar | {"type": "button", "onclick": "openRepositoryEditModal(${Number(doc.id)})"} |
| 227 | button | Eliminar | {"type": "button", "onclick": "deleteRepositoryDocument(${Number(doc.id)})"} |
| 237 | h3 | ${escapeHtml(doc.nombre &#124;&#124; doc.titulo &#124;&#124; 'Documento sin título')} | {} |
| 251 | a | Ver detalles | {"href": "/pages/repositorio-detail.php?id=${encodeURIComponent(doc.id)}"} |
| 269 | h3 | No fue posible cargar el repositorio | {} |
| 269 | button | Reintentar | {"type": "button", "onclick": "loadDocumentos(currentPage)"} |
| 286 | button | Página anterior | {"type": "button", "onclick": "loadDocumentos(${current - 1})", "aria-label": "Página anterior", "disabled": true} |
| 287 | button | ${page} | {"type": "button", "onclick": "loadDocumentos(${page})"} |
| 288 | button | Página siguiente | {"type": "button", "onclick": "loadDocumentos(${current + 1})", "aria-label": "Página siguiente", "disabled": true} |
| 301 | label | ${escapeHtml(tag.nombre)} | {} |
| 302 | input | ${escapeHtml(tag.id)} | {"type": "checkbox", "value": "${escapeHtml(tag.id)}", "checked": true} |

## pages/student/dashboard.php

[Fuente](../../pages/student/dashboard.php) · [Código completo](REFERENCIA.md#pages-student-dashboard-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 35 | h1 | Panel del Estudiante | {} |
| 46 | a | Inicio | {"href": "/index.php"} |
| 60 | a | Ver entregables | {"href": "/pages/student/my-deliverables.php", "id": "studentNextActionLink"} |
| 69 | a | Mis proyectos 0 0 propuestas pendientes | {"href": "/pages/student/proposal-register.php", "aria-label": "Ir a registrar o consultar proyecto"} |
| 86 | a | Entregables completados 0 | {"href": "/pages/student/my-deliverables.php", "aria-label": "Ir a mis entregables"} |
| 103 | a | Entregables pendientes 0 Por enviar o revisar | {"href": "/pages/student/my-deliverables.php", "aria-label": "Ir a entregables pendientes"} |
| 123 | h5 | Mi avance | {} |
| 132 | h5 | Entregables | {} |
| 138 | h5 | Propuestas | {} |
| 150 | h5 | Mis Proyectos Activos | {} |
| 151 | a | Ir a proyecto | {"href": "/pages/student/proposal-register.php"} |
| 165 | h5 | Presentaciones programadas | {} |
| 182 | form | — | {"id": "initialProfileForm", "enctype": "multipart/form-data"} |
| 184 | h5 | Completar perfil inicial | {} |
| 188 | label | Nombre | {} |
| 188 | input | — | {"name": "nombres", "id": "initialNombres", "required": true} |
| 189 | label | Apellido paterno | {} |
| 189 | input | — | {"name": "apa", "id": "initialApa", "required": true} |
| 190 | label | Apellido materno | {} |
| 190 | input | — | {"name": "ama", "id": "initialAma"} |
| 191 | label | Semestre | {} |
| 191 | select | — | {"name": "semestre", "required": true} |
| 192 | label | Grupo | {} |
| 192 | input | A, B, 5to A | {"name": "grupo", "placeholder": "A, B, 5to A", "required": true} |
| 193 | label | Foto de perfil | {} |
| 193 | input | — | {"type": "file", "name": "photo", "accept": "image/*"} |
| 196 | button | Guardar y continuar | {} |
| 326 | a | ${escapeHtml(project.title)} ${progress}% Docente: ${escapeHtml(projectActiveAdvisors(project))} | {"href": "/pages/student/my-deliverables.php"} |

## pages/student/my-deliverables.php

[Fuente](../../pages/student/my-deliverables.php) · [Código completo](REFERENCIA.md#pages-student-my-deliverables-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 44 | h1 | Mis Entregables | {} |
| 65 | h5 | Subir Archivo - | {} |
| 66 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 70 | input | — | {"type": "hidden", "id": "deliverableIdSubir"} |
| 72 | label | Seleccionar archivo | {"for": "archivoInput"} |
| 73 | input | — | {"type": "file", "id": "archivoInput", "accept": ".pdf,.doc,.docx,.xls,.xlsx,.zip"} |
| 81 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 82 | button | Subir Archivo | {"type": "button", "onclick": "guardarArchivo()"} |
| 144 | button | Descargar Archivo | {"onclick": "descargarEntregable(${deliverable.id}, '${deliverable.nombre}', ${deliverable.project_id})"} |
| 168 | h5 | ${escapeHtml(deliverable.nombre)} | {} |
| 198 | button | Subir/Actualizar Archivo | {"onclick": "abrirSubirArchivo(${deliverable.id}, ${deliverable.project_id}, '${deliverable.nombre}')"} |

## pages/student/proposal-register.php

[Fuente](../../pages/student/proposal-register.php) · [Código completo](REFERENCIA.md#pages-student-proposal-register-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 7 | h1 | Registro de Proyecto | {} |
| 7 | form | — | {"id": "projectForm"} |
| 7 | label | Nombre del proyecto | {} |
| 7 | input | — | {"name": "title", "required": true} |
| 7 | label | Descripción | {} |
| 7 | textarea | — | {"name": "description", "rows": "4", "required": true} |
| 7 | label | Empresa o negocio | {} |
| 7 | input | — | {"name": "company_name", "required": true} |
| 7 | label | Giro | {} |
| 7 | input | — | {"name": "company_giro", "required": true} |
| 7 | label | Persona a cargo | {} |
| 7 | input | — | {"name": "company_contact_name", "required": true} |
| 7 | label | Puesto | {} |
| 7 | input | — | {"name": "company_contact_position", "required": true} |
| 7 | label | Dirección | {} |
| 7 | input | — | {"name": "company_address", "required": true} |
| 7 | label | Agregar integrante disponible | {} |
| 7 | input | Número de control o nombre | {"id": "studentSearch", "list": "studentsList", "placeholder": "Número de control o nombre"} |
| 7 | button | Agregar | {"type": "button", "onclick": "addSelectedStudent()"} |
| 7 | button | Registrar propuesta | {} |
| 10 | button | x.id!=='${esc(s.id)}');renderSelected()"&gt; | {"type": "button"} |

## pages/teacher/dashboard.php

[Fuente](../../pages/teacher/dashboard.php) · [Código completo](REFERENCIA.md#pages-teacher-dashboard-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 35 | h1 | Panel del Docente | {} |
| 46 | a | Inicio | {"href": "/index.php"} |
| 60 | a | Ver entregables | {"href": "/pages/teacher/my-deliverables.php", "id": "teacherNextActionLink"} |
| 69 | a | Mis proyectos 0 0 propuestas pendientes | {"href": "/pages/teacher/my-projects.php", "aria-label": "Ir a mis proyectos"} |
| 86 | a | Estudiantes asociados 0 En proyectos y grupos activos | {"href": "/pages/teacher/my-projects.php", "aria-label": "Ir a estudiantes asociados por proyecto"} |
| 103 | a | Entregables pendientes 0 | {"href": "/pages/teacher/my-deliverables.php", "aria-label": "Ir a entregables pendientes"} |
| 123 | h5 | Avance de revisión | {} |
| 132 | h5 | Entregables | {} |
| 138 | h5 | Propuestas | {} |
| 150 | h5 | Mis Proyectos Recientes | {} |
| 151 | a | Ver todos | {"href": "/pages/teacher/my-projects.php"} |
| 286 | a | ${escapeHtml(project.title)} ${progress}% ${escapeHtml(students &#124;&#124; 'Sin estudiantes asignados')} · ${escapeHtml(group)} · ${new Date(project.created_at).toLocaleDateString('es-MX')} | {"href": "/pages/teacher/my-projects.php"} |

## pages/teacher/my-deliverables.php

[Fuente](../../pages/teacher/my-deliverables.php) · [Código completo](REFERENCIA.md#pages-teacher-my-deliverables-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 30 | h1 | Entregables de mis proyectos | {} |
| 33 | button | Actualizar | {"onclick": "loadDeliverablesMatrix()"} |
| 42 | label | Buscar alumno | {"for": "studentSearch"} |
| 43 | input | No. de Control, No. de empleado, nombre o apellido | {"type": "search", "id": "studentSearch", "placeholder": "No. de Control, No. de empleado, nombre o apellido", "oninput": "debouncedLoad()"} |
| 46 | label | Materia | {"for": "subjectFilter"} |
| 47 | select | — | {"id": "subjectFilter", "onchange": "loadDeliverablesMatrix()"} |
| 64 | form | — | {"onsubmit": "saveGrade(event)"} |
| 66 | h5 | Calificar entregable | {} |
| 67 | button | — | {"type": "button", "data-bs-dismiss": "modal"} |
| 71 | input | — | {"type": "hidden", "id": "gradeDeliverableId"} |
| 72 | input | — | {"type": "hidden", "id": "gradeProjectId"} |
| 74 | label | Calificacion (0-100) | {"for": "gradeInput"} |
| 75 | input | — | {"type": "number", "id": "gradeInput", "min": "0", "max": "100", "step": "0.01", "required": true} |
| 79 | button | Cancelar | {"type": "button", "data-bs-dismiss": "modal"} |
| 80 | button | Guardar calificacion | {"type": "submit"} |
| 158 | h2 | ${escapeHtml(projectBlock.project.title)} | {} |
| 176 | h3 | ${escapeHtml(student.id)} - ${escapeHtml(fullName(student))} ${escapeHtml(student.semestre &#124;&#124; '-')} ${escapeHtml(student.grupo &#124;&#124; '')} ${summary.entregados &#124;&#124; 0} entregados ${summary.faltantes &#124;&#124; 0} faltantes Promedio válido: ${average} | {} |
| 177 | button | ${escapeHtml(student.id)} - ${escapeHtml(fullName(student))} ${escapeHtml(student.semestre &#124;&#124; '-')} ${escapeHtml(student.grupo &#124;&#124; '')} ${summary.entregados &#124;&#124; 0} entregados ${summary.faltantes &#124;&#124; 0} faltantes Promedio válido: ${average} | {"type": "button", "data-bs-toggle": "collapse", "data-bs-target": "#${collapseId}"} |
| 226 | button | Descargar | {"onclick": "descargarEntregable(${deliverable.id}, '${escapeHtml(deliverable.nombre)}', ${deliverable.project_id})", "title": "Descargar"} |
| 229 | button | Calificar | {"onclick": "openGradeModal(${deliverable.id}, ${deliverable.project_id}, '${escapeHtml(deliverable.nombre)}', ${grade === null &#124;&#124; grade === undefined ? 'null' : Number(grade)})", "title": "Calificar"} |

## pages/teacher/my-projects.php

[Fuente](../../pages/teacher/my-projects.php) · [Código completo](REFERENCIA.md#pages-teacher-my-projects-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 37 | h1 | Mis Proyectos | {} |
| 96 | h5 | ${escapeHtml(project.title)} | {} |
| 126 | a | Ver Proyecto | {"href": "/pages/admin/projects.php?id=${project.id}"} |

## pages/teacher/proposal-review.php

[Fuente](../../pages/teacher/proposal-review.php) · [Código completo](REFERENCIA.md#pages-teacher-proposal-review-php)

| Línea | Elemento | Texto / ayuda | Identificación, eventos y restricciones |
|---:|---|---|---|
| 24 | h1 | Revisión de Propuestas | {} |
| 27 | button | — | {"onclick": "loadPage()"} |
| 32 | h5 | Periodos de registro de mis grupos | {} |
| 40 | h4 | Propuestas asignadas | {} |
| 78 | h5 | ${esc(project.title)} | {} |
| 91 | button | Aprobar | {"onclick": "review(${project.id},'aprobado')"} |
| 92 | button | Solicitar cambios | {"onclick": "review(${project.id},'requiere_cambios')"} |
| 93 | button | Rechazar | {"onclick": "review(${project.id},'rechazado')"} |
| 113 | button | Eliminar periodo | {"onclick": "deleteWindow(${item.id})", "title": "Eliminar periodo"} |
| 120 | h6 | ${esc(group.nombre)} ${esc(group.grupo &#124;&#124; '')} | {} |
| 123 | input | — | {"type": "datetime-local", "id": "windowStart-${group.id}"} |
| 124 | input | — | {"type": "datetime-local", "id": "windowEnd-${group.id}"} |
| 125 | button | — | {"onclick": "addWindow(${group.id})"} |
