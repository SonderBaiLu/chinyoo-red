<?php
/**
 * CHINYOO — Theme Functions
 *
 * @package electrical_world
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EW_THEME_VERSION', '1.0.0' );
define( 'EW_THEME_DIR', get_template_directory() );
define( 'EW_THEME_URI', get_template_directory_uri() );

// Load core modules
require_once EW_THEME_DIR . '/inc/i18n.php'; // Polylang string helpers + registration (first: others call ew_*)
require_once EW_THEME_DIR . '/inc/theme-setup.php';
require_once EW_THEME_DIR . '/inc/enqueue.php';
require_once EW_THEME_DIR . '/inc/custom-post-types.php';
require_once EW_THEME_DIR . '/inc/custom-fields.php';
require_once EW_THEME_DIR . '/inc/product-editor.php';
require_once EW_THEME_DIR . '/inc/inquiry-handler.php';
require_once EW_THEME_DIR . '/inc/chinyoo-filter.php';
require_once EW_THEME_DIR . '/inc/scenarios.php';
require_once EW_THEME_DIR . '/inc/scenario-content.php';
require_once EW_THEME_DIR . '/inc/geo-faq.php'; // 产品页 FAQPage 数据 + 解析器(GEO)
require_once EW_THEME_DIR . '/inc/product-guides.php'; // 产品页 → 指南文章 出链映射(发现通道)

// --- Performance: Remove bloat ---
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
// RSS feed links 保留(automatic-feed-links 在 theme-setup 启用):不 remove feed_links/feed_links_extra
add_image_size('product-card', 400, 0, false);


// --- Mobile/WeChat detection ---
add_filter("body_class", function($classes) {
    $ua = $_SERVER["HTTP_USER_AGENT"] ?? "";
    if (preg_match("/MicroMessenger|Android|iPhone|iPad|Mobile/i", $ua)) {
        $classes[] = "is-mobile";
    }
    return $classes;
});

// --- 语言感知 URL 助手 ---
// 不能直接用 home_url('/slug/')：Polylang 的 home_url 过滤只翻译"不带路径"的首页 URL，
// 带路径的 home_url() 永远原样返回、不加语言前缀。要生成当前语言的页面/分类链接必须走 permalink。
function ew_page_url( $slug ) {
    if ( function_exists( 'pll_get_post' ) ) {
        $en = get_page_by_path( $slug ); // 默认语言页（slug 跨语言一致）
        if ( $en ) {
            $tr = pll_get_post( $en->ID ); // 当前语言的翻译页
            if ( $tr ) {
                $url = get_permalink( $tr );
                if ( $url ) {
                    return $url;
                }
            }
        }
    }
    return home_url( '/' . $slug . '/' );
}

function ew_term_url( $slug, $taxonomy = 'product_cat' ) {
    // 前端 get_terms 会被 Polylang 过滤到当前语言,而翻译分类的 slug 带语言后缀
    // (digital-multimeters-zh 等),用 EN slug 查会扑空。传 lang=>'' 拿到默认语言分类再取翻译。
    $en_terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'slug'       => $slug,
        'hide_empty' => false,
        'lang'       => '',
        'number'     => 1,
    ) );
    if ( ! is_wp_error( $en_terms ) && ! empty( $en_terms ) ) {
        $term = $en_terms[0];
        if ( function_exists( 'pll_get_term' ) ) {
            $tr = pll_get_term( $term->term_id ); // 当前语言的分类翻译
            if ( $tr ) {
                $tr_term = get_term( $tr, $taxonomy );
                if ( $tr_term && ! is_wp_error( $tr_term ) ) {
                    $term = $tr_term;
                }
            }
        }
        $url = get_term_link( $term );
        if ( ! is_wp_error( $url ) ) {
            return $url;
        }
    }
    return home_url( '/' . $taxonomy . '/' . $slug . '/' );
}

function ew_post_url( $slug, $post_type = 'post' ) {
    // 与 ew_page_url 同理：home_url('/slug/') 带路径不加语言前缀，必须走 permalink。
    // get_posts 用 lang=>'' 拿到默认语言(EN)的帖子,再 pll_get_post 翻到当前语言。
    if ( function_exists( 'pll_get_post' ) ) {
        $posts = get_posts( array(
            'name'        => $slug,
            'post_type'   => $post_type,
            'post_status' => 'publish',
            'numberposts' => 1,
            'lang'        => '',
        ) );
        if ( ! empty( $posts ) ) {
            $tr = pll_get_post( $posts[0]->ID );
            if ( $tr ) {
                $url = get_permalink( $tr );
                if ( $url ) {
                    return $url;
                }
            }
        }
    }
    return home_url( '/' . $slug . '/' );
}

// --- Header nav fallback（唯一导航渲染路径） ---
// 说明：Polylang 的菜单模型是"每语言一个菜单"，需在后台按语言建菜单并填入 nav_menus 选项。
// 本主题刻意不用这套——菜单项与 URL 都是按语言硬编码生成的（与 footer 一致），
// 这样 header 在 5 种语言下自动输出翻译后的标签 + 语言感知链接，无需维护 5 份菜单。
function ew_header_menu_fallback( $args = array() ) {
    $menu_class = ( isset( $args['menu_class'] ) && $args['menu_class'] ) ? $args['menu_class'] : 'main-menu';
    $items = array(
        array(
            'label' => 'Products',
            'url'   => ew_page_url( 'product-categories' ),
            'mega'  => true,
        ),
        array(
            'label' => 'Solutions',
            'url'   => ew_page_url( 'solutions' ),
            'sub'   => array(
                array( 'Electrical Maintenance', ew_page_url( 'solutions/electrical-maintenance' ) ),
                array( 'Automotive & Vehicle',   ew_page_url( 'solutions/automotive-testing' ) ),
                array( 'Industrial & Facility',  ew_page_url( 'solutions/industrial-facility' ) ),
                array( 'Home & DIY',             ew_page_url( 'solutions/home-diy' ) ),
                array( 'HVAC & Temperature',     ew_page_url( 'solutions/hvac-temperature' ) ),
                array( 'LED & Appliance Repair', ew_page_url( 'solutions/led-appliance' ) ),
            ),
        ),
        array(
            'label' => 'Services & Support',
            'url'   => ew_page_url( 'support' ),
            'sub'   => array(
                array( 'Downloads',     ew_page_url( 'downloads' ) ),
                array( 'FAQs',          ew_page_url( 'faqs' ) ),
                array( 'OEM / ODM',     ew_page_url( 'oem' ) ),
                array( 'Where to Buy',  ew_page_url( 'contact-us' ) ),
            ),
        ),
        array(
            'label' => 'About Us',
            'url'   => ew_page_url( 'about-us' ),
            'sub'   => array(
                array( 'Our Factory',    ew_page_url( 'about-us' ) ),
                array( 'Certifications', ew_page_url( 'certifications' ) ),
                array( 'Blogs',          ew_page_url( 'news' ) ),
            ),
        ),
        array(
            'label' => 'Contact Us',
            'url'   => ew_page_url( 'contact-us' ),
        ),
    );
    echo '<ul class="' . esc_attr( $menu_class ) . '">';
    foreach ( $items as $it ) {
        $cls = array( 'menu-item' );
        if ( ! empty( $it['mega'] ) ) {
            $cls[] = 'mega-products';
        }
        echo '<li class="' . esc_attr( implode( ' ', $cls ) ) . '">';
        echo '<a href="' . esc_url( $it['url'] ) . '">' . ew_esc_html__( $it['label'] ) . '</a>';
        if ( ! empty( $it['mega'] ) ) {
            echo ew_build_mega_tree();
        } elseif ( ! empty( $it['sub'] ) ) {
            echo '<ul class="sub-menu">';
            foreach ( $it['sub'] as $sub ) {
                echo '<li><a href="' . esc_url( $sub[1] ) . '">' . ew_esc_html__( $sub[0] ) . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

// --- Mobile nav toggle ---
add_action('wp_footer', function () {
    echo '<script>(function(){var t=document.getElementById("mobile-nav-toggle"),p=document.getElementById("mobile-nav-panel");if(t&&p){t.addEventListener("click",function(){var open=p.classList.toggle("open");t.setAttribute("aria-expanded",open?"true":"false");});}}());</script>';
});

// --- Language switcher toggle + outside click close ---
add_action('wp_footer', function () {
    echo '<script>(function(){var w=document.getElementById("lang-switcher");if(!w)return;var b=w.querySelector(".lang-switcher__btn");if(!b)return;b.addEventListener("click",function(e){e.stopPropagation();var open=w.classList.toggle("is-open");b.setAttribute("aria-expanded",open?"true":"false");});document.addEventListener("click",function(e){if(!w.contains(e.target)){w.classList.remove("is-open");b.setAttribute("aria-expanded","false");}});document.addEventListener("keydown",function(e){if(e.key==="Escape"){w.classList.remove("is-open");b.setAttribute("aria-expanded","false");}});}());</script>';
});

// --- B2B: this is an inquiry-driven catalog, not an online shop ---
// Products carry no price, and templates render Inquiry CTAs instead of
// "Add to cart". Cart/checkout/my-account are leftovers — redirect them to the
// inquiry contact page so no e-commerce surface leaks into the front end.
// 语言感知：返回当前语言的联系页 URL（而非硬编码英文 /contact-us/）。
function ew_contact_url() {
    $en = get_page_by_path( 'contact-us' );
    $id = $en ? $en->ID : 0;
    if ( $id && function_exists( 'pll_get_post' ) ) {
        $tr = pll_get_post( $id ); // 当前语言的联系页翻译。
        if ( $tr ) {
            $url = get_permalink( $tr );
            if ( $url ) {
                return $url;
            }
        }
    }
    return home_url( '/contact-us/' );
}

add_action('template_redirect', function () {
    // 用 slug 而非 is_cart()/is_checkout()/is_account_page()：后者只匹配默认语言页 ID，
    // zh/es/ru/ar 的购物车/结算/账户页不会被拦截。slug 跨语言一致。
    if ( ! is_page() ) {
        return;
    }
    $slug = get_post_field( 'post_name', get_queried_object_id() );
    if ( in_array( $slug, array( 'cart', 'checkout', 'my-account' ), true ) ) {
        wp_safe_redirect( ew_contact_url(), 301 );
        exit;
    }
});

// --- 场景页 SEO title：短格式 "场景名 | CHINYOO"，覆盖默认 title ---
// 优先级 30 需高于 Rank Math 的 pre_get_document_title (15)，否则被其覆盖
add_filter( 'pre_get_document_title', function ( $title ) {
    if ( is_singular() ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $seo  = ew_scenario_seo_title( $slug );
        if ( $seo ) {
            return $seo;
        }
    }
    return $title;
}, 30 );

// --- 钳形表分类合并：digital-clamp-meters → clamp-meters (301) ---
// 15 款钳形表曾同时挂在两个分类，父子页内容完全相同(重复内容)。
// 已统一到 clamp-meters；此钩子把各语言 digital-clamp-meters* 链接 301 到对应语言 clamp-meters。
add_action( 'template_redirect', function () {
    if ( is_tax( 'product_cat' ) ) {
        $q = get_queried_object();
        if ( $q && 0 === strpos( $q->slug, 'digital-clamp-meters' ) ) {
            $cm_slug = 'clamp-meters' . substr( $q->slug, strlen( 'digital-clamp-meters' ) );
            $cm      = get_term_by( 'slug', $cm_slug, 'product_cat' );
            if ( ! $cm || is_wp_error( $cm ) ) {
                $cm = get_term_by( 'slug', 'clamp-meters', 'product_cat' );
            }
            if ( $cm ) {
                wp_safe_redirect( get_term_link( $cm ), 301 );
                exit;
            }
        }
    }
} );

// --- 本地化 meta description（Rank Math 无描述时的兜底） ---
// 场景页/联系页/分类页的 description 模板未配置（auto_description 关），
// 这里在 rank_math/frontend/description 上补一段本地化描述。其余页面交给 Rank Math。
add_filter( 'rank_math/frontend/description', function ( $desc ) {
	if ( is_singular() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$sc   = ew_scenario_by_slug( $slug );
		if ( $sc ) {
			return ew_esc_html__( $sc['title'] ) . ' — ' . ew_esc_html__( $sc['sub'] );
		}
		if ( 'contact-us' === $slug && '' === trim( (string) $desc ) ) {
			return ew_esc_html__( 'Send us a message — our engineers reply within 24 hours.' );
		}
		if ( 'about-us' === $slug && '' === trim( (string) $desc ) ) {
			return ew_esc_html__( "A high-tech manufacturer of electrical measuring tools in Wenzhou — China's electrical hardware capital." );
		}
	}
	if ( is_tax( 'product_cat' ) && '' === trim( (string) $desc ) ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			return $term->name . ' — ' . ew_esc_html__( 'Factory-direct electrical measuring instruments — digital multimeters, voltage testers, clamp meters and special test tools.' );
		}
	}
	return $desc;
}, 20 );

// --- 首页 SEO title/description：非默认语言直接覆盖 ---
// Rank Math 的 %sitedesc% 只解析单一英文 blogdescription（无每语言翻译），
// 且 titles 选项的每语言 homepage 值曾被部署脚本打平，这里对非 EN 首页直接覆盖。
// EN 首页保持 Rank Math 模板（英文 tagline，站点名统一为 CHINYOO）。
// 非 EN：<title> 用 ew_home_seo_title()（品牌+简短定位，与 EN 首页一致），
//        meta description 仍用 ew_home_tagline()（本地化长句更适合描述）。
function ew_home_tagline() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	$map  = array(
		'zh' => '位于中国温州的专业电工测量仪表工厂 —— 数字万用表、电压测试仪、钳形表及更多。',
		'es' => 'Fábrica profesional de instrumentos de medición eléctrica en Wenzhou, China — multímetros digitales, detectores de voltaje, pinzas amperimétricas y más.',
		'ru' => 'Профессиональная фабрика электроизмерительных приборов в Вэньчжоу, Китай — цифровые мультиметры, тестеры напряжения, токовые клещи и многое другое.',
		'ar' => 'مصنع محترف لأجهزة القياس الكهربائية في ونزو، الصين — أجهزة القياس المتعددة الرقمية وأجهزة اختبار الجهد ومقاييس المشبك والمزيد.',
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : '';
}

// --- 首页 SEO title（非默认语言）：统一 "品牌 + 简短定位"，与 EN 首页一致 ---
function ew_home_seo_title() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	$map  = array(
		'zh' => 'CHINYOO — 温州专业电工测量仪表制造商',
		'es' => 'CHINYOO — Fabricante profesional de instrumentos de medición eléctrica en Wenzhou',
		'ru' => 'CHINYOO — Профессиональный производитель электроизмерительных приборов в Вэньчжоу',
		'ar' => 'CHINYOO — مصنع محترف لأجهزة القياس الكهربائية في ونزو',
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : '';
}

function ew_is_non_default_home() {
	if ( ! is_front_page() || is_admin() ) {
		return false;
	}
	$default = function_exists( 'pll_default_language' ) ? pll_default_language() : 'en';
	$lang    = function_exists( 'pll_current_language' ) ? pll_current_language() : $default;
	return $lang && $lang !== $default;
}

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ew_is_non_default_home() ) {
		$seo = ew_home_seo_title();
		if ( $seo ) {
			return $seo;
		}
	} elseif ( is_front_page() && ! is_admin() ) {
		// EN 首页：短标题（原 Rank Math 模板 75 字符，SERP 截断）。
		return 'CHINYOO — Electrical Measuring Instruments Manufacturer';
	}
	return $title;
}, 40 );

add_filter( 'rank_math/frontend/description', function ( $desc ) {
	if ( ew_is_non_default_home() ) {
		$tagline = ew_home_tagline();
		if ( $tagline ) {
			return $tagline;
		}
	}
	return $desc;
}, 30 );

// --- WebP 优先服务（性能）：附件图存在同尺寸 .webp 副本时优先用 WebP（原图保留为回退） ---
// 副本由生产端 PHP GD 批量生成（uploads 下同名 .webp，全尺寸覆盖）。
add_filter( 'wp_get_attachment_image_src', 'ew_webp_image_src', 10, 4 );
function ew_webp_image_src( $image, $attachment_id, $size, $icon ) {
	if ( $image && ! empty( $image[0] ) ) {
		$image[0] = ew_webp_swap( $image[0] );
	}
	return $image;
}
add_filter( 'wp_calculate_image_srcset', 'ew_webp_srcset', 10, 5 );
function ew_webp_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	if ( is_array( $sources ) ) {
		foreach ( $sources as $i => $src ) {
			$sources[ $i ]['url'] = ew_webp_swap( $src['url'] );
		}
	}
	return $sources;
}
// srcset 生成器要求 src 与 meta 里的文件名匹配（src-match）。src 已被换成
// .webp 而 meta 的 file 仍是 .png/.jpg，导致永不匹配 → srcset 直接返回 false。
// 注入一个与 webp src 同名（同尺寸）的虚拟 meta 条目，让 src-match 命中。
add_filter( 'wp_calculate_image_srcset_meta', 'ew_webp_srcset_meta', 10, 4 );
function ew_webp_srcset_meta( $image_meta, $size_array, $image_src, $attachment_id ) {
	if ( ! is_array( $image_meta ) || empty( $image_meta['file'] ) ) {
		return $image_meta;
	}
	if ( ! is_string( $image_src ) || false === strpos( $image_src, '.webp' ) ) {
		return $image_meta;
	}
	$uploads = wp_upload_dir();
	$rel     = str_replace( trailingslashit( $uploads['baseurl'] ), '', $image_src );
	if ( false === strpos( $rel, '/' ) ) {
		return $image_meta;
	}
	$image_meta['sizes']['ew_webp_src'] = array(
		'width'  => isset( $size_array[0] ) ? (int) $size_array[0] : 0,
		'height' => isset( $size_array[1] ) ? (int) $size_array[1] : 0,
		'file'   => basename( $rel ),
	);
	return $image_meta;
}
/**
 * 若同尺寸 .webp 副本存在则换 webp URL，否则保留原 URL（避免 404）。
 */
