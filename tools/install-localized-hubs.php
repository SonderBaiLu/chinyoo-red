<?php
// Run with the WordPress installation bootstrapped. Never serve this migration over HTTP.
if ( PHP_SAPI !== 'cli' || !defined('ABSPATH') ) exit;

function ew_nav_page($lang,$slug,$title,$body){
    $found=get_posts(array('name'=>$slug,'post_type'=>'page','lang'=>$lang,'suppress_filters'=>false,'post_status'=>'any','numberposts'=>1));
    if($found){
        if($found[0]->post_status!=='publish'||$found[0]->post_content!==$body)throw new RuntimeException('Page conflict: '.$slug);
        return $found[0]->ID;
    }
    $id=wp_insert_post(wp_slash(array('post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>$body)),true);
    if(is_wp_error($id))throw new RuntimeException($id->get_error_message());
    pll_set_post_language($id,$lang);
    return $id;
}
global $wpdb;
$news=pll_get_post_translations((int)get_option('page_for_posts'));
$supportPage=get_posts(array('post_type'=>'page','name'=>'support','lang'=>'en','suppress_filters'=>false,'numberposts'=>1));
if(empty($news['en'])||!$supportPage)throw new RuntimeException('Missing English source');
$support=pll_get_post_translations($supportPage[0]->ID);
$faq=array();
$wpdb->query('START TRANSACTION');
try {
foreach(ew_hub_labels() as $lang=>$labels){
    if($lang==='en')continue;
    if(empty($news[$lang]))$news[$lang]=ew_nav_page($lang,'news-'.$lang,$labels[3],'');
    if(empty($support[$lang]))$support[$lang]=ew_nav_page($lang,'support-'.$lang,$labels[2],'[ew_local_support]');
    $faq[$lang]=ew_nav_page($lang,'faq-guide-'.$lang,$labels[1],'[ew_local_faq]');
    $term=get_term_by('slug',$lang,'language');
    $key='_pll_strings_translations';
    $rows=get_term_meta($term->term_id,$key,true);
    if(!is_array($rows))throw new RuntimeException('Language dictionary: '.$lang);
    foreach(array('Blogs'=>$labels[0],'FAQs'=>$labels[1],'News & Insights'=>$labels[3]) as $source=>$value){
        $i=array_search($source,array_column($rows,0),true);
        if($i===false)$rows[]=array($source,$value);
        elseif(empty($rows[$i][1])||$rows[$i][1]===$source)$rows[$i][1]=$value;
    }
    update_term_meta($term->term_id,$key,$rows);
}
pll_save_post_translations($news);pll_save_post_translations($support);pll_save_post_translations($faq);
$wpdb->query('COMMIT');
} catch(Throwable $e){$wpdb->query('ROLLBACK');throw $e;}
echo 'Blog: '.count($news).', support: '.count($support).', localized FAQ: '.count($faq).PHP_EOL;

