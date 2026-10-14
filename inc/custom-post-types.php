<?php
/**
 * Register Custom Post Types: product (WooCommerce fallback), inquiry, download, faq
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ew_register_post_types() {

    // Product CPT + Product Category taxonomy
    // When WooCommerce is active, WC owns `product` (rewrite `product`) and
    // `product_cat` (rewrite `product-category`). Register them ONLY as a
    // fallback so the two never collide.
    if ( ! class_exists( 'WooCommerce' ) ) {
        $product_labels = array(
            'name'               => ew__('Products'),
            'singular_name'      => ew__('Product'),
            'add_new'            => ew__('Add Product'),
            'add_new_item'       => ew__('Add New Product'),
            'edit_item'          => ew__('Edit Product'),
            'view_item'          => ew__('View Product'),
            'search_items'       => ew__('Search Products'),
            'not_found'          => ew__('No products found'),
            'menu_name'          => ew__('Products'),
        );

        register_post_type( 'product', array(
            'labels'        => $product_labels,
            'public'        => true,
            'has_archive'   => true,
            'rewrite'       => array( 'slug' => 'products' ),
            'menu_position' => 5,
            'menu_icon'     => 'dashicons-products',
            'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'  => true,
        ) );

        register_taxonomy( 'product_category', 'product', array(
            'labels'            => array(
                'name'          => ew__('Product Categories'),
                'singular_name' => ew__('Category'),
                'search_items'  => ew__('Search Categories'),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'product-category' ),
            'show_in_rest'      => true,
        ) );
    }

    // Inquiry CPT — stores contact form submissions
    $inquiry_labels = array(
        'name'               => ew__('Inquiries'),
        'singular_name'      => ew__('Inquiry'),
        'menu_name'          => ew__('Inquiries'),
        'not_found'          => ew__('No inquiries found'),
        'search_items'       => ew__('Search Inquiries'),
    );

    register_post_type( 'inquiry', array(
        'labels'        => $inquiry_labels,
        'public'        => false,
        'show_ui'       => true,
        'menu_position' => 30,
        'menu_icon'     => 'dashicons-email-alt',
        'supports'      => array( 'title' ),
        'capabilities'  => array(
            'create_posts' => false, // Only created via form submission
        ),
    ) );

    // Download CPT
    $download_labels = array(
        'name'          => ew__('Downloads'),
        'singular_name' => ew__('Download'),
        'menu_name'     => ew__('Downloads'),
    );

    register_post_type( 'download', array(
        'labels'        => $download_labels,
        'public'        => true,
        'has_archive'   => false,
        'rewrite'       => array( 'slug' => 'downloads' ),
        'menu_position' => 6,
        'menu_icon'     => 'dashicons-download',
        'supports'      => array( 'title' ),
        'show_in_rest'  => true,
    ) );

    // FAQ CPT + FAQ Category taxonomy (UNI-T style: /faqs/ + /faqscategory/)
    $faq_labels = array(
        'name'          => ew__('FAQs'),
        'singular_name' => ew__('FAQ'),
        'add_new'       => ew__('Add FAQ'),
        'add_new_item'  => ew__('Add New FAQ'),
        'edit_item'     => ew__('Edit FAQ'),
        'menu_name'     => ew__('FAQs'),
        'not_found'     => ew__('No FAQs found'),
    );

    register_post_type( 'faq', array(
        'labels'        => $faq_labels,
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => array( 'slug' => 'faqs' ),
        'menu_position' => 7,
        'menu_icon'     => 'dashicons-editor-help',
        'supports'      => array( 'title', 'editor' ),
        'show_in_rest'  => true,
    ) );

    register_taxonomy( 'faq_category', 'faq', array(
        'labels'            => array(
            'name'          => ew__('FAQ Categories'),
            'singular_name' => ew__('FAQ Category'),
            'search_items'  => ew__('Search FAQ Categories'),
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'faqscategory' ),
        'show_in_rest'      => true,
    ) );
}
add_action( 'init', 'ew_register_post_types' );
