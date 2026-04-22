<?php
$block_title_esc = htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8');
$block_type      = htmlspecialchars($block['type'], ENT_QUOTES, 'UTF-8');
?>
<style>
#nbh-shell {
    position: fixed;
    inset: 55px 0 0 0;
    height: calc(100vh - 55px);
    max-height: calc(100vh - 55px);
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
    background: #edf2f7;
    z-index: 100;
}
#nbh-topbar {
    height: 52px;
    background: #172033;
    border-bottom: 1px solid #0f172a;
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: 0 .8rem;
}
.nbh-back {
    color: #a5b4c7;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .78rem;
    padding: .3rem .5rem;
    border-radius: 6px;
}
.nbh-back:hover { background: rgba(255,255,255,.08); color: #e5edf7; }
.nbh-sep { width: 1px; height: 24px; background: #334155; }
.nbh-title-wrap { display: flex; align-items: center; gap: .45rem; }
#nbh-title-input {
    width: 240px;
    background: transparent;
    color: #f8fafc;
    border: none;
    border-bottom: 1px solid transparent;
    outline: none;
    font-size: .86rem;
    font-weight: 700;
    padding: .15rem .2rem;
}
#nbh-title-input:focus { border-bottom-color: #60a5fa; }
.nbh-type {
    font-size: .68rem;
    color: #94a3b8;
    background: #0f172a;
    border-radius: 999px;
    padding: .18rem .55rem;
    font-family: monospace;
}
.nbh-runtime-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .68rem;
    color: #93c5fd;
    background: rgba(29, 78, 216, .16);
    border: 1px solid rgba(96, 165, 250, .32);
    border-radius: 999px;
    padding: .18rem .55rem;
    font-family: monospace;
}
.nbh-spacer { flex: 1; }
.nbh-vp {
    display: inline-flex;
    gap: 2px;
    padding: 2px;
    background: #0f172a;
    border-radius: 8px;
}
.nbh-vp button,
.nbh-btn {
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: .77rem;
    font-weight: 600;
}
.nbh-vp button {
    padding: .34rem .72rem;
    color: #94a3b8;
    background: transparent;
}
.nbh-vp button.is-active { background: #1e293b; color: #f8fafc; }
.nbh-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .42rem .9rem;
}
.nbh-btn--ghost { background: rgba(255,255,255,.08); color: #d7e0eb; }
.nbh-btn--ghost:hover { background: rgba(255,255,255,.14); }
.nbh-btn--catalog { background: rgba(255,255,255,.08); color: #d7e0eb; }
.nbh-btn--catalog:hover { background: rgba(255,255,255,.14); }
.nbh-btn--save { background: #16a34a; color: #fff; }
.nbh-btn--save:hover { background: #15803d; }
.nbh-btn--save.is-dirty { box-shadow: 0 0 0 2px rgba(251,191,36,.55); }
.nbh-btn--save.is-saving { background: #475569; pointer-events: none; }

#nbh-body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 0;
    height: 100%;
    min-height: 0;
    flex: 1;
    overflow: hidden;
}
#nbh-canvas-wrap {
    padding: 16px;
    overflow: auto;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    min-height: 0;
    background: radial-gradient(circle at top left, rgba(96,165,250,.10), transparent 30%), #e2e8f0;
}
#nbh-canvas-frame {
    flex: 0 0 auto;
    width: 100%;
    max-width: 1280px;
    min-height: calc(100vh - 170px);
    border: none;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 18px 40px rgba(15,23,42,.16);
    transition: max-width .2s ease, height .18s ease;
}
#nbh-canvas-frame.is-mobile { max-width: 390px; }

#nbh-panel {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #ffffff;
    border-left: 1px solid #dbe4ef;
}
.nbh-panel-head {
    padding: .85rem 1rem .7rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    flex-direction: column;
    gap: .7rem;
}
.nbh-panel-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.nbh-panel-heading {
    display: flex;
    flex-direction: column;
    gap: .18rem;
}
.nbh-panel-title strong {
    font-size: .9rem;
    color: #0f172a;
}
.nbh-panel-subtitle {
    font-size: .72rem;
    color: #64748b;
    line-height: 1.45;
}
.nbh-selection {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .58rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: .7rem;
    font-weight: 700;
}
.nbh-entity-list {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}
.nbh-entity-chip {
    border: 1px solid #dbe4ef;
    background: #fff;
    color: #475569;
    border-radius: 999px;
    padding: .32rem .58rem;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
}
.nbh-entity-chip:hover { border-color: #93c5fd; background: #f8fbff; color: #1d4ed8; }
.nbh-entity-chip.is-active { background: #dbeafe; border-color: #60a5fa; color: #1d4ed8; }

.nbh-tabs {
    display: flex;
    gap: .35rem;
    border-bottom: 1px solid #edf2f7;
    padding: .75rem .85rem .65rem;
}
.nbh-tab {
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    font-size: .76rem;
    font-weight: 700;
    padding: .45rem .7rem;
    border-radius: 8px;
}
.nbh-tab.is-active { background: #eff6ff; color: #1d4ed8; }

#nbh-panel-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: .85rem;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
    display: flex;
    flex-direction: column;
    gap: .85rem;
}
.nbh-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    padding: 1rem;
    color: #64748b;
    font-size: .8rem;
    line-height: 1.5;
}
.nbh-status-block {
    border: 1px solid #fdba74;
    border-radius: 14px;
    background: linear-gradient(180deg, #fff7ed 0%, #ffedd5 100%);
    padding: .95rem 1rem;
    color: #7c2d12;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
}
.nbh-status-block__head {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
    margin-bottom: .45rem;
}
.nbh-status-block__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 .55rem;
    border-radius: 999px;
    background: #ea580c;
    color: #fff;
    font-size: .66rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.nbh-status-block__title {
    font-size: .8rem;
    font-weight: 800;
    color: #7c2d12;
}
.nbh-status-block p {
    margin: 0;
    font-size: .78rem;
    line-height: 1.55;
}
.nbh-status-block__hint {
    margin-top: .55rem;
    padding: .55rem .7rem;
    border-radius: 10px;
    background: rgba(255,255,255,.55);
    color: #9a3412;
    font-size: .74rem;
    font-weight: 700;
}
.nbh-section {
    border: 1px solid #e5edf5;
    border-radius: 14px;
    overflow: hidden;
    flex: 0 0 auto;
    background: #fff;
}
.nbh-section-head {
    padding: .75rem .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    border-bottom: 1px solid #edf2f7;
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
}
.nbh-section-head strong {
    font-size: .81rem;
    color: #0f172a;
}
.nbh-section-head span {
    font-size: .68rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.nbh-section-body {
    padding: .85rem .9rem;
    display: flex;
    flex-direction: column;
    gap: .8rem;
}
.nbh-accordion {
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.nbh-accordion-group {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    overflow: hidden;
}
.nbh-accordion-group.is-open {
    border-color: #93c5fd;
    box-shadow: 0 0 0 1px rgba(147,197,253,.18);
}
.nbh-accordion-toggle {
    width: 100%;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem .95rem;
    cursor: pointer;
    text-align: left;
}
.nbh-accordion-toggle strong {
    display: block;
    font-size: .8rem;
    color: #0f172a;
}
.nbh-accordion-toggle span {
    display: block;
    margin-top: .12rem;
    font-size: .68rem;
    font-weight: 700;
    color: #64748b;
}
.nbh-accordion-icon {
    color: #64748b;
    transition: transform .16s ease, color .16s ease;
}
.nbh-accordion-group.is-open .nbh-accordion-icon {
    transform: rotate(180deg);
    color: #1d4ed8;
}
.nbh-accordion-body {
    display: none;
    padding: 0 .75rem .75rem;
    border-top: 1px solid #edf2f7;
    background: #f8fafc;
}
.nbh-accordion-group.is-open .nbh-accordion-body {
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.nbh-field {
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.nbh-field label {
    font-size: .73rem;
    font-weight: 700;
    color: #475569;
}
.nbh-field input,
.nbh-field textarea,
.nbh-field select {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d1d9e6;
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    padding: .5rem .65rem;
    font-size: .82rem;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.nbh-field textarea { min-height: 78px; resize: vertical; }
.nbh-field input:focus,
.nbh-field textarea:focus,
.nbh-field select:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(59,130,246,.14);
}
.nbh-input-row {
    display: flex;
    gap: .45rem;
    align-items: stretch;
}
.nbh-input-row input {
    flex: 1 1 auto;
    min-width: 0;
}
.nbh-color-control {
    display: flex;
    align-items: stretch;
    gap: .55rem;
}
.nbh-color-swatch {
    position: relative;
    flex: 0 0 52px;
    width: 52px;
    min-width: 52px;
    height: 52px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    background: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
}
.nbh-color-swatch input[type="color"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    padding: 0;
    border: none;
    cursor: pointer;
}
.nbh-color-swatch__face {
    display: block;
    width: 100%;
    height: 100%;
    background: #0f172a;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
}
.nbh-color-code {
    flex: 1 1 auto;
    min-width: 0;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.nbh-picker-btn {
    flex: 0 0 auto;
    border: 1px solid #d1d9e6;
    border-radius: 8px;
    background: #f8fafc;
    color: #334155;
    padding: 0 .75rem;
    font-size: .74rem;
    font-weight: 700;
    cursor: pointer;
    transition: border-color .15s, background .15s, color .15s;
}
.nbh-picker-btn:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #1d4ed8;
}
.nbh-picker-btn--clear:hover {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #b91c1c;
}
.nbh-media-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .52);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    z-index: 3000;
}
.nbh-media-modal.is-open {
    display: flex;
}
.nbh-media-modal__dialog {
    width: min(920px, 100%);
    max-height: min(82vh, 760px);
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 30px 70px rgba(15, 23, 42, .28);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.nbh-media-modal__head,
.nbh-media-modal__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .9rem 1rem;
}
.nbh-media-modal__head {
    border-bottom: 1px solid #e5edf5;
}
.nbh-media-modal__head strong {
    font-size: .95rem;
    color: #0f172a;
}
.nbh-media-modal__toolbar {
    border-bottom: 1px solid #eef2f7;
    flex-wrap: wrap;
}
.nbh-media-modal__pager,
.nbh-media-modal__actions {
    display: flex;
    align-items: center;
    gap: .55rem;
}
.nbh-media-modal__status {
    font-size: .76rem;
    font-weight: 700;
    color: #475569;
    min-width: 110px;
    text-align: center;
}
.nbh-media-modal__body {
    position: relative;
    min-height: 320px;
    padding: 1rem;
    overflow: auto;
    background: #f8fafc;
}
.nbh-media-modal__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: .85rem;
}
.nbh-media-card {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background: #fff;
    overflow: hidden;
    cursor: pointer;
    text-align: left;
    padding: 0;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.nbh-media-card:hover {
    transform: translateY(-1px);
    border-color: #93c5fd;
    box-shadow: 0 10px 24px rgba(59, 130, 246, .12);
}
.nbh-media-card__thumb {
    display: block;
    aspect-ratio: 1 / 1;
    background: linear-gradient(135deg, #e2e8f0, #f8fafc);
    overflow: hidden;
}
.nbh-media-card__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.nbh-media-card__name {
    display: block;
    padding: .7rem .75rem .8rem;
    font-size: .74rem;
    line-height: 1.4;
    color: #334155;
    word-break: break-word;
}
.nbh-media-modal__loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(248, 250, 252, .82);
    color: #334155;
    font-size: .86rem;
    font-weight: 700;
}
.nbh-media-modal__loading.is-hidden,
.nbh-media-modal__empty.is-hidden {
    display: none;
}
.nbh-media-modal__empty {
    padding: 2rem 1rem;
    text-align: center;
    color: #64748b;
    font-size: .82rem;
}
.nbh-catalog-table-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .52);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    z-index: 3200;
}
.nbh-catalog-table-modal.is-open {
    display: flex;
}
.nbh-catalog-table-modal__dialog {
    width: min(1180px, 100%);
    max-height: min(88vh, 900px);
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 30px 70px rgba(15, 23, 42, .28);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.nbh-catalog-table-modal__head,
.nbh-catalog-table-modal__toolbar,
.nbh-catalog-table-modal__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .95rem 1rem;
}
.nbh-catalog-table-modal__head {
    border-bottom: 1px solid #e5edf5;
}
.nbh-catalog-table-modal__head strong {
    font-size: .95rem;
    color: #0f172a;
}
.nbh-catalog-table-modal__head span {
    display: block;
    margin-top: .18rem;
    font-size: .76rem;
    color: #64748b;
}
.nbh-catalog-table-modal__help {
    padding: .9rem 1rem;
    border-bottom: 1px solid #e5edf5;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    color: #334155;
    font-size: .78rem;
    line-height: 1.55;
}
.nbh-catalog-table-modal__toolbar {
    border-bottom: 1px solid #eef2f7;
    flex-wrap: wrap;
}
.nbh-catalog-table-modal__actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .55rem;
}
.nbh-catalog-table-modal__body {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr);
    min-height: 0;
    flex: 1 1 auto;
}
.nbh-catalog-table-modal__editor,
.nbh-catalog-table-modal__preview {
    min-height: 0;
    display: flex;
    flex-direction: column;
}
.nbh-catalog-table-modal__editor {
    border-right: 1px solid #edf2f7;
}
.nbh-catalog-table-modal__editor-help {
    padding: 0 1rem .7rem;
    color: #64748b;
    font-size: .76rem;
    line-height: 1.45;
}
.nbh-catalog-table-modal__editor-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .55rem;
    padding: 0 1rem .8rem;
}
.nbh-catalog-table-modal__section-title {
    padding: .8rem 1rem .55rem;
    font-size: .73rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.nbh-catalog-table-grid-wrap {
    flex: 1 1 auto;
    margin: 0 1rem 1rem;
    min-height: 280px;
    overflow: auto;
    border: 1px solid #d1d9e6;
    border-radius: 14px;
    background: #fff;
    box-sizing: border-box;
}
.nbh-catalog-table-grid {
    min-width: max-content;
}
.nbh-catalog-table-grid table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
    font-size: .75rem;
}
.nbh-catalog-table-grid th,
.nbh-catalog-table-grid td {
    border-right: 1px solid #e5edf5;
    border-bottom: 1px solid #e5edf5;
    padding: 0;
    vertical-align: top;
    background: #fff;
}
.nbh-catalog-table-grid th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8fafc;
    padding: .55rem .6rem;
    text-align: left;
    font-size: .68rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.nbh-catalog-table-grid__rownum,
.nbh-catalog-table-grid__remove {
    background: #f8fafc;
    text-align: center;
}
.nbh-catalog-table-grid__rownum {
    min-width: 44px;
    padding: .55rem .35rem;
    font-size: .7rem;
    font-weight: 800;
    color: #94a3b8;
}
.nbh-catalog-table-grid__remove {
    min-width: 52px;
}
.nbh-catalog-table-grid__input {
    display: block;
    width: 100%;
    min-width: 120px;
    border: none;
    padding: .62rem .68rem;
    font-size: .76rem;
    line-height: 1.4;
    color: #0f172a;
    background: transparent;
    box-sizing: border-box;
}
.nbh-catalog-table-grid__input:focus {
    outline: none;
    background: #eff6ff;
    box-shadow: inset 0 0 0 2px rgba(59,130,246,.25);
}
.nbh-catalog-table-grid__remove-btn {
    width: 100%;
    min-height: 40px;
    border: none;
    background: transparent;
    color: #94a3b8;
    font-size: .92rem;
    cursor: pointer;
}
.nbh-catalog-table-grid__remove-btn:hover {
    color: #dc2626;
    background: #fef2f2;
}
.nbh-catalog-table-modal__textarea-shadow {
    position: absolute;
    left: -9999px;
    top: auto;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}
