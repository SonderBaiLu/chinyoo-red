<?php
/**
 * Enqueue scripts and styles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ew_enqueue_assets() {
    // Montserrat 已本地托管于 assets/fonts(@font-face 见 main.css 顶部)——
    // 不再请求 Google Fonts,避免国内无法访问导致字体回退成 Arial。

    $dir = get_stylesheet_directory();
    // 用文件修改时间做版本号 → 每次改 CSS/JS 自动破浏览器缓存(修复"改了看不到"问题)

    // Design tokens —— 全站唯一真值来源，必须最先加载。
    // main/css 里 --primary 等旧名已改成指向本文件的别名，顺序错会让整站失色。
    $v_tokens = ($t = filemtime( "$dir/assets/css/tokens.css" )) === false ? EW_THEME_VERSION : (string) $t;
    wp_enqueue_style(
        'ew-tokens',
        EW_THEME_URI . '/assets/css/tokens.css',
        array(),
        $v_tokens
    );

    $v_main = ($t = filemtime( "$dir/assets/css/main.css" )) === false ? EW_THEME_VERSION : (string) $t;
    $v_p4   = ($t = filemtime( "$dir/assets/css/p4.css"   )) === false ? EW_THEME_VERSION : (string) $t;
    $v_mainjs = ($t = filemtime( "$dir/assets/js/main.js" )) === false ? EW_THEME_VERSION : (string) $t;
    $v_iti_css = ($t = filemtime( "$dir/assets/vendor/intl-tel-input/css/intlTelInput.min.css" )) === false ? '29.5.0' : (string) $t;
    $v_iti_js  = ($t = filemtime( "$dir/assets/vendor/intl-tel-input/js/intlTelInput.min.js" )) === false ? '29.5.0' : (string) $t;
    $v_p4js   = ($t = filemtime( "$dir/assets/js/p4-woo.js")) === false ? EW_THEME_VERSION : (string) $t;
    $v_gsap   = ($t = filemtime( "$dir/assets/js/vendor/gsap.min.js"        )) === false ? EW_THEME_VERSION : (string) $t;
    $v_st     = ($t = filemtime( "$dir/assets/js/vendor/ScrollTrigger.min.js")) === false ? EW_THEME_VERSION : (string) $t;
    $v_anim   = ($t = filemtime( "$dir/assets/js/animations.js"             )) === false ? EW_THEME_VERSION : (string) $t;

    // Main stylesheet
    wp_enqueue_style(
        'ew-main-style',
        EW_THEME_URI . '/assets/css/main.css',
        array( 'ew-tokens' ),
        $v_main
    );

    // 国际电话国家/区号选择器，国旗图片本地托管，避免 Windows 将 Emoji 显示为 US。
    wp_enqueue_style(
        'ew-intl-tel-input',
        EW_THEME_URI . '/assets/vendor/intl-tel-input/css/intlTelInput.min.css',
        array( 'ew-main-style' ),
        $v_iti_css
    );

    // P4 UNI-T style components (filter sidebar, cards, spec matrix, CTA)
    wp_enqueue_style(
        'ew-p4-style',
        EW_THEME_URI . '/assets/css/p4.css',
        array( 'ew-main-style' ),
        $v_p4
    );

    // Language switcher dropdown (header)
    $v_ls = ($t = filemtime( "$dir/assets/css/lang-switcher.css" )) === false ? EW_THEME_VERSION : (string) $t;
    wp_enqueue_style(
        'ew-lang-switcher',
        EW_THEME_URI . '/assets/css/lang-switcher.css',
        array( 'ew-p4-style' ),
        $v_ls
    );

    // Redesign layer — 只补齐"模板引用了但 CSS 里零规则"的 class
    // (.btn--primary / .page-title / .section__title / .faq-* 族 / .products-empty)。
    // 只新增不覆盖；排在 lang-switcher 之后、rtl 之前。
    $v_rd = ($t = filemtime( "$dir/assets/css/redesign.css" )) === false ? EW_THEME_VERSION : (string) $t;
    wp_enqueue_style(
        'ew-redesign',
        EW_THEME_URI . '/assets/css/redesign.css',
        array( 'ew-lang-switcher' ),
        $v_rd
    );

    // Homepage visual system — loaded after the shared redesign layer so
    // the home-only cards, overlays and inquiry layout stay scoped to .home.
    $v_home = ($t = filemtime( "$dir/assets/css/homepage.css" )) === false ? EW_THEME_VERSION : (string) $t;
    wp_enqueue_style(
        'ew-homepage',
        EW_THEME_URI . '/assets/css/homepage.css',
        array( 'ew-redesign' ),
        $v_home
    );

    // One visual system for the Products directory and all product archives.
    $catalog_page = get_page_by_path( 'product-categories' );
    $catalog_ids = $catalog_page ? array( (int) $catalog_page->ID ) : array();
    if ( $catalog_page && function_exists( 'pll_get_post_translations' ) ) {
        $catalog_ids = array_merge( $catalog_ids, array_values( pll_get_post_translations( $catalog_page->ID ) ) );
    }
    if ( ( $catalog_ids && is_page( $catalog_ids ) ) || is_page_template( 'page-product-categories.php' ) || is_post_type_archive( 'product' ) || is_tax( array( 'product_cat', 'product_tag' ) ) ) {
        wp_enqueue_style( 'ew-catalog', EW_THEME_URI . '/assets/css/catalog.css', array( 'ew-homepage' ), (string) filemtime( "$dir/assets/css/catalog.css" ) );
        wp_enqueue_script( 'ew-catalog', EW_THEME_URI . '/assets/js/catalog.js', array(), (string) filemtime( "$dir/assets/js/catalog.js" ), true );
    }

    // RTL overrides — enqueued only when the current language is RTL (Arabic).
    if ( is_rtl() ) {
        $v_rtl = ($t = filemtime( "$dir/assets/css/rtl.css" )) === false ? EW_THEME_VERSION : (string) $t;
        wp_enqueue_style(
            'ew-rtl',
            EW_THEME_URI . '/assets/css/rtl.css',
            array( 'ew-homepage' ),
            $v_rtl
        );
    }

    wp_enqueue_script(
        'ew-intl-tel-input',
        EW_THEME_URI . '/assets/vendor/intl-tel-input/js/intlTelInput.min.js',
        array(),
        $v_iti_js,
        true
    );

    // Main JavaScript (loaded in footer)
    wp_enqueue_script(
        'ew-main-script',
        EW_THEME_URI . '/assets/js/main.js',
        array( 'ew-intl-tel-input' ),
        $v_mainjs,
        true
    );

    // Cloudflare Turnstile 人机验证（询盘表单）。defer 加载，不阻塞首屏。
    wp_enqueue_script(
        'ew-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        array(),
        null,
        array(
            'in_footer' => true,
            'strategy'  => 'defer',
        )
    );

    // P4 WooCommerce theme scripts (UNI-T style: series model selector,
    // inquiry CTA autofill/scroll, gallery thumbnails) — depends on jQuery.
    wp_enqueue_script(
        'ew-p4-woo',
        EW_THEME_URI . '/assets/js/p4-woo.js',
        array( 'jquery' ),
        $v_p4js,
        true
    );

    // GSAP + ScrollTrigger + 滚动特效(本地托管,全站,复刻 RIGOL 首页特效)
    wp_enqueue_script(
        'ew-gsap',
        EW_THEME_URI . '/assets/js/vendor/gsap.min.js',
        array(),
        $v_gsap,
        true
    );
    wp_enqueue_script(
        'ew-gsap-scrolltrigger',
        EW_THEME_URI . '/assets/js/vendor/ScrollTrigger.min.js',
        array( 'ew-gsap' ),
        $v_st,
        true
    );
    wp_enqueue_script(
        'ew-animations',
        EW_THEME_URI . '/assets/js/animations.js',
        array( 'ew-gsap-scrolltrigger' ),
        $v_anim,
        true
    );

    // YITH WooCommerce Zoom Magnifier(详情页放大,UNI-T 同款插件)。
    // yith-magnifier.js 来自 YITH 插件本体,参数与 meters.uni-trend.com 页面完全一致:
    //   zoomWidth/zoomHeight=400, position=right, lensOpacity=0.5
    // 缩略图切换也由插件 _initGallery 处理(单产品页 .images > ul.yith_magnifier_gallery)。
    if ( is_product() ) {
        $v_yithcss = ($t = filemtime( "$dir/assets/css/yith-magnifier.css" )) === false ? EW_THEME_VERSION : (string) $t;
        $v_yithfcss = ($t = filemtime( "$dir/assets/css/yith-frontend.css" )) === false ? EW_THEME_VERSION : (string) $t;
        $v_yithjs = ($t = filemtime( "$dir/assets/js/yith-magnifier.js" )) === false ? EW_THEME_VERSION : (string) $t;

        wp_enqueue_style(
            'ew-yith-magnifier',
            EW_THEME_URI . '/assets/css/yith-magnifier.css',
            array( 'ew-p4-style' ),
            $v_yithcss
        );
        wp_enqueue_style(
            'ew-yith-frontend',
            EW_THEME_URI . '/assets/css/yith-frontend.css',
            array( 'ew-yith-magnifier' ),
            $v_yithfcss
        );
        wp_enqueue_script(
            'ew-yith-magnifier',
            EW_THEME_URI . '/assets/js/yith-magnifier.js',
            array( 'jquery' ),
            $v_yithjs,
            true
        );

        // 插件依赖的全局对象(与 UNI-T 页面输出的结构一致)。
        wp_localize_script( 'ew-yith-magnifier', 'yith_wc_zoom_magnifier_storage_object', array(
            'ajax_url'          => admin_url( 'admin-ajax.php' ),
            'mouse_trap_width'  => '100%',
            'mouse_trap_height' => '100%',
        ) );

        wp_localize_script( 'ew-yith-magnifier', 'yith_magnifier_options', array(
            'enableSlider'           => false,
            'showTitle'              => false,
            'zoomWidth'              => '400',
            'zoomHeight'             => '400',
            'position'               => 'right',
            'softFocus'              => false,
            'adjustY'                => 0,
            'disableRightClick'      => false,
            'phoneBehavior'          => 'right',
            'zoom_wrap_additional_css' => '',
            'lensOpacity'            => '0.5',
            'loadingLabel'           => 'Loading...',
        ) );

        // 初始化:确保 yith-magnifier.js 已加载后再调用(与 UNI-T 的 ywzm_frontend.js 相同)。
        wp_add_inline_script(
            'ew-yith-magnifier',
            'jQuery(function($){ if( window.yith_magnifier_options && $.fn.yith_magnifier && $(".images").length ){ $(".images").yith_magnifier( window.yith_magnifier_options ); } });',
            'after'
        );
    }

    // Pass AJAX URL, nonce and language-aware telephone defaults to JS.
    $ew_current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';
    $ew_phone_countries = array( 'en' => 'us', 'zh' => 'cn', 'es' => 'es', 'ru' => 'ru', 'ar' => 'ae' );
    wp_localize_script( 'ew-main-script', 'ew_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'ew_inquiry_nonce' ),
        'iti_utils_url' => EW_THEME_URI . '/assets/vendor/intl-tel-input/js/utils.js',
        'iti_initial_country' => isset( $ew_phone_countries[ $ew_current_lang ] ) ? $ew_phone_countries[ $ew_current_lang ] : 'us',
    ));
}
add_action( 'wp_enqueue_scripts', 'ew_enqueue_assets' );

/**
 * 预加载主字体。
 * @font-face 现在写在 tokens.css 里，浏览器要等 CSS 解析完才发现字体，
 * 首屏标题会先以回退字体渲染再跳变(FOUT)。提前声明可省掉这一轮往返。
 * 挂 wp_head 而非改 header.php —— 模板文件越少动越好。
 */
function ew_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( EW_THEME_URI . '/assets/fonts/montserrat-latin.woff2' )
	);
}
add_action( 'wp_head', 'ew_preload_fonts', 1 );
