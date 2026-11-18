/**
 * EW 轻量行为统计 —— 无 Cookie、无跨站请求，只往自家 admin-ajax 发一次批量包。
 * 采集：PV（含来源/语言/设备/搜索词）+ 关键点击 + 询盘转化 + 页面停留。
 * 上报节流：缓冲攒够 5 秒或 20 条发一次；beforeunload 用 sendBeacon 补发。
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof ewAnalytics ) {
		return;
	}

	var url = ewAnalytics.ajax_url;
	var nonce = ewAnalytics.nonce;
	var batch = [];

	/* ---------- 身份：localStorage 随机 uid（无 Cookie，GDPR 友好） ----------
	   优先 crypto.getRandomValues（不可预测）；拿不到时退回时间戳+Math.random
	   （该 id 仅用于去重统计，非安全凭证，降级可接受）。 */
	function randHex() {
		try {
			var a = new Uint32Array( 4 );
			crypto.getRandomValues( a );
			return Array.prototype.map.call( a, function ( n ) {
				return ( '00000000' + n.toString( 16 ) ).slice( -8 );
			} ).join( '' );
		} catch ( e ) {
			return Date.now().toString( 16 ) + Math.random().toString( 16 ).slice( 2, 10 );
		}
	}
	function getUid() {
		try {
			var u = localStorage.getItem( 'ew_uid' );
			if ( ! u ) {
				u = 'ew' + randHex();
				localStorage.setItem( 'ew_uid', u );
			}
			return u;
		} catch ( e ) {
			return 'ew' + randHex();
		}
	}
	var uid = getUid();

	/* ---------- 维度采集 ---------- */
	function dims() {
		var el = document.documentElement;
		var ln = ( el.lang || '' ).slice( 0, 8 );
		// 语言优先取 html 的 lang 属性（Polylang 会写 <html lang>）
		if ( ! ln && document.body && document.body.lang ) {
			ln = document.body.lang.slice( 0, 8 );
		}
		var ct = ( ewAnalytics.country || '' ).slice( 0, 64 );
		var ua = navigator.userAgent;
		var ds = /Mobi|Android|iPhone|iPad/i.test( ua ) ? 'mobile' : (/Tablet|iPad/i.test( ua ) ? 'tablet' : 'desktop');
		var ref = document.referrer;
		var ch = 'direct';
		var kw = '';
		if ( ref ) {
			var host;
			try { host = new URL( ref ).host; } catch ( e ) { host = ''; }
			if ( host && host !== location.host ) {
				if ( /google\./.test( host ) ) {
					ch = 'google-organic';
					kw = searchKw( ref );
				} else if ( /bing\.|yandex\./.test( host ) ) {
					ch = 'search-other';
					kw = searchKw( ref );
				} else if ( /facebook\.|instagram\.|tiktok\./.test( host ) ) {
					ch = 'social';
				} else {
					ch = 'ref:' + host.slice( 0, 24 );
				}
			}
		}
		return { ln: ln, ct: ct, ds: ds, ch: ch, kw: kw };
	}

	function searchKw( ref ) {
		try {
			var q = new URL( ref ).searchParams.get( 'q' ) || '';
			return q.slice( 0, 128 );
		} catch ( e ) {
			return '';
		}
	}

	function page() {
		var p = location.pathname.replace( /\/+$/, '' );
		// 语言前缀 /pl /en 等保留（语言维度另有 ln 字段，这里留全路径便于页面热榜）
		return p || '/';
	}

	function push( ev, extra ) {
		var d = dims();
		var e = {
			ev: ev,
			ts: Date.now(),
			uid: uid,
			pg: page(),
			ln: d.ln,
			ct: d.ct,
			ds: d.ds,
			ch: d.ch,
			kw: d.kw
		};
		if ( extra ) {
			for ( var k in extra ) {
				if ( extra.hasOwnProperty( k ) ) {
					e[ k ] = extra[ k ];
				}
			}
		}
		batch.push( e );
		if ( batch.length >= 20 ) {
			flush();
		}
	}

	function flush() {
		if ( ! batch.length ) {
			return;
		}
		var payload = batch;
		batch = [];
		try {
			if ( navigator.sendBeacon ) {
				navigator.sendBeacon( url, JSON.stringify( { b: payload, nonce: nonce } ) );
				return;
			}
		} catch ( e ) { /* fall through to XHR */ }
		var x = new XMLHttpRequest();
		x.open( 'POST', url, true );
		x.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		x.send( 'action=ew_track_events&nonce=' + encodeURIComponent( nonce ) + '&b=' + encodeURIComponent( JSON.stringify( payload ) ) );
	}

	/* ---------- PV：页面加载即记（含 0 停留的"秒退"由 exit 事件补） ---------- */
	var enterTs = Date.now();
	push( 'pv' );

	/* ---------- 停留时长：离开时记 exit（duration ms 放 tg 复用？不——用 ch 通道会污染渠道；
	   停留时长只存到 kw？也不对。方案：exit 事件不带额外维度，tg 存时长数字） ---------- */
	function onLeave() {
		try {
			var d = Date.now() - enterTs;
			if ( d > 2000 ) {
				push( 'exit', { tg: 'dwell:' + d } );
			}
		} catch ( e ) {}
		flush();
	}
	window.addEventListener( 'pagehide', onLeave );
	window.addEventListener( 'beforeunload', onLeave );

	/* ---------- 关键点击（事件委托，全站一处） ---------- */
	var clickMap = {
		'.inquiry-form button[type="submit"]': '询盘提交按钮',
		'#ew-inquiry-form button[type="submit"]': '询盘提交按钮',
		'a.cta': '头部 Get a Quote',
		'.mobile-nav-quote': '移动端 Quote',
		'.catalog-card__inquiry': '卡片 立即询价',
		'.catalog-card__actions a:first-child': '卡片 了解更多',
		'.catalog-nav a': '侧栏分类导航',
		'.mobile-cat-strip a': '移动端分类条',
		'a[href*="wa.me"], a[href*="api.whatsapp.com"]': 'WhatsApp',
		'.btn--inquiry': '页面内询价按钮',
		'.foot a': '页脚链接',
		'.wishlist-toggle': '收藏按钮',
		'.guide-link, a[href*="/support/"]': '指南/下载'
	};

	document.addEventListener( 'click', function ( e ) {
		var t = e.target;
		while ( t && t !== document ) {
			for ( var sel in clickMap ) {
				if ( clickMap.hasOwnProperty( sel ) && t.matches && t.matches( sel ) ) {
					var extra = {};
					// 导航类带目标路径
					if ( t.tagName === 'A' && t.href ) {
						try { extra.tg = clickMap[ sel ] + '|' + new URL( t.href ).pathname; } catch ( err ) { extra.tg = clickMap[ sel ]; }
					} else {
						extra.tg = clickMap[ sel ];
					}
					push( 'click', extra );
					return;
				}
			}
			t = t.parentNode;
		}
	}, true );

	/* ---------- 询盘转化：watch 表单提交成功（feedback 区域出现成功文案即算转化） ---------- */
	function watchConvert() {
		var fb = document.getElementById( 'inquiry-feedback' );
		if ( ! fb || fb.__ewWatched ) {
			return;
		}
		fb.__ewWatched = true;
		var mo = new MutationObserver( function () {
			if ( fb.style.display !== 'none' && fb.textContent && /thank|received|成功/i.test( fb.textContent ) ) {
				mo.disconnect();
				push( 'convert', { tg: 'inquiry-success' } );
			}
		} );
		mo.observe( fb, { childList: true, characterData: true, subtree: true } );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', watchConvert );
	} else {
		watchConvert();
	}

	/* ---------- 定时兜底 flush（页面一直开着也至少 30 秒落一批） ---------- */
	setInterval( function () { if ( batch.length ) { flush(); } }, 30000 );
}() );
