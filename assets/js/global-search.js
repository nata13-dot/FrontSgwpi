(() => {
    const shell = document.querySelector('[data-global-search]');
    if (!shell) return;

    const form = shell.querySelector('form');
    const input = shell.querySelector('input[type="search"]');
    const resultsBox = shell.querySelector('.global-search-results');
    const role = shell.dataset.role;
    let results = [];
    let activeIndex = -1;
    let enabledModules = null;

    const commonItems = [
        item('Inicio', 'Panel principal del sistema', dashboardPath(role), 'bi-house', ['dashboard', 'principal']),
        item('Repositorio', 'Consultar documentos publicados y privados', '/pages/repositorio.php', 'bi-archive', ['documentos', 'archivos', 'biblioteca']),
        item('Documentos de evaluación', 'Consultar presentaciones y evidencias de evaluación', '/pages/evaluation-documents.php', 'bi-file-earmark-ppt', ['diapositivas', 'evidencias']),
        item('Mi perfil', 'Consultar y actualizar datos personales', '/pages/profile.php', 'bi-person-circle', ['cuenta', 'foto', 'contraseña'])
    ];

    const roleItems = {
        admin: [
            item('Usuarios', 'Administrar estudiantes, docentes y administradores', '/pages/admin/users.php', 'bi-people', ['personas', 'alumnos', 'docentes', 'cuentas'], 'govern_users'),
            item('Crear usuario', 'Registrar una nueva cuenta', '/pages/admin/user-create.php', 'bi-person-plus', ['alta', 'nuevo estudiante', 'nuevo docente'], 'govern_users'),
            item('Asesores', 'Asignar asesores y revisar cargas', '/pages/admin/advisors.php', 'bi-person-check', ['asesorias', 'comites', 'revisores']),
            item('Proyectos y tesis', 'Consultar y administrar proyectos', '/pages/admin/projects.php', 'bi-diagram-3', ['proyectos', 'tesis', 'integrantes', 'empresas']),
            item('Crear proyecto', 'Registrar un proyecto desde administración', '/pages/admin/project-create.php', 'bi-folder-plus', ['nuevo proyecto', 'nueva tesis']),
            item('Propuestas', 'Configurar grupos, ventanas y revisiones', '/pages/admin/proposal-config.php', 'bi-calendar-check', ['registro', 'ventanas', 'excepciones']),
            item('Entregables', 'Administrar entregables académicos', '/pages/admin/deliverables.php', 'bi-file-earmark-check', ['entregas', 'fechas límite']),
            item('Evaluaciones', 'Gestionar evaluaciones y salas', '/pages/admin/evaluations.php', 'bi-clipboard-check', ['rúbricas', 'salas', 'calificaciones']),
            item('Evaluaciones archivadas', 'Consultar evaluaciones finalizadas', '/pages/admin/evaluations-archived.php', 'bi-archive-fill', ['historial', 'archivadas']),
            item('Salas y turnos', 'Organizar salas, horarios y orden de presentación', '/pages/admin/evaluation-rooms.php', 'bi-door-open', ['horarios', 'turnos', 'presentaciones']),
            item('Asignaturas', 'Gestionar asignaturas, cargas y competencias', '/pages/admin/asignaturas.php', 'bi-book', ['materias', 'competencias', 'carga académica'], 'manage_academics'),
            item('Semestres y periodos', 'Gestionar ciclos, promociones y presentaciones especiales', '/pages/admin/semesters.php', 'bi-calendar3', ['periodos', 'promocion', 'cambio de semestre', 'excepciones'], 'manage_academics'),
            item('Carga inicial', 'Importar asignaturas y grupos de la carrera activa', '/pages/admin/career-setup.php', 'bi-cloud-arrow-up', ['csv', 'plan de estudios', 'catalogo'], 'admin'),
            item('Competencias', 'Ir a la gestión de competencias', '/pages/admin/asignaturas.php#competencias', 'bi-star', ['criterios academicos'], 'manage_academics'),
            item('Etiquetas', 'Administrar etiquetas de documentos', '/pages/admin/document-tags.php', 'bi-tags', ['colores', 'categorias'], 'admin'),
            item('Avisos', 'Crear y administrar avisos del sistema', '/pages/admin/notices.php', 'bi-megaphone', ['notificaciones', 'anuncios', 'mensajes'], 'admin'),
            item('Ajustes', 'Configurar preferencias generales del sistema', '/pages/admin/settings.php', 'bi-sliders', ['configuración', 'tema', 'sesión', 'periodo'], 'admin')
        ],
        teacher: [
            item('Mis proyectos', 'Consultar proyectos y tesis asignados', '/pages/teacher/my-projects.php', 'bi-folder2-open', ['tesis', 'asesorias']),
            item('Revisar propuestas', 'Evaluar propuestas de estudiantes', '/pages/teacher/proposal-review.php', 'bi-check2-square', ['revisión', 'aprobar', 'rechazar']),
            item('Evaluaciones', 'Consultar evaluaciones y salas asignadas', '/pages/admin/evaluations.php', 'bi-clipboard-check', ['rúbricas', 'salas']),
            item('Evaluaciones archivadas', 'Consultar evaluaciones finalizadas', '/pages/admin/evaluations-archived.php', 'bi-archive-fill', ['historial']),
            item('Entregables', 'Revisar entregables de estudiantes', '/pages/teacher/my-deliverables.php', 'bi-file-earmark-check', ['entregas', 'calificar'])
        ],
        student: [
            item('Registrar proyecto', 'Registrar una propuesta o tesis', '/pages/student/proposal-register.php', 'bi-pencil-square', ['propuesta', 'nueva tesis']),
            item('Mis entregables', 'Consultar y subir entregables', '/pages/student/my-deliverables.php', 'bi-file-earmark-arrow-up', ['entregas', 'archivos'])
        ]
    };

    // Los permisos llegan calculados por PHP con los mismos helpers que usan
    // las guardias de cada página, para no ofrecer destinos que redirigen.
    const permissions = new Set((shell.dataset.permissions || '').split(/\s+/).filter(Boolean));
    roleItems.admin.splice(2, 0, item('Solicitudes de cuenta', 'Aprobar el autoregistro de alumnos', '/pages/admin/registration-requests.php', 'bi-person-check', ['autoregistro', 'aprobar', 'alumnos'], 'manage_academics'));
    roleItems.teacher.push(item('Salas y turnos', 'Consultar salas, horarios y turnos', '/pages/admin/evaluation-rooms.php', 'bi-door-open', ['horarios', 'presentaciones']));
    [roleItems.admin, roleItems.teacher, roleItems.student].forEach(list => list.push(item('Seguimiento académico', 'Inscripción a materias, residencias y servicio', '/pages/academic-workflows.php', 'bi-calendar-check', ['residencias', 'servicio', 'inscripcion', 'clave'])));
    roleItems.general_admin = [
        item('Panel institucional', 'Resumen de todas las carreras', '/pages/admin/general.php', 'bi-speedometer2', ['institucional', 'carreras']),
        item('Carreras y accesos', 'Administrar carreras y membresías', '/pages/admin/careers.php', 'bi-layers', ['membresias', 'colores']),
        item('Operaciones', 'Alertas y continuidad del sistema', '/pages/admin/operations.php', 'bi-activity', ['alertas', 'continuidad']),
        item('Auditoría', 'Bitácora de cambios', '/pages/admin/career-audit.php', 'bi-shield-check', ['bitacora']),
        item('Integridad', 'Verificación de datos', '/pages/admin/career-integrity.php', 'bi-check2-square', ['verificacion']),
        item('Respaldos', 'Generar y descargar respaldos de la base de datos', '/pages/admin/database-backups.php', 'bi-database', ['backup', 'base de datos']),
        item('Correos enviados', 'Consultar destinatarios de los correos', '/pages/admin/sent-emails.php', 'bi-envelope', ['correo', 'email', 'destinatarios']),
        item('Módulos de carrera', 'Habilitar módulos de la carrera activa', '/pages/admin/career-modules.php', 'bi-grid'),
        ...roleItems.admin
    ];

    const catalog = [...commonItems, ...(roleItems[role] || [])]
        .filter(entry => !entry.requires || permissions.has(entry.requires));

    window.addEventListener('sgpi:career-modules', event => {
        enabledModules = event.detail?.enabled || null;
        if (!resultsBox.hidden) render(input.value);
    });

    function item(title, description, url, icon, keywords = [], requires = null) {
        return { title, description, url, icon, keywords, requires, type: 'option' };
    }

    function dashboardPath(currentRole) {
        return `/pages/${['admin', 'general_admin'].includes(currentRole) ? 'admin' : (currentRole === 'teacher' ? 'teacher' : 'student')}/dashboard.php`;
    }

    function moduleForUrl(url) {
        if (url.includes('/users') || url.includes('/advisors')) return 'usuarios';
        if (url.includes('/deliverable')) return 'entregables';
        if (url.includes('/evaluation')) return 'evaluaciones';
        if (url.includes('/asignaturas') || url.includes('/semesters') || url.includes('/career-setup')) return 'academico';
        if (url.includes('/repositorio') || url.includes('/document-tags')) return 'repositorio';
        if (url.includes('/settings') || url.includes('/notices')) return 'configuracion';
        if (url.includes('/projects') || url.includes('/proposal')) return 'proyectos';
        return null;
    }

    function moduleAllowed(entry) {
        const module = moduleForUrl(entry.url);
        return !module || !enabledModules || enabledModules.get(module) !== false;
    }

    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function searchCatalog(query) {
        const term = normalize(query);
        if (!term) return catalog.filter(moduleAllowed).slice(0, 7);

        const matched = catalog
            .filter(moduleAllowed)
            .map(entry => {
                const title = normalize(entry.title);
                const haystack = normalize([entry.title, entry.description, ...entry.keywords].join(' '));
                let score = 0;
                if (term.split(/\s+/).every(word => haystack.includes(word))) score += 10;
                if (title === term) score += 100;
                if (title.startsWith(term)) score += 60;
                if (title.includes(term)) score += 35;
                if (haystack.includes(term)) score += 15;
                return { ...entry, score };
            })
            .filter(entry => entry.score > 0)
            .sort((a, b) => b.score - a.score || a.title.localeCompare(b.title));

        return matched.slice(0, 6);
    }

    /** Página donde se abre un resultado del buscador general, según el perfil. */
    function recordUrl(type, record) {
        const filter = encodeURIComponent(record.filter ?? '');
        const staff = ['admin', 'general_admin'].includes(role);
        const studentOrTeacher = role === 'student' ? 'student' : 'teacher';
        switch (type) {
            case 'users': return `/pages/admin/users.php?q=${encodeURIComponent(record.id)}`;
            case 'projects':
                if (staff) return `/pages/admin/projects.php?id=${encodeURIComponent(record.id)}`;
                return role === 'teacher' ? `/pages/teacher/my-projects.php?q=${filter}` : '/pages/student/proposal-register.php';
            case 'deliverables': return staff ? `/pages/admin/deliverables.php?q=${filter}` : `/pages/${studentOrTeacher}/my-deliverables.php`;
            case 'rooms': return `/pages/admin/evaluation-rooms.php?q=${filter}`;
            case 'documents': return `/pages/repositorio-detail.php?id=${encodeURIComponent(record.id)}`;
            // La web no tiene pantalla de empresas: se listan sus proyectos.
            case 'companies': return `/pages/admin/projects.php?q=${filter}`;
            case 'subjects': return `/pages/admin/asignaturas.php?q=${filter}`;
            default: return dashboardPath(role);
        }
    }

    let recordGroups = [];
    let recordsTerm = '';
    let searching = false;
    let searchTimer = null;
    let searchTicket = 0;

    function scheduleRecordSearch(query) {
        const term = query.trim();
        clearTimeout(searchTimer);
        const ticket = ++searchTicket;
        if (term.length < 2) {
            recordGroups = [];
            recordsTerm = '';
            searching = false;
            return;
        }
        searching = true;
        searchTimer = setTimeout(async () => {
            try {
                const data = await api.get('/search', { q: term, _silent: true, _cache_ttl: 30000 });
                if (ticket !== searchTicket) return;
                recordGroups = data.groups || [];
            } catch {
                if (ticket !== searchTicket) return;
                recordGroups = [];
            }
            recordsTerm = term;
            searching = false;
            if (!resultsBox.hidden) render(input.value);
        }, 280);
    }

    function render(query = '') {
        const pages = searchCatalog(query);
        const groups = recordsTerm === query.trim() ? recordGroups : [];
        results = [...pages, ...groups.flatMap(group => group.items.map(record => ({
            title: record.title, description: record.subtitle, url: recordUrl(group.type, record), icon: 'bi-arrow-right-short', group: group.label, type: 'record'
        })))];
        activeIndex = results.length ? 0 : -1;
        input.setAttribute('aria-expanded', 'true');
        resultsBox.hidden = false;

        const resultHtml = (entry, index) => `
                <a class="global-search-result${index === activeIndex ? ' active' : ''}" href="${escapeHtml(entry.url)}" role="option" aria-selected="${index === activeIndex}" data-search-index="${index}">
                    <span class="global-search-result-icon"><i class="bi ${escapeHtml(entry.icon)}"></i></span>
                    <span class="global-search-result-copy">
                        <strong>${escapeHtml(entry.title)}</strong>
                        <small>${escapeHtml(entry.description)}</small>
                    </span>
                    <span class="global-search-result-action">Abrir</span>
                </a>`;
        let lastGroup = null;
        const typed = query.trim();
        const status = searching ? 'Buscando en el sistema...'
            : (!results.length ? (typed.length < 2 ? 'Escribe al menos dos letras.' : 'Sin coincidencias. Sólo aparece lo que tu perfil puede consultar.') : '');
        resultsBox.innerHTML = `
            <div class="global-search-heading">${typed ? 'Pantallas' : 'Accesos frecuentes'}</div>
            ${results.map((entry, index) => {
                const title = entry.group && entry.group !== lastGroup ? `<div class="global-search-group">${escapeHtml(entry.group)}</div>` : '';
                lastGroup = entry.group || lastGroup;
                return title + resultHtml(entry, index);
            }).join('')}
            ${status ? `<div class="global-search-status">${status}</div>` : ''}
            <div class="global-search-help"><span><kbd>↑</kbd><kbd>↓</kbd> navegar</span><span><kbd>Enter</kbd> abrir</span><span><kbd>Esc</kbd> cerrar</span></div>`;
    }

    function setActive(nextIndex) {
        if (!results.length) return;
        activeIndex = (nextIndex + results.length) % results.length;
        resultsBox.querySelectorAll('[data-search-index]').forEach((element, index) => {
            const active = index === activeIndex;
            element.classList.toggle('active', active);
            element.setAttribute('aria-selected', String(active));
            if (active) element.scrollIntoView({ block: 'nearest' });
        });
    }

    function closeResults() {
        shell.classList.remove('is-open');
        resultsBox.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    }

    function openSearch() {
        shell.classList.add('is-open');
        input.focus();
        input.select();
        render(input.value);
    }

    input.addEventListener('focus', () => render(input.value));
    input.addEventListener('input', () => { scheduleRecordSearch(input.value); render(input.value); });
    input.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(activeIndex - 1);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            closeResults();
            input.blur();
        }
    });

    form.addEventListener('submit', event => {
        event.preventDefault();
        const selected = results[activeIndex] || searchCatalog(input.value)[0];
        if (selected) {
            if (window.SGPIViewTransition?.navigate) {
                window.SGPIViewTransition.navigate(selected.url);
            } else {
                window.location.href = selected.url;
            }
        }
    });

    resultsBox.addEventListener('mousemove', event => {
        const result = event.target.closest('[data-search-index]');
        if (result) setActive(Number(result.dataset.searchIndex));
    });

    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openSearch();
        }
    });

    document.addEventListener('click', event => {
        if (event.target.closest('[data-global-search-open]')) openSearch();
        else if (!shell.contains(event.target)) closeResults();
    });

    window.SGPIGlobalSearch = { open: openSearch };

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[character]));
    }
})();
