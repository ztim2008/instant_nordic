<?php
$this->setPageTitle('Дизайн-система — NordicBlocks');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Дизайн-система');
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'save',
    'title' => 'Сохранить',
    'href'  => '#',
    'icon'  => 'save',
    'onclick' => 'document.getElementById("nbDesignForm").submit(); return false;'
]);
?>

<!-- Токены применяются к превью -->
<style id="nb-design-preview-tokens"><?= $inline_css ?></style>

<style>
.nb-design-layout { display: grid; grid-template-columns: 380px 1fr; gap: 1.5rem; align-items: start; margin-top: 1rem; }
.nb-design-form   { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; }
.nb-design-form h3 { font-size: .9rem; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .05em; margin: 1.25rem 0 .75rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; }
.nb-design-form h3:first-child { margin-top: 0; padding-top: 0; border-top: none; }
.nb-design-field  { margin-bottom: 1rem; }
.nb-design-field label { display: block; font-size: .82rem; font-weight: 500; color: #374151; margin-bottom: .3rem; }
.nb-design-field input[type=text],
.nb-design-field input[type=color],
.nb-design-field select {
    width: 100%;
    padding: .45rem .7rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: .88rem;
    box-sizing: border-box;
    outline: none;
    transition: border-color .15s;
}
.nb-design-field input[type=text]:focus,
.nb-design-field select:focus { border-color: #3b82f6; }
.nb-design-field input[type=color] { height: 38px; padding: .25rem .3rem; cursor: pointer; }
.nb-design-field-error { font-size: .75rem; color: #dc2626; margin-top: .25rem; }
.nb-design-color-row { display: grid; grid-template-columns: 1fr 48px; gap: .5rem; align-items: end; }
.nb-design-color-row input[type=text]  { grid-column: 1; }
.nb-design-color-row input[type=color] { grid-column: 2; width: 48px; }

/* Превью */
.nb-design-preview {
    background: var(--nb-color-bg);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--nb-shadow-md);
    position: sticky;
    top: 80px;
}
.nb-design-preview__bar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: .65rem 1rem;
    font-size: .78rem;
    color: #64748b;
    font-weight: 500;
}
.nb-design-preview__body { padding: var(--nb-space-2xl) var(--nb-space-xl); }
.nb-preview-eyebrow {
    font-size: var(--nb-text-sm);
    font-weight: var(--nb-weight-semibold);
    letter-spacing: var(--nb-tracking-wide);
    text-transform: uppercase;
    color: var(--nb-color-accent);
    margin-bottom: var(--nb-space-sm);
}
.nb-preview-h1 {
    font-family: var(--nb-font-head);
    font-size: var(--nb-text-4xl);
    font-weight: var(--nb-weight-black);
    line-height: var(--nb-leading-tight);
    letter-spacing: var(--nb-tracking-tight);
    color: var(--nb-color-text);
    margin-bottom: var(--nb-space-md);
}
.nb-preview-lead {
    font-family: var(--nb-font-body);
    font-size: var(--nb-text-xl);
    line-height: var(--nb-leading-relaxed);
    color: var(--nb-color-text-muted);
    margin-bottom: var(--nb-space-lg);
    max-width: 54ch;
}
.nb-preview-btns { display: flex; flex-wrap: wrap; gap: var(--nb-space-sm); }
.nb-preview-card {
    background: var(--nb-color-surface);
    border: 1px solid var(--nb-color-border);
    border-radius: var(--nb-radius-card);
    box-shadow: var(--nb-shadow-card);
    padding: var(--nb-space-lg);
    margin-top: var(--nb-space-xl);
}
.nb-preview-card h4 { font-family: var(--nb-font-head); font-size: var(--nb-text-lg); color: var(--nb-color-text); margin-bottom: var(--nb-space-sm); }
.nb-preview-card p  { font-family: var(--nb-font-body); font-size: var(--nb-text-base); color: var(--nb-color-text-muted); margin: 0; }
</style>

<div class="nb-design-layout">

    <!-- Форма токенов -->
    <div class="nb-design-form">
        <form id="nbDesignForm" method="post" action="">
            <?= html_csrf_token() ?>

            <?php
            $t = $tokens;
            $e = $errors;
            function nb_field_error($errors, $key) {
                if (!empty($errors[$key])) {
                    echo '<div class="nb-design-field-error">' . htmlspecialchars($errors[$key], ENT_QUOTES, 'UTF-8') . '</div>';
                }
            }
            function nb_color_field($name, $label, $value, $errors) {
                $v = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                echo '<div class="nb-design-field">';
                echo '<label>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';
                echo '<div class="nb-design-color-row">';
                echo '<input type="text"  name="' . $name . '" value="' . $v . '" placeholder="#rrggbb" pattern="#[0-9a-fA-F]{3,6}">';
                echo '<input type="color" data-sync="' . $name . '" value="' . $v . '" title="Выбрать цвет">';
                echo '</div>';
                nb_field_error($errors, $name);
                echo '</div>';
            }
            ?>

            <h3>Цвета</h3>
            <?php nb_color_field('color_accent',     'Акцент',         $t['color_accent'],    $e) ?>
            <?php nb_color_field('color_bg',         'Фон',            $t['color_bg'],        $e) ?>
            <?php nb_color_field('color_bg_alt',     'Фон (альт)',      $t['color_bg_alt'],    $e) ?>
            <?php nb_color_field('color_text',       'Текст',          $t['color_text'],      $e) ?>
            <?php nb_color_field('color_text_muted', 'Текст (muted)',   $t['color_text_muted'],$e) ?>

            <h3>Типографика</h3>
            <div class="nb-design-field">
                <label>Шрифт: тело</label>
                <select name="font_body">
                    <option value="sans"  <?= $t['font_body']==='sans'  ? 'selected':'' ?>>Sans-serif (Inter)</option>
                    <option value="serif" <?= $t['font_body']==='serif' ? 'selected':'' ?>>Serif (Playfair)</option>
                </select>
                <?php nb_field_error($e, 'font_body') ?>
            </div>
            <div class="nb-design-field">
                <label>Шрифт: заголовки</label>
                <select name="font_head">
                    <option value="sans"  <?= $t['font_head']==='sans'  ? 'selected':'' ?>>Sans-serif (Inter)</option>
                    <option value="serif" <?= $t['font_head']==='serif' ? 'selected':'' ?>>Serif (Playfair)</option>
                </select>
                <?php nb_field_error($e, 'font_head') ?>
            </div>

            <h3>Форма интерфейса</h3>
            <div class="nb-design-field">
                <label>Скругления</label>
                <select name="radius_preset">
                    <?php foreach (['none'=>'Нет (0px)','sm'=>'Малые (4px)','md'=>'Средние (8px)','lg'=>'Большие (16px)','xl'=>'XL (24px)','pill'=>'Пилюля (∞)'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $t['radius_preset']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <?php nb_field_error($e, 'radius_preset') ?>
            </div>
            <div class="nb-design-field">
                <label>Тени</label>
                <select name="shadow_preset">
                    <?php foreach (['none'=>'Без теней','sm'=>'Лёгкие','md'=>'Средние','lg'=>'Глубокие'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $t['shadow_preset']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <?php nb_field_error($e, 'shadow_preset') ?>
            </div>

            <h3>Ритм и кнопки</h3>
            <div class="nb-design-field">
                <label>Ритм между секциями</label>
                <select name="section_spacing">
                    <?php foreach (['compact'=>'Компактный','comfortable'=>'Комфортный','spacious'=>'Просторный'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $t['section_spacing']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <?php nb_field_error($e, 'section_spacing') ?>
            </div>
            <div class="nb-design-field">
                <label>Стиль кнопок</label>
                <select name="btn_style">
                    <?php foreach (['primary'=>'Заливка','outline'=>'Контур','ghost'=>'Прозрачные'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $t['btn_style']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <?php nb_field_error($e, 'btn_style') ?>
            </div>

            <button type="submit" name="submit" value="1" class="btn btn-primary mt-2" style="width:100%">
                Сохранить дизайн-систему
            </button>
        </form>
    </div>

    <!-- Живой превью -->
    <div class="nb-design-preview" id="nbDesignPreview">
        <div class="nb-design-preview__bar">Превью — меняется в реальном времени</div>
        <div class="nb-design-preview__body">
            <div class="nb-preview-eyebrow">NordicBlocks</div>
            <h1 class="nb-preview-h1">Заголовок страницы</h1>
            <p class="nb-preview-lead">Подзаголовок: типографика, цвета и отступы настраиваются глобально для всех блоков.</p>
            <div class="nb-preview-btns">
                <a href="#" class="nb-btn nb-btn--primary" onclick="return false">Главная кнопка</a>
                <a href="#" class="nb-btn nb-btn--outline" onclick="return false">Второй вариант</a>
            </div>
            <div class="nb-preview-card">
                <h4>Карточка с текстом</h4>
                <p>Скругления, тени и поверхность — из токенов. Меняйте форму и сразу видите результат.</p>
            </div>
        </div>
    </div>

</div>

<script>
/* ── Синхронизация color picker ↔ text input ── */
document.querySelectorAll('input[type=color][data-sync]').forEach(function(picker) {
    var textInput = document.querySelector('input[name="' + picker.dataset.sync + '"]');
    if (!textInput) { return; }

    picker.addEventListener('input', function() {
        textInput.value = picker.value;
        updatePreviewTokens();
    });
    textInput.addEventListener('input', function() {
        var v = textInput.value.trim();
        if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)) {
            picker.value = v;
        }
        updatePreviewTokens();
    });
});

/* ── Остальные поля — живой превью ── */
document.querySelectorAll('#nbDesignForm select').forEach(function(sel) {
    sel.addEventListener('change', updatePreviewTokens);
});

function updatePreviewTokens() {
    var fd     = new FormData(document.getElementById('nbDesignForm'));
    var params = new URLSearchParams();
    for (var [k, v] of fd.entries()) { params.set(k, v); }

    fetch('?preview_tokens=1&' + params.toString())
        .then(function(r) { return r.text(); })
        .catch(function() { return ''; })
        .then(function(css) {
            if (css) {
                document.getElementById('nb-design-preview-tokens').textContent = css;
            }
        });
}
</script>
