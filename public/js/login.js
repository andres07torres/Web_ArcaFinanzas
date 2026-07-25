document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', () => {
        input.parentElement.querySelector('.material-symbols-outlined').style.fontVariationSettings = "'FILL' 1";
    });
    input.addEventListener('blur', () => {
        input.parentElement.querySelector('.material-symbols-outlined').style.fontVariationSettings = "'FILL' 0";
    });
});

const togglePass = document.querySelector('button[type="button"]');
const passInput = document.getElementById('password');
togglePass.addEventListener('click', () => {
    const isPass = passInput.type === 'password';
    passInput.type = isPass ? 'text' : 'password';
    togglePass.querySelector('span').textContent = isPass ? 'visibility_off' : 'visibility';
});
