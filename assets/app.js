import "./app.css";

document.querySelectorAll('input, select').forEach(input => {
    input.addEventListener('focus', () => {
        const icon = input.parentElement.querySelector('.material-symbols-outlined');
        if (icon) icon.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";
    });
    input.addEventListener('blur', () => {
        const icon = input.parentElement.querySelector('.material-symbols-outlined');
        if (icon) icon.style.fontVariationSettings = "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24";
    });
});

const togglePass = document.getElementById('togglePassword');
const passInput = document.getElementById('password');
if (togglePass && passInput) {
    togglePass.addEventListener('click', () => {
        const isPass = passInput.type === 'password';
        passInput.type = isPass ? 'text' : 'password';
        const icon = togglePass.querySelector('.material-symbols-outlined');
        if (icon) {
            const name = isPass ? 'visibility_off' : 'visibility';
            icon.textContent = name;
            icon.setAttribute('data-icon', name);
            icon.animate([
                { transform: 'scale(1)' },
                { transform: 'scale(1.25)' },
                { transform: 'scale(1)' }
            ], { duration: 300, easing: 'ease' });
        }
    });
}