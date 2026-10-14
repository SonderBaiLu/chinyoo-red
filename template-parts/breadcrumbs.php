<?php
/**
 * Breadcrumb navigation
 */
?>
<nav class="breadcrumbs" aria-label="<?php ew_esc_attr_e('Breadcrumb'); ?>">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
    <?php if ( is_post_type_archive( 'product' ) || is_tax( 'product_category' ) ) : ?>
        <span class="breadcrumbs__sep">/</span>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">
            <?php ew_esc_html_e('Products'); ?>
        </a>
    <?php endif; ?>
    <?php if ( is_singular( 'product' ) ) : ?>
        <span class="breadcrumbs__sep">/</span>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">
            <?php ew_esc_html_e('Products'); ?>
        </a>
        <?php $terms = get_the_terms( get_the_ID(), 'product_category' ); ?>
        <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
            <span class="breadcrumbs__sep">/</span>
            <a href="<?php echo esc_url( get_term_link( $terms[0] ) ); ?>">
                <?php echo esc_html( $terms[0]->name ); ?>
            </a>
        <?php endif; ?>
        <span class="breadcrumbs__sep">/</span>
        <span class="breadcrumbs__current"><?php the_title(); ?></span>
    <?php elseif ( is_single() ) : ?>
        <span class="breadcrumbs__sep">/</span>
        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">
            <?php ew_esc_html_e('Blog'); ?>
        </a>
        <span class="breadcrumbs__sep">/</span>
        <span class="breadcrumbs__current"><?php the_title(); ?></span>
    <?php elseif ( is_page() ) : ?>
        <span class="breadcrumbs__sep">/</span>
        <span class="breadcrumbs__current"><?php the_title(); ?></span>
    <?php endif; ?>
</nav>
