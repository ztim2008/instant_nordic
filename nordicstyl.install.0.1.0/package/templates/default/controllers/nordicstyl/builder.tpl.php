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
$status_message = $builder_state['status_message'] ?? 'Каркас builder-а поднят. Следующий шаг: реальная вставка секций/виджетов и сохранение структуры.';
$rules_url = $builder_state['rules_url'] ?? '';
$picker_url = $builder_state['picker_url'] ?? '';
$page_title = $page_title ?? ($page['title'] ?? 'Страница');
$layout_source_label = (string)($page['layout_source_label'] ?? 'стандартная схема шаблона');
$current_device_label = (string)($page['device_label'] ?? 'Desktop');

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
            <button class="nb-builder__link" type="button" data-builder-publish title="Опубликовать Desktop в native layout">Publish</button>
            <a class="nb-builder__link" href="<?php html($rules_url); ?>" title="Правила CSS">CSS</a>
            <a class="nb-builder__link" href="<?php html($picker_url); ?>" title="Пикер элементов">Pick</a>
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

        <main class="nb-builder__canvas" data-builder-canvas aria-label="Builder canvas">
            <?php if ($rows) {
                nordicBuilderRenderRowsTemplate($rows);
            } else { ?>
                <section class="nb-builder__panel nb-canvas-empty">
                    <h2>Схема пока не найдена</h2>
                    <div class="nb-builder__hint">Для текущего шаблона нет layout rows.</div>
                </section>
            <?php } ?>
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
                    </div>
                </div>
            </section>

            <section class="nb-builder__panel">
                <h2>Статус</h2>
                <div class="nb-builder__status" data-builder-status><?php html($status_message); ?></div>
            </section>
        </aside>
    </div>
</div>

<script>
window.NORDIC_BUILDER_STATE = <?php echo json_encode($builder_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
