<?php
// Resolve product_name: get_template_part passes data via $args['product_name'];
// fall back to a pre-set $product_name variable (legacy) or empty string.
$product_name = '';
if ( isset( $args ) && is_array( $args ) && isset( $args['product_name'] ) ) {
	$product_name = $args['product_name'];
} elseif ( isset( $product_name ) && is_string( $product_name ) ) {
	$product_name = $product_name;
}
$product_name = is_string( $product_name ) ? trim( $product_name ) : '';

// 国家区号下拉（参考图3）：用于 Phone 字段，提交时与号码拼成一串存入询盘。
$ew_countries = array(
	'+1'    => '🇺🇸 United States +1',
	'+1c'   => '🇨🇦 Canada +1',
	'+44'   => '🇬🇧 United Kingdom +44',
	'+86'   => '🇨🇳 China +86',
	'+852'  => '🇭🇰 Hong Kong +852',
	'+886'  => '🇹🇼 Taiwan +886',
	'+81'   => '🇯🇵 Japan +81',
	'+82'   => '🇰🇷 South Korea +82',
	'+91'   => '🇮🇳 India +91',
	'+92'   => '🇵🇰 Pakistan +92',
	'+880'  => '🇧🇩 Bangladesh +880',
	'+84'   => '🇻🇳 Vietnam +84',
	'+66'   => '🇹🇭 Thailand +66',
	'+60'   => '🇲🇾 Malaysia +60',
	'+65'   => '🇸🇬 Singapore +65',
	'+62'   => '🇮🇩 Indonesia +62',
	'+63'   => '🇵🇭 Philippines +63',
	'+61'   => '🇦🇺 Australia +61',
	'+64'   => '🇳🇿 New Zealand +64',
	'+49'   => '🇩🇪 Germany +49',
	'+33'   => '🇫🇷 France +33',
	'+39'   => '🇮🇹 Italy +39',
	'+34'   => '🇪🇸 Spain +34',
	'+31'   => '🇳🇱 Netherlands +31',
	'+32'   => '🇧🇪 Belgium +32',
	'+43'   => '🇦🇹 Austria +43',
	'+41'   => '🇨🇭 Switzerland +41',
	'+46'   => '🇸🇪 Sweden +46',
	'+47'   => '🇳🇴 Norway +47',
	'+45'   => '🇩🇰 Denmark +45',
	'+358'  => '🇫🇮 Finland +358',
	'+48'   => '🇵🇱 Poland +48',
	'+420'  => '🇨🇿 Czechia +420',
	'+351'  => '🇵🇹 Portugal +351',
	'+30'   => '🇬🇷 Greece +30',
	'+353'  => '🇮🇪 Ireland +353',
	'+7'    => '🇷🇺 Russia +7',
	'+380'  => '🇺🇦 Ukraine +380',
	'+90'   => '🇹🇷 Turkey +90',
	'+972'  => '🇮🇱 Israel +972',
	'+966'  => '🇸🇦 Saudi Arabia +966',
	'+971'  => '🇦🇪 UAE +971',
	'+20'   => '🇪🇬 Egypt +20',
	'+27'   => '🇿🇦 South Africa +27',
	'+234'  => '🇳🇬 Nigeria +234',
	'+254'  => '🇰🇪 Kenya +254',
	'+55'   => '🇧🇷 Brazil +55',
	'+52'   => '🇲🇽 Mexico +52',
	'+54'   => '🇦🇷 Argentina +54',
	'+56'   => '🇨🇱 Chile +56',
	'+57'   => '🇨🇴 Colombia +57',
	'+51'   => '🇵🇪 Peru +51',
);
// 按当前语言预选区号（默认美国 +1）。
$ew_cc_lang          = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
$ew_cc_lang_defaults = array( 'zh' => '+86', 'ru' => '+7', 'es' => '+34', 'ar' => '+971' );
$ew_cc_sel           = isset( $ew_cc_lang_defaults[ $ew_cc_lang ] ) ? $ew_cc_lang_defaults[ $ew_cc_lang ] : '+1';
?>
<form class="inquiry-form" id="ew-inquiry-form" method="post"<?php if ( isset( $args ) && is_array( $args ) && isset( $args['anim'] ) && '' !== (string) $args['anim'] ) { echo ' data-animation="' . esc_attr( $args['anim'] ) . '"'; } ?>>
    <div class="inquiry-form__row">
        <div class="inquiry-form__field">
            <label for="inquiry_name"><?php ew_esc_html_e('Name'); ?> <span style="color:#cc0000;">*</span></label>
            <input type="text" id="inquiry_name" name="inquiry_name" placeholder="<?php ew_esc_attr_e('Your full name'); ?>" required>
        </div>
        <div class="inquiry-form__field">
            <label for="inquiry_email"><?php ew_esc_html_e('Email'); ?> <span style="color:#cc0000;">*</span></label>
            <input type="email" id="inquiry_email" name="inquiry_email" placeholder="<?php ew_esc_attr_e('your@email.com'); ?>" required>
        </div>
        <div class="inquiry-form__field">
            <label for="inquiry_phone"><?php ew_esc_html_e('Phone / WhatsApp'); ?> <span style="color:#cc0000;">*</span></label>
            <div class="inquiry-form__phone">
                <select id="inquiry_phone_cc" name="inquiry_phone_cc" class="inquiry-form__cc" aria-label="<?php ew_esc_attr_e('Country code'); ?>">
                    <?php foreach ( $ew_countries as $ew_cc_val => $ew_cc_label ) : ?>
                        <option value="<?php echo esc_attr( $ew_cc_val ); ?>"<?php selected( $ew_cc_val, $ew_cc_sel ); ?>><?php echo esc_html( $ew_cc_label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="tel" id="inquiry_phone" name="inquiry_phone" placeholder="<?php ew_esc_attr_e('WhatsApp / WeChat number'); ?>" required>
            </div>
        </div>
    </div>
    <div class="inquiry-form__field">
        <label for="inquiry_company"><?php ew_esc_html_e('Company'); ?></label>
        <input type="text" id="inquiry_company" name="inquiry_company" placeholder="<?php ew_esc_attr_e('Your company name'); ?>">
    </div>
    <div class="inquiry-form__field">
        <label for="inquiry_product"><?php ew_esc_html_e('Product Interest'); ?></label>
        <input type="text" id="inquiry_product" name="inquiry_product"
               value="<?php echo esc_attr( $product_name ); ?>"
               placeholder="<?php ew_esc_attr_e('Which product are you interested in?'); ?>"
               <?php echo $product_name ? 'readonly' : ''; ?>>
    </div>
    <div class="inquiry-form__field">
        <label for="inquiry_message"><?php ew_esc_html_e('Message'); ?> <span style="color:#cc0000;">*</span></label>
        <textarea id="inquiry_message" name="inquiry_message" rows="5"
                  placeholder="<?php ew_esc_attr_e('Tell us about your requirements, quantity, or any questions...'); ?>"
                  required></textarea>
    </div>
    <div class="inquiry-form__feedback" id="inquiry-feedback" style="display:none;"></div>

    <?php // Turnstile 人机验证：token 由组件自己写进 cf-turnstile-response 字段，随 FormData 一起提交。 ?>
    <div class="inquiry-form__turnstile">
        <div class="cf-turnstile"
             data-sitekey="<?php echo esc_attr( EW_TURNSTILE_SITE_KEY ); ?>"
             data-language="auto"
             data-size="flexible"></div>
    </div>

    <button type="submit" class="btn btn--inquiry btn--lg" style="width:100%; padding:14px 0; font-size:1rem;">
        <?php ew_esc_html_e('Submit Inquiry'); ?>
    </button>

    <!-- 防垃圾蜜罐：对真人完全隐藏，仅机器人会填；后端检测到非空即整单丢弃 -->
    <div class="inquiry-form__honeypot" aria-hidden="true">
        <label for="inquiry_website">Company Website</label>
        <input type="text" id="inquiry_website" name="inquiry_website" tabindex="-1" autocomplete="off">
    </div>
    <style>
        .inquiry-form__honeypot{position:absolute!important;left:0!important;top:0!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;overflow:hidden;clip:rect(0 0 0 0);clip-path:inset(50%);white-space:nowrap;border:0;opacity:0;pointer-events:none}
    </style>
</form>