function ew_webp_swap( $url ) {
	if ( ! is_string( $url ) || false === strpos( $url, '/wp-content/uploads/' ) ) {
		return $url;
	}
	$webp = preg_replace( '~\.(png|jpe?g)$~i', '.webp', $url );
	if ( ! $webp || $webp === $url ) {
		return $url;
	}
	static $cache = array();
	if ( isset( $cache[ $webp ] ) ) {
		return $cache[ $webp ] ? $webp : $url;
	}
	$uploads = wp_upload_dir();
	$path    = str_replace( $uploads['baseurl'], $uploads['basedir'], $webp );
	$ok      = is_string( $path ) && is_file( $path );
	$cache[ $webp ] = $ok;
	return $ok ? $webp : $url;
}

// 当前 Polylang 语言 → BCP47 值（供 JSON-LD inLanguage / Rank Math schema/language 过滤器使用）。
function ew_schema_lang() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	$map  = array(
		'en' => 'en-US',
		'zh' => 'zh-Hans',
		'es' => 'es-ES',
		'ru' => 'ru-RU',
		'ar' => 'ar',
	);
	return isset( $map[ $lang ] ) ? $map[ $lang ] : 'en-US';
}

// --- WebSite JSON-LD（AI 搜索/GEO 要求；仅首页输出） ---
// 与 footer.php 的 Organization schema 组成实体图:WebSite.publisher → #organization
add_action( 'wp_head', function () {
	if ( ! is_front_page() || is_admin() ) {
		return;
	}
	$home = home_url( '/' );
	// publisher 必须指向统一 EN 根域名 Organization（pll_default_language 首页），
	// 与 footer.php 的 Organization @id 保持一致；WebSite 自身 @id/url 仍用当前语言首页。
	$ew_ws_home = ( function_exists('pll_home_url') && function_exists('pll_default_language') )
		? pll_home_url( pll_default_language() )
		: home_url('/');
	$json = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => $home . '#website',
		'url'             => $home,
		'name'            => get_bloginfo( 'name' ),
		'alternateName'   => 'Electrical World',
		'description'     => 'Professional test & measurement instrument manufacturer in Wenzhou, China — digital multimeters, clamp meters, voltage testers, and battery testers.',
		'publisher'       => array( '@id' => $ew_ws_home . '#organization' ),
		'inLanguage'      => ew_schema_lang(),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 10 );

// --- /faqs/ 归档页 FAQPage JSON-LD（全量 FAQ 结构化，供 AI 搜索抽取） ---
// 与 archive-faq.php 的可见列表同源(全部 publish 的 faq 帖子)。
add_action( 'wp_head', function () {
	if ( ! is_post_type_archive( 'faq' ) || is_admin() ) {
		return;
	}
	$faq_posts = get_posts( array(
		'post_type'      => 'faq',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );
	if ( empty( $faq_posts ) ) {
		return;
	}
	$main_entity = array();
	foreach ( $faq_posts as $faq_post ) {
		$faq_answer = trim( wp_strip_all_tags( get_post_field( 'post_content', $faq_post ) ) );
		if ( '' === $faq_answer ) {
			continue;
		}
		$main_entity[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( get_the_title( $faq_post ) ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq_answer,
				'url'   => get_permalink( $faq_post ),
			),
		);
	}
	if ( empty( $main_entity ) ) {
		return;
	}
	$faq_ld = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $main_entity,
	);
	echo '<script type="application/ld+json" class="chinyoo-faq-archive">' . wp_json_encode( $faq_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 15 );

// --- 单条 FAQ 页 FAQPage JSON-LD（GEO：单条问答页是 AI 抽取最直接的对象） ---
// 与 single-faq.php 的可见内容同源(H1 问题 + 正文答案)。
add_action( 'wp_head', function () {
	if ( ! is_singular( 'faq' ) || is_admin() ) {
		return;
	}
	$faq_id = get_queried_object_id();
	if ( ! $faq_id ) {
		return;
	}
	$faq_answer = trim( wp_strip_all_tags( get_post_field( 'post_content', $faq_id ) ) );
	if ( '' === $faq_answer ) {
		return;
	}
	$faq_ld = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(
			array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( get_the_title( $faq_id ) ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $faq_answer,
					'url'   => get_permalink( $faq_id ),
				),
			),
		),
	);
	echo '<script type="application/ld+json" class="chinyoo-faq-single">' . wp_json_encode( $faq_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 16 );

// --- 首页 FAQPage JSON-LD（GEO：AI 搜索审计检查的是首页；与 front-page.php 可见问答区块同源） ---
add_action( 'wp_head', function () {
	if ( ! is_front_page() || is_admin() ) {
		return;
	}
	$faqs = function_exists( 'ew_geo_faq' ) ? ew_geo_faq( 'general' ) : array();
	if ( empty( $faqs ) ) {
		return;
	}
	$modified = ew_home_date_modified();
	$main_entity = array();
	foreach ( $faqs as $qa ) {
		$main_entity[] = array(
			'@type'          => 'Question',
			'name'           => $qa['q'],
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa['a'] ),
		);
	}
	$faq_ld = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $main_entity,
	);
	if ( $modified ) {
		$faq_ld['dateModified'] = $modified;
	}
	echo '<script type="application/ld+json" class="chinyoo-home-faq">' . wp_json_encode( $faq_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 20 );

// --- 首页 dateModified 信号（microdata meta + FAQPage 内 dateModified） ---
// 首页是"最新文章"模式(show_on_front=posts)，无静态页 ID，取站内最近一次内容修改时间。
// transient 12h 缓存，避免每次访问都查库。
function ew_home_date_modified() {
	$modified = get_transient( 'ew_home_modified' );
	if ( false !== $modified ) {
		return $modified;
	}
	global $wpdb;
	$modified = $wpdb->get_var(
		"SELECT MAX(post_modified) FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('post','product','faq') AND post_password=''"
	);
	$modified = $modified ? date_i18n( 'Y-m-d', strtotime( $modified ) ) : '';
	set_transient( 'ew_home_modified', $modified, 12 * HOUR_IN_SECONDS );
	return $modified;
}
add_action( 'wp_head', function () {
	if ( ! is_front_page() || is_admin() ) {
		return;
	}
	$modified = ew_home_date_modified();
	if ( ! $modified ) {
		return;
	}
	echo '<meta itemprop="dateModified" content="' . esc_attr( $modified ) . '">' . "\n";
}, 5 );

// --- hreflang x-default：指向当前页的默认语言(EN)版本 ---
// PLL 的 en/zh/es/ru/ar rel=alternate 已由下方 pll_rel_hreflang_attributes 过滤(只列有翻译的语言)；
// Google 推荐的 x-default 补在最后。
add_action( 'wp_head', function () {
	$url = '';
	if ( is_front_page() ) {
		$lang = function_exists( 'pll_default_language' ) ? pll_default_language() : 'en';
		if ( function_exists( 'pll_home_url' ) ) {
			$url = pll_home_url( $lang );
		}
		if ( ! $url ) {
			$url = home_url( '/' );
		}
	} elseif ( is_singular() ) {
		$en = pll_get_post( get_queried_object_id(), pll_default_language() );
		if ( $en ) {
			$url = get_permalink( $en );
		}
	} elseif ( is_tax( 'product_cat' ) ) {
		$en = pll_get_term( get_queried_object_id(), pll_default_language() );
		if ( $en ) {
			$en_term = get_term( $en, 'product_cat' );
			if ( $en_term && ! is_wp_error( $en_term ) ) {
				$url = get_term_link( $en_term );
			}
		}
	}
	if ( $url ) {
		echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="x-default" />' . "\n";
	}
}, 12 );

// --- PLL hreflang 只列「当前对象有翻译」的语言 ---
// Polylang 在 wp_head 给每个语言输出 <link rel="alternate" hreflang>,当前对象(分类术语/文章)
// 在目标语言没有翻译时,它猜一个带语言前缀的 URL(如 /zh/faqscategory/oem-factory/)→ 404。
// 与 header.php 语言切换器同一规则:WP_Term 用 pll_get_term、WP_Post 用 pll_get_post 判定,
// 无翻译的语言从 pll_rel_hreflang_attributes(3.8.6 frontend-filters-links.php)摘掉。
// 全翻译页不受影响;只剩自身语言时 PLL 仍输出自链 alternate(Google 推荐保留)。
add_filter( 'pll_rel_hreflang_attributes', function ( $hreflangs ) {
    if ( ! is_array( $hreflangs ) || count( $hreflangs ) < 2 ) {
        return $hreflangs;
    }
    $ew_qo = get_queried_object();
    if ( ! $ew_qo || ( ! ( $ew_qo instanceof WP_Term ) && ! ( $ew_qo instanceof WP_Post ) ) ) {
        return $hreflangs;
    }
    $ew_cur = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
    foreach ( $hreflangs as $ew_lang => $ew_url ) {
        if ( 'x-default' === $ew_lang || ! $ew_lang || $ew_lang === $ew_cur ) {
            continue;
        }
        // 键即语言码(本站 zh/es/ru/ar,无区域冲突),hreflang 列表只来自 PLL 语言,直接当 slug 判翻译。
        $ew_keep = false;
        if ( $ew_qo instanceof WP_Term && function_exists( 'pll_get_term' ) ) {
            $ew_keep = (bool) pll_get_term( $ew_qo->term_id, (string) $ew_lang );
        } elseif ( $ew_qo instanceof WP_Post && function_exists( 'pll_get_post' ) ) {
            $ew_keep = (bool) pll_get_post( $ew_qo->ID, (string) $ew_lang );
        }
        if ( ! $ew_keep ) {
            unset( $hreflangs[ $ew_lang ] );
        }
    }
    return $hreflangs;
}, 20 );

// Kill the "Add to cart" button and post-add redirect on any leftover WooCommerce loop.
add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string' );
add_filter( 'woocommerce_add_to_cart_redirect', function () {
    return ew_contact_url();
} );

// --- Schema 去重：移除 Rank Math 的全局 Organization/WebSite 节点 ---
// footer.php 已手写输出更完整的 Organization schema（公司全名/地址/3 个销售联系方式/社交链接），
// 若 Rank Math 再输出 publisher(Organization)+WebSite，同一页面出现两个 Organization = 结构化数据错误。
// 保留 footer 版（B2B 信息全），去掉 Rank Math 版；Product/ItemPage 等页面级 schema 不受影响。
add_filter( 'rank_math/json_ld', function ( $data ) {
    unset( $data['publisher'] ); // Rank Math Organization 节点
    unset( $data['WebSite'] );   // Rank Math WebSite 节点
    return $data;
}, 99 );

// --- 作者 Person schema 增强(E-E-A-T):补 jobTitle / description / 真人 image ---
// Rank Math 把作者 Person 节点放在 $data['ProfilePage'](@type=Person),不是 'Person' 键,
// 所以遍历所有 @type=Person 节点增强,不受键名影响。
// 身份/简介跟随当前文章作者(ew_author_role_short/bio);有真人照片就输出 image,
// 没有则去掉默认 gravatar 神秘人像(会削弱可信度)。
add_filter( 'rank_math/json_ld', function ( $data ) {
    foreach ( $data as $k => $node ) {
        if ( is_array( $node ) && isset( $node['@type'] ) && 'Person' === $node['@type'] ) {
            $data[ $k ]['jobTitle']    = function_exists( 'ew_author_role_short' ) ? ew_author_role_short() : '';
            $data[ $k ]['description'] = function_exists( 'ew_author_bio' ) ? ew_author_bio() : '';
            $photo = function_exists( 'ew_author_photo' ) ? ew_author_photo() : '';
            if ( $photo ) {
                $data[ $k ]['image'] = array( '@id' => $photo, 'url' => $photo );
            } else {
                unset( $data[ $k ]['image'] );
            }
        }
    }
    return $data;
}, 100 );

// --- 裸 /author/ @id 重写(修复 post_author=0 页面的 schema 404,含本地化页)---
// 静态页(about/contact/oem/quote/downloads…)post_author=0 → Rank Math 解析不到作者,
// EN 页 Person @id / Article.author @id 写成裸 .../author/(404),本地化页(/zh/downloads/ 等)
// 则是裸 .../{lang}/author/(404)。主题语义作者回退=ew_author_profiles
// 首键(zhihai 创始人),把裸串统一改成该语言前缀下的真实作者归档 .../{lang}/author/zhihai/(均 200)。
// 整树递归,连 Article.author {@id} 引用一并改;带作者名的 /author/daijiewei/ 非裸串不动。
add_filter( 'rank_math/json_ld', function ( $data ) {
    $ew_profiles = function_exists( 'ew_author_profiles' ) ? ew_author_profiles() : array();
    $ew_key = ( is_array( $ew_profiles ) && ! empty( $ew_profiles ) ) ? (string) array_key_first( $ew_profiles ) : '';
    $ew_nicename = $ew_key;
    if ( $ew_key ) {
        $ew_user = get_user_by( 'login', $ew_key );
        if ( $ew_user ) {
            $ew_nicename = $ew_user->user_nicename; // 归档 URL 段用 nicename,取不到才回退 login
        }
    }
    if ( ! $ew_nicename ) {
        return $data;
    }
    // 裸 author @id:{scheme}://{host}[/{zh|es|ru|ar}]/author[/] → 补真实作者 nicename。
    // 本地化页(/zh/downloads/ 等)RM 输出的是 /zh/author/(404),本地化 author 归档 /zh/author/zhihai/ 均 200。
    $ew_fix = function ( &$node ) use ( $ew_nicename, &$ew_fix ) {
        if ( is_array( $node ) ) {
            foreach ( $node as &$v ) {
                $ew_fix( $v );
            }
            unset( $v );
        } elseif ( is_object( $node ) ) {
            foreach ( $node as &$v ) {
                $ew_fix( $v );
            }
            unset( $v );
        } elseif ( is_string( $node ) ) {
            $fixed = preg_replace( '#^((?:https?://[^/]+)(/(?:zh|es|ru|ar))?)/author/?$#', '$1/author/' . $ew_nicename . '/', $node );
            if ( $fixed !== $node ) {
                $node = $fixed;
            }
        }
    };
    $ew_fix( $data );
    return $data;
}, 110 );

// --- 分页 canonical 反序修正(Polylang 非默认语言首页分页)---
// RM get_canonical_paged 用站点根拼 /page/N 后,Polylang 又把语言后缀接到末尾 → /zh/page/2/
// 自声明 canonical/og:url/JSON-LD#webpage = /page/2/zh/(404)。三处共用 Paper::get()->get_canonical()
// (即已被本 filter 处理的值),一个 filter 全修好。
add_filter( 'rank_math/frontend/canonical', function ( $canonical ) {
    if ( ! is_paged() || ! is_home() || ! is_string( $canonical ) || '' === $canonical ) {
        return $canonical;
    }
    if ( ! function_exists( 'pll_current_language' ) || ! function_exists( 'pll_default_language' ) || ! function_exists( 'pll_home_url' ) ) {
        return $canonical;
    }
    $ew_lang = pll_current_language();
    if ( ! $ew_lang || $ew_lang === pll_default_language() ) {
        return $canonical; // 默认语言无此 bug
    }
    // 反序形态:{site}/page/N/{lang}/
    $ew_home = untrailingslashit( home_url( '/' ) );
    if ( preg_match( '#^' . preg_quote( $ew_home, '#' ) . '/page/(\d+)/' . preg_quote( $ew_lang, '#' ) . '/?$#', $canonical, $m ) ) {
        return user_trailingslashit( untrailingslashit( pll_home_url( $ew_lang ) ) . '/page/' . (int) $m[1] );
    }
    return $canonical;
}, 20 );

// --- Products 超级下拉：从 product_cat 分类库自动生成 4 列完整分类树 ---
// 不依赖手工菜单子项，新增分类/子分类会自动出现在下拉里。
// 默认分类(Uncategorized)在各语言下要一并排除——翻译分类 term_id 不同(带语言后缀)。
function ew_exclude_default_cat() {
    $id  = (int) get_option( 'default_product_cat' );
    $ids = array( $id );
    if ( $id && function_exists( 'pll_get_term_translations' ) ) {
        foreach ( (array) pll_get_term_translations( $id ) as $tid ) {
            $ids[] = (int) $tid;
        }
    }
    // 已合并的空分类 digital-clamp-meters（301→clamp-meters）：从导航/分类列表排除（含各语言翻译 term）
    // 用 SQL 无条件取 EN 主 term（get_term_by 会被当前语言过滤），再取全翻译组。
    global $wpdb;
    $merged_id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT t.term_id FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.slug = %s LIMIT 1",
        'product_cat', 'digital-clamp-meters'
    ) );
    if ( $merged_id ) {
        $ids[] = $merged_id;
        if ( function_exists( 'pll_get_term_translations' ) ) {
            foreach ( (array) pll_get_term_translations( $merged_id ) as $tid ) {
                $ids[] = (int) $tid;
            }
        }
    }
    return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * 当前语言下顶层主分类的「EN slug => 当前语言分类对象」映射。
 * 分类翻译的 slug 带语言后缀（digital-multimeters-zh 等），要按 EN 的顺序/slug 匹配
 * 就必须经由 Polylang 翻译组回映到 EN slug。EN 语言下映射即分类自身。
 * 供 footer Products 列、侧栏分类排序等使用。
 */
function ew_cats_by_en_slug() {
    $map = array();
    $cats = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'exclude'    => ew_exclude_default_cat(),
    ) );
    if ( is_wp_error( $cats ) || empty( $cats ) ) {
        return $map;
    }
    foreach ( $cats as $cat ) {
        $tr = function_exists( 'pll_get_term_translations' ) ? pll_get_term_translations( $cat->term_id ) : array();
        if ( ! empty( $tr['en'] ) ) {
            $en_term = get_term( $tr['en'] );
            if ( $en_term && ! is_wp_error( $en_term ) ) {
                $map[ $en_term->slug ] = $cat;
            }
        }
    }
    return $map;
}

