document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.querySelector('[data-filter-drawer]');
    const backdrop = document.querySelector('[data-filter-backdrop]');
    const open = () => { drawer?.classList.add('open'); backdrop?.classList.add('open'); document.body.classList.add('no-scroll'); };
    const close = () => { drawer?.classList.remove('open'); backdrop?.classList.remove('open'); document.body.classList.remove('no-scroll'); };
    document.querySelectorAll('[data-open-filter]').forEach(button => button.addEventListener('click', open));
    document.querySelectorAll('[data-close-filter]').forEach(button => button.addEventListener('click', close));
    backdrop?.addEventListener('click', close);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
});

