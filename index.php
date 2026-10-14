<?php
/**
 * Blog listing & fallback for any non-specialized queries
 */
get_header();
?>

<div class="container page-layout">
    <div class="page-layout__main" data-animation-wrap>
        <h1 class="page-title" data-animation="1"><?php ew_esc_html_e('News & Insights'); ?></h1>

        <?php if ( have_posts() ) : ?>
            <div class="blog-grid">
                <?php $ew_bi = 2; ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <article <?php post_class( 'blog-card' ); ?> data-animation="<?php echo esc_attr( $ew_bi++ ); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="blog-card__image">
                                <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="blog-card__content">
                            <span class="blog-card__date"><?php echo esc_html( get_the_date() ); ?></span>
                            <h2 class="blog-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="pagination">
                <?php echo paginate_links( array(
                    'prev_text' => '← ' . ew__('Previous'),
                    'next_text' => ew__('Next') . ' →',
                ) ); ?>
            </div>
        <?php else : ?>
            <p><?php ew_esc_html_e('No posts found.'); ?></p>
        <?php endif; ?>
    </div>

    <aside class="page-layout__sidebar">
        <?php get_sidebar(); ?>
    </aside>
</div>

<section class="alt" id="inquiry" style="padding:40px 0" data-animation-wrap>
	<div class="container">
		<h2 class="sec-title" data-animation="1"><?php ew_esc_html_e('Questions About Our Products?'); ?></h2>
		<p class="sec-sub" data-animation="2"><?php ew_esc_html_e('Send us a message — our engineers reply within 24 hours.'); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '', 'anim' => 3 ) ); ?>
	</div>
</section>

<?php
get_footer();
