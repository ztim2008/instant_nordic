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
    width: min(480px, 92vw);
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

@media (max-width: 640px) {
    .nb-block-card__body {
        padding: .95rem;
    }
    .nb-block-card__schematic-title {
        max-width: 150px;
        font-size: .92rem;
    }
}
</style>

<?php if (!$blocks): ?>
<div class="nb-empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin:0 auto 1rem"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/></svg>
    <h3>Блоков пока нет</h3>
    <p>Создайте первый блок и добавьте его как виджет в схему страницы.</p>
    <button class="btn btn-primary mt-3" id="nb-open-create">+ Создать блок</button>
    <div style="margin-top:.9rem">
        <a class="btn btn-default" href="<?= htmlspecialchars($widgets_url, ENT_QUOTES, 'UTF-8') ?>">
            Открыть структуру (размещение)
        </a>
    </div>
</div>
<?php else: ?>

<div style="margin-top:1rem;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap">
    <button class="btn btn-primary" id="nb-open-create">+ Создать блок</button>
    <a class="btn btn-default" href="<?= htmlspecialchars($widgets_url, ENT_QUOTES, 'UTF-8') ?>">
        Разместить блок в структуре
    </a>
    <span style="font-size:.8rem;color:#64748b">
        Шаги: создать → настроить → добавить виджет NordicBlocks Block в нужную позицию
    </span>
</div>

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
            <label for="nb-block-type">Тип блока</label>
            <select id="nb-block-type" name="type" required>
                <?php foreach ($block_types as $t_key => $t_label): ?>
                <option value="<?= htmlspecialchars($t_key, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($t_label, ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label for="nb-block-title">Название (для себя)</label>
            <input type="text" id="nb-block-title" name="title" placeholder="Например: Hero — Главная страница" required maxlength="255">

            <div class="nb-modal__footer">
                <button type="button" class="btn btn-default" id="nb-close-create">Отмена</button>
                <button type="submit" class="btn btn-primary">Создать</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var nbCsrfToken = <?= json_encode(cmsForm::getCSRFToken(), JSON_UNESCAPED_UNICODE) ?>;
    var overlay  = document.getElementById('nb-create-modal');
    var btnOpen  = document.getElementById('nb-open-create');
    var btnClose = document.getElementById('nb-close-create');

    if (btnOpen)  { btnOpen.addEventListener('click',  function() { overlay.classList.add('is-open'); }); }
    if (btnClose) { btnClose.addEventListener('click', function() { overlay.classList.remove('is-open'); }); }
    if (overlay)  { overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.classList.remove('is-open'); }); }

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
