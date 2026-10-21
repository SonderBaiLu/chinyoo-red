<?php
/**
 * AJAX inquiry form handler
 *
 * 新增：钉钉群机器人推送新询盘通知（实时）。
 * 配置：wp-config.php 里定义常量——
 *   CHINYOO_DINGTALK_WEBHOOK  → https://oapi.dingtalk.com/robot/send?access_token=xxx
 *   CHINYOO_DINGTALK_SECRET   → 仅当机器人开启「加签」安全设置时填写（SEC...）
 * 若未配置 WEBHOOK 常量，推送自动跳过，不影响询盘提交。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle logged-in and non-logged-in users
add_action( 'wp_ajax_ew_submit_inquiry', 'ew_handle_inquiry' );
add_action( 'wp_ajax_nopriv_ew_submit_inquiry', 'ew_handle_inquiry' );

function ew_handle_inquiry() {

    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ew_inquiry_nonce' ) ) {
        wp_send_json_error( array( 'message' => ew__('Security check failed. Please refresh the page.') ) );
    }

    // 人机验证：未通过就明确报错让真人重试，区别于下面的静默丢弃。
    $ew_ts_token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
    $ew_ts_ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    if ( ! ew_turnstile_verify( $ew_ts_token, $ew_ts_ip ) ) {
        wp_send_json_error( array( 'message' => ew__('Human verification failed. Please complete it and try again.') ) );
    }

    // 防垃圾询盘：蜜罐 / 频率限制 / 内容过滤。命中则静默丢弃——
    // 返回与成功一致的消息，但不创建询盘、不发邮件、不推钉钉（不告诉机器人它被拦了）。
    if ( ew_inquiry_is_spam( $_POST ) ) {
        wp_send_json_success( array(
            'message' => ew__('Thank you! We have received your inquiry and will get back to you within 24 hours.'),
        ) );
    }

    // Sanitize inputs
    $name    = sanitize_text_field( $_POST['inquiry_name'] );
    $email   = sanitize_email( $_POST['inquiry_email'] );
    $phone   = sanitize_text_field( $_POST['inquiry_phone'] );
    $phone_country = isset( $_POST['inquiry_phone_country'] )
        ? strtoupper( sanitize_text_field( $_POST['inquiry_phone_country'] ) )
        : '';
    $company = sanitize_text_field( $_POST['inquiry_company'] );
    $product = sanitize_text_field( $_POST['inquiry_product'] );
    $message = sanitize_textarea_field( $_POST['inquiry_message'] );

    // Validate required fields
    if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $message ) ) {
        wp_send_json_error( array( 'message' => ew__('Please fill in all required fields.') ) );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => ew__('Please enter a valid email address.') ) );
    }

    // Create inquiry post
    $inquiry_id = wp_insert_post( array(
        'post_type'    => 'inquiry',
        'post_title'   => sprintf(
            /* translators: %1$s = name, %2$s = product */
            ew__('Inquiry from %1$s — %2$s'),
            $name,
            $product ?: ew__('General')
        ),
        'post_status'  => 'publish',
    ) );

    if ( is_wp_error( $inquiry_id ) ) {
        wp_send_json_error( array( 'message' => ew__('Failed to submit inquiry. Please try again.') ) );
    }
    error_log( '[ew-inquiry] ACCEPTED inquiry #' . $inquiry_id . ' | IP ' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'n/a' ) . ' | ' . $email );

    // Save ACF fields
    update_field( 'inquiry_name', $name, $inquiry_id );
    update_field( 'inquiry_email', $email, $inquiry_id );
    update_field( 'inquiry_phone', $phone, $inquiry_id );
    update_post_meta( $inquiry_id, '_inquiry_phone_country', $phone_country );
    update_field( 'inquiry_company', $company, $inquiry_id );
    update_field( 'inquiry_product', $product, $inquiry_id );
    update_field( 'inquiry_message', $message, $inquiry_id );

    // Send email notification to admin
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf(
        '[Wenzhou Zhengyuan] %s',
        $product ? sprintf( ew__('New inquiry: %s'), $product ) : ew__('New inquiry')
    );
    $email_body = sprintf(
        "Name: %s\nEmail: %s\nPhone/WhatsApp: %s\nCompany: %s\nProduct: %s\n\nMessage:\n%s",
        $name, $email, $phone, $company ?: 'N/A', $product ?: 'General', $message
    );
    wp_mail( $admin_email, $subject, $email_body );

    // Send auto-reply to customer
    $auto_reply_subject = ew__('Thank you for your inquiry — Wenzhou Zhengyuan Electronics Co., Ltd.');
    $auto_reply_body = sprintf(
        "Dear %s,\n\nThank you for your inquiry. We have received your message and will get back to you within 24 hours.\n\nBest regards,\nWenzhou Zhengyuan Electronics Co., Ltd.",
        $name
    );
    wp_mail( $email, $auto_reply_subject, $auto_reply_body );

    // Push to DingTalk group robot (best-effort, non-blocking to form result)
    ew_dingtalk_push_inquiry( $name, $email, $phone, $company, $product, $message );
    error_log( '[ew-inquiry] DingTalk push fired for #' . $inquiry_id );

    wp_send_json_success( array(
        'message' => ew__('Thank you! We have received your inquiry and will get back to you within 24 hours.'),
    ) );
}

