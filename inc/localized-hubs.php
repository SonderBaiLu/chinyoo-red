<?php
defined( 'ABSPATH' ) || exit;

// Labels for the three navigation destinations, including the blog archive heading.
function ew_hub_labels() {
    return array(
        'en'=>array('Blog','FAQs','Services & Support','News & Insights'),
        'pl'=>array('Artykuły','Najczęstsze pytania','Serwis i wsparcie','Aktualności i poradniki'),
        'cs'=>array('Blog','Časté otázky','Servis a podpora','Novinky a průvodci'),
        'ro'=>array('Blog','Întrebări frecvente','Servicii și asistență','Noutăți și ghiduri'),
        'de'=>array('Blog','Häufige Fragen','Service und Support','Neuigkeiten und Ratgeber'),
        'fr'=>array('Blog','Questions fréquentes','Services et assistance','Actualités et guides'),
        'nl'=>array('Blog','Veelgestelde vragen','Service en ondersteuning','Nieuws en gidsen'),
        'it'=>array('Blog','Domande frequenti','Servizi e assistenza','Notizie e guide'),
        'ja'=>array('ブログ','よくある質問','サービス・サポート','ニュースとガイド'),
        'ko'=>array('블로그','자주 묻는 질문','서비스 및 지원','뉴스 및 가이드'),
        'zh'=>array('博客','常见问题','服务与支持','新闻与指南'),
        'es'=>array('Blog','Preguntas frecuentes','Servicios y asistencia','Noticias y guías'),
        'ru'=>array('Блог','Частые вопросы','Сервис и поддержка','Новости и руководства'),
        'ar'=>array('المدونة','الأسئلة الشائعة','الخدمات والدعم','الأخبار والأدلة'),
    );
}

add_shortcode( 'ew_local_faq', function () {
    $lang = pll_current_language();
    if ( ! isset( ew_hub_labels()[$lang] ) || 'en' === $lang ) return '';
    require_once __DIR__ . '/geo-faq-strings-' . $lang . '.php';
    $dict = call_user_func( 'ew_geo_faq_dict_' . $lang );
    $html = '';
    $category_labels = ew_geo_faq_categories();
    $temperature_labels = array('pl'=>'Mierniki temperatury','cs'=>'Teploměry','ro'=>'Termometre','de'=>'Temperaturmessgeräte','fr'=>'Thermomètres','nl'=>'Temperatuurmeters','it'=>'Termometri','ja'=>'温度計','ko'=>'온도계','zh'=>'温度计','es'=>'Termómetros','ru'=>'Термометры','ar'=>'أجهزة قياس الحرارة');
    foreach ( ew_geo_faq_all() as $group => $rows ) {
        $items = '';
        foreach ( $rows as $row ) {
            $id = $row['id'];
            if ( empty($dict[$id.'q']) || empty($dict[$id.'a']) ) continue;
            $items .= '<details class="product-faq__item"><summary>' . esc_html($dict[$id.'q']) . '</summary><div class="product-faq__answer"><p>' . esc_html($dict[$id.'a']) . '</p></div></details>';
        }
        if ( '' === $items ) continue;
        $heading = ew_hub_labels()[$lang][1];
        if ( 'general' !== $group ) {
            $heading = isset($category_labels[$group]) ? $category_labels[$group] : $temperature_labels[$lang];
            $source_terms = get_terms(array('taxonomy'=>'product_cat','slug'=>$group,'hide_empty'=>false,'lang'=>'en'));
            $source_term = !is_wp_error($source_terms) && !empty($source_terms) ? $source_terms[0] : null;
            if ( $source_term && !is_wp_error($source_term) ) {
                $translated_id = pll_get_term($source_term->term_id, $lang);
                $translated_term = $translated_id ? get_term($translated_id, 'product_cat') : null;
                if ( $translated_term && !is_wp_error($translated_term) ) $heading = $translated_term->name;
            }
        }
        $html .= '<section class="local-faq-group"><h2>' . esc_html($heading) . '</h2><div class="product-faq__list">' . $items . '</div></section>';
    }
    return '<div class="local-faq">'.$html.'</div>';
} );
add_shortcode( 'ew_local_support', function () {
    $html = '';
    foreach ( array('downloads'=>'Downloads','faqs'=>'FAQs','oem'=>'OEM / ODM','contact-us'=>'Contact Us') as $slug=>$label ) {
        $html .= '<li><a href="'.esc_url(ew_page_url($slug)).'">'.ew_esc_html__($label).'</a></li>';
    }
    return '<ul>'.$html.'</ul>';
} );


// The language landing-page plugin also matches is_home() on the posts page.
// Select the article loop only when WordPress resolved the configured news archive.
add_filter( 'template_include', function ( $template ) {
    return ew_is_news_archive() ? get_template_directory() . '/index.php' : $template;
}, PHP_INT_MAX );
