<?php
/**
 * Single FAQ — question + answer page (UNI-T style B2B).
 *
 * Structure:
 *  1. breadcrumbs (Home / FAQs / {faq_category} / {title})
 *  2. H1 question
 *  3. answer content (the_content)
 *  4. previous / next FAQ navigation (same faq_category)
 *  5. related FAQs (same faq_category, falls back to recent)
 *  6. inquiry CTA section (reuses template-parts/inquiry-form.php)
 *
 * Uses the existing theme classes (.breadcrumbs, .container, .entry-content,
 * .section, .section__title, .section__subtitle) so no extra CSS is required.
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

while ( have_posts() ) :
	the_post();

	$faq_id  = get_the_ID();
	$faq_title = (string) get_the_title();

	// faq_category term(s) on this FAQ.
	$faq_terms    = get_the_terms( $faq_id, 'faq_category' );
	$faq_term_ids = array();
	if ( is_array( $faq_terms ) && ! is_wp_error( $faq_terms ) && ! empty( $faq_terms ) ) {
		foreach ( $faq_terms as $faq_term ) {
			$faq_term_ids[] = (int) $faq_term->term_id;
		}
	}
	$faq_term_ids = array_values( array_filter( array_unique( $faq_term_ids ) ) );

	// Related FAQs: same faq_category (excludes current). Falls back to the
	// most recent FAQs when this FAQ has no category assigned.
	$related_args = array(
		'post_type'      => 'faq',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'post__not_in'   => array( $faq_id ),
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	);
	if ( ! empty( $faq_term_ids ) ) {
		$related_args['tax_query'] = array(
			array(
				'taxonomy' => 'faq_category',
				'field'    => 'term_id',
				'terms'    => $faq_term_ids,
			),
		);
	}
	$related = new WP_Query( $related_args );

	// Previous / next FAQ within the same faq_category.
	$prev_faq = get_adjacent_post( true, '', true, 'faq_category' );
	$next_faq = get_adjacent_post( true, '', false, 'faq_category' );
	?>
	<div class="container">

		<!-- Breadcrumbs: Home / FAQs / {faq_category} / {title} -->
		<nav class="breadcrumbs" aria-label="<?php ew_esc_attr_e('Breadcrumb'); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
			<span class="breadcrumbs__sep">/</span>
			<a href="<?php echo esc_url( $faq_archive_url ); ?>"><?php ew_esc_html_e('FAQs'); ?></a>
			<?php
			if ( ! empty( $faq_term_ids ) && is_array( $faq_terms ) && ! is_wp_error( $faq_terms ) ) :
				$first_faq_term = current( $faq_terms ); // 缓存按 term_id 键控，用 current() 取首项（勿用 [0]）
				$faq_term_link  = get_term_link( $first_faq_term );
				if ( ! is_wp_error( $faq_term_link ) ) :
					?>
					<span class="breadcrumbs__sep">/</span>
					<a href="<?php echo esc_url( $faq_term_link ); ?>"><?php echo esc_html( $first_faq_term->name ); ?></a>
				<?php endif; ?>
			<?php endif; ?>
			<span class="breadcrumbs__sep">/</span>
			<span class="breadcrumbs__current"><?php echo esc_html( $faq_title ); ?></span>
		</nav>

		<!-- H1 question -->
		<header class="faq-header">
			<h1 class="faq-header__title"><?php echo esc_html( $faq_title ); ?></h1>
		</header>

		<!-- Answer content -->
		<div class="faq-content entry-content">
			<?php the_content(); ?>
		</div>

		<!-- Previous / next FAQ -->
		<?php if ( ( $prev_faq instanceof WP_Post ) || ( $next_faq instanceof WP_Post ) ) : ?>
			<nav class="faq-nav" aria-label="<?php ew_esc_attr_e('FAQ navigation'); ?>">
				<?php if ( $prev_faq instanceof WP_Post ) : ?>
					<a class="faq-nav__link faq-nav__link--prev" href="<?php echo esc_url( get_permalink( $prev_faq ) ); ?>">
						&larr; <?php echo esc_html( get_the_title( $prev_faq ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $next_faq instanceof WP_Post ) : ?>
					<a class="faq-nav__link faq-nav__link--next" href="<?php echo esc_url( get_permalink( $next_faq ) ); ?>">
						<?php echo esc_html( get_the_title( $next_faq ) ); ?> &rarr;
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<!-- Related FAQs -->
		<?php if ( $related->have_posts() ) : ?>
			<section class="faq-related">
				<h2 class="section__title"><?php ew_esc_html_e('Related FAQs'); ?></h2>
				<ul class="faq-list">
					<?php
					while ( $related->have_posts() ) :
						$related->the_post();
						?>
						<li class="faq-list__item">
							<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</section>
		<?php endif; ?>

		<!-- Inquiry CTA (reuses template-parts/inquiry-form.php) -->
		<section class="section faq-inquiry-cta" id="inquiry">
			<h2 class="section__title"><?php ew_esc_html_e('Still Have Questions?'); ?></h2>
			<p class="section__subtitle">
				<?php ew_esc_html_e('Send us your question — our factory team will reply with answers, pricing, and lead time within 24 hours.'); ?>
			</p>
			<?php
			get_template_part(
				'template-parts/inquiry-form',
				null,
				array( 'product_name' => $faq_title )
			);
			?>
		</section>
	</div>
	<?php
endwhile;

get_footer();
