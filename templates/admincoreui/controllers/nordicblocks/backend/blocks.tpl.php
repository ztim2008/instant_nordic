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
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    transition: box-shadow .2s;
}
.nb-block-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); }
.nb-block-card__title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}
.nb-block-card__meta {
    font-size: .8rem;
    color: #718096;
    display: flex;
    align-items: center;
    gap: .75rem;
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
?>
<div class="nb-block-card">
    <h3 class="nb-block-card__title"><?= $title_esc ?></h3>
    <div class="nb-block-card__meta">
        <span class="nb-type-tag"><?= $type_esc ?></span>
        <span class="nb-badge nb-badge--<?= $status ?>"><?= $status === 'active' ? 'активен' : 'отключён' ?></span>
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
        ><i class="fa fa-trash"></i></button>
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
            fetch(btn.dataset.url, {
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
