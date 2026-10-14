<?php
/**
 * 侧栏分类树（MASTECH 风）：一级全大写 + ⌄ 折叠箭头，二级起 › 前缀缩进，默认全展开，当前项高亮
 * 用法：get_template_part( 'template-parts/sidebar-categories', null, array( 'current_cat_id' => int ) )
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$ew_current = isset( $args['current_cat_id'] ) ? (int) $args['current_cat_id'] : 0;

// 递归渲染子级列表（任意深度，默认展开）
$ew_sb_render = function ( $parent_id ) use ( &$ew_sb_render, $ew_current ) {
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => $parent_id,
		'exclude'    => ew_exclude_default_cat(),
	) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	$html = '<ul class="sb-sub">';
	foreach ( $terms as $t ) {
		$tid  = (int) $t->term_id;
		$kids = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => $tid, 'exclude' => ew_exclude_default_cat() ) );
		$has  = ! empty( $kids ) && ! is_wp_error( $kids );
		$html .= '<li class="' . ( $has ? 'has-sub active' : '' ) . ( $ew_current === $tid ? ' is-current' : '' ) . '">';
		$html .= '<a href="' . esc_url( get_term_link( $t ) ) . '" class="' . ( $ew_current === $tid ? 'on' : '' ) . '">' . esc_html( $t->name ) . '</a>';
		if ( $has ) {
			$html .= '<button type="button" class="sb-toggle" aria-label="Toggle submenu" aria-expanded="true">⌄</button>';
			$html .= $ew_sb_render( $tid );
		}
		$html .= '</li>';
	}
	return $html . '</ul>';
};

// 顶级分类固定顺序（EN slug 回映，兼容各语言翻译 slug）
$ew_order = array( 'voltage-testers', 'clamp-meters', 'others', 'digital-multimeters' );
$ew_top_cats = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
	'parent'     => 0,
	'exclude'    => ew_exclude_default_cat(),
) );
if ( empty( $ew_top_cats ) || is_wp_error( $ew_top_cats ) ) {
	return;
}
$ew_slug_index  = array_flip( $ew_order );
$ew_order_index = array();
foreach ( ew_cats_by_en_slug() as $ew_en_slug => $ew_cat ) {
	if ( isset( $ew_slug_index[ $ew_en_slug ] ) ) {
		$ew_order_index[ $ew_cat->term_id ] = $ew_slug_index[ $ew_en_slug ];
	}
}
usort( $ew_top_cats, function ( $a, $b ) use ( $ew_order_index ) {
	$ia = isset( $ew_order_index[ $a->term_id ] ) ? $ew_order_index[ $a->term_id ] : PHP_INT_MAX;
	$ib = isset( $ew_order_index[ $b->term_id ] ) ? $ew_order_index[ $b->term_id ] : PHP_INT_MAX;
	return $ia - $ib;
} );
?>
<ul class="sb-menu">
	<?php foreach ( $ew_top_cats as $ew_cat ) : ?>
		<?php
		$ew_cat_id = (int) $ew_cat->term_id;
		$ew_kids   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => $ew_cat_id, 'exclude' => ew_exclude_default_cat() ) );
		$ew_has    = ! empty( $ew_kids ) && ! is_wp_error( $ew_kids );
		?>
		<li class="<?php echo $ew_has ? 'has-sub active' : ''; ?><?php echo $ew_current === $ew_cat_id ? ' is-current' : ''; ?>">
			<a href="<?php echo esc_url( get_term_link( $ew_cat ) ); ?>" class="<?php echo $ew_current === $ew_cat_id ? 'on' : ''; ?>"><?php echo esc_html( $ew_cat->name ); ?></a>
			<?php if ( $ew_has ) : ?>
				<button type="button" class="sb-toggle" aria-label="Toggle submenu" aria-expanded="true">⌄</button>
				<?php echo $ew_sb_render( $ew_cat_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
