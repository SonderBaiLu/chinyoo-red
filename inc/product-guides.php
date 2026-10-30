<?php
/**
 * Product → Guide 映射（产品页出链到同语言指南文章）。
 *
 * 目标：给 Google 一条稳定的发现通道。Google 会周期抓产品页(product-sitemap3
 * 是它唯一在反复拉的 sitemap)，产品页带上指向指南文章的链接后，每抓一次产品页
 * 就能顺着摸到新文章——补上"sitemap 不重抓 + IndexNow 不达 Google"时缺失的发现环。
 *
 * 用法：single-product.php 调 ew_product_guide_links($product_id) 拿
 * ['url'=>, 'title'=>] 列表（当前语言），渲染 product-guide-banner。
 *
 * 映射键 = 默认语言(EN)产品 slug；值 = 指南文章 EN slug。
 * 新增指南文章/上架新模型 → 在这张表补一行即可，无需改模板。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 产品 EN slug → 指南文章 EN slug 列表。
 */
function ew_product_guide_map() {
	return array(
		// 数字万用表
		'156_a830l-digital-multimeter'  => array( 'a830l-digital-multimeter-guide' ),
		'dt700b'  => array( 'dt700-digital-multimeter-guide' ),
		'dt830d'  => array( 'dt830-digital-multimeter-guide' ),
		'dt9205a' => array( 'dt9205a-digital-multimeter-guide' ),
		// 批次 4/5 的单品指南此前没有任何产品指向，长期停在 Discovered、lastCrawl 为空。
		// 2026-10-23 补登记（每型号 ≤3 条，single-product.php 逐条渲染 banner）。
		'vc17-series'   => array( 'vc17-digital-multimeter-guide' ),
		'vc9805' => array( 'vc9805-digital-multimeter-guide' ),
		'154_st105-digital-multimeter'  => array( 'st105-digital-multimeter-guide' ),
		// 批次 7（2026-10-22）：汽车电瓶三篇（怎么测 / 选哪个 / 启动压降）。
		// 2026-10-23 起每型号把自家单品指南放第一位，让位给「启动压降」那篇
		// （它仍由另两篇的 Related Guides 与分类页兜住），总数保持 3 条不越线。
		'155_dt6222-digital-multimeter'            => array( 'dt6222-digital-multimeter-guide', 'multimeter-for-electricians', 'test-car-battery-with-multimeter' ),
		// 2026-10-29 批次 6：《电工选表》直接对比 XT802 / DT6222，《价格构成》承接采购意图。
		// 为守住每条目 ≤3 条，两条汽车电瓶让位（仍由 dt33c / dt6222 与文章级 Related Guides 兜住）。
		'xt802' => array( 'xt802-digital-multimeter-guide', 'multimeter-for-electricians', 'multimeter-price-factory-vs-retail' ),
		'dt33c'              => array( 'dt33-digital-multimeter-guide', 'test-car-battery-with-multimeter', 'multimeter-for-car-battery' ),
		// 钳形表
		'266c'                     => array( '266-series-digital-clamp-meter', 'clamp-meter-how-to-use' ),
		'130_xt266-digital-clamp-meter'                   => array( '266-series-digital-clamp-meter', 'clamp-meter-how-to-use' ),
		'vc3268'                  => array( 'vc3268-digital-clamp-meter-guide', 'clamp-meter-how-to-use' ),
		'vc3269'     => array( 'clamp-meter-how-to-use' ),
		'vc3270'     => array( 'clamp-meter-how-to-use' ),
		// 电压测试笔
		'88-2-digital-voltage-tester'      => array( '88-2-voltage-test-pen-guide' ),
		'6877-58-series'   => array( '6877-58-induction-voltage-tester-guide' ),
		'6885-48-series'   => array( '6885-48-digital-voltage-tester-guide' ),
		'1881-series'      => array( '1881-digital-inductive-voltage-tester' ),
		'vd06-series'      => array( 'voltage-tester-vs-multimeter' ),
		// 插座测试 / 温度 / 红外 / 电池
		'socket-tester-series'       => array( 'outlet-tester-guide', 'gfi-vs-gfci' ),
		'ht9269-multi-thermometer'   => array( 'ht9269-multi-channel-thermometer-guide' ),
		'htc-2-temperature-meter'    => array( 'htc-2-temperature-humidity-meter-guide' ),
		'gm320'                      => array( 'infrared-thermometer-guide' ),
		'bt-168-series'              => array( 'battery-and-temperature-testers-guide' ),
		'battery-meter-series'       => array( 'battery-and-temperature-testers-guide' ),
		// 汽车电笔 → 汽车电气指南（主题匹配，非单品指南）
		'hw170-series'   => array( 'automotive-electrical-testing-guide', 'multimeter-for-automotive-guide' ),
		'hw65-series'    => array( 'automotive-electrical-testing-guide' ),
		'hw68-series'    => array( 'automotive-electrical-testing-guide' ),
		'hw304-series'   => array( 'automotive-electrical-testing-guide' ),
		'hw305-series'   => array( 'automotive-electrical-testing-guide' ),
		'hw306-series'   => array( 'automotive-electrical-testing-guide' ),
		'hw307-series'   => array( 'automotive-electrical-testing-guide' ),
		'hw308-series'   => array( 'automotive-electrical-testing-guide' ),
		'hw309-series'   => array( 'automotive-electrical-testing-guide' ),
		'hw310-series'   => array( 'automotive-electrical-testing-guide' ),
		'wilk302-series' => array( 'automotive-electrical-testing-guide' ),
		'wilk303-series' => array( 'automotive-electrical-testing-guide' ),
		'wilk301-series' => array( 'automotive-electrical-testing-guide' ),
	);
}

