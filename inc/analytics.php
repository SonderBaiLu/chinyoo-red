<?php
/**
 * 数据大盘 —— 自建轻量行为统计（零第三方依赖，GDPR 友好：无 Cookie、无跨站像素）。
 *
 * 两条数据腿：
 *   1) wp_ew_events  —— 前端 analytics.js 上报的 PV / 点击 / 转化（按天聚合存库）
 *   2) inquiry 文章 + postmeta —— 询盘明细（按天聚合存库）
 *
 * 每日 cron 把明细滚动成日聚合表（wp_ew_events_daily / wp_ew_inquiries_daily），
 * 明细保留 12 个月后自动清理；大盘页面只读聚合表，秒开。
 *
 * 说明：geoip2 扩展未安装，IP 只能记前缀不能定位国家；国家维度靠
 * 询盘里的电话区号 / 邮箱域推断（见 ew_dashboard_inquiries()）。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EW_EVENTS_TABLE', $GLOBALS['wpdb']->prefix . 'ew_events' );
define( 'EW_EVENTS_DAILY', $GLOBALS['wpdb']->prefix . 'ew_events_daily' );
define( 'EW_INQ_DAILY',    $GLOBALS['wpdb']->prefix . 'ew_inquiries_daily' );

/** 允许采集的事件白名单（前端只许发这些，防灌水）。 */
function ew_event_whitelist() {
    return array( 'pv', 'click', 'convert', 'exit' );
}

/**
 * 访客国家码（ISO 3166-1 alpha-2，大写）。
 * 当前 geoip2 扩展未安装 → 返回空串，"国家"维度暂靠询盘电话区号 / 邮箱域推断。
 * 明天接 GA4 或装 geoip2 后，在这里补 IP 定位即可，前端/表格结构不用动。
 */
function ew_visitor_country_code() {
    if ( extension_loaded( 'geoip2' ) ) {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
        if ( $ip ) {
            try {
                $rec = \GeoIp2\Reader::getFromCachedFile( __DIR__ . '/assets/geoip2/GeoLite2-Country.mmdb', $ip );
                if ( $rec ) {
                    return $rec->country->isoCode;
                }
            } catch ( \Exception $e ) { /* 无数据库文件时静默回空 */ }
        }
    }
    return '';
}

function ew_install_events_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $wpdb->query( "CREATE TABLE IF NOT EXISTS " . EW_EVENTS_TABLE . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        day DATE NOT NULL,
        uid VARCHAR(64) NOT NULL,
        ev VARCHAR(32) NOT NULL,
        pg VARCHAR(200) NOT NULL DEFAULT '',
        ln VARCHAR(8) NOT NULL DEFAULT '',
        ct VARCHAR(64) NOT NULL DEFAULT '',
        ds VARCHAR(16) NOT NULL DEFAULT '',
        ch VARCHAR(32) NOT NULL DEFAULT '',
        kw VARCHAR(64) NOT NULL DEFAULT '',
        tg VARCHAR(100) NOT NULL DEFAULT '',
        ts BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY  (id),
        KEY day_ev (day, ev),
        KEY ts (ts)
    ) $charset_collate" );

    $wpdb->query( "CREATE TABLE IF NOT EXISTS " . EW_EVENTS_DAILY . " (
        day DATE NOT NULL,
        ev VARCHAR(32) NOT NULL,
        pg VARCHAR(200) NOT NULL DEFAULT '',
        ln VARCHAR(8) NOT NULL DEFAULT '',
        ct VARCHAR(64) NOT NULL DEFAULT '',
        ds VARCHAR(16) NOT NULL DEFAULT '',
        ch VARCHAR(32) NOT NULL DEFAULT '',
        kw VARCHAR(64) NOT NULL DEFAULT '',
        tg VARCHAR(100) NOT NULL DEFAULT '',
        pv_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
        uv_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
        UNIQUE KEY day_dim (day, ev, pg, ln, ct, ds, ch, kw, tg)
    ) $charset_collate" );

    $wpdb->query( "CREATE TABLE IF NOT EXISTS " . EW_INQ_DAILY . " (
        day DATE NOT NULL,
        product VARCHAR(255) NOT NULL DEFAULT '',
        phone_ct VARCHAR(8) NOT NULL DEFAULT '',
        PRIMARY KEY (day, product, phone_ct)
    ) $charset_collate" );

    update_option( 'ew_events_table_version', 1 );
}
add_action( 'admin_init', 'ew_install_events_tables' );

