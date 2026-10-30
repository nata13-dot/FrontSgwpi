# Manual funcional y técnico completo de Frontend_Swgpi

**Fecha de revisión:** 23 de octubre de 2026, America/Mexico_City.  
**Proyecto:** `Frontend_Swgpi`.  
**Destinatarios:** usuarios, responsables académicos, soporte, pruebas y desarrollo.  
**Base:** archivos de la copia de trabajo; análisis estático y comprobaciones locales indicadas en [VERIFICACION.md](VERIFICACION.md).

**Versiones de lectura:** [Word](MANUAL_FRONTEND_SWGPI.docx) · [HTML con índice navegable](MANUAL_FRONTEND_SWGPI.html). Los anexos se conservan junto al manual.

## Cómo utilizar este documento

Esta guía explica qué puede hacer una persona en cada apartado, cómo se relacionan las pantallas y qué código interviene. Los anexos completan el detalle de los controles y permiten localizar una función sin recorrer todo el proyecto.

| Documento | Para qué consultarlo |
|---|---|
| Este manual | Comprender roles, pantallas, procesos, botones principales y restricciones. |
| [RUTAS.md](RUTAS.md) | Localizar todas las entradas PHP, sus parámetros y condiciones de acceso. |
| [INTERFACES.md](INTERFACES.md) | Consultar controles HTML, etiquetas, formularios, modales, enlaces y eventos por archivo. |
| [FUNCIONES.md](FUNCIONES.md) | Localizar declaraciones PHP/JavaScript, métodos, manejadores y llamadas de integración. |
| [REFERENCIA.md](REFERENCIA.md) | Consultar la instantánea del código propio de implementación y sus condiciones exactas. |
| [INVENTARIO.md](INVENTARIO.md) | Identificar archivos, tamaños y huellas del corte documentado. |
| [VERIFICACION.md](VERIFICACION.md) | Conocer qué se comprobó y qué no se probó en ejecución. |
| [cobertura.json](cobertura.json) | Procesar inventario y métricas de documentación. No representa cobertura de pruebas. |

**Alcance:** exclusivamente el cliente PHP/HTML/JavaScript `Frontend_Swgpi`. No se documenta `React_Swgpi` ni se repite la arquitectura interna de la API. Las solicitudes HTTP se mencionan sólo para explicar el resultado de los botones y la dependencia del frontend. El [informe previo de API](../../../ApiSwgpi_v2/docs/informe_2026-10-19/README.md) debe consultarse por separado para reglas autoritativas del servidor; ese enlace se resuelve desde la estructura de proyectos hermanos.

**Cómo interpretar las afirmaciones:** una pantalla o botón presente en el código es una capacidad expuesta por la interfaz, no una certificación de que el servidor la acepte en cualquier estado. La visibilidad depende de sesión, carrera, rol, permisos devueltos, configuración y datos. Los hallazgos al final distinguen inconsistencias del cliente y compatibilidad descrita en el informe previo. No se iniciaron sesiones con cuentas reales ni se enviaron correos, modificaron matrículas o ejecutaron operaciones institucionales.

## 1. Qué es este frontend

Es el portal web del Sistema de Gestión de Proyectos Integradores del ITSSMT. Permite consultar el repositorio, acceder con una cuenta institucional y realizar seguimiento de proyectos, propuestas, materias, evidencias, evaluaciones y residencias. Para administración incorpora gobierno de usuarios y carreras, configuración, auditoría, integridad, alertas y respaldos.

La aplicación está construida con páginas PHP que generan HTML y JavaScript que consulta la API. No requiere el proyecto React para mostrar estas páginas. Las rutas principales son archivos `.php`; los cambios de filtros y formularios suelen actualizar fragmentos dentro de la misma página. Las transiciones visuales no convierten el portal completo en una SPA.

La interfaz usa Bootstrap 5.3.0, Bootstrap Icons 1.11.0 y CSS propio. También carga SweetAlert2 para mensajes y confirmaciones, Axios en los flujos que lo necesitan y Mammoth para la vista previa de DOCX. Son dependencias declaradas en los enlaces del código; no se afirma que sean las versiones más recientes. Imágenes institucionales, fondos y variantes WebP/JPEG están en `assets/img/ITSSMT`.

### Vocabulario de uso

| Concepto | Significado en las pantallas |
|---|---|
| Carrera activa | Contexto institucional bajo el que se consultan y administran los datos. |
| Perfil activo | Papel que se utiliza para decidir accesos dentro de la carrera. Puede diferir de `perfil_id`. |
| Periodo | Calendario de trabajo académico y su estado operativo. |
| Matrícula | Ubicación del estudiante en un grupo del periodo. |
| Inscripción | Participación del estudiante en un curso/materia particular. |
| Grupo o carga | Agrupación por semestre, grupo y periodo con asignaturas asociadas. |
| Propuesta | Registro de proyecto sometido a revisión y, en su caso, correcciones. |
| Proyecto | Trabajo con integrantes, empresa, asignaturas y asesores. |
| Tesis | Clasificación de proyecto/documentación que aparece en vistas específicas. |
| Entregable | Actividad o evidencia académica con archivo y posible calificación. |
| Sala | Organización de presentaciones con docentes, responsable, orden y horario. |
| Rúbrica | Conjunto de preguntas/criterios y respuestas que sustentan una evaluación. |
| Residencia | Expediente individual, con requisitos y versiones documentales. |
| Publicar | Hacer visible un documento en el repositorio público, sujeto al servidor. |
| Archivar | Llevar una evaluación o sala al historial; no equivale a borrar. |

## 2. Roles, permisos y contexto de carrera

`includes/config.php` define siete perfiles. La mayoría de comprobaciones usa `active_profile_id` y recurre a `perfil_id` cuando falta. La condición de administrador general se calcula específicamente con el `perfil_id` base igual a 4.

| ID | Perfil | Alcance que expresa el cliente |
|---:|---|---|
| 1 | Administrador | Gestión de proyectos y académica, ajustes y módulos de carrera. |
| 2 | Docente | Proyectos asignados, propuestas, entregables, evaluaciones y seguimiento aplicable. |
| 3 | Estudiante | Propuesta, proyectos propios, entregables, documentos, inscripciones y residencia propia. |
| 4 | Administrador general | Gobierno institucional, cuentas, carreras, auditoría, integridad y continuidad; además funciones administrativas. |
| 5 | Jefe de Carrera | Se considera administrador para las comprobaciones habituales. |
| 6 | Asistente de Jefe de Carrera | Gestión académica y de proyectos; no equivale a administrador de ajustes. |
| 7 | Coordinador de Proyectos | Gestión de proyectos; no recibe automáticamente gestión académica ni gobierno de cuentas. |

### Helpers que explican los accesos

| Helper PHP | Condición resumida |
|---|---|
| `is_authenticated()` | Existe `$_SESSION['auth_token']`; no consulta la API al evaluar la condición. |
| `is_admin()` | Perfil activo 1 o 5, o administrador general. |
| `can_manage_academics()` | Administrador o asistente de jefatura. |
| `can_manage_projects()` | Administrador, asistente o coordinador. |
| `is_management_staff()` | Usa `can_manage_projects()`. |
| `can_govern_users()` | Sólo administrador general. |
| `is_evaluation_manager()` | Administrador o bandera `is_evaluation_manager` del usuario. |
| `dashboard_url()` | Tablero administrativo para personal de gestión; docente para 2; estudiante para 3; portada en otros casos. |

Hay tres capas distintas: enlaces del menú, guardia PHP de la página y autorización de la API. Por ejemplo, `admin/projects.php` y `admin/deliverables.php` comprueban sesión al entrar, aunque se encuentren bajo `admin`. No debe inferirse un permiso por el nombre del directorio. Los anexos registran las guardias por archivo.

### Cambio de carrera

El selector superior aparece para administración general o cuando hay más de una carrera disponible. Muestra nombre corto, clave, color y la carrera seleccionada. Al pulsar una opción, `career-switcher.js` solicita el cambio, guarda el nuevo token y usuario mediante `/api/set-session.php`, vacía `sessionStorage` y recarga. Deshabilita la opción durante la operación; ante un error la reactiva y muestra el motivo.

El destino está fijado a `/pages/admin/dashboard.php`, incluso cuando el perfil de la carrera es docente o estudiante. Esta particularidad se registra como hallazgo; no se debe describir como redirección automática correcta para todos los perfiles.

## 3. Estructura visual compartida

### Barra superior

Contiene el logotipo y enlace a Inicio, acceso al Repositorio, botón de menú lateral, buscador global, selector de carrera cuando aplica, menú de gestiones, notificaciones y opciones de usuario. Su contenido se genera en `includes/navbar.php` según la sesión y el perfil.

