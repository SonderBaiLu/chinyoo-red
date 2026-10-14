<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Parse the _model_specs JSON meta into a clean array of name/value pairs.
 *
 * @param int $post_id Post ID.
 * @return array[] List of ['name' => string, 'value' => string].
 */
if ( ! function_exists( 'ew_get_model_specs' ) ) {
function ew_get_model_specs( $post_id ) {
    $raw   = get_post_meta( absint( $post_id ), '_model_specs', true );
    $specs = json_decode( (string) $raw, true );

    if ( ! is_array( $specs ) ) {
        return array();
    }

    $clean = array();
    foreach ( $specs as $spec ) {
        if ( ! is_array( $spec ) ) {
            continue;
        }
        $name  = isset( $spec['name'] ) ? (string) $spec['name'] : '';
        $value = isset( $spec['value'] ) ? (string) $spec['value'] : '';
        $clean[] = array(
            'name'  => wc_clean( $name ),
            'value' => wc_clean( $value ),
        );
    }

    return $clean;
}
}

/**
 * Parse the _model_gallery JSON meta into a clean list of attachment IDs.
 *
 * Accepts a JSON array, a serialized array, or a comma-separated string.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
if ( ! function_exists( 'ew_get_model_gallery' ) ) {
function ew_get_model_gallery( $post_id ) {
    $raw = get_post_meta( absint( $post_id ), '_model_gallery', true );
    $ids = array();

    if ( is_array( $raw ) ) {
        $ids = $raw;
    } elseif ( is_string( $raw ) && '' !== trim( $raw ) ) {
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            $ids = $decoded;
        } else {
            $ids = array_map( 'trim', explode( ',', $raw ) );
        }
    }

    return array_values( array_filter( array_map( 'absint', $ids ) ) );
}
}

/**
 * Detail long-images for the current model, from _model_detail_images meta.
 *
 * Stored as a JSON map {"en":[attachment ids],"cn":[attachment ids]}.
 * Chinese pages pick 'cn', every other language falls back to 'en'.
 *
 * Translations and variation fallback: variations carry their own map; the
 * parent product stores the first variation's map (set at import time); a
 * translated post holds no copy, so we resolve the EN original via pll_get_post.
 *
 * @param int $model_id Variation ID (variable product) or post ID (simple / translation).
 * @return int[]
 */
if ( ! function_exists( 'ew_get_model_detail_images' ) ) {
function ew_get_model_detail_images( $model_id ) {
    $pid = absint( $model_id );
    if ( ! $pid ) {
        return array();
    }

    $raw = get_post_meta( $pid, '_model_detail_images', true );
    $map = is_string( $raw ) ? json_decode( $raw, true ) : $raw;

    if ( ! is_array( $map ) || empty( $map ) ) {
        // 翻译页 → EN 源 post(翻译 post 各自不持 meta 副本)。
        // 注意:变体分支不回退父 post——父 post 存的是"首个变体"的详情图,
        // 当前模型非首个变体时回退会显示错型号内容。
        if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
            $en = (int) pll_get_post( $pid, pll_default_language() );
            if ( $en && $en !== $pid ) {
                $raw = get_post_meta( $en, '_model_detail_images', true );
                $map = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
            }
        }
    }
    if ( ! is_array( $map ) || empty( $map ) ) {
        return array();
    }

    $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
    $key  = ( 'zh' === $lang ) ? 'cn' : $lang;
    if ( ! empty( $map[ $key ] ) && is_array( $map[ $key ] ) ) {
        return array_values( array_filter( array_map( 'absint', $map[ $key ] ) ) );
    }
    if ( ! empty( $map['en'] ) && is_array( $map['en'] ) ) {
        return array_values( array_filter( array_map( 'absint', $map['en'] ) ) );
    }
    return array();
}
}

get_header();

