<link rel="icon" type="image/webp" href="/assets/img/ITSSMT/ITSSMT.webp">
<link rel="shortcut icon" type="image/webp" href="/assets/img/ITSSMT/ITSSMT.webp">
<link rel="apple-touch-icon" href="/assets/img/ITSSMT/ITSSMT.webp">
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