| Control | Qué hace |
|---|---|
| Logotipo / Inicio | Abre el tablero adecuado o la portada pública. |
| Icono de menú | Alterna la barra lateral y su presentación móvil. |
| Repositorio | Abre el catálogo documental. |
| Buscador global | Busca opciones del catálogo local de navegación y propone enlaces con filtros. |
| `Ctrl + K` / `Cmd + K` | Enfoca y selecciona el texto del buscador. |
| Flechas, Enter, Escape | Recorren resultados, abren el seleccionado o cierran el panel. |
| Gestiones | Despliega accesos agrupados según perfil. |
| Campana | Consulta avisos del sistema aplicables. |
| Sobre de actividad | Consulta notificaciones de actividad y permite marcarlas leídas. |
| Escudo de alertas | Acceso exclusivo de administración general al estado operativo. |
| Mi perfil | Abre la consulta de identidad y edición de los datos permitidos. |
| Modo oscuro / Modo claro | Alterna el tema y guarda la preferencia en este navegador. |
| Cerrar sesión | Inicia el flujo de salida y limpieza de sesión. |

El buscador no consulta un índice remoto de todo el sistema: combina títulos, descripciones y palabras clave de un catálogo JavaScript. Normaliza acentos y ordena coincidencias. Sin texto muestra hasta siete accesos; con texto, hasta nueve resultados. Las propuestas de búsqueda en repositorio, proyectos o usuarios navegan a esas pantallas con `q`; no son resultados de registros ya recuperados.

### Barra lateral

Muestra identidad de carrera, fotografía, nombre, perfil y texto «En línea». Ese texto es visual, no una comprobación de disponibilidad instantánea del servidor. Incluye un acceso común a «Seguimiento académico y residencias» y agrupaciones por rol. En gestión aparecen gobierno institucional, seguridad y continuidad, personas, proyectos y tesis, académico y sistema, según permisos. Docentes y estudiantes tienen menos opciones. El control inferior contrae el menú.

Los elementos `data-career-module` pueden ocultarse según `/career/modules`. La ocultación es una ayuda de navegación; no reemplaza la autorización de las solicitudes. El menú lateral, el menú superior y el buscador no tienen condiciones idénticas en todos los casos.

### Mensajes, modales y estados

Se utilizan indicadores de carga, filas vacías, insignias, mensajes de error y avisos temporales. `showAlert` prefiere las notificaciones SweetAlert; si no están disponibles genera una alerta dentro del contenedor. `confirmAction`, `promptText` y `promptPassword` centralizan confirmaciones, texto y contraseñas. Las utilidades de diálogo intentan conservar el scroll y devolver el foco al control anterior.

Cerrar o cancelar un modal no equivale a guardar. Las pantallas que modifican datos necesitan una acción de envío explícita. La rúbrica constituye una excepción parcial: conserva borrador local, pero éste tampoco equivale a una calificación enviada.

### Apariencia y adaptación

El CSS propio define tarjetas, tablas, barras laterales, avisos, formularios, temas y disposición móvil. `responsive.js` adapta navegación y tablas; `skeleton-loader.js` proporciona marcadores de carga; `view-transitions.js` anima cambios y navegación interna, respetando la preferencia de movimiento reducido. El tema puede proceder de una preferencia local o de ajustes del sistema. Escala de grises y tamaño de fuente se aplican a la interfaz.

No se ha realizado una certificación de accesibilidad ni un recorrido visual en todos los tamaños. Los atributos ARIA y las mejoras de foco presentes en el código son evidencia de implementación, no una evaluación completa de conformidad.

## 4. Portada, inicio y recuperación de sesión

**Ruta principal:** `/index.php`.

La portada presenta identidad institucional, una introducción al repositorio, acceso al catálogo y explicación de capacidades académicas. Desde ella se abre el modal de acceso; los fragmentos `#login` y `#recover` permiten abrir directamente el flujo correspondiente. Se muestra un aviso relacionado con cookies y un control para aceptarlo.

### Iniciar sesión

1. Abrir «Iniciar sesión».
2. Introducir número de control o de empleado y contraseña.
3. Elegir «Recuérdame».
4. Enviar el formulario.
5. El cliente comprueba cookies, solicita autenticación, persiste la sesión PHP y navega al tablero.

Mientras se procesa se cambia el texto/estado del botón para evitar envíos repetidos. Los campos requeridos y los mensajes de error ayudan a corregir la entrada. `AuthManager.login()` conserva token, usuario y preferencia de recordatorio en memoria. La persistencia PHP se realiza mediante la entrada local `/api/set-session.php`.

«Recuérdame» afecta las cookies persistentes; el cliente PHP declara 28 días de duración de esas cookies. No significa que un JWT nunca expire: hay renovación, comprobaciones del servidor y cierre de sesión por inactividad.

### Recuperar contraseña

El flujo está dividido en tres modales:

| Paso | Campos | Botón y resultado |
|---|---|---|
| Solicitar | Número de control/empleado y correo registrado | **Enviar token** solicita el código al servidor. |
| Verificar | Token de seis dígitos | **Validar token** comprueba el código y abre el siguiente paso. |
| Cambiar | Nueva contraseña y confirmación | Envía la nueva contraseña, recibe sesión y la guarda. |

La nueva contraseña exige al menos seis caracteres en el HTML y debe coincidir con la confirmación. La existencia de la cuenta, validez y vigencia del token dependen del servidor. El frontend no envía el correo por sí mismo.

`pages/login.php` redirige a la portada con `#login` o al tablero si ya hay sesión. `forgot-password.php` y `reset-password.php` redirigen a `#recover`; no son formularios independientes de recuperación mediante enlace de token.

### Salir y mantener la sesión

El control de la barra superior pide confirmación antes de enviar la salida; la función global `logout()` y `AuthManager.logout()` son implementaciones relacionadas. `AuthManager.logout()` intenta cerrar la sesión en la API y después navega a `pages/logout.php`. Esta última limpia sesión PHP y cookies. Las solicitudes protegidas que reciben 401 pueden intentar una renovación; si la sesión es terminal se fuerza la salida.

El mantenimiento programa renovación antes de la expiración, heartbeat y revalidación al recuperar el foco/visibilidad. `includes/footer.php` aplica el tiempo de inactividad obtenido de ajustes. Los fallos temporales de servidor se distinguen de errores terminales cuando la implementación dispone de ese dato.

## 5. Tableros por perfil

### Administración — `/pages/admin/dashboard.php`

Accesible a personal de gestión. Presenta identidad de carrera, acciones rápidas, avance global, distribución de usuarios, estados de propuestas y resumen de proyectos. Consulta estadísticas y módulos de carrera para construir indicadores y enlaces. Los gráficos son representaciones HTML/CSS de los datos recibidos.

Los accesos rápidos conducen a usuarios, proyectos, entregables y etiquetas cuando se muestran. La presencia de un enlace no amplía permisos sobre su destino. Las funciones de renderizado calculan porcentajes, etiquetas de estado y barras; los valores de negocio vienen de la API.

### Docente — `/pages/teacher/dashboard.php`

Exige perfil docente. Resume revisión, entregables, propuestas y proyectos recientes. Las tarjetas de proyectos incluyen avance y datos de integrantes/asesoría disponibles. Propone una siguiente acción según estadísticas. Su fuente principal es `/dashboard/teacher`.

### Estudiante — `/pages/student/dashboard.php`

Exige perfil estudiante. Muestra avance, entregables, propuestas, proyectos activos y presentaciones programadas. Consulta además el estado de propuesta y la agenda de evaluación. Puede abrir un modal de perfil inicial con nombre, apellidos, semestre, grupo y foto; **Guardar y continuar** envía esos datos al endpoint de completar perfil.

La agenda orienta sobre presentaciones; no debe confundirse con un editor de horarios. Cuando no hay proyecto, datos o presentaciones, el frontend genera estados vacíos en lugar de asumir que todas las etapas están completas.

## 6. Perfil personal — `/pages/profile.php`

Pantalla común para usuarios autenticados. Carga `/profile` y presenta datos personales y fotografía.

| Grupo | Campos y comportamiento |
|---|---|
| Identidad | Nombre y apellidos de sólo lectura: campos deshabilitados. |
| Contacto | Correo de sólo lectura; teléfono y dirección editables. |
| Contexto académico | Semestre y grupo del estudiante, deshabilitados al cargar; se eliminan del envío. No se cambian desde este perfil. |
| Fotografía | Selección de imagen y actualización de la presentación del perfil. |
| Contraseña | Contraseña actual, nueva y confirmación. |

