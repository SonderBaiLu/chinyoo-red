<?php
/**
 * Certifications & Compliance —— 独立认证落地页。
 *
 * 为什么单独开一页（而不是继续挂在 about-us 的 #certifications 锚点上）：
 * 认证页是外链的**接收端**。认证数据库（UL Product iQ / TÜV Certipedia）、
 * B2B 目录（Europages / ThomasNet / DirectIndustry）、行业协会会员名录
 * 在登记企业资料时都要求填一个「证书/合规」URL，过去只能填首页，
 * 相关性和权重都浪费。这里给出一个可被引用的规范地址 /certifications/。
 *
 * 样式零新增：完全复用 about-us 已有的 .about-certs / .cert-grid / .cert-card，
 * 4 列网格 + a.cert-card 的 hover 已在 p4.css 定义，RTL 下 grid 无需翻转。
 * 与 about-us 的分工：about-us 是摘要（9 张卡），本页是完整清单（按标准分组、带证书编号）。
 *
 * 卡片字段约定：std 与 no 是专有名词（标准号、证书号、型号），保持原文不翻译；
 * 只有 desc 走 ew_esc_html_e()，避免把 "Cert 260658" 这类串塞进翻译表。
 *
 * @package chinyoo-red
 */

get_header();

$ew_cert_base = 'https://www.chinyoo.com/wp-content/uploads/2026/08/';

