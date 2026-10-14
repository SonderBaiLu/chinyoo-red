<?php
/**
 * 移动端吸顶分类条 —— 页眉第二行(随 sticky header 一起吸顶)
 * 结构:All Products → 4 大分类(带产品数),横向滑动胶囊
 * 仅在产品相关页渲染(分类页/产品列表页/产品详情页),由 header.php 按页类型加载
 * 用法:get_template_part('template-parts/mobile-cat-strip', null, array('current_cat_id' => int))
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$ew_current = isset( $args['current_cat_id'] ) ? (int) $args['current_cat_id'] : 0;
$ew_order   = array( 'voltage-testers', 'clamp-meters', 'others', 'digital-multimeters' );
?>
<div class="hdr-cats">
	<div class="hdr-cats__in">
		<a class="hdr-cats__pill<?php echo $ew_current ? '' : ' on'; ?>" href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>"><?php ew_esc_html_e('All Products'); ?></a>
		<?php
		$ew_top = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'exclude'    => ew_exclude_default_cat(),
		) );
		if ( ! empty( $ew_top ) && ! is_wp_error( $ew_top ) ) :
			// 翻译 slug 带语言后缀 → 经 ew_cats_by_en_slug 回映 EN slug 再按固定顺序排(与侧栏一致)
			$ew_slug_index = array_flip( $ew_order );
			$ew_order_index = array();
			foreach ( ew_cats_by_en_slug() as $ew_en_slug => $ew_cat ) {
				if ( isset( $ew_slug_index[ $ew_en_slug ] ) ) {
					$ew_order_index[ (int) $ew_cat->term_id ] = $ew_slug_index[ $ew_en_slug ];
				}
			}
			usort( $ew_top, function ( $a, $b ) use ( $ew_order_index ) {
				$ia = isset( $ew_order_index[ (int) $a->term_id ] ) ? $ew_order_index[ (int) $a->term_id ] : PHP_INT_MAX;
				$ib = isset( $ew_order_index[ (int) $b->term_id ] ) ? $ew_order_index[ (int) $b->term_id ] : PHP_INT_MAX;
				return $ia - $ib;
			} );
			foreach ( $ew_top as $ew_cat ) :
				$ew_cid = (int) $ew_cat->term_id;
				?>
				<a class="hdr-cats__pill<?php echo $ew_current === $ew_cid ? ' on' : ''; ?>" href="<?php echo esc_url( get_term_link( $ew_cat ) ); ?>"><?php echo esc_html( $ew_cat->name ); ?> <span class="n"><?php echo (int) ew_cat_product_count( $ew_cid ); ?></span></a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