**Guardar cambios** envía `FormData` a `/profile`; omite semestre/grupo y contraseñas vacías. Tras guardar solicita actualizar la sesión PHP con el usuario devuelto. El procesamiento PHP no guarda la fotografía ni los datos en una base local del frontend. La URL de foto se forma con el origen de la API y `storage`; si falta fotografía se muestra una imagen institucional.

## 7. Gobierno de usuarios

### Listado — `/pages/admin/users.php`

La guardia exige administrador general (`can_govern_users`). Ofrece búsqueda por texto, estado activo/inactivo/todos, perfil, semestre y grupo; paginación y selección de usuarios. En móvil agrupa opciones y resume los filtros activos.

| Acción | Funcionamiento |
|---|---|
| Nuevo Usuario | Abre el formulario de alta en modal. |
| Editar | Recupera los datos del registro y permite modificarlos. |
| Activar / desactivar | Solicita cambio de estado; cuentas protegidas pasan por confirmaciones/contraseña administrativa. |
| Seleccionar página | Añade los registros visibles a la selección. |
| Limpiar selección | Descarta la selección local. |
| Plantilla Excel | Descarga el formato de importación. |
| Cargar Excel | Abre el modal para seleccionar y enviar la plantilla. |
| Enviar credenciales | Configura destinatarios, correo y posible contraseña temporal. |
| Control de grupos | Abre gestión de grupos y asignación de estudiantes. |

La selección local puede incluir varias páginas. «Todos los usuarios del filtro actual» es un alcance distinto de «Sólo usuarios seleccionados»: el primero se construye con filtros y no se limita a las filas visibles.

### Alta y edición

Campos: identificador institucional, email, teléfono, perfil, nombres, apellidos, dirección, contraseña y confirmación; estado en edición; semestre y grupo para alumnos. El cambio de semestre carga grupos compatibles. Se validan campos obligatorios y coincidencia de contraseñas según cada formulario. No debe asumirse que las variantes modal y página independiente son idénticas.

También existen `/pages/admin/user-create.php` y `/pages/admin/user-edit.php?id=...`. La edición sin `id` devuelve al listado. En edición se indica que una contraseña vacía conserva la existente. **Guardar** y **Guardar cambios** llaman a creación o actualización del usuario.

### Control de grupos

Permite listar por semestre, crear grupo, editarlo, detectar duplicados, eliminarlo y asignar estudiante. Los formularios incluyen nombre/periodo y los datos de semestre/grupo correspondientes. El cliente carga alumnos activos para seleccionar. La retirada de un estudiante en este flujo heredado se implementa enviando semestre y grupo nulos al usuario; no es el mismo proceso que una baja de curso en la consola académica nueva.

### Importación y correo de credenciales

La importación usa archivo multipart y presenta resultados o errores por la respuesta recibida. No basta con seleccionar el archivo: hay que pulsar **Importar**. El formulario de credenciales permite asunto, base del correo, perfiles destinatarios cuando se usa filtro, regeneración opcional de contraseña temporal y contraseña del administrador actual. **Enviar correos** envía la operación al servidor y puede tardar más que una consulta habitual.

El informe previo de API identifica una posible colisión de la ruta de plantilla de correo. Este manual registra la función expuesta, pero no afirma que el envío o la carga de plantilla hayan sido comprobados contra el backend actual.

## 8. Carreras, identidad y membresías

### Carreras y accesos — `/pages/admin/careers.php`

Reservada a administración general. Muestra tarjetas de carreras con clave, nombre corto, integrantes y porcentaje de preparación. Seleccionar una carrera cambia el detalle que se administra; **Administrar** entra en su contexto.

La identidad editable incluye nombre, nombre corto, colores principal/secundario/acento y lema. **Guardar identidad** persiste los datos de la carrera seleccionada. **Exportar resumen** descarga un resumen institucional.

En membresías se introduce identificador de persona, rol en la carrera y condición de carrera principal. **Asignar** crea el vínculo; los controles de fila permiten activar/desactivar o retirar una membresía. Una membresía y una cuenta global son conceptos diferentes: retirar un acceso de carrera no debe describirse como eliminar automáticamente al usuario institucional.

### Módulos — `/pages/admin/career-modules.php`

Accesible mediante `requireAdmin`. Lista módulos habilitados, registros e indicadores. Al seleccionar un módulo carga sus registros. Un interruptor solicita cambiar habilitación; el servidor puede impedir desactivar un componente obligatorio.

**Nuevo registro** abre datos de clave, título, estado y descripción; **Guardar** crea el registro. Se pueden eliminar registros mediante el control correspondiente. En indicadores se envían valores actual y meta. Estos registros genéricos no equivalen por sí solos a crear una asignatura, proyecto o evaluación de los módulos especializados.

### Carga inicial — `/pages/admin/career-setup.php`

Disponible para administradores. Organiza la preparación en asignaturas, grupos/cargas y rúbricas. **Descargar plantilla** obtiene ejemplos de estructura; el lector procesa archivos CSV y actualiza el resumen antes de enviarlos. **Importar catálogo** solicita la carga de asignaturas y grupos. Exige al menos asignaturas preparadas. Sus columnas son `clave,nombre,descripcion`; para grupos, `nombre,semestre,grupo,periodo,asignaturas`, con claves de asignaturas separadas por `|`. El resumen muestra filas preparadas; el texto indica actualización de claves existentes sin eliminar registros. **Inicializar rúbricas** crea contenedores vacíos de quinto a octavo; las preguntas se configuran después en Evaluaciones y es una operación separada. **Exportar carrera** descarga el paquete de la carrera activa.

No ejecuta migraciones ni sustituye la instalación del backend. Es una interfaz para importar catálogos y preparar datos de una carrera ya disponible.

## 9. Asignaturas, cargas y competencias

**Ruta:** `/pages/admin/asignaturas.php`. Exige `can_manage_academics()`.

La vista lista asignaturas con búsqueda y paginación, permite abrir su estructura y ofrece tres acciones principales: **Nueva Asignatura**, **Gestionar Cargas** y **Gestionar Competencias**.

| Área | Entradas | Acciones |
|---|---|---|
| Asignatura | Clave, nombre y descripción | Crear, editar, guardar, eliminar y ver estructura. |
| Competencias | Asignatura seleccionada, texto de competencia, fecha inicio y fin | Listar, abrir, editar, guardar, eliminar. |
| Grupo/carga | Semestre, grupo, nombre visible, periodo y selección de asignaturas | Crear/editar carga, guardar y eliminar. |

La vista de estructura ayuda a relacionar asignatura y competencias. El gestor de competencias puede abrirse desde una asignatura concreta o desde un selector/buscador de materias. El código calcula estados según las fechas para presentarlos visualmente.

`/pages/admin/competencias.php` sólo redirige a `/pages/admin/asignaturas.php#competencias`, después de verificar permiso académico. No mantiene una pantalla autónoma. Hay una limitación relevante: según el informe previo de API, las mutaciones genéricas de competencias están inhabilitadas en el servidor; por tanto, sus botones heredados no prueban que la operación sea aceptada.

## 10. Seguimiento académico y residencias

**Ruta:** `/pages/academic-workflows.php?tab=...`. La variante `/pages/admin/semesters.php` incluye esta misma página. La entrada exige sesión; posteriormente recupera `/auth/me` para montar la consola con usuario actualizado. `assets/js/academic-console.js` construye el DOM y gestiona las solicitudes.

### Pestañas y visibilidad

| Valor `tab` | Texto | Visibilidad en el módulo |
|---|---|---|
| `periods` | Periodos y matrícula | Gestión académica: administración, jefatura, asistencia. |
| `courses` | Mis materias / Inscripciones | Todos; el título y las acciones cambian para estudiante. |
| `authorizations` | Autorizaciones | No estudiantes. |
| `memberships` | Perfiles | Administración y jefatura. |
| `residencies` | Residencias | Todos, con acciones por relación y rol. |
| `continuity` | Continuidad | No estudiantes. |

Si la pestaña solicitada no está disponible, se elige la primera permitida. Cada renderizado carga el catálogo académico. Las operaciones muestran «Guardando…», «Operación completada» o el error y ofrecen **Reintentar** cuando falla la carga. Las entradas generadas usan nodos de texto para los datos del usuario.

### 10.1 Periodos y matrícula

Muestra la última preparación automática registrada y cada periodo con estado operativo y conciliación. Las acciones de transición son **Abrir**, **Iniciar cierre** y **Cerrar**, disponibles a manager (administración/jefatura). Que un botón se muestre no elimina las precondiciones de transición en el servidor.

