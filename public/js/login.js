document.querySelectorAll('input, select').forEach(input => {
    input.addEventListener('focus', () => {
        const icon = input.parentElement.querySelector('.material-symbols-outlined');
        if (icon) icon.style.fontVariationSettings = "'FILL' 1";
    });
    input.addEventListener('blur', () => {
        const icon = input.parentElement.querySelector('.material-symbols-outlined');
        if (icon) icon.style.fontVariationSettings = "'FILL' 0";
    });
});

const togglePass = document.getElementById('togglePassword');
const passInput = document.getElementById('password');
if (togglePass && passInput) {
    togglePass.addEventListener('click', () => {
        const isPass = passInput.type === 'password';
        passInput.type = isPass ? 'text' : 'password';
        togglePass.querySelector('span').textContent = isPass ? 'visibility_off' : 'visibility';
    });
}
