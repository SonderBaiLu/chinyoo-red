(function () {
    'use strict';
    function initCatalog() {
        var shell = document.querySelector('.catalog-shell');
        if (!shell) return;
        var header = document.querySelector('.site-header');
        var navigation = shell.querySelector('.catalog-navigation');
        var smallScreen = window.matchMedia('(max-width: 1023px)');
        function setNavigation() {
            if (navigation) navigation.open = !smallScreen.matches;
        }
        setNavigation();
        smallScreen.addEventListener('change', setNavigation);
        function setHeaderHeight() {
            if (header) shell.style.setProperty('--catalog-header-height', Math.ceil(header.getBoundingClientRect().height) + 'px');
        }
        setHeaderHeight();
        if (header && window.ResizeObserver) new ResizeObserver(setHeaderHeight).observe(header);
        var grid = document.getElementById('catalog-featured');
        if (!grid) return;
        var previous = shell.querySelector('.catalog-prev');
        var next = shell.querySelector('.catalog-next');
        var rtl = getComputedStyle(grid).direction === 'rtl';
        function updateButtons() {
            var offset = Math.abs(grid.scrollLeft);
            previous.disabled = offset < 2;
            next.disabled = offset >= grid.scrollWidth - grid.clientWidth - 2;
        }
        function move(direction) {
            var card = grid.querySelector('.catalog-card');
            var gap = parseFloat(getComputedStyle(grid).columnGap) || 0;
            var step = card ? card.getBoundingClientRect().width + gap : grid.clientWidth;
            grid.scrollBy({ left: step * direction * (rtl ? -1 : 1), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        }
        previous.addEventListener('click', function () { move(-1); });
        next.addEventListener('click', function () { move(1); });
        grid.addEventListener('scroll', updateButtons, { passive: true });
        if (window.ResizeObserver) new ResizeObserver(updateButtons).observe(grid);
        updateButtons();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCatalog);
    else initCatalog();
}());
