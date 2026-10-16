/**
 * 主脚本：移动端抽屉、侧栏手风琴、AJAX 询盘、锚点滚动、入场动画兜底
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ---- 移动端抽屉（汉堡菜单 + 遮罩） ----
        var toggleBtn = document.getElementById('mobile-nav-toggle');
        var navPanel  = document.getElementById('mobile-nav-panel');
        var backdrop  = document.getElementById('drawer-backdrop');

        function setDrawer(open) {
            if (!navPanel) return;
            navPanel.classList.toggle('active', open);
            navPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (backdrop) {
                backdrop.hidden = !open;
            }
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
            document.body.classList.toggle('drawer-open', open);
        }

        if (toggleBtn && navPanel) {
            toggleBtn.addEventListener('click', function () {
                setDrawer(!navPanel.classList.contains('active'));
            });
        }
        var drawerClose = document.getElementById('drawer-close');
        if (drawerClose) {
            drawerClose.addEventListener('click', function () { setDrawer(false); });
        }
        if (backdrop) {
            backdrop.addEventListener('click', function () { setDrawer(false); });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { setDrawer(false); }
        });
        // 抽屉内点链接（菜单/分类）自动收起
        if (navPanel) {
            navPanel.addEventListener('click', function (e) {
                if (e.target.closest('a')) { setDrawer(false); }
            });
        }

        // ---- 移动端搜索展开触发 ----
        var searchToggle = document.getElementById('hdr-search-toggle');
        var siteHeader   = document.querySelector('.site-header');
        if (searchToggle && siteHeader) {
            searchToggle.addEventListener('click', function () {
                siteHeader.classList.toggle('search-open');
            });
        }

        // ---- AJAX 询盘表单 ----
        var inquiryForm = document.getElementById('ew-inquiry-form');
        if (inquiryForm) {
            inquiryForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var feedback = document.getElementById('inquiry-feedback');
                var btn = inquiryForm.querySelector('button[type="submit"]');
                if (!btn) return;

                var originalText = btn.textContent;
                btn.textContent = 'Sending...';
                btn.disabled = true;

                var formData = new FormData(inquiryForm);
                formData.append('action', 'ew_submit_inquiry');
                formData.append('nonce', ew_ajax.nonce);

                fetch(ew_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (feedback) {
                        feedback.style.display = 'block';
                        if (data.success) {
                            feedback.className = 'inquiry-form__feedback inquiry-form__feedback--success';
                            feedback.textContent = data.data.message;
                            inquiryForm.reset();
                        } else {
                            feedback.className = 'inquiry-form__feedback inquiry-form__feedback--error';
                            feedback.textContent = data.data.message;
                        }
                    }
                })
                .catch(function () {
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.className = 'inquiry-form__feedback inquiry-form__feedback--error';
                        feedback.textContent = 'Network error. Please try again.';
                    }
                })
                .finally(function () {
                    btn.textContent = originalText;
                    btn.disabled = false;
                });
            });
        }

        // ---- 侧栏分类折叠（点箭头独立展开/收起，点名称正常跳转） ----
        document.querySelectorAll('.sb-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var li = btn.closest('li');
                li.classList.toggle('active');
                btn.setAttribute('aria-expanded', li.classList.contains('active') ? 'true' : 'false');
            });
        });

        // ---- Featured Items 轮播：‹ › 按一卡宽度滚动 ----
        var featGrid = document.getElementById('pc-feat-grid');
        if (featGrid) {
            var featStep = function () {
                var card = featGrid.querySelector('.pitem');
                return card ? card.getBoundingClientRect().width + 14 : 220;
            };
            var featPrev = document.querySelector('.feat-prev');
            var featNext = document.querySelector('.feat-next');
            if (featPrev) { featPrev.addEventListener('click', function () { featGrid.scrollBy({ left: -featStep(), behavior: 'smooth' }); }); }
            if (featNext) { featNext.addEventListener('click', function () { featGrid.scrollBy({ left: featStep(), behavior: 'smooth' }); }); }
        }

        // ---- 卡片对比/收藏图标（本地视觉态） ----
        document.querySelectorAll('.pitem__tool').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                btn.classList.toggle('on');
            });
        });

        // ---- 首页 Featured 产品：PC 自动轮播 + 左右箭头 ----
        var fcTrack = document.getElementById('featured-carousel-track');
        if (fcTrack) {
            var fcWrap = fcTrack.closest('.featured-carousel');
            var fcPrev = fcWrap ? fcWrap.querySelector('.featured-carousel__prev') : null;
            var fcNext = fcWrap ? fcWrap.querySelector('.featured-carousel__next') : null;
            var fcDelay = fcWrap ? parseInt(fcWrap.getAttribute('data-autoplay'), 10) : 0;
            if (!fcDelay || fcDelay < 1000) { fcDelay = 4500; }
            var fcTimer = null;

            var fcStep = function () {
                var card = fcTrack.querySelector('.product-card, .prod-card');
                if (!card) { return fcTrack.clientWidth; }
                var gap = parseFloat(window.getComputedStyle(fcTrack).columnGap) || 0;
                return card.getBoundingClientRect().width + gap;
            };
            var fcMax = function () { return fcTrack.scrollWidth - fcTrack.clientWidth; };
            var fcGo = function (dir) {
                var max = fcMax();
                if (max <= 1) { return; }
                var next = fcTrack.scrollLeft + dir * fcStep();
                if (dir > 0 && next >= max - 1) { next = 0; }
                else if (dir < 0 && next <= 1) { next = max; }
                fcTrack.scrollTo({ left: next, behavior: 'smooth' });
            };
            var fcStop = function () { if (fcTimer) { window.clearInterval(fcTimer); fcTimer = null; } };
            var fcStart = function () { fcStop(); fcTimer = window.setInterval(function () { fcGo(1); }, fcDelay); };

            if (fcPrev) { fcPrev.addEventListener('click', function () { fcStop(); fcGo(-1); fcStart(); }); }
            if (fcNext) { fcNext.addEventListener('click', function () { fcStop(); fcGo(1); fcStart(); }); }
            fcTrack.addEventListener('mouseenter', fcStop);
            fcTrack.addEventListener('mouseleave', fcStart);
            fcTrack.addEventListener('touchstart', fcStop, { passive: true });

            // 自动播放仅在桌面（与 CSS 轮播断点一致）且未开启减少动效时启用
            var fcMq = window.matchMedia('(min-width: 993px)');
            var fcReduce = window.matchMedia('(prefers-reduced-motion: reduce)');
            var fcSync = function () {
                if (fcMq.matches && !fcReduce.matches) { fcStart(); } else { fcStop(); }
            };
            if (fcMq.addEventListener) { fcMq.addEventListener('change', fcSync); }
            if (fcReduce.addEventListener) { fcReduce.addEventListener('change', fcSync); }
            fcSync();
        }

        // ---- 锚点平滑滚动 ----
        document.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ---- 入场动画安全兜底：动画播完移除属性，1.2s 超时强制恢复可见 ----
        var animateEls = Array.prototype.slice.call(document.querySelectorAll('[data-css-animate]'));
        if (animateEls.length) {
            var resetAnimate = function (el) {
                el.removeAttribute('data-css-animate');
                el.removeAttribute('data-css-animate-type');
            };
            animateEls.forEach(function (el) {
                el.addEventListener('animationend', function (e) {
                    if (/(slideup|slidedown|fadeIn|scaleUp)/.test(e.animationName || '')) {
                        resetAnimate(el);
                    }
                });
            });
            window.setTimeout(function () {
                animateEls.forEach(resetAnimate);
            }, 1200);
        }
    });
})();
