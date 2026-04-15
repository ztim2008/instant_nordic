<?php
/** @var cmsTemplate $this */

$builder_state = $builder_state ?? [];
$page = $builder_state['page'] ?? [];
$page_targets = is_array($builder_state['page_targets'] ?? null) ? $builder_state['page_targets'] : [];
$device_modes = is_array($builder_state['device_modes'] ?? null) ? $builder_state['device_modes'] : [];
$status_message = $builder_state['status_message'] ?? 'Live-дизайн готов. Кликните по элементу внутри страницы и сохраняйте оформление сразу на сайт.';
$rules_url = $builder_state['rules_url'] ?? '';
$schema_url = $builder_state['schema_url'] ?? '';
$picker_frame_url = $builder_state['picker_frame_url'] ?? '';
$page_title = $page_title ?? ($page['title'] ?? 'Страница');
$layout_source_label = (string)($page['layout_source_label'] ?? 'схема текущего шаблона');
$current_device_label = (string)($page['device_label'] ?? 'Desktop');
$current_uri = (string)($page['uri'] ?? '/');
$live_presets = [
    ['key' => 'hero', 'title' => 'Hero', 'description' => 'Крупный первый экран с одним главным сообщением.'],
    ['key' => 'features', 'title' => 'Преимущества', 'description' => 'Три быстрых блока для выгод и аргументов.'],
    ['key' => 'cta', 'title' => 'CTA', 'description' => 'Короткий призыв к действию в отдельной секции.'],
    ['key' => 'faq', 'title' => 'FAQ', 'description' => 'Один широкий блок под вопросы и ответы.'],
    ['key' => 'cards', 'title' => 'Карточки', 'description' => 'Ряд из карточек для товаров, тарифов или кейсов.'],
    ['key' => 'html', 'title' => 'HTML-блок', 'description' => 'Пустая секция как заготовка под ручную доработку.']
];
?>

