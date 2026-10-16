<?php
/**
 * 商品展示管理：单商品图片 / 参数 / 详情的清爽编辑页。
 * 选商品用卡片网格 + 原生搜索（不依赖任何第三方下拉库）。
 * 前台读取的 meta 契约保持不变：_model_gallery / _model_specs / _model_detail_images / product_model。
 */
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

/* 商品卡缩略图：优先缩略图，否则画廊第 1 张 */
function ew_studio_thumb( $id ) {
    $thumb = get_post_thumbnail_id( $id );
    if ( ! $thumb ) { $g = ew_editor_images( get_post_meta( $id, '_model_gallery', true ) ); $thumb = $g ? $g[0] : 0; }
    return $thumb;
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
            <button type="button" class="button button-secondary ew-editor-add-images"><span class="dashicons dashicons-plus-alt2"></span>添加图片</button>
        </div>
        <input class="ew-editor-value" type="hidden" name="ew_model[<?php echo (int) $id; ?>][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( wp_json_encode( $images ) ); ?>">
        <ul class="ew-editor-image-list" data-gallery="<?php echo $is_gallery ? '1' : '0'; ?>"><?php foreach ( $images as $index => $image_id ) { ew_editor_image_card( $image_id, $is_gallery, $index ); } ?></ul>
        <div class="ew-editor-empty"><span class="dashicons dashicons-format-image"></span><strong>还没有图片</strong><small>拖入文件夹或用「添加图片」</small></div>
    </section>
    <?php
}

