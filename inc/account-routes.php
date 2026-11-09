<?php
/**
 * Account routes — /login/ /wishlist/ /account/ handled by theme templates.
 *
 * 用 rewrite 而不是 WP 页面：页面在多语言（Polylang force_lang）下需要每个
 * 语言各建一份，而 rewrite 天然共享 /pl/login/、/zh/login/ 等前缀，文案由
 * Polylang 字符串翻译提供。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 识别账户页请求。
 *
 * 不用 rewrite 规则：Polylang（force_lang）会给非默认语言加 /zh/、/pl/ 前缀，
 * WP 的 rewrite 规则按完整路径匹配，`^login/?$` 匹配不到 `zh/login`。
 * 这里在 parse_request 阶段自己解析路径并剥掉语言前缀，各语言共用同一套路由。
 */
function ew_account_detect_view( $wp ) {
	if ( is_admin() ) {
		return;
	}

	$request = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';
	if ( '' === $request ) {
		return;
	}

	$segments = explode( '/', $request );

	// 剥掉语言前缀（如 zh/login、pl/login）
	if ( count( $segments ) > 1 ) {
		$first = $segments[0];
		$langs = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array();
		if ( in_array( $first, (array) $langs, true ) ) {
			array_shift( $segments );
		}
	}

	$views = array( 'login', 'wishlist', 'account', 'logout' );

	if ( 1 !== count( $segments ) || ! in_array( $segments[0], $views, true ) ) {
		return;
	}

	// 交给 WP_Query：query_vars 已通过 ew_account_register_query_var 注册为公有变量
	$wp->query_vars = array( 'ew_account_view' => $segments[0] );
}
add_action( 'parse_request', 'ew_account_detect_view', 1 );

/**
 * 兼容：仍注册 rewrite 规则（默认语言下直接命中，减少一次 parse_request 覆盖）。
 */
function ew_account_register_routes() {
	add_rewrite_rule( '^login/?$', 'index.php?ew_account_view=login', 'top' );
	add_rewrite_rule( '^wishlist/?$', 'index.php?ew_account_view=wishlist', 'top' );
	add_rewrite_rule( '^account/?$', 'index.php?ew_account_view=account', 'top' );
	add_rewrite_rule( '^logout/?$', 'index.php?ew_account_view=logout', 'top' );
}
add_action( 'init', 'ew_account_register_routes', 5 );

/**
 * 允许的视图 → 模板文件。
 */
function ew_account_view_templates() {
	return array(
		'login'    => 'account-login.php',
		'wishlist' => 'account-wishlist.php',
		'account'  => 'account-profile.php',
	);
}

function ew_account_register_query_var( $vars ) {
	$vars[] = 'ew_account_view';
	return $vars;
}
add_filter( 'query_vars', 'ew_account_register_query_var' );

/**
 * 首次运行刷新一次 rewrite（主题已启用时 after_switch_theme 不会再触发）。
 */
function ew_account_maybe_flush_routes() {
	if ( '1' === get_option( 'ew_account_routes_v2' ) ) {
		return;
	}
	ew_account_register_routes();
	flush_rewrite_rules( false );
	update_option( 'ew_account_routes_v2', '1', false );
}
add_action( 'init', 'ew_account_maybe_flush_routes', 99 );

/**
 * 输出账户页所需的缓存头：这些页面按用户渲染，绝不能被整页缓存。
 */
function ew_account_nocache() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	nocache_headers();
	header( 'X-Accel-Expires: 0' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
}

/**
 * 渲染账户页。
 */
function ew_account_render_view() {
	$view = get_query_var( 'ew_account_view' );
	if ( ! $view ) {
		return;
	}

	$templates = ew_account_view_templates();

	// 登出：独立路由，不经过 wps-hide-login 劫持的后台登录页
	if ( 'logout' === $view ) {
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ew_logout' ) ) {
			status_header( 403 );
			wp_die( esc_html__( 'Invalid logout request.', 'chinyoo-red' ), '', array( 'response' => 403 ) );
		}
		wp_logout();
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}

	if ( ! isset( $templates[ $view ] ) ) {
		return;
	}

	// 账户页必须登录
	if ( 'account' === $view && ! is_user_logged_in() ) {
		$login = ew_account_page_url( 'login' );
		wp_safe_redirect( $login, 302 );
		exit;
	}

	// 已登录还看登录页 → 直接去账户页
	if ( 'login' === $view && is_user_logged_in() ) {
		wp_safe_redirect( ew_account_page_url( 'account' ), 302 );
		exit;
	}

	ew_account_nocache();

	global $wp_query;
	$wp_query->is_404        = false;
	$wp_query->is_home       = false; // 否则 body 会带 .home，命中首页专属样式（1360px 版心）
	$wp_query->is_front_page = false;
	$wp_query->is_page       = true;
	$wp_query->is_singular   = false;
	status_header( 200 );

	$file = locate_template( $templates[ $view ] );
	if ( ! $file ) {
		wp_die( 'Account template missing.', '', array( 'response' => 500 ) );
	}
	include $file;
	exit;
}
add_action( 'template_redirect', 'ew_account_render_view', 1 );

