<?php
/**
 * 产品卡（B2B 风）：图 + 标题 + 型号 + 亮点 + Learn More / Inquiry 按钮
 * 用法：get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'anim' => 2, 'badge' => 'Discontinued' ) )
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( isset( $args['product'] ) && $args['product'] instanceof WC_Product ) {
	$product = $args['product'];
} elseif ( ! isset( $product ) || ! $product instanceof WC_Product ) {
	global $product;
}
if ( ! $product instanceof WC_Product && function_exists( 'wc_get_product' ) ) {
	$product = wc_get_product( get_the_ID() );
}
if ( ! $product instanceof WC_Product ) {
	return;
}

$card_badge = isset( $args['badge'] ) && is_string( $args['badge'] ) ? trim( $args['badge'] ) : '';

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

$product_id  = (int) $product->get_id();
$is_variable = $product->is_type( 'variable' );
$permalink   = get_permalink( $product_id );

// 标题去掉尾部 " series"
$title = preg_replace( '/\s+series$/i', '', (string) $product->get_name() );
if ( '' === $title ) {
	$title = (string) $product->get_name();
}

// 型号行：variable 取各变体 SKU；simple 取自身 SKU（非 EN 翻译缺 SKU 时回退 EN 原产品变体）
$model_skus = array();
if ( $is_variable ) {
	foreach ( (array) $product->get_children() as $vid ) {
		$vid = (int) $vid;
		$sku = '';
		$variation = function_exists( 'wc_get_product' ) ? wc_get_product( $vid ) : null;
		if ( $variation instanceof WC_Product ) {
			$sku = $variation->get_sku();
		}
		if ( ! is_string( $sku ) || '' === trim( $sku ) ) {
			$sku = get_post_meta( $vid, '_sku', true );
		}
		$sku = is_string( $sku ) ? trim( $sku ) : '';
		if ( '' !== $sku && ! in_array( $sku, $model_skus, true ) ) {
			$model_skus[] = $sku;
		}
	}
} else {
	$sku = $product->get_sku();
	if ( ! is_string( $sku ) || '' === trim( $sku ) ) {
		$sku = get_post_meta( $product_id, '_sku', true );
	}
	$sku = is_string( $sku ) ? trim( $sku ) : '';
	if ( '' !== $sku ) {
		$model_skus[] = $sku;
	} elseif ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang && $lang !== pll_default_language() ) {
			$en_id = pll_get_post( $product_id, pll_default_language() );
			if ( $en_id && (int) $en_id !== (int) $product_id ) {
				$en_product = function_exists( 'wc_get_product' ) ? wc_get_product( $en_id ) : null;
				if ( $en_product instanceof WC_Product && $en_product->is_type( 'variable' ) ) {
					foreach ( (array) $en_product->get_children() as $vid ) {
						$en_v = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $vid ) : null;
						$vsku = $en_v instanceof WC_Product ? $en_v->get_sku() : '';
						if ( ! is_string( $vsku ) || '' === trim( $vsku ) ) {
							$vsku = get_post_meta( (int) $vid, '_sku', true );
						}
						$vsku = is_string( $vsku ) ? trim( $vsku ) : '';
						if ( '' !== $vsku && ! in_array( $vsku, $model_skus, true ) ) {
							$model_skus[] = $vsku;
						}
					}
				}
			}
		}
	}
}
$models_line = implode( ', ', $model_skus );

// 图片：variable 优先变体 gallery[0]（真实产品照），simple 取 gallery[0]，兜底缩略图/占位图
$img_id = 0;
if ( $is_variable ) {
	foreach ( (array) $product->get_children() as $vid ) {
		$v_gallery = ew_p4_gallery_ids( (int) $vid );
		if ( ! empty( $v_gallery ) ) {
			$img_id = (int) $v_gallery[0];
			break;
		}
		$v_thumb = (int) get_post_thumbnail_id( (int) $vid );
		if ( $v_thumb ) {
			$img_id = $v_thumb;
			break;
		}
	}
	if ( ! $img_id ) {
		$img_id = (int) get_post_thumbnail_id( $product_id );
	}
} else {
	$simple_gallery = ew_p4_gallery_ids( $product_id );
	$img_id = ! empty( $simple_gallery ) ? (int) $simple_gallery[0] : (int) get_post_thumbnail_id( $product_id );
}
$thumb_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : '';
$thumb_url = is_string( $thumb_url ) ? $thumb_url : '';
if ( '' === $thumb_url && defined( 'EW_THEME_URI' ) ) {
	$thumb_url = EW_THEME_URI . '/assets/images/placeholder.png';
}

// 亮点行：从 _model_specs 提取 Display Count / True RMS
$highlight_parts = array();
$specs_raw = '';
if ( $is_variable ) {
	$children = (array) $product->get_children();
	if ( ! empty( $children ) ) {
		$specs_raw = get_post_meta( (int) $children[0], '_model_specs', true );
	}
} else {
	$specs_raw = get_post_meta( $product_id, '_model_specs', true );
}
$specs = array();
if ( is_array( $specs_raw ) ) {
	$specs = $specs_raw;
} elseif ( is_string( $specs_raw ) && '' !== trim( $specs_raw ) ) {
	$decoded = json_decode( $specs_raw, true );
	if ( is_array( $decoded ) ) {
		$specs = $decoded;
	}
}
foreach ( $specs as $spec ) {
	if ( ! is_array( $spec ) ) {
		continue;
	}
	$name  = isset( $spec['name'] ) ? (string) $spec['name'] : '';
	$value = isset( $spec['value'] ) ? (string) $spec['value'] : '';
	if ( '' === $name || '' === $value ) {
		continue;
	}
	if ( preg_match( '/display\s*count/i', $name ) ) {
		$highlight_parts[] = sprintf( ew__( 'Display Count: %s' ), $value );
	} elseif ( preg_match( '/true[\s\-_]*rms/i', $name ) ) {
		$highlight_parts[] = sprintf( ew__( 'True RMS: %s' ), $value );
	}
}
$highlight_line = implode( ' · ', $highlight_parts );
?>
<div class="product-card<?php echo '' !== $card_badge ? ' product-card--has-badge' : ''; ?>"<?php if ( isset( $args['anim'] ) && '' !== (string) $args['anim'] ) { echo ' data-animation="' . esc_attr( $args['anim'] ) . '"'; } ?>>
	<?php if ( '' !== $card_badge ) : ?>
		<span class="product-card__badge"><?php echo esc_html( $card_badge ); ?></span>
	<?php endif; ?>
	<a href="<?php echo esc_url( $permalink ); ?>" class="product-card__image" aria-label="<?php echo esc_attr( $title ); ?>">
		<?php if ( '' !== $thumb_url ) : ?>
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
		<?php else : ?>
			<span class="product-card__placeholder"><?php ew_esc_html_e('No image'); ?></span>
		<?php endif; ?>
	</a>
	<div class="product-card__content">
		<h3 class="product-card__title woocommerce-loop-product__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>
		<?php if ( '' !== $models_line ) : ?>
			<span class="product-card__model"><?php echo esc_html( $models_line ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $highlight_line ) : ?>
			<p class="product-card__highlight"><?php echo esc_html( $highlight_line ); ?></p>
		<?php endif; ?>
		<div class="product-card__actions">
			<a href="<?php echo esc_url( $permalink ); ?>" class="btn btn--outline btn--sm">
				<?php ew_esc_html_e('Learn More'); ?>
			</a>
			<?php
			// 询盘统一跳到报价页，型号经 inquiry_model 参数预填
			$inquiry_href = add_query_arg( 'inquiry_model', $models_line, ew_page_url( 'quote' ) ) . '#inquiry';
			?>
			<a href="<?php echo esc_url( $inquiry_href ); ?>"
			   class="btn btn--inquiry btn--sm js-inquiry-cta"
			   data-model="<?php echo esc_attr( $models_line ); ?>">
				<?php ew_esc_html_e('Inquiry'); ?>
			</a>
		</div>
	</div>
</div>
