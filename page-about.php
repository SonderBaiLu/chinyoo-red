<?php
/**
 * About Us / Our Factory — 工厂介绍 + 数据 + 认证 + 质检/研发/市场/愿景 + 地址（红白皮肤）
 * 按官方公司简介（2008 成立 / 18 年 / EMC-LVD-RoHS / 12道质检 / 专利研发 / 全球市场 / 愿景）
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php ew_esc_html_e('About Us'); ?></span>
</div></div>

<div class="page-head page-head--premium page-head--about" data-animation-wrap><div class="container">
	<p class="page-head__eyebrow" data-animation="1">CHINYOO / SINCE 2008</p>
	<h1 data-animation="1"><?php ew_esc_html_e('About Wenzhou Zhengyuan Electronics Co., Ltd.'); ?></h1>
	<p class="entry-content" data-animation="2"><?php ew_esc_html_e('A high-tech manufacturer of electrical measuring tools in Wenzhou — China\'s electrical hardware capital.'); ?></p>
	<div class="certs"><span class="cert">CE (EMC · LVD)</span><span class="cert">RoHS</span><span class="cert">ISO 9001</span><span class="cert">OEM / ODM</span></div>
</div></div>

<main class="inner-page inner-page--about">
<div class="container about-main">
	<section class="about-intro">
		<div>
			<h2 class="sec-title" style="text-align:left"><?php ew_esc_html_e('Our Factory'); ?></h2>
			<p><?php ew_e('Founded in 2008 and now 18 years strong, Wenzhou Zhengyuan Electronics Co., Ltd. (brand <strong>CHINYOO</strong>) is a high-tech enterprise focused on the R&D, production and sales of electrical measuring tools — from digital multimeters to high-precision smart voltage testers. With specialized production, automated manufacturing, rigorous quality control and continuous innovation, we have become a leading voltage tester and multimeter manufacturer, exporting across Europe, Southeast Asia, South America and other global markets.'); ?></p>
			<p style="margin-top:10px"><?php ew_esc_html_e('We welcome OEM & ODM projects: custom logos, custom ranges, custom colors, custom packaging and flexible MOQ.'); ?></p>
			<a class="btn-accent" href="#inquiry" style="display:inline-block;margin-top:18px"><?php ew_esc_html_e('Request Factory Tour & Catalog'); ?></a>
		</div>
		<div class="about-visual">
			<?php
			// Factory 实拍图（媒体库 attachment,slug=factory-image）;未上传时保留占位符
			$factory_q = new WP_Query( array(
				'post_type'      => 'attachment',
				'name'           => 'factory-image',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
			) );
			if ( $factory_q->have_posts() ) :
				echo wp_get_attachment_image( $factory_q->posts[0]->ID, 'full', false, array(
					'class' => 'about-factory-img',
					'alt'   => ew_esc_attr__( 'CHINYOO factory' ),
				) );
			else :
				echo '<span class="ph"><b>' . ew_esc_html__( 'Factory Image' ) . '</b><br>' . ew_esc_html__( 'Photo coming soon' ) . '</span>';
			endif;
			?>
		</div>
	</section>

	<section class="stats-row">
		<div class="stat">
			<img class="stat__icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/stats/stat-years-experience.png' ); ?>" alt="" width="96" height="96" loading="lazy" decoding="async" aria-hidden="true">
			<div class="stat__copy"><b>18+</b><span><?php ew_esc_html_e('Years Experience'); ?></span></div>
		</div>
		<div class="stat">
			<img class="stat__icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/stats/stat-product-models.png' ); ?>" alt="" width="96" height="96" loading="lazy" decoding="async" aria-hidden="true">
			<div class="stat__copy"><b>127</b><span><?php ew_esc_html_e('Product Models'); ?></span></div>
		</div>
		<div class="stat">
			<img class="stat__icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/stats/stat-export-countries.png' ); ?>" alt="" width="96" height="96" loading="lazy" decoding="async" aria-hidden="true">
			<div class="stat__copy"><b>40+</b><span><?php ew_esc_html_e('Export Countries'); ?></span></div>
		</div>
	</section>
</div>

<section class="alt-dark about-block" id="quality" data-animation-wrap>
	<div class="container">
		<h2 class="sec-title" data-animation="1"><?php ew_esc_html_e('Quality Control'); ?></h2>
		<p class="about-lead" data-animation="1"><?php ew_esc_html_e('From raw materials to finished goods, every unit passes 12 inspection steps to ensure stable performance and precise readings.'); ?></p>
		<div class="about-grid">
			<div class="about-feat" data-animation="1"><b><?php ew_esc_html_e('12-Step Inspection'); ?></b><p><?php ew_esc_html_e('Every unit goes through 12 inspection steps from incoming materials to finished goods — stable performance and accurate readings on every product.'); ?></p></div>
			<div class="about-feat" data-animation="1"><b><?php ew_esc_html_e('Automated Production'); ?></b><p><?php ew_esc_html_e('High-precision automated lines and smart testing equipment give precise control over key processes.'); ?></p></div>
			<div class="about-feat" data-animation="1"><b><?php ew_esc_html_e('Million-Unit Capacity'); ?></b><p><?php ew_esc_html_e('Annual capacity of one million units, built on automated manufacturing, keeps delivery fast and consistent.'); ?></p></div>
		</div>
	</div>
</section>

<div class="container about-main">
	<section class="about-certs" id="certifications">
		<h2 class="sec-title"><?php ew_esc_html_e('Certifications'); ?></h2>
		<p class="about-lead"><?php ew_esc_html_e('All products are CE (EMC and LVD) certified and RoHS compliant, verified by DTI (Deesev Testing) per IEC 62321, produced in our ISO 9001-certified factory.'); ?></p>
		<div class="cert-grid">
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-01.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-display/ce.jpg" alt="<?php ew_esc_attr_e('EU Conformity'); ?>" loading="lazy"><b>CE</b><span><?php ew_esc_html_e('EU Conformity'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-04.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-display/emc.jpg" alt="<?php ew_esc_attr_e('Electromagnetic Compatibility'); ?>" loading="lazy"><b>EMC</b><span><?php ew_esc_html_e('Electromagnetic Compatibility'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-09-1.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-display/lvd.jpg" alt="<?php ew_esc_attr_e('Low Voltage Directive'); ?>" loading="lazy"><b>LVD</b><span><?php ew_esc_html_e('Low Voltage Directive'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-doc-ee180805.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/e1fb503ae5fdfde4.jpg" alt="RoHS DoC — DTI EE180805" loading="lazy"><b>RoHS DoC</b><span><?php ew_esc_html_e('DTI EE180805 — Voltage Tester 1805D series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-report-ee180805.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/c2f7b6f65632fe36.jpg" alt="RoHS Report — DTI EE180805" loading="lazy"><b>RoHS Report</b><span><?php ew_esc_html_e('DTI EE180805 — Voltage Tester 1805D series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-doc-ee190805r.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/beb542ef58e4e18c.jpg" alt="RoHS DoC — DTI EE190805R" loading="lazy"><b>RoHS DoC</b><span><?php ew_esc_html_e('DTI EE190805R — Voltage Tester VD06 series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/dti-rohs-report-ee190805r.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/thumbnails/6759683c0559b32b.jpg" alt="RoHS Report — DTI EE190805R" loading="lazy"><b>RoHS Report</b><span><?php ew_esc_html_e('DTI EE190805R — Voltage Tester VD06 series'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-05.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-display/fcc.jpg" alt="<?php ew_esc_attr_e('FCC Certification'); ?>" loading="lazy"><b>FCC</b><span><?php ew_esc_html_e('FCC Certification'); ?></span></a>
			<a class="cert-card cert-card--img" href="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-02-1.pdf" target="_blank" rel="noopener"><img class="cert-card__img" src="https://www.chinyoo.com/wp-content/uploads/2026/08/cert-display/battery.jpg" alt="<?php ew_esc_attr_e('Battery Directives'); ?>" loading="lazy"><b><?php ew_esc_html_e('Battery Directives'); ?></b><span><?php ew_esc_html_e('CE & US Battery Compliance'); ?></span></a>
			<div class="cert-card"><b>ISO 9001</b><span><?php ew_esc_html_e('Quality Management System'); ?></span></div>
			<div class="cert-card"><b>OEM / ODM</b><span><?php ew_esc_html_e('Custom Manufacturing'); ?></span></div>
		</div>
		<p style="text-align:center;margin-top:24px"><a class="btn btn--primary" href="<?php echo esc_url( ew_page_url( 'certifications' ) ); ?>"><?php ew_esc_html_e('View All Certificates'); ?></a></p>
	</section>
</div>

<section class="alt-dark about-block" id="rd" data-animation-wrap>
	<div class="container">
		<h2 class="sec-title" data-animation="1"><?php ew_esc_html_e('R&D & Innovation'); ?></h2>
		<div class="about-grid">
			<div class="about-feat" data-animation="1"><b><?php ew_esc_html_e('Core Patents'); ?></b><p><?php ew_esc_html_e('Multiple national patents — including invention patents — cover circuit design, structure design and functional innovation, keeping our core technology self-owned.'); ?></p></div>
			<div class="about-feat" data-animation="1"><b><?php ew_esc_html_e('R&D Center'); ?></b><p><?php ew_esc_html_e('A dedicated R&D center with senior engineers delivers custom solutions and launches 5+ new products every year.'); ?></p></div>
			<div class="about-feat" data-animation="1"><b><?php ew_esc_html_e('University Partnerships'); ?></b><p><?php ew_esc_html_e('We work with universities and research institutes to stay at the frontier of measurement technology.'); ?></p></div>
		</div>
	</div>
</section>

<div class="container about-main">
	<section class="about-markets">
		<h2 class="sec-title"><?php ew_esc_html_e('Global Markets & Service'); ?></h2>
		<p class="about-lead"><?php ew_esc_html_e('Our instruments serve well-known electrical brands and channel partners across Europe, Southeast Asia and South America. As an OEM/ODM source factory operating since 2008, we run an annual capacity of one million units and support bulk orders and rush delivery.'); ?></p>
		<div class="about-grid">
			<div class="about-feat"><b><?php ew_esc_html_e('OEM / ODM Customization'); ?></b><p><?php ew_esc_html_e('Flexible cooperation models with fast response to orders — your logo, your range, your packaging, your market.'); ?></p></div>
			<div class="about-feat"><b><?php ew_esc_html_e('Localized After-Sales Support'); ?></b><p><?php ew_esc_html_e('A localized after-sales network answers customer requests quickly and keeps supply chains moving.'); ?></p></div>
			<div class="about-feat"><b><?php ew_esc_html_e('Better Cost-Performance'); ?></b><p><?php ew_esc_html_e('Continuous value through technology upgrades and cost optimization.'); ?></p></div>
		</div>
	</section>

	<section class="about-vision" data-animation-wrap>
		<h2 data-animation="1"><?php ew_esc_html_e('Dedicated to Measurement · Building the Future'); ?></h2>
		<p data-animation="1"><?php ew_esc_html_e('We will keep focusing on electrical measurement — technology as the engine, quality as the foundation — to become a globally trusted provider of electrical tool solutions. Every measurement, precise and reliable.'); ?></p>
	</section>

	<section class="about-address">
		<h3><?php ew_esc_html_e('Visit Us'); ?></h3>
		<p>Building 6, No.1 Hongjin Road, Daizhai Village, Tengqiao Town, Lucheng District, Wenzhou, Zhejiang, China</p>
		<div class="contact-map">
			<iframe
				src="https://www.google.com/maps?q=Hongjin%20Road%2C%20Tengqiao%20Town%2C%20Lucheng%20District%2C%20Wenzhou%2C%20Zhejiang%2C%20China&output=embed"
				width="100%" height="260" style="border:0;border-radius:8px" loading="lazy"
				referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
		</div>
	</section>
</div>

<section class="inner-cta" id="inquiry">
	<div class="container">
		<h2 class="sec-title"><?php ew_esc_html_e('Work With Our Factory'); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e('Get our full catalog, pricelist and MOQ details.'); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</div>
</section>
<?php ew_buyer_faq_block(); ?>
</main>
<?php
get_footer();

