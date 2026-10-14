<?php
/**
 * Products 总览页（/product-categories/）：左侧分类树 + Banner/New Arrival/Hot Sales + Featured 轮播 + 共享分类导航和产品卡片
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

// 浏览商品链接
$ew_browse_url = '';
if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) ) {
	$ew_browse_url = get_permalink( wc_get_page_id( 'shop' ) );
}
if ( ! $ew_browse_url ) {
	$ew_browse_url = get_post_type_archive_link( 'product' );
}
if ( ! $ew_browse_url ) {
	$ew_browse_url = ew_page_url( 'product-categories' );
}

// 解析 _model_gallery 为附件 ID 数组（与产品卡共用）
if ( ! function_exists( 'ew_p4_gallery_ids' ) ) {
	function ew_p4_gallery_ids( $pid ) {
		$raw = get_post_meta( (int) $pid, '_model_gallery', true );
		if ( is_array( $raw ) ) {
			$ids = $raw;
		} elseif ( is_string( $raw ) && '' !== trim( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$ids = is_array( $decoded ) ? $decoded : array_map( 'trim', explode( ',', $raw ) );
		} else {
			$ids = array();
		}
		return array_values( array_filter( array_map( 'absint', $ids ) ) );
	}
}

// 产品真实图（variable 优先变体 gallery[0]）
$ew_pimg = function ( $p, $size = 'medium' ) {
	$pid = (int) $p->get_id();
	$ids = array();
	if ( $p->is_type( 'variable' ) ) {
		foreach ( (array) $p->get_children() as $vid ) {
			$g = ew_p4_gallery_ids( (int) $vid );
			if ( ! empty( $g ) ) { $ids[] = (int) $g[0]; break; }
			$t = (int) get_post_thumbnail_id( (int) $vid );
			if ( $t ) { $ids[] = $t; break; }
		}
	} else {
		$g = ew_p4_gallery_ids( $pid );
		$ids[] = ! empty( $g ) ? (int) $g[0] : (int) get_post_thumbnail_id( $pid );
	}
	$ids[] = (int) get_post_thumbnail_id( $pid );
	foreach ( array_filter( $ids ) as $aid ) {
		$u = wp_get_attachment_image_url( $aid, $size );
		if ( $u ) { return $u; }
	}
	return '';
};

// 顶级分类固定顺序（Polylang 语言感知），其余顶级分类兜底追加
$ew_pc_map   = ew_cats_by_en_slug();
$ew_pc_order = array( 'voltage-testers', 'clamp-meters', 'others', 'digital-multimeters' );
$ew_pc_cats  = array();
foreach ( $ew_pc_order as $ew_slug ) {
	if ( isset( $ew_pc_map[ $ew_slug ] ) ) {
		$ew_pc_cats[] = $ew_pc_map[ $ew_slug ];
	}
}
foreach ( $ew_pc_map as $ew_slug => $ew_cat ) {
	if ( ! in_array( $ew_cat->term_id, wp_list_pluck( $ew_pc_cats, 'term_id' ), true ) ) {
		$ew_pc_cats[] = $ew_cat;
	}
}

// 每分类取 4 个产品（含子分类，按名称排序）
$ew_pc_products = function ( $cat_id, $limit = 4 ) {
	$q = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => (int) $cat_id, 'include_children' => true ) ),
	) );
	$ids = array();
	foreach ( $q->posts as $p ) { $ids[] = $p->ID; }
	wp_reset_postdata();
	return array_map( 'wc_get_product', $ids );
};

// 热销 / 新品
$ew_hot_q = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 6, 'no_found_rows' => true, 'meta_query' => array( array( 'key' => 'product_hot', 'value' => '1' ) ) ) );
$ew_hot = $ew_hot_q->have_posts() ? array_map( 'wc_get_product', wp_list_pluck( $ew_hot_q->posts, 'ID' ) ) : array();
$ew_new_q = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 6, 'no_found_rows' => true, 'orderby' => 'date', 'order' => 'DESC' ) );
$ew_new = $ew_new_q->have_posts() ? array_map( 'wc_get_product', wp_list_pluck( $ew_new_q->posts, 'ID' ) ) : array();
$ew_feat = ! empty( $ew_hot ) ? $ew_hot : $ew_new;
?>
<div class="catalog-shell">
	<div class="catalog-layout">
	<aside class="catalog-sidebar">
		<?php get_template_part( 'template-parts/catalog-sidebar', null, array( 'current_cat_id' => 0, 'overview' => true ) ); ?>
	</aside>

		<div class="catalog-content">
			<?php get_template_part( 'template-parts/catalog-header', null, array(
                'title' => ew__( 'Explore Our Product Range' ),
                'description' => ew__( 'Browse professional electrical measuring instruments by category, model and application.' ),
                'overview' => true,
            ) ); ?>

			<!-- Top merchandising: Banner + New Arrival / Hot Sales -->
		<div class="pc-top">
			<div class="pc-banner">
				<img src="<?php echo esc_url( EW_THEME_URI . '/assets/images/slide-2.jpg' ); ?>" alt="<?php ew_esc_attr_e('CHINYOO instruments'); ?>" fetchpriority="high">
				<div class="pc-banner__in">
					<h2><?php ew_esc_html_e('Measure with Precision'); ?></h2>
					<p><?php ew_esc_html_e('CHINYOO focuses on providing the best quality and innovative instruments with factory-direct support.'); ?></p>
				</div>
			</div>
			<div class="pc-promos">
				<a class="pc-promo" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', $ew_browse_url ) ); ?>">
					<span class="pc-promo__title"><?php ew_esc_html_e('New'); ?><br><?php ew_esc_html_e('Arrival'); ?></span>
					<span class="pc-promo__imgs">
						<?php foreach ( array_slice( $ew_new, 0, 3 ) as $ew_pp ) : if ( ! $ew_pp ) { continue; } $ew_u = $ew_pimg( $ew_pp ); if ( $ew_u ) : ?>
							<img src="<?php echo esc_url( $ew_u ); ?>" alt="<?php echo esc_attr( $ew_pp->get_name() ); ?>" loading="lazy">
						<?php endif; endforeach; ?>
					</span>
				</a>
				<a class="pc-promo" href="#catalog-featured">
					<span class="pc-promo__title"><?php ew_esc_html_e('Hot'); ?><br><?php ew_esc_html_e('Sales'); ?></span>
					<span class="pc-promo__imgs">
						<?php foreach ( array_slice( $ew_hot, 0, 3 ) as $ew_pp ) : if ( ! $ew_pp ) { continue; } $ew_u = $ew_pimg( $ew_pp ); if ( $ew_u ) : ?>
							<img src="<?php echo esc_url( $ew_u ); ?>" alt="<?php echo esc_attr( $ew_pp->get_name() ); ?>" loading="lazy">
						<?php endif; endforeach; ?>
					</span>
				</a>
			</div>
		</div>

		<!-- Featured Items 轮播 -->
		<?php if ( ! empty( $ew_feat ) ) : ?>
		<section class="pc-feat" aria-label="<?php ew_esc_attr_e( 'Featured Items' ); ?>">
			<div class="pc-feat__head">
				<h2><?php ew_esc_html_e('Featured Items'); ?></h2>
				<span class="pc-feat__nav">
					<button type="button" class="pc-feat__btn catalog-prev" aria-label="<?php ew_esc_attr_e('Previous'); ?>" aria-controls="catalog-featured">&#8249;</button>
					<button type="button" class="pc-feat__btn catalog-next" aria-label="<?php ew_esc_attr_e('Next'); ?>" aria-controls="catalog-featured">&#8250;</button>
				</span>
			</div>
			<div class="pc-feat__grid" id="catalog-featured">
				<?php foreach ( $ew_feat as $ew_p ) : if ( ! $ew_p ) { continue; } ?>
					<?php get_template_part( 'template-parts/product-catalog-card', null, array( 'product' => $ew_p ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

			<!-- Product directory: four products per category + View All -->
			<div id="product-directory" class="pc-directory__catalog">
			<?php foreach ( $ew_pc_cats as $ew_cat ) : ?>
			<?php
			$ew_cat_id    = (int) $ew_cat->term_id;
			$ew_cat_link  = get_term_link( $ew_cat );
			$ew_cat_items = $ew_pc_products( $ew_cat_id, 4 );
			if ( is_wp_error( $ew_cat_link ) || empty( $ew_cat_items ) ) {
				continue;
			}
			?>
			<section class="pc-cat-sec">
				<div class="pc-cat-sec__head">
					<h2><?php echo esc_html( $ew_cat->name ); ?></h2>
					<a class="pc-cat-sec__all" href="<?php echo esc_url( $ew_cat_link ); ?>"><?php ew_esc_html_e('View All'); ?> &#8250;</a>
				</div>
				<div class="pc-cat-sec__grid">
					<?php foreach ( $ew_cat_items as $ew_p ) : if ( ! $ew_p ) { continue; } ?>
						<?php get_template_part( 'template-parts/product-catalog-card', null, array( 'product' => $ew_p ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
<?php
get_footer();
