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

/* 详情长图只用于前台 full 图和响应式大图，不需要商品卡片及 WooCommerce 缩略图。 */
function ew_import_detail_image_sizes( $sizes ) {
    return array_intersect_key( $sizes, array_flip( array( 'large', '1536x1536', '2048x2048' ) ) );
}

/* 商品图保留 WordPress 和 WooCommerce 常用尺寸；这些主题专用尺寸未被商品页面调用。 */
function ew_import_catalog_image_sizes( $sizes ) {
    unset( $sizes['product-card'], $sizes['product-main'], $sizes['hero-banner'] );
    return $sizes;
}

/* ---------- 工具：把本地文件放进媒体库，返回 attachment ID ----------
   不走 wp_handle_upload（它只接受 HTTP $_FILES 直传）；
   直接把文件拷进 uploads 目录再插附件。 */
function ew_import_attach_image( $local_path, $file_name, $post_id = 0, $role = 'general' ) {
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

    $size_filter = 'detail' === $role ? 'ew_import_detail_image_sizes' : ( 'catalog' === $role ? 'ew_import_catalog_image_sizes' : '' );
    if ( $size_filter ) { add_filter( 'intermediate_image_sizes_advanced', $size_filter, 10, 1 ); }
    try {
        $meta = wp_generate_attachment_metadata( $att_id, $dest );
    } finally {
        if ( $size_filter ) { remove_filter( 'intermediate_image_sizes_advanced', $size_filter, 10 ); }
    }
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
    foreach ( array( '详细参数图', '详细参数', '详细信息图', '详细信息', '详细页', '详细图', '详细数据', '详情图', '详情', '参数页', '参数图', '信息图' ) as $kw ) {
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

/* 参考图（如「详细信息参考图.png」）只是设计样稿，不进商品库 */
function ew_import_is_reference_image( $file ) {
    return false !== mb_strpos( (string) pathinfo( $file, PATHINFO_FILENAME ), '参考图' );
}

function ew_import_is_image( $file ) {
    if ( ew_import_is_reference_image( $file ) ) { return false; }
    $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
    return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp', 'gif' ), true );
}

/* ---------- 工具：按型号判产品分类 ----------
   规则顺序有讲究：电池/插座 → 钳形 → 测电笔 → 万用表 → 温度，避免「钳形表详情里提到 voltage」这类误判。 */
function ew_import_category_rules() {
    return array(
        'battery-meters'           => '/battery\s*(meter|tester)|电池测试|电池容量/i',
        'socket-testers'           => '/socket\s*tester|插座测试|插座检测/i',
        'digital-clamp-meters'     => '/clamp\s*meter|钳形|钳表|电流钳/i',
        'digital-voltage-testers'  => '/voltage\s*(tester|pen|detector)|测电笔|试电笔|电笔|验电笔/i',
        'digital-multimeters'      => '/multimeter|万用表/i',
        'others'                   => '/temperature|thermometer|hygrometer|测温|温度|温湿度/i',
    );
}

/* 叶子分类 → 父级分类：两个都挂，父级分类页也能显示商品 */
function ew_import_category_parents() {
    return array(
        'battery-meters'          => 'others',
        'socket-testers'          => 'others',
        'digital-clamp-meters'    => 'clamp-meters',
        'digital-voltage-testers' => 'voltage-testers',
    );
}

function ew_import_category_from_text( $text ) {
    $text = trim( (string) $text );
    if ( '' === $text ) { return ''; }
    foreach ( ew_import_category_rules() as $slug => $pattern ) {
        if ( preg_match( $pattern, $text ) ) { return $slug; }
    }
    return '';
}

/* info.json / 参数c.json 的 name·model，以及 生成记录.txt 的前几百字 */
function ew_import_read_meta_text( $dir ) {
    $chunks = array();
    foreach ( (array) glob( $dir . '/*.json' ) as $file ) {
        $raw = @file_get_contents( $file );
        if ( false === $raw ) { continue; }
        $raw  = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );
        $data = json_decode( $raw, true );
        if ( is_array( $data ) ) {
            foreach ( array( 'name', 'model', 'title', 'product_name' ) as $key ) {
                if ( isset( $data[ $key ] ) && is_string( $data[ $key ] ) ) { $chunks[] = $data[ $key ]; }
            }
        } else {
            $chunks[] = mb_substr( $raw, 0, 300 );
        }
    }
    foreach ( (array) glob( $dir . '/*.txt' ) as $file ) {
        $raw = @file_get_contents( $file );
        if ( false !== $raw ) { $chunks[] = mb_substr( $raw, 0, 600 ); }
    }
    return implode( ' ', $chunks );
}

/* ---------- 解析 info.json / 参数c.json，产出五个选项卡的文本 ---------- */

/* 把一行「字段名 : 值」拆成 name/value；字段名不含冒号则当纯文本特性返回 */
function ew_import_split_kv( $line ) {
    $line = trim( (string) $line );
    $line = str_replace( array( '：', '﹕', '∶' ), ':', $line ); // 全角冒号归一
    if ( '' === $line ) { return array( '', '', false ); }
    // 特性行（Key Features）常见写法：以 Yes/No 结尾，如 "Low battery indication: yes"
    $is_bool = (bool) preg_match( '/:\s*(yes|no|true|false|with|without)\s*$/i', $line );
    $pos = mb_strpos( $line, ':' );
    if ( false === $pos ) { return array( $line, '', $is_bool ); }
    $name  = trim( mb_substr( $line, 0, $pos ) );
    $value = trim( mb_substr( $line, $pos + 1 ) );
    if ( '' === $name ) { return array( $line, '', $is_bool ); }
    return array( $name, $value, $is_bool );
}

/*
 * 把 info.json 的内容拆成：
 *   specs   → _model_specs         (DCV/ACV/Resistance/Range… 带数值)
 *   features→ _model_features      (Description 之后的纯特性/Yes-No 行)
 *   packaging→ _packaging           (packaging 字段原文)
 * 通用约定：Description 之前的「字段: 数值」是规格，之后是特性说明。
 */
function ew_import_parse_details( $details ) {
    $result = array( 'specs' => array(), 'features' => array() );
    if ( ! is_string( $details ) || '' === trim( $details ) ) { return $result; }

    $lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $details ) ) ) );
    $in_desc = false;
    foreach ( $lines as $line ) {
        if ( '' === $line ) { continue; }
        // "Description" 单独一行 = 分界：之前是规格，之后是特性
        if ( preg_match( '/^description\s*$/iu', $line ) ) { $in_desc = true; continue; }

        list( $name, $value, $is_bool ) = ew_import_split_kv( $line );
        if ( '' === $value && ! $is_bool ) { $value = $name; }

        if ( $in_desc || $is_bool ) {
            $result['features'][] = array( 'name' => $name, 'value' => $value );
        } else {
            $result['specs'][] = array( 'name' => $name, 'value' => $value );
        }
    }
    return $result;
}

/* 读取并解析文件夹里所有 JSON 的文本字段，返回五个选项卡要写入的 meta */
function ew_import_read_product_text( $dir ) {
    $specs     = array();
    $features  = array();
    $packaging = '';

    foreach ( (array) glob( $dir . '/*.json' ) as $file ) {
        $raw = @file_get_contents( $file );
        if ( false === $raw ) { continue; }
        $raw  = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );
        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) ) { continue; }

        // details：规格 + 特性
        if ( isset( $data['details'] ) && is_string( $data['details'] ) ) {
            $parsed = ew_import_parse_details( $data['details'] );
            foreach ( $parsed['specs'] as $s ) { $specs[] = $s; }
            foreach ( $parsed['features'] as $f ) { $features[] = $f; }
        }

        // product_information：电压测试笔等只有纯描述 → 全部当特性
        if ( isset( $data['product_information'] ) && is_string( $data['product_information'] ) ) {
            foreach ( preg_split( '/\r\n|\r|\n/', $data['product_information'] ) as $line ) {
                $line = trim( $line );
                if ( '' === $line ) { continue; }
                $line = preg_replace( '/^\s*\d+[\.\)、]\s*/u', '', $line ); // 去掉 1. 2. 序号
                if ( '' !== trim( $line ) ) { $features[] = array( 'name' => $line, 'value' => $line ); }
            }
        }

        // packaging：原文写入 General 选项卡
        if ( isset( $data['packaging'] ) && is_string( $data['packaging'] ) && '' !== trim( $data['packaging'] ) ) {
            $packaging = trim( $data['packaging'] );
        }
    }

    // 生成记录.txt：图片生成工具会把参数写在「详细参数：」后面的 - 列表里，一并解析
    foreach ( (array) glob( $dir . '/生成记录.txt' ) as $file ) {
        $raw = @file_get_contents( $file );
        if ( false === $raw ) { continue; }
        $raw = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );

        $section = $raw;
        if ( preg_match( '/详细参数\s*[:：]\s*(.*)$/us', $raw, $m ) ) {
            $section = $m[1];
        }
        $pack_lines = array();
        foreach ( preg_split( '/\r\n|\r|\n/', $section ) as $line ) {
            $line = trim( $line );
            // 只取 bullet 行（- / • / · 开头），其它描述行跳过
            if ( ! preg_match( '/^[-•·*]\s*/u', $line ) ) { continue; }
            $line = preg_replace( '/^[-•·*]\s*/u', '', $line );
            if ( '' === $line ) { continue; }

            list( $name, $value, $is_bool ) = ew_import_split_kv( $line );
            if ( '' === $name ) { continue; }
            // 包装类行 → 拼进 packaging；其余 → 特性
            if ( preg_match( '/^(packing|packing quantity|carton size|g\.w\.?|n\.w\.?|gross|net|weight|volume)/iu', $name ) ) {
                if ( '' !== $value ) { $pack_lines[] = $name . ': ' . $value; }
            } elseif ( '' !== $value && $value !== $name ) {
                $features[] = array( 'name' => $name, 'value' => $value );
            } else {
                $features[] = array( 'name' => $name, 'value' => $name );
            }
        }
        if ( ! empty( $pack_lines ) && '' === $packaging ) {
            $packaging = implode( "\n", $pack_lines );
        }
    }

    // 规格去重（同名保留首个）；特性去重（整行去重）
    $spec_seen = array();
    $specs     = array_values( array_filter( $specs, function ( $s ) use ( &$spec_seen ) {
        $key = mb_strtolower( trim( (string) $s['name'] ) );
        if ( '' === $key || isset( $spec_seen[ $key ] ) ) { return false; }
        $spec_seen[ $key ] = true;
        return true;
    } ) );
    $feat_seen = array();
    $features  = array_values( array_filter( $features, function ( $f ) use ( &$feat_seen ) {
        $key = mb_strtolower( trim( (string) $f['value'] ) );
        if ( '' === $key || isset( $feat_seen[ $key ] ) ) { return false; }
        $feat_seen[ $key ] = true;
        return true;
    } ) );

    // Application：素材里没有专门字段 → 留空，前台渲染通用兜底文案
    return array(
        'specs'     => $specs,
        'features'  => $features,
        'packaging' => $packaging,
    );
}

