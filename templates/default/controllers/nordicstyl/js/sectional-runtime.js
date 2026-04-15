(function () {
    function init() {
        var config = window.NORDICSTYL_SECTIONAL_PAGE || null;
        if (!config || !config.enabled || !config.schema || !Array.isArray(config.schema.sections) || !config.schema.sections.length) {
            return;
        }

        var mount = document.querySelector('[data-nordic-id="shell-content-body-runtime"][data-nordic-role="content.body"]') || document.querySelector('[data-nordic-role="content.body"]');
        if (!mount || mount.getAttribute('data-nordic-sectional-mounted') === '1') {
            return;
        }

        if (mount.querySelector('[data-nordic-sectional-root="1"]')) {
            mount.setAttribute('data-nordic-sectional-mounted', '1');
            return;
        }

        mount.setAttribute('data-nordic-sectional-mounted', '1');
        mount.innerHTML = '';

        var page = document.createElement('div');
        page.className = 'nordic-section-page';

        config.schema.sections.forEach(function (section, index) {
            page.appendChild(renderSection(section, index));
        });

        mount.appendChild(page);
    }

    function renderSection(section, index) {
        var element = document.createElement('section');
        var title = String(section.title || ('Section ' + (index + 1)));
        var columns = Array.isArray(section.columns) && section.columns.length ? section.columns : [{ id: 'col_1', width: 12 }];
        var blocks = Array.isArray(section.blocks) ? section.blocks : [];
        var style = section.style || {};
        var grid = document.createElement('div');

        element.className = 'nordic-section';
        element.style.setProperty('--section-pad-top', clampNumber(style.padding_top, 0, 240, 48) + 'px');
        element.style.setProperty('--section-pad-bottom', clampNumber(style.padding_bottom, 0, 240, 48) + 'px');
        if (style.background) {
            element.style.background = String(style.background);
        }

        element.setAttribute('data-nordic-section-title', title);
        grid.className = 'nordic-section__grid';
        grid.style.gridTemplateColumns = columns.map(function (column) {
            return clampNumber(column.width, 1, 12, 12) + 'fr';
        }).join(' ');

        columns.forEach(function (column) {
            var stack = document.createElement('div');
            stack.className = 'nordic-section__column-stack';

            blocks.filter(function (block) {
                return String(block.column_id || '') === String(column.id || '');
            }).forEach(function (block) {
                stack.appendChild(renderBlock(block));
            });

            grid.appendChild(stack);
        });

        element.appendChild(grid);

        return element;
    }

    function renderBlock(block) {
        var element = document.createElement('article');
        var props = block && block.props ? block.props : {};
        var style = block && block.style ? block.style : {};
        var widget = normalizeWidgetType(block && block.widget ? block.widget : 'text');

        element.className = 'nordic-section__block nordic-section__block--' + widget;

        element.style.setProperty('--block-font-size', clampNumber(style.font_size, 10, 120, 18) + 'px');
        element.style.setProperty('--block-title-size', clampNumber(style.title_size, 20, 96, 40) + 'px');
        element.style.setProperty('--block-color', String(style.color || '#111111'));
        element.style.setProperty('--block-align', normalizeTextAlign(style.text_align));
        element.style.setProperty('--block-padding', clampNumber(style.padding, 0, 240, 0) + 'px');
        element.style.setProperty('--block-accent-color', String(style.accent_color || '#155e63'));
        if (style.background) {
            element.style.background = String(style.background);
        }

        if (widget === 'cta') {
            element.appendChild(renderCtaBlock(props));
        } else if (widget === 'news_grid') {
            element.appendChild(renderNewsGridBlock(props, style));
        } else if (widget === 'hero') {
            element.appendChild(renderHeroBlock(props, style));
        } else {
            element.appendChild(renderTextBlock(props));
        }

        return element;
    }

    function renderTextBlock(props) {
        var content = document.createElement('div');

        content.className = 'nordic-section__block-content';
        content.textContent = String(props.text || '');

        return content;
    }

    function renderCtaBlock(props) {
        var wrap = document.createElement('div');
        var title = document.createElement('div');
        var text = document.createElement('div');

        wrap.className = 'nordic-section__cta';

        if (String(props.eyebrow || '') !== '') {
            var eyebrow = document.createElement('div');
            eyebrow.className = 'nordic-section__cta-eyebrow';
            eyebrow.textContent = String(props.eyebrow || '');
            wrap.appendChild(eyebrow);
        }

        title.className = 'nordic-section__cta-title';
        title.textContent = String(props.title || '');
        wrap.appendChild(title);

        text.className = 'nordic-section__cta-text';
        text.textContent = String(props.text || '');
        wrap.appendChild(text);

        if (String(props.button_label || '') !== '') {
            var button;
            var buttonUrl = String(props.button_url || '');

            if (buttonUrl !== '') {
                button = document.createElement('a');
                button.href = buttonUrl;
            } else {
                button = document.createElement('span');
            }

            button.className = 'nordic-section__cta-button';
            button.textContent = String(props.button_label || '');
            wrap.appendChild(button);
        }

        return wrap;
    }

    function escOrEmpty(val) {
        return String(val || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function renderNewsGridBlock(props, style) {
        var wrap = document.createElement('div');
        var items = Array.isArray(props._items) ? props._items : [];
        var cols = clampNumber(props.columns, 1, 6, 3);

        wrap.className = 'nordic-section__news-grid';
        wrap.style.gridTemplateColumns = 'repeat(' + cols + ', 1fr)';

        if (!items.length) {
            var empty = document.createElement('p');
            empty.className = 'nordic-section__news-empty';
            empty.textContent = 'Материалы не найдены.';
            wrap.appendChild(empty);
            return wrap;
        }

        items.forEach(function (item) {
            var card = document.createElement('article');
            card.className = 'nordic-section__news-card';

            var html = '';

            if (item._image) {
                html += '<div class="nordic-section__news-card-img"><img src="' + escOrEmpty(item._image) + '" alt="' + escOrEmpty(item.title) + '" loading="lazy"></div>';
            }

            html += '<div class="nordic-section__news-card-body">';

            if (item.title) {
                html += '<div class="nordic-section__news-card-title">';
                html += item._url
                    ? '<a href="' + escOrEmpty(item._url) + '">' + escOrEmpty(item.title) + '</a>'
                    : escOrEmpty(item.title);
                html += '</div>';
            }

            if (item.date_pub) {
                html += '<div class="nordic-section__news-card-date">' + escOrEmpty(String(item.date_pub)) + '</div>';
            }

            html += '</div>';
            card.innerHTML = html;
            wrap.appendChild(card);
        });

        return wrap;
    }

    function renderHeroBlock(props, style) {
        var wrap = document.createElement('div');
        var height = clampNumber(props.hero_height, 200, 800, 400);

        wrap.className = 'nordic-section__hero';
        wrap.style.minHeight = height + 'px';

        var inner = '';

        if (props.eyebrow) {
            inner += '<div class="nordic-section__hero-eyebrow">' + escOrEmpty(props.eyebrow) + '</div>';
        }

        if (props.title) {
            inner += '<h1 class="nordic-section__hero-title">' + escOrEmpty(props.title) + '</h1>';
        }

        if (props.text) {
            inner += '<p class="nordic-section__hero-text">' + escOrEmpty(props.text) + '</p>';
        }

        if (props.button_label) {
            if (props.button_url) {
                inner += '<a href="' + escOrEmpty(props.button_url) + '" class="nordic-section__hero-btn">' + escOrEmpty(props.button_label) + '</a>';
            } else {
                inner += '<span class="nordic-section__hero-btn">' + escOrEmpty(props.button_label) + '</span>';
            }
        }

        wrap.innerHTML = inner;
        return wrap;
    }

    function normalizeTextAlign(value) {
        value = String(value || '').toLowerCase();

        return ['left', 'center', 'right', 'justify'].indexOf(value) !== -1 ? value : 'left';
    }

    function normalizeWidgetType(value) {
        value = String(value || '').toLowerCase();

        return ['text', 'cta', 'news_grid', 'hero'].indexOf(value) !== -1 ? value : 'text';
    }

    function clampNumber(value, min, max, fallback) {
        var numeric = parseInt(value, 10);
        if (isNaN(numeric)) {
            numeric = fallback;
        }
        if (numeric < min) {
            return min;
        }
        if (numeric > max) {
            return max;
        }
        return numeric;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
        return;
    }

    init();
}());