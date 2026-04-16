<?php
/**
 * NordicBlocks — Редактор блока (standalone block, не страница)
 * Переменные: $block, $block_registry, $inline_css, $save_url, $canvas_url, $back_url, $widgets_url
 */
$block_title_esc = htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8');
$block_type      = htmlspecialchars($block['type'],  ENT_QUOTES, 'UTF-8');
$block_id        = (int) $block['id'];
$schema          = $block_registry[$block['type']]['schema'] ?? [];
$fields          = $schema['fields'] ?? [];
$props           = $block['props'] ?? [];
$field_groups    = [];

foreach ($fields as $field) {
    if (!is_array($field)) {
        continue;
    }

    $section_key = (string) ($field['section'] ?? 'general');
    if (!isset($field_groups[$section_key])) {
        $field_groups[$section_key] = [
            'label' => (string) ($field['section_label'] ?? 'Основное'),
            'hint'  => (string) ($field['section_hint'] ?? ''),
            'fields'=> [],
        ];
    }

    $field_groups[$section_key]['fields'][] = $field;
}

$this->setPageTitle('Редактор: ' . $block_title_esc);
$this->addBreadcrumb('NordicBlocks', $back_url);
$this->addBreadcrumb('Блоки', $back_url);
$this->addBreadcrumb($block_title_esc);
$this->addMenuItems('admin_toolbar', $menu);
?>
<style>
/* ══════════════════════════════════════════════════
   EDITOR SHELL
   ══════════════════════════════════════════════════ */
#nbe-shell {
    position: fixed;
    inset: 0;
    top: 55px; /* admin nav height */
    display: flex;
    flex-direction: column;
    background: #f1f5f9;
    z-index: 100;
}

