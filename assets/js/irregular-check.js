/**
 * Revisión semanal de alumnos irregulares: durante las primeras 8 semanas de cada periodo se pregunta a la
 * jefatura si algún alumno, por materias reprobadas, cursará un semestre distinto al que le corresponde.
 * "Sí" permite mover al alumno al grupo correcto del periodo y deja constancia de la respuesta semanal.
 */
(() => {
    const escapeText = value => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    async function answer(respuesta, comentario = null) {
        await api.post('/semester-management/irregular-check', { respuesta, comentario });
    }

    async function registerCases(check) {
        const moved = [];
        const groupOptions = check.groups.map(group => `<option value="${group.id}">${group.semestre}° ${escapeText(group.grupo)} · ${escapeText(group.nombre)}</option>`).join('');
        while (true) {
            const result = await Swal.fire({
                title: 'Alumno en otro semestre',
                html: `
                    <p class="small text-muted mb-2">Busca al alumno y elige el grupo que cursará en ${escapeText(check.period.nombre)}.</p>
                    <input id="irregularSearch" class="swal2-input" placeholder="Nombre o matrícula (mín. 2 letras)">
                    <select id="irregularStudent" class="swal2-select"><option value="">Escribe para buscar…</option></select>
                    <select id="irregularGroup" class="swal2-select"><option value="">Grupo destino</option>${groupOptions}</select>
                    ${moved.length ? `<p class="small mt-2 mb-0">Registrados: ${moved.map(escapeText).join(', ')}</p>` : ''}`,
                showCancelButton: true,
                showDenyButton: moved.length > 0,
                confirmButtonText: 'Mover alumno',
                denyButtonText: 'Terminar',
                cancelButtonText: moved.length ? 'Terminar' : 'Cancelar',
                didOpen: () => {
                    let timer = null;
                    document.getElementById('irregularSearch').addEventListener('input', event => {
                        clearTimeout(timer);
                        timer = setTimeout(async () => {
                            const term = event.target.value.trim();
                            if (term.length < 2) return;
                            const found = await api.get('/semester-management/search', { q: term, _fresh: true }).catch(() => ({ students: [] }));
                            document.getElementById('irregularStudent').innerHTML = (found.students || []).map(student =>
                                `<option value="${escapeText(student.id)}">${escapeText([student.nombres, student.apellido_paterno].filter(Boolean).join(' '))} · ${escapeText(student.id)} · ${student.semestre ? `${student.semestre}° ${escapeText(student.grupo || '')}` : 'sin grupo'}</option>`
                            ).join('') || '<option value="">Sin coincidencias</option>';
                        }, 250);
                    });
                },
                preConfirm: async () => {
                    const studentId = document.getElementById('irregularStudent').value;
                    const groupId = Number(document.getElementById('irregularGroup').value);
                    if (!studentId || !groupId) {
                        Swal.showValidationMessage('Elige alumno y grupo destino.');
                        return false;
                    }
                    try {
                        await api.post('/academic-operations/enrollments', { student_id: studentId, group_id: groupId });
                    } catch (error) {
                        Swal.showValidationMessage(error.message || 'No se pudo mover al alumno.');
                        return false;
                    }
                    const group = check.groups.find(item => Number(item.id) === groupId);
                    return `${studentId} → ${group ? `${group.semestre}° ${group.grupo}` : groupId}`;
                }
            });
            if (result.isConfirmed) {
                moved.push(result.value);
                continue;
            }
            break;
        }
        if (moved.length) {
            await answer('con_casos', moved.join('; ').slice(0, 500));
            swalToast('success', `Revisión registrada con ${moved.length} alumno(s)`);
        }
    }

    async function run() {
        if (typeof api === 'undefined' || !window.Swal) return;
        let check;
        try {
            check = await api.get('/semester-management/irregular-check', { _fresh: true });
        } catch (error) {
            return;
        }
        if (!check.pending) return;
        const result = await Swal.fire({
            icon: 'question',
            title: `Semana ${check.week} de ${check.total_weeks}`,
            html: `¿Hay algún alumno que, por materias reprobadas, cursará en <strong>${escapeText(check.period.nombre)}</strong> un semestre diferente al que le corresponde?`,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            denyButtonText: 'No, ninguno',
            cancelButtonText: 'Preguntar después',
            allowOutsideClick: false
        });
        try {
            if (result.isDenied) {
                await answer('sin_casos');
                swalToast('success', 'Revisión de la semana registrada');
            } else if (result.isConfirmed) {
                await registerCases(check);
            }
        } catch (error) {
            swalToast('danger', error.message || 'No se pudo registrar la revisión');
        }
    }

    document.addEventListener('DOMContentLoaded', () => setTimeout(run, 800));
})();