/* 编辑器：主图画廊 + 技术参数 + 详情长图（英文/中文）。studio 页与 Woo 变体共用 */
function ew_model_editor( $id, $context = 'metabox' ) {
    $details = ew_editor_array( get_post_meta( $id, '_model_detail_images', true ) );
    $gallery = ew_editor_images( get_post_meta( $id, '_model_gallery', true ) );
    $languages = array(
        'en' => array( '英文／默认', '其它语言没有详情图时，前台会用这里的图片。' ),
        'cn' => array( '中文', '中文产品页使用的详情长图。' ),
    );
    ?>
    <div class="ew-model-editor" data-context="<?php echo esc_attr( $context ); ?>">
        <input type="hidden" name="ew_model[<?php echo (int) $id; ?>][nonce]" value="<?php echo esc_attr( wp_create_nonce( 'ew_model_' . $id ) ); ?>">
        <div class="ew-editor-section-heading"><span class="ew-step-number">1</span><div><h2>商品展示图片</h2><p>第一张自动作为主图，按住左上角可拖动排序。</p></div></div>
        <?php ew_editor_image_section( $id, 'gallery', '主图与相册', $gallery, '建议白底图片，第一张显示在列表和详情页顶部。' ); ?>

        <div class="ew-editor-section-heading"><span class="ew-step-number">2</span><div><h2>技术参数</h2><p>一行一个参数；同系列用相同参数名可自动出对比表。</p></div></div>
        <section class="ew-editor-panel ew-editor-specs">
            <input type="hidden" class="ew-editor-spec-value" name="ew_model[<?php echo (int) $id; ?>][specs]" value="<?php echo esc_attr( wp_json_encode( ew_editor_array( get_post_meta( $id, '_model_specs', true ) ) ) ); ?>">
            <div class="ew-spec-table-head"><span></span><span></span><span>参数名称</span><span>参数值</span><span></span></div>
            <div class="ew-editor-spec-rows"></div>
            <div class="ew-spec-empty"><span class="dashicons dashicons-editor-table"></span>还没有技术参数</div>
            <button type="button" class="button button-secondary ew-editor-add-spec"><span class="dashicons dashicons-plus-alt2"></span>添加一行参数</button>
        </section>

        <div class="ew-editor-section-heading"><span class="ew-step-number">3</span><div><h2>详情页长图</h2><p>按顺序显示在产品详情下方。</p></div></div>
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

function ew_apply_model_values( $id, $input ) {
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

/* 带 nonce 校验的保存（编辑 / 变体共用） */
function ew_save_model_values( $id, $input ) {
    if ( ! is_array( $input ) || empty( $input['nonce'] ) || ! is_string( $input['nonce'] ) || ! wp_verify_nonce( $input['nonce'], 'ew_model_' . $id ) ) { return false; }
    return ew_apply_model_values( $id, $input );
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

function ew_product_studio_new_url() {
    return add_query_arg( 'new', '1', ew_product_studio_url() );
}

add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=product', '商品展示管理', '商品展示管理', 'edit_products', 'ew-product-studio', 'ew_render_product_studio' );
}, 30 );

/* 编辑 / 新建共用的表单。$product_id 为 0 表示新建。 */
function ew_studio_form( $product_id, $values, $is_new = false ) {
    $status = $values['status'];
    ?>
    <form class="ew-studio-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="ew_product_studio_save"><input type="hidden" name="product_id" value="<?php echo (int) $product_id; ?>"><?php wp_nonce_field( 'ew_product_studio_save_' . (int) $product_id, 'ew_studio_nonce' ); ?>
        <div class="ew-studio-toolbar"><div><a href="<?php echo esc_url( ew_product_studio_url() ); ?>">← 返回商品列表</a><strong><?php echo esc_html( $values['title'] ?: ( $is_new ? '新建商品' : '(未命名)' ) ); ?></strong><span class="ew-status-badge<?php echo ( $is_new || 'publish' === $status ) ? ' is-publish' : ''; ?>"><?php echo $is_new ? '新商品' : ( 'publish' === $status ? '已发布' : esc_html( get_post_status_object( $status )->label ) ); ?></span></div><div><?php if ( $product_id ) : ?><a class="button" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" target="_blank">查看前台</a><?php endif; ?><button class="button button-primary ew-save-button"><span class="dashicons dashicons-saved"></span>保存</button></div></div>
        <section id="ew-studio-basic" class="ew-editor-panel ew-basic-panel">
            <div class="ew-panel-heading"><div><h3>基本资料</h3><p>保存后立即同步到前台。</p></div></div>
            <div class="ew-basic-grid">
                <label class="ew-field ew-field-wide"><span>商品名称</span><input type="text" name="product_title" value="<?php echo esc_attr( $values['title'] ); ?>" required></label>
                <label class="ew-field"><span>产品型号</span><input type="text" name="product_model" value="<?php echo esc_attr( $values['model'] ); ?>" placeholder="例如：DT830L"></label>
                <label class="ew-field"><span>商品状态</span><select name="product_status"><option value="publish" <?php selected( $status, 'publish' ); ?>>发布</option><option value="draft" <?php selected( $status, 'draft' ); ?>>草稿</option></select></label>
                <label class="ew-field ew-field-wide"><span>商品简介</span><textarea name="product_excerpt" rows="4" placeholder="核心卖点与简短介绍"><?php echo esc_textarea( $values['excerpt'] ); ?></textarea></label>
            </div>
            <?php if ( ! $is_new ) : ?><p class="ew-advanced-link">价格、库存、分类、SEO 等，请用 <a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>">WooCommerce 完整编辑页</a>。</p><?php endif; ?>
        </section>
        <section id="ew-studio-drop" class="ew-editor-panel ew-dropzone-panel">
            <div class="ew-panel-heading"><div><h3>快速导入图片</h3><p>把一个型号文件夹拖进来（白底图 / 展示图1-5 / 详细参数图），按命名自动归类。保存后作为该型号的画廊与详情图。</p></div></div>
            <label class="ew-dropzone" id="ew-dropzone">
                <input type="file" name="ew_studio_files[]" class="ew-dropzone-input" multiple>
                <span class="dashicons dashicons-upload ew-dropzone-icon"></span>
                <span class="ew-dropzone-text">把文件夹拖到这里，或<span class="ew-dropzone-link">点击选择文件</span></span>
            </label>
            <ul class="ew-dropzone-preview"></ul>
            <p class="ew-dropzone-note">已选 <span class="ew-dropzone-count">0</span> 个图片 · 命名：白底图→主图，展示图N→画廊，详细参数图→详情</p>
        </section>
        <?php ew_model_editor( $product_id, 'studio' ); ?>
        <div class="ew-studio-savebar"><span>记得保存</span><button class="button button-primary ew-save-button"><span class="dashicons dashicons-saved"></span>保存</button></div>
    </form>
    <?php
}

function ew_render_product_studio() {
    if ( ! current_user_can( 'edit_products' ) ) { wp_die( '您没有权限编辑商品。' ); }
    $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
    $product = $product_id ? get_post( $product_id ) : null;
    if ( $product && ( 'product' !== $product->post_type || ! current_user_can( 'edit_post', $product_id ) ) ) { $product = null; }
    $is_new = ! $product && isset( $_GET['new'] );
    $products = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ) );
    ?>
    <div class="wrap ew-product-studio">
        <div class="ew-studio-hero"><div><span class="ew-studio-kicker">PRODUCT STUDIO</span><h1><?php echo $is_new ? '新建商品' : '商品展示管理'; ?></h1><p>在清爽的页面里维护前台商品的图片、参数和详情。</p></div><a class="button" href="<?php echo esc_url( ew_product_studio_new_url() ); ?>"><span class="dashicons dashicons-plus-alt2"></span>新增商品</a></div>
        <?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p><strong>已保存。</strong>前台页面已使用最新内容。</p></div><?php endif; ?>
        <?php if ( $is_new ) : ?>
            <?php ew_studio_form( 0, array( 'title' => '', 'model' => '', 'status' => 'publish', 'excerpt' => '' ), true ); ?>
        <?php elseif ( ! $product ) : ?>
            <div class="ew-studio-list">
                <div class="ew-studio-listbar">
                    <input type="search" id="ew-product-search" class="ew-product-search" placeholder="搜索型号 / 名称…">
                    <span class="ew-studio-count" id="ew-product-count"></span>
                </div>
                <div class="ew-product-grid" id="ew-product-grid">
                    <?php foreach ( $products as $item ) : $thumb = ew_studio_thumb( $item->ID ); $model = get_post_meta( $item->ID, 'product_model', true ); ?>
                        <a class="ew-product-card" data-name="<?php echo esc_attr( $item->post_title ); ?>" data-model="<?php echo esc_attr( $model ); ?>" href="<?php echo esc_url( ew_product_studio_url( $item->ID ) ); ?>">
                            <span class="ew-product-card__thumb"><?php echo $thumb ? wp_get_attachment_image( $thumb, 'thumbnail', false, array( 'loading' => 'lazy' ) ) : '<span class="dashicons dashicons-format-image ew-no-thumb"></span>'; ?></span>
                            <span class="ew-product-card__body"><strong><?php echo esc_html( $item->post_title ?: '(未命名)' ); ?></strong><small><?php echo esc_html( $model ? $model : '#' . (int) $item->ID ); ?></small></span>
                            <span class="ew-status-badge<?php echo 'publish' === $item->post_status ? ' is-publish' : ''; ?>"><?php echo 'publish' === $item->post_status ? '已发布' : esc_html( get_post_status_object( $item->post_status )->label ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <?php ew_studio_form( $product_id, array( 'title' => $product->post_title, 'model' => get_post_meta( $product_id, 'product_model', true ), 'status' => $product->post_status, 'excerpt' => $product->post_excerpt ) ); ?>
        <?php endif; ?>
    </div>
    <?php
}