/**
 * 分类产品总数（含全部子孙分类，DISTINCT 已发布产品）。
 * WP 的 term->count 只统计「直接」挂载到该分类的产品；顶级分类若产品挂在子分类下
 * （如 digital-multimeters → basic/professional），count 会错误显示 0。
 * 这里聚合整棵子树。结果按 term_id 静态缓存。
 */
function ew_cat_product_count( $term_id ) {
    global $wpdb;
    static $cache = array();
    $term_id = (int) $term_id;
    if ( isset( $cache[ $term_id ] ) ) {
        return $cache[ $term_id ];
    }
    $ids  = array( $term_id );
    $kids = get_term_children( $term_id, 'product_cat' );
    if ( is_array( $kids ) && ! empty( $kids ) ) {
        foreach ( $kids as $k ) {
            $ids[] = (int) $k;
        }
    }
    $in    = implode( ',', array_map( 'intval', $ids ) );
    $count = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT p.ID)
           FROM {$wpdb->posts} p
           JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
           JOIN {$wpdb->term_taxonomy} tt   ON tt.term_taxonomy_id = tr.term_taxonomy_id
          WHERE tt.taxonomy = 'product_cat'
            AND tt.term_id IN ({$in})
            AND p.post_type   = 'product'
            AND p.post_status = 'publish'"
    );
    $cache[ $term_id ] = $count;
    return $count;
}

