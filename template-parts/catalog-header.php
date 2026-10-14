<?php
/** The directory and category pages share a header and breadcrumb hierarchy. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$title = isset( $args['title'] ) ? $args['title'] : ew__( 'Products' );
$description = isset( $args['description'] ) ? $args['description'] : '';
$overview = ! empty( $args['overview'] );
$current = isset( $args['current_cat_id'] ) ? (int) $args['current_cat_id'] : 0;
?>
<nav class="catalog-breadcrumbs" aria-label="<?php ew_esc_attr_e( 'Breadcrumb' ); ?>">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e( 'Home' ); ?></a><span aria-hidden="true">/</span>
    <?php if ( ! $overview ) : ?>
        <a href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>"><?php ew_esc_html_e( 'Products' ); ?></a><span aria-hidden="true">/</span>
        <?php foreach ( array_reverse( get_ancestors( $current, 'product_cat' ) ) as $ancestor_id ) :
            $ancestor = get_term( $ancestor_id, 'product_cat' );
            if ( ! $ancestor || is_wp_error( $ancestor ) ) { continue; }
            $url = get_term_link( $ancestor );
            if ( is_wp_error( $url ) ) { continue; }
        ?>
            <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $ancestor->name ); ?></a><span aria-hidden="true">/</span>
        <?php endforeach; ?>
    <?php endif; ?>
    <span aria-current="page"><?php echo esc_html( $overview ? ew__( 'Products' ) : $title ); ?></span>
</nav>
<header class="catalog-heading">
    <div>
        <p class="catalog-heading__eyebrow"><?php ew_esc_html_e( 'Factory-direct instruments' ); ?></p>
        <h1><?php echo esc_html( $title ); ?></h1>
        <?php if ( trim( wp_strip_all_tags( $description ) ) ) : ?>
            <div class="catalog-heading__description"><?php echo wp_kses_post( $description ); ?></div>
        <?php endif; ?>
    </div>
    <a class="catalog-heading__cta" href="<?php echo esc_url( ew_page_url( 'quote' ) ); ?>"><?php ew_esc_html_e( 'Get a Quote' ); ?> <span aria-hidden="true">↗</span></a>
</header>
