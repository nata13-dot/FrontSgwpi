// Administradores generales (panel institucional): designar de forma definitiva o temporal, cambiar vigencia y retirar.
(function () {
    const list = document.getElementById('gaList');
    if (!list) return;
    const esc = (v) => String(v ?? '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));
    const fmt = (iso) => new Date(iso).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' });
    const initials = (name) => name.split(' ').slice(0, 2).map(w => w[0] || '').join('');
    const state = { admins: [], picked: null, editing: null };
    const modalEl = document.getElementById('gaModal');
    const form = document.getElementById('gaForm');
    const search = document.getElementById('gaSearch');
    const suggest = document.getElementById('gaSuggest');
    const picked = document.getElementById('gaPicked');
    const untilInput = document.getElementById('gaUntil');

    function left(iso) {
        const ms = new Date(iso) - new Date();
        const days = Math.floor(ms / 86400000);
        if (days >= 1) return `${days} día${days === 1 ? '' : 's'}`;
        const hours = Math.max(1, Math.round(ms / 3600000));
        return `${hours} h`;
    }

    async function load() {
        try {
            const response = await api.get('/admin/general-admins', { _fresh: true });
            state.admins = response.data || [];
            list.innerHTML = state.admins.map(a => `
                <div class="ga-row">
                    <span class="people-avatar">${esc(initials(a.nombre))}</span>
                    <div class="people-name">
                        <strong>${esc(a.nombre)}${a.soy_yo ? ' <span class="badge text-bg-light">Tú</span>' : ''}</strong>
                        <small>${esc(a.id)}${a.designado_por ? ` · designado por ${esc(a.designado_por)}` : ''}${a.desde ? ` el ${esc(fmt(a.desde).split(',')[0])}` : ''}</small>
                    </div>
                    ${a.hasta
                        ? `<span class="badge rounded-pill text-bg-warning" title="Vence el ${esc(fmt(a.hasta))}"><i class="bi bi-hourglass-split"></i> Temporal · quedan ${esc(left(a.hasta))}</span>`
                        : '<span class="badge rounded-pill text-bg-primary"><i class="bi bi-infinity"></i> Definitiva</span>'}
                    ${a.soy_yo ? '<span></span>' : `<div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ga-edit="${esc(a.id)}" title="Cambiar vigencia"><i class="bi bi-calendar-event"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-ga-revoke="${esc(a.id)}" title="Retirar"><i class="bi bi-person-dash"></i></button>
                    </div>`}
                </div>`).join('') || '<div class="text-muted small">Sin administradores generales.</div>';
            list.querySelectorAll('[data-ga-edit]').forEach(b => b.addEventListener('click', () => openFor(state.admins.find(a => a.id === b.dataset.gaEdit))));
            list.querySelectorAll('[data-ga-revoke]').forEach(b => b.addEventListener('click', () => revoke(state.admins.find(a => a.id === b.dataset.gaRevoke))));
        } catch (error) {
            list.innerHTML = `<div class="text-danger small">${esc(error.message)}</div>`;
        }
    }

    function setKind(kind) {
        document.getElementById(kind === 'temp' ? 'gaKindTemp' : 'gaKindForever').checked = true;
        document.getElementById('gaTempBlock').classList.toggle('d-none', kind !== 'temp');
        if (kind === 'temp' && !untilInput.value) setDays(7);
    }
    function setDays(days) {
        const d = new Date(Date.now() + days * 86400000);
        d.setSeconds(0, 0);
        untilInput.value = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    }
    function pick(person) {
        state.picked = person;
        suggest.classList.add('d-none');
        search.closest('.position-relative').classList.toggle('d-none', Boolean(person));
        picked.classList.toggle('d-none', !person);
        picked.innerHTML = person ? `<span class="people-avatar">${esc(initials(person.nombre))}</span><span><strong>${esc(person.nombre)}</strong><small>${esc(person.id)}</small></span>
            ${state.editing ? '' : '<button type="button" class="btn btn-sm btn-link" data-ga-change>Cambiar</button>'}` : '';
        picked.querySelector('[data-ga-change]')?.addEventListener('click', () => { pick(null); search.value = ''; search.focus(); });
    }
    function reset() {
        state.picked = null; state.editing = null;
        search.value = ''; untilInput.value = ''; document.getElementById('gaPassword').value = '';
        document.getElementById('gaAlert').innerHTML = '';
        pick(null); setKind('forever');
        document.getElementById('gaTitle').textContent = 'Designar administrador general';
        document.getElementById('gaSubmit').textContent = 'Designar';
    }
    function openFor(admin) {
        reset();
        state.editing = admin;
        pick({ id: admin.id, nombre: admin.nombre });
        document.getElementById('gaTitle').textContent = 'Cambiar vigencia';
        document.getElementById('gaSubmit').textContent = 'Guardar vigencia';
        if (admin.hasta) {
            const d = new Date(admin.hasta);
            untilInput.value = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            setKind('temp');
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    async function revoke(admin) {
        const result = await Swal.fire({
            icon: 'warning', title: 'Retirar administración general',
            html: `<p>${esc(admin.nombre)} volverá a su perfil dentro de su carrera y se cerrarán sus sesiones de la app.</p>`,
            input: 'password', inputLabel: 'Tu contraseña', inputAttributes: { autocomplete: 'current-password' },
            showCancelButton: true, confirmButtonText: 'Retirar', cancelButtonText: 'Cancelar', confirmButtonColor: '#b23b3b',
            preConfirm: (password) => password || Swal.showValidationMessage('Escribe tu contraseña')
        });
        if (!result.isConfirmed) return;
        try {
            const response = await api.put(`/users/${encodeURIComponent(admin.id)}/general-admin`, { grant: false, admin_password: result.value });
            Swal.fire({ icon: 'success', title: response.message, timer: 2200, showConfirmButton: false });
            load();
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'No se retiró', text: error.message });
        }
    }

    let timer = null;
    search.addEventListener('input', () => {
        clearTimeout(timer);
        const term = search.value.trim();
        if (term.length < 2) { suggest.classList.add('d-none'); return; }
        timer = setTimeout(async () => {
            try {
                const response = await api.get('/admin/career-candidates', { search: term, _fresh: true });
                const people = response.data || [];
                suggest.innerHTML = people.length ? people.map((p, i) => `<button type="button" class="people-suggest-item" data-i="${i}"><strong>${esc(p.nombre)}</strong>
                    <small>${esc(p.id)}${p.carreras.length ? ' · ' + p.carreras.map(c => esc(c.clave)).join(', ') : ''}</small></button>`).join('')
                    : '<div class="people-suggest-empty">Sin coincidencias entre el personal.</div>';
                suggest.classList.remove('d-none');
                suggest.querySelectorAll('[data-i]').forEach(b => b.addEventListener('click', () => pick(people[Number(b.dataset.i)])));
            } catch (error) {
                suggest.innerHTML = `<div class="people-suggest-empty text-danger">${esc(error.message)}</div>`;
                suggest.classList.remove('d-none');
            }
        }, 250);
    });
    document.querySelectorAll('input[name="gaKind"]').forEach(r => r.addEventListener('change', () => setKind(r.value)));
    document.querySelectorAll('[data-ga-days]').forEach(b => b.addEventListener('click', () => setDays(Number(b.dataset.gaDays))));
    document.querySelector('[data-ga-new]')?.addEventListener('click', reset);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const alert = document.getElementById('gaAlert');
        if (!state.picked) { alert.innerHTML = '<div class="alert alert-warning">Elige a la persona.</div>'; return; }
        const temp = document.getElementById('gaKindTemp').checked;
        if (temp && !untilInput.value) { alert.innerHTML = '<div class="alert alert-warning">Indica cuándo termina.</div>'; return; }
        const button = document.getElementById('gaSubmit');
        button.disabled = true;
        try {
            const response = await api.put(`/users/${encodeURIComponent(state.picked.id)}/general-admin`, {
                grant: true, admin_password: document.getElementById('gaPassword').value,
                hasta: temp ? new Date(untilInput.value).toISOString() : null
            });
            bootstrap.Modal.getInstance(modalEl)?.hide();
            Swal.fire({ icon: 'success', title: response.message, timer: 2500, showConfirmButton: false });
            load();
        } catch (error) {
            alert.innerHTML = `<div class="alert alert-danger">${esc(error.message)}</div>`;
        } finally {
            button.disabled = false;
        }
    });
    document.addEventListener('click', (event) => { if (!event.target.closest('#gaModal .people-add-search')) suggest.classList.add('d-none'); });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', load); else load();
})();
