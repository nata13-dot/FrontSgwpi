<style>
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
(function () {
    try {
        var root = document.documentElement;
        var cached = JSON.parse(localStorage.getItem('sgpi-public-settings') || '{}');
        var storedTheme = localStorage.getItem('sgpi-theme');
        var theme = storedTheme || cached.default_theme || root.dataset.theme || 'light';

        if (theme === 'system') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        root.dataset.theme = theme;
        root.style.colorScheme = theme;

        if (cached.grayscale_mode) {
            root.classList.add('grayscale-mode');
        }

        if (cached.font_scale) {
            root.style.fontSize = cached.font_scale + '%';
        }
    } catch (error) {
        document.documentElement.dataset.theme = localStorage.getItem('sgpi-theme') || 'light';
    }
})();
</script>