/**
 * 解析某产品（任意语言）对应的指南文章链接。
 *
 * @param int $product_id 当前语言产品 ID（single-product.php 里 $product->get_id()）。
 * @return array[] ['url'=>当前语言文章链接, 'title'=>当前语言文章标题]
 */
function ew_product_guide_links( $product_id ) {
	$links = array();
	$pid   = absint( $product_id );
	if ( ! $pid ) {
		return $links;
	}
	// 映射键是默认语言(EN)产品 slug：先拿到当前产品对应的 EN 产品 ID。
	$en_id = function_exists( 'pll_get_post' ) ? pll_get_post( $pid, 'en' ) : 0;
	if ( ! $en_id ) {
		$en_id = $pid;
	}
	$slug  = get_post_field( 'post_name', absint( $en_id ) );
	$map   = ew_product_guide_map();
	if ( '' === $slug || ! isset( $map[ $slug ] ) ) {
		return $links;
	}
	foreach ( (array) $map[ $slug ] as $guide_slug ) {
		// 拿指南文章 EN 帖。lang=>'' 仅绕过前端 Polylang 当前语言过滤——正确性依赖
		// 翻译 slug 必带语言后缀（{en-slug}-zh/-es/-ru/-ar）这一不变量，使 EN slug 全局唯一；
		// 否则同名 slug 跨语言共享时 get_posts 可能返回非 EN 帖。
		$g = get_posts( array(
			'name'        => $guide_slug,
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 1,
			'lang'        => '',
		) );
		if ( empty( $g ) ) {
			continue;
		}
		$tr = function_exists( 'pll_get_post' ) ? pll_get_post( $g[0]->ID ) : $g[0]->ID;
		// 当前语言译文须已发布，否则草稿/预览链接会漏到前端。
		if ( ! $tr || 'publish' !== get_post_status( $tr ) ) {
			continue;
		}
		$url = get_permalink( $tr );
		if ( $url ) {
			$links[] = array(
				'url'   => $url,
				'title' => get_the_title( $tr ),
			);
		}
	}
	return $links;
}

/**
 * 指南块当前语言的 UI 文案（避免引入 Polylang 字符串注册）。
 */
