<?php
/**
 * Scenarios — 应用场景定义(Applications & Solutions 区块 + 场景页共享数据源)
 *
 * 每个场景按 `_model_applications` postmeta 的关键词做 OR 匹配，自动拉出适用产品。
 * 场景页 slug = /solutions/{slug}/。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ew_scenarios() {
    return array(
        'electrical-maintenance' => array(
            'title'    => 'Electrical Maintenance',
            'sub'      => 'Voltage, continuity and wiring checks on live electrical systems.',
            'keywords' => array( 'wiring', 'circuit', 'maintenance', 'electrician', 'continuity', 'voltage' ),
            'img'      => 2186, // 封面: scen-electrical-maintenance.webp
        ),
        'automotive-testing'     => array(
            'title'    => 'Automotive & Vehicle',
            'sub'      => '12V / 24V car circuit tracing, battery and alternator checks.',
            'keywords' => array( 'automotive', 'car', 'vehicle', 'motorcycle', 'truck', 'battery' ),
            'img'      => 2187, // 封面: scen-automotive-testing.webp
        ),
        'home-diy'               => array(
            'title'    => 'Home & DIY',
            'sub'      => 'Socket wiring, battery and everyday electrical safety checks.',
            'keywords' => array( 'home', 'household', 'socket', 'outlet', 'battery charge', 'daily' ),
            'img'      => 2188, // 封面: scen-home-diy.webp
        ),
        'hvac-temperature'       => array(
            'title'    => 'HVAC & Temperature',
            'sub'      => 'Refrigeration, heating and ambient temperature measurement.',
            'keywords' => array( 'hvac', 'refrigeration', 'temperature', 'heating', 'cooling', 'air conditioning' ),
            'img'      => 2189, // 封面: scen-hvac-temperature.webp
        ),
        'industrial-facility'    => array(
            'title'    => 'Industrial & Facility',
            'sub'      => 'Non-contact current measurement for panels, motors and machines.',
            'keywords' => array( 'industrial', 'factory', 'machine', 'motor', 'panel', 'clamp' ),
            'img'      => 2190, // 封面: scen-industrial-facility.webp
        ),
        'led-appliance'          => array(
            'title'    => 'LED & Appliance Repair',
            'sub'      => 'Live / neutral identification and quick fault-finding on lamps and appliances.',
            'keywords' => array( 'led', 'lighting', 'appliance', 'lamp' ),
            'img'      => 2191, // 封面: scen-led-appliance.webp
        ),
    );
}

function ew_scenario_by_slug( $slug ) {
    $all = ew_scenarios();
    return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
}

/**
 * 场景 URL：/solutions/{slug}/
 */
function ew_scenario_url( $slug ) {
    return ew_page_url( 'solutions/' . $slug );
}

/**
 * 按场景关键词从 `_model_applications` 拉出适用产品 ID。
 * $limit = 0 表示全部；>0 取前 N 个。
 *
 * 关键词是英文，而 `_model_applications` meta 在非 EN 语言下是译文文本，
 * 因此始终在默认语言(EN)上做关键词匹配，再把命中的产品 ID 映射为当前语言翻译，
 * 保证场景页产品卡指向本地化产品页。无翻译的产品保持 EN 原 ID。
 */
function ew_scenario_product_ids( $slug, $limit = 0 ) {
	$sc = ew_scenario_by_slug( $slug );
	if ( ! $sc ) {
		return array();
	}
	$meta_q = array( 'relation' => 'OR' );
	foreach ( $sc['keywords'] as $kw ) {
		$meta_q[] = array(
			'key'     => '_model_applications',
			'value'   => $kw,
			'compare' => 'LIKE',
		);
	}
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'posts_per_page' => $limit ? $limit : -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => $meta_q,
		'lang'           => pll_default_language(), // 关键词基于默认语言文本
	);
	$ids = get_posts( $args );

	// 非默认语言：EN 命中 → 当前语言翻译。
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang && $lang !== pll_default_language() ) {
			foreach ( $ids as $i => $id ) {
				$tr = pll_get_post( $id, $lang );
				if ( $tr ) {
					$ids[ $i ] = (int) $tr;
				}
			}
		}
	}
	return $ids;
}

/**
 * 场景页 SEO title（短格式）。返回完整 <title> 字符串（含品牌），覆盖默认 title。
 * 供 pre_get_document_title 使用；非场景 slug 返回空串。
 */
function ew_scenario_seo_title( $slug ) {
    $titles = array(
        'electrical-maintenance' => 'Electrical Maintenance',
        'automotive-testing'     => 'Automotive & Vehicle',
        'home-diy'               => 'Home & DIY',
        'hvac-temperature'       => 'HVAC & Temperature',
        'industrial-facility'    => 'Industrial & Facility',
        'led-appliance'          => 'LED & Appliance Repair',
    );
    if ( isset( $titles[ $slug ] ) ) {
        // 场景名按当前语言翻译，品牌后缀 CHINYOO 固定不译。
        return ew_esc_html__( $titles[ $slug ] ) . ' | CHINYOO';
    }
    return '';
}
