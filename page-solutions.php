<?php
/**
 * Solutions — 行业方案（对标 UNI-T Applications & Solutions）
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php ew_esc_html_e('Solutions'); ?></span>
</div></div>

<div class="page-head page-head--premium page-head--solutions" data-animation-wrap><div class="container">
	<p class="page-head__eyebrow" data-animation="1">CHINYOO / APPLICATIONS</p>
	<h1 data-animation="1"><?php ew_esc_html_e('Applications & Solutions'); ?></h1>
	<p class="entry-content" data-animation="2"><?php ew_esc_html_e('Reliable electrical testing instruments for maintenance, automotive and home electrical work.'); ?></p>
	<div class="certs"><span class="cert">CE</span><span class="cert">RoHS</span><span class="cert">ISO 9001</span></div>
</div></div>

<main class="inner-page inner-page--solutions">
<div class="container sol-page">
	<div class="sol-grid sol-grid--premium" data-animation-wrap>
		<?php $ew_si = 1; ?>
		<?php foreach ( ew_scenarios() as $ew_slug => $ew_sc ) : ?>
			<a class="sol-card" href="<?php echo esc_url( ew_scenario_url( $ew_slug ) ); ?>" data-animation="<?php echo esc_attr( $ew_si++ ); ?>">
				<div class="sol-card__img">
					<?php if ( ! empty( $ew_sc['img'] ) ) : ?>
						<img class="sol-card__cover" src="<?php echo esc_url( wp_get_attachment_image_url( $ew_sc['img'], 'large' ) ); ?>" alt="<?php echo ew_esc_attr__( $ew_sc['title'] ); ?>" loading="lazy">
					<?php else : ?>
						<?php
						$ew_tiles = '';
						$ew_used  = 0;
						foreach ( ew_scenario_product_ids( $ew_slug, 8 ) as $ew_pid ) {
							// 用真实产品图(variable 优先 variation 的 _model_gallery[0],与详情页一致)。
							$ew_wc = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $ew_pid ) : null;
							$ew_url = '';
							if ( $ew_wc && $ew_wc->is_type( 'variable' ) ) {
								foreach ( (array) $ew_wc->get_children() as $ew_vid ) {
									$ew_g = get_post_meta( (int) $ew_vid, '_model_gallery', true );
									$ew_g = is_array( $ew_g ) ? $ew_g : ( json_decode( (string) $ew_g, true ) ?: array() );
									if ( ! empty( $ew_g ) ) {
										$ew_url = wp_get_attachment_image_url( (int) $ew_g[0], 'medium_large' );
										break;
									}
									$ew_vt = (int) get_post_thumbnail_id( (int) $ew_vid );
									if ( $ew_vt ) {
										$ew_url = wp_get_attachment_image_url( $ew_vt, 'medium_large' );
										break;
									}
								}
							}
							if ( ! $ew_url ) {
								$ew_tid = (int) get_post_thumbnail_id( (int) $ew_pid );
								if ( $ew_tid ) {
									$ew_url = wp_get_attachment_image_url( $ew_tid, 'medium_large' );
								}
							}
							if ( $ew_url ) {
								$ew_tiles .= '<img src="' . esc_url( $ew_url ) . '" alt="' . esc_attr( $ew_sc['title'] ) . '" loading="lazy">';
								if ( ++$ew_used >= 3 ) {
									break;
								}
							}
						}
						?>
						<?php if ( $ew_tiles ) : ?>
							<div class="sol-card__tiles"><?php echo $ew_tiles; ?></div>
						<?php else : ?>
							<span class="ph"><b><?php echo esc_html( $ew_sc['title'] ); ?></b></span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<h3><?php echo esc_html( $ew_sc['title'] ); ?></h3>
				<p><?php echo esc_html( $ew_sc['sub'] ); ?></p>
			</a>
		<?php endforeach; ?>
	</div>

	<section class="inner-cta" id="inquiry" data-animation-wrap>
		<div class="container">
			<h2 class="sec-title" data-animation="1"><?php ew_esc_html_e('Need a Custom Solution?'); ?></h2>
			<p class="sec-sub" data-animation="2"><?php ew_esc_html_e('Tell us your application — we\'ll recommend the right instruments and OEM options.'); ?></p>
			<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '', 'anim' => 3 ) ); ?>
		</div>
	</section>
</div>
</main>
<?php
get_footer();
