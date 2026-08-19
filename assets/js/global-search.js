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
            item('Usuarios', 'Administrar estudiantes, docentes y administradores', '/pages/admin/users.php', 'bi-people', ['personas', 'alumnos', 'docentes', 'cuentas']),
            item('Crear usuario', 'Registrar una nueva cuenta', '/pages/admin/user-create.php', 'bi-person-plus', ['alta', 'nuevo estudiante', 'nuevo docente']),
            item('Asesores', 'Asignar asesores y revisar cargas', '/pages/admin/advisors.php', 'bi-person-check', ['asesorias', 'comites', 'revisores']),
            item('Proyectos y tesis', 'Consultar y administrar proyectos', '/pages/admin/projects.php', 'bi-diagram-3', ['proyectos', 'tesis', 'integrantes', 'empresas']),
            item('Crear proyecto', 'Registrar un proyecto desde administración', '/pages/admin/project-create.php', 'bi-folder-plus', ['nuevo proyecto', 'nueva tesis']),
            item('Propuestas', 'Configurar grupos, ventanas y revisiónes', '/pages/admin/proposal-config.php', 'bi-calendar-check', ['registro', 'ventanas', 'excepciones']),
            item('Entregables', 'Administrar entregables académicos', '/pages/admin/deliverables.php', 'bi-file-earmark-check', ['entregas', 'fechas límite']),
            item('Evaluaciones', 'Gestionar evaluaciones y salas', '/pages/admin/evaluations.php', 'bi-clipboard-check', ['rúbricas', 'salas', 'calificaciones']),
            item('Evaluaciones archivadas', 'Consultar evaluaciones finalizadas', '/pages/admin/evaluations-archived.php', 'bi-archive-fill', ['historial', 'archivadas']),
            item('Asignaturas', 'Gestionar asignaturas, cargas y competencias', '/pages/admin/asignaturas.php', 'bi-book', ['materias', 'competencias', 'carga académica']),
            item('Semestres y periodos', 'Gestionar ciclos, promociones y presentaciones especiales', '/pages/admin/semesters.php', 'bi-calendar3', ['periodos', 'promocion', 'cambio de semestre', 'excepciones']),
            item('Carga inicial', 'Importar asignaturas y grupos de la carrera activa', '/pages/admin/career-setup.php', 'bi-cloud-arrow-up', ['csv', 'plan de estudios', 'catalogo']),
            item('Competencias', 'Ir a la gestión de competencias', '/pages/admin/asignaturas.php#competencias', 'bi-star', ['criterios academicos']),
            item('Etiquetas', 'Administrar etiquetas de documentos', '/pages/admin/document-tags.php', 'bi-tags', ['colores', 'categorias']),
            item('Avisos', 'Crear y administrar avisos del sistema', '/pages/admin/notices.php', 'bi-megaphone', ['notificaciones', 'anuncios', 'mensajes']),
            item('Ajustes', 'Configurar preferencias generales del sistema', '/pages/admin/settings.php', 'bi-sliders', ['configuración', 'tema', 'sesión', 'periodo'])
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

    // Las opciones de gobierno de cuentas pertenecen exclusivamente al
    // Administrador General; el resto de autoridades conserva solo consulta.
    roleItems.general_admin = roleItems.admin;
    roleItems.admin = roleItems.admin.filter(entry => !entry.url.includes('/admin/users'));

    const catalog = [...commonItems, ...(roleItems[role] || [])];

    window.addEventListener('sgpi:career-modules', event => {
        enabledModules = event.detail?.enabled || null;
        if (!resultsBox.hidden) render(input.value);
    });

    function item(title, description, url, icon, keywords = []) {
        return { title, description, url, icon, keywords, type: 'option' };
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
                if (title === term) score += 100;
                if (title.startsWith(term)) score += 60;
                if (title.includes(term)) score += 35;
                if (haystack.includes(term)) score += 15;
                return { ...entry, score };
            })
            .filter(entry => entry.score > 0)
            .sort((a, b) => b.score - a.score || a.title.localeCompare(b.title));

        return [...matched, ...dynamicSearchItems(query).filter(moduleAllowed)].slice(0, 9);
    }

    function dynamicSearchItems(query) {
        const encoded = encodeURIComponent(query.trim());
        const dynamic = [
            {
                title: `Buscar "${query.trim()}" en el repositorio`,
                description: 'Filtrar documentos por título, descripción o autor',
                url: `/pages/repositorio.php?q=${encoded}`,
                icon: 'bi-archive',
                type: 'search'
            }
        ];

        if (role === 'admin') {
            dynamic.unshift(
                {
                    title: `Buscar usuario "${query.trim()}"`,
                    description: 'Filtrar por nombre, control, correo o teléfono',
                    url: `/pages/admin/users.php?q=${encoded}`,
                    icon: 'bi-person-search',
                    type: 'search'
                },
                {
                    title: `Buscar proyecto "${query.trim()}"`,
                    description: 'Filtrar proyectos, tesis, integrantes o empresas',
                    url: `/pages/admin/projects.php?q=${encoded}`,
                    icon: 'bi-search',
                    type: 'search'
                }
            );
        }

        return dynamic;
    }

    function render(query = '') {
        results = searchCatalog(query);
        activeIndex = results.length ? 0 : -1;
        input.setAttribute('aria-expanded', 'true');
        resultsBox.hidden = false;

        const heading = query.trim() ? 'Resultados y búsquedas' : 'Accesos frecuentes';
        resultsBox.innerHTML = `
            <div class="global-search-heading">${heading}</div>
            ${results.map((entry, index) => `
                <a class="global-search-result${index === activeIndex ? ' active' : ''}" href="${escapeHtml(entry.url)}" role="option" aria-selected="${index === activeIndex}" data-search-index="${index}">
                    <span class="global-search-result-icon"><i class="bi ${escapeHtml(entry.icon)}"></i></span>
                    <span class="global-search-result-copy">
                        <strong>${escapeHtml(entry.title)}</strong>
                        <small>${escapeHtml(entry.description)}</small>
                    </span>
                    <span class="global-search-result-action">${entry.type === 'search' ? 'Filtrar' : 'Abrir'}</span>
                </a>
            `).join('')}
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
        resultsBox.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    }

    function openSearch() {
        input.focus();
        input.select();
        render(input.value);
    }

    input.addEventListener('focus', () => render(input.value));
    input.addEventListener('input', () => render(input.value));
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
        if (!shell.contains(event.target)) closeResults();
    });

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