/**
 * news / blog 归档页修复。
 * 站点 show_on_front='posts'，此时 WordPress 不会把 page_for_posts(页面 529 /news/)
 * 识别为 blog 归档，/news/ 被当普通静态页渲染(page.php，正文为空)。
 * 这里在 pre_get_posts 把该页主查询转换成 post 列表(home 语义)，让 index.php
 * 模板渲染文章。priority 5 早于 Polylang(@10)，语言过滤照常生效。
 */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || is_front_page() || ! $q->is_page() ) {
        return;
    }
    $pp = (int) get_option( 'page_for_posts' );
    if ( ! $pp ) {
        return;
    }
    // blog 页在各语言的翻译页面 ID 集合（Polylang：翻译页 queried_object_id 是各语言页 ID）
    $ids = array( $pp );
    if ( function_exists( 'pll_get_post_translations' ) ) {
        foreach ( (array) pll_get_post_translations( $pp ) as $tid ) {
            $ids[] = (int) $tid;
        }
    }
    $oid = (int) $q->get_queried_object_id();
    if ( ! in_array( $oid, $ids, true ) ) {
        return;
    }
    $q->set( 'post_type', 'post' );
    $q->set( 'pagename', '' );
    $q->set( 'page_id', 0 );
    $q->set( 'name', '' );
    $q->set( 'post_status', 'publish' );
    $q->is_home        = true;
    $q->is_page        = false;
    $q->is_singular    = false;
    $q->is_front_page  = false;
}, 5 );

