<?php
/** @var cmsTemplate $this */

$builder_state = $builder_state ?? [];
$page = $builder_state['page'] ?? [];
$rows = $builder_state['rows'] ?? [];
$page_targets = is_array($builder_state['page_targets'] ?? null) ? $builder_state['page_targets'] : [];
$device_modes = is_array($builder_state['device_modes'] ?? null) ? $builder_state['device_modes'] : [];
$widget_library = $builder_state['widget_library'] ?? ['categories' => [], 'items' => []];
$library_categories = is_array($widget_library['categories'] ?? null) ? $widget_library['categories'] : [];
$library_items = is_array($widget_library['items'] ?? null) ? $widget_library['items'] : [];
$history = is_array($builder_state['history'] ?? null) ? $builder_state['history'] : ['count' => 0, 'items' => [], 'is_available' => false];
$save_action = is_array($builder_state['save_action'] ?? null) ? $builder_state['save_action'] : ['label' => 'Сохранить', 'title' => '', 'is_enabled' => false];
$status_message = $builder_state['status_message'] ?? 'Каркас builder-а поднят. Следующий шаг: реальная вставка секций/виджетов и сохранение структуры.';
$design_url = $builder_state['design_url'] ?? '';
$rules_url = $builder_state['rules_url'] ?? '';
$picker_frame_url = $builder_state['picker_frame_url'] ?? '';
$page_title = $page_title ?? ($page['title'] ?? 'Страница');
$layout_source_label = (string)($page['layout_source_label'] ?? 'стандартная схема шаблона');
$current_device_label = (string)($page['device_label'] ?? 'Desktop');
$schema_state = is_array($builder_state['schema_state'] ?? null) ? $builder_state['schema_state'] : ['version' => 1, 'sections' => []];
$published_schema_state = is_array($builder_state['published_schema_state'] ?? null) ? $builder_state['published_schema_state'] : ['version' => 1, 'sections' => []];

