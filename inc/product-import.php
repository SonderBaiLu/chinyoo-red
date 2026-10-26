<?php
/**
 * 商品一键导入：上传一个（或多个）ZIP（每个子文件夹 = 一个产品型号），
 * 按命名规则自动建商品并映射图片；详情的英文/中文长图会分别归入 en / cn。
 *
 * 命名约定（文件夹名 = 产品型号，如 XT266C）：
 *   白底图/底图.*            → 主图（缩略图）+ Product Details 第 1 张
 *   详细参数图 / 详细信息图 / 详情图 …（可带“英文/中文”）→ Product Details 第 2 张（en / cn）
 *   使用场景图N / 场景图N.*   → Product Details 第 3 张，同时进画廊
 *   展示图N / 轮播图N.*       → 画廊图（按编号排序）
 *   未识别命名的图片          → 一律并入画廊，保证图片不丢
 *
 * 补充规则：
 *   ZIP 里套的 ZIP（如素材里的 01.zip）也会当作型号批处理；
 *   同 SKU 已存在的商品默认跳过（不重复创建）；
 *   文件夹名含「暂不处理」的整夹跳过；
 *   新建商品自动挂 Polylang 语言（默认 en）。
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
   返回：'main' | array('gallery', n) | array('scene', n) | array('detail', 'en'|'cn'|'both') | null（null→画廊兜底）。 */
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
        if ( preg_match( '/英文|english|(?<![a-z])en(?![a-z])|en版/i', $base ) ) {
            $lang = 'en';
        } elseif ( preg_match( '/中文|中国|简体|chinese|(?<![a-z])cn(?![a-z])|(?<![a-z])zh(?![a-z])|中文版/i', $base ) ) {
            $lang = 'cn';
        }
        return array( 'detail', $lang );
    }

    // 使用场景图：Product Details 第三张，同时进画廊
    if ( preg_match( '/(使用场景图|使用场景|应用场景|场景图|场景)\s*(\d+)?/u', $base, $m ) ) {
        $n = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : 0;
        return array( 'scene', $n );
    }

    // 画廊（场景图已在上面单独归类）
    if ( preg_match( '/(展示图|轮播图|展示)\s*(\d+)?/u', $base, $m ) ) {
        $n = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : 0;
        return array( 'gallery', $n );
    }

    return null; // 未识别 → 画廊兜底
}

function ew_import_is_image( $file ) {
    $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
    return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp', 'gif' ), true );
}

/* ---------- 工具：AJAX 判断 / JSON 输出 / 字节解析 ---------- */
function ew_import_is_ajax() {
    return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( (string) $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest';
}

function ew_import_json_exit( $payload ) {
    if ( ! headers_sent() ) { header( 'Content-Type: application/json; charset=utf-8' ); header( 'Cache-Control: no-store' ); }
    echo wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );
    exit;
}

function ew_import_parse_bytes( $v ) {
    $v = trim( (string) $v );
    if ( '' === $v ) { return 0; }
    $unit = strtoupper( substr( $v, -1 ) );
    $n    = (int) $v;
    if ( 'G' === $unit ) { return $n * 1073741824; }
    if ( 'M' === $unit ) { return $n * 1048576; }
    if ( 'K' === $unit ) { return $n * 1024; }
    return $n;
}

/* 解压临时目录：服务器 /tmp 可能不可写（open_basedir、权限收紧），
   优先放 uploads（媒体库能写，解压也一定能写），再退回系统临时目录。 */
