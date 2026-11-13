<?php
defined('ABSPATH') || exit;

add_action('admin_menu',function(){
    add_options_page('联系面板','联系面板','manage_options','ew-contact-panel','ew_contact_settings_page');
});
add_action('admin_enqueue_scripts',function($hook){
    if($hook !== 'settings_page_ew-contact-panel') return;
    wp_enqueue_media();
    wp_enqueue_script('ew-contact-editor',get_template_directory_uri().'/assets/js/contact-editor.js',array(),ew_contact_asset('js/contact-editor.js'),true);
});
function ew_contact_settings_page(){
    $apps=ew_contact_apps();
    $avatars = array();
    foreach ($apps as $app) {
        foreach ($app['contacts'] as $person) {
            $id = $person['avatar_id'] ?? 0;
            if ($id) $avatars[$id] = wp_get_attachment_image_url($id,'thumbnail');
        }
    }
    ?>
    <div class="wrap"><h1>联系面板</h1>
    <?php if(isset($_GET['saved'])) echo '<div class="notice notice-success"><p>联系面板已保存。</p></div>'; ?>
    <p>按应用添加联系人。默认名称用于所有语言，在“多语言名称”里可以另填译名。只有含有联系人的应用会出现在网站右下角。</p>
    <form id="ew-contact-editor" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="max-width:780px">
        <?php wp_nonce_field('ew_contact_save'); ?>
        <input type="hidden" name="action" value="ew_contact_save">
        <input type="hidden" name="revision" value="<?php echo esc_attr(hash('sha256',wp_json_encode($apps))); ?>">
        <input type="hidden" name="apps" id="ew-contact-data">
        <script type="application/json" id="ew-contact-initial"><?php echo wp_json_encode(array('apps'=>$apps,'languages'=>ew_contact_languages(),'avatars'=>$avatars),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?></script>
        <div id="ew-contact-groups"></div><button class="button" id="ew-contact-add" type="button">添加联系应用</button>
        <?php submit_button('保存联系面板','primary','submit',true,array('disabled'=>'disabled')); ?>
    </form></div>
    <?php
}
function ew_contact_clean_row($row){
    if(!is_array($row) || empty($row['name']) || !is_string($row['name'])) return false;
    $name=sanitize_text_field($row['name']);
    if($name==='') return false;
    $labels=array();
    foreach(ew_contact_languages() as $key=>$unused){
        $value=$row['labels'][$key] ?? '';
        if(!is_string($value))return false;
        $value=sanitize_text_field($value);
        if($value!=='')$labels[$key]=$value;
    }
    return array('name'=>$name,'labels'=>$labels);
}
function ew_contact_valid_url($url){
    if(!is_string($url) || preg_match('/[\s\x00-\x1f\\\\]/',$url))return false;
    if(strpos($url,'mailto:')===0)return (bool)is_email(substr($url,7));
    if(strpos($url,'tel:')===0)return (bool)preg_match('/^tel:\+?[0-9().-]+$/D',$url);
    return strpos($url,'https://')===0 && wp_parse_url($url,PHP_URL_HOST) && filter_var($url,FILTER_VALIDATE_URL);
}
add_action('admin_post_ew_contact_save',function(){
    if(!current_user_can('manage_options'))wp_die('没有权限。', '',array('response'=>403));
    check_admin_referer('ew_contact_save');
    $apps=json_decode(wp_unslash($_POST['apps'] ?? ''),true);
    if(!is_array($apps))wp_die('无法读取表单。请返回后重试。');
    $clean=array();
    foreach($apps as $app){
        $group=ew_contact_clean_row($app);
        if(!$group || !isset($app['contacts']) || !is_array($app['contacts']))wp_die('请填写应用名称。');
        $group['contacts']=array();
        foreach($app['contacts'] as $person){
            $row=ew_contact_clean_row($person);
            if(!$row || !ew_contact_valid_url($person['url'] ?? null))wp_die('请填写联系人名称和有效的 HTTPS、mailto: 或 tel: 链接。可返回修改。');
            $row['url']=esc_url_raw($person['url'],array('https','mailto','tel'));
            $avatar_id = absint($person['avatar_id'] ?? 0);
            if ($avatar_id && wp_attachment_is_image($avatar_id)) $row['avatar_id'] = $avatar_id;
            foreach (array('name_color','detail_color') as $key) {
                $color = sanitize_hex_color($person[$key] ?? '');
                if ($color) $row[$key] = $color;
            }
            $group['contacts'][]=$row;
        }
        $clean[]=$group;
    }
    $revision=hash('sha256',wp_json_encode(ew_contact_apps()));
    if(!hash_equals($revision,(string)($_POST['revision'] ?? '')))wp_die('其他编辑已保存变更。请重新载入设置后编辑。');
    update_option('ew_contact_apps',$clean);
    wp_safe_redirect(admin_url('options-general.php?page=ew-contact-panel&saved=1'));exit;
});
