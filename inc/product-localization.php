<?php
/** Shared language and model metadata helpers. Existing cn image keys are preserved. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function ew_product_language_key( $language ) {
    $language = strtolower( str_replace( '_', '-', (string) $language ) );
    return in_array( $language, array( 'cn', 'zh', 'zh-cn', 'zh-hans' ), true ) ? 'cn' : sanitize_key( $language );
}

function ew_product_languages() {
    $defaults = array( 'en' => 'English', 'pl' => 'Polski', 'cs' => 'Čeština', 'ro' => 'Română', 'de' => 'Deutsch', 'fr' => 'Français', 'nl' => 'Nederlands', 'it' => 'Italiano', 'ko' => '한국어', 'ja' => '日本語', 'zh' => '简体中文', 'es' => 'Español', 'ru' => 'Русский', 'ar' => 'العربية' );
    $slugs = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'hide_empty' => false, 'fields' => 'slug' ) ) : array();
    $names = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'hide_empty' => false, 'fields' => 'name' ) ) : array();
    if ( ! $slugs ) { $slugs = array_keys( $defaults ); }
    $languages = array();
    foreach ( $slugs as $index => $slug ) {
        $key = ew_product_language_key( $slug );
        $languages[ $key ] = array( 'slug' => $slug, 'name' => isset( $names[ $index ] ) ? $names[ $index ] : ( isset( $defaults[ $slug ] ) ? $defaults[ $slug ] : $slug ) );
    }
    return $languages;
}

function ew_product_meta_map( $id, $key ) {
    $raw = get_post_meta( absint( $id ), $key, true );
    $map = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
    return is_array( $map ) ? $map : array();
}

/** Resolve only the same model; never substitute the first sibling variation. */
function ew_product_source_id( $id ) {
    $id = absint( $id );
    if ( ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_default_language' ) ) { return $id; }
    if ( 'product_variation' === get_post_type( $id ) ) {
        $parent = wp_get_post_parent_id( $id );
        $source_parent = (int) pll_get_post( $parent, pll_default_language() );
        if ( ! $source_parent || $source_parent === $parent ) { return $id; }
        $model = (string) get_post_meta( $id, 'attribute_pa_model', true );
        if ( '' === $model ) { return $id; }
        $matches = get_posts( array( 'post_type' => 'product_variation', 'post_parent' => $source_parent, 'post_status' => 'publish', 'posts_per_page' => 2, 'fields' => 'ids', 'meta_key' => 'attribute_pa_model', 'meta_value' => $model, 'suppress_filters' => true ) );
        return 1 === count( $matches ) ? (int) $matches[0] : $id;
    }
    $source = (int) pll_get_post( $id, pll_default_language() );
    return $source ?: $id;
}

/** Language-specific values first, then the legacy field. Empty translations inherit. */
function ew_product_localized_meta( $id, $key, $legacy_key, $language = null ) {
    $language = null === $language ? ( function_exists( 'pll_current_language' ) ? pll_current_language() : 'en' ) : $language;
    $language = ew_product_language_key( $language ?: 'en' );
    $ids = array_unique( array( absint( $id ), ew_product_source_id( $id ) ) );
    foreach ( $ids as $candidate ) {
        $map = ew_product_meta_map( $candidate, $key );
        if ( isset( $map[ $language ] ) && '' !== $map[ $language ] && array() !== $map[ $language ] ) { return $map[ $language ]; }
    }
    foreach ( $ids as $candidate ) {
        $raw = get_post_meta( $candidate, $legacy_key, true );
        if ( '' !== $raw && array() !== $raw ) { return $raw; }
    }
    return '';
}

function ew_product_detail_image_ids( $id, $language = null ) {
    $language = null === $language ? ( function_exists( 'pll_current_language' ) ? pll_current_language() : 'en' ) : $language;
    $keys = array_unique( array( ew_product_language_key( $language ?: 'en' ), 'en' ) );
    $ids = array_unique( array( absint( $id ), ew_product_source_id( $id ) ) );
    foreach ( $keys as $key ) {
        foreach ( $ids as $candidate ) {
            $map = ew_product_meta_map( $candidate, '_model_detail_images' );
            // Also accept zh from imports while retaining the historical cn storage key.
            $values = isset( $map[ $key ] ) ? $map[ $key ] : ( 'cn' === $key && isset( $map['zh'] ) ? $map['zh'] : array() );
            if ( ! is_array( $values ) ) { continue; }
            $images = array();
            foreach ( $values as $value ) {
                if ( is_scalar( $value ) && absint( $value ) && wp_attachment_is_image( absint( $value ) ) ) { $images[] = absint( $value ); }
            }
            if ( $images ) { return array_values( array_unique( $images ) ); }
        }
    }
    return array();
}

function ew_product_language_url( $url ) {
    if ( is_singular( 'product' ) && isset( $_GET['attribute_pa_model'] ) && is_string( $_GET['attribute_pa_model'] ) ) {
        $model = sanitize_text_field( wp_unslash( $_GET['attribute_pa_model'] ) );
        if ( '' !== $model ) { $url = add_query_arg( 'attribute_pa_model', $model, $url ); }
    }
    return $url;
}
