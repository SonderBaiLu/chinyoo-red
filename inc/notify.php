<?php
/**
 * Notifications — 钉钉群机器人 + 邮件留档。
 *
 * 询盘（inc/inquiry-handler.php）与注册（inc/account.php）共用这里的发送逻辑，
 * 加签 / 关键词 / 错误日志只维护一份。
 *
 * 配置（wp-config.php，服务端）：
 *   define( 'CHINYOO_DINGTALK_WEBHOOK', 'https://oapi.dingtalk.com/robot/send?access_token=...' );
 *   define( 'CHINYOO_DINGTALK_SECRET',  'SEC...' ); // 机器人「加签」时必填
 *
 * 注意：钉钉机器人若设了「自定义关键词」，消息必须包含该词（当前为「新客户」），
 * 否则钉钉会静默丢弃。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 发送一条 markdown 消息到钉钉群机器人。失败只记日志，不影响业务流程。
 *
 * @param string $title 消息标题（显示在钉钉通知卡片上）
 * @param string $text  markdown 正文
 * @return bool 是否发送成功
 */
function ew_dingtalk_post( $title, $text ) {
	$webhook = defined( 'CHINYOO_DINGTALK_WEBHOOK' ) && CHINYOO_DINGTALK_WEBHOOK ? CHINYOO_DINGTALK_WEBHOOK : '';
	if ( empty( $webhook ) ) {
		return false; // 未配置，静默跳过
	}

	$url = $webhook;

	// 加签安全设置：URL 追加 timestamp + sign
	if ( defined( 'CHINYOO_DINGTALK_SECRET' ) && CHINYOO_DINGTALK_SECRET ) {
		$timestamp      = round( microtime( true ) * 1000 );
		$string_to_sign = $timestamp . "\n" . CHINYOO_DINGTALK_SECRET;
		$sign           = urlencode( base64_encode( hash_hmac( 'sha256', $string_to_sign, CHINYOO_DINGTALK_SECRET, true ) ) );
		$sep            = ( strpos( $url, '?' ) !== false ) ? '&' : '?';
		$url            = $url . $sep . 'timestamp=' . $timestamp . '&sign=' . $sign;
	}

	$payload = array(
		'msgtype'  => 'markdown',
		'markdown' => array(
			'title' => $title,
			'text'  => $text,
		),
	);

	$response = wp_remote_post(
		$url,
		array(
			'body'    => wp_json_encode( $payload ),
			'headers' => array( 'Content-Type' => 'application/json' ),
			'timeout' => 5,
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( '[ew-notify] DingTalk push failed: ' . $response->get_error_message() );
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	if ( $body ) {
		$decoded = json_decode( $body, true );
		if ( isset( $decoded['errcode'] ) && 0 !== (int) $decoded['errcode'] ) {
			error_log( '[ew-notify] DingTalk error: ' . ( isset( $decoded['errmsg'] ) ? $decoded['errmsg'] : $body ) );
			return false;
		}
	}

	return true;
}

/**
 * 接收通知的邮箱列表（默认站点管理员邮箱，可用过滤器追加）。
 *
 * @return string[]
 */
function ew_signup_notify_emails() {
	$emails = array( get_option( 'admin_email' ) );
	$emails = apply_filters( 'ew_signup_notify_emails', $emails );
	$emails = array_filter( array_map( 'sanitize_email', (array) $emails ) );
	return array_values( array_unique( $emails ) );
}

/**
 * 新客户注册通知：钉钉实时提醒 + 邮件留档。
 *
 * @param int   $user_id 新用户 ID
 * @param array $data    email / phone / company / country / city
 */
function ew_notify_new_registration( $user_id, $data ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return;
	}

	$email   = isset( $data['email'] ) ? (string) $data['email'] : '';
	$phone   = isset( $data['phone'] ) ? (string) $data['phone'] : '';
	$company = isset( $data['company'] ) ? trim( (string) $data['company'] ) : '';
	$country = isset( $data['country'] ) ? strtoupper( trim( (string) $data['country'] ) ) : '';
	$city    = isset( $data['city'] ) ? trim( (string) $data['city'] ) : '';

	$location = trim( trim( $country ) . ' / ' . trim( $city ), ' /' );
	if ( '' === $location ) {
		$location = '—';
	}
	if ( '' === $company ) {
		$company = '—';
	}

	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
	$time = current_time( 'Y-m-d H:i:s' );
	$edit = admin_url( 'user-edit.php?user_id=' . $user_id );

	// ---- 钉钉（正文必须含关键词「新客户」）----
	$content = '### 🆕 新客户注册通知' . "\n\n"
		. '**邮箱：** ' . $email . "\n"
		. '**手机号：** ' . ( '' !== $phone ? $phone : '—' ) . "\n"
		. '**公司：** ' . $company . "\n"
		. '**国家/城市：** ' . $location . "\n"
		. '**站点语言：** ' . ( '' !== $lang ? $lang : '—' ) . "\n\n"
		. '**时间：** ' . $time . "\n"
		. '**后台：** ' . $edit;

	// 标题也带上关键词，双保险
	ew_dingtalk_post( '新客户注册通知', $content );

	// ---- 邮件留档 ----
	$subject = sprintf( '[%s] 新客户注册：%s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $email );
	$body    = "新客户注册\n\n"
		. "邮箱：{$email}\n"
		. '手机号：' . ( '' !== $phone ? $phone : '—' ) . "\n"
		. '公司：' . $company . "\n"
		. '国家/城市：' . $location . "\n"
		. '站点语言：' . ( '' !== $lang ? $lang : '—' ) . "\n"
		. "时间：{$time}\n"
		. "后台：{$edit}\n";

	$to = ew_signup_notify_emails();
	if ( $to ) {
		$sent = wp_mail( $to, $subject, $body );
		if ( ! $sent ) {
			error_log( '[ew-notify] signup email failed for user #' . $user_id );
		}
	}

	/**
	 * 注册通知发出后触发，便于以后接其他渠道（企业微信 / Telegram / CRM）。
	 *
	 * @param int   $user_id
	 * @param array $data
	 */
	do_action( 'ew_after_new_registration_notify', $user_id, $data );
}