function ew_product_guide_label() {
	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : 'en';
	$map  = array(
		'en' => array( 'eyebrow' => 'Product Guide', 'cta' => 'Read the Guide →' ),
		'zh' => array( 'eyebrow' => '产品指南', 'cta' => '阅读指南 →' ),
		'es' => array( 'eyebrow' => 'Guía del producto', 'cta' => 'Leer la guía →' ),
		'ru' => array( 'eyebrow' => 'Руководство по продукту', 'cta' => 'Читать руководство →' ),
		'ar' => array( 'eyebrow' => 'دليل المنتج', 'cta' => 'اقرأ الدليل ←' ),
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : $map['en'];
}

/**
 * 分类页 → 指南文章（第二条 Google 已确认抓取的通道）。
 *
 * 组合两条来源:
 * 1) ew_category_guide_map() 直连映射(手工精选的通用/导购指南,覆盖无产品映射的类别);
 * 2) 该分类下产品经 ew_product_guide_map() 反向聚合出的单品指南。
 * 直连优先(通用指南更像分类 hub 页的内容),去重、翻当前语言、封顶 5 条。
 *
 * @param int $term_id 当前语言 product_cat term ID(archive-product.php 里 get_queried_object()->term_id)。
 * @return array[] ['url'=>当前语言文章链接, 'title'=>当前语言文章标题]
 */
function ew_category_guide_map() {
	return array(
		// 数字万用表 hub + 通用导购
		'digital-multimeters' => array( 'how-to-choose-a-digital-multimeter', 'multimeter-symbols-guide', 'how-to-use-a-multimeter', 'cat-ratings-explained' ),
		'basic-digital-multimeters' => array( 'factory-vs-trading-company-buy-digital-multimeters' ),
		'professional-digital-multimeters' => array( 'choose-multimeter-manufacturer-china' ),
		// 电压测试笔:basic 24 款产品无单品指南,通用指南兜底
		'voltage-testers' => array( 'voltage-testers-guide-types-and-safety' ),
		'basic-voltage-testers' => array( 'voltage-testers-guide-types-and-safety', 'voltage-tester-vs-multimeter' ),
		'digital-voltage-testers' => array( 'voltage-tester-vs-multimeter' ),
		'inductive-alarm-testers' => array( 'voltage-testers-guide-types-and-safety' ),
		// 钳形表 hub
		'clamp-meters' => array( 'how-to-choose-a-clamp-meter', 'clamp-meter-vs-digital-multimeter' ),
		// 插座 / 电池
		'socket-testers' => array( 'home-wiring-safety-socket-tester' ),
		'battery-meters' => array( 'battery-and-temperature-testers-guide' ),
	);
}

/**
 * 解析某分类页对应的指南文章链接(当前语言)。
 *
 * @param int $term_id 当前语言 product_cat term ID。
 * @return array[] ['url'=>, 'title'=>] 列表,最多 5 条。
 */
function ew_category_guide_links( $term_id ) {
	$links   = array();
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return $links;
	}
	// 直连映射键是 EN 分类 slug:先把当前语言 term 翻到 EN term。
	$en_term = function_exists( 'pll_get_term' ) ? pll_get_term( $term_id, 'en' ) : 0;
	if ( ! $en_term ) {
		$en_term = $term_id;
	}
	$en_term_obj = get_term( absint( $en_term ), 'product_cat' );
	if ( is_wp_error( $en_term_obj ) || ! $en_term_obj ) {
		return $links;
	}
	$en_slug = $en_term_obj->slug;

	$guide_slugs = array();

	// 1) 直连映射优先(手工精选,通用指南更像 hub 页内容)。直连已≥4 则不再混单品指南,
	//    hub 页保持稳定策展;否则补反向聚合(该分类含子分类下产品的单品指南)。
	$cmap = ew_category_guide_map();
	if ( isset( $cmap[ $en_slug ] ) ) {
		$guide_slugs = array_merge( $guide_slugs, (array) $cmap[ $en_slug ] );
	}
	if ( count( $guide_slugs ) < 4 ) {
		// 返回完整对象(勿用 fields=>'ids':WP 7.x 不 prime post 缓存,循环内 get_post_field
		// 会逐条 N+1 查库);只取 post_name 不需要 meta/term 缓存。
		$prods = get_posts( array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'numberposts'            => -1,
			'lang'                   => 'en',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => absint( $en_term ) ) ),
		) );
		$map = ew_product_guide_map();
		foreach ( $prods as $prod ) {
			if ( '' === $prod->post_name || ! isset( $map[ $prod->post_name ] ) ) {
				continue;
			}
			$guide_slugs = array_merge( $guide_slugs, (array) $map[ $prod->post_name ] );
		}
	}

	// 2) 去重 → 逐条解析当前语言(publish 校验)→ 最后才封顶,避免高优先级占位
	//    缺译文时静默漏掉第 6+ 条可用指南。
	$guide_slugs = array_values( array_unique( $guide_slugs ) );
	foreach ( $guide_slugs as $gslug ) {
		$g = get_posts( array(
			'name'        => $gslug,
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 1,
			'lang'        => '',
		) );
		if ( empty( $g ) ) {
			continue;
		}
		$tr = function_exists( 'pll_get_post' ) ? pll_get_post( $g[0]->ID ) : $g[0]->ID;
		if ( ! $tr || 'publish' !== get_post_status( $tr ) ) {
			continue;
		}
		$url = get_permalink( $tr );
		if ( $url ) {
			$links[] = array(
				'url'   => $url,
				'title' => get_the_title( $tr ),
			);
		}
		if ( count( $links ) >= 5 ) {
			break;
		}
	}
	return $links;
}

