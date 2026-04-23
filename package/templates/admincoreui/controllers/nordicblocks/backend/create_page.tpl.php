<?php
$this->setPageTitle('Новая страница — NordicBlocks');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Страницы', $this->href_to('pages'));
$this->addBreadcrumb('Новая страница');
$this->addMenuItems('admin_toolbar', $menu);
?>

<style>
.nb-form-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    max-width: 520px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.nb-form-card h2 { margin: 0 0 1.5rem; font-size: 1.15rem; font-weight: 600; }
.nb-field { margin-bottom: 1.25rem; }
.nb-field label { display: block; font-size: .85rem; font-weight: 500; color: #374151; margin-bottom: .35rem; }
.nb-field input {
    width: 100%;
    padding: .5rem .75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: .95rem;
    outline: none;
    transition: border-color .15s;
    box-sizing: border-box;
}
.nb-field input:focus { border-color: #3b82f6; }
.nb-field-hint { font-size: .75rem; color: #6b7280; margin-top: .3rem; }
.nb-field-error { font-size: .78rem; color: #dc2626; margin-top: .3rem; }
</style>

<div class="nb-form-card mt-3">
    <h2>Новая страница</h2>

    <form method="post" action="">
        <?= html_csrf_token() ?>

        <div class="nb-field">
            <label for="nb_title">Название страницы</label>
            <input
                type="text"
                id="nb_title"
                name="title"
                value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Главная страница"
                autocomplete="off"
            >
            <?php if (!empty($errors['title'])): ?>
            <div class="nb-field-error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="nb-field">
            <label for="nb_key">URL-ключ (slug)</label>
            <input
                type="text"
                id="nb_key"
                name="key"
                value="<?= htmlspecialchars($_POST['key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="homepage"
                autocomplete="off"
                pattern="[a-z0-9\-_]+"
            >
            <div class="nb-field-hint">Только строчные латинские буквы, цифры, дефис, подчёркивание.</div>
            <?php if (!empty($errors['key'])): ?>
            <div class="nb-field-error"><?= htmlspecialchars($errors['key'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" name="submit" value="1" class="btn btn-primary">
            Создать и перейти в редактор
        </button>
    </form>
</div>

<script>
(function() {
    var titleInput = document.getElementById('nb_title');
    var keyInput   = document.getElementById('nb_key');
    var keyDirty   = keyInput.value !== '';

    function slugify(s) {
        return s.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/, '')
            .slice(0, 80);
    }

    titleInput.addEventListener('input', function() {
        if (!keyDirty) {
            keyInput.value = slugify(titleInput.value);
        }
    });

    keyInput.addEventListener('input', function() {
        keyDirty = keyInput.value !== '';
    });
})();
</script>