while ( have_posts() ) :
    the_post();

    $product = wc_get_product( get_the_ID() );

    if ( ! $product instanceof WC_Product ) {
        echo '<div class="container"><p>' . ew_esc_html__('Product not found.') . '</p></div>';
        continue;
    }

    /* --------------------------------------------------------------------
     * 1. Collect the models.
     *    Variable product => iterate get_available_variations() for the
     *    dropdown options (sku + attribute_pa_model), falling back to
     *    get_children() when variations have no price set (B2B data).
     *    Simple product => the product itself.
     * -------------------------------------------------------------------- */
    $models = array();

    if ( $product->is_type( 'variable' ) ) {
        // Obtain variation records; unpriced B2B models use the child fallback below.
        $variations = $product->get_available_variations();

        if ( empty( $variations ) ) {
            // get_available_variations() hides variations without a price;
            // B2B catalogues often ship without prices — iterate children.
            // Note: $child->get_data() alone is not usable here — it has no
            // 'variation_id' key and its 'attributes' are WC_Product_Attribute
            // objects, not the 'attribute_pa_model' => string shape below. We
            // merge in the keys the downstream loop relies on.
            $variations = array();
            foreach ( (array) $product->get_children() as $child_id ) {
                $child = wc_get_product( $child_id );
                if ( $child instanceof WC_Product_Variation ) {
                    $variations[] = array_merge(
                        $child->get_data(),
                        array(
                            'variation_id' => $child->get_id(),
                            'attributes'   => $child->get_variation_attributes(),
                        )
                    );
                }
            }
        }

        foreach ( $variations as $variation ) {
            $vid      = isset( $variation['variation_id'] ) ? absint( $variation['variation_id'] ) : 0;
            if ( ! $vid ) {
                continue;
            }
            $sku      = isset( $variation['sku'] ) ? (string) $variation['sku'] : '';
            $pa_model = isset( $variation['attributes']['attribute_pa_model'] )
                ? (string) $variation['attributes']['attribute_pa_model']
                : '';

            $url_model = '' !== $pa_model ? $pa_model : $sku;
            if ( '' === $url_model ) {
                $url_model = $product->get_slug() . '-' . $vid;
            }

            $models[] = array(
                'id'        => $vid,
                'sku'       => $sku,
                'label'     => '' !== $sku ? $sku : $url_model,
                'url_model' => $url_model,
                'specs'     => ew_get_model_specs( $vid ),
                'gallery'   => ew_get_model_gallery( $vid ),
                'packaging' => get_post_meta( $vid, '_packaging', true ),
            );
        }
    } else {
        $pid       = $product->get_id();
        $sku       = $product->get_sku() ? (string) $product->get_sku() : '';
        $url_model = '' !== $sku ? $sku : $product->get_slug();

        $models[] = array(
            'id'        => $pid,
            'sku'       => $sku,
            'label'     => '' !== $sku ? $sku : $url_model,
            'url_model' => $url_model,
            'specs'     => ew_get_model_specs( $pid ),
            'gallery'   => ew_get_model_gallery( $pid ),
            'packaging' => get_post_meta( $pid, '_packaging', true ),
        );
    }

    // Defensive: if no model could be derived, fall back to the parent product.
    if ( empty( $models ) ) {
        $pid       = $product->get_id();
        $sku       = $product->get_sku() ? (string) $product->get_sku() : '';
        $url_model = '' !== $sku ? $sku : $product->get_slug();

        $models[] = array(
            'id'        => $pid,
            'sku'       => $sku,
            'label'     => '' !== $sku ? $sku : $product->get_name(),
            'url_model' => $url_model,
            'specs'     => ew_get_model_specs( $pid ),
            'gallery'   => ew_get_model_gallery( $pid ),
            'packaging' => get_post_meta( $pid, '_packaging', true ),
        );
    }

    /* --------------------------------------------------------------------
     * 2. Resolve the current model from ?attribute_pa_model, else the first.
     * -------------------------------------------------------------------- */
    $requested_model = isset( $_GET['attribute_pa_model'] )
        ? sanitize_text_field( wp_unslash( $_GET['attribute_pa_model'] ) )
        : '';

    $current_index = 0;
    if ( '' !== $requested_model ) {
        foreach ( $models as $i => $m ) {
            if (
                strtolower( $requested_model ) === strtolower( $m['url_model'] )
                || strtolower( $requested_model ) === strtolower( $m['label'] )
                || strtolower( $requested_model ) === strtolower( $m['sku'] )
            ) {
                $current_index = $i;
                break;
            }
        }
    }

    $current_model = $models[ $current_index ];

    /* --------------------------------------------------------------------
     * 3. Spec highlights for the right column: DCV / ACV / Resistance.
     * -------------------------------------------------------------------- */
    $highlights = array();
    foreach ( $current_model['specs'] as $spec ) {
        $name = isset( $spec['name'] ) ? (string) $spec['name'] : '';
        $norm = strtolower( preg_replace( '/[^a-z0-9]/i', '', $name ) );
        if ( 'dcv' === $norm || 'acv' === $norm || 'resistance' === $norm ) {
            $highlights[] = array(
                'name'  => $name,
                'value' => isset( $spec['value'] ) ? (string) $spec['value'] : '',
            );
        }
    }

    /* --------------------------------------------------------------------
     * 4. Spec matrix: ordered union of every spec name across all models.
     * -------------------------------------------------------------------- */
    $spec_matrix_names = array();
    $spec_matrix_data  = array();

    foreach ( $models as $mi => $m ) {
        $map = array();
        foreach ( $m['specs'] as $spec ) {
            $name = isset( $spec['name'] ) ? (string) $spec['name'] : '';
            if ( '' === $name ) {
                continue;
            }
            $map[ $name ] = isset( $spec['value'] ) ? (string) $spec['value'] : '';
            if ( ! isset( $spec_matrix_names[ $name ] ) ) {
                $spec_matrix_names[ $name ] = $name;
            }
        }
        $spec_matrix_data[ $mi ] = $map;
    }

    /* --------------------------------------------------------------------
     * 5. Datasheet — currently absent from the data, so the download link
     *    is a conditional container (checked on the parent then the model).
     * -------------------------------------------------------------------- */
    $datasheet_meta = get_post_meta( $product->get_id(), '_datasheet', true );
    if ( empty( $datasheet_meta ) && (int) $current_model['id'] !== (int) $product->get_id() ) {
        $datasheet_meta = get_post_meta( (int) $current_model['id'], '_datasheet', true );
    }

    $datasheet_url = '';
    if ( ! empty( $datasheet_meta ) ) {
        if ( is_numeric( $datasheet_meta ) ) {
            $datasheet_url = (string) wp_get_attachment_url( absint( $datasheet_meta ) );
        } else {
            $datasheet_url = (string) $datasheet_meta;
        }
    }

    /* --------------------------------------------------------------------
     * 6. Display values.
     * -------------------------------------------------------------------- */
    $series_title      = (string) $product->get_name();
    $short_description = (string) $product->get_short_description();
    $packaging         = isset( $current_model['packaging'] ) ? (string) $current_model['packaging'] : '';
    $current_sku       = '' !== $current_model['sku'] ? $current_model['sku'] : $current_model['label'];

    $inquiry_product_name = $series_title;
    if ( '' !== $current_model['label'] ) {
        $inquiry_product_name .= ' (' . $current_model['label'] . ')';
    }
    ?>

    <div class="container">

        <!-- Breadcrumbs: Home / Product Categories / {cat} / {series} -->
        <nav class="breadcrumbs" aria-label="<?php ew_esc_attr_e('Breadcrumb'); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
            <span class="breadcrumbs__sep">/</span>
            <a href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>">
                <?php ew_esc_html_e('Product Categories'); ?>
            </a>
            <?php
            $cat_terms = get_the_terms( $product->get_id(), 'product_cat' );
            if ( is_array( $cat_terms ) && ! is_wp_error( $cat_terms ) && ! empty( $cat_terms ) ) :
                $cat_term = $cat_terms[0];
                ?>
                <?php
                $cat_link = get_term_link( $cat_term );
                if ( ! is_wp_error( $cat_link ) ) :
                    ?>
                    <span class="breadcrumbs__sep">/</span>
                    <a href="<?php echo esc_url( $cat_link ); ?>">
                        <?php echo esc_html( $cat_term->name ); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <span class="breadcrumbs__sep">/</span>
            <span class="breadcrumbs__current"><?php echo esc_html( $series_title ); ?></span>
        </nav>

        <!-- Keep the series H1 available to search engines and assistive technology; the model is the visible product focus. -->
        <header class="series-header series-header--sr">
            <h1 class="screen-reader-text"><?php echo esc_html( $series_title ); ?></h1>
        </header>

        <?php
        /* --------------------------------------------------------------------
         * Product Guide banner（产品页 → 同语言指南文章出链）。
         * 映射在 inc/product-guides.php 的 ew_product_guide_map()：新增指南文章或
         * 上架新模型只需补一行映射。指南文章内部已链向产品，这里是反向链接，
         * 闭合文章 ↔ 产品双向环，同时给 Google 一条稳定发现通道——产品页是
         * Google 周期抓取的页面（product-sitemap3 是其唯一在反复拉的 sitemap），
         * 顺链即可摸到新文章。
         * -------------------------------------------------------------------- */
        $ew_guides = function_exists( 'ew_product_guide_links' )
            ? ew_product_guide_links( $product->get_id() )
            : array();

        if ( ! empty( $ew_guides ) ) {
            $ew_glabel = function_exists( 'ew_product_guide_label' )
                ? ew_product_guide_label()
                : array( 'eyebrow' => 'Product Guide', 'cta' => 'Read the Guide →' );
            foreach ( $ew_guides as $ew_g ) :
                ?>
                <div class="product-guide-banner">
                    <p class="product-guide-banner__text">
                        <span class="product-guide-banner__eyebrow"><?php echo esc_html( $ew_glabel['eyebrow'] ); ?></span>
                        <a class="product-guide-banner__title" href="<?php echo esc_url( $ew_g['url'] ); ?>"><?php echo esc_html( $ew_g['title'] ); ?></a>
                    </p>
                </div>
                <?php
            endforeach;
        }
        ?>

        <div class="product-detail product-detail--premium">
            <!-- Left gallery: current model _model_gallery (YITH Magnifier — UNI-T 同款结构) -->
            <div class="product-gallery product-gallery--premium">
                <?php if ( ! empty( $current_model['gallery'] ) ) : ?>
                    <?php
                    $gallery_ids     = array_values( array_filter( array_map( 'absint', $current_model['gallery'] ) ) );
                    $gallery_main_id = absint( $gallery_ids[0] );
                    ?>
                    <div class="images">
                        <input type="hidden" id="yith_wczm_traffic_light" value="free">
                        <div class="woocommerce-product-gallery__image">
                            <a href="<?php echo esc_url( wp_get_attachment_image_url( $gallery_main_id, 'full' ) ); ?>"
                               itemprop="image" class="yith_magnifier_zoom woocommerce-main-image"
                               title="<?php echo esc_attr( $current_model['label'] ); ?>">
                                <?php echo wp_get_attachment_image( $gallery_main_id, 'large', false, array(
                                    'class'         => 'attachment-shop_single size-shop_single wp-post-image',
                                    'loading'       => 'eager',
                                    'fetchpriority' => 'high',
                                    'decoding'      => 'async',
                                    'alt'           => esc_attr( $current_model['label'] ),
                                    'title'         => esc_attr( $current_model['label'] ),
                                ) ); ?>
                            </a>
                        </div>
                        <?php if ( count( $gallery_ids ) > 1 ) : ?>
                            <div class="thumbnails slider product-gallery__thumbs">
                                <ul class="yith_magnifier_gallery" data-columns="5" data-circular="yes"
                                    data-slider_infinite="yes" data-auto_carousel="no">
                                    <?php foreach ( $gallery_ids as $gi => $gid ) : ?>
                                        <?php
                                        $gid   = absint( $gid );
                                        $first = ( 0 === $gi );
                                        $last  = ( $gi === count( $gallery_ids ) - 1 );
                                        $li_cls = 'yith_magnifier_thumbnail'
                                            . ( $first ? ' first' : '' )
                                            . ( $last ? ' last' : '' );
                                        $a_cls  = 'yith_magnifier_thumbnail'
                                            . ( $first ? ' first active-thumbnail' : '' )
                                            . ( $last ? ' last' : '' );
                                        ?>
                                        <li class="<?php echo esc_attr( $li_cls ); ?>">
                                            <a href="<?php echo esc_url( wp_get_attachment_image_url( $gid, 'full' ) ); ?>"
                                               class="<?php echo esc_attr( $a_cls ); ?>"
                                               title="<?php echo esc_attr( $current_model['label'] ); ?>"
                                               data-small="<?php echo esc_url( wp_get_attachment_image_url( $gid, 'full' ) ); ?>">
                                                <img src="<?php echo esc_url( wp_get_attachment_image_url( $gid, 'thumbnail' ) ); ?>"
                                                     class="attachment-shop_thumbnail size-shop_thumbnail"
                                                     alt="<?php echo esc_attr( $current_model['label'] ); ?>"
                                                     loading="lazy">
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <input id="yith_wc_zm_carousel_controler" type="hidden" value="1">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ( has_post_thumbnail( $product->get_id() ) ) : ?>
                    <?php
                    $gallery_main_id = (int) get_post_thumbnail_id( $product->get_id() );
                    ?>
                    <div class="images">
                        <input type="hidden" id="yith_wczm_traffic_light" value="free">
                        <div class="woocommerce-product-gallery__image">
                            <a href="<?php echo esc_url( wp_get_attachment_image_url( $gallery_main_id, 'full' ) ); ?>"
                               itemprop="image" class="yith_magnifier_zoom woocommerce-main-image"
                               title="<?php echo esc_attr( $product->get_name() ); ?>">
                                <?php
                                echo get_the_post_thumbnail( $product->get_id(), 'large', array(
                                    'class'         => 'attachment-shop_single size-shop_single wp-post-image',
                                    'loading'       => 'eager',
                                    'fetchpriority' => 'high',
                                    'decoding'      => 'async',
                                    'title'         => $product->get_name(),
                                ) );
                                ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right column: model selector + highlights + CTA + inquiry form -->
            <div class="product-summary">
                <?php if ( count( $models ) > 1 ) : ?>
                    <div class="series-select">
                        <label for="series-model-select" class="series-select__label">
                            <?php ew_esc_html_e('Model'); ?>
                        </label>
                        <select id="series-model-select" name="attribute_pa_model" class="js-model-select">
                            <?php foreach ( $models as $m ) : ?>
                                <option value="<?php echo esc_attr( $m['url_model'] ); ?>" <?php selected( $m['url_model'], $current_model['url_model'] ); ?>>
                                    <?php echo esc_html( $m['label'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <h2 class="product-summary__model"><?php echo esc_html( $current_model['label'] ); ?></h2>

                <?php if ( ! empty( $highlights ) ) : ?>
                    <ul class="product-summary__specs">
                        <?php foreach ( $highlights as $highlight ) : ?>
                            <li>
                                <strong><?php echo esc_html( $highlight['name'] ); ?>:</strong>
                                <?php echo esc_html( $highlight['value'] ); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="product-summary__actions">
                    <a href="#inquiry" class="btn btn--inquiry btn--lg js-inquiry-cta"
                       data-model="<?php echo esc_attr( $current_sku ); ?>">
                        <?php ew_esc_html_e('Get a Quote'); ?>
                    </a>
                </div>

            </div>
        </div>

        <section class="product-inquiry-section product-inquiry-section--full" id="inquiry">
            <div class="product-inquiry-section__intro">
                <p class="product-inquiry-section__eyebrow"><?php ew_esc_html_e( 'Factory Direct' ); ?></p>
                <h2><?php ew_esc_html_e( 'Request a Quote for This Model' ); ?></h2>
                <p><?php ew_esc_html_e( 'Tell us your quantity, market and product requirements. Our team will respond within 24 hours.' ); ?></p>
            </div>
            <?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => $inquiry_product_name ) ); ?>
        </section>

        <!-- Product details -->
        <div class="product-tabs" id="product-detail-tabs">
            <div class="product-tabs__nav" role="tablist">
                <button class="product-tab is-active" type="button" role="tab" data-tab="overview" aria-controls="tab-Overview" aria-selected="true"><?php ew_esc_html_e('Overview'); ?></button>
                <button class="product-tab" type="button" role="tab" data-tab="information" aria-controls="tab-Information" aria-selected="false"><?php ew_esc_html_e('Information'); ?></button>
                <button class="product-tab" type="button" role="tab" data-tab="specifications" aria-controls="tab-Specifications" aria-selected="false"><?php ew_esc_html_e('Specification'); ?></button>
                <button class="product-tab" type="button" role="tab" data-tab="application" aria-controls="tab-Application" aria-selected="false"><?php ew_esc_html_e('Application'); ?></button>
                <button class="product-tab" type="button" role="tab" data-tab="general" aria-controls="tab-General" aria-selected="false"><?php ew_esc_html_e('General'); ?></button>
            </div>

            <!-- Tab panel: Overview (default active) -->
            <div class="product-tab__panel is-active" id="tab-Overview" role="tabpanel" aria-labelledby="Overview">
                <?php if ( '' !== trim( $short_description ) ) : ?><p class="product-overview__intro"><?php echo esc_html( $short_description ); ?></p><?php endif; ?>
                <?php
                $feature_raw = get_post_meta( $product->get_id(), '_model_features', true );
                if ( is_string( $feature_raw ) ) {
                    $feature_raw = json_decode( $feature_raw, true );
                }
                if ( ! is_array( $feature_raw ) && (int) $current_model['id'] !== (int) $product->get_id() ) {
                    $feature_raw = get_post_meta( (int) $current_model['id'], '_model_features', true );
                    if ( is_string( $feature_raw ) ) {
                        $feature_raw = json_decode( $feature_raw, true );
                    }
                }
                $feature_items = is_array( $feature_raw ) ? array_values( array_filter( array_map( 'trim', $feature_raw ) ) ) : array();
                ?>
                <h2 class="tab-panel-title"><?php ew_esc_html_e('Key Features'); ?></h2>
                <?php if ( ! empty( $feature_items ) ) : ?>
                    <ul class="product-features__list">
                        <?php foreach ( $feature_items as $feature_item ) : ?>
                            <li class="product-features__item"><?php echo esc_html( $feature_item ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif ( ! empty( $current_model['specs'] ) ) : ?>
                    <ul class="product-features__list">
                        <?php
                        $feature_keywords = array( 'true rms', 'auto range', 'display count' );
                        foreach ( $current_model['specs'] as $spec ) :
                            $spec_name = isset( $spec['name'] ) ? (string) $spec['name'] : '';
                            $spec_value = isset( $spec['value'] ) ? (string) $spec['value'] : '';
                            if ( '' === $spec_name ) {
                                continue;
                            }
                            $norm   = strtolower( trim( preg_replace( '/\s+/', ' ', $spec_name ) ) );
                            $is_key = false;
                            foreach ( $feature_keywords as $keyword ) {
                                if ( false !== strpos( $norm, $keyword ) ) {
                                    $is_key = true;
                                    break;
                                }
                            }
                            ?>
                            <li class="product-features__item<?php echo $is_key ? ' is-key' : ''; ?>">
                                <strong><?php echo esc_html( $spec_name ); ?>:</strong>
                                <?php echo esc_html( $spec_value ); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>


            <!-- Tab panel: Information -->
            <div class="product-tab__panel" id="tab-Information" role="tabpanel" aria-labelledby="Information">
                <h2 class="tab-panel-title"><?php ew_esc_html_e('Product Information'); ?></h2>
                <dl class="product-information">
                    <div><dt><?php ew_esc_html_e('Series'); ?></dt><dd><?php echo esc_html( $series_title ); ?></dd></div>
                    <div><dt><?php ew_esc_html_e('Model'); ?></dt><dd><?php echo esc_html( $current_model['label'] ); ?></dd></div>
                    <div><dt><?php ew_esc_html_e('SKU'); ?></dt><dd><?php echo esc_html( $current_sku ); ?></dd></div>
                    <?php foreach ( $highlights as $highlight ) : ?><div><dt><?php echo esc_html( $highlight['name'] ); ?></dt><dd><?php echo esc_html( $highlight['value'] ); ?></dd></div><?php endforeach; ?>
                </dl>
            </div>

            <!-- Tab panel: Specifications (spec matrix: Function | model1 | model2 | ...) -->
            <div class="product-tab__panel" id="tab-Specifications" role="tabpanel" aria-labelledby="Specifications">
                <h2 class="tab-panel-title"><?php ew_esc_html_e('Specifications'); ?></h2>
                <?php if ( ! empty( $spec_matrix_names ) ) : ?>
                    <table class="product-table-style2">
                        <thead>
                            <tr>
                                <th><?php ew_esc_html_e('Function'); ?></th>
                                <?php foreach ( $models as $m ) : ?>
                                    <th><?php echo esc_html( $m['label'] ); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $spec_matrix_names as $name ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $name ); ?></td>
                                    <?php foreach ( $spec_matrix_data as $map ) : ?>
                                        <?php $cell = isset( $map[ $name ] ) ? (string) $map[ $name ] : ''; ?>
                                        <td><?php echo '' !== $cell ? esc_html( $cell ) : '&mdash;'; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Tab panel: Applications (application scenarios, UNI-T style) -->
            <div class="product-tab__panel" id="tab-Application" role="tabpanel" aria-labelledby="Application">
                <h2 class="tab-panel-title"><?php ew_esc_html_e('Application'); ?></h2>
                <?php
                $app_text = (string) get_post_meta( $product->get_id(), '_model_applications', true );
                if ( '' === trim( $app_text ) && (int) $current_model['id'] !== (int) $product->get_id() ) {
                    $app_text = (string) get_post_meta( (int) $current_model['id'], '_model_applications', true );
                }
                if ( '' !== trim( $app_text ) ) :
                    $app_lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $app_text ) ) ) );
                    echo '<div class="product-applications__content"><ul>';
                    foreach ( $app_lines as $app_line ) {
                        echo '<li>' . esc_html( $app_line ) . '</li>';
                    }
                    echo '</ul></div>';
                else :
                    ?>
                    <p><?php ew_esc_html_e('This instrument is designed for electrical testing, installation and diagnostics across residential, commercial and industrial settings. Contact our team to discuss your specific application.'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Tab panel: Docs & Software (packaging + conditional datasheet download) -->
            <div class="product-tab__panel" id="tab-General" role="tabpanel" aria-labelledby="General">
                <h2 class="tab-panel-title"><?php ew_esc_html_e('General Information'); ?></h2>
                <?php if ( '' !== trim( $packaging ) || '' !== $datasheet_url ) : ?>
                    <?php if ( '' !== trim( $packaging ) ) : ?>
                        <div class="product-docs__packaging">
                            <h3><?php ew_esc_html_e('Packaging'); ?></h3>
                            <p><?php echo esc_html( $packaging ); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ( '' !== $datasheet_url ) : ?>
                        <div class="product-docs__datasheet">
                            <h3><?php ew_esc_html_e('Datasheet'); ?></h3>
                            <p>
                                <a href="<?php echo esc_url( $datasheet_url ); ?>" target="_blank" rel="noopener">
                                    <?php
                                    printf(
                                        /* translators: %s: model label */
                                        ew_esc_html__('Download %s datasheet (PDF)'),
                                        esc_html( $current_model['label'] )
                                    );
                                    ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php
        /* --------------------------------------------------------------------
         * Product Details — 实拍/详情长图(inc. _model_detail_images)。
         * 中文页取 'cn' 图,其余语言回退 'en';无图不渲染区块。
         * -------------------------------------------------------------------- */
        $ew_detail_ids = ew_get_model_detail_images( (int) $current_model['id'] );
        if ( ! empty( $ew_detail_ids ) ) :
            ?>
            <section class="product-details" data-animation-wrap>
                <h2 class="section-title" data-animation="1"><?php ew_esc_html_e('Product Details'); ?></h2>
                <div class="product-details__list">
                    <?php foreach ( $ew_detail_ids as $ew_did ) : ?>
                        <div class="product-details__item">
                            <?php
                            echo wp_get_attachment_image(
                                (int) $ew_did,
                                'full',
                                false,
                                array( 'loading' => 'lazy', 'decoding' => 'async' )
                            );
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        /* --------------------------------------------------------------------
         * FAQ (GEO) — visible accordion + FAQPage JSON-LD.
         * 问答由产品分类解析 (inc/geo-faq.php)；未命中分类回退通用问答。
         * FAQPage 结构供 AI 搜索 (ChatGPT/Perplexity/AI 概览) 抽取。
         * -------------------------------------------------------------------- */
        $ew_faq_key  = ew_geo_faq_for_product( $product->get_id() );
        $ew_faq_list = ew_geo_faq( $ew_faq_key );
        // 每个产品页都附加通用采购问答(起订量/OEM/认证/质保/物流/付款/样品/出口单证)，
        // 分类命中时避免重复渲染 general(分类=general 时不再追加)。
        if ( 'general' !== $ew_faq_key ) {
            $ew_faq_list = array_merge( $ew_faq_list, ew_geo_faq( 'general' ) );
        }
        if ( ! empty( $ew_faq_list ) ) :
            $ew_faq_i = 0;
            ?>
            <section class="product-faq" data-animation-wrap>
                <h2 class="section-title" data-animation="1"><?php ew_esc_html_e('FAQs'); ?></h2>
                <div class="product-faq__list">
                    <?php foreach ( $ew_faq_list as $ew_qa ) : ?>
                        <?php $ew_faq_i++; ?>
                        <details class="product-faq__item"<?php echo ( 1 === $ew_faq_i ) ? ' open' : ''; ?>>
                            <summary><?php echo ew_faq_emph( $ew_qa['q'] ); ?></summary>
                            <div class="product-faq__answer">
                                <p><?php echo ew_faq_emph( $ew_qa['a'] ); ?></p>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </section>

            <script type="application/ld+json">
            <?php
            $ew_faq_ld = array(
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => array(),
            );
            foreach ( $ew_faq_list as $ew_qa ) {
                $ew_faq_ld['mainEntity'][] = array(
                    '@type'          => 'Question',
                    'name'           => $ew_qa['q'],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => $ew_qa['a'],
                    ),
                );
            }
            echo wp_json_encode( $ew_faq_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            ?>
            </script>
        <?php endif; ?>

        <?php
        /* --------------------------------------------------------------------
         * Related products — same product_cat.
         * Falls back to recent products when the product has no category.
         * -------------------------------------------------------------------- */
        $cat_ids   = array();
        $cat_terms = get_the_terms( $product->get_id(), 'product_cat' );
        if ( is_array( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
            foreach ( $cat_terms as $cat_term ) {
                $cat_ids[] = (int) $cat_term->term_id;
            }
        }

        $related_args = array(
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'post__not_in'   => array( $product->get_id() ),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        );
        if ( ! empty( $cat_ids ) ) {
            $related_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $cat_ids,
                ),
            );
        }
        $related = new WP_Query( $related_args );

        if ( $related->have_posts() ) :
            ?>
            <section class="related-products" data-animation-wrap>
                <h2 class="section-title" data-animation="1"><?php ew_esc_html_e('Related Products'); ?></h2>
                <div class="products-grid">
                    <?php
                    $ew_rel_i = 2;
                    while ( $related->have_posts() ) :
                        $related->the_post();
                        $related_product = wc_get_product( get_the_ID() );
                        if ( $related_product instanceof WC_Product ) {
                            get_template_part( 'template-parts/product-card', null, array( 'product' => $related_product, 'anim' => $ew_rel_i++ ) );
                        }
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
            <?php
        endif;
        ?>
    </div>
    <?php
endwhile;

get_footer();