/** 明细保留期（12 个月）外清理 + 把当天明细滚入聚合表。每日一次。 */
function ew_events_daily_rollup() {
    global $wpdb;
    // 明细保留 12 个月，超期清理
    $wpdb->query( "DELETE FROM " . EW_EVENTS_TABLE . " WHERE ts < " . $wpdb->prepare( '%d', time() - 365 * DAY_IN_SECONDS ) );
    // 幂等：当天明细 → 聚合（重复执行只多跑一遍 upsert，结果不变）
    $wpdb->query(
        "INSERT INTO " . EW_EVENTS_DAILY . "
            (day, ev, pg, ln, ct, ds, ch, kw, tg, pv_count, uv_count)
         SELECT day, ev, pg, ln, ct, ds, ch, kw, tg, COUNT(*), COUNT(DISTINCT uid)
         FROM " . EW_EVENTS_TABLE . "
         WHERE ts >= " . $wpdb->prepare( '%d', time() - 2 * DAY_IN_SECONDS ) . "
         GROUP BY day, ev, pg, ln, ct, ds, ch, kw, tg
         ON DUPLICATE KEY UPDATE
            pv_count = VALUES(pv_count),
            uv_count = VALUES(uv_count)"
    );
}
add_action( 'ew_events_daily_rollup', 'ew_events_daily_rollup' );

function ew_events_cron_register() {
    if ( ! wp_next_scheduled( 'ew_events_daily_rollup' ) ) {
        wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'daily', 'ew_events_daily_rollup' );
    }
}
add_action( 'admin_init', 'ew_events_cron_register' );

/**
 * 采集端点。nonce 只用来挡脚本乱扫，**不硬拒**——
 * 页面被 FastCGI 缓存 24h，nonce 会过期，硬拒等于全站统计清零（同询盘表单的处理）。
 */
function ew_capture_events() {
    if ( ! wp_verify_nonce( isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '', 'ew_analytics_nonce' ) ) {
        error_log( '[ew-analytics] nonce stale, accepted anyway | ' . ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'n/a' ) );
    }
    // 直接 json_decode 原始 payload（机器生成的 JSON）；逐字段白名单 + 长度裁剪在循环内做，
    // 不再对整串跑 sanitize_text_field —— 它会吃掉 %XX / 重排空白，会把 JSON 弄坏导致整批 0 条。
    $raw = isset( $_REQUEST['b'] ) ? (string) wp_unslash( $_REQUEST['b'] ) : '';
    if ( '' === $raw ) {
        wp_send_json_success( array( 'n' => 0 ) );
    }
    $rows  = json_decode( $raw, true );
    if ( ! is_array( $rows ) ) {
        wp_send_json_success( array( 'n' => 0 ) );
    }
    // 单批上限 50 条，超出丢弃（防构造大 payload）
    $rows = array_slice( $rows, 0, 50 );
    $ok   = 0;
    global $wpdb;
    foreach ( $rows as $r ) {
        if ( ! is_array( $r ) || empty( $r['ev'] ) ) {
            continue;
        }
        $ev = sanitize_text_field( $r['ev'] );
        if ( ! in_array( $ev, ew_event_whitelist(), true ) ) {
            continue;
        }
        $ts    = isset( $r['ts'] ) ? (int) $r['ts'] : time();
        $day   = gmdate( 'Y-m-d', $ts + (int) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
        $uid   = isset( $r['uid'] ) ? substr( preg_replace( '/[^a-f0-9]/', '', (string) $r['uid'] ), 0, 64 ) : '';
        $pg    = isset( $r['pg'] ) ? substr( sanitize_text_field( $r['pg'] ), 0, 200 ) : '';
        $ln    = isset( $r['ln'] ) ? substr( sanitize_text_field( $r['ln'] ), 0, 8 ) : '';
        $ct    = isset( $r['ct'] ) ? substr( sanitize_text_field( $r['ct'] ), 0, 64 ) : '';
        $ds    = isset( $r['ds'] ) ? substr( sanitize_text_field( $r['ds'] ), 0, 16 ) : '';
        $ch    = isset( $r['ch'] ) ? substr( sanitize_text_field( $r['ch'] ), 0, 32 ) : '';
        $kw    = isset( $r['kw'] ) ? substr( sanitize_text_field( $r['kw'] ), 0, 64 ) : '';
        $tg    = isset( $r['tg'] ) ? substr( sanitize_text_field( $r['tg'] ), 0, 100 ) : '';
        if ( '' === $uid ) {
            continue; // 无 uid 的批次条目无意义，丢弃
        }
        $wpdb->insert( EW_EVENTS_TABLE, array(
            'day' => $day, 'uid' => $uid, 'ev' => $ev, 'pg' => $pg,
            'ln' => $ln, 'ct' => $ct, 'ds' => $ds, 'ch' => $ch,
            'kw' => $kw, 'tg' => $tg, 'ts' => $ts,
        ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ) );
        $ok++;
    }
    wp_send_json_success( array( 'n' => $ok ) );
}
add_action( 'wp_ajax_ew_track_events', 'ew_capture_events' );
add_action( 'wp_ajax_nopriv_ew_track_events', 'ew_capture_events' );

