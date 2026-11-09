<?php
/**
 * Customer account — front-end login / register / profile, all via AJAX.
 *
 * 站点不开放 wp-admin 注册（users_can_register 保持 0），也不使用被
 * wps-hide-login 隐藏的后台登录页；这里自建客户入口。
 *
 * 所有返回给前端的状态都走 nocache_headers()，页面本身保持可缓存。
 *
 * 设计约束：登录/注册成功后固定跳转到账户页，不接受任何用户传入的
 * 跳转目标（无开放重定向面）。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 账户路由的站内路径，带当前语言前缀。
 *
 * home_url() 不会自动加 /zh/ 这类前缀（Polylang force_lang + hide_default），
 * 所以这里按当前语言手工拼，避免中文用户点进英文页面。
 */
function ew_account_lang_path( $slug ) {
	$path = '/' . $slug . '/';

	if ( function_exists( 'pll_current_language' ) ) {
		$lang    = pll_current_language( 'slug' );
		$default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
		if ( $lang && $default && $lang !== $default ) {
			$path = '/' . $lang . $path;
		}
	}

	return $path;
}

/**
 * 自定义登出地址。
 *
 * 不用 wp_logout_url()：它会被 wps-hide-login 改写到隐藏的后台登录页，
 * 而该页拒绝前台发起的 action=logout（返回 403）。这里走自己的 /logout/ 路由。
 */
function ew_account_logout_url() {
	return add_query_arg(
		'_wpnonce',
		wp_create_nonce( 'ew_logout' ),
		home_url( ew_account_lang_path( 'logout' ) )
	);
}

/**
 * 账户相关页面 URL（rewrite 型，不走 WP page）。
 */
function ew_account_page_url( $key ) {
	$slugs = array(
		'login'    => 'login',
		'wishlist' => 'wishlist',
		'account'  => 'account',
	);
	$slug = isset( $slugs[ $key ] ) ? $slugs[ $key ] : '';
	if ( '' === $slug ) {
		return home_url( '/' );
	}
	return home_url( ew_account_lang_path( $slug ) );
}

/**
 * 客户端 IP（只用于登录失败限流，不落库）。
 */
function ew_account_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return $ip ? $ip : 'unknown';
}

/**
 * 手机号规范化：去空格/短横线，允许 + 国家码，5-20 位数字。
 * 返回规范化后的值或 '' 表示非法。
 */
function ew_account_normalize_phone( $raw ) {
	$raw     = is_string( $raw ) ? $raw : '';
	$cleaned = preg_replace( '/[\s\-]+/', '', $raw );
	$digits  = ltrim( (string) $cleaned, '+' );
	if ( strlen( $digits ) < 5 || strlen( $digits ) > 20 || ! ctype_digit( $digits ) ) {
		return '';
	}
	return (string) $cleaned;
}

/**
 * 客户资料字段 → user meta key（沿用 WooCommerce billing 约定）。
 */
function ew_account_profile_fields() {
	return array(
		'phone'     => 'billing_phone',
		'company'   => 'billing_company',
		'country'   => 'billing_country',
		'state'     => 'billing_state',
		'city'      => 'billing_city',
		'address_1' => 'billing_address_1',
		'address_2' => 'billing_address_2',
		'postcode'  => 'billing_postcode',
	);
}

/**
 * 读取某用户的可编辑资料（供账户页预填）。
 */
function ew_account_get_profile( $user_id ) {
	$out = array();
	foreach ( ew_account_profile_fields() as $key => $meta_key ) {
		$out[ $key ] = (string) get_user_meta( $user_id, $meta_key, true );
	}
	return $out;
}

/**
 * 保存资料：未提供的键保持不变（账户页只提交用户实际填写的键）。
 */