/** 认证分组：每组一个标题 + 说明 + 卡片网格。 */
$ew_cert_groups = array(
	array(
		'title' => 'Quality Management',
		'lead'  => 'Our factory operates an ISO 9001 quality management system covering incoming material inspection, in-process testing and 100% calibration before packing.',
		'items' => array(
			array( 'std' => 'ISO 9001', 'desc' => 'Quality Management System', 'no' => '', 'file' => 'iso9001-certificate.pdf' ),
		),
	),
	array(
		'title' => 'European Conformity (CE)',
		'lead'  => 'Our instruments carry CE marking under the Low Voltage Directive (LVD). Each certificate names the standard applied and the model range it covers.',
		'items' => array(
			array( 'std' => 'CE-LVD', 'desc' => 'Low Voltage Directive', 'no' => 'Cert 232238 · 61010', 'file' => 'cert-03.pdf' ),
			array( 'std' => 'CE-LVD', 'desc' => 'Multimeter', 'no' => 'Cert 260658', 'file' => 'cert-02.pdf' ),
			array( 'std' => 'CE-LVD', 'desc' => 'Voltage Tester', 'no' => 'Cert 260657', 'file' => 'cert-01.pdf' ),
			array( 'std' => 'LVD', 'desc' => 'Test Pen', 'no' => 'HW70B · 61010-1', 'file' => 'cert-09-1.pdf' ),
		),
	),
	array(
		'title' => 'Electromagnetic Compatibility (EMC)',
		'lead'  => 'Emissions and immunity testing to IEC 61326, plus FCC verification for models sold into the United States.',
		'items' => array(
			array( 'std' => 'EMC 61326', 'desc' => 'Multimeter', 'no' => 'Cert 260658 · DT9205A', 'file' => 'cert-06.pdf' ),
			array( 'std' => 'EMC 61326-88-12', 'desc' => 'Electromagnetic Compatibility', 'no' => 'Cert 260657', 'file' => 'cert-04.pdf' ),
			array( 'std' => 'EMC 61326', 'desc' => 'Multimeter', 'no' => '', 'file' => 'cert-03-1.pdf' ),
			array( 'std' => 'FCC', 'desc' => 'FCC Certification', 'no' => 'Cert 260657', 'file' => 'cert-05.pdf' ),
		),
	),
	array(
		'title' => 'Environmental Compliance (RoHS)',
		'lead'  => 'Factory-direct RoHS compliance for your private label, verified by DTI (Deesev Testing) in accordance with IEC 62321.',
		'items' => array(
			array( 'std' => 'RoHS', 'desc' => 'RoHS Compliance Certificates', 'no' => 'Cert C230609067001-2', 'file' => 'cert-12.pdf' ),
			array( 'std' => 'RoHS DoC', 'desc' => 'DTI EE190805R — Voltage Tester VD06 series', 'no' => '', 'file' => 'dti-rohs-doc-ee190805r.pdf' ),
			array( 'std' => 'RoHS Report', 'desc' => 'DTI EE190805R — Voltage Tester VD06 series', 'no' => '', 'file' => 'dti-rohs-report-ee190805r.pdf' ),
			array( 'std' => 'RoHS DoC', 'desc' => 'DTI EE180805 — Voltage Tester 1805D series', 'no' => '', 'file' => 'dti-rohs-doc-ee180805.pdf' ),
			array( 'std' => 'RoHS Report', 'desc' => 'DTI EE180805 — Voltage Tester 1805D series', 'no' => '', 'file' => 'dti-rohs-report-ee180805.pdf' ),
		),
	),
	array(
		'title' => 'Battery & Transport Safety',
		'lead'  => 'MSDS documentation, UN transport certificates and battery directive compliance for our battery-powered instruments.',
		'items' => array(
			array( 'std' => 'Battery Directives', 'desc' => 'CE & US Battery Compliance', 'no' => '', 'file' => 'cert-02-1.pdf' ),
			array( 'std' => 'MSDS', 'desc' => 'Material Safety Data Sheet', 'no' => 'EN', 'file' => 'cert-11-1.pdf' ),
			array( 'std' => 'MSDS', 'desc' => 'Alkaline Battery', 'no' => 'LR41 · Huadao', 'file' => 'cert-13.pdf' ),
			array( 'std' => 'MSDS', 'desc' => 'Alkaline Battery', 'no' => 'LR44 · Huadao', 'file' => 'cert-12-1.pdf' ),
			array( 'std' => 'MSDS', 'desc' => 'Carbon-Zinc Battery', 'no' => 'AAA · Huadao', 'file' => 'cert-04-1.pdf' ),
			array( 'std' => 'Air Transport', 'desc' => 'Air Transport Certificate', 'no' => 'AAA', 'file' => 'cert-14.pdf' ),
			array( 'std' => 'Sea Transport', 'desc' => 'Sea Transport Certificate', 'no' => 'AAA', 'file' => 'cert-01-17.pdf' ),
			array( 'std' => 'LR41', 'desc' => 'Sea Transport Certificate', 'no' => '', 'file' => 'cert-06-1.pdf' ),
			array( 'std' => 'LR41', 'desc' => 'Road Transport Certificate', 'no' => '', 'file' => 'cert-05-1.pdf' ),
			array( 'std' => 'LR44', 'desc' => 'Air Transport Certificate', 'no' => '', 'file' => 'cert-08-1.pdf' ),
			array( 'std' => 'LR44', 'desc' => 'Sea Transport Certificate', 'no' => '', 'file' => 'cert-07-1.pdf' ),
		),
	),
);
?>
<div class="crumb"><div class="container"><?php get_template_part( 'template-parts/breadcrumbs' ); ?></div></div>
<div class="page-head page-head--premium page-head--certifications" data-animation-wrap><div class="container"><p class="page-head__eyebrow" data-animation="1">CHINYOO / VERIFIED QUALITY</p><h1 data-animation="1"><?php ew_esc_html_e( 'Certifications & Compliance' ); ?></h1><p class="entry-content" data-animation="2"><?php ew_esc_html_e( 'Test reports, certificates and declarations prepared for global market access.' ); ?></p></div></div>
<main class="inner-page inner-page--certifications">

<div class="container about-main">
	<?php foreach ( $ew_cert_groups as $ew_g ) : ?>
	<section class="about-certs">
		<h2 class="sec-title"><?php ew_esc_html_e( $ew_g['title'] ); ?></h2>
		<p class="about-lead"><?php ew_esc_html_e( $ew_g['lead'] ); ?></p>
		<div class="cert-grid">
			<?php foreach ( $ew_g['items'] as $ew_it ) : ?>
			<a class="cert-card" href="<?php echo esc_url( $ew_cert_base . $ew_it['file'] ); ?>" target="_blank" rel="noopener">
				<b><?php echo esc_html( $ew_it['std'] ); ?></b>
				<span><?php echo ew_esc_html__( $ew_it['desc'] ); ?><?php if ( $ew_it['no'] ) : ?><br><?php echo esc_html( $ew_it['no'] ); ?><?php endif; ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endforeach; ?>
</div>

</main>
<section class="inner-cta" id="inquiry">
	<div class="container">
		<h2 class="sec-title"><?php ew_esc_html_e( 'Request a Certificate' ); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e( 'Need a test report or certificate for a specific model, or for a market not listed here? Send us the requirement and our engineering team will reply with the relevant documents.' ); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</div>
</section>
<?php
get_footer();
