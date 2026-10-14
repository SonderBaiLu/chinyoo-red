<?php
/**
 * Shared catalog card: product image, model numbers and working detail/inquiry links.
 * 用法：get_template_part( 'template-parts/product-catalog-card', null, array( 'product' => $p ) )
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( isset( $args['product'] ) && $args['product'] instanceof WC_Product ) {
	$product = $args['product'];
} else {
	return;
}

// 解析 _model_gallery 为附件 ID 数组
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

$pid  = (int) $product->get_id();
$perm = get_permalink( $pid );

// 名称：去掉尾部 " series"
$title = preg_replace( '/\s+series$/i', '', (string) $product->get_name() );

// 型号行：variable 取各变体 SKU，simple 取自身 SKU
$skus = array();
if ( $product->is_type( 'variable' ) ) {
	foreach ( (array) $product->get_children() as $vid ) {
		$sku = get_post_meta( (int) $vid, '_sku', true );
		$sku = is_string( $sku ) ? trim( $sku ) : '';
		if ( '' !== $sku && ! in_array( $sku, $skus, true ) ) {
			$skus[] = $sku;
		}
	}
} else {
	$sku = $product->get_sku();
	$sku = is_string( $sku ) ? trim( $sku ) : '';
	if ( '' !== $sku ) {
		$skus[] = $sku;
	}
}
$sku_line = implode( ', ', $skus );

// 图片：variable 优先变体 gallery[0]，simple 取 gallery[0]，兜底缩略图
$img_id = 0;
if ( $product->is_type( 'variable' ) ) {
	foreach ( (array) $product->get_children() as $vid ) {
		$g = ew_p4_gallery_ids( (int) $vid );
		if ( ! empty( $g ) ) {
			$img_id = (int) $g[0];
			break;
		}
		$t = (int) get_post_thumbnail_id( (int) $vid );
		if ( $t ) {
			$img_id = $t;
			break;
		}
	}
	if ( ! $img_id ) {
		$img_id = (int) get_post_thumbnail_id( $pid );
	}
} else {
	$g = ew_p4_gallery_ids( $pid );
	$img_id = ! empty( $g ) ? (int) $g[0] : (int) get_post_thumbnail_id( $pid );
}
$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : '';
if ( ! $img_url && defined( 'EW_THEME_URI' ) ) {
	$img_url = EW_THEME_URI . '/assets/images/placeholder.png';
}
?>
<article class="catalog-card">
 <?php if ( ! empty( $args['badge'] ) ) : ?><span class="catalog-card__badge"><?php echo esc_html( $args['badge'] ); ?></span><?php endif; ?>
 <a class="catalog-card__image" href="<?php echo esc_url( $perm ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
  <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" width="400" height="400">
 </a>
 <div class="catalog-card__body">
  <h3><a href="<?php echo esc_url( $perm ); ?>"><?php echo esc_html( $title ); ?></a></h3>
  <p class="catalog-card__models"><?php echo esc_html( $sku_line ?: $title ); ?></p>
  <div class="catalog-card__actions">
   <a href="<?php echo esc_url( $perm ); ?>"><?php ew_esc_html_e( 'Learn More' ); ?> <span aria-hidden="true">↗</span></a>
   <a class="catalog-card__inquiry" href="<?php echo esc_url( add_query_arg( 'inquiry_model', $sku_line ?: $title, $perm ) . '#inquiry' ); ?>"><?php ew_esc_html_e( 'Inquiry' ); ?></a>
  </div>
 </div>
</article>
