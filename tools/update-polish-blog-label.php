<?php
// CLI-only one-time Polylang string update; run from the WordPress installation.
if ( PHP_SAPI !== 'cli' ) exit(1);
require dirname(__DIR__, 4) . '/wp-load.php';
$language = get_term_by('slug', 'pl', 'language');
if (!$language || is_wp_error($language)) throw new RuntimeException('Polish language not found');
$key = '_pll_strings_translations';
$rows = get_term_meta($language->term_id, $key, true);
if (!is_array($rows)) throw new RuntimeException('Polish string table not found');
$changed = false;
$found = false;
foreach ($rows as &$row) {
    if (!isset($row[0]) || $row[0] !== 'Blogs') continue;
    $found = true;
    if (isset($row[1]) && $row[1] === 'Artykuły') break;
    if (isset($row[1]) && !in_array($row[1], ['', 'Blogs', 'Blog'], true)) throw new RuntimeException('Custom Blogs translation found');
    if (is_dir('/root') && is_writable('/root')) file_put_contents('/root/chinyoo-pl-strings-before-20261113.json', wp_json_encode($rows, JSON_UNESCAPED_UNICODE));
    $row[1] = 'Artykuły';
    $changed = true;
    break;
}
unset($row);
if (!$found) throw new RuntimeException('Blogs string not found');
if ($changed) update_term_meta($language->term_id, $key, $rows);
echo $changed ? "Polish Blogs label updated\n" : "Polish Blogs label already current\n";
