<?php

$device_titles = [
    'desktop' => 'Компьютер',
    'tablet'  => 'Планшет',
    'mobile'  => 'Телефон'
];

$page_mode_titles = [
    'full_takeover'  => 'Полностью своя страница',
    'hybrid_overlay' => 'Поверх существующей страницы',
    'zone_injection' => 'Встраивание в зону страницы',
    'data_only'      => 'Только данные для блоков'
];

$page_status_titles = [
    'draft'     => 'Черновик',
    'prototype' => 'Прототип',
    'idea'      => 'Идея',
    'published' => 'Опубликовано'
];

$page_mode_title = $page_mode_titles[$page['mode']] ?? $page['mode'];
$page_status_title = $page_status_titles[$page['status']] ?? $page['status'];

$theme_helper = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_theme.php';
$theme_runtime_catalog = [];
if (is_readable($theme_helper)) {
    require_once $theme_helper;
    if (function_exists('landingbuilder_get_theme_runtime_catalog')) {
        $theme_runtime_catalog = landingbuilder_get_theme_runtime_catalog();
    }
}

$this->setPageTitle('Редактор страницы: ' . $page['title']);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы', $screen['api']['pages_url'] ?? $this->href_to('pages'));
$this->addBreadcrumb($page['title']);
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'save',
    'title' => 'Сохранить',
    'href'  => '#',
    'icon'  => 'save'
]);

$this->addToolButton([
    'class' => 'view',
    'title' => 'Предпросмотр',
    'href'  => '#',
    'icon'  => 'eye'
]);

$canvas_state = [
    'page' => [
        'id'         => $page['id'],
        'key'        => $page['key'],
        'title'      => $page['title'],
        'status'     => $page['status'],
        'mode'       => $page['mode'],
        'adapter_key'=> $page['adapter_key'] ?? '',
        'updated_at' => $page['updated_at'],
        'template'   => !empty($page['template']) ? $page['template'] : 'nordic'
    ],
    'schema' => $page['schema'],
    'screen' => $screen
];

