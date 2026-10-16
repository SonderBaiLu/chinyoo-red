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
    function setupDropzone() {
        var $dz = $('#ew-dropzone');
        if (!$dz.length) return;
        var $input = $dz.find('.ew-dropzone-input');
        var $preview = $('.ew-dropzone-preview');
        var $count = $('.ew-dropzone-count');
        function slotFor(name) {
            name = (name || '').replace(/\\/g, '/').split('/').pop();
            if (name.indexOf('白底图') !== -1) return '主图';
            if (name.indexOf('详细参数图') !== -1) return '详情';
            var m = name.match(/展示图\s*(\d+)?/);
            if (m) return '画廊' + (m[1] || '');
            return '画廊';
        }
        function render() {
            var files = $input[0] ? Array.prototype.slice.call($input[0].files) : [];
            $preview.empty();
            var n = 0;
            files.forEach(function (f) {
                if (!/\.(jpe?g|png|webp|gif)$/i.test(f.name)) return;
                n++;
                var $li = $('<li class="ew-dropzone-item">');
                $('<span class="ew-dropzone-slot"></span>').text(slotFor(f.name)).appendTo($li);
                $('<span class="ew-dropzone-name"></span>').text(f.name).appendTo($li);
                $preview.append($li);
            });
            $count.text(n);
            $preview.toggle(n > 0);
        }
        $input.on('change', render);
        $dz.on('dragenter dragover', function (e) { e.preventDefault(); e.stopPropagation(); $dz.addClass('is-dragover'); });
        $dz.on('dragleave drop', function (e) { e.preventDefault(); e.stopPropagation(); $dz.removeClass('is-dragover'); });
        $dz.on('drop', function (e) {
            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length) { $input[0].files = dt.files; render(); }
        });
    }
    function setupGridSearch() {
        var $search = $('#ew-product-search');
        if (!$search.length) return;
        var $cards = $('#ew-product-grid .ew-product-card');
        var $count = $('#ew-product-count');
        function filter() {
            var q = $.trim($search.val()).toLowerCase();
            var shown = 0;
            $cards.each(function () {
                var $c = $(this);
                var hay = ((($c.data('name')) || '') + ' ' + ((($c.data('model')) || ''))).toLowerCase();
                var match = !q || hay.indexOf(q) !== -1;
                $c.toggle(match);
                if (match) shown++;
            });
            if ($count.length) $count.text(shown + ' / ' + $cards.length);
        }
        $search.on('input', filter);
        filter();
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
        setupDropzone();
        setupGridSearch();
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
