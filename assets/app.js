import "./app.css";

document.addEventListener('focusin', e => {
    const icon = e.target.parentElement?.querySelector('.material-symbols-outlined');
    if (icon) icon.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";
});
document.addEventListener('focusout', e => {
    const icon = e.target.parentElement?.querySelector('.material-symbols-outlined');
    if (icon) icon.style.fontVariationSettings = "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24";
});

document.addEventListener('click', e => {
    const btn = e.target.closest('#togglePassword');
    if (!btn) return;
    const group = btn.closest('.relative');
    if (!group) return;
    const passInput = group.querySelector('#password');
    if (!passInput) return;
    const isPass = passInput.type === 'password';
    passInput.type = isPass ? 'text' : 'password';
    const icon = btn.querySelector('.material-symbols-outlined');
    if (icon) {
        icon.textContent = isPass ? 'visibility_off' : 'visibility';
        icon.setAttribute('data-icon', isPass ? 'visibility_off' : 'visibility');
    }
});