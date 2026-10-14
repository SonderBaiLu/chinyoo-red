<?php
/**
 * Contact Us — 询盘大表单 + WhatsApp 直连 + 工厂地址 + Google Maps（红白皮肤）
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php ew_esc_html_e('Contact Us'); ?></span>
</div></div>

<div class="page-head page-head--premium page-head--contact" data-animation-wrap><div class="container">
	<p class="page-head__eyebrow" data-animation="1">CHINYOO / FACTORY DIRECT</p>
	<h1 data-animation="1"><?php ew_esc_html_e('Contact Us'); ?></h1>
	<p class="entry-content" data-animation="2"><?php ew_esc_html_e('Tell us which instruments you need and your target quantity. Our sales team replies within 24 hours.'); ?></p>
	<div class="certs"><span class="cert">CE</span><span class="cert">RoHS</span><span class="cert">ISO 9001</span><span class="cert">OEM / ODM</span></div>
</div></div>

<main class="inner-page inner-page--contact">
<div class="container contact-grid">
	<div class="contact-info">
		<h3 class="sec-sub">Wenzhou Zhengyuan Electronics Co., Ltd. (CHINYOO)</h3>
		<p style="color:var(--muted);font-size:14px"><?php ew_esc_html_e('Factory-direct electrical measuring instruments — digital multimeters, voltage testers, clamp meters and special test tools.'); ?></p>

		<ul class="contact-list">
			<li>
				<strong>Chingy</strong> · WhatsApp <a href="https://wa.me/8617816800511" target="_blank" rel="noopener">+86 178 1680 0511</a><br>
				<a href="mailto:chinyoo08@gmail.com">chinyoo08@gmail.com</a>
			</li>
			<li>
				<strong>Jack</strong> · WhatsApp <a href="https://wa.me/8617706872345" target="_blank" rel="noopener">+86 177 0687 2345</a><br>
				<a href="mailto:sugon163zy@gmail.com">sugon163zy@gmail.com</a>
			</li>
			<li>
				<strong>Anna</strong> · WhatsApp <a href="https://wa.me/8617706873456" target="_blank" rel="noopener">+86 177 0687 3456</a><br>
				<a href="mailto:zhengyuan2101@gmail.com">zhengyuan2101@gmail.com</a>
			</li>
		</ul>

		<div class="contact-factory">
			<h4><?php ew_esc_html_e('Factory Address'); ?></h4>
			<p>Building 6, No.1 Hongjin Road, Daizhai Village, Tengqiao Town,<br>Lucheng District, Wenzhou, Zhejiang, China</p>
		</div>

		<div class="contact-map">
			<div class="contact-map__inner">
				<iframe
					src="https://www.google.com/maps?q=Hongjin%20Road%2C%20Tengqiao%20Town%2C%20Lucheng%20District%2C%20Wenzhou%2C%20Zhejiang%2C%20China&output=embed"
					width="100%" height="260" style="border:0" loading="lazy"
					referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				<div class="map-marker" aria-hidden="true">
					<div class="map-marker__card">
						<b>Wenzhou Zhengyuan Electronics</b>
						<span>Building 6, No.1 Hongjin Rd, Tengqiao, Wenzhou</span>
					</div>
					<span class="map-marker__pin"></span>
				</div>
			</div>
		</div>
	</div>

	<div class="contact-form-wrap">
		<h3 class="sec-sub"><?php ew_esc_html_e('Send an Inquiry'); ?></h3>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</div>
</div>
</main>
<?php
get_footer();