Administración general puede **Crear periodo** con nombre, inicio y fin. El cliente envía `automatic_promotion: false`. **Guardar matrícula** asigna alumno a grupo del periodo abierto.

**Conciliar** abre la comparación de matrículas y salas históricas. Para cada estudiante permite confirmar grupo o dejarlo vacío con motivo. En salas requiere periodo, salón institucional y fuente de verificación. Presenta excepciones históricas y una casilla de reconocimiento cuando existen. **Aplicar conciliación** envía resoluciones y el `snapshot` recibido, para que el servidor detecte información desactualizada.

**Preparar promoción** muestra cada estudiante, semestre origen/destino, pendientes, acción, grupo destino y motivo. Las opciones son avance, permanencia y salida del seguimiento. Sólo manager obtiene el envío **Confirmar lote**; asistencia puede preparar y revisar. El lote incluye UUID de operación y snapshot. La interfaz advierte que noveno permanece en seguimiento y que no se declara egreso.

### 10.2 Materias e inscripciones

Cada curso muestra materia, semestre/grupo y ventana de inscripción.

Para estudiantes, **Inscribirme** pide clave del docente. Si ya está inscrito, muestra el estado y **Solicitar baja directa**, con motivo. La aceptación depende de ventana, matrícula y compromisos que controle la API.

Para no estudiantes, el formulario **Abrir inscripción / renovar clave** incluye nueva clave (mínimo seis caracteres), apertura y cierre. **Cerrar autorregistro** desactiva la inscripción por clave. **Ver inscritos** carga personas y estado activo/baja. Gestión académica puede registrar baja administrativa seleccionando alumno y motivo. La autorización del curso concreto sigue correspondiendo al servidor.

### 10.3 Autorizaciones

El formulario permite seleccionar periodo, tipo, proyecto, alumno, etapa 5–8, evaluación de origen, curso, motivo y vigencia. No todos los campos son obligatorios para todos los tipos: la API valida la combinación.

Tipos visibles: presentación en otra etapa, reprogramación, nuevo intento y, para manager, inscripción en otro semestre. **Registrar solicitud** crea la petición. El texto aclara que indicar un alumno no autoriza automáticamente a todo el equipo.

Manager puede resolver solicitudes no consumidas: aprobar/rechazar una solicitada o revocar una aprobada. La resolución incluye motivo, versión y casilla de alcance sobre el proyecto. Una autorización consumida ya no recibe esas mismas acciones en la interfaz.

### 10.4 Perfiles y compromisos

Permite elegir una membresía operativa y asignar perfil docente, estudiante, asistencia o coordinación. **Consultar compromisos** muestra cursos, salas, asesorías y residencias vinculados. El texto explica que altas, cambios de carrera, jefaturas y administradores pertenecen al gobierno general y que los compromisos deben reasignarse antes de determinados cambios.

Administración general dispone también de **Agregar salón**, con nombre del salón institucional. No es el formulario de sala de evaluación: aquí se prepara el catálogo físico.

### 10.5 Continuidad

**Registrar continuidad** relaciona proyecto y grupo del periodo abierto. La finalidad mostrada es conservar grupo, documentos y evaluaciones de periodos anteriores. La pestaña no implementa una promoción automática de todos los alumnos ni altera por sí sola una rúbrica histórica.

### 10.6 Residencias individuales

Estudiante o gestión académica pueden **Crear expediente** con periodo, proyecto, origen (nuevo de empresa o continuación del integrador), asesor y alcance/cambios de al menos veinte caracteres. Para gestión se selecciona además al residente; para estudiante se utiliza su propio ID. La empresa se toma del proyecto elegido.

Cada expediente muestra estado y requisitos. Cada requisito contiene versiones con número, nombre, estado y observaciones.

| Control | Condición local | Resultado solicitado |
|---|---|---|
| Descargar | Versión visible | Descarga autenticada de esa versión. |
| Subir nueva versión | Expediente abierto y usuario residente | Carga PDF, DOC o DOCX en el requisito elegido. |
| Guardar revisión | Expediente abierto, asesor asignado y versión pendiente | Registra correcciones o validación y observaciones. |
| Agregar requisito documental | Abierto y gestión académica o asesor | Nombre, obligatorio y fecha límite opcional. |
| Reasignar asesor | Abierto y manager | Nuevo asesor, motivo y versión del expediente. |
| Cerrar seguimiento | Abierto y manager | Envía motivo y versión para cerrar el expediente. |

El cierre es seguimiento académico; no se presenta como titulación o egreso. Éste es el flujo explícito de residencia individual; la pestaña antigua «Tesis y residencias» de documentos no lo sustituye.

## 11. Proyectos, modalidades y tesis

### Listado de gestión — `/pages/admin/projects.php`

La entrada PHP exige sesión. Los controles de gestión usan permisos y datos de la página; el servidor decide sobre cada operación. La interfaz ofrece búsqueda, filtro de semestre, paginación y vistas **Proyecto integrador**, **Modalidad Dual**, **Caso integrador** y **Tesis**.

| Acción | Resultado |
|---|---|
| Nuevo Proyecto | Abre modal adaptado a la vista/modalidad. |
| Ver detalles | Recupera ficha ampliada, integrantes y relaciones. |
| Editar | Carga el proyecto en formulario. |
| Materias / Guardar materias | Selecciona las asignaturas vinculadas. |
| Marcar o retirar clasificación de tesis | Envía el cambio de `is_thesis`. |
| Eliminar | Solicita confirmación y eliminación; el servidor puede rechazar por historial o relaciones. |
| Plantilla Excel / Cargar Excel | Descarga formato y abre importación. |
| Importar | Envía archivo y muestra resumen/errores del resultado. |

El formulario modal incluye título, descripción, empresa, RFC, giro, contacto, puesto, dirección, modalidad, semestre, año, grupo/carga e integrantes disponibles. La dirección se orienta mediante separadores por comas en los formularios que lo indican. **Agregar** incluye un estudiante en la lista local; quitarlo de esa lista modifica la selección pendiente. **Guardar** envía el conjunto.

Se consultan alumnos activos y proyectos para calcular disponibilidad de integrantes. El cliente limita esas consultas a los tamaños declarados (por ejemplo, `per_page: 500` en varios catálogos); no debe asumirse que recorrerá siempre todos los registros de una institución de cualquier tamaño.

### Páginas independientes

`project-create.php` y `project-edit.php?id=...` exigen gestión de proyectos. Incluyen título, descripción, empresa y contacto, semestre, año, carga e integrantes. La edición necesita ID; sin él se regresa al listado. Son variantes existentes, con campos que pueden diferir del modal más amplio del listado.

### Mis proyectos del docente — `/pages/teacher/my-projects.php`

Lista los proyectos devueltos por `/my-projects`, con nombre, integrantes activos y datos de seguimiento. Exige perfil docente. Su propósito es consulta de asignaciones; no constituye la misma herramienta de altas/importación del listado administrativo.

## 12. Registro y revisión de propuestas

### Estudiante — `/pages/student/proposal-register.php`

Primero consulta el estado de registro. Puede mostrar que falta completar perfil, que existe un proyecto ligado, que no hay grupo permitido o que no existe ventana abierta. Sólo presenta el formulario cuando los datos de estado lo habilitan.

Campos: nombre del proyecto, descripción, empresa/negocio, giro, persona a cargo, puesto y dirección. El buscador de integrantes consulta candidatos a partir de dos caracteres; **Agregar** incorpora el seleccionado y el control de retirada lo elimina de la selección local. **Registrar propuesta** envía el proyecto.

Si el proyecto está en `requiere_cambios` y la fecha `revision_allowed_until` todavía no ha pasado, el código intenta precargar sus datos y convertir el envío en corrección. Muestra observaciones y vencimiento. Hay un fallo estático en el selector del botón de envío de esa rama, documentado en hallazgos; el flujo no se certifica operativo sólo por estar escrito.

### Gestión — `/pages/admin/proposal-config.php`

Exige gestión de proyectos. Permite escoger materia a supervisar, semestre y buscar grupo. Carga grupos, asignaciones y docentes; permite asignar responsable, retirar asignación, crear ventana con inicio/fin y eliminar ventana. La selección de materia determina las asignaciones que se presentan.

### Docente — `/pages/teacher/proposal-review.php`

Presenta periodos de registro de sus grupos y propuestas asignadas. Puede crear/eliminar ventanas conforme a sus grupos y permisos del servidor. En cada propuesta ofrece **Aprobar**, **Solicitar cambios** y **Rechazar**. La revisión recopila observaciones y los datos del flujo de corrección cuando corresponda. El resultado actualiza el estado mostrado; no es una calificación de rúbrica de exposición.

