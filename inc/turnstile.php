<?php
// Cloudflare Turnstile 人机验证。站点密钥可公开；保密密钥只从 wp-config.php 读取。

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EW_TURNSTILE_SITE_KEY', defined( 'CHINYOO_TURNSTILE_SITE_KEY' ) ? CHINYOO_TURNSTILE_SITE_KEY : '0x4AAAAAAFejtRQF0ygtO6Em' );

// wp-config.php 没配保密密钥时视为未启用：本地环境、临时关闭都走这条。
function ew_turnstile_enabled() {
    return defined( 'CHINYOO_TURNSTILE_SECRET' ) && '' !== CHINYOO_TURNSTILE_SECRET;
}

// 通过返回 true。未启用、或 Cloudflare 接口不可达时放行，避免误杀真实询盘。
function ew_turnstile_verify( $token, $ip = '' ) {
    if ( ! ew_turnstile_enabled() ) {
        return true;
    }

    $token = is_string( $token ) ? trim( $token ) : '';
    if ( '' === $token ) {
        return false;
    }

    $body = array(
        'secret'   => CHINYOO_TURNSTILE_SECRET,
        'response' => $token,
    );
    if ( $ip ) {
        $body['remoteip'] = $ip;
    }

    // 固定官方地址，不接收外部传入的 URL。
    $response = wp_remote_post(
        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        array(
            'body'    => $body,
            'timeout' => 10,
        )
    );

    if ( is_wp_error( $response ) ) {
        error_log( '[ew-turnstile] siteverify 请求失败，放行 | ' . $response->get_error_message() );
        return true;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! empty( $data['success'] ) ) {
        return true;
    }

    $codes = isset( $data['error-codes'] ) ? implode( ',', (array) $data['error-codes'] ) : 'unknown';
    error_log( '[ew-turnstile] 校验未通过 ' . $codes . ' | IP ' . $ip );
    return false;
}
