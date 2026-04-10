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
$rules_url = $builder_state['rules_url'] ?? '';
$picker_url = $builder_state['picker_url'] ?? '';
$picker_frame_url = $builder_state['picker_frame_url'] ?? '';
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
            <button class="nb-builder__link" type="button" data-builder-publish title="<?php html((string)($save_action['title'] ?? '')); ?>" <?php if (empty($save_action['is_enabled'])) { ?>disabled<?php } ?>><?php html((string)($save_action['label'] ?? 'Сохранить')); ?></button>
            <span class="nb-builder__link" title="Количество серверных ревизий для быстрого отката"><?php echo !empty($history['is_available']) ? ('History ' . (int)($history['count'] ?? 0)) : 'History off'; ?></span>
            <?php if (!empty($builder_state['page']['default_source']) && $builder_state['page']['default_source'] !== ($builder_state['page']['template'] ?? '')) { ?>
                <button class="nb-builder__link" type="button" data-builder-reset title="Вернуть шаблон к default-схеме <?php html((string)$builder_state['page']['default_source']); ?>">Default</button>
            <?php } ?>
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
                    <div class="nb-builder__viewport-title">Live viewport</div>
                    <div class="nb-builder__viewport-subtitle" data-viewport-subtitle>Текущий режим показывает live-предпросмотр страницы.</div>
                </div>
                <div class="nb-builder__viewport-badges">
                    <span class="nb-viewport-badge nb-viewport-badge--device" data-viewport-device><?php html($current_device_label); ?></span>
                    <span class="nb-viewport-badge" data-viewport-source>Base</span>
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

                        <section class="nb-inspector__section" data-style-inspector-block>
                            <div class="nb-inspector__section-head">
                                <h3>Style</h3>
                                <span class="nb-help" title="Привязывает выбранный builder-узел к CSS selector и сохраняет rule в текущий runtime nordicstyl.">?</span>
                            </div>
                            <label class="nb-field">
                                <span class="nb-field__label">Selector target</span>
                                <input class="nb-input" type="text" data-style-selector placeholder=".hero .title" />
                            </label>
                            <div class="nb-style-target-meta">
                                <span class="nb-style-pill" data-style-target-source>manual</span>
                                <div class="nb-builder__hint" data-style-target-hint>У этого узла пока нет style target. Впишите selector вручную или возьмите его с live-страницы.</div>
                            </div>
                            <div class="nb-action-row">
                                <button class="nb-button nb-button--secondary" type="button" data-style-open-picker>Выбрать на live-странице</button>
                                <a class="nb-button nb-button--ghost" href="<?php html($picker_url); ?>" target="_blank" rel="noopener">Отдельный picker</a>
                            </div>
                            <div class="nb-inspector__device-note" data-style-scope-note>Desktop пишет в base/default. Tablet и Mobile пишут в свои device-ветки.</div>
                            <div class="nb-style-grid">
                                <label class="nb-field">
                                    <span class="nb-field__label">Цвет текста</span>
                                    <input class="nb-input" type="text" data-style-field="color" placeholder="#173042" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Фон</span>
                                    <input class="nb-input" type="text" data-style-field="background-color" placeholder="#ffffff" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Размер текста</span>
                                    <input class="nb-input" type="text" data-style-field="font-size" placeholder="18px" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Внутренний отступ</span>
                                    <input class="nb-input" type="text" data-style-field="padding" placeholder="24px" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Радиус</span>
                                    <input class="nb-input" type="text" data-style-field="border-radius" placeholder="16px" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Толщина рамки</span>
                                    <input class="nb-input" type="text" data-style-field="border-width" placeholder="1px" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Цвет рамки</span>
                                    <input class="nb-input" type="text" data-style-field="border-color" placeholder="#d5cec0" />
                                </label>
                                <label class="nb-field">
                                    <span class="nb-field__label">Стиль рамки</span>
                                    <select class="nb-select" data-style-field="border-style">
                                        <option value="">Не задано</option>
                                        <option value="solid">Solid</option>
                                        <option value="dashed">Dashed</option>
                                        <option value="dotted">Dotted</option>
                                    </select>
                                </label>
                            </div>
                            <div class="nb-action-row">
                                <button class="nb-button" type="button" data-style-save>Сохранить style rule</button>
                                <button class="nb-button nb-button--secondary" type="button" data-style-reset>Очистить поля</button>
                            </div>
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

<div class="nb-style-picker-modal" data-style-picker-modal hidden>
    <div class="nb-style-picker-modal__backdrop" data-style-picker-close></div>
    <div class="nb-style-picker-modal__dialog" role="dialog" aria-modal="true" aria-label="Live picker selector">
        <div class="nb-style-picker-modal__head">
            <div>
                <div class="nb-style-picker-modal__title">Live picker</div>
                <div class="nb-style-picker-modal__subtitle">Кликните по элементу в live-странице, чтобы забрать selector для выбранного builder-узла.</div>
            </div>
            <button class="nb-button nb-button--secondary" type="button" data-style-picker-close>Закрыть</button>
        </div>
        <div class="nb-style-picker-modal__body">
            <iframe
                class="nb-style-picker-modal__frame"
                data-style-picker-frame
                src="<?php html($picker_frame_url); ?>"
                referrerpolicy="no-referrer"
                title="Nordic builder live picker"
            ></iframe>
        </div>
    </div>
</div>

<script>
window.NORDIC_BUILDER_STATE = <?php echo json_encode($builder_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
