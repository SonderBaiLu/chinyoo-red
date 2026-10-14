<?php
/**
 * Blog sidebar — recent posts (sticky, styled in main.css)
 */
?>
<div class="sidebar">
    <?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
        <?php dynamic_sidebar( 'blog-sidebar' ); ?>
    <?php else : ?>
        <div class="widget">
            <h3 class="widget-title"><?php ew_esc_html_e('Recent Posts'); ?></h3>
            <ul>
                <?php
                $recent = new WP_Query( array(
                    'posts_per_page' => 5,
                    'post_status'    => 'publish',
                ) );
                while ( $recent->have_posts() ) : $recent->the_post(); ?>
                    <li><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></li>
                <?php endwhile;
                wp_reset_postdata();
                ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
