/**
 * Temporizador de turno por fases (igual que src/components/RoomCountdown.jsx de la app): exposición y luego preguntas
 * y respuestas. Al terminar una fase suena un aviso y se pone en rojo; si la sala pasa sola a preguntas, cuenta las
 * preguntas tras `questions_delay_seconds`, como la API (App\Support\RoomTimeline::syncPhase).
 */
(function () {
    const PHASES = { exposicion: 'Exposición', preguntas: 'Preguntas y respuestas' };
    const clock = seconds => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const beeped = new Set();

    function beep() {
        try {
            const audio = new (window.AudioContext || window.webkitAudioContext)();
            [0, 0.35, 0.7].forEach(offset => {
                const tone = audio.createOscillator();
                const gain = audio.createGain();
                tone.frequency.value = 880;
                gain.gain.setValueAtTime(0.25, audio.currentTime + offset);
                gain.gain.exponentialRampToValueAtTime(0.001, audio.currentTime + offset + 0.25);
                tone.connect(gain).connect(audio.destination);
                tone.start(audio.currentTime + offset);
                tone.stop(audio.currentTime + offset + 0.25);
            });
            navigator.vibrate?.([200, 100, 200]);
        } catch (error) { /* sin sonido */ }
    }

    function state(timer, elapsed) {
        const running = timer.status === 'en_curso';
        let phase = timer.phase || 'exposicion';
        let remaining = Math.max(0, Number(timer.remaining_seconds || 0) - (running ? elapsed : 0));
        let expired = remaining === 0 && (running || timer.status === 'finalizado');
        if (running && phase === 'exposicion' && timer.auto_questions && remaining === 0) {
            const delay = Number(timer.questions_delay_seconds || 5);
            const overtime = elapsed - Number(timer.remaining_seconds || 0);
            if (overtime >= delay) {
                phase = 'preguntas';
                remaining = Math.max(0, Number(timer.questions_seconds || 0) - (overtime - delay));
                expired = remaining === 0;
            }
        }
        const label = expired ? 'Tiempo terminado' : timer.status === 'pausado' ? 'En pausa' : running ? PHASES[phase] : 'Sin iniciar';
        return { phase, remaining, expired, running: running && !expired, label };
    }

    window.RoomCountdown = {
        markup(timer, title, controls = '') {
            return `<div class="room-live-timer" data-room-countdown='${escape(JSON.stringify(timer || {}))}' data-synced-at="${Date.now()}">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="d-flex flex-column lh-sm"><small class="text-muted">${escape(title)}</small><strong data-countdown-label>Sin iniciar</strong></span>
                    <span class="room-live-timer-display" data-countdown-display>00:00</span>
                </div>${controls}</div>`;
        },
        tick() {
            document.querySelectorAll('[data-room-countdown]').forEach(box => {
                let timer = {};
                try { timer = JSON.parse(box.dataset.roomCountdown); } catch (error) { return; }
                const elapsed = Math.floor((Date.now() - Number(box.dataset.syncedAt || Date.now())) / 1000);
                const now = state(timer, elapsed);
                box.querySelector('[data-countdown-display]').textContent = clock(now.remaining);
                box.querySelector('[data-countdown-label]').textContent = now.label;
                box.classList.toggle('running', now.running);
                box.classList.toggle('expired', now.expired);
                box.classList.toggle('phase-preguntas', now.phase === 'preguntas');
                const key = `${timer.order}:${now.phase}:${timer.started_at}`;
                if (now.expired && timer.status === 'en_curso' && !beeped.has(key)) { beeped.add(key); beep(); }
            });
        }
    };
    window.setInterval(() => window.RoomCountdown.tick(), 1000);
})();
