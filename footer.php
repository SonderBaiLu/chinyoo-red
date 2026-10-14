<?php
/**
 * Chinyoo Red — Site Footer（5 列深色页脚：Products / Solutions / Services & Support / Insights / About CHINYOO）
 * 对标参考图 1：每列带子链接，About 列带社媒图标，底部版权条。
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Products 列：4 大顶级分类 + 子分类（按 EN slug 固定顺序，Polylang 语言感知）
$ew_foot_cats_map = ew_cats_by_en_slug();
$ew_foot_cat_order = array( 'voltage-testers', 'clamp-meters', 'others', 'digital-multimeters' );
$ew_foot_cats = array();
foreach ( $ew_foot_cat_order as $ew_slug ) {
	if ( isset( $ew_foot_cats_map[ $ew_slug ] ) ) {
		$ew_foot_cats[] = $ew_foot_cats_map[ $ew_slug ];
	}
}
// 兜底：映射里没排到顺序的其他顶级分类也列出来（新增分类自动进页脚）
foreach ( $ew_foot_cats_map as $ew_slug => $ew_cat ) {
	if ( ! in_array( $ew_cat->term_id, wp_list_pluck( $ew_foot_cats, 'term_id' ), true ) ) {
		$ew_foot_cats[] = $ew_cat;
	}
}

// 子分类（当前语言，按产品数排序）
$ew_foot_subcats = function ( $parent_id ) {
	$subs = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => $parent_id,
		'exclude'    => ew_exclude_default_cat(),
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );
	return ( is_wp_error( $subs ) || ! is_array( $subs ) ) ? array() : $subs;
};

// Solutions 列：场景（1:1 对应 /solutions/{slug}/）
$ew_foot_scenarios = ew_scenarios();

// New Products Launch → shop 页
$ew_foot_shop_url = '';
if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) ) {
	$ew_foot_shop_url = get_permalink( wc_get_page_id( 'shop' ) );
}
if ( ! $ew_foot_shop_url ) {
	$ew_foot_shop_url = ew_page_url( 'product-categories' );
}
?>
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="container foot">
		<div class="foot-grid">

			<!-- 1. Products -->
			<div class="foot-col">
				<h4 class="foot-col__title"><?php ew_esc_html_e('Products'); ?></h4>
				<ul class="foot-col__list">
					<?php foreach ( $ew_foot_cats as $ew_cat ) : ?>
						<?php $ew_subs = $ew_foot_subcats( $ew_cat->term_id ); ?>
						<li>
							<a href="<?php echo esc_url( ew_term_url( $ew_cat->slug ) ); ?>">
								<?php echo esc_html( $ew_cat->name ); ?>
								<span class="foot-count"><?php echo esc_html( number_format_i18n( ew_cat_product_count( $ew_cat->term_id ) ) ); ?></span>
							</a>
							<?php if ( ! empty( $ew_subs ) ) : ?>
								<ul class="foot-sub">
									<?php foreach ( $ew_subs as $ew_sub ) : ?>
										<li><a href="<?php echo esc_url( get_term_link( $ew_sub ) ); ?>"><?php echo esc_html( $ew_sub->name ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- 2. Solutions -->
			<div class="foot-col">
				<h4 class="foot-col__title"><?php ew_esc_html_e('Solutions'); ?></h4>
				<ul class="foot-col__list">
					<?php foreach ( $ew_foot_scenarios as $ew_slug => $ew_sc ) : ?>
						<li><a href="<?php echo esc_url( ew_scenario_url( $ew_slug ) ); ?>"><?php echo esc_html( $ew_sc['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- 3. Services & Support -->
			<div class="foot-col">
				<h4 class="foot-col__title"><?php ew_esc_html_e('Services & Support'); ?></h4>
				<ul class="foot-col__list">
					<li><a href="<?php echo esc_url( ew_page_url( 'downloads' ) ); ?>"><?php ew_esc_html_e('Downloads'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'faqs' ) ); ?>"><?php ew_esc_html_e('FAQs'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'oem' ) ); ?>"><?php ew_esc_html_e('OEM / ODM'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'quote' ) ); ?>"><?php ew_esc_html_e('Where to Buy'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'contact-us' ) ); ?>"><?php ew_esc_html_e('Feedback'); ?></a></li>
				</ul>
			</div>

			<!-- 4. Insights -->
			<div class="foot-col">
				<h4 class="foot-col__title"><?php ew_esc_html_e('Insights'); ?></h4>
				<ul class="foot-col__list">
					<li><a href="<?php echo esc_url( ew_page_url( 'news' ) ); ?>"><?php ew_esc_html_e('News & Events'); ?></a></li>
					<li><a href="<?php echo esc_url( $ew_foot_shop_url ); ?>"><?php ew_esc_html_e('New Products Launch'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'news' ) ); ?>"><?php ew_esc_html_e('Blogs'); ?></a></li>
				</ul>
			</div>

			<!-- 5. About CHINYOO -->
			<div class="foot-col">
				<h4 class="foot-col__title"><?php ew_esc_html_e('About CHINYOO'); ?></h4>
				<ul class="foot-col__list">
					<li><a href="<?php echo esc_url( ew_page_url( 'about-us' ) ); ?>"><?php ew_esc_html_e('Our Factory'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'certifications' ) ); ?>"><?php ew_esc_html_e('Certifications'); ?></a></li>
					<li><a href="<?php echo esc_url( ew_page_url( 'contact-us' ) ); ?>"><?php ew_esc_html_e('Contact Us'); ?></a></li>
				</ul>
				<div class="social foot-social">
					<a href="https://www.facebook.com/profile.php?id=61590602160891" target="_blank" rel="noopener" title="Facebook" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
					<a href="https://www.linkedin.com/company/wenzhou-zhengyuan-electronic-co.-ltd." target="_blank" rel="noopener" title="LinkedIn" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.065 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
					<a href="https://www.tiktok.com/@chingy_cai1101" target="_blank" rel="noopener" title="TikTok" aria-label="TikTok"><svg viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></a>
					<a href="https://www.youtube.com/channel/UCOi2X3jqn38vBQN57k8CuXg" target="_blank" rel="noopener" title="YouTube" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
				</div>
			</div>

		</div>
	</div>

	<!-- 底部版权行 -->
	<div class="copy">
		<div class="container">
			<p><a href="<?php echo esc_url( ew_page_url( 'privacy-policy' ) ); ?>"><?php ew_esc_html_e('Privacy Policy'); ?></a> | <a href="<?php echo esc_url( ew_page_url( 'refund_returns' ) ); ?>"><?php ew_esc_html_e('Terms of Use'); ?></a> | <a href="<?php echo esc_url( ew_page_url( 'contact-us' ) ); ?>"><?php ew_esc_html_e('Feedback'); ?></a></p>
			<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?>
			<?php
			$ew_foot_lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
			if ( 'zh' === $ew_foot_lang ) {
				echo esc_html( '温州正原电子有限公司 版权所有' );
			} else {
				echo esc_html( 'Wenzhou Zhengyuan Electronics Co., Ltd. ' );
				ew_esc_html_e( 'All Rights Reserved.' );
			}
			?></p>
		</div>
	</div>
</footer>

<?php
// 统一 Organization 实体 @id：任何语言页面都指向 EN 根域名（默认语言），避免 Polylang 拆成 5 个实体。
$ew_org_home = ( function_exists('pll_home_url') && function_exists('pll_default_language') )
    ? pll_home_url( pll_default_language() )
    : home_url('/');
?>
<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "Organization",
	"@id": "<?php echo esc_url( $ew_org_home ); ?>#organization",
	"name": "Wenzhou Zhengyuan Electronics Co., Ltd.",
	"alternateName": ["CHINYOO", "Electrical World", "Wenzhou Zhengyuan Electronics Co., Ltd."],
	"logo": "https://www.chinyoo.com/wp-content/themes/chinyoo-red/assets/images/chinyoo-logo-header-v2.png",
	"legalName": "温州正原电子有限公司",
	"foundingDate": "2008",
	"url": "<?php echo esc_url( $ew_org_home ); ?>",
	"description": "Professional test & measurement instrument manufacturer in Wenzhou, China — digital multimeters, clamp meters, voltage testers, and battery testers.",
	"address": {
		"@type": "PostalAddress",
		"streetAddress": "Building 6, No.1 Hongjin Road, Daizhai Village, Tengqiao Town, Lucheng District",
		"addressLocality": "Wenzhou",
		"addressRegion": "Zhejiang",
		"postalCode": "325000",
		"addressCountry": "CN"
	},
	"contactPoint": [
		{ "@type": "ContactPoint", "contactType": "sales", "name": "Chingy", "telephone": "+8617816800511", "email": "chinyoo08@gmail.com", "availableLanguage": ["en", "zh"] },
		{ "@type": "ContactPoint", "contactType": "sales", "name": "Jack", "telephone": "+8617706872345", "email": "sugon163zy@gmail.com", "availableLanguage": ["en", "zh"] },
		{ "@type": "ContactPoint", "contactType": "sales", "name": "Anna", "telephone": "+8617706873456", "email": "zhengyuan2101@gmail.com", "availableLanguage": ["en", "zh"] }
	],
	"sameAs": [
		"https://www.facebook.com/profile.php?id=61590602160891",
		"https://www.linkedin.com/company/wenzhou-zhengyuan-electronic-co.-ltd.",
		"https://www.tiktok.com/@chingy_cai1101",
		"https://www.youtube.com/channel/UCOi2X3jqn38vBQN57k8CuXg",
		"https://www.wikidata.org/wiki/Q141118285"
	]
}
</script>

<?php wp_footer(); ?>

<!-- 客服浮层（WhatsApp） -->
<a class="fab" href="https://wa.me/8617816800511" target="_blank" rel="noopener" title="WhatsApp">WA</a>

<!-- Lightbox Modal -->
<div class="lightbox-overlay" id="lightbox-overlay" onclick="closeLightbox()">
	<span class="lightbox-close">&times;</span>
	<img src="" alt="" id="lightbox-img" onclick="event.stopPropagation()">
</div>
<script>
(function(){
	var overlay = document.getElementById('lightbox-overlay');
	var img = document.getElementById('lightbox-img');
	window.openLightbox = function(src) { img.src = src; overlay.classList.add('active'); document.body.style.overflow = 'hidden'; };
	window.closeLightbox = function() { overlay.classList.remove('active'); document.body.style.overflow = ''; };
	document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLightbox(); });
})();
</script>
</body>
</html>
