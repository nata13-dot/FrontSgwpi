/**
 * Propuestas: detalle, validación y ventanas (autoregistro y registro de propuestas).
 * Lo usan admin/proposal-config.php (coordinación y jefatura) y teacher/proposal-review.php (docentes validadores).
 */
const ProposalUI = (() => {
    const STATUS = {
        pendiente: ['warning', 'Pendiente de validación'],
        requiere_cambios: ['info', 'Requiere cambios'],
        aprobado: ['success', 'Aprobada'],
        rechazado: ['danger', 'Rechazada']
    };
    let projects = [];
    let filter = 'pendiente';

    const esc = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[char]));
    const fullName = person => [person?.nombres, person?.apa || person?.apellido_paterno, person?.ama || person?.apellido_materno].filter(Boolean).join(' ');
    const dateTime = value => value ? new Date(value).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }) : '';
    const badge = status => `<span class="badge bg-${STATUS[status]?.[0] || 'secondary'}${STATUS[status]?.[0] === 'warning' || STATUS[status]?.[0] === 'info' ? ' text-dark' : ''}">${esc(STATUS[status]?.[1] || status)}</span>`;
    const localInput = iso => {
        const date = iso ? new Date(iso) : new Date();
        return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    };

    function details(project) {
        const contact = [project.company_contact_phone, project.company_contact_email].filter(Boolean).join(' · ');
        return `
            ${project.description ? `<p class="text-muted small mb-2">${esc(project.description)}</p>` : ''}
            <dl class="row small mb-0">
                <dt class="col-sm-4">Integrantes</dt><dd class="col-sm-8">${esc((project.students || []).map(fullName).join(', ') || '—')}</dd>
                ${project.subject_group ? `<dt class="col-sm-4">Grupo</dt><dd class="col-sm-8">${esc(project.subject_group.nombre)}</dd>` : ''}
                <dt class="col-sm-4">Empresa</dt><dd class="col-sm-8">${esc(project.company_name || '—')}<br><span class="text-muted">${esc([project.company_giro, project.company_contact_name, project.company_contact_position].filter(Boolean).join(' · '))}</span>${contact ? `<br><span class="text-muted">${esc(contact)}</span>` : ''}</dd>
                ${project.objetivo_general ? `<dt class="col-sm-4">Objetivo general</dt><dd class="col-sm-8">${esc(project.objetivo_general)}</dd>` : ''}
                ${(project.objetivos || []).length ? `<dt class="col-sm-4">Objetivos específicos</dt><dd class="col-sm-8"><ol class="ps-3 mb-0">${project.objetivos.map(item => `<li>${esc(item)}</li>`).join('')}</ol></dd>` : ''}
            </dl>`;
    }

    function renderList() {
        const container = document.getElementById('proposalList');
        const count = status => projects.filter(project => project.proposal_status === status).length;
        document.getElementById('proposalFilters').innerHTML = [...Object.entries(STATUS), ['', [null, 'Todas']]].map(([value, [, label]]) =>
            `<button type="button" class="btn btn-sm ${filter === value ? 'btn-primary' : 'btn-outline-secondary'}" onclick="ProposalUI.setFilter('${value}')">${esc(label)} <span class="badge bg-light text-dark">${value ? count(value) : projects.length}</span></button>`).join('');
        const list = filter ? projects.filter(project => project.proposal_status === filter) : projects;
        if (!list.length) {
            container.innerHTML = `<div class="col-12"><div class="alert alert-light border text-center">${filter ? `No hay propuestas en estado «${esc(STATUS[filter][1])}».` : 'No hay propuestas registradas.'}</div></div>`;
            return;
        }
        container.innerHTML = list.map(project => `
            <div class="col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex justify-content-between gap-2 mb-1"><h5 class="mb-0">${esc(project.title)}</h5>${badge(project.proposal_status)}</div>
                ${details(project)}
                ${project.proposal_review_comment ? `<div class="alert alert-light border small mt-2 mb-0">${esc(project.proposal_review_comment)}</div>` : ''}
                ${project.proposal_reviewer ? `<p class="text-muted small mt-2 mb-0">Revisó: ${esc(fullName(project.proposal_reviewer))} · ${esc(dateTime(project.proposal_reviewed_at))}</p>` : ''}
                ${project.proposal_status !== 'aprobado' ? `<div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-sm btn-success" onclick="ProposalUI.review(${project.id}, 'aprobado')"><i class="bi bi-check-lg"></i> Aprobar</button>
                    <button class="btn btn-sm btn-warning" onclick="ProposalUI.review(${project.id}, 'requiere_cambios')"><i class="bi bi-pencil"></i> Solicitar cambios</button>
                    ${project.proposal_status !== 'rechazado' ? `<button class="btn btn-sm btn-outline-danger" onclick="ProposalUI.review(${project.id}, 'rechazado')"><i class="bi bi-x-lg"></i> Rechazar</button>` : ''}
                </div>` : ''}
            </div></div></div>`).join('');
    }

    async function loadReview() {
        const container = document.getElementById('proposalList');
        container.innerHTML = '<div class="col-12 text-muted">Cargando propuestas...</div>';
        try {
            const data = await api.get('/proposal/teacher-projects', { _fresh: true });
            if (!data.is_validator) {
                document.getElementById('proposalFilters').innerHTML = '';
                container.innerHTML = '<div class="col-12"><div class="alert alert-secondary"><i class="bi bi-lock"></i> No validas propuestas. La coordinación o la jefatura deben designarte como docente validador de la carrera.</div></div>';
                return;
            }
            projects = data.projects || [];
            renderList();
        } catch (error) {
            container.innerHTML = `<div class="col-12"><div class="alert alert-danger">${esc(error.message)}</div></div>`;
        }
    }

    async function review(id, status) {
        const project = projects.find(item => item.id === id);
        let comment = null;
        let until = null;
        if (status === 'aprobado') {
            const confirmed = await Swal.fire({ title: 'Aprobar propuesta', text: `${project.title} pasará a Proyectos.`, icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, aprobar', cancelButtonText: 'Cancelar' });
            if (!confirmed.isConfirmed) return;
        } else {
            const result = await Swal.fire({ title: status === 'rechazado' ? 'Motivo del rechazo' : 'Cambios solicitados', input: 'textarea', inputPlaceholder: 'Observaciones para el equipo', showCancelButton: true, confirmButtonText: 'Continuar', cancelButtonText: 'Cancelar', inputValidator: value => value.trim() ? undefined : 'Este dato es obligatorio.' });
            if (!result.isConfirmed) return;
            comment = result.value.trim();
        }
        if (status === 'requiere_cambios') {
            const result = await Swal.fire({ title: 'Permitir corrección hasta', input: 'datetime-local', inputValue: localInput(new Date(Date.now() + 3 * 864e5).toISOString()), showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar', inputValidator: value => value ? undefined : 'Indica la fecha.' });
            if (!result.isConfirmed) return;
            until = result.value;
        }
        try {
            await api.post(`/proposal/projects/${id}/review`, { proposal_status: status, proposal_review_comment: comment, revision_allowed_until: until });
            swalToast('success', 'Revisión registrada');
            loadReview();
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }

    function windowCard(title, subtitle, state, endpoint, disabled, extra = '') {
        const key = endpoint.split('/').pop();
        return `<div class="col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="d-flex justify-content-between gap-2"><h5 class="mb-1">${esc(title)}</h5><span class="badge ${state.open ? 'bg-success' : 'bg-secondary'}"><i class="bi ${state.open ? 'bi-unlock' : 'bi-lock'}"></i> ${state.open ? 'Abierto' : 'Cerrado'}</span></div>
            <p class="text-muted small">${esc(subtitle)}</p>
            ${state.open ? `<p class="small mb-2">Cierra: <strong>${esc(dateTime(state.until || state.closes_at))}</strong></p>` : ''}
            ${extra}
            ${disabled ? '' : `<div class="d-flex flex-wrap align-items-end gap-2 mt-2">
                <div><label class="form-label small mb-1" for="until-${key}">${state.open ? 'Cambiar cierre' : 'Abrir hasta'}</label><input class="form-control" type="datetime-local" id="until-${key}" min="${localInput()}"></div>
                <button class="btn btn-primary" onclick="ProposalUI.saveWindow('${endpoint}', 'until-${key}')"><i class="bi bi-unlock"></i> ${state.open ? 'Actualizar' : 'Abrir'}</button>
                ${state.open ? `<button class="btn btn-outline-danger" onclick="ProposalUI.saveWindow('${endpoint}', null)"><i class="bi bi-lock"></i> Cerrar ahora</button>` : ''}
            </div>`}
        </div></div></div>`;
    }

    async function loadSettings() {
        const container = document.getElementById('proposalSettings');
        container.innerHTML = '<div class="text-muted">Cargando ajustes...</div>';
        try {
            const data = await api.get('/proposal/settings', { _fresh: true });
            const self = data.self_registration;
            const selected = data.validator_ids.map(String);
            container.innerHTML = `
                ${data.period ? '' : '<div class="alert alert-warning">La carrera no tiene un periodo abierto. Ábrelo en Operación académica antes de habilitar el autoregistro.</div>'}
                <div class="row g-3 mb-3">
                    ${windowCard('Autoregistro de alumnos', 'Mientras esté abierto, el inicio de sesión muestra «Crear cuenta».', self, '/proposal/settings/self-registration', !self.can_manage,
                        `<p class="text-muted small mb-0">Correo esperado: <strong>${esc(self.rule.example)}</strong>. ${esc(self.rule.description)}</p>${self.can_manage ? '' : '<p class="text-muted small mb-0">Lo abre la administración general o la jefatura.</p>'}`)}
                    ${windowCard('Registro de propuestas', `Los alumnos registran su propuesta o piden unirse a la de un compañero (máximo ${data.max_project_members} integrantes).`, data.proposal_window, '/proposal/settings/proposal-window', false)}
                </div>
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <h5 class="mb-1">Docentes validadores</h5>
                    <p class="text-muted small">Además de coordinación, jefatura y asistencia, estos docentes pueden validar las propuestas de la carrera.</p>
                    ${data.teachers.length ? `<div class="d-flex flex-wrap gap-2 mb-3">${data.teachers.map(teacher => `
                        <input type="checkbox" class="btn-check validator-option" id="validator-${esc(teacher.id)}" value="${esc(teacher.id)}" ${selected.includes(String(teacher.id)) ? 'checked' : ''} autocomplete="off">
                        <label class="btn btn-sm btn-outline-primary rounded-pill" for="validator-${esc(teacher.id)}">${esc(fullName(teacher))}</label>`).join('')}</div>
                        <button class="btn btn-primary" onclick="ProposalUI.saveValidators()"><i class="bi bi-check-lg"></i> Guardar validadores</button>` : '<p class="text-muted small mb-0">No hay docentes activos en la carrera.</p>'}
                </div></div>`;
        } catch (error) {
            container.innerHTML = `<div class="alert alert-danger">${esc(error.message)}</div>`;
        }
    }

    async function saveWindow(endpoint, inputId) {
        const until = inputId ? document.getElementById(inputId).value : null;
        if (inputId && !until) {
            swalToast('warning', 'Indica la fecha y hora de cierre.');
            return;
        }
        try {
            const response = await api.put(endpoint, { until });
            swalToast('success', response.message);
            loadSettings();
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }

    async function saveValidators() {
        const ids = [...document.querySelectorAll('.validator-option:checked')].map(input => input.value);
        try {
            await api.put('/proposal/settings/validators', { teacher_ids: ids });
            swalToast('success', 'Docentes validadores guardados');
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }

    return {
        STATUS, esc, fullName, dateTime, badge, details, loadReview, review, loadSettings, saveWindow, saveValidators,
        setFilter(value) { filter = value; renderList(); }
    };
})();
