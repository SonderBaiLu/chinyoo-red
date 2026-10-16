<?php
/**
 * 商品一键导入：上传一个（或多个）ZIP（每个子文件夹 = 一个产品型号），
 * 按命名规则自动建商品并映射图片；详情的英文/中文长图会分别归入 en / cn。
 *
 * 命名约定（文件夹名 = 产品型号，如 XT266C）：
 *   白底图/底图.*            → 主图（缩略图）+ 画廊第 1 张
 *   展示图N / 轮播图N / 场景图N.* → 画廊图（按编号排序）
 *   详细参数图 / 详细信息图 / 详情图 …（可带“英文/中文”）→ 详情长图（en / cn）
 *   未识别命名的图片          → 一律并入画廊，保证图片不丢
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------- 工具：把本地文件放进媒体库，返回 attachment ID ----------
   不走 wp_handle_upload（它只接受 HTTP $_FILES 直传）；
   直接把文件拷进 uploads 目录再插附件。 */
function ew_import_attach_image( $local_path, $file_name, $post_id = 0 ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if ( ! is_file( $local_path ) ) { return 0; }

    $upload_dir = wp_upload_dir();
    if ( ! empty( $upload_dir['error'] ) ) { return 0; }

    $ext  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
    if ( '' === $ext ) { $ext = 'jpg'; }
    $safe = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
    if ( '' === $safe ) { $safe = 'image'; }
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

/* ---------- 工具：按文件名归类 ----------
   返回：'main' | array('gallery', n) | array('detail', 'en'|'cn'|'both') | null（null→画廊兜底）。 */
function ew_import_classify( $file_name ) {
    $base = pathinfo( $file_name, PATHINFO_FILENAME );
    $base = preg_replace( '/\s+/u', ' ', trim( (string) $base ) );
    if ( '' === $base ) { return null; }

    // 主图：白底图 / 底图 / 主图
    if ( false !== mb_strpos( $base, '白底图' ) || false !== mb_strpos( $base, '主图' ) ) { return 'main'; }
    if ( preg_match( '/底图/u', $base ) ) { return 'main'; }

    // 详情长图（可带语言标识）
    $is_detail = false;
    foreach ( array( '详细参数图', '详细参数', '详细信息图', '详细信息', '详细页', '详细数据', '详情图', '详情', '参数页', '参数图', '信息图' ) as $kw ) {
        if ( false !== mb_strpos( $base, $kw ) ) { $is_detail = true; break; }
    }
    if ( ! $is_detail && preg_match( '/product[_\-\s]?detail/i', $base ) ) { $is_detail = true; }

    if ( $is_detail ) {
        $lang = 'both';
        if ( preg_match( '/(英文|english|eng)|(_|-)en($|[^A-Za-z])|en版/is', $base ) ) {
            $lang = 'en';
        } elseif ( preg_match( '/(中文|中国|简体|chinese)|(_|-)cn($|[^A-Za-z])|(_|-)zh($|[^A-Za-z])|中文版/is', $base ) ) {
            $lang = 'cn';
        }
        return array( 'detail', $lang );
    }

    // 画廊
    if ( preg_match( '/(展示图|轮播图|场景图|展示)\s*(\d+)?/u', $base, $m ) ) {
        $n = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : 0;
        return array( 'gallery', $n );
    }

    return null; // 未识别 → 画廊兜底
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

/* 递归收集一个型号文件夹里全部图片（含子目录，跳过 __MACOSX 等） */
function ew_import_collect_images( $dir ) {
    $out = array();
    $items = @scandir( $dir );
    if ( ! is_array( $items ) ) { return $out; }
    sort( $items, SORT_NATURAL | SORT_FLAG_CASE );
    foreach ( $items as $item ) {
        if ( '.' === $item || '..' === $item || '__MACOSX' === $item || '.DS_Store' === $item ) { continue; }
        $full = $dir . '/' . $item;
        if ( is_dir( $full ) ) {
            $out = array_merge( $out, ew_import_collect_images( $full ) );
        } elseif ( is_file( $full ) && ew_import_is_image( $item ) ) {
            $out[] = $full;
        }
    }
    return $out;
}

/* 折叠 ZIP 的单一顶层包装目录：把「修改后的/型号A、型号B…」里的「修改后的」跳过，
   直接以真正含图片的层级为根。返回 (根目录, 是否为包装层级)。 */
function ew_import_resolve_root( $dir ) {
    for ( $i = 0; $i < 6; $i++ ) {
        $entries = array();
        foreach ( (array) @scandir( $dir ) as $e ) {
            if ( '.' === $e || '..' === $e || '__MACOSX' === $e ) { continue; }
            $entries[] = $e;
        }
        // 只有一个子目录，且该子目录里没有直接图片（说明它是包装层）→ 下钻
        if ( 1 === count( $entries ) && is_dir( $dir . '/' . $entries[0] ) ) {
            $child = $dir . '/' . $entries[0];
            $has_img = false;
            foreach ( (array) @scandir( $child ) as $f ) {
                if ( ew_import_is_image( $f ) ) { $has_img = true; break; }
            }
            if ( ! $has_img ) { $dir = $child; continue; }
        }
        break;
    }
    return $dir;
}

/* ---------- 工具：处理一个型号文件夹，返回结果行 ---------- */
function ew_import_one_folder( $dir, $folder_name, $options ) {
    $files = ew_import_collect_images( $dir );
    $main       = array();
    $gallery    = array();
    $detail_en  = array();
    $detail_cn  = array();

    foreach ( $files as $full ) {
        $cls = ew_import_classify( basename( $full ) );
        if ( 'main' === $cls ) {
            $main[] = $full;
        } elseif ( is_array( $cls ) && 'detail' === $cls[0] ) {
            if ( 'en' === $cls[1] || 'both' === $cls[1] ) { $detail_en[] = $full; }
            if ( 'cn' === $cls[1] || 'both' === $cls[1] ) { $detail_cn[] = $full; }
        } elseif ( is_array( $cls ) && 'gallery' === $cls[0] ) {
            $gallery[ $cls[1] ][] = $full;
        } else {
            $gallery[ 999 ][] = $full; // 未识别 → 画廊兜底
        }
    }

    // 排序：编号升序
    ksort( $gallery );

    if ( empty( $main ) && empty( $gallery ) && empty( $detail_en ) && empty( $detail_cn ) ) {
        return array( 'name' => $folder_name, 'ok' => false, 'msg' => '文件夹里没有可用图片', 'url' => '' );
    }

    $gallery_total = 0;
    foreach ( $gallery as $group ) { $gallery_total += count( $group ); }
    $summary = sprintf( '主图 %d · 画廊 %d · 详情 en %d / cn %d', count( $main ), $gallery_total, count( $detail_en ), count( $detail_cn ) );

    // 试运行：只统计不落库
    if ( ! empty( $options['dry'] ) ) {
        return array( 'name' => $folder_name, 'ok' => true, 'msg' => $summary, 'url' => '' );
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
    update_post_meta( $prod_id, 'product_model', $folder_name );
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
        set_post_thumbnail( $prod_id, $main_id );
    } elseif ( ! empty( $gallery_ids ) ) {
        set_post_thumbnail( $prod_id, $gallery_ids[0] );
    }
    if ( ! empty( $gallery_ids ) ) {
        update_post_meta( $prod_id, '_model_gallery', wp_json_encode( $gallery_ids ) );
    }

    // 详情长图：按语言分别入库（en / cn）
    $detail_map = array();
    $de_ids = array();
    foreach ( $detail_en as $d ) {
        $aid = ew_import_attach_image( $d, $folder_name . '-详细参数图-en', $prod_id );
        if ( $aid ) { $de_ids[] = $aid; }
    }
    $dc_ids = array();
    foreach ( $detail_cn as $d ) {
        $aid = ew_import_attach_image( $d, $folder_name . '-详细参数图-cn', $prod_id );
        if ( $aid ) { $dc_ids[] = $aid; }
    }
    if ( ! empty( $de_ids ) ) { $detail_map['en'] = $de_ids; }
    if ( ! empty( $dc_ids ) ) { $detail_map['cn'] = $dc_ids; }
    if ( ! empty( $detail_map ) ) {
        update_post_meta( $prod_id, '_model_detail_images', wp_json_encode( $detail_map, JSON_UNESCAPED_UNICODE ) );
    }

    // 分类 / 首页推荐
    if ( ! empty( $options['cat_id'] ) ) {
        wp_set_object_terms( $prod_id, array( (int) $options['cat_id'] ), 'product_cat' );
    }
    if ( ! empty( $options['hot'] ) ) {
        update_post_meta( $prod_id, 'product_hot', '1' );
    }

    return array(
        'name' => $folder_name,
        'ok'   => true,
        'msg'  => $summary,
        'url'  => get_permalink( $prod_id ),
        'edit' => get_edit_post_link( $prod_id ),
    );
}

/* ---------- 单商品拖拽导入：把 $_FILES 的一组图按命名归类并上传 ---------- */
function ew_import_files_for_product( $id, $files ) {
    $result = array( 'main' => 0, 'gallery' => array(), 'detail_en' => array(), 'detail_cn' => array(), 'count' => 0 );
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
        } elseif ( is_array( $cls ) && 'detail' === $cls[0] ) {
            if ( 'en' === $cls[1] || 'both' === $cls[1] ) { $result['detail_en'][] = $aid; }
            if ( 'cn' === $cls[1] || 'both' === $cls[1] ) { $result['detail_cn'][] = $aid; }
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

    @set_time_limit( 0 );
    @ini_set( 'memory_limit', '512M' );
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $results = array();
    $error   = '';

    // 收集上传的 ZIP（支持多选：ew_import_zip[] ；兼容单文件 ew_import_zip）
    $zips = array();
    if ( isset( $_FILES['ew_import_zip'] ) ) {
        $f = $_FILES['ew_import_zip'];
        if ( is_array( $f['name'] ) ) {
            foreach ( $f['name'] as $i => $n ) {
                $zips[] = array(
                    'name' => $n,
                    'tmp'  => isset( $f['tmp_name'][ $i ] ) ? $f['tmp_name'][ $i ] : '',
                    'err'  => isset( $f['error'][ $i ] ) ? (int) $f['error'][ $i ] : UPLOAD_ERR_NO_FILE,
                );
            }
        } else {
            $zips[] = array( 'name' => $f['name'], 'tmp' => $f['tmp_name'], 'err' => (int) $f['error'] );
        }
    }

    if ( empty( $zips ) ) {
        $error = '请选择要导入的 ZIP 文件。';
    } elseif ( ! class_exists( 'ZipArchive' ) ) {
        $error = '服务器缺少 ZipArchive 扩展，无法解压。';
    } else {
        $cat_id = isset( $_POST['ew_cat_id'] ) ? absint( $_POST['ew_cat_id'] ) : 0;
        $status = ( isset( $_POST['ew_status'] ) && 'draft' === $_POST['ew_status'] ) ? 'draft' : 'publish';
        $options = array(
            'cat_id' => $cat_id,
            'status' => $status,
            'hot'    => ! empty( $_POST['ew_hot'] ),
            'dry'    => ! empty( $_POST['ew_dry'] ),
        );

        foreach ( $zips as $z ) {
            if ( UPLOAD_ERR_OK !== $z['err'] ) {
                $results[] = array( 'name' => $z['name'], 'ok' => false, 'msg' => ew_import_upload_error_text( $z['err'] ), 'url' => '' );
                continue;
            }
            if ( '' === $z['tmp'] || ! is_file( $z['tmp'] ) ) {
                continue;
            }
            $zip = new ZipArchive();
            if ( true !== $zip->open( $z['tmp'] ) ) {
                $results[] = array( 'name' => $z['name'], 'ok' => false, 'msg' => 'ZIP 无法打开', 'url' => '' );
                continue;
            }
            $extract_dir = trailingslashit( sys_get_temp_dir() ) . 'ew_import_' . wp_unique_id();
            if ( ! @mkdir( $extract_dir, 0755, true ) ) {
                $results[] = array( 'name' => $z['name'], 'ok' => false, 'msg' => '无法创建临时目录', 'url' => '' );
                $zip->close();
                continue;
            }
            if ( ! $zip->extractTo( $extract_dir ) ) {
                $results[] = array( 'name' => $z['name'], 'ok' => false, 'msg' => '解压失败', 'url' => '' );
                $zip->close();
                ew_import_rmdir( $extract_dir );
                continue;
            }
            $zip->close();

            $root = ew_import_resolve_root( $extract_dir );

            // 顶层子文件夹 = 型号
            $count = 0;
            foreach ( (array) @scandir( $root ) as $entry ) {
                if ( '.' === $entry || '..' === $entry || '__MACOSX' === $entry ) { continue; }
                $sub = $root . '/' . $entry;
                if ( ! is_dir( $sub ) ) { continue; }
                $count++;
                $results[] = ew_import_one_folder( $sub, $entry, $options );
            }
            // 若顶层直接是图片（无子文件夹），按 ZIP 名当一个型号
            if ( 0 === $count ) {
                $results[] = ew_import_one_folder( $root, pathinfo( $z['name'], PATHINFO_FILENAME ), $options );
            }

            ew_import_rmdir( $extract_dir );
        }
    }

    // 结果存入 transient（避免把超长 payload 塞进跳转 URL 触发 414）
    $key = 'ew_import_' . wp_generate_password( 12, false );
    set_transient( $key, array( 'error' => $error, 'results' => $results, 'dry' => ! empty( $_POST['ew_dry'] ) ), HOUR_IN_SECONDS );
    wp_safe_redirect( add_query_arg( array( 'imported' => '1', 'key' => $key ), admin_url( 'edit.php?post_type=product&page=ew-product-import' ) ) );
    exit;
}
add_action( 'admin_post_ew_product_import', 'ew_handle_product_import' );

function ew_import_upload_error_text( $code ) {
    switch ( $code ) {
        case UPLOAD_ERR_INI_SIZE:   return '超过服务器 upload_max_filesize 上限，请拆分 ZIP 后重试。';
        case UPLOAD_ERR_FORM_SIZE:  return '超过表单大小上限。';
        case UPLOAD_ERR_PARTIAL:    return '文件只上传了一部分，请重试。';
        case UPLOAD_ERR_NO_FILE:    return '没有选择文件。';
        case UPLOAD_ERR_NO_TMP_DIR: return '服务器缺少临时目录。';
        case UPLOAD_ERR_CANT_WRITE: return '服务器写入磁盘失败。';
        default:                    return '上传失败（错误码 ' . (int) $code . '）。';
    }
}

/* ---------- 后台菜单 + 页面 ---------- */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=product', '商品一键导入', '商品一键导入', 'edit_products', 'ew-product-import', 'ew_render_product_import' );
}, 30 );

