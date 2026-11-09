/**
 * Account UI — header auth state, wishlist toggles, login / register / profile forms.
 *
 * 依赖 inc/enqueue.php 注入的 window.ew_account（ajax_url / nonce / i18n）。
 * 登录态与收藏状态一律通过 ew_account_state 拉取 —— 页面 HTML 保持匿名可缓存。
 */
(function () {
	'use strict';

	var cfg = window.ew_account || {};
	if (!cfg.ajax_url) {
		return;
	}

	var i18n = cfg.i18n || {};
	var state = {
		loggedIn: false,
		ids: [],
		count: 0
	};

	// nonce 由 ew_account_state 下发（该接口 no-store），不写进被缓存的全页 HTML。
	var nonce = '';
	var statePromise = null;

	function qsa(selector, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(selector));
	}

	// 不带 nonce 的原始请求（仅用于 ew_account_state）
	function rawPost(action, data) {
		var body = new FormData();
		body.append('action', action);
		Object.keys(data || {}).forEach(function (key) {
			if (data[key] !== null && typeof data[key] !== 'undefined') {
				body.append(key, data[key]);
			}
		});
		return fetch(cfg.ajax_url, {
			method: 'POST',
			body: body,
			credentials: 'same-origin'
		}).then(function (res) {
			return res.json();
		});
	}

	// 带 nonce 的请求：先确保 state（含 nonce）已就绪
	function post(action, data) {
		var ready = statePromise || fetchState();
		return ready.then(function () {
			var body = new FormData();
			body.append('action', action);
			body.append('nonce', nonce);
			Object.keys(data || {}).forEach(function (key) {
				if (data[key] !== null && typeof data[key] !== 'undefined') {
					body.append(key, data[key]);
				}
			});
			return fetch(cfg.ajax_url, {
				method: 'POST',
				body: body,
				credentials: 'same-origin'
			}).then(function (res) {
				return res.json();
			});
		});
	}

	/* ---------- toast ---------- */
	var toastEl = null;
	var toastTimer = null;

	function toast(message) {
		if (!message) {
			return;
		}
		if (!toastEl) {
			toastEl = document.createElement('div');
			toastEl.className = 'ew-toast';
			toastEl.innerHTML = '<div class="ew-toast__inner"></div>';
			document.body.appendChild(toastEl);
		}
		toastEl.querySelector('.ew-toast__inner').textContent = message;
		toastEl.classList.add('is-visible');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			toastEl.classList.remove('is-visible');
		}, 1800);
	}

	/* ---------- 状态渲染 ---------- */
	function setCount(count) {
		state.count = count;
		qsa('[data-wish-count]').forEach(function (el) {
			el.textContent = String(count);
			el.hidden = count <= 0;
		});
	}

	function applyIds(ids) {
		state.ids = Array.isArray(ids) ? ids : [];
		qsa('.js-wish[data-product-id]').forEach(function (btn) {
			var id = parseInt(btn.getAttribute('data-product-id'), 10);
			var active = state.ids.indexOf(id) !== -1;
			btn.classList.toggle('is-active', active);
			btn.setAttribute('aria-pressed', active ? 'true' : 'false');
			var label = btn.querySelector('.js-wish-label');
			if (label) {
				label.textContent = active
					? (i18n.remove || 'Remove from Wishlist')
					: (i18n.add || 'Add to Wishlist');
			}
		});
	}

	function applyAuth(data) {
		state.loggedIn = !!data.logged_in;

		qsa('.js-hdr-guest').forEach(function (el) {
			el.hidden = state.loggedIn;
		});
		qsa('.js-hdr-user').forEach(function (el) {
			el.hidden = !state.loggedIn;
		});
		qsa('.js-hdr-signout').forEach(function (el) {
			el.hidden = !state.loggedIn;
			if (data.logout_url) {
				el.setAttribute('href', data.logout_url);
			}
		});
		qsa('.js-hdr-name').forEach(function (el) {
			el.textContent = data.name || (i18n.account || 'Account');
		});
	}

	function fetchState() {
		return rawPost('ew_account_state', {}).then(function (res) {
			if (!res || !res.success || !res.data) {
				return null;
			}
			if (res.data.nonce) {
				nonce = res.data.nonce;
			}
			applyAuth(res.data);
			applyIds(res.data.wishlist || []);
			setCount(res.data.count || 0);
			return res.data;
		}).catch(function () {
			return null;
		});
	}

	/* ---------- 收藏切换 ---------- */
	var pending = {};

	function onWishClick(btn) {
		var id = parseInt(btn.getAttribute('data-product-id'), 10);
		if (!id || pending[id]) {
			return;
		}
		pending[id] = true;
		btn.disabled = true;

		var before = state.ids.slice();
		var wasActive = before.indexOf(id) !== -1;
		var optimistic = wasActive
			? before.filter(function (x) { return x !== id; })
			: before.concat([id]);

		applyIds(optimistic);
		setCount(optimistic.length);

		post('ew_wishlist_toggle', { product_id: id }).then(function (res) {
			if (!res || !res.success) {
				throw new Error((res && res.data && res.data.message) || '');
			}
			var data = res.data || {};
			applyIds(data.ids || []);
			setCount(typeof data.count === 'number' ? data.count : 0);
			toast(data.active ? (i18n.added || 'Added to wishlist') : (i18n.removed || 'Removed from wishlist'));

			// 收藏页：移除后把卡片摘掉，空了就显示空态
			var grid = document.querySelector('[data-wishlist-grid]');
			var card = btn.closest ? btn.closest('.catalog-card') : null;
			if (grid && card && !data.active) {
				card.parentNode.removeChild(card);
				if (!grid.querySelector('.catalog-card')) {
					grid.hidden = true;
					var empty = document.querySelector('[data-wishlist-empty]');
					if (empty) {
						empty.hidden = false;
					}
				}
			}
		}).catch(function () {
			applyIds(before);
			setCount(before.length);
			toast(i18n.error || 'Something went wrong. Please try again.');
		}).then(function () {
			pending[id] = false;
			btn.disabled = false;
		});
	}

	document.addEventListener('click', function (event) {
		var btn = event.target.closest ? event.target.closest('.js-wish[data-product-id]') : null;
		if (!btn) {
			return;
		}
		event.preventDefault();
		if (btn.disabled) {
			return;
		}
		onWishClick(btn);
	});

	/* ---------- Turnstile（仅注册表单） ---------- */
	// 面板默认 display:none，自动渲染在隐藏容器里不可靠，改为切到注册 Tab 时显式渲染。
	var turnstileWidget = null;

	function ensureTurnstile() {
		if (!cfg.turnstileSiteKey) {
			return;
		}
		var box = document.getElementById('ew-turnstile-register');
		if (!box || !window.turnstile || turnstileWidget !== null) {
			return;
		}
		try {
			turnstileWidget = window.turnstile.render(box, {
				sitekey: cfg.turnstileSiteKey,
				language: cfg.turnstileLang || 'auto'
			});
		} catch (e) {
			turnstileWidget = null;
		}
	}

	function resetTurnstile() {
		if (turnstileWidget !== null && window.turnstile) {
			try {
				window.turnstile.reset(turnstileWidget);
			} catch (e) {}
		}
	}

	/* ---------- Tab 切换 ---------- */
	function activateTab(name) {
		qsa('.account-tab').forEach(function (tab) {
			var active = tab.getAttribute('data-tab') === name;
			tab.classList.toggle('is-active', active);
			tab.setAttribute('aria-selected', active ? 'true' : 'false');
		});
		qsa('.account-panel').forEach(function (panel) {
			panel.classList.toggle('is-active', panel.id === 'panel-' + name);
		});
		if (name === 'register') {
			ensureTurnstile();
		}
	}

	qsa('.account-tab').forEach(function (tab) {
		tab.addEventListener('click', function () {
			activateTab(tab.getAttribute('data-tab'));
		});
	});

	qsa('[data-tab-goto]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			activateTab(btn.getAttribute('data-tab-goto'));
		});
	});

	/* ---------- 表单反馈 ---------- */
	function feedback(form, message, type) {
		var box = form.querySelector('.account-feedback');
		if (!box) {
			if (message) {
				toast(message);
			}
			return;
		}
		if (!message) {
			box.hidden = true;
			box.textContent = '';
			return;
		}
		box.hidden = false;
		box.textContent = message;
		box.className = 'account-feedback account-feedback--' + (type || 'error');
	}

	function setBusy(form, busy) {
		qsa('button[type="submit"], [data-register-skip]', form).forEach(function (btn) {
			btn.disabled = !!busy;
		});
	}

	function formValues(form) {
		var data = {};
		qsa('input, select, textarea', form).forEach(function (input) {
			if (!input.name || input.disabled) {
				return;
			}
			if (input.type === 'checkbox') {
				if (input.checked) {
					data[input.name] = input.value || '1';
				}
				return;
			}
			data[input.name] = input.value;
		});
		return data;
	}

	/* ---------- 登录 ---------- */
	var loginForm = document.getElementById('ew-login-form');
	if (loginForm) {
		loginForm.addEventListener('submit', function (event) {
			event.preventDefault();
			feedback(loginForm, '');
			setBusy(loginForm, true);

			post('ew_account_login', formValues(loginForm)).then(function (res) {
				if (!res || !res.success) {
					throw new Error((res && res.data && res.data.message) || (i18n.error || 'Sign in failed.'));
				}
				toast(res.data.message || (i18n.signedIn || 'Signed in successfully.'));
				window.location.href = res.data.redirect || cfg.accountUrl || '/';
			}).catch(function (err) {
				feedback(loginForm, err.message || (i18n.error || 'Sign in failed.'));
				setBusy(loginForm, false);
			});
		});
	}

	/* ---------- 注册 ---------- */
	var registerForm = document.getElementById('ew-register-form');
	if (registerForm) {
		var submitRegister = function (skipDetails) {
			feedback(registerForm, '');
			setBusy(registerForm, true);

			var payload = formValues(registerForm);
			if (skipDetails) {
				['company', 'country', 'state', 'city', 'address_1', 'address_2', 'postcode'].forEach(function (key) {
					payload[key] = '';
				});
			}

			post('ew_account_register', payload).then(function (res) {
				if (!res || !res.success) {
					throw new Error((res && res.data && res.data.message) || (i18n.error || 'Registration failed.'));
				}
				toast(res.data.message || (i18n.registered || 'Account created.'));
				window.location.href = res.data.redirect || cfg.accountUrl || '/';
			}).catch(function (err) {
				feedback(registerForm, err.message || (i18n.error || 'Registration failed.'));
				setBusy(registerForm, false);
				resetTurnstile(); // token 一次性，失败后必须换新的
			});
		};

		registerForm.addEventListener('submit', function (event) {
			event.preventDefault();
			submitRegister(false);
		});

		qsa('[data-register-skip]', registerForm).forEach(function (btn) {
			btn.addEventListener('click', function () {
				var details = registerForm.querySelector('.account-details');
				if (details) {
					details.open = false;
				}
				submitRegister(true);
			});
		});
	}

	/* ---------- 资料保存 ---------- */
	var profileForm = document.getElementById('ew-profile-form');
	if (profileForm) {
		profileForm.addEventListener('submit', function (event) {
			event.preventDefault();
			feedback(profileForm, '');
			setBusy(profileForm, true);

			post('ew_account_profile', formValues(profileForm)).then(function (res) {
				if (!res || !res.success) {
					throw new Error((res && res.data && res.data.message) || (i18n.error || 'Save failed.'));
				}
				feedback(profileForm, res.data.message || (i18n.saved || 'Saved successfully.'), 'success');
			}).catch(function (err) {
				feedback(profileForm, err.message || (i18n.error || 'Save failed.'));
			}).then(function () {
				setBusy(profileForm, false);
			});
		});
	}

	/* ---------- 启动 ---------- */
	statePromise = fetchState();

	// 如果直接落在注册面板（例如带 ?tab=register 或脚本主动切换），补一次渲染
	var registerPanel = document.getElementById('panel-register');
	if (registerPanel && registerPanel.classList.contains('is-active')) {
		ensureTurnstile();
	}
})();