/* ── Top Bar ─────────────────────────────────────── */
#nbe-topbar {
    height: 52px;
    background: #1e293b;
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: 0 .75rem;
    flex-shrink: 0;
    border-bottom: 1px solid #0f172a;
    z-index: 10;
}
.nbe-back {
    color: #94a3b8;
    font-size: .78rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: .3rem;
    padding: .3rem .5rem;
    border-radius: 5px;
    white-space: nowrap;
}
.nbe-back:hover { background: rgba(255,255,255,.08); color: #e2e8f0; }
.nbe-sep { width: 1px; height: 24px; background: #334155; margin: 0 .15rem; }
.nbe-page-name {
    color: #f1f5f9;
    font-size: .85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .35rem;
}
.nbe-page-name span { color: #64748b; font-family: monospace; font-size: .75rem; }
.nbe-spacer { flex: 1; }
.nbe-vp-btns { display: flex; background: #0f172a; border-radius: 7px; padding: 2px; gap: 2px; }
.nbe-vp-btn {
    padding: .3rem .7rem;
    font-size: .75rem;
    font-weight: 500;
    color: #64748b;
    border: none;
    background: none;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .3rem;
    transition: background .15s, color .15s;
}
.nbe-vp-btn.active { background: #1e293b; color: #e2e8f0; }
.nbe-vp-btn:hover:not(.active) { color: #94a3b8; }
.nbe-tb-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .38rem .85rem;
    font-size: .78rem;
    font-weight: 500;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background .15s, transform .08s;
    white-space: nowrap;
}
.nbe-tb-btn:active { transform: scale(.96); }
.nbe-btn-save  { background: #22c55e; color: #fff; }
.nbe-btn-save:hover { background: #16a34a; }
.nbe-btn-save.saving { background: #475569; pointer-events: none; }
.nbe-btn-save.saved  { background: #0ea5e9; }
.nbe-btn-save.dirty  { outline: 2px solid #f59e0b; outline-offset: 1px; }
.nbe-btn-view  { background: rgba(255,255,255,.08); color: #cbd5e1; }
.nbe-btn-view:hover { background: rgba(255,255,255,.14); }
.nbe-btn-add {
    background: #3b82f6;
    color: #fff;
    font-size: .8rem;
}
.nbe-btn-add:hover { background: #2563eb; }

/* ── Body ────────────────────────────────────────── */
#nbe-body {
    flex: 1;
    display: flex;
    overflow: hidden;
}

/* ── Canvas ──────────────────────────────────────── */
#nbe-canvas-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
    background: #e2e8f0;
    padding: 16px;
    position: relative;
}
#nbe-canvas-frame {
    flex: 1;
    width: 100%;
    max-width: 1280px;
    background: #fff;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 24px rgba(0,0,0,.15);
    transition: max-width .3s;
}
#nbe-canvas-frame.mobile { max-width: 390px; }

/* ── Right Panel ─────────────────────────────────── */
#nbe-panel {
    width: 300px;
    flex-shrink: 0;
    background: #fff;
    border-left: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.nbe-panel-section {
    display: flex;
    flex-direction: column;
}
.nbe-panel-section + .nbe-panel-section {
    border-top: 1px solid #f1f5f9;
}
.nbe-panel-head {
    padding: .7rem 1rem .5rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nbe-panel-body {
    overflow-y: auto;
    flex: 1;
}

/* ── Block items in panel ────────────────────────── */
.nbe-block-row {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .75rem;
    border-bottom: 1px solid #f8fafc;
    cursor: pointer;
    transition: background .1s;
}
.nbe-block-row:hover { background: #f8fafc; }
.nbe-block-row.is-sel { background: #eff6ff; }
.nbe-block-row .brow-icon {
    width: 28px; height: 28px;
    background: #f1f5f9;
    border-radius: 5px;
    display: flex; align-items: center; justify-content: center;
    color: #64748b; font-size: .8rem; flex-shrink: 0;
}
.nbe-block-row.is-sel .brow-icon { background: #dbeafe; color: #2563eb; }
.nbe-block-row .brow-name { flex: 1; font-size: .82rem; font-weight: 500; color: #374151; }
.nbe-block-row .brow-type { font-size: .65rem; color: #9ca3af; font-family: monospace; }
.nbe-block-row-actions { display: flex; gap: 2px; }
.nbe-block-row-actions button {
    border: none; background: none; cursor: pointer;
    color: #cbd5e1; padding: .2rem .28rem; border-radius: 4px; font-size: .75rem;
}
.nbe-block-row-actions button:hover { background: #f1f5f9; color: #374151; }
.nbe-block-row-actions .del:hover { color: #ef4444; background: #fef2f2; }

/* Block stack empty hint */
.nbe-blocks-empty {
    padding: 1.5rem 1rem;
    text-align: center;
    color: #94a3b8;
    font-size: .8rem;
    line-height: 1.6;
}

/* ── Inspector ───────────────────────────────────── */
#nbe-inspector {
    flex: 1;
    overflow-y: auto;
    padding: .75rem .85rem;
}
.nbe-insp-empty {
    display: flex; flex-direction: column; align-items: center;
    gap: .5rem; padding: 2rem 1rem; color: #9ca3af;
    font-size: .8rem; text-align: center;
}
.nbe-insp-title {
    font-size: .82rem; font-weight: 700; color: #374151;
    margin-bottom: .85rem; padding-bottom: .5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .4rem;
}
.nbe-field { margin-bottom: .85rem; }
.nbe-field:last-child { margin-bottom: 0; }
.nbe-field label {
    display: block; font-size: .74rem; font-weight: 500;
    color: #4b5563; margin-bottom: .28rem;
}
.nbe-field input,
.nbe-field textarea,
.nbe-field select {
    width: 100%; padding: .4rem .65rem;
    border: 1px solid #d1d5db; border-radius: 6px;
    font-size: .83rem; outline: none; box-sizing: border-box;
    transition: border-color .15s;
}
.nbe-field input:focus,
.nbe-field textarea:focus,
.nbe-field select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.1);
}
.nbe-field textarea { resize: vertical; min-height: 64px; }
.nbe-field input[type=checkbox] { width: auto; }
.nbe-color-row { display: flex; gap: .4rem; align-items: center; }
.nbe-color-swatch {
    width: 32px; height: 32px; border-radius: 5px;
    border: 1px solid #d1d5db; overflow: hidden;
    position: relative; flex-shrink: 0; cursor: pointer;
}
.nbe-color-swatch input[type=color] {
    position: absolute; inset: -4px;
    width: calc(100% + 8px); height: calc(100% + 8px);
    opacity: 0; cursor: pointer; border: none;
}
.nbe-insp-section {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: .85rem .9rem;
    margin-bottom: .9rem;
}
.nbe-insp-section:last-child { margin-bottom: 0; }
.nbe-insp-section__title {
    font-size: .8rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .2rem;
}
.nbe-insp-section__hint {
    font-size: .72rem;
    line-height: 1.45;
    color: #64748b;
    margin-bottom: .75rem;
}
.nbe-field-help {
    margin-top: .3rem;
    font-size: .7rem;
    line-height: 1.4;
    color: #64748b;
}

/* ════════════════════════════════════════════════════
   MODAL: Add Block
   ════════════════════════════════════════════════════ */
#nbe-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
#nbe-modal-overlay.open { display: flex; }
#nbe-modal {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 900px;
    max-height: calc(100vh - 3rem);
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    overflow: hidden;
}
#nbe-modal-header {
    padding: 1rem 1.25rem .75rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: .75rem;
}
#nbe-modal-header h2 { margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b; flex: 1; }
#nbe-modal-search {
    padding: .38rem .7rem;
    border: 1px solid #d1d5db; border-radius: 6px;
    font-size: .83rem; outline: none; width: 200px;
}
#nbe-modal-search:focus { border-color: #3b82f6; }
.nbe-modal-close {
    background: none; border: none; cursor: pointer;
    color: #9ca3af; font-size: 1.1rem; padding: .3rem;
    border-radius: 5px; line-height: 1;
}
.nbe-modal-close:hover { background: #f1f5f9; color: #374151; }
#nbe-modal-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}
#nbe-modal-cats {
    width: 180px;
    flex-shrink: 0;
    border-right: 1px solid #f1f5f9;
    overflow-y: auto;
    padding: .5rem 0;
}
.nbe-cat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .5rem .9rem;
    font-size: .82rem;
    color: #64748b;
    cursor: pointer;
    border-radius: 0;
    transition: background .1s;
}
.nbe-cat-item:hover { background: #f8fafc; }
.nbe-cat-item.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
.nbe-cat-item .count {
    font-size: .7rem; background: #f1f5f9; color: #9ca3af;
    border-radius: 99px; padding: .1rem .45rem;
}
.nbe-cat-item.active .count { background: #dbeafe; color: #2563eb; }
#nbe-modal-blocks {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .85rem;
    align-content: start;
}
.nbe-lib-card {
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, transform .1s;
    background: #fafbfc;
}
.nbe-lib-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    transform: translateY(-2px);
}
.nbe-lib-card__thumb {
    height: 90px;
    background: #f8fafc;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #f1f5f9;
    padding: 10px;
    position: relative;
}
/* Scaled wireframe inside card thumbnail */
.nbe-lib-card__thumb .nb-wire-scene {
    transform: scale(.65);
    transform-origin: top center;
    width: 100%;
    position: absolute;
    top: 6px;
    left: 0;
}
.nbe-lib-card__info {
    padding: .5rem .7rem;
    display: flex;
    flex-direction: column;
    gap: .1rem;
}
.nbe-lib-card__name { font-size: .82rem; font-weight: 600; color: #374151; }
.nbe-lib-card__desc { font-size: .72rem; color: #9ca3af; }
.nbe-lib-card:hover .nbe-lib-card__name { color: #1d4ed8; }

/* Wireframe reusable elements */
.nb-wf { display: flex; flex-direction: column; gap: 6px; padding: 0 8px; }
.nb-wf-h { height: 12px; border-radius: 3px; background: #9ca3af; }
.nb-wf-t { height: 7px; border-radius: 3px; background: #d1d5db; }
.nb-wf-acc { height: 4px; border-radius: 2px; background: var(--nb-color-accent, #b42318); width: 36%; }
.nb-wf-btn { height: 24px; border-radius: 5px; background: var(--nb-color-accent, #b42318); width: 80px; flex-shrink: 0; }
.nb-wf-btn-o { height: 24px; border-radius: 5px; border: 2px solid var(--nb-color-accent, #b42318); width: 80px; flex-shrink: 0; }
.nb-wf-img { height: 52px; border-radius: 5px; background: #e2e8f0; width: 120px; flex-shrink: 0; }
.nb-wf-row { display: flex; gap: 5px; align-items: center; }
.nb-wf-card { border: 1px solid #e5e7eb; border-radius: 5px; padding: 7px; background: #fff; flex: 1; display: flex; flex-direction: column; gap: 4px; }
.nb-wf-icon { width: 20px; height: 20px; border-radius: 50%; background: var(--nb-color-accent, #b42318); opacity: .3; }
.nb-wf-g3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 5px; }
.nb-wf-strip { height: 56px; border-radius: 6px; background: var(--nb-color-accent, #b42318); opacity: .15; display: flex; align-items: center; padding: 0 12px; gap: 10px; }
.nb-wf-strip-inner { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.nb-wf-strip-h { height: 10px; border-radius: 2px; background: rgba(0,0,0,.35); width: 60%; }
.nb-wf-strip-btn { height: 22px; border-radius: 4px; background: rgba(0,0,0,.3); width: 60px; flex-shrink: 0; }

/* Image field */
.nbe-field-image-wrap { display: flex; flex-direction: column; gap: .4rem; }
.nbe-field-image-preview {
    width: 100%; height: 80px; border-radius: 6px;
    border: 1px dashed #d1d5db; background: #f8fafc;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; position: relative;
}
.nbe-field-image-preview img { max-width:100%; max-height:100%; object-fit:cover; border-radius:4px; }
.nbe-img-btns { display: flex; gap: .4rem; }
.nbe-img-btns button {
    flex: 1; padding: .3rem .5rem; border: 1px solid #d1d5db; border-radius: 5px;
    background: #f8fafc; font-size: .75rem; cursor: pointer; color: #4b5563; transition: background .15s;
}
.nbe-img-btns button:hover { background: #f1f5f9; }
.nbe-block-name-wrap { display: flex; align-items: center; }
</style>

<!-- ═══════════════════════════════════════════
     EDITOR SHELL
     ═══════════════════════════════════════════ -->
<div id="nbe-shell">

    <!-- Top Bar -->
    <div id="nbe-topbar">
        <a href="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') ?>" class="nbe-back">
            <i class="fa fa-chevron-left"></i> Блоки
        </a>
        <div class="nbe-sep"></div>
        <div class="nbe-block-name-wrap">
            <input type="text" id="nbe-title-input" value="<?= $block_title_esc ?>" placeholder="Название блока" style="background:transparent;border:none;outline:none;color:#f1f5f9;font-size:.85rem;font-weight:600;width:220px;border-bottom:1px solid transparent;padding:.1rem .2rem;transition:border-color .15s">
            <span style="font-size:.68rem;background:#0f172a;color:#64748b;padding:.15em .5em;border-radius:4px;font-family:monospace;margin-left:.4rem"><?= $block_type ?></span>
        </div>
        <div class="nbe-spacer"></div>
        <div class="nbe-vp-btns">
            <button class="nbe-vp-btn active" id="nbeVpDesktop" onclick="setViewport('desktop')">
                <i class="fa fa-desktop"></i> Desktop
            </button>
            <button class="nbe-vp-btn" id="nbeVpMobile" onclick="setViewport('mobile')">
                <i class="fa fa-mobile-alt"></i> Mobile
            </button>
        </div>
        <div class="nbe-sep"></div>
        <a class="nbe-tb-btn nbe-btn-view" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>">
            <i class="fa fa-thumb-tack"></i> Разместить
        </a>
        <button class="nbe-tb-btn nbe-btn-save" id="nbeSaveBtn" onclick="saveBlock(false)">
            <i class="fa fa-save"></i> Сохранить
        </button>
    </div>

    <!-- Body: canvas + inspector -->
    <div id="nbe-body">

        <!-- Canvas iframe -->
        <div id="nbe-canvas-wrap">
            <iframe
                id="nbe-canvas-frame"
                src="<?= htmlspecialchars($canvas_url, ENT_QUOTES, 'UTF-8') ?>"
                title="Block preview"
                sandbox="allow-same-origin allow-scripts"
            ></iframe>
        </div>

        <!-- Inspector Panel -->
        <div id="nbe-panel">
            <div class="nbe-panel-section" style="flex:1;overflow:hidden;display:flex;flex-direction:column">
                <div class="nbe-panel-head"><i class="fa fa-sliders-h"></i> Инспектор</div>
                <div id="nbe-inspector" class="nbe-panel-body">
                    <?php if (empty($fields)): ?>
                    <div class="nbe-insp-empty">
                        <i class="fa fa-cube" style="font-size:1.4rem;opacity:.4"></i>
                        <div>Для этого типа блока<br>нет настроек</div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($field_groups as $group): ?>
                    <div class="nbe-insp-section">
                        <div class="nbe-insp-section__title"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if (!empty($group['hint'])): ?>
                        <div class="nbe-insp-section__hint"><?= htmlspecialchars($group['hint'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <?php foreach ($group['fields'] as $f):
                        $fkey   = htmlspecialchars($f['key']   ?? '', ENT_QUOTES, 'UTF-8');
                        $flabel = htmlspecialchars($f['label'] ?? $f['key'] ?? '', ENT_QUOTES, 'UTF-8');
                        $ftype  = $f['type'] ?? 'text';
                        $fval   = $props[$f['key'] ?? ''] ?? ($f['default'] ?? '');
                        $fval_e = htmlspecialchars((string)$fval, ENT_QUOTES, 'UTF-8');
                        $fmin   = htmlspecialchars((string) ($f['min'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $fmax   = htmlspecialchars((string) ($f['max'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $fstep  = htmlspecialchars((string) ($f['step'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $fph    = htmlspecialchars((string) ($f['placeholder'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $frows  = (int) ($f['rows'] ?? 3);
                        $fhelp  = htmlspecialchars((string) ($f['help'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $fbool  = in_array(strtolower((string) $fval), ['1', 'true', 'yes', 'on'], true);
                    ?>
                    <div class="nbe-field">
                        <label for="nbf-<?= $fkey ?>"><?= $flabel ?></label>
                        <?php if ($ftype === 'textarea'): ?>
                            <textarea id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" rows="<?= $frows ?>"
                                placeholder="<?= $fph ?>"
                                oninput="markDirty();scheduleReload()"><?= $fval_e ?></textarea>
                        <?php elseif ($ftype === 'select'): ?>
                            <select id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" onchange="markDirty();scheduleReload()">
                                <?php foreach (($f['options'] ?? []) as $opt): ?>
                                <option value="<?= htmlspecialchars($opt['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    <?= ($fval == ($opt['value'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opt['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($ftype === 'color'): ?>
                            <div class="nbe-color-row">
                                <div class="nbe-color-swatch">
                                    <input type="color" id="nbf-<?= $fkey ?>-picker" value="<?= $fval_e ?>"
                                        oninput="document.getElementById('nbf-<?= $fkey ?>').value=this.value;markDirty();scheduleReload()">
                                </div>
                                <input type="text" id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" value="<?= $fval_e ?>"
                                    oninput="if(this.value.match(/^#[0-9a-fA-F]{3,6}$/)){document.getElementById('nbf-<?= $fkey ?>-picker').value=this.value;}markDirty();scheduleReload()">
                            </div>
                        <?php elseif ($ftype === 'number'): ?>
                            <input type="number" id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" value="<?= $fval_e ?>"
                                <?= $fmin !== '' ? 'min="' . $fmin . '"' : '' ?>
                                <?= $fmax !== '' ? 'max="' . $fmax . '"' : '' ?>
                                <?= $fstep !== '' ? 'step="' . $fstep . '"' : '' ?>
                                placeholder="<?= $fph ?>"
                                oninput="markDirty();scheduleReload()">
                        <?php elseif ($ftype === 'boolean'): ?>
                            <label style="display:flex;align-items:center;gap:.55rem;font-size:.83rem;color:#374151;margin:0">
                                <input type="checkbox" id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" value="1"
                                    <?= $fbool ? 'checked' : '' ?>
                                    onchange="this.nextElementSibling.textContent=this.checked?'Включено':'Выключено';markDirty();scheduleReload()">
                                <span><?= $fbool ? 'Включено' : 'Выключено' ?></span>
                            </label>
                        <?php elseif ($ftype === 'image'): ?>
                            <div class="nbe-field-image-wrap">
                                <div class="nbe-field-image-preview" id="nbf-<?= $fkey ?>-preview">
                                    <?php if ($fval): ?>
                                        <img src="<?= $fval_e ?>" alt="">
                                    <?php else: ?>
                                        <span style="color:#94a3b8;font-size:.75rem">Не выбрано</span>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" value="<?= $fval_e ?>">
                                <div class="nbe-img-btns">
                                    <button type="button" onclick="nbeOpenFilePicker('nbf-<?= $fkey ?>')">
                                        <i class="fa fa-folder-open"></i> Выбрать
                                    </button>
                                    <button type="button" onclick="nbeClearImage('nbf-<?= $fkey ?>')">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php elseif ($ftype === 'url'): ?>
                            <input type="text" id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" value="<?= $fval_e ?>"
                                placeholder="<?= $fph ?: 'https:// или /path/' ?>"
                                oninput="markDirty();scheduleReload()">
                        <?php else: /* text */ ?>
                            <input type="text" id="nbf-<?= $fkey ?>" data-key="<?= $fkey ?>" value="<?= $fval_e ?>"
                                placeholder="<?= $fph ?>"
                                oninput="markDirty();scheduleReload()">
                        <?php endif; ?>
                        <?php if ($fhelp): ?>
                        <div class="nbe-field-help"><?= $fhelp ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /#nbe-body -->

</div><!-- /#nbe-shell -->

<!-- File Picker Modal -->
<div id="nb-filepicker-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;width:min(640px,94vw);max-height:72vh;display:flex;flex-direction:column;box-shadow:0 16px 48px rgba(0,0,0,.25);overflow:hidden">
        <div style="padding:.9rem 1.1rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.75rem">
            <h3 style="margin:0;font-size:.95rem;flex:1;color:#1e293b"><i class="fa fa-images"></i> Медиабиблиотека</h3>
            <button onclick="nbeCloseFilePicker()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.15rem;padding:.25rem;border-radius:4px"><i class="fa fa-times"></i></button>
        </div>
        <div id="nb-filepicker-grid" style="flex:1;overflow-y:auto;padding:.75rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem;align-content:start">
            <div style="grid-column:1/-1;min-height:120px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:.8rem">
                <i class="fa fa-spinner fa-spin" style="font-size:1.5rem;margin-right:.4rem"></i> Загрузка...
            </div>
        </div>
        <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;display:flex;align-items:center;gap:.5rem">
            <input type="file" id="nb-fp-file-input" accept="image/*" style="display:none">
            <label for="nb-fp-file-input" style="padding:.4rem .85rem;border:1px solid #3b82f6;border-radius:6px;background:#eff6ff;color:#1d4ed8;font-size:.8rem;cursor:pointer">
                <i class="fa fa-upload"></i> Загрузить
            </label>
            <span id="nb-fp-upload-status" style="font-size:.78rem;color:#64748b"></span>
        </div>
    </div>
</div>

<script>
var nbeSaveUrl   = <?= json_encode($save_url,   JSON_UNESCAPED_UNICODE) ?>;
var nbeCanvasUrl = <?= json_encode($canvas_url, JSON_UNESCAPED_UNICODE) ?>;
var nbeDirty     = false;
var nbeDebTimer  = null;

function nbeGetProps() {
    var props = {};
    document.querySelectorAll('#nbe-inspector [data-key]').forEach(function(el) {
        props[el.dataset.key] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
    });
    return props;
}

function markDirty() {
    nbeDirty = true;
    var btn = document.getElementById('nbeSaveBtn');
    if (btn) { btn.classList.add('dirty'); btn.classList.remove('saved'); }
}

function scheduleReload() {
    clearTimeout(nbeDebTimer);
    nbeDebTimer = setTimeout(function() { saveBlock(true); }, 700);
}

function reloadCanvas() {
    var f = document.getElementById('nbe-canvas-frame');
    if (f) f.src = nbeCanvasUrl + '?t=' + Date.now();
}

function setViewport(type) {
    var f   = document.getElementById('nbe-canvas-frame');
    var btnD = document.getElementById('nbeVpDesktop');
    var btnM = document.getElementById('nbeVpMobile');
    if (type === 'mobile') {
        f.classList.add('mobile'); btnM.classList.add('active'); btnD.classList.remove('active');
    } else {
        f.classList.remove('mobile'); btnD.classList.add('active'); btnM.classList.remove('active');
    }
}

var nbeSaving = false;
function saveBlock(silent) {
    if (nbeSaving) return;
    if (silent && !nbeDirty) return;

    nbeSaving = true;
    var btn   = document.getElementById('nbeSaveBtn');
    var title = document.getElementById('nbe-title-input').value.trim();
    var props = nbeGetProps();

    if (!silent && btn) {
        btn.classList.add('saving');
        btn.classList.remove('dirty', 'saved');
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Сохранение...';
    }

    fetch(nbeSaveUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: title, props: props })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        nbeSaving = false;
        if (!silent && btn) btn.classList.remove('saving');

        if (d.ok) {
            nbeDirty = false;
            reloadCanvas();

            if (!silent && btn) {
                btn.classList.add('saved');
                btn.innerHTML = '<i class="fa fa-check"></i> Сохранено';
                setTimeout(function() {
                    if (btn) {
                        btn.classList.remove('saved');
                        btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
                    }
                }, 2000);
            } else if (btn) {
                btn.classList.remove('dirty');
            }
        } else {
            if (btn) { btn.classList.add('dirty'); btn.innerHTML = '<i class="fa fa-save"></i> Сохранить'; }
            if (!silent) {
                alert('Ошибка: ' + (d.error || '?'));
            }
        }
    })
    .catch(function() {
        nbeSaving = false;
        if (btn) {
            btn.classList.remove('saving');
            btn.classList.add('dirty');
            btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
        }
    });
}

/* ── File Picker ── */
var nbeFpTarget = null;

function nbeOpenFilePicker(fieldId) {
    nbeFpTarget = fieldId;
    var overlay = document.getElementById('nb-filepicker-overlay');
    overlay.style.display = 'flex';
    nbeFpLoad();
}

function nbeCloseFilePicker() {
    document.getElementById('nb-filepicker-overlay').style.display = 'none';
    nbeFpTarget = null;
}

function nbeFpLoad() {
    var grid = document.getElementById('nb-filepicker-grid');
    grid.innerHTML = '<div style="grid-column:1/-1;min-height:120px;display:flex;align-items:center;justify-content:center;color:#94a3b8"><i class="fa fa-spinner fa-spin" style="font-size:1.5rem"></i></div>';
    fetch('/nordicblocks/media_list', { headers: {'X-Requested-With': 'XMLHttpRequest'} })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        grid.innerHTML = '';
        var files = d.files || [];
        if (!files.length) {
            grid.innerHTML = '<div style="grid-column:1/-1;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#94a3b8;gap:.4rem"><i class="fa fa-images" style="font-size:1.8rem"></i><span style="font-size:.78rem">Нет изображений</span></div>';
            return;
        }
        files.forEach(function(f) {
            var el = document.createElement('div');
            el.style.cssText = 'border:2px solid #e5e7eb;border-radius:7px;overflow:hidden;cursor:pointer;aspect-ratio:1;background:#f8fafc;transition:border-color .15s';
            el.innerHTML = '<img src="' + f.url + '" style="width:100%;height:100%;object-fit:cover">';
            el.addEventListener('mouseenter', function() { el.style.borderColor = '#3b82f6'; });
            el.addEventListener('mouseleave', function() { el.style.borderColor = '#e5e7eb'; });
            el.addEventListener('click', function() { nbeFpSelect(f.url); });
            grid.appendChild(el);
        });
    })
    .catch(function() {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#ef4444;padding:2rem;font-size:.8rem">Ошибка загрузки</div>';
    });
}

function nbeFpSelect(url) {
    if (!nbeFpTarget) return;
    var input   = document.getElementById(nbeFpTarget);
    var preview = document.getElementById(nbeFpTarget + '-preview');
    if (input)   { input.value = url; markDirty(); scheduleReload(); }
    if (preview) { preview.innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:100%;object-fit:cover;border-radius:4px">'; }
    nbeCloseFilePicker();
}

function nbeClearImage(fieldId) {
    var input   = document.getElementById(fieldId);
    var preview = document.getElementById(fieldId + '-preview');
    if (input)   { input.value = ''; markDirty(); scheduleReload(); }
    if (preview) { preview.innerHTML = '<span style="color:#94a3b8;font-size:.75rem">Не выбрано</span>'; }
}

document.getElementById('nb-fp-file-input').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var status = document.getElementById('nb-fp-upload-status');
    status.textContent = 'Загрузка...';
    var fd = new FormData();
    fd.append('file', file);
    fetch('/nordicblocks/media_upload', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.url) { status.textContent = 'Готово!'; nbeFpSelect(d.url); setTimeout(function(){ status.textContent=''; }, 2000); }
        else { status.textContent = 'Ошибка: ' + (d.error || '?'); }
    })
    .catch(function() { status.textContent = 'Ошибка сети'; });
    this.value = '';
});

document.getElementById('nb-filepicker-overlay').addEventListener('click', function(e) {
    if (e.target === this) nbeCloseFilePicker();
});

/* ── Keyboard shortcuts ── */
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveBlock(false); }
});

window.addEventListener('beforeunload', function(e) {
    if (nbeDirty) { e.preventDefault(); e.returnValue = ''; }
});

document.getElementById('nbe-title-input').addEventListener('input', function() {
    markDirty();
    scheduleReload();
});
</script>