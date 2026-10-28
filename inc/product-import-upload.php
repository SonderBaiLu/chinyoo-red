<?php
/* Resumable ZIP transfer for the product importer. Each request carries one 1 MiB part. */

function ew_import_upload_dir( $id = '' ) {
    $base = ew_import_temp_base();
    if ( '' === $base ) { return ''; }
    $base = trailingslashit( $base ) . 'transfers';
    if ( ! is_dir( $base ) ) { @mkdir( $base, 0755, true ); }
    if ( ! is_dir( $base ) || ! is_writable( $base ) ) { return ''; }
    return $id ? $base . '/' . $id : $base;
}

function ew_import_upload_manifest( $id ) {
    if ( ! preg_match( '/^[a-z0-9]{24}$/', $id ) ) { return false; }
    $dir = ew_import_upload_dir( $id );
    $raw = $dir ? @file_get_contents( $dir . '/manifest.json' ) : false;
    $meta = $raw ? json_decode( $raw, true ) : false;
    return is_array( $meta ) && (int) $meta['user'] === get_current_user_id() ? $meta : false;
}

function ew_import_upload_reply( $data ) { ew_import_json_exit( $data ); }

function ew_handle_product_import_upload() {
    if ( ! current_user_can( 'edit_products' ) || ! wp_verify_nonce( isset( $_POST['ew_import_nonce'] ) ? $_POST['ew_import_nonce'] : '', 'ew_product_import' ) ) {
        ew_import_upload_reply( array( 'ok' => false, 'error' => '权限或安全校验失败，请刷新页面。' ) );
    }
    $op = isset( $_POST['op'] ) ? sanitize_key( $_POST['op'] ) : '';
    $id = isset( $_POST['upload'] ) ? sanitize_key( $_POST['upload'] ) : '';
    if ( 'start' === $op ) {
        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_basename( wp_unslash( $_POST['name'] ) ) ) : '';
        $size = isset( $_POST['size'] ) ? (int) $_POST['size'] : 0;
        $parts = isset( $_POST['parts'] ) ? (int) $_POST['parts'] : 0;
        $chunk = 1024 * 1024;
        if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) || $size < 1 || $parts !== (int) ceil( $size / $chunk ) || $parts > 10000 ) {
            ew_import_upload_reply( array( 'ok' => false, 'error' => 'ZIP 文件信息无效或文件过大。' ) );
        }
        $base = ew_import_upload_dir();
        if ( ! $base ) { ew_import_upload_reply( array( 'ok' => false, 'error' => '无法创建上传临时目录。' ) ); }
        foreach ( (array) glob( $base . '/*' ) as $old ) {
            if ( is_dir( $old ) && @filemtime( $old ) < time() - DAY_IN_SECONDS ) { ew_import_rmdir( $old ); }
        }
        do { $id = strtolower( wp_generate_password( 24, false, false ) ); $dir = $base . '/' . $id; } while ( file_exists( $dir ) );
        if ( ! @mkdir( $dir, 0755 ) ) { ew_import_upload_reply( array( 'ok' => false, 'error' => '无法创建上传任务。' ) ); }
        @file_put_contents( $dir . '/index.html', '' );
        @file_put_contents( $dir . '/.htaccess', 'Deny from all' . "\n" );
        $meta = array( 'user' => get_current_user_id(), 'name' => $name, 'size' => $size, 'parts' => $parts, 'created' => time() );
        if ( false === @file_put_contents( $dir . '/manifest.json', wp_json_encode( $meta ) ) ) {
            ew_import_rmdir( $dir );
            ew_import_upload_reply( array( 'ok' => false, 'error' => '无法保存上传任务。' ) );
        }
        ew_import_upload_reply( array( 'ok' => true, 'upload' => $id, 'received' => array() ) );
    }
    $meta = ew_import_upload_manifest( $id );
    if ( ! $meta ) { ew_import_upload_reply( array( 'ok' => false, 'expired' => true, 'error' => '上传任务已过期，请重新开始。' ) ); }
    $dir = ew_import_upload_dir( $id );
    if ( 'status' === $op ) {
        if ( is_file( $dir . '/receipt.json' ) ) {
            $result = json_decode( file_get_contents( $dir . '/receipt.json' ), true );
            if ( is_array( $result ) && ! empty( $result['job'] ) ) {
                $result['name'] = $meta['name'];
                $result['size'] = $meta['size'];
                $result['parts'] = $meta['parts'];
                ew_import_upload_reply( $result );
            }
        }
        $received = array();
        for ( $i = 0; $i < $meta['parts']; $i++ ) {
            $expected = min( 1024 * 1024, $meta['size'] - $i * 1024 * 1024 );
            if ( is_file( $dir . '/' . $i . '.part' ) && filesize( $dir . '/' . $i . '.part' ) === $expected ) { $received[] = $i; }
        }
        ew_import_upload_reply( array( 'ok' => true, 'received' => $received, 'name' => $meta['name'], 'size' => $meta['size'], 'parts' => $meta['parts'] ) );
    }
    if ( 'part' === $op ) {
        $i = isset( $_POST['index'] ) ? (int) $_POST['index'] : -1;
        $expected = min( 1024 * 1024, $meta['size'] - $i * 1024 * 1024 );
        $file = isset( $_FILES['chunk'] ) ? $_FILES['chunk'] : null;
        if ( $i < 0 || $i >= $meta['parts'] || ! $file || UPLOAD_ERR_OK !== $file['error'] || $expected < 1 || (int) $file['size'] !== $expected || ! is_uploaded_file( $file['tmp_name'] ) ) {
            ew_import_upload_reply( array( 'ok' => false, 'error' => '分片无效或上传未完成。' ) );
        }
        $tmp = $dir . '/' . $i . '.' . wp_generate_password( 8, false, false ) . '.tmp';
        if ( ! @move_uploaded_file( $file['tmp_name'], $tmp ) || ! @rename( $tmp, $dir . '/' . $i . '.part' ) ) {
            @unlink( $tmp );
            ew_import_upload_reply( array( 'ok' => false, 'error' => '服务器保存分片失败。' ) );
        }
        ew_import_upload_reply( array( 'ok' => true, 'index' => $i ) );
    }
    if ( 'finish' === $op ) {
        @set_time_limit( 0 );
        @ini_set( 'memory_limit', '512M' );
        if ( ! class_exists( 'ZipArchive' ) ) { ew_import_upload_reply( array( 'ok' => false, 'error' => '服务器缺少 ZipArchive 扩展。' ) ); }
        $lock = @fopen( $dir . '/finish.lock', 'c' );
        if ( ! $lock || ! flock( $lock, LOCK_EX ) ) { ew_import_upload_reply( array( 'ok' => false, 'error' => '无法锁定上传任务。' ) ); }
        $receipt = $dir . '/receipt.json';
        if ( is_file( $receipt ) ) {
            $result = json_decode( file_get_contents( $receipt ), true );
            ew_import_upload_reply( $result );
        }
        $assembled = $dir . '/complete.zip';
        $out = @fopen( $assembled . '.tmp', 'wb' );
        if ( ! $out ) { ew_import_upload_reply( array( 'ok' => false, 'error' => '无法合并 ZIP 文件。' ) ); }
        for ( $i = 0; $i < $meta['parts']; $i++ ) {
            $part = $dir . '/' . $i . '.part';
            $expected = min( 1024 * 1024, $meta['size'] - $i * 1024 * 1024 );
            if ( ! is_file( $part ) || filesize( $part ) !== $expected ) { fclose( $out ); @unlink( $assembled . '.tmp' ); ew_import_upload_reply( array( 'ok' => false, 'error' => '仍有缺失分片，请继续上传。' ) ); }
            $in = fopen( $part, 'rb' );
            $copied = $in ? stream_copy_to_stream( $in, $out ) : false;
            if ( $in ) { fclose( $in ); }
            if ( $copied !== $expected ) { fclose( $out ); @unlink( $assembled . '.tmp' ); ew_import_upload_reply( array( 'ok' => false, 'error' => '合并 ZIP 文件失败。' ) ); }
        }
        fclose( $out );
        if ( ! @rename( $assembled . '.tmp', $assembled ) ) { ew_import_upload_reply( array( 'ok' => false, 'error' => '无法保存完整 ZIP。' ) ); }
        $zip = new ZipArchive();
        if ( true !== $zip->open( $assembled ) ) { @unlink( $assembled ); ew_import_upload_reply( array( 'ok' => false, 'error' => 'ZIP 文件损坏，请重新选择文件上传。' ) ); }
        $zip->close();
        $options = array(
            'cat_id' => isset( $_POST['ew_cat_id'] ) ? absint( $_POST['ew_cat_id'] ) : 0,
            'cat_auto' => ! empty( $_POST['ew_cat_auto'] ), 'sync_langs' => ! empty( $_POST['ew_sync_langs'] ),
            'status' => isset( $_POST['ew_status'] ) && 'draft' === $_POST['ew_status'] ? 'draft' : 'publish',
            'hot' => ! empty( $_POST['ew_hot'] ), 'dry' => ! empty( $_POST['ew_dry'] ),
            'replace' => isset( $_POST['ew_duplicate'] ) && 'replace' === $_POST['ew_duplicate'],
        );
        if ( isset( $_POST['ew_cat_map'] ) && is_string( $_POST['ew_cat_map'] ) ) { update_option( 'ew_import_category_map', wp_unslash( $_POST['ew_cat_map'] ), false ); }
        $job = ew_import_create_job( array( array( 'tmp' => $assembled, 'name' => $meta['name'] ) ), $options );
        if ( is_wp_error( $job ) ) { ew_import_upload_reply( array( 'ok' => false, 'error' => $job->get_error_message() ) ); }
        $result = array( 'ok' => true, 'job' => $job['id'], 'total_models' => (int) $job['total_models'], 'dry' => ! empty( $options['dry'] ) );
        @file_put_contents( $receipt, wp_json_encode( $result ) );
        foreach ( (array) glob( $dir . '/*.part' ) as $part ) { @unlink( $part ); }
        @unlink( $assembled );
        ew_import_upload_reply( $result );
    }
    ew_import_upload_reply( array( 'ok' => false, 'error' => '未知上传操作。' ) );
}
add_action( 'admin_post_ew_product_import_upload', 'ew_handle_product_import_upload' );
