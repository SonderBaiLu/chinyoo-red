<?php
/**
 * 商品一键导入：上传一个 ZIP（每个子文件夹 = 一个产品型号），
 * 按命名规则自动建商品并映射图片。
 *
 * 命名约定（文件夹名 = 产品型号，如 XT266C）：
 *   白底图.*     → 主图（缩略图）+ 画廊第 1 张
 *   展示图1..N.* → 画廊图（按编号排序）
 *   详细参数图.* → “Product Details” 长图（en + cn）
 * 识别不出中文名的文件，一律并入画廊，保证图片不丢。
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------- 工具：把本地文件放进媒体库，返回 attachment ID ----------
   不走 wp_handle_upload（它只接受 HTTP $_FILES 直传）；
   直接把文件拷进 uploads 目录再插附件。 */
function ew_import_attach_image( $local_path, $file_name, $post_id = 0 ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload_dir = wp_upload_dir();
    if ( ! empty( $upload_dir['error'] ) ) { return 0; }

    $ext  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
    if ( '' === $ext ) { $ext = 'jpg'; }
    $safe = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
    $dest = $upload_dir['path'] . '/' . $safe . '-' . wp_rand( 100000, 999999 ) . '.' . $ext;
    if ( ! @copy( $local_path, $dest ) ) { return 0; }

    $mime   = function_exists( 'mime_content_type' ) ? mime_content_type( $dest ) : 'image/jpeg';
    $att_id = wp_insert_attachment( array(
        'post_title'     => $safe,
        'post_mime_type' => $mime,
        'post_parent'    => $post_id,
        'post_status'    => 'inherit',
    ), $dest, $post_id );
    if ( ! $att_id ) { @unlink( $dest ); return 0; }

    $meta = wp_generate_attachment_metadata( $att_id, $dest );
    wp_update_attachment_metadata( $att_id, $meta );
    return $att_id;
}

/* ---------- 工具：按文件名归类 ---------- */
function ew_import_classify( $file_name ) {
    $base = pathinfo( $file_name, PATHINFO_FILENAME );
    if ( false !== mb_strpos( $base, '白底图' ) ) {
        return 'main';
    }
    if ( false !== mb_strpos( $base, '详细参数图' ) ) {
        return 'detail';
    }
    if ( preg_match( '/展示图\s*(\d+)?/u', $base, $m ) ) {
        return array( 'gallery', isset( $m[1] ) && '' !== $m[1] ? (int) $m[1] : 0 );
    }
    return null;
}

function ew_import_is_image( $file ) {
    $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
    return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp', 'gif' ), true );
}

/* 递归删除目录（此 WP 环境无 core unlink_files） */
function ew_import_rmdir( $dir ) {
    if ( ! is_dir( $dir ) ) { return; }
    foreach ( scandir( $dir ) as $item ) {
        if ( '.' === $item || '..' === $item ) { continue; }
        $p = $dir . '/' . $item;
        is_dir( $p ) ? ew_import_rmdir( $p ) : @unlink( $p );
    }
    @rmdir( $dir );
}