add_action( 'admin_post_ew_product_studio_save', function () {
    $id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $title = isset( $_POST['product_title'] ) ? sanitize_text_field( wp_unslash( $_POST['product_title'] ) ) : '';
    $excerpt = isset( $_POST['product_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_excerpt'] ) ) : '';
    $status = isset( $_POST['product_status'] ) && in_array( $_POST['product_status'], array( 'publish', 'draft' ), true ) ? $_POST['product_status'] : 'publish';
    $all = isset( $_POST['ew_model'] ) && is_array( $_POST['ew_model'] ) ? wp_unslash( $_POST['ew_model'] ) : array();

    if ( $id ) {
        if ( 'product' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) { wp_die( '您没有权限编辑这个商品。' ); }
        check_admin_referer( 'ew_product_studio_save_' . $id, 'ew_studio_nonce' );
        wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_excerpt' => $excerpt, 'post_status' => $status ) );
        if ( isset( $_POST['product_model'] ) ) { update_post_meta( $id, 'product_model', sanitize_text_field( wp_unslash( $_POST['product_model'] ) ) ); }
        if ( isset( $all[ $id ] ) && is_array( $all[ $id ] ) ) { ew_save_model_values( $id, $all[ $id ] ); }
    } else {
        if ( ! current_user_can( 'edit_products' ) ) { wp_die( '您没有权限新建商品。' ); }
        check_admin_referer( 'ew_product_studio_save_0', 'ew_studio_nonce' );
        $id = wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'draft', 'post_title' => $title !== '' ? $title : '未命名商品', 'post_excerpt' => $excerpt ) );
        if ( is_wp_error( $id ) ) { wp_die( '新建商品失败。' ); }
        if ( isset( $_POST['product_model'] ) ) { update_post_meta( $id, 'product_model', sanitize_text_field( wp_unslash( $_POST['product_model'] ) ) ); }
        $model_input = isset( $all['0'] ) && is_array( $all['0'] ) ? $all['0'] : array();
        if ( ! empty( $model_input['nonce'] ) && wp_verify_nonce( $model_input['nonce'], 'ew_model_0' ) ) { ew_apply_model_values( $id, $model_input ); }
    }

    // 拖拽导入（编辑 / 新建通用）：文件优先，写入该型号的画廊与详情图
    if ( ! empty( $_FILES['ew_studio_files']['name'] ) && is_array( $_FILES['ew_studio_files']['name'] ) ) {
        $imp = ew_import_files_for_product( $id, $_FILES['ew_studio_files'] );
        $gallery = $imp['gallery'];
        if ( $imp['main'] ) { $gallery = array_merge( array( $imp['main'] ), $gallery ); }
        if ( $gallery ) {
            update_post_meta( $id, '_model_gallery', wp_slash( wp_json_encode( $gallery ) ) );
            set_post_thumbnail( $id, $imp['main'] ? $imp['main'] : $gallery[0] );
        }
        if ( $imp['detail'] ) {
            $details = ew_editor_array( get_post_meta( $id, '_model_detail_images', true ) );
            $details['en'] = $imp['detail'];
            $details['cn'] = $imp['detail'];
            update_post_meta( $id, '_model_detail_images', wp_slash( wp_json_encode( $details, JSON_UNESCAPED_UNICODE ) ) );
        }
    }

    if ( $status !== get_post_status( $id ) ) { wp_update_post( array( 'ID' => $id, 'post_status' => $status ) ); }
    wp_safe_redirect( add_query_arg( 'saved', '1', ew_product_studio_url( $id ) ) ); exit;
} );

