/**
 * P4 WooCommerce theme scripts (chinyoo.com, UNI-T style B2B).
 *
 * Dependencies: jQuery (enqueued as 'ew-p4-woo' with 'jquery' dependency).
 *
 * Features:
 *  1. Series model dropdown -> update URL (?attribute_pa_model=MODEL#Specifications)
 *  2. Inquiry CTA (.js-inquiry-cta[data-model]) -> fill #inquiry_product + scroll to #inquiry
 *  3. Product gallery thumbnail switching and click-to-enlarge preview
 */
(function ($) {
	'use strict';

	$(function () {

		/* ----------------------------------------------------------
		   1) Model dropdown -> update URL
		   Selector covers the recommended markup (select inside
		   .series-select), the raw attribute dropdown, and an explicit
		   .js-model-select hook. Changing the option navigates to the
		   series page for that model, keeping the #Specifications anchor.
		   ---------------------------------------------------------- */
		var $modelSelect = $(
			'.series-select select, ' +
			'select[name="attribute_pa_model"], ' +
			'select.js-model-select'
		);

		if ($modelSelect.length) {
			$modelSelect.on('change', function () {
				var model = $.trim($(this).val());
				if (!model) {
					return;
				}
				var url = window.location.pathname +
					'?attribute_pa_model=' + encodeURIComponent(model) +
					'#tab-Specifications';
				window.location.href = url;
			});
		}

		/* ----------------------------------------------------------
		   2) Inquiry CTA -> fill product field + scroll to #inquiry
		   On a series page (or anywhere with a local #inquiry) we
		   preventDefault and scroll + autofill. On archive/card grids
		   there is no local #inquiry — the button is a link to the
		   series page #inquiry anchor, so we let the browser navigate.
		   ---------------------------------------------------------- */
		$(document).on('click', '.js-inquiry-cta', function (e) {
			var $field = $('#inquiry_product');
			var $target = $('#inquiry');

			if (!$target.length && !$field.length) {
				// No local form: follow the anchor href (series page).
				return;
			}

			e.preventDefault();

			var model = $.trim($(this).data('model') || '');

			if ($field.length) {
				if ($field.is('select')) {
					// Only select the model when it is a real option value.
					var $opt = $field.find('option').filter(function () {
						return this.value === model;
					});
					if ($opt.length) {
						$field.val(model);
					}
				} else if (model) {
					var current = $.trim($field.val() || '');
					var value;
					if (!current) {
						value = model;
					} else if (current.indexOf(model) === -1) {
						value = current + ' - ' + model;
					} else {
						value = current;
					}
					$field.val(value);
				}
			}

			if ($target.length) {
				var targetOffset = $target.offset();
				var scrollTop = targetOffset ? (targetOffset.top - 70) : 0;
				$('html, body').stop(true, true).animate(
					{ scrollTop: scrollTop },
					400,
					function () {
						if ($field.length) {
							$field.trigger('focus');
						}
					}
				);
			} else if ($field.length) {
				$field.trigger('focus');
			}
		});

		/* ----------------------------------------------------------
		   2b) Card "Inquiry" links land on the series page carrying
		   ?inquiry_model=MODELS — prefill + scroll on load.
		   ---------------------------------------------------------- */
		(function () {
			var qs = window.location.search.slice(1);
			if (!qs) {
				return;
			}
			var pairs = qs.split('&');
			var model = '';
			for (var i = 0; i < pairs.length; i++) {
				var kv = pairs[i].split('=');
				if (kv[0] === 'inquiry_model') {
					try {
						model = decodeURIComponent(kv[1] || '');
					} catch (err) {
						model = '';
					}
					break;
				}
			}
			if (!model) {
				return;
			}
			var $field = $('#inquiry_product');
			if ($field.length) {
				if ($field.is('select')) {
					$field.find('option').each(function () {
						if (this.value === model) {
							$field.val(model);
						}
					});
				} else {
					var cur = $.trim($field.val() || '');
					if (!cur) {
						$field.val(model);
					} else if (cur.indexOf(model) === -1) {
						$field.val(cur + ' - ' + model);
					}
				}
			}
			var $target = $('#inquiry');
			if ($target.length) {
				setTimeout(function () {
					var offset = $target.offset();
					if (offset) {
						$('html, body').stop(true, true).animate(
							{ scrollTop: offset.top - 70 },
							400
						);
					}
					if ($field.length) {
						$field.trigger('focus');
					}
				}, 350);
			} else if ($field.length) {
				$field.trigger('focus');
			}
		})();

			/* ----------------------------------------------------------
			   3) Product gallery thumbnails and click-to-enlarge preview.
			   ---------------------------------------------------------- */
			$(document).on('click', '.product-gallery--premium .yith_magnifier_gallery a', function (e) {
				e.preventDefault();
				var $thumb = $(this);
				var $gallery = $thumb.closest('.product-gallery--premium');
				var $main = $gallery.find('.woocommerce-main-image').first();
				var $img = $main.find('img').first();
				var source = $thumb.attr('data-small') || $thumb.attr('href');
				var full = $thumb.attr('href') || source;
				if (!source || !$main.length || !$img.length) { return; }
				$main.attr('href', full);
				$img.attr('src', source).attr('srcset', '').attr('sizes', '');
				$gallery.find('.yith_magnifier_gallery a').removeClass('active-thumbnail');
				$thumb.addClass('active-thumbnail');
			});

			$(document).on('click', '.product-gallery--premium .woocommerce-main-image', function (e) {
				e.preventDefault();
				if (window.openLightbox && this.href) {
					window.openLightbox(this.href);
				}
			});

		/* ----------------------------------------------------------
		   4) Product detail tabs (UNI-T style)
		   #product-detail-tabs -> .product-tab[data-tab] switches the
		   matching .product-tab__panel[id=tab-<name>]. On load, the
		   hash (e.g. #tab-Specifications, set by the model dropdown
		   navigation) opens that tab.
		   ---------------------------------------------------------- */
		var $tabs = $('#product-detail-tabs');

		if ($tabs.length) {
			function ewOpenTab(name) {
				if (!name) {
					return;
				}
				name = String(name).toLowerCase();
				var $tab = $tabs.find('.product-tab[data-tab="' + name + '"]');
				var $panel = $tabs.find('.product-tab__panel').filter(function () {
					return this.id && this.id.toLowerCase() === 'tab-' + name;
				});
				if (!$tab.length || !$panel.length) {
					return;
				}
				$tabs.find('.product-tab').removeClass('is-active').attr('aria-selected', 'false');
				$tabs.find('.product-tab__panel').removeClass('is-active');
				$tab.addClass('is-active').attr('aria-selected', 'true');
				$panel.addClass('is-active');
			}

			$tabs.on('click', '.product-tab', function (e) {
				e.preventDefault();
				var name = $(this).data('tab');
				ewOpenTab(name);
				// keep the anchor in the URL without jumping
				if (history.replaceState) {
					history.replaceState(null, '', '#tab-' + name);
				}
			});

			// On load, honor a #tab-<name> hash (set by model dropdown navigation).
			var hash = window.location.hash;
			var m = hash.match(/^#tab-([a-z]+)/i);
			if (m) {
				ewOpenTab(m[1].toLowerCase());
			}
		}
	});
})(jQuery);

/* 通过原生 JavaScript 监听左侧导航的 mouseenter 事件切换右侧面板激活状态：     */
document.addEventListener('DOMContentLoaded', function () {
	const megaNavItems = document.querySelectorAll('.c-mega-nav__item');
	const megaPanels = document.querySelectorAll('.c-mega-panel');

	megaNavItems.forEach(function (item) {
		item.addEventListener('mouseenter', function () {
			const targetId = this.getAttribute('data-target');

			megaNavItems.forEach(function (el) { el.classList.remove('is-active'); });
			megaPanels.forEach(function (el) { el.classList.remove('is-active'); });

			this.classList.add('is-active');
			const targetPanel = document.getElementById(targetId);
			if (targetPanel) {
				targetPanel.classList.add('is-active');
			}
		});
	});
});