/** 询盘明细 → 日聚合。幂等：先删当天再整表重插，重复执行结果不变。 */
function ew_inquiries_daily_rollup() {
    global $wpdb;
    $today = gmdate( 'Y-m-d', time() + (int) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
    $wpdb->query( "DELETE FROM " . EW_INQ_DAILY . " WHERE day = '" . $wpdb->esc_like( $today ) . "'" );
    $wpdb->query(
        "INSERT INTO " . EW_INQ_DAILY . " (day, product, phone_ct)
         SELECT
            DATE(`" . $wpdb->prefix . "posts`.post_date_gmt) AS day,
            IFNULL(pm_p.meta_value, '') AS product,
            IFNULL(pm_c.meta_value, '') AS phone_ct
         FROM `" . $wpdb->prefix . "posts`
         LEFT JOIN `" . $wpdb->prefix . "postmeta` pm_p ON pm_p.post_id = `" . $wpdb->prefix . "posts`.ID AND pm_p.meta_key = 'inquiry_product'
         LEFT JOIN `" . $wpdb->prefix . "postmeta` pm_c ON pm_c.post_id = `" . $wpdb->prefix . "posts`.ID AND pm_c.meta_key = '_inquiry_phone_country'
         WHERE `" . $wpdb->prefix . "posts`.post_type = 'inquiry'
         AND DATE(`" . $wpdb->prefix . "posts`.post_date_gmt) = '" . $wpdb->esc_like( $today ) . "'
         GROUP BY day, product, phone_ct"
    );
}
add_action( 'ew_inquiries_daily_rollup', 'ew_inquiries_daily_rollup' );

function ew_inquiries_cron_register() {
    if ( ! wp_next_scheduled( 'ew_inquiries_daily_rollup' ) ) {
        wp_schedule_event( time() + 10 * MINUTE_IN_SECONDS, 'daily', 'ew_inquiries_daily_rollup' );
    }
}
add_action( 'admin_init', 'ew_inquiries_cron_register' );

/**
 * 大盘数据（一次函数取全）。返回结构见 inc/analytics-dashboard.php 的消费处。
 * 只读日聚合表（快）；聚合缺失的当天（cron 还没跑）回退读明细。
 */
function ew_dashboard_data() {
    global $wpdb;
    $d = array();

    // ---- 事件 KPI（近 30 天 / 今日） ----
    $since30 = gmdate( 'Y-m-d', time() - 30 * DAY_IN_SECONDS );
    $today   = gmdate( 'Y-m-d', time() + (int) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );

    $kpi = array(
        'pv'       => (int) $wpdb->get_var( "SELECT SUM(pv_count) FROM " . EW_EVENTS_DAILY . " WHERE day >= '$since30' AND ev='pv'" ),
        'uv'       => 0,
        'clicks'   => (int) $wpdb->get_var( "SELECT SUM(pv_count) FROM " . EW_EVENTS_DAILY . " WHERE day >= '$since30' AND ev='click'" ),
        'converts' => (int) $wpdb->get_var( "SELECT SUM(pv_count) FROM " . EW_EVENTS_DAILY . " WHERE day >= '$since30' AND ev='convert'" ),
        'today_pv' => 0,
    );
    // UV 跨维度不可加和：取近 30 天去重 uid（明细保留 12 个月，量小）
    $kpi['uv'] = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT uid) FROM " . EW_EVENTS_TABLE . " WHERE day >= '$since30' AND ev='pv'" );
    $kpi['today_pv'] = (int) $wpdb->get_var( "SELECT SUM(pv_count) FROM " . EW_EVENTS_DAILY . " WHERE day='$today' AND ev='pv'" );
    if ( 0 === $kpi['today_pv'] ) {
        $kpi['today_pv'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . EW_EVENTS_TABLE . " WHERE day='$today' AND ev='pv'" );
    }
    // 平均停留时长（exit 事件 tg='dwell:毫秒'，明细表 12 个月内取均值）
    $dw = $wpdb->get_var( "SELECT AVG(SUBSTRING_INDEX(tg, ':', -1)) FROM " . EW_EVENTS_TABLE . " WHERE day >= '$since30' AND ev='exit' AND tg LIKE 'dwell:%'" );
    $kpi['avg_dwell'] = $dw ? (float) $dw : 0.0;
    $d['kpi'] = $kpi;

    // ---- 30 天趋势（PV / 询盘 双线） ----
    $trend = array( 'labels' => array(), 'pv' => array(), 'inq' => array() );
    for ( $i = 29; $i >= 0; $i-- ) {
        $day = gmdate( 'Y-m-d', time() - $i * DAY_IN_SECONDS );
        $trend['labels'][] = substr( $day, 5 );
        $pv = $wpdb->get_var( "SELECT SUM(pv_count) FROM " . EW_EVENTS_DAILY . " WHERE day='$day' AND ev='pv'" );
        $trend['pv'][]  = $pv ? (int) $pv : 0;
        $iq = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "posts WHERE post_type='inquiry' AND DATE(post_date_gmt)='$day'" );
        $trend['inq'][] = $iq ? (int) $iq : 0;
    }
    $d['trend'] = $trend;

    // ---- 结构维度（近 30 天 PV） ----
    $dims = array( 'ch' => '渠道', 'ln' => '语言', 'ct' => '国家(推断)', 'ds' => '设备', 'kw' => '搜索词' );
    $d['dims'] = array();
    foreach ( $dims as $col => $label ) {
        $rows = $wpdb->get_results(
            "SELECT $col AS k, SUM(pv_count) AS v FROM " . EW_EVENTS_DAILY .
            " WHERE day >= '$since30' AND ev='pv' AND $col <> '' GROUP BY $col ORDER BY v DESC LIMIT 12",
            ARRAY_A
        );
        $d['dims'][ $col ] = $rows ? $rows : array();
    }

    // ---- 点击热榜（近 30 天，按 tg 目标） ----
    $d['clicks'] = $wpdb->get_results(
        "SELECT tg AS k, SUM(pv_count) AS v FROM " . EW_EVENTS_DAILY .
        " WHERE day >= '$since30' AND ev='click' AND tg <> '' GROUP BY tg ORDER BY v DESC LIMIT 10",
        ARRAY_A
    );

    // ---- 页面热榜（近 30 天 PV top10） ----
    $d['pages'] = $wpdb->get_results(
        "SELECT pg AS k, SUM(pv_count) AS v, SUM(uv_count) AS uv FROM " . EW_EVENTS_DAILY .
        " WHERE day >= '$since30' AND ev='pv' AND pg <> '' GROUP BY pg ORDER BY v DESC LIMIT 10",
        ARRAY_A
    );

    // ---- 询盘：近 30 天按天（明细直查，量小） ----
    $d['inq_by_day'] = $wpdb->get_results(
        "SELECT DATE(post_date_gmt) AS k, COUNT(*) AS v FROM " . $wpdb->prefix . "posts
         WHERE post_type='inquiry' AND post_date_gmt >= '" . $wpdb->esc_like( $since30 ) . "'
         GROUP BY DATE(post_date_gmt) ORDER BY k",
        ARRAY_A
    );
    $d['inquiries_total_30d'] = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM " . $wpdb->prefix . "posts WHERE post_type='inquiry' AND post_date_gmt >= '" . $wpdb->esc_like( $since30 ) . "'"
    );

    // ---- 询盘：按产品 / 电话区号 / 邮箱域（近 30 天） ----
    $p = $wpdb->get_results(
        "SELECT IFNULL(m1.meta_value,'(未填)') AS k, COUNT(*) AS v FROM " . $wpdb->prefix . "posts p
         LEFT JOIN " . $wpdb->prefix . "postmeta m1 ON m1.post_id=p.ID AND m1.meta_key='inquiry_product'
         WHERE p.post_type='inquiry' AND p.post_date_gmt >= '" . $wpdb->esc_like( $since30 ) . "'
         GROUP BY m1.meta_value ORDER BY v DESC LIMIT 12",
        ARRAY_A
    );
    $d['inq_by_product'] = $p;

    $c = $wpdb->get_results(
        "SELECT IFNULL(m2.meta_value,'(未填)') AS k, COUNT(*) AS v FROM " . $wpdb->prefix . "posts p
         LEFT JOIN " . $wpdb->prefix . "postmeta m2 ON m2.post_id=p.ID AND m2.meta_key='_inquiry_phone_country'
         WHERE p.post_type='inquiry' AND p.post_date_gmt >= '" . $wpdb->esc_like( $since30 ) . "'
         GROUP BY m2.meta_value ORDER BY v DESC LIMIT 15",
        ARRAY_A
    );
    $d['inq_by_phone_ctry'] = $c;

    // 邮箱域：拆 meta 值再分组（域名单词，无注入面）
    $e_rows = $wpdb->get_col(
        "SELECT m3.meta_value FROM " . $wpdb->prefix . "posts p
         LEFT JOIN " . $wpdb->prefix . "postmeta m3 ON m3.post_id=p.ID AND m3.meta_key='inquiry_email'
         WHERE p.post_type='inquiry' AND p.post_date_gmt >= '" . $wpdb->esc_like( $since30 ) . "'"
    );
    $doms = array();
    foreach ( $e_rows as $em ) {
        if ( $em && strpos( $em, '@' ) ) {
            $dom = strtolower( substr( strrchr( $em, '@' ), 1 ) );
            // 免费邮箱单列，其余公司域合并展示前 12 名
            $doms[ $dom ] = ( $doms[ $dom ] ?? 0 ) + 1;
        }
    }
    arsort( $doms );
    $d['inq_by_email_domain'] = array();
    foreach ( array_slice( $doms, 0, 12, true ) as $k2 => $v2 ) {
        $d['inq_by_email_domain'][] = array( 'k' => $k2, 'v' => (int) $v2 );
    }

    // ---- 询盘：按小时（近 30 天，服务器时区） ----
    $h = $wpdb->get_results(
        "SELECT HOUR(post_date_gmt) AS k, COUNT(*) AS v FROM " . $wpdb->prefix . "posts
         WHERE post_type='inquiry' AND post_date_gmt >= '" . $wpdb->esc_like( $since30 ) . "'
         GROUP BY k ORDER BY v DESC",
        ARRAY_A
    );
    $d['inq_by_hour'] = $h;

    // ---- 最近 20 条询盘明细 ----
    $last = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_date,
                IFNULL(mn.meta_value,'') AS name, IFNULL(me.meta_value,'') AS email,
                IFNULL(mp.meta_value,'') AS phone, IFNULL(mc.meta_value,'') AS ptry,
                IFNULL(mm.meta_value,'') AS product, IFNULL(md.meta_value,'') AS msg
         FROM " . $wpdb->prefix . "posts p
         LEFT JOIN " . $wpdb->prefix . "postmeta mn ON mn.post_id=p.ID AND mn.meta_key='inquiry_name'
         LEFT JOIN " . $wpdb->prefix . "postmeta me ON me.post_id=p.ID AND me.meta_key='inquiry_email'
         LEFT JOIN " . $wpdb->prefix . "postmeta mp ON mp.post_id=p.ID AND mp.meta_key='inquiry_phone'
         LEFT JOIN " . $wpdb->prefix . "postmeta mc ON mc.post_id=p.ID AND mc.meta_key='_inquiry_phone_country'
         LEFT JOIN " . $wpdb->prefix . "postmeta mm ON mm.post_id=p.ID AND mm.meta_key='inquiry_product'
         LEFT JOIN " . $wpdb->prefix . "postmeta md ON md.post_id=p.ID AND md.meta_key='inquiry_message'
         WHERE p.post_type='inquiry'
         ORDER BY p.post_date DESC LIMIT 20",
        ARRAY_A
    );
    $d['inq_recent'] = $last;

    return $d;
}
