<?php
$this->setPageTitle('NordicBlocks — Страницы');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Страницы');
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'add',
    'title' => 'Новая страница',
    'href'  => $create_page_url,
    'icon'  => 'plus'
]);
?>

<style>
.nb-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
    margin-top: 1.5rem;
}
.nb-page-card {
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
.nb-page-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); }
.nb-page-card__title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}
.nb-page-card__meta {
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
.nb-badge--draft     { background: #fef3c7; color: #92400e; }
.nb-badge--published { background: #d1fae5; color: #065f46; }
.nb-page-card__actions {
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
}
.nb-btn-sm--edit    { background: #3b82f6; color: #fff; }
.nb-btn-sm--edit:hover { background: #2563eb; }
.nb-btn-sm--view    { background: #f1f5f9; color: #334155; border-color: #e2e8f0; }
.nb-btn-sm--view:hover { background: #e2e8f0; }
.nb-btn-sm--pub     { background: #10b981; color: #fff; }
.nb-btn-sm--pub:hover { background: #059669; }
.nb-btn-sm--draft   { background: #fbbf24; color: #fff; }
.nb-btn-sm--draft:hover { background: #d97706; }
.nb-empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #718096;
}
.nb-empty-state h3 { color: #4a5568; margin-bottom: .5rem; }
</style>

<?php if (!$pages): ?>
<div class="nb-empty-state">
    <h3>Страниц пока нет</h3>
    <p>Создайте первую страницу и начните наполнять её блоками.</p>
    <a href="<?= htmlspecialchars($create_page_url, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary mt-3">
        + Новая страница
    </a>
</div>
<?php else: ?>

<div class="nb-admin-grid">
<?php foreach ($pages as $page):
    $status    = $page['status'] === 'published' ? 'published' : 'draft';
    $status_label = $status === 'published' ? 'Опубликована' : 'Черновик';
    $key_esc   = htmlspecialchars($page['key'],   ENT_QUOTES, 'UTF-8');
    $title_esc = htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8');
    $editor_url = htmlspecialchars($page['editor_url'], ENT_QUOTES, 'UTF-8');
    $view_url   = htmlspecialchars($page['view_url'],   ENT_QUOTES, 'UTF-8');
?>
<div class="nb-page-card">
    <h3 class="nb-page-card__title"><?= $title_esc ?></h3>
    <div class="nb-page-card__meta">
        <code><?= $key_esc ?></code>
        <span class="nb-badge nb-badge--<?= $status ?>"><?= $status_label ?></span>
        <span><?= (int)$page['blocks_count'] ?> бл.</span>
    </div>
    <div class="nb-page-card__actions">
        <a href="<?= $editor_url ?>" class="nb-btn-sm nb-btn-sm--edit">
            <i class="fa fa-edit"></i> Редактор
        </a>
        <a href="<?= $view_url ?>" target="_blank" class="nb-btn-sm nb-btn-sm--view">
            <i class="fa fa-eye"></i> Просмотр
        </a>
        <?php if ($status === 'draft'): ?>
        <button
            class="nb-btn-sm nb-btn-sm--pub nb-status-toggle"
            data-id="<?= (int)$page['id'] ?>"
            data-status="published"
            data-url="<?= htmlspecialchars($set_status_url, ENT_QUOTES, 'UTF-8') ?>"
        >
            <i class="fa fa-check"></i> Опубликовать
        </button>
        <?php else: ?>
        <button
            class="nb-btn-sm nb-btn-sm--draft nb-status-toggle"
            data-id="<?= (int)$page['id'] ?>"
            data-status="draft"
            data-url="<?= htmlspecialchars($set_status_url, ENT_QUOTES, 'UTF-8') ?>"
        >
            <i class="fa fa-undo"></i> В черновик
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<script>
document.querySelectorAll('.nb-status-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id     = btn.dataset.id;
        var status = btn.dataset.status;
        var url    = btn.dataset.url;
        btn.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(id), status: status })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) { location.reload(); }
            else { alert('Ошибка: ' + (d.error || '?')); btn.disabled = false; }
        })
        .catch(function() { btn.disabled = false; });
    });
});
</script>