/* ---------- 工具：处理一个型号文件夹，返回结果行 ---------- */
function ew_import_one_folder( $dir, $folder_name, $options ) {
    $files = scandir( $dir );
    $main = array();
    $gallery = array();
    $detail = array();

    foreach ( $files as $f ) {
        if ( '.' === $f || '..' === $f ) { continue; }
        $full = $dir . '/' . $f;
        if ( ! is_file( $full ) || ! ew_import_is_image( $f ) ) { continue; }
        $cls = ew_import_classify( $f );
        if ( 'main' === $cls ) {
            $main[] = $full;
        } elseif ( 'detail' === $cls ) {
            $detail[] = $full;
        } elseif ( is_array( $cls ) ) {
            $gallery[ $cls[1] ][] = $full;
        } else {
            $gallery[ 999 ][] = $full; // 未识别 → 画廊
        }
    }

    // 排序：编号升序
    ksort( $gallery );

    if ( empty( $main ) && empty( $gallery ) && empty( $detail ) ) {
        return array( 'name' => $folder_name, 'ok' => false, 'msg' => '文件夹里没有可用图片', 'url' => '' );
    }

    // 建商品
    $prod_id = wp_insert_post( array(
        'post_type'    => 'product',
        'post_title'   => $folder_name,
        'post_excerpt' => '',
        'post_content' => '',
        'post_status'  => $options['status'],
    ) );
    if ( ! $prod_id ) {
        return array( 'name' => $folder_name, 'ok' => false, 'msg' => '创建商品失败', 'url' => '' );
    }
    update_post_meta( $prod_id, '_product_type', 'simple' );
    update_post_meta( $prod_id, '_stock_status', 'instock' );
    update_post_meta( $prod_id, '_sku', $folder_name );
    update_post_meta( $prod_id, '_model_specs', wp_json_encode( array() ) );

    // 上传主图
    $main_id = 0;
    if ( ! empty( $main[0] ) ) {
        $main_id = ew_import_attach_image( $main[0], $folder_name . '-白底图', $prod_id );
    }
    // 上传画廊
    $gallery_ids = array();
    foreach ( $gallery as $group ) {
        foreach ( $group as $g ) {
            $aid = ew_import_attach_image( $g, $folder_name . '-展示图', $prod_id );
            if ( $aid ) { $gallery_ids[] = $aid; }
        }
    }
    // 主图并入画廊首位
    if ( $main_id ) {
        $gallery_ids = array_merge( array( $main_id ), $gallery_ids );
    }
    if ( $main_id ) {
        set_post_thumbnail( $prod_id, $main_id );
    } elseif ( ! empty( $gallery_ids ) ) {
        set_post_thumbnail( $prod_id, $gallery_ids[0] );
    }
    if ( ! empty( $gallery_ids ) ) {
        update_post_meta( $prod_id, '_model_gallery', wp_json_encode( $gallery_ids ) );
    }
    // 详情长图
    $detail_ids = array();
    foreach ( $detail as $d ) {
        $aid = ew_import_attach_image( $d, $folder_name . '-详细参数图', $prod_id );
        if ( $aid ) { $detail_ids[] = $aid; }
    }
    if ( ! empty( $detail_ids ) ) {
        update_post_meta( $prod_id, '_model_detail_images', wp_json_encode( array( 'en' => $detail_ids, 'cn' => $detail_ids ) ) );
    }

    // 分类 / 首页推荐
    if ( ! empty( $options['cat_id'] ) ) {
        wp_set_object_terms( $prod_id, array( $options['cat_id'] ), 'product_cat' );
    }
    if ( ! empty( $options['hot'] ) ) {
        update_post_meta( $prod_id, 'product_hot', '1' );
    }

    return array(
        'name' => $folder_name,
        'ok'   => true,
        'msg'  => sprintf( '主图 %s · 画廊 %d · 详情 %d', $main_id ? '有' : '无', count( $gallery_ids ), count( $detail_ids ) ),
        'url'  => get_permalink( $prod_id ),
        'edit' => get_edit_post_link( $prod_id ),
    );
}

/* ---------- 单商品拖拽导入：把 $_FILES 的一组图按命名归类并上传，返回 ID ---------- */
function ew_import_files_for_product( $id, $files ) {
    $result = array( 'main' => 0, 'gallery' => array(), 'detail' => array(), 'count' => 0 );
    if ( empty( $files['name'] ) || ! is_array( $files['name'] ) ) {
        return $result;
    }
    foreach ( array_keys( $files['name'] ) as $i ) {
        $tmp  = isset( $files['tmp_name'][ $i ] ) ? $files['tmp_name'][ $i ] : '';
        $name = isset( $files['name'][ $i ] ) ? $files['name'][ $i ] : '';
        if ( '' === $tmp || '' === $name || ! is_file( $tmp ) ) { continue; }
        if ( ! ew_import_is_image( $name ) ) { continue; }
        $aid = ew_import_attach_image( $tmp, $name, $id );
        if ( ! $aid ) { continue; }
        $result['count']++;
        $cls = ew_import_classify( $name );
        if ( 'main' === $cls ) {
            if ( ! $result['main'] ) { $result['main'] = $aid; }
        } elseif ( 'detail' === $cls ) {
            $result['detail'][] = $aid;
        } else {
            $result['gallery'][] = $aid;
        }
    }
    return $result;
}

