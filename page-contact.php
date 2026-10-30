<?php
/**
 * Contact Us — How can we help you? 四个标签（图1）：
 * Contact Sales / Contact Technical Support / Contact Service / Online Order Support
 * + 工厂地址 + Google Maps
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
<div class="container">

	<section class="contact-help" data-animation-wrap>
		<h2 class="contact-help__title" data-animation="1"><?php ew_esc_html_e('How can we help you?'); ?></h2>

		<div class="contact-tabs" role="tablist" data-animation="2">
			<button type="button" role="tab" class="contact-tab is-active" data-tab="sales" aria-controls="ctab-sales" aria-selected="true"><?php ew_esc_html_e('Contact Sales'); ?></button>
			<button type="button" role="tab" class="contact-tab" data-tab="technical" aria-controls="ctab-technical" aria-selected="false"><?php ew_esc_html_e('Contact Technical Support'); ?></button>
			<button type="button" role="tab" class="contact-tab" data-tab="service" aria-controls="ctab-service" aria-selected="false"><?php ew_esc_html_e('Contact Service'); ?></button>
			<button type="button" role="tab" class="contact-tab" data-tab="order" aria-controls="ctab-order" aria-selected="false"><?php ew_esc_html_e('Online Order Support'); ?></button>
		</div>

		<div class="contact-tab__panels">

			<!-- 标签 1：Contact Sales -->
			<div class="contact-tab__panel is-active" id="ctab-sales" role="tabpanel">
				<div class="contact-help__cards">
					<div class="help-card">
						<span class="help-card__icon" aria-hidden="true">✉</span>
						<h3><?php ew_esc_html_e('Email Us'); ?></h3>
						<p><?php ew_esc_html_e('Send your target models, quantity or sourcing list — our engineers reply with a quotation and lead time.'); ?></p>
						<a class="help-card__link" href="mailto:chingycai@chinyoo.com"><?php ew_esc_html_e('Click Here'); ?> <span aria-hidden="true">→</span></a>
						<span class="help-card__note">chingycai@chinyoo.com</span>
					</div>
					<div class="help-card">
						<span class="help-card__icon" aria-hidden="true">☎</span>
						<h3><?php ew_esc_html_e('Phone Support'); ?></h3>
						<p class="help-card__line"><span><?php ew_esc_html_e('International Line'); ?>:</span> <a href="tel:+8617816800511">+86 178 1680 0511</a></p>
						<p class="help-card__line"><span><?php ew_esc_html_e('Local Number'); ?>:</span> <a href="tel:+8617706872345">+86 177 0687 2345</a></p>
						<a class="help-card__link" href="https://wa.me/8617816800511" target="_blank" rel="noopener"><?php ew_esc_html_e('Chat on WhatsApp'); ?> <span aria-hidden="true">→</span></a>
					</div>
				</div>
				<div class="ctab-form">
					<h3 class="sec-sub"><?php ew_esc_html_e('Send an Inquiry'); ?></h3>
					<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
				</div>
			</div>

			<!-- 标签 2：Contact Technical Support -->
			<div class="contact-tab__panel" id="ctab-technical" role="tabpanel">
				<div class="contact-help__cards">
					<div class="help-card">
						<span class="help-card__icon" aria-hidden="true">⚙</span>
						<h3><?php ew_esc_html_e('Technical Support'); ?></h3>
						<p><?php ew_esc_html_e('Questions about specs, ranges, accuracy, or which model fits your application — our engineers help you choose the right instrument.'); ?></p>
						<div class="region-item__contact">
							<span><strong>Jack</strong></span>
							<span>WhatsApp <a href="https://wa.me/8617706872345" target="_blank" rel="noopener">+86 177 0687 2345</a></span>
							<span><a href="mailto:jack@chinyoo.com">jack@chinyoo.com</a></span>
						</div>
					</div>
					<div class="help-card">
						<span class="help-card__icon" aria-hidden="true">📄</span>
						<h3><?php ew_esc_html_e('Datasheets & Documents'); ?></h3>
						<p><?php ew_esc_html_e('Download datasheets, CE / RoHS certificates and the full product catalog from our resource center.'); ?></p>
						<a class="help-card__link" href="<?php echo esc_url( ew_page_url( 'downloads' ) ); ?>"><?php ew_esc_html_e('Open Downloads'); ?> <span aria-hidden="true">→</span></a>
					</div>
				</div>
			</div>

			<!-- 标签 3：Contact Service -->
			<div class="contact-tab__panel" id="ctab-service" role="tabpanel">
				<p class="contact-regions__lead"><?php ew_esc_html_e('Service, Repair and Calibration — choose your region to reach the engineer responsible for your market.'); ?></p>
				<div class="region-accordion">
					<details class="region-item">
						<summary><?php ew_esc_html_e('Americas'); ?><span class="region-item__chev" aria-hidden="true"></span></summary>
						<div class="region-item__body">
							<p><?php ew_esc_html_e('North and South America — after-sales support, repair and calibration.'); ?></p>
							<div class="region-item__contact">
								<span><strong>Chingy</strong></span>
								<span>WhatsApp <a href="https://wa.me/8617816800511" target="_blank" rel="noopener">+86 178 1680 0511</a></span>
								<span><a href="mailto:chingycai@chinyoo.com">chingycai@chinyoo.com</a></span>
							</div>
						</div>
					</details>
					<details class="region-item">
						<summary><?php ew_esc_html_e('Europe'); ?><span class="region-item__chev" aria-hidden="true"></span></summary>
						<div class="region-item__body">
							<p><?php ew_esc_html_e('Europe, Middle East and Africa — after-sales support, repair and calibration.'); ?></p>
							<div class="region-item__contact">
								<span><strong>Jack</strong></span>
								<span>WhatsApp <a href="https://wa.me/8617706872345" target="_blank" rel="noopener">+86 177 0687 2345</a></span>
								<span><a href="mailto:jack@chinyoo.com">jack@chinyoo.com</a></span>
							</div>
						</div>
					</details>
					<details class="region-item">
						<summary><?php ew_esc_html_e('Asia Pacific'); ?><span class="region-item__chev" aria-hidden="true"></span></summary>
						<div class="region-item__body">
							<p><?php ew_esc_html_e('Asia, Oceania and the Pacific — after-sales support, repair and calibration.'); ?></p>
							<div class="region-item__contact">
								<span><strong>Anna</strong></span>
								<span>WhatsApp <a href="https://wa.me/8617706873456" target="_blank" rel="noopener">+86 177 0687 3456</a></span>
								<span><a href="mailto:anna@chinyoo.com">anna@chinyoo.com</a></span>
							</div>
						</div>
					</details>
				</div>
			</div>

			<!-- 标签 4：Online Order Support -->
			<div class="contact-tab__panel" id="ctab-order" role="tabpanel">
				<div class="contact-help__cards">
					<div class="help-card">
						<span class="help-card__icon" aria-hidden="true">📦</span>
						<h3><?php ew_esc_html_e('Online Order Support'); ?></h3>
						<p><?php ew_esc_html_e('Order status, invoices, shipping updates and after-purchase questions. Please include your order number when you write.'); ?></p>
						<div class="region-item__contact">
							<span><strong><?php ew_esc_html_e('Order Desk'); ?></strong></span>
							<span><a href="mailto:chingycai@chinyoo.com">chingycai@chinyoo.com</a></span>
							<span>WhatsApp <a href="https://wa.me/8617816800511" target="_blank" rel="noopener">+86 178 1680 0511</a></span>
						</div>
					</div>
				</div>
			</div>

		</div>
	</section>

	<!-- ===== 工厂地址 / 地图 ===== -->
	<div class="contact-grid contact-grid--solo">
		<div class="contact-info">
			<h3 class="sec-sub">Wenzhou Zhengyuan Electronics Co., Ltd. (CHINYOO)</h3>
			<p style="color:var(--muted);font-size:14px"><?php ew_esc_html_e('Factory-direct electrical measuring instruments — digital multimeters, voltage testers, clamp meters and special test tools.'); ?></p>

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
	</div>
</div>
<?php ew_buyer_faq_block(); ?>
</main>

<script>
/* Contact 页标签切换 */
(function () {
	var tabs = document.querySelectorAll('.contact-tab');
	var panels = document.querySelectorAll('.contact-tab__panel');
	if (!tabs.length) { return; }
	tabs.forEach(function (t) {
		t.addEventListener('click', function () {
			var id = t.getAttribute('data-tab');
			tabs.forEach(function (x) {
				var on = x === t;
				x.classList.toggle('is-active', on);
				x.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			panels.forEach(function (p) {
				p.classList.toggle('is-active', p.id === 'ctab-' + id);
			});
		});
	});
})();
</script>
<?php
get_footer();
