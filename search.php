<?php
get_header();
?>

<div class="container" data-animation-wrap>
        <h1 class="page-title" data-animation="1">
            <?php printf(
                ew_esc_html__('Search Results for: %s'),
                '<span>' . esc_html( get_search_query() ) . '</span>'
            ); ?>
        </h1>

        <?php if ( have_posts() ) : ?>
            <div class="search-results">
                <?php $ew_sri = 2; ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <article <?php post_class( 'search-item' ); ?> data-animation="<?php echo esc_attr( $ew_sri++ ); ?>">
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                        <span class="search-item__type"><?php echo esc_html( get_post_type() ); ?></span>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p><?php ew_esc_html_e('No results found. Please try a different search term.'); ?></p>
        <?php endif; ?>
</div>

<?php
get_footer();