// show_on_front='posts' 时 is_front_page() 由 is_home 派生,front-page.php 会抢走 /news/。
// 这里强制 blog 页(Polylang 各语言翻译)走 index.php 文章列表模板。
add_filter( 'template_include', function ( $template ) {
    global $wp_query;
    $pp = (int) get_option( 'page_for_posts' );
    if ( ! $pp ) {
        return $template;
    }
    $ids = array( $pp );
    if ( function_exists( 'pll_get_post_translations' ) ) {
        foreach ( (array) pll_get_post_translations( $pp ) as $tid ) {
            $ids[] = (int) $tid;
        }
    }
    $oid = (int) $wp_query->get_queried_object_id();
    if ( in_array( $oid, $ids, true ) && $wp_query->is_home() ) {
        $tpl = get_query_template( 'index' );
        if ( $tpl ) {
            return $tpl;
        }
    }
    return $template;
} );

// --- /news/ SEO 修正（canonical / title / description） ---
// show_on_front='posts' 时 is_front_page() 由 is_home 派生 true，Rank Math 会把 page_for_posts
// 渲染出的 /news/ 当成首页，输出 home 的 canonical 与 title/desc。这里按 blog 页单独修正，
// canonical 指回当前语言的 /news/，title/desc 用本地化文案。
function ew_is_news_archive() {
    global $wp_query;
    if ( is_admin() || ! $wp_query || ! $wp_query->is_home() ) {
        return false;
    }
    $pp = (int) get_option( 'page_for_posts' );
    if ( ! $pp ) {
        return false;
    }
    $ids = array( $pp );
    if ( function_exists( 'pll_get_post_translations' ) ) {
        foreach ( (array) pll_get_post_translations( $pp ) as $tid ) {
            $ids[] = (int) $tid;
        }
    }
    return in_array( (int) $wp_query->get_queried_object_id(), $ids, true );
}