.nbh-catalog-table-preview {
    flex: 1 1 auto;
    min-height: 0;
    padding: 0 1rem 1rem;
    overflow: auto;
}
.nbh-catalog-table-preview__metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: .55rem;
    margin-bottom: .85rem;
}
.nbh-catalog-table-preview__metric {
    border: 1px solid #dbe4ef;
    border-radius: 12px;
    background: #f8fafc;
    padding: .7rem .8rem;
}
.nbh-catalog-table-preview__metric strong {
    display: block;
    font-size: 1rem;
    color: #0f172a;
}
.nbh-catalog-table-preview__metric span {
    display: block;
    margin-top: .18rem;
    font-size: .7rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.nbh-catalog-table-preview__metric.is-good {
    border-color: #86efac;
    background: #f0fdf4;
}
.nbh-catalog-table-preview__metric.is-warn {
    border-color: #fdba74;
    background: #fff7ed;
}
.nbh-catalog-table-preview__metric.is-bad {
    border-color: #fca5a5;
    background: #fef2f2;
}
.nbh-catalog-table-preview__table {
    width: 100%;
    border-collapse: collapse;
    font-size: .75rem;
}
.nbh-catalog-table-preview__table th,
.nbh-catalog-table-preview__table td {
    border: 1px solid #e5edf5;
    padding: .5rem .55rem;
    text-align: left;
    vertical-align: top;
}
.nbh-catalog-table-preview__table th {
    position: sticky;
    top: 0;
    background: #f8fafc;
    z-index: 1;
    font-size: .68rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.nbh-catalog-table-preview__status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 .55rem;
    border-radius: 999px;
    font-size: .66rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.nbh-catalog-table-preview__status.is-import {
    background: #dcfce7;
    color: #166534;
}
.nbh-catalog-table-preview__status.is-skip {
    background: #ffedd5;
    color: #9a3412;
}
.nbh-catalog-table-preview__status.is-invalid {
    background: #fee2e2;
    color: #b91c1c;
}
.nbh-catalog-table-modal__status {
    flex: 1 1 auto;
    font-size: .77rem;
    color: #64748b;
}
.nbh-catalog-table-modal__status.is-error {
    color: #b91c1c;
}
.nbh-catalog-table-modal__status.is-success {
    color: #166534;
}
@media (max-width: 1100px) {
    .nbh-catalog-table-modal__body {
        grid-template-columns: 1fr;
    }
    .nbh-catalog-table-modal__editor {
        border-right: none;
        border-bottom: 1px solid #edf2f7;
    }
}
.nbh-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
}
.nbh-note {
    padding: .8rem .9rem;
    border-radius: 10px;
    background: #f8fafc;
    color: #64748b;
    font-size: .76rem;
    line-height: 1.5;
}
.nbh-breakpoints {
    display: inline-flex;
    gap: 2px;
    padding: 2px;
    border-radius: 8px;
    background: #eef2f7;
}
.nbh-breakpoints button {
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    font-size: .7rem;
    font-weight: 700;
    padding: .32rem .52rem;
    border-radius: 6px;
}
.nbh-breakpoints button.is-active { background: #fff; color: #1d4ed8; box-shadow: 0 1px 2px rgba(15,23,42,.06); }
@media (max-width: 1180px) {
    #nbh-body { grid-template-columns: minmax(0, 1fr) 330px; }
}
</style>

<div id="nbh-shell">
    <div id="nbh-topbar">
        <a href="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') ?>" class="nbh-back"><i class="fa fa-chevron-left"></i> Блоки</a>
        <div class="nbh-sep"></div>
        <div class="nbh-title-wrap">
            <input type="text" id="nbh-title-input" value="<?= $block_title_esc ?>" placeholder="Название блока">
            <span class="nbh-type"><?= $block_type ?></span>
            <?php if (!empty($render_version ?? '')): ?>
            <span class="nbh-runtime-badge">Рендер v<?= htmlspecialchars((string) $render_version, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="nbh-spacer"></div>
        <div class="nbh-vp">
            <button type="button" class="is-active" id="nbhVpDesktop">Компьютер</button>
            <button type="button" id="nbhVpMobile">Мобильный</button>
        </div>
        <div class="nbh-sep"></div>
        <?php if (($block['type'] ?? '') === 'catalog_browser'): ?>
        <button type="button" class="nbh-btn nbh-btn--catalog" id="nbhCatalogTableBtn"><i class="fa fa-table"></i> Таблица</button>
        <button type="button" class="nbh-btn nbh-btn--catalog" id="nbhCatalogXlsxImportBtn"><i class="fa fa-file-excel-o"></i> Импорт XLSX</button>
        <button type="button" class="nbh-btn nbh-btn--catalog" id="nbhCatalogDemoBtn"><i class="fa fa-download"></i> Демо JSON</button>
        <button type="button" class="nbh-btn nbh-btn--catalog" id="nbhCatalogExportBtn"><i class="fa fa-file-code-o"></i> Экспорт JSON</button>
        <button type="button" class="nbh-btn nbh-btn--catalog" id="nbhCatalogImportBtn"><i class="fa fa-upload"></i> Импорт JSON</button>
        <input type="file" id="nbhCatalogImportInput" accept=".json,application/json" style="display:none;">
        <input type="file" id="nbhCatalogXlsxImportInput" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" style="display:none;">
        <?php endif; ?>
        <a class="nbh-btn nbh-btn--ghost" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-thumb-tack"></i> Разместить</a>
        <button class="nbh-btn nbh-btn--save" id="nbhSaveBtn"><i class="fa fa-save"></i> Сохранить</button>
    </div>

    <div id="nbh-body">
        <div id="nbh-canvas-wrap">
            <iframe id="nbh-canvas-frame" src="<?= htmlspecialchars($canvas_url, ENT_QUOTES, 'UTF-8') ?>" title="Предпросмотр блока" sandbox="allow-same-origin allow-scripts"></iframe>
        </div>

        <div id="nbh-panel">
            <div class="nbh-panel-head">
                <div class="nbh-panel-title">
                    <div class="nbh-panel-heading">
                        <strong>Универсальный инспектор</strong>
                        <span class="nbh-panel-subtitle" id="nbhPanelSubtitle">Выберите сущность на превью или в списке справа, затем настройте контент, дизайн, макет или данные.</span>
                    </div>
                    <span class="nbh-selection" id="nbhSelectionLabel">Редактируется: заголовок</span>
                </div>
                <div class="nbh-entity-list" id="nbhEntityList"></div>
            </div>

            <div class="nbh-tabs" id="nbhTabs"></div>
            <div id="nbh-panel-body"></div>
        </div>
    </div>
</div>

<script>
var nbhSaveUrl = <?= json_encode($save_url, JSON_UNESCAPED_UNICODE) ?>;
var nbhEditorStateUrl = <?= json_encode($editor_state_url, JSON_UNESCAPED_UNICODE) ?>;
var nbhCanvasUrl = <?= json_encode($canvas_url, JSON_UNESCAPED_UNICODE) ?>;
var nbhCsrfToken = <?= json_encode(cmsForm::getCSRFToken(), JSON_UNESCAPED_UNICODE) ?>;
var nbhIconPickerUrl = <?= json_encode(href_to('admin', 'settings', ['theme', cmsConfig::get('http_template'), 'icon_list']), JSON_UNESCAPED_UNICODE) ?>;
var nbhImagePickerListUrl = <?= json_encode(href_to('nordicblocks', 'media_list'), JSON_UNESCAPED_UNICODE) ?>;
var nbhImagePickerUploadUrl = <?= json_encode(href_to('nordicblocks', 'media_upload'), JSON_UNESCAPED_UNICODE) ?>;
var nbhServerBlockType = <?= json_encode((string) ($block['type'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
var nbhCssOverlayEnabled = <?= json_encode(!empty($css_overlay_enabled), JSON_UNESCAPED_UNICODE) ?>;
var nbhCssOverlayStateUrl = <?= json_encode($css_overlay_state_url ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var nbhCssOverlaySaveUrl = <?= json_encode($css_overlay_save_url ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var nbhCssOverlayPublishUrl = <?= json_encode($css_overlay_publish_url ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var nbhCssOverlayRevisionsUrl = <?= json_encode($css_overlay_revisions_url ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var nbhCssOverlayRestoreUrl = <?= json_encode($css_overlay_restore_url ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

var nbhState = {
    loaded: false,
    server: null,
    inspector: null,
    draft: null,
    cssOverlay: null,
    blockTitle: document.getElementById('nbh-title-input').value || '',
    selectedEntity: '',
    activeTab: 'content',
    activeBreakpoint: 'desktop',
    dirty: false,
    saving: false,
    autoSelectionNotice: null,
    queuedSave: false,
    queuedSilent: true,
    debounceTimer: null,
    openAccordionByTab: {},
    canvas: {
        ready: false,
        availableEntities: [],
        selectedEntity: ''
    }
};

function nbhClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function nbhGet(obj, path, fallback) {
    var parts = String(path || '').split('.');
    var current = obj;
    for (var i = 0; i < parts.length; i++) {
        if (!parts[i]) continue;
        if (!current || typeof current !== 'object' || !(parts[i] in current)) {
            return fallback;
        }
        current = current[parts[i]];
    }
    return current === undefined ? fallback : current;
}

function nbhSet(obj, path, value) {
    var parts = String(path || '').split('.');
    var current = obj;
    for (var i = 0; i < parts.length - 1; i++) {
        var key = parts[i];
        if (!key) continue;
        if (!current[key] || typeof current[key] !== 'object') {
            current[key] = {};
        }
        current = current[key];
    }
    current[parts[parts.length - 1]] = value;
}

function nbhResolvedEntityKeys() {
    var resolved = nbhGet(nbhState, 'server.resolved.entities', {});

    return resolved && typeof resolved === 'object' ? Object.keys(resolved) : [];
}

function nbhPostCanvasMessage(type, payload) {
    var frame = document.getElementById('nbh-canvas-frame');

    if (!frame || !frame.contentWindow) {
        return;
    }

    frame.contentWindow.postMessage(Object.assign({
        source: 'nordicblocks-editor',
        type: type
    }, payload || {}), '*');
}

function nbhRequestCanvasState(reason) {
    nbhPostCanvasMessage('canvas:request-state', {
        reason: reason || 'shell-request-state'
    });
}

function nbhCanvasHasEntity(entityKey) {
    if (!entityKey) {
        return false;
    }

    if (!nbhState.canvas || !nbhState.canvas.ready) {
        return true;
    }

    return Array.isArray(nbhState.canvas.availableEntities) && nbhState.canvas.availableEntities.indexOf(entityKey) !== -1;
}

function nbhSelectableEntityKeys() {
    return nbhResolvedEntityKeys().filter(function(entityKey) {
        return nbhCanvasHasEntity(entityKey);
    });
}

function nbhResolveSelectedEntity(entityKey) {
    var resolvedKeys = nbhSelectableEntityKeys();
    var preferredEntity = '';

    if (entityKey && (!nbhState.canvas || !nbhState.canvas.ready || nbhCanvasHasEntity(entityKey))) {
        return String(entityKey);
    }

    if (nbhState.selectedEntity && (!nbhState.canvas || !nbhState.canvas.ready || nbhCanvasHasEntity(nbhState.selectedEntity))) {
        return String(nbhState.selectedEntity);
    }

    if (typeof nbhPreferredEntityForActiveTab === 'function') {
        preferredEntity = nbhPreferredEntityForActiveTab();
        if (preferredEntity) {
            return preferredEntity;
        }
    }

    return resolvedKeys.length ? resolvedKeys[0] : '';
}

function nbhSyncCanvasSelection(reason) {
    var entityKey = nbhResolveSelectedEntity('');

    if (!entityKey) {
        return;
    }

    nbhPostCanvasMessage('canvas:select-entity', {
        entity: entityKey,
        reason: reason || 'shell-sync'
    });
}

function nbhApplyCanvasState(payload) {
    var entities = Array.isArray(payload && payload.entities) ? payload.entities.filter(Boolean) : [];

    nbhState.canvas.ready = true;
    nbhState.canvas.availableEntities = entities;
    nbhState.canvas.selectedEntity = payload && payload.entity ? String(payload.entity) : '';

    if (nbhState.selectedEntity && !nbhCanvasHasEntity(nbhState.selectedEntity)) {
        nbhState.selectedEntity = '';
    }
}

function nbhHumanEntity(entityKey) {
    var registry = nbhState.server && nbhState.server.registry ? nbhState.server.registry.entities : null;
    return registry && registry[entityKey] ? registry[entityKey].label : entityKey;
}

function nbhHumanSection(sectionKey) {
    var labels = {
        text: 'Текст',
        actions: 'Действия',
        media: 'Медиа',
        repeaters: 'Повторы',
        section: 'Секция',
        typography: 'Типографика',
        surfaces: 'Поверхности',
        fineTune: 'Тонкая настройка',
        spacing: 'Отступы',
        alignment: 'Выравнивание',
        bindings: 'Данные'
    };
    return labels[sectionKey] || sectionKey;
}

function nbhBlockType() {
    return nbhGet(nbhState.server, 'block.type', '');
}

function nbhHasLegacyRepeaterCollection() {
    return nbhHasEntity('items') && nbhHasCapability('repeaterContent');
}

function nbhHasSliderRepeaterCollection() {
    return nbhHasEntity('slide') && nbhHasCapability('hasSlides');
}

function nbhCollectionRepeaterEntityKey() {
    if (nbhHasSliderRepeaterCollection()) {
        return 'slide';
    }

    if (nbhHasLegacyRepeaterCollection()) {
        return 'items';
    }

    return '';
}

function nbhCollectionRepeaterGroupKey() {
    var entityKey = nbhCollectionRepeaterEntityKey();

    if (entityKey === 'slide') {
        return 'slides';
    }

    return entityKey;
}

function nbhCollectionRepeaterPath() {
    var entityKey = nbhCollectionRepeaterEntityKey();

    if (entityKey === 'slide') {
        return 'content.slides';
    }

    if (entityKey === 'items') {
        return 'content.items';
    }

    return 'content.items';
}

function nbhCollectionBlockKind() {
    if (nbhHasSliderRepeaterCollection()) {
        if (nbhBlockType() === 'cards_slider') {
            return 'cards_slider';
        }

        return 'slider';
    }

    if (!nbhHasLegacyRepeaterCollection()) {
        return '';
    }

    if (nbhBlockType() === 'content_feed') {
        return 'content_feed';
    }

    if (nbhBlockType() === 'category_cards') {
        return 'category_cards';
    }

    if (nbhBlockType() === 'headline_feed') {
        return 'headline_feed';
    }

    if (nbhBlockType() === 'swiss_grid') {
        return 'swiss_grid';
    }

    if (nbhBlockType() === 'catalog_browser') {
        return 'catalog_browser';
    }

    if (nbhBlockType() === 'bento_feed') {
        return 'bento_feed';
    }

    return 'faq';
}

function nbhIsCardCollectionBlock() {
    var kind = nbhCollectionBlockKind();
    return kind === 'content_feed' || kind === 'category_cards' || kind === 'headline_feed' || kind === 'swiss_grid' || kind === 'catalog_browser' || kind === 'bento_feed';
}

function nbhIsSliderCollectionBlock() {
    return nbhCollectionBlockKind() === 'cards_slider';
}

function nbhBlockUiProfile() {
    if (nbhIsSliderCollectionBlock()) {
        return {
            kind: 'cards_slider',
            themeOptions: [
                { value: 'light', label: 'Светлая' },
                { value: 'alt', label: 'Мягкий фон' },
                { value: 'dark', label: 'Темная' }
            ],
            contentWidth: 1360,
            title: {
                desktopFontSize: 42,
                mobileFontSize: 28,
                desktopMarginBottom: 12,
                mobileMarginBottom: 10,
                desktopWeight: '800',
                mobileWeight: '800',
                desktopColor: '#0f172a',
                mobileColor: '#0f172a',
                desktopLineHeightPercent: 108,
                mobileLineHeightPercent: 112,
                desktopLetterSpacing: -0.4,
                mobileLetterSpacing: -0.2,
                desktopMaxWidth: 760,
                mobileMaxWidth: 540,
                tag: 'h2'
            },
            subtitle: {
                desktopFontSize: 18,
                mobileFontSize: 15,
                desktopMarginBottom: 0,
                mobileMarginBottom: 0,
                desktopWeight: '400',
                mobileWeight: '400',
                desktopColor: '#475569',
                mobileColor: '#475569',
                desktopLineHeightPercent: 160,
                mobileLineHeightPercent: 160,
                desktopLetterSpacing: 0,
                mobileLetterSpacing: 0,
                desktopMaxWidth: 640,
                mobileMaxWidth: 480
            },
            buttonsText: {
                desktopFontSize: 14,
                mobileFontSize: 14,
                desktopWeight: '700',
                mobileWeight: '700',
                desktopColor: '#ffffff',
                mobileColor: '#ffffff',
                desktopLineHeightPercent: 120,
                mobileLineHeightPercent: 120,
                desktopLetterSpacing: 0,
                mobileLetterSpacing: 0
            },
            slideMedia: {
                aspectRatio: '4:3',
                objectFit: 'cover',
                radius: 24
            },
            slideSurface: {
                backgroundMode: 'solid',
                backgroundColor: '#ffffff',
                padding: 0,
                radius: 28,
                borderWidth: 1,
                borderColor: '#dbe4ef',
                shadow: 'sm'
            },
            slideTypography: {
                eyebrow: {
                    desktopFontSize: 12,
                    mobileFontSize: 11,
                    desktopWeight: '700',
                    mobileWeight: '700',
                    desktopColor: '#2563eb',
                    mobileColor: '#2563eb',
                    desktopLineHeightPercent: 120,
                    mobileLineHeightPercent: 120,
                    desktopLetterSpacing: 0.6,
                    mobileLetterSpacing: 0.5
                },
                title: {
                    desktopFontSize: 24,
                    mobileFontSize: 19,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 118,
                    mobileLineHeightPercent: 122,
                    desktopLetterSpacing: -0.2,
                    mobileLetterSpacing: -0.1
                },
                text: {
                    desktopFontSize: 15,
                    mobileFontSize: 14,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#475569',
                    mobileColor: '#475569',
                    desktopLineHeightPercent: 155,
                    mobileLineHeightPercent: 155,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0
                },
                meta: {
                    desktopFontSize: 12,
                    mobileFontSize: 12,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#64748b',
                    mobileColor: '#64748b',
                    desktopLineHeightPercent: 130,
                    mobileLineHeightPercent: 130,
                    desktopLetterSpacing: 0.2,
                    mobileLetterSpacing: 0.2
                },
                primaryAction: {
                    desktopFontSize: 14,
                    mobileFontSize: 14,
                    desktopWeight: '700',
                    mobileWeight: '700',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 120,
                    mobileLineHeightPercent: 120,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0
                },
                secondaryAction: {
                    desktopFontSize: 14,
                    mobileFontSize: 14,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#475569',
                    mobileColor: '#475569',
                    desktopLineHeightPercent: 120,
                    mobileLineHeightPercent: 120,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0
                }
            },
            navigation: {
                size: 46,
                radius: 999,
                backgroundColor: '#0f172a',
                textColor: '#ffffff',
                borderColor: '#0f172a',
                shadow: 'md'
            },
            pagination: {
                dotSize: 10,
                gap: 8,
                color: '#cbd5e1',
                activeColor: '#0f172a'
            },
            progress: {
                trackColor: '#e2e8f0',
                fillColor: '#0f172a',
                height: 4,
                radius: 999
            },
            layout: {
                desktopPaddingTop: 72,
                desktopPaddingBottom: 72,
                mobilePaddingTop: 40,
                mobilePaddingBottom: 40,
                desktopSlidesPerView: 3,
                mobileSlidesPerView: 1,
                desktopGap: 24,
                mobileGap: 16,
                desktopHeaderGap: 28,
                mobileHeaderGap: 18,
                desktopContentGap: 28,
                mobileContentGap: 18,
                desktopActionsGap: 12,
                mobileActionsGap: 10,
                navigationPosition: 'overlay',
                paginationPosition: 'below',
                progressPosition: 'below',
                autoplay: '0',
                loop: '0',
                swipe: '1',
                autoplayDelay: 4500,
                transitionMs: 450,
                primaryControl: 'slider'
            }
        };
    }

    if (nbhHasLegacyRepeaterCollection()) {
        if (nbhCollectionBlockKind() === 'headline_feed') {
            return {
                kind: 'headline_feed',
                themeOptions: [
                    { value: 'light', label: 'Светлая' },
                    { value: 'alt', label: 'Мягкий фон' },
                    { value: 'dark', label: 'Темная' }
                ],
                presets: [
                    { value: 'split', label: 'Главный материал слева + лента' },
                    { value: 'stack', label: 'Главный материал сверху + сетка' },
                    { value: 'cover', label: 'Обложка главного материала + сетка' }
                ],
                contentWidth: 1140,
                title: {
                    desktopFontSize: 32,
                    mobileFontSize: 24,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 110,
                    mobileLineHeightPercent: 110,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 640,
                    mobileMaxWidth: 640,
                    tag: 'h2',
                    desktopExtras: true,
                },
                subtitle: {
                    desktopFontSize: 16,
                    mobileFontSize: 14,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#475569',
                    mobileColor: '#475569',
                    desktopLineHeightPercent: 160,
                    mobileLineHeightPercent: 160,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 640,
                    mobileMaxWidth: 640,
                    desktopExtras: true,
                },
                meta: {
                    desktopFontSize: 12,
                    mobileFontSize: 11,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#64748b',
                    mobileColor: '#64748b',
                    desktopLineHeightPercent: 140,
                    mobileLineHeightPercent: 140,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                },
                media: {
                    aspectRatio: '4:3',
                    objectFit: 'cover',
                    radius: 22,
                },
                itemSurface: {
                    radius: 22,
                    borderWidth: 1,
                    borderColor: '#dbe4ef',
                    shadow: 'md',
                },
                itemTypography: {
                    enabled: true,
                    titleLabel: 'Заголовки материалов',
                    textLabel: 'Анонсы материалов',
                    title: {
                        desktopFontSize: 17,
                        mobileFontSize: 16,
                        desktopWeight: '800',
                        mobileWeight: '800',
                        desktopColor: '#0f172a',
                        mobileColor: '#0f172a',
                        desktopLineHeightPercent: 130,
                        mobileLineHeightPercent: 130,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    text: {
                        desktopFontSize: 14,
                        mobileFontSize: 13,
                        desktopWeight: '400',
                        mobileWeight: '400',
                        desktopColor: '#475569',
                        mobileColor: '#475569',
                        desktopLineHeightPercent: 160,
                        mobileLineHeightPercent: 160,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    link: {
                        desktopFontSize: 12,
                        mobileFontSize: 12,
                        desktopWeight: '700',
                        mobileWeight: '700',
                        desktopColor: '',
                        mobileColor: '',
                        desktopLineHeightPercent: 120,
                        mobileLineHeightPercent: 120,
                        desktopLetterSpacing: 1,
                        mobileLetterSpacing: 1,
                    },
                },
                layout: {
                    desktopPaddingTop: 64,
                    desktopPaddingBottom: 64,
                    mobilePaddingTop: 44,
                    mobilePaddingBottom: 44,
                    supportsMinHeight: false,
                    primaryControl: 'headline-feed',
                    desktopColumns: 3,
                    mobileColumns: 1,
                    desktopCardGap: 18,
                    mobileCardGap: 14,
                    desktopHeaderGap: 18,
                    mobileHeaderGap: 14,
                },
            };
        }

        if (nbhCollectionBlockKind() === 'category_cards') {
            return {
                kind: 'category_cards',
                themeOptions: [
                    { value: 'light', label: 'Светлая' },
                    { value: 'alt', label: 'Мягкий фон' },
                    { value: 'dark', label: 'Темная' }
                ],
                contentWidth: 1180,
                eyebrow: {
                    desktopFontSize: 13,
                    mobileFontSize: 12,
                    desktopMarginBottom: 8,
                    mobileMarginBottom: 6,
                    desktopWeight: '700',
                    mobileWeight: '700',
                    desktopColor: '#0f766e',
                    mobileColor: '#0f766e',
                    desktopLineHeightPercent: 140,
                    mobileLineHeightPercent: 140,
                    desktopLetterSpacing: 1,
                    mobileLetterSpacing: 1,
                    textTransform: 'uppercase',
                },
                title: {
                    desktopFontSize: 30,
                    mobileFontSize: 24,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 110,
                    mobileLineHeightPercent: 110,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 720,
                    mobileMaxWidth: 720,
                    tag: 'h2',
                    desktopExtras: true,
                },
                subtitle: {
                    desktopFontSize: 15,
                    mobileFontSize: 14,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#475569',
                    mobileColor: '#475569',
                    desktopLineHeightPercent: 155,
                    mobileLineHeightPercent: 155,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 720,
                    mobileMaxWidth: 720,
                    desktopExtras: true,
                },
                meta: {
                    desktopFontSize: 12,
                    mobileFontSize: 11,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#64748b',
                    mobileColor: '#64748b',
                    desktopLineHeightPercent: 140,
                    mobileLineHeightPercent: 140,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                },
                media: {
                    aspectRatio: '4:3',
                    objectFit: 'cover',
                    radius: 18,
                },
                itemSurface: {
                    radius: 18,
                    borderWidth: 1,
                    borderColor: '#dbe4ef',
                    shadow: 'sm',
                },
                itemTypography: {
                    enabled: true,
                    titleLabel: 'Заголовок карточки',
                    textLabel: 'Анонс карточки',
                    title: {
                        desktopFontSize: 18,
                        mobileFontSize: 16,
                        desktopWeight: '800',
                        mobileWeight: '800',
                        desktopColor: '#0f172a',
                        mobileColor: '#0f172a',
                        desktopLineHeightPercent: 130,
                        mobileLineHeightPercent: 130,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    text: {
                        desktopFontSize: 14,
                        mobileFontSize: 13,
                        desktopWeight: '400',
                        mobileWeight: '400',
                        desktopColor: '#475569',
                        mobileColor: '#475569',
                        desktopLineHeightPercent: 160,
                        mobileLineHeightPercent: 160,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                },
                layout: {
                    desktopPaddingTop: 56,
                    desktopPaddingBottom: 56,
                    mobilePaddingTop: 40,
                    mobilePaddingBottom: 40,
                    supportsMinHeight: false,
                    primaryControl: 'feed-grid',
                    desktopColumns: 4,
                    mobileColumns: 2,
                    desktopCardGap: 16,
                    mobileCardGap: 12,
                    desktopHeaderGap: 14,
                    mobileHeaderGap: 12,
                },
            };
        }

        if (nbhCollectionBlockKind() === 'swiss_grid') {
            return {
                kind: 'swiss_grid',
                themeOptions: [
                    { value: 'light', label: 'Светлая' },
                    { value: 'alt', label: 'Мягкий фон' },
                    { value: 'dark', label: 'Темная' }
                ],
                contentWidth: 1400,
                title: {
                    desktopFontSize: 48,
                    mobileFontSize: 32,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '700',
                    mobileWeight: '700',
                    desktopColor: '#111827',
                    mobileColor: '#111827',
                    desktopLineHeightPercent: 110,
                    mobileLineHeightPercent: 110,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 1400,
                    mobileMaxWidth: 1400,
                    tag: 'h2',
                    desktopExtras: true,
                },
                subtitle: {
                    desktopFontSize: 16,
                    mobileFontSize: 12,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '500',
                    mobileWeight: '500',
                    desktopColor: '#5b6472',
                    mobileColor: '#5b6472',
                    desktopLineHeightPercent: 140,
                    mobileLineHeightPercent: 140,
                    desktopLetterSpacing: 1,
                    mobileLetterSpacing: 1,
                    desktopMaxWidth: 1400,
                    mobileMaxWidth: 1400,
                    desktopExtras: true,
                },
                meta: {
                    desktopFontSize: 11,
                    mobileFontSize: 11,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '700',
                    mobileWeight: '700',
                    desktopColor: '#d92e1c',
                    mobileColor: '#d92e1c',
                    desktopLineHeightPercent: 120,
                    mobileLineHeightPercent: 120,
                    desktopLetterSpacing: 1,
                    mobileLetterSpacing: 1,
                },
                media: {
                    aspectRatio: '4:3',
                    objectFit: 'cover',
                    radius: 0,
                },
                itemSurface: {
                    radius: 0,
                    borderWidth: 1,
                    borderColor: '#eaeaea',
                    shadow: 'none',
                },
                itemTypography: {
                    enabled: true,
                    titleLabel: 'Заголовок карточки',
                    textLabel: 'Текст карточки',
                    title: {
                        desktopFontSize: 20,
                        mobileFontSize: 17,
                        desktopWeight: '700',
                        mobileWeight: '700',
                        desktopColor: '#111827',
                        mobileColor: '#111827',
                        desktopLineHeightPercent: 124,
                        mobileLineHeightPercent: 124,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    text: {
                        desktopFontSize: 14,
                        mobileFontSize: 13,
                        desktopWeight: '400',
                        mobileWeight: '400',
                        desktopColor: '#475569',
                        mobileColor: '#475569',
                        desktopLineHeightPercent: 148,
                        mobileLineHeightPercent: 148,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    link: {
                        desktopFontSize: 12,
                        mobileFontSize: 12,
                        desktopWeight: '700',
                        mobileWeight: '700',
                        desktopColor: '',
                        mobileColor: '',
                        desktopLineHeightPercent: 120,
                        mobileLineHeightPercent: 120,
                        desktopLetterSpacing: 1,
                        mobileLetterSpacing: 1,
                    },
                },
                layout: {
                    desktopPaddingTop: 0,
                    desktopPaddingBottom: 0,
                    mobilePaddingTop: 0,
                    mobilePaddingBottom: 0,
                    supportsMinHeight: false,
                    primaryControl: 'feed-grid',
                    desktopColumns: 3,
                    mobileColumns: 1,
                    desktopCardGap: 0,
                    mobileCardGap: 0,
                    desktopHeaderGap: 0,
                    mobileHeaderGap: 0,
                },
            };
        }

        if (nbhCollectionBlockKind() === 'content_feed') {
            return {
                kind: 'content_feed',
                presets: [
                    { value: 'default', label: 'Редакционная лента' },
                    { value: 'swiss', label: 'Швейцарская сетка' }
                ],
                themeOptions: [
                    { value: 'light', label: 'Светлая' },
                    { value: 'alt', label: 'Мягкий фон' },
                    { value: 'dark', label: 'Темная' }
                ],
                contentWidth: 1080,
                title: {
                    desktopFontSize: 36,
                    mobileFontSize: 27,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 110,
                    mobileLineHeightPercent: 110,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 700,
                    mobileMaxWidth: 700,
                    tag: 'h2',
                    desktopExtras: true,
                },
                subtitle: {
                    desktopFontSize: 16,
                    mobileFontSize: 15,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#475569',
                    mobileColor: '#475569',
                    desktopLineHeightPercent: 160,
                    mobileLineHeightPercent: 160,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 620,
                    mobileMaxWidth: 620,
                    desktopExtras: true,
                },
                meta: {
                    desktopFontSize: 13,
                    mobileFontSize: 12,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#64748b',
                    mobileColor: '#64748b',
                    desktopLineHeightPercent: 140,
                    mobileLineHeightPercent: 140,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                },
                media: {
                    aspectRatio: '16:10',
                    objectFit: 'cover',
                    radius: 20,
                },
                itemSurface: {
                    radius: 22,
                    borderWidth: 1,
                    borderColor: '#e2e8f0',
                    shadow: 'md',
                },
                itemTypography: {
                    enabled: true,
                    titleLabel: 'Заголовок карточки',
                    textLabel: 'Анонс карточки',
                    title: {
                        desktopFontSize: 20,
                        mobileFontSize: 18,
                        desktopWeight: '800',
                        mobileWeight: '800',
                        desktopColor: '#0f172a',
                        mobileColor: '#0f172a',
                        desktopLineHeightPercent: 130,
                        mobileLineHeightPercent: 130,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    text: {
                        desktopFontSize: 15,
                        mobileFontSize: 14,
                        desktopWeight: '400',
                        mobileWeight: '400',
                        desktopColor: '#475569',
                        mobileColor: '#475569',
                        desktopLineHeightPercent: 165,
                        mobileLineHeightPercent: 165,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    link: {
                        desktopFontSize: 13,
                        mobileFontSize: 12,
                        desktopWeight: '700',
                        mobileWeight: '700',
                        desktopColor: '',
                        mobileColor: '',
                        desktopLineHeightPercent: 120,
                        mobileLineHeightPercent: 120,
                        desktopLetterSpacing: 1,
                        mobileLetterSpacing: 1,
                    },
                },
                layout: {
                    desktopPaddingTop: 64,
                    desktopPaddingBottom: 64,
                    mobilePaddingTop: 44,
                    mobilePaddingBottom: 44,
                    supportsMinHeight: false,
                    primaryControl: 'feed-grid',
                    desktopColumns: 3,
                    mobileColumns: 1,
                    desktopCardGap: 18,
                    mobileCardGap: 14,
                    desktopHeaderGap: 18,
                    mobileHeaderGap: 14,
                },
            };
        }

        if (nbhCollectionBlockKind() === 'catalog_browser') {
            return {
                kind: 'catalog_browser',
                themeOptions: [
                    { value: 'light', label: 'Светлая' },
                    { value: 'alt', label: 'Мягкий фон' },
                    { value: 'dark', label: 'Темная' }
                ],
                contentWidth: 1180,
                title: {
                    desktopFontSize: 34,
                    mobileFontSize: 26,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 112,
                    mobileLineHeightPercent: 112,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 760,
                    mobileMaxWidth: 760,
                    tag: 'h2',
                    desktopExtras: true,
                },
                subtitle: {
                    desktopFontSize: 16,
                    mobileFontSize: 14,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#475569',
                    mobileColor: '#475569',
                    desktopLineHeightPercent: 160,
                    mobileLineHeightPercent: 160,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 720,
                    mobileMaxWidth: 720,
                    desktopExtras: true,
                },
                meta: {
                    desktopFontSize: 12,
                    mobileFontSize: 11,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#64748b',
                    mobileColor: '#64748b',
                    desktopLineHeightPercent: 140,
                    mobileLineHeightPercent: 140,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                },
                buttonsText: {
                    desktopFontSize: 15,
                    mobileFontSize: 14,
                    desktopWeight: '700',
                    mobileWeight: '700',
                    desktopColor: '#ffffff',
                    mobileColor: '#ffffff',
                    desktopLineHeightPercent: 120,
                    mobileLineHeightPercent: 120,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                },
                media: {
                    aspectRatio: '4:3',
                    objectFit: 'cover',
                    radius: 20,
                    inheritGlobalStyle: true,
                },
                mediaSurface: {
                    backgroundMode: 'transparent',
                    backgroundColor: '#ffffff',
                    padding: 0,
                    radius: 20,
                    borderWidth: 0,
                    borderColor: '#dbe4ef',
                    shadow: 'none',
                },
                toolbarSurface: {
                    backgroundMode: 'solid',
                    backgroundColor: '#ffffff',
                    padding: 16,
                    radius: 22,
                    borderWidth: 1,
                    borderColor: '#dbe4ef',
                    shadow: 'sm',
                },
                toolbarControlsSurface: {
                    backgroundMode: 'solid',
                    backgroundColor: '#ffffff',
                    radius: 16,
                    borderWidth: 1,
                    borderColor: '#d5dfeb',
                    shadow: 'none',
                },
                itemSurface: {
                    radius: 22,
                    borderWidth: 1,
                    borderColor: '#dbe4ef',
                    shadow: 'md',
                    inheritGlobalStyle: true,
                },
                cardPrice: {
                    desktopFontSize: 19,
                    mobileFontSize: 17,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 120,
                    mobileLineHeightPercent: 120,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                },
                itemTypography: {
                    enabled: true,
                    titleLabel: 'Заголовок карточки',
                    textLabel: 'Описание карточки',
                    title: {
                        desktopFontSize: 20,
                        mobileFontSize: 18,
                        desktopWeight: '800',
                        mobileWeight: '800',
                        desktopColor: '#0f172a',
                        mobileColor: '#0f172a',
                        desktopLineHeightPercent: 130,
                        mobileLineHeightPercent: 130,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    text: {
                        desktopFontSize: 14,
                        mobileFontSize: 13,
                        desktopWeight: '400',
                        mobileWeight: '400',
                        desktopColor: '#475569',
                        mobileColor: '#475569',
                        desktopLineHeightPercent: 160,
                        mobileLineHeightPercent: 160,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                },
                layout: {
                    desktopPaddingTop: 64,
                    desktopPaddingBottom: 64,
                    mobilePaddingTop: 44,
                    mobilePaddingBottom: 44,
                    supportsMinHeight: false,
                    primaryControl: 'feed-grid',
                    desktopColumns: 3,
                    mobileColumns: 1,
                    desktopCardGap: 20,
                    mobileCardGap: 14,
                    desktopHeaderGap: 20,
                    mobileHeaderGap: 14,
                },
            };
        }


        if (nbhCollectionBlockKind() === 'bento_feed') {
            return {
                kind: 'bento_feed',
                presets: [
                    { value: 'editorial_mix', label: 'Editorial Mix' },
                    { value: 'feature_stack', label: 'Feature Stack' }
                ],
                themeOptions: [
                    { value: 'light', label: 'Светлая' },
                    { value: 'alt', label: 'Мягкий фон' },
                    { value: 'dark', label: 'Темная' }
                ],
                contentWidth: 1360,
                title: {
                    desktopFontSize: 44,
                    mobileFontSize: 30,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '800',
                    mobileWeight: '800',
                    desktopColor: '#0f172a',
                    mobileColor: '#0f172a',
                    desktopLineHeightPercent: 104,
                    mobileLineHeightPercent: 108,
                    desktopLetterSpacing: -1,
                    mobileLetterSpacing: -1,
                    desktopMaxWidth: 920,
                    mobileMaxWidth: 920,
                    tag: 'h2',
                    desktopExtras: true,
                },
                subtitle: {
                    desktopFontSize: 16,
                    mobileFontSize: 14,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#5b6472',
                    mobileColor: '#5b6472',
                    desktopLineHeightPercent: 155,
                    mobileLineHeightPercent: 155,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0,
                    desktopMaxWidth: 760,
                    mobileMaxWidth: 760,
                    desktopExtras: true,
                },
                meta: {
                    desktopFontSize: 11,
                    mobileFontSize: 11,
                    desktopMarginBottom: 0,
                    mobileMarginBottom: 0,
                    desktopWeight: '600',
                    mobileWeight: '600',
                    desktopColor: '#5b6472',
                    mobileColor: '#5b6472',
                    desktopLineHeightPercent: 130,
                    mobileLineHeightPercent: 130,
                    desktopLetterSpacing: 1,
                    mobileLetterSpacing: 1,
                },
                media: {
                    aspectRatio: '4:3',
                    objectFit: 'cover',
                    radius: 0,
                },
                itemSurface: {
                    radius: 0,
                    borderWidth: 1,
                    borderColor: '#d9dde4',
                    shadow: 'none',
                },
                itemTypography: {
                    enabled: true,
                    titleLabel: 'Заголовок карточки',
                    textLabel: 'Анонс карточки',
                    title: {
                        desktopFontSize: 22,
                        mobileFontSize: 18,
                        desktopWeight: '700',
                        mobileWeight: '700',
                        desktopColor: '#111827',
                        mobileColor: '#111827',
                        desktopLineHeightPercent: 118,
                        mobileLineHeightPercent: 122,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    text: {
                        desktopFontSize: 15,
                        mobileFontSize: 14,
                        desktopWeight: '400',
                        mobileWeight: '400',
                        desktopColor: '#5b6472',
                        mobileColor: '#5b6472',
                        desktopLineHeightPercent: 150,
                        mobileLineHeightPercent: 150,
                        desktopLetterSpacing: 0,
                        mobileLetterSpacing: 0,
                    },
                    link: {
                        desktopFontSize: 12,
                        mobileFontSize: 12,
                        desktopWeight: '700',
                        mobileWeight: '700',
                        desktopColor: '',
                        mobileColor: '',
                        desktopLineHeightPercent: 120,
                        mobileLineHeightPercent: 120,
                        desktopLetterSpacing: 1,
                        mobileLetterSpacing: 1,
                    },
                },
                layout: {
                    desktopPaddingTop: 0,
                    desktopPaddingBottom: 0,
                    mobilePaddingTop: 0,
                    mobilePaddingBottom: 0,
                    supportsMinHeight: false,
                    primaryControl: 'feed-grid',
                    desktopColumns: 3,
                    mobileColumns: 1,
                    desktopCardGap: 0,
                    mobileCardGap: 0,
                    desktopHeaderGap: 18,
                    mobileHeaderGap: 14,
                },
            };
        }
        return {
            kind: 'collection',
            themeOptions: [
                { value: 'light', label: 'Светлая' },
                { value: 'alt', label: 'Серый фон' },
                { value: 'dark', label: 'Темная' }
            ],
            contentWidth: 760,
            eyebrow: {
                desktopFontSize: 13,
                mobileFontSize: 12,
                desktopMarginBottom: 8,
                mobileMarginBottom: 6,
                desktopWeight: '700',
                mobileWeight: '700',
                desktopColor: '#0f766e',
                mobileColor: '#0f766e',
                desktopLineHeightPercent: 140,
                mobileLineHeightPercent: 140,
                desktopLetterSpacing: 1,
                mobileLetterSpacing: 1,
                textTransform: 'uppercase',
            },
            title: {
                desktopFontSize: 48,
                mobileFontSize: 32,
                desktopMarginBottom: 0,
                mobileMarginBottom: 0,
                weight: '800',
                tag: 'h2',
                desktopExtras: true,
            },
            subtitle: {
                desktopFontSize: 18,
                mobileFontSize: 16,
                desktopMarginBottom: 32,
                mobileMarginBottom: 24,
                desktopExtras: true,
            },
            itemTypography: {
                enabled: true,
                questionDesktopSize: 18,
                questionMobileSize: 17,
                answerDesktopSize: 16,
                answerMobileSize: 15,
            },
            layout: {
                desktopPaddingTop: 88,
                desktopPaddingBottom: 88,
                mobilePaddingTop: 56,
                mobilePaddingBottom: 56,
                supportsMinHeight: false,
                primaryControl: 'align',
            },
        };
    }

    return {
        kind: 'hero',
        presets: [
            { value: 'classic', label: 'Текст по центру' },
            { value: 'split-left', label: 'Фото слева' },
            { value: 'split-right', label: 'Фото справа' },
            { value: 'edge-left', label: 'Фото слева до края' },
            { value: 'edge-right', label: 'Фото справа до края' },
            { value: 'strip', label: 'Без вертикальных отступов' }
        ],
        layoutPreset: 'classic',
        themeOptions: [
            { value: 'light', label: 'Светлая' },
            { value: 'dark', label: 'Темная' },
            { value: 'accent', label: 'Акцентная' }
        ],
        contentWidth: 640,
        eyebrow: {
            desktopFontSize: 14,
            mobileFontSize: 13,
            desktopMarginBottom: 16,
            mobileMarginBottom: 14,
            desktopWeight: '600',
            mobileWeight: '600',
            desktopColor: '#2563eb',
            mobileColor: '#2563eb',
            desktopLineHeightPercent: 140,
            mobileLineHeightPercent: 140,
            desktopLetterSpacing: 1,
            mobileLetterSpacing: 1,
            textTransform: 'uppercase',
        },
        title: {
            desktopFontSize: 64,
            mobileFontSize: 40,
            desktopMarginBottom: 16,
            mobileMarginBottom: 14,
            desktopWeight: '900',
            mobileWeight: '900',
            desktopColor: '#0f172a',
            mobileColor: '#0f172a',
            desktopLineHeightPercent: 110,
            mobileLineHeightPercent: 110,
            desktopLetterSpacing: 0,
            mobileLetterSpacing: 0,
            desktopMaxWidth: 600,
            mobileMaxWidth: 600,
            tag: 'h1',
        },
        subtitle: {
            desktopFontSize: 20,
            mobileFontSize: 18,
            desktopMarginBottom: 24,
            mobileMarginBottom: 20,
            desktopWeight: '400',
            mobileWeight: '400',
            desktopColor: '#475569',
            mobileColor: '#475569',
            desktopLineHeightPercent: 165,
            mobileLineHeightPercent: 165,
            desktopLetterSpacing: 0,
            mobileLetterSpacing: 0,
            desktopMaxWidth: 720,
            mobileMaxWidth: 720,
        },
        body: {
            desktopFontSize: 18,
            mobileFontSize: 17,
            desktopWeight: '400',
            mobileWeight: '400',
            desktopColor: '#f8fafc',
            mobileColor: '#f8fafc',
            desktopLineHeightPercent: 170,
            mobileLineHeightPercent: 170,
            desktopLetterSpacing: 0,
            mobileLetterSpacing: 0,
        },
        meta: {
            desktopFontSize: 14,
            mobileFontSize: 13,
            desktopMarginBottom: 24,
            mobileMarginBottom: 20,
            desktopWeight: '600',
            mobileWeight: '600',
            desktopColor: '#64748b',
            mobileColor: '#64748b',
            desktopLineHeightPercent: 140,
            mobileLineHeightPercent: 140,
            desktopLetterSpacing: 0,
            mobileLetterSpacing: 0,
        },
        buttonsText: {
            desktopFontSize: 16,
            mobileFontSize: 15,
            desktopWeight: '600',
            mobileWeight: '600',
            desktopColor: '#ffffff',
            mobileColor: '#ffffff',
            desktopLineHeightPercent: 120,
            mobileLineHeightPercent: 120,
            desktopLetterSpacing: 0,
            mobileLetterSpacing: 0,
        },
        media: {
            aspectRatio: '16:10',
            objectFit: 'cover',
            radius: 28,
        },
        mediaSurface: {
            backgroundMode: 'transparent',
            backgroundColor: '#ffffff',
            padding: 0,
            radius: 28,
            borderWidth: 0,
            borderColor: '#e2e8f0',
            shadow: 'lg',
        },
        accentSurface: {
            backgroundMode: 'solid',
            backgroundColor: '#2563eb',
        },
        bodySurface: {
            backgroundMode: 'solid',
            backgroundColor: '#1d1d1f',
        },
        itemTypography: {
            enabled: false,
        },
        layout: {
            desktopPaddingTop: 96,
            desktopPaddingBottom: 96,
            mobilePaddingTop: 56,
            mobilePaddingBottom: 56,
            desktopContentGap: 40,
            mobileContentGap: 24,
            desktopActionsGap: 12,
            mobileActionsGap: 10,
            containerMode: 'contained',
            mediaPositionDesktop: 'start',
            mediaPositionMobile: 'top',
            supportsMinHeight: true,
            primaryControl: 'mode',
        },
    };
}

function nbhRepeaterItems() {
    var items = nbhGet(nbhState.draft, nbhCollectionRepeaterPath(), []);
    return Array.isArray(items) ? items : [];
}

function nbhCatalogAspectRatioOptions() {
    return [
        { value: 'auto', label: 'По размеру изображения' },
        { value: '16:10', label: '16:10' },
        { value: '16:9', label: '16:9' },
        { value: '4:3', label: '4:3' },
        { value: '1:1', label: '1:1' },
        { value: '3:4', label: '3:4' }
    ];
}

function nbhCatalogObjectFitOptions() {
    return [
        { value: 'cover', label: 'Заполнить кадр' },
        { value: 'contain', label: 'Показать целиком' }
    ];
}

function nbhIsCatalogBrowserBlock() {
    return nbhServerBlockType === 'catalog_browser' || nbhBlockType() === 'catalog_browser';
}

function nbhCatalogItemIdValue(item) {
    if (!item || typeof item !== 'object') {
        return '';
    }

    if (typeof item.id === 'string' && item.id.trim()) {
        return item.id.trim();
    }

    if (typeof item.itemId === 'string' && item.itemId.trim()) {
        return item.itemId.trim();
    }

    if (typeof item.item_id === 'string' && item.item_id.trim()) {
        return item.item_id.trim();
    }

    return '';
}

function nbhCatalogSlug(value) {
    return String(value == null ? '' : value)
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 48);
}

function nbhCatalogUsedIdMap(excludeIndex) {
    var used = {};

    nbhRepeaterItems().forEach(function(item, index) {
        var itemId;
        if (index === excludeIndex) {
            return;
        }
        itemId = nbhCatalogItemIdValue(item);
        if (itemId) {
            used[itemId] = true;
        }
    });

    return used;
}

function nbhCatalogCreateItemId(usedIds, seed) {
    var base = nbhCatalogSlug(seed) || 'catalog-item';
    var candidate = base;
    var suffix = 2;

    usedIds = usedIds && typeof usedIds === 'object' ? usedIds : {};

    while (usedIds[candidate]) {
        candidate = base + '-' + suffix;
        suffix += 1;
    }

    usedIds[candidate] = true;
    return candidate;
}

function nbhCatalogNormalizeTags(value) {
    if (Array.isArray(value)) {
        return value.map(function(tag) {
            return String(tag == null ? '' : tag).trim();
        }).filter(Boolean);
    }

    return String(value == null ? '' : value)
        .split(',')
        .map(function(tag) { return tag.trim(); })
        .filter(Boolean);
}

function nbhCatalogNormalizeGallery(value) {
    var slides = value;

    if (typeof slides === 'string' && slides.trim()) {
        try {
            slides = JSON.parse(slides);
        } catch (error) {
            slides = [];
        }
    }

    if (!Array.isArray(slides)) {
        slides = [];
    }

    return slides.map(function(slide) {
        if (!slide || typeof slide !== 'object' || Array.isArray(slide)) {
            return null;
        }

        return {
            src: String(slide.src == null ? '' : slide.src).trim(),
            alt: String(slide.alt == null ? '' : slide.alt).trim(),
            caption: String(slide.caption == null ? '' : slide.caption).trim()
        };
    }).filter(function(slide) {
        return slide && (slide.src || slide.alt || slide.caption);
    });
}

function nbhCatalogItemHasContent(item) {
    if (!item || typeof item !== 'object') {
        return false;
    }

    return !!(
        String(item.title == null ? '' : item.title).trim()
        || String(item.excerpt == null ? '' : item.excerpt).trim()
        || String(item.category == null ? '' : item.category).trim()
        || String(item.price == null ? '' : item.price).trim()
        || String(item.url == null ? '' : item.url).trim()
        || String(item.image == null ? '' : item.image).trim()
        || String(item.badge == null ? '' : item.badge).trim()
    );
}

function nbhCatalogBaseItem() {
    return {
        id: '',
        itemId: '',
        item_id: '',
        category: 'Категория',
        title: 'Новая позиция каталога',
        excerpt: 'Короткое описание позиции, чтобы объяснить ценность карточки и сценарий перехода.',
        text: 'Короткое описание позиции, чтобы объяснить ценность карточки и сценарий перехода.',
        category_url: '',
        categoryUrl: '',
        badge: 'Хит',
        price: '9 900',
        priceOld: '12 900',
        price_old: '12 900',
        currency: '₽',
        availability: 'available',
        tags: ['Новинка'],
        cta_label: 'Открыть',
        ctaLabel: 'Открыть',
        cta_kind: 'url',
        ctaKind: 'url',
        cta_url: '/catalog/item',
        ctaUrl: '/catalog/item',
        messenger_type: 'none',
        messengerType: 'none',
        link_label: 'Открыть',
        linkLabel: 'Открыть',
        url: '/catalog/item',
        image: '',
        imageAlt: '',
        alt: '',
        gallery: []
    };
}

function nbhCatalogImportBaseItem() {
    return {
        id: '',
        itemId: '',
        item_id: '',
        category: '',
        title: '',
        excerpt: '',
        text: '',
        category_url: '',
        categoryUrl: '',
        badge: '',
        price: '',
        priceOld: '',
        price_old: '',
        currency: '',
        availability: 'available',
        tags: [],
        cta_label: '',
        ctaLabel: '',
        cta_kind: 'url',
        ctaKind: 'url',
        cta_url: '',
        ctaUrl: '',
        messenger_type: 'none',
        messengerType: 'none',
        link_label: '',
        linkLabel: '',
        url: '',
        image: '',
        imageAlt: '',
        alt: '',
        gallery: []
    };
}

function nbhNormalizeCatalogItem(item, options) {
    var normalized = options && options.emptyBase ? nbhCatalogImportBaseItem() : nbhCatalogBaseItem();
    var source = item && typeof item === 'object' && !Array.isArray(item) ? item : {};
    var usedIds = options && options.usedIds && typeof options.usedIds === 'object' ? options.usedIds : {};
    var seed = source.title || source.name || source.category || normalized.title;
    var itemId = (options && options.forceNewId) ? '' : nbhCatalogItemIdValue(source);

    normalized.category = String(source.category == null ? normalized.category : source.category).trim();
    normalized.title = String(source.title == null ? normalized.title : source.title).trim();
    normalized.excerpt = String(source.excerpt == null ? (source.text == null ? normalized.excerpt : source.text) : source.excerpt).trim();
    normalized.text = normalized.excerpt;
    normalized.categoryUrl = String(source.categoryUrl == null ? (source.category_url == null ? normalized.categoryUrl : source.category_url) : source.categoryUrl).trim();
    normalized.category_url = normalized.categoryUrl;
    normalized.badge = String(source.badge == null ? normalized.badge : source.badge).trim();
    normalized.price = String(source.price == null ? normalized.price : source.price).trim();
    normalized.priceOld = String(source.priceOld == null ? (source.price_old == null ? normalized.priceOld : source.price_old) : source.priceOld).trim();
    normalized.price_old = normalized.priceOld;
    normalized.currency = String(source.currency == null ? normalized.currency : source.currency).trim();
    normalized.availability = String(source.availability == null ? normalized.availability : source.availability).trim() || 'available';
    normalized.tags = nbhCatalogNormalizeTags(source.tags == null ? normalized.tags : source.tags);
    normalized.ctaLabel = String(source.ctaLabel == null ? (source.cta_label == null ? normalized.ctaLabel : source.cta_label) : source.ctaLabel).trim();
    normalized.cta_label = normalized.ctaLabel;
    normalized.ctaKind = String(source.ctaKind == null ? (source.cta_kind == null ? normalized.ctaKind : source.cta_kind) : source.ctaKind).trim() || 'url';
    normalized.cta_kind = normalized.ctaKind;
    normalized.ctaUrl = String(source.ctaUrl == null ? (source.cta_url == null ? normalized.ctaUrl : source.cta_url) : source.ctaUrl).trim();
    normalized.cta_url = normalized.ctaUrl;
    normalized.messengerType = String(source.messengerType == null ? (source.messenger_type == null ? normalized.messengerType : source.messenger_type) : source.messengerType).trim() || 'none';
    normalized.messenger_type = normalized.messengerType;
    normalized.linkLabel = String(source.linkLabel == null ? (source.link_label == null ? normalized.linkLabel : source.link_label) : source.linkLabel).trim() || normalized.ctaLabel;
    normalized.link_label = normalized.linkLabel;
    normalized.url = String(source.url == null ? normalized.url : source.url).trim();
    normalized.image = String(source.image == null ? normalized.image : source.image).trim();
    normalized.imageAlt = String(source.imageAlt == null ? (source.alt == null ? normalized.imageAlt : source.alt) : source.imageAlt).trim();
    normalized.alt = normalized.imageAlt;
    normalized.gallery = nbhCatalogNormalizeGallery(source.gallery == null ? normalized.gallery : source.gallery);

    itemId = String(itemId || '').trim();
    if (!itemId) {
        itemId = nbhCatalogCreateItemId(usedIds, seed);
    } else {
        itemId = nbhCatalogSlug(itemId) || nbhCatalogCreateItemId(usedIds, seed);
        if (usedIds[itemId]) {
            itemId = nbhCatalogCreateItemId(usedIds, seed);
        } else {
            usedIds[itemId] = true;
        }
    }

    normalized.id = itemId;
    normalized.itemId = itemId;
    normalized.item_id = itemId;

    return normalized;
}

function nbhPrimeCatalogBrowserDraft() {
    var items;
    var usedIds;
    var changed = false;

    if (!nbhIsCatalogBrowserBlock() || !nbhState.draft) {
        return false;
    }

    items = nbhRepeaterItems();
    usedIds = {};
    items = items.map(function(item) {
        var normalized = nbhNormalizeCatalogItem(item, { usedIds: usedIds });
        if (JSON.stringify(normalized) !== JSON.stringify(item)) {
            changed = true;
        }
        return normalized;
    });

    if (changed) {
        nbhSet(nbhState.draft, 'content.items', items);
    }

    if (typeof nbhGet(nbhState.draft, 'design.entities.media.aspectRatio', '') !== 'string' || !nbhGet(nbhState.draft, 'design.entities.media.aspectRatio', '')) {
        nbhSet(nbhState.draft, 'design.entities.media.aspectRatio', '16:10');
        changed = true;
    }

    if (typeof nbhGet(nbhState.draft, 'design.entities.media.objectFit', '') !== 'string' || !nbhGet(nbhState.draft, 'design.entities.media.objectFit', '')) {
        nbhSet(nbhState.draft, 'design.entities.media.objectFit', 'cover');
        changed = true;
    }

    return changed;
}

function nbhCatalogTransferItem(item) {
    var normalized = nbhNormalizeCatalogItem(item, { usedIds: {} });

    return {
        id: normalized.id,
        category: normalized.category,
        categoryUrl: normalized.categoryUrl,
        title: normalized.title,
        excerpt: normalized.excerpt,
        badge: normalized.badge,
        price: normalized.price,
        priceOld: normalized.priceOld,
        currency: normalized.currency,
        availability: normalized.availability,
        tags: normalized.tags,
        image: normalized.image,
        imageAlt: normalized.imageAlt,
        url: normalized.url,
        ctaLabel: normalized.ctaLabel,
        ctaKind: normalized.ctaKind,
        ctaUrl: normalized.ctaUrl,
        messengerType: normalized.messengerType,
        gallery: normalized.gallery
    };
}

function nbhCatalogTransferEnvelope(items, mode) {
    return {
        format: 'nordicblocks/catalog-browser-items@1',
        blockType: 'catalog_browser',
        mode: mode || 'export',
        mergePolicy: 'skip_existing_ids',
        generatedAt: new Date().toISOString(),
        fields: [
            { name: 'id', required: true, description: 'Стабильный ID позиции. Повторный импорт пропускает уже существующие id.' },
            { name: 'title', required: true, description: 'Название карточки.' },
            { name: 'excerpt', required: false, description: 'Короткое описание карточки.' },
            { name: 'price', required: false, description: 'Текущая цена.' },
            { name: 'priceOld', required: false, description: 'Старая цена.' },
            { name: 'image', required: false, description: 'URL изображения.' },
            { name: 'url', required: false, description: 'URL карточки.' },
            { name: 'ctaLabel', required: false, description: 'Текст CTA кнопки.' },
            { name: 'ctaUrl', required: false, description: 'URL CTA кнопки.' },
            { name: 'gallery', required: false, description: 'Массив слайдов modal gallery.' }
        ],
        items: items.map(nbhCatalogTransferItem)
    };
}

function nbhDownloadJson(filename, payload) {
    var blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(function() {
        URL.revokeObjectURL(url);
    }, 0);
}

function nbhBuildCatalogDemoPayload() {
    return nbhCatalogTransferEnvelope([
        {
            id: 'sku-oak-desk-140',
            category: 'Письменные столы',
            categoryUrl: '/catalog/desks',
            title: 'Стол Oak 140',
            excerpt: 'Компактный стол из массива дуба для домашнего кабинета и студии.',
            badge: 'Хит',
            price: '59 900',
            priceOld: '69 900',
            currency: '₽',
            availability: 'available',
            tags: ['Дуб', 'Склад'],
            image: '/upload/nordicblocks/demo/oak-desk-140.jpg',
            imageAlt: 'Стол Oak 140',
            url: '/catalog/oak-desk-140',
            ctaLabel: 'Открыть',
            ctaKind: 'url',
            ctaUrl: '/catalog/oak-desk-140',
            messengerType: 'none',
            gallery: [
                { src: '/upload/nordicblocks/demo/oak-desk-140.jpg', alt: 'Стол Oak 140', caption: 'Главный ракурс' },
                { src: '/upload/nordicblocks/demo/oak-desk-140-side.jpg', alt: 'Стол Oak 140 сбоку', caption: 'Боковой ракурс' }
            ]
        },
        {
            id: 'sku-linen-chair-sand',
            category: 'Стулья',
            categoryUrl: '/catalog/chairs',
            title: 'Linen Chair Sand',
            excerpt: 'Мягкий стул для кухни или переговорной зоны с нейтральной текстурой ткани.',
            badge: 'Новинка',
            price: '18 500',
            priceOld: '',
            currency: '₽',
            availability: 'preorder',
            tags: ['Ткань', 'Под заказ'],
            image: '/upload/nordicblocks/demo/linen-chair-sand.jpg',
            imageAlt: 'Linen Chair Sand',
            url: '/catalog/linen-chair-sand',
            ctaLabel: 'Заказать',
            ctaKind: 'url',
            ctaUrl: '/catalog/linen-chair-sand',
            messengerType: 'none',
            gallery: []
        }
    ], 'demo');
}

function nbhExportCatalogItems() {
    var changed = nbhPrimeCatalogBrowserDraft();
    var filename;

    if (!nbhIsCatalogBrowserBlock()) {
        return;
    }

    if (changed) {
        nbhRenderPanels();
        nbhMarkDirty();
        nbhScheduleSave();
    }

    filename = 'catalog-browser-' + String(nbhState.blockTitle || 'items').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') + '.json';
    filename = filename.replace(/-+/g, '-');

    nbhDownloadJson(filename || 'catalog-browser-items.json', nbhCatalogTransferEnvelope(nbhRepeaterItems(), 'export'));
}

function nbhDownloadCatalogDemo() {
    if (!nbhIsCatalogBrowserBlock()) {
        return;
    }

    nbhDownloadJson('catalog-browser-demo.json', nbhBuildCatalogDemoPayload());
}

function nbhParseCatalogImportItems(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (payload && typeof payload === 'object') {
        if (Array.isArray(payload.items)) {
            return payload.items;
        }

        if (payload.content && Array.isArray(payload.content.items)) {
            return payload.content.items;
        }
    }

    return [];
}

function nbhImportCatalogPayload(payload) {
    var importedItems = nbhParseCatalogImportItems(payload);
    var existingItems = nbhRepeaterItems().slice();
    var usedIds = nbhCatalogUsedIdMap();
    var seenImportedIds = {};
    var stats = { imported: 0, skippedExisting: 0, skippedInvalid: 0, generatedIds: 0 };

    if (!Array.isArray(importedItems) || !importedItems.length) {
        throw new Error('В JSON не найден массив items для импорта.');
    }

    importedItems.forEach(function(rawItem) {
        var incomingId = nbhCatalogItemIdValue(rawItem);
        var previewId = incomingId ? (nbhCatalogSlug(incomingId) || incomingId) : '';
        var normalized;

        if (!rawItem || typeof rawItem !== 'object' || Array.isArray(rawItem)) {
            stats.skippedInvalid += 1;
            return;
        }

        if (previewId && (usedIds[previewId] || seenImportedIds[previewId])) {
            stats.skippedExisting += 1;
            return;
        }

        normalized = nbhNormalizeCatalogItem(rawItem, { usedIds: usedIds, emptyBase: true });
        if (!incomingId) {
            stats.generatedIds += 1;
        }

        if (!nbhCatalogItemHasContent(normalized)) {
            stats.skippedInvalid += 1;
            return;
        }

        seenImportedIds[normalized.id] = true;
        existingItems.push(normalized);
        stats.imported += 1;
    });

    if (!stats.imported) {
        throw new Error('Нечего импортировать: все позиции уже существуют по id или не прошли валидацию.');
    }

    nbhSet(nbhState.draft, 'content.items', existingItems);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();

    alert('Импорт завершён. Добавлено: ' + stats.imported + '. Пропущено по существующим id: ' + stats.skippedExisting + '. Невалидных записей: ' + stats.skippedInvalid + '. Сгенерировано новых id: ' + stats.generatedIds + '.');
}

function nbhImportCatalogFile(file) {
    var reader;

    if (!file) {
        return;
    }

    reader = new FileReader();
    reader.onload = function(event) {
        var text = String(event && event.target ? event.target.result : '');
        var payload;

        try {
            payload = JSON.parse(text);
        } catch (error) {
            alert('Файл не похож на корректный JSON.');
            return;
        }

        try {
            nbhImportCatalogPayload(payload);
        } catch (error) {
            alert(error && error.message ? error.message : 'Не удалось импортировать JSON.');
        }
    };
    reader.onerror = function() {
        alert('Не удалось прочитать файл.');
    };
    reader.readAsText(file, 'utf-8');
}

var nbhCatalogXlsxLibraryPromise = null;

function nbhCatalogXlsxAssetUrl() {
    return '/static/nordicblocks/vendor/xlsx.full.min.js?v=20260419-1';
}

function nbhLoadCatalogXlsxLibrary() {
    if (window.XLSX && typeof window.XLSX.read === 'function') {
        return Promise.resolve(window.XLSX);
    }

    if (nbhCatalogXlsxLibraryPromise) {
        return nbhCatalogXlsxLibraryPromise;
    }

    nbhCatalogXlsxLibraryPromise = new Promise(function(resolve, reject) {
        var script = document.querySelector('script[data-nbh-xlsx-loader="1"]');

        if (script) {
            script.addEventListener('load', function() {
                if (window.XLSX && typeof window.XLSX.read === 'function') {
                    resolve(window.XLSX);
                }
            }, { once: true });
            script.addEventListener('error', function() {
                reject(new Error('Не удалось загрузить библиотеку XLSX.'));
            }, { once: true });
            return;
        }

        script = document.createElement('script');
        script.src = nbhCatalogXlsxAssetUrl();
        script.async = true;
        script.dataset.nbhXlsxLoader = '1';
        script.onload = function() {
            if (window.XLSX && typeof window.XLSX.read === 'function') {
                resolve(window.XLSX);
                return;
            }
            reject(new Error('Библиотека XLSX загрузилась, но не инициализировалась.'));
        };
        script.onerror = function() {
            reject(new Error('Не удалось загрузить библиотеку XLSX.'));
        };
        document.head.appendChild(script);
    }).catch(function(error) {
        nbhCatalogXlsxLibraryPromise = null;
        throw error;
    });

    return nbhCatalogXlsxLibraryPromise;
}

var nbhCatalogTableState = {
    rawText: '',
    parsed: null,
    gridRows: [],
    activeCell: { row: 0, col: 0 }
};

function nbhCatalogTableColumns() {
    return [
        { key: 'id', label: 'ID' },
        { key: 'category', label: 'Категория' },
        { key: 'title', label: 'Заголовок' },
        { key: 'excerpt', label: 'Описание' },
        { key: 'price', label: 'Цена' },
        { key: 'priceOld', label: 'Старая цена' },
        { key: 'currency', label: 'Валюта' },
        { key: 'badge', label: 'Бейдж' },
        { key: 'availability', label: 'Наличие' },
        { key: 'tags', label: 'Теги' },
        { key: 'image', label: 'Изображение' },
        { key: 'imageAlt', label: 'Alt' },
        { key: 'url', label: 'URL карточки' },
        { key: 'ctaLabel', label: 'CTA текст' },
        { key: 'ctaKind', label: 'CTA тип' },
        { key: 'ctaUrl', label: 'CTA ссылка' },
        { key: 'messengerType', label: 'Мессенджер' }
    ];
}

function nbhCatalogTableHeaderAliases() {
    return {
        id: 'id',
        itemid: 'id',
        item_id: 'id',
        importid: 'id',
        импортid: 'id',
        категория: 'category',
        category: 'category',
        title: 'title',
        заголовок: 'title',
        название: 'title',
        excerpt: 'excerpt',
        text: 'excerpt',
        описание: 'excerpt',
        анонс: 'excerpt',
        price: 'price',
        цена: 'price',
        priceold: 'priceOld',
        стараяцена: 'priceOld',
        currency: 'currency',
        валюта: 'currency',
        badge: 'badge',
        бейдж: 'badge',
        availability: 'availability',
        наличие: 'availability',
        tags: 'tags',
        теги: 'tags',
        image: 'image',
        изображение: 'image',
        imagealt: 'imageAlt',
        alt: 'imageAlt',
        urlкарточки: 'url',
        url: 'url',
        ctalabel: 'ctaLabel',
        ctaтекст: 'ctaLabel',
        ctakind: 'ctaKind',
        ctaтип: 'ctaKind',
        ctaurl: 'ctaUrl',
        ctaссылка: 'ctaUrl',
        messengertype: 'messengerType',
        мессенджер: 'messengerType'
    };
}

function nbhCatalogTableNormalizeHeader(value) {
    return String(value == null ? '' : value)
        .toLowerCase()
        .replace(/ё/g, 'е')
        .replace(/[^a-z0-9а-я]+/g, '');
}

function nbhCatalogTableCellValue(value) {
    if (Array.isArray(value)) {
        return value.join(', ');
    }

    return String(value == null ? '' : value)
        .replace(/\r?\n+/g, ' ')
        .replace(/\t+/g, ' ')
        .trim();
}

function nbhCatalogTableTextFromItems(items) {
    var columns = nbhCatalogTableColumns();
    var header = columns.map(function(column) { return column.label; }).join('\t');
    var lines = [header];

    (items || []).forEach(function(item) {
        var transferred = nbhCatalogTransferItem(item);
        lines.push(columns.map(function(column) {
            return nbhCatalogTableCellValue(transferred[column.key]);
        }).join('\t'));
    });

    return lines.join('\n');
}

function nbhCatalogTableEmptyRow() {
    var row = {};

    nbhCatalogTableColumns().forEach(function(column) {
        row[column.key] = '';
    });

    return row;
}

function nbhCatalogTableRowHasContent(row) {
    return nbhCatalogTableColumns().some(function(column) {
        return String(row && row[column.key] != null ? row[column.key] : '').trim() !== '';
    });
}

function nbhCatalogTableEnsureRows(rows) {
    var normalized = (Array.isArray(rows) ? rows : []).map(function(row) {
        var item = nbhCatalogTableEmptyRow();

        nbhCatalogTableColumns().forEach(function(column) {
            item[column.key] = nbhCatalogTableCellValue(row && row[column.key] != null ? row[column.key] : '');
        });

        return item;
    });

    if (!normalized.length) {
        normalized.push(nbhCatalogTableEmptyRow());
    }

    return normalized;
}

function nbhCatalogTableGridRowsFromParsed(parsed) {
    var rows = (parsed && Array.isArray(parsed.rows) ? parsed.rows : []).map(function(rowInfo) {
        var source = rowInfo && rowInfo.item && typeof rowInfo.item === 'object' ? rowInfo.item : {};
        var row = nbhCatalogTableEmptyRow();

        nbhCatalogTableColumns().forEach(function(column) {
            row[column.key] = nbhCatalogTableCellValue(source[column.key]);
        });

        return row;
    });

    return nbhCatalogTableEnsureRows(rows);
}

function nbhCatalogTableTextFromGridRows(rows) {
    var columns = nbhCatalogTableColumns();
    var header = columns.map(function(column) { return column.label; }).join('\t');
    var lines = [header];

    nbhCatalogTableEnsureRows(rows).forEach(function(row) {
        if (!nbhCatalogTableRowHasContent(row)) {
            return;
        }

        lines.push(columns.map(function(column) {
            return nbhCatalogTableCellValue(row[column.key]);
        }).join('\t'));
    });

    return lines.join('\n');
}

function nbhCatalogTableMatrixFromText(text) {
    return nbhCatalogTableSplitRows(text).filter(function(line, index, lines) {
        return String(line).trim() !== '' || index < lines.length - 1;
    }).map(function(line) {
        return String(line == null ? '' : line).split('\t');
    });
}

function nbhCatalogTableNormalizeMatrix(matrix) {
    return (Array.isArray(matrix) ? matrix : []).map(function(row) {
        var normalized = Array.isArray(row) ? row.slice() : [row];

        while (normalized.length && String(normalized[normalized.length - 1] == null ? '' : normalized[normalized.length - 1]).trim() === '') {
            normalized.pop();
        }

        return normalized.map(function(value) {
            return nbhCatalogTableCellValue(value);
        });
    }).filter(function(row) {
        return row.some(function(value) {
            return String(value).trim() !== '';
        });
    });
}

function nbhCatalogTableTextFromMatrix(matrix) {
    return nbhCatalogTableNormalizeMatrix(matrix).map(function(row) {
        return row.join('\t');
    }).join('\n');
}

function nbhCatalogTableColumnWidth(key) {
    if (key === 'excerpt' || key === 'image' || key === 'url' || key === 'ctaUrl') {
        return '240px';
    }

    if (key === 'title' || key === 'tags' || key === 'imageAlt') {
        return '180px';
    }

    if (key === 'category' || key === 'price' || key === 'priceOld' || key === 'currency' || key === 'badge' || key === 'availability' || key === 'ctaLabel' || key === 'ctaKind' || key === 'messengerType' || key === 'id') {
        return '130px';
    }

    return '150px';
}

function nbhCatalogTableSplitRows(text) {
    return String(text == null ? '' : text)
        .replace(/\r\n?/g, '\n')
        .split('\n');
}

function nbhCatalogTableHasHeader(cells) {
    var aliases = nbhCatalogTableHeaderAliases();
    var recognized = cells.filter(function(cell) {
        return !!aliases[nbhCatalogTableNormalizeHeader(cell)];
    }).length;

    return recognized >= 2;
}

function nbhCatalogTableMapHeaders(cells) {
    var aliases = nbhCatalogTableHeaderAliases();
    return cells.map(function(cell) {
        return aliases[nbhCatalogTableNormalizeHeader(cell)] || '';
    });
}

function nbhCatalogTableDefaultHeaders() {
    return nbhCatalogTableColumns().map(function(column) { return column.key; });
}

function nbhCatalogTableParseText(text) {
    var rows = nbhCatalogTableSplitRows(text);
    var headerKeys = nbhCatalogTableDefaultHeaders();
    var hasHeader = false;
    var usedIds = nbhCatalogUsedIdMap();
    var parsedRows = [];
    var rawItems = [];
    var stats = { total: 0, ready: 0, skipped: 0, invalid: 0, generatedIds: 0 };
    var startIndex = 0;

    while (rows.length && !String(rows[rows.length - 1]).trim()) {
        rows.pop();
    }

    if (!rows.length) {
        return {
            hasHeader: false,
            rawItems: [],
            rows: [],
            stats: stats
        };
    }

    if (nbhCatalogTableHasHeader(rows[0].split('\t'))) {
        hasHeader = true;
        headerKeys = nbhCatalogTableMapHeaders(rows[0].split('\t'));
        startIndex = 1;
    }

    rows.slice(startIndex).forEach(function(line, offset) {
        var cells = String(line == null ? '' : line).split('\t');
        var rawItem = {};
        var incomingId;
        var previewId;
        var normalized;
        var action = 'import';
        var reason = '';

        if (!cells.some(function(cell) { return String(cell).trim() !== ''; })) {
            return;
        }

        headerKeys.forEach(function(key, index) {
            var cellValue = String(cells[index] == null ? '' : cells[index]).trim();
            if (!key || !cellValue) {
                return;
            }
            rawItem[key] = cellValue;
        });

        stats.total += 1;
        incomingId = nbhCatalogItemIdValue(rawItem);
        previewId = incomingId ? (nbhCatalogSlug(incomingId) || incomingId) : '';

        if (previewId && usedIds[previewId]) {
            action = 'skip';
            reason = 'Такой ID уже есть в каталоге.';
            stats.skipped += 1;
        } else {
            normalized = nbhNormalizeCatalogItem(rawItem, { usedIds: usedIds, emptyBase: true });

            if (!incomingId) {
                stats.generatedIds += 1;
            }

            if (!nbhCatalogItemHasContent(normalized)) {
                action = 'invalid';
                reason = 'В строке нет названия, описания или другой полезной информации.';
                stats.invalid += 1;
            } else {
                action = 'import';
                rawItem = normalized;
                stats.ready += 1;
                rawItems.push(rawItem);
            }
        }

        parsedRows.push({
            line: startIndex + offset + 1,
            action: action,
            reason: reason,
            item: rawItem,
            title: rawItem.title || rawItem.excerpt || '',
            id: nbhCatalogItemIdValue(rawItem)
        });
    });

    return {
        hasHeader: hasHeader,
        rawItems: rawItems,
        rows: parsedRows,
        stats: stats
    };
}

function nbhCatalogTableStatusClass(action) {
    if (action === 'import') return 'is-import';
    if (action === 'skip') return 'is-skip';
    return 'is-invalid';
}

function nbhCatalogTableStatusLabel(action) {
    if (action === 'import') return 'Импорт';
    if (action === 'skip') return 'Пропуск';
    return 'Ошибка';
}

function nbhCatalogTableMetricsHtml(parsed) {
    var stats = parsed.stats;
    return '<div class="nbh-catalog-table-preview__metrics">'
        + '<div class="nbh-catalog-table-preview__metric"><strong>' + stats.total + '</strong><span>Строк в таблице</span></div>'
        + '<div class="nbh-catalog-table-preview__metric is-good"><strong>' + stats.ready + '</strong><span>Будет импортировано</span></div>'
        + '<div class="nbh-catalog-table-preview__metric is-warn"><strong>' + stats.skipped + '</strong><span>Пропуск по ID</span></div>'
        + '<div class="nbh-catalog-table-preview__metric is-bad"><strong>' + stats.invalid + '</strong><span>Строк с ошибками</span></div>'
        + '<div class="nbh-catalog-table-preview__metric"><strong>' + stats.generatedIds + '</strong><span>ID сгенерируется</span></div>'
        + '</div>';
}

function nbhCatalogTablePreviewHtml(parsed) {
    if (!parsed.rows.length) {
        return '<div class="nbh-note">Вставьте строки из Excel или Google Sheets. Первая строка может быть заголовком колонок на русском языке.</div>';
    }

    return nbhCatalogTableMetricsHtml(parsed)
        + '<table class="nbh-catalog-table-preview__table">'
        + '<thead><tr><th>Строка</th><th>Статус</th><th>ID</th><th>Заголовок</th><th>Комментарий</th></tr></thead>'
        + '<tbody>' + parsed.rows.slice(0, 18).map(function(row) {
            return '<tr>'
                + '<td>' + row.line + '</td>'
                + '<td><span class="nbh-catalog-table-preview__status ' + nbhCatalogTableStatusClass(row.action) + '">' + nbhCatalogTableStatusLabel(row.action) + '</span></td>'
                + '<td>' + nbhEscapeHtml(row.id || '') + '</td>'
                + '<td>' + nbhEscapeHtml(row.title || '') + '</td>'
                + '<td>' + nbhEscapeHtml(row.reason || (row.action === 'import' ? 'Строка готова к импорту.' : '')) + '</td>'
                + '</tr>';
        }).join('') + '</tbody></table>'
        + (parsed.rows.length > 18 ? '<div class="nbh-note" style="margin-top:.75rem;">Показаны первые 18 строк из ' + parsed.rows.length + '.</div>' : '');
}

function nbhEnsureCatalogTableModal() {
    var modal = document.getElementById('nbhCatalogTableModal');

    if (modal) {
        return modal;
    }

    modal = document.createElement('div');
    modal.className = 'nbh-catalog-table-modal';
    modal.id = 'nbhCatalogTableModal';
    modal.innerHTML = ''
        + '<div class="nbh-catalog-table-modal__dialog">'
        + '<div class="nbh-catalog-table-modal__head">'
        + '<div><strong>Табличный режим каталога</strong><span>Одна строка таблицы = одна карточка. Работает со вставкой из Excel и Google Sheets.</span></div>'
        + '<button type="button" class="nbh-picker-btn" data-catalog-table-close="1">Закрыть</button>'
        + '</div>'
        + '<div class="nbh-catalog-table-modal__help">Скопируйте диапазон строк из Excel или Google Sheets и вставьте его в левое поле. Можно начинать с заголовка колонок: ID, Категория, Заголовок, Описание, Цена и так далее. Существующие ID будут пропущены автоматически.</div>'
        + '<div class="nbh-catalog-table-modal__toolbar">'
        + '<div class="nbh-catalog-table-modal__actions">'
        + '<button type="button" class="nbh-picker-btn" data-catalog-table-mode="current">Собрать из текущих карточек</button>'
        + '<button type="button" class="nbh-picker-btn" data-catalog-table-upload="1">Загрузить XLSX</button>'
        + '<button type="button" class="nbh-picker-btn" data-catalog-table-mode="demo">Заполнить примером</button>'
        + '<button type="button" class="nbh-picker-btn" data-catalog-table-copy="1">Скопировать таблицу</button>'
        + '<button type="button" class="nbh-picker-btn nbh-picker-btn--clear" data-catalog-table-clear="1">Очистить</button>'
        + '</div>'
        + '</div>'
        + '<div class="nbh-catalog-table-modal__body">'
        + '<div class="nbh-catalog-table-modal__editor">'
        + '<div class="nbh-catalog-table-modal__section-title">Таблица</div>'
        + '<div class="nbh-catalog-table-modal__editor-help">Редактируйте карточки прямо в сетке, как в XLSX. Можно кликнуть в первую ячейку и вставить диапазон из Excel или Google Sheets.</div>'
        + '<div class="nbh-catalog-table-modal__editor-actions">'
        + '<button type="button" class="nbh-picker-btn" data-catalog-table-add-row="1">Добавить строку</button>'
        + '</div>'
        + '<div class="nbh-catalog-table-grid-wrap"><div class="nbh-catalog-table-grid" id="nbhCatalogTableGrid"></div></div>'
        + '<textarea id="nbhCatalogTableTextarea" class="nbh-catalog-table-modal__textarea-shadow" placeholder="ID	Категория	Заголовок	Описание	Цена\nchair-01	Стулья	Linen Chair	Мягкий стул...	18500"></textarea>'
        + '</div>'
        + '<div class="nbh-catalog-table-modal__preview">'
        + '<div class="nbh-catalog-table-modal__section-title">Предпросмотр импорта</div>'
        + '<div class="nbh-catalog-table-preview" id="nbhCatalogTablePreview"></div>'
        + '</div>'
        + '</div>'
        + '<div class="nbh-catalog-table-modal__foot">'
        + '<div class="nbh-catalog-table-modal__status" id="nbhCatalogTableStatus">Соберите текущие карточки или вставьте диапазон из таблицы, чтобы увидеть preview.</div>'
        + '<div class="nbh-catalog-table-modal__actions">'
        + '<button type="button" class="nbh-btn nbh-btn--catalog" id="nbhCatalogTableImportNowBtn"><i class="fa fa-upload"></i> Импортировать строки</button>'
        + '<button type="button" class="nbh-btn nbh-btn--ghost" data-catalog-table-close="1">Закрыть</button>'
        + '</div>'
        + '</div>'
        + '</div>';

    document.body.appendChild(modal);

    modal.addEventListener('click', function(event) {
        if (event.target === modal || event.target.closest('[data-catalog-table-close]')) {
            nbhCloseCatalogTableModal();
            return;
        }

        if (event.target.closest('[data-catalog-table-mode="current"]')) {
            nbhCatalogTableLoadCurrent();
            return;
        }

        if (event.target.closest('[data-catalog-table-mode="demo"]')) {
            nbhCatalogTableLoadDemo();
            return;
        }

        if (event.target.closest('[data-catalog-table-copy]')) {
            nbhCatalogTableCopyText();
            return;
        }

        if (event.target.closest('[data-catalog-table-upload]')) {
            nbhTriggerCatalogSpreadsheetInput();
            return;
        }

        if (event.target.closest('[data-catalog-table-clear]')) {
            nbhCatalogTableSetText('');
            return;
        }

        if (event.target.closest('[data-catalog-table-add-row]')) {
            nbhCatalogTableState.gridRows.push(nbhCatalogTableEmptyRow());
            nbhCatalogTableRenderGrid();
            nbhCatalogTableSyncFromGrid();
            nbhCatalogTableFocusCell(nbhCatalogTableState.gridRows.length - 1, 0);
            return;
        }

        if (event.target.closest('[data-catalog-table-remove-row]')) {
            var removeIndex = parseInt(event.target.closest('[data-catalog-table-remove-row]').dataset.catalogTableRemoveRow || '0', 10);

            nbhCatalogTableState.gridRows.splice(removeIndex, 1);
            nbhCatalogTableState.gridRows = nbhCatalogTableEnsureRows(nbhCatalogTableState.gridRows);
            nbhCatalogTableRenderGrid();
            nbhCatalogTableSyncFromGrid();
            nbhCatalogTableFocusCell(Math.max(0, removeIndex - 1), 0);
            return;
        }
    });

    document.getElementById('nbhCatalogTableGrid').addEventListener('input', function(event) {
        var cell = event.target.closest('[data-catalog-table-cell]');
        var rowIndex;
        var key;

        if (!cell) {
            return;
        }

        rowIndex = parseInt(cell.dataset.row || '0', 10);
        key = cell.dataset.key || '';

        if (!nbhCatalogTableState.gridRows[rowIndex] || !key) {
            return;
        }

        nbhCatalogTableState.gridRows[rowIndex][key] = cell.value;
        nbhCatalogTableState.activeCell = {
            row: rowIndex,
            col: parseInt(cell.dataset.col || '0', 10)
        };
        nbhCatalogTableSyncFromGrid();
    });

    document.getElementById('nbhCatalogTableGrid').addEventListener('focusin', function(event) {
        var cell = event.target.closest('[data-catalog-table-cell]');

        if (!cell) {
            return;
        }

        nbhCatalogTableState.activeCell = {
            row: parseInt(cell.dataset.row || '0', 10),
            col: parseInt(cell.dataset.col || '0', 10)
        };
    });

    document.getElementById('nbhCatalogTableGrid').addEventListener('keydown', function(event) {
        var cell = event.target.closest('[data-catalog-table-cell]');
        var rowIndex;
        var colIndex;

        if (!cell || event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        rowIndex = parseInt(cell.dataset.row || '0', 10);
        colIndex = parseInt(cell.dataset.col || '0', 10);

        if (rowIndex >= nbhCatalogTableState.gridRows.length - 1) {
            nbhCatalogTableState.gridRows.push(nbhCatalogTableEmptyRow());
            nbhCatalogTableRenderGrid();
            nbhCatalogTableSyncFromGrid();
        }

        nbhCatalogTableFocusCell(rowIndex + 1, colIndex);
    });

    document.getElementById('nbhCatalogTableGrid').addEventListener('paste', function(event) {
        var cell = event.target.closest('[data-catalog-table-cell]');
        var text = event.clipboardData ? event.clipboardData.getData('text/plain') : '';

        if (!cell || !text || (text.indexOf('\t') === -1 && text.indexOf('\n') === -1)) {
            return;
        }

        event.preventDefault();
        nbhCatalogTableApplyMatrix(
            text,
            parseInt(cell.dataset.row || '0', 10),
            parseInt(cell.dataset.col || '0', 10)
        );
    });

    document.getElementById('nbhCatalogTableImportNowBtn').addEventListener('click', nbhCatalogTableImportCurrent);

    return modal;
}

function nbhCatalogTableRenderGrid() {
    var host = document.getElementById('nbhCatalogTableGrid');
    var columns = nbhCatalogTableColumns();
    var rows;

    if (!host) {
        return;
    }

    nbhCatalogTableState.gridRows = nbhCatalogTableEnsureRows(nbhCatalogTableState.gridRows);
    rows = nbhCatalogTableState.gridRows;

    host.innerHTML = '<table><thead><tr><th class="nbh-catalog-table-grid__rownum">#</th>'
        + columns.map(function(column) {
            return '<th>' + nbhEscapeHtml(column.label) + '</th>';
        }).join('')
        + '<th class="nbh-catalog-table-grid__remove"></th></tr></thead><tbody>'
        + rows.map(function(row, rowIndex) {
            return '<tr><td class="nbh-catalog-table-grid__rownum">' + (rowIndex + 1) + '</td>'
                + columns.map(function(column, colIndex) {
                    return '<td><input type="text" class="nbh-catalog-table-grid__input" data-catalog-table-cell="1" data-row="' + rowIndex + '" data-col="' + colIndex + '" data-key="' + nbhEscapeHtml(column.key) + '" value="' + nbhEscapeHtml(row[column.key] || '') + '" style="min-width:' + nbhCatalogTableColumnWidth(column.key) + ';"></td>';
                }).join('')
                + '<td class="nbh-catalog-table-grid__remove">'
                + (rows.length > 1 ? '<button type="button" class="nbh-catalog-table-grid__remove-btn" data-catalog-table-remove-row="' + rowIndex + '" title="Удалить строку">×</button>' : '')
                + '</td></tr>';
        }).join('')
        + '</tbody></table>';
}

function nbhCatalogTableFocusCell(rowIndex, colIndex) {
    var cell = document.querySelector('#nbhCatalogTableGrid [data-row="' + rowIndex + '"][data-col="' + colIndex + '"]');

    if (!cell) {
        return;
    }

    cell.focus();
    if (typeof cell.select === 'function') {
        cell.select();
    }
}

function nbhCatalogTableSyncFromGrid() {
    var textarea = document.getElementById('nbhCatalogTableTextarea');
    var preview = document.getElementById('nbhCatalogTablePreview');

    nbhCatalogTableState.gridRows = nbhCatalogTableEnsureRows(nbhCatalogTableState.gridRows);
    nbhCatalogTableState.rawText = nbhCatalogTableTextFromGridRows(nbhCatalogTableState.gridRows);
    nbhCatalogTableState.parsed = nbhCatalogTableParseText(nbhCatalogTableState.rawText);

    if (textarea) {
        textarea.value = nbhCatalogTableState.rawText;
    }

    if (preview) {
        preview.innerHTML = nbhCatalogTablePreviewHtml(nbhCatalogTableState.parsed);
    }

    if (!nbhCatalogTableState.parsed.rows.length) {
        nbhCatalogTableSetStatus('Вставьте строки из Excel или Google Sheets, чтобы увидеть предпросмотр.', '');
    } else if (nbhCatalogTableState.parsed.stats.ready) {
        nbhCatalogTableSetStatus('Готово к импорту: ' + nbhCatalogTableState.parsed.stats.ready + ' строк.', 'success');
    } else {
        nbhCatalogTableSetStatus('Импортировать пока нечего: проверьте ID и заполнение строк.', 'error');
    }
}

function nbhCatalogTableApplyMatrix(text, startRow, startCol) {
    var matrix = nbhCatalogTableMatrixFromText(text);
    var columns = nbhCatalogTableColumns();

    if (!matrix.length) {
        return;
    }

    if (startRow === 0 && startCol === 0 && nbhCatalogTableHasHeader(matrix[0])) {
        nbhCatalogTableSetText(text);
        nbhCatalogTableFocusCell(0, 0);
        return;
    }

    if (startRow === 0 && startCol === 0) {
        nbhCatalogTableState.gridRows = [];
    }

    nbhCatalogTableState.gridRows = nbhCatalogTableEnsureRows(nbhCatalogTableState.gridRows);

    matrix.forEach(function(cells, rowOffset) {
        var targetRow = startRow + rowOffset;

        while (!nbhCatalogTableState.gridRows[targetRow]) {
            nbhCatalogTableState.gridRows.push(nbhCatalogTableEmptyRow());
        }

        cells.forEach(function(value, colOffset) {
            var column = columns[startCol + colOffset];
            if (!column) {
                return;
            }
            nbhCatalogTableState.gridRows[targetRow][column.key] = String(value == null ? '' : value).trim();
        });
    });

    nbhCatalogTableRenderGrid();
    nbhCatalogTableSyncFromGrid();
    nbhCatalogTableFocusCell(startRow, startCol);
}

function nbhCatalogTableSetStatus(text, kind) {
    var status = document.getElementById('nbhCatalogTableStatus');

    if (!status) {
        return;
    }

    status.className = 'nbh-catalog-table-modal__status' + (kind ? ' is-' + kind : '');
    status.textContent = text;
}

function nbhCatalogTableSetText(text, keepExistingTextareaValue) {
    var textarea = document.getElementById('nbhCatalogTableTextarea');
    var preview = document.getElementById('nbhCatalogTablePreview');

    nbhCatalogTableState.rawText = String(text == null ? '' : text);
    nbhCatalogTableState.parsed = nbhCatalogTableParseText(nbhCatalogTableState.rawText);
    nbhCatalogTableState.gridRows = nbhCatalogTableGridRowsFromParsed(nbhCatalogTableState.parsed);

    if (textarea && !keepExistingTextareaValue) {
        textarea.value = nbhCatalogTableState.rawText;
    }

    nbhCatalogTableRenderGrid();

    if (preview) {
        preview.innerHTML = nbhCatalogTablePreviewHtml(nbhCatalogTableState.parsed);
    }

    if (!nbhCatalogTableState.parsed.rows.length) {
        nbhCatalogTableSetStatus('Вставьте строки из Excel или Google Sheets, чтобы увидеть предпросмотр.', '');
    } else if (nbhCatalogTableState.parsed.stats.ready) {
        nbhCatalogTableSetStatus('Готово к импорту: ' + nbhCatalogTableState.parsed.stats.ready + ' строк.', 'success');
    } else {
        nbhCatalogTableSetStatus('Импортировать пока нечего: проверьте ID и заполнение строк.', 'error');
    }
}

function nbhCatalogTableLoadCurrent() {
    nbhCatalogTableSetText(nbhCatalogTableTextFromItems(nbhRepeaterItems()));
    nbhCatalogTableSetStatus('Текущие карточки собраны в таблицу. Скопируйте её в Excel или замените строки перед импортом.', '');
}

function nbhCatalogTableLoadDemo() {
    nbhCatalogTableSetText(nbhCatalogTableTextFromItems(nbhBuildCatalogDemoPayload().items));
    nbhCatalogTableSetStatus('Загружен пример таблицы. Его можно править и сразу импортировать в каталог.', 'success');
}

function nbhCatalogTableCopyText() {
    var textarea = document.getElementById('nbhCatalogTableTextarea');

    if (!textarea || !textarea.value) {
        nbhCatalogTableSetStatus('Нечего копировать: таблица пока пустая.', 'error');
        return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textarea.value).then(function() {
            nbhCatalogTableSetStatus('Таблица скопирована в буфер обмена.', 'success');
        }).catch(function() {
            textarea.focus();
            textarea.select();
            nbhCatalogTableSetStatus('Не удалось скопировать автоматически. Выделение уже сделано, нажмите Ctrl+C.', 'error');
        });
        return;
    }

    textarea.focus();
    textarea.select();
    nbhCatalogTableSetStatus('Автокопирование недоступно. Нажмите Ctrl+C.', 'error');
}

function nbhTriggerCatalogSpreadsheetInput() {
    var input = document.getElementById('nbhCatalogXlsxImportInput');

    if (input) {
        input.click();
    }
}

function nbhImportCatalogSpreadsheetFile(file) {
    var modal;

    if (!file) {
        return;
    }

    modal = nbhEnsureCatalogTableModal();
    modal.classList.add('is-open');
    nbhCatalogTableSetStatus('Загружаю файл ' + file.name + '...', '');

    nbhLoadCatalogXlsxLibrary().then(function(XLSX) {
        return new Promise(function(resolve, reject) {
            var reader = new FileReader();

            reader.onerror = function() {
                reject(new Error('Не удалось прочитать файл Excel.'));
            };

            reader.onload = function() {
                var workbook;
                var sheetName;
                var sheet;
                var matrix;
                var text;

                try {
                    workbook = XLSX.read(reader.result, { type: 'array' });
                    sheetName = workbook && workbook.SheetNames && workbook.SheetNames[0] ? workbook.SheetNames[0] : '';

                    if (!sheetName) {
                        reject(new Error('В Excel-файле не найдено ни одного листа.'));
                        return;
                    }

                    sheet = workbook.Sheets[sheetName];
                    matrix = XLSX.utils.sheet_to_json(sheet, {
                        header: 1,
                        raw: false,
                        defval: '',
                        blankrows: false
                    });
                    text = nbhCatalogTableTextFromMatrix(matrix);

                    if (!text.trim()) {
                        reject(new Error('Первый лист Excel-файла пустой.'));
                        return;
                    }

                    resolve({
                        sheetName: sheetName,
                        text: text
                    });
                } catch (error) {
                    reject(error instanceof Error ? error : new Error('Не удалось разобрать Excel-файл.'));
                }
            };

            reader.readAsArrayBuffer(file);
        });
    }).then(function(result) {
        nbhCatalogTableSetText(result.text);
        nbhCatalogTableSetStatus('Файл ' + file.name + ' загружен. Использован лист: ' + result.sheetName + '.', 'success');
    }).catch(function(error) {
        var message = error && error.message ? error.message : 'Не удалось импортировать Excel-файл.';
        nbhCatalogTableSetStatus(message, 'error');
        if (window.alert) {
            window.alert(message);
        }
    });
}

function nbhOpenCatalogTableModal(mode) {
    var modal;

    if (!nbhIsCatalogBrowserBlock()) {
        return;
    }

    modal = nbhEnsureCatalogTableModal();
    modal.classList.add('is-open');

    if (mode === 'demo') {
        nbhCatalogTableLoadDemo();
        return;
    }

    nbhCatalogTableLoadCurrent();
}

function nbhCloseCatalogTableModal() {
    var modal = document.getElementById('nbhCatalogTableModal');
    if (modal) {
        modal.classList.remove('is-open');
    }
}

function nbhCatalogTableImportCurrent() {
    var parsed = nbhCatalogTableState.parsed;

    if (!parsed || !parsed.rows.length) {
        nbhCatalogTableSetStatus('Сначала вставьте таблицу или соберите текущие карточки.', 'error');
        return;
    }

    if (!parsed.stats.ready) {
        nbhCatalogTableSetStatus('Нет строк, готовых к импорту. Исправьте ошибки или удалите дубликаты.', 'error');
        return;
    }

    try {
        nbhImportCatalogPayload({ items: parsed.rawItems });
        nbhCatalogTableSetStatus('Импорт завершён. Каталог обновлён.', 'success');
        nbhCloseCatalogTableModal();
    } catch (error) {
        nbhCatalogTableSetStatus(error && error.message ? error.message : 'Не удалось импортировать таблицу.', 'error');
    }
}

function nbhDataOptions() {
    var options = nbhState.server && nbhState.server.dataOptions ? nbhState.server.dataOptions : null;
    if (!options || typeof options !== 'object') {
        return { contentTypes: [], fieldsByType: {}, sourceModes: [], itemResolverModes: [], listModes: [], sortOptions: [] };
    }

    options.contentTypes = Array.isArray(options.contentTypes) ? options.contentTypes : [];
    options.fieldsByType = options.fieldsByType && typeof options.fieldsByType === 'object' ? options.fieldsByType : {};
    options.sourceModes = Array.isArray(options.sourceModes) ? options.sourceModes : [];
    options.itemResolverModes = Array.isArray(options.itemResolverModes) ? options.itemResolverModes : [];
    options.listModes = Array.isArray(options.listModes) ? options.listModes : [];
    options.sortOptions = Array.isArray(options.sortOptions) ? options.sortOptions : [];
    return options;
}

function nbhListSource() {
    var source = nbhGet(nbhState.draft, 'data.listSource', null);
    if (!source || typeof source !== 'object' || Array.isArray(source)) {
        source = {};
    }

    if (!source.type) source.type = 'manual';
    if (!source.ctype) source.ctype = '';
    if (typeof source.limit !== 'number') source.limit = 3;
    if (!source.sort) source.sort = 'date_pub_desc';
    if (!source.map || typeof source.map !== 'object' || Array.isArray(source.map)) {
        source.map = {};
    }
    if (nbhIsSliderCollectionBlock()) {
        if (typeof source.map.eyebrow !== 'string') source.map.eyebrow = 'category.title';
        if (typeof source.map.title !== 'string') source.map.title = 'title';
        if (typeof source.map.text !== 'string') source.map.text = 'teaser';
        if (typeof source.map.image !== 'string') source.map.image = 'record_image_url';
        if (typeof source.map.imageAlt !== 'string') source.map.imageAlt = 'title';
        if (typeof source.map.date !== 'string') source.map.date = 'date_pub';
        if (typeof source.map.metaLabel !== 'string') source.map.metaLabel = 'category.title';
        if (typeof source.map.primaryCtaLabel !== 'string') source.map.primaryCtaLabel = 'title';
        if (typeof source.map.primaryCtaUrl !== 'string') source.map.primaryCtaUrl = 'record_url';
        if (typeof source.map.secondaryCtaLabel !== 'string') source.map.secondaryCtaLabel = '';
        if (typeof source.map.secondaryCtaUrl !== 'string') source.map.secondaryCtaUrl = '';
        if (typeof source.map.recordUrl !== 'string') source.map.recordUrl = 'record_url';
    } else if (nbhIsCardCollectionBlock()) {
        if (typeof source.map.title !== 'string') source.map.title = 'title';
        if (typeof source.map.excerpt !== 'string') source.map.excerpt = 'teaser';
        if (typeof source.map.image !== 'string') source.map.image = 'record_image_url';
        if (typeof source.map.imageAlt !== 'string') source.map.imageAlt = 'title';
        if (typeof source.map.category !== 'string') source.map.category = 'category.title';
        if (typeof source.map.categoryUrl !== 'string') source.map.categoryUrl = 'category.url';
        if (typeof source.map.date !== 'string') source.map.date = 'date_pub';
        if (typeof source.map.views !== 'string') source.map.views = 'hits_count';
        if (typeof source.map.comments !== 'string') source.map.comments = 'comments_count';
        if (typeof source.map.url !== 'string') source.map.url = 'record_url';
        if (nbhCollectionBlockKind() === 'catalog_browser') {
            if (typeof source.map.price !== 'string') source.map.price = 'price';
            if (typeof source.map.priceOld !== 'string') source.map.priceOld = 'price_old';
            if (typeof source.map.currency !== 'string') source.map.currency = 'currency';
            if (typeof source.map.badge !== 'string') source.map.badge = 'badge';
            if (typeof source.map.tags !== 'string') source.map.tags = 'tags';
            if (typeof source.map.ctaLabel !== 'string') source.map.ctaLabel = 'cta_label';
            if (typeof source.map.ctaKind !== 'string') source.map.ctaKind = 'cta_kind';
            if (typeof source.map.ctaUrl !== 'string') source.map.ctaUrl = 'cta_url';
            if (typeof source.map.messengerType !== 'string') source.map.messengerType = 'messenger_type';
            if (typeof source.map.availability !== 'string') source.map.availability = 'availability';
            if (typeof source.map.gallery !== 'string') source.map.gallery = 'gallery';
        }
        source.map.text = source.map.excerpt;
    } else {
        if (typeof source.map.title !== 'string') source.map.title = typeof source.map.question === 'string' ? source.map.question : 'title';
        if (typeof source.map.text !== 'string') source.map.text = typeof source.map.answer === 'string' ? source.map.answer : '';
        source.map.question = source.map.title;
        source.map.answer = source.map.text;
    }
    if (!source.emptyBehavior) source.emptyBehavior = 'fallback';

    nbhSet(nbhState.draft, 'data.listSource', source);
    return source;
}

function nbhCollectionDefaultItem() {
    if (nbhIsSliderCollectionBlock()) {
        return {
            eyebrow: 'Featured',
            title: 'Новый слайд',
            text: 'Короткое описание слайда, которое задаёт контекст и помогает перейти дальше.',
            primary_cta_label: 'Подробнее',
            primary_cta_url: '/news',
            secondary_cta_label: 'Все материалы',
            secondary_cta_url: '/news',
            image: '',
            image_alt: '',
            imageAlt: '',
            alt: '',
            date: '',
            meta_label: 'Новость',
            record_url: '/news'
        };
    }

    if (nbhIsCardCollectionBlock()) {
        if (nbhCollectionBlockKind() === 'catalog_browser') {
            return nbhNormalizeCatalogItem(nbhCatalogBaseItem(), { usedIds: nbhCatalogUsedIdMap(), forceNewId: true });
        }

        return {
            category: nbhCollectionBlockKind() === 'category_cards' ? 'Раздел' : (nbhCollectionBlockKind() === 'headline_feed' ? 'Тема' : (nbhCollectionBlockKind() === 'swiss_grid' ? 'Проект' : 'Новости')),
            title: nbhCollectionBlockKind() === 'category_cards' ? 'Новая карточка раздела' : (nbhCollectionBlockKind() === 'headline_feed' ? 'Новый материал ленты' : (nbhCollectionBlockKind() === 'swiss_grid' ? 'Новая swiss-карточка' : 'Новая карточка')),
            excerpt: 'Короткий анонс материала, который объясняет, почему в него стоит перейти.',
            text: 'Короткий анонс материала, который объясняет, почему в него стоит перейти.',
            category_url: '',
            categoryUrl: '',
            link_label: 'Подробнее',
            linkLabel: 'Подробнее',
            url: '/news',
            image: '',
            imageAlt: '',
            alt: '',
            date: '',
            views: '',
            comments: ''
        };
    }

    return {
        title: 'Новый вопрос',
        text: 'Короткий ответ на вопрос.',
        question: 'Новый вопрос',
        answer: 'Короткий ответ на вопрос.'
    };
}

function nbhCollectionItemValue(item, key) {
    if (!item || typeof item !== 'object') {
        return '';
    }

    if (nbhIsSliderCollectionBlock() && key === 'imageAlt') {
        return typeof item.image_alt === 'string' ? item.image_alt : (typeof item.imageAlt === 'string' ? item.imageAlt : (typeof item.alt === 'string' ? item.alt : ''));
    }

    if (nbhIsSliderCollectionBlock() && key === 'primary_cta_label') {
        return typeof item.primary_cta_label === 'string' ? item.primary_cta_label : (typeof item.primaryCtaLabel === 'string' ? item.primaryCtaLabel : '');
    }

    if (nbhIsSliderCollectionBlock() && key === 'primary_cta_url') {
        return typeof item.primary_cta_url === 'string' ? item.primary_cta_url : (typeof item.primaryCtaUrl === 'string' ? item.primaryCtaUrl : '');
    }

    if (nbhIsSliderCollectionBlock() && key === 'secondary_cta_label') {
        return typeof item.secondary_cta_label === 'string' ? item.secondary_cta_label : (typeof item.secondaryCtaLabel === 'string' ? item.secondaryCtaLabel : '');
    }

    if (nbhIsSliderCollectionBlock() && key === 'secondary_cta_url') {
        return typeof item.secondary_cta_url === 'string' ? item.secondary_cta_url : (typeof item.secondaryCtaUrl === 'string' ? item.secondaryCtaUrl : '');
    }

    if (nbhIsSliderCollectionBlock() && key === 'meta_label') {
        return typeof item.meta_label === 'string' ? item.meta_label : (typeof item.metaLabel === 'string' ? item.metaLabel : '');
    }

    if (nbhIsSliderCollectionBlock() && key === 'record_url') {
        return typeof item.record_url === 'string' ? item.record_url : (typeof item.recordUrl === 'string' ? item.recordUrl : (typeof item.url === 'string' ? item.url : ''));
    }

    if (nbhIsCardCollectionBlock() && key === 'id') {
        return nbhCatalogItemIdValue(item);
    }

    if (!nbhIsCardCollectionBlock() && key === 'title') {
        return typeof item.title === 'string' ? item.title : (typeof item.question === 'string' ? item.question : '');
    }

    if (!nbhIsCardCollectionBlock() && key === 'text') {
        return typeof item.text === 'string' ? item.text : (typeof item.answer === 'string' ? item.answer : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'excerpt') {
        return typeof item.excerpt === 'string' ? item.excerpt : (typeof item.text === 'string' ? item.text : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'imageAlt') {
        return typeof item.imageAlt === 'string' ? item.imageAlt : (typeof item.alt === 'string' ? item.alt : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'link_label') {
        return typeof item.link_label === 'string' ? item.link_label : (typeof item.linkLabel === 'string' ? item.linkLabel : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'category_url') {
        return typeof item.category_url === 'string' ? item.category_url : (typeof item.categoryUrl === 'string' ? item.categoryUrl : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'priceOld') {
        return typeof item.priceOld === 'string' ? item.priceOld : (typeof item.price_old === 'string' ? item.price_old : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'cta_label') {
        return typeof item.cta_label === 'string' ? item.cta_label : (typeof item.ctaLabel === 'string' ? item.ctaLabel : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'cta_kind') {
        return typeof item.cta_kind === 'string' ? item.cta_kind : (typeof item.ctaKind === 'string' ? item.ctaKind : 'url');
    }

    if (nbhIsCardCollectionBlock() && key === 'cta_url') {
        return typeof item.cta_url === 'string' ? item.cta_url : (typeof item.ctaUrl === 'string' ? item.ctaUrl : '');
    }

    if (nbhIsCardCollectionBlock() && key === 'messenger_type') {
        return typeof item.messenger_type === 'string' ? item.messenger_type : (typeof item.messengerType === 'string' ? item.messengerType : 'none');
    }

    if (nbhIsCardCollectionBlock() && key === 'tags') {
        if (Array.isArray(item.tags)) {
            return item.tags.join(', ');
        }
        return typeof item.tags === 'string' ? item.tags : '';
    }

    if (nbhIsCardCollectionBlock() && key === 'gallery') {
        if (Array.isArray(item.gallery)) {
            try {
                return JSON.stringify(item.gallery, null, 2);
            } catch (error) {
                return '';
            }
        }
        return typeof item.gallery === 'string' ? item.gallery : '';
    }

    return typeof item[key] === 'string' ? item[key] : '';
}

function nbhSingleSource() {
    var source = nbhGet(nbhState.draft, 'data.source', null);
    if (!source || typeof source !== 'object' || Array.isArray(source)) {
        source = {};
    }

    if (!source.type) source.type = 'manual';
    if (!source.ctype) source.ctype = '';
    if (!source.resolver || typeof source.resolver !== 'object' || Array.isArray(source.resolver)) {
        source.resolver = {};
    }
    if (!source.resolver.mode) source.resolver.mode = 'current';

    var resolvedId = parseInt(source.resolver.id || source.resolver.itemId || source.resolver.item_id || 0, 10);
    source.resolver.id = isNaN(resolvedId) ? 0 : resolvedId;

    nbhSet(nbhState.draft, 'data.source', source);
    return source;
}

function nbhSingleBindings() {
    var defaults = {
        eyebrow: { mode: 'mixed', formatter: 'plain_text', emptyBehavior: 'fallback' },
        title: { mode: 'bound', formatter: 'plain_text', emptyBehavior: 'fallback' },
        subtitle: { mode: 'mixed', formatter: 'plain_text', emptyBehavior: 'fallback' },
        body: { mode: 'mixed', formatter: 'plain_text', emptyBehavior: 'fallback' },
        image: { mode: 'mixed', formatter: 'image_url', emptyBehavior: 'fallback' },
        imageAlt: { mode: 'mixed', formatter: 'plain_text', emptyBehavior: 'fallback' },
        category: { mode: 'bound', formatter: 'plain_text', emptyBehavior: 'hide' },
        author: { mode: 'bound', formatter: 'plain_text', emptyBehavior: 'hide' },
        date: { mode: 'bound', formatter: 'date_human', emptyBehavior: 'hide' },
        views: { mode: 'bound', formatter: 'number', emptyBehavior: 'hide' },
        comments: { mode: 'bound', formatter: 'number', emptyBehavior: 'hide' },
        primaryButtonUrl: { mode: 'mixed', formatter: 'record_url', emptyBehavior: 'fallback' },
        secondaryButtonUrl: { mode: 'mixed', formatter: 'record_url', emptyBehavior: 'fallback' },
        tertiaryButtonUrl: { mode: 'mixed', formatter: 'record_url', emptyBehavior: 'fallback' }
    };
    var bindings = nbhGet(nbhState.draft, 'data.bindings', null);
    if (!bindings || typeof bindings !== 'object' || Array.isArray(bindings)) {
        bindings = {};
    }

    Object.keys(defaults).forEach(function(key) {
        var binding = bindings[key];
        if (!binding || typeof binding !== 'object' || Array.isArray(binding)) {
            binding = {};
        }
        if (!binding.mode) binding.mode = defaults[key].mode;
        if (typeof binding.field !== 'string') binding.field = '';
        if (!binding.formatter) binding.formatter = defaults[key].formatter;
        if (!binding.emptyBehavior) binding.emptyBehavior = defaults[key].emptyBehavior;
        bindings[key] = binding;
    });

    nbhSet(nbhState.draft, 'data.bindings', bindings);
    return bindings;
}

function nbhUsesCollectionData() {
    return (nbhHasCapability('repeaterBindings') && nbhHasEntity('items'))
        || (nbhHasCapability('hasContentListSource') && nbhHasEntity('slide'));
}

function nbhFieldMatchesKinds(field, kinds) {
    var fieldKinds = Array.isArray(field && field.kinds) ? field.kinds : ['text'];
    var allowedKinds = Array.isArray(kinds) ? kinds : ['text'];
    return allowedKinds.some(function(kind) {
        return fieldKinds.indexOf(kind) !== -1;
    });
}

function nbhFieldOptionsByKinds(fields, kinds, emptyLabel) {
    fields = Array.isArray(fields) ? fields : [];
    return [{ value: '', label: emptyLabel || 'Не выбрано' }].concat(fields.filter(function(field) {
        return nbhFieldMatchesKinds(field, kinds);
    }).map(function(field) {
        return { value: field.name, label: field.label + ' [' + field.type + ']' };
    }));
}

function nbhEscapeAttr(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function nbhEscapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function nbhNormalizeColor(value, fallback) {
    var normalized = String(value == null ? '' : value).trim();
    var safeFallback = String(fallback == null ? '#000000' : fallback).trim();

    if (/^#[0-9a-f]{6}$/i.test(normalized)) {
        return normalized;
    }

    if (/^#[0-9a-f]{3}$/i.test(normalized)) {
        return '#' + normalized.charAt(1) + normalized.charAt(1)
            + normalized.charAt(2) + normalized.charAt(2)
            + normalized.charAt(3) + normalized.charAt(3);
    }

    if (/^#[0-9a-f]{3}$/i.test(safeFallback)) {
        return '#' + safeFallback.charAt(1) + safeFallback.charAt(1)
            + safeFallback.charAt(2) + safeFallback.charAt(2)
            + safeFallback.charAt(3) + safeFallback.charAt(3);
    }

    if (/^#[0-9a-f]{6}$/i.test(safeFallback)) {
        return safeFallback;
    }

    return '#000000';
}

function nbhColorControlSelector(path) {
    return '[data-color-control="' + String(path || '').replace(/"/g, '\\"') + '"]';
}

function nbhSyncColorControls(path, value) {
    var normalized = nbhNormalizeColor(value, nbhGet(nbhState.draft, path, '#000000'));

    document.querySelectorAll(nbhColorControlSelector(path)).forEach(function(control) {
        var colorInput = control.querySelector('[data-color-path]');
        var textInput = control.querySelector('[data-color-text]');
        var swatchFace = control.querySelector('.nbh-color-swatch__face');

        if (colorInput) {
            colorInput.value = normalized;
        }

        if (textInput) {
            textInput.value = normalized.toUpperCase();
        }

        if (swatchFace) {
            swatchFace.style.background = normalized;
        }
    });

    return normalized;
}

function nbhPreviewColorControl(path, value) {
    var normalized = String(value == null ? '' : value).trim();

    if (!/^#[0-9a-f]{3}$/i.test(normalized) && !/^#[0-9a-f]{6}$/i.test(normalized)) {
        return null;
    }

    return nbhSyncColorControls(path, normalized);
}

function nbhCommitPathValue(path, value, forceRerender) {
    nbhSet(nbhState.draft, path, value);
    if (forceRerender || nbhShouldRerenderPanels(path)) {
        nbhRenderPanels();
    }
    nbhMarkDirty();
    nbhScheduleSave();
}

function nbhHeroPresetConfig(preset) {
    var presets = {
        'classic': {
            mode: 'centered',
            containerMode: 'contained',
            mediaPositionDesktop: 'start',
            mediaPositionMobile: 'top',
            paddingTopDesktop: 96,
            paddingBottomDesktop: 96,
            paddingTopMobile: 56,
            paddingBottomMobile: 56,
            mediaRadius: 28,
            mediaSurfaceRadius: 28,
            mediaSurfaceShadow: 'lg'
        },
        'split-left': {
            mode: 'split',
            containerMode: 'contained',
            mediaPositionDesktop: 'start',
            mediaPositionMobile: 'top',
            paddingTopDesktop: 96,
            paddingBottomDesktop: 96,
            paddingTopMobile: 56,
            paddingBottomMobile: 56,
            mediaRadius: 28,
            mediaSurfaceRadius: 28,
            mediaSurfaceShadow: 'lg'
        },
        'split-right': {
            mode: 'split',
            containerMode: 'contained',
            mediaPositionDesktop: 'end',
            mediaPositionMobile: 'top',
            paddingTopDesktop: 96,
            paddingBottomDesktop: 96,
            paddingTopMobile: 56,
            paddingBottomMobile: 56,
            mediaRadius: 28,
            mediaSurfaceRadius: 28,
            mediaSurfaceShadow: 'lg'
        },
        'edge-left': {
            mode: 'split',
            containerMode: 'fluid',
            mediaPositionDesktop: 'start',
            mediaPositionMobile: 'top',
            paddingTopDesktop: 96,
            paddingBottomDesktop: 96,
            paddingTopMobile: 56,
            paddingBottomMobile: 56,
            mediaRadius: 0,
            mediaSurfaceRadius: 0,
            mediaSurfaceShadow: 'none'
        },
        'edge-right': {
            mode: 'split',
            containerMode: 'fluid',
            mediaPositionDesktop: 'end',
            mediaPositionMobile: 'top',
            paddingTopDesktop: 96,
            paddingBottomDesktop: 96,
            paddingTopMobile: 56,
            paddingBottomMobile: 56,
            mediaRadius: 0,
            mediaSurfaceRadius: 0,
            mediaSurfaceShadow: 'none'
        },
        'strip': {
            mode: 'split',
            containerMode: 'fluid',
            mediaPositionDesktop: 'start',
            mediaPositionMobile: 'top',
            paddingTopDesktop: 0,
            paddingBottomDesktop: 0,
            paddingTopMobile: 0,
            paddingBottomMobile: 0,
            mediaRadius: 0,
            mediaSurfaceRadius: 0,
            mediaSurfaceShadow: 'none'
        }
    };

    return presets[preset] || presets.classic;
}

function nbhApplyHeroPreset(preset) {
    var profile = nbhBlockUiProfile();
    var config;

    if (!profile || profile.kind !== 'hero' || !nbhState.draft) {
        return false;
    }

    config = nbhHeroPresetConfig(preset);
    nbhSet(nbhState.draft, 'layout.preset', preset);
    nbhSet(nbhState.draft, 'layout.desktop.mode', config.mode);
    nbhSet(nbhState.draft, 'layout.desktop.containerMode', config.containerMode);
    nbhSet(nbhState.draft, 'layout.desktop.mediaPosition', config.mediaPositionDesktop);
    nbhSet(nbhState.draft, 'layout.mobile.mediaPosition', config.mediaPositionMobile);
    nbhSet(nbhState.draft, 'layout.desktop.paddingTop', config.paddingTopDesktop);
    nbhSet(nbhState.draft, 'layout.desktop.paddingBottom', config.paddingBottomDesktop);
    nbhSet(nbhState.draft, 'layout.mobile.paddingTop', config.paddingTopMobile);
    nbhSet(nbhState.draft, 'layout.mobile.paddingBottom', config.paddingBottomMobile);
    nbhSet(nbhState.draft, 'design.entities.media.radius', config.mediaRadius);
    nbhSet(nbhState.draft, 'design.entities.mediaSurface.radius', config.mediaSurfaceRadius);
    nbhSet(nbhState.draft, 'design.entities.mediaSurface.shadow', config.mediaSurfaceShadow);

    return true;
}

var nbhImagePickerState = {
    path: '',
    page: 1,
    total: 0,
    perPage: 0
};

function nbhEnsureImagePickerModal() {
    if (document.getElementById('nbhImagePickerModal')) {
        return document.getElementById('nbhImagePickerModal');
    }

    var modal = document.createElement('div');
    modal.id = 'nbhImagePickerModal';
    modal.className = 'nbh-media-modal';
    modal.innerHTML = ''
        + '<div class="nbh-media-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="nbhImagePickerTitle">'
        + '<div class="nbh-media-modal__head">'
        + '<strong id="nbhImagePickerTitle">Выбрать изображение</strong>'
        + '<button type="button" class="nbh-picker-btn" data-media-close="1">Закрыть</button>'
        + '</div>'
        + '<div class="nbh-media-modal__toolbar">'
        + '<div class="nbh-media-modal__pager">'
        + '<button type="button" class="nbh-picker-btn" data-media-prev="1">Назад</button>'
        + '<span class="nbh-media-modal__status" data-media-status="1">Страница 1</span>'
        + '<button type="button" class="nbh-picker-btn" data-media-next="1">Вперед</button>'
        + '</div>'
        + '<div class="nbh-media-modal__actions">'
        + '<input type="file" accept="image/*" data-media-file="1" style="display:none;">'
        + '<button type="button" class="nbh-picker-btn" data-media-upload="1">Загрузить</button>'
        + '</div>'
        + '</div>'
        + '<div class="nbh-media-modal__body">'
        + '<div class="nbh-media-modal__loading is-hidden" data-media-loading="1"><span><i class="fa fa-spinner fa-spin"></i> Загружаем изображения...</span></div>'
        + '<div class="nbh-media-modal__empty is-hidden" data-media-empty="1">В библиотеке пока нет изображений.</div>'
        + '<div class="nbh-media-modal__grid" data-media-grid="1"></div>'
        + '</div>'
        + '</div>';

    document.body.appendChild(modal);

    modal.addEventListener('click', function(event) {
        var selectButton = event.target.closest('[data-media-select]');
        if (selectButton) {
            event.preventDefault();
            nbhCommitPathValue(nbhImagePickerState.path, selectButton.getAttribute('data-media-select') || '', true);
            nbhCloseImagePicker();
            return;
        }

        if (event.target === modal || event.target.closest('[data-media-close]')) {
            event.preventDefault();
            nbhCloseImagePicker();
            return;
        }

        if (event.target.closest('[data-media-prev]')) {
            event.preventDefault();
            if (nbhImagePickerState.page > 1) {
                nbhLoadImagePickerPage(nbhImagePickerState.page - 1);
            }
            return;
        }

        if (event.target.closest('[data-media-next]')) {
            event.preventDefault();
            var totalPages = Math.max(1, Math.ceil((nbhImagePickerState.total || 0) / (nbhImagePickerState.perPage || 1)));
            if (nbhImagePickerState.page < totalPages) {
                nbhLoadImagePickerPage(nbhImagePickerState.page + 1);
            }
            return;
        }

        if (event.target.closest('[data-media-upload]')) {
            event.preventDefault();
            modal.querySelector('[data-media-file]').click();
        }
    });

    modal.querySelector('[data-media-file]').addEventListener('change', function() {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            return;
        }

        nbhSetImagePickerLoading(true, 'Загружаем изображение...');

        var formData = new FormData();
        formData.append('file', file);

        fetch(nbhImagePickerUploadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('upload_failed');
            }
            return response.json();
        })
        .then(function(result) {
            var uploadedUrl = result && result.media && result.media.original ? result.media.original : (result && result.url ? result.url : '');
            if (!uploadedUrl) {
                throw new Error(result && result.error ? result.error : 'upload_failed');
            }
            nbhCommitPathValue(nbhImagePickerState.path, uploadedUrl, true);
            nbhCloseImagePicker();
        })
        .catch(function(error) {
            alert(error && error.message ? error.message : 'Не удалось загрузить изображение.');
        })
        .finally(function() {
            modal.querySelector('[data-media-file]').value = '';
            nbhSetImagePickerLoading(false);
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            nbhCloseImagePicker();
        }
    });

    return modal;
}

function nbhSetImagePickerLoading(isLoading, message) {
    var modal = nbhEnsureImagePickerModal();
    var loading = modal.querySelector('[data-media-loading]');
    if (message) {
        loading.innerHTML = '<span><i class="fa fa-spinner fa-spin"></i> ' + nbhEscapeHtml(message) + '</span>';
    }
    loading.classList.toggle('is-hidden', !isLoading);
}

function nbhRenderImagePickerResult(result) {
    var modal = nbhEnsureImagePickerModal();
    var grid = modal.querySelector('[data-media-grid]');
    var empty = modal.querySelector('[data-media-empty]');
    var status = modal.querySelector('[data-media-status]');
    var prev = modal.querySelector('[data-media-prev]');
    var next = modal.querySelector('[data-media-next]');
    var images = result && Array.isArray(result.files) ? result.files : (result && Array.isArray(result.images) ? result.images : []);

    nbhImagePickerState.total = images.length;
    nbhImagePickerState.perPage = images.length || 1;

    status.textContent = images.length + ' изображений';
    prev.disabled = true;
    next.disabled = true;

    if (!images.length) {
        grid.innerHTML = '';
        empty.classList.remove('is-hidden');
        return;
    }

    empty.classList.add('is-hidden');
    grid.innerHTML = images.map(function(image) {
        var title = image && image.title ? image.title : 'Изображение';
        var url = image && image.media && image.media.original ? image.media.original : (image && image.url ? image.url : (image && image.preview_url ? image.preview_url : ''));
        var previewUrl = image && image.preview_url ? image.preview_url : url;
        return ''
            + '<button type="button" class="nbh-media-card" data-media-select="' + nbhEscapeAttr(url) + '">'
            + '<span class="nbh-media-card__thumb"><img src="' + nbhEscapeAttr(previewUrl) + '" alt="' + nbhEscapeAttr(title) + '"></span>'
            + '<span class="nbh-media-card__name">' + nbhEscapeHtml(title) + '</span>'
            + '</button>';
    }).join('');
}

function nbhLoadImagePickerPage(page) {
    nbhImagePickerState.page = Math.max(1, page || 1);
    nbhSetImagePickerLoading(true, 'Загружаем изображения...');

    fetch(nbhImagePickerListUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('list_failed');
        }
        return response.json();
    })
    .then(function(result) {
        nbhRenderImagePickerResult(result || {});
    })
    .catch(function() {
        var modal = nbhEnsureImagePickerModal();
        modal.querySelector('[data-media-grid]').innerHTML = '';
        modal.querySelector('[data-media-empty]').textContent = 'Не удалось загрузить системную библиотеку изображений.';
        modal.querySelector('[data-media-empty]').classList.remove('is-hidden');
    })
    .finally(function() {
        nbhSetImagePickerLoading(false);
    });
}

function nbhOpenImagePicker(path) {
    var modal = nbhEnsureImagePickerModal();
    nbhImagePickerState.path = path;
    nbhImagePickerState.page = 1;
    modal.classList.add('is-open');
    nbhLoadImagePickerPage(1);
}

function nbhCloseImagePicker() {
    nbhEnsureImagePickerModal().classList.remove('is-open');
}

function nbhOpenIconPicker(path) {
    if (!window.icms || !icms.modal || typeof icms.modal.openAjax !== 'function') {
        window.open(nbhIconPickerUrl, '_blank');
        return;
    }

    icms.modal.openAjax(nbhIconPickerUrl, {}, function() {
        Array.prototype.forEach.call(document.querySelectorAll('.icon-select'), function(icon) {
            icon.addEventListener('click', function(event) {
                event.preventDefault();
                nbhCommitPathValue(path, icon.getAttribute('data-name') || '', true);
                if (icms.modal && typeof icms.modal.close === 'function') {
                    icms.modal.close();
                }
                return false;
            }, { once: true });
        });
    }, 'Выбрать иконку');
}

function nbhAddRepeaterItem() {
    var items = nbhRepeaterItems().slice();
    items.push(nbhCollectionDefaultItem());
    nbhSet(nbhState.draft, nbhCollectionRepeaterPath(), items);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();
}

function nbhRemoveRepeaterItem(index) {
    var items = nbhRepeaterItems().slice();
    if (index < 0 || index >= items.length) {
        return;
    }
    items.splice(index, 1);
    nbhSet(nbhState.draft, nbhCollectionRepeaterPath(), items);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();
}

function nbhDuplicateRepeaterItem(index) {
    var items = nbhRepeaterItems().slice();
    var clone;
    if (index < 0 || index >= items.length) {
        return;
    }

    clone = nbhClone(items[index]);
    if (nbhCollectionBlockKind() === 'catalog_browser') {
        clone = nbhNormalizeCatalogItem(clone, { usedIds: nbhCatalogUsedIdMap(), forceNewId: true });
    }

    items.splice(index + 1, 0, clone);
    nbhSet(nbhState.draft, nbhCollectionRepeaterPath(), items);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();
}

function nbhMoveRepeaterItem(index, direction) {
    var items = nbhRepeaterItems().slice();
    var nextIndex = index + direction;
    var current;

    if (index < 0 || index >= items.length || nextIndex < 0 || nextIndex >= items.length) {
        return;
    }

    current = items[index];
    items[index] = items[nextIndex];
    items[nextIndex] = current;
    nbhSet(nbhState.draft, nbhCollectionRepeaterPath(), items);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();
}

function nbhUpdateRepeaterItem(index, field, value) {
    var items = nbhRepeaterItems().slice();
    if (!items[index] || typeof items[index] !== 'object') {
        items[index] = nbhCollectionDefaultItem();
    }

    var item = items[index];
    if (nbhIsSliderCollectionBlock()) {
        if (field === 'alt' || field === 'imageAlt') field = 'image_alt';

        item[field] = value;

        if (field === 'image_alt') {
            item.imageAlt = value;
            item.alt = value;
        }
        if (field === 'primary_cta_label') {
            item.primaryCtaLabel = value;
        }
        if (field === 'primary_cta_url') {
            item.primaryCtaUrl = value;
        }
        if (field === 'secondary_cta_label') {
            item.secondaryCtaLabel = value;
        }
        if (field === 'secondary_cta_url') {
            item.secondaryCtaUrl = value;
        }
        if (field === 'meta_label') {
            item.metaLabel = value;
        }
        if (field === 'record_url') {
            item.recordUrl = value;
            item.url = value;
        }

        if (typeof item.image_alt !== 'string') item.image_alt = typeof item.imageAlt === 'string' ? item.imageAlt : (typeof item.alt === 'string' ? item.alt : '');
        if (typeof item.imageAlt !== 'string') item.imageAlt = item.image_alt;
        if (typeof item.alt !== 'string') item.alt = item.image_alt;
        if (typeof item.primary_cta_label !== 'string') item.primary_cta_label = typeof item.primaryCtaLabel === 'string' ? item.primaryCtaLabel : '';
        if (typeof item.primaryCtaLabel !== 'string') item.primaryCtaLabel = item.primary_cta_label;
        if (typeof item.primary_cta_url !== 'string') item.primary_cta_url = typeof item.primaryCtaUrl === 'string' ? item.primaryCtaUrl : '';
        if (typeof item.primaryCtaUrl !== 'string') item.primaryCtaUrl = item.primary_cta_url;
        if (typeof item.secondary_cta_label !== 'string') item.secondary_cta_label = typeof item.secondaryCtaLabel === 'string' ? item.secondaryCtaLabel : '';
        if (typeof item.secondaryCtaLabel !== 'string') item.secondaryCtaLabel = item.secondary_cta_label;
        if (typeof item.secondary_cta_url !== 'string') item.secondary_cta_url = typeof item.secondaryCtaUrl === 'string' ? item.secondaryCtaUrl : '';
        if (typeof item.secondaryCtaUrl !== 'string') item.secondaryCtaUrl = item.secondary_cta_url;
        if (typeof item.meta_label !== 'string') item.meta_label = typeof item.metaLabel === 'string' ? item.metaLabel : '';
        if (typeof item.metaLabel !== 'string') item.metaLabel = item.meta_label;
        if (typeof item.record_url !== 'string') item.record_url = typeof item.recordUrl === 'string' ? item.recordUrl : (typeof item.url === 'string' ? item.url : '');
        if (typeof item.recordUrl !== 'string') item.recordUrl = item.record_url;
        if (typeof item.url !== 'string') item.url = item.record_url;
    } else if (!nbhIsCardCollectionBlock()) {
        if (field === 'question') field = 'title';
        if (field === 'answer') field = 'text';
    } else {
        if (field === 'text') field = 'excerpt';
        if (field === 'alt') field = 'imageAlt';
        if (field === 'itemId' || field === 'item_id') field = 'id';
    }

    item[field] = value;

    if (!nbhIsCardCollectionBlock()) {
        if (field === 'title') {
            item.question = value;
        }
        if (field === 'text') {
            item.answer = value;
        }

        if (typeof item.title !== 'string') item.title = typeof item.question === 'string' ? item.question : '';
        if (typeof item.text !== 'string') item.text = typeof item.answer === 'string' ? item.answer : '';
        item.question = item.title;
        item.answer = item.text;
    } else {
        if (field === 'excerpt') {
            item.text = value;
        }
        if (field === 'category_url') {
            item.categoryUrl = value;
        }
        if (field === 'id') {
            item.itemId = value;
            item.item_id = value;
        }
        if (field === 'priceOld') {
            item.price_old = value;
        }
        if (field === 'cta_label') {
            item.ctaLabel = value;
            item.link_label = value;
            item.linkLabel = value;
        }
        if (field === 'cta_kind') {
            item.ctaKind = value;
        }
        if (field === 'cta_url') {
            item.ctaUrl = value;
        }
        if (field === 'messenger_type') {
            item.messengerType = value;
        }
        if (field === 'imageAlt') {
            item.alt = value;
        }
        if (field === 'link_label') {
            item.linkLabel = value;
        }
        if (typeof item.excerpt !== 'string') item.excerpt = typeof item.text === 'string' ? item.text : '';
        if (typeof item.text !== 'string') item.text = item.excerpt;
        if (typeof item.category_url !== 'string') item.category_url = typeof item.categoryUrl === 'string' ? item.categoryUrl : '';
        if (typeof item.categoryUrl !== 'string') item.categoryUrl = item.category_url;
        if (typeof item.id !== 'string') item.id = nbhCatalogItemIdValue(item);
        if (typeof item.itemId !== 'string') item.itemId = item.id;
        if (typeof item.item_id !== 'string') item.item_id = item.id;
        if (typeof item.priceOld !== 'string') item.priceOld = typeof item.price_old === 'string' ? item.price_old : '';
        if (typeof item.price_old !== 'string') item.price_old = item.priceOld;
        if (typeof item.cta_label !== 'string') item.cta_label = typeof item.ctaLabel === 'string' ? item.ctaLabel : '';
        if (typeof item.ctaLabel !== 'string') item.ctaLabel = item.cta_label;
        if (typeof item.cta_kind !== 'string') item.cta_kind = typeof item.ctaKind === 'string' ? item.ctaKind : 'url';
        if (typeof item.ctaKind !== 'string') item.ctaKind = item.cta_kind;
        if (typeof item.cta_url !== 'string') item.cta_url = typeof item.ctaUrl === 'string' ? item.ctaUrl : '';
        if (typeof item.ctaUrl !== 'string') item.ctaUrl = item.cta_url;
        if (typeof item.messenger_type !== 'string') item.messenger_type = typeof item.messengerType === 'string' ? item.messengerType : 'none';
        if (typeof item.messengerType !== 'string') item.messengerType = item.messenger_type;
        if (typeof item.link_label !== 'string') item.link_label = typeof item.linkLabel === 'string' ? item.linkLabel : item.cta_label;
        if (typeof item.linkLabel !== 'string') item.linkLabel = item.link_label;
        if (typeof item.imageAlt !== 'string') item.imageAlt = typeof item.alt === 'string' ? item.alt : '';
        if (typeof item.alt !== 'string') item.alt = item.imageAlt;

        if (nbhCollectionBlockKind() === 'catalog_browser') {
            item = nbhNormalizeCatalogItem(item, { usedIds: nbhCatalogUsedIdMap(index) });
            items[index] = item;
        }
    }

    nbhSet(nbhState.draft, nbhCollectionRepeaterPath(), items);
    nbhMarkDirty();
    nbhScheduleSave();
}

function nbhMarkDirty() {
    nbhState.dirty = true;
    document.getElementById('nbhSaveBtn').classList.add('is-dirty');
}

function nbhScheduleSave() {
    clearTimeout(nbhState.debounceTimer);
    nbhState.debounceTimer = setTimeout(function() {
        nbhSave(true);
    }, 350);
}

<?php include __DIR__ . '/editor_hero_v2_css_overlay.tpl.php'; ?>

function nbhReloadCanvas() {
    var frame = document.getElementById('nbh-canvas-frame');
    frame.style.height = '';
    frame.src = nbhCanvasUrl + (nbhCanvasUrl.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
}

function nbhApplyCanvasHeight(height) {
    var frame = document.getElementById('nbh-canvas-frame');
    var numericHeight = parseInt(height, 10);

    if (!frame || !numericHeight || numericHeight < 320) {
        return;
    }

    frame.style.height = numericHeight + 'px';
}

function nbhSyncCanvasHeightFromFrame() {
    var frame = document.getElementById('nbh-canvas-frame');
    var frameDoc;
    var body;
    var html;
    var height;

    if (!frame) return;

    try {
        frameDoc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
        body = frameDoc && frameDoc.body;
        html = frameDoc && frameDoc.documentElement;
        height = Math.max(
            body ? body.scrollHeight : 0,
            body ? body.offsetHeight : 0,
            html ? html.scrollHeight : 0,
            html ? html.offsetHeight : 0,
            html ? html.clientHeight : 0
        );
    } catch (error) {
        return;
    }

    nbhApplyCanvasHeight(height);
}

function nbhSelectEntity(entityKey, fromCanvas, options) {
    options = options || {};

    entityKey = nbhResolveSelectedEntity(entityKey);
    if (!entityKey) return;

    var subtitle = document.getElementById('nbhPanelSubtitle');
    var label = document.getElementById('nbhSelectionLabel');

    if (!options.preserveNotice) {
        nbhState.autoSelectionNotice = null;
    }

    nbhState.selectedEntity = entityKey;
    nbhState.canvas.selectedEntity = entityKey;

    if (label) {
        label.textContent = 'Редактируется: ' + nbhHumanEntity(entityKey);
    }

    if (subtitle) {
        subtitle.textContent = 'Вкладка «' + nbhTabLabel(nbhState.activeTab) + '» показывает настройки для сущности «' + nbhHumanEntity(entityKey) + '».';
    }

    document.querySelectorAll('.nbh-entity-chip').forEach(function(chip) {
        chip.classList.toggle('is-active', chip.dataset.entity === entityKey);
    });

    nbhRenderPanels();

    if (!fromCanvas && options.syncCanvas !== false) {
        nbhSyncCanvasSelection(options.reason || 'shell-selection');
    }
}

function nbhSetViewport(mode) {
    var frame = document.getElementById('nbh-canvas-frame');
    document.getElementById('nbhVpDesktop').classList.toggle('is-active', mode === 'desktop');
    document.getElementById('nbhVpMobile').classList.toggle('is-active', mode === 'mobile');
    frame.classList.toggle('is-mobile', mode === 'mobile');
    setTimeout(nbhSyncCanvasHeightFromFrame, 30);
}

function nbhBuildInspectorState(payload) {
    return {
        entityGroups: nbhGet(payload, 'inspector.entityGroups', {}),
        controls: nbhGet(payload, 'inspector.controls', nbhGet(payload, 'registry.controls', nbhGet(payload, 'inspector.controlPresets', {}))),
        controlPresets: nbhGet(payload, 'inspector.controlPresets', {}),
        availablePanels: nbhGet(payload, 'inspector.availablePanels', nbhGet(payload, 'registry.panels', [])),
        tabs: nbhGet(payload, 'inspector.tabs', nbhGet(payload, 'registry.tabs', [])),
        selectionModel: nbhGet(payload, 'inspector.selectionModel', {})
    };
}

function nbhPanelControlKey(panel) {
    if (!panel) {
        return '';
    }

    return panel.controlKey || panel.control || panel.controlPreset || '';
}

function nbhLoadState() {
    return fetch(nbhEditorStateUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            var primedCatalog = false;
            if (!payload.ok) {
                throw new Error(payload.error || 'state_error');
            }
            nbhState.server = payload;
            nbhState.draft = nbhClone(payload.contract);
            nbhState.inspector = nbhBuildInspectorState(payload);
            nbhState.cssOverlay = nbhCssOverlayBuildState(payload.cssOverlay || {});
            nbhState.activeTab = payload.ui && payload.ui.activeTab ? payload.ui.activeTab : 'content';
            nbhState.activeBreakpoint = payload.ui && payload.ui.activeBreakpoint ? payload.ui.activeBreakpoint : 'desktop';
            nbhState.selectedEntity = nbhResolveSelectedEntity(payload.ui && payload.ui.selectedEntity ? payload.ui.selectedEntity : '');
            primedCatalog = nbhPrimeCatalogBrowserDraft();
            return nbhCssOverlayLoadPersisted().then(function() {
                nbhState.loaded = true;
                nbhRender();
                nbhCssOverlaySyncFrame();
                nbhSyncCanvasSelection('state-load');

                if (primedCatalog) {
                    nbhMarkDirty();
                    nbhScheduleSave();
                }
            });
        });
}

<?php include __DIR__ . '/editor_hero_v2_panel_helpers.tpl.php'; ?>

function nbhSave(silent) {
    if (!nbhState.loaded) return;
    if (silent && !nbhState.dirty) return;

    if (nbhState.saving) {
        nbhState.queuedSave = true;
        nbhState.queuedSilent = nbhState.queuedSilent && silent;
        return;
    }

    nbhState.saving = true;
    var btn = document.getElementById('nbhSaveBtn');
    btn.classList.add('is-saving');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Сохранение...';

    fetch(nbhSaveUrl + '?csrf_token=' + encodeURIComponent(nbhCsrfToken), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            title: nbhState.blockTitle,
            contract: nbhState.draft
        })
    })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            nbhState.saving = false;
            btn.classList.remove('is-saving');

            if (!payload.ok) {
                btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
                alert('Ошибка: ' + (payload.error || '?'));
                return;
            }

            if (payload.contract) {
                nbhState.draft = nbhClone(payload.contract);
            }

            nbhState.dirty = false;
            btn.classList.remove('is-dirty');
            btn.innerHTML = '<i class="fa fa-check"></i> Сохранено';
            nbhReloadCanvas();

            setTimeout(function() {
                btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
            }, 1600);

            if (nbhState.queuedSave) {
                var queuedSilent = nbhState.queuedSilent;
                nbhState.queuedSave = false;
                nbhState.queuedSilent = true;
                nbhSave(queuedSilent);
            }
        })
        .catch(function() {
            nbhState.saving = false;
            btn.classList.remove('is-saving');
            btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
        });
}

<?php include __DIR__ . '/editor_hero_v2_control_ui_helpers.tpl.php'; ?>

<?php include __DIR__ . '/editor_hero_v2_control_dispatch.tpl.php'; ?>
<?php include __DIR__ . '/editor_hero_v2_shell_events.tpl.php'; ?>

if (document.getElementById('nbhCatalogTableBtn')) {
    document.getElementById('nbhCatalogTableBtn').addEventListener('click', function() {
        nbhOpenCatalogTableModal('current');
    });
}

if (document.getElementById('nbhCatalogDemoBtn')) {
    document.getElementById('nbhCatalogDemoBtn').addEventListener('click', nbhDownloadCatalogDemo);
}

if (document.getElementById('nbhCatalogXlsxImportBtn')) {
    document.getElementById('nbhCatalogXlsxImportBtn').addEventListener('click', function() {
        nbhTriggerCatalogSpreadsheetInput();
    });
}

if (document.getElementById('nbhCatalogExportBtn')) {
    document.getElementById('nbhCatalogExportBtn').addEventListener('click', nbhExportCatalogItems);
}

if (document.getElementById('nbhCatalogImportBtn')) {
    document.getElementById('nbhCatalogImportBtn').addEventListener('click', function() {
        var input = document.getElementById('nbhCatalogImportInput');
        if (input) {
            input.click();
        }
    });
}

if (document.getElementById('nbhCatalogImportInput')) {
    document.getElementById('nbhCatalogImportInput').addEventListener('change', function() {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            return;
        }

        nbhImportCatalogFile(file);
        this.value = '';
    });
}

if (document.getElementById('nbhCatalogXlsxImportInput')) {
    document.getElementById('nbhCatalogXlsxImportInput').addEventListener('change', function() {
        var file = this.files && this.files[0] ? this.files[0] : null;

        if (!file) {
            return;
        }

        nbhImportCatalogSpreadsheetFile(file);
        this.value = '';
    });
}
</script>