## 13. Asesores y comités — `/pages/admin/advisors.php`

Exige gestión de proyectos. Ofrece tres perspectivas: **Por proyecto**, **Comité de tesis** y **Por asesor**, con filtros por semestre, búsqueda de asesor y rol. Carga proyectos, tesis y candidatos desde usuarios administrativos/docentes.

La asignación se realiza mediante selectores por papel. El cliente evita seleccionar repetidamente una misma persona donde bloquea opciones, identifica filas cambiadas y permite guardar o revertir cambios pendientes. **Guardar cambios** puede procesar varias filas; cada asignación o retirada se traduce en solicitudes al proyecto, con contraseña administrativa en el flujo que la solicita.

**Ver detalles** abre el contexto del proyecto. **Ajustar asesores** y **Ajustar comité** llevan a la perspectiva correspondiente. La vista por asesor resume sus proyectos y papeles; no sustituye la verificación de compromisos de una membresía en la consola académica.

## 14. Entregables

### Gestión — `/pages/admin/deliverables.php`

La guardia PHP verifica sesión. La pantalla consulta proyectos, competencias y entregables; permite filtrar por materia/competencia y trabajar con filas paginadas.

El modal **Nuevo Entregable** reúne proyecto opcional, selector rotulado Asignatura (internamente `competencia_id`), nombre, descripción, tipo, estado, rama asociada y autores. El texto indica que el alcance principal procede de competencia y materia. Tipos ofrecidos: documento, reporte, video, presentación, código y otro. Estados del selector heredado: pendiente, enviado, revisado y aprobado; deben contrastarse con los estados aceptados por la API actual. **Guardar** crea o actualiza. La tabla ofrece edición y eliminación; los modales adicionales permiten calificar y subir archivo para el proyecto correspondiente.

**Calificar** exige un valor entre 0 y 100. **Subir** selecciona archivo y usa el helper común con validación de MIME y tamaño. Un entregable y la entrega de un proyecto se distinguen mediante `deliverable_id` y `project_id`.

### Estudiante — `/pages/student/my-deliverables.php`

Exige estudiante. Carga `/my-deliverables` y muestra datos de actividad, archivo, calificación y docente que calificó cuando están disponibles. **Descargar Archivo** obtiene la evidencia. **Subir/Actualizar Archivo** abre un modal con selección de archivo; **Subir Archivo** valida y solicita la carga. No crea una nueva actividad por seleccionar un archivo.

### Docente — `/pages/teacher/my-deliverables.php`

Exige docente. Ofrece **Buscar alumno**, filtro **Materia** y **Actualizar**. La matriz organiza proyecto → estudiante → actividades. Muestra entregados, faltantes y promedio válido; expande el detalle por alumno. El modal de calificación admite 0–100 y **Guardar calificación** actualiza el elemento de la matriz a partir de la respuesta.

### Archivos y diferencias de validación

`app.js` construye los tipos permitidos con `SGPI_SETTINGS.allowed_file_types`; si no hay configuración usa PDF, Word, Excel y ZIP. El tamaño por defecto es 50 MB, modificable por ajustes. Hay formularios con `accept` fijo que pueden diferir de la configuración. `includes/validations.php` conserva helpers PHP con 50 MB y una lista fija. La API y el servidor HTTP vuelven a validar; aceptar un archivo en el selector no garantiza su almacenamiento.

## 15. Salas, turnos y horarios de evaluación

**Ruta:** `/pages/admin/evaluation-rooms.php`. Acceso a gestión de proyectos o docente. La gestión de salas se calcula aparte con `can_manage_projects()`; el docente puede acceder sin recibir todas las acciones administrativas.

### Vistas y filtros

**Por sala** muestra la organización, estado y controles; **Por proyecto** permite ver asignación y orden; **Por evaluador** presenta participación de docentes. Los filtros incluyen semestre, estado y búsqueda. **Crear sala** prepara el formulario y **Limpiar** descarta los valores cargados.

### Configurar una sala

Campos: nombre de sala, salón institucional, semestre, inicio y fin, minutos de evaluación docente, minutos de presentación, oportunidades, docentes participantes, responsable y proyectos con orden. El cliente comprueba rangos y detecta cruces de horario de salones/docentes/proyectos según los datos cargados. El catálogo institucional y el periodo forman parte del flujo más reciente de esta página.

Límites HTML: minutos de 1 a 240, oportunidades de 1 a 10. Valores iniciales comunes: 15 minutos de evaluación docente, 20 de presentación y una oportunidad. **Guardar sala** crea o actualiza. El servidor puede rechazar disponibilidad o datos que hayan cambiado desde la consulta.

### Operar la sesión

| Acción | Efecto solicitado |
|---|---|
| Editar | Recupera configuración en el formulario. |
| Bloquear orden | Fija la secuencia de presentación. |
| Siguiente proyecto | Avanza el turno conforme al estado de sala y la opción de continuidad. |
| Iniciar / Pausar / Reanudar | Controla el temporizador de la sala. |
| Reiniciar / Finalizar | Reinicia o termina la cuenta según acción seleccionada. |
| Horario | Abre fin ordinario y autorización fuera de tiempo. |
| Archivar | Envía la sala al historial. |
| Restaurar | Intenta desarchivar; hay incompatibilidad heredada reportada por la API. |
| Eliminar | Solicita eliminación con confirmación. |
| Reporte sala / Reporte docentes | Descarga PDF para la audiencia correspondiente. |

El temporizador mostrado se actualiza localmente; las acciones de control se envían al servidor. No debe tratarse como un cronómetro aislado del estado compartido. La autorización fuera de horario incluye interruptor, fecha «Autorizada hasta» y motivo; el textarea declara 10–500 caracteres. El formulario sólo representa la solicitud: la API decide quién puede ampliarla y en qué condiciones.

La vista por proyecto puede retirar de una sala y añadir a otra mediante solicitudes separadas. Por eso un cambio visual de selección no es una transacción global garantizada por el navegador.

## 16. Evaluaciones, rúbricas e historial

**Rutas:** `/pages/admin/evaluations.php` y `/pages/admin/evaluations-archived.php`. Ambas requieren gestión de proyectos o docente. La segunda incluye el mismo código y activa la vista archivada por el nombre del script.

### Consulta y organización

La pantalla agrupa por semestre y sala, ordena conforme a la secuencia y muestra proyecto, integrantes, contexto, estado, evaluadores y promedio. **Desplegar/Ocultar** cambia el detalle de la sala. **Ver detalles** abre información del proyecto. El desglose muestra resultados de criterios y comentarios disponibles.

Hay actualización periódica de datos que evita interferir con un modal de evaluación abierto. La página conserva arreglos locales y reemplaza elementos devueltos al guardar para reducir recargas. El estado recibido, como `can_score_now` o `can_manage_evaluations`, influye en los botones.

### Evaluar

1. Abrir **Evaluar** en un proyecto liberado para evaluación.
2. Revisar contexto de proyecto, integrantes y orden.
3. Responder criterios según el modo disponible.
4. Añadir comentario general.
5. En octavo, responder si recomienda continuar el proyecto cuando se solicite.
6. Pulsar **Guardar Rúbrica**.

Hay dos modos: **Acuerdos** y **Puntaje 1 a 5**. La interfaz utiliza niveles/criterios devueltos por la API; no debe asumirse una tabla universal de porcentajes sólo por el texto del modo. Controla oportunidades y advierte si ya se alcanzó el límite. La recomendación de continuidad se presenta expresamente como una respuesta que no acredita titulación ni afecta el puntaje general.

### Borradores

El cliente guarda progreso de la rúbrica en almacenamiento local y puede restaurarlo al abrir. Muestra «Se restauró un progreso guardado en este navegador». El borrador se guarda en `localStorage` y depende de ese navegador y no constituye evidencia recibida por el servidor. Guardar exitosamente limpia el borrador correspondiente.

### Administrar rúbricas

**Gestionar Rúbrica** abre semestre 5–8, método de puntuación y, en octavo, proyecto opcional. Sin proyecto se trabaja sobre la rúbrica general; con proyecto se editan preguntas específicas. **Agregar** crea pregunta; los iconos **Guardar** y **Desactivar** modifican texto/orden o retiran criterio. El texto admite hasta 255 caracteres; el orden no negativo se edita en la fila.

`CAN_MANAGE_EVALUATIONS` se basa en `is_evaluation_manager()`, no sólo en el directorio `admin`. La bandera para otras acciones de gestión usa condiciones distintas; por eso se deben revisar las acciones concretas.

