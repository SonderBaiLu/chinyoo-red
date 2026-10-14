<?php
get_header();
?>
<div class="container">
<?php get_template_part('template-parts/breadcrumbs'); ?>
<h1 class="page-title"><?php single_term_title(); ?></h1>

<?php
$current = get_queried_object();
$top_cats = get_terms(['taxonomy'=>'product_category','parent'=>0,'hide_empty'=>true]);
?>
<div class="filter-bar">
    <a href="<?php echo esc_url(get_post_type_archive_link('product')); ?>" class="btn btn--sm btn--outline"><?php ew_esc_html_e('All'); ?></a>
    <?php foreach ($top_cats as $cat):
        $active = ($current->term_id == $cat->term_id || $current->parent == $cat->term_id);
    ?>
        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="btn btn--sm<?php echo $active?' btn--primary':' btn--outline'; ?>"><?php echo esc_html($cat->name); ?></a>
    <?php endforeach; ?>
</div>

<?php
$children = get_terms(['taxonomy'=>'product_category','parent'=>$current->term_id,'hide_empty'=>true]);
if (empty($children) && $current->parent > 0) {
    $children = get_terms(['taxonomy'=>'product_category','parent'=>$current->parent,'hide_empty'=>true]);
}
if (!empty($children)): ?>
<div class="subcat-bar">
    <?php 
    $parent_id = ($current->parent > 0) ? $current->parent : $current->term_id;
    $parent_link = get_term_link($parent_id);
    ?>
    <a href="<?php echo esc_url($parent_link); ?>" class="subcat-link<?php echo ($current->parent==0)?' subcat-link--active':''; ?>"><?php echo '← ' . ew_esc_html__('All'); ?></a>
    <?php foreach ($children as $child):
        $child_active = ($current->term_id == $child->term_id);
    ?>
        <a href="<?php echo esc_url(get_term_link($child)); ?>" class="subcat-link<?php echo $child_active?' subcat-link--active':''; ?>"><?php echo esc_html($child->name); ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (have_posts()): ?>
<div class="product-grid product-grid--3col">
<?php while (have_posts()): the_post(); get_template_part('template-parts/product-card'); endwhile; ?>
</div>
<div class="pagination"><?php echo paginate_links(['prev_text'=>ew__('← Previous'),'next_text'=>ew__('Next →')]); ?></div>
<?php else: ?>
<p><?php ew_esc_html_e('No products found.'); ?></p>
<?php endif; ?>
</div>
<?php get_footer();