function ew_import_temp_base() {
    $candidates = array();

    $uploads = wp_upload_dir( null, false );
    if ( empty( $uploads['error'] ) && ! empty( $uploads['basedir'] ) ) {
        $candidates[] = trailingslashit( $uploads['basedir'] ) . 'ew-import-tmp';
    }
    if ( defined( 'WP_TEMP_DIR' ) ) {
        $candidates[] = trailingslashit( WP_TEMP_DIR ) . 'ew-import-tmp';
    }
    $candidates[] = trailingslashit( get_temp_dir() ) . 'ew-import-tmp';
    if ( function_exists( 'sys_get_temp_dir' ) ) {
        $candidates[] = trailingslashit( sys_get_temp_dir() ) . 'ew-import-tmp';
    }

    foreach ( array_unique( $candidates ) as $base ) {
        if ( ! is_dir( $base ) ) { @mkdir( $base, 0755, true ); }
        if ( is_dir( $base ) && is_writable( $base ) ) { return $base; }
    }
    return '';
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
    $scene      = array();
    $detail_en  = array();
    $detail_cn  = array();

    foreach ( $files as $full ) {
        $cls = ew_import_classify( basename( $full ) );
        if ( 'main' === $cls ) {
            $main[] = $full;
        } elseif ( is_array( $cls ) && 'detail' === $cls[0] ) {
            if ( 'en' === $cls[1] || 'both' === $cls[1] ) { $detail_en[] = $full; }
            if ( 'cn' === $cls[1] || 'both' === $cls[1] ) { $detail_cn[] = $full; }
        } elseif ( is_array( $cls ) && 'scene' === $cls[0] ) {
            $scene[ $cls[1] ][] = $full;
        } elseif ( is_array( $cls ) && 'gallery' === $cls[0] ) {
            $gallery[ $cls[1] ][] = $full;
        } else {
            $gallery[ 999 ][] = $full; // 未识别 → 画廊兜底
        }
    }

    // 排序：编号升序
    ksort( $gallery );
    ksort( $scene );

    if ( empty( $main ) && empty( $gallery ) && empty( $scene ) && empty( $detail_en ) && empty( $detail_cn ) ) {
        return array( 'name' => $folder_name, 'ok' => false, 'msg' => '文件夹里没有可用图片', 'url' => '' );
    }

    $gallery_total = 0;
    foreach ( $gallery as $group ) { $gallery_total += count( $group ); }
    $scene_total = 0;
    foreach ( $scene as $group ) { $scene_total += count( $group ); }
    $summary = sprintf( '主图 %d · 画廊 %d · 详情 en %d / cn %d · 场景 %d', count( $main ), $gallery_total, count( $detail_en ), count( $detail_cn ), $scene_total );

    // 试运行：只统计不落库
    if ( ! empty( $options['dry'] ) ) {
        return array( 'name' => $folder_name, 'ok' => true, 'msg' => $summary, 'url' => '' );
    }

    // 重复检查：同 SKU 已存在则跳过，不重复创建
    $found = get_posts( array(
        'post_type'        => 'product',
        'meta_key'         => '_sku',
        'meta_value'       => $folder_name,
        'post_status'      => 'publish,draft',
        'numberposts'      => 1,
        'fields'           => 'ids',
        'suppress_filters' => true,
    ) );
    if ( ! empty( $found ) ) {
        $ex = (int) $found[0];
        return array( 'name' => $folder_name, 'ok' => false, 'skip' => true, 'msg' => '已存在（同 SKU），已跳过', 'url' => get_permalink( $ex ), 'edit' => get_edit_post_link( $ex ) );
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

    // 多语言：wp_insert_post 不会挂 Polylang 语言，前台按语言过滤会看不到，须手动挂默认语言
    if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_default_language' ) ) {
        pll_set_post_language( $prod_id, pll_default_language() );
    }

    // 上传主图（第 1 张 = 缩略图；多张主图全部入库，保证图片不丢）
    $main_ids = array();
    foreach ( $main as $m ) {
        $aid = ew_import_attach_image( $m, $folder_name . '-白底图', $prod_id );
        if ( $aid ) { $main_ids[] = $aid; }
    }
    // 上传画廊
    $gallery_ids = $main_ids;
    foreach ( $gallery as $group ) {
        foreach ( $group as $g ) {
            $aid = ew_import_attach_image( $g, $folder_name . '-展示图', $prod_id );
            if ( $aid ) { $gallery_ids[] = $aid; }
        }
    }
    // 使用场景图：Product Details 第三张，同时保留在画廊
    $scene_ids = array();
    foreach ( $scene as $group ) {
        foreach ( $group as $s ) {
            $aid = ew_import_attach_image( $s, $folder_name . '-使用场景图', $prod_id );
            if ( $aid ) { $scene_ids[] = $aid; $gallery_ids[] = $aid; }
        }
    }
    if ( ! empty( $gallery_ids ) ) {
        set_post_thumbnail( $prod_id, $gallery_ids[0] );
        update_post_meta( $prod_id, '_model_gallery', wp_json_encode( $gallery_ids ) );
    }

    // Product Details 固定三图：白底图 / 详细信息图 / 使用场景图（场景只取第一张，其余留在画廊）
    $detail_extras = array();
    if ( ! empty( $main_ids ) ) { $detail_extras['white'] = array( (int) $main_ids[0] ); }
    if ( ! empty( $scene_ids ) ) { $detail_extras['scene'] = array( (int) $scene_ids[0] ); }
    if ( ! empty( $detail_extras ) ) {
        update_post_meta( $prod_id, '_model_detail_extras', wp_json_encode( $detail_extras ) );
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
    $result = array( 'main' => 0, 'white' => array(), 'scene' => array(), 'gallery' => array(), 'detail_en' => array(), 'detail_cn' => array(), 'count' => 0 );
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
            $result['white'][] = $aid;
            if ( ! $result['main'] ) { $result['main'] = $aid; }
        } elseif ( is_array( $cls ) && 'detail' === $cls[0] ) {
            if ( 'en' === $cls[1] || 'both' === $cls[1] ) { $result['detail_en'][] = $aid; }
            if ( 'cn' === $cls[1] || 'both' === $cls[1] ) { $result['detail_cn'][] = $aid; }
        } elseif ( is_array( $cls ) && 'scene' === $cls[0] ) {
            $result['scene'][] = $aid;
            $result['gallery'][] = $aid;
        } else {
            $result['gallery'][] = $aid;
        }
    }
    return $result;
}

