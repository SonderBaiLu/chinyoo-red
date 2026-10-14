<?php
/**
 * Theme setup — menus, image sizes, theme supports
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ew_theme_setup() {
    // Translation-ready
    load_theme_textdomain( 'chinyoo-red', EW_THEME_DIR . '/languages' );

    // Title tag (WordPress handles <title>)
    add_theme_support( 'title-tag' );

    // RSS feed links (输出 feed URL 供订阅/AI 抓取；勿在 functions.php 里 remove_action)
    add_theme_support( 'automatic-feed-links' );

    // Post thumbnails
    add_theme_support( 'post-thumbnails' );

    // WooCommerce (Product CPT, gallery zoom/lightbox)
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // HTML5 markup
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Register menus
    register_nav_menus( array(
        'primary'  => ew__('Primary Menu'),
        'footer_company' => ew__('Footer — Company'),
        'footer_products' => ew__('Footer — Products'),
    ) );

    // Product thumbnail size
    // 400×400 hard crop 会把宽幅产品图裁掉左右两边 → 改为等比缩略(产品完整可见)。
    // 模板现优先用 medium_large(768px 等比);此尺寸保留为等比回退。
    add_image_size( 'product-card', 400, 0, false );
    add_image_size( 'product-main', 600, 600, true );
    add_image_size( 'hero-banner', 1920, 700, true );

    // Register sidebar
    register_sidebar( array(
        'name'          => ew__('Blog Sidebar'),
        'id'            => 'blog-sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}

// Set products per page
function ew_products_per_page( $query ) {
    if ( ! is_admin() && $query->is_main_query() && ( is_post_type_archive( 'product' ) || is_tax( 'product_category' ) || ( function_exists( 'is_product_category' ) && is_product_category() ) ) ) {
        $query->set( 'posts_per_page', 12 );
    }
}
add_action( 'pre_get_posts', 'ew_products_per_page' );

add_action( 'after_setup_theme', 'ew_theme_setup' );