add_filter( 'post_row_actions', function ( $actions, $post ) {
    if ( 'product' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) { $actions = array_slice( $actions, 0, 1, true ) + array( 'ew_studio' => '<a href="' . esc_url( ew_product_studio_url( $post->ID ) ) . '"><strong>编辑展示资料</strong></a>' ) + array_slice( $actions, 1, null, true ); }
    return $actions;
}, 10, 2 );

add_action( 'add_meta_boxes_product', function () {
    add_meta_box( 'ew-model-editor', '商品展示：图片与参数', function ( $post ) {
        echo '<div class="ew-editor-callout"><strong>推荐使用「商品展示管理」</strong><p>清爽页面维护图片、参数和详情，支持拖拽导入。</p><a class="button button-primary" href="' . esc_url( ew_product_studio_url( $post->ID ) ) . '">打开商品展示管理</a></div><details class="ew-legacy-editor"><summary>仍在当前页面编辑</summary>';
        echo '<p class="show_if_variable"><strong>系列商品：</strong>请在下方「商品数据 → 变体」中维护每个型号。</p><div class="hide_if_variable">';
        ew_model_editor( $post->ID );
        echo '</div></details>';
    }, 'product', 'normal', 'high' );
} );

add_action( 'woocommerce_product_after_variable_attributes', function ( $loop, $data, $variation ) { echo '<div class="form-row form-row-full"><details class="ew-variation-editor"><summary>该型号的图片与参数</summary>'; ew_model_editor( $variation->ID, 'variation' ); echo '</details></div>'; }, 10, 3 );

add_action( 'admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    $is_product_edit = $screen && 'product' === $screen->post_type && 'post' === $screen->base;
    $is_studio = $screen && 'product_page_ew-product-studio' === $screen->id;
    if ( ! $is_product_edit && ! $is_studio ) { return; }
    wp_enqueue_media(); wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'ew-product-editor', EW_THEME_URI . '/assets/js/product-editor.js', array( 'jquery', 'jquery-ui-sortable' ), (string) filemtime( EW_THEME_DIR . '/assets/js/product-editor.js' ), true );
    wp_enqueue_style( 'ew-product-editor', EW_THEME_URI . '/assets/css/product-editor.css', array(), (string) filemtime( EW_THEME_DIR . '/assets/css/product-editor.css' ) );
    if ( $is_studio ) { add_filter( 'admin_body_class', function ( $classes ) { return $classes . ' ew-product-studio-page'; } ); }
} );

foreach ( array( 'field_product_gallery', 'field_product_specs' ) as $field_key ) { add_filter( 'acf/prepare_field/key=' . $field_key, '__return_false' ); }
