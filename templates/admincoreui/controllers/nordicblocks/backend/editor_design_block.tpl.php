<?php
$this->setPageTitle('NordicBlocks - Дизайн-блок');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Дизайн-блок');
$this->addMenuItems('admin_toolbar', $menu);

$editor_css = @file_get_contents(__DIR__ . '/design-block-editor.css') ?: '';
$editor_js  = @file_get_contents(__DIR__ . '/design-block-editor.js') ?: '';
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
                <span class="nbde-badge">Дизайн-блок</span>
                <input class="nbde-title-input" id="nbd-title-input" type="text" value="<?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Название блока">
                <div class="nbde-statusline" id="nbd-status-text">Загрузка редактора...</div>
            </div>
        </div>
        <div class="nbde-topbar__actions">
            <div class="nbde-breakpoints" id="nbd-breakpoints">
                <button class="nbde-breakpoint is-active" type="button" data-breakpoint="desktop">Компьютер</button>
                <button class="nbde-breakpoint" type="button" data-breakpoint="tablet">Планшет</button>
                <button class="nbde-breakpoint" type="button" data-breakpoint="mobile">Мобильный</button>
            </div>
            <button class="nbde-ghost-button" type="button" data-action="reload-state">Перечитать с сервера</button>
            <a class="nbde-ghost-button" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>">Разместить</a>
            <button class="nbde-primary-button" type="button" id="nbd-save-button">Сохранить</button>
        </div>
    </header>

    <div class="nbde-main">
        <section class="nbde-canvas-panel">
            <div class="nbde-canvas-panel__head">
                <div>
                    <strong>Холст</strong>
                    <span>Живой DOM-холст без iframe: перетаскивание, набор текста и направляющие работают сразу в редакторе.</span>
                </div>
                <div class="nbde-canvas-chip" id="nbd-canvas-meta">Загрузка холста...</div>
            </div>
            <div class="nbde-canvas-workarea">
                <div class="nbde-canvas-frame nbde-canvas-frame--desktop" id="nbd-canvas-frame-wrap">
                    <div class="nbde-canvas-stage" id="nbd-canvas-stage"></div>
                </div>
            </div>
        </section>

        <aside class="nbde-sidebar">
            <section class="nbde-card">
                <div class="nbde-card__head">
                    <h3>Дизайн-блок</h3>
                    <span>Палитра элементов</span>
                </div>
                <div class="nbde-card__body" id="nbd-block-card"></div>
            </section>

            <section class="nbde-card">
                <div class="nbde-card__head">
                    <h3>Холст</h3>
                    <span>Текущий режим</span>
                </div>
                <div class="nbde-card__body" id="nbd-stage-card"></div>
            </section>

            <section class="nbde-card">
                <div class="nbde-card__head">
                    <h3>Секция</h3>
                    <span>Фон и контейнер</span>
                </div>
                <div class="nbde-card__body" id="nbd-section-card"></div>
            </section>

            <section class="nbde-card">
                <div class="nbde-card__head">
                    <h3>Слои</h3>
                    <span id="nbd-layers-summary">0 элементов</span>
                </div>
                <div class="nbde-card__body" id="nbd-layers-card"></div>
            </section>

            <section class="nbde-card nbde-card--sticky">
                <div class="nbde-card__head">
                    <h3>Свойства элемента</h3>
                    <span id="nbd-properties-summary">Ничего не выбрано</span>
                </div>
                <div class="nbde-card__body" id="nbd-properties-card"></div>
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
    'csrfToken' => cmsForm::getCSRFToken(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script><?= $editor_js ?></script>