/* 解压一个 ZIP 并按「顶层子文件夹 = 型号」处理；嵌套 ZIP（ZIP 套 ZIP，如素材里的 01.zip）递归处理 */
function ew_import_process_zip( $zip_path, $label, $options, &$results, $depth = 1 ) {
    $zip = new ZipArchive();
    if ( true !== $zip->open( $zip_path ) ) {
        $results[] = array( 'name' => $label, 'ok' => false, 'msg' => 'ZIP 无法打开', 'url' => '' );
        return;
    }
    $temp_base = ew_import_temp_base();
    if ( '' === $temp_base ) {
        $results[] = array( 'name' => $label, 'ok' => false, 'msg' => '无法创建临时目录（uploads 与系统临时目录均不可写）', 'url' => '' );
        $zip->close();
        return;
    }
    $extract_dir = trailingslashit( $temp_base ) . 'zip-' . wp_unique_id();
    if ( ! @mkdir( $extract_dir, 0755, true ) ) {
        $results[] = array( 'name' => $label, 'ok' => false, 'msg' => '无法创建临时目录：' . $temp_base, 'url' => '' );
        $zip->close();
        return;
    }
    if ( ! $zip->extractTo( $extract_dir ) ) {
        $results[] = array( 'name' => $label, 'ok' => false, 'msg' => '解压失败', 'url' => '' );
        $zip->close();
        ew_import_rmdir( $extract_dir );
        return;
    }
    $zip->close();

    $root = ew_import_resolve_root( $extract_dir );

    $count  = 0;
    $nested = array();
    foreach ( (array) @scandir( $root ) as $entry ) {
        if ( '.' === $entry || '..' === $entry || '__MACOSX' === $entry ) { continue; }
        $sub = $root . '/' . $entry;
        if ( is_dir( $sub ) ) {
            if ( preg_match( '/暂不处理/u', $entry ) ) {
                $results[] = array( 'name' => $entry, 'ok' => false, 'skip' => true, 'msg' => '文件夹名标注「暂不处理」，已跳过', 'url' => '' );
                continue;
            }
            $count++;
            $results[] = ew_import_one_folder( $sub, $entry, $options );
        } elseif ( $depth < 4 && is_file( $sub ) && 'zip' === strtolower( pathinfo( $sub, PATHINFO_EXTENSION ) ) ) {
            $nested[] = $sub; // ZIP 里套的 ZIP：当作型号批继续处理，图片不丢
        }
    }
    // 顶层直接是图片（无子文件夹、无嵌套 ZIP）→ 按 ZIP 名当一个型号
    if ( 0 === $count ) {
        $has_top = false;
        foreach ( (array) @scandir( $root ) as $e ) {
            if ( ew_import_is_image( $e ) ) { $has_top = true; break; }
        }
        if ( $has_top || empty( $nested ) ) {
            $results[] = ew_import_one_folder( $root, $label, $options );
        }
    }

    foreach ( $nested as $nz ) {
        ew_import_process_zip( $nz, pathinfo( $nz, PATHINFO_FILENAME ), $options, $results, $depth + 1 );
    }

    ew_import_rmdir( $extract_dir );
}