/**
 * 分类页 Related Guides 标题(当前语言)。
 */
function ew_category_guide_label() {
	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : 'en';
	$map  = array(
		'en' => 'Related Guides',
		'zh' => '相关指南',
		'es' => 'Guías relacionadas',
		'ru' => 'Связанные руководства',
		'ar' => 'أدلة ذات صلة',
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : $map['en'];
}

/* ============================================================
   文章页 → 相关指南 + 相关产品（第三条内链通道）
   ------------------------------------------------------------
   single-product.php 走「产品 → 指南」，archive-product.php 走「分类 → 指南」，
   两者都只往产品侧灌链接。文章页(single.php)此前**零出链**——实测全站 877 个
   内容页里 656 个零内部入链，正文零出链的英文对象 150/177。抓取需求靠内链
   喂，所以在 article 侧补这一环。

   文章没有 topical taxonomy（无分类法可用），聚类只能复用上面两张映射表反推：
   guide → product → product_cat，以及 guide → product_cat 直连；两者都空时
   退回 slug 关键词。全部在 EN 上推导，最后统一翻当前语言——与
   ew_product_guide_links() / ew_category_guide_links() 同一套约定。
   ============================================================ */

/**
 * 聚类索引：分类 ↔ 指南 ↔ 产品，一次请求内 static 缓存。
 *
 * 返回 array(
 *   'cats'  => [ product_cat EN slug => [ guide EN slug => true ] ],
 *   'prods' => [ product_cat EN slug => [ product EN slug => true ] ],
 *   'g2p'   => [ guide EN slug       => [ product EN slug => true ] ],
 * )
 *
 * 产品→分类必须**批量**取：115 个产品逐条 wp_get_post_terms() 实测 236ms
 * （N+1），换成一次 wp_get_object_terms( $ids, ... ) 后降到个位数毫秒。
 */
function ew_guide_cluster_index() {
	static $idx = null;
	if ( null !== $idx ) {
		return $idx;
	}
	$idx  = array( 'cats' => array(), 'prods' => array(), 'g2p' => array() );
	$pmap = ew_product_guide_map();

	// 1) 分类直连映射（与 ew_category_guide_links() 第 1 步同源）
	foreach ( ew_category_guide_map() as $cat_slug => $guides ) {
		foreach ( (array) $guides as $g ) {
			$idx['cats'][ $cat_slug ][ $g ] = true;
		}
	}

	// 2) 反推 guide → product
	foreach ( $pmap as $prod_slug => $guides ) {
		foreach ( (array) $guides as $g ) {
			$idx['g2p'][ $g ][ $prod_slug ] = true;
		}
	}

	// 3) product → product_cat → 指南（与 ew_category_guide_links() 第 2 步同源）
	$prods = get_posts( array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'numberposts'            => -1,
		'lang'                   => 'en',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );
	if ( empty( $prods ) ) {
		return $idx;
	}
	$rows = wp_get_object_terms( wp_list_pluck( $prods, 'ID' ), 'product_cat', array( 'fields' => 'all_with_object_id' ) );
	$p2c  = array();
	if ( ! is_wp_error( $rows ) ) {
		foreach ( $rows as $t ) {
			// wp_get_object_terms 受 Polylang 当前语言过滤，返回的可能是译文 term；
			// 统一经 pll_get_term(...,'en') 归一到 EN 再取 slug，映射键才对得上。
			$en  = function_exists( 'pll_get_term' ) ? pll_get_term( $t->term_id, 'en' ) : 0;
			$obj = get_term( absint( $en ? $en : $t->term_id ), 'product_cat' );
			if ( $obj && ! is_wp_error( $obj ) && ! empty( $obj->slug ) ) {
				$p2c[ $t->object_id ][ $obj->slug ] = true;
			}
		}
	}

	// 分类树(EN)：slug => 父 slug。产品要向上挂到祖先分类——
	// digital-multimeters 这类 hub 自身不挂产品(count=0)，产品全在子分类里；
	// 不向上传播，该 hub 下的文章就拿不到任何产品链接(实测 dt33 / vc9805 / st105 /
	// vc890 / new-6000-count 等 7 篇数字万用表文章 P=0)。
	$tree = array();
	foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $t ) {
		$en  = function_exists( 'pll_get_term' ) ? pll_get_term( $t->term_id, 'en' ) : 0;
		$obj = get_term( absint( $en ? $en : $t->term_id ), 'product_cat' );
		if ( ! $obj || is_wp_error( $obj ) || empty( $obj->slug ) ) {
			continue;
		}
		$pslug = '';
		if ( $obj->parent ) {
			$pen  = function_exists( 'pll_get_term' ) ? pll_get_term( $obj->parent, 'en' ) : 0;
			$pobj = get_term( absint( $pen ? $pen : $obj->parent ), 'product_cat' );
			if ( $pobj && ! is_wp_error( $pobj ) && ! empty( $pobj->slug ) ) {
				$pslug = $pobj->slug;
			}
		}
		$tree[ $obj->slug ] = $pslug;
	}

	foreach ( $prods as $p ) {
		if ( '' === $p->post_name || ! isset( $pmap[ $p->post_name ] ) || empty( $p2c[ $p->ID ] ) ) {
			continue;
		}
		foreach ( array_keys( $p2c[ $p->ID ] ) as $cat_slug ) {
			$chain = array( $cat_slug );
			$up    = $cat_slug;
			$guard = 0;
			while ( $guard++ < 5 && ! empty( $tree[ $up ] ) ) {
				$up      = $tree[ $up ];
				$chain[] = $up;
			}
			foreach ( $chain as $c ) {
				$idx['prods'][ $c ][ $p->post_name ] = true;
				foreach ( (array) $pmap[ $p->post_name ] as $g ) {
					$idx['cats'][ $c ][ $g ] = true;
				}
			}
		}
	}
	return $idx;
}