function ew_account_save_profile( $user_id, $data ) {
	foreach ( ew_account_profile_fields() as $key => $meta_key ) {
		if ( ! array_key_exists( $key, $data ) ) {
			continue;
		}
		$value = is_string( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
		if ( 'country' === $key ) {
			$value = strtoupper( $value );
		}
		update_user_meta( $user_id, $meta_key, $value );
	}
}

/**
 * AJAX：登录态 + 收藏状态（一次拿全，避免多次请求）。
 */
function ew_ajax_account_state() {
	nocache_headers();

	$logged_in = is_user_logged_in();
	$user      = wp_get_current_user();
	$ids       = ew_wishlist_ids();

	$payload = array(
		'logged_in'    => $logged_in,
		'name'         => $logged_in ? $user->display_name : '',
		'email'        => $logged_in ? $user->user_email : '',
		'wishlist'     => $ids,
		'count'        => count( $ids ),
		// nonce 由本接口（no-store）每次下发，绝不能写进被缓存的全页 HTML
		'nonce'        => wp_create_nonce( 'ew_account_nonce' ),
		'login_url'    => ew_account_page_url( 'login' ),
		'account_url'  => ew_account_page_url( 'account' ),
		'wishlist_url' => ew_account_page_url( 'wishlist' ),
	);

	if ( $logged_in ) {
		$payload['logout_url'] = ew_account_logout_url();
		$payload['profile']    = ew_account_get_profile( $user->ID );
		$payload['phone']      = $payload['profile']['phone'];
	}

	wp_send_json_success( $payload );
}
add_action( 'wp_ajax_ew_account_state', 'ew_ajax_account_state' );
add_action( 'wp_ajax_nopriv_ew_account_state', 'ew_ajax_account_state' );

/**
 * AJAX：登录（邮箱或用户名 + 密码）。
 */
function ew_ajax_account_login() {
	check_ajax_referer( 'ew_account_nonce', 'nonce' );

	$ip   = ew_account_client_ip();
	$rate = 'ew_login_fail_' . md5( $ip );
	$fail = (int) get_transient( $rate );
	if ( $fail >= 5 ) {
		wp_send_json_error(
			array( 'message' => ew__( 'Too many failed attempts. Please try again in 15 minutes.' ) ),
			429
		);
	}

	$login    = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
	$remember = ! empty( $_POST['remember'] );

	if ( '' === $login || '' === $password ) {
		wp_send_json_error( array( 'message' => ew__( 'Please enter your email and password.' ) ) );
	}

	$user = wp_signon(
		array(
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => $remember,
		),
		is_ssl()
	);

	if ( is_wp_error( $user ) ) {
		set_transient( $rate, $fail + 1, 15 * MINUTE_IN_SECONDS );
		wp_send_json_error(
			array(
				'message' => ew__( 'Incorrect email or password.' ),
				'left'    => max( 0, 5 - ( $fail + 1 ) ),
			)
		);
	}

	delete_transient( $rate );
	wp_set_current_user( $user->ID );

	nocache_headers();
	wp_send_json_success(
		array(
			'message'  => ew__( 'Signed in successfully.' ),
			'redirect' => ew_account_page_url( 'account' ),
			'name'     => $user->display_name,
			'count'    => count( ew_wishlist_ids() ),
		)
	);
}
add_action( 'wp_ajax_ew_account_login', 'ew_ajax_account_login' );
add_action( 'wp_ajax_nopriv_ew_account_login', 'ew_ajax_account_login' );

/**
 * AJAX：注册。必填 email + password + phone；其余资料选填。
 */
function ew_ajax_account_register() {
	check_ajax_referer( 'ew_account_nonce', 'nonce' );

	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
	$phone    = isset( $_POST['phone'] ) ? ew_account_normalize_phone( wp_unslash( $_POST['phone'] ) ) : '';

	if ( '' === $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => ew__( 'Please enter a valid email address.' ) ) );
	}
	if ( strlen( $password ) < 8 ) {
		wp_send_json_error( array( 'message' => ew__( 'Password must be at least 8 characters.' ) ) );
	}
	if ( '' === $phone ) {
		wp_send_json_error( array( 'message' => ew__( 'Please enter a valid phone number.' ) ) );
	}
	if ( email_exists( $email ) || username_exists( $email ) ) {
		wp_send_json_error( array( 'message' => ew__( 'This email is already registered. Please sign in.' ) ) );
	}

	$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$display = '' !== $company ? $company : strtok( $email, '@' );

	$user_id = wp_insert_user(
		array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $password,
			'display_name' => $display,
			'nickname'     => $display,
		)
	);

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
	}

	// 选填资料（缺省即空，账户页可后补）
	$profile = array(
		'phone'     => $phone,
		'company'   => $company,
		'country'   => isset( $_POST['country'] ) ? wp_unslash( $_POST['country'] ) : '',
		'state'     => isset( $_POST['state'] ) ? wp_unslash( $_POST['state'] ) : '',
		'city'      => isset( $_POST['city'] ) ? wp_unslash( $_POST['city'] ) : '',
		'address_1' => isset( $_POST['address_1'] ) ? wp_unslash( $_POST['address_1'] ) : '',
		'address_2' => isset( $_POST['address_2'] ) ? wp_unslash( $_POST['address_2'] ) : '',
		'postcode'  => isset( $_POST['postcode'] ) ? wp_unslash( $_POST['postcode'] ) : '',
	);
	ew_account_save_profile( $user_id, $profile );

	// 自动登录 + 合并游客收藏
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true, is_ssl() );
	ew_wishlist_merge_for_user( $user_id );

	nocache_headers();
	wp_send_json_success(
		array(
			'message'  => ew__( 'Account created. Welcome!' ),
			'redirect' => ew_account_page_url( 'account' ),
			'name'     => $display,
			'count'    => count( ew_wishlist_ids() ),
		)
	);
}
add_action( 'wp_ajax_ew_account_register', 'ew_ajax_account_register' );
add_action( 'wp_ajax_nopriv_ew_account_register', 'ew_ajax_account_register' );

/**
 * AJAX：更新资料（仅登录用户）。
 */
function ew_ajax_account_profile() {
	check_ajax_referer( 'ew_account_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => ew__( 'Please sign in first.' ) ), 401 );
	}
	$user_id = get_current_user_id();

	$data   = array();
	$fields = array_keys( ew_account_profile_fields() );
	foreach ( $fields as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$data[ $key ] = wp_unslash( $_POST[ $key ] );
		}
	}

	if ( isset( $data['phone'] ) ) {
		$phone = ew_account_normalize_phone( $data['phone'] );
		if ( '' === $phone ) {
			wp_send_json_error( array( 'message' => ew__( 'Please enter a valid phone number.' ) ) );
		}
		$data['phone'] = $phone;
	}

	ew_account_save_profile( $user_id, $data );

	nocache_headers();
	wp_send_json_success(
		array(
			'message' => ew__( 'Saved successfully.' ),
			'profile' => ew_account_get_profile( $user_id ),
		)
	);
}
add_action( 'wp_ajax_ew_account_profile', 'ew_ajax_account_profile' );
