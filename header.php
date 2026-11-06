<?php
/**
 * 页眉：紧凑商用顶栏（Logo | 中央搜索 | 收藏/登录）+ 主导航行 + 移动端抽屉
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<script>document.documentElement.className += ' js';</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="topbar"><div class="container">
	<span>CE (EMC/LVD) · RoHS · ISO 9001</span>
	<span><?php ew_esc_html_e('Wenzhou, China'); ?></span>
	<span><a href="mailto:chingycai@chinyoo.com">chingycai@chinyoo.com</a></span>
</div></div>

<header class="site-header">
	<div class="container hdr">
		<button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="<?php ew_esc_attr_e('Menu'); ?>" aria-expanded="false">
			<span class="hamburger"></span>
		</button>

		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>"><img class="logo__img" src="<?php echo esc_url( EW_THEME_URI . '/assets/images/chinyoo-logo-header-v2.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="646" height="159"></a>

		<!-- 中央通栏搜索：s + post_type=product -->
		<form role="search" method="get" class="hdr-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php ew_esc_attr_e('Enter Model Name ex: MS8218'); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="<?php ew_esc_attr_e('Search products'); ?>">
			<input type="hidden" name="post_type" value="product">
			<button type="submit" aria-label="<?php ew_esc_attr_e('Search'); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
			</button>
		</form>
		<button class="hdr-search-toggle" id="hdr-search-toggle" aria-label="<?php ew_esc_attr_e('Search'); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
		</button>

		<div class="hdr-actions">
			<a class="hdr-ic" href="<?php echo esc_url( ew_page_url( 'product-categories' ) ); ?>" title="<?php ew_esc_attr_e('Wishlist'); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 21s-7.5-4.7-9.5-9C1 8.5 3 5 6.5 5c2 0 3.5 1 4.5 2.5C12 6 13.5 5 15.5 5 19 5 21 8.5 20.5 12c-2 4.3-8.5 9-8.5 9z"></path></svg>
				<span><?php ew_esc_html_e('Wishlist'); ?></span>
			</a>
			<a class="hdr-ic" href="<?php echo esc_url( wp_login_url() ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"></path></svg>
				<span><?php ew_esc_html_e('Sign In / Register'); ?></span>
			</a>
			<div class="lang-switcher" id="lang-switcher">
				<?php
				// 只列「当前对象有翻译」的语言，避免 Polylang 猜 URL 导致 404
				if ( function_exists( 'pll_the_languages' ) ) :
					$ew_langs = pll_the_languages( array( 'raw' => 1, 'hide_current' => 0 ) );
					if ( is_array( $ew_langs ) && function_exists( 'pll_current_language' ) ) {
						$ew_cur_lang = pll_current_language();
						$ew_qo       = get_queried_object();
						if ( $ew_qo && ( $ew_qo instanceof WP_Term || $ew_qo instanceof WP_Post ) ) {
							$ew_keep = array( $ew_cur_lang );
							foreach ( $ew_langs as $ew_l ) {
								$ew_slug = isset( $ew_l['slug'] ) ? $ew_l['slug'] : '';
								if ( ! $ew_slug || $ew_slug === $ew_cur_lang ) {
									continue;
								}
								if ( $ew_qo instanceof WP_Term && function_exists( 'pll_get_term' ) && pll_get_term( $ew_qo->term_id, $ew_slug ) ) {
									$ew_keep[] = $ew_slug;
								} elseif ( $ew_qo instanceof WP_Post && function_exists( 'pll_get_post' ) && pll_get_post( $ew_qo->ID, $ew_slug ) ) {
									$ew_keep[] = $ew_slug;
								}
							}
							foreach ( $ew_langs as $ew_l_key => $ew_l ) {
								$ew_slug = isset( $ew_l['slug'] ) ? $ew_l['slug'] : '';
								if ( ! in_array( $ew_slug, $ew_keep, true ) ) {
									unset( $ew_langs[ $ew_l_key ] );
								}
							}
						}
					}
					if ( is_array( $ew_langs ) && count( $ew_langs ) > 1 ) :
						$ew_cur = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
						?>
						<button type="button" class="lang-switcher__btn" aria-haspopup="true" aria-expanded="false">
							<?php echo esc_html( strtoupper( $ew_cur ) ); ?><span class="lang-switcher__caret" aria-hidden="true"></span>
						</button>
						<ul class="lang-switcher__menu">
							<?php foreach ( $ew_langs as $ew_lang ) : ?>
								<?php $ew_code = isset( $ew_lang['slug'] ) ? strtoupper( $ew_lang['slug'] ) : ''; ?>
								<li class="lang-switcher__item">
									<a href="<?php echo esc_url( $ew_lang['url'] ); ?>" class="lang-switcher__link" hreflang="<?php echo esc_attr( $ew_lang['slug'] ); ?>" lang="<?php echo esc_attr( $ew_lang['slug'] ); ?>"<?php echo ! empty( $ew_lang['current_lang'] ) ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $ew_code ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<a class="cta" href="<?php echo esc_url( ew_page_url( 'quote' ) ); ?>"><?php ew_esc_html_e('Get a Quote'); ?></a>
		</div>
	</div>

	<!-- 主导航行（桌面） -->
	<nav class="hdr-nav" aria-label="<?php ew_esc_attr_e('Primary Menu'); ?>">
		<div class="container">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'main-menu',
				'fallback_cb'    => 'ew_header_menu_fallback',
				'depth'          => 3,
				'walker'         => new Ew_Products_Mega_Walker(),
			) );
			?>
		</div>
	</nav>

	<?php
	// 移动端吸顶分类条（仅产品相关页）
	$ew_hdr_q    = get_queried_object();
	$ew_hdr_cat  = ( is_tax( 'product_cat' ) && $ew_hdr_q && isset( $ew_hdr_q->term_id ) ) ? (int) $ew_hdr_q->term_id : 0;
	$ew_show_strip = is_tax( 'product_cat' ) || is_page( 'product-categories' ) || is_singular( 'product' );
	if ( $ew_show_strip ) :
		get_template_part( 'template-parts/mobile-cat-strip', null, array( 'current_cat_id' => $ew_hdr_cat ) );
	endif;
	?>

	<!-- 移动端抽屉：遮罩 + 左侧滑出面板（导航 + 分类树） -->
	<div class="drawer-backdrop" id="drawer-backdrop" hidden></div>
	<aside class="mobile-nav-panel" id="mobile-nav-panel" aria-hidden="true">
		<div class="mobile-nav-panel__head">
			<span><?php ew_esc_html_e('Menu'); ?></span>
			<button type="button" class="drawer-close" id="drawer-close" aria-label="<?php ew_esc_attr_e('Close menu'); ?>">&times;</button>
		</div>
		<form role="search" method="get" class="mobile-nav-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php ew_esc_attr_e('Enter Model Name ex: MS8218'); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
			<input type="hidden" name="post_type" value="product" />
			<button type="submit" aria-label="<?php ew_esc_attr_e('Search'); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
			</button>
		</form>
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false, // this value/key error: Themes(chinyoo-red): ther err
			'menu_class'     => 'mobile-nav-menu',
			'fallback_cb'    => 'ew_header_menu_fallback',
			'depth'          => 3,
		) );
		?>
		<div class="mobile-nav-panel__cats">
			<?php get_template_part( 'template-parts/sidebar-categories', null, array( 'current_cat_id' => $ew_hdr_cat ) ); ?>
		</div>
		<a class="mobile-nav-quote" href="<?php echo esc_url( ew_page_url( 'quote' ) ); ?>"><?php ew_esc_html_e('Get a Quote'); ?></a>
	</aside>
</header>

<main id="main-content" class="site-main">
