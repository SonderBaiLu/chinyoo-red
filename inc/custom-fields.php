<?php
/**
 * Register ACF field groups for Product, Download, and Inquiry CPTs.
 *
 * Uses acf_add_local_field_group() so fields are in version control.
 * Requires Advanced Custom Fields plugin (free).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Only register if ACF is active
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
}

add_action( 'acf/init', 'ew_register_acf_fields' );

function ew_register_acf_fields() {

    // ===========================
    // Product Fields
    // ===========================
    acf_add_local_field_group( array(
        'key'      => 'group_product_details',
        'title'    => ew__('Product Details'),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ),
            ),
        ),
        'fields' => array(
            // Model Number
            array(
                'key'   => 'field_product_model',
                'label' => ew__('Model Number'),
                'name'  => 'product_model',
                'type'  => 'text',
            ),
            // Product Gallery
            array(
                'key'   => 'field_product_gallery',
                'label' => ew__('Product Gallery'),
                'name'  => 'product_gallery',
                'type'  => 'gallery',
                'instructions' => ew__('Add 3-5 product images. First image = main thumbnail.'),
            ),
            // Technical Specifications (repeater)
            array(
                'key'   => 'field_product_specs',
                'label' => ew__('Technical Specifications'),
                'name'  => 'product_specs',
                'type'  => 'repeater',
                'sub_fields' => array(
                    array(
                        'key'   => 'field_spec_name',
                        'label' => ew__('Parameter'),
                        'name'  => 'spec_name',
                        'type'  => 'text',
                    ),
                    array(
                        'key'   => 'field_spec_value',
                        'label' => ew__('Value'),
                        'name'  => 'spec_value',
                        'type'  => 'text',
                    ),
                ),
            ),
            // Packaging Info
            array(
                'key'   => 'field_product_packaging',
                'label' => ew__('Packaging Information'),
                'name'  => 'product_packaging',
                'type'  => 'textarea',
            ),
            // MOQ
            array(
                'key'   => 'field_product_moq',
                'label' => ew__('Minimum Order Quantity'),
                'name'  => 'product_moq',
                'type'  => 'text',
            ),
            // Delivery Time
            array(
                'key'   => 'field_product_delivery',
                'label' => ew__('Delivery Time'),
                'name'  => 'product_delivery',
                'type'  => 'text',
            ),
            // Hot Product flag
            array(
                'key'   => 'field_product_hot',
                'label' => ew__('Featured / Hot Product'),
                'name'  => 'product_hot',
                'type'  => 'true_false',
                'ui'    => true,
            ),
            // Spec Sheet PDF
            array(
                'key'   => 'field_product_spec_pdf',
                'label' => ew__('Specification Sheet (PDF)'),
                'name'  => 'product_spec_pdf',
                'type'  => 'file',
                'return_format' => 'url',
                'mime_types'    => 'pdf',
            ),
        ),
    ) );

    // ===========================
    // Download Fields
    // ===========================
    acf_add_local_field_group( array(
        'key'      => 'group_download_details',
        'title'    => ew__('Download Details'),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'download',
                ),
            ),
        ),
        'fields' => array(
            array(
                'key'   => 'field_download_file',
                'label' => ew__('File'),
                'name'  => 'download_file',
                'type'  => 'file',
                'return_format' => 'url',
            ),
            array(
                'key'   => 'field_download_product',
                'label' => ew__('Related Product'),
                'name'  => 'download_product',
                'type'  => 'post_object',
                'post_type' => array( 'product' ),
            ),
            array(
                'key'   => 'field_download_category',
                'label' => ew__('Category'),
                'name'  => 'download_category',
                'type'  => 'taxonomy',
                'taxonomy' => 'product_category',
                'field_type' => 'select',
            ),
        ),
    ) );

    // ===========================
    // Inquiry Fields
    // ===========================
    acf_add_local_field_group( array(
        'key'      => 'group_inquiry_details',
        'title'    => ew__('Inquiry Details'),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'inquiry',
                ),
            ),
        ),
        'fields' => array(
            array(
                'key'   => 'field_inquiry_name',
                'label' => ew__('Name'),
                'name'  => 'inquiry_name',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_inquiry_email',
                'label' => ew__('Email'),
                'name'  => 'inquiry_email',
                'type'  => 'email',
            ),
            array(
                'key'   => 'field_inquiry_phone',
                'label' => ew__('Phone / WhatsApp'),
                'name'  => 'inquiry_phone',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_inquiry_company',
                'label' => ew__('Company'),
                'name'  => 'inquiry_company',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_inquiry_product',
                'label' => ew__('Product Interested'),
                'name'  => 'inquiry_product',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_inquiry_message',
                'label' => ew__('Message'),
                'name'  => 'inquiry_message',
                'type'  => 'textarea',
            ),
        ),
    ) );
}
