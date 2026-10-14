<?php
/**
 * Single post — blog article detail (Chinyoo Red).
 * Used by /news/ articles. Renders title, meta, thumbnail, content,
 * and a closing inquiry CTA (matching the site's B2B lead pattern).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<div class="container page-layout">
	<div class="page-layout__main">

		<?php while ( have_posts() ) : the_post(); ?>

			<nav class="breadcrumbs" aria-label="<?php ew_esc_attr_e('Breadcrumb'); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<a href="<?php echo esc_url( ew_page_url( 'news' ) ); ?>"><?php ew_esc_html_e('News'); ?></a>
				<span class="breadcrumbs__sep">/</span>
				<span class="breadcrumbs__current"><?php echo esc_html( get_the_title() ); ?></span>
			</nav>

			<article <?php post_class( 'single-article' ); ?>>
				<header class="single-article__header">
					<h1 class="single-article__title"><?php the_title(); ?></h1>
					<div class="single-article__meta">
						<time class="single-article__date"><?php echo esc_html( get_the_date() ); ?></time>
						<p class="single-article__byline">
							<?php if ( $ew_avatar = ew_author_photo() ) : ?>
								<img class="single-article__avatar-img" src="<?php echo esc_url( $ew_avatar ); ?>" alt="<?php echo esc_attr( ew_author_name() ); ?>" loading="lazy">
							<?php else : ?>
								<span class="single-article__avatar" aria-hidden="true"><?php echo esc_html( ew_author_initial() ); ?></span>
							<?php endif; ?>
							<span class="single-article__byline-text">
								<?php echo esc_html( ew_author_by_label() ); ?>
								<a href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( ew_author_name() ); ?></a>
								· <?php echo esc_html( ew_author_role_short() ); ?>
							</span>
						</p>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="single-article__thumb">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="single-article__content entry-content">
					<?php the_content(); ?>
				</div>

				<?php
				/* FAQ (GEO) — 文章主题问答 + FAQPage JSON-LD。
				   主题按 EN 文章 slug 解析(inc/geo-faq.php)，未命中不输出。 */
				$ew_news_faq_topic = ew_news_faq_for_post( get_the_ID() );
				$ew_news_faq_list  = '' !== $ew_news_faq_topic ? ew_news_faq( $ew_news_faq_topic ) : array();
				if ( ! empty( $ew_news_faq_list ) ) :
					$ew_news_i = 0;
					?>
					<section class="product-faq article-faq">
						<h2 class="section-title"><?php ew_esc_html_e('FAQs'); ?></h2>
						<div class="product-faq__list">
							<?php foreach ( $ew_news_faq_list as $ew_news_qa ) : ?>
								<?php $ew_news_i++; ?>
								<details class="product-faq__item"<?php echo ( 1 === $ew_news_i ) ? ' open' : ''; ?>>
									<summary><?php echo ew_faq_emph( $ew_news_qa['q'] ); ?></summary>
									<div class="product-faq__answer">
										<p><?php echo ew_faq_emph( $ew_news_qa['a'] ); ?></p>
									</div>
								</details>
							<?php endforeach; ?>
						</div>
					</section>

					<script type="application/ld+json">
					<?php
					$ew_news_ld = array(
						'@context'   => 'https://schema.org',
						'@type'      => 'FAQPage',
						'mainEntity' => array(),
					);
					foreach ( $ew_news_faq_list as $ew_news_qa ) {
						$ew_news_ld['mainEntity'][] = array(
							'@type'          => 'Question',
							'name'           => $ew_news_qa['q'],
							'acceptedAnswer' => array(
								'@type' => 'Answer',
								'text'  => $ew_news_qa['a'],
							),
						);
					}
					echo wp_json_encode( $ew_news_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
					?>
					</script>
				<?php endif; ?>

				<?php
				/* 相关内链 —— 文章 → 指南 / 产品。此前 single.php 零出链，文章是死胡同
				   (实测全站 877 个内容页里 656 个零内部入链)，抓取需求因此起不来。
				   数据源见 inc/product-guides.php: ew_post_related_links()。
				   样式复用分类页的 .cat-guides(main.css / rtl.css 已有，含 RTL 适配)，
				   不新增 CSS 文件改动。 */
				$ew_rel   = function_exists( 'ew_post_related_links' ) ? ew_post_related_links( get_the_ID() ) : array();
				$ew_rel_g = isset( $ew_rel['guides'] ) ? $ew_rel['guides'] : array();
				$ew_rel_p = isset( $ew_rel['products'] ) ? $ew_rel['products'] : array();
				$ew_rel_h = isset( $ew_rel['hubs'] ) ? $ew_rel['hubs'] : array();
				if ( $ew_rel_g || $ew_rel_p || $ew_rel_h ) :
					$ew_rel_lbl = function_exists( 'ew_post_related_label' ) ? ew_post_related_label() : array();
					$ew_rel_sec = array(
						array( 'head' => isset( $ew_rel_lbl['guides'] ) ? $ew_rel_lbl['guides'] : 'Related Guides', 'items' => $ew_rel_g ),
						array( 'head' => isset( $ew_rel_lbl['products'] ) ? $ew_rel_lbl['products'] : 'Related Products', 'items' => $ew_rel_p ),
						array( 'head' => isset( $ew_rel_lbl['hubs'] ) ? $ew_rel_lbl['hubs'] : 'Browse Categories', 'items' => $ew_rel_h ),
					);
					$ew_rel_i = 0;
					?>
					<section class="cat-guides article-related">
						<?php foreach ( $ew_rel_sec as $ew_rel_s ) : ?>
							<?php
							if ( empty( $ew_rel_s['items'] ) ) {
								continue;
							}
							?>
							<h2 class="cat-guides__head"<?php echo ( $ew_rel_i++ > 0 ) ? ' style="margin-top:24px"' : ''; ?>><?php echo esc_html( $ew_rel_s['head'] ); ?></h2>
							<div class="cat-guides__list">
								<?php foreach ( $ew_rel_s['items'] as $ew_rel_x ) : ?>
									<a class="cat-guides__item" href="<?php echo esc_url( $ew_rel_x['url'] ); ?>"><?php echo esc_html( $ew_rel_x['title'] ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>
					</section>
				<?php endif; ?>

				<aside class="author-box">
					<?php if ( $ew_avatar = ew_author_photo() ) : ?>
						<img class="author-box__avatar-img" src="<?php echo esc_url( $ew_avatar ); ?>" alt="<?php echo esc_attr( ew_author_name() ); ?>" loading="lazy">
					<?php else : ?>
						<div class="author-box__avatar" aria-hidden="true"><?php echo esc_html( ew_author_initial() ); ?></div>
					<?php endif; ?>
					<div class="author-box__body">
						<p class="author-box__name"><?php echo esc_html( ew_author_name() ); ?></p>
						<p class="author-box__role"><?php echo esc_html( ew_author_role() ); ?></p>
						<p class="author-box__bio"><?php echo esc_html( ew_author_bio() ); ?></p>
					</div>
				</aside>
			</article>

		<?php endwhile; ?>

	</div>

	<aside class="page-layout__sidebar">
		<?php get_sidebar(); ?>
	</aside>
</div>

<section class="alt" id="inquiry" style="padding:40px 0">
	<div class="container">
		<h2 class="sec-title"><?php ew_esc_html_e('Questions About Our Products?'); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e('Send us a message — our engineers reply within 24 hours.'); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</div>
</section>

<?php
get_footer();
