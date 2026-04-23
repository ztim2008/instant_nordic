<?php
$this->setPageTitle('NordicBlocks — Блоки');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Блоки');
$this->addMenuItems('admin_toolbar', $menu);
?>

<style>
.nb-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
    margin-top: 1.5rem;
}
.nb-block-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 0;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    transition: transform .18s ease, box-shadow .2s;
}
.nb-block-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
}
.nb-block-card__preview {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    border-bottom: 1px solid #edf2f7;
    background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 45%, #f8fafc 100%);
}
.nb-block-card__preview img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}
.nb-block-card__preview-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    padding: .3rem .6rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .03em;
    color: #fff;
    background: rgba(15, 23, 42, .78);
    backdrop-filter: blur(10px);
}
.nb-block-card__preview::after {
    content: '';
    position: absolute;
    inset: auto 0 0 0;
    height: 45%;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, .28) 100%);
    pointer-events: none;
}
.nb-block-card__preview--fallback {
    color: #0f172a;
}
.nb-block-card__schematic {
    position: absolute;
    inset: 0;
    padding: 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 12px;
}
.nb-block-card__schematic-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}
.nb-block-card__schematic-eyebrow {
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #1d4ed8;
}
.nb-block-card__schematic-title {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.1;
    max-width: 190px;
}
.nb-block-card__wire {
    display: grid;
    gap: 8px;
    margin-top: auto;
}
.nb-block-card__wire-box,
.nb-block-card__wire-line,
.nb-block-card__wire-dot {
    border-radius: 10px;
    background: rgba(255, 255, 255, .78);
    border: 1px solid rgba(148, 163, 184, .34);
    box-shadow: 0 8px 18px rgba(148, 163, 184, .12);
}
.nb-block-card__wire--hero {
    grid-template-columns: 1.2fr .9fr;
    grid-template-rows: 1fr .7fr;
    min-height: 112px;
}
.nb-block-card__wire--hero .nb-block-card__wire-box:first-child { grid-row: 1 / span 2; min-height: 112px; }
.nb-block-card__wire--hero .nb-block-card__wire-box:nth-child(2) { min-height: 52px; }
.nb-block-card__wire--hero .nb-block-card__wire-box:nth-child(3) { min-height: 52px; }
.nb-block-card__wire--features {
    grid-template-columns: repeat(3, 1fr);
    min-height: 112px;
}
.nb-block-card__wire--features .nb-block-card__wire-box { min-height: 70px; }
.nb-block-card__wire--faq {
    min-height: 112px;
}
.nb-block-card__wire--faq .nb-block-card__wire-line { height: 20px; }
.nb-block-card__wire--content {
    min-height: 112px;
}
.nb-block-card__wire--content .nb-block-card__wire-line:first-child { height: 54px; }
.nb-block-card__wire--content .nb-block-card__wire-line:nth-child(2) { width: 82%; height: 16px; }
.nb-block-card__wire--content .nb-block-card__wire-line:nth-child(3) { width: 68%; height: 16px; }
.nb-block-card__schematic-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.nb-block-card__schematic-tag {
    display: inline-flex;
    align-items: center;
    padding: .24rem .55rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    color: #334155;
    background: rgba(255, 255, 255, .78);
    border: 1px solid rgba(148, 163, 184, .34);
}
.nb-block-card__body {
    display: flex;
    flex-direction: column;
    gap: .75rem;
    padding: 1rem 1.1rem 1.1rem;
    flex: 1 1 auto;
}
.nb-block-card__title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}
.nb-block-card__subtitle {
    margin: -.2rem 0 0;
    font-size: .84rem;
    color: #64748b;
    line-height: 1.45;
}
.nb-block-card__meta {
    font-size: .8rem;
    color: #718096;
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
}
.nb-badge {
    display: inline-block;
    padding: .15em .6em;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.nb-badge--active   { background: #d1fae5; color: #065f46; }
.nb-badge--disabled { background: #f1f5f9; color: #64748b; }
.nb-type-tag {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 4px;
    padding: .1em .45em;
    font-size: .72rem;
    font-family: monospace;
}
.nb-block-card__actions {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: .75rem;
    border-top: 1px solid #f1f5f9;
}
.nb-btn-sm {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .8rem;
    border-radius: 6px;
    font-size: .8rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .15s, border-color .15s;
    background: none;
}
.nb-btn-sm--edit  { background: #3b82f6; color: #fff; }
.nb-btn-sm--edit:hover { background: #2563eb; color: #fff; }
.nb-btn-sm--place { background: #10b981; color: #fff; }
.nb-btn-sm--place:hover { background: #059669; color: #fff; }
.nb-btn-sm--del   { background: #fff; color: #ef4444; border-color: #fca5a5; }
.nb-btn-sm--del:hover { background: #fef2f2; border-color: #ef4444; }
.nb-btn-sm--del i { margin-right: .15rem; }

.nb-empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #718096;
}
.nb-empty-state h3 { color: #4a5568; margin-bottom: .5rem; }
.nb-build-cta {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .78rem 1.15rem;
    border: 0;
    border-radius: 999px;
    font-size: .9rem;
    font-weight: 800;
    letter-spacing: .01em;
    color: #fff;
    background: linear-gradient(135deg, #0f172a 0%, #2563eb 52%, #38bdf8 100%);
    box-shadow: 0 16px 34px rgba(37, 99, 235, .24);
    transition: transform .16s ease, box-shadow .18s ease, filter .18s ease;
}
.nb-build-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 42px rgba(37, 99, 235, .30);
    filter: saturate(1.05);
}
.nb-build-cta__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.55rem;
    height: 1.55rem;
    border-radius: 999px;
    background: rgba(255,255,255,.16);
    font-size: .95rem;
    line-height: 1;
}

/* Модальное окно создания блока */
.nb-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.nb-modal-overlay.is-open { display: flex; }
.nb-modal {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    width: min(1080px, 96vw);
    max-height: calc(100vh - 2rem);
    overflow: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.nb-modal h2 { margin: 0 0 1.25rem; font-size: 1.2rem; color: #1a202c; }
.nb-modal label { display: block; font-size: .85rem; color: #4a5568; margin-bottom: .4rem; font-weight: 500; }
.nb-modal select, .nb-modal input[type="text"] {
    width: 100%;
    padding: .6rem .85rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: .9rem;
    color: #1a202c;
    margin-bottom: 1rem;
    outline: none;
    transition: border-color .15s;
}
.nb-modal select:focus, .nb-modal input[type="text"]:focus { border-color: #3b82f6; }
.nb-modal__footer { display: flex; gap: .75rem; justify-content: flex-end; margin-top: .5rem; }
.nb-modal__footer .btn { padding: .5rem 1.25rem; }
.nb-create-picker {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.nb-create-picker__catalog {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.nb-create-picker__intro {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 14px;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #dbe4ff;
}
.nb-create-picker__intro strong {
    display: block;
    color: #0f172a;
    font-size: .98rem;
    margin-bottom: .25rem;
}
.nb-create-picker__intro span {
    display: block;
    color: #475569;
    font-size: .85rem;
    line-height: 1.45;
}
.nb-create-picker__selection {
    min-width: 240px;
    padding: .75rem .9rem;
    border-radius: 12px;
    background: rgba(255,255,255,.82);
    border: 1px solid #dbe4ff;
}
.nb-create-picker__selection-label {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #6366f1;
    margin-bottom: .35rem;
}
.nb-create-picker__selection-value {
    font-size: .9rem;
    font-weight: 700;
    color: #0f172a;
}
.nb-create-picker__selection-help {
    margin-top: .35rem;
    font-size: .8rem;
    color: #64748b;
    line-height: 1.45;
}
.nb-create-picker__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}
.nb-create-card {
    border: 1px solid #dbe4ff;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.nb-create-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(15, 23, 42, .10);
}
.nb-create-card.is-selected {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12), 0 18px 36px rgba(37, 99, 235, .12);
}
.nb-create-card.is-disabled {
    opacity: .72;
}
.nb-create-card__media {
    position: relative;
    aspect-ratio: 16 / 10;
    background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 45%, #f8fafc 100%);
    overflow: hidden;
}
.nb-create-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.nb-create-card__badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    padding: .3rem .65rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #fff;
    background: rgba(15, 23, 42, .8);
}
.nb-create-card__badge--installed {
    background: rgba(5, 150, 105, .92);
}
.nb-create-card__badge--coming-soon {
    background: rgba(100, 116, 139, .9);
}
.nb-create-card__body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .7rem;
}
.nb-create-card__eyebrow {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #2563eb;
}
.nb-create-card__title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}
.nb-create-card__subtitle,
.nb-create-card__summary {
    margin: 0;
    color: #475569;
    line-height: 1.45;
}
.nb-create-card__subtitle {
    font-size: .82rem;
}
.nb-create-card__summary {
    font-size: .84rem;
}
.nb-create-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.nb-create-card__tag {
    display: inline-flex;
    align-items: center;
    padding: .24rem .55rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.nb-create-card__actions {
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
    align-items: center;
    padding-top: .15rem;
}
.nb-create-card__choose {
    border: 1px solid transparent;
}
.nb-create-card__choose[disabled] {
    cursor: not-allowed;
    opacity: .65;
}
.nb-create-picker__divider {
    position: relative;
    text-align: center;
    margin-top: .2rem;
}
.nb-create-picker__divider::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: #e2e8f0;
}
.nb-create-picker__divider span {
    position: relative;
    display: inline-block;
    padding: 0 .8rem;
    background: #fff;
    font-size: .8rem;
    color: #64748b;
}
.nb-modal__field-note {
    margin: -.5rem 0 1rem;
    font-size: .8rem;
    color: #64748b;
}
.nb-create-mode-panel {
    display: none;
    gap: .8rem;
    align-items: flex-start;
    padding: 1rem 1.1rem;
    border-radius: 14px;
    border: 1px solid #bfdbfe;
    background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
}
.nb-create-mode-panel__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 1rem;
    font-weight: 800;
    flex: 0 0 auto;
}
.nb-create-mode-panel strong {
    display: block;
    margin-bottom: .2rem;
    color: #0f172a;
}
.nb-create-mode-panel span {
    display: block;
    color: #475569;
    font-size: .84rem;
    line-height: 1.5;
}
.nb-modal-overlay.is-design-block-start .nb-create-mode-panel {
    display: flex;
}
.nb-modal-overlay.is-design-block-start .nb-create-picker__catalog,
.nb-modal-overlay.is-design-block-start .nb-modal__field-note {
    display: none;
}

@media (max-width: 640px) {
    .nb-block-card__body {
        padding: .95rem;
    }
    .nb-block-card__schematic-title {
        max-width: 150px;
        font-size: .92rem;
    }
    .nb-modal {
        width: min(1080px, 100vw);
        min-height: 100vh;
        border-radius: 0;
    }
    .nb-create-picker__intro {
        flex-direction: column;
    }
}
</style>

<?php
$cache_stats = is_array($cache_stats ?? null) ? $cache_stats : [];
$cache_total = (int) ($cache_stats['total'] ?? 0);
$cache_active = (int) ($cache_stats['active'] ?? 0);
$cache_page = (int) ($cache_stats['page'] ?? 0);
$cache_block = (int) ($cache_stats['block'] ?? 0);
$cache_runtime = (int) ($cache_stats['runtime'] ?? 0);
$catalog_renderer_version = htmlspecialchars((string) ($catalog_renderer_version ?? ''), ENT_QUOTES, 'UTF-8');
$create_catalog_cards = is_array($create_catalog_cards ?? null) ? $create_catalog_cards : [];
?>

<div style="margin-top:1rem;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap">
    <button class="btn btn-default" id="nb-flush-cache" data-url="<?= htmlspecialchars($flush_cache_url, ENT_QUOTES, 'UTF-8') ?>">Сбросить SSR cache</button>
    <span id="nb-cache-stats" style="font-size:.82rem;color:#475569;padding:.42rem .72rem;border:1px solid #e2e8f0;border-radius:999px;background:#fff">
        SSR cache: active <?= $cache_active ?> / total <?= $cache_total ?> / page <?= $cache_page ?> / block <?= $cache_block ?> / runtime <?= $cache_runtime ?>
    </span>
    <?php if ($catalog_renderer_version !== ''): ?>
    <span style="font-size:.78rem;color:#1d4ed8;padding:.42rem .72rem;border:1px solid #bfdbfe;border-radius:999px;background:#eff6ff">
        catalog_browser SSR v<?= $catalog_renderer_version ?>
    </span>
    <?php endif; ?>
    <span style="font-size:.8rem;color:#64748b">Очищает таблицу nordicblocks_cache без затрагивания контента и дизайн-токенов.</span>
</div>

<?php if (!$blocks): ?>
<div class="nb-empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin:0 auto 1rem"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/></svg>
    <h3>Блоков пока нет</h3>
    <p>Создайте первый блок и добавьте его как виджет в схему страницы.</p>
    <button class="nb-build-cta mt-3" id="nb-open-design-block" type="button">
        <span class="nb-build-cta__icon">+</span>
        Собрать свой блок
    </button>
    <div style="font-size:.8rem;color:#64748b;margin-top:.65rem">Быстрый старт для design block: имя → canvas → сборка.</div>
    <div style="margin-top:.7rem">
        <button class="btn btn-primary" id="nb-open-create">+ Создать блок</button>
    </div>
    <div style="margin-top:.9rem">
        <a class="btn btn-default" href="<?= htmlspecialchars($widgets_url, ENT_QUOTES, 'UTF-8') ?>">
            Открыть структуру (размещение)
        </a>
    </div>
</div>
<?php else: ?>

<div style="margin-top:1rem;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap">
    <button class="nb-build-cta" id="nb-open-design-block" type="button">
        <span class="nb-build-cta__icon">+</span>
        Собрать свой блок
    </button>
    <button class="btn btn-primary" id="nb-open-create">+ Создать блок</button>
    <a class="btn btn-default" href="<?= htmlspecialchars($widgets_url, ENT_QUOTES, 'UTF-8') ?>">
        Разместить блок в структуре
    </a>
    <span style="font-size:.8rem;color:#64748b">
        Шаги: создать → настроить → добавить виджет NordicBlocks Block в нужную позицию
    </span>
</div>

<?php if (!empty($hidden_legacy_count)): ?>
<div style="margin-top:.85rem;padding:.9rem 1rem;border:1px solid #f5d0fe;background:#fdf4ff;border-radius:14px;color:#6b21a8;font-size:.9rem;line-height:1.5">
    Первая волна продукта сейчас ограничена блоками <strong>hero</strong> и <strong>faq</strong>.
    Скрыто legacy-блоков из старого потока: <strong><?= (int) $hidden_legacy_count ?></strong>.
</div>
<?php endif; ?>

<div class="nb-admin-grid">
<?php foreach ($blocks as $block):
    $title_esc  = htmlspecialchars($block['title'],    ENT_QUOTES, 'UTF-8');
    $type_esc   = htmlspecialchars($block['type'],     ENT_QUOTES, 'UTF-8');
    $editor_url = htmlspecialchars($block['editor_url'], ENT_QUOTES, 'UTF-8');
    $place_url  = htmlspecialchars($block['place_url'], ENT_QUOTES, 'UTF-8');
    $status     = $block['status'] === 'active' ? 'active' : 'disabled';
    $definition = is_array($block['definition'] ?? null) ? $block['definition'] : [];
    $definition_title = trim((string) ($definition['title'] ?? $block['type'] ?? 'Блок'));
    $definition_title_esc = htmlspecialchars($definition_title, ENT_QUOTES, 'UTF-8');
    $definition_description = trim((string) ($definition['description'] ?? ''));
    if ($definition_description !== '' && mb_strlen($definition_description) > 120) {
        $definition_description = rtrim(mb_substr($definition_description, 0, 117)) . '...';
    }
    $definition_description_esc = htmlspecialchars($definition_description, ENT_QUOTES, 'UTF-8');
    $definition_preview = trim((string) ($definition['preview'] ?? ''));
    $definition_preview_esc = htmlspecialchars($definition_preview, ENT_QUOTES, 'UTF-8');
    $definition_category = trim((string) ($definition['category'] ?? 'content'));
    $definition_category_label = $definition_category !== '' ? str_replace(['_', '-'], ' ', $definition_category) : 'content';
    $definition_category_label_esc = htmlspecialchars($definition_category_label, ENT_QUOTES, 'UTF-8');
    $definition_fields = is_array($definition['schema']['fields'] ?? null) ? $definition['schema']['fields'] : [];
    $definition_fields_count = count($definition_fields);
    $definition_tags = array_values(array_filter(array_map('trim', (array) ($definition['meta']['tags'] ?? []))));
    $definition_tags = array_slice($definition_tags, 0, 3);
    $wire_class = 'content';
    if ($definition_category === 'hero') {
        $wire_class = 'hero';
    } elseif ($definition_category === 'features' || $definition_category === 'cta') {
        $wire_class = 'features';
    } elseif ($definition_category === 'faq') {
        $wire_class = 'faq';
    }
?>
<div class="nb-block-card">
    <div class="nb-block-card__preview <?= $definition_preview !== '' ? 'nb-block-card__preview--image' : 'nb-block-card__preview--fallback' ?>">
        <span class="nb-block-card__preview-badge"><?= $definition_preview !== '' ? 'Preview' : 'Схема' ?></span>
        <?php if ($definition_preview !== ''): ?>
            <img src="<?= $definition_preview_esc ?>" alt="<?= $definition_title_esc ?>" loading="lazy" decoding="async">
        <?php else: ?>
            <div class="nb-block-card__schematic">
                <div class="nb-block-card__schematic-top">
                    <div>
                        <div class="nb-block-card__schematic-eyebrow"><?= $definition_category_label_esc ?></div>
                        <div class="nb-block-card__schematic-title"><?= $definition_title_esc ?></div>
                    </div>
                </div>
                <div class="nb-block-card__wire nb-block-card__wire--<?= htmlspecialchars($wire_class, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($wire_class === 'hero'): ?>
                        <div class="nb-block-card__wire-box"></div>
                        <div class="nb-block-card__wire-box"></div>
                        <div class="nb-block-card__wire-box"></div>
                    <?php elseif ($wire_class === 'features'): ?>
                        <div class="nb-block-card__wire-box"></div>
                        <div class="nb-block-card__wire-box"></div>
                        <div class="nb-block-card__wire-box"></div>
                    <?php elseif ($wire_class === 'faq'): ?>
                        <div class="nb-block-card__wire-line"></div>
                        <div class="nb-block-card__wire-line"></div>
                        <div class="nb-block-card__wire-line"></div>
                        <div class="nb-block-card__wire-line"></div>
                    <?php else: ?>
                        <div class="nb-block-card__wire-line"></div>
                        <div class="nb-block-card__wire-line"></div>
                        <div class="nb-block-card__wire-line"></div>
                    <?php endif; ?>
                </div>
                <?php if ($definition_tags): ?>
                    <div class="nb-block-card__schematic-tags">
                        <?php foreach ($definition_tags as $tag): ?>
                            <span class="nb-block-card__schematic-tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="nb-block-card__body">
        <h3 class="nb-block-card__title"><?= $title_esc ?></h3>
        <?php if ($definition_description_esc !== ''): ?>
            <p class="nb-block-card__subtitle"><?= $definition_description_esc ?></p>
        <?php endif; ?>
        <div class="nb-block-card__meta">
            <span class="nb-type-tag"><?= $type_esc ?></span>
            <span class="nb-badge nb-badge--<?= $status ?>"><?= $status === 'active' ? 'активен' : 'отключён' ?></span>
            <span><?= (int) $definition_fields_count ?> полей</span>
            <span><?= $definition_category_label_esc ?></span>
        </div>
    </div>
    <div class="nb-block-card__actions">
        <a href="<?= $place_url ?>" class="nb-btn-sm nb-btn-sm--place">
            <i class="fa fa-thumb-tack"></i> Разместить
        </a>
        <a href="<?= $editor_url ?>" class="nb-btn-sm nb-btn-sm--edit">
            <i class="fa fa-edit"></i> Редактировать
        </a>
        <button
            class="nb-btn-sm nb-btn-sm--del nb-delete-block"
            data-id="<?= (int)$block['id'] ?>"
            data-title="<?= $title_esc ?>"
            data-url="<?= htmlspecialchars($delete_block_url, ENT_QUOTES, 'UTF-8') ?>"
            title="Удалить блок"
        ><i class="fa fa-trash"></i> Удалить</button>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<!-- Модальное окно создания блока -->
<div class="nb-modal-overlay" id="nb-create-modal">
    <div class="nb-modal">
        <h2>Создать блок</h2>
        <form method="post" action="<?= htmlspecialchars($create_block_url, ENT_QUOTES, 'UTF-8') ?>">
            <?= html_csrf_token() ?>
            <div class="nb-create-picker">
                <div class="nb-create-mode-panel" id="nb-create-mode-panel">
                    <div class="nb-create-mode-panel__icon">D</div>
                    <div>
                        <strong>Старт для Design Block</strong>
                        <span>Тип уже переключён на Design Block. Задайте имя и после создания сразу откроется canvas-редактор для свободной сборки блока.</span>
                    </div>
                </div>

                <div class="nb-create-picker__catalog">
                <?php if ($create_catalog_cards): ?>
                <div class="nb-create-picker__intro">
                    <div>
                        <strong>Быстрый выбор из curated-подборки</strong>
                        <span>Теперь это основной вход вместо отдельного каталога: выберите готовый pilot block, затем задайте внутреннее название и откройте редактор.</span>
                    </div>
                    <div class="nb-create-picker__selection">
                        <div class="nb-create-picker__selection-label">Текущий выбор</div>
                        <div class="nb-create-picker__selection-value" id="nb-create-selection-value">Не выбран</div>
                        <div class="nb-create-picker__selection-help" id="nb-create-selection-help">Выберите карточку из подборки или задайте тип вручную ниже.</div>
                    </div>
                </div>

                <div class="nb-create-picker__grid">
                    <?php foreach ($create_catalog_cards as $card): ?>
                        <?php
                            $card_slug = (string) ($card['slug'] ?? '');
                            $card_title = (string) ($card['title'] ?? $card_slug);
                            $card_subtitle = trim((string) ($card['subtitle'] ?? ''));
                            $card_summary = trim((string) ($card['summary'] ?? ''));
                            $card_category = trim((string) ($card['category'] ?? 'content'));
                            $card_preview_url = trim((string) ($card['preview_url'] ?? ''));
                            $card_preview_alt = trim((string) ($card['preview_alt'] ?? $card_title));
                            $card_tags = array_values(array_filter(array_map('trim', (array) ($card['tags'] ?? []))));
                            $card_availability = (string) ($card['availability'] ?? 'free');
                            $card_existing = is_array($card['existing_block'] ?? null) ? $card['existing_block'] : null;
                            $card_is_available = $card_availability === 'free';
                            $card_badge = $card_existing ? 'Установлен' : ($card_is_available ? 'Pilot ready' : 'Скоро');
                            $card_badge_class = $card_existing ? ' nb-create-card__badge--installed' : ($card_is_available ? '' : ' nb-create-card__badge--coming-soon');
                        ?>
                        <div
                            class="nb-create-card<?= $card_is_available ? '' : ' is-disabled' ?>"
                            data-type="<?= htmlspecialchars($card_slug, ENT_QUOTES, 'UTF-8') ?>"
                            data-title="<?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8') ?>"
                            data-summary="<?= htmlspecialchars($card_summary !== '' ? $card_summary : $card_subtitle, ENT_QUOTES, 'UTF-8') ?>"
                            data-available="<?= $card_is_available ? '1' : '0' ?>"
                            tabindex="0"
                            role="button"
                            aria-disabled="<?= $card_is_available ? 'false' : 'true' ?>"
                        >
                            <div class="nb-create-card__media">
                                <span class="nb-create-card__badge<?= $card_badge_class ?>"><?= htmlspecialchars($card_badge, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($card_preview_url !== ''): ?>
                                    <img src="<?= htmlspecialchars($card_preview_url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card_preview_alt, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
                                <?php endif; ?>
                            </div>
                            <div class="nb-create-card__body">
                                <div class="nb-create-card__eyebrow"><?= htmlspecialchars($card_category, ENT_QUOTES, 'UTF-8') ?></div>
                                <h3 class="nb-create-card__title"><?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8') ?></h3>
                                <?php if ($card_subtitle !== ''): ?>
                                <p class="nb-create-card__subtitle"><?= htmlspecialchars($card_subtitle, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <?php if ($card_summary !== ''): ?>
                                <p class="nb-create-card__summary"><?= htmlspecialchars($card_summary, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <?php if ($card_tags): ?>
                                <div class="nb-create-card__tags">
                                    <?php foreach (array_slice($card_tags, 0, 3) as $tag): ?>
                                    <span class="nb-create-card__tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <div class="nb-create-card__actions">
                                    <button type="button" class="nb-btn-sm nb-btn-sm--edit nb-create-card__choose"<?= $card_is_available ? '' : ' disabled' ?>><?= $card_existing ? 'Выбрать снова' : 'Выбрать' ?></button>
                                    <?php if ($card_existing): ?>
                                    <a href="<?= htmlspecialchars((string) ($card_existing['editor_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="nb-btn-sm nb-btn-sm--place">Открыть установленный</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="nb-create-picker__divider"><span>Или выберите любой supported type вручную</span></div>
                <?php endif; ?>
                </div>

            <label for="nb-block-type">Тип блока</label>
            <select id="nb-block-type" name="type" required>
                <?php foreach ($block_types as $t_key => $t_label): ?>
                <option value="<?= htmlspecialchars($t_key, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($t_label, ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="nb-modal__field-note">Curated карточки выше только ускоряют выбор. Форма ниже остаётся единым create-flow для всех supported block types.</div>

            <label for="nb-block-title">Название (для себя)</label>
            <input type="text" id="nb-block-title" name="title" placeholder="Например: Hero — Главная страница" required maxlength="255">

            <div class="nb-modal__footer">
                <button type="button" class="btn btn-default" id="nb-close-create">Отмена</button>
                <button type="submit" class="btn btn-primary">Создать</button>
            </div>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var nbCsrfToken = <?= json_encode(cmsForm::getCSRFToken(), JSON_UNESCAPED_UNICODE) ?>;
    var overlay  = document.getElementById('nb-create-modal');
    var btnOpen  = document.getElementById('nb-open-create');
    var btnOpenDesign = document.getElementById('nb-open-design-block');
    var btnClose = document.getElementById('nb-close-create');
    var flushBtn = document.getElementById('nb-flush-cache');
    var cacheStats = document.getElementById('nb-cache-stats');
    var typeSelect = document.getElementById('nb-block-type');
    var titleInput = document.getElementById('nb-block-title');
    var selectionValue = document.getElementById('nb-create-selection-value');
    var selectionHelp = document.getElementById('nb-create-selection-help');
    var createCards = Array.prototype.slice.call(document.querySelectorAll('.nb-create-card'));

    function getCardByType(type) {
        return createCards.find(function(card) {
            return card.dataset.type === type;
        }) || null;
    }

    function syncCreateSelection(type) {
        var activeCard = getCardByType(type);

        createCards.forEach(function(card) {
            card.classList.toggle('is-selected', !!activeCard && card === activeCard);
        });

        if (!selectionValue || !selectionHelp) {
            return;
        }

        if (!activeCard) {
            selectionValue.textContent = type || 'Не выбран';
            selectionHelp.textContent = 'Выберите карточку из подборки или задайте тип вручную ниже.';
            return;
        }

        selectionValue.textContent = activeCard.dataset.title || type;
        selectionHelp.textContent = activeCard.dataset.summary || 'Выбран curated block type.';
    }

    function applyCreateCard(card) {
        if (!card || card.dataset.available !== '1' || !typeSelect) {
            return;
        }

        typeSelect.value = card.dataset.type;
        syncCreateSelection(typeSelect.value);

        if (titleInput && !titleInput.value.trim()) {
            titleInput.value = card.dataset.title || '';
        }
    }

    function openCreateModal(mode) {
        if (!overlay) {
            return;
        }

        overlay.classList.toggle('is-design-block-start', mode === 'design_block');
        overlay.classList.add('is-open');

        if (typeSelect && mode === 'design_block') {
            typeSelect.value = 'design_block';
            syncCreateSelection(typeSelect.value);
        }

        if (titleInput) {
            if (mode === 'design_block') {
                titleInput.value = '';
                titleInput.placeholder = 'Например: Новый дизайн блок';
            } else {
                titleInput.placeholder = 'Например: Hero — Главная страница';
            }

            setTimeout(function() {
                titleInput.focus();
            }, 0);
        }
    }

    function closeCreateModal() {
        if (!overlay) {
            return;
        }

        overlay.classList.remove('is-open');
        overlay.classList.remove('is-design-block-start');

        if (titleInput) {
            titleInput.placeholder = 'Например: Hero — Главная страница';
        }
    }

    function renderCacheStats(stats) {
        if (!cacheStats || !stats) {
            return;
        }

        cacheStats.textContent = 'SSR cache: active ' + (stats.active || 0)
            + ' / total ' + (stats.total || 0)
            + ' / page ' + (stats.page || 0)
            + ' / block ' + (stats.block || 0)
            + ' / runtime ' + (stats.runtime || 0);
    }

    if (btnOpen)  { btnOpen.addEventListener('click',  function() { openCreateModal(); }); }
    if (btnOpenDesign) { btnOpenDesign.addEventListener('click', function() { openCreateModal('design_block'); }); }
    if (btnClose) { btnClose.addEventListener('click', function() { closeCreateModal(); }); }
    if (overlay)  { overlay.addEventListener('click', function(e) { if (e.target === overlay) closeCreateModal(); }); }
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            syncCreateSelection(typeSelect.value);
        });

        if (createCards.length && !getCardByType(typeSelect.value)) {
            var firstAvailableCard = createCards.find(function(card) {
                return card.dataset.available === '1';
            });

            if (firstAvailableCard) {
                typeSelect.value = firstAvailableCard.dataset.type;
            }
        }

        syncCreateSelection(typeSelect.value);
    }

    createCards.forEach(function(card) {
        card.addEventListener('click', function(event) {
            if (event.target.closest('a')) {
                return;
            }

            applyCreateCard(card);
        });

        card.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            applyCreateCard(card);
        });

        var chooseButton = card.querySelector('.nb-create-card__choose');
        if (chooseButton) {
            chooseButton.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                applyCreateCard(card);
            });
        }
    });

    if (flushBtn) {
        flushBtn.addEventListener('click', function() {
            if (!confirm('Очистить весь SSR cache NordicBlocks?')) return;

            flushBtn.disabled = true;
            var initialLabel = flushBtn.textContent;
            flushBtn.textContent = 'Очищаю...';

            fetch(flushBtn.dataset.url + '?csrf_token=' + encodeURIComponent(nbCsrfToken), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: '{}'
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d.ok) {
                    throw new Error(d.error || '?');
                }

                renderCacheStats(d.after || {});
                flushBtn.textContent = 'SSR cache очищен';
                setTimeout(function() {
                    flushBtn.textContent = initialLabel;
                    flushBtn.disabled = false;
                }, 1200);
            })
            .catch(function(error) {
                alert('Ошибка очистки SSR cache: ' + error.message);
                flushBtn.textContent = initialLabel;
                flushBtn.disabled = false;
            });
        });
    }

    document.querySelectorAll('.nb-delete-block').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Удалить блок «' + btn.dataset.title + '»? Это действие нельзя отменить.')) return;
            btn.disabled = true;
            fetch(btn.dataset.url + '?csrf_token=' + encodeURIComponent(nbCsrfToken), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(btn.dataset.id) })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.ok) { location.reload(); }
                else { alert('Ошибка: ' + (d.error || '?')); btn.disabled = false; }
            })
            .catch(function() { btn.disabled = false; });
        });
    });
})();
</script>