/**
 * 标题不需要额外处理。
 *
 * get_the_title() 与分类名都过了 wptexturize，撇号已是 HTML 实体(&#8217;)；
 * 看着像会被 esc_html() 二次编码，其实不会——WP 的 esc_html() 走 _wp_specialchars()，
 * 它对 htmlspecialchars 传的是 $double_encode = false，已有实体原样保留。
 * &#8217; 就是 U+2019，模板里照常写 esc_html( get_the_title() ) 即可。
 * （PHP 裸用 htmlspecialchars 才是默认 double_encode=true，会变成 &amp;#8217;。）
 */

/**
 * 文章 EN slug → 所属聚类分类 EN slug 列表。
 */
function ew_post_related_cats( $slug ) {
	$cats = array();
	foreach ( ew_guide_cluster_index()['cats'] as $cat_slug => $guides ) {
		if ( isset( $guides[ $slug ] ) ) {
			$cats[] = $cat_slug;
		}
	}

	// 兜底：未进任何映射表的通用文章，按 slug 关键词归位。
	if ( empty( $cats ) ) {
		$kw = array(
			'clamp-meter'    => 'clamp-meters',
			'voltage-tester' => 'voltage-testers',
			'test-pen'       => 'voltage-testers',
			'multimeter'     => 'digital-multimeters',
			'battery'        => 'battery-meters',
			'outlet'         => 'socket-testers',
			'socket'         => 'socket-testers',
		);
		foreach ( $kw as $needle => $cat_slug ) {
			if ( false !== strpos( $slug, $needle ) ) {
				$cats[] = $cat_slug;
			}
		}
	}
	return array_values( array_unique( $cats ) );
}

/**
 * EN slug 列表 → 当前语言链接列表（publish 校验 + 封顶）。
 *
 * 与 ew_product_guide_links() 同款：lang=>'' 绕过前端 Polylang 当前语言过滤，
 * 正确性依赖「译文 slug 必带语言后缀，使 EN slug 全局唯一」这一不变量；
 * 译文未发布则跳过，避免草稿/预览链接漏到前端。
 */
function ew_resolve_post_slugs( $slugs, $cap, $post_type = 'post' ) {
	$links = array();
	$cap   = absint( $cap );
	if ( empty( $slugs ) || $cap < 1 ) {
		return $links;
	}
	foreach ( $slugs as $s ) {
		$g = get_posts( array(
			'name'        => $s,
			'post_type'   => $post_type,
			'post_status' => 'publish',
			'numberposts' => 1,
			'lang'        => '',
		) );
		if ( empty( $g ) ) {
			continue;
		}
		$tr = function_exists( 'pll_get_post' ) ? pll_get_post( $g[0]->ID ) : $g[0]->ID;
		if ( ! $tr || 'publish' !== get_post_status( $tr ) ) {
			continue;
		}
		$url = get_permalink( $tr );
		if ( ! $url ) {
			continue;
		}
		$links[] = array(
			'url'   => $url,
			'title' => get_the_title( $tr ),
		);
		if ( count( $links ) >= $cap ) {
			break;
		}
	}
	return $links;
}

