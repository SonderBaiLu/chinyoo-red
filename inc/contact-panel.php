<?php
defined( 'ABSPATH' ) || exit;

function ew_contact_languages() {
    return array( 'en'=>'English', 'zh'=>'简体中文', 'de'=>'Deutsch', 'fr'=>'Français', 'nl'=>'Nederlands', 'it'=>'Italiano', 'ja'=>'日本語', 'ko'=>'한국어', 'es'=>'Español', 'ru'=>'Русский', 'ar'=>'العربية', 'pl'=>'Polski', 'cs'=>'Čeština', 'ro'=>'Română' );
}

function ew_contact_apps() {
    $apps = get_option( 'ew_contact_apps', false );
    if ( false !== $apps ) {
        return $apps;
    }
    $wa = array();
    $mail = array();
    foreach ( array( array('Chingy','8617816800511','chingycai'), array('Jack','8617706872345','jack'), array('Anna','8617706873456','anna') ) as $person ) {
        list($name, $number, $user) = $person;
        $wa[] = array('name'=>$name, 'url'=>'https://wa.me/'.$number);
        $mail[] = array('name'=>$name, 'url'=>'mailto:'.$user.'@chinyoo.com');
    }
    return array(
        array('name'=>'WhatsApp','contacts'=>$wa),
        array('name'=>'Email','labels'=>array('zh'=>'电子邮件','de'=>'E-Mail','fr'=>'E-mail','nl'=>'E-mail','it'=>'E-mail','ja'=>'メール','ko'=>'이메일','es'=>'Correo electrónico','ru'=>'Электронная почта','ar'=>'البريد الإلكتروني','pl'=>'E-mail','cs'=>'E-mail','ro'=>'E-mail'),'contacts'=>$mail)
    );
}

function ew_contact_copy( $lang ) {
    $copy = array(
        'en'=>array('Contact us','Close'), 'zh'=>array('联系我们','关闭'), 'de'=>array('Kontakt','Schließen'),
        'fr'=>array('Nous contacter','Fermer'), 'nl'=>array('Neem contact op','Sluiten'), 'it'=>array('Contattaci','Chiudi'),
        'ja'=>array('お問い合わせ','閉じる'), 'ko'=>array('문의하기','닫기'), 'es'=>array('Contáctanos','Cerrar'),
        'ru'=>array('Связаться с нами','Закрыть'), 'ar'=>array('تواصل معنا','إغلاق'), 'pl'=>array('Kontakt','Zamknij'),
        'cs'=>array('Kontaktujte nás','Zavřít'), 'ro'=>array('Contactează-ne','Închide')
    );
    return $copy[$lang] ?? $copy['en'];
}
function ew_contact_name( $row, $lang ) {
    return $row['labels'][$lang] ?? $row['name'];
}
function ew_contact_asset( $file ) {
    return filemtime( get_template_directory().'/assets/'.$file );
}
add_action( 'wp_enqueue_scripts', function () {
    if ( ! ew_contact_apps() ) return;
    $uri = get_template_directory_uri().'/assets/';
    wp_enqueue_style('ew-contact-panel', $uri.'css/contact-panel.css', array(), ew_contact_asset('css/contact-panel.css'));
    wp_enqueue_script('ew-contact-panel', $uri.'js/contact-panel.js', array(), ew_contact_asset('js/contact-panel.js'), true);
} );

function ew_contact_render() {
    $apps = array_filter(ew_contact_apps(), static function($app){ return !empty($app['contacts']); });
    if (!$apps) return;
    $lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'en';
    list($title,$close) = ew_contact_copy($lang);
    ?>
    <aside class="ew-contact" aria-label="<?php echo esc_attr($title); ?>">
        <button class="ew-contact-trigger" type="button" aria-label="<?php echo esc_attr($title); ?>" aria-expanded="false" aria-controls="ew-contact-card">
            <svg aria-hidden="true" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 11.5a8.5 8.5 0 0 1-8.5 8.5H5l-3 2 1.4-6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M7.8 9.5h8.5m-8.5 4h5.5"/></svg>
        </button>
        <section id="ew-contact-card" class="ew-contact-card" aria-labelledby="ew-contact-title" hidden>
            <header><h2 id="ew-contact-title"><?php echo esc_html($title); ?></h2><button type="button" class="ew-contact-close" aria-label="<?php echo esc_attr($close); ?>">×</button></header>
            <div class="ew-contact-channels" role="group" aria-labelledby="ew-contact-title">
            <?php foreach ($apps as $index => $app) : ?>
                <button type="button" class="ew-contact-channel" aria-controls="ew-contact-group-<?php echo (int)$index; ?>" aria-pressed="<?php echo $index === array_key_first($apps) ? 'true' : 'false'; ?>"><?php echo esc_html(ew_contact_name($app,$lang)); ?></button>
            <?php endforeach; ?></div>
            <div class="ew-contact-list">
            <?php foreach ($apps as $index => $app) : $app_name=ew_contact_name($app,$lang); ?>
                <section class="ew-contact-group" id="ew-contact-group-<?php echo (int)$index; ?>" aria-label="<?php echo esc_attr($app_name); ?>" <?php if ($index !== array_key_first($apps)) echo 'hidden'; ?>>
                <?php foreach ($app['contacts'] as $contact) : $name=ew_contact_name($contact,$lang); ?>
<?php
                    $photo = !empty($contact['avatar_id']) ? wp_get_attachment_image($contact['avatar_id'],'thumbnail',false,array('alt'=>'','loading'=>'lazy')) : '';
                    $style = '';
                    foreach (array('name_color'=>'--person-name','detail_color'=>'--person-detail') as $key=>$property) {
                        $color = sanitize_hex_color($contact[$key] ?? '');
                        if ($color) $style .= $property.':'.$color.';';
                    }
                    ?>
                    <a style="<?php echo esc_attr($style); ?>" href="<?php echo esc_url($contact['url'],array('https','mailto','tel')); ?>" <?php if(strpos($contact['url'],'https://')===0) echo 'target="_blank" rel="noopener noreferrer"'; ?> aria-label="<?php echo esc_attr($name.' · '.$app_name); ?>">
                        <span class="ew-contact-avatar" aria-hidden="true"><?php echo $photo ?: esc_html(wp_html_excerpt($name,1,'')); ?></span>
                        <span class="ew-contact-details"><span class="ew-contact-name"><?php echo esc_html($name); ?></span><?php $destination = preg_replace('~^mailto:|^tel:~', '', $contact['url']); if (preg_match('~^https://wa\.me/(\d+)(?:\?.*)?$~', $destination, $number)) $destination = '+'.$number[1]; if (strpos($destination, 'https://') === 0) $destination = wp_parse_url($destination, PHP_URL_HOST); ?><small dir="auto"><?php echo esc_html($destination); ?></small></span><span class="ew-contact-arrow" aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?></section>
            <?php endforeach; ?></div>
        </section>
    </aside>
    <?php
}

if ( is_admin() ) require_once __DIR__.'/contact-panel-admin.php';
