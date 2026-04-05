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
$this->addBreadcrumb('Страницы', href_to('admin', 'controllers', ['edit', 'landingbuilder', 'pages']));
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

    .lb-device-switcher .btn,
    .lb-topbar__end .btn {
        white-space: nowrap;
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

    .lb-library-tab {
        border-radius: 14px;
        background: transparent;
        color: #425466;
        font-weight: 600;
    }

    .lb-library-tab.active {
        background: #132236 !important;
        color: #ffffff !important;
    }

    .lb-workspace .list-group-item {
        border-radius: 16px !important;
        border: 1px solid #e3eaf1;
        margin-bottom: 0.5rem;
    }

    .lb-workspace .list-group-item:last-child {
        margin-bottom: 0;
    }

    #lb-widget-form form {
        margin-bottom: 0;
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
            <a class="btn btn-outline-light btn-sm" href="<?php html(href_to('admin', 'controllers', ['edit', 'landingbuilder', 'pages'])); ?>">К страницам</a>
            <div class="lb-topbar__page">
                <div class="lb-topbar__eyebrow">Редактор страницы</div>
                <h1 class="lb-topbar__title"><?php html($page['title']); ?></h1>
                <div class="lb-topbar__meta"><span id="lb-page-meta">Ключ страницы: <code><?php html($page['key']); ?></code> | Режим: <?php html($page_mode_title); ?> | Статус: <?php html($page_status_title); ?></span></div>
            </div>
        </div>
        <div class="lb-topbar__center">
            <div class="btn-group lb-device-switcher" role="group" aria-label="Устройства">
            <?php foreach ($screen['devices'] as $index => $device) { ?>
                <button type="button" class="btn btn-outline-light btn-sm lb-device-toggle<?php if ($index === 0) { ?> active<?php } ?>" data-device="<?php html($device['key']); ?>"><?php html($device['title']); ?></button>
            <?php } ?>
            </div>
            <span class="lb-device-width">Viewport: <span class="ml-1" id="lb-device-width-label"><?php echo !empty($screen['devices'][0]['viewport_width']) ? (int) $screen['devices'][0]['viewport_width'] . 'px' : 'Авто'; ?></span></span>
        </div>
        <div class="lb-topbar__end">
            <div class="lb-topbar__save">Последнее обновление: <span id="lb-updated-at"><?php html($page['updated_at']); ?></span></div>
            <button type="button" class="btn btn-outline-light btn-sm" data-drawer-toggle="library">Библиотека</button>
            <button type="button" class="btn btn-outline-light btn-sm" data-drawer-toggle="inspector">Инспектор</button>
            <button type="button" class="btn btn-outline-light btn-sm" id="lb-add-section">Добавить секцию</button>
                <a class="btn btn-outline-light btn-sm" href="<?php html($screen['design_url']); ?>">Глобальные стили</a>
                <button type="button" class="btn btn-outline-light btn-sm" id="lb-edit-page-theme">Стиль на холсте</button>
            <a class="btn btn-outline-light btn-sm" href="<?php html($screen['preview_url']); ?>" target="_blank" rel="noopener">Предпросмотр</a>
            <button type="button" class="btn btn-primary btn-sm" id="lb-save-canvas">Сохранить</button>
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
                <button type="button" class="btn btn-sm btn-outline-secondary" data-drawer-toggle="library">Скрыть</button>
            </div>
            <div class="lb-drawer__body">
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Что можно сделать</div>
                    <div class="small text-muted">Сначала выбери секцию, колонку или страницу. Затем добавляй готовые секции, блоки и системные виджеты.</div>
                </div>
                <div class="lb-panel-block">
                    <ul class="nav nav-pills flex-column mb-3">
                        <?php foreach ($screen['left_tabs'] as $index => $tab) { ?>
                            <li class="nav-item mb-2">
                                <button type="button" class="nav-link text-left w-100 border-0 lb-library-tab<?php if ($index === 0) { ?> active<?php } ?>" data-tab="<?php html($tab['key']); ?>"><?php html($tab['title']); ?></button>
                            </li>
                        <?php } ?>
                    </ul>
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
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="lb-reload-widgets">Обновить</button>
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
                        <div class="lb-canvas-stage__eyebrow">Canvas workspace</div>
                        <h2 class="lb-canvas-stage__title">Живой холст страницы</h2>
                        <p class="lb-canvas-stage__desc">Главная работа со стилем должна происходить здесь: кликните по странице, секции или элементу, меняйте настройки справа и сразу видьте результат на самом холсте.</p>
                    </div>
                    <div class="lb-canvas-stage__status" id="lb-canvas-status"><?php if ($screen['schema_installed']) { ?>Работа с базой данных<?php } else { ?>Временный режим без базы<?php } ?></div>
                </div>
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
                    <p class="lb-drawer__desc">Настройки страницы, секции, колонки или элемента. Основная visual-first работа со стилем должна происходить здесь, а не на отдельном экране настроек.</p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-drawer-toggle="inspector">Скрыть</button>
            </div>
            <div class="lb-drawer__body">
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Статус страницы</div>
                    <p class="mb-2"><strong>Виджетов на странице:</strong> <span id="lb-widget-count"><?php echo count($screen['widget_nodes']); ?></span></p>
                    <label class="small text-muted d-block mb-1">Комментарий версии</label>
                    <input type="text" class="form-control form-control-sm" id="lb-version-note" placeholder="Например: перестроил первый экран и боковую колонку">
                </div>
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Выделение</div>
                    <div id="lb-selection-summary" class="small">Ничего не выбрано</div>
                    <div id="lb-selection-controls" class="mt-3"></div>
                </div>
                <div class="lb-panel-block">
                    <div class="lb-panel-block__title">Настройки виджета</div>
                    <div id="lb-widget-form" class="border rounded p-2 bg-light small">Выберите системный виджет на макете, чтобы открыть его штатные настройки.</div>
                </div>
                <div class="lb-panel-block">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="lb-panel-block__title mb-0">История версий</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="lb-refresh-versions">Обновить</button>
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
        const blockPresets = [
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
        ];
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
        const deviceWidthLabel = document.getElementById('lb-device-width-label');
        const pageThemeButton = document.getElementById('lb-edit-page-theme');
        const drawerToggleButtons = document.querySelectorAll('[data-drawer-toggle]');

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
                    return preset.title;
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
            return '<label class="small text-muted d-block mb-1">' + escapeHtml(title) + (hint ? helpIcon(hint) : '') + '</label>';
        }

        function renderSelectOptions(options, currentValue) {
            return options.map(function (option) {
                return '<option value="' + escapeHtml(option.value) + '"' + (String(currentValue) === String(option.value) ? ' selected' : '') + '>' + escapeHtml(option.title) + '</option>';
            }).join('');
        }

        function renderSelectField(title, hint, field, options, value) {
            return '' +
                '<div class="form-group mb-2">' +
                    fieldLabel(title, hint) +
                    '<select class="form-control form-control-sm" data-field="' + field + '">' +
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
            const shellTitle = effectiveShell.variant ? effectiveShell.variant.title : 'Shell не определен';

            return 'Ключ страницы: <code>' + escapeHtml(state.page.key) + '</code> | Режим: ' + escapeHtml(getPageModeTitle(state.page.mode)) + ' | Статус: ' + escapeHtml(getPageStatusTitle(state.page.status)) + ' | Shell: ' + escapeHtml(shellTitle);
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

            const autoKey = pageShellScreen.auto_variant_key || '';
            const fallbackVariant = pageShellScreen.effective_variant || {};

            return {
                variant: catalog[autoKey] || {
                    key: autoKey,
                    title: fallbackVariant.title || autoKey || 'Базовый shell',
                    active_slots: normalizeShellSlotKeys(fallbackVariant.active_slots || []),
                    body_layout: fallbackVariant.body_layout || 'no_sidebars'
                },
                assignment_source: pageShellScreen.auto_assignment_source || fallbackVariant.assignment_source || 'default'
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
                return '<span class="badge badge-light border mr-1 mb-1">' + escapeHtml(getShellSlotTitle(slotKey)) + '</span>';
            }).join('');

            return '' +
                '<div class="border rounded p-2 mb-3 bg-light">' +
                    '<div class="font-weight-bold mb-1">Эффективный shell: ' + escapeHtml(variant.title || variant.key || 'Базовый shell') + '</div>' +
                    '<div class="small text-muted mb-1">Источник: ' + escapeHtml(getShellAssignmentSourceTitle(effectiveShell.assignment_source)) + '</div>' +
                    '<div class="small text-muted mb-2">Сценарий корпуса: ' + escapeHtml(variant.body_layout || 'no_sidebars') + '</div>' +
                    (state.page.mode === 'full_takeover'
                        ? '<div class="small text-muted mb-2">Основной slot страницы: ' + escapeHtml(getShellSlotTitle((state.schema.layout && state.schema.layout.content_slot) || 'content_body')) + '</div>'
                        : '<div class="small text-muted mb-2">Для overlay-страниц основной системный content slot задается adapter-ом.</div>') +
                    '<div>' + (slotBadges || '<span class="small text-muted">Активные shell slots не определены.</span>') + '</div>' +
                '</div>';
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
                    '<div class="form-group mb-2">' +
                        fieldLabel('Ширина для устройства «' + getDeviceTitle(device) + '»', 'Задает, сколько места колонка занимает на выбранном типе устройства.') +
                        '<select class="form-control form-control-sm" data-field="width.' + device + '">' +
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

            widgetForm.querySelectorAll('input, select, textarea').forEach(function (element) {
                if (!element.name) {
                    return;
                }

                if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
                    return;
                }

                if (element.tagName === 'SELECT' && element.multiple) {
                    options[element.name] = Array.from(element.selectedOptions).map(function (option) {
                        return option.value;
                    });
                    return;
                }

                if (element.type === 'checkbox') {
                    if (!Array.isArray(options[element.name])) {
                        options[element.name] = [];
                    }
                    options[element.name].push(element.value || '1');
                    return;
                }

                options[element.name] = element.value;
            });

            node.options = options;
        }

        function renderSectionLibrary() {
            const presets = Array.isArray(state.screen.section_presets) ? state.screen.section_presets : [];

            sectionList.innerHTML = '' +
                '<button type="button" class="list-group-item list-group-item-action mb-2" data-role="insert-empty-section">' +
                    '<div class="d-flex justify-content-between align-items-center">' +
                        '<span class="font-weight-bold">Пустая секция</span>' +
                        '<span class="badge badge-light">Создать</span>' +
                    '</div>' +
                    '<div class="small text-muted lb-library-caption mt-1">Чистая секция без контента. Удобно, если нужен свой состав колонок и блоков.</div>' +
                '</button>' +
                presets.map(function (preset, index) {
                    const columnsCount = Array.isArray(preset.columns) ? preset.columns.length : getLayoutColumnCount(preset.layout || '1col');
                    return '' +
                        '<button type="button" class="list-group-item list-group-item-action mb-2" data-role="insert-section-preset" data-preset-index="' + index + '">' +
                            '<div class="d-flex justify-content-between align-items-center">' +
                                '<span class="font-weight-bold">' + escapeHtml(preset.title) + '</span>' +
                                '<span class="badge badge-light">' + escapeHtml(String(columnsCount)) + ' кол.</span>' +
                            '</div>' +
                            '<div class="small text-muted lb-library-caption mt-1">' + escapeHtml(preset.description || 'Готовый стартовый состав секции.') + '</div>' +
                        '</button>';
                }).join('');

            initTooltips(sectionList);
        }

        function renderBlockLibrary() {
            blockList.innerHTML = blockPresets.map(function (block, index) {
                return '' +
                    '<button type="button" class="list-group-item list-group-item-action" data-role="insert-block" data-block-index="' + index + '">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<span class="font-weight-bold">' + escapeHtml(block.title) + '</span>' +
                            '<span class="badge badge-light">Добавить</span>' +
                        '</div>' +
                        '<div class="small text-muted lb-library-caption mt-1">' + escapeHtml(block.description) + '</div>' +
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
                                '<button type="button" class="list-group-item list-group-item-action mb-1 border rounded" data-role="insert-widget" data-widget-id="' + widget.id + '">' +
                                    '<div class="font-weight-bold">' + escapeHtml(widget.title) + '</div>' +
                                    '<div class="text-muted small">Код: ' + escapeHtml((widget.controller || 'core') + '.' + widget.name) + '</div>' +
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
                                '<div class="btn-group btn-group-sm">' +
                                    '<button type="button" class="btn btn-outline-danger" data-action="delete-section" data-section-index="' + sectionIndex + '">Удалить</button>' +
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
                                                                    '<button type="button" class="btn btn-link btn-sm text-danger p-0" data-action="delete-node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '">удалить</button>' +
                                                                '</div>' +
                                                                '<div class="lb-live-node__meta">' + (node.class_name ? 'Оформление: ' + escapeHtml(node.class_name) : 'Дополнительное оформление не задано') + (!nodeVisible ? ' | скрыто на устройстве «' + escapeHtml(getDeviceTitle(state.activeDevice)) + '»' : '') + '</div>' +
                                                            '</div>';
                                                    }).join('')
                                                    : '<div class="lb-live-column__empty">Колонка пока пустая. Добавьте блок или системный виджет из библиотеки слева.</div>') +
                                            '</div>';
                                    }).join('') +
                                '</div>' +
                            '</div>' +
                        '</section>';
                }).join('')
                : '<div class="lb-live-page__empty" data-role="page">Секция ещё не добавлена. Нажмите «Добавить секцию» и начните собирать страницу прямо на живом холсте.</div>';

            canvasRoot.innerHTML = '' +
                '<div class="lb-live-page" style="' + escapeHtml(pageThemeStyle) + '" data-role="page">' +
                    '<section class="lb-live-page__hero" data-role="page">' +
                        '<div class="lb-live-page__kicker">Visual-first canvas</div>' +
                        '<div class="lb-live-page__header">' +
                            '<div>' +
                                '<h2 class="lb-live-page__title">' + escapeHtml(state.page.title) + '</h2>' +
                                '<p class="lb-live-page__lead">Это рабочее превью страницы. Главная идея нового потока: вы меняете стиль прямо здесь, а отдельный экран глобальных стилей нужен только для редких site-wide defaults.</p>' +
                            '</div>' +
                        '</div>' +
                        '<div class="lb-live-page__chips">' +
                            '<span class="lb-live-pill"><strong>Shell</strong><span>' + escapeHtml(effectiveShellTitle) + '</span></span>' +
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
                selectionSummary.innerHTML = '<strong>Страница</strong><br><span class="text-muted">' + escapeHtml(state.page.title) + ' · live styling на canvas</span>';
                selectionControls.innerHTML = '' +
                        '<div class="small text-muted mb-3">Это основной слой визуальной работы со страницей. Меняйте стиль прямо здесь и сразу проверяйте результат на холсте. Экран глобальных стилей нужен только для редких site-wide defaults.</div>' +
                    '<div class="small font-weight-bold text-uppercase text-muted mb-2">Каркас страницы</div>' +
                    renderSelectField('Shell variant страницы', 'Можно явно назначить shell variant для этой страницы. Если оставить авто-режим, resolver выберет вариант сам.', 'layout.shell_variant', getShellVariantOptions(), layout.shell_variant || '') +
                    (state.page.mode === 'full_takeover'
                        ? renderSelectField('Основной slot builder-содержимого', 'Куда должен вставляться основной runtime-контент страницы внутри shell.', 'layout.content_slot', contentSlotOptions, layout.content_slot || 'content_body')
                        : '') +
                    renderPageShellSummary() +
                    '<div class="small font-weight-bold text-uppercase text-muted mt-3 mb-2">Визуальный язык страницы</div>' +
                        renderSelectField('Общий стиль страницы', 'Локальный page-level preset. Для глобального стиля всего сайта используйте экран «Дизайн сайта».', 'theme.global_style_preset', pageThemeOptions.global_style_preset, theme.global_style_preset) +
                    renderSelectField('Цветовая схема', 'Базовый набор цветов интерфейса и контента.', 'theme.color_preset', pageThemeOptions.color_preset, theme.color_preset) +
                    renderSelectField('Типографика', 'Пресет для заголовков, текста и ритма набора.', 'theme.typography_preset', pageThemeOptions.typography_preset, theme.typography_preset) +
                    renderSelectField('Контейнеры', 'Базовая ширина контейнеров по странице.', 'theme.container_preset', pageThemeOptions.container_preset, theme.container_preset) +
                    renderSelectField('Кнопки', 'Главный стиль кнопок и призывов к действию.', 'theme.button_preset', pageThemeOptions.button_preset, theme.button_preset) +
                    renderSelectField('Карточки', 'Пресет карточек для списков и блоков.', 'theme.card_preset', pageThemeOptions.card_preset, theme.card_preset) +
                    renderSelectField('Ритм между секциями', 'Общий вертикальный ритм страницы.', 'theme.section_spacing', pageThemeOptions.section_spacing, theme.section_spacing) +
                        '<div class="small text-muted mt-3"><a href="' + escapeHtml(state.screen.design_url || '#') + '">Открыть глобальные стили</a> для редких site-wide defaults. Повседневная работа со страницей должна происходить здесь, на canvas.</div>' +
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
                        '<input type="text" class="form-control form-control-sm" data-field="title" value="' + escapeHtml(section.title) + '">' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Схема колонок', 'Определяет, сколько колонок будет в секции и как они распределяются по ширине.') +
                        '<select class="form-control form-control-sm" data-field="layout">' +
                            renderSelectOptions(layoutOptions, section.layout) +
                        '</select>' +
                    '</div>' +
                    renderSelectField('Тип секции', 'Смысл секции на странице: первый экран, контент, действие, каталог и так далее.', 'section_type', sectionTypeOptions, section.section_type) +
                    renderSelectField('Стилевой пресет', 'Готовый пресет оформления секции.', 'style_preset', sectionStyleOptions, section.style_preset) +
                    renderSelectField('Тон фона', 'Быстрый выбор общего тона секции без ручной CSS-настройки.', 'background_tone', backgroundToneOptions, section.background_tone) +
                    renderSelectField('Пресет контейнера', 'Управляет рабочей шириной секции.', 'container_preset', pageThemeOptions.container_preset, section.container_preset) +
                    renderSelectField('Вертикальный ритм', 'Отступы сверху и снизу для секции.', 'spacing_preset', spacingPresetOptions, section.spacing_preset) +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Дополнительное оформление', 'Служебное поле для особого оформления секции. Если оно не нужно, оставьте поле пустым.') +
                        '<input type="text" class="form-control form-control-sm" data-field="settings.css_class" value="' + escapeHtml(section.settings.css_class || '') + '">' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Служебный CSS-класс фона', 'Нужно только если для секции уже подготовлен отдельный backend/frontend класс.') +
                        '<input type="text" class="form-control form-control-sm" data-field="settings.background_class" value="' + escapeHtml(section.settings.background_class || '') + '">' +
                    '</div>' +
                    '<div class="form-group mb-3">' +
                        fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть секцию на компьютере, планшете или телефоне.') +
                        renderVisibilityControls('visibility', section.visibility) +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-section" data-section-index="' + selection.sectionIndex + '">Удалить секцию</button>';
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
                        '<input type="text" class="form-control form-control-sm" data-field="title" value="' + escapeHtml(column.title) + '">' +
                    '</div>' +
                    renderWidthControls(column.width) +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Выравнивание содержимого', 'Помогает прижать содержимое колонки к верху, центру или низу.') +
                        '<select class="form-control form-control-sm" data-field="settings.align">' +
                            alignOptions.map(function (option) {
                                return '<option value="' + option.value + '"' + (column.settings.align === option.value ? ' selected' : '') + '>' + option.title + '</option>';
                            }).join('') +
                        '</select>' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Дополнительное оформление', 'Служебное поле для особого оформления колонки. Если оно не нужно, оставьте поле пустым.') +
                        '<input type="text" class="form-control form-control-sm" data-field="settings.css_class" value="' + escapeHtml(column.settings.css_class || '') + '">' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
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
                    '<input type="text" class="form-control form-control-sm" data-field="label" value="' + escapeHtml(node.label || '') + '">' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Дополнительное оформление', 'Служебное поле для особого оформления конкретного элемента.') +
                    '<input type="text" class="form-control form-control-sm" data-field="class_name" value="' + escapeHtml(node.class_name || '') + '">' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Источник данных блока', 'Нужен только если блок должен брать данные из заранее заданного сценария или источника.') +
                    '<input type="text" class="form-control form-control-sm" data-field="source_key" value="' + escapeHtml(node.source_key || '') + '">' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Заметки для редактора', 'Сюда можно записать, зачем нужен блок или что в нем важно не забыть.') +
                    '<textarea class="form-control form-control-sm" rows="3" data-field="notes">' + escapeHtml(node.notes || '') + '</textarea>' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть этот элемент на нужных типах устройств.') +
                    renderVisibilityControls('device_visibility', node.device_visibility) +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-node" data-section-index="' + selection.sectionIndex + '" data-column-index="' + selection.columnIndex + '" data-node-index="' + selection.nodeIndex + '">Удалить элемент</button>';

            if (node.type === 'system_widget' && node.widget_id) {
                loadWidgetOptions(node);
            } else if (node.type === 'system_widget') {
                widgetForm.innerHTML = 'У этого виджета пока нет связи с системным каталогом. Добавьте его заново из списка слева.';
            } else {
                widgetForm.innerHTML = 'Для этого блока сейчас доступны базовые настройки: название, видимость, заметки и связь с источником данных.';
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

            const response = await fetch(state.screen.api.widgets_catalog_url, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                widgetList.innerHTML = '<div class="text-danger">Не удалось загрузить список виджетов.</div>';
                return;
            }

            state.widgetsCatalog = result.widgets || {};
            renderWidgetLibrary();
            renderCanvas();
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
                    '<div class="border rounded p-2 mb-2">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<strong>#' + version.id + '</strong>' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="restore-version" data-version-id="' + version.id + '">Восстановить</button>' +
                        '</div>' +
                        '<div class="small text-muted mt-1">' + escapeHtml(version.created_at) + '</div>' +
                        '<div class="small mt-1">' + escapeHtml(version.version_note || 'Без комментария') + '</div>' +
                    '</div>';
            }).join('');
        }

        async function loadWidgetOptions(node) {
            widgetForm.innerHTML = 'Загрузка формы настроек виджета...';

            const body = new URLSearchParams();
            body.set('widget_id', node.widget_id);
            body.set('template', state.page.template || 'nordic');
            body.set('options', JSON.stringify(node.options || {}));

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
            if (result.error) {
                widgetForm.innerHTML = '<div class="text-danger">Не удалось загрузить форму настроек виджета.</div>';
                return;
            }

            widgetForm.innerHTML = result.html;
            initTooltips(widgetForm);
        }

        function moveSection(fromIndex, targetIndex) {
            if (fromIndex === targetIndex || fromIndex < 0 || targetIndex < 0) {
                return;
            }

            const toIndex = fromIndex < targetIndex ? targetIndex - 1 : targetIndex;
            state.schema.sections = moveArrayItem(state.schema.sections, fromIndex, toIndex);
            state.selection = {type: 'section', sectionIndex: toIndex};
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
                label: block.title,
                class_name: '',
                notes: '',
                source_key: block.key,
                device_visibility: defaultVisibility(),
                options: {}
            });

            setSelection({
                type: 'node',
                sectionIndex: state.selection.sectionIndex,
                columnIndex: state.selection.columnIndex,
                nodeIndex: column.nodes.length - 1
            });
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

            setSelection({
                type: 'node',
                sectionIndex: state.selection.sectionIndex,
                columnIndex: state.selection.columnIndex,
                nodeIndex: column.nodes.length - 1
            });
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
            canvasStatus.textContent = 'Изменения сохранены';
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

            if (state.selection && state.selection.type === 'section' && field.dataset.field === 'layout') {
                syncSectionColumnsWithLayout(target);
            }

            if (state.selection && state.selection.type === 'page' && field.dataset.field === 'layout.shell_variant') {
                syncPageLayoutWithEffectiveShell();
            }

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
                    renderCanvas();
                }
                return true;
            }

            if (action === 'delete-node') {
                if (window.confirm('Удалить элемент?')) {
                    syncSelectedWidgetFormIntoState();
                    state.schema.sections[sectionIndex].columns[columnIndex].nodes.splice(nodeIndex, 1);
                    state.selection = null;
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