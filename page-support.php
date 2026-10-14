<?php
/**
 * Services & Support — Downloads / FAQs / OEM / 询盘
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php ew_esc_html_e('Services & Support'); ?></span>
</div></div>

<div class="page-head page-head--premium page-head--support" data-animation-wrap><div class="container">
	<p class="page-head__eyebrow" data-animation="1">CHINYOO / PARTNER CARE</p>
	<h1 data-animation="1"><?php ew_esc_html_e('Services & Support'); ?></h1>
	<p class="entry-content" data-animation="2"><?php ew_esc_html_e('Downloads, FAQs, OEM & ODM — everything you need after the sale.'); ?></p>
</div></div>

<main class="inner-page inner-page--support">
<div class="container">
	<div class="support-intro"><p>Resources, technical answers and factory-direct support for every stage of your business.</p></div>
	<div class="support-grid">
	<a class="support-card" href="<?php echo esc_url( ew_page_url( 'downloads' ) ); ?>">
		<b><?php ew_esc_html_e('Downloads'); ?></b>
		<span><?php ew_esc_html_e('Datasheets, certifications and manuals'); ?></span>
	</a>
	<a class="support-card" href="<?php echo esc_url( ew_page_url( 'faqs' ) ); ?>">
		<b><?php ew_esc_html_e('FAQs'); ?></b>
		<span><?php ew_esc_html_e('Common questions about our products'); ?></span>
	</a>
	<a class="support-card" href="<?php echo esc_url( ew_page_url( 'oem' ) ); ?>">
		<b><?php ew_esc_html_e('OEM / ODM'); ?></b>
		<span><?php ew_esc_html_e('Custom logo, range, color and packaging'); ?></span>
	</a>
	<a class="support-card" href="<?php echo esc_url( ew_page_url( 'contact-us' ) ); ?>">
		<b><?php ew_esc_html_e('Where to Buy'); ?></b>
		<span><?php ew_esc_html_e('Factory-direct wholesale and agent pricing'); ?></span>
	</a>
	</div>
</div>

<section class="inner-cta" id="inquiry" style="padding:40px 0">
	<div class="container">
		<h2 class="sec-title"><?php ew_esc_html_e('Still Have Questions?'); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e('Our team replies within 24 hours.'); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => 'Support' ) ); ?>
	</div>
</section>
</main>
<?php
get_footer();
