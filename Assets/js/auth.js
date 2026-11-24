document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        if (!input) return;

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.textContent = showPassword ? 'Hide' : 'Show';
        button.setAttribute('aria-label', `${showPassword ? 'Hide' : 'Show'} password`);
        button.setAttribute('aria-pressed', String(showPassword));
    });
});

const registerForm = document.getElementById('register-form');

if (registerForm) {
    const fields = {
        name: document.getElementById('full-name'),
        email: document.getElementById('email'),
        phone: document.getElementById('phone'),
        password: document.getElementById('password'),
        confirm: document.getElementById('confirm-password'),
        terms: document.getElementById('terms'),
    };

    registerForm.addEventListener('submit', (event) => {
        const errors = {
            'full-name': fields.name.value.trim().length < 2 ? 'Please enter your full name.' : '',
            email: !fields.email.validity.valid ? 'Please enter a valid email address.' : '',
            phone: !/^\+?[0-9]{9,15}$/.test(fields.phone.value.replace(/[\s-]/g, ''))
                ? 'Please enter a valid phone number.' : '',
            password: !/^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/.test(fields.password.value)
                ? 'Use at least 8 characters, including letters and numbers.' : '',
            'confirm-password': fields.confirm.value !== fields.password.value
                ? 'Your passwords do not match.' : '',
            terms: !fields.terms.checked ? 'Please accept the Terms & Conditions and Privacy Policy.' : '',
        };

        let valid = true;
        Object.entries(errors).forEach(([id, message]) => {
            const input = id === 'terms' ? fields.terms : document.getElementById(id);
            const output = document.querySelector(`[data-error-for="${id}"]`);
            output.textContent = message;
            input.classList.toggle('auth-invalid', Boolean(message));
            if (message) valid = false;
        });

        if (!valid) event.preventDefault();
    });

    registerForm.addEventListener('input', (event) => {
        if (event.target.matches('input:not([type="hidden"])')) {
            event.target.classList.remove('auth-invalid');
            const output = document.querySelector(`[data-error-for="${event.target.id}"]`);
            if (output) output.textContent = '';
        }
    });

    fields.terms.addEventListener('change', () => {
        document.querySelector('[data-error-for="terms"]').textContent = '';
        fields.terms.classList.remove('auth-invalid');
    });
}
