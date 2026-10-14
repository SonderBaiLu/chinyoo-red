/**
 * Scroll animations — 1:1 复刻 RIGOL(rigol.com) 首页特效
 *
 * 页面结构约定(与 RIGOL 一致):
 *   <div data-animation-wrap>            ← 滚动触发容器(可选 data-animation-duration 控制总时长,默认 1s)
 *     <h2 data-animation="1">…</h2>      ← data-animation 为动画顺序(升序),支持小数
 *     <div data-animation="2" data-animation-type="slide-bottom">…</div>
 *     …                                  ← type: slide-bottom(默认) / slide-up / slide-left
 *                                            / slide-right / scale-up / scale-down / rotate
 *   </div>
 *
 * 复刻自 RIGOL footer.min.js: gsap.fromTo 0.4s, ease power1.inOut,
 * stagger = 总时长/元素数, ScrollTrigger start "top bottom" → end "top 70%",
 * toggleActions "play none none none"(播一次不回头)。
 */
(function () {
    'use strict';

    function init() {
        if (!window.gsap || !window.ScrollTrigger) {
            return; // GSAP 未加载时保持内容原样(渐进增强,无 JS 也正常显示)
        }
        gsap.registerPlugin(ScrollTrigger);

        // 系统"减弱动态效果"时跳过所有滚动动画
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        document.querySelectorAll('[data-animation-wrap]').forEach(function (wrap) {
            gsap.set(wrap, { opacity: 1 });

            var total = parseFloat(wrap.getAttribute('data-animation-duration')) || 1;
            var els = Array.prototype.filter.call(
                wrap.querySelectorAll('[data-animation]'),
                function (el) { return (el.getAttribute('data-animation') || '').trim() !== ''; }
            ).sort(function (a, b) {
                return parseFloat(a.getAttribute('data-animation')) - parseFloat(b.getAttribute('data-animation'));
            });

            if (!els.length) {
                return;
            }

            var stagger = total / els.length;

            gsap.fromTo(els, {
                opacity: 0,
                x: function (i, el) {
                    var t = el.getAttribute('data-animation-type') || 'slide-bottom';
                    return t === 'slide-left' ? '-2rem' : t === 'slide-right' ? '2rem' : 0;
                },
                y: function (i, el) {
                    var t = el.getAttribute('data-animation-type') || 'slide-bottom';
                    return t === 'slide-bottom' ? '2rem'
                        : t === 'slide-up' ? '-2rem'
                        : t === 'slide-down' ? '2rem' : 0;
                },
                scale: function (i, el) {
                    var t = el.getAttribute('data-animation-type') || 'slide-bottom';
                    return t === 'scale-down' ? 0.8 : t === 'scale-up' ? 1.2 : 1;
                },
                rotate: function (i, el) {
                    var t = el.getAttribute('data-animation-type') || 'slide-bottom';
                    return t === 'rotate' ? 45 : 0;
                }
            }, {
                opacity: 1,
                x: 0,
                y: 0,
                scale: 1,
                rotate: 0,
                duration: 0.4,
                ease: 'power1.inOut',
                stagger: stagger,
                scrollTrigger: {
                    trigger: wrap,
                    start: 'top bottom',
                    end: 'top 70%',
                    toggleActions: 'play none none none'
                }
            });
        });

        // 动态内容变化(如 View More 展开后布局改变)后重算触发位置
        document.querySelectorAll('[data-animation-reset]').forEach(function (el) {
            el.addEventListener('click', function () {
                setTimeout(function () { ScrollTrigger.refresh(); }, 700);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
