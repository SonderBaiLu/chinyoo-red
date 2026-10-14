<?php
/**
 * FAQ archive — grouped by faq_category (UNI-T style B2B).
 *
 * Structure:
 *  1. breadcrumbs (Home / FAQs)
 *  2. H1 "FAQs" + short intro
 *  3. FAQs grouped by faq_category: each group = H2 (term name, linked to the
 *     category archive) + a list of question links. FAQs with no category fall
 *     into an "Other Questions" group.
 *  4. Flat fallback: when the faq_category taxonomy has no terms assigned,
 *     every FAQ is listed in a single flat list driven by the main archive
 *     query, with the_posts_pagination().
 *  5. Inquiry CTA section (reuses template-parts/inquiry-form.php).
 *
 * Uses existing theme classes (.breadcrumbs, .container, .section,
 * .section__title, .section__subtitle, .entry-content).
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

// Grouping terms for the faq_category taxonomy.
$faq_terms = get_terms( array(
	'taxonomy'   => 'faq_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );
if ( is_wp_error( $faq_terms ) || ! is_array( $faq_terms ) ) {
	$faq_terms = array();
}
?>

<div class="container">

	<!-- Breadcrumbs: Home / FAQs -->
	<nav class="breadcrumbs" aria-label="<?php ew_esc_attr_e('Breadcrumb'); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php ew_esc_html_e('Home'); ?></a>
		<span class="breadcrumbs__sep">/</span>
		<span class="breadcrumbs__current"><?php ew_esc_html_e('FAQs'); ?></span>
	</nav>

	<header class="faq-archive-header">
		<h1 class="faq-archive-header__title"><?php ew_esc_html_e('FAQs'); ?></h1>
		<p class="faq-archive-header__intro">
			<?php ew_esc_html_e('Answers to the most common questions about our digital multimeters, clamp meters, voltage testers, and other electrical testing instruments. Everything here is written from our factory experience — distributors, wholesalers, and OEM partners are always welcome to ask more.'); ?>
		</p>
	</header>

	<?php if ( ! empty( $faq_terms ) ) : ?>

		<?php
		// Grouped listing: one query per term.
		foreach ( $faq_terms as $faq_term ) :
			$term_id   = (int) $faq_term->term_id;
			$term_link = get_term_link( $faq_term );
			if ( is_wp_error( $term_link ) ) {
				$term_link = '';
			}

			$group_query = new WP_Query( array(
				'post_type'      => 'faq',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'faq_category',
						'field'    => 'term_id',
						'terms'    => $term_id,
					),
				),
			) );

			if ( ! $group_query->have_posts() ) {
				continue;
			}
			?>
			<section class="faq-group" id="<?php echo esc_attr( 'faq-' . $faq_term->slug ); ?>">
				<h2 class="faq-group__title">
					<?php if ( '' !== $term_link ) : ?>
						<a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $faq_term->name ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $faq_term->name ); ?>
					<?php endif; ?>
				</h2>
				<?php if ( '' !== trim( (string) $faq_term->description ) ) : ?>
					<p class="faq-group__desc"><?php echo esc_html( $faq_term->description ); ?></p>
				<?php endif; ?>
				<ul class="faq-list">
					<?php
					while ( $group_query->have_posts() ) :
						$group_query->the_post();
						?>
						<li class="faq-list__item" id="faq-<?php echo esc_attr( get_the_ID() ); ?>">
							<h3 class="faq-list__q">
								<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</h3>
							<div class="faq-list__a entry-content">
								<?php the_content(); ?>
							</div>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</section>
		<?php endforeach; ?>

		<?php
		// FAQs that carry no faq_category term at all.
		$orphan_term_ids = array();
		foreach ( $faq_terms as $faq_term ) {
			$orphan_term_ids[] = (int) $faq_term->term_id;
		}
		$orphan_query = new WP_Query( array(
			'post_type'      => 'faq',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'faq_category',
					'operator' => 'NOT IN',
					'terms'    => $orphan_term_ids,
				),
			),
		) );
		if ( $orphan_query->have_posts() ) :
			?>
			<section class="faq-group faq-group--uncategorized" id="faq-other">
				<h2 class="faq-group__title"><?php ew_esc_html_e('Other Questions'); ?></h2>
				<ul class="faq-list">
					<?php
					while ( $orphan_query->have_posts() ) :
						$orphan_query->the_post();
						?>
						<li class="faq-list__item" id="faq-<?php echo esc_attr( get_the_ID() ); ?>">
							<h3 class="faq-list__q">
								<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</h3>
							<div class="faq-list__a entry-content">
								<?php the_content(); ?>
							</div>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</section>
			<?php
		endif;
		?>

	<?php else : ?>

		<?php
		// Flat fallback: no term grouping — list all from the main query.
		if ( have_posts() ) :
			?>
			<ul class="faq-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="faq-list__item">
						<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 2,
				'prev_text' => ew__('← Previous'),
				'next_text' => ew__('Next →'),
			) );
		else :
			?>
			<p class="faq-empty"><?php ew_esc_html_e('No FAQs have been published yet.'); ?></p>
		<?php endif; ?>

	<?php endif; ?>

	<!-- Inquiry CTA (reuses template-parts/inquiry-form.php) -->
	<section class="section faq-inquiry-cta" id="inquiry">
		<h2 class="section__title"><?php ew_esc_html_e('Can\'t Find What You Need?'); ?></h2>
		<p class="section__subtitle">
			<?php ew_esc_html_e('Send us your question — our team will reply within 24 hours with a straight answer.'); ?>
		</p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</section>
</div>
<?php
get_footer();
