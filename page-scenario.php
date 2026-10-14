<?php
/**
 * Scenario detail page — 应用场景页模板。
 *
 * 自动匹配 /solutions/{slug}/ 的页面；数据来自 inc/scenarios.php。
 * 页内按场景关键词从 `_model_applications` 拉出适用产品，渲染产品卡网格 + 询盘表单。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ew_sc_slug = get_post_field( 'post_name', get_queried_object_id() );
$ew_sc      = ew_scenario_by_slug( $ew_sc_slug );
if ( ! $ew_sc ) {
    $ew_sc = array( 'title' => get_the_title() ?: 'Solutions', 'sub' => '', 'keywords' => array() );
    $ew_sc_slug = '';
}

get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><a href="<?php echo esc_url( ew_page_url( 'solutions' ) ); ?>"><?php ew_esc_html_e('Solutions'); ?></a>
	<span> › </span><span><?php ew_esc_html_e( $ew_sc['title'] ); ?></span>
</div></div>

<div class="page-head page-head--premium page-head--scenario" data-animation-wrap><div class="container">
	<p class="page-head__eyebrow" data-animation="1">CHINYOO / SOLUTION</p>
	<h1 data-animation="1"><?php ew_esc_html_e( $ew_sc['title'] ); ?></h1>
	<?php if ( ! empty( $ew_sc['sub'] ) ) : ?>
		<p class="entry-content" data-animation="2"><?php ew_esc_html_e( $ew_sc['sub'] ); ?></p>
	<?php endif; ?>
	<div class="certs"><span class="cert">CE</span><span class="cert">RoHS</span><span class="cert">ISO 9001</span></div>
</div></div>

<main class="inner-page inner-page--scenario">
<div class="container sol-page">
	<?php
	// 深度内容区块(测什么/怎么测/安全提示/适用仪表)——SEO 长尾词载体,放产品网格前
	$ew_guide = $ew_sc_slug ? ew_scenario_content( $ew_sc_slug ) : array();
	if ( ! empty( $ew_guide ) ) :
		?>
		<div class="scen-guide" data-animation-wrap>
			<?php $ew_gb = 0; ?>
			<?php if ( ! empty( $ew_guide['what'] ) ) : ?>
				<section class="scen-guide__block" data-animation="<?php echo esc_attr( ++$ew_gb ); ?>">
					<h2 class="scen-guide__title"><?php echo esc_html( $ew_guide['what']['heading'] ); ?></h2>
					<p class="scen-guide__intro"><?php echo esc_html( $ew_guide['what']['intro'] ); ?></p>
					<ul class="scen-guide__list">
						<?php foreach ( $ew_guide['what']['items'] as $ew_li ) : ?>
							<li><?php echo esc_html( $ew_li ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $ew_guide['how'] ) ) : ?>
				<section class="scen-guide__block" data-animation="<?php echo esc_attr( ++$ew_gb ); ?>">
					<h2 class="scen-guide__title"><?php echo esc_html( $ew_guide['how']['heading'] ); ?></h2>
					<p class="scen-guide__intro"><?php echo esc_html( $ew_guide['how']['intro'] ); ?></p>
					<ol class="scen-guide__steps">
						<?php foreach ( $ew_guide['how']['steps'] as $ew_li ) : ?>
							<li><?php echo esc_html( $ew_li ); ?></li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $ew_guide['tips'] ) ) : ?>
				<section class="scen-guide__block scen-guide__block--tips" data-animation="<?php echo esc_attr( ++$ew_gb ); ?>">
					<h2 class="scen-guide__title"><?php echo esc_html( $ew_guide['tips']['heading'] ); ?></h2>
					<p class="scen-guide__intro"><?php echo esc_html( $ew_guide['tips']['intro'] ); ?></p>
					<ul class="scen-guide__list">
						<?php foreach ( $ew_guide['tips']['items'] as $ew_li ) : ?>
							<li><?php echo esc_html( $ew_li ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $ew_guide['recommended'] ) ) : ?>
				<section class="scen-guide__block scen-guide__block--kit" data-animation="<?php echo esc_attr( ++$ew_gb ); ?>">
					<h2 class="scen-guide__title"><?php echo esc_html( $ew_guide['recommended']['heading'] ); ?></h2>
					<p class="scen-guide__intro"><?php echo esc_html( $ew_guide['recommended']['intro'] ); ?></p>
					<ul class="scen-guide__kit">
						<?php foreach ( $ew_guide['recommended']['items'] as $ew_kit ) : ?>
							<li class="scen-guide__kit-item">
								<b class="scen-guide__kit-name"><?php echo ew_scen_kit_name_html( isset( $ew_kit['name'] ) ? $ew_kit['name'] : '', $ew_kit ); // 已转义,连到对应产品页 ?></b>
								<span class="scen-guide__kit-why"><?php echo esc_html( $ew_kit['why'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
	$ew_ids = $ew_sc_slug ? ew_scenario_product_ids( $ew_sc_slug, 24 ) : array();
	if ( ! empty( $ew_ids ) ) :
		?>
		<section class="solution-products" data-animation-wrap>
			<h2 class="solution-products__title" data-animation="1"><?php ew_esc_html_e( 'Recommended Instruments' ); ?></h2>
			<div class="prod-grid">
			<?php
			$ew_backup = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
			$ew_gi = 1;
			foreach ( $ew_ids as $ew_pid ) {
				$ew_prod = wc_get_product( $ew_pid );
				if ( ! $ew_prod instanceof WC_Product ) {
					continue;
				}
				get_template_part( 'template-parts/product-card', null, array( 'product' => $ew_prod, 'anim' => $ew_gi++ ) );
			}
			$GLOBALS['product'] = $ew_backup;
			?>
			</div>
			</section>
		<p class="sol-page__more"><a href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>"><?php ew_esc_html_e('Browse the full product range →'); ?></a></p>
	<?php else : ?>
		<p><?php ew_esc_html_e('Products for this application are being updated — contact us for recommendations.'); ?></p>
	<?php endif; ?>

	<section class="inner-cta" id="inquiry" data-animation-wrap>
		<div class="container">
			<h2 class="sec-title" data-animation="1"><?php printf( ew_esc_html__('Get a Quote for %s Tools'), ew_esc_html__( $ew_sc['title'] ) ); ?></h2>
			<p class="sec-sub" data-animation="2"><?php ew_esc_html_e('Tell us your application and target quantity — we\'ll reply within 24 hours.'); ?></p>
			<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => $ew_sc['title'], 'anim' => 3 ) ); ?>
		</div>
	</section>
</div>
</main>
<?php
get_footer();
