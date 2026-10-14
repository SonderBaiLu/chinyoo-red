<?php
/** Shared, keyboard-accessible category navigation for every catalog view. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$current = isset( $args['current_cat_id'] ) ? (int) $args['current_cat_id'] : 0;
$overview = ! empty( $args['overview'] ) || is_page( 'product-categories' );
$ancestors = $current ? array_map( 'intval', get_ancestors( $current, 'product_cat' ) ) : array();
$categories = ew_cats_by_en_slug();
$ordered = array();
foreach ( array( 'voltage-testers', 'clamp-meters', 'others', 'digital-multimeters' ) as $slug ) {
    if ( isset( $categories[ $slug ] ) ) { $ordered[ $slug ] = $categories[ $slug ]; }
}
$ordered += $categories;
$render_children = function ( $parent ) use ( &$render_children, $current ) {
    $children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent, 'hide_empty' => false, 'exclude' => ew_exclude_default_cat() ) );
    if ( is_wp_error( $children ) || ! $children ) { return; }
    echo '<ul class="catalog-nav__children">';
    foreach ( $children as $child ) {
        $url = get_term_link( $child );
        if ( is_wp_error( $url ) ) { continue; }
        echo '<li><a href="' . esc_url( $url ) . '"' . ( $current === (int) $child->term_id ? ' aria-current="page"' : '' ) . '>' . esc_html( $child->name ) . '</a>';
        $render_children( (int) $child->term_id );
        echo '</li>';
    }
    echo '</ul>';
};
?>
<details class="catalog-navigation" open>
    <summary><?php ew_esc_html_e( 'Categories' ); ?> <span aria-hidden="true">⌄</span></summary>
    <nav class="catalog-nav" aria-label="<?php ew_esc_attr_e( 'Product Categories' ); ?>">
        <a class="catalog-nav__all" href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>"<?php echo $overview ? ' aria-current="page"' : ''; ?>><?php ew_esc_html_e( 'All Products' ); ?> <span aria-hidden="true">↗</span></a>
        <?php foreach ( $ordered as $category ) :
            $id = (int) $category->term_id;
            $url = get_term_link( $category );
            if ( is_wp_error( $url ) ) { continue; }
            $active = $id === $current || in_array( $id, $ancestors, true );
        ?>
            <div class="catalog-nav__group<?php echo $active ? ' is-active' : ''; ?>">
                <a class="catalog-nav__parent" href="<?php echo esc_url( $url ); ?>"<?php echo $id === $current ? ' aria-current="page"' : ''; ?>>
                    <span><?php echo esc_html( $category->name ); ?></span>
                    <span class="catalog-nav__count"><?php echo (int) ew_cat_product_count( $id ); ?></span>
                </a>
                <?php $render_children( $id ); ?>
            </div>
        <?php endforeach; ?>
    </nav>
</details>