/* ---------- 主处理：解压 ZIP 并逐文件夹导入 ---------- */
function ew_handle_product_import() {
    if ( ! current_user_can( 'edit_products' ) ) { wp_die( '无权限' ); }
    check_admin_referer( 'ew_product_import', 'ew_import_nonce' );

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $results = array();
    $error = '';

    if ( empty( $_FILES['ew_import_zip']['tmp_name'] ) ) {
        $error = '请选择要导入的 ZIP 文件。';
    } else {
        $zip_path = $_FILES['ew_import_zip']['tmp_name'];
        if ( ! class_exists( 'ZipArchive' ) ) {
            $error = '服务器缺少 ZipArchive 扩展，无法解压。';
        } else {
            $zip = new ZipArchive();
            if ( ZIP_OK !== $zip->open( $zip_path ) ) {
                $error = 'ZIP 文件无法打开，请确认是有效的压缩包。';
            } else {
                $extract_dir = sys_get_temp_dir() . '/ew_import_' . wp_unique_id( '_' );
                if ( ! @mkdir( $extract_dir, 0755, true ) ) {
                    $error = '无法创建临时目录。';
                } else {
                    $zip->extractTo( $extract_dir );
                    $zip->close();

                    $cat_id = isset( $_POST['ew_cat_id'] ) ? absint( $_POST['ew_cat_id'] ) : 0;
                    $status = ! empty( $_POST['ew_status'] ) && 'draft' === $_POST['ew_status'] ? 'draft' : 'publish';
                    $options = array(
                        'cat_id' => $cat_id,
                        'status' => $status,
                        'hot'    => ! empty( $_POST['ew_hot'] ),
                    );

                    // 顶层子文件夹 = 型号
                    $count = 0;
                    foreach ( scandir( $extract_dir ) as $entry ) {
                        if ( '.' === $entry || '..' === $entry ) { continue; }
                        $sub = $extract_dir . '/' . $entry;
                        if ( ! is_dir( $sub ) ) { continue; }
                        $count++;
                        $results[] = ew_import_one_folder( $sub, $entry, $options );
                    }
                    // 若 ZIP 顶层直接是图片（无子文件夹），按 ZIP 名当一个型号
                    if ( 0 === $count ) {
                        $results[] = ew_import_one_folder( $extract_dir, pathinfo( $_FILES['ew_import_zip']['name'], PATHINFO_FILENAME ), $options );
                    }

                    // 清理临时目录
                    ew_import_rmdir( $extract_dir );
                }
            }
        }
    }

    // 重定向回页面并带结果
    $payload = base64_encode( wp_json_encode( array( 'error' => $error, 'results' => $results ) ) );
    wp_safe_redirect( add_query_arg( array( 'imported' => '1', 'data' => $payload ), admin_url( 'edit.php?post_type=product&page=ew-product-import' ) ) );
    exit;
}
add_action( 'admin_post_ew_product_import', 'ew_handle_product_import' );

/* ---------- 后台菜单 + 页面 ---------- */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=product', '商品一键导入', '商品一键导入', 'edit_products', 'ew-product-import', 'ew_render_product_import' );
}, 30 );