if (($page['workspace'] ?? '') === 'schema') {
?>
<div class="nb-schema-builder" data-nordic-schema-builder>
    <header class="nb-schema-builder__topbar">
        <div class="nb-schema-builder__hero">
            <div class="nb-schema-builder__eyebrow">Секционный режим</div>
            <h1 class="nb-schema-builder__title"><?php html($page_title); ?></h1>
            <p class="nb-schema-builder__subtitle">Отдельный секционный режим внутри content.body. Здесь собирается структура страницы, а опубликованная схема выводится прямо на самой странице. Поверх базового текстового слоя уже доступен первый маркетинговый блок CTA.</p>
            <div class="nb-schema-builder__meta">
                <span>Шаблон: <strong><?php html((string)($page['template'] ?? 'modern')); ?></strong></span>
                <span>URI: <strong><?php html((string)($page['uri'] ?? '/')); ?></strong></span>
                <span>Правило: <strong data-schema-binding-summary><?php html((string)($page['binding_summary'] ?? 'точный · /')); ?></strong></span>
                <span>Источник: <strong><?php html((string)($page['binding_source_label'] ?? 'новое точечное правило')); ?></strong></span>
                <span>Опубликовано секций: <strong data-schema-published-count><?php echo count(is_array($published_schema_state['sections'] ?? null) ? $published_schema_state['sections'] : []); ?></strong></span>
            </div>
        </div>

        <div class="nb-schema-builder__actions">
            <label class="nb-schema-builder__page-switcher">
                <span>Страница</span>
                <select data-schema-page-switcher>
                    <?php foreach ($page_targets as $target) { ?>
                        <option value="<?php html((string)($target['url'] ?? '')); ?>" <?php if (!empty($target['is_active'])) { ?>selected<?php } ?>><?php html((string)($target['title'] ?? 'Страница')); ?><?php if (!empty($target['uri'])) { ?> · <?php html((string)$target['uri']); ?><?php } ?></option>
                    <?php } ?>
                </select>
            </label>

            <div class="nb-schema-builder__page-switcher">
                <span>Показывать</span>
                <div class="nb-schema-builder__binding-tabs">
                    <button type="button" class="nb-schema-builder__binding-tab" data-schema-binding-mode="exact">Точный URL</button>
                    <button type="button" class="nb-schema-builder__binding-tab" data-schema-binding-mode="prefix">Раздел</button>
                    <button type="button" class="nb-schema-builder__binding-tab" data-schema-binding-mode="global">Все страницы</button>
                </div>
                <input type="hidden" data-schema-binding-field="route_type" value="exact">
            </div>

            <label class="nb-schema-builder__page-switcher">
                <span>Путь или префикс</span>
                <input type="text" data-schema-binding-field="route_pattern" placeholder="/news или /">
            </label>

            <div class="nb-schema-builder__panel-note" data-schema-binding-note>Режим «Точный URL» показывает схему только на текущем адресе.</div>

            <div class="nb-schema-builder__button-row">
                <button type="button" class="nb-schema-builder__button nb-schema-builder__button--ghost" data-schema-action="add-section">Стартовая секция</button>
                <button type="button" class="nb-schema-builder__button nb-schema-builder__button--ghost" data-schema-action="save">Сохранить черновик</button>
                <button type="button" class="nb-schema-builder__button" data-schema-action="publish">Опубликовать</button>
                <?php if ($design_url !== '') { ?><a class="nb-schema-builder__button nb-schema-builder__button--ghost" href="<?php html($design_url); ?>">Живой дизайн</a><?php } ?>
                <a class="nb-schema-builder__button nb-schema-builder__button--ghost" href="<?php html($rules_url); ?>">CSS-правила</a>
            </div>
        </div>
    </header>

    <div class="nb-schema-builder__layout">
        <aside class="nb-schema-builder__sidebar nb-schema-builder__sidebar--left">
            <section class="nb-schema-builder__panel">
                <div class="nb-schema-builder__panel-head">
                    <h2>Секции</h2>
                    <span data-schema-count>0</span>
                </div>
                <div class="nb-schema-builder__panel-note">Не стартуем с пустого холста: кнопка сразу добавляет двухколоночную стартовую секцию с текстом и CTA.</div>
                <div class="nb-schema-builder__section-list" data-schema-sections></div>
            </section>

            <section class="nb-schema-builder__panel">
                <div class="nb-schema-builder__panel-head">
                    <h2>Блоки</h2>
                    <span><?php echo count($library_items); ?></span>
                </div>
                <div class="nb-schema-builder__panel-note">Первый продуктовый слой поверх секций: базовый текст и CTA с кнопкой. Кнопка добавляет блок в выбранную секцию.</div>
                <div class="nb-schema-builder__widget-library">
                    <?php if ($library_items) { ?>
                        <?php foreach ($library_items as $item) { ?>
                            <button
                                type="button"
                                class="nb-schema-builder__widget-card"
                                data-schema-action="add-widget-selected"
                                data-widget-kind="<?php html((string)($item['key'] ?? 'text')); ?>"
                            >
                                <strong><?php html((string)($item['title'] ?? 'Блок')); ?></strong>
                                <span><?php html((string)($item['description'] ?? '')); ?></span>
                            </button>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="nb-schema-builder__empty">Библиотека блоков пока пуста.</div>
                    <?php } ?>
                </div>
            </section>

            <section class="nb-schema-builder__panel">
                <div class="nb-schema-builder__panel-head">
                    <h2>Правила показа</h2>
                    <span data-schema-bindings-count>0</span>
                </div>
                <div class="nb-schema-builder__panel-note">Здесь лежат все правила: точечные, по разделу и сквозные на весь сайт. Любое правило можно открыть, поправить и удалить целиком.</div>
                <div class="nb-schema-builder__bindings" data-schema-bindings-list></div>
            </section>

            <section class="nb-schema-builder__panel">
                <h2>Статус</h2>
                <div class="nb-schema-builder__status" data-schema-status><?php html($status_message); ?></div>
            </section>
        </aside>

        <main class="nb-schema-builder__canvas-wrap">
            <div class="nb-schema-builder__canvas-head">
                <div>
                    <h2>Холст</h2>
                    <p>Секции и блоки кликабельны. Справа меняются свойства выбранного узла, включая CTA-оффер и кнопку.</p>
                </div>
                <div class="nb-schema-builder__canvas-badge">зона вывода: content.body</div>
            </div>
            <div class="nb-schema-builder__canvas" data-schema-canvas></div>
        </main>

        <aside class="nb-schema-builder__sidebar nb-schema-builder__sidebar--right">
            <section class="nb-schema-builder__panel">
                <div class="nb-schema-builder__panel-head">
                    <h2>Свойства</h2>
                    <span data-schema-selection-kind>ничего</span>
                </div>
                <div class="nb-schema-builder__empty" data-schema-empty>Выберите секцию или блок.</div>

                <div class="nb-schema-inspector" data-schema-inspector hidden>
                    <div class="nb-schema-inspector__group" data-schema-group="section" hidden>
                        <label>
                            <span>Название секции</span>
                            <input type="text" data-schema-field="section.title">
                        </label>
                        <label>
                            <span>Фон</span>
                            <input type="text" data-schema-field="section.style.background" placeholder="#ffffff или linear-gradient(...)">
                        </label>
                        <label>
                            <span>Отступ сверху</span>
                            <input type="range" min="0" max="240" step="4" data-schema-field="section.style.padding_top">
                            <strong data-schema-value="section.style.padding_top">48</strong>
                        </label>
                        <label>
                            <span>Отступ снизу</span>
                            <input type="range" min="0" max="240" step="4" data-schema-field="section.style.padding_bottom">
                            <strong data-schema-value="section.style.padding_bottom">48</strong>
                        </label>
                        <div class="nb-schema-inspector__actions">
                            <button type="button" class="nb-schema-builder__button nb-schema-builder__button--ghost" data-schema-action="add-text-selected">Добавить текстовый блок</button>
                            <button type="button" class="nb-schema-builder__button nb-schema-builder__button--ghost" data-schema-action="add-widget-selected" data-widget-kind="cta">Добавить CTA</button>
                            <button type="button" class="nb-schema-builder__button nb-schema-builder__button--ghost" data-schema-action="delete-selected">Удалить секцию</button>
                        </div>
                    </div>

                    <div class="nb-schema-inspector__group" data-schema-group="block" hidden>
                        <div class="nb-schema-inspector__type">Тип блока: <strong data-schema-block-type>Текст</strong></div>
                        <div data-schema-widget-group="text">
                            <label>
                                <span>Текст</span>
                                <textarea rows="7" data-schema-field="block.props.text"></textarea>
                            </label>
                        </div>
                        <div data-schema-widget-group="cta" hidden>
                            <label>
                                <span>Надзаголовок</span>
                                <input type="text" data-schema-field="block.props.eyebrow" placeholder="Спецпредложение">
                            </label>
                            <label>
                                <span>Заголовок CTA</span>
                                <textarea rows="4" data-schema-field="block.props.title"></textarea>
                            </label>
                            <label>
                                <span>Описание</span>
                                <textarea rows="5" data-schema-field="block.props.text"></textarea>
                            </label>
                            <label>
                                <span>Текст кнопки</span>
                                <input type="text" data-schema-field="block.props.button_label" placeholder="Оставить заявку">
                            </label>
                            <label>
                                <span>Ссылка кнопки</span>
                                <input type="text" data-schema-field="block.props.button_url" placeholder="/contacts">
                            </label>
                        </div>

                        <div data-schema-widget-group="news_grid" hidden>
                            <label>
                                <span>Тип контента</span>
                                <select data-schema-field="block.props.ctype">
                                    <option value="">— выберите —</option>
                                </select>
                            </label>
                            <label>
                                <span>Количество карточек</span>
                                <input type="number" min="1" max="24" data-schema-field="block.props.limit" value="6">
                            </label>
                            <label>
                                <span>Колонок в сетке</span>
                                <input type="number" min="1" max="6" data-schema-field="block.props.columns" value="3">
                            </label>
                            <label>
                                <span>Поля (через запятую)</span>
                                <input type="text" data-schema-field="block.props.fields" placeholder="title,image,date_pub">
                                <small>Доступные: title, image, date_pub и пользовательские поля типа контента</small>
                            </label>
                        </div>

                        <div data-schema-widget-group="hero" hidden>
                            <label>
                                <span>Надзаголовок</span>
                                <input type="text" data-schema-field="block.props.eyebrow" placeholder="Добро пожаловать">
                            </label>
                            <label>
                                <span>Главный заголовок</span>
                                <textarea rows="3" data-schema-field="block.props.title"></textarea>
                            </label>
                            <label>
                                <span>Подзаголовок</span>
                                <textarea rows="3" data-schema-field="block.props.text"></textarea>
                            </label>
                            <label>
                                <span>Текст кнопки</span>
                                <input type="text" data-schema-field="block.props.button_label" placeholder="Подробнее">
                            </label>
                            <label>
                                <span>Ссылка кнопки</span>
                                <input type="text" data-schema-field="block.props.button_url" placeholder="/about">
                            </label>
                            <label>
                                <span>Высота баннера (px)</span>
                                <input type="range" min="200" max="800" step="20" data-schema-field="block.props.hero_height">
                            </label>
                        </div>
                        <label>
                            <span>Колонка</span>
                            <select data-schema-field="block.column_id"></select>
                        </label>
                        <div data-schema-widget-group="text">
                            <label>
                                <span>Размер текста</span>
                                <input type="range" min="10" max="120" step="1" data-schema-field="block.style.font_size">
                                <strong data-schema-value="block.style.font_size">18</strong>
                            </label>
                        </div>
                        <div data-schema-widget-group="cta" hidden>
                            <label>
                                <span>Размер заголовка</span>
                                <input type="range" min="20" max="96" step="1" data-schema-field="block.style.title_size">
                                <strong data-schema-value="block.style.title_size">40</strong>
                            </label>
                            <label>
                                <span>Цвет кнопки</span>
                                <input type="text" data-schema-field="block.style.accent_color" placeholder="#155e63">
                            </label>
                        </div>
                        <label>
                            <span>Цвет текста</span>
                            <input type="text" data-schema-field="block.style.color" placeholder="#111111">
                        </label>
                        <label>
                            <span>Выравнивание</span>
                            <select data-schema-field="block.style.text_align">
                                <option value="left">Слева</option>
                                <option value="center">По центру</option>
                                <option value="right">Справа</option>
                                <option value="justify">По ширине</option>
                            </select>
                        </label>
                        <label>
                            <span>Фон</span>
                            <input type="text" data-schema-field="block.style.background" placeholder="#ffffff">
                        </label>
                        <label>
                            <span>Внутренний отступ</span>
                            <input type="range" min="0" max="240" step="4" data-schema-field="block.style.padding">
                            <strong data-schema-value="block.style.padding">0</strong>
                        </label>
                        <div class="nb-schema-inspector__actions">
                            <button type="button" class="nb-schema-builder__button nb-schema-builder__button--ghost" data-schema-action="delete-selected">Удалить блок</button>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
window.NORDIC_BUILDER_STATE = <?php echo json_encode($builder_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<?php
    return;
}

if (!function_exists('nordicBuilderRenderRowsTemplate')) {
    function nordicBuilderRenderRowsTemplate(array $rows, bool $isNested = false) {
        foreach ($rows as $row) {
            $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
            ?>
            <section
                class="nb-row<?php if ($isNested) { ?> nb-row--nested<?php } ?>"
                id="row-<?php html($row['uid']); ?>"
                data-builder-node
                data-node-type="row"
                data-node-title="<?php html((string)($row['title'] ?? 'Ряд'), true); ?>"
            >
                <header class="nb-row__header">
                    <div class="nb-row__heading">
                        <div class="nb-row__label"><?php html((string)($row['title'] ?? 'Ряд')); ?></div>
                        <span class="nb-help" title="<?php html($isNested ? 'Вложенная секция унаследована из схемы шаблона.' : 'Добавляй узлы через плюс и меняй ширину перетаскиванием.', true); ?>">?</span>
                    </div>
                    <button class="nb-add-button" type="button" data-builder-add data-add-kind="row" aria-label="Добавить ряд или секцию" title="Добавить ряд или секцию">+</button>
                </header>

                <div class="nb-row__columns" data-columns-row>
                    <?php foreach ($columns as $columnIndex => $column) {
                        $width = (int)($column['width'] ?? 12);
                        $widgets = is_array($column['widgets'] ?? null) ? $column['widgets'] : [];
                        $nested_rows = is_array($column['nested_rows'] ?? null) ? $column['nested_rows'] : [];
                    ?>
                        <div
                            class="nb-column"
                            data-builder-node
                            data-node-type="column"
                            data-node-title="<?php html((string)($column['title'] ?? 'Колонка'), true); ?>"
                            data-column
                            data-units="<?php echo $width; ?>"
                            style="--nb-col-span: <?php echo $width; ?>;"
                        >
                            <div class="nb-column__chrome">
                                <div class="nb-column__title"><?php html((string)($column['title'] ?? 'Колонка')); ?></div>
                                <div class="nb-column__width"><span data-column-width-label><?php echo $width; ?>/12</span></div>
                            </div>

                            <div class="nb-column__body">
                                <?php foreach ($widgets as $widget) { ?>
                                    <article
                                        class="nb-widget"
                                        data-builder-node
                                        data-node-type="widget"
                                        data-node-title="<?php html((string)($widget['title'] ?? 'Виджет'), true); ?>"
                                    >
                                        <div class="nb-widget__source"><?php html((string)($widget['source'] ?? 'library')); ?></div>
                                        <div class="nb-widget__title"><?php html((string)($widget['title'] ?? 'Виджет')); ?></div>
                                    </article>
                                <?php } ?>
                                <button class="nb-add-slot" type="button" data-builder-add data-add-kind="widget" aria-label="Добавить виджет или секцию" title="Добавить виджет или секцию">+</button>

                                <?php if ($nested_rows) { ?>
                                    <div class="nb-column__nested">
                                        <div class="nb-column__nested-label">Секции</div>
                                        <?php nordicBuilderRenderRowsTemplate($nested_rows, true); ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if ($columnIndex < count($columns) - 1) { ?>
                            <div class="nb-resize-handle" data-resize-handle aria-label="Изменить ширину колонок" title="Потяни, чтобы изменить ширину"></div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </section>
            <?php
        }
    }
}
?>

<div class="nb-builder" data-nordic-builder>
    <header class="nb-builder__topbar">
        <div class="nb-builder__topbar-main">
            <div class="nb-builder__eyebrow">Редакционный режим</div>
            <h1 class="nb-builder__title"><?php html($page_title); ?></h1>
            <div class="nb-builder__subtitle">
                Шаблон: <strong><?php html((string)($page['template'] ?? 'modern')); ?></strong>
                <span>•</span>
                Основа: <?php html($layout_source_label); ?>
                <span>•</span>
                Экран: <span data-current-device-label><?php html($current_device_label); ?></span>
                <span>•</span>
                Контекст: <?php html((string)($page['uri'] ?? '/')); ?>
            </div>

            <div class="nb-builder__toolbar">
                <label class="nb-control nb-control--page">
                    <span class="nb-control__label">Страница</span>
                    <select class="nb-select" data-page-switcher aria-label="Переключить контекст страницы">
                        <?php foreach ($page_targets as $target) { ?>
                            <option
                                value="<?php html((string)($target['url'] ?? '')); ?>"
                                <?php if (!empty($target['is_active'])) { ?>selected<?php } ?>
                            >
                                <?php html((string)($target['title'] ?? 'Страница')); ?>
                                <?php if (!empty($target['uri'])) { ?> · <?php html((string)$target['uri']); ?><?php } ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>

                <div class="nb-control">
                    <span class="nb-control__label">Экран</span>
                    <div class="nb-segmented" data-device-switcher>
                        <?php foreach ($device_modes as $device_mode) { ?>
                            <button
                                class="nb-segmented__button<?php if (!empty($device_mode['is_active'])) { ?> is-active<?php } ?>"
                                type="button"
                                data-device-button
                                    data-device-key="<?php html((string)($device_mode['key'] ?? 'desktop')); ?>"
                                data-nav-url="<?php html((string)($device_mode['url'] ?? '')); ?>"
                            >
                                <?php html((string)($device_mode['title'] ?? 'Desktop')); ?>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="nb-builder__topbar-actions">
            <button class="nb-builder__link" type="button" data-builder-publish title="<?php html((string)($save_action['title'] ?? '')); ?>" <?php if (empty($save_action['is_enabled'])) { ?>disabled<?php } ?>><?php html((string)($save_action['label'] ?? 'Сохранить')); ?></button>
            <span class="nb-builder__link" title="Количество серверных ревизий для быстрого отката"><?php echo !empty($history['is_available']) ? ('History ' . (int)($history['count'] ?? 0)) : 'History off'; ?></span>
            <?php if (!empty($builder_state['page']['default_source']) && $builder_state['page']['default_source'] !== ($builder_state['page']['template'] ?? '')) { ?>
                <button class="nb-builder__link" type="button" data-builder-reset title="Вернуть шаблон к default-схеме <?php html((string)$builder_state['page']['default_source']); ?>">Default</button>
            <?php } ?>
            <?php if (!empty($builder_state['editor_url'])) { ?><a class="nb-builder__link" href="<?php html($builder_state['editor_url']); ?>" title="Открыть новый editor shell">Editor</a><?php } ?>
            <?php if ($design_url !== '') { ?><a class="nb-builder__link" href="<?php html($design_url); ?>" title="Открыть отдельную страницу live-дизайна">Design</a><?php } ?>
            <a class="nb-builder__link" href="<?php html($rules_url); ?>" title="Правила CSS">CSS</a>
        </div>
    </header>

    <div class="nb-builder__layout">
        <aside class="nb-builder__sidebar nb-builder__sidebar--left">
            <section class="nb-builder__panel">
                <div class="nb-panel__title">
                    <h2>Библиотека</h2>
                    <span class="nb-help" title="Список доступных системных виджетов. Выбери карточку и затем точку вставки.">?</span>
                </div>
                <div class="nb-builder__chips" data-widget-library-filters>
                    <?php foreach ($library_categories as $index => $category) { ?>
                        <button
                            class="nb-chip<?php if ($index === 0) { ?> is-active<?php } ?>"
                            type="button"
                            data-widget-filter="<?php html((string)($category['key'] ?? 'all')); ?>"
                        >
                            <?php html((string)($category['title'] ?? 'Все')); ?>
                            <span class="nb-chip__count"><?php echo (int)($category['count'] ?? 0); ?></span>
                        </button>
                    <?php } ?>
                </div>

                <div class="nb-library-table-head" aria-hidden="true">
                    <span>Виджет</span>
                    <span>Источник</span>
                    <span>Флаги</span>
                </div>

                <div class="nb-widget-library" data-widget-library>
                    <?php if ($library_items) { ?>
                        <?php foreach ($library_items as $item) { ?>
                            <article
                                class="nb-library-card"
                                data-widget-library-item
                                data-widget-category="<?php html((string)($item['category_key'] ?? 'all')); ?>"
                                data-widget-title="<?php html((string)($item['title'] ?? 'Виджет'), true); ?>"
                                data-widget-id="<?php echo (int)($item['widget_id'] ?? 0); ?>"
                                data-widget-controller="<?php html((string)($item['widget_controller'] ?? ''), true); ?>"
                                data-widget-name="<?php html((string)($item['widget_name'] ?? ''), true); ?>"
                                data-widget-has-options="<?php echo !empty($item['has_options']) ? '1' : '0'; ?>"
                            >
                                <div class="nb-library-card__head">
                                    <div class="nb-library-card__badge"><?php html((string)($item['category_title'] ?? 'Системные')); ?></div>
                                    <div class="nb-library-card__flags">
                                        <?php if (!empty($item['has_options'])) { ?><div class="nb-library-card__meta" title="У виджета есть настройки">opt</div><?php } ?>
                                        <div class="nb-library-card__meta" title="<?php html(!empty($item['description']) ? (string)$item['description'] : 'Системный виджет InstantCMS. Подготовлен для будущей вставки через плюсик.', true); ?>">?</div>
                                    </div>
                                </div>
                                <div class="nb-library-card__title"><?php html((string)($item['title'] ?? 'Виджет')); ?></div>
                                <div class="nb-library-card__source"><?php html((string)($item['source'] ?? 'system')); ?></div>
                            </article>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="nb-builder__hint">Список виджетов не найден.</div>
                    <?php } ?>
                </div>
            </section>

            <section class="nb-builder__panel">
                <h2>Структура</h2>
                <ol class="nb-outline" data-builder-outline>
                    <?php foreach ($rows as $row) { ?>
                        <li>
                            <button class="nb-outline__item" type="button" data-select-target="row-<?php html($row['uid']); ?>">
                                <?php html((string)($row['title'] ?? 'Ряд')); ?>
                            </button>
                        </li>
                    <?php } ?>
                </ol>
            </section>
        </aside>

        <main class="nb-builder__canvas" data-builder-canvas data-active-device="<?php html((string)($page['device'] ?? 'desktop')); ?>" data-device-source="base" aria-label="Builder canvas">
            <div class="nb-builder__viewport-bar">
                <div>
                    <div class="nb-builder__viewport-title">Schema viewport</div>
                    <div class="nb-builder__viewport-subtitle" data-viewport-subtitle>Это отдельная страница схемы. Live-дизайн вынесен в самостоятельный workspace, чтобы не смешивать структуру и оформление.</div>
                </div>
                <div class="nb-builder__viewport-badges">
                    <span class="nb-viewport-badge nb-viewport-badge--device" data-viewport-device><?php html($current_device_label); ?></span>
                    <span class="nb-viewport-badge" data-viewport-source>Desktop</span>
                </div>
            </div>

            <div class="nb-builder__viewport-shell">
                <div class="nb-builder__viewport" data-builder-stage>
                    <?php if ($rows) {
                        nordicBuilderRenderRowsTemplate($rows);
                    } else { ?>
                        <section class="nb-builder__panel nb-canvas-empty">
                            <h2>Схема пока не найдена</h2>
                            <div class="nb-builder__hint">Для текущего шаблона нет layout rows.</div>
                        </section>
                    <?php } ?>
                </div>
            </div>
        </main>

        <aside class="nb-builder__sidebar nb-builder__sidebar--right">
            <section class="nb-builder__panel">
                <div class="nb-panel__title">
                    <h2>Инспектор</h2>
                    <span class="nb-help" title="Показывает тип, имя и ширину выбранного узла.">?</span>
                </div>
                <div class="nb-inspector" data-inspector>
                    <div class="nb-inspector__empty">Выберите узел.</div>
                    <dl class="nb-inspector__meta" hidden>
                        <dt>Тип</dt>
                        <dd data-inspector-type>—</dd>
                        <dt>Название</dt>
                        <dd data-inspector-title>—</dd>
                        <dt>Ширина</dt>
                        <dd data-inspector-width>—</dd>
                    </dl>
                    <div class="nb-builder__hint" data-node-settings-empty>Иконки узла открывают настройки, дублирование, скрытие и удаление без перегруза интерфейса.</div>
                    <div class="nb-inspector__settings" data-node-settings hidden>
                        <label class="nb-field">
                            <span class="nb-field__label">Название</span>
                            <input class="nb-input" type="text" data-setting-input="title" />
                        </label>
                        <label class="nb-field" data-setting-scope="row" hidden>
                            <span class="nb-field__label">Режим ряда</span>
                            <select class="nb-select" data-setting-input="width_mode">
                                <option value="grid">Grid</option>
                                <option value="full">Full</option>
                            </select>
                        </label>
                        <label class="nb-field" data-setting-scope="column" hidden>
                            <span class="nb-field__label">Ширина</span>
                            <input class="nb-range" type="range" min="2" max="12" step="1" data-setting-input="width" />
                            <span class="nb-field__value" data-setting-width-value>12/12</span>
                        </label>
                        <div class="nb-inspector__device-note" data-device-override-state></div>

                        <section class="nb-inspector__section" data-column-insert-panel hidden>
                            <div class="nb-inspector__section-head">
                                <h3>Вставка</h3>
                                <span class="nb-help" title="Колонка может принять выбранный виджет из библиотеки или новую вложенную секцию.">?</span>
                            </div>
                            <div class="nb-insert-card">
                                <div class="nb-insert-card__label">Выбранный виджет</div>
                                <div class="nb-insert-card__title" data-selected-library-title>Ничего не выбрано</div>
                                <div class="nb-insert-card__hint" data-selected-library-hint>Выберите карточку в библиотеке слева, чтобы вставить ее в текущую колонку.</div>
                            </div>
                            <div class="nb-action-stack">
                                <button class="nb-button" type="button" data-insert-selected-widget disabled>Вставить выбранный виджет</button>
                                <button class="nb-button nb-button--secondary" type="button" data-insert-section>Добавить секцию</button>
                            </div>
                        </section>

                        <section class="nb-inspector__section" data-widget-options-block hidden>
                            <div class="nb-inspector__section-head">
                                <h3>Настройки виджета</h3>
                                <span class="nb-help" title="Для inherited-виджетов сначала сделайте дубликат, чтобы не трогать общий bind шаблона.">?</span>
                            </div>
                            <div class="nb-builder__hint" data-widget-options-empty>Выберите виджет, чтобы открыть его настройки.</div>
                            <div class="nb-builder__hint" data-widget-options-lock hidden>Этот виджет пришел из default-схемы. Чтобы менять его настройки безопасно, продублируйте виджет и настройте копию.</div>
                            <div class="nb-widget-options__body" data-widget-options-body hidden></div>
                        </section>

                        <section class="nb-inspector__section">
                            <div class="nb-inspector__section-head">
                                <h3>Live-дизайн</h3>
                                <span class="nb-help" title="Оформление теперь живет на отдельной странице, чтобы не смешивать дизайн и layout-схему.">?</span>
                            </div>
                            <div class="nb-builder__hint">Открывайте отдельный design workspace и управляйте стилем на живой странице без примеси layout-схемы.</div>
                            <?php if ($design_url !== '') { ?><div class="nb-action-row"><a class="nb-button" href="<?php html($design_url); ?>">Открыть live-дизайн</a></div><?php } ?>
                        </section>
                    </div>
                </div>
            </section>

            <section class="nb-builder__panel">
                <h2>Статус</h2>
                <div class="nb-builder__status" data-builder-status><?php html($status_message); ?></div>
            </section>

            <section class="nb-builder__panel">
                <div class="nb-panel__title">
                    <h2>History</h2>
                    <span class="nb-help" title="Серверные ревизии live-сохранений. Можно быстро откатиться на одну из последних точек.">?</span>
                </div>
                <div class="nb-builder__hint" <?php if (!empty($history['items'])) { ?>hidden<?php } ?> data-history-empty>
                    История появится после первых live-сохранений.
                </div>
                <div class="nb-history" data-history-list>
                    <?php foreach (($history['items'] ?? []) as $revision) { ?>
                        <div class="nb-history__item">
                            <div class="nb-history__meta">
                                <div class="nb-history__title">#<?php echo (int)($revision['id'] ?? 0); ?> · <?php html((string)($revision['revision_type'] ?? 'live_save')); ?></div>
                                <div class="nb-history__time"><?php html((string)($revision['created_at'] ?? '')); ?></div>
                            </div>
                            <button class="nb-button nb-button--secondary" type="button" data-history-restore data-revision-id="<?php echo (int)($revision['id'] ?? 0); ?>" <?php if (empty($history['is_available'])) { ?>disabled<?php } ?>>Restore</button>
                        </div>
                    <?php } ?>
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
window.NORDIC_BUILDER_STATE = <?php echo json_encode($builder_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