### Cierre, retroalimentación y reportes

**Marcar evaluada** solicita completar la evaluación conservando las rúbricas existentes; la confirmación informa cuántos docentes han respondido y que ausentes quedan sin evaluación. **Guardar retroalimentación** registra comentarios desde el contexto permitido. Reportes por evaluación/sala tienen variante general y para docentes; se descargan como PDF con autenticación y reintento de renovación ante 401 en el helper correspondiente.

### Archivo y componentes heredados

Se puede activar selección, elegir evaluaciones o grupos por sala/semestre y solicitar archivado masivo. El historial permite consultar archivadas y conserva controles de restauración. También existen un formulario genérico de creación de evaluación y un editor de salas dentro de esta página.

Es importante no confundir presencia y vigencia: el informe previo de API señala creación genérica y desarchivo como operaciones bloqueadas. Además, el editor de salas embebido y la página independiente tienen implementaciones diferentes; la página independiente consulta el catálogo académico actualizado.

## 17. Documentos de evaluación, tesis y publicación

**Ruta:** `/pages/evaluation-documents.php`. Requiere sesión. **Actualizar** recarga documentos y proyectos. La pantalla divide **Entregas de evaluación** y **Tesis y residencias**.

### Hoja de liberación y presentación

Organiza proyectos por semestre 5–9. Cada proyecto muestra integrantes y dos espacios: hoja de liberación y presentación. Se indica **Archivo pendiente** o **Archivo cargado**, nombre, fecha y **Descargar**.

Si `puede_subir` lo permite, se presenta selector de archivo y **Subir archivo** o **Reemplazar archivo**. Las extensiones vienen de `allowed_extensions` por tipo de documento. Se valida selección, extensión y tamaño antes de enviar. Los metadatos se construyen a partir del tipo y los integrantes.

Cuando existe hoja de liberación se muestran interruptores por estudiante. Sólo `puede_revisar` permite cambiarlos y **Guardar liberación**. La liberación es individual por alumno de esa entrega; no equivale automáticamente a cerrar todas las obligaciones del proyecto.

### Tesis y residencias de noveno

La interfaz heredada ofrece tipo tesis/residencias, nombre del avance, descripción, autores y PDF/DOC/DOCX. La carga se muestra si `perfil_id === 3` y semestre 9; usa el perfil base, no el helper de perfil activo. Los documentos se muestran con insignias privado/publicado y categoría.

**Descargar** obtiene el archivo. **Publicar / Privado** solicita cambiar visibilidad, pero aquí el botón se limita a `perfil_id === 1`. Esto difiere de otras pantallas administrativas y se registra como inconsistencia. La opción antigua «Residencias» no debe usarse como descripción del expediente individual moderno; el informe previo de API señala que ese flujo antiguo está bloqueado para residencia.

## 18. Repositorio público y privado

### Catálogo — `/pages/repositorio.php`

No exige sesión para cargar la página. Presenta tarjetas de documentos, texto de búsqueda, selección de orden y paginación. Admite `q` desde el buscador global. La selección del endpoint y las acciones dependen de si hay sesión y del perfil. Una lista pública vacía no implica que no existan documentos privados.

Las tarjetas muestran título/nombre, descripción y metadatos disponibles, categorías/etiquetas y enlaces de detalle o descarga. Los estados de carga, sin resultados y error incluyen mensajes y **Reintentar** cuando corresponde. El orden admite recientes, antiguos y nombre ascendente/descendente.

### Cargar y editar

Administradores y estudiantes autenticados tienen formulario de carga, con diferencias:

| Campo | Restricción/interacción |
|---|---|
| Nombre | Obligatorio, hasta 255 caracteres. |
| Descripción | Obligatoria, hasta 5.000 caracteres. |
| Autores | Obligatorio, hasta 1.000 caracteres. |
| Archivo | Obligatorio en alta; puede conservarse en edición según el formulario. |
| Proyecto asignado | Selección requerida para estudiante. |
| Visibilidad | Administrador elige público/privado; estudiante envía privado. |
| Etiquetas | Selección administrativa desde catálogo. |

El selector declara PDF, DOC/DOCX, XLS/XLSX, ZIP, TXT, JPG/JPEG, PNG, WebP y EPUB. La aceptación final depende de la validación del servidor. **Subir documento** envía multipart; la edición utiliza el identificador y el mecanismo de actualización definido en el código. **Cancelar** descarta el formulario. **Editar** y **Eliminar** se muestran conforme al contexto administrativo de la vista.

### Detalle — `/pages/repositorio-detail.php?id=...`

Consulta el documento por ID, presenta nombre, vista previa, detalles y etiquetas. Si no se puede cargar muestra «Documento no disponible». La vista previa varía por extensión: PDF, imágenes, texto y DOCX tienen ramas específicas; EPUB y tipos no compatibles ofrecen descarga para lectura externa. DOCX utiliza Mammoth para convertir a HTML.

Hay diferencias de autenticación entre ramas de vista previa: el helper de blob añade Bearer, mientras otras peticiones/enlaces dependen de URL y cookies. Un documento descargable puede no previsualizarse por esa diferencia o por cabeceras del servidor. No se comprobó la visualización de archivos privados reales.

## 19. Etiquetas documentales — `/pages/admin/document-tags.php`

Requiere administrador. Incluye búsqueda, tamaño de página, resumen y paginación. **Nueva etiqueta** abre nombre, descripción y color. Hay colores predefinidos, entrada manual, vista previa y cálculo de contraste para el texto. **Guardar** crea/actualiza; **Eliminar** solicita confirmación. **Actualizar**, limpiar búsqueda y cambiar página recargan el listado según parámetros.

Las etiquetas sirven para identificar documentos visualmente; no otorgan permisos de acceso. El informe previo de API contiene una discrepancia de relaciones del backend que puede afectar el listado; no se ha revalidado en esta revisión del frontend.

## 20. Avisos y notificaciones

### Avisos administrables — `/pages/admin/notices.php`

Exige administrador. **Nuevo aviso** abre título, audiencia, tipo, tiempo de autocierre, activo, inicio de visualización, eliminación/vencimiento y mensaje. Editar, activar/desactivar y eliminar modifican la colección local; el guardado envía el conjunto `notices` al servidor.

Los avisos pueden aparecer como notificaciones o toasts. El pie común filtra audiencia, calcula duración y conserva identificadores vistos en `sessionStorage`. La duración del toast se limita entre 2 y 30 segundos. El aviso global de ajustes es un componente separado, colocado en la parte superior.

### Actividad reciente

`activity-notifications.js` carga actividad para la sesión, actualiza contador y lista, permite marcar todas leídas y procesa acciones sobre elementos. Se programa consulta cada 30 segundos. El enlace de una notificación navega al destino recibido/gestionado por el módulo; no crea un registro de negocio.

### Alertas operativas

`operational-notifications.js` alimenta el indicador exclusivo de administración general, con consultas cada 60 segundos y acceso al centro de operaciones. Estas alertas no son los mismos avisos redactados manualmente ni las notificaciones académicas.

## 21. Ajustes generales — `/pages/admin/settings.php`

Exige administrador. Carga ajustes y permite **Guardar ajustes**. Campos principales:

| Ajuste | Restricción de la interfaz | Efecto esperado en el cliente |
|---|---|---|
| Inactividad | 1–480 minutos | Configura cierre por inactividad. |
| Tema predeterminado | Opciones del selector | Apariencia cuando no prevalece preferencia local. |
| Tamaño de fuente | 85–125 %, paso 5 | Escala tipográfica general. |
| Tamaño máximo de archivo | 1–200 MB | Validación JavaScript en cargas que usan ajustes. |
| Máximo de integrantes | 1–10 | Configuración de equipos consumida por el sistema. |
| Tipos permitidos | Selección de extensiones | Lista usada por helpers de carga. |
| Aviso global | Hasta 1.000 caracteres | Franja informativa compartida. |
| Registro de propuestas | Interruptor | Solicita habilitación de la función en configuración. |
| Escala de grises | Interruptor con vista previa | Modifica presentación visual. |

Los ajustes públicos/actuales se sincronizan desde `footer.php`, normalmente cada 60 segundos. Se guarda una selección de preferencias visuales localmente para aplicar apariencia antes de terminar la consulta. El límite de archivo de esta pantalla no modifica automáticamente PHP, proxy o infraestructura del backend.

## 22. Operaciones, auditoría, integridad y respaldos

Todas estas páginas exigen administración general.

### Centro de operaciones — `/pages/admin/operations.php`

