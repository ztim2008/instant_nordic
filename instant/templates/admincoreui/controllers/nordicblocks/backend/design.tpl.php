<?php
$this->setPageTitle('Дизайн-система — NordicBlocks');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Дизайн-система');
$this->addMenuItems('admin_toolbar', $menu);

$token = function ($group, $key, $default = '') use ($tokens) {
    return $tokens[$group][$key] ?? $default;
};
$field_error = function ($key) use ($errors) {
    return isset($errors[$key]) ? (string) $errors[$key] : '';
};

$fonts = [
    'sans'    => 'Inter / System Sans',
    'serif'   => 'Playfair Display / Serif',
    'mono'    => 'JetBrains Mono',
    'display' => 'Montserrat / Display',
];
$font_faces = [
    'sans'    => "'Inter','Helvetica Neue',sans-serif",
    'serif'   => "'Playfair Display',Georgia,serif",
    'mono'    => "'JetBrains Mono','Courier New',monospace",
    'display' => "'Montserrat','Arial',sans-serif",
];

$base_colors = [
    'color_accent'     => ['Акцент', $token('colors', 'accent', '#b42318'), 'colors.accent'],
    'color_bg'         => ['Фон страницы', $token('colors', 'bg', '#ffffff'), 'colors.bg'],
    'color_bg_alt'     => ['Фон альт', $token('colors', 'bg_alt', '#f7f7f6'), 'colors.bg_alt'],
    'color_surface'    => ['Поверхность', $token('colors', 'surface', '#ffffff'), 'colors.surface'],
    'color_border'     => ['Граница', $token('colors', 'border', '#e5e7eb'), 'colors.border'],
    'color_text'       => ['Текст', $token('colors', 'text', '#1a1a1a'), 'colors.text'],
    'color_text_muted' => ['Текст muted', $token('colors', 'text_muted', '#6b7280'), 'colors.text_muted'],
];

$button_colors = [
    'button_primary_bg'     => ['Primary: фон', $token('colors', 'button_primary_bg', '#b42318'), 'colors.button_primary_bg'],
    'button_primary_bg_hover' => ['Primary: hover фон', $token('colors', 'button_primary_bg_hover', '#8a1910'), 'colors.button_primary_bg_hover'],
    'button_primary_text'   => ['Primary: текст', $token('colors', 'button_primary_text', '#ffffff'), 'colors.button_primary_text'],
    'button_primary_border' => ['Primary: бордер', $token('colors', 'button_primary_border', '#b42318'), 'colors.button_primary_border'],
    'button_outline_text'   => ['Outline: текст', $token('colors', 'button_outline_text', '#b42318'), 'colors.button_outline_text'],
    'button_outline_border' => ['Outline: бордер', $token('colors', 'button_outline_border', '#b42318'), 'colors.button_outline_border'],
    'button_ghost_text'     => ['Ghost: текст', $token('colors', 'button_ghost_text', '#1a1a1a'), 'colors.button_ghost_text'],
    'button_ghost_border'   => ['Ghost: бордер', $token('colors', 'button_ghost_border', '#e5e7eb'), 'colors.button_ghost_border'],
];
?>

<style><?= $tokens_css ?></style>
<style><?= $blocks_css ?></style>
<style id="nb-design-preview-tokens"><?= $inline_css ?></style>

<style>
.nb-ds { max-width: 1360px; }
.nb-ds-grid {
    display: grid;
    grid-template-columns: minmax(420px, 520px) 1fr;
    gap: 1.5rem;
    align-items: start;
    margin-top: 1.25rem;
}
@media (max-width: 1100px) {
    .nb-ds-grid { grid-template-columns: 1fr; }
}
.nb-presets {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.nb-preset-btn {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    padding: .6rem .9rem;
    border: 2px solid transparent;
    border-radius: 14px;
    background: #fff;
    color: #334155;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
    cursor: pointer;
    font-size: .82rem;
    font-weight: 600;
    transition: border-color .15s, box-shadow .15s, transform .12s;
}
.nb-preset-btn:hover {
    border-color: #3b82f6;
    box-shadow: 0 8px 28px rgba(15, 23, 42, .10);
    transform: translateY(-1px);
}
.nb-preset-btn.active {
    border-color: #3b82f6;
    background: #eff6ff;
}
.nb-preset-swatches {
    display: flex;
    gap: 2px;
    overflow: hidden;
    border-radius: 8px;
}
.nb-preset-swatches span { width: 14px; height: 28px; display: block; }

.nb-ds-form-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}
.nb-ds-section {
    padding: 1.15rem 1.35rem;
    border-bottom: 1px solid #f1f5f9;
}
.nb-ds-section:last-child { border-bottom: none; }
.nb-ds-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .95rem;
}
.nb-ds-section-title {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-size: .73rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #94a3b8;
}
.nb-ds-section-note {
    font-size: .75rem;
    color: #94a3b8;
}
.nb-ds-grid-2,
.nb-ds-grid-3 {
    display: grid;
    gap: .7rem;
}
.nb-ds-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.nb-ds-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
@media (max-width: 620px) {
    .nb-ds-grid-2,
    .nb-ds-grid-3 { grid-template-columns: 1fr; }
}
.nb-ds-field { margin-bottom: .85rem; }
.nb-ds-field:last-child { margin-bottom: 0; }
.nb-ds-field label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: .32rem;
}
.nb-ds-field small {
    display: block;
    margin-top: .25rem;
    font-size: .72rem;
    color: #94a3b8;
}
.nb-ds-field select,
.nb-ds-field input[type=text],
.nb-ds-field input[type=number] {
    width: 100%;
    padding: .48rem .72rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: .85rem;
    box-sizing: border-box;
    outline: none;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.nb-ds-field select:focus,
.nb-ds-field input[type=text]:focus,
.nb-ds-field input[type=number]:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
}
.nb-ds-field-err {
    margin-top: .28rem;
    font-size: .73rem;
    color: #dc2626;
}
.nb-color-row {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.nb-color-row input[type=text] { flex: 1; }
.nb-color-swatch {
    width: 38px;
    height: 38px;
    position: relative;
    flex-shrink: 0;
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid rgba(15, 23, 42, .10);
    cursor: pointer;
}
.nb-color-swatch input[type=color] {
    position: absolute;
    inset: -4px;
    width: calc(100% + 8px);
    height: calc(100% + 8px);
    opacity: 0;
    border: 0;
    cursor: pointer;
}
.nb-color-swatch-bg {
    position: absolute;
    inset: 0;
    border-radius: 7px;
}
.nb-font-preview,
.nb-mini-preview {
    margin-top: .5rem;
    padding: .75rem .9rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
}
.nb-mini-preview { font-size: .82rem; color: #475569; }
.nb-live-playground {
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, .10), transparent 38%),
        linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}