function ew_render_product_import() {
    if ( ! current_user_can( 'edit_products' ) ) { wp_die( '您没有权限。' ); }
    $cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
    $notice = '';
    $rows = array();
    if ( isset( $_GET['imported'] ) && ! empty( $_GET['key'] ) ) {
        $decoded = get_transient( sanitize_text_field( $_GET['key'] ) );
        delete_transient( sanitize_text_field( $_GET['key'] ) );
        if ( is_array( $decoded ) ) {
            $error = isset( $decoded['error'] ) ? $decoded['error'] : '';
            $rows  = isset( $decoded['results'] ) ? $decoded['results'] : array();
            $dry   = ! empty( $decoded['dry'] );
            $ok = count( array_filter( $rows, function ( $r ) { return ! empty( $r['ok'] ); } ) );
            $fail = count( $rows ) - $ok;
            if ( $error ) {
                $notice = '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
            } elseif ( $rows ) {
                $label = $dry ? '试运行完成（未建商品）：可识别' : '导入完成：成功';
                $notice = '<div class="notice ' . ( $dry ? 'notice-info' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( $label )
                    . ' <strong>' . esc_html( (string) $ok ) . '</strong> 个'
                    . ( $fail ? '，失败 <strong style="color:#d63638">' . esc_html( (string) $fail ) . '</strong> 个（见下表）' : '' ) . '。</p></div>';
            }
        }
    }
    ?>
    <div class="wrap ew-product-import">
        <h1>商品一键导入</h1>
        <p>上传 ZIP 压缩包，里面<strong>每个子文件夹 = 一个产品型号</strong>，文件夹内按命名放图，系统自动建商品并映射图片。图片<strong>可放在子文件夹里</strong>（会递归识别）；直接压缩「型号文件夹的上一级目录」也可以（会自动跳过外层包装目录）。</p>
        <?php echo $notice; ?>

        <table class="widefat striped" style="max-width:960px;margin-bottom:24px">
            <thead><tr><th style="width:62%">文件命名约定（大小写/空格不敏感）</th><th>对应位置</th></tr></thead>
            <tbody>
                <tr><td>文件夹名（如 <code>XT266C</code>）</td><td>产品型号 / 商品名称 / SKU</td></tr>
                <tr><td><code>白底图</code> / <code>底图</code> / <code>主图</code>（如 <code>A830L_底图_01</code>）</td><td>主图（缩略图）+ 画廊第 1 张</td></tr>
                <tr><td><code>展示图N</code> / <code>轮播图N</code> / <code>场景图N</code>（如 <code>使用场景图</code>）</td><td>画廊图（按编号排序）</td></tr>
                <tr><td><code>详细参数图</code> / <code>详细信息图</code> / <code>详情图</code> …（如 <code>详细参数图 - 英文版.png</code>）</td><td>“Product Details” 详情长图</td></tr>
            </tbody>
        </table>
        <p class="description" style="max-width:960px;margin-top:-12px">
            <strong>多语言：</strong>详情长图会按文件名里的语言字样分开入库——
            含「<code>英文 / English / _en</code>」的进 <strong>英文页</strong>，含「<code>中文 / _cn</code>」的进 <strong>中文页</strong>；
            没有语言字样的详情图会同时给英文和中文页。其它语言（es/ru/ar）前台自动回退英文详情。
        </p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width:680px">
            <input type="hidden" name="action" value="ew_product_import">
            <?php wp_nonce_field( 'ew_product_import', 'ew_import_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">ZIP 文件</th>
                    <td><input type="file" name="ew_import_zip[]" accept=".zip" multiple required><p class="description">可一次选择多个 ZIP（例如把大素材拆成几个包，避开单文件 300M 上限）。</p></td>
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
                <tr>
                    <th scope="row">试运行</th>
                    <td><label><input type="checkbox" name="ew_dry" value="1"> 只统计不建商品（先看识别结果再正式导入）</label></td>
                </tr>
            </table>
            <p class="submit"><button type="submit" class="button button-primary button-hero"><span class="dashicons dashicons-upload"></span> 开始导入</button></p>
        </form>

        <?php if ( ! empty( $rows ) ) : ?>
            <h2>本次导入结果</h2>
            <table class="widefat striped" style="max-width:960px">
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
