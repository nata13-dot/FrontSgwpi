<!-- Modal genérico reutilizable -->
<div class="modal fade" id="genericModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header" style="background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); color: white; border: 0;">
                <h5 class="modal-title">Modal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="confirmModal()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(title, content, callback) {
    document.getElementById('genericModal').querySelector('.modal-title').textContent = title;
    document.getElementById('modalBody').innerHTML = content;
    window.modalCallback = callback;
    new bootstrap.Modal(document.getElementById('genericModal')).show();
}

function confirmModal() {
    if (window.modalCallback) {
        window.modalCallback();
    }
    bootstrap.Modal.getInstance(document.getElementById('genericModal')).hide();
}
</script>