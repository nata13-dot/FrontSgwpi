// Recuperación de contraseña: una sola ventana con tres pasos (datos, código y nueva contraseña).
(() => {
    const RESEND_SECONDS = 60;
    const MIN_PASSWORD = 8;
    const element = id => document.getElementById(id);
    const state = { id: '', email: '', token: '', step: 0, timer: null };

    const modal = () => bootstrap.Modal.getOrCreateInstance(element('passwordRecoveryModal'));
    const forms = () => [element('recoveryDataForm'), element('recoveryCodeForm'), element('recoveryPasswordForm')];

    function showMessage(type, text) {
        const box = element('recoveryMessage');
        box.innerHTML = '';
        if (!text) return;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} py-2 mb-3`;
        alert.setAttribute('role', 'alert');
        alert.textContent = text;
        box.append(alert);
    }

    function goTo(step) {
        state.step = step;
        forms().forEach((form, index) => { form.hidden = index !== step; });
        document.querySelectorAll('#recoverySteps li').forEach((item, index) => {
            item.classList.toggle('current', index === step);
            item.classList.toggle('done', index < step);
            if (index === step) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        forms()[step].querySelector('input')?.focus();
    }

    /** l2224••••22@smartin… : confirma a dónde llegó sin mostrar la dirección completa. */
    function maskEmail(email) {
        const [local, domain] = email.split('@');
        if (!domain) return email;
        return `${local.slice(0, 2)}•••${local.length > 4 ? local.slice(-2) : ''}@${domain}`;
    }

    function startResendTimer() {
        const button = element('recoveryResend');
        let left = RESEND_SECONDS;
        clearInterval(state.timer);
        const paint = () => {
            button.disabled = left > 0;
            button.textContent = left > 0 ? `Reenviar código en ${left} s` : 'Reenviar código';
        };
        paint();
        state.timer = setInterval(() => {
            left -= 1;
            paint();
            if (left <= 0) clearInterval(state.timer);
        }, 1000);
    }

    async function withLoading(button, text, action) {
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${text}`;
        try {
            return await action();
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    function sendCode() {
        return api.post('/auth/password/request-token', { id: state.id, email: state.email }, { _timeout: 20000 });
    }

    function paintRequirements() {
        const password = element('recoveryPassword').value;
        const confirmation = element('recoveryPasswordConfirm').value;
        const rules = { recoveryRuleLength: password.length >= MIN_PASSWORD, recoveryRuleMatch: password !== '' && password === confirmation };
        Object.entries(rules).forEach(([id, met]) => {
            const rule = element(id);
            rule.classList.toggle('met', met);
            rule.querySelector('i').className = `bi ${met ? 'bi-check-circle-fill' : 'bi-circle'}`;
        });
        return Object.values(rules).every(Boolean);
    }

    window.openPasswordRecoveryModal = function () {
        bootstrap.Modal.getInstance(element('loginModal'))?.hide();
        clearInterval(state.timer);
        Object.assign(state, { id: '', email: '', token: '' });
        forms().forEach(form => { form.reset(); form.classList.remove('was-validated'); });
        showMessage();
        paintRequirements();
        modal().show();
        goTo(0);
    };

    document.addEventListener('DOMContentLoaded', () => {
        element('passwordRecoveryModal').addEventListener('shown.bs.modal', () => forms()[state.step].querySelector('input')?.focus());
        element('passwordRecoveryModal').addEventListener('hidden.bs.modal', () => clearInterval(state.timer));

        element('recoveryDataForm').addEventListener('submit', async event => {
            event.preventDefault();
            showMessage();
            if (!event.target.checkValidity()) return event.target.classList.add('was-validated');
            state.id = element('recoveryUserId').value.trim();
            state.email = element('recoveryEmail').value.trim();
            try {
                await withLoading(element('recoverySendBtn'), 'Enviando...', sendCode);
                element('recoveryEmailShown').textContent = maskEmail(state.email);
                element('recoveryCodeForm').reset();
                goTo(1);
                startResendTimer();
            } catch (error) {
                showMessage('danger', error.message || 'No pudimos enviar el código. Intenta de nuevo.');
            }
        });

        element('recoveryCode').addEventListener('input', event => {
            event.target.value = event.target.value.replace(/\D/g, '').slice(0, 6);
            if (event.target.value.length === 6) element('recoveryCodeForm').requestSubmit();
        });

        element('recoveryCodeForm').addEventListener('submit', async event => {
            event.preventDefault();
            if (element('recoveryVerifyBtn').disabled) return;
            showMessage();
            if (!event.target.checkValidity()) return event.target.classList.add('was-validated');
            state.token = element('recoveryCode').value;
            try {
                await withLoading(element('recoveryVerifyBtn'), 'Verificando...', () => api.post('/auth/password/verify-token', { id: state.id, email: state.email, token: state.token }));
                clearInterval(state.timer);
                goTo(2);
            } catch (error) {
                element('recoveryCode').value = '';
                element('recoveryCode').focus();
                showMessage('danger', error.message || 'El código no es válido o ya venció.');
            }
        });

        element('recoveryResend').addEventListener('click', async () => {
            showMessage();
            element('recoveryResend').disabled = true;
            try {
                await sendCode();
                element('recoveryCode').value = '';
                showMessage('success', 'Te enviamos un código nuevo. El anterior ya no sirve.');
                startResendTimer();
            } catch (error) {
                element('recoveryResend').disabled = false;
                showMessage('danger', error.message || 'No pudimos reenviar el código.');
            }
        });

        element('recoveryChangeData').addEventListener('click', () => {
            clearInterval(state.timer);
            showMessage();
            goTo(0);
        });

        ['recoveryPassword', 'recoveryPasswordConfirm'].forEach(id => element(id).addEventListener('input', paintRequirements));

        document.querySelectorAll('[data-recovery-toggle]').forEach(button => button.addEventListener('click', () => {
            const input = element(button.dataset.recoveryToggle);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            button.querySelector('i').className = `bi ${show ? 'bi-eye-slash' : 'bi-eye'}`;
        }));

        element('recoveryBackToLogin').addEventListener('click', () => {
            modal().hide();
            bootstrap.Modal.getOrCreateInstance(element('loginModal')).show();
        });

        element('recoveryPasswordForm').addEventListener('submit', async event => {
            event.preventDefault();
            showMessage();
            if (!paintRequirements()) return showMessage('warning', 'Revisa los requisitos de la contraseña.');
            const password = element('recoveryPassword').value;
            try {
                const response = await withLoading(element('recoverySaveBtn'), 'Guardando...', () => api.post('/auth/password/reset', {
                    id: state.id, email: state.email, token: state.token, password, password_confirmation: password
                }));
                auth.token = response.access_token;
                auth.user = response.user;
                auth.remember = false;
                await auth.persistSession();
                await Swal.fire({ icon: 'success', title: 'Contraseña actualizada', text: 'Entrando al sistema...', timer: 900, showConfirmButton: false });
                window.location.replace(dashboardUrlForCurrentUser());
            } catch (error) {
                showMessage('danger', error.message || 'No se pudo cambiar la contraseña.');
            }
        });
    });
})();
