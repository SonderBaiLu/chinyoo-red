<?php
/** Products, category, tag and WooCommerce search archives share the catalog UI. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
global $wp_query, $product;
$queried = get_queried_object();
$is_term = $queried instanceof WP_Term && in_array( $queried->taxonomy, array( 'product_cat', 'product_tag' ), true );
$current_cat_id = $is_term && 'product_cat' === $queried->taxonomy ? (int) $queried->term_id : 0;
$title = $is_term ? $queried->name : ( is_search() ? sprintf( ew__( 'Search Results for: %s' ), get_search_query() ) : ew__( 'All Products' ) );
$description = $is_term ? $queried->description : '';
// Imported descriptions often just repeat the category name.
if ( ! trim( wp_strip_all_tags( $description ) ) || trim( wp_strip_all_tags( $description ) ) === $title ) {
    $description = ew__( 'Browse professional electrical measuring instruments by category, model and application.' );
}
$discontinued = false;
if ( $is_term && 'product_tag' === $queried->taxonomy ) {
    $flag = strtolower( (string) get_term_meta( $queried->term_id, 'discontinued', true ) );
    $discontinued = in_array( $flag, array( '1', 'yes', 'true', 'on' ), true ) || false !== stripos( $queried->slug, 'discontinued' ) || false !== stripos( $title, 'discontinued' );
}
$subcategories = array();
$parent_id = 0;
if ( $current_cat_id ) {
    $parent_id = $current_cat_id;
    $subcategories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $current_cat_id, 'hide_empty' => false, 'exclude' => ew_exclude_default_cat() ) );
    // A leaf category shows its siblings; deeper categories show their children.
    if ( ! is_wp_error( $subcategories ) && ! $subcategories && $queried->parent ) {
        $parent_id = (int) $queried->parent;
        $subcategories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent_id, 'hide_empty' => false, 'exclude' => ew_exclude_default_cat() ) );
    }
}
?>
<div class="catalog-shell">
    <div class="catalog-layout">
        <aside class="catalog-sidebar">
            <?php get_template_part( 'template-parts/catalog-sidebar', null, array( 'current_cat_id' => $current_cat_id ) ); ?>
        </aside>
        <div class="catalog-content">
            <?php get_template_part( 'template-parts/catalog-header', null, array( 'title' => $title, 'description' => $description, 'current_cat_id' => $current_cat_id ) ); ?>
            <?php if ( $discontinued ) : ?>
                <p class="catalog-notice"><strong><?php ew_esc_html_e( 'Discontinued models' ); ?></strong> <?php ew_esc_html_e( '— stock & remaining units' ); ?></p>
            <?php endif; ?>
            <?php if ( $subcategories && ! is_wp_error( $subcategories ) ) : ?>
                <nav class="catalog-subcategories" aria-label="<?php ew_esc_attr_e( 'Categories' ); ?>">
                    <?php $parent_url = get_term_link( $parent_id, 'product_cat' ); if ( ! is_wp_error( $parent_url ) ) : ?>
                        <a href="<?php echo esc_url( $parent_url ); ?>"<?php echo $current_cat_id === $parent_id ? ' aria-current="page"' : ''; ?>><?php ew_esc_html_e( 'All' ); ?></a>
                    <?php endif; ?>
                    <?php foreach ( $subcategories as $subcategory ) :
                        $url = get_term_link( $subcategory );
                        if ( is_wp_error( $url ) ) { continue; }
                    ?>
                        <a href="<?php echo esc_url( $url ); ?>"<?php echo $current_cat_id === (int) $subcategory->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $subcategory->name ); ?> <span><?php echo (int) ew_cat_product_count( $subcategory->term_id ); ?></span></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <div class="catalog-toolbar">
                <p><strong><?php echo number_format_i18n( (int) $wp_query->found_posts ); ?></strong> <?php ew_esc_html_e( 'Products found' ); ?></p>
                <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : get_post_type_archive_link( 'product' ) ); ?>"><?php ew_esc_html_e( 'All Products' ); ?> <span aria-hidden="true">↗</span></a>
            </div>
            <?php if ( class_exists( 'Chinyoo_Product_Filter' ) ) { Chinyoo_Product_Filter::render_active_chips(); } ?>
            <?php if ( have_posts() ) : ?>
                <div class="catalog-grid">
                    <?php
                    $backup = isset( $product ) ? $product : null;
                    while ( have_posts() ) :
                        the_post();
                        $product = wc_get_product( get_the_ID() );
                        if ( ! $product instanceof WC_Product ) { continue; }
                        get_template_part( 'template-parts/product-catalog-card', null, array( 'product' => $product, 'badge' => $discontinued ? ew__( 'Discontinued' ) : '' ) );
                    endwhile;
                    $product = $backup;
                    ?>
                </div>
                <nav class="pagination" aria-label="<?php ew_esc_attr_e( 'Products' ); ?>">
                    <?php echo paginate_links( array( 'prev_text' => is_rtl() ? '»' : '«', 'next_text' => is_rtl() ? '«' : '»', 'mid_size' => 2 ) ); ?>
                </nav>
            <?php else : ?>
                <div class="catalog-empty"><p><?php ew_esc_html_e( 'No products found.' ); ?></p><a href="<?php echo esc_url( $current_cat_id ? get_term_link( $queried ) : ew_page_url( 'product-categories' ) ); ?>"><?php ew_esc_html_e( 'Browse Products' ); ?> →</a></div>
            <?php endif; ?>
            <?php
            $guides = $current_cat_id && function_exists( 'ew_category_guide_links' ) ? ew_category_guide_links( $current_cat_id ) : array();
            if ( $guides ) : ?>
                <section class="catalog-guides">
                    <h2><?php echo esc_html( function_exists( 'ew_category_guide_label' ) ? ew_category_guide_label() : ew__( 'Related Guides' ) ); ?></h2>
                    <div><?php foreach ( $guides as $guide ) : ?><a href="<?php echo esc_url( $guide['url'] ); ?>"><?php echo esc_html( $guide['title'] ); ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?></div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer();