<div class="nlive" data-nordic-live data-active-device="<?php html((string)($page['device'] ?? 'desktop')); ?>">
    <header class="nlive__topbar">
        <div class="nlive__topbar-main">
            <div class="nlive__eyebrow">Живой дизайн</div>
            <div class="nlive__heading-row">
                <div>
                    <h1 class="nlive__title"><?php html($page_title); ?></h1>
                    <div class="nlive__summary">Отдельная рабочая зона для визуального редактирования живой страницы без layout-схемы и структурного canvas.</div>
                </div>
                <div class="nlive__page-chip" data-live-page-chip><?php html($current_uri); ?></div>
            </div>
            <div class="nlive__subtitle">
                Шаблон: <strong><?php html((string)($page['template'] ?? 'modern')); ?></strong>
                <span>•</span>
                Схема живет отдельно: <?php html($layout_source_label); ?>
                <span>•</span>
                Экран: <span data-live-device-label><?php html($current_device_label); ?></span>
                <span>•</span>
                Контекст: <?php html((string)($page['uri'] ?? '/')); ?>
            </div>
            <div class="nlive__toolbar">
                <label class="nlive__control nlive__control--page">
                    <span class="nlive__control-label">Страница</span>
                    <select class="nlive__select" data-live-page-switcher aria-label="Переключить live-контекст страницы">
                        <?php foreach ($page_targets as $target) { ?>
                            <option value="<?php html((string)($target['url'] ?? '')); ?>" <?php if (!empty($target['is_active'])) { ?>selected<?php } ?>>
                                <?php html((string)($target['title'] ?? 'Страница')); ?>
                                <?php if (!empty($target['uri'])) { ?> · <?php html((string)$target['uri']); ?><?php } ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
                <div class="nlive__control">
                    <span class="nlive__control-label">Экран</span>
                    <div class="nlive__segmented">
                        <?php foreach ($device_modes as $device_mode) { ?>
                            <button
                                class="nlive__segment<?php if (!empty($device_mode['is_active'])) { ?> is-active<?php } ?>"
                                type="button"
                                data-live-device-button
                                data-nav-url="<?php html((string)($device_mode['url'] ?? '')); ?>"
                                data-device-key="<?php html((string)($device_mode['key'] ?? 'desktop')); ?>"
                            >
                                <?php html((string)($device_mode['title'] ?? 'Desktop')); ?>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="nlive__actions">
            <?php if ($schema_url !== '') { ?><a class="nlive__link" href="<?php html($schema_url); ?>">Schema</a><?php } ?>
            <?php if (!empty($builder_state['editor_url'])) { ?><a class="nlive__link" href="<?php html($builder_state['editor_url']); ?>">Editor</a><?php } ?>
            <?php if ($rules_url !== '') { ?><a class="nlive__link" href="<?php html($rules_url); ?>">CSS</a><?php } ?>
        </div>
    </header>

    <section class="nlive__status-strip" data-live-status-strip data-tone="muted">
        <div class="nlive__status-main">
            <div class="nlive__status-kicker">Состояние live-режима</div>
            <div class="nlive__status-text" data-live-frame-status><?php html($status_message); ?></div>
        </div>
        <div class="nlive__status-meta">
            <div class="nlive__meta-card">
                <span class="nlive__meta-label">Страница</span>
                <strong class="nlive__meta-value" data-live-current-uri><?php html($current_uri); ?></strong>
            </div>
            <div class="nlive__meta-card">
                <span class="nlive__meta-label">Экран</span>
                <strong class="nlive__meta-value" data-live-current-device><?php html($current_device_label); ?></strong>
            </div>
            <div class="nlive__meta-card">
                <span class="nlive__meta-label">Режим правки</span>
                <strong class="nlive__meta-value" data-live-edit-mode>Desktop · Обычный</strong>
            </div>
            <div class="nlive__meta-card">
                <span class="nlive__meta-label">Источник target</span>
                <strong class="nlive__meta-value" data-live-target-source>Ждем выбор элемента</strong>
            </div>
            <div class="nlive__meta-card">
                <span class="nlive__meta-label">Элемент</span>
                <strong class="nlive__meta-value" data-live-selected-title>Еще не выбран</strong>
            </div>
            <div class="nlive__meta-card">
                <span class="nlive__meta-label">Выбранный элемент</span>
                <strong class="nlive__meta-value nlive__meta-value--selector" data-live-selected-selector>Еще не выбран</strong>
            </div>
        </div>
    </section>

    <div class="nlive__layout">
        <aside class="nlive__info">
            <section class="nlive__panel">
                <h2>Добавить секцию</h2>
                <p class="nlive__text">Первый рабочий срез вставляет секцию прямо в draft live-runtime без публикации на сайт.</p>
                <div class="nlive__insert-context" data-live-insert-context>Точка вставки: в конец страницы.</div>
                <div class="nlive__preset-list">
                    <?php foreach ($live_presets as $preset) { ?>
                        <button
                            class="nlive__preset-card"
                            type="button"
                            data-live-preset="<?php html((string)$preset['key']); ?>"
                            data-live-preset-title="<?php html((string)$preset['title'], true); ?>"
                            data-live-preset-description="<?php html((string)$preset['description'], true); ?>"
                        >
                            <span class="nlive__preset-title"><?php html((string)$preset['title']); ?></span>
                            <span class="nlive__preset-description"><?php html((string)$preset['description']); ?></span>
                        </button>
                    <?php } ?>
                </div>
            </section>

            <section class="nlive__panel" data-live-content-panel>
                <h2>Контент секции</h2>
                <p class="nlive__text" data-live-content-hint>Выберите вставленную draft-секцию, чтобы сразу поменять ее текст и снова увидеть результат в iframe.</p>
                <div class="nlive__content-form" data-live-content-form hidden>
                    <label class="nlive__field" data-live-content-wrap="section_title">
                        <span class="nlive__field-label">Служебное имя</span>
                        <input class="nlive__input" type="text" data-live-content-field="section_title" placeholder="Hero">
                    </label>
                    <label class="nlive__field" data-live-content-wrap="eyebrow">
                        <span class="nlive__field-label">Кикер</span>
                        <input class="nlive__input" type="text" data-live-content-field="eyebrow" placeholder="Hero">
                    </label>
                    <label class="nlive__field" data-live-content-wrap="heading">
                        <span class="nlive__field-label">Заголовок</span>
                        <input class="nlive__input" type="text" data-live-content-field="heading" placeholder="Главный заголовок">
                    </label>
                    <label class="nlive__field" data-live-content-wrap="text">
                        <span class="nlive__field-label">Описание</span>
                        <textarea class="nlive__textarea" data-live-content-field="text" rows="4" placeholder="Короткое описание секции"></textarea>
                    </label>
                    <label class="nlive__field" data-live-content-wrap="items">
                        <span class="nlive__field-label">Пункты</span>
                        <textarea class="nlive__textarea" data-live-content-field="items" rows="5" placeholder="По одному пункту на строку"></textarea>
                    </label>
                    <label class="nlive__field" data-live-content-wrap="button_text">
                        <span class="nlive__field-label">Текст кнопки</span>
                        <input class="nlive__input" type="text" data-live-content-field="button_text" placeholder="Оставить заявку">
                    </label>
                    <label class="nlive__field" data-live-content-wrap="button_url">
                        <span class="nlive__field-label">Ссылка кнопки</span>
                        <input class="nlive__input" type="text" data-live-content-field="button_url" placeholder="#lead-form">
                    </label>
                    <label class="nlive__field" data-live-content-wrap="html">
                        <span class="nlive__field-label">HTML-заготовка</span>
                        <textarea class="nlive__textarea nlive__textarea--code" data-live-content-field="html" rows="6" placeholder="<div>Ваш HTML</div>"></textarea>
                    </label>
                    <div class="nlive__content-actions">
                        <button class="nlive__button nlive__button--primary" type="button" data-live-content-save>Обновить draft-секцию</button>
                    </div>
                </div>
            </section>

            <section class="nlive__panel">
                <h2>Что делать здесь</h2>
                <p class="nlive__text">Выберите страницу и размер экрана сверху, затем кликните по нужному элементу внутри живой страницы.</p>
                <p class="nlive__text">После клика откроется floating panel внутри iframe. Для вставленных draft-секций сначала обновится iframe, затем новый блок сразу выделится.</p>
            </section>
            <section class="nlive__panel">
                <h2>Рабочий контекст</h2>
                <dl class="nlive__facts">
                    <div class="nlive__fact-row">
                        <dt>Шаблон</dt>
                        <dd><?php html((string)($page['template'] ?? 'modern')); ?></dd>
                    </div>
                    <div class="nlive__fact-row">
                        <dt>Основа схемы</dt>
                        <dd><?php html($layout_source_label); ?></dd>
                    </div>
                    <div class="nlive__fact-row">
                        <dt>Текущий URI</dt>
                        <dd data-live-current-uri-duplicate><?php html($current_uri); ?></dd>
                    </div>
                </dl>
            </section>
            <section class="nlive__panel">
                <h2>Быстрые переходы</h2>
                <div class="nlive__quick-links">
                    <?php if ($schema_url !== '') { ?><a class="nlive__quick-link" href="<?php html($schema_url); ?>">Открыть schema workspace</a><?php } ?>
                    <?php if ($rules_url !== '') { ?><a class="nlive__quick-link" href="<?php html($rules_url); ?>">Открыть список CSS-правил</a><?php } ?>
                </div>
            </section>
        </aside>

        <main class="nlive__workspace">
            <div class="nlive__frame-shell" data-live-frame-shell data-frame-state="loading">
                <div class="nlive__frame-overlay" data-live-frame-overlay>
                    <div class="nlive__frame-overlay-card">
                        <div class="nlive__frame-overlay-title">Live-страница загружается</div>
                        <div class="nlive__frame-overlay-text">После загрузки можно кликать по элементам внутри страницы и сразу править оформление.</div>
                    </div>
                </div>
                <iframe
                    class="nlive__frame"
                    data-live-frame
                    src="<?php html($picker_frame_url); ?>"
                    referrerpolicy="no-referrer"
                    title="Nordic live design shell"
                ></iframe>
            </div>
        </main>
    </div>
</div>

<script>
window.NORDIC_LIVE_STATE = <?php echo json_encode($builder_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>