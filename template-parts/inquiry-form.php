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
            <input type="tel" id="inquiry_phone" name="inquiry_phone" placeholder="<?php ew_esc_attr_e('WhatsApp / WeChat number'); ?>" required>
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
