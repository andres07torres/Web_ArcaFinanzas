document.addEventListener('click', e => {
    const btn = e.target.closest('#togglePassword');
    if (!btn) return;
    const input = document.getElementById('password');
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    const icon = btn.querySelector('.material-symbols-outlined');
    if (icon) {
        icon.textContent = show ? 'visibility_off' : 'visibility';
        icon.dataset.icon = show ? 'visibility_off' : 'visibility';
    }
});
