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

const form = document.querySelector('form');
form.addEventListener('submit', (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    btn.innerHTML = '<span class="flex items-center justify-center gap-sm"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="font-headline-sm">Procesando...</span></span>';
    setTimeout(() => {
        alert('Solicitud enviada correctamente. Un administrador revisará su cuenta.');
        window.location.reload();
    }, 1500);
});
