<?php
/**
 * Nordic Page Context Detector
 *
 * Определяет контекст текущей страницы по controller + action из InstantCMS core.
 * НЕ использует имена типов контента (news, articles, board) — только паттерны.
 * Это делает шаблон универсальным: работает у любого клиента с любыми компонентами.
 *
 * Возвращает массив:
 *   page_type    — логический тип страницы для CSS классов и builder
 *   shell_preset — 'no_sidebars' | 'right_sidebar' | 'left_sidebar' | 'both_sidebars'
 *   hero_mode    — 'page' (builder управляет) | 'shell' (виджеты) | 'none'
 *   body_class   — CSS классы на <body>
 *   is_homepage  — bool
 *   is_content_list — bool
 *   is_content_item — bool
 *   can_use_builder — bool
 */

/** @var cmsCore $core */
$_nc_core   = cmsCore::getInstance();
$_nc_ctrl   = (string) ($_nc_core->uri_controller ?? '');
$_nc_action = (string) ($_nc_core->uri_action ?? '');

// Root URI detection: when visiting homepage, InstantCMS может подставлять
// controller/action по умолчанию (ct_autoload/index), а $core->uri при этом пуст.
// Для builder нам важно распознать главную как логический контекст homepage.
$_nc_is_root = trim((string) ($_nc_core->uri ?? '')) === '';
if ($_nc_is_root) {
    $_nc_ctrl = '';
    $_nc_action = 'index';
}

/**
 * Матрица контекста: controller + action → preset
 *
 * Порядок важен: первое совпадение выигрывает.
 * Специфичные правила — выше, общие — ниже.
 *
 * shell_preset:
 *   no_sidebars   — полная ширина, без боковых колонок
 *   right_sidebar — правая боковая колонка включена
 *   left_sidebar  — левая боковая колонка включена
 *   both_sidebars — обе боковые колонки
 *
 * hero_mode:
 *   page   — hero-зона управляется landing builder или отдельным виджетом страницы
 *   shell  — hero-зона управляется глобальным shell (слайдер и т.п.)
 *   none   — hero-зона скрыта
 */
$_nc_context_matrix = [

    // ── Главная страница ────────────────────────────────────────────────────
    [
        'ctrl'        => '',
        'action'      => 'index',
        'page_type'   => 'homepage',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'page',
        'body_class'  => 'page-homepage',
        'is_homepage' => true,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => true,
    ],

    // ── Список контента: любой компонент на базе content controller ─────────
    // InstantCMS: controller=content, action=index (список записей в рубрике)
    [
        'ctrl'        => 'content',
        'action'      => 'index',
        'page_type'   => 'content-list',
        'shell_preset'=> 'right_sidebar',
        'hero_mode'   => 'none',
        'body_class'  => 'page-content-list',
        'is_homepage' => false,
        'is_content_list' => true,
        'is_content_item' => false,
        'can_use_builder' => true,
    ],

    // Категория/рубрика контента
    [
        'ctrl'        => 'content',
        'action'      => 'category',
        'page_type'   => 'content-category',
        'shell_preset'=> 'right_sidebar',
        'hero_mode'   => 'none',
        'body_class'  => 'page-content-category',
        'is_homepage' => false,
        'is_content_list' => true,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],

    // Одна запись контента (статья, новость, объявление — любая)
    [
        'ctrl'        => 'content',
        'action'      => 'item',
        'page_type'   => 'content-item',
        'shell_preset'=> 'right_sidebar',
        'hero_mode'   => 'none',
        'body_class'  => 'page-content-item',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => true,
        'can_use_builder' => false,
    ],

    // Форма добавления/редактирования контента
    [
        'ctrl'        => 'content',
        'action'      => 'add',
        'page_type'   => 'content-form',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'none',
        'body_class'  => 'page-content-form',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],
    [
        'ctrl'        => 'content',
        'action'      => 'edit',
        'page_type'   => 'content-form',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'none',
        'body_class'  => 'page-content-form',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],

    // ── Пользователи ────────────────────────────────────────────────────────
    [
        'ctrl'        => 'users',
        'action'      => 'profile',
        'page_type'   => 'user-profile',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'page',
        'body_class'  => 'page-user-profile',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => true,
    ],
    [
        'ctrl'        => 'users',
        'action'      => 'index',
        'page_type'   => 'user-list',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'none',
        'body_class'  => 'page-user-list',
        'is_homepage' => false,
        'is_content_list' => true,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],

    // ── Авторизация ──────────────────────────────────────────────────────────
    [
        'ctrl'        => 'auth',
        'action'      => '*',
        'page_type'   => 'auth',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'none',
        'body_class'  => 'page-auth',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],

    // ── Фото/Медиа ──────────────────────────────────────────────────────────
    [
        'ctrl'        => 'photos',
        'action'      => 'image',
        'page_type'   => 'media-item',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'none',
        'body_class'  => 'page-media-item',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => true,
        'can_use_builder' => false,
    ],
    [
        'ctrl'        => 'photos',
        'action'      => '*',
        'page_type'   => 'media-list',
        'shell_preset'=> 'right_sidebar',
        'hero_mode'   => 'none',
        'body_class'  => 'page-media-list',
        'is_homepage' => false,
        'is_content_list' => true,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],

    // ── Landing Builder runtime (наша страница) ────────────────────────────
    [
        'ctrl'        => 'landingbuilder',
        'action'      => '*',
        'page_type'   => 'landing',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'page',
        'body_class'  => 'page-landing',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => true,
    ],

    // ── Fallback ─────────────────────────────────────────────────────────────
    [
        'ctrl'        => '*',
        'action'      => '*',
        'page_type'   => 'generic',
        'shell_preset'=> 'no_sidebars',
        'hero_mode'   => 'none',
        'body_class'  => 'page-generic',
        'is_homepage' => false,
        'is_content_list' => false,
        'is_content_item' => false,
        'can_use_builder' => false,
    ],
];

/**
 * Поиск контекста по матрице
 */
$_nc_matched = null;
foreach ($_nc_context_matrix as $_nc_rule) {
    $ctrl_match   = $_nc_rule['ctrl']   === '*' || $_nc_rule['ctrl']   === $_nc_ctrl;
    $action_match = $_nc_rule['action'] === '*' || $_nc_rule['action'] === $_nc_action;
    if ($ctrl_match && $action_match) {
        $_nc_matched = $_nc_rule;
        break;
    }
}

if (!$_nc_matched) {
    $_nc_matched = end($_nc_context_matrix);
}

/**
 * Публичный результат — доступен в main.tpl.php как $nordic_context
 */
$nordic_context = [
    'page_type'       => $_nc_matched['page_type'],
    'shell_preset'    => $_nc_matched['shell_preset'],
    'hero_mode'       => $_nc_matched['hero_mode'],
    'body_class'      => $_nc_matched['body_class'],
    'is_homepage'     => (bool) ($_nc_matched['is_homepage'] ?? false),
    'is_content_list' => (bool) ($_nc_matched['is_content_list'] ?? false),
    'is_content_item' => (bool) ($_nc_matched['is_content_item'] ?? false),
    'can_use_builder' => (bool) ($_nc_matched['can_use_builder'] ?? false),
    'ctrl'            => $_nc_ctrl,
    'action'          => $_nc_action,
];

unset($_nc_core, $_nc_ctrl, $_nc_action, $_nc_is_root, $_nc_context_matrix, $_nc_matched, $_nc_rule, $_nc_matched);