.nb-live-playground-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .85rem;
}
.nb-live-playground-head strong {
    display: block;
    margin-bottom: .18rem;
    font-size: .88rem;
    color: #0f172a;
}
.nb-live-playground-head span {
    font-size: .76rem;
    color: #64748b;
}
.nb-live-playground-main {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    margin-bottom: .8rem;
}
.nb-live-demo-button {
    min-width: 220px;
}
.nb-live-demo-button.is-demo-hover {
    transform: var(--nb-btn-hover-transform, none);
    box-shadow: var(--nb-btn-hover-shadow, none);
}
.nb-live-demo-button.is-demo-hover::after {
    opacity: var(--nb-btn-glint-opacity, 0);
    transform: translateX(135%);
}
.nb-live-demo-button.is-demo-hover.nb-btn--primary {
    background: var(--nb-btn-primary-bg-hover);
    color: var(--nb-btn-primary-text-hover);
    border-color: var(--nb-btn-primary-border-hover);
}
.nb-live-demo-button.is-demo-hover.nb-btn--outline {
    background: var(--nb-btn-outline-bg-hover);
    color: var(--nb-btn-outline-text-hover);
    border-color: var(--nb-btn-outline-border-hover);
}
.nb-live-demo-button.is-demo-hover.nb-btn--ghost {
    background: var(--nb-btn-ghost-bg-hover);
    color: var(--nb-btn-ghost-text-hover);
    border-color: var(--nb-btn-ghost-border-hover);
}
.nb-live-demo-button.is-demo-press {
    transform: var(--nb-btn-active-transform, scale(.98));
}
.nb-live-demo-button.is-demo-press.nb-btn--primary {
    background: var(--nb-btn-primary-bg-active);
    color: var(--nb-btn-primary-text-active);
    border-color: var(--nb-btn-primary-border-active);
}
.nb-live-demo-button.is-demo-press.nb-btn--outline {
    background: var(--nb-btn-outline-bg-active);
    color: var(--nb-btn-outline-text-active);
    border-color: var(--nb-btn-outline-border-active);
}
.nb-live-demo-button.is-demo-press.nb-btn--ghost {
    background: var(--nb-btn-ghost-bg-active);
    color: var(--nb-btn-ghost-text-active);
    border-color: var(--nb-btn-ghost-border-active);
}
.nb-live-replay-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: .65rem .95rem;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    color: #334155;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .15s, transform .12s, box-shadow .15s;
}
.nb-live-replay-btn:hover {
    border-color: #94a3b8;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    transform: translateY(-1px);
}
.nb-live-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.nb-live-pill {
    display: inline-flex;
    align-items: center;
    gap: .42rem;
    padding: .36rem .58rem;
    border: 1px solid #dbe4ef;
    border-radius: 999px;
    background: rgba(255,255,255,.82);
    font-size: .75rem;
    color: #475569;
}
.nb-live-swatch {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: var(--nb-btn-primary-bg-hover, #8a1910);
    border: 1px solid rgba(15, 23, 42, .12);
}
.nb-toggle {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    font-size: .85rem;
    color: #334155;
    cursor: pointer;
    user-select: none;
}
.nb-toggle input { margin: 0; }
.nb-ds-submit {
    padding: 1rem 1.35rem;
    background: #f8fafc;
}

.nb-ds-preview {
    position: sticky;
    top: 72px;
    overflow: hidden;
    border: 1px solid #dbe4ef;
    border-radius: 18px;
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
}
.nb-ds-preview-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .8rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(8px);
    font-size: .78rem;
    color: #64748b;
}
.nb-ds-preview-inner {
    padding: 1.4rem;
    background: var(--nb-color-bg, #fff);
}
.nb-pre-shell {
    padding: 1.45rem;
    border-radius: 22px;
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--nb-color-accent) 14%, transparent), transparent 38%),
        linear-gradient(180deg, var(--nb-color-bg, #fff) 0%, var(--nb-color-bg-alt, #f8fafc) 100%);
    color: var(--nb-color-text);
}
.nb-pre-eyebrow {
    margin-bottom: .55rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--nb-color-accent);
}
.nb-pre-h1 {
    margin: 0 0 .8rem;
    font-family: var(--nb-font-head);
    font-size: clamp(1.8rem, 3.8vw, 2.8rem);
    line-height: 1.04;
    letter-spacing: -.03em;
    color: var(--nb-color-text);
}
.nb-pre-lead {
    margin: 0 0 1.1rem;
    font-family: var(--nb-font-body);
    font-size: 1rem;
    line-height: 1.65;
    color: var(--nb-color-text-muted);
    max-width: 58ch;
}
.nb-pre-button-grid {
    display: grid;
    gap: .85rem;
    margin-bottom: 1.25rem;
}
.nb-pre-row-title {
    margin-bottom: .4rem;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #94a3b8;
}
.nb-pre-btns {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
}
.nb-pre-btn-demo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 156px;
    pointer-events: none;
}
.nb-pre-btn-demo.is-hover {
    transform: var(--nb-btn-hover-transform, none);
    box-shadow: var(--nb-btn-hover-shadow, none);
}
.nb-pre-btn-demo.is-active {
    transform: var(--nb-btn-active-transform, scale(.98));
}
.nb-pre-surfaces {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .85rem;
    margin-bottom: 1rem;
}
@media (max-width: 760px) {
    .nb-pre-surfaces { grid-template-columns: 1fr; }
}
.nb-pre-card {
    background: var(--nb-color-surface, #fff);
    border: var(--nb-border-width, 1px) solid var(--nb-color-border, #e5e7eb);
    border-radius: var(--nb-radius-card, 16px);
    box-shadow: var(--nb-shadow-card, none);
    padding: 1rem 1rem 1.05rem;
}
.nb-pre-card h4 {
    margin: 0 0 .35rem;
    font-family: var(--nb-font-head);
    font-size: 1rem;
    color: var(--nb-color-text);
}
.nb-pre-card p {
    margin: 0;
    font-family: var(--nb-font-body);
    font-size: .86rem;
    line-height: 1.65;
    color: var(--nb-color-text-muted);
}
.nb-pre-media {
    height: 120px;
    margin-bottom: .85rem;
    border-radius: var(--nb-radius-media, var(--nb-radius-card));
    background:
        linear-gradient(135deg, color-mix(in srgb, var(--nb-color-accent) 12%, transparent), transparent),
        linear-gradient(135deg, var(--nb-color-bg-alt), var(--nb-color-surface));
    border: var(--nb-border-width, 1px) solid color-mix(in srgb, var(--nb-color-border) 70%, transparent);
}
.nb-pre-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
}
@media (max-width: 760px) {
    .nb-pre-meta { grid-template-columns: 1fr; }
}
.nb-pre-chip {
    padding: .8rem .9rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: rgba(255,255,255,.72);
}
.nb-pre-chip strong {
    display: block;
    margin-bottom: .2rem;
    font-size: .8rem;
    color: #0f172a;
}
.nb-pre-chip span {
    font-size: .78rem;
    color: #64748b;
}
</style>

<div class="nb-ds">
    <div class="nb-presets" id="nbPresetStrip">
        <?php foreach ($presets as $pkey => $preset): ?>
        <button type="button" class="nb-preset-btn" data-preset="<?= htmlspecialchars($pkey, ENT_QUOTES) ?>">
            <span class="nb-preset-swatches">
                <span style="background:<?= htmlspecialchars($preset['color_accent'], ENT_QUOTES) ?>"></span>
                <span style="background:<?= htmlspecialchars($preset['color_bg'], ENT_QUOTES) ?>"></span>
                <span style="background:<?= htmlspecialchars($preset['color_text'], ENT_QUOTES) ?>"></span>
            </span>
            <?= htmlspecialchars($preset['name'], ENT_QUOTES) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="nb-ds-grid">
        <div class="nb-ds-form-card">
            <form id="nbDesignForm" method="post" action="">
                <?= html_csrf_token() ?>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head">
                        <div class="nb-ds-section-title"><i class="fa fa-palette"></i> Базовая палитра</div>
                        <div class="nb-ds-section-note">Главные цвета всей системы</div>
                    </div>
                    <div class="nb-ds-grid-2">
                        <?php foreach ($base_colors as $field => $meta): ?>
                        <div class="nb-ds-field">
                            <label><?= htmlspecialchars($meta[0], ENT_QUOTES) ?></label>
                            <div class="nb-color-row">
                                <div class="nb-color-swatch nb-swatch-outer">
                                    <div class="nb-color-swatch-bg" id="nbSwatchBg_<?= $field ?>" style="background:<?= htmlspecialchars($meta[1], ENT_QUOTES) ?>"></div>
                                    <input type="color" data-sync="<?= $field ?>" id="nbColorPicker_<?= $field ?>" value="<?= htmlspecialchars($meta[1], ENT_QUOTES) ?>">
                                </div>
                                <input type="text" name="<?= $field ?>" id="nbColorText_<?= $field ?>" value="<?= htmlspecialchars($meta[1], ENT_QUOTES) ?>" placeholder="#rrggbb">
                            </div>
                            <?php if ($field_error($meta[2])): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error($meta[2]), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head">
                        <div class="nb-ds-section-title"><i class="fa fa-font"></i> Типографика</div>
                        <div class="nb-ds-section-note">Глобальные семейства без зависимости от блоков</div>
                    </div>
                    <div class="nb-ds-grid-3">
                        <div class="nb-ds-field">
                            <label>Шрифт текста</label>
                            <select name="font_body" id="nbFontBody">
                                <?php foreach ($fonts as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('typography', 'font_body', 'sans') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="nb-font-preview" id="nbFontBodyPreview" style="font-family:<?= htmlspecialchars($font_faces[$token('typography', 'font_body', 'sans')] ?? $font_faces['sans'], ENT_QUOTES) ?>">Основной текст — читаемый, спокойный, рабочий.</div>
                            <?php if ($field_error('typography.font_body')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('typography.font_body'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Шрифт заголовков</label>
                            <select name="font_head" id="nbFontHead">
                                <?php foreach ($fonts as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('typography', 'font_head', 'sans') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="nb-font-preview" id="nbFontHeadPreview" style="font-family:<?= htmlspecialchars($font_faces[$token('typography', 'font_head', 'sans')] ?? $font_faces['sans'], ENT_QUOTES) ?>;font-size:1.3rem;font-weight:700">Заголовок системы</div>
                            <?php if ($field_error('typography.font_head')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('typography.font_head'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Шрифт кнопок</label>
                            <select name="font_button" id="nbFontButton">
                                <option value="">Как основной текст</option>
                                <?php foreach ($fonts as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('typography', 'font_button', '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="nb-font-preview" id="nbFontButtonPreview" style="font-family:<?= htmlspecialchars($font_faces[$token('typography', 'font_button', $token('typography', 'font_body', 'sans'))] ?? $font_faces[$token('typography', 'font_body', 'sans')] ?? $font_faces['sans'], ENT_QUOTES) ?>;font-size:1rem;font-weight:600">Кнопка / CTA</div>
                            <?php if ($field_error('typography.font_button')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('typography.font_button'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head">
                        <div class="nb-ds-section-title"><i class="fa fa-hand-pointer-o"></i> Кнопки</div>
                        <div class="nb-ds-section-note">Одна механика для всех блоков</div>
                    </div>
                    <div class="nb-live-playground">
                        <div class="nb-live-playground-head">
                            <div>
                                <strong>Живой preview кнопки</strong>
                                <span>Наведи курсор, нажми или вручную переиграй hover-анимацию.</span>
                            </div>
                            <span class="nb-live-pill" id="nbLiveAnimationLabel">Анимация: Lift</span>
                        </div>
                        <div class="nb-live-playground-main">
                            <a href="#" class="nb-btn nb-btn--primary nb-live-demo-button" id="nbLivePreviewButton" onclick="return false">Живая CTA-кнопка</a>
                            <button type="button" class="nb-live-replay-btn" id="nbLivePreviewReplay">Повторить hover</button>
                        </div>
                        <div class="nb-live-meta">
                            <span class="nb-live-pill" id="nbLivePreviewState">Состояние: обычное</span>
                            <span class="nb-live-pill"><span class="nb-live-swatch" id="nbLiveHoverSwatch"></span> Hover color</span>
                            <span class="nb-live-pill" id="nbLiveStyleLabel">Стиль: Primary</span>
                        </div>
                    </div>
                    <div class="nb-ds-grid-2">
                        <div class="nb-ds-field">
                            <label>Стиль по умолчанию</label>
                            <select name="btn_style">
                                <?php foreach (['primary' => 'Primary / заливка', 'outline' => 'Outline / контур', 'ghost' => 'Ghost / прозрачная'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('buttons', 'style', 'primary') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('buttons.style')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('buttons.style'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Размер кнопок</label>
                            <select name="btn_size">
                                <?php foreach (['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('buttons', 'size', 'md') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('buttons.size')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('buttons.size'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Hover-анимация</label>
                            <select name="btn_hover_animation">
                                <?php foreach (['none' => 'Без анимации', 'lift' => 'Lift', 'grow' => 'Grow', 'glow' => 'Glow', 'glint' => 'Glint'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('buttons', 'hover_animation', 'lift') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('buttons.hover_animation')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('buttons.hover_animation'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Радиус кнопок</label>
                            <select name="button_radius_preset">
                                <?php foreach (['none' => 'Нет 0px', 'sm' => '4px', 'md' => '8px', 'lg' => '16px', 'xl' => '24px', 'pill' => 'Пилюля'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('radii', 'button', 'md') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('radii.button')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('radii.button'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Цвет блика Glint</label>
                            <div class="nb-color-row">
                                <div class="nb-color-swatch nb-swatch-outer">
                                    <div class="nb-color-swatch-bg" id="nbSwatchBg_btn_glint_color" style="background:<?= htmlspecialchars($token('buttons', 'glint_color', '#ffffff'), ENT_QUOTES) ?>"></div>
                                    <input type="color" data-sync="btn_glint_color" id="nbColorPicker_btn_glint_color" value="<?= htmlspecialchars($token('buttons', 'glint_color', '#ffffff'), ENT_QUOTES) ?>">
                                </div>
                                <input type="text" name="btn_glint_color" id="nbColorText_btn_glint_color" value="<?= htmlspecialchars($token('buttons', 'glint_color', '#ffffff'), ENT_QUOTES) ?>" placeholder="#ffffff">
                            </div>
                            <?php if ($field_error('buttons.glint_color')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('buttons.glint_color'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Длительность Glint, мс</label>
                            <input type="number" name="btn_glint_duration" min="250" max="3000" step="50" value="<?= (int) $token('buttons', 'glint_duration', 900) ?>">
                            <?php if ($field_error('buttons.glint_duration')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('buttons.glint_duration'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="nb-ds-grid-2" style="margin-top:1rem">
                        <?php foreach ($button_colors as $field => $meta): ?>
                        <div class="nb-ds-field">
                            <label><?= htmlspecialchars($meta[0], ENT_QUOTES) ?></label>
                            <div class="nb-color-row">
                                <div class="nb-color-swatch nb-swatch-outer">
                                    <div class="nb-color-swatch-bg" id="nbSwatchBg_<?= $field ?>" style="background:<?= htmlspecialchars($meta[1], ENT_QUOTES) ?>"></div>
                                    <input type="color" data-sync="<?= $field ?>" id="nbColorPicker_<?= $field ?>" value="<?= htmlspecialchars($meta[1], ENT_QUOTES) ?>">
                                </div>
                                <input type="text" name="<?= $field ?>" id="nbColorText_<?= $field ?>" value="<?= htmlspecialchars($meta[1], ENT_QUOTES) ?>" placeholder="#rrggbb">
                            </div>
                            <?php if ($field_error($meta[2])): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error($meta[2]), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="nb-ds-section">
                    <div class="nb-ds-section-head">
                        <div class="nb-ds-section-title"><i class="fa fa-clone"></i> Карточки и поверхности</div>
                        <div class="nb-ds-section-note">Скругления, бордеры, медиа и motion</div>
                    </div>
                    <div class="nb-ds-grid-2">
                        <div class="nb-ds-field">
                            <label>Базовый радиус системы</label>
                            <select name="radius_preset">
                                <?php foreach (['none' => 'Нет 0px', 'sm' => '4px', 'md' => '8px', 'lg' => '16px', 'xl' => '24px', 'pill' => 'Пилюля'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('radii', 'base', 'md') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('radii.base')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('radii.base'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Радиус карточек</label>
                            <select name="card_radius_preset">
                                <?php foreach (['none' => 'Нет 0px', 'sm' => '4px', 'md' => '8px', 'lg' => '16px', 'xl' => '24px', 'pill' => 'Пилюля'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('radii', 'card', 'lg') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('radii.card')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('radii.card'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Радиус медиа</label>
                            <select name="media_radius_preset">
                                <?php foreach (['none' => 'Нет 0px', 'sm' => '4px', 'md' => '8px', 'lg' => '16px', 'xl' => '24px', 'pill' => 'Пилюля'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('radii', 'media', 'lg') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('radii.media')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('radii.media'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Тень карточек</label>
                            <select name="shadow_preset">
                                <?php foreach (['none' => 'Без тени', 'sm' => 'Лёгкая', 'md' => 'Средняя', 'lg' => 'Глубокая'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('cards', 'shadow_preset', 'md') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('cards.shadow_preset')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('cards.shadow_preset'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Толщина бордера, px</label>
                            <input type="number" name="card_border_width" min="0" max="6" step="1" value="<?= (int) $token('cards', 'border_width', 1) ?>">
                            <?php if ($field_error('cards.border_width')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('cards.border_width'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                        <div class="nb-ds-field">
                            <label>Ритм секций</label>
                            <select name="section_spacing">
                                <?php foreach (['compact' => 'Компактный', 'comfortable' => 'Комфортный', 'spacious' => 'Просторный'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $token('layout', 'section_spacing', 'comfortable') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($field_error('layout.section_spacing')): ?><div class="nb-ds-field-err"><?= htmlspecialchars($field_error('layout.section_spacing'), ENT_QUOTES) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="nb-ds-field" style="margin-top:1rem">
                        <input type="hidden" name="surface_motion" value="0">
                        <label class="nb-toggle">
                            <input type="checkbox" name="surface_motion" value="1" <?= $token('cards', 'surface_motion', true) ? 'checked' : '' ?>>
                            Плавное движение поверхностей и карточек при hover/обновлении
                        </label>
                    </div>
                </div>

                <div class="nb-ds-submit">
                    <button type="submit" name="submit" value="1" class="btn btn-primary" style="width:100%;padding:.72rem 1rem;text-decoration:none">
                        <i class="fa fa-save"></i>&nbsp; Сохранить фундамент дизайн-системы
                    </button>
                </div>
            </form>
        </div>

        <div class="nb-ds-preview" id="nbDsPreview">
            <div class="nb-ds-preview-bar">
                <span><i class="fa fa-eye"></i> Живой preview глобальных токенов</span>
                <span>меняется на лету</span>
            </div>
            <div class="nb-ds-preview-inner">
                <div class="nb-pre-shell">
                    <div class="nb-pre-eyebrow">Global Foundation</div>
                    <h1 class="nb-pre-h1">Единая база для всех будущих блоков</h1>
                    <p class="nb-pre-lead">Здесь видно не только палитру, но и реальное поведение кнопок, карточек и радиусов. После этого новые блоки можно строить уже на готовой механике, а не заново изобретать стили внутри каждого render.</p>

                    <div class="nb-pre-button-grid">
                        <div>
                            <div class="nb-pre-row-title">Primary</div>
                            <div class="nb-pre-btns">
                                <a href="#" class="nb-btn nb-btn--primary nb-pre-btn-demo" onclick="return false">Normal</a>
                                <a href="#" class="nb-btn nb-btn--primary nb-pre-btn-demo is-hover" onclick="return false" style="background:var(--nb-btn-primary-bg-hover);color:var(--nb-btn-primary-text-hover);border-color:var(--nb-btn-primary-border-hover)">Hover</a>
                                <a href="#" class="nb-btn nb-btn--primary nb-pre-btn-demo is-active" onclick="return false" style="background:var(--nb-btn-primary-bg-active);color:var(--nb-btn-primary-text-active);border-color:var(--nb-btn-primary-border-active)">Pressed</a>
                            </div>
                        </div>
                        <div>
                            <div class="nb-pre-row-title">Outline и Ghost</div>
                            <div class="nb-pre-btns">
                                <a href="#" class="nb-btn nb-btn--outline nb-pre-btn-demo" onclick="return false">Outline</a>
                                <a href="#" class="nb-btn nb-btn--outline nb-pre-btn-demo is-hover" onclick="return false" style="background:var(--nb-btn-outline-bg-hover);color:var(--nb-btn-outline-text-hover);border-color:var(--nb-btn-outline-border-hover)">Outline Hover</a>
                                <a href="#" class="nb-btn nb-btn--ghost nb-pre-btn-demo is-hover" onclick="return false" style="background:var(--nb-btn-ghost-bg-hover);color:var(--nb-btn-ghost-text-hover);border-color:var(--nb-btn-ghost-border-hover)">Ghost Hover</a>
                            </div>
                        </div>
                    </div>

                    <div class="nb-pre-surfaces">
                        <div class="nb-pre-card">
                            <div class="nb-pre-media"></div>
                            <h4>Карточка контента</h4>
                            <p>Радиус, тень, бордер и фон теперь управляются глобально и могут переиспользоваться между блоками без локальных костылей.</p>
                        </div>
                        <div class="nb-pre-card" style="background:var(--nb-color-bg-alt)">
                            <h4>Альтернативная поверхность</h4>
                            <p>Подходит для zebra-секций, мягких плашек, карточек преимуществ и фото-блоков с разным media radius.</p>
                        </div>
                    </div>

                    <div class="nb-pre-meta">
                        <div class="nb-pre-chip">
                            <strong>Шрифтовая пара</strong>
                            <span>Глобально задает семью, блоки сверху выбирают локальную жирность.</span>
                        </div>
                        <div class="nb-pre-chip">
                            <strong>Кнопочная механика</strong>
                            <span>Normal / hover / pressed теперь живут в одной системе, а не в каждом блоке отдельно.</span>
                        </div>
                        <div class="nb-pre-chip">
                            <strong>Поверхности</strong>
                            <span>Общие радиусы, медиа-радиусы, бордер и shadow дают единый visual language.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var nbPresets = <?= json_encode($presets, JSON_UNESCAPED_UNICODE) ?>;
var nbFontFaces = {
    'sans': "'Inter','Helvetica Neue',sans-serif",
    'serif': "'Playfair Display',Georgia,serif",
    'mono': "'JetBrains Mono','Courier New',monospace",
    'display': "'Montserrat','Arial',sans-serif"
};
var nbButtonStyleLabels = {
    'primary': 'Primary',
    'outline': 'Outline',
    'ghost': 'Ghost'
};
var nbButtonAnimationLabels = {
    'none': 'Без анимации',
    'lift': 'Lift',
    'grow': 'Grow',
    'glow': 'Glow',
    'glint': 'Glint'
};
var nbLivePreviewCurrentState = 'base';
var nbButtonAnimationPresets = {
    'none':  { transform: 'none', shadow: 'none', glint: '0' },
    'lift':  { transform: 'translateY(-2px)', shadow: '0 8px 32px 0 rgb(0 0 0 / .12)', glint: '0' },
    'grow':  { transform: 'scale(1.03)', shadow: '0 4px 12px 0 rgb(0 0 0 / .08)', glint: '0' },
    'glow':  { transform: 'none', shadow: '', glint: '0' },
    'glint': { transform: 'none', shadow: '0 4px 12px 0 rgb(0 0 0 / .08)', glint: '1' }
};

function nbSyncColorField(key) {
    var textEl = document.getElementById('nbColorText_' + key);
    var picker = document.getElementById('nbColorPicker_' + key);
    var swatch = document.getElementById('nbSwatchBg_' + key);
    if (!textEl) { return; }
    var value = textEl.value.trim();
    if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(value)) {
        if (picker) { picker.value = value; }
        if (swatch) { swatch.style.background = value; }
    }
}

function nbUpdateFontPreview(selectId, previewId, fallbackKey) {
    var select = document.getElementById(selectId);
    var preview = document.getElementById(previewId);
    if (!preview || !select) { return; }
    var next = select.value || fallbackKey;
    if (!next || !nbFontFaces[next]) { next = fallbackKey; }
    preview.style.fontFamily = nbFontFaces[next] || nbFontFaces[fallbackKey];
}

function nbSetFieldValue(name, value) {
    var nodes = document.querySelectorAll('[name="' + name + '"]');
    if (!nodes.length) { return; }

    nodes.forEach(function(node) {
        if (node.type === 'checkbox') {
            node.checked = String(value) === '1' || String(value).toLowerCase() === 'true';
            return;
        }
        node.value = value;
    });

    if (document.getElementById('nbColorText_' + name)) {
        nbSyncColorField(name);
    }
}

function nbGetFieldValue(name, fallback) {
    var node = document.querySelector('[name="' + name + '"]');
    if (!node) {
        return fallback || '';
    }
    if (node.type === 'checkbox') {
        return node.checked ? '1' : '0';
    }
    var value = String(node.value || '').trim();
    return value || (fallback || '');
}

function nbNormalizeHex(value, fallback) {
    var raw = String(value || '').trim();
    if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(raw)) {
        return raw.toLowerCase();
    }
    var safeFallback = String(fallback || '').trim();
    if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(safeFallback)) {
        return safeFallback.toLowerCase();
    }
    return '#b42318';
}

function nbExpandHex(value) {
    var hex = nbNormalizeHex(value, '#000000').slice(1);
    if (hex.length === 3) {
        return hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    return hex;
}

function nbDarkenHex(value, amount) {
    var hex = nbExpandHex(value);
    var shift = Math.max(0, parseInt(amount, 10) || 0);
    var red = Math.max(0, parseInt(hex.slice(0, 2), 16) - shift);
    var green = Math.max(0, parseInt(hex.slice(2, 4), 16) - shift);
    var blue = Math.max(0, parseInt(hex.slice(4, 6), 16) - shift);
    return '#' + [red, green, blue].map(function(channel) {
        return channel.toString(16).padStart(2, '0');
    }).join('');
}

function nbHexToRgba(value, alpha) {
    var hex = nbExpandHex(value);
    var opacity = Number(alpha);
    if (isNaN(opacity)) {
        opacity = 1;
    }
    opacity = Math.max(0, Math.min(1, opacity));
    return 'rgba(' + parseInt(hex.slice(0, 2), 16) + ', ' + parseInt(hex.slice(2, 4), 16) + ', ' + parseInt(hex.slice(4, 6), 16) + ', ' + opacity + ')';
}

function nbClampInt(value, min, max, fallback) {
    var parsed = parseInt(value, 10);
    if (isNaN(parsed)) {
        parsed = fallback;
    }
    return Math.max(min, Math.min(max, parsed));
}

function nbBuildLivePreviewVars() {
    var accent = nbNormalizeHex(nbGetFieldValue('color_accent', '#b42318'), '#b42318');
    var bgAlt = nbNormalizeHex(nbGetFieldValue('color_bg_alt', '#f7f7f6'), '#f7f7f6');
    var border = nbNormalizeHex(nbGetFieldValue('color_border', '#e5e7eb'), '#e5e7eb');
    var text = nbNormalizeHex(nbGetFieldValue('color_text', '#1a1a1a'), '#1a1a1a');

    var primaryBg = nbNormalizeHex(nbGetFieldValue('button_primary_bg', accent), accent);
    var primaryText = nbNormalizeHex(nbGetFieldValue('button_primary_text', '#ffffff'), '#ffffff');
    var primaryBorder = nbNormalizeHex(nbGetFieldValue('button_primary_border', primaryBg), primaryBg);
    var primaryBgHover = nbNormalizeHex(nbGetFieldValue('button_primary_bg_hover', nbDarkenHex(primaryBg, 12)), nbDarkenHex(primaryBg, 12));
    var primaryBgActive = nbDarkenHex(primaryBgHover, 10);

    var outlineText = nbNormalizeHex(nbGetFieldValue('button_outline_text', accent), accent);
    var outlineBorder = nbNormalizeHex(nbGetFieldValue('button_outline_border', accent), accent);

    var ghostText = nbNormalizeHex(nbGetFieldValue('button_ghost_text', text), text);
    var ghostBorder = nbNormalizeHex(nbGetFieldValue('button_ghost_border', border), border);

    var animationKey = nbGetFieldValue('btn_hover_animation', 'lift');
    var animationPreset = nbButtonAnimationPresets[animationKey] || nbButtonAnimationPresets.lift;
    var hoverShadow = animationKey === 'glow'
        ? '0 0 0 4px ' + nbHexToRgba(accent, 0.16)
        : animationPreset.shadow;
    var glintColor = nbNormalizeHex(nbGetFieldValue('btn_glint_color', '#ffffff'), '#ffffff');
    var glintDuration = nbClampInt(nbGetFieldValue('btn_glint_duration', 900), 250, 3000, 900);

    return {
        '--nb-btn-primary-bg': primaryBg,
        '--nb-btn-primary-text': primaryText,
        '--nb-btn-primary-border': primaryBorder,
        '--nb-btn-primary-bg-hover': primaryBgHover,
        '--nb-btn-primary-text-hover': primaryText,
        '--nb-btn-primary-border-hover': primaryBgHover,
        '--nb-btn-primary-bg-active': primaryBgActive,
        '--nb-btn-primary-text-active': primaryText,
        '--nb-btn-primary-border-active': primaryBgActive,
        '--nb-btn-outline-bg': 'transparent',
        '--nb-btn-outline-text': outlineText,
        '--nb-btn-outline-border': outlineBorder,
        '--nb-btn-outline-bg-hover': primaryBgHover,
        '--nb-btn-outline-text-hover': primaryText,
        '--nb-btn-outline-border-hover': outlineBorder,
        '--nb-btn-outline-bg-active': primaryBgActive,
        '--nb-btn-outline-text-active': primaryText,
        '--nb-btn-outline-border-active': primaryBgActive,
        '--nb-btn-ghost-bg': 'transparent',
        '--nb-btn-ghost-text': ghostText,
        '--nb-btn-ghost-border': ghostBorder,
        '--nb-btn-ghost-bg-hover': bgAlt,
        '--nb-btn-ghost-text-hover': ghostText,
        '--nb-btn-ghost-border-hover': ghostBorder,
        '--nb-btn-ghost-bg-active': nbDarkenHex(bgAlt, 8),
        '--nb-btn-ghost-text-active': ghostText,
        '--nb-btn-ghost-border-active': ghostBorder,
        '--nb-btn-hover-transform': animationPreset.transform,
        '--nb-btn-hover-shadow': hoverShadow,
        '--nb-btn-active-transform': 'scale(.98)',
        '--nb-btn-glint-opacity': animationPreset.glint,
        '--nb-btn-glint-color': glintColor,
        '--nb-btn-glint-duration': glintDuration + 'ms'
    };
}

function nbApplyLivePreviewVars(liveButton) {
    if (!liveButton) { return; }
    var vars = nbBuildLivePreviewVars();
    Object.keys(vars).forEach(function(name) {
        liveButton.style.setProperty(name, vars[name]);
    });
}

function nbGetLivePreviewStyleValue(liveButton, name, fallback) {
    if (!liveButton) {
        return fallback || '';
    }
    var value = getComputedStyle(liveButton).getPropertyValue(name).trim();
    return value || (fallback || '');
}

function nbSetLivePreviewState(text) {
    var state = document.getElementById('nbLivePreviewState');
    if (state) {
        state.textContent = text;
    }
}

function nbGetCssVar(name, fallback) {
    var value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || (fallback || '');
}

function nbApplyLivePreviewVisualState(stateName) {
    var liveButton = document.getElementById('nbLivePreviewButton');
    var styleSelect = document.querySelector('[name="btn_style"]');
    if (!liveButton || !styleSelect) { return; }

    nbApplyLivePreviewVars(liveButton);
    nbLivePreviewCurrentState = stateName === 'hover' || stateName === 'press' ? stateName : 'base';

    var style = styleSelect.value || 'primary';
    var prefix = '--nb-btn-' + style;
    var suffix = '';

    if (stateName === 'hover') {
        suffix = '-hover';
    }
    if (stateName === 'press') {
        suffix = '-active';
    }

    liveButton.classList.remove('is-demo-hover', 'is-demo-press');
    if (stateName === 'hover') {
        liveButton.classList.add('is-demo-hover');
    }
    if (stateName === 'press') {
        liveButton.classList.add('is-demo-press');
    }

    liveButton.style.backgroundColor = nbGetLivePreviewStyleValue(liveButton, prefix + '-bg' + suffix, style === 'ghost' || style === 'outline' ? 'transparent' : '#b42318');
    liveButton.style.color = nbGetLivePreviewStyleValue(liveButton, prefix + '-text' + suffix, '#ffffff');
    liveButton.style.borderColor = nbGetLivePreviewStyleValue(liveButton, prefix + '-border' + suffix, '#b42318');
    liveButton.style.transform = stateName === 'press'
        ? nbGetLivePreviewStyleValue(liveButton, '--nb-btn-active-transform', 'scale(.98)')
        : stateName === 'hover'
            ? nbGetLivePreviewStyleValue(liveButton, '--nb-btn-hover-transform', 'none')
            : 'none';
    liveButton.style.boxShadow = stateName === 'hover'
        ? nbGetLivePreviewStyleValue(liveButton, '--nb-btn-hover-shadow', 'none')
        : 'none';
}

function nbSyncLivePreviewButton() {
    var liveButton = document.getElementById('nbLivePreviewButton');
    if (!liveButton) { return; }

    var styleSelect = document.querySelector('[name="btn_style"]');
    var animationSelect = document.querySelector('[name="btn_hover_animation"]');
    var hoverColor = document.getElementById('nbColorText_button_primary_bg_hover');
    var hoverSwatch = document.getElementById('nbLiveHoverSwatch');
    var styleLabel = document.getElementById('nbLiveStyleLabel');
    var animationLabel = document.getElementById('nbLiveAnimationLabel');
    var style = styleSelect ? styleSelect.value : 'primary';
    var animation = animationSelect ? animationSelect.value : 'lift';

    liveButton.className = 'nb-btn nb-live-demo-button nb-btn--' + style;
    nbApplyLivePreviewVars(liveButton);

    if (styleLabel) {
        styleLabel.textContent = 'Стиль: ' + (nbButtonStyleLabels[style] || 'Primary');
    }
    if (animationLabel) {
        animationLabel.textContent = 'Анимация: ' + (nbButtonAnimationLabels[animation] || 'Lift');
    }
    if (hoverSwatch && hoverColor && /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(hoverColor.value.trim())) {
        hoverSwatch.style.background = hoverColor.value.trim();
    }

    nbApplyLivePreviewVisualState(nbLivePreviewCurrentState);
}

function nbReplayLivePreviewHover() {
    var liveButton = document.getElementById('nbLivePreviewButton');
    if (!liveButton) { return; }
    liveButton.classList.remove('is-demo-hover');
    void liveButton.offsetWidth;
    nbApplyLivePreviewVisualState('hover');
    nbSetLivePreviewState('Состояние: demo hover');
    window.setTimeout(function() {
        nbApplyLivePreviewVisualState('base');
        nbSetLivePreviewState('Состояние: обычное');
    }, 1100);
}

document.querySelectorAll('.nb-preset-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var preset = nbPresets[btn.dataset.preset];
        if (!preset) { return; }
        document.querySelectorAll('.nb-preset-btn').forEach(function(node) { node.classList.remove('active'); });
        btn.classList.add('active');
        Object.keys(preset).forEach(function(key) {
            if (key === 'name') { return; }
            nbSetFieldValue(key, preset[key]);
        });
        nbUpdateFontPreview('nbFontBody', 'nbFontBodyPreview', 'sans');
        nbUpdateFontPreview('nbFontHead', 'nbFontHeadPreview', 'sans');
        var buttonFallback = document.getElementById('nbFontButton') && document.getElementById('nbFontButton').value ? document.getElementById('nbFontButton').value : (document.getElementById('nbFontBody') ? document.getElementById('nbFontBody').value : 'sans');
        nbUpdateFontPreview('nbFontButton', 'nbFontButtonPreview', buttonFallback || 'sans');
        nbSyncLivePreviewButton();
        updatePreviewTokens();
    });
});

document.querySelectorAll('input[type=color][data-sync]').forEach(function(picker) {
    var key = picker.dataset.sync;
    var textEl = document.getElementById('nbColorText_' + key);
    var swatch = document.getElementById('nbSwatchBg_' + key);
    picker.addEventListener('input', function() {
        if (textEl) { textEl.value = picker.value; }
        if (swatch) { swatch.style.background = picker.value; }
        updatePreviewTokens();
    });
    if (textEl) {
        textEl.addEventListener('input', function() {
            nbSyncColorField(key);
            updatePreviewTokens();
        });
    }
    var outer = picker.closest('.nb-swatch-outer');
    if (outer) {
        outer.addEventListener('click', function(e) {
            if (e.target !== picker) { picker.click(); }
        });
    }
});

document.querySelectorAll('#nbDesignForm select, #nbDesignForm input[type=number], #nbDesignForm input[type=checkbox]').forEach(function(el) {
    el.addEventListener('change', function() {
        nbUpdateFontPreview('nbFontBody', 'nbFontBodyPreview', 'sans');
        nbUpdateFontPreview('nbFontHead', 'nbFontHeadPreview', 'sans');
        var buttonFallback = document.getElementById('nbFontButton') && document.getElementById('nbFontButton').value ? document.getElementById('nbFontButton').value : (document.getElementById('nbFontBody') ? document.getElementById('nbFontBody').value : 'sans');
        nbUpdateFontPreview('nbFontButton', 'nbFontButtonPreview', buttonFallback || 'sans');
        nbSyncLivePreviewButton();
        updatePreviewTokens();
    });
});

document.querySelectorAll('#nbDesignForm input[type=text]').forEach(function(el) {
    if (el.id && el.id.indexOf('nbColorText_') === 0) { return; }
    el.addEventListener('input', updatePreviewTokens);
});

(function() {
    var liveButton = document.getElementById('nbLivePreviewButton');
    var replayButton = document.getElementById('nbLivePreviewReplay');
    if (!liveButton) { return; }

    liveButton.addEventListener('mouseenter', function() {
        nbApplyLivePreviewVisualState('hover');
        nbSetLivePreviewState('Состояние: hover');
    });
    liveButton.addEventListener('mouseleave', function() {
        nbApplyLivePreviewVisualState('base');
        nbSetLivePreviewState('Состояние: обычное');
    });
    liveButton.addEventListener('mousedown', function() {
        nbApplyLivePreviewVisualState('press');
        nbSetLivePreviewState('Состояние: нажатие');
    });
    liveButton.addEventListener('mouseup', function() {
        nbApplyLivePreviewVisualState('hover');
        nbSetLivePreviewState('Состояние: hover');
    });
    liveButton.addEventListener('focus', function() {
        nbApplyLivePreviewVisualState('hover');
        nbSetLivePreviewState('Состояние: focus');
    });
    liveButton.addEventListener('blur', function() {
        nbApplyLivePreviewVisualState('base');
        nbSetLivePreviewState('Состояние: обычное');
    });

    if (replayButton) {
        replayButton.addEventListener('click', function() {
            nbReplayLivePreviewHover();
        });
    }
})();

var nbPreviewTimer = null;
function updatePreviewTokens() {
    clearTimeout(nbPreviewTimer);
    nbPreviewTimer = setTimeout(function() {
        nbSyncLivePreviewButton();
        var form = document.getElementById('nbDesignForm');
        if (!form) { return; }
        var params = new URLSearchParams();
        var formData = new FormData(form);
        formData.forEach(function(value, key) {
            params.set(key, value);
        });
        fetch('?preview_tokens=1&' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(response) { return response.text(); })
            .then(function(css) {
                if (!css) { return; }
                var styleEl = document.getElementById('nb-design-preview-tokens');
                if (styleEl) { styleEl.textContent = css; }
                nbSyncLivePreviewButton();
            })
            .catch(function() {});
    }, 120);
}

nbUpdateFontPreview('nbFontBody', 'nbFontBodyPreview', 'sans');
nbUpdateFontPreview('nbFontHead', 'nbFontHeadPreview', 'sans');
nbUpdateFontPreview('nbFontButton', 'nbFontButtonPreview', document.getElementById('nbFontBody') ? document.getElementById('nbFontBody').value : 'sans');
nbSyncLivePreviewButton();
</script>