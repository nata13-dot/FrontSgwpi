/**
 * Función para mostrar alertas
 */
function showAlert(container, type, message, duration = 5000) {
    const alertId = 'alert-' + Date.now();
    const html = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const element = document.querySelector(container);
    if (element) {
        element.innerHTML = html;
        
        if (duration > 0) {
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, duration);
        }
    }
}

/**
 * Validar form
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Formatear hora
 */
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Cargar imagen con preview
 */
function handleImageUpload(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input) return;

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            if (preview) {
                preview.src = event.target.result;
            }
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Copiar al portapapeles
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('body', 'success', 'Copiado al portapapeles');
    }).catch(err => {
        console.error('Error al copiar:', err);
    });
}

/**
 * Validar calificación (0-100)
 */
function validarCalificacion(valor) {
    const num = parseFloat(valor);
    return !isNaN(num) && num >= 0 && num <= 100;
}

/**
 * Validar MIME type de archivo
 */
function validarMimeType(mimeType) {
    const mimeByExtension = {
        pdf: ['application/pdf'],
        doc: ['application/msword'],
        docx: ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        xls: ['application/vnd.ms-excel'],
        xlsx: ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ppt: ['application/vnd.ms-powerpoint'],
        pptx: ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        zip: ['application/zip', 'application/x-zip-compressed'],
        txt: ['text/plain'],
        jpg: ['image/jpeg'],
        jpeg: ['image/jpeg'],
        png: ['image/png']
    };
    const extensions = window.SGPI_SETTINGS?.allowed_file_types || ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
    const tiposPermitidos = extensions.flatMap(extension => mimeByExtension[extension] || []);
    return tiposPermitidos.includes(mimeType);
}

/**
 * Validar tamaño de archivo (máximo 50MB)
 */
function validarTamañoArchivo(tamaño) {
    const maxSizeMb = Number(window.SGPI_SETTINGS?.max_file_size_mb || 50);
    const maxSize = maxSizeMb * 1024 * 1024;
    return tamaño <= maxSize;
}

/**
 * Obtener nombre legible del tamaño
 */
