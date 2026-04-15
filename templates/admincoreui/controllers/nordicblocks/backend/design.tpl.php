<?php
$this->setPageTitle('Дизайн-система — NordicBlocks');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Дизайн-система');
$this->addMenuItems('admin_toolbar', $menu);
?>

<!-- Токены применяются к превью -->
<style id="nb-design-preview-tokens"><?= $inline_css ?></style>

<style>
.nb-ds { max-width: 1200px; }
.nb-ds-grid {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 1.5rem;
    align-items: start;
    margin-top: 1.25rem;
}
@media (max-width: 900px) { .nb-ds-grid { grid-template-columns: 1fr; } }
.nb-presets {
    display: flex;
    gap: .75rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}
.nb-preset-btn {
    border: 2px solid transparent;
    border-radius: 10px;
    background: #fff;
    padding: .55rem .9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .6rem;
    font-size: .8rem;
    font-weight: 600;
    color: #374151;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
    transition: border-color .15s, box-shadow .15s, transform .1s;
}
.nb-preset-btn:hover { border-color: #3b82f6; box-shadow: 0 2px 8px rgba(0,0,0,.12); transform: translateY(-1px); }
.nb-preset-btn.active { border-color: #3b82f6; background: #eff6ff; }
.nb-preset-swatches { display: flex; gap: 2px; border-radius: 4px; overflow: hidden; }
.nb-preset-swatches span { display: block; width: 12px; height: 24px; }
.nb-ds-form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.nb-ds-section { padding: 1.1rem 1.35rem; border-bottom: 1px solid #f1f5f9; }
.nb-ds-section:last-child { border-bottom: none; }
.nb-ds-section-head {
    display: flex; align-items: center; gap: .5rem;
    font-size: .72rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: #9ca3af; margin-bottom: .9rem;
}
.nb-ds-field { margin-bottom: .85rem; }
.nb-ds-field:last-child { margin-bottom: 0; }
.nb-ds-field label { display: block; font-size: .78rem; font-weight: 500; color: #4b5563; margin-bottom: .3rem; }
.nb-ds-field select,
.nb-ds-field input[type=text] {
    width: 100%; padding: .42rem .7rem;
    border: 1px solid #d1d5db; border-radius: 6px;
    font-size: .85rem; box-sizing: border-box; outline: none; background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.nb-ds-field select:focus,
.nb-ds-field input[type=text]:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.nb-ds-field-err { font-size: .73rem; color: #dc2626; margin-top: .25rem; }
.nb-color-row { display: flex; gap: .5rem; align-items: center; }
.nb-color-swatch {
    width: 38px; height: 38px; border-radius: 6px;
    border: 1px solid rgba(0,0,0,.12); flex-shrink: 0;
    cursor: pointer; overflow: hidden; position: relative;
}
.nb-color-swatch input[type=color] {
    position: absolute; inset: -4px;
    width: calc(100% + 8px); height: calc(100% + 8px);
    border: none; padding: 0; cursor: pointer; opacity: 0;
}
.nb-color-swatch-bg { position: absolute; inset: 0; border-radius: 5px; transition: background .1s; }
.nb-color-row input[type=text] { flex: 1; }
.nb-palette-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
.nb-font-preview {
    padding: .75rem 1rem; background: #f9fafb;
    border-radius: 6px; border: 1px solid #e5e7eb;
    margin-top: .5rem; font-size: 1.1rem; color: #111827;
    transition: font-family .2s;
}
.nb-ds-submit { padding: 1rem 1.35rem; }
.nb-ds-preview {
    position: sticky; top: 72px;
    background: var(--nb-color-bg);
    border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;
    transition: background .3s;
}
.nb-ds-preview-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: .6rem 1rem; background: rgba(0,0,0,.04);
    font-size: .75rem; color: #6b7280; border-bottom: 1px solid #e5e7eb;
}
.nb-ds-preview-inner { padding: 2rem 2rem 1.5rem; }
.nb-pre-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--nb-color-accent); margin-bottom: .5rem; }
.nb-pre-h1 { font-family: var(--nb-font-head); font-size: clamp(1.6rem,4vw,2.4rem); font-weight: 800; line-height: 1.15; color: var(--nb-color-text); margin: 0 0 .75rem; letter-spacing: -.02em; }
.nb-pre-lead { font-family: var(--nb-font-body); font-size: .95rem; line-height: 1.65; color: var(--nb-color-text-muted); margin: 0 0 1.25rem; }
.nb-pre-btns { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.nb-pre-card { background: var(--nb-color-surface, var(--nb-color-bg)); border: 1px solid var(--nb-color-border, #e5e7eb); border-radius: var(--nb-radius-card,8px); box-shadow: var(--nb-shadow-card,none); padding: 1rem 1.25rem; margin-bottom: 1rem; }
.nb-pre-card h4 { font-family: var(--nb-font-head); font-size: 1rem; font-weight: 700; color: var(--nb-color-text); margin: 0 0 .4rem; }
.nb-pre-card p { font-family: var(--nb-font-body); font-size: .85rem; line-height: 1.6; color: var(--nb-color-text-muted); margin: 0; }
.nb-pre-palette { display: flex; gap: .4rem; margin-top: .5rem; }
.nb-pre-swatch { height: 28px; flex: 1; border-radius: 4px; }
.nb-pre-labels { display: flex; gap: .4rem; margin-top: .3rem; }
.nb-pre-labels span { flex: 1; font-size: .6rem; color: #9ca3af; text-align: center; overflow: hidden; white-space: nowrap; }
</style>

<div class="nb-ds">
    <div class="nb-presets" id="nbPresetStrip">
        <?php foreach ($presets as $pkey => $p):
            $acc = htmlspecialchars($p['color_accent'], ENT_QUOTES);
            $bg2 = htmlspecialchars($p['color_bg'], ENT_QUOTES);
            $tx  = htmlspecialchars($p['color_text'], ENT_QUOTES);
        ?>
        <button type="button" class="nb-preset-btn" data-preset="<?= htmlspecialchars($pkey, ENT_QUOTES) ?>">
            <div class="nb-preset-swatches">
                <span style="background:<?= $acc ?>"></span>
                <span style="background:<?= $bg2 ?>"></span>
                <span style="background:<?= $tx ?>"></span>
            </div>
            <?= htmlspecialchars($p['name'], ENT_QUOTES) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="nb-ds-grid">
        <div class="nb-ds-form-card">
            <form id="nbDesignForm" method="post" action="">
                <?= html_csrf_token() ?>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head"><i class="fa fa-palette"></i> Цвета</div>
                    <div class="nb-palette-grid">
                    <?php
                    $colorFields = [
                        'color_accent'     => 'Акцент',
                        'color_bg'         => 'Фон страницы',
                        'color_bg_alt'     => 'Фон (альт)',
                        'color_surface'    => 'Поверхность (карточки)',
                        'color_border'     => 'Граница / разделитель',
                        'color_text'       => 'Текст',
                        'color_text_muted' => 'Текст приглушённый',
                    ];
                    foreach ($colorFields as $field => $label):
                        $val = htmlspecialchars($tokens[$field] ?? '#000000', ENT_QUOTES);
                        $err = !empty($errors[$field]) ? htmlspecialchars($errors[$field], ENT_QUOTES) : '';
                    ?>
                    <div class="nb-ds-field" data-field="<?= $field ?>">
                        <label><?= $label ?></label>
                        <div class="nb-color-row">
                            <div class="nb-color-swatch nb-swatch-outer" title="Выбрать цвет" style="border-color:rgba(0,0,0,.1)">
                                <div class="nb-color-swatch-bg" id="nbSwatchBg_<?= $field ?>" style="background:<?= $val ?>"></div>
                                <input type="color" data-sync="<?= $field ?>" value="<?= $val ?>" id="nbColorPicker_<?= $field ?>">
                            </div>
                            <input type="text" name="<?= $field ?>" value="<?= $val ?>"
                                   id="nbColorText_<?= $field ?>" placeholder="#rrggbb">
                        </div>
                        <?php if ($err): ?><div class="nb-ds-field-err"><?= $err ?></div><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head"><i class="fa fa-font"></i> Типографика</div>
                    <?php
                    $fonts = ['sans'=>'Inter / System Sans','serif'=>'Playfair Display / Serif','mono'=>'JetBrains Mono','display'=>'Montserrat / Display'];
                    $fontFaces = ['sans'=>"'Inter','Helvetica Neue',sans-serif",'serif'=>"'Playfair Display',Georgia,serif",'mono'=>"'JetBrains Mono','Courier New',monospace",'display'=>"'Montserrat','Arial',sans-serif"];
                    ?>
                    <div class="nb-ds-field">
                        <label>Шрифт основного текста</label>
                        <select name="font_body" id="nbFontBody">
                            <?php foreach ($fonts as $v => $l): ?><option value="<?= $v ?>" <?= ($tokens['font_body']??'sans')===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
                        </select>
                        <div class="nb-font-preview" id="nbFontBodyPreview" style="font-family:<?= $fontFaces[$tokens['font_body']??'sans'] ?>">
                            Создавайте сайты быстро — Build fast
                        </div>
                    </div>
                    <div class="nb-ds-field">
                        <label>Шрифт заголовков</label>
                        <select name="font_head" id="nbFontHead">
                            <?php foreach ($fonts as $v => $l): ?><option value="<?= $v ?>" <?= ($tokens['font_head']??'sans')===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
                        </select>
                        <div class="nb-font-preview" id="nbFontHeadPreview" style="font-family:<?= $fontFaces[$tokens['font_head']??'sans'] ?>;font-size:1.4rem;font-weight:700">
                            Заголовок страницы — Page Heading
                        </div>
                    </div>
                </div>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head"><i class="fa fa-vector-square"></i> Форма и ритм</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                        <div class="nb-ds-field">
                            <label>Скругления</label>
                            <select name="radius_preset">
                                <?php foreach (['none'=>'Нет 0px','sm'=>'Малые 4px','md'=>'Средние 8px','lg'=>'Большие 16px','xl'=>'XL 24px','pill'=>'Пилюля'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($tokens['radius_preset']??'md')===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="nb-ds-field">
                            <label>Тени</label>
                            <select name="shadow_preset">
                                <?php foreach (['none'=>'Без теней','sm'=>'Лёгкие','md'=>'Средние','lg'=>'Глубокие'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($tokens['shadow_preset']??'md')===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="nb-ds-field">
                            <label>Ритм секций</label>
                            <select name="section_spacing">
                                <?php foreach (['compact'=>'Компактный','comfortable'=>'Комфортный','spacious'=>'Просторный'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($tokens['section_spacing']??'comfortable')===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="nb-ds-field">
                            <label>Стиль кнопок</label>
                            <select name="btn_style">
                                <?php foreach (['primary'=>'Заливка','outline'=>'Контур','ghost'=>'Прозрачные'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($tokens['btn_style']??'primary')===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="nb-ds-submit">
                    <button type="submit" name="submit" value="1" class="btn btn-primary" style="width:100%;padding:.65rem">
                        <i class="fa fa-save"></i>&nbsp; Сохранить дизайн-систему
                    </button>
                </div>
            </form>
        </div>

        <div class="nb-ds-preview" id="nbDsPreview">
            <div class="nb-ds-preview-bar">
                <span><i class="fa fa-eye"></i> Живой превью</span>
                <span style="font-weight:400;color:#9ca3af">меняется в реальном времени</span>
            </div>
            <div class="nb-ds-preview-inner">
                <div class="nb-pre-eyebrow">NordicBlocks</div>
                <h1 class="nb-pre-h1">Заголовок страницы</h1>
                <p class="nb-pre-lead">Типографика, цвета и отступы применяются глобально ко всем блокам. Меняйте токены слева — и сразу видите результат здесь.</p>
                <div class="nb-pre-btns">
                    <a href="#" class="nb-btn nb-btn--primary" onclick="return false" style="text-decoration:none">Главная кнопка</a>
                    <a href="#" class="nb-btn nb-btn--outline" onclick="return false" style="text-decoration:none">Второй вариант</a>
                </div>
                <div class="nb-pre-card">
                    <h4>Карточка с текстом</h4>
                    <p>Скругления, тени и поверхность берутся из токенов. Меняйте форму и сразу видите результат.</p>
                </div>
                <div class="nb-pre-card" style="background:var(--nb-color-bg-alt)">
                    <h4>Альтернативный фон</h4>
                    <p>Используется для зебра-секций и чередующихся блоков.</p>
                </div>
                <div style="margin-top:1rem">
                    <div style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.4rem">Палитра токенов</div>
                    <div class="nb-pre-palette">
                        <div class="nb-pre-swatch" style="background:var(--nb-color-accent)"></div>
                        <div class="nb-pre-swatch" style="background:var(--nb-color-bg)"></div>
                        <div class="nb-pre-swatch" style="background:var(--nb-color-bg-alt)"></div>
                        <div class="nb-pre-swatch" style="background:var(--nb-color-surface,var(--nb-color-bg));border:1px solid var(--nb-color-border,#e5e7eb)"></div>
                        <div class="nb-pre-swatch" style="background:var(--nb-color-text)"></div>
                        <div class="nb-pre-swatch" style="background:var(--nb-color-text-muted)"></div>
                    </div>
                    <div class="nb-pre-labels">
                        <span>Акцент</span><span>Фон</span><span>Фон альт</span><span>Surface</span><span>Текст</span><span>Muted</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var nbPresets    = <?= json_encode($presets, JSON_UNESCAPED_UNICODE) ?>;
var nbFontFaces  = {'sans':"'Inter','Helvetica Neue',sans-serif",'serif':"'Playfair Display',Georgia,serif",'mono':"'JetBrains Mono','Courier New',monospace",'display':"'Montserrat','Arial',sans-serif"};
var nbColorKeys  = ['color_accent','color_bg','color_bg_alt','color_surface','color_border','color_text','color_text_muted'];

document.querySelectorAll('.nb-preset-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var preset = nbPresets[btn.dataset.preset];
        if (!preset) { return; }
        document.querySelectorAll('.nb-preset-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        nbColorKeys.forEach(function(k) {
            if (!preset[k]) { return; }
            var txt  = document.getElementById('nbColorText_'   + k);
            var pick = document.getElementById('nbColorPicker_' + k);
            var sbg  = document.getElementById('nbSwatchBg_'    + k);
            if (txt)  { txt.value  = preset[k]; }
            if (pick) { pick.value = preset[k]; }
            if (sbg)  { sbg.style.background = preset[k]; }
        });
        ['font_body','font_head','radius_preset','shadow_preset','section_spacing','btn_style'].forEach(function(k) {
            if (!preset[k]) { return; }
            var sel = document.querySelector('select[name="'+k+'"]');
            if (sel) { sel.value = preset[k]; sel.dispatchEvent(new Event('change')); }
        });
        updatePreviewTokens();
    });
});

document.querySelectorAll('input[type=color][data-sync]').forEach(function(picker) {
    var f = picker.dataset.sync;
    var textEl = document.getElementById('nbColorText_' + f);
    var sbg    = document.getElementById('nbSwatchBg_'  + f);
    picker.addEventListener('input', function() {
        if (textEl) { textEl.value = picker.value; }
        if (sbg)    { sbg.style.background = picker.value; }
        updatePreviewTokens();
    });
    if (textEl) {
        textEl.addEventListener('input', function() {
            var v = textEl.value.trim();
            if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)) {
                picker.value = v;
                if (sbg) { sbg.style.background = v; }
            }
            updatePreviewTokens();
        });
    }
    var outer = picker.closest('.nb-swatch-outer');
    if (outer) { outer.addEventListener('click', function(e) { if (e.target !== picker) { picker.click(); } }); }
});

document.querySelectorAll('#nbDesignForm select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        if (sel.name === 'font_body') {
            var p = document.getElementById('nbFontBodyPreview');
            if (p) { p.style.fontFamily = nbFontFaces[sel.value] || nbFontFaces['sans']; }
        }
        if (sel.name === 'font_head') {
            var p = document.getElementById('nbFontHeadPreview');
            if (p) { p.style.fontFamily = (nbFontFaces[sel.value] || nbFontFaces['sans']); }
        }
        updatePreviewTokens();
    });
});

var nbPreviewTimer = null;
function updatePreviewTokens() {
    clearTimeout(nbPreviewTimer);
    nbPreviewTimer = setTimeout(function() {
        var fd = new FormData(document.getElementById('nbDesignForm'));
        var p  = new URLSearchParams();
        for (var pair of fd.entries()) { p.set(pair[0], pair[1]); }
        fetch('?preview_tokens=1&' + p.toString(), { headers: {'X-Requested-With':'XMLHttpRequest'} })
            .then(function(r) { return r.text(); })
            .then(function(css) {
                if (!css) { return; }
                var el = document.getElementById('nb-design-preview-tokens');
                if (el) { el.textContent = css; }
            }).catch(function(){});
    }, 120);
}
</script>
