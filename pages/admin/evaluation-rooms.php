<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || (!can_manage_projects() && !is_teacher())) {
    header('Location: /index.php');
    exit;
}
$canManageRooms = can_manage_projects();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Salas - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
    <style>
        .room-wizard .modal-header .room-wizard-subtitle { color: inherit; opacity: .82; }
        .room-wizard-steps { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
        .room-wizard-step { align-items: center; background: var(--soft-bg); border: 1px solid var(--border-color); border-radius: 999px; color: var(--text-muted); display: inline-flex; font-weight: 600; gap: .5rem; padding: .35rem .9rem .35rem .35rem; }
        .room-wizard-step span { align-items: center; background: var(--surface-bg); border: 1px solid var(--border-color); border-radius: 50%; display: inline-flex; font-size: .8rem; height: 1.6rem; justify-content: center; width: 1.6rem; }
        .room-wizard-step em { font-style: normal; font-size: .78rem; }
        .room-wizard-step.is-current { border-color: var(--primary-blue); color: var(--primary-blue); }
        .room-wizard-step.is-current span { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }
        .room-wizard-step.is-done span { background: var(--success-accent); border-color: var(--success-accent); color: #fff; }
        .room-wizard-step:focus-visible { outline: 2px solid var(--primary-blue); outline-offset: 2px; }
        .room-wizard-layout { display: grid; gap: 1.25rem; grid-template-columns: minmax(0, 1fr) 280px; }
        .room-wizard-main { min-width: 0; }
        .room-wizard-summary { align-self: start; background: var(--soft-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; position: sticky; top: 0; }
        .room-wizard-summary h6 { color: var(--text-muted); font-size: .72rem; letter-spacing: .08em; margin-bottom: .75rem; }
        .room-summary-list { display: grid; gap: .6rem; list-style: none; margin: 0 0 1rem; padding: 0; }
        .room-summary-list li { display: grid; gap: .1rem .5rem; grid-template-columns: 1.1rem minmax(0, 1fr); font-size: .875rem; }
        .room-summary-list li > i { grid-row: span 2; padding-top: .1rem; }
        .room-summary-list li small { color: var(--text-muted); }
        .room-summary-list li.is-missing { color: var(--text-muted); }
        .room-summary-list li.is-ok > i { color: var(--success-accent); }
        .room-summary-list li.is-missing > i { color: var(--warning-accent); }
        .room-field-group { margin-bottom: 1rem; }
        .room-chip-row { display: flex; flex-wrap: wrap; gap: .5rem; }
        .room-chip { background: var(--surface-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-dark); font-weight: 600; min-width: 7.5rem; padding: .5rem .85rem; text-align: left; }
        .room-chip small { color: var(--text-muted); display: block; font-weight: 500; }
        .room-chip[aria-checked="true"] { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }
        .room-chip[aria-checked="true"] small { color: rgba(255, 255, 255, .8); }
        .room-times { border: 1px solid var(--border-color); border-radius: 8px; padding: .6rem .85rem; }
        .room-times summary { cursor: pointer; font-size: .9rem; }
        .room-order-badge { align-items: center; background: var(--primary-blue); border-radius: 50%; color: #fff; display: inline-flex; flex: 0 0 1.75rem; font-size: .8rem; font-variant-numeric: tabular-nums; font-weight: 700; height: 1.75rem; justify-content: center; margin-top: .25rem; }
        .room-order-badge.is-empty { background: transparent; border: 1px dashed var(--border-color); color: var(--text-muted); }
        .room-order-arrows { display: flex; flex-direction: column; gap: 2px; }
        .room-order-arrows .btn { line-height: 1; padding: 0 .35rem; }
        .room-teacher-grid { display: grid; gap: .5rem; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
        .room-teacher-grid .room-option-card + .room-option-card { margin-top: 0; }
        .room-scroll-list { max-height: 46vh; }
        @media (max-width: 991.98px) {
            .room-wizard-layout { grid-template-columns: 1fr; }
            .room-wizard-summary { order: -1; position: static; }
        }
        .room-time-budget { font-size: .875rem; border-radius: .5rem; padding: .5rem .75rem; background: var(--bs-light); }
        .room-time-budget.is-short { background: rgba(var(--bs-danger-rgb), .1); color: var(--bs-danger); }
        .room-time-budget.is-ok { background: rgba(var(--bs-success-rgb), .1); color: var(--bs-success-text-emphasis); }
        .room-option-card.is-busy { opacity: .55; }
        .room-special-cases { border: 1px dashed var(--bs-warning); border-radius: .5rem; padding: .5rem .75rem; }
        .room-special-cases summary { cursor: pointer; font-weight: 600; }
        .room-special-reason { font-size: .8rem; }
        .room-work-panel {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .room-work-panel-header {
            background: var(--soft-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.15rem;
        }
        .room-work-panel-body {
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
            max-height: 320px;
            overflow: auto;
            padding: .75rem;
        }
        .room-option-card,
        .room-project-item,
        .room-list-card,
        .room-teacher-card,
        .room-project-card {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }
        .room-option-card {
            align-items: flex-start;
            cursor: pointer;
            display: flex;
            gap: .65rem;
            padding: .7rem .8rem;
        }
        .room-option-card + .room-option-card,
        .room-project-item + .room-project-item,
        .room-list-card + .room-list-card {
            margin-top: .65rem;
        }
        .room-option-card:has(input:checked),
        .room-project-item:has(input:checked) {
            background: color-mix(in srgb, var(--primary-blue) 9%, var(--surface-bg));
            border-color: color-mix(in srgb, var(--primary-blue) 45%, var(--border-color));
        }
        .room-project-item,
        .room-list-card,
        .room-teacher-card,
        .room-project-card {
            padding: .9rem;
        }
        .room-meta-grid {
            display: grid;
            gap: .45rem .8rem;
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
        .room-metric {
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: .75rem;
            text-align: center;
        }
        .room-metric strong {
            color: var(--primary-blue);
            display: block;
            font-size: 1.25rem;
        }
        .room-metric span {
            color: var(--text-muted);
            font-size: .82rem;
        }
        .room-step-list {
            display: grid;
            gap: .5rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 1rem;
        }
        .room-step {
            align-items: center;
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-muted);
            display: flex;
            gap: .55rem;
            min-height: 48px;
            padding: .6rem .7rem;
        }
        .room-step i {
            color: var(--primary-blue);
            font-size: 1rem;
        }
        .room-step strong {
            color: var(--text-color);
            display: block;
            font-size: .82rem;
            line-height: 1.1;
        }
        .room-step span {
            display: block;
            font-size: .74rem;
            line-height: 1.15;
        }
        .room-form-help {
            background: color-mix(in srgb, var(--primary-blue) 7%, var(--surface-bg));
            border: 1px solid color-mix(in srgb, var(--primary-blue) 26%, var(--border-color));
            border-radius: 8px;
            color: var(--text-color);
            font-size: .88rem;
            margin-bottom: 1rem;
            padding: .75rem .85rem;
        }
        .room-inline-hint {
            color: var(--text-muted);
            font-size: .8rem;
            margin-top: .3rem;
        }
        .room-live-timer {
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin: .8rem 0;
            padding: .75rem;
        }
        .room-live-timer.running { border-color: var(--success-color, #198754); }
        .room-live-timer.expired { border-color: var(--warning-color, #f59e0b); }
        .room-live-timer-display { font-size: 1.65rem; font-variant-numeric: tabular-nums; font-weight: 700; }
        .room-live-timer-controls { align-items: end; display: flex; flex-wrap: wrap; gap: .35rem; }
        .room-live-timer-controls input { width: 82px; }
        @media (max-width: 991.98px) {
            .room-meta-grid {
                grid-template-columns: 1fr;
            }
            .room-action-group {
                justify-content: flex-start;
            }
            .room-step-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 575.98px) {
            .room-step-list {
                grid-template-columns: 1fr;
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
                        <h1 class="mb-1">Salas de evaluación</h1>
                        <p class="text-muted mb-0">Función de evaluaciones para programar salas, responsables, docentes y orden de proyectos.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/pages/admin/evaluations.php">
                        <i class="bi bi-arrow-left"></i> Volver a evaluaciones
                    </a>
                </div>

                <div id="alertContainer"></div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label" for="semesterFilter">Semestre</label>
                        <select data-semester-scope="career" data-semester-max="8" data-semester-min="5" class="form-select" id="semesterFilter" onchange="renderCurrentView()">
                            <option value="">Todos</option>
                            <option value="5">5 - Propuesta</option>
                            <option value="6">6 - Avance</option>
                            <option value="7">7 - Avance</option>
                            <option value="8">8 - Titulacion</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="roomStatusFilter">Estado</label>
                        <select class="form-select" id="roomStatusFilter" onchange="reloadRooms()">
                            <option value="active">Activas</option>
                            <option value="archived">Archivadas</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="roomSearch">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" id="roomSearch" placeholder="Sala, docente, proyecto o salon" oninput="renderCurrentView()">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Vista de gestión de salas">
                    <button type="button" class="btn btn-primary" id="viewRoomsBtn" onclick="setRoomView('rooms')">
                        <i class="bi bi-door-open"></i> Por sala
                    </button>
                    <?php if ($canManageRooms): ?>
                    <button type="button" class="btn btn-outline-secondary" id="viewProjectsBtn" onclick="setRoomView('projects')">
                        <i class="bi bi-kanban"></i> Por proyecto
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="viewTeachersBtn" onclick="setRoomView('teachers')">
                        <i class="bi bi-person-workspace"></i> Por evaluador
                    </button>
                    <button type="button" class="btn btn-success ms-md-auto" onclick="startNewRoom()">
                        <i class="bi bi-plus-circle"></i> Crear sala
                    </button>
                    <?php endif; ?>
                </div>

                <div class="row g-3 mb-4" id="roomSummary">
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryRooms">0</strong><span>Salas</span></div></div>
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryProjects">0</strong><span>Proyectos asignados</span></div></div>
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryTeachers">0</strong><span>Evaluadores</span></div></div>
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryPending">0</strong><span>Sin sala</span></div></div>
                </div>

                <div id="roomByRoomView">
                    <div class="room-work-panel">
                        <div class="room-work-panel-header d-flex justify-content-between align-items-center gap-2">
                            <h5 class="mb-0"><i class="bi bi-list-check"></i> Salas registradas</h5>
                            <span class="badge bg-light text-primary" id="roomsCountBadge">0 salas</span>
                        </div>
                        <div class="room-work-panel-body" id="roomsList"></div>
                    </div>
                </div>

                <?php if ($canManageRooms): ?>
                <div class="d-none" id="roomByProjectView">
                    <div class="room-work-panel">
                        <div class="room-work-panel-header">
                            <h5 class="mb-0"><i class="bi bi-kanban"></i> Proyectos por sala</h5>
                        </div>
                        <div class="room-work-panel-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Proyecto</th>
                                            <th>Semestre</th>
                                            <th>Sala actual</th>
                                            <th>Asignar a sala</th>
                                            <th>Orden</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="projectRoomTable"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-none" id="roomByTeacherView">
                    <div class="row g-3" id="teacherRoomContainer"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($canManageRooms): ?>
    <!-- Alta y edición de sala en tres pasos con un resumen que dice qué falta. Los id se conservan para la lógica existente. -->
    <div class="modal fade" id="roomFormModal" tabindex="-1" aria-labelledby="roomFormTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
            <div class="modal-content room-wizard" id="roomFormPanel">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="roomFormTitle">Nueva sala</h5>
                        <small class="room-wizard-subtitle" id="roomFormSubtitle">Tres pasos: cuándo y dónde, proyectos y evaluadores.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="roomId">
                    <nav class="room-wizard-steps" aria-label="Pasos para crear la sala">
                        <button type="button" class="room-wizard-step is-current" data-step="1" onclick="goRoomStep(1)"><span>1</span> Cuándo y dónde</button>
                        <button type="button" class="room-wizard-step" data-step="2" onclick="goRoomStep(2)"><span>2</span> Proyectos <em id="roomStepProjectsCount"></em></button>
                        <button type="button" class="room-wizard-step" data-step="3" onclick="goRoomStep(3)"><span>3</span> Evaluadores <em id="roomStepTeachersCount"></em></button>
                    </nav>
                    <div class="room-wizard-layout">
                        <div class="room-wizard-main">
                            <section class="room-wizard-pane" data-pane="1">
                                <div class="room-field-group">
                                    <span class="form-label d-block">Semestre que se evalúa</span>
                                    <div class="room-chip-row" id="roomSemesterChips" role="radiogroup" aria-label="Semestre"></div>
                                    <select data-semester-scope="period" data-semester-max="8" data-semester-min="5" class="d-none" id="roomSemester" onchange="onRoomSemesterChange()" aria-hidden="true" tabindex="-1">
                                        <option value="5">5° · Propuesta</option>
                                        <option value="6">6° · Avance</option>
                                        <option value="7">7° · Avance</option>
                                        <option value="8">8° · Titulación</option>
                                    </select>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="roomDate">Inicio</label>
                                        <input type="datetime-local" class="form-control" id="roomDate" onchange="updateRoomAvailability()">
                                        <div class="room-inline-hint" id="roomDateHint">Debe ser posterior a la fecha actual.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="roomEndDate">Fin</label>
                                        <input type="datetime-local" class="form-control" id="roomEndDate" onchange="roomEndAuto = false; updateRoomAvailability()">
                                        <div class="room-inline-hint">Se calcula solo con los proyectos; puedes cambiarlo.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="roomClassroom">Salón</label>
                                        <select class="form-select" id="roomClassroom" onchange="renderRoomSummary()"><option value="">Selecciona salón institucional</option></select>
                                        <div class="room-inline-hint">Los salones ocupados en ese horario aparecen bloqueados.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="roomName">Nombre de la sala</label>
                                        <input class="form-control" id="roomName" placeholder="SALA 1" oninput="roomNameAuto = false; renderRoomSummary()">
                                        <div class="room-inline-hint">Te sugerimos el siguiente número libre.</div>
                                    </div>
                                </div>
                                <details class="room-times mt-3">
                                    <summary>Tiempos por proyecto: <strong id="roomTimesSummary">20 min de presentación + 15 de evaluación · 1 oportunidad</strong></summary>
                                    <div class="row g-3 mt-1">
                                        <div class="col-sm-4">
                                            <label class="form-label" for="presentationMinutes">Presentación (min)</label>
                                            <input type="number" class="form-control" id="presentationMinutes" min="1" max="240" value="20" oninput="renderTimeBudget()">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label" for="teacherMinutes">Evaluación docente (min)</label>
                                            <input type="number" class="form-control" id="teacherMinutes" min="1" max="240" value="15" oninput="renderTimeBudget()">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label" for="maxAttempts">Oportunidades</label>
                                            <input type="number" class="form-control" id="maxAttempts" min="1" max="10" value="1" oninput="renderRoomSummary()">
                                        </div>
                                    </div>
                                </details>
                                <div class="small text-muted mt-3" id="roomAvailabilityHint"></div>
                            </section>

                            <section class="room-wizard-pane" data-pane="2" hidden>
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                    <input type="search" class="form-control form-control-sm flex-grow-1" id="roomProjectSearch" placeholder="Buscar proyecto o integrante..." oninput="filterRoomOptions('roomProjects', this.value)">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllRoomProjects(true)">Marcar todos</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllRoomProjects(false)">Quitar todos</button>
                                </div>
                                <div class="room-inline-hint mb-2">Marca los proyectos en el orden en que presentarán; ajusta el orden con las flechas.</div>
                                <div class="room-scroll-list" id="roomProjects"></div>
                                <details class="room-special-cases mt-3" id="roomSpecialCasesBox">
                                    <summary>
                                        <i class="bi bi-stars"></i> Casos especiales <span class="badge text-bg-warning ms-1" id="roomSpecialCount">0</span>
                                        <span class="room-inline-hint d-block">Proyectos de otro semestre que se evaluarán con la etapa de esta sala, por ejemplo alumnos de 7° que adelantaron materias y se evalúan como 8° para saber si van a titulación.</span>
                                    </summary>
                                    <input type="search" class="form-control form-control-sm my-2" id="roomSpecialSearch" placeholder="Buscar proyecto de otro semestre..." oninput="filterRoomOptions('roomSpecialCases', this.value)">
                                    <div class="room-scroll-list" id="roomSpecialCases"></div>
                                </details>
                            </section>

                            <section class="room-wizard-pane" data-pane="3" hidden>
                                <input type="search" class="form-control form-control-sm mb-2" id="roomTeacherSearch" placeholder="Buscar evaluador..." oninput="filterRoomOptions('roomTeachers', this.value)">
                                <div class="room-inline-hint mb-2">Un docente no puede estar en dos salas a la vez; los ocupados en ese horario aparecen bloqueados.</div>
                                <div class="room-scroll-list room-teacher-grid" id="roomTeachers"></div>
                                <div class="mt-3">
                                    <label class="form-label" for="responsibleTeacher">Responsable de sala</label>
                                    <select class="form-select" id="responsibleTeacher" onchange="renderRoomSummary()"><option value="">Selecciona primero docentes</option></select>
                                    <div class="room-inline-hint">Se propone el primer evaluador que marques.</div>
                                </div>
                            </section>
                        </div>

                        <aside class="room-wizard-summary" aria-label="Resumen de la sala">
                            <h6 class="text-uppercase">Resumen</h6>
                            <ul class="room-summary-list" id="roomSummaryList"></ul>
                            <div class="room-time-budget" id="roomTimeBudget" aria-live="polite"></div>
                        </aside>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="roomPrevBtn" onclick="goRoomStep(roomStep - 1)"><i class="bi bi-arrow-left"></i> Atrás</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="roomNextBtn" onclick="goRoomStep(roomStep + 1)">Siguiente <i class="bi bi-arrow-right"></i></button>
                        <button type="button" class="btn btn-primary" id="roomSaveBtn" onclick="saveRoom()"><i class="bi bi-save"></i> Guardar sala</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="roomScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="roomScheduleForm">
                <div class="modal-header"><h5 class="modal-title">Horario y evaluación fuera de tiempo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" id="scheduleRoomId">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="scheduleStart">Inicio</label><input class="form-control" id="scheduleStart" type="datetime-local" required></div>
                        <div class="col-md-6"><label class="form-label" for="scheduleEnd">Fin ordinario</label><input class="form-control" id="scheduleEnd" type="datetime-local" required></div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input" id="scheduleAllowLate" type="checkbox" onchange="toggleLateScheduleFields()"><label class="form-check-label" for="scheduleAllowLate">Permitir evaluaciones fuera del horario ordinario</label></div></div>
                        <div class="col-md-6 d-none" id="scheduleLateUntilBox"><label class="form-label" for="scheduleLateUntil">Autorizada hasta</label><input class="form-control" id="scheduleLateUntil" type="datetime-local"></div>
                        <div class="col-12 d-none" id="scheduleLateReasonBox"><label class="form-label" for="scheduleLateReason">Motivo</label><textarea class="form-control" id="scheduleLateReason" rows="3" minlength="10" maxlength="500"></textarea></div>
                    </div>
                    <div class="small text-muted mt-3">Si la sala ya comenzó, la hora de inicio queda protegida; todavía puedes extender el cierre o autorizar una captura tardía.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar horario</button></div>
            </form>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="<?= asset('/assets/js/auth.js') ?>"></script>
    <script src="<?= asset('/assets/js/api.js') ?>"></script>
    <script src="<?= asset('/assets/js/app.js') ?>"></script>
    <script>
        const CAN_MANAGE_ROOMS = <?= $canManageRooms ? 'true' : 'false' ?>;
        // Con nombres de evaluadores sólo descargan jefatura y administración general; los demás, el informe limitado.
        const FULL_EVALUATION_REPORTS = <?= can_download_full_evaluation_reports() ? 'true' : 'false' ?>;
        let teachers = [];
        let projects = [];
        let rooms = [];
        let roomProjects = [];
        let roomView = 'rooms';
        let roomProjectsReorderTimer = null;
        const roomScheduleModal = new bootstrap.Modal(document.getElementById('roomScheduleModal'));

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function projectSemester(project) {
            return Number(project?.presentation_semester ?? project?.semestre ?? 0);
        }

        function fullName(user) {
            // La API manda los apellidos con dos nombres (apa y apellido_paterno): se toma uno de cada uno.
            return [user?.nombres, user?.apa ?? user?.apellido_paterno, user?.ama ?? user?.apellido_materno].filter(Boolean).join(' ') || user?.id || '';
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

        function projectCompany(project) {
            return project?.company_name || project?.empresa?.nombre || '';
        }

        function normalizeRoomName(value) {
            const clean = String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
            if (/^SALA\s+\d+/.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || '';
            return number ? `SALA ${number}` : clean;
        }

        function normalizeClassroom(value) {
            const clean = String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
            if (/^EB\d+/.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || '';
            return number ? `EB${number}` : clean;
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

        function nextMinuteInput() {
            const date = new Date();
            date.setSeconds(0, 0);
            date.setMinutes(date.getMinutes() + 1);
            return formatLocalDateTimeInput(date);
        }

        function roomRange(room) {
            const start = parseRoomDate(room.fecha_evaluacion);
            const end = parseRoomDate(room.fecha_fin_evaluacion);
            return { start, end: end || (start ? new Date(start.getTime() + 60 * 60 * 1000) : null) };
        }

        function rangesOverlap(startA, endA, startB, endB) {
            return startA && endA && startB && endB && startA < endB && endA > startB;
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

        function roomForProject(projectId) {
            return rooms.find(room => (room.projects || []).some(project => Number(project.id) === Number(projectId))) || null;
        }

        function currentSemesterFilter() {
            return document.getElementById('semesterFilter').value;
        }

        function searchText() {
            return document.getElementById('roomSearch').value.trim().toLowerCase();
        }

        function filteredRooms() {
            const semester = currentSemesterFilter();
            const search = searchText();
            return rooms.filter(room => {
                if (semester && String(room.semestre) !== String(semester)) return false;
                if (!search) return true;
                const haystack = [
                    roomDisplayName(room),
                    room.salon,
                    fullName(room.responsible_teacher),
                    ...(room.teachers || []).map(fullName),
                    ...(room.projects || []).map(project => project.title)
                ].join(' ').toLowerCase();
                return haystack.includes(search);
            });
        }

        function filteredProjects() {
            const semester = currentSemesterFilter();
            const search = searchText();
            return projects.filter(project => {
                if (semester && String(projectSemester(project)) !== String(semester)) return false;
                if (!search) return true;
                const currentRoom = roomForProject(project.id);
                const haystack = `${project.title} ${projectActiveAuthors(project)} ${projectCompany(project)} ${currentRoom?.nombre || ''}`.toLowerCase();
                return haystack.includes(search);
            });
        }

        // Pueden evaluar los mismos perfiles que acepta la API: administración, docentes, jefatura, asistencia y coordinación.
        const EVALUATOR_PROFILES = { 1: 'Administrativo', 2: 'Docente', 5: 'Jefatura', 6: 'Asistencia de jefatura', 7: 'Coordinación' };
        let classrooms = [];

        async function loadTeachers() {
            const response = await api.getAll('/users', { perfil_ids: Object.keys(EVALUATOR_PROFILES).join(','), status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000, _timeout: 45000 });
            teachers = (response.data || [])
                .sort((a, b) => (Number(a.perfil_id) === 2 ? 0 : 1) - (Number(b.perfil_id) === 2 ? 0 : 1) || fullName(a).localeCompare(fullName(b)));
        }

        function projectCategoryBadge(project) {
            if (project.tipo === 'tesis') return '<span class="badge text-bg-success ms-1">Tesis</span>';
            if (project.modalidad === 'dual') return '<span class="badge text-bg-info ms-1">Dual</span>';
            if (project.modalidad === 'caso_integrador') return '<span class="badge text-bg-secondary ms-1">Caso integrador</span>';
            return '';
        }

        function academicSemester(project) {
            return Number(project?.academic_semester ?? project?.semestre ?? 0);
        }

        function filterRoomOptions(containerId, text) {
            const needle = String(text || '').trim().toLowerCase();
            document.querySelectorAll(`#${containerId} > *`).forEach(item => {
                item.classList.toggle('d-none', Boolean(needle) && !item.textContent.toLowerCase().includes(needle));
            });
        }

        function toDateTimeInput(value) {
            // La API entrega "YYYY-MM-DD HH:MM:SS"; datetime-local exige la "T".
            return value ? String(value).replace(' ', 'T').slice(0, 16) : '';
        }

        function requiredRoomMinutes(projectCount = selectedRoomProjectIds().length) {
            const perProject = Number(document.getElementById('teacherMinutes').value || 0) + Number(document.getElementById('presentationMinutes').value || 0);
            return projectCount * perProject;
        }

        function selectedRoomProjectIds() {
            return [...document.querySelectorAll('.room-project:checked, .room-special:checked')].map(input => Number(input.value));
        }

        function renderTimeBudget() {
            const box = document.getElementById('roomTimeBudget');
            if (!box) return;
            autoFitRoomEnd();
            renderRoomSummary();
            const count = selectedRoomProjectIds().length;
            const required = requiredRoomMinutes(count);
            const start = parseRoomDate(document.getElementById('roomDate').value);
            const end = parseRoomDate(document.getElementById('roomEndDate').value);
            box.className = 'room-time-budget';
            if (!count) {
                box.innerHTML = '<i class="bi bi-hourglass"></i> Marca proyectos para calcular el tiempo necesario.';
                return;
            }
            const available = start && end ? Math.round((end - start) / 60000) : 0;
            const short = available < required;
            box.classList.add(short ? 'is-short' : 'is-ok');
            box.innerHTML = `<i class="bi ${short ? 'bi-exclamation-triangle' : 'bi-check-circle'}"></i>
                ${count} proyecto(s) × ${requiredRoomMinutes(1)} min = <strong>${required} min</strong> necesarios · disponibles <strong>${available} min</strong>
                ${short && start ? '<button type="button" class="btn btn-sm btn-link p-0 ms-2 align-baseline" onclick="fitRoomEndToProjects()">Ajustar fin</button>' : ''}`;
        }

        function fitRoomEndToProjects() {
            const start = parseRoomDate(document.getElementById('roomDate').value);
            if (!start || document.getElementById('roomEndDate').readOnly) return;
            document.getElementById('roomEndDate').value = formatLocalDateTimeInput(new Date(start.getTime() + Math.max(60, requiredRoomMinutes()) * 60000));
            updateRoomAvailability();
        }

        async function loadProjects() {
            projects = await api.get('/evaluations/projects', { _cache_ttl: 30000, _timeout: 45000 });
        }

        async function loadRooms() {
            const archived = document.getElementById('roomStatusFilter').value === 'archived' ? 1 : 0;
            rooms = await api.get('/evaluations/rooms', { archived, _fresh: 1, _timeout: 30000 });
        }

        async function reloadRooms() {
            await loadRooms();
            renderCurrentView();
        }

        async function loadInitialData() {
            if (CAN_MANAGE_ROOMS) {
                const catalog = await api.get('/academic-operations/catalog');
                classrooms = catalog.rooms || [];
                renderClassroomOptions();
                await Promise.all([loadTeachers(), loadProjects(), loadRooms()]);
                renderRoomTeachers();
                loadRoomProjects();
            } else {
                await loadRooms();
            }
            renderCurrentView();
        }

        function setRoomView(view) {
            if (roomView === view) return;
            SGPIViewTransition.run(() => {
                roomView = view;
                document.getElementById('roomByRoomView').classList.toggle('d-none', view !== 'rooms');
                document.getElementById('roomByProjectView').classList.toggle('d-none', view !== 'projects');
                document.getElementById('roomByTeacherView').classList.toggle('d-none', view !== 'teachers');
                document.getElementById('viewRoomsBtn').className = view === 'rooms' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('viewProjectsBtn').className = view === 'projects' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('viewTeachersBtn').className = view === 'teachers' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                renderCurrentView();
            });
        }

        function renderCurrentView() {
            renderSummary();
            if (roomView === 'rooms') renderRooms();
            if (roomView === 'projects') renderProjectRoomView();
            if (roomView === 'teachers') renderTeacherRoomView();
        }

        function renderSummary() {
            const visibleRooms = filteredRooms();
            const assignedProjectIds = new Set(rooms.flatMap(room => (room.projects || []).map(project => Number(project.id))));
            const visibleProjects = filteredProjects();
            const visibleTeacherIds = new Set(visibleRooms.flatMap(room => (room.teachers || []).map(teacher => String(teacher.id))));
            document.getElementById('summaryRooms').textContent = visibleRooms.length;
            document.getElementById('summaryProjects').textContent = visibleRooms.reduce((total, room) => total + (room.projects || []).length, 0);
            document.getElementById('summaryTeachers').textContent = visibleTeacherIds.size;
            document.getElementById('summaryPending').textContent = visibleProjects.filter(project => !assignedProjectIds.has(Number(project.id))).length;
        }

        function renderRooms() {
            const visibleRooms = filteredRooms();
            document.getElementById('roomsCountBadge').textContent = `${visibleRooms.length} sala${visibleRooms.length === 1 ? '' : 's'}`;
            document.getElementById('roomsList').innerHTML = visibleRooms.map(room => {
                const archived = document.getElementById('roomStatusFilter').value === 'archived';
                const windowStatus = room.evaluation_window?.status || 'closed';
                const windowLabel = windowStatus === 'open' ? 'Horario abierto' : windowStatus === 'late_authorized' ? 'Fuera de horario autorizado' : windowStatus === 'scheduled' ? 'Pendiente de inicio' : 'Horario cerrado';
                const windowClass = room.evaluation_window?.is_open ? 'text-success' : 'text-warning';
                return `
                    <div class="room-list-card">
                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="fw-bold">${escapeHtml(roomDisplayName(room))}</span>
                                    <span class="badge bg-secondary">Semestre ${escapeHtml(room.semestre)}</span>
                                    ${room.sequence_locked ? '<span class="badge bg-primary">Orden bloqueado</span>' : ''}
                                    ${room.completed_at ? '<span class="badge bg-success">Finalizada</span>' : ''}
                                </div>
                                <div class="room-meta-grid small">
                                    <div class="room-meta-item"><span class="text-muted d-block">Lugar</span>${escapeHtml(room.salon || 'Sin salon')}</div>
                                    <div class="room-meta-item"><span class="text-muted d-block">Horario</span>${room.fecha_evaluacion ? new Date(room.fecha_evaluacion).toLocaleString('es-MX') : 'Sin inicio'}${room.fecha_fin_evaluacion ? ` - ${new Date(room.fecha_fin_evaluacion).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}` : ''}</div>
                                    <div class="room-meta-item"><span class="text-muted d-block">Responsable</span>${escapeHtml(fullName(room.responsible_teacher) || '-')}</div>
                                    <div class="room-meta-item"><span class="text-muted d-block">Tiempos</span>${room.project_presentation_minutes} min exposición / ${room.teacher_evaluation_minutes} min evaluación</div>
                                </div>
                                <div class="small mb-2"><span class="fw-semibold">Evaluadores:</span> ${(room.teachers || []).map(teacher => escapeHtml(fullName(teacher))).filter(Boolean).join(', ') || '-'}</div>
                                <div class="small ${windowClass} mb-2"><i class="bi bi-clock"></i> ${escapeHtml(windowLabel)}${windowStatus === 'late_authorized' ? ` hasta ${new Date(room.late_evaluation_until).toLocaleString('es-MX')}` : ''}</div>
                                ${room.sequence_locked && !room.completed_at ? roomTimerMarkup(room) : ''}
                                <div class="small fw-semibold mb-1">Proyectos (${(room.projects || []).length})</div>
                                <ol class="small mb-0 ps-3">${orderedRoomProjects(room).map(project => `<li class="mb-1 ${project.is_current ? 'fw-bold text-primary' : ''}">${escapeHtml(project.title)} <span class="badge bg-light text-dark">Orden ${escapeHtml(project.presentation_order || project.pivot?.presentation_order || '-')}</span> <span class="text-muted">${project.sequence_status === 'evaluado' ? 'Finalizada' : `${Number(project.submitted_evaluators_count || 0)}/${Number(project.expected_evaluators_count || 0)} rúbricas`}</span></li>`).join('') || '<li>Sin proyectos asignados</li>'}</ol>
                            </div>
                            <div class="room-action-group align-self-start">
                                ${FULL_EVALUATION_REPORTS ? `<button class="btn btn-sm btn-outline-dark" onclick="downloadRoomReport(${room.id})" title="Reporte completo con nombres de evaluadores"><i class="bi bi-file-earmark-pdf"></i></button>` : ''}
                                <button class="btn btn-sm btn-outline-info" onclick="downloadRoomTeacherReport(${room.id})" title="Informe limitado: evaluadores sin nombre y sólo comentarios generales"><i class="bi bi-incognito"></i></button>
                                ${!archived ? `
                                    ${room.can_edit_room ? `<button class="btn btn-sm btn-outline-primary" onclick="editRoom(${room.id})" title="Editar"><i class="bi bi-pencil"></i></button>` : ''}
                                    ${room.can_control_room ? `<button class="btn btn-sm btn-outline-primary" onclick="openRoomSchedule(${room.id})" title="Modificar horario"><i class="bi bi-clock-history"></i></button>` : ''}
                                    ${CAN_MANAGE_ROOMS && !room.sequence_locked ? `<button class="btn btn-sm btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"><i class="bi bi-lock"></i></button>` : ''}
                                    ${room.can_control_room && room.sequence_locked && !room.completed_at ? `<button class="btn btn-sm btn-outline-warning" onclick="advanceRoom(${room.id})" title="Siguiente proyecto"><i class="bi bi-skip-forward"></i></button>` : ''}
                                    ${CAN_MANAGE_ROOMS ? `<button class="btn btn-sm btn-outline-secondary" onclick="archiveRoom(${room.id})" title="Archivar"><i class="bi bi-archive"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(${room.id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                ` : ''}
                            </div>
                        </div>
                    </div>`;
            }).join('') || '<p class="text-muted mb-0">No hay salas con los filtros seleccionados.</p>';
            tickRoomTimers();
        }

        function roomTimerMarkup(room) {
            const timer = room.timer || {};
            const remaining = Number(timer.remaining_seconds || 0);
            const minutes = Math.max(1, Math.ceil(Number(timer.duration_seconds || room.project_presentation_minutes * 60) / 60));
            return `<div class="room-live-timer ${timer.status === 'en_curso' ? 'running' : ''} ${remaining === 0 ? 'expired' : ''}">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span><i class="bi bi-stopwatch"></i> Tiempo del equipo en turno</span>
                    <span class="room-live-timer-display" data-room-timer="${room.id}" data-status="${escapeHtml(timer.status || 'detenido')}" data-remaining="${remaining}">00:00</span>
                </div>
                ${room.can_control_room ? `<div class="room-live-timer-controls">
                    <label class="small">Minutos<input class="form-control form-control-sm" id="timerMinutes${room.id}" type="number" min="1" max="240" value="${minutes}" ${timer.status === 'en_curso' ? 'disabled' : ''}></label>
                    ${timer.status === 'en_curso'
                        ? `<button class="btn btn-sm btn-outline-warning" onclick="controlRoomTimer(${room.id}, 'pause')"><i class="bi bi-pause"></i> Pausar</button>`
                        : timer.status === 'pausado'
                            ? `<button class="btn btn-sm btn-outline-success" onclick="controlRoomTimer(${room.id}, 'resume')"><i class="bi bi-play"></i> Reanudar</button>`
                            : `<button class="btn btn-sm btn-outline-success" onclick="controlRoomTimer(${room.id}, 'start')"><i class="bi bi-play"></i> Iniciar</button>`}
                    <button class="btn btn-sm btn-outline-secondary" onclick="controlRoomTimer(${room.id}, 'reset')"><i class="bi bi-arrow-counterclockwise"></i> Reiniciar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="controlRoomTimer(${room.id}, 'finish')"><i class="bi bi-stop"></i> Finalizar</button>
                </div>` : ''}
            </div>`;
        }

        function tickRoomTimers() {
            document.querySelectorAll('[data-room-timer]').forEach(display => {
                let remaining = Math.max(0, Number(display.dataset.remaining || 0));
                if (display.dataset.status === 'en_curso' && display.dataset.tickReady === '1') remaining = Math.max(0, remaining - 1);
                display.dataset.remaining = remaining;
                display.dataset.tickReady = '1';
                display.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
            });
        }

        function renderProjectRoomView() {
            const rows = filteredProjects();
            document.getElementById('projectRoomTable').innerHTML = rows.map(project => {
                const currentRoom = roomForProject(project.id);
                const availableRooms = rooms.filter(room => String(room.semestre) === String(projectSemester(project)));
                const currentOrder = currentRoom ? (orderedRoomProjects(currentRoom).find(item => Number(item.id) === Number(project.id))?.presentation_order || '') : '';
                return `
                    <tr>
                        <td>
                            <strong>${escapeHtml(project.title)}</strong>
                            <div class="small text-muted">${escapeHtml(projectActiveAuthors(project) || 'Sin integrantes registrados')}</div>
                        </td>
                        <td>${escapeHtml(projectSemester(project) || '-')}</td>
                        <td>${currentRoom ? `<span class="badge bg-primary">${escapeHtml(roomDisplayName(currentRoom))}</span><div class="small text-muted">${escapeHtml(currentRoom.salon || '')}</div>` : '<span class="text-muted">Sin sala</span>'}</td>
                        <td>
                            <select class="form-select form-select-sm" id="projectRoom${project.id}">
                                <option value="">Sin sala</option>
                                ${availableRooms.map(room => `<option value="${room.id}" ${currentRoom && Number(currentRoom.id) === Number(room.id) ? 'selected' : ''}>${escapeHtml(roomDisplayName(room))} - ${escapeHtml(room.salon || 'Sin salon')}</option>`).join('')}
                            </select>
                        </td>
                        <td><input class="form-control form-control-sm" id="projectOrder${project.id}" type="number" min="1" value="${escapeHtml(currentOrder)}" style="width:90px"></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="saveProjectRoom(${project.id})"><i class="bi bi-save"></i></button>
                            ${currentRoom ? `<button class="btn btn-sm btn-outline-secondary" onclick="editRoom(${currentRoom.id}); setRoomView('rooms')"><i class="bi bi-door-open"></i></button>` : ''}
                        </td>
                    </tr>`;
            }).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">No hay proyectos con los filtros seleccionados.</td></tr>';
        }

        function renderTeacherRoomView() {
            const search = searchText();
            const visibleRooms = filteredRooms();
            const cards = teachers.map(teacher => {
                const evaluatorRooms = visibleRooms.filter(room => (room.teachers || []).some(item => String(item.id) === String(teacher.id)));
                const responsibleRooms = visibleRooms.filter(room => String(room.responsible_teacher_id || room.responsible_teacher?.id || '') === String(teacher.id));
                return { teacher, evaluatorRooms, responsibleRooms };
            }).filter(item => {
                if (!item.evaluatorRooms.length && !item.responsibleRooms.length) return false;
                if (!search) return true;
                return `${item.teacher.id} ${fullName(item.teacher)} ${(item.teacher.email || '')}`.toLowerCase().includes(search)
                    || item.evaluatorRooms.some(room => String(room.nombre || '').toLowerCase().includes(search));
            }).sort((a, b) => (b.evaluatorRooms.length + b.responsibleRooms.length) - (a.evaluatorRooms.length + a.responsibleRooms.length) || fullName(a.teacher).localeCompare(fullName(b.teacher)));

            document.getElementById('teacherRoomContainer').innerHTML = cards.map(item => `
                <div class="col-xl-6">
                    <div class="room-teacher-card h-100">
                        <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                            <div>
                                <strong>${escapeHtml(fullName(item.teacher))}</strong>
                                <div class="small text-muted">${escapeHtml(item.teacher.id)}${item.teacher.email ? ' · ' + escapeHtml(item.teacher.email) : ''}</div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary">${item.evaluatorRooms.length} como evaluador</span>
                                <span class="badge bg-success">${item.responsibleRooms.length} responsable</span>
                            </div>
                        </div>
                        <div class="small fw-semibold mb-1">Salas</div>
                        <div class="d-grid gap-2">
                            ${[...new Map([...item.evaluatorRooms, ...item.responsibleRooms].map(room => [room.id, room])).values()].map(room => `
                                <div class="room-meta-item">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                                        <strong>${escapeHtml(roomDisplayName(room))}</strong>
                                        <span class="badge bg-secondary">Semestre ${escapeHtml(room.semestre)}</span>
                                    </div>
                                    <div class="small text-muted">${escapeHtml(room.salon || '-')} · ${room.fecha_evaluacion ? new Date(room.fecha_evaluacion).toLocaleString('es-MX') : '-'}</div>
                                    <div class="small">${(room.projects || []).length} proyecto(s)</div>
                                </div>`).join('')}
                        </div>
                    </div>
                </div>`).join('') || '<div class="col-12"><div class="alert alert-info mb-0">No hay evaluadores con salas en los filtros actuales.</div></div>';
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
            const teacherRooms = new Map();
            conflicts.forEach(room => (room.teachers || []).forEach(teacher => teacherRooms.set(String(teacher.id), room)));
            return {
                projects: new Set(conflicts.flatMap(room => (room.projects || []).map(project => Number(project.id)))),
                teachers: teacherRooms,
                classrooms: new Map(conflicts.filter(room => room.salon_id).map(room => [String(room.salon_id), room])),
                rooms: conflicts
            };
        }

        // La API rechaza un salón o un docente con otra sala en el mismo horario: se bloquean aquí.
        function renderClassroomOptions(busy = busyRoomIds()) {
            const select = document.getElementById('roomClassroom');
            if (!select) return;
            const current = select.value;
            select.replaceChildren(new Option('Selecciona salón institucional', ''));
            classrooms.forEach(classroom => {
                const takenBy = busy.classrooms.get(String(classroom.id));
                const option = new Option(takenBy ? `${classroom.nombre} — ocupado por ${roomDisplayName(takenBy)}` : classroom.nombre, classroom.id);
                option.disabled = Boolean(takenBy) && String(classroom.id) !== current;
                select.add(option);
            });
            select.value = current;
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
            // La API exige un orden consecutivo (1..n): al quitar un proyecto se recorren los siguientes.
            Object.keys(normalized).sort((a, b) => normalized[a] - normalized[b]).forEach((projectId, index) => { normalized[projectId] = index + 1; });
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
                endInput.value = formatLocalDateTimeInput(new Date(startsAt.getTime() + Math.max(60, requiredRoomMinutes()) * 60000));
            }
            const selectedTeachers = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const { selected: selectedProjects, orderMap } = currentRoomProjectState();
            renderClassroomOptions();
            if (roomNameAuto && !document.getElementById('roomId').value) document.getElementById('roomName').value = suggestRoomName();
            renderRoomTeachers(selectedTeachers);
            loadRoomProjects(selectedProjects, orderMap);
        }

        function renderRoomTeachers(selected = []) {
            const selectedIds = selected.map(String);
            const busy = busyRoomIds();
            // Los ocupados en ese horario van al final para que primero se vean los disponibles.
            const orderedTeachers = [...teachers].sort((a, b) => Number(busy.teachers.has(String(a.id)) && !selectedIds.includes(String(a.id)))
                - Number(busy.teachers.has(String(b.id)) && !selectedIds.includes(String(b.id))));
            document.getElementById('roomTeachers').innerHTML = orderedTeachers.map(teacher => {
                const checked = selectedIds.includes(String(teacher.id));
                const takenBy = busy.teachers.get(String(teacher.id));
                return `
                <label class="room-option-card ${takenBy && !checked ? 'is-busy' : ''}" for="roomTeacher${escapeHtml(teacher.id)}">
                    <input class="form-check-input room-teacher mt-1" type="checkbox" value="${escapeHtml(teacher.id)}" id="roomTeacher${escapeHtml(teacher.id)}" ${checked ? 'checked' : ''} ${takenBy && !checked ? 'disabled' : ''} onchange="refreshResponsibleTeacherOptions()">
                    <span class="flex-grow-1">
                        <span class="fw-semibold d-block">${escapeHtml(fullName(teacher))}</span>
                        <span class="text-muted small">${escapeHtml(EVALUATOR_PROFILES[Number(teacher.perfil_id)] || 'Docente')}</span>
                        ${takenBy ? `<span class="${checked ? 'text-danger' : 'text-muted'} small d-block"><i class="bi bi-calendar-x"></i> Ocupado en ${escapeHtml(roomDisplayName(takenBy))}</span>` : ''}
                    </span>
                </label>`;
            }).join('') || '<p class="text-muted mb-0">No hay evaluadores activos.</p>';
            filterRoomOptions('roomTeachers', document.getElementById('roomTeacherSearch')?.value);
            refreshResponsibleTeacherOptions();
            renderAvailabilityHint(busy);
        }

        function refreshResponsibleTeacherOptions(selected = null) {
            const select = document.getElementById('responsibleTeacher');
            const current = selected ?? select?.value ?? '';
            const checked = [...document.querySelectorAll('.room-teacher:checked')].map(input => String(input.value));
            const available = teachers.filter(teacher => checked.includes(String(teacher.id)));
            select.innerHTML = '<option value="">Sin responsable</option>' + available.map(teacher => `
                <option value="${escapeHtml(teacher.id)}">${escapeHtml(fullName(teacher))}</option>
            `).join('');
            const validCurrent = available.some(teacher => String(teacher.id) === String(current));
            const firstChecked = checked.find(teacherId => available.some(teacher => String(teacher.id) === teacherId));
            select.value = validCurrent ? String(current) : (firstChecked || '');
            renderRoomSummary();
        }

        function loadRoomProjects(selected = [], orderMap = {}) {
            const semester = document.getElementById('roomSemester').value;
            const currentRoomId = Number(document.getElementById('roomId').value || 0);
            roomProjects = projects.filter(project => {
                const assignedRoomId = Number(project.assigned_room_id || 0);
                // Los proyectos ya programados en esta sala se conservan aunque sean casos especiales de otro semestre.
                if (currentRoomId && assignedRoomId === currentRoomId) return true;
                return String(projectSemester(project) || '') === String(semester) && !assignedRoomId;
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
                    <span class="room-order-badge ${normalizedOrderMap[project.id] ? '' : 'is-empty'}" title="Orden de presentación">${normalizedOrderMap[project.id] || '–'}</span>
                    <input class="room-project-order" data-project-id="${project.id}" type="hidden" value="${normalizedOrderMap[project.id] || ''}">
                    <label class="form-check-label flex-grow-1" for="roomProject${project.id}">
                        <span class="fw-semibold">${escapeHtml(project.title)}</span>${projectCategoryBadge(project)}
                        ${academicSemester(project) && academicSemester(project) !== Number(semester) ? `<span class="badge text-bg-warning ms-1">Caso especial: ${academicSemester(project)}° evaluado como ${semester}°</span>` : ''}
                        <span class="text-muted small d-block">${escapeHtml(projectActiveAuthors(project) || 'Sin integrantes registrados')}</span>
                        ${projectCompany(project) ? `<span class="text-muted small d-block">Empresa: ${escapeHtml(projectCompany(project))}</span>` : ''}
                    </label>
                    ${normalizedOrderMap[project.id] ? `<span class="room-order-arrows">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRoomProject(${project.id}, -1)" aria-label="Subir en el orden"><i class="bi bi-chevron-up"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRoomProject(${project.id}, 1)" aria-label="Bajar en el orden"><i class="bi bi-chevron-down"></i></button>
                    </span>` : ''}
                </div>`).join('') || '<p class="text-muted mb-0">No hay proyectos de este semestre libres en ese horario. Revisa los casos especiales si es un proyecto de otro semestre.</p>';
            filterRoomOptions('roomProjects', document.getElementById('roomProjectSearch')?.value);
            renderSpecialCases();
            renderAvailabilityHint(busy);
            renderTimeBudget();
        }

        // Proyectos de otro semestre (5°–9°) que pueden evaluarse con la etapa de esta sala.
        function renderSpecialCases(selected = currentSpecialCases()) {
            const container = document.getElementById('roomSpecialCases');
            if (!container) return;
            const semester = Number(document.getElementById('roomSemester').value);
            const currentRoomId = Number(document.getElementById('roomId').value || 0);
            const busy = busyRoomIds();
            const candidates = projects.filter(project => {
                const assignedRoomId = Number(project.assigned_room_id || 0);
                return projectSemester(project) && projectSemester(project) !== semester
                    && !assignedRoomId
                    && !busy.projects.has(Number(project.id));
            }).sort((a, b) => Math.abs(projectSemester(a) - semester) - Math.abs(projectSemester(b) - semester)
                || String(a.title || '').localeCompare(String(b.title || ''), 'es', { sensitivity: 'base' }));
            container.innerHTML = candidates.map(project => {
                const reason = selected[project.id];
                return `
                <div class="room-project-item d-flex align-items-start gap-2">
                    <input class="form-check-input room-special mt-2" type="checkbox" value="${project.id}" id="roomSpecial${project.id}" ${reason !== undefined ? 'checked' : ''} onchange="toggleSpecialCase(${project.id})">
                    <label class="form-check-label flex-grow-1" for="roomSpecial${project.id}">
                        <span class="fw-semibold">${escapeHtml(project.title)}</span>${projectCategoryBadge(project)}
                        <span class="badge text-bg-light border ms-1">${projectSemester(project)}° → se evalúa como ${semester}°</span>
                        <span class="text-muted small d-block">${escapeHtml(projectActiveAuthors(project) || 'Sin integrantes registrados')}</span>
                        <input class="form-control form-control-sm room-special-reason mt-1 ${reason !== undefined ? '' : 'd-none'}" data-project-id="${project.id}" maxlength="500"
                            placeholder="Motivo (mín. 10 caracteres), p. ej. adelantó materias de ${semester}°" value="${escapeHtml(reason || '')}" oninput="renderTimeBudget()">
                    </label>
                </div>`;
            }).join('') || '<p class="text-muted small mb-0">No hay proyectos de otros semestres disponibles en este horario.</p>';
            document.getElementById('roomSpecialCount').textContent = Object.keys(selected).filter(id => candidates.some(project => Number(project.id) === Number(id))).length;
            filterRoomOptions('roomSpecialCases', document.getElementById('roomSpecialSearch')?.value);
        }

        function currentSpecialCases() {
            const cases = {};
            document.querySelectorAll('.room-special:checked').forEach(input => {
                cases[input.value] = document.querySelector(`.room-special-reason[data-project-id="${input.value}"]`)?.value || '';
            });
            return cases;
        }

        function toggleSpecialCase(projectId) {
            const checked = document.getElementById(`roomSpecial${projectId}`).checked;
            const reason = document.querySelector(`.room-special-reason[data-project-id="${projectId}"]`);
            reason.classList.toggle('d-none', !checked);
            if (checked) {
                document.getElementById('roomSpecialCasesBox').open = true;
                reason.focus();
            }
            document.getElementById('roomSpecialCount').textContent = document.querySelectorAll('.room-special:checked').length;
            renderTimeBudget();
        }

        function renderAvailabilityHint(busy = busyRoomIds()) {
            const hint = document.getElementById('roomAvailabilityHint');
            if (!hint) return;
            if (!document.getElementById('roomDate').value || !document.getElementById('roomEndDate').value) {
                hint.textContent = 'Selecciona hora de inicio y fin para ver qué docentes, salones y proyectos están libres.';
                return;
            }
            const selectedStart = parseRoomDate(document.getElementById('roomDate').value);
            const selectedEnd = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!selectedStart || !selectedEnd || selectedEnd <= selectedStart) {
                hint.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> La hora de fin debe ser posterior a la hora de inicio.</span>';
                return;
            }
            if (!busy.rooms.length) {
                hint.textContent = 'Horario libre: no coincide con otras salas.';
                return;
            }
            const names = busy.rooms.map(roomDisplayName).join(', ');
            hint.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Coincide con ${escapeHtml(names)}. Sus docentes, salón y proyectos quedan bloqueados para esta sala.</span>`;
        }

        let roomStep = 1;
        let roomEndAuto = true;
        let roomFormModal = null;
        const ROOM_STAGES = { 5: 'Propuesta', 6: 'Avance', 7: 'Avance', 8: 'Titulación' };

        function focusRoomForm() {
            roomFormModal ??= new bootstrap.Modal(document.getElementById('roomFormModal'));
            const editing = Boolean(document.getElementById('roomId').value);
            document.getElementById('roomFormTitle').textContent = editing ? `Editar ${document.getElementById('roomName').value}` : 'Nueva sala';
            document.getElementById('roomFormSubtitle').textContent = editing
                ? 'El horario se cambia con el botón Horario; aquí ajustas proyectos, orden y evaluadores.'
                : 'Tres pasos: cuándo y dónde, proyectos y evaluadores.';
            renderRoomSemesterChips();
            goRoomStep(editing ? 2 : 1);
            roomFormModal.show();
        }

        function goRoomStep(step) {
            roomStep = Math.min(3, Math.max(1, step));
            document.querySelectorAll('.room-wizard-pane').forEach(pane => { pane.hidden = Number(pane.dataset.pane) !== roomStep; });
            document.querySelectorAll('.room-wizard-step').forEach(button => {
                const number = Number(button.dataset.step);
                button.classList.toggle('is-current', number === roomStep);
                button.classList.toggle('is-done', number < roomStep);
                button.setAttribute('aria-current', number === roomStep ? 'step' : 'false');
            });
            document.getElementById('roomPrevBtn').disabled = roomStep === 1;
            document.getElementById('roomNextBtn').hidden = roomStep === 3;
            renderRoomSummary();
        }

        // Botones de semestre construidos desde el select (ya filtrado por la regla par/impar del periodo).
        function renderRoomSemesterChips() {
            const select = document.getElementById('roomSemester');
            const container = document.getElementById('roomSemesterChips');
            if (!select || !container) return;
            const locked = Boolean(document.getElementById('roomId').value);
            container.innerHTML = [...select.options].map(option => `
                <button type="button" class="room-chip" role="radio" aria-checked="${option.value === select.value}" ${locked && option.value !== select.value ? 'disabled' : ''}
                    onclick="chooseRoomSemester('${option.value}')">${option.value}°<small>${ROOM_STAGES[option.value] || 'Etapa'}</small></button>`).join('');
        }

        function chooseRoomSemester(value) {
            const select = document.getElementById('roomSemester');
            if (select.value === value) return;
            select.value = value;
            onRoomSemesterChange();
        }

        function onRoomSemesterChange() {
            renderRoomSemesterChips();
            loadRoomProjects();
        }

        // La API sólo impide repetir nombre en el mismo horario: se sugiere el número libre más bajo de ese horario.
        let roomNameAuto = true;
        function suggestRoomName() {
            const used = new Set(conflictingRoomsForCurrentForm().map(room => Number(String(room.nombre || '').match(/(\d+)/)?.[1] || 0)));
            let number = 1;
            while (used.has(number)) number++;
            return `SALA ${number}`;
        }

        // Mientras no se edite a mano, el fin se ajusta al tiempo que necesitan los proyectos marcados.
        function autoFitRoomEnd() {
            const end = document.getElementById('roomEndDate');
            const start = parseRoomDate(document.getElementById('roomDate').value);
            if (!roomEndAuto || end.readOnly || !start) return;
            const value = formatLocalDateTimeInput(new Date(start.getTime() + Math.max(30, requiredRoomMinutes()) * 60000));
            if (end.value === value) return;
            end.value = value;
            const selectedTeachers = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            renderClassroomOptions();
            renderRoomTeachers(selectedTeachers);
        }

        function moveRoomProject(projectId, delta) {
            const { selected, orderMap } = currentRoomProjectState();
            const ordered = selected.sort((a, b) => (orderMap[a] || 0) - (orderMap[b] || 0));
            const index = ordered.indexOf(Number(projectId));
            const target = index + delta;
            if (index < 0 || target < 0 || target >= ordered.length) return;
            [ordered[index], ordered[target]] = [ordered[target], ordered[index]];
            loadRoomProjects(ordered, Object.fromEntries(ordered.map((id, position) => [id, position + 1])));
        }

        function selectAllRoomProjects(select) {
            if (!select) return loadRoomProjects([], {});
            const { selected, orderMap } = currentRoomProjectState();
            const visible = [...document.querySelectorAll('#roomProjects .room-project-item:not(.d-none) .room-project')].map(input => Number(input.value));
            loadRoomProjects([...new Set([...selected, ...visible])], orderMap);
        }

        function formatRoomRange() {
            const start = parseRoomDate(document.getElementById('roomDate').value);
            const end = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!start) return null;
            const day = start.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric', month: 'short' });
            const time = date => date.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
            return `${day} · ${time(start)}${end ? `–${time(end)}` : ''}`;
        }

        function renderRoomSummary() {
            const list = document.getElementById('roomSummaryList');
            if (!list) return;
            const semester = document.getElementById('roomSemester').value;
            const classroom = document.getElementById('roomClassroom');
            const regular = document.querySelectorAll('.room-project:checked').length;
            const special = document.querySelectorAll('.room-special:checked').length;
            const teacherCount = document.querySelectorAll('.room-teacher:checked').length;
            const responsible = document.getElementById('responsibleTeacher');
            const range = formatRoomRange();
            const presentation = Number(document.getElementById('presentationMinutes').value || 0);
            const evaluation = Number(document.getElementById('teacherMinutes').value || 0);
            const attempts = Number(document.getElementById('maxAttempts').value || 1);
            document.getElementById('roomTimesSummary').textContent = `${presentation} min de presentación + ${evaluation} de evaluación · ${attempts} oportunidad${attempts === 1 ? '' : 'es'}`;
            document.getElementById('roomStepProjectsCount').textContent = regular + special ? `(${regular + special})` : '';
            document.getElementById('roomStepTeachersCount').textContent = teacherCount ? `(${teacherCount})` : '';
            const items = [
                [Boolean(document.getElementById('roomName').value.trim()), 'Sala', document.getElementById('roomName').value.trim() || 'Sin nombre'],
                [Boolean(semester), 'Semestre', semester ? `${semester}° · ${ROOM_STAGES[semester] || ''}` : 'Elige el semestre'],
                [Boolean(range), 'Horario', range || 'Elige fecha y hora'],
                [Boolean(classroom.value), 'Salón', classroom.value ? classroom.selectedOptions[0].textContent : 'Elige un salón'],
                [regular + special > 0, 'Proyectos', regular + special ? `${regular + special} proyecto(s)${special ? `, ${special} caso(s) especial(es)` : ''}` : 'Marca al menos uno'],
                [teacherCount > 0, 'Evaluadores', teacherCount ? `${teacherCount} docente(s)` : 'Marca al menos uno'],
                [Boolean(responsible.value), 'Responsable', responsible.value ? responsible.selectedOptions[0].textContent.trim() : 'Elige entre los evaluadores']
            ];
            list.innerHTML = items.map(([ok, label, value]) => `
                <li class="${ok ? 'is-ok' : 'is-missing'}"><i class="bi ${ok ? 'bi-check-circle-fill' : 'bi-circle'}"></i><small>${label}</small><span>${escapeHtml(value)}</span></li>`).join('');
        }

        async function startNewRoom() {
            if (document.getElementById('roomStatusFilter').value === 'archived') {
                document.getElementById('roomStatusFilter').value = 'active';
                await reloadRooms();
            }
            resetRoomForm();
            focusRoomForm();
        }

        async function editRoom(id) {
            const room = rooms.find(item => Number(item.id) === Number(id));
            if (!room) return;
            document.getElementById('roomId').value = room.id;
            document.getElementById('roomName').value = room.nombre || '';
            document.getElementById('roomClassroom').value = room.salon_id || '';
            document.getElementById('roomSemester').value = room.semestre;
            document.getElementById('roomDate').value = toDateTimeInput(room.fecha_evaluacion);
            const legacyEnd = room.fecha_evaluacion
                ? formatLocalDateTimeInput(new Date(parseRoomDate(room.fecha_evaluacion).getTime() + 60 * 60 * 1000))
                : '';
            document.getElementById('roomEndDate').value = room.fecha_fin_evaluacion ? toDateTimeInput(room.fecha_fin_evaluacion) : legacyEnd;
            setRoomScheduleLocked(true);
            roomEndAuto = false;
            roomNameAuto = false;
            document.getElementById('teacherMinutes').value = room.teacher_evaluation_minutes || 15;
            document.getElementById('presentationMinutes').value = room.project_presentation_minutes || 20;
            document.getElementById('maxAttempts').value = room.max_attempts || 1;
            renderRoomTeachers((room.teachers || []).map(teacher => teacher.id));
            refreshResponsibleTeacherOptions(room.responsible_teacher_id || room.responsible_teacher?.id || '');
            const selectedProjects = orderedRoomProjects(room);
            const orderMap = Object.fromEntries(selectedProjects.map(project => [project.id, project.presentation_order || project.pivot?.presentation_order || 0]));
            loadRoomProjects(selectedProjects.map(project => project.id), orderMap);
            focusRoomForm();
        }

        // Al editar, el horario se cambia con el botón Horario (la API protege el horario de una sala existente).
        function setRoomScheduleLocked(locked) {
            ['roomDate', 'roomEndDate'].forEach(id => document.getElementById(id).readOnly = locked);
            document.getElementById('roomDateHint').innerHTML = locked
                ? 'Para cambiar el horario usa el botón <i class="bi bi-clock"></i> Horario de la sala.'
                : 'Debe ser posterior a la fecha actual.';
        }

        function resetRoomForm() {
            setRoomScheduleLocked(false);
            ['roomId', 'roomName', 'roomClassroom', 'roomDate', 'roomEndDate', 'roomTeacherSearch', 'roomProjectSearch', 'roomSpecialSearch'].forEach(id => {
                const field = document.getElementById(id);
                if (field) field.value = '';
            });
            document.getElementById('roomSpecialCasesBox').open = false;
            document.getElementById('roomSpecialCases').innerHTML = '';
            document.getElementById('responsibleTeacher').innerHTML = '<option value="">Selecciona primero docentes</option>';
            document.getElementById('teacherMinutes').value = 15;
            document.getElementById('presentationMinutes').value = 20;
            document.getElementById('maxAttempts').value = 1;
            document.getElementById('roomDate').min = nextMinuteInput();
            roomNameAuto = true;
            document.getElementById('roomName').value = suggestRoomName();
            roomEndAuto = true;
            renderRoomTeachers();
            loadRoomProjects();
        }

        function roomPayloadFromRoom(room, projectsOverride = null) {
            const orderedProjects = projectsOverride || orderedRoomProjects(room);
            return {
                nombre: room.nombre,
                salon: room.salon || null,
                salon_id: room.salon_id,
                semestre: room.semestre,
                responsible_teacher_id: room.responsible_teacher_id || room.responsible_teacher?.id || null,
                fecha_evaluacion: room.fecha_evaluacion,
                fecha_fin_evaluacion: room.fecha_fin_evaluacion,
                teacher_evaluation_minutes: Number(room.teacher_evaluation_minutes || 15),
                project_presentation_minutes: Number(room.project_presentation_minutes || 20),
                max_attempts: Number(room.max_attempts || 1),
                teacher_ids: (room.teachers || []).map(teacher => teacher.id),
                project_ids: orderedProjects.map(project => Number(project.id)),
                project_order: Object.fromEntries(orderedProjects.map((project, index) => [project.id, Number(project.presentation_order || project.pivot?.presentation_order || index + 1)]))
            };
        }

        async function saveRoom() {
            const id = document.getElementById('roomId').value;
            const teacherIds = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const specialCases = Object.entries(currentSpecialCases()).map(([projectId, reason]) => ({ project_id: Number(projectId), reason: reason.trim() }));
            const projectIds = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value))
                .concat(specialCases.map(item => item.project_id));
            const startsAt = parseRoomDate(document.getElementById('roomDate').value);
            const endsAt = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!startsAt || !endsAt || endsAt <= startsAt) {
                goRoomStep(1);
                showAlert('#alertContainer', 'warning', 'La hora de fin de la sala debe ser posterior a la hora de inicio.');
                return;
            }
            if (specialCases.some(item => item.reason.length < 10)) {
                goRoomStep(2);
                showAlert('#alertContainer', 'warning', 'Escribe el motivo de cada caso especial (mínimo 10 caracteres).');
                document.getElementById('roomSpecialCasesBox').open = true;
                return;
            }
            const projectOrder = {};
            document.querySelectorAll('.room-project-order').forEach(input => {
                if (projectIds.includes(Number(input.dataset.projectId))) projectOrder[input.dataset.projectId] = Number(input.value || 0);
            });
            // Los casos especiales pasan al final, en el orden en que se marcaron.
            let nextOrder = Math.max(0, ...Object.values(projectOrder)) + 1;
            specialCases.forEach(item => { projectOrder[item.project_id] = nextOrder++; });
            const availableMinutes = Math.round((endsAt - startsAt) / 60000);
            if (!id && availableMinutes < requiredRoomMinutes(projectIds.length)) {
                showAlert('#alertContainer', 'warning', `El horario no alcanza: se necesitan ${requiredRoomMinutes(projectIds.length)} min para ${projectIds.length} proyecto(s) y hay ${availableMinutes} min. Usa "Ajustar fin" o reduce los tiempos.`);
                return;
            }
            const selectedOrders = Object.values(projectOrder).filter(order => order > 0);
            if (selectedOrders.some((order, index) => selectedOrders.indexOf(order) !== index)) {
                showAlert('#alertContainer', 'danger', 'No repitas el orden de presentación entre proyectos.');
                return;
            }
            const payload = {
                nombre: normalizeRoomName(document.getElementById('roomName').value),
                salon_id: Number(document.getElementById('roomClassroom').value),
                semestre: document.getElementById('roomSemester').value,
                responsible_teacher_id: document.getElementById('responsibleTeacher').value || null,
                fecha_evaluacion: document.getElementById('roomDate').value,
                fecha_fin_evaluacion: document.getElementById('roomEndDate').value,
                teacher_evaluation_minutes: Number(document.getElementById('teacherMinutes').value),
                project_presentation_minutes: Number(document.getElementById('presentationMinutes').value),
                max_attempts: Number(document.getElementById('maxAttempts').value),
                teacher_ids: teacherIds,
                project_ids: projectIds,
                project_order: projectOrder,
                special_cases: specialCases
            };
            if (!payload.salon_id) {
                goRoomStep(1);
                showAlert('#alertContainer', 'warning', 'Elige el salón institucional de la sala.');
                return;
            }
            if (!payload.nombre || !payload.fecha_evaluacion) {
                goRoomStep(1);
                showAlert('#alertContainer', 'danger', 'Indica el nombre de la sala y la fecha de evaluación.');
                return;
            }
            if (!id && new Date(payload.fecha_evaluacion) < new Date()) {
                showAlert('#alertContainer', 'danger', 'La fecha de la sala debe ser posterior al momento actual.');
                return;
            }
            if (!teacherIds.length || (!id && !projectIds.length)) {
                goRoomStep(!id && !projectIds.length ? 2 : 3);
                showAlert('#alertContainer', 'danger', id
                    ? 'Selecciona al menos un docente para la sala.'
                    : 'Selecciona al menos un docente y un proyecto para la sala.');
                return;
            }
            if (!payload.responsible_teacher_id) {
                goRoomStep(3);
                showAlert('#alertContainer', 'danger', 'Selecciona como encargado a uno de los docentes activos de la sala.');
                return;
            }
            try {
                const response = id
                    ? await api.put(`/evaluations/rooms/${id}`, payload)
                    : await api.post('/evaluations/rooms', payload);
                replaceRoomLocal(response.room);
                if (specialCases.length) await loadProjects();
                renderCurrentView();
                roomFormModal?.hide();
                resetRoomForm();
                swalToast('success', specialCases.length ? `Sala guardada con ${specialCases.length} caso(s) especial(es)` : 'Sala guardada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando sala');
            }
        }

        async function saveProjectRoom(projectId) {
            const targetRoomId = document.getElementById(`projectRoom${projectId}`).value;
            const order = Number(document.getElementById(`projectOrder${projectId}`).value || 0);
            const currentRoom = roomForProject(projectId);
            const targetRoom = targetRoomId ? rooms.find(room => Number(room.id) === Number(targetRoomId)) : null;
            const project = projects.find(item => Number(item.id) === Number(projectId));
            if (!project) return;
            try {
                if (currentRoom && (!targetRoom || Number(currentRoom.id) !== Number(targetRoom.id))) {
                    const nextProjects = orderedRoomProjects(currentRoom).filter(item => Number(item.id) !== Number(projectId));
                    const response = await api.put(`/evaluations/rooms/${currentRoom.id}`, roomPayloadFromRoom(currentRoom, nextProjects));
                    replaceRoomLocal(response.room);
                }
                if (targetRoom) {
                    const existing = orderedRoomProjects(targetRoom).filter(item => Number(item.id) !== Number(projectId));
                    existing.push({ ...project, presentation_order: order || existing.length + 1 });
                    const response = await api.put(`/evaluations/rooms/${targetRoom.id}`, roomPayloadFromRoom(targetRoom, existing));
                    replaceRoomLocal(response.room);
                }
                renderCurrentView();
                swalToast('success', 'Asignacion de proyecto actualizada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo actualizar la asignacion del proyecto.');
            }
        }

        async function deleteRoom(id) {
            if (!await confirmAction({ title: 'Eliminar sala', text: 'Se eliminarán también evaluaciones, puntajes e intentos vinculados.', confirmButtonText: 'Sí, eliminar' })) return;
            try {
                const response = await api.delete(`/evaluations/rooms/${id}`);
                rooms = rooms.filter(room => Number(room.id) !== Number(id));
                syncProjectRoomAssignments();
                renderCurrentView();
                swalToast('success', response.message || 'Sala eliminada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo eliminar la sala');
            }
        }

        async function archiveRoom(id) {
            if (!await confirmAction({ title: 'Archivar sala', text: 'Todas las evaluaciones de esta sala dejarán la vista principal.', confirmButtonText: 'Sí, archivar sala' })) return;
            await api.post(`/evaluations/rooms/${id}/archive`, {});
            rooms = rooms.filter(room => Number(room.id) !== Number(id));
            syncProjectRoomAssignments();
            renderCurrentView();
            swalToast('success', 'Sala archivada');
        }

        async function unarchiveRoom(id) {
            if (!await confirmAction({ title: 'Restaurar sala', text: 'Todas las evaluaciones de esta sala volverán a la vista principal.', confirmButtonText: 'Sí, restaurar sala' })) return;
            await api.post(`/evaluations/rooms/${id}/unarchive`, {});
            rooms = rooms.filter(room => Number(room.id) !== Number(id));
            syncProjectRoomAssignments();
            renderCurrentView();
            swalToast('success', 'Sala restaurada');
        }

        async function lockRoomSequence(id) {
            if (!await confirmAction({ title: 'Bloquear orden de paso', text: 'Despues de bloquear, solo el proyecto en turno podra evaluarse.', confirmButtonText: 'Si, bloquear' })) return;
            const response = await api.post(`/evaluations/rooms/${id}/lock-sequence`, {});
            replaceRoomLocal(response.room);
            renderCurrentView();
        }

        async function advanceRoom(id) {
            if (!await confirmAction({ title: 'Avanzar turno', text: 'Se finalizara el proyecto actual y se activara el siguiente.', confirmButtonText: 'Si, avanzar' })) return;
            try {
                const room = rooms.find(item => Number(item.id) === Number(id));
                const response = await api.post(`/evaluations/rooms/${id}/advance`, {
                    continue_next: true,
                    expected_sequence_version: room?.sequence_version ?? null
                });
                replaceRoomLocal(response.room);
                renderCurrentView();
                swalToast('success', response.message || 'Turno actualizado');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo avanzar el turno');
            }
        }

        async function controlRoomTimer(id, action) {
            const room = rooms.find(item => Number(item.id) === Number(id));
            if (!room) return;
            const minutes = Number(document.getElementById(`timerMinutes${id}`)?.value || room.project_presentation_minutes || 20);
            try {
                const response = await api.post(`/evaluations/rooms/${id}/timer`, {
                    action,
                    expected_sequence_version: room.sequence_version,
                    ...(['start', 'reset'].includes(action) ? { duration_seconds: Math.max(1, minutes) * 60 } : {})
                });
                replaceRoomLocal(response.room);
                renderCurrentView();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo actualizar el temporizador');
                await reloadRooms();
            }
        }

        function openRoomSchedule(id) {
            const room = rooms.find(item => Number(item.id) === Number(id));
            if (!room) return;
            document.getElementById('scheduleRoomId').value = room.id;
            document.getElementById('scheduleStart').value = room.fecha_evaluacion?.slice(0, 16) || '';
            document.getElementById('scheduleStart').disabled = room.evaluation_window?.status !== 'scheduled';
            document.getElementById('scheduleEnd').value = room.fecha_fin_evaluacion?.slice(0, 16) || '';
            document.getElementById('scheduleAllowLate').checked = Boolean(room.allow_late_evaluations);
            document.getElementById('scheduleLateUntil').value = room.late_evaluation_until?.slice(0, 16) || '';
            document.getElementById('scheduleLateReason').value = room.late_evaluation_reason || '';
            toggleLateScheduleFields();
            roomScheduleModal.show();
        }

        function toggleLateScheduleFields() {
            const enabled = document.getElementById('scheduleAllowLate').checked;
            document.getElementById('scheduleLateUntilBox').classList.toggle('d-none', !enabled);
            document.getElementById('scheduleLateReasonBox').classList.toggle('d-none', !enabled);
            document.getElementById('scheduleLateUntil').required = enabled;
            document.getElementById('scheduleLateReason').required = enabled;
        }

        document.getElementById('roomScheduleForm').addEventListener('submit', async event => {
            event.preventDefault();
            const id = Number(document.getElementById('scheduleRoomId').value);
            const room = rooms.find(item => Number(item.id) === id);
            if (!room) return;
            const start = document.getElementById('scheduleStart').value;
            const end = document.getElementById('scheduleEnd').value;
            const allowsLate = document.getElementById('scheduleAllowLate').checked;
            const lateUntil = document.getElementById('scheduleLateUntil').value;
            if (new Date(end) <= new Date(start)) {
                showAlert('#alertContainer', 'warning', 'La hora de fin debe ser posterior al inicio.');
                return;
            }
            if (allowsLate && new Date(lateUntil) <= new Date(end)) {
                showAlert('#alertContainer', 'warning', 'La autorización fuera de horario debe terminar después del horario ordinario.');
                return;
            }
            try {
                const response = await api.put(`/evaluations/rooms/${id}/schedule`, {
                    fecha_evaluacion: start,
                    fecha_fin_evaluacion: end,
                    allow_late_evaluations: allowsLate,
                    late_evaluation_until: allowsLate ? lateUntil : null,
                    late_evaluation_reason: allowsLate ? document.getElementById('scheduleLateReason').value.trim() : null,
                    expected_sequence_version: room.sequence_version
                });
                replaceRoomLocal(response.room);
                renderCurrentView();
                roomScheduleModal.hide();
                swalToast('success', response.message || 'Horario actualizado');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo actualizar el horario');
                await reloadRooms();
            }
        });

        function replaceRoomLocal(updatedRoom) {
            if (!updatedRoom) return;
            const index = rooms.findIndex(room => Number(room.id) === Number(updatedRoom.id));
            if (index >= 0) rooms[index] = updatedRoom;
            else rooms.unshift(updatedRoom);
            syncProjectRoomAssignments();
        }

        function syncProjectRoomAssignments() {
            const assignments = new Map();
            rooms.forEach(room => {
                (room.projects || room.proyectos || []).forEach(project => assignments.set(String(project.id), room.id));
            });
            projects = projects.map(project => ({
                ...project,
                assigned_room_id: assignments.get(String(project.id)) || null
            }));
        }

        async function downloadRoomReport(id) {
            await downloadPdf(`/evaluations/rooms/${id}/report.pdf`, `reporte_sala_${id}.pdf`, 'No se pudo generar el reporte PDF de la sala.');
        }

        async function downloadRoomTeacherReport(id) {
            await downloadPdf(`/evaluations/rooms/${id}/report.pdf?audience=teachers`, `reporte_sala_${id}_limitado.pdf`, 'No se pudo generar el reporte PDF para docentes.');
        }

        async function downloadPdf(endpoint, filename, fallbackMessage) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    headers: { Authorization: `Bearer ${auth.getToken()}` },
                    credentials: 'include'
                });
                if (!response.ok) throw new Error(fallbackMessage);
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                link.click();
                URL.revokeObjectURL(url);
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || fallbackMessage);
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            document.getElementById('roomSemester')?.addEventListener('sgpi:semesters-ready', renderRoomSemesterChips);
            try {
                await loadInitialData();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo cargar la gestión de salas.');
            }
            window.setInterval(tickRoomTimers, 1000);
            let roomsReloadInProgress = false;
            window.setInterval(async () => {
                // Evita solapar consultas y no consulta con la pestaña oculta.
                if (document.hidden || roomsReloadInProgress) return;
                roomsReloadInProgress = true;
                try {
                    await reloadRooms();
                } catch (error) {
                    // El siguiente ciclo reintenta.
                } finally {
                    roomsReloadInProgress = false;
                }
            }, 5000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !roomsReloadInProgress) reloadRooms().catch(() => {});
            });
        });
    </script>
</body>
</html>
