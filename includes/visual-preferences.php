<link rel="icon" type="image/webp" href="/assets/img/ITSSMT/ITSSMT-192.webp">
<link rel="shortcut icon" type="image/webp" href="/assets/img/ITSSMT/ITSSMT-192.webp">
<link rel="apple-touch-icon" href="/assets/img/ITSSMT/ITSSMT-192.webp">
<style>
:root {
    --career-primary: <?= htmlspecialchars(active_career()['color_primario'] ?? '#1B396A') ?> !important;
    --career-secondary: <?= htmlspecialchars(active_career()['color_secundario'] ?? '#2D5A96') ?> !important;
    --career-accent: <?= htmlspecialchars(active_career()['color_acento'] ?? '#00A6D6') ?> !important;
    --primary-blue: var(--career-primary) !important;
    --secondary-blue: var(--career-secondary) !important;
    --dashboard-accent: var(--career-primary) !important;
    --dashboard-accent-soft: var(--career-secondary) !important;
}

html[data-theme="dark"] {
    background: #111827;
    color-scheme: dark;
}

html[data-theme="light"] {
    background: #ffffff;
    color-scheme: light;
}

html.grayscale-mode {
    filter: grayscale(1);
}
</style>
<script>
// Apariencia: tema claro, oscuro o según el dispositivo, y escala personal sobre la letra del navegador.
// Se guarda en la cuenta (/appearance) y se copia aquí para aplicarla antes de pintar la página.
window.SGPIAppearance = (function () {
    var root = document.documentElement;
    var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var read = function (key) { try { return localStorage.getItem(key); } catch (error) { return null; } };
    var write = function (key, value) { try { localStorage.setItem(key, value); } catch (error) { /* Dura hasta cerrar. */ } };

    function theme() {
        var value = read('sgpi-theme');
        return value === 'dark' || value === 'system' ? value : 'light';
    }
    function fontScale() { return Number(read('sgpi-font-scale')) || 100; }
    function apply() {
        var resolved = theme() === 'system' ? (media && media.matches ? 'dark' : 'light') : theme();
        root.dataset.theme = resolved;
        root.style.colorScheme = resolved;
        root.style.fontSize = fontScale() + '%';
        window.dispatchEvent(new CustomEvent('sgpi:appearance'));
    }
    function set(next) {
        if (next.theme) write('sgpi-theme', next.theme);
        if (next.fontScale) write('sgpi-font-scale', String(next.fontScale));
        apply();
    }
    if (media && media.addEventListener) media.addEventListener('change', apply);
    try {
        if (JSON.parse(read('sgpi-public-settings') || '{}').grayscale_mode) root.classList.add('grayscale-mode');
    } catch (error) { /* Sin ajustes guardados se muestra a color. */ }
    apply();
    return { theme: theme, fontScale: fontScale, set: set };
})();
</script>
