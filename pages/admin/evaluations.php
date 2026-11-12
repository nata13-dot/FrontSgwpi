<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || (!can_manage_projects() && !is_teacher())) {
    header('Location: /index.php');
    exit;
}

$is_archived_view = basename($_SERVER['PHP_SELF']) === 'evaluations-archived.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_archived_view ? 'Evaluaciones archivadas' : 'Evaluaciones' ?> - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
    <style>
        .room-panel {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            height: 100%;
            overflow: hidden;
        }
        .room-panel-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.15rem;
            background: var(--soft-bg);
        }
        .room-panel-body {
            padding: 1.15rem;
        }
        .room-section-title {
            color: var(--primary-blue);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: 0;
            margin: .25rem 0 .75rem;
            text-transform: uppercase;
        }
        .room-scroll-list {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            max-height: 280px;
            overflow: auto;
            padding: .75rem;
        }
        .room-option-card,
        .room-list-card {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            transition: border-color .15s ease, background-color .15s ease;
        }
        .room-option-card {
            align-items: flex-start;
            cursor: pointer;
            display: flex;
            gap: .65rem;
            padding: .7rem .8rem;
        }
        .room-option-card + .room-option-card {
            margin-top: .55rem;
        }
        .room-option-card:has(input:checked),
        .room-project-item:has(input:checked) {
            background: color-mix(in srgb, var(--primary-blue) 9%, var(--surface-bg));
            border-color: color-mix(in srgb, var(--primary-blue) 45%, var(--border-color));
        }
        .room-list-card + .room-list-card {
            margin-top: .75rem;
        }
        .room-project-item {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: .65rem;
        }
        .room-project-item + .room-project-item {
            margin-top: .65rem;
        }
        .room-card-title {
            color: var(--text-dark);
            font-weight: 700;
        }
        .room-meta-grid {
            display: grid;
            gap: .35rem .8rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin: .75rem 0;
        }
        .room-meta-item {
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: .55rem .65rem;
        }
        .room-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            justify-content: flex-end;
        }
        .room-action-group .btn {
            min-width: 2.25rem;
        }
        .score-question-card {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
        }
        .score-question-card.is-answered { border-color: var(--success-accent); }
        .score-question-card.is-missing { border-color: var(--bs-danger); }
        .score-question-head { display: flex; gap: .75rem; align-items: flex-start; margin-bottom: .75rem; }
        .score-question-number {
            align-items: center; background: var(--primary-blue); border-radius: 50%; color: #fff; display: inline-flex;
            flex: 0 0 1.9rem; font-size: .85rem; font-variant-numeric: tabular-nums; font-weight: 700; height: 1.9rem; justify-content: center;
        }
        .scale-options { display: grid; gap: .4rem; }
        .scale-options.scale-acuerdo { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .scale-options.scale-puntos_5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .scale-options.scale-puntos_10 { grid-template-columns: repeat(10, minmax(0, 1fr)); }
        .scale-options .btn { font-size: .82rem; line-height: 1.2; min-height: 2.6rem; padding: .35rem .25rem; white-space: normal; }
        .scale-options.scale-puntos_5 .btn, .scale-options.scale-puntos_10 .btn { font-size: 1rem; font-variant-numeric: tabular-nums; font-weight: 700; }
        .scale-ends { color: var(--text-muted); display: flex; font-size: .75rem; justify-content: space-between; margin-top: .25rem; }
        .score-summary-bar {
            align-items: center; background: var(--soft-bg); border: 1px solid var(--border-color); border-radius: 8px;
            display: flex; flex-wrap: wrap; gap: .75rem 1.25rem; padding: .65rem 1rem; position: sticky; top: -1rem; z-index: 2;
        }
        .score-summary-bar strong { font-variant-numeric: tabular-nums; }
        .rubric-chip-row { display: flex; flex-wrap: wrap; gap: .5rem; }
        .rubric-chip { background: var(--surface-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-dark); font-weight: 600; min-width: 7rem; padding: .45rem .8rem; text-align: left; }
        .rubric-chip small { color: var(--text-muted); display: block; font-weight: 500; }
        .rubric-chip[aria-checked="true"] { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }
        .rubric-chip[aria-checked="true"] small { color: rgba(255, 255, 255, .8); }
        .rubric-question { background: var(--surface-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: .75rem 1rem; }
        .rubric-question + .rubric-question { margin-top: .5rem; }
        .scale-preview { color: var(--text-muted); font-size: .78rem; }
        @media (max-width: 767.98px) {
            .scale-options.scale-acuerdo { grid-template-columns: 1fr; }
            .scale-options.scale-puntos_10 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        }
        @media (max-width: 991.98px) {
            .room-meta-grid {
                grid-template-columns: 1fr;
            }
            .room-action-group {
                justify-content: flex-start;
                margin-top: .75rem;
            }
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="mb-1"><?= $is_archived_view ? 'Evaluaciones archivadas' : 'Evaluaciones' ?></h1>
                        <p class="text-muted mb-0"><?= $is_archived_view ? 'Consulta evaluaciones finalizadas que ya no aparecen en la vista principal. El historial no se restaura: para repetir una presentación registra una autorización de nuevo intento en Seguimiento académico.' : 'Rúbricas únicas por semestre, con comentarios opcionales' ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary" href="/pages/admin/<?= $is_archived_view ? 'evaluations.php' : 'evaluations-archived.php' ?>">
                            <i class="bi <?= $is_archived_view ? 'bi-arrow-left' : 'bi-archive' ?>"></i> <?= $is_archived_view ? 'Volver a evaluaciones' : 'Ver archivadas' ?>
                        </a>
                        <?php if (is_evaluation_manager() && !$is_archived_view): ?>
                        <button class="btn btn-outline-secondary" id="archiveSelectionBtn" onclick="handleArchiveSelectionButton()">
                            <i class="bi <?= $is_archived_view ? 'bi-arrow-counterclockwise' : 'bi-archive' ?>"></i> <span id="archiveSelectionButtonText"><?= $is_archived_view ? 'Desarchivar' : 'Archivar' ?></span>
                        </button>
                        <button class="btn btn-outline-secondary d-none" id="archiveSelectionCancelBtn" onclick="cancelArchiveSelectionMode()">
                            Cancelar
                        </button>
                        <?php endif; ?>
                        <?php if (is_evaluation_manager()): ?>
                        <button class="btn btn-outline-primary" onclick="openRubricModal()">
                            <i class="bi bi-list-check"></i> Gestionar Rúbrica
                        </button>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                        <a class="btn btn-outline-primary" href="/pages/admin/evaluation-rooms.php">
                            <i class="bi bi-door-open"></i> Gestionar salas
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label" for="semesterFilter">Semestre</label>
                        <select data-semester-scope="career" data-semester-max="8" data-semester-min="5" class="form-select" id="semesterFilter" onchange="loadEvaluations()">
                            <option value="">Todos los semestres</option>
                            <option value="5">5 - Propuesta</option>
                            <option value="6">6 - Avance</option>
                            <option value="7">7 - Avance</option>
                            <option value="8">8 - Titulación</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="projectFilter">Proyecto</label>
                        <select class="form-select" id="projectFilter" onchange="loadEvaluations()">
                            <option value="">Todos los proyectos</option>
                        </select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="evaluation-col-project">Proyecto</th>
                                        <th class="evaluation-col-members">Integrantes</th>
                                        <th class="evaluation-col-data">Datos clave</th>
                                        <th class="evaluation-col-room">Sala y turno</th>
                                        <th class="evaluation-col-status">Estado</th>
                                        <th class="evaluation-col-average">Promedio</th>
                                        <th class="evaluation-col-actions">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="evaluationsTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="evaluationModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="evaluationForm">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Evaluación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onmousedown="this.blur()" onclick="this.blur()"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="evaluation_room_id">Sala configurada</label>
                        <select class="form-select" id="evaluation_room_id" onchange="applyRoomToEvaluationForm()" required>
                            <option value="">Selecciona una sala</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="project_id">Proyecto</label>
                        <select class="form-select" id="project_id" required></select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="semestre">Semestre</label>
                            <select data-semester-scope="period" data-semester-max="8" data-semester-min="5" class="form-select" id="semestre" required>
                                <option value="5">5 - Propuesta</option>
                                <option value="6">6 - Avance</option>
                                <option value="7">7 - Avance</option>
                                <option value="8">8 - Titulación</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="sala">Sala</label>
                            <input type="text" class="form-control" id="sala" placeholder="Sala 1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="fecha_exposicion">Fecha de exposición</label>
                        <input type="datetime-local" class="form-control" id="fecha_exposicion">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="rubricModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Rúbricas por semestre</h5>
                        <small style="opacity: .82">Cada pregunta tiene su propia escala. Al calificar, cada respuesta se convierte de 0 a 100 % y todas pesan igual en el promedio.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onmousedown="this.blur()" onclick="this.blur()"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-3 align-items-end mb-3">
                        <div>
                            <span class="form-label d-block">Semestre</span>
                            <div class="rubric-chip-row" id="rubricSemesterChips" role="radiogroup" aria-label="Semestre de la rúbrica"></div>
                            <select class="d-none" id="rubricSemester" onchange="onRubricScopeChange()" aria-hidden="true" tabindex="-1">
                                <option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option>
                            </select>
                        </div>
                        <div class="d-none flex-grow-1" id="rubricProjectBox" style="max-width: 28rem">
                            <label class="form-label" for="rubricProjectId">Proyecto de 8° (rúbrica personalizada)</label>
                            <select class="form-select" id="rubricProjectId" onchange="loadRubricCriteria()">
                                <option value="">Rúbrica general de 8°</option>
                            </select>
                        </div>
                    </div>

                    <section class="border rounded p-3 mb-3" aria-labelledby="newQuestionTitle">
                        <h6 id="newQuestionTitle" class="mb-2"><i class="bi bi-plus-circle"></i> Nueva pregunta</h6>
                        <div class="row g-2 align-items-start">
                            <div class="col-lg-6">
                                <label class="form-label small" for="newCriterionText">Pregunta</label>
                                <input type="text" class="form-control" id="newCriterionText" maxlength="255" placeholder="Ej. El problema está bien delimitado y justificado">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label small" for="newCriterionScale">Escala de respuesta</label>
                                <select class="form-select" id="newCriterionScale" onchange="renderScalePreview()">
                                    <option value="acuerdo">Nivel de acuerdo</option>
                                    <option value="puntos_5">Puntaje del 1 al 5</option>
                                    <option value="puntos_10">Puntaje del 1 al 10</option>
                                </select>
                                <div class="scale-preview mt-1" id="newCriterionScalePreview"></div>
                            </div>
                            <div class="col-lg-2 d-grid">
                                <label class="form-label small d-none d-lg-block">&nbsp;</label>
                                <button type="button" class="btn btn-primary" onclick="addCriterion()"><i class="bi bi-plus-lg"></i> Agregar</button>
                            </div>
                        </div>
                    </section>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0" id="rubricListTitle">Preguntas</h6>
                        <small class="text-muted" id="rubricListSummary"></small>
                    </div>
                    <div id="rubricCriteriaList"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="scoreModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" id="scoreForm">
                <div class="modal-header">
                    <h5 class="modal-title">Rúbrica de Evaluación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onmousedown="this.blur()" onclick="this.blur()"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="scoreEvaluationId">
                    <div id="scoreProjectContext" class="border rounded p-3 mb-3"></div>
                    <div id="scoreRoomTimer" class="mb-3"></div>
                    <div class="score-summary-bar mb-3" id="scoreSummaryBar" aria-live="polite"></div>
                    <div id="scoreDraftStatus" class="small text-muted mb-2"></div>
                    <div id="scoreFields"></div>
                    <div class="border rounded p-3 mt-3">
                        <label class="form-label fw-semibold" for="generalEvaluationComment">Comentarios o puntos de mejora</label>
                        <textarea class="form-control" id="generalEvaluationComment" rows="3" placeholder="Comentario final visible en el desglose"></textarea>
                    </div>
                    <div class="border rounded p-3 mt-3 d-none" id="titulationAptBox">
                        <label class="form-label fw-semibold">¿Recomienda continuar el proyecto? (No acredita titulación)</label>
                        <select class="form-select" id="apto_titulacion">
                            <option value="">Sin respuesta</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                        <div class="form-text">Esta respuesta no afecta el puntaje general.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-check"></i> Enviar evaluación</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="roomsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title"><i class="bi bi-door-open"></i> Salas de Evaluación</h5>
                        <div class="text-muted small">Configura fecha, docentes, responsable y orden de proyectos en un solo flujo.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="room-panel">
                                <div class="room-panel-header">
                                    <h6 class="mb-0"><i class="bi bi-sliders"></i> Configuración de sala</h6>
                                </div>
                                <div class="room-panel-body">
                                <input type="hidden" id="roomId">
                                <div class="row g-3">
                                    <div class="col-12"><div class="room-section-title">Datos generales</div></div>
                                    <div class="col-md-6"><label class="form-label">Sala</label><input class="form-control" id="roomName" placeholder="Sala 1" required><div class="invalid-feedback">Nombre obligatorio y no repetido.</div></div>
                                    <div class="col-md-6"><label class="form-label">Salon</label><input class="form-control" id="roomClassroom" placeholder="Salon/Laboratorio" required></div>
                                    <div class="col-md-4"><label class="form-label">Semestre</label><select data-semester-scope="period" data-semester-max="8" data-semester-min="5" class="form-select" id="roomSemester" onchange="loadRoomProjects()"><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option></select></div>
                                    <div class="col-md-4"><label class="form-label">Inicio</label><input type="datetime-local" class="form-control" id="roomDate" required onchange="updateRoomAvailability()"><div class="form-text">Posterior a la fecha actual.</div></div>
                                    <div class="col-md-4"><label class="form-label">Fin</label><input type="datetime-local" class="form-control" id="roomEndDate" required onchange="updateRoomAvailability()"><div class="form-text">Debe ser posterior al inicio.</div></div>
                                    <div class="col-12"><div class="room-section-title">Tiempos</div></div>
                                    <div class="col-md-4"><label class="form-label">Eval. docente</label><input type="number" class="form-control" id="teacherMinutes" min="1" max="240" value="15"></div>
                                    <div class="col-md-4"><label class="form-label">Presentación</label><input type="number" class="form-control" id="presentationMinutes" min="1" max="240" value="20"></div>
                                    <div class="col-md-4"><label class="form-label">Oportunidades</label><input type="number" class="form-control" id="maxAttempts" min="1" max="10" value="1"></div>
                                    <div class="col-12"><div class="room-section-title">Evaluadores</div><div class="form-text mb-2">Un docente puede participar en varias salas el mismo dia mientras los horarios no se empalmen.</div><div class="room-scroll-list" id="roomTeachers"></div></div>
                                    <div class="col-12"><label class="form-label">Responsable de sala</label><select class="form-select" id="responsibleTeacher"><option value="">Selecciona primero docentes</option></select></div>
                                    <div class="col-12"><div class="room-section-title">Proyectos y orden</div><div class="room-scroll-list" id="roomProjects"></div></div>
                                    <div class="col-12"><div class="small text-muted" id="roomAvailabilityHint"></div></div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button class="btn btn-outline-secondary" onclick="resetRoomForm()">Limpiar</button>
                                    <button class="btn btn-primary" onclick="saveRoom()"><i class="bi bi-save"></i> Guardar sala</button>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="room-panel">
                                <div class="room-panel-header d-flex justify-content-between align-items-center gap-2">
                                    <h6 class="mb-0"><i class="bi bi-list-check"></i> Salas registradas</h6>
                                    <span class="badge bg-light text-primary" id="roomsCountBadge">0 salas</span>
                                </div>
                                <div class="room-panel-body" id="roomsList"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="breakdownModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Desglose por Docente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="breakdownContent"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="projectDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="projectDetailsContent"></div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="<?= asset('/assets/js/auth.js') ?>"></script>
    <script src="<?= asset('/assets/js/api.js') ?>"></script>
    <script src="<?= asset('/assets/js/app.js') ?>"></script>
    <script src="<?= asset('/assets/js/room-countdown.js') ?>"></script>
    <script>
        const IS_ADMIN = <?= can_manage_projects() ? 'true' : 'false' ?>;
        const CAN_MANAGE_EVALUATIONS = <?= is_evaluation_manager() ? 'true' : 'false' ?>;
        // Con nombres de evaluadores sólo descargan jefatura y administración general; los demás, el informe limitado.
        const FULL_EVALUATION_REPORTS = <?= can_download_full_evaluation_reports() ? 'true' : 'false' ?>;
        const IS_ARCHIVED_VIEW = <?= $is_archived_view ? 'true' : 'false' ?>;
    </script>
    <script>
        let projects = [];
        let roomProjects = [];
        let teachers = [];
        let rooms = [];
        let evaluations = [];
        let expandedEvaluationRooms = new Set();
        let archiveSelectionMode = false;
        let selectedArchiveEvaluationIds = new Set();
        let criteria = [];
        let criteriaBySemester = {};
        let levels = [];
        let rubricScoreModes = {};
        let evaluationModal;
        let rubricModal;
        let scoreModal;
        let breakdownModal;
        let projectDetailsModal;
        let roomsModal;
        let evaluationsRealtimeTimer = null;
        let evaluationsRefreshInProgress = false;
        let evaluationsBulkActionInProgress = false;
        let roomProjectsReorderTimer = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function projectSemester(project) {
            return Number(project?.presentation_semester ?? project?.semestre ?? 0);
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function roomDisplayIdentifier(room) {
            if (room?.display_identifier) return room.display_identifier;
            const labels = { 1: '1ro', 2: '2do', 3: '3ro', 4: '4to', 5: '5to', 6: '6to', 7: '7mo', 8: '8vo', 9: '9no', 10: '10mo' };
            const semester = Number(room?.semestre || 0);
            const date = new Date(room?.fecha_evaluacion || room?.inicia_en || Date.now());
            const half = date.getMonth() < 6 ? 1 : 2;
            const year = String(date.getFullYear()).slice(-2);
            return `${labels[semester] || `${semester}to`}-${half}-${year}`;
        }

        function roomDisplayName(room) {
            return room?.display_name || `${room?.nombre || 'Sin sala'} · ${roomDisplayIdentifier(room)}`;
        }

        function projectActiveAuthors(project) {
            const students = Array.isArray(project?.students) ? project.students : [];
            return students.map(student => fullName(student)).filter(Boolean).join(', ');
        }

        function evaluationPresentationIndicator(evaluation) {
            const uploaded = Boolean(evaluation?.document_readiness?.presentation_uploaded);
            return `<i class="bi bi-circle-fill ${uploaded ? 'text-success' : 'text-danger'} ms-2"
                title="${uploaded ? 'Presentación cargada' : 'Presentación pendiente'}"></i>`;
        }

        function evaluationMemberIndicators(evaluation, project) {
            const statuses = new Map(
                (evaluation?.document_readiness?.students || []).map(student => [String(student.id), student])
            );
            const students = Array.isArray(project?.students) ? project.students : [];
            if (!students.length) return '<span class="text-muted">Sin integrantes</span>';

            return students.map(student => {
                const released = Boolean(statuses.get(String(student.id))?.released);
                return `<div class="small">
                    ${escapeHtml(fullName(student))}
                    <i class="bi bi-circle-fill ${released ? 'text-success' : 'text-danger'} ms-1"
                        title="${released ? 'Alumno liberado' : 'Alumno no liberado'}"></i>
                </div>`;
            }).join('');
        }

        function projectCompany(project) {
            return project?.company_name || project?.empresa || project?.company?.name || project?.business_name || '';
        }

        function projectAdvisor(project) {
            return fullName(project?.advisor || project?.asesor || project?.teacher || project?.docente);
        }

        function projectControlNumbers(project) {
            const students = Array.isArray(project?.students) ? project.students : [];
            return students.map(student => student.numero_control || student.control_number || student.no_control || student.id).filter(Boolean).join(', ');
        }

        function projectDetailsRows(project) {
            return [
                ['Integrantes', projectActiveAuthors(project)],
                ['No. control', projectControlNumbers(project)],
                ['Empresa', projectCompany(project)],
                ['Giro', project?.company_giro || project?.giro],
                ['Contacto', project?.company_contact_name || project?.contact_name],
                ['Asesor', projectAdvisor(project)],
                ['Descripción', project?.description || project?.descripcion]
            ].filter(([, value]) => String(value ?? '').trim() !== '');
        }

        function compactProjectMeta(project) {
            const rows = projectDetailsRows(project).slice(0, 4);
            if (rows.length === 0) return '<span class="text-muted small d-block">Sin datos adicionales registrados</span>';
            return rows.map(([label, value]) => `<span class="text-muted small d-block"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</span>`).join('');
        }

        function projectKeyData(project) {
            const rows = [
                ['Empresa', projectCompany(project)],
                ['Giro', project?.company_giro || project?.giro],
                ['Contacto', project?.company_contact_name || project?.contact_name],
                ['Periodo', project?.year],
                ['Estatus', project?.proposal_status]
            ].filter(([, value]) => String(value ?? '').trim() !== '');
            return rows.length
                ? rows.map(([label, value]) => `<span class="small d-block"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</span>`).join('')
                : '<span class="text-muted small">Sin datos clave registrados</span>';
        }

        function orderedRoomProjects(room) {
            return [...(room?.projects || [])].sort((a, b) => {
                const orderA = Number(a.presentation_order ?? a.pivot?.presentation_order ?? 0);
                const orderB = Number(b.presentation_order ?? b.pivot?.presentation_order ?? 0);
                if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                if (orderA && !orderB) return -1;
                if (!orderA && orderB) return 1;
                return String(a.title || '').localeCompare(String(b.title || ''), 'es', { sensitivity: 'base' });
            });
        }

        function evaluationProject(evaluation) {
            return evaluation?.project || projects.find(project => String(project.id) === String(evaluation?.project_id)) || {};
        }

        function stageLabel(stage) {
            return { propuesta: 'Propuesta', avance: 'Avance', titulacion: 'Titulacion' }[stage] || stage;
        }

        function groupCriteria() {
            criteriaBySemester = criteria.reduce((groups, criterion) => {
                if (!groups[criterion.semestre]) groups[criterion.semestre] = [];
                groups[criterion.semestre].push(criterion);
                return groups;
            }, {});
        }

        function criteriaForEvaluation(evaluation) {
            if (Array.isArray(evaluation?.criteria)) return evaluation.criteria;
            const projectId = String(evaluation?.project_id || evaluation?.project?.id || '');
            const semesterCriteria = criteriaBySemester[evaluation?.semestre] || [];
            return semesterCriteria.filter(criterion => {
                const criterionProjectId = criterion.project_id === null || criterion.project_id === undefined ? '' : String(criterion.project_id);
                if (Number(evaluation?.semestre) !== 8) return criterionProjectId === '';
                return criterionProjectId === '' || criterionProjectId === projectId;
            });
        }

        function levelLabel(level) {
            if (typeof level === 'object' && level !== null) return level.label || level.key || '';
            const legacyLabels = {
                nada: 'Totalmente en desacuerdo',
                poco: 'En desacuerdo',
                bastante: 'De acuerdo',
                mucho: 'Totalmente de acuerdo'
            };
            if (legacyLabels[level]) return legacyLabels[level];
            const found = levels.find(item => typeof item === 'object' ? item.key === level : item === level);
            if (found && typeof found === 'object') return found.label || found.key;
            return String(level || '').replaceAll('_', ' ');
        }

        function levelValue(level) {
            return typeof level === 'object' && level !== null ? level.key : level;
        }

        function levelPoints(level) {
            const value = levelValue(level);
            const found = levels.find(item => typeof item === 'object' ? item.key === value : item === value);
            return found && typeof found === 'object' && found.puntaje !== undefined ? Number(found.puntaje) : null;
        }

        function levelText(level) {
            const points = levelPoints(level);
            return points === null ? levelLabel(level) : `${levelLabel(level)} (${points} pts)`;
        }

        // Escalas por pregunta (las mismas reglas que App\Support\RubricScale en la API).
        const SCALE_NAMES = { acuerdo: 'Nivel de acuerdo', puntos_5: 'Puntaje del 1 al 5', puntos_10: 'Puntaje del 1 al 10' };
        const AGREEMENT_OPTIONS = [
            { value: 'completamente_en_desacuerdo', points: 1, label: 'Completamente en desacuerdo' },
            { value: 'en_desacuerdo', points: 2, label: 'En desacuerdo' },
            { value: 'nulo', points: 3, label: 'Nulo' },
            { value: 'de_acuerdo', points: 4, label: 'De acuerdo' },
            { value: 'completamente_de_acuerdo', points: 5, label: 'Completamente de acuerdo' }
        ];
        const STAGE_NAMES = { 5: 'Propuesta', 6: 'Avance', 7: 'Avance', 8: 'Titulación' };

        function scaleOf(criterion) {
            return SCALE_NAMES[criterion?.scale] ? criterion.scale : 'acuerdo';
        }

        function scaleOptions(scale) {
            if (scale === 'acuerdo') return AGREEMENT_OPTIONS;
            const max = scale === 'puntos_10' ? 10 : 5;
            return Array.from({ length: max }, (_, index) => ({ value: String(index + 1), points: index + 1, label: String(index + 1) }));
        }

        // La respuesta más baja vale 0 % y la más alta 100 %; así se pueden promediar escalas distintas.
        function scalePercentage(scale, value) {
            const option = scaleOptions(scale).find(item => item.value === String(value));
            if (!option) return null;
            const max = scale === 'puntos_10' ? 10 : 5;
            return Math.round(((option.points - 1) / (max - 1)) * 10000) / 100;
        }

        function verdictFor(average) {
            if (average === null || average === undefined || Number.isNaN(Number(average))) return { key: 'sin_resultado', label: 'Sin resultado', tone: 'secondary' };
            if (average >= 80) return { key: 'aprobado', label: 'Aprobado', tone: 'success' };
            if (average >= 70) return { key: 'apenas_cumple', label: 'Apenas cumple', tone: 'warning' };
            return { key: 'reprobado', label: 'Reprobado', tone: 'danger' };
        }

        function verdictBadge(verdict, average = null) {
            const value = verdict || verdictFor(average);
            return `<span class="badge text-bg-${value.tone}">${escapeHtml(value.label)}</span>`;
        }

        function scalePreviewText(scale) {
            return scale === 'acuerdo'
                ? 'Completamente en desacuerdo · En desacuerdo · Nulo · De acuerdo · Completamente de acuerdo'
                : (scale === 'puntos_10' ? '1 (0 %) a 10 (100 %)' : '1 (0 %) a 5 (100 %)');
        }

        function renderScalePreview() {
            const scale = document.getElementById('newCriterionScale').value;
            document.getElementById('newCriterionScalePreview').textContent = scalePreviewText(scale);
        }

        function renderRubricSemesterChips() {
            const current = document.getElementById('rubricSemester').value;
            document.getElementById('rubricSemesterChips').innerHTML = [5, 6, 7, 8].map(semester => `
                <button type="button" class="rubric-chip" role="radio" aria-checked="${String(semester) === current}" onclick="chooseRubricSemester(${semester})">
                    ${semester}°<small>${STAGE_NAMES[semester]}</small>
                </button>`).join('');
        }

        function chooseRubricSemester(semester) {
            document.getElementById('rubricSemester').value = String(semester);
            onRubricScopeChange();
        }

        function scoreDraftKey(evaluationId) {
            return `sgpi-score-draft:${evaluationId}`;
        }

        function readScoreDraft(evaluationId) {
            try {
                return JSON.parse(localStorage.getItem(scoreDraftKey(evaluationId)) || '{}');
            } catch (error) {
                return {};
            }
        }

        function collectScoreDraft() {
            return {
                general_comment: document.getElementById('generalEvaluationComment').value,
                apto_titulacion: document.getElementById('apto_titulacion').value,
                scores: [...document.querySelectorAll('[data-criterion]')].map(row => ({
                    criterio: row.dataset.criterion,
                    valor: row.querySelector('.criterion-value:checked')?.value || '',
                    comentario: row.querySelector('.criterion-comment')?.value || ''
                }))
            };
        }

        function saveScoreDraft() {
            const evaluationId = document.getElementById('scoreEvaluationId').value;
            if (!evaluationId) return;
            localStorage.setItem(scoreDraftKey(evaluationId), JSON.stringify(collectScoreDraft()));
            const status = document.getElementById('scoreDraftStatus');
            if (status) status.innerHTML = '<i class="bi bi-save"></i> Progreso guardado en este navegador.';
        }

        function clearScoreDraft(evaluationId) {
            localStorage.removeItem(scoreDraftKey(evaluationId));
        }

        async function refreshCriteria(fresh = false) {
            const criteriaResponse = await api.get('/evaluations/criteria', fresh ? { _fresh: 1, _timeout: 30000 } : { _cache_ttl: 60000, _timeout: 30000 });
            criteria = criteriaResponse.criteria || [];
            levels = criteriaResponse.levels || levels;
            rubricScoreModes = criteriaResponse.score_modes || rubricScoreModes;
            groupCriteria();
        }

        async function loadInitialData() {
            const [projectsResult, criteriaResult] = await Promise.allSettled([
                api.get('/evaluations/projects', { _cache_ttl: 30000, _timeout: 45000 }),
                refreshCriteria()
            ]);

            if (projectsResult.status === 'fulfilled') {
                projects = projectsResult.value || [];
            } else {
                projects = [];
                showAlert('#alertContainer', 'warning', 'No se pudieron cargar los proyectos para los filtros. La lista de evaluaciones intentara cargarse de todos modos.');
            }

            if (criteriaResult.status === 'rejected') {
                console.warn('No se pudieron cargar los criterios de evaluación:', criteriaResult.reason);
            }

            if (CAN_MANAGE_EVALUATIONS) {
                try {
                    const staffResponse = await api.getAll('/users', {
                        perfil_ids: '1,2',
                        status: 'active',
                        compact: 1,
                        per_page: 500,
                        _cache_ttl: 60000,
                        _timeout: 45000
                    });
                    teachers = (staffResponse.data || [])
                        .sort((a, b) => fullName(a).localeCompare(fullName(b)));
                } catch (error) {
                    teachers = [];
                    showAlert('#alertContainer', 'warning', 'No se pudieron cargar todos los docentes. Algunas opciones de salas o responsables podrian aparecer incompletas.');
                }
            }

            try {
                await loadRooms();
            } catch (error) {
                rooms = [];
                showAlert('#alertContainer', 'warning', 'No se pudieron cargar las salas al iniciar. Puedes reintentar desde la vista de evaluaciones.');
            }

            const projectFilter = document.getElementById('projectFilter');
            const projectSelect = document.getElementById('project_id');
            const rubricProjectSelect = document.getElementById('rubricProjectId');
            projectFilter.innerHTML = '<option value="">Todos los proyectos</option>';
            projectSelect.innerHTML = '<option value="">Selecciona un proyecto</option>';
            if (rubricProjectSelect) rubricProjectSelect.innerHTML = '<option value="">Rubrica general de 8vo</option>';
            projects.forEach(project => {
                const option = `<option value="${project.id}">${escapeHtml(project.title)}</option>`;
                projectFilter.innerHTML += option;
                projectSelect.innerHTML += option;
                if (rubricProjectSelect && projectSemester(project) === 8) rubricProjectSelect.innerHTML += option;
            });
            renderRoomOptions();
        }

        function renderRoomOptions() {
            document.getElementById('evaluation_room_id').innerHTML = '<option value="">Selecciona una sala</option>' + rooms.filter(room => !room.sequence_locked).map(room => `<option value="${room.id}">${escapeHtml(roomDisplayName(room))} · ${escapeHtml(room.salon || 'Sin salon')}</option>`).join('');
        }

        function normalizeRoomName(value) {
            const clean = String(value || '').trim();
            if (/^sala\s+\d+/i.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || (rooms.length + 1);
            return `Sala ${number}`;
        }

        function normalizeClassroom(value) {
            const clean = String(value || '').trim().toUpperCase();
            if (/^EB\d+/.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || '';
            return number ? `EB${number}` : clean;
        }

        function hasOpenEvaluationModal() {
            return Boolean(document.querySelector('.modal.show'));
        }

        function startEvaluationsRealtime() {
            clearInterval(evaluationsRealtimeTimer);
            evaluationsRealtimeTimer = setInterval(async () => {
                if (document.hidden || hasOpenEvaluationModal() || evaluationsBulkActionInProgress || evaluationsRefreshInProgress) return;
                await refreshEvaluationLiveData();
            }, 15000);
        }

        async function refreshEvaluationLiveData() {
            if (evaluationsRefreshInProgress || evaluationsBulkActionInProgress) return;
            evaluationsRefreshInProgress = true;
            try {
                await refreshCriteria(true);
                await loadRooms(false, true);
                await loadEvaluations(false, true);
                renderRoomOptions();
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    console.warn('No se pudo actualizar evaluaciones en segundo plano:', error);
                }
            } finally {
                evaluationsRefreshInProgress = false;
            }
        }

        function evaluationRoomKey(evaluation) {
            return String(evaluation.room?.id || evaluation.evaluation_room_id || 'sin-sala');
        }

        function evaluationRoomGroups(items) {
            return items.reduce((groups, evaluation) => {
                const key = evaluationRoomKey(evaluation);
                if (!groups[key]) {
                    groups[key] = {
                        total: 0,
                        evaluated: 0,
                        active: 0,
                        pending: 0,
                        canAdvance: false,
                        sequenceLocked: false,
                        completed: false
                    };
                }
                groups[key].total++;
                if (evaluation.is_completed) groups[key].evaluated++;
                if (evaluation.sequence_status === 'activo') groups[key].active++;
                if (!evaluation.is_completed) groups[key].pending++;
                groups[key].canAdvance = groups[key].canAdvance || evaluation.can_manage_evaluations || evaluation.is_room_responsible;
                groups[key].sequenceLocked = groups[key].sequenceLocked || Boolean(evaluation.room?.sequence_locked);
                groups[key].completed = groups[key].completed || Boolean(evaluation.room?.completed_at);
                return groups;
            }, {});
        }

        function cssEscape(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(String(value));
            }
            return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
        }

        function toggleEvaluationRoom(roomKey) {
            const key = String(roomKey);
            const expanded = expandedEvaluationRooms.has(key);
            if (expanded) {
                expandedEvaluationRooms.delete(key);
            } else {
                expandedEvaluationRooms.add(key);
            }

            document.querySelectorAll(`[data-evaluation-room-row="${cssEscape(key)}"]`).forEach(row => {
                row.classList.toggle('d-none', expanded);
            });
            const icon = document.querySelector(`[data-evaluation-room-icon="${cssEscape(key)}"]`);
            if (icon) icon.className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
            const label = document.querySelector(`[data-evaluation-room-label="${cssEscape(key)}"]`);
            if (label) label.textContent = expanded ? 'Desplegar' : 'Ocultar';
        }

        function canUseArchiveSelection() {
            // La API no desarchiva (409): la selección sólo sirve para archivar.
            return CAN_MANAGE_EVALUATIONS && !IS_ARCHIVED_VIEW;
        }

        function archiveSelectionAction() {
            return IS_ARCHIVED_VIEW
                ? {
                    baseLabel: 'Desarchivar',
                    selectedLabel: 'Desarchivar seleccionados',
                    emptyMessage: 'Selecciona evaluaciones, una sala completa o un semestre completo para desarchivar.',
                    confirmTitle: 'Desarchivar seleccionados',
                    confirmText: 'Se restauraran las evaluaciones seleccionadas a la vista principal.',
                    confirmButton: 'Desarchivar seleccionados',
                    endpoint: '/evaluations/unarchive-selected',
                    successMessage: 'Evaluaciones desarchivadas correctamente',
                    errorMessage: 'No se pudieron desarchivar las evaluaciones seleccionadas.'
                }
                : {
                    baseLabel: 'Archivar',
                    selectedLabel: 'Archivar seleccionados',
                    emptyMessage: 'Selecciona evaluaciones, una sala completa o un semestre completo para archivar.',
                    confirmTitle: 'Archivar seleccionados',
                    confirmText: 'Se archivarán las evaluaciones seleccionadas. Se conservarán sus reportes y detalle.',
                    confirmButton: 'Archivar seleccionados',
                    endpoint: '/evaluations/archive-selected',
                    successMessage: 'Evaluaciones archivadas correctamente',
                    errorMessage: 'No se pudieron archivar las evaluaciones seleccionadas.'
                };
        }

        function archiveableEvaluations() {
            if (!canUseArchiveSelection()) return [];
            return evaluations.filter(evaluation => evaluation.can_manage_evaluations);
        }

        function archiveableEvaluationIdsForSemester(semesterKey) {
            return archiveableEvaluations()
                .filter(evaluation => evaluationSemesterKey(evaluation) === String(semesterKey))
                .map(evaluation => Number(evaluation.id));
        }

        function archiveableEvaluationIdsForRoom(roomKey) {
            return archiveableEvaluations()
                .filter(evaluation => evaluationRoomKey(evaluation) === String(roomKey))
                .map(evaluation => Number(evaluation.id));
        }

        function selectionStateForIds(ids) {
            const cleanIds = ids.filter(Boolean);
            return {
                checked: cleanIds.length > 0 && cleanIds.every(id => selectedArchiveEvaluationIds.has(Number(id))),
                indeterminate: cleanIds.some(id => selectedArchiveEvaluationIds.has(Number(id))) && !cleanIds.every(id => selectedArchiveEvaluationIds.has(Number(id)))
            };
        }

        function renderArchiveGroupCheckbox(type, key, ids, label) {
            if (!archiveSelectionMode || !canUseArchiveSelection() || ids.length === 0) return '';
            const state = selectionStateForIds(ids);
            const safeKey = escapeHtml(key);
            const dataKeyName = type === 'semester' ? 'archiveSemesterKey' : 'archiveRoomKey';
            return `
                <label class="archive-selection-control">
                    <input class="form-check-input" type="checkbox" data-archive-${type}-key="${safeKey}" onchange="toggleArchiveGroupSelection('${type}', this.dataset.${dataKeyName}, this.checked)" ${state.checked ? 'checked' : ''}>
                    <span>${escapeHtml(label)}</span>
                </label>`;
        }

        function renderArchiveEvaluationCheckbox(evaluation) {
            if (!archiveSelectionMode || !canUseArchiveSelection() || !evaluation.can_manage_evaluations) return '';
            const id = Number(evaluation.id);
            return `
                <input class="form-check-input archive-selection-checkbox mt-1" type="checkbox" data-archive-evaluation-id="${id}" onchange="toggleArchiveEvaluationSelection(${id}, this.checked)" ${selectedArchiveEvaluationIds.has(id) ? 'checked' : ''} title="Seleccionar evaluación">`;
        }

        function pruneArchiveSelection() {
            const validIds = new Set(archiveableEvaluations().map(evaluation => Number(evaluation.id)));
            selectedArchiveEvaluationIds = new Set([...selectedArchiveEvaluationIds].filter(id => validIds.has(Number(id))));
        }

        function updateArchiveSelectionControls() {
            const button = document.getElementById('archiveSelectionBtn');
            const buttonText = document.getElementById('archiveSelectionButtonText');
            const cancelButton = document.getElementById('archiveSelectionCancelBtn');
            if (!button || !buttonText || !cancelButton) return;
            const count = selectedArchiveEvaluationIds.size;
            const action = archiveSelectionAction();
            buttonText.textContent = archiveSelectionMode ? `${action.selectedLabel}${count ? ` (${count})` : ''}` : action.baseLabel;
            button.classList.toggle('btn-warning', archiveSelectionMode);
            button.classList.toggle('btn-outline-secondary', !archiveSelectionMode);
            cancelButton.classList.toggle('d-none', !archiveSelectionMode);
        }

        function syncArchiveSelectionCheckboxes() {
            document.querySelectorAll('[data-archive-evaluation-id]').forEach(input => {
                input.checked = selectedArchiveEvaluationIds.has(Number(input.dataset.archiveEvaluationId));
            });
            document.querySelectorAll('[data-archive-semester-key]').forEach(input => {
                const ids = archiveableEvaluationIdsForSemester(input.dataset.archiveSemesterKey);
                const state = selectionStateForIds(ids);
                input.checked = state.checked;
                input.indeterminate = state.indeterminate;
            });
            document.querySelectorAll('[data-archive-room-key]').forEach(input => {
                const ids = archiveableEvaluationIdsForRoom(input.dataset.archiveRoomKey);
                const state = selectionStateForIds(ids);
                input.checked = state.checked;
                input.indeterminate = state.indeterminate;
            });
        }

        async function handleArchiveSelectionButton() {
            if (!canUseArchiveSelection()) return;
            if (!archiveSelectionMode) {
                archiveSelectionMode = true;
                selectedArchiveEvaluationIds.clear();
                updateArchiveSelectionControls();
                await loadEvaluations(false);
                return;
            }
            if (selectedArchiveEvaluationIds.size === 0) {
                showAlert('#alertContainer', 'warning', archiveSelectionAction().emptyMessage);
                return;
            }
            await archiveSelectedEvaluations();
        }

        async function cancelArchiveSelectionMode() {
            archiveSelectionMode = false;
            selectedArchiveEvaluationIds.clear();
            updateArchiveSelectionControls();
            await loadEvaluations(false);
        }

        function toggleArchiveEvaluationSelection(id, checked) {
            if (checked) {
                selectedArchiveEvaluationIds.add(Number(id));
            } else {
                selectedArchiveEvaluationIds.delete(Number(id));
            }
            updateArchiveSelectionControls();
            syncArchiveSelectionCheckboxes();
        }

        function toggleArchiveGroupSelection(type, key, checked) {
            const ids = type === 'semester'
                ? archiveableEvaluationIdsForSemester(key)
                : archiveableEvaluationIdsForRoom(key);
            ids.forEach(id => {
                if (checked) {
                    selectedArchiveEvaluationIds.add(Number(id));
                } else {
                    selectedArchiveEvaluationIds.delete(Number(id));
                }
            });
            updateArchiveSelectionControls();
            syncArchiveSelectionCheckboxes();
        }

        async function archiveSelectedEvaluations() {
            const ids = [...selectedArchiveEvaluationIds];
            const action = archiveSelectionAction();
            if (!await confirmAction({
                title: action.confirmTitle,
                text: `${action.confirmText} Total: ${ids.length} evaluación${ids.length === 1 ? '' : 'es'}.`,
                confirmButtonText: action.confirmButton
            })) return;
            try {
                const button = document.getElementById('archiveSelectionBtn');
                if (button) button.disabled = true;
                evaluationsBulkActionInProgress = true;
                await api.post(action.endpoint, { ids }, { _timeout: 45000 });
                archiveSelectionMode = false;
                evaluations = evaluations.filter(evaluation => !ids.includes(Number(evaluation.id)));
                selectedArchiveEvaluationIds.clear();
                updateArchiveSelectionControls();
                showAlert('#alertContainer', 'success', action.successMessage);
                await loadEvaluations(false, false, true);
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || action.errorMessage);
            } finally {
                evaluationsBulkActionInProgress = false;
                const button = document.getElementById('archiveSelectionBtn');
                if (button) button.disabled = false;
            }
        }

        async function loadEvaluations(showLoading = true, fresh = false, useLocal = false) {
            const tbody = document.getElementById('evaluationsTable');
            if (!useLocal) {
                const projectId = document.getElementById('projectFilter').value;
                const semester = document.getElementById('semesterFilter').value;
                const params = projectId ? { project_id: projectId } : {};
                if (semester) params.semestre = semester;
                params.archived = IS_ARCHIVED_VIEW ? 1 : 0;
                params.per_page = 200;
                if (fresh) params._fresh = 1;
                params._timeout = 30000;
                if (showLoading) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';
                }
                const response = await api.get('/evaluations', params);
                let evaluationItems = response.data || [];
                let currentPage = Number(response.current_page || 1);
                const lastPage = Number(response.last_page || 1);
                while (currentPage < lastPage) {
                    currentPage++;
                    const nextResponse = await api.get('/evaluations', { ...params, page: currentPage });
                    evaluationItems = evaluationItems.concat(nextResponse.data || []);
                }
                evaluations = orderEvaluationsByRoomSequence(removeDuplicateEvaluations(evaluationItems));
            }
            pruneArchiveSelection();
            updateArchiveSelectionControls();
            tbody.innerHTML = '';

            if (evaluations.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">${IS_ARCHIVED_VIEW ? 'No hay evaluaciones archivadas' : 'No hay evaluaciones'}</td></tr>`;
                return;
            }

            let lastSemesterKey = null;
            let lastRoomKey = null;
            const roomGroups = evaluationRoomGroups(evaluations);
            evaluations.forEach(evaluation => {
                const semesterKey = evaluationSemesterKey(evaluation);
                if (semesterKey !== lastSemesterKey) {
                    lastSemesterKey = semesterKey;
                    lastRoomKey = null;
                    const semesterStats = evaluationSemesterStats(evaluations, semesterKey);
                    const semesterSelection = renderArchiveGroupCheckbox('semester', semesterKey, archiveableEvaluationIdsForSemester(semesterKey), 'Seleccionar semestre');
                    tbody.innerHTML += `
                        <tr class="evaluation-semester-header">
                            <td colspan="7">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-start gap-2">
                                        ${semesterSelection}
                                        <div>
                                            <strong>Semestre ${escapeHtml(semesterKey)}</strong>
                                            <span class="text-muted small d-block">${IS_ARCHIVED_VIEW ? 'Evaluaciones archivadas separadas por semestre' : 'Evaluaciones activas separadas por semestre'}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-light text-dark">${semesterStats.total} evaluación${semesterStats.total === 1 ? '' : 'es'}</span>
                                        <span class="badge evaluation-success-badge">${semesterStats.evaluated} completa${semesterStats.evaluated === 1 ? '' : 's'}</span>
                                        ${semesterStats.pending ? `<span class="badge bg-warning text-dark">${semesterStats.pending} pendiente${semesterStats.pending === 1 ? '' : 's'}</span>` : ''}
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                }
                const roomKey = evaluationRoomKey(evaluation);
                const isExpanded = expandedEvaluationRooms.has(roomKey);
                if (roomKey !== lastRoomKey) {
                    lastRoomKey = roomKey;
                    const room = evaluation.room;
                    const stats = roomGroups[roomKey] || { total: 0, evaluated: 0, active: 0, pending: 0, canAdvance: false, sequenceLocked: false, completed: false };
                    const canAdvanceRoom = room?.id && stats.canAdvance && stats.sequenceLocked && !stats.completed;
                    const roomReportButton = room?.id
                        ? (FULL_EVALUATION_REPORTS
                            ? `<button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2" onclick="downloadRoomReport(${room.id})" title="Reporte completo con nombres de evaluadores"><i class="bi bi-file-earmark-pdf"></i><span>Reporte completo</span></button>
                           <button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-2" onclick="downloadRoomTeacherReport(${room.id})" title="Informe limitado para compartir"><i class="bi bi-incognito"></i><span>Informe limitado</span></button>`
                            : `<button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-2" onclick="downloadRoomTeacherReport(${room.id})" title="Evaluadores sin nombre y sólo comentarios generales"><i class="bi bi-incognito"></i><span>Informe de sala</span></button>`)
                        : '';
                    const roomSelection = renderArchiveGroupCheckbox('room', roomKey, archiveableEvaluationIdsForRoom(roomKey), 'Seleccionar sala');
                    tbody.innerHTML += `
                        <tr class="table-light evaluation-room-header">
                            <td colspan="7">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2" onclick="toggleEvaluationRoom('${escapeHtml(roomKey)}')">
                                        <i class="bi ${isExpanded ? 'bi-chevron-down' : 'bi-chevron-right'}" data-evaluation-room-icon="${escapeHtml(roomKey)}"></i>
                                        <span data-evaluation-room-label="${escapeHtml(roomKey)}">${isExpanded ? 'Ocultar' : 'Desplegar'}</span>
                                    </button>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start gap-2">
                                            ${roomSelection}
                                            <div>
                                                <strong>${escapeHtml(roomDisplayName(room))}</strong>
                                                <span class="text-muted small d-block">${escapeHtml(room?.salon || '-')} · Responsable: ${escapeHtml(fullName(room?.responsible_teacher) || '-')}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${roomReportButton}
                                        <span class="badge bg-secondary">${stats.total} proyecto${stats.total === 1 ? '' : 's'}</span>
                                        <span class="badge evaluation-success-badge">${stats.evaluated} evaluado${stats.evaluated === 1 ? '' : 's'}</span>
                                        ${stats.active ? `<span class="badge bg-primary">${stats.active} en turno</span>` : ''}
                                        ${stats.pending ? `<span class="badge bg-warning text-dark">${stats.pending} pendiente${stats.pending === 1 ? '' : 's'}</span>` : ''}
                                        ${canAdvanceRoom ? `<button type="button" class="btn btn-sm btn-warning" onclick="advanceRoom(${room.id})"><i class="bi bi-skip-forward"></i> Siguiente proyecto</button>` : ''}
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                }
                const averageColor = evaluation.global_average_color || 'secondary';
                const project = evaluationProject(evaluation);
                const disabled = evaluation.can_score_now ? '' : 'disabled';
                const statusClass = evaluation.evaluation_badge_color
                    ? `bg-${evaluation.evaluation_badge_color}`
                    : ({ activo: 'bg-primary', evaluado: 'evaluation-success-badge', pendiente: 'bg-secondary' }[evaluation.sequence_status] || 'bg-secondary');
                const evaluatedClass = evaluation.is_completed ? 'evaluation-row-complete' : '';
                const evaluatedBadge = evaluation.is_completed
                    ? `<span class="badge evaluation-success-badge ms-2"><i class="bi bi-check2-circle"></i> ${evaluation.evaluated_by_all ? 'Evaluado por todos' : 'Evaluación completada'}</span>`
                    : '';
                const evaluationSelection = renderArchiveEvaluationCheckbox(evaluation);
                tbody.innerHTML += `
                    <tr class="${evaluatedClass} ${isExpanded ? '' : 'd-none'}" data-evaluation-room-row="${escapeHtml(roomKey)}">
                        <td class="evaluation-cell-project">
                            <div class="d-flex align-items-start gap-2">
                                ${evaluationSelection}
                                <div>
                                    <div class="fw-semibold">${escapeHtml(project?.title || 'N/A')}${evaluationPresentationIndicator(evaluation)}${evaluatedBadge}</div>
                                    <span class="text-muted small d-block">Semestre ${escapeHtml(evaluation.semestre || '-')} · ${escapeHtml(stageLabel(evaluation.etapa))}</span>
                                </div>
                            </div>
                        </td>
                        <td class="evaluation-cell-members">
                            <div>${evaluationMemberIndicators(evaluation, project)}</div>
                            ${projectControlNumbers(project) ? `<div class="text-muted small"><strong>No. control:</strong> ${escapeHtml(projectControlNumbers(project))}</div>` : ''}
                        </td>
                        <td class="evaluation-cell-data">${projectKeyData(project)}</td>
                        <td class="evaluation-cell-room">
                            <span class="badge ${statusClass}">Orden #${evaluation.presentation_order || '-'}</span>
                            <div class="small mt-1">${escapeHtml(evaluation.room ? roomDisplayName(evaluation.room) : (evaluation.sala || '-'))}</div>
                            <div class="text-muted small">${evaluation.fecha_exposicion ? new Date(evaluation.fecha_exposicion).toLocaleString('es-MX') : '-'}</div>
                        </td>
                        <td class="evaluation-cell-status">
                            <span class="badge bg-secondary">${escapeHtml(evaluation.resultado)}</span>
                            <div class="small text-muted mt-1">${escapeHtml(evaluation.sequence_status || evaluation.estado || '-')}</div>
                        </td>
                        <td class="evaluation-cell-average">
                            <button class="btn btn-sm evaluation-average-btn evaluation-average-${averageColor}" onclick="showBreakdown(${evaluation.id})">
                                ${evaluation.evaluators_count > 0
                                    ? `<strong>${escapeHtml(evaluation.verdict?.label || verdictFor(evaluation.global_average).label)}</strong> · ${evaluation.global_average}% · ${evaluation.evaluators_count}/${evaluation.expected_evaluators_count || 0} evaluadores`
                                    : `Sin calificaciones · 0/${evaluation.expected_evaluators_count || 0} evaluadores`}
                            </button>
                        </td>
                        <td class="evaluation-cell-actions">
                            <div class="evaluation-actions">
                                <button class="btn btn-sm btn-success evaluation-start-btn" onclick="openScoreModal(${evaluation.id})"
                                    title="${escapeHtml(evaluation.score_block_reason || 'Evaluar')}" ${disabled}>
                                    <i class="bi bi-clipboard-check"></i><span>Evaluar</span>
                                </button>
                                ${evaluation.can_mark_completed ? `
                                    <button class="btn btn-sm btn-outline-success" onclick="markEvaluationCompleted(${evaluation.id})"
                                        title="Marcar esta evaluación como completada con los docentes presentes">
                                        <i class="bi bi-check2-circle"></i><span>Marcar evaluada</span>
                                    </button>` : ''}
                                <div class="evaluation-secondary-actions">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showProjectDetails(${evaluation.id})" title="Detalles del proyecto"><i class="bi bi-info-circle"></i></button>
                                    ${FULL_EVALUATION_REPORTS ? `<button class="btn btn-sm btn-outline-secondary evaluation-report-btn" onclick="downloadEvaluationReport(${evaluation.id})" title="Reporte completo con nombres de evaluadores"><i class="bi bi-file-earmark-pdf"></i></button>` : ''}
                                    <button class="btn btn-sm btn-outline-info" onclick="downloadEvaluationTeacherReport(${evaluation.id})" title="Informe limitado: evaluadores sin nombre y sólo comentarios generales"><i class="bi bi-incognito"></i></button>
                                    ${(evaluation.can_manage_evaluations && Number(evaluation.semestre) === 8) ? `<button class="btn btn-sm btn-outline-primary" onclick="openRubricModal(${evaluation.project_id})" title="Rubrica personalizada"><i class="bi bi-ui-checks-grid"></i></button>` : ''}
                                    ${evaluation.can_manage_evaluations && !IS_ARCHIVED_VIEW ? `<button class="btn btn-sm btn-outline-secondary" onclick="archiveEvaluation(${evaluation.id})" title="Archivar"><i class="bi bi-archive"></i></button>` : ''}
                                    ${evaluation.can_manage_evaluations ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteEvaluation(${evaluation.id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                </div>
                            </div>
                        </td>
                    </tr>`;
            });
            syncArchiveSelectionCheckboxes();
        }

        function removeDuplicateEvaluations(items) {
            const seen = new Set();
            return items.filter(item => {
                const key = [
                    item.evaluation_room_id || item.room?.id || 'sin-sala',
                    item.project_id || item.project?.id || item.id
                ].join(':');
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
        }

        function orderEvaluationsByRoomSequence(items) {
            return [...items].sort((a, b) => {
                const semesterA = Number(a.semestre || 0);
                const semesterB = Number(b.semestre || 0);
                if (semesterA !== semesterB) return semesterA - semesterB;

                const roomA = String(a.room?.id || a.evaluation_room_id || '');
                const roomB = String(b.room?.id || b.evaluation_room_id || '');
                if (roomA !== roomB) return roomA.localeCompare(roomB, 'es', { numeric: true });

                const orderA = Number(a.presentation_order || 0);
                const orderB = Number(b.presentation_order || 0);
                if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                if (orderA && !orderB) return -1;
                if (!orderA && orderB) return 1;

                const dateA = new Date(a.fecha_exposicion || 0).getTime();
                const dateB = new Date(b.fecha_exposicion || 0).getTime();
                return dateA - dateB;
            });
        }

        function evaluationSemesterKey(evaluation) {
            return String(evaluation?.semestre || 'Sin semestre');
        }

        function evaluationSemesterStats(items, semesterKey) {
            return items
                .filter(item => evaluationSemesterKey(item) === semesterKey)
                .reduce((stats, item) => {
                    stats.total++;
                    if (item.is_completed) {
                        stats.evaluated++;
                    } else {
                        stats.pending++;
                    }
                    return stats;
                }, { total: 0, evaluated: 0, pending: 0 });
        }

        function showProjectDetails(evaluationId) {
            const evaluation = evaluations.find(item => Number(item.id) === Number(evaluationId));
            if (!evaluation) return;
            const project = evaluationProject(evaluation);
            const rows = projectDetailsRows(project);
            document.getElementById('projectDetailsContent').innerHTML = `
                <h5 class="mb-2">${escapeHtml(project?.title || 'N/A')}</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-secondary">Orden #${escapeHtml(evaluation.presentation_order || '-')}</span>
                    <span class="badge bg-light text-dark">Semestre ${escapeHtml(evaluation.semestre || '-')}</span>
                    <span class="badge bg-light text-dark">${escapeHtml(stageLabel(evaluation.etapa))}</span>
                </div>
                ${rows.length ? `
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                ${rows.map(([label, value]) => `
                                    <tr>
                                        <th style="width: 160px">${escapeHtml(label)}</th>
                                        <td>${escapeHtml(value)}</td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p class="text-muted mb-0">No hay datos adicionales registrados para este proyecto.</p>'}
            `;
            projectDetailsModal.show();
        }

        function openEvaluationModal() {
            document.getElementById('evaluationForm').reset();
            document.getElementById('project_id').innerHTML = '<option value="">Selecciona primero una sala</option>';
            evaluationModal.show();
        }

        function applyRoomToEvaluationForm() {
            const room = rooms.find(item => String(item.id) === document.getElementById('evaluation_room_id').value);
            if (!room) return;
            document.getElementById('semestre').value = room.semestre;
            document.getElementById('sala').value = room.nombre;
            document.getElementById('fecha_exposicion').value = room.fecha_evaluacion ? room.fecha_evaluacion.slice(0, 16) : '';
            document.getElementById('project_id').innerHTML = '<option value="">Selecciona un proyecto</option>' + (room.projects || []).map(project => `<option value="${project.id}">${escapeHtml(project.title)}</option>`).join('');
        }

        document.getElementById('evaluationForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const data = {
                project_id: document.getElementById('project_id').value,
                evaluation_room_id: document.getElementById('evaluation_room_id').value,
                semestre: document.getElementById('semestre').value,
                sala: document.getElementById('sala').value.trim() || null,
                fecha_exposicion: document.getElementById('fecha_exposicion').value || null,
                estado: 'programada',
                resultado: 'pendiente'
            };
            try {
                await api.post('/evaluations', data);
                evaluationModal.hide();
                showAlert('#alertContainer', 'success', 'Evaluación creada correctamente');
                loadEvaluations();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error creando evaluación');
            }
        });

        function onRubricScopeChange() {
            const semester = document.getElementById('rubricSemester').value;
            renderRubricSemesterChips();
            const projectBox = document.getElementById('rubricProjectBox');
            projectBox.classList.toggle('d-none', Number(semester) !== 8);
            if (Number(semester) !== 8) document.getElementById('rubricProjectId').value = '';
            loadRubricCriteria();
        }

        function openRubricModal(projectId = '') {
            if (!CAN_MANAGE_EVALUATIONS) return;
            document.getElementById('rubricSemester').value = projectId ? '8' : '5';
            document.getElementById('rubricProjectId').value = projectId ? String(projectId) : '';
            document.getElementById('rubricProjectBox').classList.toggle('d-none', !projectId);
            renderRubricSemesterChips();
            renderScalePreview();
            loadRubricCriteria();
            rubricModal.show();
        }

        async function loadRooms(renderAfterLoad = true, fresh = false) {
            const params = { archived: IS_ARCHIVED_VIEW ? 1 : 0 };
            if (fresh) params._fresh = 1;
            params._timeout = 30000;
            rooms = await api.get('/evaluations/rooms', params);
            if (renderAfterLoad && roomsModal?._isShown) renderRooms();
        }

        async function openRoomsModal() {
            if (!CAN_MANAGE_EVALUATIONS) return;
            await loadRooms();
            renderRooms();
            renderRoomTeachers();
            await loadRoomProjects();
            roomsModal.show();
        }

        function parseRoomDate(value) {
            if (!value) return null;
            const normalized = String(value).includes('T') ? String(value) : String(value).replace(' ', 'T');
            const date = new Date(normalized);
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function formatLocalDateTimeInput(date) {
            const pad = value => String(value).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function roomRange(room) {
            const start = parseRoomDate(room.fecha_evaluacion);
            const end = parseRoomDate(room.fecha_fin_evaluacion);
            return {
                start,
                end: end || (start ? new Date(start.getTime() + 60 * 60 * 1000) : null)
            };
        }

        function rangesOverlap(startA, endA, startB, endB) {
            return startA && endA && startB && endB && startA < endB && endA > startB;
        }

        function conflictingRoomsForCurrentForm() {
            const currentRoomId = document.getElementById('roomId').value;
            const selectedStart = parseRoomDate(document.getElementById('roomDate').value);
            const selectedEnd = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!selectedStart || !selectedEnd || selectedEnd <= selectedStart) return [];

            return rooms.filter(room => {
                const isSameRecord = currentRoomId && String(room.id) === String(currentRoomId);
                const range = roomRange(room);
                return !isSameRecord && rangesOverlap(selectedStart, selectedEnd, range.start, range.end);
            });
        }

        function busyRoomIds() {
            const conflicts = conflictingRoomsForCurrentForm();
            return {
                teachers: new Set(conflicts.flatMap(room => (room.teachers || []).map(teacher => String(teacher.id)))),
                projects: new Set(conflicts.flatMap(room => (room.projects || []).map(project => Number(project.id)))),
                rooms: conflicts,
                projectRooms: conflicts
            };
        }

        function currentRoomProjectState() {
            const selected = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value));
            const orderMap = {};
            document.querySelectorAll('.room-project-order').forEach(input => {
                const projectId = Number(input.dataset.projectId);
                const order = Number(input.value || 0);
                if (projectId && order > 0) orderMap[projectId] = order;
            });
            return { selected, orderMap };
        }

        function normalizeProjectOrderMap(selectedIds, orderMap = {}) {
            const normalized = {};
            let nextOrder = Math.max(0, ...Object.values(orderMap).map(Number).filter(order => order > 0)) + 1;
            selectedIds.forEach(projectId => {
                const order = Number(orderMap[projectId] || 0);
                normalized[projectId] = order > 0 ? order : nextOrder++;
            });
            return normalized;
        }

        function scheduleRoomProjectsReorder() {
            clearTimeout(roomProjectsReorderTimer);
            roomProjectsReorderTimer = setTimeout(() => {
                const { selected, orderMap } = currentRoomProjectState();
                loadRoomProjects(selected, orderMap);
            }, 180);
        }

        async function updateRoomAvailability() {
            const startInput = document.getElementById('roomDate');
            const endInput = document.getElementById('roomEndDate');
            const startsAt = parseRoomDate(startInput.value);
            if (startsAt && !endInput.value) {
                endInput.value = formatLocalDateTimeInput(new Date(startsAt.getTime() + 60 * 60 * 1000));
            }
            const selectedTeachers = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const { selected: selectedProjects, orderMap } = currentRoomProjectState();
            renderRoomTeachers(selectedTeachers);
            await loadRoomProjects(selectedProjects, orderMap);
        }

        function renderRoomTeachers(selected = []) {
            const selectedIds = selected.map(String);
            const busy = busyRoomIds();
            const availableTeachers = teachers.filter(teacher => !busy.teachers.has(String(teacher.id)));
            document.getElementById('roomTeachers').innerHTML = availableTeachers.map(teacher => `
                <label class="room-option-card" for="roomTeacher${escapeHtml(teacher.id)}">
                    <input class="form-check-input room-teacher mt-1" type="checkbox" value="${escapeHtml(teacher.id)}" id="roomTeacher${escapeHtml(teacher.id)}" ${selectedIds.includes(String(teacher.id)) ? 'checked' : ''} onchange="refreshResponsibleTeacherOptions()">
                    <span class="flex-grow-1">
                        <span class="fw-semibold d-block">${escapeHtml(fullName(teacher))}</span>
                        <span class="text-muted small">${Number(teacher.perfil_id) === 1 ? 'Administrativo' : 'Docente'}</span>
                    </span>
                </label>`).join('') || '<p class="text-muted mb-0">No hay evaluadores disponibles para esta fecha y hora.</p>';
            refreshResponsibleTeacherOptions();
            renderAvailabilityHint(busy);
        }

        function refreshResponsibleTeacherOptions(selected = null) {
            const current = selected ?? document.getElementById('responsibleTeacher')?.value ?? '';
            const checked = [...document.querySelectorAll('.room-teacher:checked')].map(input => String(input.value));
            const available = teachers.filter(teacher => checked.includes(String(teacher.id)));
            document.getElementById('responsibleTeacher').innerHTML = '<option value="">Sin responsable</option>' + available.map(teacher => `
                <option value="${escapeHtml(teacher.id)}">${escapeHtml(fullName(teacher))}</option>
            `).join('');
            if (current) document.getElementById('responsibleTeacher').value = current;
        }

        async function loadRoomProjects(selected = [], orderMap = {}) {
            const semester = document.getElementById('roomSemester').value;
            roomProjects = await api.get('/evaluations/projects', { semestre: semester, _fresh: 1 });
            const currentRoomId = Number(document.getElementById('roomId').value || 0);
            roomProjects = roomProjects.filter(project => {
                const assignedRoomId = Number(project.assigned_room_id || 0);
                return !assignedRoomId || assignedRoomId === currentRoomId;
            });
            const selectedIds = selected.map(Number);
            const normalizedOrderMap = normalizeProjectOrderMap(selectedIds, orderMap);
            const busy = busyRoomIds();
            const selectedSet = new Set(selectedIds);
            const availableProjects = roomProjects
                .filter(project => selectedSet.has(Number(project.id)) || !busy.projects.has(Number(project.id)))
                .sort((a, b) => {
                    const selectedA = selectedSet.has(Number(a.id));
                    const selectedB = selectedSet.has(Number(b.id));
                    if (selectedA !== selectedB) return selectedA ? -1 : 1;
                    const orderA = Number(normalizedOrderMap[a.id] || 0);
                    const orderB = Number(normalizedOrderMap[b.id] || 0);
                    if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                    if (orderA && !orderB) return -1;
                    if (!orderA && orderB) return 1;
                    return String(a.title || '').localeCompare(String(b.title || ''), 'es', { sensitivity: 'base' });
                });
            document.getElementById('roomProjects').innerHTML = availableProjects.map(project => `
                <div class="room-project-item d-flex align-items-start gap-2">
                    <input class="form-check-input room-project mt-2" type="checkbox" value="${project.id}" id="roomProject${project.id}" ${selectedIds.includes(Number(project.id)) ? 'checked' : ''} onchange="scheduleRoomProjectsReorder()">
                    <input class="form-control form-control-sm room-project-order" data-project-id="${project.id}" type="number" min="1" value="${normalizedOrderMap[project.id] || ''}" style="width:76px" title="Orden" aria-label="Orden de presentación" onchange="scheduleRoomProjectsReorder()">
                    <label class="form-check-label flex-grow-1" for="roomProject${project.id}">
                        <span class="fw-semibold">${escapeHtml(project.title)}</span>
                        <span class="text-muted small d-block">${escapeHtml(projectActiveAuthors(project) || 'Sin integrantes registrados')}</span>
                        ${projectCompany(project) ? `<span class="text-muted small d-block">Empresa: ${escapeHtml(projectCompany(project))}</span>` : ''}
                    </label>
                </div>`).join('') || '<p class="text-muted mb-0">No hay proyectos disponibles para esta fecha, hora y semestre.</p>';
            renderAvailabilityHint(busy);
        }

        function renderAvailabilityHint(busy = busyRoomIds()) {
            const hint = document.getElementById('roomAvailabilityHint');
            if (!hint) return;
            if (!document.getElementById('roomDate').value || !document.getElementById('roomEndDate').value) {
                hint.textContent = 'Selecciona hora de inicio y fin para filtrar docentes y proyectos ocupados en ese rango.';
                return;
            }
            const selectedStart = parseRoomDate(document.getElementById('roomDate').value);
            const selectedEnd = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!selectedStart || !selectedEnd || selectedEnd <= selectedStart) {
                hint.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> La hora de fin debe ser posterior a la hora de inicio.</span>';
                return;
            }
            if (!busy.rooms.length && !busy.projects.size) {
                hint.textContent = 'No hay conflictos para el rango seleccionado.';
                return;
            }
            const names = busy.rooms.map(roomDisplayName).join(', ');
            const projectCount = busy.projects.size;
            const teacherText = busy.rooms.length ? ` Se ocultaron ${busy.teachers.size} docente(s) ocupados en: ${escapeHtml(names)}.` : '';
            const projectText = projectCount ? ` ${projectCount} proyecto(s) ya asignados a otra sala no están disponibles.` : '';
            hint.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i>${teacherText}${projectText}</span>`;
        }

        function renderRooms() {
            const badge = document.getElementById('roomsCountBadge');
            if (badge) badge.textContent = `${rooms.length} sala${rooms.length === 1 ? '' : 's'}`;
            const roomActions = room => `
                            ${FULL_EVALUATION_REPORTS ? `<button class="btn btn-sm btn-outline-dark" onclick="downloadRoomReport(${room.id})" title="Reporte completo con nombres de evaluadores"><i class="bi bi-file-earmark-pdf"></i></button>` : ''}
                            <button class="btn btn-sm btn-outline-info" onclick="downloadRoomTeacherReport(${room.id})" title="Informe limitado: evaluadores sin nombre y sólo comentarios generales"><i class="bi bi-incognito"></i></button>
                            ${CAN_MANAGE_EVALUATIONS ? `
                            <button class="btn btn-sm btn-outline-primary" onclick="editRoom(${room.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"><i class="bi bi-lock"></i></button>
                            ${room.sequence_locked && !room.completed_at ? `<button class="btn btn-sm btn-outline-warning" onclick="advanceRoom(${room.id})" title="Siguiente proyecto"><i class="bi bi-skip-forward"></i></button>` : ''}
                            ${IS_ARCHIVED_VIEW ? '' : `<button class="btn btn-sm btn-outline-secondary" onclick="archiveRoom(${room.id})" title="Archivar sala"><i class="bi bi-archive"></i></button>`}
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(${room.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                            ` : ''}
            `;
            document.getElementById('roomsList').innerHTML = rooms.map(room => `
                <div class="room-list-card">
                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="room-card-title">${escapeHtml(roomDisplayName(room))}</span>
                                <span class="badge bg-secondary">Semestre ${room.semestre}</span>
                                ${room.sequence_locked ? '<span class="badge bg-primary">Orden bloqueado</span>' : ''}
                                ${room.completed_at ? '<span class="badge bg-success">Sala finalizada</span>' : ''}
                            </div>
                            <div class="room-meta-grid small">
                                <div class="room-meta-item"><span class="text-muted d-block">Lugar</span>${escapeHtml(room.salon || 'Sin salon')}</div>
                                <div class="room-meta-item"><span class="text-muted d-block">Horario</span>${room.fecha_evaluacion ? new Date(room.fecha_evaluacion).toLocaleString('es-MX') : 'Sin inicio'}${room.fecha_fin_evaluacion ? ` - ${new Date(room.fecha_fin_evaluacion).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}` : ''}</div>
                                <div class="room-meta-item"><span class="text-muted d-block">Responsable</span>${escapeHtml(fullName(room.responsible_teacher) || '-')}</div>
                                <div class="room-meta-item"><span class="text-muted d-block">Tiempos</span>${room.project_presentation_minutes} min exposición / ${room.teacher_evaluation_minutes} min evaluación</div>
                            </div>
                            <div class="small mb-2"><span class="fw-semibold">Docentes:</span> ${(room.teachers || []).map(t => escapeHtml(fullName(t) || t.nombres || '')).filter(Boolean).join(', ') || '-'}</div>
                            <div class="small fw-semibold mb-1">Proyectos (${(room.projects || []).length})</div>
                            <ol class="small mb-0 ps-3">${orderedRoomProjects(room).map(project => `<li class="mb-1">${escapeHtml(project.title)} <span class="badge bg-light text-dark">${escapeHtml(project.sequence_status || 'pendiente')}</span></li>`).join('') || '<li>Sin proyectos asignados</li>'}</ol>
                            <div class="small text-muted mt-2">Oportunidades por docente: ${room.max_attempts}</div>
                        </div>
                        <div class="room-action-group align-self-start">
                            ${roomActions(room)}
                        </div>
                    </div>
                </div>`).join('') || '<p class="text-muted">No hay salas creadas.</p>';
        }

        async function editRoom(id) {
            const room = rooms.find(item => Number(item.id) === Number(id));
            if (!room) return;
            document.getElementById('roomId').value = room.id;
            document.getElementById('roomName').value = room.nombre || '';
            document.getElementById('roomClassroom').value = room.salon || '';
            document.getElementById('roomSemester').value = room.semestre;
            document.getElementById('roomDate').value = room.fecha_evaluacion ? room.fecha_evaluacion.slice(0, 16) : '';
            document.getElementById('roomEndDate').value = room.fecha_fin_evaluacion ? room.fecha_fin_evaluacion.slice(0, 16) : '';
            document.getElementById('teacherMinutes').value = room.teacher_evaluation_minutes || 15;
            document.getElementById('presentationMinutes').value = room.project_presentation_minutes || 20;
            document.getElementById('maxAttempts').value = room.max_attempts || 1;
            renderRoomTeachers((room.teachers || []).map(t => t.id));
            refreshResponsibleTeacherOptions(room.responsible_teacher_id || '');
            const selectedProjects = orderedRoomProjects(room);
            const orderMap = Object.fromEntries(selectedProjects.map(p => [p.id, p.presentation_order || 0]));
            await loadRoomProjects(selectedProjects.map(p => p.id), orderMap);
        }

        function resetRoomForm() {
            ['roomId', 'roomName', 'roomClassroom', 'roomDate', 'roomEndDate'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('responsibleTeacher').innerHTML = '<option value="">Selecciona primero docentes</option>';
            document.getElementById('teacherMinutes').value = 15;
            document.getElementById('presentationMinutes').value = 20;
            document.getElementById('maxAttempts').value = 1;
            renderRoomTeachers();
            loadRoomProjects();
        }

        async function saveRoom() {
            const id = document.getElementById('roomId').value;
            const teacherIds = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const projectIds = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value));
            const startsAt = parseRoomDate(document.getElementById('roomDate').value);
            const endsAt = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!startsAt || !endsAt || endsAt <= startsAt) {
                showAlert('#alertContainer', 'warning', 'La hora de fin de la sala debe ser posterior a la hora de inicio.');
                return;
            }
            const projectOrder = {};
            document.querySelectorAll('.room-project-order').forEach(input => {
                if (projectIds.includes(Number(input.dataset.projectId))) {
                    projectOrder[input.dataset.projectId] = Number(input.value || 0);
                }
            });
            const duplicateProjectIds = projectIds.filter((projectId, index) => projectIds.indexOf(projectId) !== index);
            if (duplicateProjectIds.length) {
                showAlert('#alertContainer', 'danger', 'Hay proyectos duplicados en la sala. Revisa la seleccion antes de guardar.');
                return;
            }
            const selectedOrders = Object.values(projectOrder).filter(order => order > 0);
            const duplicateOrders = selectedOrders.filter((order, index) => selectedOrders.indexOf(order) !== index);
            if (duplicateOrders.length) {
                showAlert('#alertContainer', 'danger', 'No repitas el orden de presentación entre proyectos.');
                return;
            }
            const payload = {
                nombre: normalizeRoomName(document.getElementById('roomName').value),
                salon: normalizeClassroom(document.getElementById('roomClassroom').value) || null,
                semestre: document.getElementById('roomSemester').value,
                responsible_teacher_id: document.getElementById('responsibleTeacher').value || null,
                fecha_evaluacion: document.getElementById('roomDate').value,
                fecha_fin_evaluacion: document.getElementById('roomEndDate').value,
                teacher_evaluation_minutes: Number(document.getElementById('teacherMinutes').value),
                project_presentation_minutes: Number(document.getElementById('presentationMinutes').value),
                max_attempts: Number(document.getElementById('maxAttempts').value),
                teacher_ids: teacherIds,
                project_ids: projectIds,
                project_order: projectOrder
            };
            if (!payload.nombre || !payload.fecha_evaluacion) {
                showAlert('#alertContainer', 'danger', 'Indica el nombre de la sala y la fecha de evaluación.');
                return;
            }
            if (new Date(payload.fecha_evaluacion) <= new Date()) {
                showAlert('#alertContainer', 'danger', 'La fecha de la sala debe ser posterior al momento actual.');
                return;
            }
            const repeated = rooms.find(room => {
                if (String(room.id) === String(id)) return false;
                if (String(room.nombre || '').toLowerCase() !== payload.nombre.toLowerCase()) return false;
                const range = roomRange(room);
                return rangesOverlap(startsAt, endsAt, range.start, range.end);
            });
            if (repeated) {
                showAlert('#alertContainer', 'danger', 'Ya existe una sala activa con ese nombre en ese horario.');
                return;
            }
            if (!teacherIds.length || !projectIds.length) {
                showAlert('#alertContainer', 'danger', 'Selecciona al menos un docente y un proyecto para la sala.');
                return;
            }
            try {
                if (id) await api.put(`/evaluations/rooms/${id}`, payload);
                else await api.post('/evaluations/rooms', payload);
                await loadRooms();
                renderRooms();
                renderRoomOptions();
                resetRoomForm();
                loadEvaluations();
                swalToast('success', 'Sala guardada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando sala');
            }
        }

        async function deleteRoom(id) {
            if (!await confirmAction({
                title: 'Eliminar sala',
                text: 'Se eliminarán también las evaluaciones, puntajes e intentos vinculados a esta sala.',
                confirmButtonText: 'Sí, eliminar'
            })) return;
            try {
                const response = await api.delete(`/evaluations/rooms/${id}`);
                await loadRooms();
                renderRooms();
                renderRoomOptions();
                await loadEvaluations();
                swalToast('success', response.message || 'Sala eliminada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo eliminar la sala');
            }
        }

        async function archiveRoom(id) {
            if (!await confirmAction({
                title: 'Archivar sala',
                text: 'Todas las evaluaciones de esta sala dejarán de aparecer en la vista principal.',
                confirmButtonText: 'Si, archivar sala'
            })) return;
            await api.post(`/evaluations/rooms/${id}/archive`, {});
            await loadRooms(true, true);
            renderRooms();
            loadEvaluations(true, true);
            swalToast('success', 'Sala archivada');
        }

        async function unarchiveRoom(id) {
            if (!await confirmAction({
                title: 'Restaurar sala',
                text: 'Todas las evaluaciones de esta sala volverán a la vista principal.',
                confirmButtonText: 'Si, restaurar sala'
            })) return;
            await api.post(`/evaluations/rooms/${id}/unarchive`, {});
            await loadRooms(true, true);
            renderRooms();
            loadEvaluations(true, true);
            swalToast('success', 'Sala restaurada');
        }

        async function lockRoomSequence(id) {
            if (!await confirmAction({ title: 'Bloquear orden de paso', text: 'Despues de bloquear, solo el proyecto en turno podra evaluarse.', confirmButtonText: 'Si, bloquear' })) return;
            await api.post(`/evaluations/rooms/${id}/lock-sequence`, {});
            await loadRooms();
            renderRooms();
            loadEvaluations();
        }

        async function advanceRoom(id) {
            if (!await confirmAction({
                title: 'Avanzar turno',
                text: 'El proyecto actual se marcara como finalizado y se activara el siguiente proyecto de la sala.',
                confirmButtonText: 'Si, avanzar'
            })) return;
            try {
                const response = await api.post(`/evaluations/rooms/${id}/advance`, { continue_next: true });
                await loadRooms(true, true);
                renderRooms();
                await loadEvaluations(true, true);
                swalToast('success', response.message || 'Turno actualizado');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo avanzar el turno');
            }
        }

        async function markEvaluationCompleted(id) {
            const evaluation = evaluations.find(item => Number(item.id) === Number(id));
            if (!evaluation) return;
            if (!await confirmAction({
                title: 'Marcar evaluación como completada',
                text: `Se conservarán las ${evaluation.evaluators_count || 0} rúbricas registradas de ${evaluation.expected_evaluators_count || 0} docentes asignados. Los docentes ausentes quedarán sin evaluación.`,
                confirmButtonText: 'Si, marcar evaluada'
            })) return;

            try {
                const response = await api.post(`/evaluations/${id}/mark-completed`, {});
                replaceEvaluationLocal(response.evaluation);
                await loadEvaluations(false, false, true);
                swalToast('success', response.message || 'Evaluación marcada como completada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo marcar la evaluación como completada');
            }
        }

        async function downloadRoomReport(id) {
            await generateRoomReport(id, '', `reporte_sala_${id}.pdf`);
        }

        async function downloadRoomTeacherReport(id) {
            await generateRoomReport(id, '?audience=teachers', `reporte_sala_${id}_limitado.pdf`);
        }

        async function downloadEvaluationReport(id) {
            await downloadPdf(`/evaluations/${id}/report.pdf`, `reporte_evaluacion_${id}.pdf`, 'No se pudo generar el reporte PDF.');
        }

        async function downloadEvaluationTeacherReport(id) {
            await downloadPdf(`/evaluations/${id}/report.pdf?audience=teachers`, `reporte_evaluacion_${id}_limitado.pdf`, 'No se pudo generar el reporte PDF para docentes.');
        }

        // Una sala reúne muchos proyectos y docentes: su informe se genera en segundo plano.
        async function generateRoomReport(id, query, filename) {
            swalToast('info', 'Generando el informe de la sala. Se descargará al terminar.', 6000);
            try {
                await downloadBackgroundReport(`/evaluations/rooms/${id}/report-job${query}`, filename);
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo generar el informe de la sala.');
            }
        }

        async function downloadPdf(endpoint, filename, fallbackMessage) {
            const request = async (retryingAfterRefresh = false) => {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    credentials: 'include',
                    headers: {
                        Accept: 'application/pdf',
                        Authorization: `Bearer ${auth.getToken()}`
                    }
                });

                if (response.status === 401 && auth.getToken() && !retryingAfterRefresh) {
                    await api.refreshAuthToken();
                    return request(true);
                }

                return response;
            };

            const response = await request();
            if (!response.ok) {
                const message = await response.clone().json()
                    .then(data => data.error || data.message)
                    .catch(() => fallbackMessage);
                showAlert('#alertContainer', 'danger', message || fallbackMessage);
                return;
            }
            saveBlob(new Blob([await response.blob()], { type: 'application/pdf' }), filename);
        }

        async function loadRubricCriteria(useLocal = false) {
            if (!useLocal) {
                const criteriaResponse = await api.get('/evaluations/criteria', { _fresh: 1, _timeout: 30000 });
                criteria = criteriaResponse.criteria || [];
                levels = criteriaResponse.levels || levels;
            }
            groupCriteria();
            const semester = document.getElementById('rubricSemester').value;
            const selectedProjectId = Number(semester) === 8 ? document.getElementById('rubricProjectId').value : '';
            const list = document.getElementById('rubricCriteriaList');
            const semesterCriteria = (criteriaBySemester[semester] || []).filter(criterion => {
                const criterionProjectId = criterion.project_id === null || criterion.project_id === undefined ? '' : String(criterion.project_id);
                return criterionProjectId === String(selectedProjectId || '');
            }).sort((a, b) => Number(a.orden || 0) - Number(b.orden || 0) || Number(a.id) - Number(b.id));
            const byScale = semesterCriteria.reduce((counts, criterion) => ({ ...counts, [scaleOf(criterion)]: (counts[scaleOf(criterion)] || 0) + 1 }), {});
            document.getElementById('rubricListTitle').textContent = `Preguntas de ${semester}° · ${STAGE_NAMES[semester] || ''}${selectedProjectId ? ' (personalizadas del proyecto)' : ''}`;
            document.getElementById('rubricListSummary').textContent = semesterCriteria.length
                ? `${semesterCriteria.length} pregunta(s): ${Object.entries(byScale).map(([scale, count]) => `${count} de ${SCALE_NAMES[scale].toLowerCase()}`).join(', ')}`
                : '';

            if (semesterCriteria.length === 0) {
                list.innerHTML = `<p class="text-muted mb-0">${selectedProjectId ? 'Este proyecto aún no tiene preguntas personalizadas.' : 'Esta rúbrica todavía no tiene preguntas. Agrega la primera arriba.'}</p>`;
                return;
            }

            list.innerHTML = semesterCriteria.map((criterion, index) => `
                <div class="rubric-question" data-rubric-id="${criterion.id}">
                    <div class="d-flex flex-wrap gap-2 align-items-start">
                        <span class="score-question-number">${index + 1}</span>
                        <div class="flex-grow-1" style="min-width: 14rem">
                            <input type="text" class="form-control criterion-question" value="${escapeHtml(criterion.label)}" maxlength="255" aria-label="Pregunta ${index + 1}">
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <select class="form-select form-select-sm criterion-scale" style="width: auto" aria-label="Escala de la pregunta ${index + 1}">
                                    ${Object.entries(SCALE_NAMES).map(([value, label]) => `<option value="${value}" ${scaleOf(criterion) === value ? 'selected' : ''}>${label}</option>`).join('')}
                                </select>
                                <span class="scale-preview">${escapeHtml(scalePreviewText(scaleOf(criterion)))}</span>
                                ${criterion.project_id ? '<span class="badge text-bg-info">Personalizada</span>' : ''}
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="moveCriterion(${criterion.id}, -1)" ${index === 0 ? 'disabled' : ''} title="Subir" aria-label="Subir"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="moveCriterion(${criterion.id}, 1)" ${index === semesterCriteria.length - 1 ? 'disabled' : ''} title="Bajar" aria-label="Bajar"><i class="bi bi-chevron-down"></i></button>
                            <button type="button" class="btn btn-outline-primary" onclick="updateCriterion(${criterion.id})" title="Guardar cambios"><i class="bi bi-save"></i></button>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteCriterion(${criterion.id})" title="Desactivar"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>`).join('') + '<p class="small text-muted mt-2 mb-0">Una pregunta que ya tiene calificaciones no se puede editar: conserva el historial. Desactívala y crea una nueva.</p>';
        }

        async function addCriterion() {
            if (!CAN_MANAGE_EVALUATIONS) return;
            const semester = document.getElementById('rubricSemester').value;
            const projectId = Number(semester) === 8 ? document.getElementById('rubricProjectId').value : '';
            const text = document.getElementById('newCriterionText').value.trim();
            if (!text) {
                showAlert('#alertContainer', 'warning', 'Escribe la pregunta antes de agregarla.');
                return;
            }
            try {
                const payload = { semestre: semester, pregunta: text, escala: document.getElementById('newCriterionScale').value };
                if (projectId) payload.project_id = Number(projectId);
                const response = await api.post('/evaluations/rubric-criteria', payload);
                criteria.push(response.criterion);
                document.getElementById('newCriterionText').value = '';
                await loadRubricCriteria(true);
                swalToast('success', 'Pregunta agregada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo agregar la pregunta.');
            }
        }

        async function saveCriterion(id, changes) {
            const row = document.querySelector(`[data-rubric-id="${id}"]`);
            const current = criteria.find(criterion => Number(criterion.id) === Number(id)) || {};
            const response = await api.put(`/evaluations/rubric-criteria/${id}`, {
                pregunta: row?.querySelector('.criterion-question').value.trim() || current.label,
                escala: row?.querySelector('.criterion-scale').value || scaleOf(current),
                orden: current.orden || 0,
                ...changes
            });
            const index = criteria.findIndex(criterion => Number(criterion.id) === Number(id));
            if (index >= 0) criteria[index] = response.criterion;
        }

        async function updateCriterion(id) {
            try {
                await saveCriterion(id, {});
                await loadRubricCriteria(true);
                swalToast('success', 'Pregunta guardada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo guardar la pregunta.');
            }
        }

        // Cambia el orden intercambiando posiciones con la pregunta vecina.
        async function moveCriterion(id, delta) {
            const rows = [...document.querySelectorAll('[data-rubric-id]')].map(row => Number(row.dataset.rubricId));
            const index = rows.indexOf(Number(id));
            const target = index + delta;
            if (index < 0 || target < 0 || target >= rows.length) return;
            [rows[index], rows[target]] = [rows[target], rows[index]];
            try {
                for (const [position, criterionId] of rows.entries()) {
                    const current = criteria.find(criterion => Number(criterion.id) === criterionId);
                    if (Number(current?.orden) !== position + 1) await saveCriterion(criterionId, { orden: position + 1 });
                }
                await loadRubricCriteria(true);
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo cambiar el orden.');
                await loadRubricCriteria();
            }
        }

        async function deleteCriterion(id) {
            if (!await confirmAction({ title: 'Desactivar pregunta', text: '¿Desactivar esta pregunta de la rúbrica?', confirmButtonText: 'Sí, desactivar' })) return;
            await api.delete(`/evaluations/rubric-criteria/${id}`);
            criteria = criteria.filter(criterion => Number(criterion.id) !== Number(id));
            await loadRubricCriteria(true);
        }

        // Temporizador de la sala dentro de la rúbrica: el mismo que controla el encargado, consultado cada 5 s.
        let scoreRoomTimerInterval = null;
        function stopScoreRoomTimer() {
            clearInterval(scoreRoomTimerInterval);
            scoreRoomTimerInterval = null;
            document.getElementById('scoreRoomTimer').innerHTML = '';
        }
        function startScoreRoomTimer(roomId) {
            stopScoreRoomTimer();
            if (!roomId) return;
            const load = async () => {
                try {
                    const live = await api.get(`/evaluations/rooms/${Number(roomId)}/live`, { _fresh: true, _silent: true });
                    const box = document.getElementById('scoreRoomTimer');
                    if (!live.current_order || live.completed_at) { box.innerHTML = ''; return; }
                    box.innerHTML = RoomCountdown.markup(live.timer, `Turno ${live.current_order} de ${live.total}${live.current_project?.title ? ` · ${live.current_project.title}` : ''}`);
                    RoomCountdown.tick();
                } catch (error) { /* sin temporizador si no participa en la sala */ }
            };
            load();
            scoreRoomTimerInterval = setInterval(() => { if (!document.hidden) load(); }, 5000);
        }

        async function openScoreModal(evaluationId) {
            await refreshCriteria(true);
            document.getElementById('scoreEvaluationId').value = evaluationId;
            const evaluation = evaluations.find(item => item.id === evaluationId);
            if (evaluation && !evaluation.can_score_now) {
                showAlert('#alertContainer', 'warning', 'Este proyecto aún no está liberado para evaluacion dentro de la sala.');
                return;
            }
            const draft = readScoreDraft(evaluationId);
            document.getElementById('scoreDraftStatus').innerHTML = Object.keys(draft).length
                ? '<i class="bi bi-save"></i> Se restauro un progreso guardado en este navegador.'
                : '';
            const project = evaluationProject(evaluation);
            document.getElementById('scoreProjectContext').innerHTML = `
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <h6 class="mb-1">${escapeHtml(project?.title || 'N/A')}</h6>
                        <div class="small text-muted"><strong>Integrantes:</strong> ${escapeHtml(projectActiveAuthors(project) || '-')}</div>
                        ${projectControlNumbers(project) ? `<div class="small text-muted"><strong>No. control:</strong> ${escapeHtml(projectControlNumbers(project))}</div>` : ''}
                    </div>
                    <span class="badge bg-secondary align-self-start">Orden #${escapeHtml(evaluation?.presentation_order || '-')}</span>
                </div>
            `;
            startScoreRoomTimer(evaluation?.room?.id || evaluation?.evaluation_room_id);
            document.getElementById('generalEvaluationComment').value = draft.general_comment || '';
            document.getElementById('titulationAptBox').classList.toggle('d-none', Number(evaluation?.semestre) !== 8);
            document.getElementById('apto_titulacion').value = draft.apto_titulacion
                ?? (evaluation?.current_teacher_apto_titulacion === true ? '1' : (evaluation?.current_teacher_apto_titulacion === false ? '0' : ''));
            if (evaluation?.current_teacher_has_scores && Number(evaluation.current_teacher_attempts) >= Number(evaluation.max_attempts)) {
                showAlert('#alertContainer', 'danger', `Ya alcanzaste el límite de ${evaluation.max_attempts} oportunidad(es) para esta evaluación.`);
                return;
            }
            const semesterCriteria = criteriaForEvaluation(evaluation);
            const container = document.getElementById('scoreFields');
            container.innerHTML = '';

            if (semesterCriteria.length === 0) {
                container.innerHTML = '<p class="text-muted mb-0">Este semestre no tiene preguntas de rúbrica configuradas.</p>';
                scoreModal.show();
                return;
            }

            container.innerHTML = semesterCriteria.map((criterion, index) => {
                const draftScore = (draft.scores || []).find(score => score.criterio === criterion.key) || {};
                const scale = scaleOf(criterion);
                const options = scaleOptions(scale);
                const name = `score-${criterion.key}`;
                return `
                    <div class="score-question-card mb-3 ${draftScore.valor ? 'is-answered' : ''}" data-criterion="${escapeHtml(criterion.key)}" data-scale="${scale}" role="group" aria-labelledby="${escapeHtml(name)}-title">
                        <div class="score-question-head" id="${escapeHtml(name)}-title">
                            <span class="score-question-number">${index + 1}</span>
                            <span>
                                <strong class="d-block">${escapeHtml(criterion.label)}</strong>
                                <small class="text-muted">${escapeHtml(SCALE_NAMES[scale])}${criterion.project_id ? ' · Pregunta personalizada del proyecto' : ''}</small>
                            </span>
                        </div>
                        <div class="scale-options scale-${scale}" role="radiogroup" aria-label="${escapeHtml(criterion.label)}">
                            ${options.map(option => `
                                <input type="radio" class="btn-check criterion-value" name="${escapeHtml(name)}" id="${escapeHtml(name)}-${option.value}" value="${option.value}" ${String(draftScore.valor || '') === option.value ? 'checked' : ''}>
                                <label class="btn btn-outline-primary" for="${escapeHtml(name)}-${option.value}">${escapeHtml(option.label)}</label>`).join('')}
                        </div>
                        ${scale !== 'acuerdo' ? `<div class="scale-ends"><span>${options[0].label} = 0 %</span><span>${options[options.length - 1].label} = 100 %</span></div>` : ''}
                        <details class="mt-2" ${draftScore.comentario ? 'open' : ''}>
                            <summary class="small text-muted">Comentario de esta pregunta (opcional, no afecta el puntaje)</summary>
                            <textarea class="form-control criterion-comment mt-1" rows="2">${escapeHtml(draftScore.comentario || '')}</textarea>
                        </details>
                    </div>`;
            }).join('');
            container.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('input', saveScoreDraft);
                input.addEventListener('change', () => { saveScoreDraft(); renderScoreSummary(); });
            });
            document.getElementById('generalEvaluationComment').oninput = saveScoreDraft;
            document.getElementById('apto_titulacion').onchange = saveScoreDraft;
            renderScoreSummary();
            scoreModal.show();
        }

        // Progreso y promedio estimado mientras se califica (el resultado oficial lo calcula la API).
        function renderScoreSummary() {
            const cards = [...document.querySelectorAll('#scoreFields [data-criterion]')];
            const percentages = cards.map(card => {
                const value = card.querySelector('.criterion-value:checked')?.value;
                card.classList.toggle('is-answered', Boolean(value));
                if (value) card.classList.remove('is-missing');
                return value ? scalePercentage(card.dataset.scale, value) : null;
            }).filter(value => value !== null);
            const average = percentages.length ? Math.round(percentages.reduce((sum, value) => sum + value, 0) / percentages.length * 100) / 100 : null;
            document.getElementById('scoreSummaryBar').innerHTML = cards.length ? `
                <span><i class="bi bi-ui-checks"></i> Respondidas <strong>${percentages.length} de ${cards.length}</strong></span>
                <span>Promedio ${percentages.length === cards.length ? '' : 'parcial '}<strong>${average === null ? '—' : `${average.toFixed(2)} %`}</strong></span>
                ${average === null ? '' : verdictBadge(null, average)}
                <span class="small text-muted ms-auto">Aprobado desde 80 % · Apenas cumple de 70 a 79 %</span>` : '';
        }

        document.getElementById('scoreForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const evaluationId = document.getElementById('scoreEvaluationId').value;
            const rows = [...document.querySelectorAll('#scoreFields [data-criterion]')];
            const scores = rows.map(row => ({
                criterio: row.dataset.criterion,
                valor: row.querySelector('.criterion-value:checked')?.value || '',
                comentario: row.querySelector('.criterion-comment').value.trim() || null
            }));
            rows.forEach(row => row.classList.toggle('is-missing', !row.querySelector('.criterion-value:checked')));
            const firstMissing = rows.find(row => !row.querySelector('.criterion-value:checked'));
            if (firstMissing) {
                firstMissing.scrollIntoView({ behavior: 'smooth', block: 'center' });
                showAlert('#alertContainer', 'warning', `Faltan ${rows.filter(row => !row.querySelector('.criterion-value:checked')).length} pregunta(s) por responder.`);
                return;
            }
            try {
                const evaluation = evaluations.find(item => String(item.id) === String(evaluationId));
                const confirmSave = await confirmAction({
                    title: 'Guardar evaluación',
                    text: `Se guardará la rúbrica para ${evaluationProject(evaluation)?.title || 'este proyecto'}.`,
                    confirmButtonText: 'Si, guardar'
                });
                if (!confirmSave) return;
                if (evaluation?.current_teacher_has_scores) throw new Error('Solicita un nuevo intento autorizado; el resultado registrado se conserva.');
                const payload = { scores, general_comment: document.getElementById('generalEvaluationComment').value.trim() || null };
                const aptValue = document.getElementById('apto_titulacion').value;
                if (aptValue !== '') payload.apto_titulacion = aptValue === '1';
                const response = await api.post(`/evaluations/${evaluationId}/score`, payload);
                replaceEvaluationLocal(response.evaluation);
                clearScoreDraft(evaluationId);
                document.getElementById('scoreEvaluationId').value = '';
                if (document.activeElement && document.getElementById('scoreModal').contains(document.activeElement)) {
                    document.activeElement.blur();
                }
                scoreModal.hide();
                showAlert('#alertContainer', 'success', 'Rubrica guardada correctamente');
                loadEvaluations(false, false, true);
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando rúbrica');
            }
        });

        function showBreakdown(evaluationId) {
            const evaluation = evaluations.find(item => item.id === evaluationId);
            const container = document.getElementById('breakdownContent');
            if (!evaluation) return;
            const aptSummary = evaluation.titulation_apt_summary || {};
            const renderAptVote = value => value === true ? 'Si' : (value === false ? 'No' : 'Sin respuesta');
            const aptSummaryBox = aptSummary.applies ? `
                <div class="alert alert-info border mb-3">
                    <strong>Recomendación del proyecto (sin efecto de egreso): ${escapeHtml(aptSummary.label || 'Sin votos')}</strong>
                </div>` : '';
            const renderTeacherScores = teacher => {
                if (!teacher.can_view_score_detail) {
                    return `
                        <div class="alert alert-secondary border mb-0">
                            <i class="bi bi-shield-lock"></i>
                            El desglose por criterio de este docente es privado. Solo se muestra su comentario general y promedio.
                        </div>`;
                }

                const scores = teacher.scores || [];
                if (!scores.length) {
                    return '<p class="text-muted mb-0">Sin respuestas detalladas registradas.</p>';
                }

                return `
                    <div class="d-grid gap-2">
                        ${scores.map(score => `
                            <div class="border rounded p-2">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <strong>${escapeHtml(score.criterio_label)}</strong>
                                    <span>
                                        <span class="badge bg-secondary">${escapeHtml(score.nivel_label || levelLabel(score.nivel))}</span>
                                        <span class="small text-muted ms-1">${Number(score.porcentaje ?? 0).toFixed(0)} %</span>
                                    </span>
                                </div>
                                <div class="small text-muted mt-2">Comentario de la pregunta</div>
                                <div class="bg-light border rounded p-2 mt-1 ${score.comentario ? '' : 'text-muted'}" style="white-space:pre-wrap">${escapeHtml(score.comentario || 'Sin comentario para esta pregunta.')}</div>
                            </div>`).join('')}
                    </div>`;
            };
            const teacherBlocks = evaluation.teacher_breakdown.length === 0
                ? '<p class="text-muted mb-0">Aún no hay evaluaciones registradas por docentes.</p>'
                : evaluation.teacher_breakdown.map(teacher => `
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">${escapeHtml(teacher.teacher_name)}</h6>
                        <span><span class="badge bg-primary">${teacher.average}%</span> ${verdictBadge(teacher.verdict, teacher.average)}</span>
                    </div>
                    ${aptSummary.applies ? `<div class="small text-muted mb-2"><strong>Recomendación del proyecto (sin efecto de egreso):</strong> ${escapeHtml(renderAptVote(teacher.apto_titulacion))}</div>` : ''}
                    ${teacher.general_comment ? `<div class="alert alert-light border py-2 mb-3"><strong>Comentario general:</strong><div class="mt-1" style="white-space:pre-wrap">${escapeHtml(teacher.general_comment)}</div></div>` : ''}
                    ${renderTeacherScores(teacher)}
                </div>`).join('');
            const feedbackBox = `
                <div class="border rounded p-3">
                    <h6>Retroalimentación de evaluación</h6>
                    <p class="text-muted small">${escapeHtml(evaluation.room_feedback || 'Sin retroalimentacion registrada.')}</p>
                    ${(evaluation.can_manage_evaluations || evaluation.is_room_responsible) ? `
                        <textarea class="form-control mb-2" id="roomFeedbackText" rows="3">${escapeHtml(evaluation.room_feedback || '')}</textarea>
                        <button class="btn btn-sm btn-primary" onclick="saveRoomFeedback(${evaluation.id})"><i class="bi bi-save"></i> Guardar retroalimentacion</button>
                    ` : ''}
                </div>`;
            const resultBox = evaluation.evaluators_count > 0 ? `
                <div class="d-flex flex-wrap align-items-center gap-2 border rounded p-3 mb-3">
                    <strong>Resultado del proyecto:</strong> <span class="fs-5">${evaluation.global_average}%</span> ${verdictBadge(evaluation.verdict, evaluation.global_average)}
                    <small class="text-muted ms-auto">Aprobado desde 80 % · Apenas cumple de 70 a 79 % · Reprobado menos de 70 %</small>
                </div>` : '';
            container.innerHTML = resultBox + aptSummaryBox + teacherBlocks + feedbackBox;
            breakdownModal.show();
        }

        async function saveRoomFeedback(evaluationId) {
            const response = await api.post(`/evaluations/${evaluationId}/feedback`, {
                room_feedback: document.getElementById('roomFeedbackText').value.trim()
            });
            replaceEvaluationLocal(response.evaluation);
            releaseModalFocus('breakdownModal');
            breakdownModal.hide();
            showAlert('#alertContainer', 'success', 'Retroalimentacion guardada');
            loadEvaluations(false, false, true);
        }

        function releaseModalFocus(modalId) {
            const modalElement = document.getElementById(modalId);
            if (modalElement?.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        }

        function replaceEvaluationLocal(updatedEvaluation) {
            if (!updatedEvaluation) return;
            const index = evaluations.findIndex(item => Number(item.id) === Number(updatedEvaluation.id));
            if (index >= 0) evaluations[index] = updatedEvaluation;
            else evaluations.unshift(updatedEvaluation);
            evaluations = orderEvaluationsByRoomSequence(removeDuplicateEvaluations(evaluations));
        }

        async function deleteEvaluation(id) {
            if (!await confirmAction({ title: 'Eliminar evaluación', text: '¿Eliminar esta evaluación?', confirmButtonText: 'Sí, eliminar' })) return;
            await api.delete(`/evaluations/${id}`);
            evaluations = evaluations.filter(evaluation => Number(evaluation.id) !== Number(id));
            showAlert('#alertContainer', 'success', 'Evaluación eliminada');
            loadEvaluations(false, false, true);
        }

        async function archiveEvaluation(id) {
            if (!await confirmAction({
                title: 'Archivar evaluación',
                text: 'La evaluación dejará de aparecer en la vista principal, pero conservará sus reportes y detalle.',
                confirmButtonText: 'Si, archivar'
            })) return;
            await api.post(`/evaluations/${id}/archive`, {});
            evaluations = evaluations.filter(evaluation => Number(evaluation.id) !== Number(id));
            showAlert('#alertContainer', 'success', 'Evaluación archivada');
            loadEvaluations(false, false, true);
        }

        async function unarchiveEvaluation(id) {
            if (!await confirmAction({
                title: 'Restaurar evaluación',
                text: 'La evaluación volverá a aparecer en la vista principal.',
                confirmButtonText: 'Si, restaurar'
            })) return;
            await api.post(`/evaluations/${id}/unarchive`, {});
            evaluations = evaluations.filter(evaluation => Number(evaluation.id) !== Number(id));
            showAlert('#alertContainer', 'success', 'Evaluación restaurada');
            loadEvaluations(false, false, true);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            evaluationModal = new bootstrap.Modal(document.getElementById('evaluationModal'));
            document.getElementById('scoreModal').addEventListener('hidden.bs.modal', stopScoreRoomTimer);
            rubricModal = new bootstrap.Modal(document.getElementById('rubricModal'));
            scoreModal = new bootstrap.Modal(document.getElementById('scoreModal'));
            breakdownModal = new bootstrap.Modal(document.getElementById('breakdownModal'));
            document.getElementById('breakdownModal').addEventListener('hide.bs.modal', () => releaseModalFocus('breakdownModal'));
            projectDetailsModal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
            roomsModal = new bootstrap.Modal(document.getElementById('roomsModal'));
            document.getElementById('scoreModal').addEventListener('hide.bs.modal', () => {
                if (document.activeElement && document.getElementById('scoreModal').contains(document.activeElement)) {
                    document.activeElement.blur();
                }
            });
            document.getElementById('scoreModal').addEventListener('hide.bs.modal', saveScoreDraft);
            document.getElementById('roomDate')?.addEventListener('input', updateRoomAvailability);
            document.getElementById('roomEndDate')?.addEventListener('input', updateRoomAvailability);
            try {
                await loadInitialData();
                await loadEvaluations();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo cargar la vista de evaluaciones.');
                console.error('Error cargando evaluaciones:', error);
            } finally {
                startEvaluationsRealtime();
            }
        });
    </script>
</body>
</html>