/* ---------- 主处理：解压 ZIP 并逐文件夹导入 ---------- */
function ew_handle_product_import() {
    if ( ! current_user_can( 'edit_products' ) ) {
        if ( ew_import_is_ajax() ) {
            ew_import_json_exit( array( 'ok' => false, 'error' => '无权限', 'results' => array() ) );
        }
        wp_die( '无权限' );
    }

    @set_time_limit( 0 );
    @ini_set( 'memory_limit', '512M' );
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $ajax    = ew_import_is_ajax();
    $results = array();
    $error   = '';
    $options = array( 'cat_id' => 0, 'status' => 'publish', 'hot' => false, 'dry' => false );

    // 超过 post_max_size 时 PHP 已把 POST/FILES 整体清空（连 nonce 都拿不到）→ 先给出明确提示
    if ( empty( $_FILES['ew_import_zip'] ) ) {
        $max = ew_import_parse_bytes( ini_get( 'post_max_size' ) );
        $len = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
        if ( $max > 0 && $len > $max ) {
            $error = '上传内容超过服务器 post_max_size（' . ini_get( 'post_max_size' ) . '），整个请求已被 PHP 丢弃。请把 ZIP 拆小后重试，或调大 php.ini 的 post_max_size。';
        } else {
            $error = '请选择要导入的 ZIP 文件。';
        }
    } else {
        if ( $ajax ) {
            $nonce = isset( $_REQUEST['ew_import_nonce'] ) ? $_REQUEST['ew_import_nonce'] : '';
            if ( ! wp_verify_nonce( $nonce, 'ew_product_import' ) ) {
                ew_import_json_exit( array( 'ok' => false, 'error' => '安全校验失败，请刷新页面后重试。', 'results' => array() ) );
            }
        } else {
            check_admin_referer( 'ew_product_import', 'ew_import_nonce' );
        }

        $options = array(
            'cat_id' => isset( $_POST['ew_cat_id'] ) ? absint( $_POST['ew_cat_id'] ) : 0,
            'status' => ( isset( $_POST['ew_status'] ) && 'draft' === $_POST['ew_status'] ) ? 'draft' : 'publish',
            'hot'    => ! empty( $_POST['ew_hot'] ),
            'dry'    => ! empty( $_POST['ew_dry'] ),
        );

        // 收集上传的 ZIP（支持多选：ew_import_zip[] ；兼容单文件 ew_import_zip）
        $zips = array();
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

        $real = array();
        foreach ( $zips as $z ) {
            if ( UPLOAD_ERR_OK === $z['err'] && '' !== $z['tmp'] && is_file( $z['tmp'] ) ) { $real[] = $z; }
            else { $results[] = array( 'name' => $z['name'], 'ok' => false, 'msg' => ew_import_upload_error_text( $z['err'] ), 'url' => '' ); }
        }
        if ( empty( $zips ) || empty( $real ) ) {
            if ( '' === $error ) { $error = '没有可选的 ZIP 文件（上传失败或文件不存在）。'; }
        } elseif ( ! class_exists( 'ZipArchive' ) ) {
            $error = '服务器缺少 ZipArchive 扩展，无法解压。';
        } else {
            foreach ( $real as $z ) {
                ew_import_process_zip( $z['tmp'], pathinfo( $z['name'], PATHINFO_FILENAME ), $options, $results );
            }
        }
    }

    if ( $ajax ) {
        ew_import_json_exit( array( 'ok' => ( '' === $error ), 'error' => $error, 'results' => $results, 'dry' => ! empty( $options['dry'] ) ) );
    }

    // 非 AJAX（无 JS）走原流程：结果存入 transient（避免把超长 payload 塞进跳转 URL 触发 414）
    $key = 'ew_import_' . wp_generate_password( 12, false );
    set_transient( $key, array( 'error' => $error, 'results' => $results, 'dry' => ! empty( $options['dry'] ) ), HOUR_IN_SECONDS );
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
    if ( is_wp_error( $cats ) ) { $cats = array(); }
    $notice  = '';
    $rows    = array();
    $expired = false;
    if ( isset( $_GET['imported'] ) && ! empty( $_GET['key'] ) ) {
        $decoded = get_transient( sanitize_text_field( $_GET['key'] ) );
        delete_transient( sanitize_text_field( $_GET['key'] ) );
        if ( is_array( $decoded ) ) {
            $error = isset( $decoded['error'] ) ? $decoded['error'] : '';
            $rows  = isset( $decoded['results'] ) ? $decoded['results'] : array();
            $dry   = ! empty( $decoded['dry'] );
            $ok = $skip = $fail = 0;
            foreach ( $rows as $r ) {
                if ( ! empty( $r['skip'] ) ) { $skip++; }
                elseif ( ! empty( $r['ok'] ) ) { $ok++; }
                else { $fail++; }
            }
            if ( $error ) {
                $notice = '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
            } elseif ( $rows ) {
                $label = $dry ? '试运行完成（未建商品）：可识别' : '导入完成：成功';
                $extra = '';
                if ( $skip ) { $extra .= '，跳过 <strong>' . esc_html( (string) $skip ) . '</strong> 个（重复/标注）'; }
                if ( $fail ) { $extra .= '，失败 <strong style="color:#d63638">' . esc_html( (string) $fail ) . '</strong> 个'; }
                $notice = '<div class="notice ' . ( $dry ? 'notice-info' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( $label )
                    . ' <strong>' . esc_html( (string) $ok ) . '</strong> 个' . $extra . '（见下表）。</p></div>';
            }
        } else {
            $expired = true;
        }
    }
    ?>
    <div class="wrap ew-product-import">
        <h1>商品一键导入</h1>
        <p>上传 ZIP 压缩包，里面<strong>每个子文件夹 = 一个产品型号</strong>，文件夹内按命名放图，系统自动建商品并映射图片。图片<strong>可放在子文件夹里</strong>（会递归识别）；直接压缩「型号文件夹的上一级目录」也可以（会自动跳过外层包装目录）。ZIP 里套的 ZIP（如 01.zip 这类批次包）也能识别；同 SKU 已存在时默认跳过（不重复创建）；文件夹名含「暂不处理」的整夹跳过。</p>
        <?php echo $notice; ?>
        <?php if ( $expired ) { echo '<div class="notice notice-error is-dismissible"><p>导入结果已过期（超过 1 小时），请重新导入。</p></div>'; } ?>

        <table class="widefat striped" style="max-width:960px;margin-bottom:24px">
            <thead><tr><th style="width:62%">文件命名约定（大小写/空格不敏感）</th><th>对应位置</th></tr></thead>
            <tbody>
                <tr><td>文件夹名（如 <code>XT266C</code>）</td><td>产品型号 / 商品名称 / SKU</td></tr>
                <tr><td><code>白底图</code> / <code>底图</code> / <code>主图</code>（如 <code>A830L_底图_01</code>）</td><td>主图（缩略图）+ Product Details 第 1 张</td></tr>
                <tr><td><code>详细参数图</code> / <code>详细信息图</code> / <code>详情图</code> …（如 <code>详细参数图 - 英文版.png</code>）</td><td>Product Details 第 2 张（详情长图）</td></tr>
                <tr><td><code>使用场景图</code> / <code>场景图N</code>（如 <code>使用场景图.jpg</code>）</td><td>Product Details 第 3 张（同时进画廊）</td></tr>
                <tr><td><code>展示图N</code> / <code>轮播图N</code>（如 <code>展示图1.jpg</code>）</td><td>画廊图（按编号排序）</td></tr>
            </tbody>
        </table>
        <p class="description" style="max-width:960px;margin-top:-12px">
            <strong>Product Details 固定三张：</strong>白底图 → 商品详细信息图 → 使用场景图。
            <strong>多语言：</strong>详情长图会按文件名里的语言字样分开入库——
            含「<code>英文 / English / _en</code>」的进 <strong>英文页</strong>，含「<code>中文 / _cn</code>」的进 <strong>中文页</strong>；
            没有语言字样的详情图会同时给英文和中文页。其它语言（es/ru/ar）前台自动回退英文详情。
        </p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width:680px" id="ew-import-form">
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
            <p class="submit"><button type="submit" class="button button-primary button-hero" id="ew-import-btn"><span class="dashicons dashicons-upload"></span> 开始导入</button></p>
        </form>

        <div id="ew-progress" style="display:none;max-width:680px">
            <div style="height:22px;background:#e5e7eb;border-radius:6px;overflow:hidden"><div id="ew-progress-fill" style="height:100%;width:0%;background:#2271ae;transition:width .25s ease"></div></div>
            <p id="ew-progress-text" style="margin-top:8px;color:#505759"></p>
        </div>

        <div id="ew-live-results" style="max-width:960px"></div>

        <script>
        (function () {
            var form = document.getElementById('ew-import-form');
            if (!form) { return; }
            var fileInput = form.querySelector('input[type=file]');
            var btn = document.getElementById('ew-import-btn');
            var bar = document.getElementById('ew-progress');
            var fill = document.getElementById('ew-progress-fill');
            var txt = document.getElementById('ew-progress-text');
            var out = document.getElementById('ew-live-results');
            var t0 = 0;
            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }
            function mb(b) { return (b / 1048576).toFixed(1) + ' MB'; }
            function paint(data) {
                var ok = 0, skip = 0, fail = 0;
                (data.results || []).forEach(function (r) {
                    if (r.skip) { skip++; } else if (r.ok) { ok++; } else { fail++; }
                });
                var html = '';
                if (data.error) {
                    html = '<div class="notice notice-error is-dismissible"><p>' + esc(data.error) + '</p></div>';
                } else if (data.results && data.results.length) {
                    var head = '<p><strong>' + (data.dry ? '试运行完成（未建商品）：可识别 ' : '导入完成：成功 ') + ok + ' 个';
                    if (skip) { head += '，跳过 ' + skip + ' 个（重复/标注）'; }
                    if (fail) { head += '，失败 ' + fail + ' 个'; }
                    html = '<div class="notice ' + (data.dry ? 'notice-info' : 'notice-success') + ' is-dismissible"><p>' + head + '（见下表）。</p></div>';
                }
                if (data.results && data.results.length) {
                    html += '<h2>本次导入结果</h2><table class="widefat striped" style="max-width:960px"><thead><tr><th>型号</th><th>状态</th><th>图片</th><th>链接</th></tr></thead><tbody>';
                    data.results.forEach(function (r) {
                        var st = r.skip ? '<span style="color:#2271ae">跳过</span> · ' + esc(r.msg)
                            : r.ok ? '<span style="color:#00a32a">成功</span>'
                            : '<span style="color:#d63638">失败</span> · ' + esc(r.msg);
                        var img = r.ok && !r.skip ? esc(r.msg) : '';
                        var link = '';
                        if (r.url) { link += '<a href="' + esc(r.url) + '" target="_blank">查看</a>&nbsp;'; }
                        if (r.edit) { link += '<a href="' + esc(r.edit) + '">编辑</a>'; }
                        html += '<tr><td>' + esc(r.name) + '</td><td>' + st + '</td><td>' + img + '</td><td>' + link + '</td></tr>';
                    });
                    html += '</tbody></table>';
                }
                out.innerHTML = html;
            }
            form.addEventListener('submit', function (e) {
                if (!fileInput.files || !fileInput.files.length) { return; }
                e.preventDefault();
                var fd = new FormData(form);
                var xhr = new XMLHttpRequest();
                btn.disabled = true;
                out.innerHTML = '';
                bar.style.display = 'block';
                fill.style.width = '0%';
                txt.textContent = '准备上传…';
                t0 = Date.now();
                xhr.upload.onprogress = function (ev) {
                    if (ev.lengthComputable) {
                        var pct = Math.round(ev.loaded * 100 / ev.total);
                        fill.style.width = pct + '%';
                        txt.textContent = '已上传 ' + pct + '%（' + mb(ev.loaded) + ' / ' + mb(ev.total) + '）…';
                    } else {
                        txt.textContent = '已上传 ' + mb(ev.loaded) + '…';
                    }
                };
                xhr.upload.onload = function () {
                    fill.style.width = '100%';
                    txt.textContent = '上传完成，服务器处理中（解压、建商品、生成缩略图），大 ZIP 可能需要几分钟…';
                };
                xhr.onload = function () {
                    var data = null;
                    try { data = JSON.parse(xhr.responseText); } catch (err) {}
                    var secs = ((Date.now() - t0) / 1000).toFixed(1);
                    if (!data) {
                        txt.textContent = '服务器返回异常（' + secs + ' 秒）：' + String(xhr.responseText || '').slice(0, 200);
                        btn.disabled = false;
                        return;
                    }
                    txt.textContent = '处理完成，用时 ' + secs + ' 秒。';
                    paint(data);
                    btn.disabled = false;
                };
                xhr.onerror = function () { txt.textContent = '上传失败（网络错误）。'; btn.disabled = false; };
                xhr.open('POST', form.getAttribute('action'), true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.send(fd);
            });
        })();
        </script>

        <?php if ( ! empty( $rows ) ) : ?>
            <h2>本次导入结果</h2>
            <table class="widefat striped" style="max-width:960px">
                <thead><tr><th>型号</th><th>状态</th><th>图片</th><th>链接</th></tr></thead>
                <tbody>
                <?php foreach ( $rows as $r ) : ?>
                    <tr>
                        <td><?php echo esc_html( $r['name'] ); ?></td>
                        <td>
                            <?php if ( ! empty( $r['skip'] ) ) : ?>
                                <span style="color:#2271ae">跳过</span> · <?php echo esc_html( $r['msg'] ); ?>
                            <?php elseif ( ! empty( $r['ok'] ) ) : ?>
                                <span style="color:#00a32a">成功</span>
                            <?php else : ?>
                                <span style="color:#d63638">失败</span> · <?php echo esc_html( $r['msg'] ); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo ! empty( $r['ok'] ) && empty( $r['skip'] ) ? esc_html( $r['msg'] ) : ''; ?></td>
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