/**
 * 兜底 hub 链接：当前语言的一级产品分类归档页。
 *
 * 给「与产品毫无关系的公司新闻」用（实测 2 篇：曼谷展会、20 周年），
 * 免得它们仍然是纯死胡同。get_terms 由 Polylang 按当前语言过滤，天然语言正确。
 */
function ew_related_hub_links( $cap = 3 ) {
	$links = array();
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'number'     => absint( $cap ),
	) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $links;
	}
	foreach ( $terms as $t ) {
		$url = get_term_link( $t );
		if ( is_wp_error( $url ) || ! $url ) {
			continue;
		}
		$links[] = array(
			'url'   => $url,
			'title' => $t->name,
		);
	}
	return $links;
}

/**
 * 文章页相关链接（当前语言）。single.php 调用。
 *
 * @param int $post_id 当前文章 ID（任意语言）。
 * @return array ['guides'=>[], 'products'=>[], 'hubs'=>[]]
 *               guides 最多 4 条，products 最多 3 条，hubs 仅在两者合计 <2 时出现。
 */
function ew_post_related_links( $post_id ) {
	$out = array( 'guides' => array(), 'products' => array(), 'hubs' => array() );
	$pid = absint( $post_id );
	if ( ! $pid ) {
		return $out;
	}
	// 映射键是 EN slug：先翻到 EN 再推导。
	$en_id = function_exists( 'pll_get_post' ) ? pll_get_post( $pid, 'en' ) : 0;
	if ( ! $en_id ) {
		$en_id = $pid;
	}
	$slug = get_post_field( 'post_name', absint( $en_id ) );
	if ( '' === $slug ) {
		return $out;
	}

	$idx  = ew_guide_cluster_index();
	$cats = ew_post_related_cats( $slug );

	// 指南：同聚类下除自己以外的全部
	$g_slugs = array();
	foreach ( $cats as $c ) {
		if ( isset( $idx['cats'][ $c ] ) ) {
			$g_slugs = array_merge( $g_slugs, array_keys( $idx['cats'][ $c ] ) );
		}
	}
	$g_slugs = array_values( array_diff( array_unique( $g_slugs ), array( $slug ) ) );

	// 产品：文章直讲的那些型号优先（最相关），再补聚类内其它产品
	$p_slugs = array();
	if ( isset( $idx['g2p'][ $slug ] ) ) {
		$p_slugs = array_merge( $p_slugs, array_keys( $idx['g2p'][ $slug ] ) );
	}
	foreach ( $cats as $c ) {
		if ( isset( $idx['prods'][ $c ] ) ) {
			$p_slugs = array_merge( $p_slugs, array_keys( $idx['prods'][ $c ] ) );
		}
	}
	$p_slugs = array_values( array_unique( $p_slugs ) );

	$out['guides']   = ew_resolve_post_slugs( $g_slugs, 4, 'post' );
	$out['products'] = ew_resolve_post_slugs( $p_slugs, 3, 'product' );

	if ( count( $out['guides'] ) + count( $out['products'] ) < 2 ) {
		$out['hubs'] = ew_related_hub_links( 3 );
	}
	return $out;
}

/**
 * 文章页相关链接的小标题（当前语言）。
 */
function ew_post_related_label() {
	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : 'en';
	$map  = array(
		'en' => array( 'guides' => 'Related Guides', 'products' => 'Related Products', 'hubs' => 'Browse Product Categories' ),
		'zh' => array( 'guides' => '相关指南', 'products' => '相关产品', 'hubs' => '浏览产品分类' ),
		'es' => array( 'guides' => 'Guías relacionadas', 'products' => 'Productos relacionados', 'hubs' => 'Ver categorías de productos' ),
		'ru' => array( 'guides' => 'Связанные руководства', 'products' => 'Связанные товары', 'hubs' => 'Категории товаров' ),
		'ar' => array( 'guides' => 'أدلة ذات صلة', 'products' => 'منتجات ذات صلة', 'hubs' => 'تصفح فئات المنتجات' ),
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : $map['en'];
}
