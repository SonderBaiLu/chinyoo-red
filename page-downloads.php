<?php
get_header();
?>
<div class="crumb"><div class="container"><?php get_template_part( 'template-parts/breadcrumbs' ); ?></div></div>
<div class="page-head page-head--premium page-head--downloads" data-animation-wrap><div class="container"><p class="page-head__eyebrow" data-animation="1">CHINYOO / RESOURCE CENTER</p><h1 data-animation="1"><?php ew_esc_html_e( 'Downloads' ); ?></h1><p class="entry-content" data-animation="2">Datasheets, certificates and product documents for your technical and sourcing teams.</p></div></div>
<main class="inner-page inner-page--downloads"><div class="container">
<?php
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$args = array('post_type' => 'download', 'posts_per_page' => 12);
$args['paged'] = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;
if ($type) { $args['meta_key'] = 'download_type'; $args['meta_value'] = $type; }
$downloads = new WP_Query($args);
?>
<div class="filter-bar">
<a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn--sm<?php echo !$type ? ' btn--primary' : ' btn--outline'; ?>"><?php ew_esc_html_e('All'); ?></a>
<a href="?type=certification" class="btn btn--sm<?php echo $type === 'certification' ? ' btn--primary' : ' btn--outline'; ?>"><?php ew_esc_html_e('Certification'); ?></a>
<a href="?type=catalog" class="btn btn--sm<?php echo $type === 'catalog' ? ' btn--primary' : ' btn--outline'; ?>"><?php ew_esc_html_e('Catalog'); ?></a>
<a href="?type=patent" class="btn btn--sm<?php echo $type === 'patent' ? ' btn--primary' : ' btn--outline'; ?>"><?php ew_esc_html_e('Patent'); ?></a>
</div>
<?php if ($downloads->have_posts()): ?>
<div class="download-grid" data-animation-wrap>
<?php $ew_di = 1; ?>
<?php while ($downloads->have_posts()): $downloads->the_post();
    $file_url = get_post_meta(get_the_ID(), 'download_file', true);
    if (!$file_url) $file_url = get_field('download_file');
    $thumb = '';
    if ($file_url) {
        // Hash the FILE URL itself (not filesystem path) for a stable key
        $hash = substr(md5($file_url), 0, 16);
        $thumb = content_url('/uploads/thumbnails/' . $hash . '.jpg');
    }
    $file_ext = $file_url ? strtoupper( pathinfo( parse_url( $file_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) : '';
?>
<div class="download-card" data-animation="<?php echo esc_attr( $ew_di++ ); ?>">
<a href="<?php echo $file_url ? esc_url($file_url) : '#inquiry'; ?>" class="download-card__preview" target="<?php echo $file_url ? '_blank' : ''; ?>" rel="noopener">
<?php if ($file_url): ?>
    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr( ew_download_title( get_the_title() ) ); ?>" loading="lazy">
<?php else: ?>
    <div class="download-card__placeholder"><?php echo ew_esc_html__('File available on request'); ?></div>
<?php endif; ?>
</a>
<div class="download-card__info">
<h3 class="download-card__title"><?php echo esc_html( ew_download_title( get_the_title() ) ); ?></h3>
<?php if ( ! empty( $file_ext ) ) : ?><span class="download-card__ext"><?php echo esc_html( $file_ext ); ?></span><?php endif; ?>
<div class="download-card__actions">
<?php if ( $file_url ) : ?>
<a href="<?php echo esc_url($file_url); ?>" class="btn btn--sm btn--primary" target="_blank" rel="noopener"><?php ew_esc_html_e('View'); ?></a>
<a href="/download-proxy.php?url=<?php echo rawurlencode(rawurldecode($file_url)); ?>" class="btn btn--sm btn--outline"><?php ew_esc_html_e('Download'); ?></a>
<?php else : ?>
<a href="#inquiry" class="btn btn--sm btn--primary"><?php ew_esc_html_e('Request'); ?></a>
<?php endif; ?>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
<?php
echo '<div class="pagination">';
echo paginate_links(array('base' => '?pg=%#%' . ($type ? '&type=' . $type : ''), 'format' => '?pg=%#%', 'current' => max(1, intval($_GET['pg'] ?? 1)), 'total' => $downloads->max_num_pages, 'prev_text' => ew__('← Previous'), 'next_text' => ew__('Next →')));
echo '</div>';
wp_reset_postdata();
?>
<?php else: ?><p><?php ew_esc_html_e('No downloads found.'); ?></p><?php endif; ?>
</div>

</div></main>
<section class="inner-cta" id="inquiry">
	<div class="container">
		<h2 class="sec-title"><?php ew_esc_html_e('Need More Documents?'); ?></h2>
		<p class="sec-sub"><?php ew_esc_html_e('Request datasheets, certificates or the full product catalog.'); ?></p>
		<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '' ) ); ?>
	</div>
</section>
</div></main>
<?php get_footer();