/* 强制指定：文件夹里放 分类.txt（一行分类名 / 中文名 / slug）时优先采用 */
function ew_import_read_category_file( $dir ) {
    foreach ( array( '分类.txt', '分类.md' ) as $name ) {
        $file = $dir . '/' . $name;
        if ( ! is_file( $file ) ) { continue; }
        $raw = @file_get_contents( $file );
        if ( false === $raw ) { continue; }
        $raw  = trim( preg_replace( '/^\xEF\xBB\xBF/', '', $raw ) );
        if ( '' === $raw ) { continue; }
        $line = trim( (string) strtok( $raw, "\r\n" ) );

        $slug = ew_import_resolve_category( $line );
        if ( '' !== $slug ) { return $slug; }
    }
    return '';
}

/* 把「分类名 / 中文名 / slug / 关键词」统一解析成 product_cat slug */
function ew_import_resolve_category( $value ) {
    $value = trim( (string) $value );
    if ( '' === $value ) { return ''; }
    if ( preg_match( '/^(待定|未定|\?|？|-|none)$/ui', $value ) ) { return ''; }

    $term = get_term_by( 'slug', sanitize_title( $value ), 'product_cat' );
    if ( $term && ! is_wp_error( $term ) ) { return $term->slug; }
    $term = get_term_by( 'name', $value, 'product_cat' );
    if ( $term && ! is_wp_error( $term ) ) { return $term->slug; }
    return ew_import_category_from_text( $value );
}

/* 分类对照表（后台保存的文本）：每行「型号 = 分类」，支持 # 注释 */
function ew_import_parse_category_map( $text ) {
    $map = array();
    if ( ! is_string( $text ) || '' === trim( $text ) ) { return $map; }
    foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
        $line = trim( $line );
        if ( '' === $line || '#' === $line[0] ) { continue; }
        if ( ! preg_match( '/^(.+?)\s*[=,，:：|\t]\s*(.+)$/u', $line, $m ) ) { continue; }
        $model = trim( $m[1] );
        if ( '' === $model ) { continue; }
        $slug = ew_import_resolve_category( $m[2] );
        if ( '' === $slug ) { continue; }
        $map[ mb_strtolower( $model ) ] = $slug;
    }
    return $map;
}

function ew_import_category_map() {
    static $map = null;
    if ( null === $map ) {
        $map = ew_import_parse_category_map( (string) get_option( 'ew_import_category_map', '' ) );
    }
    return $map;
}

/* 判定顺序：分类对照表 → 文件夹里的 分类.txt → 文件夹名 → info.json/生成记录 */
function ew_import_detect_category( $dir, $folder_name, $cat_map = array() ) {
    if ( empty( $cat_map ) ) { $cat_map = ew_import_category_map(); }
    $key = mb_strtolower( trim( (string) $folder_name ) );
    if ( '' !== $key && ! empty( $cat_map[ $key ] ) ) { return $cat_map[ $key ]; }

    $slug = ew_import_read_category_file( $dir );
    if ( '' !== $slug ) { return $slug; }
    $slug = ew_import_category_from_text( $folder_name );
    if ( '' !== $slug ) { return $slug; }
    return ew_import_category_from_text( ew_import_read_meta_text( $dir ) );
}

function ew_import_apply_category( $product_id, $slug, $fallback_id = 0 ) {
    $slugs = array();
    if ( '' !== $slug ) {
        $slugs[] = $slug;
        $parents = ew_import_category_parents();
        if ( isset( $parents[ $slug ] ) ) { $slugs[] = $parents[ $slug ]; }
    }

    $ids = array();
    foreach ( $slugs as $s ) {
        $term = get_term_by( 'slug', $s, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) { $ids[] = (int) $term->term_id; }
    }
    if ( empty( $ids ) && $fallback_id ) { $ids[] = (int) $fallback_id; }
    $ids = array_values( array_unique( array_filter( $ids ) ) );

    if ( ! empty( $ids ) ) { wp_set_object_terms( $product_id, $ids, 'product_cat' ); }
    return $ids;
}

function ew_import_category_label( $slug ) {
    if ( '' === $slug ) { return ''; }
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    return ( $term && ! is_wp_error( $term ) ) ? $term->name : $slug;
}

/* 当前语言已发布商品总数（分类侧栏 All Products 计数，不受分类限制） */
function ew_total_product_count() {
    static $count = null;
    if ( null !== $count ) { return $count; }
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
    );
    if ( function_exists( 'pll_current_language' ) && pll_current_language() ) {
        $args['lang'] = pll_current_language();
    }
    $query = new WP_Query( $args );
    $count = (int) $query->found_posts;
    return $count;
}

/* 当前语言的 /shop/ 地址（WooCommerce shop 页没有 Polylang 翻译时用语言前缀兜底） */
function ew_shop_url() {
    $shop_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
    $lang    = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
    if ( $shop_id && $lang && function_exists( 'pll_get_post' ) ) {
        $translated = (int) pll_get_post( $shop_id, $lang );
        if ( $translated ) { return get_permalink( $translated ); }
        if ( function_exists( 'pll_default_language' ) && $lang !== pll_default_language() ) {
            $slug = (string) get_post_field( 'post_name', $shop_id );
            if ( '' !== $slug ) { return home_url( '/' . $lang . '/' . $slug . '/' ); }
        }
    }
    if ( function_exists( 'wc_get_page_permalink' ) ) { return wc_get_page_permalink( 'shop' ); }
    return get_post_type_archive_link( 'product' );
}

/* ---------- 多语言：把默认语言商品同步到其它 Polylang 语言 ---------- */
function ew_product_translation_meta_keys() {
    return array(
        '_product_type',
        '_stock_status',
        'product_model',
        '_model_specs',
        '_model_gallery',
        '_model_detail_images',
        '_model_detail_extras',
        '_model_features',
        '_model_applications',
        '_packaging',
        '_datasheet',
        'product_hot',
    );
}

