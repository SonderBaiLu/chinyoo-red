<?php
/** A focused product display editor for the storefront metadata. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function ew_editor_array( $raw ) {
    if ( is_array( $raw ) ) { return $raw; }
    $value = json_decode( (string) $raw, true );
    return is_array( $value ) ? $value : array();
}

function ew_editor_images( $raw ) {
    $ids = ew_editor_array( $raw );
    if ( ! $ids && is_string( $raw ) && preg_match( '/^\s*\d+(\s*,\s*\d+)*\s*$/', $raw ) ) { $ids = explode( ',', $raw ); }
    return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

function ew_editor_image_card( $image_id, $is_gallery = false, $index = 0 ) {
    $image_id = absint( $image_id );
    if ( ! $image_id || ! wp_attachment_is_image( $image_id ) ) { return; }
    ?>
    <li class="ew-image-card" data-id="<?php echo (int) $image_id; ?>">
        <span class="dashicons dashicons-move ew-drag-handle" aria-hidden="true"></span>
        <?php if ( $is_gallery && 0 === $index ) : ?><span class="ew-primary-badge">主图</span><?php endif; ?>
        <?php echo wp_get_attachment_image( $image_id, 'medium', false, array( 'loading' => 'lazy' ) ); ?>
        <div class="ew-image-card-footer"><span>#<?php echo (int) $image_id; ?></span><button type="button" class="ew-image-remove"><span class="dashicons dashicons-trash"></span>移除</button></div>
    </li>
    <?php
}

function ew_editor_image_section( $id, $key, $title, $images, $description = '' ) {
    $is_gallery = 'gallery' === $key;
    ?>
    <section class="ew-editor-panel ew-editor-images<?php echo $is_gallery ? ' ew-gallery-panel' : ''; ?>">
        <div class="ew-panel-heading">
            <div><h3><?php echo esc_html( $title ); ?></h3><?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?></div>
            <button type="button" class="button button-secondary ew-editor-add-images"><span class="dashicons dashicons-plus-alt2"></span>选择或上传图片</button>
        </div>
        <input class="ew-editor-value" type="hidden" name="ew_model[<?php echo (int) $id; ?>][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( wp_json_encode( $images ) ); ?>">
        <ul class="ew-editor-image-list" data-gallery="<?php echo $is_gallery ? '1' : '0'; ?>"><?php foreach ( $images as $index => $image_id ) { ew_editor_image_card( $image_id, $is_gallery, $index ); } ?></ul>
        <div class="ew-editor-empty"><span class="dashicons dashicons-format-image"></span><strong>还没有图片</strong><small>点击“选择或上传图片”添加</small></div>
    </section>
    <?php
}

function ew_model_editor( $id, $context = 'metabox' ) {
    $details = ew_editor_array( get_post_meta( $id, '_model_detail_images', true ) );
    $gallery = ew_editor_images( get_post_meta( $id, '_model_gallery', true ) );
    $languages = array(
        'en' => array( '英文／默认', '其他语言没有上传详情图时，会自动使用这里的图片。' ),
        'cn' => array( '中文', '中文产品页使用的详情长图。' ),
        'es' => array( '西班牙语', '西班牙语产品页使用的详情长图。' ),
        'ru' => array( '俄语', '俄语产品页使用的详情长图。' ),
        'ar' => array( '阿拉伯语', '阿拉伯语产品页使用的详情长图。' ),
    );
    ?>
    <div class="ew-model-editor" data-context="<?php echo esc_attr( $context ); ?>">
        <input type="hidden" name="ew_model[<?php echo (int) $id; ?>][nonce]" value="<?php echo esc_attr( wp_create_nonce( 'ew_model_' . $id ) ); ?>">
        <div id="ew-studio-gallery" class="ew-editor-section-heading"><span class="ew-step-number">1</span><div><h2>商品展示图片</h2><p>第一张自动作为主图。按住图片左上角可直接拖动排序。</p></div></div>
        <?php ew_editor_image_section( $id, 'gallery', '主图与相册', $gallery, '建议使用白底正方形图片，第一张会显示在商品列表和详情页顶部。' ); ?>

        <div id="ew-studio-specs" class="ew-editor-section-heading"><span class="ew-step-number">2</span><div><h2>技术参数</h2><p>一行一个参数；同系列商品使用相同参数名称，可自动生成型号对比表。</p></div></div>
        <section class="ew-editor-panel ew-editor-specs">
            <input type="hidden" class="ew-editor-spec-value" name="ew_model[<?php echo (int) $id; ?>][specs]" value="<?php echo esc_attr( wp_json_encode( ew_editor_array( get_post_meta( $id, '_model_specs', true ) ) ) ); ?>">
            <div class="ew-spec-table-head"><span></span><span></span><span>参数名称</span><span>参数值</span><span></span></div>
            <div class="ew-editor-spec-rows"></div>
            <div class="ew-spec-empty"><span class="dashicons dashicons-editor-table"></span>还没有技术参数</div>
            <button type="button" class="button button-secondary ew-editor-add-spec"><span class="dashicons dashicons-plus-alt2"></span>添加一行参数</button>
        </section>

        <div id="ew-studio-details" class="ew-editor-section-heading"><span class="ew-step-number">3</span><div><h2>详情页长图</h2><p>图片会按当前顺序显示在产品详情下方。通常先维护英文／默认图片即可。</p></div></div>
        <div class="ew-language-tabs" role="tablist">
            <?php foreach ( $languages as $lang => $language ) : ?><button type="button" class="ew-language-tab<?php echo 'en' === $lang ? ' is-active' : ''; ?>" data-target="<?php echo esc_attr( $lang ); ?>"><?php echo esc_html( $language[0] ); ?></button><?php endforeach; ?>
        </div>
        <div class="ew-language-panels">
            <?php foreach ( $languages as $lang => $language ) : ?>
                <div class="ew-language-panel<?php echo 'en' === $lang ? ' is-active' : ''; ?>" data-language="<?php echo esc_attr( $lang ); ?>"><?php ew_editor_image_section( $id, 'detail_' . $lang, $language[0] . '详情图', isset( $details[ $lang ] ) ? ew_editor_images( $details[ $lang ] ) : array(), $language[1] ); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function ew_save_model_values( $id, $input ) {
    if ( ! is_array( $input ) || empty( $input['nonce'] ) || ! is_string( $input['nonce'] ) || ! wp_verify_nonce( $input['nonce'], 'ew_model_' . $id ) ) { return false; }
    $details = ew_editor_array( get_post_meta( $id, '_model_detail_images', true ) );
    $details_changed = false;
    foreach ( array( 'gallery', 'detail_en', 'detail_cn', 'detail_es', 'detail_ru', 'detail_ar', 'specs' ) as $key ) {
        if ( ! isset( $input[ $key ] ) || ! is_string( $input[ $key ] ) ) { continue; }
        $values = json_decode( $input[ $key ], true );
        if ( ! is_array( $values ) || '[' !== substr( ltrim( $input[ $key ] ), 0, 1 ) ) { continue; }
        if ( 'specs' === $key ) {
            $clean = array();
            foreach ( $values as $row ) {
                if ( ! is_array( $row ) || ! isset( $row['name'], $row['value'] ) || ! is_scalar( $row['name'] ) || ! is_scalar( $row['value'] ) ) { continue; }
                $name = sanitize_text_field( (string) $row['name'] );
                if ( '' !== $name ) { $clean[] = array( 'name' => $name, 'value' => sanitize_text_field( (string) $row['value'] ) ); }
            }
            update_post_meta( $id, '_model_specs', wp_slash( wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) ) );
        } else {
            $images = array();
            foreach ( $values as $value ) {
                if ( ! is_scalar( $value ) ) { continue; }
                $image_id = absint( $value );
                if ( $image_id && wp_attachment_is_image( $image_id ) ) { $images[] = $image_id; }
            }
            $images = array_values( array_unique( $images ) );
            if ( 'gallery' === $key ) { update_post_meta( $id, '_model_gallery', wp_slash( wp_json_encode( $images ) ) ); }
            else { $details[ substr( $key, 7 ) ] = $images; $details_changed = true; }
        }
    }
    if ( $details_changed ) { update_post_meta( $id, '_model_detail_images', wp_slash( wp_json_encode( $details, JSON_UNESCAPED_UNICODE ) ) ); }
    return true;
}

function ew_save_model_editor( $id ) {
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || ! current_user_can( 'edit_post', $id ) ) { return; }
    if ( ! in_array( get_post_type( $id ), array( 'product', 'product_variation' ), true ) ) { return; }
    $all = isset( $_POST['ew_model'] ) && is_array( $_POST['ew_model'] ) ? wp_unslash( $_POST['ew_model'] ) : array();
    ew_save_model_values( $id, isset( $all[ $id ] ) && is_array( $all[ $id ] ) ? $all[ $id ] : array() );
}
add_action( 'save_post_product', 'ew_save_model_editor', 20 );
add_action( 'woocommerce_save_product_variation', 'ew_save_model_editor', 20 );

function ew_product_studio_url( $product_id = 0 ) {
    $url = admin_url( 'edit.php?post_type=product&page=ew-product-studio' );
    return $product_id ? add_query_arg( 'product_id', absint( $product_id ), $url ) : $url;
}

add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=product', '商品展示管理', '商品展示管理', 'edit_products', 'ew-product-studio', 'ew_render_product_studio' );
}, 30 );

function ew_render_product_studio() {
    if ( ! current_user_can( 'edit_products' ) ) { wp_die( '您没有权限编辑商品。' ); }
    $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
    $product = $product_id ? get_post( $product_id ) : null;
    if ( $product && ( 'product' !== $product->post_type || ! current_user_can( 'edit_post', $product_id ) ) ) { $product = null; }
    $products = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ) );
    ?>
    <div class="wrap ew-product-studio">
        <div class="ew-studio-hero"><div><span class="ew-studio-kicker">PRODUCT STUDIO</span><h1>商品展示管理</h1><p>在一个清爽的页面里维护前台商品图片、参数和详情内容。</p></div><a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>"><span class="dashicons dashicons-plus-alt2"></span>新增商品</a></div>
        <?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p><strong>商品展示资料已保存。</strong>前台页面已经使用最新内容。</p></div><?php endif; ?>
        <form class="ew-studio-picker" method="get">
            <input type="hidden" name="post_type" value="product"><input type="hidden" name="page" value="ew-product-studio">
            <label for="ew-product-picker">选择要编辑的商品</label>
            <select id="ew-product-picker" class="ew-product-select" name="product_id"><option value="">请选择商品…</option><?php foreach ( $products as $item ) : ?><option value="<?php echo (int) $item->ID; ?>" <?php selected( $product_id, $item->ID ); ?>><?php echo esc_html( $item->post_title ?: '(未命名商品)' ); ?> · #<?php echo (int) $item->ID; ?></option><?php endforeach; ?></select>
            <button class="button button-primary">开始编辑</button>
        </form>
        <?php if ( ! $product ) : ?>
            <div class="ew-studio-welcome"><span class="dashicons dashicons-products"></span><h2>先选择一个商品</h2><p>可以输入商品名称快速搜索，然后点击“开始编辑”。</p><div class="ew-recent-products">
                <?php foreach ( array_slice( $products, 0, 8 ) as $item ) : $thumb_id = get_post_thumbnail_id( $item->ID ); if ( ! $thumb_id ) { $gallery = ew_editor_images( get_post_meta( $item->ID, '_model_gallery', true ) ); $thumb_id = $gallery ? $gallery[0] : 0; } ?><a href="<?php echo esc_url( ew_product_studio_url( $item->ID ) ); ?>"><?php echo $thumb_id ? wp_get_attachment_image( $thumb_id, 'thumbnail' ) : '<span class="dashicons dashicons-format-image ew-no-thumb"></span>'; ?><span><strong><?php echo esc_html( $item->post_title ?: '(未命名商品)' ); ?></strong><small>最近更新 · #<?php echo (int) $item->ID; ?></small></span></a><?php endforeach; ?>
            </div></div>
        <?php else : ?>
            <form class="ew-studio-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="ew_product_studio_save"><input type="hidden" name="product_id" value="<?php echo (int) $product_id; ?>"><?php wp_nonce_field( 'ew_product_studio_save_' . $product_id, 'ew_studio_nonce' ); ?>
                <div class="ew-studio-toolbar"><div><a href="<?php echo esc_url( ew_product_studio_url() ); ?>">← 返回商品选择</a><strong><?php echo esc_html( $product->post_title ?: '(未命名商品)' ); ?></strong><span class="ew-status-badge"><?php echo 'publish' === $product->post_status ? '已发布' : esc_html( get_post_status_object( $product->post_status )->label ); ?></span></div><div><a class="button" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" target="_blank">查看前台</a><button class="button button-primary ew-save-button"><span class="dashicons dashicons-saved"></span>保存全部修改</button></div></div>
                <nav class="ew-studio-nav"><a href="#ew-studio-basic">基本资料</a><a href="#ew-studio-gallery">展示图片</a><a href="#ew-studio-specs">技术参数</a><a href="#ew-studio-details">详情长图</a></nav>
                <section id="ew-studio-basic" class="ew-editor-panel ew-basic-panel">
                    <div class="ew-panel-heading"><div><h3>基本资料</h3><p>这里修改最常用的商品文字，保存后立即同步到前台。</p></div></div>
                    <div class="ew-basic-grid">
                        <label class="ew-field ew-field-wide"><span>商品名称</span><input type="text" name="product_title" value="<?php echo esc_attr( $product->post_title ); ?>" required></label>
                        <label class="ew-field"><span>产品型号</span><input type="text" name="product_model" value="<?php echo esc_attr( get_post_meta( $product_id, 'product_model', true ) ); ?>" placeholder="例如：DT830L"></label>
                        <label class="ew-field"><span>商品状态</span><select name="product_status"><option value="publish" <?php selected( $product->post_status, 'publish' ); ?>>发布</option><option value="draft" <?php selected( $product->post_status, 'draft' ); ?>>草稿</option></select></label>
                        <label class="ew-field ew-field-wide"><span>商品简介</span><textarea name="product_excerpt" rows="5" placeholder="填写产品的核心卖点和简短介绍"><?php echo esc_textarea( $product->post_excerpt ); ?></textarea></label>
                    </div>
                    <p class="ew-advanced-link">价格、库存、分类、SEO 等高级设置，请使用 <a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>">WooCommerce 完整编辑页</a>。</p>
                </section>
                <?php ew_model_editor( $product_id, 'studio' ); ?>
                <div class="ew-studio-savebar"><span>修改完成后记得保存</span><button class="button button-primary ew-save-button"><span class="dashicons dashicons-saved"></span>保存全部修改</button></div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

add_action( 'admin_post_ew_product_studio_save', function () {
    $id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    if ( ! $id || 'product' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) { wp_die( '您没有权限编辑这个商品。' ); }
    check_admin_referer( 'ew_product_studio_save_' . $id, 'ew_studio_nonce' );
    $status = isset( $_POST['product_status'] ) && in_array( $_POST['product_status'], array( 'publish', 'draft' ), true ) ? $_POST['product_status'] : get_post_status( $id );
    wp_update_post( array( 'ID' => $id, 'post_title' => isset( $_POST['product_title'] ) ? sanitize_text_field( wp_unslash( $_POST['product_title'] ) ) : get_the_title( $id ), 'post_excerpt' => isset( $_POST['product_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_excerpt'] ) ) : '', 'post_status' => $status ) );
    if ( isset( $_POST['product_model'] ) ) { update_post_meta( $id, 'product_model', sanitize_text_field( wp_unslash( $_POST['product_model'] ) ) ); }
    $all = isset( $_POST['ew_model'] ) && is_array( $_POST['ew_model'] ) ? wp_unslash( $_POST['ew_model'] ) : array();
    if ( isset( $all[ $id ] ) && is_array( $all[ $id ] ) ) { ew_save_model_values( $id, $all[ $id ] ); }
    wp_safe_redirect( add_query_arg( 'saved', '1', ew_product_studio_url( $id ) ) ); exit;
} );

add_filter( 'post_row_actions', function ( $actions, $post ) {
    if ( 'product' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) { $actions = array_slice( $actions, 0, 1, true ) + array( 'ew_studio' => '<a href="' . esc_url( ew_product_studio_url( $post->ID ) ) . '"><strong>编辑展示资料</strong></a>' ) + array_slice( $actions, 1, null, true ); }
    return $actions;
}, 10, 2 );

add_action( 'add_meta_boxes_product', function () {
    add_meta_box( 'ew-model-editor', '商品展示：图片与参数', function ( $post ) {
        echo '<div class="ew-editor-callout"><strong>推荐使用新版“商品展示管理”</strong><p>新版页面更清晰，支持图片拖动排序和多语言详情图切换。</p><a class="button button-primary" href="' . esc_url( ew_product_studio_url( $post->ID ) ) . '">打开商品展示管理</a></div><details class="ew-legacy-editor"><summary>仍在当前页面编辑</summary>';
        echo '<p class="show_if_variable"><strong>这是系列商品：</strong>请在下方“商品数据 → 变体”中维护每个型号。</p><div class="hide_if_variable">'; ew_model_editor( $post->ID ); echo '</div></details>';
    }, 'product', 'normal', 'high' );
} );

add_action( 'woocommerce_product_after_variable_attributes', function ( $loop, $data, $variation ) { echo '<div class="form-row form-row-full"><details class="ew-variation-editor"><summary>该型号的图片与参数</summary>'; ew_model_editor( $variation->ID, 'variation' ); echo '</details></div>'; }, 10, 3 );

add_action( 'admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    $is_product_edit = $screen && 'product' === $screen->post_type && 'post' === $screen->base;
    $is_studio = $screen && 'product_page_ew-product-studio' === $screen->id;
    if ( ! $is_product_edit && ! $is_studio ) { return; }
    wp_enqueue_media(); wp_enqueue_script( 'jquery-ui-sortable' );
    if ( $is_studio && wp_script_is( 'selectWoo', 'registered' ) ) { wp_enqueue_script( 'selectWoo' ); }
    if ( $is_studio && wp_style_is( 'select2', 'registered' ) ) { wp_enqueue_style( 'select2' ); }
    wp_enqueue_script( 'ew-product-editor', EW_THEME_URI . '/assets/js/product-editor.js', array( 'jquery', 'jquery-ui-sortable' ), (string) filemtime( EW_THEME_DIR . '/assets/js/product-editor.js' ), true );
    wp_enqueue_style( 'ew-product-editor', EW_THEME_URI . '/assets/css/product-editor.css', array(), (string) filemtime( EW_THEME_DIR . '/assets/css/product-editor.css' ) );
    if ( $is_studio ) { add_filter( 'admin_body_class', function ( $classes ) { return $classes . ' ew-product-studio-page'; } ); }
} );

foreach ( array( 'field_product_gallery', 'field_product_specs' ) as $field_key ) { add_filter( 'acf/prepare_field/key=' . $field_key, '__return_false' ); }