// canonical：指向当前语言 /news/（不再误指首页）
add_filter( 'rank_math/frontend/canonical', function ( $url ) {
    if ( ew_is_news_archive() ) {
        return ew_page_url( 'news' );
    }
    return $url;
} );

// title：`本地化 News & Insights`（站点名统一 CHINYOO，不再追加 Electrical World）
// 优先级须高于首页 tagline title 过滤器(40)——show_on_front='posts' 下 /news/ 也会命中 is_front_page
add_filter( 'pre_get_document_title', function ( $title ) {
    if ( ew_is_news_archive() ) {
        return ew_esc_html__( 'News & Insights' );
    }
    return $title;
}, 50 );
add_filter( 'rank_math/frontend/title', function ( $title ) {
    if ( ew_is_news_archive() ) {
        return ew_esc_html__( 'News & Insights' );
    }
    return $title;
} );

// description：`News & Insights — 工厂直供一句话`
// 优先级须高于首页 tagline description 过滤器(30)
add_filter( 'rank_math/frontend/description', function ( $desc ) {
    if ( ew_is_news_archive() ) {
        return ew_esc_html__( 'News & Insights' ) . ' — ' . ew_esc_html__( 'Factory-direct electrical measuring instruments — digital multimeters, voltage testers, clamp meters and special test tools.' );
    }
    return $desc;
}, 40 );