Muestra alertas e información de continuidad. **Analizar ahora** solicita un análisis; **Atender** marca una alerta como atendida. Atender no significa reparar automáticamente su causa.

**Política** abre objetivo porcentual, umbral crítico, antigüedad máxima y retención. **Guardar política** persiste los valores. **Guardar medición** registra una medición de continuidad y actualiza historial. **Reporte de continuidad** descarga el informe. Las operaciones de análisis pueden usar un timeout mayor al estándar.

### Auditoría — `/pages/admin/career-audit.php`

Permite filtros por carrera, actor, método HTTP, estado HTTP y rango desde/hasta. **Aplicar filtros** consulta el registro. Presenta eventos con sus datos y paginación disponible en la respuesta/vista. Es lectura de auditoría; no permite editar retrospectivamente una acción desde la tabla.

### Integridad — `/pages/admin/career-integrity.php`

Carga diagnóstico e historial, muestra si la integridad es correcta o requiere revisión y presenta comprobaciones individuales. **Ejecutar diagnóstico** solicita nueva ejecución persistida. No hay que interpretar el diagnóstico como reparación automática de filas ni como demostración de cobertura de todas las tablas del sistema.

### Respaldos — `/pages/admin/database-backups.php`

Muestra salud del almacenamiento, historial y tamaño de archivos.

| Acción | Significado |
|---|---|
| Crear respaldo ahora | Solicita al servidor generar un respaldo de base de datos. |
| Verificar | Solicita comprobación del respaldo seleccionado. |
| Descargar | Recupera el archivo mediante solicitud autenticada. |
| Aplicar retención | Pide limpieza conforme a días y confirmación introducida. |

Crear y verificar pueden esperar hasta cinco minutos en el cliente. La pantalla no incorpora un flujo completo de restauración productiva ni promete respaldo de todos los documentos subidos: su título y endpoints se refieren a base de datos. No se ejecutó ninguna de estas operaciones al elaborar el manual.

## 23. Procesos de principio a fin

### Preparar una carrera para trabajar

1. Administración general revisa carrera, identidad y accesos.
2. Administración carga asignaturas/grupos y prepara rúbricas.
3. Se prepara y concilia el periodo con sus matrículas y salones.
4. Administración/jefatura abre el periodo.
5. Se asignan docentes y se configura inscripción a cursos.
6. Se habilitan ventanas de propuesta y módulos aplicables.

Importar catálogo, abrir periodo y abrir registro de propuestas son acciones diferentes. Completar una no implica completar las demás.

### Alumno: propuesta, entregables y evaluación

1. Accede y completa el perfil inicial si se solicita.
2. Consulta materias y se inscribe con clave dentro de la ventana.
3. Consulta estado de propuesta, registra empresa/proyecto e integrantes.
4. Revisa aprobación u observaciones; corrige dentro del plazo si el flujo está disponible.
5. Consulta y sube entregables.
6. Sube hoja de liberación y presentación cuando tiene permiso.
7. Consulta presentación programada y resultados visibles.

La interfaz tiene inconsistencias heredadas; antes de usar este recorrido como guion de aceptación debe revisarse el apartado 27.

### Docente: seguimiento y exposición

1. Revisa proyectos y propuestas asignadas.
2. Administra las ventanas de sus grupos cuando corresponde.
3. Aprueba, solicita cambios o rechaza propuestas.
4. Consulta matriz por alumno/materia y califica entregables.
5. Revisa evidencias y liberación de alumnos conforme a permisos.
6. Participa en sala y evalúa el turno habilitado.
7. Guarda rúbrica y consulta desglose/reportes permitidos.

### Cambio de periodo con conservación de historia

1. Preparar/conciliar periodo y resolver inconsistencias.
2. Revisar promoción y motivos de cada movimiento.
3. Confirmar lote con snapshot vigente.
4. Registrar continuidad de proyectos en grupos del nuevo periodo.
5. Reabrir inscripciones/ventanas conforme al nuevo calendario.
6. Usar historial de evaluaciones y documentos sin asumir que son registros nuevos.

### Residencia individual

1. Crear expediente asociado al proyecto y periodo.
2. Establecer requisitos obligatorios y opcionales.
3. Residente sube versiones.
4. Asesor revisa cada versión pendiente.
5. Residente entrega correcciones cuando se solicitan.
6. Jefatura/administración gestiona reasignación si es necesaria y solicita cierre con versión vigente.

## 24. Arquitectura técnica para mantenimiento

### Responsabilidades por carpeta

| Ubicación | Responsabilidad |
|---|---|
| `index.php` | Portada, acceso y recuperación. |
| `pages/` | Páginas comunes y documentos. |
| `pages/admin/` | Pantallas de gestión; algunas también accesibles a docentes o cualquier sesión. |
| `pages/student/` | Flujos específicos de alumno. |
| `pages/teacher/` | Flujos específicos de docente. |
| `includes/config.php` | Configuración, cookies, sesión, roles y helpers. |
| `includes/navbar.php`, `sidebar.php` | Navegación compartida. |
| `includes/footer.php` | Sesión inicial para JS, ajustes, avisos, diálogos y scripts visuales. |
| `includes/visual-preferences.php` | Aplicación temprana de preferencias visuales. |
| `includes/alert.php`, `modal.php` | Helpers reutilizables de interfaz. |
| `includes/validations.php` | Validaciones PHP simples, sin consultar base de datos. |
| `api/set-session.php` | Puente local para persistir sesión del navegador en PHP. |
| `assets/js/` | Cliente HTTP, autenticación, consola y comportamiento común. |
| `assets/css/app.css` | Sistema visual, temas y estilos adaptables. |
| `tests/academic-page-smoke.php` | Prueba local de renderizado de la consola con sesión sintética. |
| `test-api.php` | Página de diagnóstico; no forma parte del recorrido académico habitual. |

### Entorno y arranque

`composer.json` declara PHP `8.3.*` y un script `start` que ejecuta el servidor integrado en `0.0.0.0`, puerto tomado de `PORT` o 8080 y raíz en el proyecto. No hay `package.json` propio en este frontend: las páginas cargan JavaScript directamente y dependencias por CDN. El servidor integrado sirve para revisión local; la configuración de producción debe comprobarse por separado. El frontend necesita PHP para sesión/plantillas y conectividad del navegador con la API.

### Configuración

`SGPI_API_BASE_URL` permite sobrescribir la URL de API; se recorta la barra final. Sin variable se usa la URL de producción declarada en el código. `API_ORIGIN_URL` elimina `/api` para formar enlaces de archivos. `FRONTEND_URL` existe como configuración, pero gran parte de la navegación usa rutas absolutas desde `/`.

Las cookies se configuran con `HttpOnly` donde corresponde y `SameSite=Lax`; `Secure` depende de HTTPS o del encabezado de proxy. La firma usa `SGPI_AUTH_COOKIE_SECRET`, luego `APP_KEY` y finalmente un hash calculado a partir de ruta/API. Los valores de entorno no se copiaron a este informe.

### Cliente HTTP — `assets/js/api.js`

`ApiClient` ofrece `get`, `post`, `put`, `delete` y `request`; `prefetchNextPage` precarga páginas. Añade Bearer cuando dispone de token y `X-SGPI-Remember` cuando procede. Para objetos serializa JSON; para `FormData` deja que el navegador genere el boundary multipart.

Maneja timeout con `AbortController`, errores de validación y traducción de mensajes, renovación de sesión y reintento ante 401. El timeout base es diez segundos; algunas operaciones usan 30, 45, 60, 120 o 300 segundos. Las descargas binarias normalmente usan helpers específicos de `fetch` y blob, por lo que no todas heredan idéntico manejo de errores.

La caché combina memoria, almacenamiento de sesión, TTL por recurso y deduplicación de GET pendientes. Existe ventana local de diez segundos para datos actualizados por mutación. `_fresh` evita la validez normal de caché, pero no necesariamente esa ventana de mutación. Parámetros internos `_cache_ttl`, `_fresh`, `_timeout` y `_silent` se eliminan antes de construir la consulta de negocio.

### Autenticación — `assets/js/auth.js`

`AuthManager` toma `SGPI_SESSION`, expone usuario/token/roles, comprueba cookies, realiza login/logout, decodifica expiración JWT, renueva token, mantiene heartbeat y limpia estado local. El token disponible en JavaScript se utiliza para solicitudes autenticadas. No debe confundirse el uso de cookies HttpOnly con que nunca haya token en memoria de la página: el pie genera explícitamente el objeto de sesión.

### Funciones de apoyo

`app.js` contiene alertas, validación de formularios, fechas/horas `es-MX`, debounce, previsualización de imagen, portapapeles, validación de archivos/calificaciones, carga y descarga de entregables y tooltips. La existencia de un helper no garantiza que todos los formularios lo invoquen.