/**
 * 推送新询盘通知到钉钉群机器人（实时）。
 * 支持自定义机器人三种安全设置：无、自定义关键词、加签。
 */
function ew_dingtalk_push_inquiry( $name, $email, $phone, $company, $product, $message ) {
    $webhook = defined( 'CHINYOO_DINGTALK_WEBHOOK' ) && CHINYOO_DINGTALK_WEBHOOK ? CHINYOO_DINGTALK_WEBHOOK : '';
    if ( empty( $webhook ) ) {
        return; // 未配置，静默跳过
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

    // 注意：钉钉机器人自定义关键词为「新客户」，消息必须包含该词
    $content = '### 📩 新客户询盘通知' . "\n\n"
        . '**姓名：** ' . $name . "\n"
        . '**邮箱：** ' . $email . "\n"
        . '**电话/WhatsApp：** ' . $phone . "\n"
        . '**公司：** ' . ( $company ? $company : '—' ) . "\n"
        . '**产品：** ' . ( $product ? $product : 'General' ) . "\n\n"
        . '**留言：**' . "\n" . $message . "\n\n"
        . '**时间：** ' . date( 'Y-m-d H:i:s' ) . "\n"
        . '**后台：** ' . admin_url( 'edit.php?post_type=inquiry' );

    $payload = array(
        'msgtype'  => 'markdown',
        'markdown' => array(
            'title' => '新客户询盘通知',
            'text'  => $content,
        ),
    );

    $response = wp_remote_post( $url, array(
        'body'    => wp_json_encode( $payload ),
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        error_log( 'DingTalk push failed: ' . $response->get_error_message() );
        return;
    }

    // 校验钉钉返回（可选日志）
    $body = wp_remote_retrieve_body( $response );
    if ( $body ) {
        $decoded = json_decode( $body, true );
        if ( isset( $decoded['errcode'] ) && (int) $decoded['errcode'] !== 0 ) {
            error_log( 'DingTalk push error: ' . ( isset( $decoded['errmsg'] ) ? $decoded['errmsg'] : $body ) );
        }
    }
}

/**
 * 防垃圾询盘检测：蜜罐字段 / 提交频率 / 留言内容。命中任意一条即视为机器人。
 * 策略为"无感拦截"：调用方应返回与成功一致的消息，不创建询盘、不发邮件、不推送。
 * 覆盖刚上线的独立站被群发脚本/钓鱼询盘轰炸的场景。
 *
 * @param array $p $_POST 原始数据。
 * @return bool true = 判定为垃圾询盘，应丢弃。
 */
function ew_inquiry_is_spam( $p ) {
    // 总开关：wp-config.php 中 CHINYOO_SPAM_FILTER = false 可整体关闭防垃圾（临时排障/上线初期用）。
    // 关闭后所有提交全量放行（仍走入库 + 邮件 + 钉钉）。恢复时把该常量改回 true 或删掉即可。
    if ( defined( 'CHINYOO_SPAM_FILTER' ) && ! CHINYOO_SPAM_FILTER ) {
        return false;
    }

    // 丢弃原因统一写日志（生产排障用），IP 一并记录。
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

    // 1) 蜜罐字段：对真人完全隐藏（CSS 移出视口），只有机器人会填。
    if ( ! empty( $p['inquiry_website'] ) ) {
        error_log( '[ew-inquiry] SPAM-DROP honeypot filled | IP ' . $ip );
        return true;
    }

    // 2) 频率限制：同一 IP 5 分钟内最多提交 10 次询盘（防批量轰炸）。
    //    放宽到 10 次，避免连测多语言的真人被误杀。CLI/cron 下无 IP 时跳过。
    if ( $ip ) {
        $key = 'ew_inq_cnt_' . md5( $ip );
        $cnt = (int) get_transient( $key );
        if ( $cnt >= 10 ) {
            error_log( '[ew-inquiry] SPAM-DROP rate limit ' . $cnt . ' | IP ' . $ip );
            return true;
        }
        set_transient( $key, $cnt + 1, 5 * MINUTE_IN_SECONDS );
    }

    // 3) 留言内容：纯数字 / 纯符号（真买家会用文字描述需求）。
    //    例如机器人留言 "5499826890"。
    //    用 \p{L}（任意 Unicode 字母）判定，而非仅英文+中文——
    //    否则俄语(西里尔)/西语(重音)/阿拉伯语真实询盘会被误杀（站内 5 语言含 RU/AR/ES）。
    $message = isset( $p['inquiry_message'] ) ? (string) $p['inquiry_message'] : '';
    if ( $message !== '' && ! preg_match( '/\p{L}/u', $message ) ) {
        error_log( '[ew-inquiry] SPAM-DROP no letters in message | IP ' . $ip );
        return true;
    }

    // 4) 留言含 HTML / 脚本标签（垃圾 / 注入特征）。
    if ( preg_match( '/<[a-z\/!]/i', $message ) ) {
        error_log( '[ew-inquiry] SPAM-DROP html tag in message | IP ' . $ip );
        return true;
    }

    return false;
}