/* 以默认语言原版为源，创建/更新其它语言版本；标题/简介只在首次创建时复制，之后保留翻译版本自己的内容。 */
function ew_product_sync_translations( $product_id ) {
    if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_set_post_language' ) ) {
        return array();
    }

    $product_id = absint( $product_id );
    if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) { return array(); }

    $default = function_exists( 'pll_default_language' ) ? pll_default_language() : 'en';
    $lang    = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $product_id ) : '';
    if ( $lang && $lang !== $default ) {
        $original = (int) pll_get_post( $product_id, $default );
        if ( ! $original || 'product' !== get_post_type( $original ) ) { return array(); }
        $product_id = $original;
    }
    if ( ! function_exists( 'pll_get_post_language' ) || ! pll_get_post_language( $product_id ) ) {
        pll_set_post_language( $product_id, $default );
    }

    $source = get_post( $product_id );
    if ( ! $source ) { return array(); }

    // 变体商品暂不自动翻译：父级有翻译但变体没有会导致型号下拉为空
    if ( function_exists( 'wc_get_product' ) ) {
        $source_product = wc_get_product( $product_id );
        if ( $source_product && $source_product->is_type( 'variable' ) ) {
            return function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $product_id ) : array();
        }
    }

    $languages = pll_languages_list();
    if ( empty( $languages ) ) { return array(); }

    $translations = function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $product_id ) : array();
    $translations[ $default ] = $product_id;

    $source_terms = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
    $source_terms = is_wp_error( $source_terms ) ? array() : array_map( 'intval', $source_terms );
    $thumb        = (int) get_post_thumbnail_id( $product_id );
    $meta_keys    = ew_product_translation_meta_keys();

    foreach ( $languages as $target ) {
        if ( $target === $default ) { continue; }

        $trans_id = isset( $translations[ $target ] ) ? (int) $translations[ $target ] : 0;
        if ( $trans_id && 'product' !== get_post_type( $trans_id ) ) { $trans_id = 0; }

        if ( ! $trans_id ) {
            // 翻译版本用「原 slug + 语言码」，和站点已有的 1805a-series-ru 命名一致，避免 -2/-3 后缀
            $trans_slug = (string) $source->post_name;
            if ( '' !== $trans_slug ) { $trans_slug .= '-' . sanitize_title( $target ); }
            $trans_id = wp_insert_post( array(
                'post_type'      => 'product',
                'post_status'    => $source->post_status,
                'post_title'     => $source->post_title,
                'post_name'      => $trans_slug,
                'post_content'   => $source->post_content,
                'post_excerpt'   => $source->post_excerpt,
                'post_author'    => $source->post_author,
                'post_date'      => $source->post_date,
                'menu_order'     => $source->menu_order,
                'comment_status' => $source->comment_status,
                'ping_status'    => $source->ping_status,
            ), true );
            if ( is_wp_error( $trans_id ) || ! $trans_id ) { continue; }
            pll_set_post_language( $trans_id, $target );
        } else {
            if ( function_exists( 'pll_get_post_language' ) && pll_get_post_language( $trans_id ) !== $target ) {
                pll_set_post_language( $trans_id, $target );
            }
            if ( $source->post_status !== get_post_status( $trans_id ) ) {
                wp_update_post( array( 'ID' => $trans_id, 'post_status' => $source->post_status ) );
            }
        }

        // 图片、参数、详情、首页推荐等共用数据
        foreach ( $meta_keys as $key ) {
            $value = get_post_meta( $product_id, $key, true );
            if ( '' === $value || null === $value ) { delete_post_meta( $trans_id, $key ); }
            else { update_post_meta( $trans_id, $key, wp_slash( $value ) ); }
        }

        if ( $thumb ) { set_post_thumbnail( $trans_id, $thumb ); }
        else { delete_post_thumbnail( $trans_id ); }

        // 分类：映射到目标语言的同名分类
        $mapped = array();
        foreach ( $source_terms as $term_id ) {
            $t = function_exists( 'pll_get_term' ) ? pll_get_term( $term_id, $target ) : 0;
            $mapped[] = $t ? (int) $t : (int) $term_id;
        }
        wp_set_object_terms( $trans_id, array_values( array_unique( array_filter( $mapped ) ) ), 'product_cat' );

        $translations[ $target ] = $trans_id;
    }

    if ( function_exists( 'pll_save_post_translations' ) ) {
        pll_save_post_translations( $translations );
    }

    return $translations;
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

    // 分类：对照表 → 分类.txt → 文件夹名 → info.json/生成记录
    $cat_slug  = ! empty( $options['cat_auto'] ) ? ew_import_detect_category( $dir, $folder_name ) : '';
    $cat_label = ew_import_category_label( $cat_slug );

    // 试运行：只统计不落库
    if ( ! empty( $options['dry'] ) ) {
        $dry_text = ew_import_read_product_text( $dir );
        $dry_extra = ( ! empty( $dry_text['specs'] ) || ! empty( $dry_text['features'] ) || '' !== $dry_text['packaging'] )
            ? sprintf( ' · 参数 %d 条 / 特性 %d 条 / 包装%s', count( $dry_text['specs'] ), count( $dry_text['features'] ), '' !== $dry_text['packaging'] ? ' 有' : ' 无' )
            : '';
        return array( 'name' => $folder_name, 'ok' => true, 'msg' => $summary . $dry_extra, 'cat' => $cat_label, 'url' => '' );
    }

    // 重复检查：同 SKU 已存在
    // cache_results=false：导入同批次重复型号时，避免复用本请求早先的空查询缓存
    $found = get_posts( array(
        'post_type'        => 'product',
        'meta_key'         => '_sku',
        'meta_value'       => $folder_name,
        'post_status'      => array( 'publish', 'draft' ),
        'numberposts'      => 1,
        'fields'           => 'ids',
        'suppress_filters' => true,
        'cache_results'    => false,
    ) );
    $existing_id = ! empty( $found ) ? (int) $found[0] : 0;
    $replacing   = false;

    if ( $existing_id && empty( $options['replace'] ) ) {
        return array( 'name' => $folder_name, 'ok' => false, 'skip' => true, 'msg' => '已存在（同 SKU），已跳过', 'url' => get_permalink( $existing_id ), 'edit' => get_edit_post_link( $existing_id ) );
    }

    if ( $existing_id ) {
        // 替换：复用原商品 ID，不改 slug → 前台链接不变；只刷新标题/状态、图片与内容
        $replacing = true;
        $prod_id   = $existing_id;
        wp_update_post( array(
            'ID'          => $prod_id,
            'post_title'  => $folder_name,
            'post_status' => $options['status'],
        ) );
    } else {
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
    }
    update_post_meta( $prod_id, '_product_type', 'simple' );
    update_post_meta( $prod_id, '_stock_status', 'instock' );
    update_post_meta( $prod_id, '_sku', $folder_name );
    update_post_meta( $prod_id, 'product_model', $folder_name );

    // 五个选项卡的文本：从 info.json / 参数c.json 解析
    $text_data = ew_import_read_product_text( $dir );
    // 替换时若新素材没带文字，保留原文字，避免误清空；带了的字段则覆盖刷新
    if ( ! $replacing || ! empty( $text_data['specs'] ) ) {
        update_post_meta( $prod_id, '_model_specs', wp_json_encode( $text_data['specs'], JSON_UNESCAPED_UNICODE ) );
    }
    if ( ! empty( $text_data['features'] ) ) {
        $feat_lines = array();
        foreach ( $text_data['features'] as $f ) {
            $feat_lines[] = isset( $f['value'] ) && '' !== $f['value'] && $f['value'] !== $f['name']
                ? $f['name'] . ': ' . $f['value']
                : $f['name'];
        }
        update_post_meta( $prod_id, '_model_features', wp_json_encode( array_values( $feat_lines ), JSON_UNESCAPED_UNICODE ) );
    }
    if ( '' !== $text_data['packaging'] ) {
        update_post_meta( $prod_id, '_packaging', $text_data['packaging'] );
    }

    // 多语言：wp_insert_post 不会挂 Polylang 语言，前台按语言过滤会看不到，须手动挂默认语言
    if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_default_language' ) ) {
        pll_set_post_language( $prod_id, pll_default_language() );
    }

    // 上传主图（第 1 张 = 缩略图；多张主图全部入库，保证图片不丢）
    $main_ids = array();
    foreach ( $main as $m ) {
        $aid = ew_import_attach_image( $m, $folder_name . '-白底图', $prod_id, 'catalog' );
        if ( $aid ) { $main_ids[] = $aid; }
    }
    // 上传画廊
    $gallery_ids = $main_ids;
    foreach ( $gallery as $group ) {
        foreach ( $group as $g ) {
            $aid = ew_import_attach_image( $g, $folder_name . '-展示图', $prod_id, 'catalog' );
            if ( $aid ) { $gallery_ids[] = $aid; }
        }
    }
    // 使用场景图：Product Details 第三张，同时保留在画廊
    $scene_ids = array();
    foreach ( $scene as $group ) {
        foreach ( $group as $s ) {
            $aid = ew_import_attach_image( $s, $folder_name . '-使用场景图', $prod_id, 'catalog' );
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
    $detail_ids_by_path = array();
    foreach ( $detail_en as $d ) {
        $aid = ew_import_attach_image( $d, $folder_name . '-详细参数图-en', $prod_id, 'detail' );
        if ( $aid ) { $de_ids[] = $aid; $detail_ids_by_path[ $d ] = $aid; }
    }
    $dc_ids = array();
    foreach ( $detail_cn as $d ) {
        // 未标语言的同一张图同时用于英文和中文，无需复制及生成两次缩略图。
        $aid = isset( $detail_ids_by_path[ $d ] ) ? $detail_ids_by_path[ $d ] : ew_import_attach_image( $d, $folder_name . '-详细参数图-cn', $prod_id, 'detail' );
        if ( $aid ) { $dc_ids[] = $aid; }
    }
    if ( ! empty( $de_ids ) ) { $detail_map['en'] = $de_ids; }
    if ( ! empty( $dc_ids ) ) { $detail_map['cn'] = $dc_ids; }
    if ( ! empty( $detail_map ) ) {
        update_post_meta( $prod_id, '_model_detail_images', wp_json_encode( $detail_map, JSON_UNESCAPED_UNICODE ) );
    }

    // 分类：自动识别优先，识别不到用表单手选的分类
    ew_import_apply_category( $prod_id, $cat_slug, ! empty( $options['cat_id'] ) ? (int) $options['cat_id'] : 0 );
    if ( ! empty( $options['hot'] ) ) {
        update_post_meta( $prod_id, 'product_hot', '1' );
    }

    // 多语言：同步到中文 / 西语 / 俄语 / 阿语版本
    if ( ! empty( $options['sync_langs'] ) ) {
        ew_product_sync_translations( $prod_id );
    }

    return array(
        'name'     => $folder_name,
        'ok'       => true,
        'replaced' => $replacing,
        'msg'      => $replacing ? ( $summary . '（替换原商品，链接不变）' ) : $summary,
        'cat'      => $cat_label,
        'url'      => get_permalink( $prod_id ),
        'edit'     => get_edit_post_link( $prod_id ),
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

/* ---------- 异步导入任务：ZIP 先解压，随后每个型号单独一批处理 ----------
   单次请求只处理少量型号，避免图片缩略图生成把 PHP-FPM 100 秒超时打爆。 */
function ew_import_job_base_dir() {
    $base = ew_import_temp_base();
    if ( '' === $base ) { return ''; }
    $dir = trailingslashit( $base ) . 'jobs';
    if ( ! is_dir( $dir ) ) { @mkdir( $dir, 0755, true ); }
    return ( is_dir( $dir ) && is_writable( $dir ) ) ? $dir : '';
}

function ew_import_cleanup_stale_jobs() {
    $base = ew_import_job_base_dir();
    if ( '' === $base ) { return; }
    foreach ( (array) glob( $base . '/*' ) as $dir ) {
        if ( is_dir( $dir ) && (int) @filemtime( $dir ) < time() - DAY_IN_SECONDS ) {
            ew_import_rmdir( $dir );
        }
    }
}

/* 递归展开 ZIP：把型号文件夹 / 「暂不处理」文件夹 / 嵌套 ZIP 都变成队列项 */
function ew_import_expand_zip_item( $zip_path, $label, $job_dir, $depth, &$queue, &$total ) {
    $zip = new ZipArchive();
    if ( true !== $zip->open( $zip_path ) ) {
        $queue[] = array( 'type' => 'error', 'name' => $label, 'msg' => 'ZIP 无法打开' );
        $total++;
        return;
    }

    $extract_dir = trailingslashit( $job_dir ) . 'zip-' . wp_unique_id();
    if ( ! @mkdir( $extract_dir, 0755, true ) ) {
        $zip->close();
        $queue[] = array( 'type' => 'error', 'name' => $label, 'msg' => '无法创建临时目录' );
        $total++;
        return;
    }
    if ( ! $zip->extractTo( $extract_dir ) ) {
        $zip->close();
        ew_import_rmdir( $extract_dir );
        $queue[] = array( 'type' => 'error', 'name' => $label, 'msg' => '解压失败' );
        $total++;
        return;
    }
    $zip->close();

    $root    = ew_import_resolve_root( $extract_dir );
    $count   = 0;
    $nested  = array();
    foreach ( (array) @scandir( $root ) as $entry ) {
        if ( '.' === $entry || '..' === $entry || '__MACOSX' === $entry ) { continue; }
        $sub = $root . '/' . $entry;
        if ( is_dir( $sub ) ) {
            if ( preg_match( '/暂不处理/u', $entry ) ) {
                $queue[] = array( 'type' => 'skip', 'name' => $entry, 'msg' => '文件夹名标注「暂不处理」，已跳过' );
                $total++;
                continue;
            }
            $queue[] = array( 'type' => 'folder', 'path' => $sub, 'name' => $entry );
            $total++;
            $count++;
        } elseif ( $depth < 4 && is_file( $sub ) && 'zip' === strtolower( pathinfo( $sub, PATHINFO_EXTENSION ) ) ) {
            $nested[] = $sub;
        }
    }

    // 顶层直接是图片（无子文件夹、无嵌套 ZIP）→ 按 ZIP 名当一个型号
    if ( 0 === $count ) {
        $has_top = false;
        foreach ( (array) @scandir( $root ) as $e ) {
            if ( ew_import_is_image( $e ) ) { $has_top = true; break; }
        }
        if ( $has_top || empty( $nested ) ) {
            $queue[] = array( 'type' => 'folder', 'path' => $root, 'name' => $label );
            $total++;
        }
    }

    foreach ( $nested as $nz ) {
        ew_import_expand_zip_item( $nz, pathinfo( $nz, PATHINFO_FILENAME ), $job_dir, $depth + 1, $queue, $total );
    }
}

function ew_import_create_job( $zips, $options ) {
    ew_import_cleanup_stale_jobs();
    $base = ew_import_job_base_dir();
    if ( '' === $base ) {
        return new WP_Error( 'ew_import_temp', '无法创建临时目录（uploads 不可写）。' );
    }

    $job_id  = strtolower( wp_generate_password( 24, false, false ) );
    $job_dir = trailingslashit( $base ) . $job_id;
    if ( ! @mkdir( $job_dir, 0755, true ) ) {
        return new WP_Error( 'ew_import_temp', '无法创建任务目录：' . $job_dir );
    }

    $queue = array();
    $total = 0;
    $saved = 0;
    foreach ( $zips as $z ) {
        $dest = $job_dir . '/source-' . wp_unique_id() . '.zip';
        $ok   = @move_uploaded_file( $z['tmp'], $dest );
        if ( ! $ok ) { $ok = @copy( $z['tmp'], $dest ); }
        if ( ! $ok ) {
            $queue[] = array( 'type' => 'error', 'name' => $z['name'], 'msg' => '上传文件保存失败，请重试' );
            $total++;
            continue;
        }
        $saved++;
        ew_import_expand_zip_item( $dest, pathinfo( $z['name'], PATHINFO_FILENAME ), $job_dir, 1, $queue, $total );
    }

    if ( 0 === $saved ) {
        ew_import_rmdir( $job_dir );
        return new WP_Error( 'ew_import_temp', 'ZIP 文件保存失败，请重试。' );
    }
    if ( 0 === $total ) {
        $queue[] = array( 'type' => 'error', 'name' => 'ZIP', 'msg' => '没有找到型号文件夹或图片' );
        $total   = 1;
    }

    $job = array(
        'id'           => $job_id,
        'user_id'      => get_current_user_id(),
        'options'      => $options,
        'queue'        => $queue,
        'index'        => 0,
        'total_models' => $total,
        'done_models'  => 0,
        'results'      => array(),
        'dir'          => $job_dir,
        'created'      => time(),
    );
    set_transient( 'ew_import_job_' . $job_id, $job, 12 * HOUR_IN_SECONDS );
    return $job;
}

/* 处理一批型号：最多跑 $max_seconds 秒，返回更新后的任务 */
function ew_import_run_job_batch( $job, $max_seconds = 35 ) {
    $start = microtime( true );
    $count = count( $job['queue'] );

    while ( $job['index'] < $count ) {
        if ( microtime( true ) - $start >= $max_seconds ) { break; }

        $item = $job['queue'][ $job['index'] ];
        if ( 'skip' === $item['type'] ) {
            $result = array( 'name' => $item['name'], 'ok' => false, 'skip' => true, 'msg' => $item['msg'], 'url' => '' );
        } elseif ( 'error' === $item['type'] ) {
            $result = array( 'name' => $item['name'], 'ok' => false, 'msg' => $item['msg'], 'url' => '' );
        } else {
            try {
                $result = ew_import_one_folder( $item['path'], $item['name'], $job['options'] );
            } catch ( Throwable $e ) {
                $result = array( 'name' => $item['name'], 'ok' => false, 'msg' => '处理失败：' . $e->getMessage(), 'url' => '' );
            }
        }

        $job['results'][] = $result;
        $job['done_models']++;
        $job['index']++;
    }

    $job['done'] = ( $job['index'] >= count( $job['queue'] ) );
    return $job;
}

function ew_import_job_finish( $job ) {
    if ( ! empty( $job['dir'] ) ) { ew_import_rmdir( $job['dir'] ); }
    if ( ! empty( $job['id'] ) ) { set_transient( 'ew_import_job_' . $job['id'], $job, 12 * HOUR_IN_SECONDS ); }
}

/* AJAX 分批处理入口：每次只跑一小批，前端轮询进度 */
function ew_handle_product_import_process() {
    if ( ! current_user_can( 'edit_products' ) ) {
        ew_import_json_exit( array( 'ok' => false, 'error' => '无权限' ) );
    }
    $nonce = isset( $_REQUEST['ew_import_nonce'] ) ? $_REQUEST['ew_import_nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'ew_product_import' ) ) {
        ew_import_json_exit( array( 'ok' => false, 'error' => '安全校验失败，请刷新页面后重试。' ) );
    }

    $job_id = isset( $_POST['job'] ) ? sanitize_key( $_POST['job'] ) : '';
    $job    = $job_id ? get_transient( 'ew_import_job_' . $job_id ) : false;
    if ( ! is_array( $job ) || empty( $job['user_id'] ) || (int) $job['user_id'] !== get_current_user_id() ) {
        ew_import_json_exit( array( 'ok' => false, 'expired' => true, 'error' => '导入任务已过期，请重新上传 ZIP。' ) );
    }

    if ( ! empty( $job['done'] ) ) {
        ew_import_json_exit( array( 'ok' => true, 'done' => true, 'done_models' => (int) $job['done_models'], 'total_models' => (int) $job['total_models'], 'results' => array_values( $job['results'] ), 'dry' => ! empty( $job['options']['dry'] ) ) );
    }
    $lock_path = trailingslashit( $job['dir'] ) . 'process.lock';
    $lock = @fopen( $lock_path, 'c' );
    if ( ! $lock || ! flock( $lock, LOCK_EX | LOCK_NB ) ) {
        if ( $lock ) { fclose( $lock ); }
        ew_import_json_exit( array( 'ok' => true, 'busy' => true ) );
    }
    // The other request may have finished while this one waited for the lock.
    $job = get_transient( 'ew_import_job_' . $job_id );
    if ( ! is_array( $job ) ) { ew_import_json_exit( array( 'ok' => false, 'error' => '导入任务已过期。' ) ); }
    if ( ! empty( $job['done'] ) ) {
        ew_import_json_exit( array( 'ok' => true, 'done' => true, 'done_models' => (int) $job['done_models'], 'total_models' => (int) $job['total_models'], 'results' => array_values( $job['results'] ), 'dry' => ! empty( $job['options']['dry'] ) ) );
    }
    @set_time_limit( 0 );
    @ini_set( 'memory_limit', '512M' );
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $job  = ew_import_run_job_batch( $job, 35 );
    $done = ! empty( $job['done'] );
    $current_item = ! $done && isset( $job['queue'][ $job['index'] ] ) ? $job['queue'][ $job['index'] ] : null;

    $payload = array(
        'ok'           => true,
        'done'         => $done,
        'done_models'  => (int) $job['done_models'],
        'total_models' => (int) $job['total_models'],
        'results'      => array_values( $job['results'] ),
        'current'      => ( $current_item && isset( $current_item['name'] ) ) ? (string) $current_item['name'] : '',
        'dry'          => ! empty( $job['options']['dry'] ),
        'error'        => '',
    );

    if ( $done ) { ew_import_job_finish( $job ); }
    else { set_transient( 'ew_import_job_' . $job['id'], $job, 12 * HOUR_IN_SECONDS ); }

    ew_import_json_exit( $payload );
}
add_action( 'admin_post_ew_product_import_process', 'ew_handle_product_import_process' );

require_once __DIR__ . '/product-import-upload.php';

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
    $options = array( 'cat_id' => 0, 'cat_auto' => true, 'sync_langs' => true, 'status' => 'publish', 'hot' => false, 'dry' => false );

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
            'cat_id'     => isset( $_POST['ew_cat_id'] ) ? absint( $_POST['ew_cat_id'] ) : 0,
            'cat_auto'   => ! empty( $_POST['ew_cat_auto'] ),
            'sync_langs' => ! empty( $_POST['ew_sync_langs'] ),
            'status'     => ( isset( $_POST['ew_status'] ) && 'draft' === $_POST['ew_status'] ) ? 'draft' : 'publish',
            'hot'        => ! empty( $_POST['ew_hot'] ),
            'dry'        => ! empty( $_POST['ew_dry'] ),
            'replace'    => ! empty( $_POST['ew_duplicate'] ) && 'replace' === $_POST['ew_duplicate'],
        );

        // 分类对照表：表单提交后保存，后续导入自动沿用
        if ( isset( $_POST['ew_cat_map'] ) && is_string( $_POST['ew_cat_map'] ) ) {
            update_option( 'ew_import_category_map', wp_unslash( $_POST['ew_cat_map'] ), false );
        }

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
        } elseif ( $ajax ) {
            // 只解压并建任务，图片处理交给后续分批请求，避免 100 秒超时
            $job = ew_import_create_job( $real, $options );
            if ( is_wp_error( $job ) ) {
                $error = $job->get_error_message();
            } else {
                ew_import_json_exit( array(
                    'ok'           => true,
                    'job'          => $job['id'],
                    'total_models' => (int) $job['total_models'],
                    'results'      => array(),
                    'dry'          => ! empty( $options['dry'] ),
                ) );
            }
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
    $sync_lang_names = array();
    if ( function_exists( 'pll_languages_list' ) ) {
        $pll_default = function_exists( 'pll_default_language' ) ? pll_default_language() : '';
        foreach ( (array) pll_languages_list( array( 'fields' => '' ) ) as $pll_lang ) {
            if ( $pll_lang->slug !== $pll_default ) { $sync_lang_names[] = $pll_lang->name; }
        }
    }

    // 兜底分类默认 Other Instruments：识别不出的型号也不会从分类页消失
    $default_cat_id = 0;
    foreach ( $cats as $c ) {
        if ( 'others' === $c->slug ) { $default_cat_id = (int) $c->term_id; break; }
    }
    if ( ! $default_cat_id ) {
        $others_term = get_term_by( 'slug', 'others', 'product_cat' );
        if ( $others_term && ! is_wp_error( $others_term ) ) { $default_cat_id = (int) $others_term->term_id; }
    }
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
            $ok = $skip = $fail = $uncat = 0;
            foreach ( $rows as $r ) {
                if ( ! empty( $r['skip'] ) ) { $skip++; }
                elseif ( ! empty( $r['ok'] ) ) { $ok++; if ( empty( $r['cat'] ) ) { $uncat++; } }
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
                if ( $uncat ) {
                    $notice .= '<div class="notice notice-warning is-dismissible"><p>有 <strong>' . esc_html( (string) $uncat ) . '</strong> 个商品未识别分类，不会出现在分类页；请在「分类对照表」补型号，或把「兜底分类」设为 Other Instruments。</p></div>';
                }
            }
        } else {
            $expired = true;
        }
    }
    ?>
    <style>
    .ew-product-import{--ew-navy:#14273e;--ew-ink:#1c314b;--ew-muted:#64748b;--ew-line:#e2e9f1;--ew-blue:#1463a5;--ew-sky:#e9f5ff;max-width:1240px;margin:20px 20px 50px 0;color:var(--ew-ink);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    .ew-product-import *{box-sizing:border-box}
    .ew-product-import .ew-hero{position:relative;overflow:hidden;display:flex;justify-content:space-between;gap:24px;align-items:center;padding:31px 36px;border-radius:20px;background:radial-gradient(circle at 90% -30%,#2c87ab 0,transparent 36%),linear-gradient(120deg,#14273e,#214c66);color:#fff;box-shadow:0 16px 30px rgba(19,43,68,.15)}
    .ew-product-import .ew-hero:after{content:"";position:absolute;width:250px;height:250px;right:7%;bottom:-180px;border:1px solid rgba(255,255,255,.2);border-radius:50%;box-shadow:0 0 0 42px rgba(255,255,255,.035),0 0 0 95px rgba(255,255,255,.025);pointer-events:none}
    .ew-product-import .ew-eyebrow{display:inline-flex;align-items:center;gap:8px;margin-bottom:11px;color:#99d9e8;font-size:11px;font-weight:800;letter-spacing:.15em;text-transform:uppercase}
    .ew-product-import .ew-eyebrow:before{content:"";width:7px;height:7px;border-radius:50%;background:#60dbbd}
    .ew-product-import .ew-hero h1{color:#fff;margin:0 0 10px;font-size:30px;line-height:1.2;font-weight:750;letter-spacing:-.02em}
    .ew-product-import .ew-hero p{max-width:630px;margin:0;color:#d5e7ef;font-size:14px;line-height:1.8}
    .ew-product-import .ew-hero-mark{display:grid;place-items:center;flex:0 0 90px;height:90px;border:1px solid rgba(255,255,255,.3);border-radius:24px;background:rgba(255,255,255,.09);transform:rotate(-8deg)}
    .ew-product-import .ew-hero-mark .dashicons{font-size:47px;width:47px;height:47px;transform:rotate(8deg)}
    .ew-product-import .ew-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}
    .ew-product-import .ew-step{display:flex;gap:11px;align-items:center;padding:13px 17px;border:1px solid var(--ew-line);border-radius:12px;background:#fff;color:#607187;font-size:12px;font-weight:650}
    .ew-product-import .ew-step b{display:grid;place-items:center;flex:none;width:24px;height:24px;border-radius:7px;background:#eaf1f8;color:#1a649c;font-size:12px}
    .ew-product-import .ew-step:first-child{border-color:#c8e0ef;color:#174b73;background:#f3faff}
    .ew-product-import .ew-step:first-child b{background:#1e78ac;color:#fff}
    .ew-product-import .ew-layout{display:grid;grid-template-columns:minmax(0,1.38fr) minmax(310px,.86fr);align-items:start;gap:18px}
    .ew-product-import .ew-card{border:1px solid var(--ew-line);border-radius:18px;background:#fff;box-shadow:0 5px 22px rgba(26,53,78,.055)}
    .ew-product-import .ew-card-body{padding:24px}
    .ew-product-import .ew-card-heading{display:flex;align-items:start;justify-content:space-between;gap:12px;margin-bottom:21px}
    .ew-product-import .ew-card-heading h2{margin:0 0 5px;font-size:19px;line-height:1.3;color:var(--ew-navy)}
    .ew-product-import .ew-card-heading p{margin:0;color:var(--ew-muted);font-size:13px;line-height:1.6}
    .ew-product-import .ew-chip{display:inline-block;flex:none;padding:6px 10px;border-radius:20px;background:#eaf4fb;color:#24658a;font-size:11px;font-weight:800}
    .ew-product-import .ew-dropzone{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:222px;padding:24px;text-align:center;border:2px dashed #acc9db;border-radius:14px;background:linear-gradient(155deg,#f5fbff,#f9fcff);transition:.2s ease;cursor:pointer}
    .ew-product-import .ew-dropzone:hover,.ew-product-import .ew-dropzone.is-dragging{border-color:#1686b8;background:#eaf7ff;transform:translateY(-1px)}
    .ew-product-import .ew-drop-icon{display:grid;place-items:center;width:56px;height:56px;margin-bottom:13px;border-radius:16px;background:#e4f1fa;color:#2078ac}
    .ew-product-import .ew-drop-icon .dashicons{width:27px;height:27px;font-size:27px}
    .ew-product-import .ew-dropzone strong{display:block;color:#18364f;font-size:16px}
    .ew-product-import .ew-dropzone small{display:block;margin:6px 0 14px;color:#687d90;font-size:12px}
    .ew-product-import .ew-pick{display:inline-block;padding:8px 16px;border:1px solid #aed3ea;border-radius:8px;background:#fff;color:#12629a;font-size:12px;font-weight:750}
    .ew-product-import .ew-file-input{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;clip-path:inset(50%)}
    .ew-product-import .ew-dropzone:focus-within{outline:2px solid #2271b1;outline-offset:3px}
    .ew-product-import .ew-selected{min-height:58px;margin-top:15px}
    .ew-product-import .ew-selected-empty{display:flex;align-items:center;gap:8px;margin:0;color:#708298;font-size:12px}
    .ew-product-import .ew-selected-empty .dashicons{color:#83a1b6}
    .ew-product-import .ew-file-row{display:flex;align-items:center;gap:11px;padding:10px 12px;margin-top:7px;border:1px solid #e5edf4;border-radius:9px;background:#fbfdff}
    .ew-product-import .ew-file-row .dashicons{color:#2180a6}
    .ew-product-import .ew-file-row span:nth-child(2){min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:650}
    .ew-product-import .ew-file-row small{color:#708298}
    .ew-product-import .ew-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 24px;border-top:1px solid #e9eef4;background:#fbfdff;border-radius:0 0 18px 18px}
    .ew-product-import .ew-actions p{margin:0;color:#718299;font-size:12px;line-height:1.5}
    .ew-product-import #ew-import-btn{display:inline-flex;align-items:center;gap:7px;min-height:42px;padding:0 19px;border:0;border-radius:9px;background:#1476aa;color:#fff;font-size:13px;font-weight:750;box-shadow:0 5px 11px rgba(20,118,170,.18);cursor:pointer;white-space:nowrap}
    .ew-product-import #ew-import-btn:hover{background:#0e648f}
    .ew-product-import #ew-import-btn:disabled{opacity:.55;cursor:default}
    .ew-product-import #ew-import-btn .dashicons{font-size:18px;width:18px;height:18px}
    .ew-product-import .ew-field{padding:13px 0;border-bottom:1px solid #edf1f5}
    .ew-product-import .ew-field:first-of-type{padding-top:0}
    .ew-product-import .ew-field:last-child{border:0;padding-bottom:0}
    .ew-product-import .ew-field-title{display:block;margin-bottom:9px;color:#29445c;font-size:12px;font-weight:800}
    .ew-product-import .ew-field select,.ew-product-import .ew-field textarea{width:100%;max-width:none;border-color:#d8e3eb;border-radius:8px}
    .ew-product-import .ew-field select{min-height:36px}
    .ew-product-import .ew-choice-row{display:flex;gap:7px;flex-wrap:wrap}
    .ew-product-import .ew-choice{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #dce5ed;border-radius:8px;background:#fff;color:#42586d;font-size:12px;cursor:pointer}
    .ew-product-import .ew-choice:has(input:checked){border-color:#76b8dc;background:#eef8fe;color:#175d85;font-weight:750}
    .ew-product-import .ew-choice input{margin:0}
    .ew-product-import .ew-help{margin:7px 0 0;color:#708298;font-size:11px;line-height:1.6}
    .ew-product-import .ew-extra{margin-top:18px;padding:0 24px;border:1px solid var(--ew-line);border-radius:15px;background:#fff}
    .ew-product-import .ew-extra summary{display:flex;align-items:center;justify-content:space-between;list-style:none;cursor:pointer;padding:19px 0;font-size:14px;font-weight:800;color:#23435d}
    .ew-product-import .ew-extra summary::-webkit-details-marker{display:none}
    .ew-product-import .ew-extra summary:after{content:"＋";font-size:20px;line-height:1;color:#5889a7}
    .ew-product-import .ew-extra[open] summary:after{content:"−"}
    .ew-product-import .ew-extra-body{border-top:1px solid #eaf0f5;padding:5px 0 22px}
    .ew-product-import .ew-extra .ew-field{max-width:760px}
    .ew-product-import .ew-extra textarea{min-height:120px;font-family:ui-monospace,SFMono-Regular,monospace;font-size:12px}
    .ew-product-import #ew-progress{padding:23px 25px;margin:20px 0 0;border:1px solid #c9e1ef;border-radius:16px;background:#f7fcff;box-shadow:0 5px 18px rgba(20,82,124,.05)}
    .ew-product-import .ew-progress-label{margin:0 0 12px;color:#215a7b;font-size:13px;font-weight:800}
    .ew-product-import #ew-progress-track{height:12px;overflow:hidden;border-radius:20px;background:#e0edf5}
    .ew-product-import #ew-progress-fill{height:100%;width:0;border-radius:20px;background:linear-gradient(90deg,#2080b2,#2bb6a1);transition:width .25s ease}
    .ew-product-import #ew-progress-text{margin:12px 0 0;color:#37586d;font-size:13px;font-weight:650}
    .ew-product-import #ew-import-resume-wrap{display:flex;align-items:center;gap:13px;margin:12px 0;padding:14px 17px;border:1px solid #cbe2ef;border-radius:11px;background:#f2faff}
    .ew-product-import #ew-live-results{margin-top:20px;overflow-x:auto}
    .ew-product-import #ew-live-results .widefat{border-radius:10px;overflow:hidden;border-color:#dbe6ed}
    .ew-product-import #ew-live-results th{background:#eef5fa}
    .ew-product-import .ew-guide{margin-top:26px}
    .ew-product-import .ew-guide h2{margin:0 0 5px;color:var(--ew-navy);font-size:20px}
    .ew-product-import .ew-guide>p{margin:0 0 13px;color:#718299;font-size:12px}
    .ew-product-import .ew-guide .ew-extra{margin-top:9px}
    .ew-product-import .ew-guide-table{width:100%;border-collapse:collapse;font-size:12px}
    .ew-product-import .ew-guide-table th,.ew-product-import .ew-guide-table td{padding:11px;border-bottom:1px solid #edf1f5;text-align:left;vertical-align:top}
    .ew-product-import .ew-guide-table th{color:#4a657a;font-weight:800}
    .ew-product-import .ew-guide code{border-radius:4px;background:#f1f5f9;color:#34627d}
    .ew-product-import .ew-json-example{width:100%;max-width:760px;min-height:220px;padding:14px;border:1px solid #dfe8ef;border-radius:9px;background:#f7f9fc;font:12px/1.6 ui-monospace,SFMono-Regular,monospace}
    @media(max-width:1050px){.ew-product-import .ew-layout{grid-template-columns:1fr}.ew-product-import .ew-settings .ew-card-body{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:24px}.ew-product-import .ew-settings .ew-card-heading{grid-column:1/-1}}
    @media(max-width:680px){.ew-product-import{margin-right:10px}.ew-product-import .ew-hero{padding:25px}.ew-product-import .ew-hero h1{font-size:25px}.ew-product-import .ew-hero-mark{display:none}.ew-product-import .ew-steps{grid-template-columns:1fr;gap:6px}.ew-product-import .ew-step{padding:9px 12px}.ew-product-import .ew-settings .ew-card-body{display:block}.ew-product-import .ew-card-body{padding:19px}.ew-product-import .ew-actions{align-items:stretch;flex-direction:column;padding:16px 19px}.ew-product-import #ew-import-btn{justify-content:center}.ew-product-import .ew-extra{padding:0 18px}}
    </style>
    <div class="wrap ew-product-import">
        <header class="ew-hero">
            <div><span class="ew-eyebrow">PRODUCT IMPORT STUDIO</span><h1>商品批量导入</h1><p>将商品素材打包为 ZIP，一次选择多个文件。系统依次上传、识别型号并创建商品；进度可在刷新后继续。</p></div>
            <span class="ew-hero-mark" aria-hidden="true"><span class="dashicons dashicons-upload"></span></span>
        </header>
        <?php echo $notice; ?>
        <?php if ( $expired ) { echo '<div class="notice notice-error is-dismissible"><p>导入结果已过期（超过 1 小时），请重新导入。</p></div>'; } ?>
        <div class="ew-steps" aria-label="导入流程"><div class="ew-step"><b>1</b> 选择 ZIP 文件</div><div class="ew-step"><b>2</b> 确认导入设置</div><div class="ew-step"><b>3</b> 查看进度与结果</div></div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="ew-import-form">
            <input type="hidden" name="action" value="ew_product_import">
            <?php wp_nonce_field( 'ew_product_import', 'ew_import_nonce' ); ?>
            <div class="ew-layout">
                <section class="ew-card ew-upload">
                    <div class="ew-card-body">
                        <div class="ew-card-heading"><div><h2>选择素材包</h2><p>每个 ZIP 中，一个型号对应一个文件夹。支持一次选择多个 ZIP。</p></div><span class="ew-chip">ZIP 格式</span></div>
                        <label class="ew-dropzone" id="ew-dropzone" for="ew-import-files">
                            <span class="ew-drop-icon" aria-hidden="true"><span class="dashicons dashicons-media-archive"></span></span>
                            <strong>拖入 ZIP 文件，或点击选择</strong><small>支持批量选择 · 自动分片上传 · 中断后可续传</small><span class="ew-pick">浏览文件</span>
                            <input class="ew-file-input" id="ew-import-files" type="file" name="ew_import_zip[]" accept=".zip" multiple required>
                        </label>
                        <div class="ew-selected" id="ew-selected-files"><p class="ew-selected-empty"><span class="dashicons dashicons-info-outline"></span> 尚未选择文件</p></div>
                    </div>
                    <div class="ew-actions"><p>上传后将自动解压并逐个导入型号。</p><button type="submit" id="ew-import-btn"><span class="dashicons dashicons-upload"></span> 开始导入</button></div>
                </section>

                <aside class="ew-card ew-settings">
                    <div class="ew-card-body">
                        <div class="ew-card-heading"><div><h2>导入设置</h2><p>按需调整，默认设置适合常规批量导入。</p></div></div>
                        <div class="ew-field"><span class="ew-field-title">商品状态</span><div class="ew-choice-row"><label class="ew-choice"><input type="radio" name="ew_status" value="publish" checked> 立即发布</label><label class="ew-choice"><input type="radio" name="ew_status" value="draft"> 保存为草稿</label></div></div>
                        <div class="ew-field"><span class="ew-field-title">重复型号</span><div class="ew-choice-row"><label class="ew-choice"><input type="radio" name="ew_duplicate" value="skip" checked> 跳过已有商品</label><label class="ew-choice"><input type="radio" name="ew_duplicate" value="replace"> 替换已有商品</label></div><p class="ew-help">替换会保留原商品 ID 与链接，并更新图片和内容。</p></div>
                        <div class="ew-field"><span class="ew-field-title">分类方式</span><label class="ew-choice"><input type="checkbox" name="ew_cat_auto" value="1" checked> 自动识别分类</label><p class="ew-help">根据文件夹名、info.json 和分类文件识别。</p></div>
                        <div class="ew-field"><label class="ew-field-title" for="ew-cat-id">未识别时归入</label><select id="ew-cat-id" name="ew_cat_id"><option value="0" <?php selected( $default_cat_id, 0 ); ?>>不分类</option><?php foreach ( $cats as $c ) : ?><option value="<?php echo (int) $c->term_id; ?>" <?php selected( $default_cat_id, (int) $c->term_id ); ?>><?php echo esc_html( $c->name ); ?></option><?php endforeach; ?></select></div>
                    </div>
                </aside>
            </div>
            <details class="ew-extra"><summary>更多导入选项 <span class="ew-help">多语言、试运行与分类对照表</span></summary><div class="ew-extra-body">
                <?php if ( ! empty( $sync_lang_names ) ) : ?><div class="ew-field"><span class="ew-field-title">多语言</span><label class="ew-choice"><input type="checkbox" name="ew_sync_langs" value="1" checked> 同时创建其它语言版本</label><p class="ew-help"><?php echo esc_html( implode( ' / ', $sync_lang_names ) ); ?>。图片、参数和分类共用。</p></div><?php endif; ?>
                <div class="ew-field"><span class="ew-field-title">首页推荐</span><label class="ew-choice"><input type="checkbox" name="ew_hot" value="1"> 设为 Featured 商品</label></div>
                <div class="ew-field"><span class="ew-field-title">试运行</span><label class="ew-choice"><input type="checkbox" name="ew_dry" value="1"> 只统计，不创建商品</label><p class="ew-help">建议首次导入新素材时先检查识别结果。</p></div>
                <div class="ew-field"><label class="ew-field-title" for="ew-cat-map">分类对照表</label><textarea id="ew-cat-map" name="ew_cat_map" rows="7" placeholder="# 每行一个型号：型号 = 分类&#10;DM9205A = 万用表&#10;VT811 = 测电笔"><?php echo esc_textarea( (string) get_option( 'ew_import_category_map', '' ) ); ?></textarea><p class="ew-help">适合纯数字型号或无法自动识别的分类。保存后下次导入继续使用。</p></div>
            </div></details>
        </form>

        <section id="ew-progress" style="display:none" role="status" aria-live="polite"><p class="ew-progress-label">导入进度</p><div id="ew-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div id="ew-progress-fill"></div></div><p id="ew-progress-text"></p></section>
        <div id="ew-import-resume-wrap" style="display:none"><button type="button" class="button button-primary" id="ew-import-resume">继续导入</button><span class="description">从已上传的分片或已完成的型号继续。</span></div>
        <div id="ew-live-results"></div>

        <section class="ew-guide"><h2>素材准备指南</h2><p>导入前核对命名规则；详细说明默认收起，不影响主要操作。</p>
            <details class="ew-extra"><summary>文件夹与图片如何命名</summary><div class="ew-extra-body"><table class="ew-guide-table"><thead><tr><th>文件或文件夹</th><th>导入位置</th></tr></thead><tbody>
                <tr><td>型号文件夹（如 <code>XT266C</code>）</td><td>商品名称与 SKU</td></tr><tr><td><code>白底图</code> / <code>底图</code> / <code>主图</code></td><td>商品主图、详情第 1 张</td></tr><tr><td><code>详细参数图</code> / <code>详情图</code></td><td>商品详情第 2 张；文件名可加“英文”或“中文”区分语言</td></tr><tr><td><code>使用场景图</code> / <code>场景图N</code></td><td>商品详情第 3 张，同时进入画廊</td></tr><tr><td><code>展示图N</code> / <code>轮播图N</code></td><td>画廊图，按编号排序</td></tr><tr><td><code>info.json</code> 或 <code>参数c.json</code></td><td>规格、特性与包装文字</td></tr>
            </tbody></table><p class="ew-help">ZIP 内可包含嵌套 ZIP；含“暂不处理”的文件夹会跳过。重复 SKU 默认跳过。</p></div></details>
            <details class="ew-extra" id="ew-import-json-example"><summary>info.json 示例与字段说明</summary><div class="ew-extra-body"><p class="ew-help">把 UTF-8 格式的 <code>info.json</code> 放进型号文件夹。<code>details</code> 中的 Description 是规格与特性的分界，<code>packaging</code> 用于包装信息。</p><textarea class="ew-json-example" readonly rows="12">{
  "name": "DT6221 digital meter",
  "details": "DCV: 400mV~600V ±1.8%\nACV: 4V~600V ±2.0%\nDescription\nLCD size: 48x29mm\nLow battery indication: yes",
  "packaging": "Packing: color box\nPacking quantity: 60PCS/CTN"
}</textarea></div></details>
        </section>

        <script>
        (function () {
            var form = document.getElementById('ew-import-form');
            if (!form) { return; }
            var fileInput = form.querySelector('input[type=file]');
            var btn = document.getElementById('ew-import-btn');
            var resumeBtn = document.getElementById('ew-import-resume');
            var resumeWrap = document.getElementById('ew-import-resume-wrap');
            var dropzone = document.getElementById('ew-dropzone');
            var selectedFiles = document.getElementById('ew-selected-files');
            var bar = document.getElementById('ew-progress');
            var fill = document.getElementById('ew-progress-fill');
            var txt = document.getElementById('ew-progress-text');
            var out = document.getElementById('ew-live-results');
            var jobId = '', nonce = '', allResults = [], completedResults = [], dry = false;
            var files = [], fileIndex = 0, currentUpload = '', uploading = false;
            var batchKey = 'ew-product-import-batch', batch = null;
            var chunkSize = 1024 * 1024, concurrency = 3;

            function fileKey(file) { return file.name + ':' + file.size + ':' + file.lastModified; }
            function saveBatch() {
                if (!batch) { return; }
                try { localStorage.setItem(batchKey, JSON.stringify(batch)); } catch (e) {}
            }
            function clearBatch() {
                batch = null;
                try { localStorage.removeItem(batchKey); } catch (e) {}
            }
            function setPercent(value) {
                var pct = Math.max(0, Math.min(100, Math.round(value)));
                fill.style.width = pct + '%';
                document.getElementById('ew-progress-track').setAttribute('aria-valuenow', String(pct));
            }

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }
            function mb(b) { return (b / 1048576).toFixed(1) + ' MB'; }

            function renderSelectedFiles() {
                var chosen = Array.prototype.slice.call(fileInput.files || []);
                if (!chosen.length) {
                    selectedFiles.innerHTML = '<p class="ew-selected-empty"><span class="dashicons dashicons-info-outline"></span> 尚未选择文件</p>';
                    return;
                }
                selectedFiles.innerHTML = chosen.map(function (file) {
                    return '<div class="ew-file-row"><span class="dashicons dashicons-media-archive" aria-hidden="true"></span><span title="' + esc(file.name) + '">' + esc(file.name) + '</span><small>' + mb(file.size) + '</small></div>';
                }).join('');
            }
            fileInput.addEventListener('change', renderSelectedFiles);
            if (dropzone) {
                ['dragenter', 'dragover'].forEach(function (name) {
                    dropzone.addEventListener(name, function (event) { event.preventDefault(); dropzone.classList.add('is-dragging'); });
                });
                ['dragleave', 'drop'].forEach(function (name) {
                    dropzone.addEventListener(name, function (event) { event.preventDefault(); dropzone.classList.remove('is-dragging'); });
                });
                dropzone.addEventListener('drop', function (event) {
                    var dropped = Array.prototype.slice.call(event.dataTransfer && event.dataTransfer.files || []);
                    if (!dropped.length) { return; }
                    if (dropped.some(function (file) { return !/\.zip$/i.test(file.name); })) {
                        showError('这里只能选择 ZIP 文件。', false);
                        return;
                    }
                    try {
                        var transfer = new DataTransfer();
                        dropped.forEach(function (file) { transfer.items.add(file); });
                        fileInput.files = transfer.files;
                        renderSelectedFiles();
                    } catch (error) {
                        showError('当前浏览器不支持拖入文件，请点击「浏览文件」选择 ZIP。', false);
                    }
                });
            }

            function countResults() {
                var ok = 0, skip = 0, fail = 0;
                allResults.forEach(function (r) {
                    if (r.skip) { skip++; } else if (r.ok) { ok++; } else { fail++; }
                });
                return { ok: ok, skip: skip, fail: fail };
            }

            function tableHtml() {
                if (!allResults.length) { return ''; }
                var html = '<h2>本次导入结果</h2><table class="widefat striped" style="max-width:960px"><thead><tr><th>型号</th><th>状态</th><th>分类</th><th>图片</th><th>链接</th></tr></thead><tbody>';
                allResults.forEach(function (r) {
                    var st = r.skip ? '<span style="color:#2271ae">跳过</span> · ' + esc(r.msg)
                        : (r.replaced ? '<span style="color:#b35d00">已替换</span> · ' + esc(r.msg)
                        : r.ok ? '<span style="color:#00a32a">成功</span>'
                        : '<span style="color:#d63638">失败</span> · ' + esc(r.msg));
                    var img = r.ok && !r.skip ? esc(r.msg) : '';
                    var link = '';
                    if (r.url) { link += '<a href="' + esc(r.url) + '" target="_blank">查看</a>&nbsp;'; }
                    if (r.edit) { link += '<a href="' + esc(r.edit) + '">编辑</a>'; }
                    html += '<tr><td>' + esc(r.name) + '</td><td>' + st + '</td><td>' + (r.cat ? esc(r.cat) : '<span style="color:#d63638">未识别</span>') + '</td><td>' + img + '</td><td>' + link + '</td></tr>';
                });
                return html + '</tbody></table>';
            }

            function paint(complete) {
                var c = countResults(), html = '';
                if (complete) {
                    if (!allResults.length) {
                        html = '<div class="notice notice-info"><p>没有可导入的型号。</p></div>';
                    } else {
                        var label = dry ? '试运行完成（未建商品）：可识别 ' : '导入完成：成功 ';
                        var head = '<p><strong>' + label + c.ok + ' 个';
                        if (c.skip) { head += '，跳过 ' + c.skip + ' 个（重复/标注）'; }
                        if (c.fail) { head += '，失败 ' + c.fail + ' 个'; }
                        var uncat = allResults.filter(function (r) { return r.ok && !r.skip && !r.cat; }).length;
                        if (uncat) { head += '；其中 ' + uncat + ' 个未识别分类（不会出现在分类页，可补分类对照表或设兜底分类）'; }
                        head += '（见下表）。</p>';
                        html = '<div class="notice ' + (dry ? 'notice-info' : (c.fail ? 'notice-warning' : 'notice-success')) + ' is-dismissible">' + head + '</div>';
                    }
                } else if (allResults.length) {
                    html = '<div class="notice notice-info"><p><strong>服务器处理中：</strong>已处理 ' + allResults.length + ' 个型号（成功 ' + c.ok + ' / 跳过 ' + c.skip + ' / 失败 ' + c.fail + '）。</p></div>';
                }
                out.innerHTML = html + tableHtml();
            }

            function setProgress(done, total, label) {
                if (total > 0) {
                    var pct = Math.min(100, Math.round(done * 100 / total));
                    setPercent(pct);
                    txt.textContent = '服务器处理中：' + done + ' / ' + total + ' 个型号' + (label ? '（' + label + '）' : '');
                } else {
                    setPercent(5);
                    txt.textContent = label || '服务器正在解压 ZIP…';
                }
            }

            function showError(msg, canResume) {
                btn.disabled = false;
                if (resumeBtn) { resumeBtn.disabled = false; }
                txt.textContent = msg;
                resumeWrap.style.display = canResume ? 'block' : 'none';
                out.innerHTML = '<div class="notice notice-error is-dismissible"><p>' + esc(msg) + '</p></div>' + tableHtml();
            }

            function parseJson(xhr) {
                try { return JSON.parse(xhr.responseText); } catch (e) { return null; }
            }

            function processNext() {
                if (!jobId) { return; }
                resumeWrap.style.display = 'none';
                if (resumeBtn) { resumeBtn.disabled = true; }
                var fd = new FormData();
                fd.append('action', 'ew_product_import_process');
                fd.append('job', jobId);
                fd.append('ew_import_nonce', nonce);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', form.getAttribute('action'), true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.timeout = 120000;
                xhr.onload = function () {
                    var data = parseJson(xhr);
                    if (!data) {
                        showError('服务器返回异常（' + xhr.status + '）：' + String(xhr.responseText || '').slice(0, 200) + '。已处理 ' + allResults.length + ' 个型号，可点击「继续导入」从断点继续。', true);
                        return;
                    }
                    if (data.error) {
                        if (data.expired && batch) {
                            if (batch.entries[batch.index]) { localStorage.removeItem('ew-product-upload:' + batch.entries[batch.index]); }
                            batch.job = '';
                            batch.upload = '';
                            jobId = '';
                            saveBatch();
                            showError(data.error + ' 请重新选择原 ZIP 文件。', false);
                        } else { showError(data.error, true); }
                        return;
                    }
                    if (data.busy) { setTimeout(processNext, 1500); return; }
                    allResults = completedResults.concat(data.results || []);
                    dry = !!data.dry;
                    if (batch) { batch.done = data.done_models; batch.total = data.total_models; batch.dry = dry; saveBatch(); }
                    setProgress(data.done_models, data.total_models, data.current);
                    paint(!!data.done);
                    if (data.done) {
                        setPercent(100);
                        txt.textContent = '处理完成，共 ' + allResults.length + ' 个型号。';
                        completedResults = allResults;
                        if (batch) {
                            var entry = batch.entries[batch.index];
                            if (entry) { localStorage.removeItem('ew-product-upload:' + entry); }
                            batch.index++;
                            batch.job = '';
                            batch.upload = '';
                            batch.results = completedResults;
                            saveBatch();
                        }
                        fileIndex++;
                        jobId = '';
                        currentUpload = '';
                        if (fileIndex < files.length) { startFile(); return; }
                        btn.disabled = false;
                        if (resumeBtn) { resumeBtn.disabled = false; }
                        if (batch && batch.index < batch.entries.length) {
                            txt.textContent = '已完成 ' + batch.index + '/' + batch.entries.length + ' 个 ZIP。请重新选择剩余 ZIP 文件继续。';
                            resumeWrap.style.display = 'block';
                        } else { clearBatch(); }
                        return;
                    }
                    processNext();
                };
                xhr.onerror = function () {
                    showError('服务器连接中断。已处理 ' + allResults.length + ' 个型号，可点击「继续导入」从断点继续。', true);
                };
                xhr.ontimeout = function () { showError('服务器处理超时，任务仍保留。点击「继续导入」读取最新进度。', true); };
                xhr.send(fd);
            }

            function uploadKey(file) {
                return 'ew-product-upload:' + fileKey(file);
            }
            function request(data, blob, progress) {
                return new Promise(function (resolve, reject) {
                    var fd = new FormData();
                    fd.append('action', 'ew_product_import_upload');
                    fd.append('ew_import_nonce', nonce);
                    Object.keys(data).forEach(function (key) { fd.append(key, data[key]); });
                    if (blob) { fd.append('chunk', blob, 'part.bin'); }
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.getAttribute('action'), true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.timeout = data.op === 'finish' ? 180000 : 60000;
                    if (progress) { xhr.upload.onprogress = progress; }
                    xhr.onload = function () {
                        var result = parseJson(xhr);
                        if (xhr.status >= 200 && xhr.status < 300 && result && result.ok) { resolve(result); }
                        else {
                            var error = new Error(result && result.error ? result.error : '服务器返回异常（HTTP ' + xhr.status + '）');
                            error.expired = !!(result && result.expired);
                            reject(error);
                        }
                    };
                    xhr.onerror = function () { reject(new Error('网络连接中断')); };
                    xhr.ontimeout = function () { reject(new Error('上传超时')); };
                    xhr.send(fd);
                });
            }
            function retry(fn) {
                var attempt = 0;
                function run() {
                    return fn().catch(function (error) {
                        if (++attempt >= 5 || /权限|安全校验|无效|已过期/.test(error.message)) { throw error; }
                        return new Promise(function (resolve) { setTimeout(resolve, Math.min(10000, 700 * Math.pow(2, attempt))); }).then(run);
                    });
                }
                return run();
            }
            function uploadFile(file, saved) {
                var key = uploadKey(file), parts = Math.ceil(file.size / chunkSize);
                var session = saved && saved.upload ? request({ op: 'status', upload: saved.upload }).then(function (data) {
                    if (data.name !== file.name || data.size !== file.size || data.parts !== parts) { throw new Error('所选文件与上次上传的文件不一致。'); }
                    return data.job ? { upload: saved.upload, job: data.job, dry: data.dry } : { upload: saved.upload, received: data.received || [] };
                }).catch(function (error) {
                    if (!error.expired) { throw error; }
                    return request({ op: 'start', name: file.name, size: file.size, parts: parts });
                }) : request({ op: 'start', name: file.name, size: file.size, parts: parts });
                return session.then(function (data) {
                    currentUpload = data.upload;
                    if (batch) { batch.upload = currentUpload; saveBatch(); }
                    if (data.job) { return data; }
                    localStorage.setItem(key, JSON.stringify({ upload: currentUpload }));
                    var received = {}, completed = 0, pending = [], activeBytes = {};
                    (data.received || []).forEach(function (i) { received[i] = true; completed += Math.min(chunkSize, file.size - i * chunkSize); });
                    for (var i = 0; i < parts; i++) { if (!received[i]) { pending.push(i); } }
                    function paintUpload() {
                        var inFlight = Object.keys(activeBytes).reduce(function (n, k) { return n + activeBytes[k]; }, 0);
                        var loaded = Math.min(file.size, completed + inFlight);
                        setPercent(loaded * 100 / file.size);
                        txt.textContent = '上传 ' + (fileIndex + 1) + '/' + files.length + '：' + file.name + ' · ' + mb(loaded) + ' / ' + mb(file.size) + '（3 路并发）';
                    }
                    paintUpload();
                    function worker() {
                        var index = pending.shift();
                        if (index === undefined) { return Promise.resolve(); }
                        var from = index * chunkSize, to = Math.min(file.size, from + chunkSize);
                        return retry(function () {
                            return request({ op: 'part', upload: currentUpload, index: index }, file.slice(from, to), function (ev) {
                                activeBytes[index] = ev.loaded;
                                paintUpload();
                            });
                        }).then(function () {
                            delete activeBytes[index];
                            completed += to - from;
                            paintUpload();
                            return worker();
                        });
                    }
                    return Promise.all(Array.from({ length: Math.min(concurrency, pending.length) }, worker)).then(function () {
                        txt.textContent = '上传完成，服务器正在解压 ZIP…';
                        return retry(function () {
                            var fields = { op: 'finish', upload: currentUpload };
                            new FormData(form).forEach(function (value, name) {
                                if (name !== 'ew_import_zip[]' && name !== 'ew_import_nonce' && name !== 'action') { fields[name] = value; }
                            });
                            return request(fields);
                        });
                    });
                });
            }
            function startFile() {
                if (fileIndex >= files.length || uploading) { return; }
                var file = files[fileIndex], key = uploadKey(file), saved = null;
                try { saved = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) {}
                if (saved && saved.job) {
                    jobId = saved.job;
                    if (batch) { batch.job = jobId; batch.upload = saved.upload || ''; saveBatch(); }
                    txt.textContent = '继续处理 ' + file.name + '…';
                    processNext();
                    return;
                }
                uploading = true;
                uploadFile(file, saved).then(function (data) {
                    uploading = false;
                    jobId = data.job;
                    dry = !!data.dry;
                    localStorage.setItem(key, JSON.stringify({ upload: currentUpload, job: jobId }));
                    if (batch) { batch.job = jobId; batch.upload = currentUpload; batch.dry = dry; saveBatch(); }
                    setPercent(5);
                    processNext();
                }).catch(function (error) {
                    uploading = false;
                    showError('上传暂停：' + error.message + '。检查网络后点击「继续导入」；重新打开页面时，请重新选择同一 ZIP 文件。', true);
                });
            }
            form.addEventListener('submit', function (e) {
                if (!fileInput.files || !fileInput.files.length) { return; }
                e.preventDefault();
                var selected = Array.prototype.slice.call(fileInput.files);
                var keys = selected.map(fileKey), continuing = batch && !batch.job && batch.index < batch.entries.length;
                if (continuing) {
                    var remaining = batch.entries.slice(batch.index);
                    var fullMatch = keys.join('|') === batch.entries.join('|');
                    var remainingMatch = keys.join('|') === remaining.join('|');
                    if (!fullMatch && !remainingMatch) {
                        showError('当前有未完成的批次。请重新选择原来的全部 ZIP，或只选择尚未处理的 ZIP（顺序保持一致）。', false);
                        return;
                    }
                    files = selected;
                    fileIndex = fullMatch ? batch.index : 0;
                    if (remainingMatch) { batch.entries = remaining; batch.index = 0; }
                    completedResults = batch.results || [];
                } else {
                    files = selected;
                    fileIndex = 0;
                    completedResults = [];
                    batch = { entries: keys, index: 0, upload: '', job: '', results: [], done: 0, total: 0, dry: false };
                }
                saveBatch();
                jobId = '';
                allResults = completedResults.slice();
                dry = false;
                nonce = form.querySelector('[name="ew_import_nonce"]').value;
                resumeWrap.style.display = 'none';
                btn.disabled = true;
                out.innerHTML = '';
                bar.style.display = 'block';
                setPercent(0);
                startFile();
            });
            if (resumeBtn) {
                resumeBtn.addEventListener('click', function () {
                    resumeWrap.style.display = 'none';
                    if (jobId) { processNext(); }
                    else if (files.length) { startFile(); }
                    else { showError('请重新选择未完成的 ZIP 文件，然后点击「开始导入」。', false); }
                });
            }
            try { batch = JSON.parse(localStorage.getItem(batchKey) || 'null'); } catch (e) { batch = null; }
            if (batch && Array.isArray(batch.entries) && batch.index < batch.entries.length) {
                nonce = form.querySelector('[name="ew_import_nonce"]').value;
                completedResults = Array.isArray(batch.results) ? batch.results : [];
                allResults = completedResults.slice();
                dry = !!batch.dry;
                bar.style.display = 'block';
                if (batch.job) {
                    jobId = batch.job;
                    currentUpload = batch.upload || '';
                    btn.disabled = true;
                    txt.textContent = '正在恢复服务器导入任务…';
                    setProgress(batch.done || 0, batch.total || 0, '恢复中');
                    processNext();
                } else {
                    var pending = batch.entries[batch.index].split(':').slice(0, -2).join(':');
                    txt.textContent = '上传已暂停：请重新选择 ' + pending + ' 及剩余 ZIP，然后点击「开始导入」。已上传分片会自动续传。';
                    resumeWrap.style.display = 'block';
                    out.innerHTML = tableHtml();
                }
            }
        })();
        </script>

        <?php if ( ! empty( $rows ) ) : ?>
            <h2>本次导入结果</h2>
            <table class="widefat striped" style="max-width:960px">
                <thead><tr><th>型号</th><th>状态</th><th>分类</th><th>图片</th><th>链接</th></tr></thead>
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
                        <td>
                            <?php if ( ! empty( $r['cat'] ) ) : ?>
                                <?php echo esc_html( $r['cat'] ); ?>
                            <?php else : ?>
                                <span style="color:#d63638">未识别</span>
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
