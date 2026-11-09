<?php
/**
 * Wishlist — guest cookie + logged-in user meta, AJAX toggle.
 *
 * 游客收藏存 cookie，登录用户存 user meta；登录/注册时把 cookie 合并进账号。
 * 注意：收藏状态绝不能服务端渲染进 HTML —— 全站有 24h FastCGI 缓存，
 * 匿名 HTML 必须对所有人一致，状态由 assets/js/account.js 通过 ew_account_state 拉取。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EW_WISHLIST_COOKIE', 'ew_wishlist' );
define( 'EW_WISHLIST_META', 'ew_wishlist' );
define( 'EW_WISHLIST_COOKIE_DAYS', 30 );

/**
 * 规范化 ID 列表：去重、去零、转 int。
 */
function ew_wishlist_clean_ids( $ids ) {
	if ( ! is_array( $ids ) ) {
		return array();
	}
	$out = array();
	foreach ( $ids as $id ) {
		$id = absint( $id );
		if ( $id && ! in_array( $id, $out, true ) ) {
			$out[] = $id;
		}
	}
	return $out;
}

/**
 * 当前收藏 ID 列表（登录读 meta，游客读 cookie）。
 */
function ew_wishlist_ids() {
	if ( is_user_logged_in() ) {
		return ew_wishlist_clean_ids( (array) get_user_meta( get_current_user_id(), EW_WISHLIST_META, true ) );
	}

	$raw = isset( $_COOKIE[ EW_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ EW_WISHLIST_COOKIE ] ) : '';
	if ( '' === $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		$decoded = explode( ',', $raw );
	}
	return ew_wishlist_clean_ids( $decoded );
}

/**
 * 写入收藏列表（登录写 meta，游客写 cookie）。
 */
function ew_wishlist_set( $ids ) {
	$ids = ew_wishlist_clean_ids( $ids );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), EW_WISHLIST_META, $ids );
		return;
	}

	if ( headers_sent() ) {
		return;
	}

	$value  = implode( ',', $ids );
	$expire = empty( $ids ) ? time() - YEAR_IN_SECONDS : time() + DAY_IN_SECONDS * EW_WISHLIST_COOKIE_DAYS;

	setcookie(
		EW_WISHLIST_COOKIE,
		$value,
		array(
			'expires'  => $expire,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => false, // 允许前端读取以便即时反映状态；内容无敏感性
			'samesite' => 'Lax',
		)
	);

	// 同请求内后续读取保持一致
	$_COOKIE[ EW_WISHLIST_COOKIE ] = $value;
}

/**
 * 把当前浏览器 cookie 里的收藏合并进指定用户。
 */
function ew_wishlist_merge_for_user( $user_id ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return;
	}

	$raw = isset( $_COOKIE[ EW_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ EW_WISHLIST_COOKIE ] ) : '';
	if ( '' === $raw ) {
		return;
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		$decoded = explode( ',', $raw );
	}
	$cookie_ids = ew_wishlist_clean_ids( $decoded );
	if ( empty( $cookie_ids ) ) {
		return;
	}

	$meta_ids = ew_wishlist_clean_ids( (array) get_user_meta( $user_id, EW_WISHLIST_META, true ) );
	$merged   = ew_wishlist_clean_ids( array_merge( $meta_ids, $cookie_ids ) );
	update_user_meta( $user_id, EW_WISHLIST_META, $merged );

	// 清掉游客 cookie
	if ( ! headers_sent() ) {
		setcookie(
			EW_WISHLIST_COOKIE,
			'',
			array(
				'expires'  => time() - YEAR_IN_SECONDS,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			)
		);
	}
	unset( $_COOKIE[ EW_WISHLIST_COOKIE ] );
}
add_action( 'wp_login', 'ew_wishlist_merge_for_user', 10, 2 );

/**
 * AJAX：切换收藏（登录/游客都可用）。
 */
function ew_ajax_wishlist_toggle() {
	check_ajax_referer( 'ew_account_nonce', 'nonce' );

	$pid = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $pid || 'product' !== get_post_type( $pid ) || 'publish' !== get_post_status( $pid ) ) {
		wp_send_json_error( array( 'message' => ew__( 'Product not found.' ) ), 404 );
	}

	$ids = ew_wishlist_ids();
	$has = in_array( $pid, $ids, true );

	if ( $has ) {
		$ids = array_values( array_diff( $ids, array( $pid ) ) );
	} else {
		$ids[] = $pid;
	}
	ew_wishlist_set( $ids );

	nocache_headers();
	wp_send_json_success(
		array(
			'active' => ! $has,
			'count'  => count( $ids ),
			'ids'    => $ids,
		)
	);
}
add_action( 'wp_ajax_ew_wishlist_toggle', 'ew_ajax_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_ew_wishlist_toggle', 'ew_ajax_wishlist_toggle' );