function ew_build_mega_tree() {
    $top_cats = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
        'exclude'    => ew_exclude_default_cat(),
    ) );

    if ( is_wp_error( $top_cats ) || empty( $top_cats ) ) {
        return '<div class="sub-menu c-mega-menu"></div>';
    }

    $nav_html    = '<ul class="c-mega-nav">';
    $panels_html = '<div class="c-mega-panels">';

    foreach ( $top_cats as $index => $cat ) {
        $is_active = ( 0 === $index ) ? ' is-active' : '';
        $panel_id  = 'mega-panel-' . esc_attr( $cat->slug );

        // 1. 左侧导航项
        $nav_html .= sprintf(
            '<li class="c-mega-nav__item%s" data-target="%s"><a href="%s">%s <span class="c-mega-nav__arrow">›</span></a></li>',
            $is_active,
            $panel_id,
            esc_url( get_term_link( $cat ) ),
            esc_html( $cat->name )
        );

        // 2. 右侧对应子内容板
        $kids = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'parent'     => (int) $cat->term_id,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'exclude'    => ew_exclude_default_cat(),
        ) );

        $panels_html .= sprintf( '<div class="c-mega-panel%s" id="%s">', $is_active, $panel_id );
        $panels_html .= '<div class="c-mega-panel__head">';
        $panels_html .= '<h4 class="c-mega-panel__title">' . esc_html( $cat->name ) . '</h4>';
        $panels_html .= '<a class="c-mega-panel__all" href="' . esc_url( get_term_link( $cat ) ) . '">' . ew_esc_html__( 'View All Series' ) . ' →</a>';
        $panels_html .= '</div>';

        if ( ! is_wp_error( $kids ) && ! empty( $kids ) ) {
            $panels_html .= '<ul class="c-mega-subgrid">';
            foreach ( $kids as $kid ) {
                $panels_html .= sprintf(
                    '<li><a href="%s" class="c-mega-sublink">%s</a></li>',
                    esc_url( get_term_link( $kid ) ),
                    esc_html( $kid->name )
                );
            }
            $panels_html .= '</ul>';
        } else {
            $panels_html .= '<p class="c-mega-empty">' . ew_esc_html__( 'Series data updating...' ) . '</p>';
        }
        $panels_html .= '</div>';
    }

    $nav_html    .= '</ul>';
    $panels_html .= '</div>';

    return '<div class="sub-menu c-mega-menu">' . $nav_html . $panels_html . '</div>';
}

/**
 * 导航 walker：把 Products 菜单项(depth 0, class=mega-products)的整块子菜单
 * 替换为动态分类树，并吞掉后台手工配置的原子项。
 */
class Ew_Products_Mega_Walker extends Walker_Nav_Menu {
    private $mega_skip = false;

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( $this->mega_skip ) {
            if ( 0 === (int) $depth ) {
                $output .= ew_build_mega_tree();
            }
            return;
        }
        parent::start_lvl( $output, $depth, $args );
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( $this->mega_skip ) {
            if ( 0 === (int) $depth ) {
                $this->mega_skip = false;
            }
            return;
        }
        parent::end_lvl( $output, $depth, $args );
    }

    public function start_el( &$output, $item, $depth = 0, $args = null, $current_object_id = 0 ) {
        if ( $this->mega_skip ) { return; }
        if ( 0 === (int) $depth && isset( $item->classes ) && is_array( $item->classes ) && in_array( 'mega-products', $item->classes, true ) ) {
            $this->mega_skip = true;
        }
        parent::start_el( $output, $item, $depth, $args, $current_object_id );
    }

    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( $this->mega_skip ) { return; }
        parent::end_el( $output, $item, $depth, $args );
    }
}

// --- 作者体系(E-E-A-T):按当前文章作者动态输出多语言署名/简介/头像 ---
// 文章 post_author 指向具体 user(Rank Math BlogPosting 的 author 节点联动归档页),
// 主题按作者查配置表(ew_author_profiles,key=user_login)输出 byline + 简介块;
// 有照片的作者输出真人头像(ew_author_photo,user meta 'ew_author_photo'),
// 无照片退回首字母占位(ew_author_initial)。
function ew_author_lang() {
    $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
    return $lang ? $lang : 'en';
}

