function ewInitContactPanel() {
    const host = document.querySelector('.ew-contact');
    if (!host) return;
    const trigger = host.querySelector('.ew-contact-trigger');
    const panel = host.querySelector('.ew-contact-card');
    const close = host.querySelector('.ew-contact-close');
    const channels = [...host.querySelectorAll('.ew-contact-channel')];
    channels.forEach(button => button.addEventListener('click', () => {
        pinned = true;
        for (const channel of channels) {
            const selected = channel === button;
            channel.setAttribute('aria-pressed', String(selected));
            document.getElementById(channel.getAttribute('aria-controls')).hidden = !selected;
        }
    }));
    let pinned = false;
    let timer;
    let hideTimer;
    function show(open) {
        clearTimeout(timer);
        if (trigger.getAttribute('aria-expanded') === String(open)) return;
        clearTimeout(hideTimer);
        trigger.setAttribute('aria-expanded', String(open));
        panel.classList.toggle('is-closing', !open);
        panel.inert = !open;
        if (open) {
            panel.hidden = false;
        } else {
            pinned = false;
            hideTimer = setTimeout(() => { panel.hidden = true; }, matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 160);
        }
    }
    host.addEventListener('pointerenter',e => {
        clearTimeout(timer);
        if (e.pointerType === 'mouse' && matchMedia('(any-hover:hover)').matches) show(true);
    });
    host.addEventListener('pointerleave',() => {
        if (!pinned && !panel.contains(document.activeElement)) timer = setTimeout(() => show(false),220);
    });
    trigger.addEventListener('click',() => {
        if (pinned) show(false);
        else { show(true); pinned = true; }
    });
    function dismiss() { show(false); trigger.focus(); }
    close.addEventListener('click',dismiss);
    document.addEventListener('keydown',e => {
        if (e.key === 'Escape' && !panel.hidden) {
            if (host.contains(document.activeElement)) dismiss(); else show(false);
        }
    });
    document.addEventListener('pointerdown',e => { if (!host.contains(e.target)) show(false); });
    host.addEventListener('focusout',e => {
        if (e.relatedTarget && !host.contains(e.relatedTarget)) show(false);
    });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',ewInitContactPanel);
else ewInitContactPanel();
