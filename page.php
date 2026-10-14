<?php
/**
 * Chinyoo Red — 通用页面模板（对标 UNI-T：浅灰页头 + 面包屑 + 白底正文排版）
 * 用于无专门 page-{slug}.php 模板的页面：privacy-policy / terms-of-service 等法务、说明页。
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="crumb"><div class="container">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
	<span> › </span><span><?php echo esc_html( get_the_title() ); ?></span>
</div></div>

<div class="page-head" data-animation-wrap><div class="container">
	<h1 data-animation="1"><?php echo esc_html( get_the_title() ); ?></h1>
</div></div>

<div class="container">
	<article class="single-article__content page-body">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</article>
</div>
<?php
get_footer();
