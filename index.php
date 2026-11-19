<?php
// nginx manda aquí toda dirección que no existe (try_files): sólo «/» e «/index.php» son la portada.
$sgpiIndexPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (!in_array($sgpiIndexPath, ['/', '/index.php'], true)) {
    require __DIR__ . '/404.php';
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$serverAuthenticated = is_authenticated();
$serverDashboardUrl = dashboard_url();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Inicio</title>
    <?php if ($serverAuthenticated): ?>
    <script>
        (function () {
            window.location.replace('<?= $serverDashboardUrl ?>');
        })();
    </script>
    <?php endif; ?>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
    <style>
        .login-modal {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .login-brand img {
            height: 54px;
            object-fit: contain;
        }

        .index-hero {
            background-color: #0b2548;
            background-image: url('/assets/img/ITSSMT/fondo.webp');
            background-size: cover;
            background-position: center 42%;
            min-height: min(660px, calc(100svh - 82px));
            padding: 72px 0;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .index-hero .overlay {
            background:
                linear-gradient(90deg, rgba(5, 23, 49, 0.94) 0%, rgba(7, 30, 61, 0.82) 38%, rgba(8, 31, 59, 0.35) 68%, rgba(8, 26, 54, 0.16) 100%),
                linear-gradient(0deg, rgba(5, 20, 42, 0.2), transparent 45%);
        }

        .index-hero-content {
            position: relative;
            z-index: 1;
            max-width: 900px;
        }

        .index-hero h1 {
            font-size: clamp(3rem, 4.3vw, 4.65rem);
            letter-spacing: -0.045em;
            line-height: 1.03;
            text-wrap: balance;
        }

        .index-hero .lead {
            font-size: clamp(1.05rem, 1.35vw, 1.28rem);
            line-height: 1.6;
            max-width: 760px;
            text-wrap: pretty;
        }

        .repository-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .hero-actions .btn {
            align-items: center;
            display: inline-flex;
            font-weight: 700;
            gap: 0.45rem;
            justify-content: center;
            min-height: 54px;
            padding-left: 1.35rem;
            padding-right: 1.35rem;
        }

        .index-hero .container-xl { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 2.5rem; }
        .hero-tiles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; max-width: 720px; }
        .hero-tile {
            display: flex; align-items: center; gap: 0.85rem; padding: 0.95rem 1.05rem; border-radius: 18px; text-decoration: none;
            color: #fff; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(10px) saturate(1.2); -webkit-backdrop-filter: blur(10px) saturate(1.2);
            box-shadow: 0 10px 26px rgba(5, 20, 42, 0.28); transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .hero-tile:hover, .hero-tile:focus-visible { background: rgba(255, 255, 255, 0.95); color: #1B396A; transform: translateY(-3px); box-shadow: 0 16px 34px rgba(5, 20, 42, 0.4); outline: 0; }
        .hero-tile > i { width: 46px; height: 46px; flex: 0 0 46px; display: grid; place-items: center; border-radius: 14px; font-size: 1.3rem; background: rgba(255, 255, 255, 0.18); }
        .hero-tile:hover > i, .hero-tile:focus-visible > i { background: linear-gradient(135deg, #1B396A, #2D5A96); color: #fff; }
        .hero-tile span { display: flex; flex-direction: column; min-width: 0; }
        .hero-tile strong { font-size: 1.05rem; font-weight: 800; line-height: 1.2; }
        .hero-tile small { font-size: 0.86rem; opacity: 0.85; }
        .hero-links { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 1.2rem; margin-top: 1.1rem; color: rgba(255, 255, 255, 0.86); font-size: 0.98rem; }
        .hero-links a { color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; }
        .hero-link-strong {
            font-weight: 800; padding: 0.75rem 1.2rem; border-radius: 16px; color: #1B396A !important; background: #fff;
            box-shadow: 0 2px 6px rgba(5, 20, 42, 0.2), 0 16px 34px rgba(5, 20, 42, 0.35); transition: transform .15s ease;
        }
        .hero-link-strong:hover { transform: translateY(-2px); }
        .hero-total:not(:empty) { padding: 0.1rem 0.55rem; border-radius: 999px; background: #e8effa; font-size: 0.85rem; }
        .hero-link-note { display: inline-flex; align-items: center; gap: 0.4rem; }
        .hero-link-soft { font-weight: 600; text-decoration: underline !important; text-underline-offset: 3px; }
        .hero-latest {
            width: 340px; padding: 1.1rem; border-radius: 22px; color: #fff; background: rgba(8, 26, 54, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.18); backdrop-filter: blur(14px) saturate(1.2); -webkit-backdrop-filter: blur(14px) saturate(1.2);
            box-shadow: 0 20px 50px rgba(5, 20, 42, 0.45); position: relative; z-index: 1;
        }
        .hero-latest-title { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9; }
        .hero-latest ul { list-style: none; margin: 0.75rem 0 0; padding: 0; display: grid; gap: 0.5rem; }
        .hero-latest a { display: block; padding: 0.65rem 0.8rem; border-radius: 14px; text-decoration: none; color: #fff; background: rgba(255, 255, 255, 0.08); transition: background .15s ease; }
        .hero-latest a:hover { background: rgba(255, 255, 255, 0.18); }
        .hero-latest strong { display: block; font-size: 0.95rem; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .hero-latest small { opacity: 0.8; font-size: 0.8rem; }
        @media (max-width: 1199.98px) { .index-hero .container-xl { grid-template-columns: 1fr; } .hero-latest { display: none !important; } }
        @media (max-width: 575.98px) { .hero-tiles { grid-template-columns: 1fr; } .hero-tile { padding: 0.8rem 0.9rem; } }

        .repository-spotlight {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        [data-theme="dark"] .repository-spotlight {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        }

        .repository-showcase {
            border: 1px solid rgba(27, 57, 106, 0.14);
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        [data-theme="dark"] .repository-showcase {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-showcase-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(27, 57, 106, 0.12);
            background: rgba(27, 57, 106, 0.05);
        }

        [data-theme="dark"] .repository-showcase-toolbar {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
        }

        .repository-search-preview {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
            color: #64748b;
            background: #ffffff;
            border: 1px solid rgba(100, 116, 139, 0.22);
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
            font-size: 0.92rem;
        }

        [data-theme="dark"] .repository-search-preview {
            color: #cbd5e1;
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-showcase-body {
            display: grid;
            grid-template-columns: 0.85fr 1.15fr;
            min-height: 310px;
        }

        .repository-list-preview {
            border-right: 1px solid rgba(27, 57, 106, 0.12);
            padding: 0.85rem;
        }

        [data-theme="dark"] .repository-list-preview {
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-file-row {
            display: flex;
            gap: 0.7rem;
            padding: 0.75rem;
            border-radius: 8px;
            align-items: flex-start;
        }

        .repository-file-row.active {
            background: rgba(27, 57, 106, 0.08);
        }

        [data-theme="dark"] .repository-file-row.active {
            background: rgba(96, 165, 250, 0.12);
        }

        .repository-file-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1B396A;
            color: #ffffff;
            flex: 0 0 auto;
        }

        .repository-reader-preview {
            padding: 1.1rem;
            background: linear-gradient(135deg, rgba(27, 57, 106, 0.08), rgba(255, 255, 255, 0));
        }

        [data-theme="dark"] .repository-reader-preview {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.12), rgba(17, 24, 39, 0));
        }

        .repository-reader-page {
            background: #ffffff;
            border: 1px solid rgba(100, 116, 139, 0.18);
            border-radius: 8px;
            min-height: 250px;
            padding: 1.1rem;
        }

        [data-theme="dark"] .repository-reader-page {
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .repository-line {
            height: 10px;
            border-radius: 999px;
            background: #dbe4ef;
            margin-bottom: 0.7rem;
        }

        [data-theme="dark"] .repository-line {
            background: #334155;
        }

        .repository-benefit {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .repository-benefit i {
            color: #1B396A;
            font-size: 1.35rem;
            line-height: 1.2;
        }

        [data-theme="dark"] .repository-benefit i {
            color: #93c5fd;
        }

        .cookie-notice {
            position: fixed;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 1080;
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid rgba(27, 57, 106, 0.14);
            border-radius: 8px;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.18);
            padding: 1rem;
        }

        [data-theme="dark"] .cookie-notice {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.12);
            color: #f8fafc;
        }

        .cookie-notice[hidden] {
            display: none !important;
        }

        .cookie-notice p {
            margin: 0;
            color: inherit;
        }

        @media (min-width: 768px) {
            .cookie-notice {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .cookie-notice .btn {
                flex: 0 0 auto;
            }
        }

        @media (max-width: 991.98px) {
            .index-hero {
                background-position: 58% center;
                min-height: 560px;
                padding: 64px 0;
            }

            .index-hero-content {
                max-width: 720px;
            }

            .index-hero h1 {
                font-size: clamp(2.7rem, 7vw, 4rem);
            }

            .repository-showcase-body {
                grid-template-columns: 1fr;
            }

            .repository-list-preview {
                border-right: 0;
                border-bottom: 1px solid rgba(27, 57, 106, 0.12);
            }
        }

        @media (max-width: 575.98px) {
            .index-hero {
                background-position: 62% center;
                min-height: calc(100svh - 72px);
                padding: 52px 0 44px;
            }

            .index-hero h1 {
                font-size: clamp(2.35rem, 11vw, 3.15rem);
                letter-spacing: -0.035em;
                line-height: 1.06;
            }

            .index-hero .lead {
                font-size: 1rem;
                line-height: 1.55;
            }

            .repository-hero-kicker {
                font-size: 0.78rem;
            }

            .hero-actions .btn {
                width: 100%;
            }

            .repository-showcase-toolbar {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="index-hero">
        <div class="overlay"></div>
        <div class="container-xl">
            <div class="index-hero-content">
                <div class="repository-hero-kicker">
                    <i class="bi bi-globe2" aria-hidden="true"></i>
                    Repositorio público · acceso libre, sin cuenta
                </div>
                <h1 class="display-3 fw-bold text-white mb-3">Consulta los proyectos y evidencias académicas del instituto</h1>
                <p class="lead text-white mb-4">Cualquier persona puede buscar y leer los proyectos integradores, tesis y documentos que publica el ITSSMT. No necesitas registrarte.</p>
                <nav class="hero-tiles" aria-label="Qué hay en el repositorio">
                    <a class="hero-tile" href="/pages/repositorio.php?categoria=desarrollo"><i class="bi bi-diagram-3" aria-hidden="true"></i><span><strong>Proyectos integradores</strong><small data-repo-count="desarrollo">Ver documentos</small></span></a>
                    <a class="hero-tile" href="/pages/repositorio.php?categoria=tesis"><i class="bi bi-mortarboard" aria-hidden="true"></i><span><strong>Tesis</strong><small data-repo-count="tesis">Ver documentos</small></span></a>
                    <a class="hero-tile" href="/pages/repositorio.php?categoria=residencias"><i class="bi bi-briefcase" aria-hidden="true"></i><span><strong>Residencias</strong><small data-repo-count="residencias">Ver documentos</small></span></a>
                    <a class="hero-tile" href="/pages/repositorio.php?categoria=general"><i class="bi bi-journal-text" aria-hidden="true"></i><span><strong>Material general</strong><small data-repo-count="general">Ver documentos</small></span></a>
                </nav>
                <div class="hero-links">
                    <a href="/pages/repositorio.php" class="hero-link-strong"><i class="bi bi-archive" aria-hidden="true"></i> Ver todo el repositorio <span class="hero-total" data-repo-count="total"></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    <span class="hero-link-note"><i class="bi bi-unlock" aria-hidden="true"></i> Lectura y descarga de documentos públicos</span>
                    <a href="/pages/register.php" id="selfRegistrationHero" class="hero-link-soft d-none"><i class="bi bi-person-plus" aria-hidden="true"></i> ¿Eres alumno? Crea tu cuenta</a>
                </div>
            </div>
            <aside class="hero-latest d-none" id="heroLatest" aria-label="Documentos publicados recientemente">
                <span class="hero-latest-title"><i class="bi bi-stars" aria-hidden="true"></i> Recién publicados</span>
                <ul id="heroLatestList"></ul>
            </aside>
        </div>
    </div>

    <div class="section repository-spotlight">
        <div class="container-xl">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <h2 class="section-title mb-3">Repositorio Digital</h2>
                    <p class="section-subtitle mb-4">Un espacio para encontrar proyectos, revisar entregables y mantener disponible la memoria académica del instituto.</p>
                    <div class="d-grid gap-3 mb-4">
                        <div class="repository-benefit">
                            <i class="bi bi-file-earmark-text"></i>
                            <div>
                                <h5 class="mb-1">Documentos organizados</h5>
                                <p class="text-muted mb-0">Consulta archivos por proyecto, categoría, periodo y datos clave.</p>
                            </div>
                        </div>
                        <div class="repository-benefit">
                            <i class="bi bi-eye"></i>
                            <div>
                                <h5 class="mb-1">Lectura rápida</h5>
                                <p class="text-muted mb-0">Previsualiza evidencias y materiales sin perder el contexto del sistema.</p>
                            </div>
                        </div>
                        <div class="repository-benefit">
                            <i class="bi bi-mortarboard"></i>
                            <div>
                                <h5 class="mb-1">Referencia académica</h5>
                                <p class="text-muted mb-0">Facilita que nuevas generaciones conozcan proyectos previos y buenas prácticas.</p>
                            </div>
                        </div>
                    </div>
                    <a href="/pages/repositorio.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-archive"></i> Ir al repositorio
                    </a>
                </div>
                <div class="col-lg-7">
                    <div class="repository-showcase" aria-label="Vista previa del repositorio digital">
                        <div class="repository-showcase-toolbar">
                            <div class="repository-search-preview">
                                <i class="bi bi-search"></i>
                                Buscar por proyecto, autor, empresa o documento
                            </div>
                            <span class="badge bg-primary">Repositorio</span>
                        </div>
                        <div class="repository-showcase-body">
                            <div class="repository-list-preview">
                                <div class="repository-file-row active">
                                    <span class="repository-file-icon"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <div>
                                        <div class="fw-semibold">Proyecto integrador</div>
                                        <div class="text-muted small">Reporte final · Evidencia académica</div>
                                    </div>
                                </div>
                                <div class="repository-file-row">
                                    <span class="repository-file-icon"><i class="bi bi-file-earmark-slides"></i></span>
                                    <div>
                                        <div class="fw-semibold">Presentación</div>
                                        <div class="text-muted small">Material de exposición</div>
                                    </div>
                                </div>
                                <div class="repository-file-row">
                                    <span class="repository-file-icon"><i class="bi bi-tags"></i></span>
                                    <div>
                                        <div class="fw-semibold">Etiquetas y categorías</div>
                                        <div class="text-muted small">Búsqueda por áreas y entregables</div>
                                    </div>
                                </div>
                            </div>
                            <div class="repository-reader-preview">
                                <div class="repository-reader-page">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge bg-light text-dark">Vista de lectura</span>
                                        <span class="text-muted small">Documento consultable</span>
                                    </div>
                                    <div class="repository-line" style="width: 76%;"></div>
                                    <div class="repository-line" style="width: 92%;"></div>
                                    <div class="repository-line" style="width: 64%;"></div>
                                    <div class="repository-line mt-4" style="width: 88%;"></div>
                                    <div class="repository-line" style="width: 82%;"></div>
                                    <div class="repository-line" style="width: 54%;"></div>
                                    <div class="d-flex flex-wrap gap-2 mt-4">
                                        <span class="badge bg-secondary">PDF</span>
                                        <span class="badge bg-secondary">Entregable</span>
                                        <span class="badge bg-secondary">Proyecto</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="container-xl">
            <h2 class="section-title text-center mb-5">Gestión académica conectada al repositorio</h2>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-folder2" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Gestión de proyectos</h5>
                            <p class="text-muted">Administra proyectos integradores y conserva sus evidencias en un mismo flujo.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Evaluación documentada</h5>
                            <p class="text-muted">Relaciona entregables, rúbricas y resultados para dar seguimiento académico.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people" style="font-size: 3rem; color: #1B396A;"></i>
                            <h5 class="mt-3">Colaboracion</h5>
                            <p class="text-muted">Facilita el trabajo entre estudiantes, docentes y responsables de evaluación.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cookie-notice" id="cookieNotice" role="status" aria-live="polite" hidden>
        <p class="small">
            Este sistema usa cookies necesarias para mantener tu sesión iniciada y proteger el acceso a tu cuenta.
        </p>
        <button type="button" class="btn btn-primary btn-sm mt-3 mt-md-0" id="cookieNoticeAccept">
            Entendido
        </button>
    </div>

    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal p-3">
                <div class="modal-body">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div class="login-brand text-center mb-3">
                        <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="ITSSMT">
                        <h4 class="mt-2" id="loginModalLabel">Bienvenido</h4>
                        <p class="text-muted mb-0">Ingresa tus credenciales para continuar</p>
                    </div>

                    <div id="loginMessageContainer" class="mb-3"></div>

                    <form id="loginForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="loginUserId">Número de control o empleado</label>
                            <input type="text" class="form-control" id="loginUserId" name="username" placeholder="Escribe tu número" required autocomplete="username" autocapitalize="none" spellcheck="false">
                            <div class="invalid-feedback">Ingresa tu número de control o empleado.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="loginPassword">Contraseña</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Escribe tu contraseña" required autocomplete="current-password">
                            <div class="invalid-feedback">Ingresa tu contraseña.</div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mb-3">
                            <button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="openPasswordRecoveryModal()">¿Olvidaste tu contraseña?</button>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginSubmitBtn">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/pages/register.php" id="selfRegistrationLink" class="d-none btn btn-outline-primary w-100 mb-2"><i class="bi bi-person-plus"></i> ¿Eres alumno y no tienes cuenta? Crear cuenta</a>
                        <small class="text-muted">Acceso exclusivo para usuarios registrados del sistema.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordRecoveryModal" tabindex="-1" aria-labelledby="passwordRecoveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal recovery-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordRecoveryModalLabel"><i class="bi bi-shield-lock" aria-hidden="true"></i> Recupera tu acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <ol class="recovery-steps" id="recoverySteps">
                        <li class="current"><span>1</span><small>Tus datos</small></li>
                        <li><span>2</span><small>Código</small></li>
                        <li><span>3</span><small>Contraseña</small></li>
                    </ol>
                    <div id="recoveryMessage" aria-live="polite"></div>

                    <form id="recoveryDataForm" class="needs-validation" novalidate>
                        <p class="text-muted small">Confirma los datos de tu cuenta y te enviaremos un código a tu correo.</p>
                        <div class="mb-3">
                            <label class="form-label" for="recoveryUserId">Número de control o empleado</label>
                            <input type="text" class="form-control" id="recoveryUserId" name="username" placeholder="Escribe tu número" maxlength="10" required autocomplete="username" autocapitalize="none" spellcheck="false">
                            <div class="invalid-feedback">Escribe tu número de control o empleado.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="recoveryEmail">Correo de tu cuenta</label>
                            <input type="email" class="form-control" id="recoveryEmail" name="email" placeholder="El correo registrado en tu cuenta" required autocomplete="email" autocapitalize="none" spellcheck="false">
                            <div class="invalid-feedback">Escribe un correo válido.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="recoverySendBtn"><i class="bi bi-send" aria-hidden="true"></i> Enviarme el código</button>
                        </div>
                    </form>

                    <form id="recoveryCodeForm" class="needs-validation" novalidate hidden>
                        <p class="text-muted small">Escribe el código de seis dígitos que enviamos a <strong id="recoveryEmailShown"></strong>. Vence en 15 minutos; si no lo ves, revisa tu carpeta de spam.</p>
                        <div class="mb-3">
                            <label class="form-label" for="recoveryCode">Código</label>
                            <input type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" class="form-control recovery-code" id="recoveryCode" name="one-time-code" placeholder="000000" required autocomplete="one-time-code">
                            <div class="invalid-feedback">Escribe los seis dígitos.</div>
                        </div>
                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="recoveryVerifyBtn"><i class="bi bi-check2-circle" aria-hidden="true"></i> Verificar código</button>
                        </div>
                        <div class="recovery-links">
                            <button type="button" class="btn btn-link btn-sm p-0" id="recoveryResend" disabled>Reenviar código</button>
                            <button type="button" class="btn btn-link btn-sm p-0" id="recoveryChangeData">Corregir mis datos</button>
                        </div>
                    </form>

                    <form id="recoveryPasswordForm" novalidate hidden>
                        <p class="text-muted small">Crea tu nueva contraseña. Al guardarla entrarás al sistema y se cerrará tu sesión en la app.</p>
                        <div class="mb-3">
                            <label class="form-label" for="recoveryPassword">Nueva contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="recoveryPassword" placeholder="Escribe tu nueva contraseña" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-recovery-toggle="recoveryPassword" aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="recoveryPasswordConfirm">Confirmar contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="recoveryPasswordConfirm" placeholder="Repite la contraseña" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-recovery-toggle="recoveryPasswordConfirm" aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <ul class="recovery-rules">
                            <li id="recoveryRuleLength"><i class="bi bi-circle" aria-hidden="true"></i> Al menos 8 caracteres</li>
                            <li id="recoveryRuleMatch"><i class="bi bi-circle" aria-hidden="true"></i> Las dos contraseñas coinciden</li>
                        </ul>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="recoverySaveBtn"><i class="bi bi-save" aria-hidden="true"></i> Guardar y entrar</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-link btn-sm text-muted text-decoration-none" id="recoveryBackToLogin"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a iniciar sesión</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="<?= asset('/assets/js/auth.js') ?>"></script>
    <script src="<?= asset('/assets/js/api.js') ?>"></script>
<?php if (!is_authenticated()): ?><script src="<?= asset('/assets/js/help-center.js') ?>" data-guest="1"></script><?php endif; ?>
    <script src="<?= asset('/assets/js/app.js') ?>"></script>
    <script src="<?= asset('/assets/js/responsive.js') ?>"></script>
    <script src="<?= asset('/assets/js/password-recovery.js') ?>"></script>
    <script>
        // "Crear cuenta" sólo aparece mientras alguna carrera tenga abierto el autoregistro.
        // Se vuelve a consultar al abrir el inicio de sesión, al regresar a la pestaña y a la hora del cierre.
        let selfRegistrationTimer = null;
        function refreshSelfRegistration() {
            api.get('/self-registration', { _fresh: true, _silent: true }).then(data => {
                ['selfRegistrationLink', 'selfRegistrationHero'].forEach(id => document.getElementById(id)?.classList.toggle('d-none', !data.open));
                clearTimeout(selfRegistrationTimer);
                const closes = (data.careers || []).map(career => new Date(career.closes_at).getTime() - Date.now());
                // Una consulta al menos cada 24 horas: setTimeout no admite plazos muy largos.
                if (closes.length) selfRegistrationTimer = setTimeout(refreshSelfRegistration, Math.min(Math.max(Math.min(...closes) + 1000, 1000), 86400000));
            }).catch(() => {});
        }
        refreshSelfRegistration();
        document.getElementById('loginModal')?.addEventListener('show.bs.modal', refreshSelfRegistration);
        window.addEventListener('pageshow', event => { if (event.persisted) refreshSelfRegistration(); });
        document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshSelfRegistration(); });

        const loginForm = document.getElementById('loginForm');
        const loginSubmitBtn = document.getElementById('loginSubmitBtn');
        const loginMessageContainer = document.getElementById('loginMessageContainer');
        function dashboardUrlForCurrentUser() {
            if (auth.isAdmin()) return '/pages/admin/dashboard.php';
            if (auth.isTeacher()) return '/pages/teacher/dashboard.php';
            return '/pages/student/dashboard.php';
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function modalAlert(type, message) {
            return `<div class="alert alert-${type} py-2 mb-0" role="alert">${escapeHtml(message)}</div>`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const cookieNotice = document.getElementById('cookieNotice');
            const cookieNoticeAccept = document.getElementById('cookieNoticeAccept');
            const cookiesAvailable = auth.cookiesEnabled();

            try {
                if (cookieNotice && (!cookiesAvailable || localStorage.getItem('sgpi-cookie-notice-seen') !== '1')) {
                    cookieNotice.hidden = false;
                }
            } catch (error) {
                if (cookieNotice) cookieNotice.hidden = false;
            }

            if (cookieNotice && !cookiesAvailable) {
                cookieNotice.querySelector('p').innerHTML = '<strong>Las cookies están bloqueadas.</strong> Habilítalas para este sitio; son necesarias para mantener tu sesión iniciada.';
                cookieNoticeAccept.textContent = 'Volver a comprobar';
            }

            cookieNoticeAccept?.addEventListener('click', () => {
                if (!auth.cookiesEnabled()) {
                    swalToast('warning', 'El navegador sigue bloqueando las cookies para este sitio.');
                    return;
                }
                cookieNotice.hidden = true;
                try {
                    localStorage.setItem('sgpi-cookie-notice-seen', '1');
                } catch (error) {
                    // El aviso ya quedó cerrado en la página actual.
                }
            });

            loadRepositorySummary();
            document.querySelectorAll('[data-open-login]').forEach(link => {
                link.addEventListener('click', event => {
                    event.preventDefault();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal')).show();
                    history.replaceState(null, '', '/index.php#login');
                });
            });

            if (window.location.hash === '#login') {
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }

            if (window.location.hash === '#recover') {
                openPasswordRecoveryModal();
            }
        });

        // Portada: cuántos documentos públicos hay por tipo y los recién publicados (GET /repositorio/resumen, sin sesión).
        async function loadRepositorySummary() {
            const label = (count) => count === 1 ? '1 documento' : `${count} documentos`;
            const names = { thesis_general: 'Tesis', thesis_residency: 'Residencia', evaluation_document: 'Proyecto integrador', repository: 'Material general' };
            const escapeText = (value) => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            try {
                const response = await fetch(`${API_BASE_URL}/repositorio/resumen`, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                Object.entries(data.categories || {}).forEach(([key, count]) => {
                    const box = document.querySelector(`[data-repo-count="${key}"]`);
                    if (box) box.textContent = count ? label(count) : 'Próximamente';
                });
                const total = document.querySelector('[data-repo-count="total"]');
                if (total && data.total) total.textContent = data.total;
                const latest = data.latest || [];
                if (latest.length) {
                    document.getElementById('heroLatestList').innerHTML = latest.map(item => `
                        <li><a href="/pages/repositorio-detail.php?id=${encodeURIComponent(item.id)}">
                            <strong>${escapeText(item.nombre)}</strong>
                            <small>${escapeText(names[item.categoria] || 'Documento')}${item.autor ? ' · ' + escapeText(item.autor) : ''}</small>
                        </a></li>`).join('');
                    document.getElementById('heroLatest').classList.remove('d-none');
                }
            } catch (error) {
                // Sin conexión con la API los accesos siguen funcionando sin los números.
            }
        }

        function showLoginError(text) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger py-2 mb-0';
            alert.setAttribute('role', 'alert');
            alert.innerHTML = '<i class="bi bi-exclamation-circle"></i> ';
            alert.append(document.createTextNode(text));
            loginMessageContainer.replaceChildren(alert);
            swalToast('danger', text);
            loginSubmitBtn.disabled = false;
            loginSubmitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Iniciar sesión';
        }

        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            loginMessageContainer.innerHTML = '';

            if (!loginForm.checkValidity()) {
                loginForm.classList.add('was-validated');
                return;
            }

            const id = document.getElementById('loginUserId').value.trim();
            const password = document.getElementById('loginPassword').value;
            if (!auth.cookiesEnabled()) {
                loginMessageContainer.innerHTML = modalAlert('warning', 'Habilita las cookies para este sitio antes de iniciar sesión.');
                return;
            }

            loginSubmitBtn.disabled = true;
            loginSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...';

            const result = await auth.login(id, password);

            if (!result.success) {
                showLoginError(result.error);
                return;
            }

            try {
                await auth.persistSession();
            } catch (error) {
                showLoginError(error.message);
                return;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Bienvenido',
                text: 'Sesión iniciada correctamente',
                timer: 900,
                showConfirmButton: false
            });

            window.location.replace(dashboardUrlForCurrentUser());
        });
    </script>
</body>
</html>
