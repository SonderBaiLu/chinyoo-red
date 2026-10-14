<?php
get_header();
?>
<div class="container error-404" style="text-align:center; padding: 80px 0;">
    <h1><?php ew_esc_html_e('404'); ?></h1>
    <p><?php ew_esc_html_e('Page not found.'); ?></p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary">
        <?php ew_esc_html_e('Back to Home'); ?>
    </a>
</div>
<?php
get_footer();
