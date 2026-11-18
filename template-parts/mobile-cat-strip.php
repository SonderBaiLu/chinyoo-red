<?php
/**
 * 移动端吸顶分类条 —— 页眉第二行(随 sticky header 一起吸顶)
 * 结构:All Products → 4 大分类(带产品数),横向滑动胶囊
 * 仅在产品相关页渲染(分类页/产品列表页/产品详情页),由 header.php 按页类型加载
 * 用法:get_template_part('template-parts/mobile-cat-strip', null, array('current_cat_id' => int))
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$ew_current = isset( $args['current_cat_id'] ) ? (int) $args['current_cat_id'] : 0;
?>
<div class="hdr-cats">
	<div class="hdr-cats__in">
		<a class="hdr-cats__pill<?php echo $ew_current ? '' : ' on'; ?>" href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>"><?php ew_esc_html_e('All Products'); ?></a>
		<?php
		// 顶级主分类（固定顺序）收敛到 ew_top_categories()，与侧栏/页脚一致。
		foreach ( ew_top_categories() as $ew_cat ) :
			$ew_cid = (int) $ew_cat->term_id;
		?>
			<a class="hdr-cats__pill<?php echo $ew_current === $ew_cid ? ' on' : ''; ?>" href="<?php echo esc_url( get_term_link( $ew_cat ) ); ?>"><?php echo esc_html( $ew_cat->name ); ?> <span class="n"><?php echo (int) ew_cat_product_count( $ew_cid ); ?></span></a>
		<?php endforeach; ?>
	</div>
</div>
