/**
 * Clase para gestionar tablas dinámicas
 */
class DynamicTable {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.data = [];
        this.page = 1;
        this.perPage = 10;
    }

    /**
     * Cargar datos
     */
    async load(endpoint, filters = {}) {
        try {
            const response = await api.get(endpoint, { 
                page: this.page, 
                per_page: this.perPage,
                ...filters 
            });
            this.data = response.data || [];
            this.render();
        } catch (error) {
            console.error('Error al cargar tabla:', error);
            showAlert(this.container, 'danger', 'Error al cargar los datos');
        }
    }

    /**
     * Renderizar tabla
     */
    render() {
        if (!this.container) return;

        if (this.data.length === 0) {
            this.container.innerHTML = '<p class="text-center text-muted">No hay registros</p>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
        
        // Encabezados
        const headers = Object.keys(this.data[0]);
        headers.forEach(header => {
            html += `<th>${header}</th>`;
        });
        html += '<th>Acciones</th></tr></thead><tbody>';

        // Filas
        this.data.forEach(row => {
            html += '<tr>';
            headers.forEach(header => {
                html += `<td>${row[header] || '-'}</td>`;
            });
            html += `<td><button class="btn btn-sm btn-outline-primary">Editar</button></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        this.container.innerHTML = html;
    }

    /**
     * Ir a página
     */
    goToPage(page) {
        this.page = page;
        this.load();
    }

    /**
     * Siguiente página
     */
    nextPage() {
        this.page++;
        this.load();
    }

    /**
     * Página anterior
     */
    prevPage() {
        if (this.page > 1) {
            this.page--;
            this.load();
        }
    }
}