/**
 * 账户页 body class：去掉误加的 home/blog/404，方便样式精准作用域。
 */
function ew_account_body_class( $classes ) {
	if ( ! get_query_var( 'ew_account_view' ) ) {
		return $classes;
	}
	$classes = array_diff( $classes, array( 'home', 'blog', 'archive', 'error404', 'paged' ) );
	$classes[] = 'account-page';
	$classes[] = 'ew-account-view';
	return $classes;
}
add_filter( 'body_class', 'ew_account_body_class' );

/**
 * 账户页标题。
 */
function ew_account_document_title( $title ) {
	$view = get_query_var( 'ew_account_view' );
	$map  = array(
		'login'    => 'Sign In',
		'wishlist' => 'My Wishlist',
		'account'  => 'Account Details',
	);
	if ( $view && isset( $map[ $view ] ) ) {
		return ew__( $map[ $view ] ) . ' | ' . get_bloginfo( 'name' );
	}
	return $title;
}
// 优先级 30 需高于 Rank Math 的 pre_get_document_title (15)，否则被其覆盖
add_filter( 'pre_get_document_title', 'ew_account_document_title', 30 );

/**
 * 把新 UI 字符串注册到 Polylang（字符串翻译面板可见/可改）。
 */
function ew_account_register_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$file = EW_THEME_DIR . '/inc/account-i18n-strings.php';
	if ( ! file_exists( $file ) ) {
		return;
	}
	$data = include $file;
	if ( ! is_array( $data ) || empty( $data['zh'] ) ) {
		return;
	}
	foreach ( array_keys( $data['zh'] ) as $string ) {
		pll_register_string( $string, $string, 'chinyoo-red' );
	}
}
add_action( 'init', 'ew_account_register_strings', 20 );

/**
 * 首次运行时把译文写入各语言（已存在的条目保留人工修改）。
 *
 * Polylang 在本地版本把字符串翻译存在语言 term 的
 * `_pll_strings_translations` meta 里，格式为 [原文, 译文] 对数组。
 */
function ew_account_import_strings() {
	if ( '1' === get_option( 'ew_account_i18n_v4' ) ) {
		return;
	}
	if ( ! function_exists( 'pll_languages_list' ) || ! taxonomy_exists( 'language' ) ) {
		return;
	}

	$file = EW_THEME_DIR . '/inc/account-i18n-strings.php';
	if ( ! file_exists( $file ) ) {
		return;
	}
	$data = include $file;
	if ( ! is_array( $data ) ) {
		return;
	}

	$imported = 0;
	foreach ( $data as $lang => $map ) {
		$term = get_term_by( 'slug', $lang, 'language' );
		if ( ! $term || is_wp_error( $term ) || ! is_array( $map ) ) {
			continue;
		}

		$existing = get_term_meta( $term->term_id, '_pll_strings_translations', true );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		$have = array();
		foreach ( $existing as $pair ) {
			if ( isset( $pair[0] ) ) {
				$have[ (string) $pair[0] ] = true;
			}
		}

		$changed = false;
		foreach ( $map as $source => $translation ) {
			if ( '' === (string) $translation || isset( $have[ (string) $source ] ) ) {
				continue;
			}
			$existing[] = array( (string) $source, (string) $translation );
			$imported++;
			$changed = true;
		}

		if ( $changed ) {
			update_term_meta( $term->term_id, '_pll_strings_translations', $existing );
		}
	}

	update_option( 'ew_account_i18n_v4', '1', false );
	if ( $imported ) {
		error_log( '[ew-account] imported ' . $imported . ' UI string translations' );
	}
}
add_action( 'init', 'ew_account_import_strings', 30 );