`table.js` y `router.js` ofrecen abstracciones de tabla y navegación, pero hay que distinguirlas de las implementaciones específicas usadas por las páginas. Por ejemplo, el botón genérico Editar de `DynamicTable` se genera sin un manejador de negocio; no representa un editor universal. El anexo de funciones identifica definiciones y eventos, y la referencia conserva el código para comprobar usos reales.

## 25. Estado local, fechas y persistencia

| Estado | Ubicación | Consecuencia |
|---|---|---|
| Sesión PHP y cookies firmadas | Servidor frontend/navegador | Permiten reconstruir identidad de la página. |
| Token y usuario JavaScript | Memoria de página / `SGPI_SESSION` | Se usan en llamadas y controles del cliente. |
| Caché de API | Memoria y `sessionStorage` | Reduce consultas; puede retrasar cambios observados. |
| Preferencias visuales | `localStorage` | Persisten en ese navegador. |
| Avisos vistos | `sessionStorage` | Evita o controla repetición durante la sesión. |
| Borrador de rúbrica | Almacenamiento local según implementación | Recupera edición local, no sustituye envío. |
| Datos institucionales | API | Persistencia de negocio fuera del frontend. |

La mayoría de fechas se presenta con `toLocaleString`/`toLocaleDateString('es-MX')` y entradas `datetime-local`. El idioma `es-MX` no fija por sí mismo la zona horaria: intervienen navegador, formato recibido y servidor. Al revisar vencimientos o salas, conviene distinguir hora mostrada y valor enviado.

## 26. Qué revisar cuando algo no aparece o falla

| Síntoma | Comprobaciones a partir del funcionamiento del cliente |
|---|---|
| Una opción no aparece | Perfil activo, carrera, módulo habilitado, bandera de permiso y asignación concreta. |
| Regreso a Inicio al abrir enlace | Guardia PHP de la página y datos de sesión. |
| El menú ofrece algo pero la página no abre | Diferencia de condiciones entre menú y destino; ver hallazgos. |
| Propuesta no disponible | Perfil inicial, proyecto ya ligado, grupo y ventana activa. |
| No puedo evaluar | Turno, horario, intentos, sala y `can_score_now`. |
| No veo botón de subir | `puede_subir`, perfil, propiedad del expediente y estado abierto. |
| Archivo rechazado | Extensión, MIME, tamaño configurado y límites reales de servidor. |
| Datos parecen antiguos | Caché local, actualización periódica y respuesta de API. |
| Vista previa no abre | Tipo compatible, autenticación de la rama y cabeceras de servidor. |
| Operación rechazada con conflicto | Estado/version/snapshot cambió; recargar antes de una nueva decisión. |
| El formulario ofrece una acción antigua | Contrastar incompatibilidades con el informe de API. |

La documentación no transforma un mensaje de error en un diagnóstico definitivo. El código exacto y la respuesta real de red son la evidencia necesaria para investigar un caso concreto.

## 27. Hallazgos que delimitan el comportamiento real

Estos puntos se documentan sin corregir código de aplicación.

### F01. Selector de envío en corrección de propuesta

En `student/proposal-register.php`, la rama de corrección busca `#projectForm button[type="submit"]`, pero el botón principal se declara sin atributo `type`. Aunque el navegador le dé comportamiento submit por defecto, el selector de atributo no lo encuentra. La asignación de `innerHTML` puede provocar excepción y detener el resto de esa rama. Evidencia estática comprobable; no se reprodujo con una propuesta real.

### F02. Destino fijo después de cambiar carrera

`career-switcher.js` termina en `/pages/admin/dashboard.php` para cualquier cambio exitoso. El tablero sólo acepta personal de gestión. Un docente/estudiante multicarrera puede terminar redirigido a portada. El helper general de tablero sí distingue perfiles, pero ese flujo no lo usa.

### F03. Condiciones diferentes entre menú y guardias

El menú superior presenta Usuarios bajo `is_admin()`, mientras la página exige administrador general. El buscador filtra `/admin/users`, pero no elimina necesariamente `user-create.php`, y genera búsquedas de usuarios para `role === 'admin'`. También agrupa asistencia/coordinación como admin para su catálogo. Es posible mostrar un enlace cuyo destino redirige por falta de permiso.

### F04. Perfil base en documentos

`evaluation-documents.php` limita publicación a `perfil_id === 1` y carga de noveno a `perfil_id === 3`. No utiliza de forma uniforme el perfil activo ni la definición compartida de administrador. Puede ocultar controles a otros perfiles administrativos o a membresías con rol distinto del perfil base.

### F05. Funciones heredadas con compatibilidad limitada

El informe de API del 19 de octubre registra bloqueos para mutaciones genéricas de competencias, creación genérica de evaluación, desarchivo y residencia por el flujo antiguo. El cliente conserva controles o funciones para esas operaciones. Esta observación es un contraste documental con el informe previo, no una nueva prueba de endpoints realizada el 23 de octubre.

### F06. `.htaccess` incluye PHP en una regla de denegación

El `FilesMatch` declara denegación para `.env`, `.json`, `.php` y `.sql`. En Apache, si esta configuración se aplica con compatibilidad para esas directivas, podría impedir servir las propias páginas PHP; otros servidores pueden ignorarla. No se certificó el despliegue ni se modificó la regla.

### F07. Guardias locales basadas en sesión suministrada al puente

`api/set-session.php` valida forma de JSON y presencia de token/usuario, y persiste esos valores sin una consulta de validación a `/auth/me` dentro de ese archivo. Las guardias PHP confían en esa sesión; la API debe seguir siendo la autoridad sobre los datos protegidos. La consola académica sí obtiene usuario actualizado antes de montar sus pestañas. No se hizo una prueba de explotación.

### F08. Diferencias en previsualización y límites de archivos

Los helpers de descarga y vistas previas no siempre envían los mismos encabezados. Además, los límites de `accept`, helpers PHP, configuración JavaScript y servidor pueden ser distintos. Un fallo en una vista previa no prueba que la descarga sea imposible ni que el documento no exista.

### F09. Catálogos limitados y dos implementaciones de salas

Varios selectores piden hasta 100 o 500 elementos sin una iteración global de todas las páginas. Si el servidor pagina o limita más, las opciones pueden ser incompletas. El editor de salas embebido en evaluaciones y la página independiente se mantienen por separado y no incorporan exactamente el mismo contexto académico.

### F10. Helpers genéricos no equivalen a funciones operativas completas

`Router` registra callbacks y cambia historial, pero el portal navega principalmente por PHP y transiciones. `DynamicTable` genera un botón Editar sin conectar edición y sus métodos de paginación llaman a `load()` sin conservar explícitamente el endpoint. Deben tratarse como utilidades existentes, no como prueba de capacidades adicionales del sistema.

### F11. Destino de detalle sin identificador

Si falta `id` en `repositorio-detail.php`, el código navega a `/repositorio`, mientras el catálogo real es `/pages/repositorio.php`. La regla genérica de reescritura no demuestra que exista un manejador de esa ruta amigable. Conviene usar el enlace explícito del catálogo.

## 28. Alcance de la verificación y trazabilidad

Se inventariaron **97 archivos**, incluidos **42 archivos de páginas**. La referencia conserva **70 archivos de código propio** y **27.255 líneas**. Se detectaron **795 sitios de declaración**, **1.278 etiquetas de controles/estructura HTML** y **73 sitios de construcción dinámica de la consola**. Estas métricas son de extracción documental; no significan 795 operaciones independientes ni cobertura de pruebas del 100 %. Los anexos de controles y funciones son extracciones estáticas: incluyen plantillas dinámicas, nombres técnicos y ubicaciones, y pueden mostrar expresiones `${...}` o fragmentos PHP cuando el valor depende de ejecución. No constituyen una captura del DOM final para cada combinación de rol y datos.

La referencia conserva fuentes propias para consultar callbacks anónimos, closures, campos generados y validaciones que un índice de funciones con nombre no puede representar completamente. Se excluyen de esa copia de referencia documentos anteriores, imágenes binarias y la página de diagnóstico `test-api.php`; esta última sí se inventaría para dejar constancia de su existencia sin reproducir posibles datos de prueba.

Los resultados concretos de sintaxis y renderizado local están en [VERIFICACION.md](VERIFICACION.md). No se ejecutó una prueba de extremo a extremo contra la API, no se comprobó el correo real, no se descargaron archivos privados y no se modificaron datos institucionales. Los archivos del informe son el único cambio intencional en el proyecto.
