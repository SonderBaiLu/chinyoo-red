<?php
defined( 'ABSPATH' ) || exit;

// Labels for the three navigation destinations, including the blog archive heading.
function ew_hub_labels() {
    return array(
        'en'=>array('Blog','FAQs','Services & Support','News & Insights'),
        'pl'=>array('Blog','Najczęstsze pytania','Serwis i wsparcie','Aktualności i poradniki'),
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
    foreach ( array('g1','g2','g7','bdm1','bdm2','bdm3','bdm4','dcm1','dcm2','dcm3','dcm4') as $id ) {
        if ( empty($dict[$id.'q']) || empty($dict[$id.'a']) ) continue;
        $html .= '<details class="product-faq__item"><summary>' . esc_html($dict[$id.'q']) . '</summary><p>' . esc_html($dict[$id.'a']) . '</p></details>';
    }
    return '<div class="product-faq__list">'.$html.'</div>';
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
