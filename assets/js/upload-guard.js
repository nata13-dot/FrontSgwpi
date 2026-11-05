// Ningún formulario acepta archivos vacíos. Se revisa al elegirlos, antes de que cada página los procese; la API también los rechaza.
(() => {
    document.addEventListener('change', event => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files?.length) return;

        const empty = [...input.files].find(file => file.size === 0);
        if (!empty) return;

        input.value = '';
        event.stopImmediatePropagation();
        const message = `El archivo «${empty.name}» está vacío. Elige otro.`;
        if (window.Swal) window.Swal.fire({ icon: 'warning', title: 'Archivo vacío', text: message });
        else window.alert(message);
    }, true);
})();
