<?php
/**
 * OEM / ODM — 定制服务（Logo / 量程 / 配色 / 包装 / MOQ）
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php ew_esc_html_e('OEM / ODM'); ?></span>
</div></div>

<div class="page-head page-head--premium page-head--oem" data-animation-wrap><div class="container">
	<p class="page-head__eyebrow" data-animation="1">CHINYOO / PRIVATE LABEL</p>
	<h1 data-animation="1"><?php ew_esc_html_e('OEM / ODM Services'); ?></h1>
	<p class="entry-content" data-animation="2"><?php ew_esc_html_e('Turnkey custom manufacturing for electrical measuring instruments — your brand, our factory.'); ?></p>
	<div class="certs"><span class="cert">CE</span><a class="cert" href="#certifications">RoHS</a><span class="cert">ISO 9001</span></div>
</div></div>

<main class="inner-page inner-page--oem">
<div class="container oem-main">
	<section class="oem-grid" data-animation-wrap>
		<div class="oem-card">
			<h3><?php ew_esc_html_e('Custom Logo'); ?></h3>
			<p><?php ew_esc_html_e('Your brand printed on the meter front panel, display overlay and packaging — with a free digital proof before mass production.'); ?></p>
		</div>
		<div class="oem-card">
			<h3><?php ew_esc_html_e('Custom Range'); ?></h3>
			<p><?php ew_esc_html_e('Adjust measuring ranges, resolution and accuracy to your target market\'s specs and local standards.'); ?></p>
		</div>
		<div class="oem-card">
			<h3><?php ew_esc_html_e('Custom Color'); ?></h3>
			<p><?php ew_esc_html_e('Housing colors, holster colors and button accents matched to your brand identity.'); ?></p>
		</div>
		<div class="oem-card">
			<h3><?php ew_esc_html_e('Custom Packaging'); ?></h3>
			<p><?php ew_esc_html_e('Blister, gift-box, color box, PVC or OEM-neutral carton — plus multilingual manuals.'); ?></p>
		</div>
		<div class="oem-card">
			<h3><?php ew_esc_html_e('Flexible MOQ'); ?></h3>
			<p><?php ew_esc_html_e('Low minimums for popular models; mixed-model containers supported.'); ?></p>
		</div>
		<div class="oem-card">
			<h3><?php ew_esc_html_e('Quality Control'); ?></h3>
			<p><?php ew_esc_html_e('100% calibration & function test before packing — with CE (EMC · LVD), RoHS (verified by DTI per IEC 62321) and ISO 9001 compliance documents.'); ?></p>
		</div>
	</section>

	<section class="oem-certs" id="certifications">
		<h2 class="sec-title"><?php ew_esc_html_e('RoHS Compliance Certificates'); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e('Factory-direct RoHS compliance for your private label, verified by DTI (Deesev Testing) in accordance with IEC 62321.'); ?></p>
		<div class="cert-grid">
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-doc-ee180805.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/e1fb503ae5fdfde4.jpg" alt="RoHS DoC — DTI EE180805" loading="lazy"><b>RoHS DoC</b><span><?php ew_esc_html_e('DTI EE180805 — Voltage Tester 1805D series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-report-ee180805.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/c2f7b6f65632fe36.jpg" alt="RoHS Report — DTI EE180805" loading="lazy"><b>RoHS Report</b><span><?php ew_esc_html_e('DTI EE180805 — Voltage Tester 1805D series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-doc-ee190805r.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/beb542ef58e4e18c.jpg" alt="RoHS DoC — DTI EE190805R" loading="lazy"><b>RoHS DoC</b><span><?php ew_esc_html_e('DTI EE190805R — Voltage Tester VD06 series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-report-ee190805r.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/6759683c0559b32b.jpg" alt="RoHS Report — DTI EE190805R" loading="lazy"><b>RoHS Report</b><span><?php ew_esc_html_e('DTI EE190805R — Voltage Tester VD06 series'); ?></span></a>
		</div>
	</section>

	<section class="inner-cta oem-cta" id="inquiry">
		<h2 class="sec-title"><?php ew_esc_html_e('Start Your OEM Project'); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e('Send your specs or target price — we reply within 24 hours with a quotation.'); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => 'OEM / ODM Project' ) ); ?>
	</section>
</div>
</main>
<?php
get_footer();