function formatearTamaño(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Descargar archivo de entregable
 */
async function descargarEntregable(deliverable_id, nombre = null) {
    try {
        const token = auth.getToken();
        if (!token) {
            showAlert('#alertContainer', 'danger', 'Token no disponible. Por favor inicia sesión.');
            return;
        }

        const response = await fetch(`${API_BASE_URL}/deliverables/${deliverable_id}/download`, {
            credentials: 'include',
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) {
            if (response.status === 404) {
                showAlert('#alertContainer', 'danger', 'Archivo no encontrado.');
            } else if (response.status === 403) {
                showAlert('#alertContainer', 'danger', 'No tienes permiso para descargar este archivo.');
            } else {
                showAlert('#alertContainer', 'danger', 'Error al descargar: ' + response.statusText);
            }
            return;
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = nombre || `entregable_${deliverable_id}.pdf`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        showAlert('#alertContainer', 'success', 'Archivo descargado exitosamente.');
    } catch (error) {
        console.error('Error:', error);
        showAlert('#alertContainer', 'danger', 'Error al descargar: ' + error.message);
    }
}

/**
 * Validar rango de fechas de competencia
 */
async function validarFechaEntregable(competencia_id, fecha_limite) {
    try {
        const response = await api.get(`/competencias/${competencia_id}`);
        const competencia = response.data || response;
        
        if (!competencia.fecha_inicio || !competencia.fecha_fin) {
            return { valido: true, mensaje: '' };
        }
        
        const fecha = new Date(fecha_limite);
        const inicio = new Date(competencia.fecha_inicio);
        const fin = new Date(competencia.fecha_fin);
        
        if (fecha < inicio || fecha > fin) {
            return {
                valido: false,
                mensaje: `La fecha debe estar entre ${formatDate(competencia.fecha_inicio)} y ${formatDate(competencia.fecha_fin)}`
            };
        }
        
        return { valido: true, mensaje: '' };
    } catch (error) {
        console.error('Error validando fecha:', error);
        return { valido: true, mensaje: '' };
    }
}

/**
 * Subir archivo a entregable
 */
async function subirArchivo(deliverable_id, file) {
    try {
        const token = auth.getToken();
        if (!token) {
            showAlert('#alertContainer', 'danger', 'Token no disponible. Por favor inicia sesión.');
            return null;
        }

        // Validar MIME type
        if (!validarMimeType(file.type)) {
            const allowed = (window.SGPI_SETTINGS?.allowed_file_types || ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip']).join(', ').toUpperCase();
            showAlert('#alertContainer', 'danger', `Tipo de archivo no permitido. Permitidos: ${allowed}`);
            return null;
        }

        // Validar tamaño
        if (!validarTamañoArchivo(file.size)) {
            const maxSizeMb = Number(window.SGPI_SETTINGS?.max_file_size_mb || 50);
            showAlert('#alertContainer', 'danger', `Archivo muy grande. Máximo ${maxSizeMb}MB, tu archivo es ${formatearTamaño(file.size)}`);
            return null;
        }

        const formData = new FormData();
        formData.append('archivo', file);

        const response = await fetch(`${API_BASE_URL}/deliverables/${deliverable_id}/upload`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        });

        if (!response.ok) {
            const error = await response.json();
            showAlert('#alertContainer', 'danger', 'Error: ' + (error.error || error.message || response.statusText));
            return null;
        }

        const data = await response.json();
        showAlert('#alertContainer', 'success', 'Archivo subido exitosamente.');
        return data;
    } catch (error) {
        console.error('Error:', error);
        showAlert('#alertContainer', 'danger', 'Error al subir: ' + error.message);
        return null;
    }
}

/**
 * Calificar entregable
 */
async function calificarEntregable(deliverable_id, calificacion) {
    try {
        if (!validarCalificacion(calificacion)) {
            showAlert('#alertContainer', 'danger', 'Calificación debe ser un número entre 0 y 100.');
            return null;
        }

        const token = auth.getToken();
        if (!token) {
            showAlert('#alertContainer', 'danger', 'Token no disponible. Por favor inicia sesión.');
            return null;
        }

        const response = await fetch(`${API_BASE_URL}/deliverables/${deliverable_id}/calificar`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ calificacion: parseFloat(calificacion) })
        });

        if (!response.ok) {
            const error = await response.json();
            showAlert('#alertContainer', 'danger', 'Error: ' + (error.error || error.message || response.statusText));
            return null;
        }

        const data = await response.json();
        showAlert('#alertContainer', 'success', 'Entregable calificado exitosamente.');
        return data;
    } catch (error) {
        console.error('Error:', error);
        showAlert('#alertContainer', 'danger', 'Error al calificar: ' + error.message);
        return null;
    }
}

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('appSidebar');
    const profilePhoto = sidebar?.querySelector('.sidebar-profile-photo');
    const desktopQuery = window.matchMedia('(min-width: 769px)');
    let collapseTimer = null;

    if (!sidebar) return;

    function isDesktop() {
        return desktopQuery.matches;
    }

    function sidebarLabel(element) {
        return element.querySelector('span')?.textContent?.trim()
            || element.querySelector('strong')?.textContent?.trim()
            || element.getAttribute('aria-label')
            || '';
    }

    function setItemTitles() {
        sidebar.querySelectorAll('.sidebar-item, .sidebar-group summary').forEach(item => {
            const label = sidebarLabel(item);
            if (label && !item.getAttribute('title')) item.setAttribute('title', label);
        });
    }

    function clearCollapseTimer() {
        if (collapseTimer) {
            clearTimeout(collapseTimer);
            collapseTimer = null;
        }
    }

    function collapseSidebar() {
        if (!isDesktop()) return;
        sidebar.classList.add('sidebar-collapsed');
        sidebar.classList.remove('sidebar-expanded');
        if (profilePhoto) {
            profilePhoto.setAttribute('role', 'button');
            profilePhoto.setAttribute('tabindex', '0');
            profilePhoto.setAttribute('title', 'Expandir menu');
            profilePhoto.setAttribute('aria-label', 'Expandir menu lateral');
        }
    }

    function expandSidebar(scheduleCollapse = true) {
        if (!isDesktop()) return;
        sidebar.classList.remove('sidebar-collapsed');
        sidebar.classList.add('sidebar-expanded');
        if (profilePhoto) {
            profilePhoto.removeAttribute('role');
            profilePhoto.removeAttribute('tabindex');
            profilePhoto.removeAttribute('title');
            profilePhoto.removeAttribute('aria-label');
        }
        clearCollapseTimer();
        if (scheduleCollapse) {
            collapseTimer = setTimeout(collapseSidebar, 4500);
        }
    }

    function keepOpenBriefly() {
        if (!isDesktop() || sidebar.classList.contains('sidebar-collapsed')) return;
        expandSidebar(true);
    }

    function syncResponsiveState() {
        clearCollapseTimer();
        if (isDesktop()) {
            collapseSidebar();
        } else {
            sidebar.classList.remove('sidebar-collapsed', 'sidebar-expanded');
            if (profilePhoto) {
                profilePhoto.removeAttribute('role');
                profilePhoto.removeAttribute('tabindex');
                profilePhoto.removeAttribute('title');
                profilePhoto.removeAttribute('aria-label');
            }
        }
    }

    setItemTitles();
    syncResponsiveState();

    profilePhoto?.addEventListener('click', event => {
        if (!isDesktop() || !sidebar.classList.contains('sidebar-collapsed')) return;
        event.preventDefault();
        expandSidebar(true);
    });

    profilePhoto?.addEventListener('keydown', event => {
        if (!isDesktop() || !sidebar.classList.contains('sidebar-collapsed')) return;
        if (!['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        expandSidebar(true);
    });

    sidebar.addEventListener('click', event => {
        if (!isDesktop() || !sidebar.classList.contains('sidebar-collapsed')) return;
        const interactiveItem = event.target.closest('.sidebar-item, .sidebar-group summary');
        if (!interactiveItem) return;
        event.preventDefault();
        expandSidebar(true);
    });

    sidebar.addEventListener('mousemove', keepOpenBriefly, { passive: true });
    sidebar.addEventListener('focusin', () => expandSidebar(true));
    sidebar.addEventListener('mouseleave', () => {
        if (!isDesktop()) return;
        clearCollapseTimer();
        collapseTimer = setTimeout(collapseSidebar, 1400);
    }, { passive: true });

    if (desktopQuery.addEventListener) {
        desktopQuery.addEventListener('change', syncResponsiveState);
    } else {
        desktopQuery.addListener(syncResponsiveState);
    }
});
