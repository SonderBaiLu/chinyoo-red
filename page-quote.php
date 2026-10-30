<?php
/**
 * Get a Quote — 独立询盘页(对标 UNI-T 红白 B2B 风格)
 * URL: /quote/  (slug=quote,page-quote.php 自动匹配)
 *
 * 左栏:工厂直供信任/联系信息卡;右栏:共享询盘表单(6 字段,复用
 * template-parts/inquiry-form.php + inc/inquiry-handler.php)。
 * 外层带 id="inquiry",承接 p4-woo.js 的 .js-inquiry-cta[data-model]
 * 与 ?inquiry_model= 预填 + 滚动定位。
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php ew_esc_html_e('Get a Quote'); ?></span>
</div></div>

<div class="page-head" data-animation-wrap><div class="container">
	<h1 data-animation="1"><?php ew_e('Get a <em>Quote</em>'); ?></h1>
	<p class="entry-content" data-animation="2"><?php ew_esc_html_e('Tell us which instruments you need and your target quantity. Our sales team replies within 24 hours.'); ?></p>
	<div class="certs"><span class="cert">CE</span><span class="cert">RoHS</span><span class="cert">ISO 9001</span><span class="cert">OEM / ODM</span></div>
</div></div>

<div class="container quote-grid">
	<aside class="quote-aside">
		<h3 class="quote-aside__title"><?php ew_esc_html_e('Direct from the Wenzhou Factory'); ?></h3>
		<p class="quote-aside__intro"><?php ew_esc_html_e('Factory-direct electrical measuring instruments — digital multimeters, voltage testers, clamp meters and special test tools.'); ?></p>

		<div class="quote-stats">
			<div class="quote-stat"><b>20+</b><span><?php ew_esc_html_e('Years'); ?><br><?php ew_esc_html_e('Experience'); ?></span></div>
			<div class="quote-stat"><b>127</b><span><?php ew_esc_html_e('Product'); ?><br><?php ew_esc_html_e('Models'); ?></span></div>
			<div class="quote-stat"><b>24h</b><span><?php ew_esc_html_e('Response'); ?><br><?php ew_esc_html_e('Time'); ?></span></div>
		</div>

		<div class="quote-block">
			<h4><?php ew_esc_html_e('Contact'); ?></h4>
			<ul class="quote-list">
				<li><strong>Chingy</strong> · WhatsApp <a href="https://wa.me/8617816800511" target="_blank" rel="noopener">+86 178 1680 0511</a><br><a href="mailto:chingycai@chinyoo.com">chingycai@chinyoo.com</a></li>
				<li><strong>Jack</strong> · WhatsApp <a href="https://wa.me/8617706872345" target="_blank" rel="noopener">+86 177 0687 2345</a><br><a href="mailto:jack@chinyoo.com">jack@chinyoo.com</a></li>
				<li><strong>Anna</strong> · WhatsApp <a href="https://wa.me/8617706873456" target="_blank" rel="noopener">+86 177 0687 3456</a><br><a href="mailto:anna@chinyoo.com">anna@chinyoo.com</a></li>
			</ul>
		</div>

		<div class="quote-block">
			<h4><?php ew_esc_html_e('Factory Address'); ?></h4>
			<p class="quote-address">Building 6, No.1 Hongjin Road, Daizhai Village, Tengqiao Town,<br>Lucheng District, Wenzhou, Zhejiang, China</p>
		</div>

		<div class="quote-note"><?php ew_e('All inquiries are answered within <strong>24 hours</strong>.'); ?></div>
	</aside>

	<div class="quote-form-wrap" id="inquiry">
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</div>
</div>
<?php ew_buyer_faq_block(); ?>
<?php
get_footer();
