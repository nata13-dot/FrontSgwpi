<?php
// Página no encontrada. La sirven nginx (error_page 404) e index.php cuando la dirección no existe.
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
header('X-Robots-Tag: noindex');

$signedIn = is_authenticated();
$homeUrl = $signedIn ? dashboard_url() : '/';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada · <?= APP_NAME ?></title>
    <?php require_once __DIR__ . '/includes/visual-preferences.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --n-navy: #1B396A; --n-navy-2: #2D5A96; --n-deep: #0c2142; --n-sky: #e9f0fb; --n-bg: #f3f6fb;
            --n-surface: #ffffff; --n-ink: #2b3040; --n-muted: #5b6474; --n-line: #e2e7f0;
            --n-shadow: 0 1px 2px rgba(16, 30, 54, .05), 0 4px 10px rgba(16, 30, 54, .05), 0 14px 32px rgba(16, 30, 54, .08);
            --n-shadow-lg: 0 10px 24px rgba(16, 30, 54, .14), 0 28px 64px rgba(16, 30, 54, .18);
        }
        :root[data-theme="dark"] {
            --n-navy: #9cc2ff; --n-navy-2: #79a9ef; --n-sky: #1c273c; --n-bg: #0f1522; --n-surface: #172033;
            --n-ink: #edf2f7; --n-muted: #9aa6b9; --n-line: #2c3850;
            --n-shadow: 0 1px 2px rgba(0, 0, 0, .35), 0 16px 36px rgba(0, 0, 0, .3); --n-shadow-lg: 0 30px 70px rgba(0, 0, 0, .5);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100vh; display: flex; flex-direction: column; color: var(--n-ink);
            font-family: 'Segoe UI', system-ui, -apple-system, Roboto, 'Helvetica Neue', sans-serif;
            background:
                radial-gradient(60% 50% at 85% 10%, rgba(45, 90, 150, .12), transparent 70%),
                radial-gradient(50% 45% at 10% 90%, rgba(45, 90, 150, .10), transparent 70%),
                var(--n-bg);
        }
        a { color: inherit; }
        .nf-top {
            display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px clamp(16px, 4vw, 40px);
            background: var(--n-surface); box-shadow: 0 6px 24px rgba(16, 30, 54, .08); border-radius: 0 0 24px 24px;
        }
        .nf-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
        .nf-brand img { width: 46px; height: 46px; border-radius: 14px; padding: 4px; background: #fff; box-shadow: 0 6px 16px rgba(16, 30, 54, .14); }
        .nf-brand strong { display: block; color: var(--n-navy); font-size: 1.02rem; font-weight: 800; letter-spacing: -.01em; }
        .nf-brand small { display: block; color: var(--n-muted); font-size: .8rem; }
        .nf-top-link {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 14px; text-decoration: none;
            font-weight: 700; color: var(--n-navy); background: var(--n-sky); white-space: nowrap; transition: box-shadow .18s, transform .12s;
        }
        .nf-top-link:hover { box-shadow: var(--n-shadow); transform: translateY(-1px); }
        .nf-main { flex: 1; display: grid; place-items: center; padding: clamp(24px, 5vw, 56px) clamp(16px, 4vw, 40px); }
        .nf-card {
            width: min(1080px, 100%); display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr); align-items: center; gap: clamp(16px, 4vw, 48px);
            padding: clamp(20px, 4vw, 44px); border-radius: 32px; background: var(--n-surface); box-shadow: var(--n-shadow-lg); position: relative; overflow: hidden;
        }
        .nf-card::before {
            content: ''; position: absolute; inset: 0 auto 0 0; width: 6px; background: linear-gradient(180deg, var(--n-navy), var(--n-navy-2));
        }
        .nf-art { position: relative; display: grid; place-items: center; }
        .nf-art::before {
            content: ''; position: absolute; width: 88%; aspect-ratio: 1; border-radius: 50%;
            background: radial-gradient(circle at 45% 40%, var(--n-sky) 0%, rgba(233, 240, 251, .55) 50%, transparent 72%);
        }
        .nf-art img {
            position: relative; width: min(100%, 420px); height: auto; display: block; animation: nf-float 5s ease-in-out infinite;
            /* El fondo claro de la ilustración se desvanece hacia la tarjeta. */
            -webkit-mask-image: linear-gradient(to right, transparent 0, #000 24%, #000 92%, transparent 100%), linear-gradient(to bottom, #000 86%, transparent 100%);
            -webkit-mask-composite: source-in;
            mask-image: linear-gradient(to right, transparent 0, #000 24%, #000 92%, transparent 100%), linear-gradient(to bottom, #000 86%, transparent 100%);
            mask-composite: intersect;
            mix-blend-mode: multiply;
        }
        :root[data-theme="dark"] .nf-art img { mix-blend-mode: normal; filter: brightness(.94); border-radius: 28px; -webkit-mask-image: none; mask-image: none; box-shadow: 0 18px 40px rgba(0, 0, 0, .45); }
        .nf-code {
            margin: 0; font-size: clamp(5rem, 14vw, 9.5rem); line-height: .9; font-weight: 900; letter-spacing: -.04em;
            background: linear-gradient(135deg, var(--n-deep) 0%, var(--n-navy) 45%, var(--n-navy-2) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;
            filter: drop-shadow(0 10px 18px rgba(27, 57, 106, .22));
        }
        :root[data-theme="dark"] .nf-code { background: linear-gradient(135deg, #cfe0ff, #79a9ef); -webkit-background-clip: text; background-clip: text; }
        .nf-chip {
            display: inline-flex; align-items: center; gap: 10px; margin: 18px 0 14px; padding: 10px 18px; border-radius: 16px;
            background: linear-gradient(135deg, var(--n-navy), var(--n-navy-2)); color: #fff; font-size: clamp(1.05rem, 2.4vw, 1.4rem); font-weight: 800;
            box-shadow: 0 2px 4px rgba(16, 30, 54, .10), 0 10px 22px rgba(27, 57, 106, .25); transform: rotate(-1.5deg);
        }
        .nf-text { margin: 0 0 24px; color: var(--n-muted); font-size: 1.04rem; line-height: 1.6; max-width: 46ch; }
        .nf-actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .nf-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 50px; padding: 0 22px; border-radius: 16px; border: 0;
            font: inherit; font-weight: 800; font-size: 1rem; text-decoration: none; cursor: pointer; transition: box-shadow .18s, transform .12s;
        }
        .nf-btn.primary { color: #fff; background: linear-gradient(135deg, var(--n-navy), var(--n-navy-2)); box-shadow: 0 2px 4px rgba(16, 30, 54, .10), 0 10px 22px rgba(27, 57, 106, .28); }
        :root[data-theme="dark"] .nf-btn.primary { background: linear-gradient(135deg, #2D5A96, #4f86d6); }
        .nf-btn.ghost { color: var(--n-navy); background: var(--n-surface); border: 1px solid var(--n-line); box-shadow: 0 2px 8px rgba(16, 30, 54, .06); }
        .nf-btn:hover { transform: translateY(-2px); box-shadow: 0 2px 4px rgba(16, 30, 54, .06), 0 12px 26px rgba(16, 30, 54, .16); }
        .nf-btn:focus-visible, .nf-top-link:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(45, 90, 150, .3); }
        .nf-hints { margin: 22px 0 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 8px 18px; color: var(--n-muted); font-size: .88rem; }
        .nf-hints a { color: var(--n-navy); font-weight: 700; text-decoration: none; }
        .nf-hints a:hover { text-decoration: underline; }
        .nf-foot {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px; padding: 18px clamp(16px, 4vw, 40px);
            color: #dfe8f7; background: radial-gradient(120% 140% at 0% 0%, rgba(77, 132, 214, .35), transparent 60%), linear-gradient(135deg, #0c2142, #1B396A 60%, #2D5A96);
            border-radius: 24px 24px 0 0; box-shadow: 0 -8px 24px rgba(16, 30, 54, .12); font-size: .88rem;
        }
        .nf-foot strong { color: #fff; }
        @keyframes nf-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @media (prefers-reduced-motion: reduce) { .nf-art img { animation: none; } .nf-btn, .nf-top-link { transition: none; } }
        @media (max-width: 760px) {
            .nf-card { grid-template-columns: 1fr; text-align: center; }
            .nf-art img { width: min(78%, 300px); }
            .nf-text { margin-left: auto; margin-right: auto; }
            .nf-actions, .nf-hints { justify-content: center; }
            .nf-btn { flex: 1 1 100%; }
            .nf-brand small { display: none; }
            .nf-top-link span { display: none; }
        }
    </style>
</head>
<body>
    <header class="nf-top">
        <a class="nf-brand" href="<?= htmlspecialchars($homeUrl) ?>">
            <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT">
            <span><strong>Gestión de Proyectos Integradores</strong><small>Instituto Tecnológico Superior de San Martín Texmelucan</small></span>
        </a>
        <a class="nf-top-link" href="<?= htmlspecialchars($homeUrl) ?>"><i class="bi <?= $signedIn ? 'bi-speedometer2' : 'bi-box-arrow-in-right' ?>" aria-hidden="true"></i><span><?= $signedIn ? 'Ir a mi panel' : 'Iniciar sesión' ?></span></a>
    </header>

    <main class="nf-main">
        <section class="nf-card" aria-labelledby="nfTitle">
            <div class="nf-art">
                <img src="<?= asset('/assets/img/mascota/mascota-404.webp') ?>" alt="La mascota del SGPI, confundida, revisa un libro buscando la página" width="620" height="672">
            </div>
            <div>
                <p class="nf-code" aria-hidden="true">404</p>
                <h1 class="nf-chip" id="nfTitle"><i class="bi bi-signpost-split" aria-hidden="true"></i> ¡Página no encontrada!</h1>
                <p class="nf-text">La dirección que buscas no existe, cambió de lugar o ya no está disponible. Revisa que esté bien escrita o vuelve al inicio.</p>
                <div class="nf-actions">
                    <a class="nf-btn primary" href="<?= htmlspecialchars($homeUrl) ?>"><i class="bi bi-house-door-fill" aria-hidden="true"></i> Volver al inicio</a>
                    <button type="button" class="nf-btn ghost" onclick="history.length > 1 ? history.back() : location.assign('<?= htmlspecialchars($homeUrl, ENT_QUOTES) ?>')"><i class="bi bi-arrow-left" aria-hidden="true"></i> Regresar</button>
                </div>
                <ul class="nf-hints">
                    <li><a href="<?= htmlspecialchars(page_url('/pages/repositorio.php')) ?>"><i class="bi bi-archive" aria-hidden="true"></i> Repositorio de proyectos</a></li>
                    <?php if (!$signedIn): ?><li><a href="/index.php#recover"><i class="bi bi-shield-lock" aria-hidden="true"></i> Recuperar mi acceso</a></li><?php endif; ?>
                </ul>
            </div>
        </section>
    </main>

    <footer class="nf-foot">
        <span><strong>© <?= date('Y') ?> ITSSMT</strong> · Sistema de Gestión de Proyectos Integradores</span>
        <span>#OrgulloHalcón</span>
    </footer>
</body>
</html>
