<?php
/**
 * FAQ category archive — list of FAQ questions within a faq_category term.
 *
 * Structure:
 *  1. breadcrumbs (Home / FAQs / {term})
 *  2. H1 term name (+ term description when present)
 *  3. FAQ question links with excerpts
 *  4. pagination (the_posts_pagination)
 *  5. inquiry CTA section (reuses template-parts/inquiry-form.php)
 *
 * Uses existing theme classes (.breadcrumbs, .container, .entry-content,
 * .section, .section__title, .section__subtitle).
 *
 * @package electrical-world
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Defensive FAQ archive URL for breadcrumbs (rewrite slug: /faqs/).
$faq_archive_url = function_exists( 'get_post_type_archive_link' ) ? get_post_type_archive_link( 'faq' ) : '';
if ( ! is_string( $faq_archive_url ) || '' === $faq_archive_url ) {
	$faq_archive_url = ew_page_url( 'faqs' );
}

// Queried faq_category term.
$term              = get_queried_object();
$term_name         = '';
$term_description  = '';
if ( $term && isset( $term->name ) ) {
	$term_name = (string) $term->name;
}
if ( $term && isset( $term->description ) ) {
	$term_description = (string) $term->description;
}
if ( '' === $term_name ) {
	$term_name = ew__('FAQs');
}
?>

<div class="container">

	<!-- Breadcrumbs: Home / FAQs / {term} -->
	<nav class="breadcrumbs" aria-label="<?php ew_esc_attr_e('Breadcrumb'); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
		<span class="breadcrumbs__sep">/</span>
		<a href="<?php echo esc_url( $faq_archive_url ); ?>"><?php ew_esc_html_e('FAQs'); ?></a>
		<span class="breadcrumbs__sep">/</span>
		<span class="breadcrumbs__current"><?php echo esc_html( $term_name ); ?></span>
	</nav>

	<header class="faq-category-header">
		<h1 class="faq-category-header__title"><?php echo esc_html( $term_name ); ?></h1>
		<?php if ( '' !== trim( wp_strip_all_tags( $term_description ) ) ) : ?>
			<div class="faq-category-header__desc entry-content"><?php echo wp_kses_post( $term_description ); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<ul class="faq-list faq-list--excerpts">
			<?php
			while ( have_posts() ) :
				the_post();
				$faq_excerpt = get_the_excerpt();
				if ( '' === trim( $faq_excerpt ) ) {
					$faq_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content() ), 22, '…' );
				}
				?>
				<li class="faq-list__item">
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="faq-list__link">
						<?php echo esc_html( get_the_title() ); ?>
					</a>
					<?php if ( '' !== trim( $faq_excerpt ) ) : ?>
						<p class="faq-list__excerpt"><?php echo esc_html( $faq_excerpt ); ?></p>
					<?php endif; ?>
				</li>
				<?php
			endwhile;
			?>
		</ul>

		<?php
		the_posts_pagination( array(
			'mid_size'  => 2,
			'prev_text' => ew__('← Previous'),
			'next_text' => ew__('Next →'),
		) );
		?>

	<?php else : ?>
		<p class="faq-empty"><?php ew_esc_html_e('No FAQs published in this category yet.'); ?></p>
	<?php endif; ?>

	<!-- Inquiry CTA (reuses template-parts/inquiry-form.php) -->
	<section class="section faq-inquiry-cta" id="inquiry">
		<h2 class="section__title"><?php ew_esc_html_e('Ask About This Topic'); ?></h2>
		<p class="section__subtitle">
			<?php ew_esc_html_e('Have a specific question about our instruments? Send it to us and our team will reply within 24 hours.'); ?>
		</p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => $term_name ) ); ?>
	</section>
</div>
<?php
get_footer();