function ew_author_profiles() {
    return array(
        'zhihai' => array( // 胡志海 / Zhihai Hu(创始人,备用配置)
            'name'        => array( 'zh' => '胡志海', 'en' => 'Zhihai Hu' ),
            'initial'     => array( 'zh' => '胡', 'en' => 'ZH' ),
            'role_short'  => array( 'zh' => '创始人', 'en' => 'Founder', 'es' => 'Fundador', 'ru' => 'Основатель', 'ar' => 'مؤسس' ),
            'role'        => array(
                'zh' => '温州正原电子创始人',
                'en' => 'Founder, Wenzhou Zhengyuan Electronics',
                'es' => 'Fundador de Wenzhou Zhengyuan Electronics',
                'ru' => 'Основатель Wenzhou Zhengyuan Electronics',
                'ar' => 'مؤسس Wenzhou Zhengyuan Electronics',
            ),
            'bio'         => array(
                'en' => 'Founder of Wenzhou Zhengyuan Electronics, the factory behind the CHINYOO brand of digital multimeters, clamp meters, voltage testers and battery meters. He writes from hands-on factory experience to help buyers and electrical professionals choose instruments they can rely on.',
                'zh' => '温州正原电子创始人，CHINYOO 品牌数字万用表、钳形表、电压测试仪和电池测试仪的生产工厂。他以一线工厂经验撰写内容，帮助采购方与电气从业者挑选值得信赖的仪器。',
                'es' => 'Fundador de Wenzhou Zhengyuan Electronics, la fábrica detrás de la marca CHINYOO de multímetros digitales, pinzas amperimétricas, detectores de voltaje y medidores de batería. Escribe desde la experiencia directa de fábrica para ayudar a compradores y profesionales eléctricos a elegir instrumentos fiables.',
                'ru' => 'Основатель Wenzhou Zhengyuan Electronics — фабрики, выпускающей под брендом CHINYOO мультиметры, токовые клещи, тестеры напряжения и измерители аккумуляторов. Пишет на основе опыта производства, помогая покупателям и электромонтажникам выбирать надёжные приборы.',
                'ar' => 'مؤسس Wenzhou Zhengyuan Electronics، الشركة المصنعة وراء علامة CHINYOO التجارية لأجهزة القياس الرقمية المتعددة ومقاييس المشبك وأجهزة اختبار الجهد وأجهزة قياس البطارية. يكتب من خبرته المباشرة في المصنع لمساعدة المشترين ومتخصصي الكهرباء على اختيار أجهزة يعتمدون عليها.',
            ),
        ),
        'daijiewei' => array( // 戴杰伟 / Dai Jiewei(工程师)
            'name'        => array( 'zh' => '戴杰伟', 'en' => 'Dai Jiewei' ),
            'initial'     => array( 'zh' => '戴', 'en' => 'DJ' ),
            'role_short'  => array( 'zh' => '工程师', 'en' => 'Engineer', 'es' => 'Ingeniero', 'ru' => 'Инженер', 'ar' => 'مهندس' ),
            'role'        => array(
                'zh' => '温州正原电子工程师',
                'en' => 'Engineer, Wenzhou Zhengyuan Electronics',
                'es' => 'Ingeniero de Wenzhou Zhengyuan Electronics',
                'ru' => 'Инженер Wenzhou Zhengyuan Electronics',
                'ar' => 'مهندس في Wenzhou Zhengyuan Electronics',
            ),
            'bio'         => array(
                'en' => 'Dai is an engineer at Wenzhou Zhengyuan Electronics. He tests and validates every CHINYOO instrument before it ships, and also works directly with overseas customers to help them spec the right product. He writes technical guides from hands-on workbench experience so buyers can match the right meter to the job.',
                'zh' => '戴杰伟是温州正原电子工程师，负责 CHINYOO 每款仪表出厂前的测试与验证，同时直接对接海外客户，协助选型与采购。他以一线测试经验撰写技术指南，帮助采购方为实际工况选对仪表。',
                'es' => 'Dai es ingeniero de Wenzhou Zhengyuan Electronics. Prueba y valida cada instrumento CHINYOO antes de su envío y atiende directamente a clientes en el extranjero para ayudarles a elegir el producto adecuado. También escribe guías técnicas desde su experiencia en el banco de pruebas para que los compradores elijan el medidor correcto para cada trabajo.',
                'ru' => 'Дай — инженер Wenzhou Zhengyuan Electronics. Он тестирует и проверяет каждый прибор CHINYOO перед отправкой, а также напрямую работает с зарубежными клиентами, помогая подобрать нужную продукцию. Пишет технические руководства на основе практического опыта, чтобы покупатели выбирали правильный прибор для каждой задачи.',
                'ar' => 'داي مهندس في Wenzhou Zhengyuan Electronics. يختبر ويتحقق من كل جهاز CHINYOO قبل شحنه، ويعمل مباشرة مع العملاء الخارجيين لمساعدتهم على اختيار المنتج المناسب. يكتب أدلة تقنية مستندة إلى خبرته العملية في المختبر ليختار المشترون الجهاز المناسب لكل مهمة.',
            ),
        ),
    );
}

function ew_current_author_id() {
    if ( is_author() ) {
        return (int) get_queried_object_id();
    }
    return (int) get_the_author_meta( 'ID' );
}

function ew_author_profile( $field ) {
    $profiles = ew_author_profiles();
    $id       = ew_current_author_id();
    $user     = $id ? get_userdata( $id ) : false;
    $key      = ( $user && isset( $profiles[ $user->user_login ] ) ) ? $user->user_login : '';
    if ( ! $key ) {
        $key = (string) array_key_first( $profiles );
    }
    $p    = $profiles[ $key ];
    $lang = ew_author_lang();
    if ( isset( $p[ $field ][ $lang ] ) ) {
        return $p[ $field ][ $lang ];
    }
    if ( isset( $p[ $field ]['en'] ) ) {
        return $p[ $field ]['en'];
    }
    return '';
}

function ew_author_name()       { return ew_author_profile( 'name' ); }
function ew_author_initial()    { return ew_author_profile( 'initial' ); }
function ew_author_role_short() { return ew_author_profile( 'role_short' ); }
function ew_author_role()       { return ew_author_profile( 'role' ); }
function ew_author_bio()        { return ew_author_profile( 'bio' ); }

// 作者真人照片(user meta 'ew_author_photo')，无则空串。
function ew_author_photo() {
    $id = ew_current_author_id();
    if ( ! $id ) {
        return '';
    }
    return (string) get_user_meta( $id, 'ew_author_photo', true );
}

function ew_author_by_label() {
    $map = array(
        'en' => 'By',
        'zh' => '作者',
        'es' => 'Por',
        'ru' => 'Автор',
        'ar' => 'بقلم',
    );
    $lang = ew_author_lang();
    return isset( $map[ $lang ] ) ? $map[ $lang ] : $map['en'];
}