function ew_render_product_import() {
    if ( ! current_user_can( 'edit_products' ) ) { wp_die( '您没有权限。' ); }
    $cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
    $notice = '';
    $rows = array();
    if ( isset( $_GET['imported'] ) && isset( $_GET['data'] ) ) {
        $decoded = json_decode( base64_decode( sanitize_text_field( $_GET['data'] ) ), true );
        if ( is_array( $decoded ) ) {
            $error = $decoded['error'];
            $rows = isset( $decoded['results'] ) ? $decoded['results'] : array();
            $ok = count( array_filter( $rows, function ( $r ) { return ! empty( $r['ok'] ); } ) );
            $notice = $error
                ? '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>'
                : '<div class="notice notice-success is-dismissible"><p>导入完成：成功 <strong>' . esc_html( (string) $ok ) . '</strong> 个产品。</p></div>';
        }
    }
    ?>
    <div class="wrap ew-product-import">
        <h1>商品一键导入</h1>
        <p>上传一个 ZIP 压缩包，里面<strong>每个子文件夹 = 一个产品型号</strong>，文件夹内按命名放图，系统自动建商品并映射图片。</p>
        <?php echo $notice; ?>

        <table class="widefat striped" style="max-width:900px;margin-bottom:24px">
            <thead><tr><th style="width:60%">文件命名约定</th><th>对应位置</th></tr></thead>
            <tbody>
                <tr><td>文件夹名（如 <code>XT266C</code>）</td><td>产品型号 / 商品名称 / SKU</td></tr>
                <tr><td><code>白底图.jpg</code></td><td>主图（缩略图）+ 画廊第 1 张</td></tr>
                <tr><td><code>展示图1.jpg</code> … <code>展示图5.jpg</code></td><td>画廊图（按编号排序）</td></tr>
                <tr><td><code>详细参数图.jpeg</code></td><td>“Product Details” 详情长图</td></tr>
            </tbody>
        </table>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width:640px">
            <input type="hidden" name="action" value="ew_product_import">
            <?php wp_nonce_field( 'ew_product_import', 'ew_import_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">ZIP 文件</th>
                    <td><input type="file" name="ew_import_zip" accept=".zip" required></td>
                </tr>
                <tr>
                    <th scope="row">产品分类</th>
                    <td>
                        <select name="ew_cat_id">
                            <option value="0">不分类</option>
                            <?php foreach ( $cats as $c ) : ?>
                                <option value="<?php echo (int) $c->term_id; ?>"><?php echo esc_html( $c->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">商品状态</th>
                    <td>
                        <label><input type="radio" name="ew_status" value="publish" checked> 发布</label>
                        &nbsp;
                        <label><input type="radio" name="ew_status" value="draft"> 草稿</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">首页推荐</th>
                    <td><label><input type="checkbox" name="ew_hot" value="1"> 设为 Featured（首页 Featured Products 展示）</label></td>
                </tr>
            </table>
            <p class="submit"><button type="submit" class="button button-primary button-hero"><span class="dashicons dashicons-upload"></span> 开始导入</button></p>
        </form>

        <?php if ( ! empty( $rows ) ) : ?>
            <h2>本次导入结果</h2>
            <table class="widefat striped" style="max-width:900px">
                <thead><tr><th>型号</th><th>状态</th><th>图片</th><th>链接</th></tr></thead>
                <tbody>
                <?php foreach ( $rows as $r ) : ?>
                    <tr>
                        <td><?php echo esc_html( $r['name'] ); ?></td>
                        <td><?php echo ! empty( $r['ok'] ) ? '<span style="color:#00a32a">成功</span>' : '<span style="color:#d63638">失败</span> · ' . esc_html( $r['msg'] ); ?></td>
                        <td><?php echo ! empty( $r['ok'] ) ? esc_html( $r['msg'] ) : ''; ?></td>
                        <td>
                            <?php if ( ! empty( $r['url'] ) ) : ?><a href="<?php echo esc_url( $r['url'] ); ?>" target="_blank">查看</a>&nbsp;<?php endif; ?>
                            <?php if ( ! empty( $r['edit'] ) ) : ?><a href="<?php echo esc_url( $r['edit'] ); ?>">编辑</a><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
