(function ($) {
    'use strict';
    function parse(value) { try { var data = JSON.parse(value); return Array.isArray(data) ? data : []; } catch (_) { return []; } }
    function notify($input, data) { $input.val(JSON.stringify(data)).trigger('change'); }
    function refreshImages($section) {
        var $list = $section.find('.ew-editor-image-list');
        var ids = $list.children('li').map(function () { return Number($(this).attr('data-id')); }).get();
        notify($section.find('.ew-editor-value'), ids);
        $section.toggleClass('is-empty', ids.length === 0);
        if ($list.attr('data-gallery') === '1') {
            $list.children().each(function (index) {
                var $item = $(this), $badge = $item.find('.ew-primary-badge');
                if (index === 0 && !$badge.length) $('<span class="ew-primary-badge">主图</span>').appendTo($item);
                if (index > 0) $badge.remove();
            });
        }
    }
    function syncSpecs($section) {
        var rows = $section.find('.ew-spec-row').map(function () { return { name: $(this).find('.ew-spec-name').val(), value: $(this).find('.ew-spec-value').val() }; }).get();
        notify($section.find('.ew-editor-spec-value'), rows);
        $section.toggleClass('is-empty', rows.length === 0);
        $section.find('.ew-spec-index').each(function (index) { $(this).text(index + 1); });
    }
    function specRow(row) {
        var $row = $('<div class="ew-spec-row">');
        $('<span class="dashicons dashicons-menu ew-spec-handle" aria-label="拖动排序"></span>').appendTo($row);
        $('<span class="ew-spec-index"></span>').appendTo($row);
        $('<input type="text" class="ew-spec-name" aria-label="参数名称" placeholder="例如：直流电压">').val(row.name || '').appendTo($row);
        $('<input type="text" class="ew-spec-value" aria-label="参数值" placeholder="例如：600 V">').val(row.value || '').appendTo($row);
        $('<button type="button" class="ew-spec-remove" aria-label="删除参数"><span class="dashicons dashicons-trash"></span></button>').appendTo($row);
        return $row;
    }
    function initialize(root) {
        var $root = root ? $(root) : $(document);
        $root.find('.ew-editor-images').each(function () {
            var $section = $(this); if ($section.data('initialized')) return; $section.data('initialized', true);
            $section.find('.ew-editor-image-list').sortable({ items: '> li', handle: '.ew-drag-handle', placeholder: 'ew-image-placeholder', update: function () { refreshImages($section); } });
            refreshImages($section);
        });
        $root.find('.ew-editor-specs').each(function () {
            var $section = $(this); if ($section.data('initialized')) return; $section.data('initialized', true);
            parse($section.find('.ew-editor-spec-value').val()).forEach(function (row) { if (row && typeof row === 'object') $section.find('.ew-editor-spec-rows').append(specRow(row)); });
            $section.find('.ew-editor-spec-rows').sortable({ items: '> .ew-spec-row', handle: '.ew-spec-handle', update: function () { syncSpecs($section); } });
            syncSpecs($section);
        });
    }
    $(function () {
        initialize();
        if ($.fn.selectWoo) $('#ew-product-picker').selectWoo({ placeholder: '输入名称或 ID 搜索商品', allowClear: true, width: 'resolve' });
        $('#ew-product-picker').on('change', function () { if ($(this).val()) $(this).closest('form').trigger('submit'); });
    });
    $(document).on('woocommerce_variations_loaded woocommerce_variations_added', function () { initialize(); });
    $(document).on('click', '.ew-variation-editor > summary, .ew-legacy-editor > summary', function () { initialize($(this).parent()); });
    $(document).on('click', '.ew-language-tab', function () {
        var language = $(this).data('target'), $editor = $(this).closest('.ew-model-editor');
        $(this).addClass('is-active').siblings().removeClass('is-active');
        $editor.find('.ew-language-panel').removeClass('is-active').filter('[data-language="' + language + '"]').addClass('is-active');
    });
    $(document).on('click', '.ew-editor-add-images', function () {
        var $section = $(this).closest('.ew-editor-images');
        var frame = wp.media({ title: '选择商品图片（可多选）', button: { text: '添加所选图片' }, library: { type: 'image' }, multiple: true });
        frame.on('select', function () {
            var existing = $section.find('.ew-editor-image-list > li').map(function () { return Number($(this).data('id')); }).get();
            frame.state().get('selection').each(function (attachment) {
                var item = attachment.toJSON(); if (existing.indexOf(item.id) !== -1) return; existing.push(item.id);
                var src = item.sizes && item.sizes.medium ? item.sizes.medium.url : (item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail.url : item.url);
                var $li = $('<li class="ew-image-card">').attr('data-id', item.id);
                $('<span class="dashicons dashicons-move ew-drag-handle" aria-hidden="true"></span>').appendTo($li); $('<img alt="">').attr('src', src).appendTo($li);
                var $footer = $('<div class="ew-image-card-footer">').appendTo($li); $('<span>').text('#' + item.id).appendTo($footer);
                $('<button type="button" class="ew-image-remove"><span class="dashicons dashicons-trash"></span>移除</button>').appendTo($footer); $section.find('.ew-editor-image-list').append($li);
            });
            refreshImages($section);
        }); frame.open();
    });
    $(document).on('click', '.ew-image-remove', function () { var $section = $(this).closest('.ew-editor-images'); $(this).closest('li').remove(); refreshImages($section); });
    $(document).on('click', '.ew-editor-add-spec', function () { var $section = $(this).closest('.ew-editor-specs'); $section.find('.ew-editor-spec-rows').append(specRow({})); syncSpecs($section); $section.find('.ew-spec-row:last .ew-spec-name').trigger('focus'); });
    $(document).on('input', '.ew-spec-name, .ew-spec-value', function () { syncSpecs($(this).closest('.ew-editor-specs')); });
    $(document).on('click', '.ew-spec-remove', function () { var $section = $(this).closest('.ew-editor-specs'); $(this).closest('.ew-spec-row').remove(); syncSpecs($section); });
}(jQuery));
