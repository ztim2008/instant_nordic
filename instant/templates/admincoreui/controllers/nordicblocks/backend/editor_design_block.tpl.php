<?php
$this->setPageTitle('NordicBlocks - Дизайн-блок');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Дизайн-блок');
$this->addMenuItems('admin_toolbar', $menu);

$editor_css = @file_get_contents(__DIR__ . '/design-block-editor.css') ?: '';
$editor_geometry_core_js = @file_get_contents(__DIR__ . '/design-block-geometry-core.js') ?: '';
$editor_interaction_core_js = @file_get_contents(__DIR__ . '/design-block-interaction-core.js') ?: '';
$editor_js  = @file_get_contents(__DIR__ . '/design-block-editor.js') ?: '';
$icon_sprite_urls = [];
$site_root_path = rtrim((string) cmsConfig::get('root_path'), '/') . '/';
$site_root_url = rtrim((string) cmsConfig::get('root'), '/') . '/';
$http_template = (string) cmsConfig::get('http_template');

foreach ([$http_template, 'modern'] as $template_name) {
    $template_name = trim((string) $template_name);

    if ($template_name === '') {
        continue;
    }

    $icons_dir = $site_root_path . 'templates/' . $template_name . '/images/icons/';

    if (!is_dir($icons_dir)) {
        continue;
    }

    foreach ((array) glob($icons_dir . '*.svg') as $icon_file) {
        $file_name = pathinfo($icon_file, PATHINFO_FILENAME);

        if ($file_name === '' || isset($icon_sprite_urls[$file_name])) {
            continue;
        }

        $icon_sprite_urls[$file_name] = $site_root_url . 'templates/' . $template_name . '/images/icons/' . basename($icon_file);
    }
}
?>

<style><?= $editor_css ?></style>

<div
    id="nbd-editor"
    class="nbde-shell"
    data-state-url="<?= htmlspecialchars($state_url, ENT_QUOTES, 'UTF-8') ?>"
    data-save-url="<?= htmlspecialchars($save_url, ENT_QUOTES, 'UTF-8') ?>"
    data-back-url="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') ?>"
    data-place-url="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>"
>
    <header class="nbde-topbar">
        <div class="nbde-topbar__main">
            <a class="nbde-ghost-button" href="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') ?>">К списку блоков</a>
            <div class="nbde-titlebox">
                <div class="nbde-title-label">Название блока</div>
                <input class="nbde-title-input" id="nbd-title-input" type="text" value="<?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Название блока">
            </div>
        </div>
        <div class="nbde-topbar__actions">
            <div class="nbde-topbar__cluster">
                <div class="nbde-breakpoints" id="nbd-breakpoints">
                    <button class="nbde-breakpoint is-active" type="button" data-breakpoint="desktop">Компьютер</button>
                    <button class="nbde-breakpoint" type="button" data-breakpoint="tablet">Планшет</button>
                    <button class="nbde-breakpoint" type="button" data-breakpoint="mobile">Мобильный</button>
                </div>
            </div>
            <div class="nbde-topbar__cluster nbde-topbar__cluster--actions">
                <div class="nbde-statusline" id="nbd-status-text">Загрузка редактора...</div>
                <button class="nbde-ghost-button" type="button" data-action="toggle-focus-mode" id="nbd-focus-mode-button" aria-pressed="false">Фокус-режим</button>
                <button class="nbde-ghost-button" type="button" data-action="reload-state">Перечитать</button>
                <a class="nbde-ghost-button" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>">Разместить</a>
                <button class="nbde-primary-button" type="button" id="nbd-save-button">Сохранить</button>
            </div>
        </div>
    </header>

    <div class="nbde-main">
        <section class="nbde-canvas-panel">
            <div class="nbde-canvas-workarea">
                <div class="nbde-canvas-frame nbde-canvas-frame--desktop" id="nbd-canvas-frame-wrap">
                    <div class="nbde-canvas-stage" id="nbd-canvas-stage"></div>
                </div>
            </div>
        </section>

        <aside class="nbde-sidebar" id="nbd-sidebar">
            <button class="nbde-sidebar__toggle" type="button" data-action="toggle-sidebar" aria-expanded="true" aria-controls="nbd-sidebar">
                <span class="nbde-sidebar__toggle-icon" aria-hidden="true"></span>
                <span class="nbde-sidebar__toggle-text">Свернуть панель</span>
            </button>
            <section class="nbde-card">
                <div class="nbde-card__head">
                    <h3>Дизайн-блок</h3>
                    <span>Элементы</span>
                </div>
                <div class="nbde-card__body" id="nbd-block-card"></div>
            </section>

            <section class="nbde-card nbde-card--properties nbde-card--accordion">
                <button class="nbde-card__head nbde-card__head--toggle" type="button" data-action="toggle-properties-card" aria-expanded="true">
                    <span class="nbde-card__head-copy">
                        <h3>Свойства элемента</h3>
                        <span id="nbd-properties-summary">Ничего не выбрано</span>
                    </span>
                    <span class="nbde-card__chevron" aria-hidden="true"></span>
                </button>
                <div class="nbde-card__body" id="nbd-properties-card"></div>
            </section>

            <section class="nbde-card nbde-card--accordion is-collapsed">
                <button class="nbde-card__head nbde-card__head--toggle" type="button" data-action="toggle-stage-card" aria-expanded="false">
                    <span class="nbde-card__head-copy">
                        <h3>Холст</h3>
                        <span>Блок</span>
                    </span>
                    <span class="nbde-card__chevron" aria-hidden="true"></span>
                </button>
                <div class="nbde-card__body" id="nbd-stage-card" hidden></div>
            </section>

            <section class="nbde-card nbde-card--accordion is-collapsed">
                <button class="nbde-card__head nbde-card__head--toggle" type="button" data-action="toggle-section-card" aria-expanded="false">
                    <span class="nbde-card__head-copy">
                        <h3>Секция</h3>
                        <span>Фон</span>
                    </span>
                    <span class="nbde-card__chevron" aria-hidden="true"></span>
                </button>
                <div class="nbde-card__body" id="nbd-section-card" hidden></div>
            </section>

            <section class="nbde-card nbde-card--accordion is-collapsed">
                <button class="nbde-card__head nbde-card__head--toggle" type="button" data-action="toggle-layers-card" aria-expanded="false">
                    <span class="nbde-card__head-copy">
                        <h3>Слои</h3>
                        <span id="nbd-layers-summary">0 элементов</span>
                    </span>
                    <span class="nbde-card__chevron" aria-hidden="true"></span>
                </button>
                <div class="nbde-card__body" id="nbd-layers-card" hidden></div>
            </section>

        </aside>
    </div>
</div>

<script>
window.NordicblocksDesignBlockBootstrap = <?= json_encode([
    'blockId'   => (int) ($block['id'] ?? 0),
    'stateUrl'  => $state_url,
    'saveUrl'   => $save_url,
    'backUrl'   => $back_url,
    'placeUrl'  => $place_url,
    'mediaUploadUrl' => href_to('nordicblocks', 'media_upload'),
    'iconPickerUrl' => href_to('admin', 'settings', ['theme', cmsConfig::get('http_template'), 'icon_list']),
    'iconSpriteUrls' => $icon_sprite_urls,
    'devFlags'  => [
        'geometryDebug' => !empty($_GET['nb_debug_geometry'])
    ],
    'csrfToken' => cmsForm::getCSRFToken(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script><?= $editor_geometry_core_js ?></script>
<script><?= $editor_interaction_core_js ?></script>
<script><?= $editor_js ?></script>