?>
<style>
    .lb-workspace {
        position: relative;
        margin-bottom: 2rem;
        border: 1px solid #d6dfe8;
        border-radius: 26px;
        background: linear-gradient(180deg, #f6f8fb 0%, #edf2f7 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .lb-workspace__topbar {
        position: sticky;
        top: 0;
        z-index: 40;
        display: grid;
        grid-template-columns: minmax(300px, 1fr) auto minmax(320px, 1fr);
        gap: 1rem;
        align-items: center;
        padding: 1rem 1.25rem;
        background: rgba(17, 24, 39, 0.94);
        color: #f8fafc;
        backdrop-filter: blur(18px);
    }

    .lb-topbar__start,
    .lb-topbar__center,
    .lb-topbar__end {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .lb-topbar__center {
        justify-content: center;
    }

    .lb-topbar__end {
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .lb-topbar__page {
        min-width: 0;
    }

    .lb-topbar__eyebrow {
        margin-bottom: 0.15rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(248, 250, 252, 0.62);
    }

    .lb-topbar__title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
        color: #f8fafc;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lb-topbar__meta,
    .lb-topbar__save {
        font-size: 12px;
        color: rgba(248, 250, 252, 0.72);
    }

    .lb-topbar__meta code {
        padding: 0.1rem 0.3rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #f8fafc;
    }

    .lb-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 38px;
        padding: 0.6rem 0.9rem;
        border: 1px solid transparent;
        border-radius: 999px;
        background: #ffffff;
        color: #173042;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        line-height: 1;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        white-space: nowrap;
    }

    .lb-btn:hover,
    .lb-btn:focus {
        color: #122033;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
    }

    .lb-btn--topbar {
        border-color: rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
    }

    .lb-btn--topbar:hover,
    .lb-btn--topbar:focus,
    .lb-btn--topbar.is-active,
    .lb-btn--topbar.active {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.16);
        border-color: rgba(255, 255, 255, 0.22);
    }

    .lb-btn--accent {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border-color: transparent;
        color: #fff7f2;
        box-shadow: 0 14px 30px rgba(234, 88, 12, 0.3);
    }

    .lb-btn--accent:hover,
    .lb-btn--accent:focus {
        color: #ffffff;
        box-shadow: 0 18px 34px rgba(234, 88, 12, 0.34);
    }

    .lb-btn--panel {
        border-color: #d6e1ea;
        background: #fbfdff;
        color: #183247;
    }

    .lb-btn--panel:hover,
    .lb-btn--panel:focus {
        background: #ffffff;
        border-color: #c5d5e1;
    }

    .lb-btn--danger {
        border-color: #fecaca;
        background: #fff5f5;
        color: #b42318;
    }

    .lb-btn--danger:hover,
    .lb-btn--danger:focus {
        color: #991b1b;
        background: #fee2e2;
        border-color: #fca5a5;
    }

    .lb-btn--block {
        width: 100%;
    }

    .lb-btn--link {
        min-height: auto;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        color: #b42318;
        font-size: 12px;
        letter-spacing: 0.02em;
        text-transform: none;
        box-shadow: none;
    }

    .lb-btn--link:hover,
    .lb-btn--link:focus {
        color: #7f1d1d;
        box-shadow: none;
        transform: none;
    }

    .lb-device-switcher {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
    }

    .lb-device-toggle {
        min-width: 98px;
    }

    .lb-device-width {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        font-size: 12px;
        color: rgba(248, 250, 252, 0.84);
    }

    .lb-workspace__body {
        position: relative;
        min-height: 720px;
        padding: 1.5rem;
    }

    .lb-workspace__canvas {
        position: relative;
        z-index: 1;
        min-height: 640px;
        padding: 0 4.5rem;
    }

    .lb-canvas-stage {
        width: 100%;
        max-width: 1760px;
        margin: 0 auto;
    }

    .lb-canvas-stage__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .lb-canvas-stage__eyebrow {
        margin-bottom: 0.25rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #64748b;
    }

    .lb-canvas-stage__title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #122033;
    }

    .lb-canvas-stage__desc {
        margin: 0.35rem 0 0;
        max-width: 760px;
        font-size: 14px;
        color: #5f7083;
    }

    .lb-canvas-stage__status {
        display: inline-flex;
        align-items: center;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        border: 1px solid #d6dfe8;
        background: rgba(255, 255, 255, 0.82);
        font-size: 12px;
        color: #425466;
        white-space: nowrap;
    }

    .lb-canvas-viewport {
        position: relative;
        min-height: 620px;
        padding: 2rem;
        border: 1px solid #d6dfe8;
        border-radius: 30px;
        background:
            radial-gradient(circle at top left, rgba(148, 163, 184, 0.18), transparent 32%),
            linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
    }

    .lb-canvas-frame {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        transition: max-width 0.2s ease;
    }

    .lb-canvas-surface {
        min-height: 520px;
        padding: 1rem;
        border: 1px solid #dce4ec;
        border-radius: 26px;
        background: #ffffff;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
    }

    .lb-live-page {
        max-width: var(--lb-page-max-width, 1120px);
        margin: 0 auto;
        padding: 1.5rem;
        border-radius: 28px;
        background: var(--lb-page-background, #f4f7fa);
        color: var(--lb-text-color, #173042);
        font-family: var(--lb-font-body, "Segoe UI", Tahoma, sans-serif);
        box-shadow: 0 24px 56px rgba(15, 23, 42, 0.08);
    }

    .lb-live-page h1,
    .lb-live-page h2,
    .lb-live-page h3,
    .lb-live-page h4 {
        font-family: var(--lb-font-heading, "Segoe UI", Tahoma, sans-serif);
        color: var(--lb-heading-color, #142c3d);
    }

    .lb-live-page__hero {
        margin-bottom: var(--lb-section-gap, 32px);
        padding: 1.5rem;
        border: 1px solid var(--lb-border-color, #dce4ea);
        border-radius: var(--lb-radius-lg, 24px);
        background: var(--lb-hero-background, linear-gradient(135deg, #f4f6f8 0%, #ffffff 60%, #eef3f8 100%));
        box-shadow: var(--lb-shadow-lg, 0 18px 48px rgba(19, 41, 61, 0.10));
    }

    .lb-live-page__kicker,
    .lb-live-section__kicker,
    .lb-live-column__eyebrow,
    .lb-live-node__eyebrow {
        margin-bottom: 0.35rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--lb-text-muted, #64748b);
    }

    .lb-live-page__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .lb-live-page__title {
        margin: 0;
        font-size: clamp(28px, 4vw, var(--lb-hero-title-size, 40px));
        line-height: 1.08;
    }

    .lb-live-page__lead {
        margin: 0.7rem 0 0;
        max-width: 48rem;
        color: var(--lb-text-muted, #5b7282);
        font-size: 15px;
        line-height: 1.55;
    }

    .lb-live-page__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .lb-live-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        border: 1px solid var(--lb-border-color, #dce4ea);
        background: var(--lb-zone-pill-background, rgba(255, 255, 255, 0.9));
        color: var(--lb-zone-pill-color, #335168);
        font-size: 13px;
        line-height: 1.2;
    }

    .lb-live-pill strong {
        color: var(--lb-heading-color, #142c3d);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .lb-live-sections {
        display: grid;
        gap: var(--lb-section-gap, 32px);
    }

    .lb-live-page__empty {
        padding: 1.4rem;
        border: 1px dashed var(--lb-border-color, #cbd5df);
        border-radius: var(--lb-radius-lg, 24px);
        background: var(--lb-surface-soft, #f8fbfd);
        color: var(--lb-text-muted, #5b7282);
    }

    .lb-starter {
        padding: 2rem 1.75rem 2.25rem;
        border: 1px dashed var(--lb-border-color, #cbd5df);
        border-radius: var(--lb-radius-lg, 24px);
        background: var(--lb-surface-soft, #f8fbfd);
        text-align: center;
    }

    .lb-starter__icon {
        font-size: 2.25rem;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .lb-starter__title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--lb-text-color, #1a2636);
        margin: 0 0 0.35rem;
    }

    .lb-starter__desc {
        font-size: 0.8rem;
        color: var(--lb-text-muted, #5b7282);
        margin: 0 0 1.5rem;
        line-height: 1.5;
    }

    .lb-starter__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.625rem;
        margin-bottom: 0.875rem;
    }

    .lb-starter__btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
        padding: 0.8rem 0.625rem;
        border: 1px solid var(--lb-border-color, #cbd5df);
        border-radius: var(--lb-radius-md, 14px);
        background: #ffffff;
        cursor: pointer;
        transition: box-shadow 0.15s, border-color 0.15s;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--lb-text-color, #1a2636);
    }

    .lb-starter__btn:hover {
        border-color: var(--lb-accent-color, #2f7aa1);
        box-shadow: 0 4px 12px rgba(47, 122, 161, 0.12);
    }

    .lb-starter__btn-icon {
        font-size: 1.3rem;
        line-height: 1;
    }

    .lb-starter__hint {
        font-size: 0.75rem;
        color: var(--lb-text-muted, #5b7282);
    }

    .lb-live-section {
        padding: 1.4rem;
        border: 1px solid var(--lb-border-color, #dce4ea);
        border-radius: var(--lb-radius-lg, 22px);
        background: var(--lb-surface-color, #ffffff);
        box-shadow: var(--lb-shadow-md, 0 12px 32px rgba(18, 36, 52, 0.08));
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .lb-live-section--selected,
    .lb-live-column--selected,
    .lb-live-node--selected {
        box-shadow: 0 0 0 2px rgba(47, 122, 161, 0.34), var(--lb-shadow-md, 0 12px 32px rgba(18, 36, 52, 0.08));
    }

    .lb-live-section__topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .lb-live-section__title {
        margin: 0;
        font-size: 24px;
        line-height: 1.15;
    }

    .lb-live-section__meta,
    .lb-live-node__meta,
    .lb-live-column__meta {
        margin-top: 0.35rem;
        color: var(--lb-text-muted, #5b7282);
        font-size: 13px;
        line-height: 1.45;
    }

    .lb-live-columns {
        display: grid;
        gap: 1rem;
    }

    .lb-live-columns--1col {
        grid-template-columns: minmax(0, 1fr);
    }

    .lb-live-columns--2col_equal,
    .lb-live-columns--2col_sidebar_left,
    .lb-live-columns--2col_sidebar_right {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .lb-live-columns--2col_sidebar_left {
        grid-template-columns: minmax(220px, 0.8fr) minmax(0, 1.55fr);
    }

    .lb-live-columns--2col_sidebar_right {
        grid-template-columns: minmax(0, 1.55fr) minmax(220px, 0.8fr);
    }

    .lb-live-columns--3col_equal {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .lb-live-column {
        min-height: 100%;
        padding: 1rem;
        border: 1px solid var(--lb-border-color, #dbe6ee);
        border-radius: var(--lb-radius-md, 18px);
        background: var(--lb-surface-soft, #f8fbfd);
    }

    .lb-live-column__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .lb-live-column__title {
        font-weight: 700;
        color: var(--lb-heading-color, #142c3d);
    }

    .lb-live-column__empty {
        padding: 0.95rem;
        border: 1px dashed var(--lb-border-color, #cfd9e2);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--lb-text-muted, #667b8d);
        font-size: 13px;
    }

    .lb-live-node {
        margin-top: 0.85rem;
        padding: 0.95rem 1rem;
        border: 1px solid var(--lb-card-border, var(--lb-border-color, #dce5ec));
        border-radius: 16px;
        background: var(--lb-card-background, #ffffff);
        box-shadow: var(--lb-card-shadow, 0 8px 20px rgba(22, 38, 52, 0.05));
    }

    .lb-live-node:first-child {
        margin-top: 0;
    }

    .lb-live-node--block {
        border-left: 4px solid var(--lb-accent-color, #2f7aa1);
    }

    .lb-live-node--widget {
        border-left: 4px solid #2a6752;
    }

    .lb-live-node__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .lb-live-node__label {
        font-weight: 700;
        color: var(--lb-heading-color, #173042);
    }

    .lb-live-section.lb-section--container-text .lb-live-section__inner {
        max-width: 760px;
        margin: 0 auto;
    }

    .lb-live-section.lb-section--tone-brand-soft {
        background: var(--lb-accent-soft, #e8f3f8);
    }

    .lb-live-section.lb-section--tone-brand-strong {
        background: var(--lb-accent-color, #2f7aa1);
        color: var(--lb-accent-contrast, #ffffff);
    }

    .lb-live-section.lb-section--tone-brand-strong h2,
    .lb-live-section.lb-section--tone-brand-strong h3,
    .lb-live-section.lb-section--tone-brand-strong .lb-live-column__title,
    .lb-live-section.lb-section--tone-brand-strong .lb-live-node__label,
    .lb-live-section.lb-section--tone-brand-strong .lb-live-pill strong {
        color: var(--lb-accent-contrast, #ffffff);
    }

    .lb-live-section.lb-section--tone-brand-strong .lb-live-section__meta,
    .lb-live-section.lb-section--tone-brand-strong .lb-live-node__meta,
    .lb-live-section.lb-section--tone-brand-strong .lb-live-column__meta {
        color: rgba(255, 255, 255, 0.82);
    }

    .lb-live-section.lb-section--tone-brand-strong .lb-live-column,
    .lb-live-section.lb-section--tone-brand-strong .lb-live-node,
    .lb-live-section.lb-section--tone-contrast .lb-live-column,
    .lb-live-section.lb-section--tone-contrast .lb-live-node,
    .lb-live-section.lb-section--tone-inverse .lb-live-column,
    .lb-live-section.lb-section--tone-inverse .lb-live-node {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.16);
    }

    .lb-live-section.lb-section--tone-muted {
        background: var(--lb-surface-muted, #eef3f8);
    }

    .lb-live-section.lb-section--tone-contrast,
    .lb-live-section.lb-section--tone-inverse {
        background: var(--lb-contrast-surface, #173042);
        color: var(--lb-contrast-text, #f7fbff);
    }

    .lb-live-section.lb-section--tone-contrast h2,
    .lb-live-section.lb-section--tone-contrast h3,
    .lb-live-section.lb-section--tone-contrast .lb-live-column__title,
    .lb-live-section.lb-section--tone-contrast .lb-live-node__label,
    .lb-live-section.lb-section--tone-inverse h2,
    .lb-live-section.lb-section--tone-inverse h3,
    .lb-live-section.lb-section--tone-inverse .lb-live-column__title,
    .lb-live-section.lb-section--tone-inverse .lb-live-node__label,
    .lb-live-section.lb-section--tone-contrast .lb-live-pill strong,
    .lb-live-section.lb-section--tone-inverse .lb-live-pill strong {
        color: var(--lb-contrast-text, #f7fbff);
    }

    .lb-live-section.lb-section--tone-contrast .lb-live-section__meta,
    .lb-live-section.lb-section--tone-contrast .lb-live-node__meta,
    .lb-live-section.lb-section--tone-contrast .lb-live-column__meta,
    .lb-live-section.lb-section--tone-inverse .lb-live-section__meta,
    .lb-live-section.lb-section--tone-inverse .lb-live-node__meta,
    .lb-live-section.lb-section--tone-inverse .lb-live-column__meta {
        color: rgba(247, 251, 255, 0.78);
    }

    .lb-live-section.lb-section--style-hero,
    .lb-live-section.lb-section--style-hero-split,
    .lb-live-section.lb-section--style-cta {
        background: var(--lb-hero-background, linear-gradient(135deg, #f4f6f8 0%, #ffffff 60%, #eef3f8 100%));
    }

    .lb-live-section.lb-section--spacing-sm {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .lb-live-section.lb-section--spacing-lg {
        padding-top: 1.9rem;
        padding-bottom: 1.9rem;
    }

    .lb-live-section.lb-section--spacing-xl {
        padding-top: 2.5rem;
        padding-bottom: 2.5rem;
    }

    .lb-drawer {
        position: absolute;
        top: 1.5rem;
        bottom: 1.5rem;
        z-index: 25;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(210, 219, 228, 0.95);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
        backdrop-filter: blur(14px);
        transition: transform 0.25s ease, opacity 0.25s ease;
        overflow: hidden;
    }

    .lb-drawer--left {
        left: 1.5rem;
        width: 340px;
        transform: translateX(calc(-100% - 52px));
        opacity: 0.98;
    }

    .lb-drawer--right {
        right: 1.5rem;
        width: 390px;
        transform: translateX(calc(100% + 52px));
        opacity: 0.98;
    }

    .lb-workspace--library-open .lb-drawer--left,
    .lb-workspace--inspector-open .lb-drawer--right {
        transform: translateX(0);
    }

    .lb-drawer__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1.1rem 1.15rem 0.85rem;
        border-bottom: 1px solid #edf2f7;
    }

    .lb-drawer__title {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #132236;
    }

    .lb-drawer__desc {
        margin: 0.3rem 0 0;
        font-size: 13px;
        line-height: 1.45;
        color: #617286;
    }

    .lb-drawer__body {
        flex: 1 1 auto;
        padding: 1rem 1.15rem 1.2rem;
        overflow: auto;
    }

    .lb-panel-block {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid #e7edf3;
        border-radius: 18px;
        background: #fbfdff;
    }

    .lb-panel-block:last-child {
        margin-bottom: 0;
    }

    .lb-panel-block__title {
        margin: 0 0 0.75rem;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #607186;
    }

    .lb-library-tabs {
        display: grid;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .lb-library-tab {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: flex-start;
        padding: 0.8rem 0.95rem;
        border: 1px solid #dce5ed;
        border-radius: 16px;
        background: #ffffff;
        color: #425466;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-align: left;
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }

    .lb-library-tab:hover,
    .lb-library-tab:focus {
        color: #132236;
        border-color: #c4d3df;
        background: #ffffff;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .lb-library-tab.active {
        background: #132236;
        color: #ffffff;
        border-color: #132236;
        box-shadow: 0 14px 30px rgba(19, 34, 54, 0.18);
    }

    .lb-library-card {
        display: block;
        width: 100%;
        margin-bottom: 0.65rem;
        padding: 0.95rem 1rem;
        border: 1px solid #dfe7ee;
        border-radius: 18px;
        background: #ffffff;
        color: #132236;
        text-align: left;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .lb-library-card:last-child {
        margin-bottom: 0;
    }

    .lb-library-card:hover,
    .lb-library-card:focus {
        border-color: #c8d6e1;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.09);
        transform: translateY(-1px);
        text-decoration: none;
    }

    .lb-library-card__row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
    }

    .lb-library-card__title {
        font-weight: 700;
        color: #132236;
    }

    .lb-library-card__meta {
        margin-top: 0.45rem;
        font-size: 13px;
        line-height: 1.45;
        color: #637689;
    }

    .lb-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.62rem;
        border-radius: 999px;
        background: #eef4f8;
        color: #375167;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lb-field {
        margin-bottom: 0.8rem;
    }

    .lb-field__label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 12px;
        font-weight: 700;
        color: #607186;
    }

    .lb-control {
        width: 100%;
        min-height: 42px;
        padding: 0.7rem 0.85rem;
        border: 1px solid #d4dee7;
        border-radius: 14px;
        background: #ffffff;
        color: #173042;
        font-size: 14px;
        line-height: 1.4;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    textarea.lb-control {
        min-height: 96px;
        resize: vertical;
    }

    .lb-control:focus {
        outline: 0;
        border-color: #7aa7c4;
        box-shadow: 0 0 0 0.2rem rgba(47, 122, 161, 0.14);
    }

    .lb-widget-placeholder,
    .lb-shell-summary,
    .lb-version-card,
    .lb-semantic-card {
        border: 1px solid #dfe7ee;
        border-radius: 18px;
        background: #f8fbff;
    }

    .lb-widget-placeholder,
    .lb-semantic-card,
    .lb-shell-summary {
        padding: 0.9rem 1rem;
    }

    .lb-widget-placeholder {
        color: #5f7284;
    }

    .lb-shell-summary__title {
        margin-bottom: 0.35rem;
        font-size: 14px;
        font-weight: 700;
        color: #132236;
    }

    .lb-shell-map {
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid #d6dfe8;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
    }

    .lb-shell-map__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }

    .lb-shell-map__eyebrow {
        margin-bottom: 0.25rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #64748b;
    }

    .lb-shell-map__title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #132236;
    }

    .lb-shell-map__copy,
    .lb-shell-map__meta {
        margin-top: 0.35rem;
        font-size: 13px;
        line-height: 1.5;
        color: #617286;
    }

    .lb-shell-map__legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.9rem;
    }

    .lb-shell-map__legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 12px;
        color: #516376;
    }

    .lb-shell-map__legend-swatch {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #cdd9e3;
    }

    .lb-shell-map__legend-swatch--active {
        background: #2f7aa1;
    }

    .lb-shell-map__legend-swatch--page {
        background: #ea580c;
    }

    .lb-shell-map__grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .lb-shell-slot {
        position: relative;
        min-height: 88px;
        padding: 0.85rem 0.9rem;
        border: 1px solid #dbe5ed;
        border-radius: 18px;
        background: #f8fbfe;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .lb-shell-slot--wide {
        grid-column: span 12;
    }

    .lb-shell-slot--half {
        grid-column: span 6;
    }

    .lb-shell-slot--third {
        grid-column: span 4;
    }

    .lb-shell-slot--active {
        border-color: #b9d0df;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .lb-shell-slot--page {
        border-color: rgba(234, 88, 12, 0.42);
        box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.14);
    }

    .lb-shell-slot--muted {
        opacity: 0.62;
        background: #f3f6f9;
    }

    .lb-shell-slot__top {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .lb-shell-slot__title {
        margin: 0 0 0.2rem;
        font-size: 14px;
        font-weight: 700;
        color: #173042;
    }

    .lb-shell-slot__key {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7a8da0;
    }

    .lb-shell-slot__state {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.55rem;
        border-radius: 999px;
        background: #e8f1f7;
        color: #2c5a78;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lb-shell-slot--page .lb-shell-slot__state {
        background: #fff0e8;
        color: #b45309;
    }

    .lb-shell-slot__copy {
        margin-top: 0.55rem;
        font-size: 12px;
        line-height: 1.45;
        color: #617286;
    }

    .lb-token-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.55rem;
    }

    .lb-version-card {
        margin-bottom: 0.65rem;
        padding: 0.9rem 1rem;
    }

    .lb-version-card:last-child {
        margin-bottom: 0;
    }

    .lb-version-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.35rem;
    }

    .lb-version-card__id {
        font-size: 14px;
        font-weight: 700;
        color: #132236;
    }

    .lb-version-card__meta,
    .lb-version-card__note {
        font-size: 13px;
        color: #617286;
    }

    .lb-semantic-card__title {
        margin-bottom: 0.25rem;
        font-size: 13px;
        font-weight: 700;
        color: #132236;
    }

    .lb-semantic-card__desc {
        font-size: 13px;
        color: #617286;
        line-height: 1.45;
    }

    .lb-drawer-handle {
        position: absolute;
        top: 6.75rem;
        z-index: 30;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        min-height: 128px;
        padding: 0.65rem 0.35rem;
        border: 1px solid #d6dfe8;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.98);
        color: #132236;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transition: left 0.25s ease, right 0.25s ease, background-color 0.25s ease;
    }

    .lb-drawer-handle:hover,
    .lb-drawer-handle:focus {
        background: #ffffff;
        text-decoration: none;
    }

    .lb-drawer-handle--left {
        left: 0.7rem;
    }

    .lb-workspace--library-open .lb-drawer-handle--left {
        left: 21.3rem;
    }

    .lb-drawer-handle--right {
        right: 0.7rem;
    }

    .lb-workspace--inspector-open .lb-drawer-handle--right {
        right: 24.4rem;
    }

    .lb-section[draggable="true"],
    .lb-node[draggable="true"] {
        cursor: move;
    }

    .lb-dragging {
        opacity: 0.45;
    }

    .lb-drop-target {
        box-shadow: 0 0 0 2px rgba(23, 162, 184, 0.45) inset;
    }

    .lb-muted-device {
        opacity: 0.55;
    }

    .lb-help {
        display: inline-flex;
        align-items: center;
        margin-left: 6px;
        color: #6c757d;
        cursor: help;
    }

    .lb-help i {
        font-size: 13px;
    }

    .lb-library-caption {
        line-height: 1.25;
    }

    #lb-widget-form form {
        margin-bottom: 0;
    }

    #lb-widget-form .form-control,
    #lb-widget-form .custom-select,
    #lb-widget-form .custom-file-label {
        min-height: 42px;
        border-radius: 14px;
        border-color: #d4dee7;
        box-shadow: none;
    }

    #lb-widget-form .form-control:focus,
    #lb-widget-form .custom-select:focus {
        border-color: #7aa7c4;
        box-shadow: 0 0 0 0.2rem rgba(47, 122, 161, 0.14);
    }

    #lb-widget-form .btn {
        border-radius: 999px;
    }

    @media (max-width: 1399.98px) {
        .lb-workspace__canvas {
            padding-left: 3.5rem;
            padding-right: 3.5rem;
        }
    }

    @media (max-width: 1199.98px) {
        .lb-workspace__topbar {
            grid-template-columns: 1fr;
        }

        .lb-topbar__center,
        .lb-topbar__end {
            justify-content: flex-start;
        }

        .lb-workspace__body {
            padding: 1rem;
        }

        .lb-workspace__canvas {
            padding: 0 1.5rem;
        }

        .lb-drawer--left,
        .lb-drawer--right {
            top: 1rem;
            bottom: 1rem;
            width: calc(100% - 2rem);
        }

        .lb-drawer--left {
            left: 1rem;
            transform: translateX(calc(-100% - 24px));
        }

        .lb-drawer--right {
            right: 1rem;
            transform: translateX(calc(100% + 24px));
        }

        .lb-drawer-handle {
            top: auto;
            bottom: 1rem;
            min-height: 44px;
            width: auto;
            padding: 0.7rem 0.9rem;
            writing-mode: horizontal-tb;
        }

        .lb-drawer-handle--left {
            left: 1rem;
        }

        .lb-workspace--library-open .lb-drawer-handle--left {
            left: 1rem;
        }

        .lb-drawer-handle--right {
            right: 1rem;
        }

        .lb-workspace--inspector-open .lb-drawer-handle--right {
            right: 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .lb-shell-map__header {
            display: block;
        }

        .lb-shell-map__grid {
            grid-template-columns: 1fr;
        }

        .lb-shell-slot--wide,
        .lb-shell-slot--half,
        .lb-shell-slot--third {
            grid-column: auto;
        }

        .lb-workspace__canvas {
            padding: 0 0.25rem;
        }

        .lb-canvas-stage__header {
            display: block;
        }

        .lb-canvas-stage__status {
            margin-top: 0.75rem;
        }

        .lb-canvas-viewport {
            padding: 0.85rem;
            border-radius: 22px;
        }

        .lb-canvas-surface {
            padding: 1rem;
            border-radius: 18px;
        }
    }
</style>
<div class="lb-workspace lb-workspace--library-open lb-workspace--inspector-open" id="lb-workspace">
    <div class="lb-workspace__topbar">
        <div class="lb-topbar__start">
            <a class="lb-btn lb-btn--topbar" href="<?php html($screen['api']['pages_url'] ?? $this->href_to('pages')); ?>">К макетам</a>
            <div class="lb-topbar__page">
                <div class="lb-topbar__eyebrow">Редактор макета</div>
                <h1 class="lb-topbar__title"><?php html($page['title']); ?></h1>
                <div class="lb-topbar__meta"><span id="lb-page-meta">Макет <code><?php html($page['key']); ?></code> | Шаблон и каркас выбираются справа в инспекторе</span></div>
            </div>
        </div>
        <div class="lb-topbar__center">
            <div class="btn-group lb-device-switcher" role="group" aria-label="Устройства">
            <?php foreach ($screen['devices'] as $index => $device) { ?>
                <button type="button" class="lb-btn lb-btn--topbar lb-device-toggle<?php if ($index === 0) { ?> is-active<?php } ?>" data-device="<?php html($device['key']); ?>"><?php html($device['title']); ?></button>
            <?php } ?>
            </div>
            <span class="lb-device-width">Viewport: <span class="ml-1" id="lb-device-width-label"><?php echo !empty($screen['devices'][0]['viewport_width']) ? (int) $screen['devices'][0]['viewport_width'] . 'px' : 'Авто'; ?></span></span>
        </div>
        <div class="lb-topbar__end">
            <div class="lb-topbar__save">Последнее обновление: <span id="lb-updated-at"><?php html($page['updated_at']); ?></span></div>
            <button type="button" class="lb-btn lb-btn--topbar" data-drawer-toggle="library">Библиотека</button>
            <button type="button" class="lb-btn lb-btn--topbar" data-drawer-toggle="inspector">Инспектор</button>
            <button type="button" class="lb-btn lb-btn--topbar" id="lb-add-section">Добавить секцию</button>
                <a class="lb-btn lb-btn--topbar" href="<?php html($screen['design_url']); ?>">Глобальные стили</a>
                <button type="button" class="lb-btn lb-btn--topbar" id="lb-edit-page-theme">Стиль на холсте</button>
            <a class="lb-btn lb-btn--topbar" href="<?php html($screen['preview_url']); ?>" target="_blank" rel="noopener">Предпросмотр</a>
            <button type="button" class="lb-btn lb-btn--accent" id="lb-save-canvas">Сохранить</button>
        </div>
    </div>

    <div class="lb-workspace__body">
        <button type="button" class="lb-drawer-handle lb-drawer-handle--left" data-drawer-toggle="library">Библиотека</button>
        <aside class="lb-drawer lb-drawer--left" id="lb-library-drawer">
            <div class="lb-drawer__head">
                <div>
                    <h2 class="lb-drawer__title">Библиотека и навигация</h2>
                    <p class="lb-drawer__desc">Добавляй секции, блоки и виджеты без потери ширины canvas. Панель можно прятать за край окна.</p>
                </div>
                <button type="button" class="lb-btn lb-btn--panel" data-drawer-toggle="library">Скрыть</button>
            </div>
            <div class="lb-drawer__body">
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Что можно сделать</div>
                    <div class="small text-muted">Сначала выбери секцию, колонку или страницу. Затем добавляй готовые секции, блоки и системные виджеты.</div>
                </div>
                <div class="lb-panel-block">
                    <div class="lb-library-tabs">
                        <?php foreach ($screen['left_tabs'] as $index => $tab) { ?>
                            <button type="button" class="lb-library-tab<?php if ($index === 0) { ?> active<?php } ?>" data-tab="<?php html($tab['key']); ?>"><?php html($tab['title']); ?></button>
                        <?php } ?>
                    </div>
                    <div id="lb-sections-library" class="lb-library-panel">
                        <div class="small text-muted mb-2">Готовые секции для быстрого старта страницы и адаптации под устройства</div>
                        <div class="list-group list-group-flush" id="lb-section-list"></div>
                    </div>
                    <div id="lb-blocks-library" class="lb-library-panel d-none">
                        <div class="small text-muted mb-2">Готовые блоки для быстрого старта</div>
                        <div class="list-group list-group-flush" id="lb-block-list"></div>
                    </div>
                    <div id="lb-widgets-library" class="lb-library-panel d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted">Системные виджеты InstantCMS</div>
                            <button type="button" class="lb-btn lb-btn--panel" id="lb-reload-widgets">Обновить</button>
                        </div>
                        <div id="lb-widget-list" class="small"></div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lb-workspace__canvas">
            <div class="lb-canvas-stage">
                <div class="lb-canvas-stage__header">
                    <div>
                        <div class="lb-canvas-stage__eyebrow">Главный визуальный экран</div>
                        <h2 class="lb-canvas-stage__title">Живой холст страницы</h2>
                        <p class="lb-canvas-stage__desc">Собирайте страницу прямо здесь: выбирайте шаблон, секции и блоки, меняйте настройки справа и сразу видьте результат на холсте.</p>
                    </div>
                    <div class="lb-canvas-stage__status" id="lb-canvas-status"><?php if ($screen['schema_installed']) { ?>Страница сохранится в базе<?php } else { ?>Временный черновик без базы<?php } ?></div>
                </div>
                <div id="lb-shell-map" style="display:none"></div>
                <div class="lb-canvas-viewport">
                    <div class="lb-canvas-frame" id="lb-canvas-frame">
                        <div class="lb-canvas-surface" id="lb-canvas-root"></div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="lb-drawer-handle lb-drawer-handle--right" data-drawer-toggle="inspector">Инспектор</button>
        <aside class="lb-drawer lb-drawer--right" id="lb-inspector-drawer">
            <div class="lb-drawer__head">
                <div>
                    <h2 class="lb-drawer__title">Инспектор</h2>
                    <p class="lb-drawer__desc">Настройки страницы, секции, колонки или элемента. Основная работа со страницей должна происходить здесь, прямо рядом с холстом.</p>
                </div>
                <button type="button" class="lb-btn lb-btn--panel" data-drawer-toggle="inspector">Скрыть</button>
            </div>
            <div class="lb-drawer__body">
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Статус страницы</div>
                    <p class="mb-2"><strong>Виджетов на странице:</strong> <span id="lb-widget-count"><?php echo count($screen['widget_nodes']); ?></span></p>
                    <label class="small text-muted d-block mb-1">Комментарий версии</label>
                    <input type="text" class="lb-control" id="lb-version-note" placeholder="Например: перестроил первый экран и боковую колонку">
                    <div class="mt-3">
                        <button type="button" class="lb-btn lb-btn--accent lb-btn--block" id="lb-save-canvas-inspector">Сохранить</button>
                    </div>
                </div>
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Выделение</div>
                    <div id="lb-selection-summary" class="small">Ничего не выбрано</div>
                    <div id="lb-selection-controls" class="mt-3"></div>
                </div>
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Настройки виджета</div>
                    <div id="lb-widget-form" class="lb-widget-placeholder small">Выберите системный виджет на макете, чтобы открыть его штатные настройки.</div>
                </div>
                <div class="lb-panel-block">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="lb-panel-block__title mb-0">История версий</div>
                        <button type="button" class="lb-btn lb-btn--panel" id="lb-refresh-versions">Обновить</button>
                    </div>
                    <div id="lb-versions-list" class="small"></div>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php ob_start(); ?>
<script>
    (function () {
        const state = <?php echo json_encode($canvas_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const deviceTitles = <?php echo json_encode($device_titles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const pageModeTitles = <?php echo json_encode($page_mode_titles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const pageStatusTitles = <?php echo json_encode($page_status_titles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const blockPresets = (Array.isArray(state.screen.block_catalog) && state.screen.block_catalog.length ? state.screen.block_catalog : [
            {
                key: 'core.hero-heading',
                title: 'Главный экран с заголовком',
                description: 'Крупный первый блок страницы с основным сообщением и подзаголовком.'
            },
            {
                key: 'core.hero-actions',
                title: 'Главный экран с кнопками',
                description: 'Первый экран с призывом к действию и заметными кнопками.'
            },
            {
                key: 'core.cards-grid',
                title: 'Сетка карточек',
                description: 'Подходит для преимуществ, услуг, тарифов или подборок.'
            },
            {
                key: 'core.feature-list',
                title: 'Список преимуществ',
                description: 'Короткий блок с причинами выбрать предложение.'
            },
            {
                key: 'ads.category-header',
                title: 'Шапка категории объявлений',
                description: 'Верхняя часть страницы категории с акцентом на заголовок и фильтры.'
            },
            {
                key: 'ads.filter-bar',
                title: 'Панель фильтров',
                description: 'Блок для фильтров и быстрых уточнений списка на странице категории.'
            },
            {
                key: 'profile.cover-hero',
                title: 'Обложка профиля',
                description: 'Широкий блок для профиля пользователя или компании.'
            },
            {
                key: 'profile.quick-stats',
                title: 'Короткая статистика профиля',
                description: 'Компактный блок с числами, показателями и важными фактами профиля.'
            }
        ]).map(function (block) {
            return Object.assign({default_label: '', summary: '', fields: [], defaults: {}}, block || {});
        });
        const blockPresetMap = blockPresets.reduce(function (map, block) {
            map[block.key] = block;
            return map;
        }, {});
        const layoutOptions = [
            {value: '1col', title: 'Одна колонка', hint: 'Один широкий столбец на всю ширину секции.'},
            {value: '2col_equal', title: 'Две равные колонки', hint: 'Подходит для текста рядом с изображением или формы рядом с описанием.'},
            {value: '2col_sidebar_left', title: 'Узкая колонка слева', hint: 'Слева узкий вспомогательный блок, справа основное содержимое.'},
            {value: '2col_sidebar_right', title: 'Узкая колонка справа', hint: 'Справа узкий вспомогательный блок, слева основное содержимое.'},
            {value: '3col_equal', title: 'Три равные колонки', hint: 'Подходит для карточек, этапов или трёх преимуществ.'}
        ];
        const columnWidthOptions = [
            {value: 'auto', title: 'Авто'},
            {value: '12', title: 'Во всю ширину'},
            {value: '8', title: 'Широкая'},
            {value: '6', title: 'Половина'},
            {value: '4', title: 'Узкая'},
            {value: '3', title: 'Очень узкая'}
        ];
        const alignOptions = [
            {value: 'stretch', title: 'Растянуть'},
            {value: 'start', title: 'По верхнему краю'},
            {value: 'center', title: 'По центру'},
            {value: 'end', title: 'По нижнему краю'}
        ];
        const pageThemeOptions = state.screen.theme_option_catalog || {
            template_preset: [
                {value: 'nordic_classic', title: 'Классический Nordic'},
                {value: 'nordic_editorial', title: 'Nordic Editorial'},
                {value: 'nordic_catalog', title: 'Nordic Catalog'},
                {value: 'nordic_warm_market', title: 'Nordic Warm Market'},
                {value: 'nordic_compact', title: 'Nordic Compact'},
                {value: 'nm_landing', title: 'NM Landing'}
            ],
            global_style_preset: [
                {value: 'nordic_balanced', title: 'Сбалансированный Нордик'},
                {value: 'nordic_contrast', title: 'Контрастный Нордик'},
                {value: 'nordic_editorial', title: 'Редакционный Нордик'},
                {value: 'nordic_catalog', title: 'Каталоговый Нордик'}
            ],
            color_preset: [
                {value: 'nordic_day', title: 'Дневная палитра'},
                {value: 'slate_contrast', title: 'Сланцевый контраст'},
                {value: 'forest_accent', title: 'Лесной акцент'}
            ],
            typography_preset: [
                {value: 'editorial', title: 'Редакционная'},
                {value: 'neutral', title: 'Нейтральная'},
                {value: 'compact', title: 'Компактная'}
            ],
            container_preset: [
                {value: 'text', title: 'Узкий текстовый'},
                {value: 'standard', title: 'Стандартный'},
                {value: 'wide', title: 'Широкий'},
                {value: 'full', title: 'Во всю ширину'}
            ],
            button_preset: [
                {value: 'soft_accent', title: 'Мягкий акцент'},
                {value: 'solid_brand', title: 'Плотный брендовый'},
                {value: 'ghost', title: 'Прозрачный'}
            ],
            card_preset: [
                {value: 'quiet', title: 'Спокойные'},
                {value: 'raised', title: 'Поднятые'},
                {value: 'outline', title: 'С обводкой'}
            ],
            section_spacing: [
                {value: 'compact', title: 'Компактный'},
                {value: 'comfortable', title: 'Комфортный'},
                {value: 'airy', title: 'Воздушный'}
            ]
        };
        const themeRuntimeCatalog = <?php echo json_encode($theme_runtime_catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?> || {};
        const pageShellScreen = state.screen.page_shell || {};
        const templatePresetCatalog = state.screen.template_preset_catalog || [];
        const templatePresetList = Array.isArray(templatePresetCatalog)
            ? templatePresetCatalog
            : Object.keys(templatePresetCatalog).map(function (key) {
                return Object.assign({key: key}, templatePresetCatalog[key] || {});
            });
        const templatePresetMap = templatePresetList.reduce(function (map, preset) {
            if (preset && preset.key) {
                map[preset.key] = preset;
            }
            return map;
        }, {});
        const sectionTypeOptions = [
            {value: 'hero', title: 'Первый экран'},
            {value: 'content', title: 'Контент'},
            {value: 'proof', title: 'Доверие'},
            {value: 'cta', title: 'Действие'},
            {value: 'faq', title: 'Вопросы и ответы'},
            {value: 'catalog', title: 'Каталог'},
            {value: 'contacts', title: 'Контакты'},
            {value: 'custom', title: 'Произвольная'}
        ];
        const sectionStyleOptions = [
            {value: 'hero', title: 'Первый экран'},
            {value: 'hero-split', title: 'Первый экран с разделением'},
            {value: 'cards', title: 'Карточки'},
            {value: 'feature-list', title: 'Список преимуществ'},
            {value: 'logos', title: 'Логотипы'},
            {value: 'stats', title: 'Статистика'},
            {value: 'faq', title: 'FAQ'},
            {value: 'cta', title: 'Призыв к действию'},
            {value: 'content', title: 'Контент'},
            {value: 'catalog', title: 'Каталог'},
            {value: 'profile', title: 'Профиль'},
            {value: 'custom', title: 'Произвольный'}
        ];
        const backgroundToneOptions = [
            {value: 'base', title: 'Базовый'},
            {value: 'brand-soft', title: 'Мягкий брендовый'},
            {value: 'brand-strong', title: 'Плотный брендовый'},
            {value: 'muted', title: 'Приглушенный'},
            {value: 'contrast', title: 'Контрастный'},
            {value: 'inverse', title: 'Инверсный'}
        ];
        const spacingPresetOptions = [
            {value: 'sm', title: 'Компактный'},
            {value: 'md', title: 'Средний'},
            {value: 'lg', title: 'Свободный'},
            {value: 'xl', title: 'Воздушный'}
        ];

        state.widgetsCatalog = {};
        state.deviceKeys = Array.isArray(state.screen.device_keys) && state.screen.device_keys.length
            ? state.screen.device_keys.slice()
            : (Array.isArray(state.screen.devices) ? state.screen.devices.map(function (device) {
                return typeof device === 'string' ? device : device.key;
            }).filter(Boolean) : ['desktop', 'mobile']);
        state.deviceMap = Array.isArray(state.screen.devices) ? state.screen.devices.reduce(function (map, device) {
            if (typeof device === 'string') {
                map[device] = {key: device, title: deviceTitles[device] || device, canvas_width: '100%'};
            } else if (device && device.key) {
                map[device.key] = device;
            }
            return map;
        }, {}) : {};
        state.activeDevice = state.deviceKeys[0] || 'desktop';
        state.selection = null;
        state.drag = null;
        state.schema = normalizeSchema(state.schema || {sections: []});
        state.ui = loadWorkspaceUIState();

        const workspace = document.getElementById('lb-workspace');
        const canvasRoot = document.getElementById('lb-canvas-root');
        const canvasFrame = document.getElementById('lb-canvas-frame');
        const blockList = document.getElementById('lb-block-list');
        const sectionList = document.getElementById('lb-section-list');
        const widgetList = document.getElementById('lb-widget-list');
        const widgetForm = document.getElementById('lb-widget-form');
        const versionsList = document.getElementById('lb-versions-list');
        const selectionSummary = document.getElementById('lb-selection-summary');
        const selectionControls = document.getElementById('lb-selection-controls');
        const widgetCount = document.getElementById('lb-widget-count');
        const updatedAt = document.getElementById('lb-updated-at');
        const versionNote = document.getElementById('lb-version-note');
        const pageMeta = document.getElementById('lb-page-meta');
        const canvasStatus = document.getElementById('lb-canvas-status');
        const shellMap = document.getElementById('lb-shell-map');
        const deviceWidthLabel = document.getElementById('lb-device-width-label');
        const pageThemeButton = document.getElementById('lb-edit-page-theme');
        const drawerToggleButtons = document.querySelectorAll('[data-drawer-toggle]');

        const baseCanvasStatusText = canvasStatus ? String(canvasStatus.textContent || '') : '';
        let isCanvasDirty = false;
        let lastSavedAt = (state.page && state.page.updated_at) ? String(state.page.updated_at) : '';

        function setCanvasDirty(value) {
            isCanvasDirty = !!value;
            if (!canvasStatus) {
                return;
            }

            if (isCanvasDirty) {
                canvasStatus.textContent = 'Есть несохранённые изменения — нажмите «Сохранить»';
                return;
            }

            if (lastSavedAt) {
                canvasStatus.textContent = 'Изменения сохранены (' + lastSavedAt + ')';
                return;
            }

            canvasStatus.textContent = baseCanvasStatusText;
        }

        window.addEventListener('beforeunload', function (event) {
            if (!isCanvasDirty) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });

        function sanitizeThemeToken(value) {
            return String(value || '')
                .toLowerCase()
                .replace(/[^a-z0-9_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function getNormalizedPageTheme(theme) {
            const defaults = Object.assign({}, themeRuntimeCatalog.defaults || {}, state.screen.theme_defaults || {});
            const normalized = Object.assign({}, defaults, theme || {});

            Object.keys(themeRuntimeCatalog.defaults || {}).forEach(function (key) {
                if (!normalized[key]) {
                    normalized[key] = themeRuntimeCatalog.defaults[key];
                }
            });

            return normalized;
        }

        function getThemeVars(theme) {
            const catalog = themeRuntimeCatalog || {};

            return Object.assign({}, catalog.base_vars || {}, {
                '--lb-page-max-width': ((catalog.container_presets || {})[theme.container_preset]) || '1120px',
                '--lb-section-gap': ((catalog.section_spacing || {})[theme.section_spacing]) || '32px'
            }, ((catalog.global_style_presets || {})[theme.global_style_preset]) || {}, ((catalog.color_presets || {})[theme.color_preset]) || {}, ((catalog.typography_presets || {})[theme.typography_preset]) || {}, ((catalog.button_presets || {})[theme.button_preset]) || {}, ((catalog.card_presets || {})[theme.card_preset]) || {});
        }

        function renderThemeVars(vars) {
            return Object.keys(vars || {}).reduce(function (parts, key) {
                if (vars[key] === '' || vars[key] === null || typeof vars[key] === 'undefined') {
                    return parts;
                }

                parts.push(key + ':' + vars[key]);
                return parts;
            }, []).join(';');
        }

        function getCurrentPageTheme() {
            return getNormalizedPageTheme(state.schema.theme || {});
        }

        function getTemplatePresetDefinition(key) {
            if (!key) {
                return null;
            }

            return templatePresetMap[key] || null;
        }

        function getCurrentTemplatePresetKey() {
            const theme = getCurrentPageTheme();
            return String(theme.template_preset || state.screen.theme_defaults.template_preset || '');
        }

        function getCurrentTemplatePresetTitle() {
            const key = getCurrentTemplatePresetKey();
            const preset = getTemplatePresetDefinition(key);

            if (preset && preset.title) {
                return preset.title;
            }

            return getOptionTitle(pageThemeOptions.template_preset || [], key) || 'Без шаблона';
        }

        function resolveTemplatePresetRouteVariantKey() {
            const preset = getTemplatePresetDefinition(getCurrentTemplatePresetKey());
            const routeVariants = preset && preset.route_variants && typeof preset.route_variants === 'object'
                ? preset.route_variants
                : {};

            if (state.page.key === 'homepage' && routeVariants.homepage) {
                return String(routeVariants.homepage);
            }

            if (state.page.adapter_key === 'content_category_generic' && routeVariants.category) {
                return String(routeVariants.category);
            }

            if (state.page.adapter_key === 'user_profile' && routeVariants.profile) {
                return String(routeVariants.profile);
            }

            if (state.page.mode === 'full_takeover' && routeVariants.landing) {
                return String(routeVariants.landing);
            }

            return String(routeVariants.site || '');
        }

        function syncTemplatePresetLayoutTemplate() {
            state.schema.layout = state.schema.layout || {shell_variant: '', content_slot: 'content_body'};

            const preset = getTemplatePresetDefinition(getCurrentTemplatePresetKey());
            if (preset && preset.preview_template) {
                state.schema.layout.template = String(preset.preview_template);
            }
        }

        function getSectionPresentation(section) {
            const theme = getCurrentPageTheme();
            const classes = [
                'lb-section--style-' + sanitizeThemeToken(section.style_preset || 'content'),
                'lb-section--tone-' + sanitizeThemeToken(section.background_tone || 'base'),
                'lb-section--container-' + sanitizeThemeToken(section.container_preset || theme.container_preset || 'standard'),
                'lb-section--spacing-' + sanitizeThemeToken(section.spacing_preset || 'md')
            ];

            if (section.settings && section.settings.css_class) {
                classes.push(section.settings.css_class);
            }

            return {
                className: classes.filter(Boolean).join(' ')
            };
        }

        function getSectionLayoutClass(layout) {
            return 'lb-live-columns--' + sanitizeThemeToken(layout || '1col');
        }

        function defaultVisibility() {
            const visibility = {};
            state.deviceKeys.forEach(function (device) {
                visibility[device] = true;
            });
            return visibility;
        }

        function defaultColumnWidth() {
            const width = {};
            state.deviceKeys.forEach(function (device) {
                width[device] = 'auto';
            });
            return width;
        }

        function normalizeSchema(schema) {
            const next = Object.assign({sections: [], theme: {}, layout: {}}, schema || {});
            next.theme = Object.assign({}, state.screen.theme_defaults || {}, next.theme || {});
            next.layout = Object.assign({shell_variant: '', content_slot: 'content_body'}, next.layout || {});
            next.sections = Array.isArray(next.sections) ? next.sections.map(function (section, sectionIndex) {
                return normalizeSection(section, sectionIndex);
            }) : [];
            return next;
        }

        function normalizeSection(section, sectionIndex) {
            const next = Object.assign({}, section || {});
            next.uid = next.uid || uid('section');
            next.title = next.title || ('Секция ' + ((sectionIndex || 0) + 1));
            next.layout = next.layout || '1col';
            next.section_type = next.section_type || 'content';
            next.style_preset = next.style_preset || 'content';
            next.background_tone = next.background_tone || 'base';
            next.container_preset = next.container_preset || 'standard';
            next.spacing_preset = next.spacing_preset || 'md';
            next.visibility = Object.assign(defaultVisibility(), next.visibility || {});
            next.settings = Object.assign({background_class: '', padding: 'md', css_class: ''}, next.settings || {});
            next.columns = Array.isArray(next.columns) ? next.columns.map(function (column, columnIndex) {
                return normalizeColumn(column, next.uid, columnIndex);
            }) : [];
            syncSectionColumnsWithLayout(next);
            return next;
        }

        function normalizeColumn(column, sectionUid, columnIndex) {
            const next = Object.assign({}, column || {});
            next.uid = next.uid || (sectionUid + '-column-' + (columnIndex + 1));
            next.title = next.title || ('Колонка ' + (columnIndex + 1));
            next.visibility = Object.assign(defaultVisibility(), next.visibility || {});
            next.width = Object.assign(defaultColumnWidth(), next.width || {});
            next.settings = Object.assign({align: 'stretch', css_class: ''}, next.settings || {});
            next.nodes = Array.isArray(next.nodes) ? next.nodes.map(function (node, nodeIndex) {
                return normalizeNode(node, next.uid, nodeIndex);
            }) : [];
            return next;
        }

        function normalizeNode(node, columnUid, nodeIndex) {
            const next = Object.assign({}, node || {});
            next.uid = next.uid || (columnUid + '-node-' + (nodeIndex + 1));
            next.type = next.type || 'block';
            next.label = next.label || ('node.' + (nodeIndex + 1));
            next.class_name = next.class_name || '';
            next.notes = next.notes || '';
            next.source_key = next.source_key || '';
            next.device_visibility = Object.assign(defaultVisibility(), next.device_visibility || {});
            next.options = (next.options && typeof next.options === 'object') ? next.options : {};
            next.widget_id = Number(next.widget_id || 0);
            next.widget_name = next.widget_name || '';
            next.widget_controller = next.widget_controller || '';

            if (next.type === 'block') {
                applyBlockPresetToNode(next, true);
            }

            return next;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getDeviceTitle(device) {
            return (state.deviceMap[device] && state.deviceMap[device].title) || deviceTitles[device] || device;
        }

        function getDeviceConfig(device) {
            return state.deviceMap[device] || {key: device, title: getDeviceTitle(device), canvas_width: '100%'};
        }

        function getPageModeTitle(mode) {
            return pageModeTitles[mode] || mode;
        }

        function getPageStatusTitle(status) {
            return pageStatusTitles[status] || status;
        }

        function getLayoutOption(value) {
            return layoutOptions.find(function (option) {
                return option.value === value;
            }) || {value: value, title: value, hint: ''};
        }

        function getWidthOption(value) {
            return columnWidthOptions.find(function (option) {
                return option.value === value;
            }) || {value: value, title: value};
        }

        function getAlignOption(value) {
            return alignOptions.find(function (option) {
                return option.value === value;
            }) || {value: value, title: value};
        }

        function getOptionTitle(options, value) {
            const option = Array.isArray(options) ? options.find(function (item) {
                return String(item.value) === String(value);
            }) : null;

            return option ? option.title : value;
        }

        function getNodeTypeTitle(type) {
            if (type === 'system_widget') {
                return 'Системный виджет';
            }
            return 'Блок';
        }

        function getBlockPreset(value) {
            return blockPresetMap[value] || null;
        }

        function getBlockPresetOptions(currentValue) {
            const options = blockPresets.map(function (block) {
                return {value: block.key, title: block.title};
            });

            if (currentValue && !options.some(function (option) {
                return String(option.value) === String(currentValue);
            })) {
                options.unshift({value: currentValue, title: currentValue + ' (вне каталога)'});
            }

            return options;
        }

        function getBlockPresetDefaults(preset) {
            return (preset && preset.defaults && typeof preset.defaults === 'object') ? Object.assign({}, preset.defaults) : {};
        }

        function applyBlockPresetToNode(node, preserveExistingValues) {
            if (!node || node.type !== 'block') {
                return node;
            }

            const preset = getBlockPreset(node.source_key || node.label);
            node.options = (node.options && typeof node.options === 'object') ? node.options : {};

            if (!preset) {
                return node;
            }

            const defaults = getBlockPresetDefaults(preset);
            node.options = preserveExistingValues ? Object.assign({}, defaults, node.options) : Object.assign({}, defaults);

            if (!node.label || node.label === node.source_key || node.label === preset.title || node.label === preset.default_label) {
                node.label = preset.default_label || preset.title;
            }

            return node;
        }

        function getNodeSemanticSummary(node) {
            if (!node || node.type !== 'block') {
                return '';
            }

            const preset = getBlockPreset(node.source_key || node.label);
            return preset ? (preset.summary || preset.description || '') : '';
        }

        function renderBlockField(field, value) {
            const inputField = 'options.' + field.key;
            const hint = field.hint || '';

            if (field.type === 'textarea') {
                return '' +
                    '<div class="lb-field">' +
                        fieldLabel(field.title, hint) +
                        '<textarea class="lb-control" rows="3" data-field="' + inputField + '" placeholder="' + escapeHtml(field.placeholder || '') + '">' + escapeHtml(value || '') + '</textarea>' +
                    '</div>';
            }

            if (field.type === 'select') {
                let options = [];
                if (Array.isArray(field.options)) {
                    options = field.options.map(function (item) {
                        if (item && typeof item === 'object') {
                            return {value: String(item.value ?? ''), title: String(item.title ?? item.value ?? '')};
                        }
                        return {value: String(item ?? ''), title: String(item ?? '')};
                    });
                } else if (field.items && typeof field.items === 'object') {
                    options = Object.keys(field.items).map(function (key) {
                        return {value: String(key), title: String(field.items[key])};
                    });
                }

                return renderSelectField(field.title, hint, inputField, options, value);
            }

            if (field.type === 'checkbox') {
                const inputId = 'lb-field-' + String(field.key || '').replace(/[^a-z0-9_-]+/gi, '-') + '-' + Math.random().toString(16).slice(2);
                const checked = value === true || value === 1 || value === '1' || value === 'true' || value === 'on';

                return '' +
                    '<div class="lb-field">' +
                        '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" id="' + inputId + '" data-field="' + inputField + '"' + (checked ? ' checked' : '') + '>' +
                            '<label class="form-check-label" for="' + inputId + '">' + escapeHtml(field.title || '') + (hint ? helpIcon(hint) : '') + '</label>' +
                        '</div>' +
                    '</div>';
            }

            if (field.type === 'number') {
                return '' +
                    '<div class="lb-field">' +
                        fieldLabel(field.title, hint) +
                        '<input type="number" class="lb-control" data-field="' + inputField + '" value="' + escapeHtml(value ?? '') + '" placeholder="' + escapeHtml(field.placeholder || '') + '">' +
                    '</div>';
            }

            return '' +
                '<div class="lb-field">' +
                    fieldLabel(field.title, hint) +
                    '<input type="text" class="lb-control" data-field="' + inputField + '" value="' + escapeHtml(value || '') + '" placeholder="' + escapeHtml(field.placeholder || '') + '">' +
                '</div>';
        }

        function renderBlockSemanticInspector(node) {
            const preset = getBlockPreset(node.source_key || node.label);
            if (!preset) {
                return '' +
                    '<div class="lb-semantic-card">' +
                        '<div class="lb-semantic-card__title">Пользовательский блок</div>' +
                        '<div class="lb-semantic-card__desc">Для этого блока пока нет зарегистрированного semantic-пресета. Можно оставить технический source key и продолжить работу.</div>' +
                    '</div>';
            }

            const fields = Array.isArray(preset.fields) ? preset.fields : [];
            const options = (node.options && typeof node.options === 'object') ? node.options : {};

            return '' +
                '<div class="lb-semantic-card">' +
                    '<div class="lb-semantic-card__title">' + escapeHtml(preset.title) + '</div>' +
                    '<div class="lb-semantic-card__desc">' + escapeHtml(preset.summary || preset.description || '') + '</div>' +
                '</div>' +
                fields.map(function (field) {
					const value = Object.prototype.hasOwnProperty.call(options, field.key) ? options[field.key] : '';
					return renderBlockField(field, value);
                }).join('');
        }

        function getNodeDisplayLabel(node) {
            if (!node) {
                return '';
            }

            if (node.label) {
                return node.label;
            }

            if (node.type === 'block') {
                const preset = getBlockPreset(node.source_key || node.label);
                if (preset) {
                    return node.label || preset.default_label || preset.title;
                }
            }

            if (node.type === 'system_widget') {
                const widget = findWidgetById(node.widget_id);
                if (widget && widget.title) {
                    return widget.title;
                }
            }

            return node.label || 'Элемент';
        }

        function helpIcon(text) {
            return ' <span class="lb-help" data-toggle="tooltip" data-placement="top" title="' + escapeHtml(text) + '"><i class="fas fa-question-circle"></i></span>';
        }

        function fieldLabel(title, hint) {
            return '<label class="lb-field__label">' + escapeHtml(title) + (hint ? helpIcon(hint) : '') + '</label>';
        }

        function renderSelectOptions(options, currentValue) {
            return options.map(function (option) {
                return '<option value="' + escapeHtml(option.value) + '"' + (String(currentValue) === String(option.value) ? ' selected' : '') + '>' + escapeHtml(option.title) + '</option>';
            }).join('');
        }

        function renderSelectField(title, hint, field, options, value) {
            return '' +
                '<div class="lb-field">' +
                    fieldLabel(title, hint) +
                    '<select class="lb-control" data-field="' + field + '">' +
                        renderSelectOptions(options, value) +
                    '</select>' +
                '</div>';
        }

        function initTooltips(root) {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.tooltip) {
                return;
            }

            const $root = root ? window.jQuery(root) : window.jQuery(document);
            $root.find('[data-toggle="tooltip"]').tooltip({container: 'body'});
        }

        function renderPageMeta() {
            const effectiveShell = getEffectiveShellVariantState();
            const shellTitle = effectiveShell.variant ? effectiveShell.variant.title : 'Каркас не определён';
            const templateTitle = getCurrentTemplatePresetTitle();

            return 'Макет: ' + escapeHtml(state.page.title || state.page.key) + ' (<code>' + escapeHtml(state.page.key) + '</code>) | Шаблон: ' + escapeHtml(templateTitle) + ' | Каркас: ' + escapeHtml(shellTitle) + ' | Применение: ' + escapeHtml(getPageModeTitle(state.page.mode));
        }

        function getShellVariantCatalog() {
            return pageShellScreen.variant_catalog || {};
        }

        function getShellVariantOptions() {
            return Array.isArray(pageShellScreen.variant_options) && pageShellScreen.variant_options.length
                ? pageShellScreen.variant_options
                : [{value: '', title: 'Авто по правилам'}];
        }

        function normalizeShellSlotKeys(activeSlots) {
            if (!Array.isArray(activeSlots)) {
                return [];
            }

            return activeSlots.map(function (item) {
                return typeof item === 'string' ? item : item && item.key;
            }).filter(Boolean);
        }

        function getShellSlotTitle(slotKey) {
            return (pageShellScreen.slot_titles && pageShellScreen.slot_titles[slotKey]) || slotKey;
        }

        function getShellAssignmentSourceTitle(source) {
            return (pageShellScreen.assignment_source_titles && pageShellScreen.assignment_source_titles[source]) || source;
        }

        function resolveAutoShellVariantKey() {
            const presetRouteVariant = resolveTemplatePresetRouteVariantKey();
            if (presetRouteVariant) {
                return presetRouteVariant;
            }

            const pageTemplate = String((state.schema.layout && state.schema.layout.template) || state.page.template || '');

            if (pageTemplate === 'nm') {
                if (state.page.key === 'homepage') {
                    return 'nm-homepage';
                }

                if (state.page.mode === 'full_takeover') {
                    return 'nm-landing';
                }

                return 'nm-site';
            }

            return String(pageShellScreen.auto_variant_key || '');
        }

        function getEffectiveShellVariantState() {
            const layout = state.schema.layout || {};
            const catalog = getShellVariantCatalog();
            const override = String(layout.shell_variant || '');

            if (override && catalog[override]) {
                return {
                    variant: catalog[override],
                    assignment_source: 'page-layout'
                };
            }

            const autoKey = resolveAutoShellVariantKey();
            const fallbackVariant = pageShellScreen.effective_variant || {};
            const assignmentSource = resolveTemplatePresetRouteVariantKey()
                ? 'template-preset'
                : (pageShellScreen.auto_assignment_source || fallbackVariant.assignment_source || 'default');

            return {
                variant: catalog[autoKey] || {
                    key: autoKey,
                    title: fallbackVariant.title || autoKey || 'Базовый shell',
                    active_slots: normalizeShellSlotKeys(fallbackVariant.active_slots || []),
                    body_layout: fallbackVariant.body_layout || 'no_sidebars'
                },
                assignment_source: assignmentSource
            };
        }

        function getPageContentSlotOptions() {
            const layout = state.schema.layout || {};
            const effectiveShell = getEffectiveShellVariantState();
            const allowedSlots = {
                hero: true,
                before_content: true,
                content_body: true,
                content_sidebar_left: true,
                content_sidebar_right: true,
                after_content: true
            };

            let slotKeys = normalizeShellSlotKeys((effectiveShell.variant && effectiveShell.variant.active_slots) || []);

            if (!slotKeys.length && Array.isArray(pageShellScreen.content_slot_options)) {
                slotKeys = pageShellScreen.content_slot_options.map(function (option) {
                    return option.value;
                }).filter(Boolean);
            }

            if (layout.content_slot && allowedSlots[layout.content_slot] && slotKeys.indexOf(layout.content_slot) === -1) {
                slotKeys.push(layout.content_slot);
            }

            slotKeys = slotKeys.filter(function (slotKey) {
                return allowedSlots[slotKey];
            });

            if (!slotKeys.length) {
                slotKeys = ['content_body'];
            }

            return slotKeys.map(function (slotKey) {
                return {
                    value: slotKey,
                    title: getShellSlotTitle(slotKey)
                };
            });
        }

        function syncPageLayoutWithEffectiveShell() {
            state.schema.layout = state.schema.layout || {shell_variant: '', content_slot: 'content_body'};

            const options = getPageContentSlotOptions();
            const hasCurrent = options.some(function (option) {
                return String(option.value) === String(state.schema.layout.content_slot || '');
            });

            if (!hasCurrent && options.length) {
                state.schema.layout.content_slot = options[0].value;
            }
        }

        function renderPageShellSummary() {
            const effectiveShell = getEffectiveShellVariantState();
            const variant = effectiveShell.variant || {title: 'Базовый shell', active_slots: [], body_layout: 'no_sidebars'};
            const slotBadges = normalizeShellSlotKeys(variant.active_slots).map(function (slotKey) {
                return '<span class="lb-chip">' + escapeHtml(getShellSlotTitle(slotKey)) + '</span>';
            }).join('');

            return '' +
                '<div class="lb-shell-summary">' +
                    '<div class="lb-shell-summary__title">Каркас страницы: ' + escapeHtml(variant.title || variant.key || 'Базовый') + '</div>' +
                    '<div class="lb-token-list">' + (slotBadges || '<span class="small text-muted">Зоны каркаса не определены.</span>') + '</div>' +
                '</div>';
        }

        function getPagePrimaryShellSlotKey() {
            if (state.page.mode === 'full_takeover') {
                return String((state.schema.layout && state.schema.layout.content_slot) || 'content_body');
            }

            return 'content_body';
        }

        function renderShellSlotCard(slotKey, sizeClass, options) {
            const settings = options || {};
            const classes = ['lb-shell-slot', sizeClass || 'lb-shell-slot--wide'];

            if (settings.isActive) {
                classes.push('lb-shell-slot--active');
            } else {
                classes.push('lb-shell-slot--muted');
            }

            if (settings.isPageSlot) {
                classes.push('lb-shell-slot--page');
            }

            return '' +
                '<div class="' + escapeHtml(classes.join(' ')) + '">' +
                    '<div class="lb-shell-slot__top">' +
                        '<div>' +
                            '<div class="lb-shell-slot__title">' + escapeHtml(getShellSlotTitle(slotKey)) + '</div>' +
                            '<div class="lb-shell-slot__key">' + escapeHtml(slotKey) + '</div>' +
                        '</div>' +
                        '<span class="lb-shell-slot__state">' + escapeHtml(settings.stateLabel || (settings.isActive ? 'Активен' : 'Выключен')) + '</span>' +
                    '</div>' +
                    '<div class="lb-shell-slot__copy">' + escapeHtml(settings.copy || '') + '</div>' +
                '</div>';
        }

        function renderShellParticipationMap() {
            const effectiveShell = getEffectiveShellVariantState();
            const variant = effectiveShell.variant || {title: 'Базовый shell', active_slots: [], body_layout: 'no_sidebars'};
            const activeSlots = normalizeShellSlotKeys(variant.active_slots);
            const activeLookup = activeSlots.reduce(function (map, slotKey) {
                map[slotKey] = true;
                return map;
            }, {});
            const primarySlot = getPagePrimaryShellSlotKey();
            const isFullTakeover = state.page.mode === 'full_takeover';
            const contentCopy = isFullTakeover
                ? 'Здесь находится основное содержимое страницы, которое вы собираете на холсте.'
                : 'В overlay-режиме здесь остаётся системное содержимое, а редактор добавляет блоки поверх разрешённых зон.';

            return '' +
                '<section class="lb-shell-map">' +
                    '<div class="lb-shell-map__header">' +
                        '<div>' +
                            '<div class="lb-shell-map__eyebrow">Карта каркаса</div>' +
                            '<h3 class="lb-shell-map__title">Как страница встраивается в каркас сайта</h3>' +
                            '<div class="lb-shell-map__copy">Эта карта показывает каркас страницы: какие зоны активны, где находится основное содержимое и что управляется самим каркасом.</div>' +
                        '</div>' +
                        '<div class="lb-shell-map__meta">Каркас: ' + escapeHtml(variant.title || variant.key || 'Базовый каркас') + '<br>Источник: ' + escapeHtml(getShellAssignmentSourceTitle(effectiveShell.assignment_source)) + '<br>Сайдбары: ' + escapeHtml(variant.body_layout || 'no_sidebars') + '</div>' +
                    '</div>' +
                    '<div class="lb-shell-map__legend">' +
                        '<span class="lb-shell-map__legend-item"><span class="lb-shell-map__legend-swatch lb-shell-map__legend-swatch--active"></span>Активная зона каркаса</span>' +
                        '<span class="lb-shell-map__legend-item"><span class="lb-shell-map__legend-swatch lb-shell-map__legend-swatch--page"></span>Главная зона этой страницы</span>' +
                        '<span class="lb-shell-map__legend-item"><span class="lb-shell-map__legend-swatch"></span>Не участвует в текущем варианте</span>' +
                    '</div>' +
                    '<div class="lb-shell-map__grid">' +
                        renderShellSlotCard('site_top', 'lb-shell-slot--wide', {
                            isActive: !!activeLookup.site_top,
                            copy: 'Верхняя сервисная полоса сайта: объявления, служебные ссылки, уведомления.'
                        }) +
                        renderShellSlotCard('header_primary', 'lb-shell-slot--half', {
                            isActive: !!activeLookup.header_primary,
                            copy: 'Основной header сайта: бренд, ключевая навигация, основной слой интерфейса.'
                        }) +
                        renderShellSlotCard('header_secondary', 'lb-shell-slot--half', {
                            isActive: !!activeLookup.header_secondary,
                            copy: 'Дополнительный header-слой: вторичное меню, служебные действия, уточняющий контент.'
                        }) +
                        renderShellSlotCard('hero', 'lb-shell-slot--wide', {
                            isActive: !!activeLookup.hero,
                            copy: 'Отдельная hero-зона каркаса. Подходит для первого экрана сайта или hero над содержимым.'
                        }) +
                        renderShellSlotCard('before_content', 'lb-shell-slot--wide', {
                            isActive: !!activeLookup.before_content,
                            copy: 'Зона перед основным контентом: баннеры, вводный блок, контекстный блок.'
                        }) +
                        renderShellSlotCard('content_sidebar_left', 'lb-shell-slot--third', {
                            isActive: !!activeLookup.content_sidebar_left,
                            isPageSlot: primarySlot === 'content_sidebar_left',
                            stateLabel: primarySlot === 'content_sidebar_left' ? 'Сюда идет страница' : (!!activeLookup.content_sidebar_left ? 'Активен' : 'Выключен'),
                            copy: primarySlot === 'content_sidebar_left' ? 'Редкий режим: основное содержимое страницы встраивается в левую колонку каркаса.' : 'Левая sidebar-зона каркаса для вспомогательных виджетов и навигации.'
                        }) +
                        renderShellSlotCard('content_body', 'lb-shell-slot--third', {
                            isActive: !!activeLookup.content_body,
                            isPageSlot: primarySlot === 'content_body',
                            stateLabel: primarySlot === 'content_body' ? (isFullTakeover ? 'Сюда идет страница' : 'Системный content') : (!!activeLookup.content_body ? 'Активен' : 'Выключен'),
                            copy: contentCopy
                        }) +
                        renderShellSlotCard('content_sidebar_right', 'lb-shell-slot--third', {
                            isActive: !!activeLookup.content_sidebar_right,
                            isPageSlot: primarySlot === 'content_sidebar_right',
                            stateLabel: primarySlot === 'content_sidebar_right' ? 'Сюда идет страница' : (!!activeLookup.content_sidebar_right ? 'Активен' : 'Выключен'),
                            copy: primarySlot === 'content_sidebar_right' ? 'Редкий режим: основное содержимое страницы встраивается в правую колонку каркаса.' : 'Правая sidebar-зона каркаса для дополнительных виджетов, фильтров и вспомогательного контента.'
                        }) +
                        renderShellSlotCard('after_content', 'lb-shell-slot--wide', {
                            isActive: !!activeLookup.after_content,
                            copy: 'Зона после основного контента: CTA-блок, доверие/гарантии, следующий шаг.'
                        }) +
                        renderShellSlotCard('footer_primary', 'lb-shell-slot--half', {
                            isActive: !!activeLookup.footer_primary,
                            copy: 'Основной footer сайта: колонки, контакты, подвал.'
                        }) +
                        renderShellSlotCard('footer_secondary', 'lb-shell-slot--half', {
                            isActive: !!activeLookup.footer_secondary,
                            copy: 'Нижний footer-слой: копирайт, служебные ссылки, юридическая информация.'
                        }) +
                    '</div>' +
                '</section>';
        }

        function getWorkspaceStorageKey() {
            return 'landingbuilder.canvas.workspace.v1';
        }

        function getWorkspaceMode() {
            return window.matchMedia('(max-width: 1199.98px)').matches ? 'mobile' : 'desktop';
        }

        function getDefaultDrawers(mode) {
            return mode === 'desktop'
                ? {library: true, inspector: true}
                : {library: false, inspector: false};
        }

        function loadWorkspaceUIState() {
            const baseState = {
                drawers: {
                    desktop: getDefaultDrawers('desktop'),
                    mobile: getDefaultDrawers('mobile')
                }
            };

            try {
                const rawState = window.localStorage.getItem(getWorkspaceStorageKey());
                if (!rawState) {
                    return baseState;
                }

                const parsedState = JSON.parse(rawState);
                if (!parsedState || typeof parsedState !== 'object') {
                    return baseState;
                }

                baseState.drawers.desktop = Object.assign({}, baseState.drawers.desktop, parsedState.drawers && parsedState.drawers.desktop ? parsedState.drawers.desktop : {});
                baseState.drawers.mobile = Object.assign({}, baseState.drawers.mobile, parsedState.drawers && parsedState.drawers.mobile ? parsedState.drawers.mobile : {});
            } catch (error) {
                return baseState;
            }

            return baseState;
        }

        function saveWorkspaceUIState() {
            try {
                window.localStorage.setItem(getWorkspaceStorageKey(), JSON.stringify(state.ui));
            } catch (error) {
                return;
            }
        }

        function isDrawerOpen(name) {
            const mode = getWorkspaceMode();
            const drawers = state.ui && state.ui.drawers ? state.ui.drawers[mode] : null;

            return !!(drawers && drawers[name]);
        }

        function syncWorkspaceShell() {
            if (!workspace) {
                return;
            }

            const mode = getWorkspaceMode();
            const libraryOpen = isDrawerOpen('library');
            const inspectorOpen = isDrawerOpen('inspector');

            workspace.classList.toggle('lb-workspace--mobile', mode === 'mobile');
            workspace.classList.toggle('lb-workspace--library-open', libraryOpen);
            workspace.classList.toggle('lb-workspace--inspector-open', inspectorOpen);

            drawerToggleButtons.forEach(function (button) {
                const drawerName = button.dataset.drawerToggle;
                if (!drawerName) {
                    return;
                }

                const isOpen = drawerName === 'library' ? libraryOpen : inspectorOpen;
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        function setDrawerOpen(name, isOpen) {
            const mode = getWorkspaceMode();
            const defaults = getDefaultDrawers(mode);

            if (!state.ui.drawers[mode]) {
                state.ui.drawers[mode] = defaults;
            }

            if (mode === 'mobile' && isOpen) {
                state.ui.drawers[mode].library = false;
                state.ui.drawers[mode].inspector = false;
            }

            state.ui.drawers[mode][name] = isOpen;
            saveWorkspaceUIState();
            syncWorkspaceShell();
        }

        function toggleDrawer(name) {
            setDrawerOpen(name, !isDrawerOpen(name));
        }

        function applyDeviceViewport() {
            const config = getDeviceConfig(state.activeDevice);
            if (!canvasFrame) {
                return;
            }

            canvasFrame.style.maxWidth = config.canvas_width || '100%';
            canvasFrame.setAttribute('data-device', config.key || state.activeDevice);

            if (deviceWidthLabel) {
                deviceWidthLabel.textContent = config.viewport_width ? String(config.viewport_width) + 'px' : 'Авто';
            }
        }

        function sectionColumnClass(count) {
            if (count <= 1) {
                return 'col-12';
            }
            if (count === 2) {
                return 'col-md-6';
            }
            return 'col-md-4';
        }

        function isVisibleOnDevice(visibility) {
            if (!visibility || typeof visibility !== 'object') {
                return true;
            }
            return visibility[state.activeDevice] !== false;
        }

        function getLayoutColumnCount(layout) {
            if (layout === '1col') {
                return 1;
            }
            if (layout.indexOf('3col') === 0) {
                return 3;
            }
            return 2;
        }

        function syncSectionColumnsWithLayout(section) {
            const required = getLayoutColumnCount(section.layout);
            section.columns = Array.isArray(section.columns) ? section.columns : [];

            while (section.columns.length < required) {
                section.columns.push(normalizeColumn({title: 'Колонка ' + (section.columns.length + 1)}, section.uid, section.columns.length));
            }

            if (section.columns.length > required) {
                const overflow = section.columns.splice(required);
                overflow.forEach(function (column) {
                    if (column && Array.isArray(column.nodes)) {
                        section.columns[required - 1].nodes = section.columns[required - 1].nodes.concat(column.nodes);
                    }
                });
            }

            section.columns = section.columns.map(function (column, columnIndex) {
                return normalizeColumn(column, section.uid, columnIndex);
            });
        }

        function moveArrayItem(items, fromIndex, toIndex) {
            const list = items.slice();
            const chunk = list.splice(fromIndex, 1);
            if (!chunk.length) {
                return list;
            }
            list.splice(toIndex, 0, chunk[0]);
            return list;
        }

        function setDeepValue(target, path, value) {
            const parts = path.split('.');
            let current = target;

            for (let index = 0; index < parts.length - 1; index += 1) {
                if (!current[parts[index]] || typeof current[parts[index]] !== 'object') {
                    current[parts[index]] = {};
                }
                current = current[parts[index]];
            }

            current[parts[parts.length - 1]] = value;
        }

        function getSelectionTarget() {
            if (!state.selection) {
                return null;
            }

            if (state.selection.type === 'section') {
                return state.schema.sections[state.selection.sectionIndex] || null;
            }

            if (state.selection.type === 'page') {
                return state.schema || null;
            }

            if (state.selection.type === 'column') {
                return getSelectedColumn();
            }

            return getSelectedNode();
        }

        function renderVisibilityControls(pathBase, visibility) {
            return state.deviceKeys.map(function (device) {
                const inputId = 'lb-' + pathBase.replace(/\./g, '-') + '-' + device;
                return '' +
                    '<div class="form-check form-check-inline mr-2">' +
                        '<input class="form-check-input" type="checkbox" id="' + inputId + '" data-field="' + pathBase + '.' + device + '"' + (visibility[device] !== false ? ' checked' : '') + '>' +
                        '<label class="form-check-label small" for="' + inputId + '">' + escapeHtml(getDeviceTitle(device)) + '</label>' +
                    '</div>';
            }).join('');
        }

        function renderWidthControls(width) {
            return state.deviceKeys.map(function (device) {
                const widthOption = getWidthOption(String(width[device] || 'auto'));
                return '' +
                    '<div class="lb-field">' +
                        fieldLabel('Ширина для устройства «' + getDeviceTitle(device) + '»', 'Задает, сколько места колонка занимает на выбранном типе устройства.') +
                        '<select class="lb-control" data-field="width.' + device + '">' +
                            columnWidthOptions.map(function (option) {
                                return '<option value="' + option.value + '"' + (String(width[device] || 'auto') === option.value ? ' selected' : '') + '>' + option.title + '</option>';
                            }).join('') +
                        '</select>' +
                        '<div class="small text-muted mt-1">Сейчас: ' + escapeHtml(widthOption.title) + '</div>' +
                    '</div>';
            }).join('');
        }

        function ensureSelection() {
            if (state.selection) {
                return true;
            }

            if (!state.schema.sections.length) {
                state.selection = {type: 'page'};
                return true;
            }

            state.selection = {type: 'section', sectionIndex: 0};
            return true;
        }

        function getSelectedNode() {
            if (!state.selection || state.selection.type !== 'node') {
                return null;
            }

            const section = state.schema.sections[state.selection.sectionIndex];
            const column = section && section.columns[state.selection.columnIndex];
            return column && column.nodes[state.selection.nodeIndex] ? column.nodes[state.selection.nodeIndex] : null;
        }

        function getSelectedColumn() {
            if (!state.selection) {
                return null;
            }

            if (state.selection.type === 'column' || state.selection.type === 'node') {
                const section = state.schema.sections[state.selection.sectionIndex];
                return section && section.columns[state.selection.columnIndex] ? section.columns[state.selection.columnIndex] : null;
            }

            return null;
        }

        function uid(prefix) {
            return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 7);
        }

        function syncSelectedWidgetFormIntoState() {
            const node = getSelectedNode();
            if (!node || node.type !== 'system_widget') {
                return;
            }

            if (!widgetForm) {
                return;
            }
            const options = {};

            function normalizeWidgetOptionName(name) {
                const raw = String(name || '').trim();
                if (!raw) {
                    return '';
                }

                const matchColon = raw.match(/^options:(.+)$/);
                if (matchColon) {
                    return matchColon[1];
                }

                const matchSingle = raw.match(/^options\[([^\]]+)\]$/);
                if (matchSingle) {
                    return matchSingle[1];
                }

                const matchArray = raw.match(/^options\[([^\]]+)\]\[\]$/);
                if (matchArray) {
                    return matchArray[1];
                }

                return raw;
            }

            widgetForm.querySelectorAll('input, select, textarea').forEach(function (element) {
                if (!element.name) {
                    return;
                }

                const optionName = normalizeWidgetOptionName(element.name);
                if (!optionName) {
                    return;
                }

                if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
                    return;
                }

                if (element.tagName === 'SELECT' && element.multiple) {
                    options[optionName] = Array.from(element.selectedOptions).map(function (option) {
                        return option.value;
                    });
                    return;
                }

                if (element.type === 'checkbox') {
                    const value = element.value || '1';

                    if (!Object.prototype.hasOwnProperty.call(options, optionName)) {
                        options[optionName] = value;
                        return;
                    }

                    if (Array.isArray(options[optionName])) {
                        options[optionName].push(value);
                        return;
                    }

                    options[optionName] = [options[optionName], value];
                    return;
                }

                options[optionName] = element.value;
            });

            node.options = options;
        }

        if (widgetForm) {
            widgetForm.addEventListener('change', function () {
                const node = getSelectedNode();
                if (!node || node.type !== 'system_widget') {
                    return;
                }

                syncSelectedWidgetFormIntoState();
                setCanvasDirty(true);
                renderCanvas();
            });
        }

        function renderSectionLibrary() {
            const presets = Array.isArray(state.screen.section_presets) ? state.screen.section_presets : [];

            sectionList.innerHTML = '' +
                '<button type="button" class="lb-library-card" data-role="insert-empty-section">' +
                    '<div class="lb-library-card__row">' +
                        '<span class="lb-library-card__title">Пустая секция</span>' +
                        '<span class="lb-chip">Создать</span>' +
                    '</div>' +
                    '<div class="lb-library-card__meta lb-library-caption">Чистая секция без контента. Удобно, если нужен свой состав колонок и блоков.</div>' +
                '</button>' +
                presets.map(function (preset, index) {
                    const columnsCount = Array.isArray(preset.columns) ? preset.columns.length : getLayoutColumnCount(preset.layout || '1col');
                    const presetKeyAttr = preset && preset.key ? ' data-preset-key="' + escapeHtml(String(preset.key)) + '"' : '';
                    return '' +
                        '<button type="button" class="lb-library-card" data-role="insert-section-preset" data-preset-index="' + index + '"' + presetKeyAttr + '>' +
                            '<div class="lb-library-card__row">' +
                                '<span class="lb-library-card__title">' + escapeHtml(preset.title) + '</span>' +
                                '<span class="lb-chip">' + escapeHtml(String(columnsCount)) + ' кол.</span>' +
                            '</div>' +
                            '<div class="lb-library-card__meta lb-library-caption">' + escapeHtml(preset.description || 'Готовый стартовый состав секции.') + '</div>' +
                        '</button>';
                }).join('');

            initTooltips(sectionList);
        }

        function renderBlockLibrary() {
            blockList.innerHTML = blockPresets.map(function (block, index) {
                return '' +
                    '<button type="button" class="lb-library-card" data-role="insert-block" data-block-index="' + index + '">' +
                        '<div class="lb-library-card__row">' +
                            '<span class="lb-library-card__title">' + escapeHtml(block.title) + '</span>' +
                            '<span class="lb-chip">Добавить</span>' +
                        '</div>' +
                        '<div class="lb-library-card__meta lb-library-caption">' + escapeHtml(block.description) + '</div>' +
                    '</button>';
            }).join('');

            initTooltips(blockList);
        }

        function renderWidgetLibrary() {
            const groups = Object.keys(state.widgetsCatalog);
            if (!groups.length) {
                widgetList.innerHTML = '<div class="text-muted">Список виджетов пока пуст или еще не загружен.</div>';
                return;
            }

            widgetList.innerHTML = groups.map(function (group) {
                const items = state.widgetsCatalog[group] || [];
                return '' +
                    '<div class="mb-3">' +
                        '<div class="font-weight-bold text-uppercase small mb-2">' + escapeHtml(group === 'core' ? 'Система' : group) + '</div>' +
                        items.map(function (widget) {
                            return '' +
                                '<button type="button" class="lb-library-card" data-role="insert-widget" data-widget-id="' + widget.id + '">' +
                                    '<div class="lb-library-card__title">' + escapeHtml(widget.title) + '</div>' +
                                    '<div class="lb-library-card__meta">Код: ' + escapeHtml((widget.controller || 'core') + '.' + widget.name) + '</div>' +
                                '</button>';
                        }).join('') +
                    '</div>';
            }).join('');

            initTooltips(widgetList);
        }

        function renderCanvas() {
            applyDeviceViewport();

            const pageTheme = getCurrentPageTheme();
            const pageThemeStyle = renderThemeVars(getThemeVars(pageTheme));
            const effectiveShell = getEffectiveShellVariantState();
            const effectiveShellTitle = effectiveShell.variant ? effectiveShell.variant.title : 'Базовый shell';
            const templatePresetTitle = getCurrentTemplatePresetTitle();

            if (shellMap) {
                shellMap.innerHTML = renderShellParticipationMap();
                initTooltips(shellMap);
            }

            const sectionsMarkup = state.schema.sections.length
                ? state.schema.sections.map(function (section, sectionIndex) {
                    const sectionVisible = isVisibleOnDevice(section.visibility);
                    const layoutTitle = getLayoutOption(section.layout).title;
                    const sectionPresentation = getSectionPresentation(section);
                    const sectionClasses = [
                        'lb-live-section',
                        sectionPresentation.className,
                        state.selection && state.selection.sectionIndex === sectionIndex ? 'lb-live-section--selected' : '',
                        !sectionVisible ? 'lb-muted-device' : ''
                    ].filter(Boolean).join(' ');

                    return '' +
                        '<section class="' + escapeHtml(sectionClasses) + '" data-role="section" data-section-index="' + sectionIndex + '" data-drag-kind="section" draggable="true">' +
                            '<div class="lb-live-section__topbar">' +
                                '<div>' +
                                    '<div class="lb-live-section__kicker">' + escapeHtml(getOptionTitle(sectionTypeOptions, section.section_type || 'content')) + '</div>' +
                                    '<h3 class="lb-live-section__title">' + escapeHtml(section.title) + '</h3>' +
                                    '<div class="lb-live-section__meta">Схема: ' + escapeHtml(layoutTitle) + ' | стиль: ' + escapeHtml(getOptionTitle(sectionStyleOptions, section.style_preset || 'content')) + ' | тон: ' + escapeHtml(getOptionTitle(backgroundToneOptions, section.background_tone || 'base')) + '</div>' +
                                    (!sectionVisible ? '<div class="lb-live-section__meta">Секция скрыта на устройстве «' + escapeHtml(getDeviceTitle(state.activeDevice)) + '».</div>' : '') +
                                '</div>' +
                                '<div>' +
                                    '<button type="button" class="lb-btn lb-btn--danger" data-action="delete-section" data-section-index="' + sectionIndex + '">Удалить</button>' +
                                '</div>' +
                            '</div>' +
                            '<div class="lb-live-section__inner">' +
                                '<div class="lb-live-columns ' + escapeHtml(getSectionLayoutClass(section.layout)) + '">' +
                                    section.columns.map(function (column, columnIndex) {
                                        const columnVisible = isVisibleOnDevice(column.visibility);
                                        const columnClasses = [
                                            'lb-live-column',
                                            state.selection && state.selection.sectionIndex === sectionIndex && state.selection.columnIndex === columnIndex ? 'lb-live-column--selected' : '',
                                            !columnVisible ? 'lb-muted-device' : ''
                                        ].filter(Boolean).join(' ');

                                        return '' +
                                            '<div class="' + escapeHtml(columnClasses) + '" data-role="column" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '">' +
                                                '<div class="lb-live-column__head">' +
                                                    '<div>' +
                                                        '<div class="lb-live-column__eyebrow">Колонка</div>' +
                                                        '<div class="lb-live-column__title">' + escapeHtml(column.title) + '</div>' +
                                                    '</div>' +
                                                    '<div class="lb-live-column__meta">' + escapeHtml(getWidthOption(String(column.width[state.activeDevice] || 'auto')).title) + '</div>' +
                                                '</div>' +
                                                '<div class="lb-live-column__meta">Нажмите, чтобы выбрать колонку. Перетаскивание в эту область отправляет элемент в конец колонки.</div>' +
                                                (column.nodes.length
                                                    ? column.nodes.map(function (node, nodeIndex) {
                                                        const nodeVisible = isVisibleOnDevice(node.device_visibility);
                                                        const nodeLabel = getNodeDisplayLabel(node);
                                                        const nodeClasses = [
                                                            'lb-live-node',
                                                            node.type === 'system_widget' ? 'lb-live-node--widget' : 'lb-live-node--block',
                                                            state.selection && state.selection.type === 'node' && state.selection.sectionIndex === sectionIndex && state.selection.columnIndex === columnIndex && state.selection.nodeIndex === nodeIndex ? 'lb-live-node--selected' : '',
                                                            !nodeVisible ? 'lb-muted-device' : ''
                                                        ].filter(Boolean).join(' ');

                                                        return '' +
                                                            '<div class="' + escapeHtml(nodeClasses) + '" data-role="node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '" data-drag-kind="node" draggable="true">' +
                                                                '<div class="lb-live-node__head">' +
                                                                    '<div>' +
                                                                        '<div class="lb-live-node__eyebrow">' + escapeHtml(getNodeTypeTitle(node.type)) + '</div>' +
                                                                        '<div class="lb-live-node__label">' + escapeHtml(nodeLabel) + '</div>' +
                                                                    '</div>' +
                                                                    '<button type="button" class="lb-btn lb-btn--link" data-action="delete-node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '">удалить</button>' +
                                                                '</div>' +
	                                                                '<div class="lb-live-node__meta">' + (node.type === 'block' && getNodeSemanticSummary(node)
	                                                                    ? escapeHtml(getNodeSemanticSummary(node))
	                                                                    : (node.class_name ? 'Оформление: ' + escapeHtml(node.class_name) : 'Дополнительное оформление не задано')) + (!nodeVisible ? ' | скрыто на устройстве «' + escapeHtml(getDeviceTitle(state.activeDevice)) + '»' : '') + '</div>' +
                                                            '</div>';
                                                    }).join('')
                                                    : '<div class="lb-live-column__empty">Колонка пока пустая. Добавьте блок или системный виджет из библиотеки слева.</div>') +
                                            '</div>';
                                    }).join('') +
                                '</div>' +
                            '</div>' +
                        '</section>';
                }).join('')
                : '' +
                    '<div class="lb-starter" data-role="page">' +
                        '<div class="lb-starter__icon">✦</div>' +
                        '<p class="lb-starter__title">Начните создавать страницу</p>' +
                        '<p class="lb-starter__desc">Выберите готовый стартовый блок или добавьте чистую секцию и настройте всё сам.</p>' +
                        '<div class="lb-starter__grid">' +
                            '<button type="button" class="lb-starter__btn" data-role="insert-section-preset" data-preset-index="0" data-preset-key="hero_simple">' +
                                '<span class="lb-starter__btn-icon">🏠</span>' +
                                '<span>Первый экран</span>' +
                            '</button>' +
                            '<button type="button" class="lb-starter__btn" data-role="insert-section-preset" data-preset-index="3" data-preset-key="benefits_3_cards">' +
                                '<span class="lb-starter__btn-icon">⭐</span>' +
                                '<span>Преимущества</span>' +
                            '</button>' +
                            '<button type="button" class="lb-starter__btn" data-role="insert-section-preset" data-preset-index="7" data-preset-key="testimonials">' +
                                '<span class="lb-starter__btn-icon">💬</span>' +
                                '<span>Отзывы</span>' +
                            '</button>' +
                            '<button type="button" class="lb-starter__btn" data-role="insert-empty-section">' +
                                '<span class="lb-starter__btn-icon">＋</span>' +
                                '<span>Чистая секция</span>' +
                            '</button>' +
                        '</div>' +
                        '<p class="lb-starter__hint">Или выберите любую готовую секцию в <strong>Библиотеке</strong> слева.</p>' +
                    '</div>';

            canvasRoot.innerHTML = '' +
                '<div class="lb-live-page" style="' + escapeHtml(pageThemeStyle) + '" data-role="page">' +
                    '<section class="lb-live-page__hero" data-role="page">' +
                        '<div class="lb-live-page__kicker">Визуальная сборка страницы</div>' +
                        '<div class="lb-live-page__header">' +
                            '<div>' +
                                '<h2 class="lb-live-page__title">' + escapeHtml(state.page.title) + '</h2>' +
                                '<p class="lb-live-page__lead">Это рабочий экран страницы. Выбирайте шаблон, меняйте стиль и собирайте секции прямо здесь, а глобальные настройки сайта трогайте только когда это действительно нужно.</p>' +
                            '</div>' +
                        '</div>' +
                        '<div class="lb-live-page__chips">' +
                            '<span class="lb-live-pill"><strong>Шаблон</strong><span>' + escapeHtml(templatePresetTitle) + '</span></span>' +
                            '<span class="lb-live-pill"><strong>Каркас</strong><span>' + escapeHtml(effectiveShellTitle) + '</span></span>' +
                            '<span class="lb-live-pill"><strong>Стиль</strong><span>' + escapeHtml(getOptionTitle(pageThemeOptions.global_style_preset, pageTheme.global_style_preset)) + '</span></span>' +
                            '<span class="lb-live-pill"><strong>Цвет</strong><span>' + escapeHtml(getOptionTitle(pageThemeOptions.color_preset, pageTheme.color_preset)) + '</span></span>' +
                            '<span class="lb-live-pill"><strong>Типографика</strong><span>' + escapeHtml(getOptionTitle(pageThemeOptions.typography_preset, pageTheme.typography_preset)) + '</span></span>' +
                            '<span class="lb-live-pill"><strong>Кнопки</strong><span>' + escapeHtml(getOptionTitle(pageThemeOptions.button_preset, pageTheme.button_preset)) + '</span></span>' +
                        '</div>' +
                    '</section>' +
                    '<div class="lb-live-sections">' + sectionsMarkup + '</div>' +
                '</div>';

            widgetCount.textContent = String(countWidgetNodes());
            renderInspector();
            initTooltips(canvasRoot);
        }

        function renderInspector() {
            const selection = state.selection;

            if (!selection) {
                selectionSummary.textContent = 'Ничего не выбрано';
                selectionControls.innerHTML = '';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                return;
            }

            if (selection.type === 'page') {
                const theme = state.schema.theme || {};
                const layout = state.schema.layout || {};
                const contentSlotOptions = getPageContentSlotOptions();
                selectionSummary.innerHTML = '<strong>Страница</strong><br><span class="text-muted">' + escapeHtml(state.page.title) + ' · шаблон, shell и стиль</span>';
                selectionControls.innerHTML = '' +
                        '<div class="small text-muted mb-3">Это основной слой визуальной работы со страницей. Сначала выберите шаблон страницы, затем при необходимости уточните каркас и локальные пресеты. Глобальный экран дизайна нужен только для редких общих настроек сайта.</div>' +
                    '<div class="small font-weight-bold text-uppercase text-muted mb-2">Каркас страницы</div>' +
                    renderSelectField('Шаблон страницы', 'Определяет общий тип страницы и подсказывает, какой shell использовать автоматически.', 'theme.template_preset', pageThemeOptions.template_preset || [], theme.template_preset || '') +
                    renderSelectField('Вариант каркаса', 'Обычно оставьте авто. Меняйте только если нужна нестандартная раскладка.', 'layout.shell_variant', getShellVariantOptions(), layout.shell_variant || '') +
                    (state.page.mode === 'full_takeover'
                        ? renderSelectField('Основной слот содержимого', 'Куда должен вставляться основной runtime-контент страницы внутри shell.', 'layout.content_slot', contentSlotOptions, layout.content_slot || 'content_body')
                        : '') +
                    renderPageShellSummary() +
                    '<div class="small font-weight-bold text-uppercase text-muted mb-2">Внешний вид страницы</div>' +
                        renderSelectField('Общий стиль страницы', 'Локальный пресет страницы. Для общего стиля всего сайта используйте экран «Дизайн сайта».', 'theme.global_style_preset', pageThemeOptions.global_style_preset, theme.global_style_preset) +
                    renderSelectField('Цветовая схема', 'Базовый набор цветов интерфейса и контента.', 'theme.color_preset', pageThemeOptions.color_preset, theme.color_preset) +
                    renderSelectField('Типографика', 'Пресет для заголовков, текста и ритма набора.', 'theme.typography_preset', pageThemeOptions.typography_preset, theme.typography_preset) +
                    renderSelectField('Контейнеры', 'Базовая ширина контейнеров по странице.', 'theme.container_preset', pageThemeOptions.container_preset, theme.container_preset) +
                    renderSelectField('Кнопки', 'Главный стиль кнопок и призывов к действию.', 'theme.button_preset', pageThemeOptions.button_preset, theme.button_preset) +
                    renderSelectField('Карточки', 'Пресет карточек для списков и блоков.', 'theme.card_preset', pageThemeOptions.card_preset, theme.card_preset) +
                    renderSelectField('Ритм между секциями', 'Общий вертикальный ритм страницы.', 'theme.section_spacing', pageThemeOptions.section_spacing, theme.section_spacing) +
                        '<div class="small text-muted mt-3"><a href="' + escapeHtml(state.screen.design_url || '#') + '">Открыть глобальные стили</a> для редких общих настроек сайта. Повседневная работа со страницей должна происходить здесь, на главном визуальном экране.</div>' +
                    '<div class="small text-muted mt-3">Устройства предпросмотра: ' + escapeHtml(state.deviceKeys.map(getDeviceTitle).join(', ')) + '</div>';
                widgetForm.innerHTML = 'Выберите секцию, колонку или системный виджет, чтобы открыть локальные настройки.';
                initTooltips(selectionControls);
                return;
            }

            if (selection.type === 'section') {
                const section = state.schema.sections[selection.sectionIndex];
                selectionSummary.innerHTML = '<strong>Секция</strong><br><span class="text-muted">' + escapeHtml(section.title) + '</span>';
                selectionControls.innerHTML = '' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Название секции', 'Это имя видят редакторы внутри конструктора. На сайте его можно не показывать.') +
                        '<input type="text" class="lb-control" data-field="title" value="' + escapeHtml(section.title) + '">' +
                    '</div>' +
                    '<div class="lb-field">' +
                        fieldLabel('Схема колонок', 'Определяет, сколько колонок будет в секции и как они распределяются по ширине.') +
                        '<select class="lb-control" data-field="layout">' +
                            renderSelectOptions(layoutOptions, section.layout) +
                        '</select>' +
                    '</div>' +
                    renderSelectField('Тип секции', 'Смысл секции на странице: первый экран, контент, действие, каталог и так далее.', 'section_type', sectionTypeOptions, section.section_type) +
                    renderSelectField('Стилевой пресет', 'Готовый пресет оформления секции.', 'style_preset', sectionStyleOptions, section.style_preset) +
                    renderSelectField('Тон фона', 'Быстрый выбор общего тона секции без ручной CSS-настройки.', 'background_tone', backgroundToneOptions, section.background_tone) +
                    renderSelectField('Пресет контейнера', 'Управляет рабочей шириной секции.', 'container_preset', pageThemeOptions.container_preset, section.container_preset) +
                    renderSelectField('Вертикальный ритм', 'Отступы сверху и снизу для секции.', 'spacing_preset', spacingPresetOptions, section.spacing_preset) +
                    '<div class="lb-field">' +
                        fieldLabel('Дополнительное оформление', 'Служебное поле для особого оформления секции. Если оно не нужно, оставьте поле пустым.') +
                        '<input type="text" class="lb-control" data-field="settings.css_class" value="' + escapeHtml(section.settings.css_class || '') + '">' +
                    '</div>' +
                    '<div class="lb-field">' +
                        fieldLabel('Служебный CSS-класс фона', 'Нужно только если для секции уже подготовлен отдельный backend/frontend класс.') +
                        '<input type="text" class="lb-control" data-field="settings.background_class" value="' + escapeHtml(section.settings.background_class || '') + '">' +
                    '</div>' +
                    '<div class="lb-field">' +
                        fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть секцию на компьютере, планшете или телефоне.') +
                        renderVisibilityControls('visibility', section.visibility) +
                    '</div>' +
                    '<button type="button" class="lb-btn lb-btn--danger" data-action="delete-section" data-section-index="' + selection.sectionIndex + '">Удалить секцию</button>';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                initTooltips(selectionControls);
                return;
            }

            if (selection.type === 'column') {
                const column = getSelectedColumn();
                selectionSummary.innerHTML = '<strong>Колонка</strong><br><span class="text-muted">' + escapeHtml(column ? column.title : 'Колонка') + '</span>';
                selectionControls.innerHTML = '' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Название колонки', 'Служебное имя для редактора. Помогает не путаться в сложных секциях.') +
                        '<input type="text" class="lb-control" data-field="title" value="' + escapeHtml(column.title) + '">' +
                    '</div>' +
                    renderWidthControls(column.width) +
                    '<div class="lb-field">' +
                        fieldLabel('Выравнивание содержимого', 'Помогает прижать содержимое колонки к верху, центру или низу.') +
                        '<select class="lb-control" data-field="settings.align">' +
                            alignOptions.map(function (option) {
                                return '<option value="' + option.value + '"' + (column.settings.align === option.value ? ' selected' : '') + '>' + option.title + '</option>';
                            }).join('') +
                        '</select>' +
                    '</div>' +
                    '<div class="lb-field">' +
                        fieldLabel('Дополнительное оформление', 'Служебное поле для особого оформления колонки. Если оно не нужно, оставьте поле пустым.') +
                        '<input type="text" class="lb-control" data-field="settings.css_class" value="' + escapeHtml(column.settings.css_class || '') + '">' +
                    '</div>' +
                    '<div class="lb-field">' +
                        fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть колонку на нужных типах устройств.') +
                        renderVisibilityControls('visibility', column.visibility) +
                    '</div>' +
                    '<div class="small text-muted">В эту колонку можно перетаскивать блоки и виджеты из других колонок.</div>';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                initTooltips(selectionControls);
                return;
            }

            const node = getSelectedNode();
            if (!node) {
                selectionSummary.textContent = 'Выбранный элемент не найден';
                selectionControls.innerHTML = '';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                return;
            }

            selectionSummary.innerHTML = '<strong>' + escapeHtml(getNodeDisplayLabel(node)) + '</strong><br><span class="text-muted">Тип: ' + escapeHtml(getNodeTypeTitle(node.type)) + '</span>';
            selectionControls.innerHTML = '' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Название элемента', 'Короткое понятное имя, по которому редактор узнает блок внутри конструктора.') +
                    '<input type="text" class="lb-control" data-field="label" value="' + escapeHtml(node.label || '') + '">' +
                '</div>' +
                '<div class="lb-field">' +
                    fieldLabel('Дополнительное оформление', 'Служебное поле для особого оформления конкретного элемента.') +
                    '<input type="text" class="lb-control" data-field="class_name" value="' + escapeHtml(node.class_name || '') + '">' +
                '</div>' +
                (node.type === 'block'
                    ? renderSelectField('Semantic-пресет блока', 'Определяет смысловой сценарий блока и набор его базовых полей.', 'source_key', getBlockPresetOptions(node.source_key || ''), node.source_key || '')
                        + renderBlockSemanticInspector(node)
                    : '<div class="lb-field">' +
                        fieldLabel('Источник данных блока', 'Нужен только если блок должен брать данные из заранее заданного сценария или источника.') +
                        '<input type="text" class="lb-control" data-field="source_key" value="' + escapeHtml(node.source_key || '') + '">' +
                      '</div>') +
                '<div class="lb-field">' +
                    fieldLabel('Заметки для редактора', 'Сюда можно записать, зачем нужен блок или что в нем важно не забыть.') +
                    '<textarea class="lb-control" rows="3" data-field="notes">' + escapeHtml(node.notes || '') + '</textarea>' +
                '</div>' +
                '<div class="lb-field">' +
                    fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть этот элемент на нужных типах устройств.') +
                    renderVisibilityControls('device_visibility', node.device_visibility) +
                '</div>' +
                '<button type="button" class="lb-btn lb-btn--danger" data-action="delete-node" data-section-index="' + selection.sectionIndex + '" data-column-index="' + selection.columnIndex + '" data-node-index="' + selection.nodeIndex + '">Удалить элемент</button>';

            if (node.type === 'system_widget' && node.widget_id) {
                loadWidgetOptions(node);
            } else if (node.type === 'system_widget') {
                widgetForm.innerHTML = 'У этого виджета пока нет связи с системным каталогом. Добавьте его заново из списка слева.';
            } else {
	                widgetForm.innerHTML = 'Для этого semantic-блока доступны preset-поля, видимость, заметки и локальное оформление.';
            }

            initTooltips(selectionControls);
        }

        function countWidgetNodes() {
            let count = 0;
            state.schema.sections.forEach(function (section) {
                section.columns.forEach(function (column) {
                    column.nodes.forEach(function (node) {
                        if (node.type === 'system_widget') {
                            count += 1;
                        }
                    });
                });
            });
            return count;
        }

        async function loadWidgetCatalog() {
            widgetList.innerHTML = '<div class="text-muted">Загрузка списка виджетов...</div>';

            try {
                const response = await fetch(state.screen.api.widgets_catalog_url, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (result.error) {
                    const message = result.message ? String(result.message) : 'Не удалось загрузить список виджетов.';
                    widgetList.innerHTML = '<div class="text-danger">' + escapeHtml(message) + '</div>';
                    return;
                }

                state.widgetsCatalog = result.widgets || {};
                renderWidgetLibrary();
                renderCanvas();
            } catch (error) {
                widgetList.innerHTML = '<div class="text-danger">Не удалось загрузить список виджетов. Проверьте ответ сервера.</div>';
            }
        }

        async function loadVersions() {
            const response = await fetch(state.screen.api.versions_url + '?page_key=' + encodeURIComponent(state.page.key), {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (!result.error) {
                state.screen.versions = result.versions || [];
            }

            renderVersions();
        }

        function renderVersions() {
            const versions = state.screen.versions || [];
            if (!versions.length) {
                versionsList.innerHTML = '<div class="text-muted">Версий пока нет. Первая появится после первого сохранения страницы.</div>';
                return;
            }

            versionsList.innerHTML = versions.map(function (version) {
                return '' +
                    '<div class="lb-version-card">' +
                        '<div class="lb-version-card__top">' +
                            '<span class="lb-version-card__id">#' + version.id + '</span>' +
                            '<button type="button" class="lb-btn lb-btn--panel" data-action="restore-version" data-version-id="' + version.id + '">Восстановить</button>' +
                        '</div>' +
                        '<div class="lb-version-card__meta">' + escapeHtml(version.created_at) + '</div>' +
                        '<div class="lb-version-card__note">' + escapeHtml(version.version_note || 'Без комментария') + '</div>' +
                    '</div>';
            }).join('');
        }

        async function loadWidgetOptions(node) {
            const templateName = state.page.template || 'nordic';
            const nodeUid = node && node.uid ? String(node.uid) : '';
            const formKey = String(node.widget_id || '') + '::' + String(templateName) + '::' + nodeUid;

            if (widgetForm && widgetForm.dataset && widgetForm.dataset.formKey === formKey && widgetForm.querySelector('form')) {
                return;
            }

            if (widgetForm && widgetForm.dataset) {
                widgetForm.dataset.formKey = formKey;
            }

            widgetForm.innerHTML = 'Загрузка формы настроек виджета...';

            const body = new URLSearchParams();
            body.set('widget_id', node.widget_id);
            body.set('template', templateName);
            body.set('options', JSON.stringify(node.options || {}));

            try {
                const response = await fetch(state.screen.api.widget_options_url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString(),
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (widgetForm && widgetForm.dataset && widgetForm.dataset.formKey !== formKey) {
                    return;
                }
                if (result.error) {
                    const message = result.message ? String(result.message) : 'Не удалось загрузить форму настроек виджета.';
                    widgetForm.innerHTML = '<div class="text-danger">' + escapeHtml(message) + '</div>';
                    return;
                }

                const html = (result.html || '').trim();
                if (!html) {
                    widgetForm.innerHTML = '<div class="lb-widget-placeholder small">У этого системного виджета нет отдельной формы настроек. Его поведение задается самим компонентом и общими параметрами шаблона.</div>';
                    return;
                }

                widgetForm.innerHTML = html;
                initTooltips(widgetForm);

                const shouldAutoFocus = state.ui && String(state.ui.widgetOptionsAutofocusFor || '') === String(node.widget_id || '');
                if (shouldAutoFocus && widgetForm && widgetForm.dataset && widgetForm.dataset.formKey === formKey) {
                    if (typeof widgetForm.scrollIntoView === 'function') {
                        widgetForm.scrollIntoView({block: 'nearest'});
                    }
                    const firstControl = widgetForm.querySelector('select, input, textarea');
                    if (firstControl && typeof firstControl.focus === 'function') {
                        firstControl.focus();
                    }

                    state.ui.widgetOptionsAutofocusFor = '';
                }
            } catch (error) {
                widgetForm.innerHTML = '<div class="text-danger">Не удалось загрузить форму настроек виджета. Проверьте маршрут и ответ сервера.</div>';
            }
        }

        function moveSection(fromIndex, targetIndex) {
            if (fromIndex === targetIndex || fromIndex < 0 || targetIndex < 0) {
                return;
            }

            const toIndex = fromIndex < targetIndex ? targetIndex - 1 : targetIndex;
            state.schema.sections = moveArrayItem(state.schema.sections, fromIndex, toIndex);
            state.selection = {type: 'section', sectionIndex: toIndex};
            setCanvasDirty(true);
        }

        function moveNode(fromSectionIndex, fromColumnIndex, fromNodeIndex, toSectionIndex, toColumnIndex, toNodeIndex) {
            const sourceSection = state.schema.sections[fromSectionIndex];
            const targetSection = state.schema.sections[toSectionIndex];

            if (!sourceSection || !targetSection) {
                return;
            }

            const sourceColumn = sourceSection.columns[fromColumnIndex];
            const targetColumn = targetSection.columns[toColumnIndex];

            if (!sourceColumn || !targetColumn) {
                return;
            }

            const movedNodes = sourceColumn.nodes.splice(fromNodeIndex, 1);
            if (!movedNodes.length) {
                return;
            }

            const node = movedNodes[0];
            let insertIndex = typeof toNodeIndex === 'number' ? toNodeIndex : targetColumn.nodes.length;

            if (sourceColumn === targetColumn && fromNodeIndex < insertIndex) {
                insertIndex -= 1;
            }

            targetColumn.nodes.splice(insertIndex, 0, node);
            state.selection = {
                type: 'node',
                sectionIndex: toSectionIndex,
                columnIndex: toColumnIndex,
                nodeIndex: insertIndex
            };

            setCanvasDirty(true);
        }

        function setSelection(selection) {
            syncSelectedWidgetFormIntoState();
            state.selection = selection;

            if (selection && getWorkspaceMode() === 'mobile') {
                setDrawerOpen('inspector', true);
            }

            renderCanvas();
        }

        function addSection() {
            const sectionUid = uid('section');
            const layout = state.screen.default_section_layout || '1col';
            const section = normalizeSection({
                uid: sectionUid,
                title: 'Секция ' + (state.schema.sections.length + 1),
                layout: layout,
                section_type: 'content',
                style_preset: 'content',
                background_tone: 'base',
                container_preset: state.schema.theme.container_preset || 'standard',
                spacing_preset: 'md',
                visibility: defaultVisibility(),
                settings: {background_class: '', padding: 'md', css_class: ''},
                columns: []
            }, state.schema.sections.length);

            state.schema.sections.push(section);
            setSelection({type: 'section', sectionIndex: state.schema.sections.length - 1});
            setCanvasDirty(true);
        }

        function insertSectionPreset(presetIndex) {
            const preset = Array.isArray(state.screen.section_presets) ? state.screen.section_presets[presetIndex] : null;
            if (!preset) {
                return;
            }

            const sectionUid = uid('section');
            const columns = Array.isArray(preset.columns) ? preset.columns.map(function (column, columnIndex) {
                const columnUid = sectionUid + '-column-' + (columnIndex + 1);
                return {
                    uid: columnUid,
                    title: column.title || ('Колонка ' + (columnIndex + 1)),
                    visibility: defaultVisibility(),
                    width: defaultColumnWidth(),
                    settings: {align: 'stretch', css_class: ''},
                    nodes: Array.isArray(column.nodes) ? column.nodes.map(function (node, nodeIndex) {
                        return normalizeNode(Object.assign({}, node || {}), columnUid, nodeIndex);
                    }) : []
                };
            }) : [];

            state.schema.sections.push(normalizeSection({
                uid: sectionUid,
                title: preset.title || ('Секция ' + (state.schema.sections.length + 1)),
                layout: preset.layout || '1col',
                section_type: preset.section_type || 'content',
                style_preset: preset.style_preset || 'content',
                background_tone: preset.background_tone || 'base',
                container_preset: preset.container_preset || state.schema.theme.container_preset || 'standard',
                spacing_preset: preset.spacing_preset || 'md',
                visibility: defaultVisibility(),
                settings: {background_class: '', padding: 'md', css_class: ''},
                columns: columns
            }, state.schema.sections.length));


            setSelection({type: 'section', sectionIndex: state.schema.sections.length - 1});
            setCanvasDirty(true);
        }

        function insertSectionPresetByKey(presetKey) {
            const presets = Array.isArray(state.screen.section_presets) ? state.screen.section_presets : [];
            const preset = presets.find(function (item) {
                return item && String(item.key || '') === String(presetKey || '');
            });
            if (!preset) {
                return;
            }

            const sectionUid = uid('section');
            const columns = Array.isArray(preset.columns) ? preset.columns.map(function (column, columnIndex) {
                const columnUid = sectionUid + '-column-' + (columnIndex + 1);
                return {
                    uid: columnUid,
                    title: column.title || ('Колонка ' + (columnIndex + 1)),
                    visibility: defaultVisibility(),
                    width: defaultColumnWidth(),
                    settings: {align: 'stretch', css_class: ''},
                    nodes: Array.isArray(column.nodes) ? column.nodes.map(function (node, nodeIndex) {
                        return normalizeNode(Object.assign({}, node || {}), columnUid, nodeIndex);
                    }) : []
                };
            }) : [];

            state.schema.sections.push(normalizeSection({
                uid: sectionUid,
                title: preset.title || ('Секция ' + (state.schema.sections.length + 1)),
                layout: preset.layout || '1col',
                section_type: preset.section_type || 'content',
                style_preset: preset.style_preset || 'content',
                background_tone: preset.background_tone || 'base',
                container_preset: preset.container_preset || state.schema.theme.container_preset || 'standard',
                spacing_preset: preset.spacing_preset || 'md',
                visibility: defaultVisibility(),
                settings: {background_class: '', padding: 'md', css_class: ''},
                columns: columns
            }, state.schema.sections.length));


            setSelection({type: 'section', sectionIndex: state.schema.sections.length - 1});
            setCanvasDirty(true);
        }

        function insertBlock(blockIndex) {
            const column = getSelectedColumn();
            if (!column) {
                window.alert('Сначала выберите колонку на макете страницы.');
                return;
            }

            const block = blockPresets[blockIndex];
            if (!block) {
                return;
            }

            column.nodes.push({
                uid: uid('node'),
                type: 'block',
                label: block.default_label || block.title,
                class_name: '',
                notes: '',
                source_key: block.key,
                device_visibility: defaultVisibility(),
                options: Object.assign({}, getBlockPresetDefaults(block))
            });

            setSelection({
                type: 'node',
                sectionIndex: state.selection.sectionIndex,
                columnIndex: state.selection.columnIndex,
                nodeIndex: column.nodes.length - 1
            });

            setCanvasDirty(true);
        }

        function findWidgetById(widgetId) {
            const groups = Object.keys(state.widgetsCatalog);
            for (let index = 0; index < groups.length; index += 1) {
                const items = state.widgetsCatalog[groups[index]] || [];
                for (let inner = 0; inner < items.length; inner += 1) {
                    if (String(items[inner].id) === String(widgetId)) {
                        return items[inner];
                    }
                }
            }
            return null;
        }

        function insertWidget(widgetId) {
            const column = getSelectedColumn();
            if (!column) {
                window.alert('Сначала выберите колонку на макете страницы.');
                return;
            }

            const widget = findWidgetById(widgetId);
            if (!widget) {
                window.alert('Виджет не найден в каталоге.');
                return;
            }

            column.nodes.push({
                uid: uid('node'),
                type: 'system_widget',
                label: widget.title || 'Системный виджет',
                widget_id: widget.id,
                widget_name: widget.name,
                widget_controller: widget.controller,
                options: {},
                device_visibility: defaultVisibility(),
                class_name: '',
                notes: '',
                source_key: ''
            });

            setDrawerOpen('inspector', true);

            if (state.ui) {
                state.ui.widgetOptionsAutofocusFor = String(widget.id);
            }

            setSelection({
                type: 'node',
                sectionIndex: state.selection.sectionIndex,
                columnIndex: state.selection.columnIndex,
                nodeIndex: column.nodes.length - 1
            });

            setCanvasDirty(true);
        }

        async function saveCanvas() {
            syncSelectedWidgetFormIntoState();

            const body = new URLSearchParams();
            body.set('page_key', state.page.key);
            body.set('schema', JSON.stringify(state.schema));
            body.set('version_note', versionNote.value || 'Сохранение страницы');

            const response = await fetch(state.screen.api.canvas_save_url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                window.alert(result.message || 'Не удалось сохранить страницу');
                return;
            }

            state.page = Object.assign({}, state.page, result.page || {});
            updatedAt.textContent = state.page.updated_at || '';
            pageMeta.innerHTML = renderPageMeta();
            lastSavedAt = state.page.updated_at ? String(state.page.updated_at) : lastSavedAt;
            setCanvasDirty(false);
            versionNote.value = '';
            await loadVersions();
            renderCanvas();
        }

        function clearDropTargets() {
            canvasRoot.querySelectorAll('.lb-drop-target').forEach(function (element) {
                element.classList.remove('lb-drop-target');
            });
        }

        function getDropElement(event) {
            if (!state.drag) {
                return null;
            }

            if (state.drag.type === 'section') {
                return event.target.closest('[data-role="section"]');
            }

            return event.target.closest('[data-role="node"], [data-role="column"]');
        }

        async function restoreVersion(versionId) {
            if (!window.confirm('Восстановить выбранную сохраненную версию страницы?')) {
                return;
            }

            const body = new URLSearchParams();
            body.set('version_id', versionId);

            const response = await fetch(state.screen.api.version_restore_url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                window.alert(result.message || 'Не удалось восстановить версию');
                return;
            }

            state.page = Object.assign({}, state.page, result.page || {});
            state.schema = result.schema || {sections: []};
            state.screen.versions = result.versions || [];
            state.selection = null;
            updatedAt.textContent = state.page.updated_at || '';
            renderCanvas();
            renderVersions();
        }

        document.querySelectorAll('.lb-library-tab').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelectorAll('.lb-library-tab').forEach(function (item) {
                    item.classList.remove('active');
                });
                button.classList.add('active');
                document.querySelectorAll('.lb-library-panel').forEach(function (panel) {
                    panel.classList.add('d-none');
                });
                const activePanel = document.getElementById('lb-' + button.dataset.tab + '-library');
                if (activePanel) {
                    activePanel.classList.remove('d-none');
                }
            });
        });

        document.querySelectorAll('.lb-device-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelectorAll('.lb-device-toggle').forEach(function (item) {
                    item.classList.remove('active');
                });
                button.classList.add('active');
                state.activeDevice = button.dataset.device;
                renderCanvas();
            });
        });

        drawerToggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                if (!button.dataset.drawerToggle) {
                    return;
                }

                toggleDrawer(button.dataset.drawerToggle);
            });
        });

        document.getElementById('lb-add-section').addEventListener('click', addSection);
        pageThemeButton.addEventListener('click', function () {
            setDrawerOpen('inspector', true);
            setSelection({type: 'page'});
        });
        document.getElementById('lb-save-canvas').addEventListener('click', function () {
            saveCanvas().catch(function (error) {
                console.error(error);
                window.alert('Ошибка сохранения страницы');
            });
        });
        const inspectorSaveButton = document.getElementById('lb-save-canvas-inspector');
        if (inspectorSaveButton) {
            inspectorSaveButton.addEventListener('click', function () {
                saveCanvas().catch(function (error) {
                    console.error(error);
                    window.alert('Ошибка сохранения страницы');
                });
            });
        }
        document.getElementById('lb-reload-widgets').addEventListener('click', function () {
            loadWidgetCatalog().catch(function (error) {
                console.error(error);
                widgetList.innerHTML = '<div class="text-danger">Ошибка загрузки списка виджетов.</div>';
            });
        });
        document.getElementById('lb-refresh-versions').addEventListener('click', function () {
            loadVersions().catch(function (error) {
                console.error(error);
            });
        });

        selectionControls.addEventListener('change', function (event) {
            const field = event.target.closest('[data-field]');
            if (!field) {
                return;
            }

            const target = getSelectionTarget();
            if (!target) {
                return;
            }

            syncSelectedWidgetFormIntoState();

            const value = field.type === 'checkbox' ? field.checked : field.value;
            setDeepValue(target, field.dataset.field, value);

	        if (state.selection && state.selection.type === 'node' && target.type === 'block' && field.dataset.field === 'source_key') {
	            applyBlockPresetToNode(target, false);
	        }

            if (state.selection && state.selection.type === 'section' && field.dataset.field === 'layout') {
                syncSectionColumnsWithLayout(target);
            }

            if (state.selection && state.selection.type === 'page' && field.dataset.field === 'theme.template_preset') {
                syncTemplatePresetLayoutTemplate();
                syncPageLayoutWithEffectiveShell();
            }

            if (state.selection && state.selection.type === 'page' && field.dataset.field === 'layout.shell_variant') {
                syncPageLayoutWithEffectiveShell();
            }

            setCanvasDirty(true);
            renderCanvas();
        });

        function handleActionTarget(actionTarget) {
            if (!actionTarget) {
                return false;
            }

            const sectionIndex = Number(actionTarget.dataset.sectionIndex);
            const columnIndex = Number(actionTarget.dataset.columnIndex);
            const nodeIndex = Number(actionTarget.dataset.nodeIndex);
            const action = actionTarget.dataset.action;

            if (action === 'delete-section') {
                if (window.confirm('Удалить секцию?')) {
                    syncSelectedWidgetFormIntoState();
                    state.schema.sections.splice(sectionIndex, 1);
                    state.selection = null;
                    setCanvasDirty(true);
                    renderCanvas();
                }
                return true;
            }

            if (action === 'delete-node') {
                if (window.confirm('Удалить элемент?')) {
                    syncSelectedWidgetFormIntoState();
                    state.schema.sections[sectionIndex].columns[columnIndex].nodes.splice(nodeIndex, 1);
                    state.selection = null;
                    setCanvasDirty(true);
                    renderCanvas();
                }
                return true;
            }

            return false;
        }

        selectionControls.addEventListener('click', function (event) {
            const actionTarget = event.target.closest('[data-action]');
            if (handleActionTarget(actionTarget)) {
                event.preventDefault();
            }
        });

        canvasRoot.addEventListener('dragstart', function (event) {
            const target = event.target.closest('[data-drag-kind]');
            if (!target) {
                return;
            }

            syncSelectedWidgetFormIntoState();

            if (target.dataset.dragKind === 'section') {
                state.drag = {
                    type: 'section',
                    sectionIndex: Number(target.dataset.sectionIndex)
                };
            }

            if (target.dataset.dragKind === 'node') {
                state.drag = {
                    type: 'node',
                    sectionIndex: Number(target.dataset.sectionIndex),
                    columnIndex: Number(target.dataset.columnIndex),
                    nodeIndex: Number(target.dataset.nodeIndex)
                };
            }

            target.classList.add('lb-dragging');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', target.dataset.dragKind || 'drag');
            }
        });

        canvasRoot.addEventListener('dragend', function () {
            state.drag = null;
            clearDropTargets();
            canvasRoot.querySelectorAll('.lb-dragging').forEach(function (element) {
                element.classList.remove('lb-dragging');
            });
        });

        canvasRoot.addEventListener('dragover', function (event) {
            const target = getDropElement(event);

            if (!target && state.drag && state.drag.type === 'section') {
                event.preventDefault();
                clearDropTargets();
                return;
            }

            if (!target) {
                return;
            }

            event.preventDefault();
            clearDropTargets();
            target.classList.add('lb-drop-target');
        });

        canvasRoot.addEventListener('drop', function (event) {
            if (!state.drag) {
                return;
            }

            event.preventDefault();

            const target = getDropElement(event);

            if (state.drag.type === 'section') {
                if (target) {
                    moveSection(state.drag.sectionIndex, Number(target.dataset.sectionIndex));
                } else {
                    const fromIndex = state.drag.sectionIndex;
                    const lastIndex = state.schema.sections.length - 1;
                    if (fromIndex !== lastIndex) {
                        state.schema.sections = moveArrayItem(state.schema.sections, fromIndex, lastIndex);
                        state.selection = {type: 'section', sectionIndex: lastIndex};
                    }
                }

                renderCanvas();
                clearDropTargets();
                return;
            }

            if (!target) {
                clearDropTargets();
                return;
            }

            if (target.dataset.role === 'column') {
                moveNode(
                    state.drag.sectionIndex,
                    state.drag.columnIndex,
                    state.drag.nodeIndex,
                    Number(target.dataset.sectionIndex),
                    Number(target.dataset.columnIndex)
                );
            }

            if (target.dataset.role === 'node') {
                moveNode(
                    state.drag.sectionIndex,
                    state.drag.columnIndex,
                    state.drag.nodeIndex,
                    Number(target.dataset.sectionIndex),
                    Number(target.dataset.columnIndex),
                    Number(target.dataset.nodeIndex)
                );
            }

            renderCanvas();
            clearDropTargets();
        });

        canvasRoot.addEventListener('click', function (event) {
            const actionTarget = event.target.closest('[data-action]');
            if (handleActionTarget(actionTarget)) {
                event.preventDefault();
                return;
            }

            const nodeTarget = event.target.closest('[data-role="node"]');
            if (nodeTarget) {
                setSelection({
                    type: 'node',
                    sectionIndex: Number(nodeTarget.dataset.sectionIndex),
                    columnIndex: Number(nodeTarget.dataset.columnIndex),
                    nodeIndex: Number(nodeTarget.dataset.nodeIndex)
                });
                return;
            }

            const columnTarget = event.target.closest('[data-role="column"]');
            if (columnTarget) {
                setSelection({
                    type: 'column',
                    sectionIndex: Number(columnTarget.dataset.sectionIndex),
                    columnIndex: Number(columnTarget.dataset.columnIndex)
                });
                return;
            }

            const sectionTarget = event.target.closest('[data-role="section"]');
            if (sectionTarget) {
                setSelection({
                    type: 'section',
                    sectionIndex: Number(sectionTarget.dataset.sectionIndex)
                });
                return;
            }

            const pageTarget = event.target.closest('[data-role="page"]');
            if (pageTarget || event.target === canvasRoot) {
                setSelection({type: 'page'});
            }
        });

        widgetList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-role="insert-widget"]');
            if (!button) {
                return;
            }

            insertWidget(button.dataset.widgetId);
        });

        blockList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-role="insert-block"]');
            if (!button) {
                return;
            }

            insertBlock(Number(button.dataset.blockIndex));
        });

        sectionList.addEventListener('click', function (event) {
            const emptyButton = event.target.closest('[data-role="insert-empty-section"]');
            if (emptyButton) {
                addSection();
                return;
            }

            const presetButton = event.target.closest('[data-role="insert-section-preset"]');
            if (!presetButton) {
                return;
            }

            if (presetButton.dataset.presetKey) {
                insertSectionPresetByKey(String(presetButton.dataset.presetKey));
                return;
            }

            insertSectionPreset(Number(presetButton.dataset.presetIndex));
        });

        versionsList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-action="restore-version"]');
            if (!button) {
                return;
            }

            restoreVersion(button.dataset.versionId).catch(function (error) {
                console.error(error);
                window.alert('Ошибка восстановления версии');
            });
        });

        renderSectionLibrary();
        renderBlockLibrary();
        ensureSelection();
        syncWorkspaceShell();
        renderCanvas();
        renderVersions();
        loadWidgetCatalog().catch(function (error) {
            console.error(error);
            widgetList.innerHTML = '<div class="text-danger">Ошибка загрузки списка виджетов.</div>';
        });
        window.addEventListener('resize', function () {
            syncWorkspaceShell();
            applyDeviceViewport();
        });
        initTooltips(document);
    })();
</script>
<?php $this->addBottom(ob_get_clean()); ?>