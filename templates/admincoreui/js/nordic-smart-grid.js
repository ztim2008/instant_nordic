/**
 * Nordic Smart Grid v1.0
 * Drag-resize для колонок в admin/widgets (только шаблон nordic).
 * Использует data-атрибуты из nordic/controllers/admin/widgets_scheme.tpl.php.
 */
(function ($) {
    'use strict';

    var GRID_COLS = 12;
    var MIN_WIDTH = 1;

    /* ─── Утилиты ────────────────────────────────────────────────── */

    /**
     * Разобрать первое col-{bp}-N из строки класса.
     * Возвращает { bp: 'xl', n: 6 } или null.
     */
    function parseColClass(classStr) {
        if (!classStr) { return null; }
        var m = classStr.match(/col-(xl|lg|md|sm)-(\d+)/);
        if (m) { return { bp: m[1], n: parseInt(m[1], 10) > 0 ? parseInt(m[2], 10) : parseInt(m[2], 10) }; }
        // col-N (без брейкпоинта)
        m = classStr.match(/\bcol-(\d+)\b/);
        if (m) { return { bp: null, n: parseInt(m[1], 10) }; }
        return null;
    }

    /**
     * Заменить/добавить col-{bp}-N в строке класса.
     */
    function replaceColWidth(classStr, bp, n) {
        var re, newCls;
        if (bp) {
            re = new RegExp('col-' + bp + '-\\d+', 'g');
            if (re.test(classStr)) {
                return classStr.replace(re, 'col-' + bp + '-' + n);
            }
            return classStr + ' col-' + bp + '-' + n;
        }
        // col-N без брейкпоинта
        re = /\bcol-\d+\b/g;
        newCls = classStr.replace(re, 'col-' + n);
        return newCls !== classStr ? newCls : classStr + ' col-' + n;
    }

    /**
     * Применить визуальную ширину к колонке (CSS flex).
     */
    function applyVisualWidth($col, n) {
        $col.css({
            'flex': '0 0 ' + (n / GRID_COLS * 100).toFixed(4) + '%',
            'max-width': (n / GRID_COLS * 100).toFixed(4) + '%'
        });
    }

    /**
     * Сбросить inline-width (вернуть контроль Bootstrap-классам).
     */
    function resetVisualWidth($col) {
        $col.css({ 'flex': '', 'max-width': '' });
    }

    /* ─── Инициализация handle'ов ────────────────────────────────── */

    function insertHandles($wrap) {
        $wrap.children('.nordic-col-resizer').remove();
        var $cols = $wrap.children('.widgets-layout-scheme-col');
        if ($cols.length < 2) { return; }
        $cols.each(function (i) {
            if (i === $cols.length - 1) { return; }
            $(this).after(
                '<div class="nordic-col-resizer" title="Тянуть для изменения ширины">' +
                '<div class="nordic-col-resizer__line"></div>' +
                '</div>'
            );
        });
    }

    function initHandles() {
        if (!$('#cp-widgets-layout').length) { return; }
        $('.widgets-layout-scheme-col-wrap').each(function () {
            insertHandles($(this));
        });
    }

    /* ─── Drag-события ───────────────────────────────────────────── */

    var drag = {
        active: false,
        startX: 0,
        $wrap: null,
        $left: null,
        $right: null,
        leftN: 0,
        rightN: 0,
        bp: 'xl',
        $tooltip: null
    };

    function onMouseDown(e) {
        var $handle = $(e.currentTarget);
        var $wrap   = $handle.closest('.widgets-layout-scheme-col-wrap');
        var $left   = $handle.prev('.widgets-layout-scheme-col');
        var $right  = $handle.next('.widgets-layout-scheme-col');

        if (!$left.length || !$right.length) { return; }

        var leftParsed  = parseColClass($left.data('col-class'));
        var rightParsed = parseColClass($right.data('col-class'));

        if (!leftParsed || !rightParsed) {
            /* Колонки без явной ширины — делаем равный сплит */
            var eqN = Math.floor(GRID_COLS / 2);
            leftParsed  = { bp: 'xl', n: eqN };
            rightParsed = { bp: 'xl', n: GRID_COLS - eqN };
        }

        drag.active  = true;
        drag.startX  = e.pageX;
        drag.$wrap   = $wrap;
        drag.$left   = $left;
        drag.$right  = $right;
        drag.leftN   = leftParsed.n;
        drag.rightN  = rightParsed.n;
        drag.bp      = leftParsed.bp || rightParsed.bp || 'xl';

        drag.$tooltip = $(
            '<div class="nordic-col-resize-tooltip">' +
            drag.leftN + ' / ' + drag.rightN +
            '</div>'
        );
        $handle.after(drag.$tooltip);

        $handle.addClass('is-dragging');
        $('body').addClass('nordic-resizing');
        e.preventDefault();
    }

    function onMouseMove(e) {
        if (!drag.active) { return; }

        var wrapW    = drag.$wrap.width();
        var unitPx   = wrapW / GRID_COLS;
        var deltaCol = Math.round((e.pageX - drag.startX) / unitPx);

        var total    = drag.leftN + drag.rightN;
        var newLeft  = Math.max(MIN_WIDTH, Math.min(total - MIN_WIDTH, drag.leftN + deltaCol));
        var newRight = total - newLeft;

        applyVisualWidth(drag.$left,  newLeft);
        applyVisualWidth(drag.$right, newRight);

        if (drag.$tooltip) {
            drag.$tooltip.text(newLeft + ' / ' + newRight);
        }

        drag.$left.data('new-n',  newLeft);
        drag.$right.data('new-n', newRight);
    }

    function onMouseUp() {
        if (!drag.active) { return; }
        drag.active = false;

        $('body').removeClass('nordic-resizing');
        $('.nordic-col-resizer.is-dragging').removeClass('is-dragging');

        var newLeft  = drag.$left.data('new-n');
        var newRight = drag.$right.data('new-n');

        if (drag.$tooltip) { drag.$tooltip.remove(); drag.$tooltip = null; }

        if (newLeft === undefined || newRight === undefined) { return; }

        /* Сбрасываем temp data */
        drag.$left.removeData('new-n');
        drag.$right.removeData('new-n');

        /* Сохраняем оба столбца */
        saveCols([
            { $col: drag.$left,  newN: newLeft  },
            { $col: drag.$right, newN: newRight }
        ], drag.bp);
    }

    /* ─── AJAX сохранение ────────────────────────────────────────── */

    function saveCols(items, bp) {
        var promises = items.map(function (item) {
            return saveOneCol(item.$col, item.newN, bp);
        });

        $.when.apply($, promises).done(function () {
            /* После сохранения обоих — перезагружаем схему через admin-scheme.js
               если есть метод, иначе перезагружаем страницу мягко */
            if (typeof icms !== 'undefined' && icms.adminWidgets && icms.adminWidgets.reloadScheme) {
                icms.adminWidgets.reloadScheme();
            }
            /* Подсветка успеха */
            items.forEach(function (item) { flashSuccess(item.$col); });
        }).fail(function () {
            /* Откатываем визуальную ширину */
            items.forEach(function (item) { resetVisualWidth(item.$col); });
            alert('Ошибка сохранения. Попробуйте ещё раз.');
        });
    }

    function saveOneCol($col, newN, bp) {
        var currentClass = $col.data('col-class') || '';
        var newClass     = replaceColWidth(currentClass, bp, newN);

        var editUrl  = $col.data('col-edit-url');
        var csrfTok  = window.nordicCsrfToken || '';

        var postData = {
            title:      $col.data('col-title'),
            name:       $col.data('col-name'),
            row_id:     $col.data('col-row-id'),
            type:       $col.data('col-type') || 'typical',
            tag:        $col.data('col-tag')  || 'div',
            class:      newClass,
            csrf_token: csrfTok
        };

        return $.ajax({
            url:      editUrl,
            type:     'POST',
            data:     postData,
            dataType: 'json'
        }).done(function (result) {
            if (result && !result.errors) {
                /* Обновляем data-атрибут */
                $col.data('col-class', newClass);
                /* Обновляем классы в DOM */
                var classes = $col.attr('class')
                    .replace(/col-(?:xl|lg|md|sm)-\d+/g, '')
                    .replace(/\bcol-\d+\b/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
                $col.attr('class', classes + ' ' + newClass.trim());
                resetVisualWidth($col);
            } else if (result && result.errors) {
                resetVisualWidth($col);
                console.warn('nordic-smart-grid: ошибка сохранения', result.errors);
            }
        });
    }

    function flashSuccess($col) {
        $col.addClass('nordic-saved');
        setTimeout(function () { $col.removeClass('nordic-saved'); }, 1200);
    }

    /* ─── Навешивание событий ────────────────────────────────────── */

    function bindEvents() {
        $(document).on('mousedown', '#cp-widgets-layout .nordic-col-resizer', onMouseDown);
        $(document).on('mousemove', onMouseMove);
        $(document).on('mouseup',   onMouseUp);

        /* После изменения страницы в дереве — переинициализируемся */
        $(document).on('click', '#treeData li', function () {
            setTimeout(initHandles, 600);
        });

        /* После AJAX-добавления ряда / колонки */
        $(document).on('icms.widgets.scheme.updated', function () {
            setTimeout(initHandles, 300);
        });
    }

    /* ─── Точка входа ────────────────────────────────────────────── */
    $(function () {
        initHandles();
        bindEvents();
    });

}(jQuery));
