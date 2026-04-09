<?php
/**
 * Основной макет шаблона nordic.
 * Первая версия сохраняет совместимость с dynamic layout modern,
 * но добавляет собственный shell и именованные slots.
 */
/** @var cmsTemplate $this */

$shell_scheme_file = cmsConfig::get('root_path') . 'templates/nordic/shell_scheme.php';
$shell_scheme = is_readable($shell_scheme_file) ? include $shell_scheme_file : [];
$slot_positions = !empty($shell_scheme['slot_positions']) && is_array($shell_scheme['slot_positions']) ? $shell_scheme['slot_positions'] : [];

// Определяем контекст страницы (controller+action → preset)
$_nc_ctx_file = cmsConfig::get('root_path') . 'templates/nordic/page_context.php';
if (is_readable($_nc_ctx_file)) { include $_nc_ctx_file; }
/** @var array $nordic_context */
if (empty($nordic_context) || !is_array($nordic_context)) {
    $nordic_context = ['page_type' => 'generic', 'shell_preset' => 'no_sidebars', 'hero_mode' => 'none', 'body_class' => '', 'is_homepage' => false, 'is_content_list' => false, 'is_content_item' => false, 'can_use_builder' => false, 'ctrl' => '', 'action' => ''];
}

// Интеграция landingbuilder во фронтовом шаблоне включена по умолчанию,
// чтобы изменения из конструктора сразу применялись на сайте.
$lb_front_integration_enabled = true;
$lb_trace_flag = '';
if (isset($_GET['lb_effective_trace'])) {
    $lb_trace_flag = (string) $_GET['lb_effective_trace'];
} elseif (isset($_GET['lb_trace'])) {
    $lb_trace_flag = (string) $_GET['lb_trace'];
}
$lb_debug_trace_enabled = $lb_front_integration_enabled && cmsUser::isAdmin() && in_array(strtolower(trim($lb_trace_flag)), ['1', 'true', 'yes', 'on'], true);
$lb_effective_page_trace = [];
$lb_effective_page_trace_comment = '';
$lb_push_effective_trace = function($stage, array $payload = []) use (&$lb_effective_page_trace, $lb_debug_trace_enabled) {
    if (!$lb_debug_trace_enabled) {
        return;
    }

    $lb_effective_page_trace[] = [
        'stage' => (string) $stage,
        'payload' => $payload,
        'timestamp' => date('c')
    ];
};
unset($lb_trace_flag);

// Full takeover через bindings (page.*): заменяем контент страницы на Landing Builder page
$landingbuilder_takeover = null;
try {
    $ctrl = (string) ($nordic_context['ctrl'] ?? '');
    $action = (string) ($nordic_context['action'] ?? '');
    $page_type = (string) ($nordic_context['page_type'] ?? '');
    $lb_route_uses_overlay_hooks =
        ($ctrl === 'content' && $action === 'category') ||
        ($ctrl === 'users' && $action === 'profile') ||
        in_array($page_type, ['content-category', 'user-profile'], true);
    $lb_push_effective_trace('template.route-context', [
        'ctrl' => $ctrl,
        'action' => $action,
        'page_type' => $page_type,
        'uses_overlay_hooks' => $lb_route_uses_overlay_hooks,
        'integration_enabled' => $lb_front_integration_enabled
    ]);

    // For category/profile routes InstantCMS keeps native body/grid logic,
    // and builder is injected through dedicated overlay hooks.
    if ($lb_front_integration_enabled && $ctrl !== 'landingbuilder' && !$lb_route_uses_overlay_hooks) {
        $lb_model = cmsCore::getModel('landingbuilder');
        if ($lb_model && method_exists($lb_model, 'resolveFullTakeoverPageKeyFromBindings')) {
            $route_params = [
                'ctrl' => (string) ($nordic_context['ctrl'] ?? ''),
                'action' => (string) ($nordic_context['action'] ?? ''),
                'page_type' => (string) ($nordic_context['page_type'] ?? '')
            ];
            $lb_push_effective_trace('template.route-params', $route_params);

            $takeover_page_key = (string) $lb_model->resolveFullTakeoverPageKeyFromBindings($route_params, '');
            $lb_push_effective_trace('template.binding-resolution', ['takeover_page_key' => $takeover_page_key]);
            if ($lb_debug_trace_enabled && method_exists($lb_model, 'getLastEffectivePageKeyTrace')) {
                $lb_push_effective_trace('resolver.trace', ['events' => $lb_model->getLastEffectivePageKeyTrace()]);
            }

            // Авто-takeover по page_type: если есть страница с ключом как у контекста
            // (например homepage/content-list/content-item/generic) — берём её без bindings.
            if ($takeover_page_key === '') {
                $candidate_keys = [];
                $page_type_key = trim((string) ($route_params['page_type'] ?? ''));
                if ($page_type_key !== '') {
                    $candidate_keys[] = $page_type_key;
                }
                if ($page_type_key !== 'generic') {
                    $candidate_keys[] = 'generic';
                }
                $lb_push_effective_trace('template.page-type-fallback.start', ['candidate_keys' => $candidate_keys]);

                foreach ($candidate_keys as $candidate_key) {
                    if ($candidate_key === '') {
                        continue;
                    }

                    $candidate_page = $lb_model->getPageByKey($candidate_key);
                    if ($candidate_page) {
                        $takeover_page_key = $candidate_key;
                        $lb_push_effective_trace('template.page-type-fallback.match', [
                            'selected_key' => $candidate_key,
                            'source' => 'page_type_or_generic'
                        ]);
                        unset($candidate_page);
                        break;
                    }

                    unset($candidate_page);
                }

                if ($takeover_page_key === '') {
                    $lb_push_effective_trace('template.page-type-fallback.miss', ['result' => 'no-candidate-page']);
                }

                unset($candidate_keys, $page_type_key, $candidate_key);
            }

            // Homepage bootstrap fallback: если bindings ещё не настроены,
            // но на сайте есть ровно один созданный макет — считаем его главной.
            // Это даёт UX "нулевой шаблон строится на canvas" без старого шаблона на /.
            if ($takeover_page_key === '' && ($route_params['ctrl'] ?? '') === '' && ($route_params['action'] ?? '') === 'index') {
                $lb_push_effective_trace('template.homepage-bootstrap.start', ['route' => 'index']);
                $homepage_page = $lb_model->getPageByKey('homepage');
                if ($homepage_page) {
                    $takeover_page_key = 'homepage';
                    $lb_push_effective_trace('template.homepage-bootstrap.match', [
                        'selected_key' => 'homepage',
                        'source' => 'homepage-page-exists'
                    ]);
                    unset($homepage_page);
                } else if (method_exists($lb_model, 'getPagesForAdmin')) {
                    $all_pages = $lb_model->getPagesForAdmin();
                    if (is_array($all_pages) && count($all_pages) === 1 && !empty($all_pages[0]['key'])) {
                        $takeover_page_key = (string) $all_pages[0]['key'];
                        $lb_push_effective_trace('template.homepage-bootstrap.match', [
                            'selected_key' => $takeover_page_key,
                            'source' => 'single-page-catalog'
                        ]);
                    }
                    unset($all_pages);
                }

                if ($takeover_page_key === '') {
                    $lb_push_effective_trace('template.homepage-bootstrap.miss', ['result' => 'no-homepage-fallback']);
                }
            }

            $lb_push_effective_trace('template.effective-page-key', ['effective_page_key' => $takeover_page_key]);
            if ($takeover_page_key !== '') {
                $takeover_page = $lb_model->getPageByKey($takeover_page_key);
                if ($takeover_page) {
                    $is_preview = ($takeover_page['status'] ?? 'draft') !== 'published';
                    $takeover_runtime = $lb_model->getRuntimePage($takeover_page);
                    $takeover_runtime['device_type'] = cmsRequest::getDeviceType();
                    $takeover_runtime['is_preview'] = $is_preview;
                    $landingbuilder_takeover = ['page' => $takeover_page, 'runtime' => $takeover_runtime];
                    $landingbuilder_shell_runtime = $takeover_runtime['shell'];
                    $lb_push_effective_trace('template.takeover-applied', [
                        'page_key' => $takeover_page_key,
                        'page_status' => (string) ($takeover_page['status'] ?? ''),
                        'is_preview' => $is_preview
                    ]);
                } else {
                    $lb_push_effective_trace('template.takeover-missing-page', ['page_key' => $takeover_page_key]);
                }
            } else {
                $lb_push_effective_trace('template.takeover-skip', ['reason' => 'empty-effective-page-key']);
            }
        }
    } else {
        $lb_push_effective_trace('template.takeover-skip', [
            'reason' => 'overlay-or-landingbuilder-route',
            'ctrl' => $ctrl,
            'action' => $action
        ]);
    }
} catch (Throwable $exception) {
    $landingbuilder_takeover = null;
    $lb_push_effective_trace('template.exception', [
        'type' => get_class($exception),
        'message' => (string) $exception->getMessage()
    ]);
}

if ($lb_debug_trace_enabled && $lb_effective_page_trace) {
    $lb_trace_payload = json_encode($lb_effective_page_trace, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (is_string($lb_trace_payload) && $lb_trace_payload !== '') {
        error_log('[landingbuilder][template-effective-page-key] ' . $lb_trace_payload);
        $lb_effective_page_trace_comment = '<!-- lb-effective-page-trace: ' . base64_encode($lb_trace_payload) . ' -->';
    }
}

// Shell variant из конструктора должен работать глобально,
// даже когда страница не перехвачена landingbuilder (нет takeover).
if ($lb_front_integration_enabled && empty($landingbuilder_shell_runtime)) {
    try {
        $lb_model = cmsCore::getModel('landingbuilder');
        if ($lb_model && method_exists($lb_model, 'getShellVariantByKey')) {
            $page_type = trim((string) ($nordic_context['page_type'] ?? 'generic'));
            $variant_key = 'site-default';

            if ($page_type === 'homepage') {
                $variant_key = 'homepage';
            } elseif (in_array($page_type, ['content-category', 'content-list'], true)) {
                $variant_key = 'category-pages';
            } elseif ($page_type === 'content-item') {
                $variant_key = 'content-pages';
            } elseif ($page_type === 'user-profile') {
                $variant_key = 'profile-pages';
            } elseif ($page_type === 'landing') {
                $variant_key = 'landing-pages';
            }

            $shell_variant = $lb_model->getShellVariantByKey($variant_key);
            if ($shell_variant) {
                $landingbuilder_shell_runtime = [
                    'active_slots' => $shell_variant['active_slots'] ?? [],
                    'variant_key'  => $shell_variant['key'] ?? $variant_key,
                    'body_layout'  => $shell_variant['body_layout'] ?? 'no_sidebars',
                    'chrome'       => [
                        'header_variant'      => $shell_variant['header_variant'] ?? 'classic',
                        'footer_variant'      => $shell_variant['footer_variant'] ?? 'columns_4',
                        'menu_placement'      => $shell_variant['menu_placement'] ?? 'header_primary',
                        'sticky_header'       => $shell_variant['sticky_header'] ?? 'off',
                        'mobile_menu_mode'    => $shell_variant['mobile_menu_mode'] ?? 'drawer',
                        'homepage_shell_mode' => $shell_variant['homepage_shell_mode'] ?? 'inherit'
                    ],
                    'body_classes' => [
                        'lb-shell-scope-' . ($shell_variant['scope'] ?? 'site'),
                        'lb-shell-variant-' . ($shell_variant['key'] ?? $variant_key)
                    ]
                ];
            }
        }
    } catch (Throwable $exception) {
        // noop
    }
}

$shell_runtime = !empty($landingbuilder_shell_runtime) && is_array($landingbuilder_shell_runtime) ? $landingbuilder_shell_runtime : [];
$shell_chrome = !empty($shell_runtime['chrome']) && is_array($shell_runtime['chrome']) ? $shell_runtime['chrome'] : [];
$active_shell_slots = !empty($shell_runtime['active_slots']) && is_array($shell_runtime['active_slots']) ? array_fill_keys($shell_runtime['active_slots'], true) : [];

// Если builder не переопределил active_slots — применяем preset из page_context
if (empty($active_shell_slots)) {
    $_nc_presets = $shell_scheme['context_rules']['shell_presets'] ?? [];
    $_nc_preset_key = $nordic_context['shell_preset'];
    $_nc_preset = $_nc_presets[$_nc_preset_key] ?? [];
    if ($_nc_preset) {
        $_nc_always = array_fill_keys($shell_scheme['context_rules']['always_active'] ?? [], true);
        // Отключаем боковые слоты согласно preset
        foreach ($_nc_preset as $_nc_slot => $_nc_enabled) {
            if (!$_nc_enabled) {
                // Слот принудительно выключен для этого preset
                // Используем active_shell_slots как whitelist: заполняем только allowed
                if (!isset($_nc_active_build)) { $_nc_active_build = $_nc_always; }
            } else {
                if (!isset($_nc_active_build)) { $_nc_active_build = $_nc_always; }
                $_nc_active_build[$_nc_slot] = true;
            }
        }
        if (isset($_nc_active_build)) {
            $active_shell_slots = $_nc_active_build;
        }
        unset($_nc_presets, $_nc_preset_key, $_nc_preset, $_nc_always, $_nc_slot, $_nc_enabled, $_nc_active_build);
    }
    unset($_nc_presets);
}
$menu_placement = !empty($shell_chrome['menu_placement']) ? (string) $shell_chrome['menu_placement'] : 'header_primary';

if (!in_array($menu_placement, ['header_primary', 'header_secondary', 'site_top'], true)) {
    $menu_placement = 'header_primary';
}

if (!empty($shell_runtime['body_classes']) && is_array($shell_runtime['body_classes'])) {
    $body_classes = array_values(array_unique(array_merge($body_classes ?? [], $shell_runtime['body_classes'])));
}

// Добавляем page-type класс из page_context
if (!empty($nordic_context['body_class'])) {
    $body_classes = array_values(array_unique(array_merge($body_classes ?? [], [$nordic_context['body_class']])));
}
// data-атрибут для страницы (используется CSS)
$_nc_body_page_type = $nordic_context['page_type'] ?? '';

$getSlotPositions = function($slot, array $fallback = []) use ($slot_positions) {
    return !empty($slot_positions[$slot]) ? $slot_positions[$slot] : $fallback;
};

$isSlotEnabled = function($slot) use ($active_shell_slots) {
    if (!$active_shell_slots) {
        return true;
    }

    return isset($active_shell_slots[$slot]);
};

$renderPositionGroup = function($positions, $groupClass, $wrapper = 'wrapper_plain', $itemBaseClass = 'nordic-shell__slot') {
    $has_content = false;

    foreach ($positions as $position) {
        ob_start();
        $this->widgets($position, false, $wrapper);
        $position_html = trim((string) ob_get_clean());

        if ($position_html === '') {
            continue;
        }

        if (!$has_content) {
            echo '<div class="' . html($groupClass, false) . '">';
            $has_content = true;
        }

        echo '<div class="' . html($itemBaseClass, false) . ' ' . html($itemBaseClass . '--' . $position, false) . '">';
        echo $position_html;
        echo '</div>';
    }

    if ($has_content) {
        echo '</div>';
    }

    return $has_content;
};

$renderSlot = function($positions, $slotClass, $wrapper = 'wrapper_plain') use ($renderPositionGroup) {
    ob_start();
    $has_content = $renderPositionGroup($positions, 'nordic-shell__slot-panel', $wrapper, 'nordic-shell__slot');
    $content = ob_get_clean();

    if (!$has_content) {
        return;
    }

    echo '<section class="' . html($slotClass, false) . '">';
    echo $content;
    echo '</section>';
};

$slot_fallbacks = [
    'site_top' => ['site_top', 'top', 'pos_26'],
    'header_primary' => ['header_primary', 'pos_27', 'pos_29'],
    'header_secondary' => ['header_secondary', 'pos_31'],
    'hero' => ['hero', 'con_header'],
    'before_content' => ['before_content', 'pos_10'],
    'content_body' => ['content_body', 'pos_8'],
    'content_sidebar_left' => ['content_sidebar_left', 'pos_34'],
    'content_sidebar_right' => ['content_sidebar_right', 'pos_9'],
    'after_content' => ['after_content', 'pos_17'],
    'footer_primary' => ['footer_primary', 'footer', 'pos_38', 'pos_39', 'pos_40'],
    'footer_secondary' => ['footer_secondary', 'pos_11']
];

$slot_fallbacks[$menu_placement][] = 'header';

$resolveSlotPositions = function($slot) use ($getSlotPositions, $slot_fallbacks) {
    $fallback = $slot_fallbacks[$slot] ?? [$slot];
    return $getSlotPositions($slot, array_values(array_unique($fallback)));
};

$lb_takeover_active = !empty($landingbuilder_takeover) && is_array($landingbuilder_takeover)
    && !empty($landingbuilder_takeover['page']) && is_array($landingbuilder_takeover['page'])
    && !empty($landingbuilder_takeover['runtime']) && is_array($landingbuilder_takeover['runtime']);

$lb_takeover_page = $lb_takeover_active ? $landingbuilder_takeover['page'] : [];
$lb_takeover_runtime = $lb_takeover_active ? $landingbuilder_takeover['runtime'] : [];
$lb_takeover_zones_by_slot = [];
$lb_takeover_theme_context = ['theme' => [], 'vars' => []];
$lb_site_theme_context = ['theme' => [], 'vars' => []];
$lb_site_shell_style = '';
$lb_takeover_shell_style = '';

$lb_theme_lib = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_theme.php';
$lb_renderer_lib = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_renderer.php';
$lb_styles_lib = cmsConfig::get('root_path') . 'system/controllers/landingbuilder/helpers/runtime_styles.php';

if (is_readable($lb_theme_lib)) {
    require_once $lb_theme_lib;
}
if (is_readable($lb_renderer_lib)) {
    require_once $lb_renderer_lib;
}
if (is_readable($lb_styles_lib)) {
    require_once $lb_styles_lib;
}

// Стартовый UX по умолчанию: modern skin (привычная база для пользователей).
// Для админа доступен override в рантайме:
//   ?nordic_skin=nordic  -> принудительно Nordic skin
//   ?nordic_skin=modern  -> принудительно Modern skin
$nordic_skin_override = (string) cmsCore::getInstance()->request->get('nordic_skin', '');
$nordic_use_modern_skin = true;

if (cmsUser::isAdmin()) {
    if ($nordic_skin_override === 'nordic') {
        $nordic_use_modern_skin = false;
    } else if ($nordic_skin_override === 'modern') {
        $nordic_use_modern_skin = true;
    }
}

unset($nordic_skin_override);

if (function_exists('landingbuilder_get_runtime_theme_context_from_theme')) {
    $lb_site_theme_context = landingbuilder_get_runtime_theme_context_from_theme([], []);
}
if (function_exists('landingbuilder_render_css_vars') && !empty($lb_site_theme_context['vars']) && is_array($lb_site_theme_context['vars'])) {
    $lb_site_shell_style = landingbuilder_render_css_vars($lb_site_theme_context['vars']);
}
if ((!$nordic_use_modern_skin || $lb_takeover_active) && function_exists('landingbuilder_get_runtime_site_styles_css')) {
    $lb_runtime_css = landingbuilder_get_runtime_site_styles_css();
    if ($lb_runtime_css !== '') {
        $this->addHead('<style>' . $lb_runtime_css . '</style>');
    }
    unset($lb_runtime_css);
}

if ($lb_takeover_active) {
    if (function_exists('landingbuilder_get_runtime_theme_context')) {
        $lb_takeover_theme_context = landingbuilder_get_runtime_theme_context($lb_takeover_page);
    }
    if (function_exists('landingbuilder_render_css_vars') && !empty($lb_takeover_theme_context['vars']) && is_array($lb_takeover_theme_context['vars'])) {
        $lb_takeover_shell_style = landingbuilder_render_css_vars($lb_takeover_theme_context['vars']);
    }

    foreach (($lb_takeover_runtime['zones'] ?? []) as $zone) {
        if (!is_array($zone)) {
            continue;
        }
        $slot_key = (string) ($zone['slot_key'] ?? ($zone['key'] ?? ''));
        if ($slot_key === '') {
            continue;
        }
        $lb_takeover_zones_by_slot[$slot_key] = $zone;
    }
}

$lb_effective_shell_style = $lb_takeover_shell_style !== '' ? $lb_takeover_shell_style : $lb_site_shell_style;

$lbGetBuilderSlotHtml = function($slot_key) use ($lb_takeover_active, $lb_takeover_zones_by_slot, $device_type, $lb_takeover_theme_context, $lb_takeover_runtime) {
    if (!$lb_takeover_active) {
        return '';
    }
    $zone = $lb_takeover_zones_by_slot[$slot_key] ?? null;
    if (empty($zone) || empty($zone['sections']) || !is_array($zone['sections'])) {
        return '';
    }
    if (!function_exists('landingbuilder_render_runtime_zone_sections')) {
        return '';
    }

    $slot_map = !empty($lb_takeover_runtime['slot_map']) && is_array($lb_takeover_runtime['slot_map']) ? $lb_takeover_runtime['slot_map'] : [];
    return landingbuilder_render_runtime_zone_sections($zone, [
        'device_type'   => $device_type,
        'theme_context' => $lb_takeover_theme_context,
        'slot_map'      => $slot_map,
        'surface'       => 'site'
    ]);
};

$lbIsNativeRuntimeSlot = function($slot_key) use ($lb_takeover_active, $lb_takeover_runtime) {
    if (!$lb_takeover_active) {
        return false;
    }

    $slot_map = !empty($lb_takeover_runtime['slot_map']) && is_array($lb_takeover_runtime['slot_map']) ? $lb_takeover_runtime['slot_map'] : [];
    $slot_meta = isset($slot_map[$slot_key]) && is_array($slot_map[$slot_key]) ? $slot_map[$slot_key] : [];

    return ($slot_meta['render_mode'] ?? 'builder') === 'native';
};

$left_sidebar_positions = $resolveSlotPositions('content_sidebar_left');
$right_sidebar_positions = $resolveSlotPositions('content_sidebar_right');
$content_body_positions = $resolveSlotPositions('content_body');

$has_left_content_sidebar = $isSlotEnabled('content_sidebar_left') && $this->hasWidgetsOn($left_sidebar_positions);
$has_right_content_sidebar = $isSlotEnabled('content_sidebar_right') && $this->hasWidgetsOn($right_sidebar_positions);
$has_content_body_widgets = $this->hasWidgetsOn($content_body_positions);

$lb_left_sidebar_html = '';
$lb_right_sidebar_html = '';
$content_grid_style = '';
$lb_native_body_autoscale = false;
$lb_native_body_fullwidth = false;
$lb_native_body_full_padding = 20;

if ($lb_takeover_active) {
    $lb_left_sidebar_html = $lbGetBuilderSlotHtml('content_sidebar_left');
    $lb_right_sidebar_html = $lbGetBuilderSlotHtml('content_sidebar_right');

    $layout_state = isset($lb_takeover_page['schema']['layout']) && is_array($lb_takeover_page['schema']['layout'])
        ? $lb_takeover_page['schema']['layout']
        : [];

    $lb_native_body_autoscale = !empty($layout_state['native_body_autoscale']);
    $has_explicit_native_body_autoscale = array_key_exists('native_body_autoscale', $layout_state);
    $native_body_width_mode = isset($layout_state['native_body_width_mode']) ? (string) $layout_state['native_body_width_mode'] : '';
    $lb_native_body_full_padding = isset($layout_state['native_body_full_padding']) ? (int) $layout_state['native_body_full_padding'] : 20;
    if ($lb_native_body_full_padding < 0) {
        $lb_native_body_full_padding = 0;
    }
    if ($lb_native_body_full_padding > 60) {
        $lb_native_body_full_padding = 60;
    }

    if (!$has_explicit_native_body_autoscale) {
        $autoscale_sections = isset($lb_takeover_page['schema']['sections']) && is_array($lb_takeover_page['schema']['sections'])
            ? $lb_takeover_page['schema']['sections']
            : [];

        if ($autoscale_sections) {
            $lb_native_body_autoscale = true;

            foreach ($autoscale_sections as $autoscale_section) {
                $autoscale_settings = isset($autoscale_section['settings']) && is_array($autoscale_section['settings'])
                    ? $autoscale_section['settings']
                    : [];

                if (($autoscale_settings['autoscale_base_blocks'] ?? false) !== true) {
                    $lb_native_body_autoscale = false;
                    break;
                }
            }
        }
    }

    if ($native_body_width_mode === 'full') {
        $lb_native_body_fullwidth = true;
    } elseif ($native_body_width_mode === 'grid') {
        $lb_native_body_fullwidth = false;
    } else {
        $lb_native_body_fullwidth = $lb_native_body_autoscale;
    }

    $body_columns = $lb_takeover_runtime['shell']['body_columns'] ?? ($lb_takeover_page['schema']['layout']['body_columns'] ?? []);
    $body_columns_mode = (string) ($body_columns['mode'] ?? '1');
    $body_left_span = (int) ($body_columns['left_span'] ?? 0);
    $body_right_span = (int) ($body_columns['right_span'] ?? 0);
    $body_main_span = (int) ($body_columns['body_span'] ?? 12);

    $has_left_content_sidebar = in_array($body_columns_mode, ['2-left', '3'], true) && ($lb_left_sidebar_html !== '');
    $has_right_content_sidebar = in_array($body_columns_mode, ['2-right', '3'], true) && ($lb_right_sidebar_html !== '');
    $has_content_body_widgets = false;

    if (!$has_left_content_sidebar) {
        $body_left_span = 0;
    }
    if (!$has_right_content_sidebar) {
        $body_right_span = 0;
    }

    $body_main_span = 12 - $body_left_span - $body_right_span;
    if ($body_main_span < 4) {
        $body_main_span = 4;
    }

    $content_grid_style = '--lb-content-left-span:' . $body_left_span . ';--lb-content-right-span:' . $body_right_span . ';--lb-content-main-span:' . $body_main_span . ';';
}

$content_grid_class = 'nordic-shell__content-grid';
if ($has_left_content_sidebar) {
    $content_grid_class .= ' nordic-shell__content-grid--with-left';
}
if ($has_right_content_sidebar) {
    $content_grid_class .= ' nordic-shell__content-grid--with-right';
}

$modern_skin_rows = null;

if ($nordic_use_modern_skin) {
    try {
        $widgets_model = cmsCore::getModel('widgets');

        if ($widgets_model && method_exists($widgets_model, 'getLayoutRows')) {
            $modern_skin_rows = $widgets_model->getLayoutRows('modern');

            if (is_array($modern_skin_rows) && $modern_skin_rows) {
                $legacy_bind_map = !empty($shell_scheme['legacy_bind_map']) && is_array($shell_scheme['legacy_bind_map'])
                    ? $shell_scheme['legacy_bind_map']
                    : [];

                $has_modern_positions = $this->hasWidgetsOn(['pos_26', 'pos_27', 'pos_29', 'pos_8', 'pos_11']);
                $effective_legacy_bind_map = $has_modern_positions ? [] : $legacy_bind_map;

                // Transitional map modern->nordic нужен только до БД-синхронизации.
                // После синхронизации nordic получает native modern позиции,
                // и этот map должен быть отключен для 1:1 старта как modern.
                $modern_skin_position_map = $has_modern_positions ? [] : [
                    'pos_26'     => 'site_top',
                    'pos_27'     => 'header_primary',
                    'pos_31'     => '__nordic_void_pos_31',
                    'pos_29'     => '__nordic_void_pos_29',
                    'con_header' => 'hero',
                    'pos_10'     => 'before_content',
                    'pos_33'     => '__nordic_void_pos_33',
                    'pos_8'      => 'content_body',
                    'pos_34'     => 'content_sidebar_left',
                    'pos_9'      => 'content_sidebar_right',
                    'pos_17'     => 'after_content',
                    'pos_18'     => '__nordic_void_pos_18',
                    'pos_38'     => 'footer_col1_about',
                    'pos_39'     => 'footer_col2_sections',
                    'pos_40'     => 'footer_col3_contacts',
                    'pos_11'     => 'footer_secondary',
                    'pos_32'     => '__nordic_void_pos_32'
                ];

                if ($effective_legacy_bind_map || $modern_skin_position_map) {
                    $mapPosition = function($position) use ($effective_legacy_bind_map, $modern_skin_position_map) {
                        $position = trim((string) $position);
                        if ($position === '') {
                            return $position;
                        }

                        if (isset($modern_skin_position_map[$position])) {
                            return (string) $modern_skin_position_map[$position];
                        }

                        return (string) ($effective_legacy_bind_map[$position] ?? $position);
                    };

                    $mapRows = function(array $rows) use (&$mapRows, $mapPosition) {
                        foreach ($rows as $row_id => $row) {
                            if (!empty($row['positions']) && is_array($row['positions'])) {
                                $row['positions'] = array_values(array_unique(array_map($mapPosition, $row['positions'])));
                            }

                            if (!empty($row['cols']) && is_array($row['cols'])) {
                                foreach ($row['cols'] as $col_id => $col) {
                                    if (!empty($col['name'])) {
                                        $col['name'] = $mapPosition($col['name']);
                                    }

                                    if (!empty($col['positions']) && is_array($col['positions'])) {
                                        $col['positions'] = array_values(array_unique(array_map($mapPosition, $col['positions'])));
                                    }

                                    if (!empty($col['rows']) && is_array($col['rows'])) {
                                        if (!empty($col['rows']['before']) && is_array($col['rows']['before'])) {
                                            $col['rows']['before'] = $mapRows($col['rows']['before']);
                                        }

                                        if (!empty($col['rows']['after']) && is_array($col['rows']['after'])) {
                                            $col['rows']['after'] = $mapRows($col['rows']['after']);
                                        }
                                    }

                                    $row['cols'][$col_id] = $col;
                                }
                            }

                            $rows[$row_id] = $row;
                        }

                        return $rows;
                    };

                    $modern_skin_rows = $mapRows($modern_skin_rows);
                }
            } else {
                $modern_skin_rows = null;
            }
        }
    } catch (Throwable $exception) {
        $modern_skin_rows = null;
    }
}

if ($lb_effective_page_trace_comment !== '') {
    echo $lb_effective_page_trace_comment . "\n";
}
?>
<!DOCTYPE html>
<html <?php echo html_attr_str(($this->layout_params['attr'] ?? []), false); ?>>
    <head>
        <title><?php $this->title(); ?></title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="<?php echo cmsForm::getCSRFToken(); ?>">
<?php if (!$config->disable_copyright) { ?>
        <meta name="generator" content="InstantCMS">
<?php } ?>
    <?php if (!empty($this->options['head_html_top'])) { ?>
        <?php echo $this->options['head_html_top'] . "\n"; ?>
    <?php } ?>
<?php
    if ($nordic_use_modern_skin) {
        $this->addMainCSS('templates/modern/css/theme.css');
    } else {
        $this->addMainTplCSSName(['theme']);
        if ($lb_effective_shell_style !== '') {
            $this->addHead('<style>:root{' . $lb_effective_shell_style . '}</style>');
        }
        // Nordic фирменные шрифты: PT Serif (контент) + Roboto Condensed (UI)
        $this->addHead('<link rel="dns-prefetch" href="https://fonts.googleapis.com">');
        $this->addHead('<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>');
        $this->addHead('<link rel="dns-prefetch" href="https://fonts.gstatic.com">');
        $this->addHead('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
        $this->addCSS('https://fonts.googleapis.com/css2?family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Roboto+Condensed:wght@300;400;600;700&display=swap', false);
    }
        $this->addMainTplJSName('jquery', true);
        $this->addMainTplJSName(['vendors/popper.js/js/popper.min', 'vendors/bootstrap/bootstrap.min']);
        $this->onDemandTplJSName(['vendors/photoswipe/photoswipe.min']);
        $this->onDemandTplCSSName(['photoswipe']);
        $this->addMainTplJSName(['core', 'modal']);
?>
        <?php $this->head(true, !empty($this->options['js_print_head']), true); ?>
    <?php if (!empty($this->options['favicon_head_html'])) { ?>
        <?php echo $this->options['favicon_head_html'] . "\n"; ?>
    <?php } ?>
    <?php if (!empty($this->options['favicon']['path'])) { ?>
        <link rel="icon" href="<?php echo $config->upload_root . $this->options['favicon']['path']; ?>" type="<?php echo pathinfo($this->options['favicon']['path'], PATHINFO_EXTENSION) === 'svg' ? 'image/svg+xml' : 'image/x-icon'; ?>">
    <?php } ?>
    </head>
    <body id="<?php echo $device_type; ?>_device_type" data-device="<?php echo $device_type; ?>"<?php if (!empty($_nc_body_page_type)) { ?> data-page-type="<?php html($_nc_body_page_type); ?>"<?php } ?><?php if (!empty($shell_runtime['variant_key'])) { ?> data-shell-variant="<?php html($shell_runtime['variant_key']); ?>"<?php } ?><?php if (!empty($shell_runtime['body_layout'])) { ?> data-shell-layout="<?php html($shell_runtime['body_layout']); ?>"<?php } ?><?php if (!empty($shell_chrome['header_variant'])) { ?> data-shell-header="<?php html($shell_chrome['header_variant']); ?>"<?php } ?><?php if (!empty($shell_chrome['footer_variant'])) { ?> data-shell-footer="<?php html($shell_chrome['footer_variant']); ?>"<?php } ?><?php if (!empty($shell_chrome['menu_placement'])) { ?> data-shell-menu="<?php html($shell_chrome['menu_placement']); ?>"<?php } ?><?php if (!empty($shell_chrome['mobile_menu_mode'])) { ?> data-shell-mobile-menu="<?php html($shell_chrome['mobile_menu_mode']); ?>"<?php } ?><?php if (!empty($shell_chrome['homepage_shell_mode'])) { ?> data-shell-homepage-mode="<?php html($shell_chrome['homepage_shell_mode']); ?>"<?php } ?> class="d-flex flex-column min-vh-100<?php if (!$nordic_use_modern_skin) { ?> nordic-template<?php } ?><?php if (!empty($body_classes)) { ?> <?php html(implode(' ', $body_classes)); ?><?php } ?> <?php html($this->options['body_classes'] ?? ''); ?>">
        <a class="<?php echo $nordic_use_modern_skin ? 'sr-only sr-only-focusable' : 'nordic-skip-link'; ?>" href="#nordic-content-frame">Перейти к содержимому</a>

        <?php if ($nordic_use_modern_skin) { ?>
            <?php
                // В modern-skin не используем nordic-shell разметку, иначе получается
                // смесь modern CSS + nordic markup и визуальная «поломка».
                if ($lb_takeover_active) {
                    $content_slot_key = (string) (($lb_takeover_runtime['shell']['content_slot'] ?? '') ?: 'content_body');
                    $resolved_content_slot_key = $content_slot_key;
                    $lb_content_html = $lbGetBuilderSlotHtml($content_slot_key);
                    $lb_can_render_native_body = false;
                    if ($lb_content_html === '' && $content_slot_key !== 'content_body') {
                        $resolved_content_slot_key = 'content_body';
                        $lb_content_html = $lbGetBuilderSlotHtml('content_body');
                    }
                    $lb_can_render_native_body = $lbIsNativeRuntimeSlot($resolved_content_slot_key) || (($lb_takeover_page['adapter_key'] ?? '') !== 'standalone_landing');

                    $rows_for_modern_skin = $modern_skin_rows ?: $rows;
                    $modern_header_positions = ['pos_26', 'pos_27', 'pos_29', 'pos_31'];
                    $modern_footer_positions = ['pos_38', 'pos_39', 'pos_40', 'pos_11'];
                    $modern_header_rows = [];
                    $modern_footer_rows = [];

                    foreach ($rows_for_modern_skin as $layout_row) {
                        $row_positions = !empty($layout_row['positions']) && is_array($layout_row['positions']) ? $layout_row['positions'] : [];
                        if (!$row_positions) {
                            continue;
                        }

                        if (array_intersect($row_positions, $modern_header_positions)) {
                            $modern_header_rows[] = $layout_row;
                            continue;
                        }

                        if (array_intersect($row_positions, $modern_footer_positions)) {
                            $modern_footer_rows[] = $layout_row;
                        }
                    }

                    if ($modern_header_rows) {
                        $this->renderLayoutChild('scheme', [
                            'rows' => $modern_header_rows,
                            'nordic_disable_reserved_filter' => true
                        ]);
                    }

                    echo '<main id="nordic-content-frame" class="container py-4">';
                    $lb_hero_html = $lbGetBuilderSlotHtml('hero');
                    if ($lb_hero_html !== '') { echo $lb_hero_html; }
                    $lb_before_html = $lbGetBuilderSlotHtml('before_content');
                    if ($lb_before_html !== '') { echo $lb_before_html; }
                    $has_content_sidebars = ($has_left_content_sidebar || $has_right_content_sidebar);

                    if ($has_content_sidebars) {
                        $main_span = max(1, min(12, (int) ($body_main_span ?? 12)));
                        $left_span = max(1, min(11, (int) ($body_left_span ?? 3)));
                        $right_span = max(1, min(11, (int) ($body_right_span ?? 3)));

                        $native_body_layout_class = 'lb-native-body-layout';
                        $native_body_layout_attrs = '';
                        if ($lb_native_body_fullwidth) {
                            $native_body_layout_class .= ' lb-native-body-layout--autoscale';
                            $native_body_layout_attrs = ' style="--lb-native-body-full-padding:' . (int) $lb_native_body_full_padding . 'px;"';
                        }

                        echo '<div class="' . html($native_body_layout_class, false) . '"' . $native_body_layout_attrs . '>';

                        if ($has_left_content_sidebar && $lb_left_sidebar_html !== '') {
                            echo '<aside class="lb-native-body-col lb-native-body-col--left" data-slot="content_sidebar_left" style="grid-column:span ' . (int) $left_span . ';">' . $lb_left_sidebar_html . '</aside>';
                        }

                        echo '<div class="lb-native-body-col lb-native-body-col--main" data-slot="content_body" style="grid-column:span ' . (int) $main_span . ';">';
                        if ($lb_content_html !== '') {
                            echo $lb_content_html;
                        } elseif ($lb_can_render_native_body) {
                            echo '<div class="lb-native-body-runtime">';
                            $this->body();
                            echo '</div>';
                        }
                        echo '</div>';

                        if ($has_right_content_sidebar && $lb_right_sidebar_html !== '') {
                            echo '<aside class="lb-native-body-col lb-native-body-col--right" data-slot="content_sidebar_right" style="grid-column:span ' . (int) $right_span . ';">' . $lb_right_sidebar_html . '</aside>';
                        }

                        echo '</div>';
                    } else {
                        if ($lb_content_html !== '') {
                            echo $lb_content_html;
                        } elseif ($lb_can_render_native_body) {
                            $native_body_class = 'lb-native-body-runtime';
                            if ($lb_native_body_fullwidth) {
                                $native_body_class .= ' lb-native-body-runtime--autoscale';
                            }
                            $native_body_attrs = '';
                            if (strpos($native_body_class, 'lb-native-body-runtime--autoscale') !== false) {
                                $native_body_attrs = ' style="--lb-native-body-full-padding:' . (int) $lb_native_body_full_padding . 'px;"';
                            }
                            echo '<div class="' . html($native_body_class, false) . '"' . $native_body_attrs . '>';
                            $this->body();
                            echo '</div>';
                        }
                    }
                    $lb_after_html = $lbGetBuilderSlotHtml('after_content');
                    if ($lb_after_html !== '') { echo $lb_after_html; }
                    echo '</main>';

                    if ($modern_footer_rows) {
                        $this->renderLayoutChild('scheme', [
                            'rows' => $modern_footer_rows,
                            'nordic_disable_reserved_filter' => true
                        ]);
                    }
                } else {
                    $rows_for_modern_skin = $modern_skin_rows ?: $rows;
                    $this->renderLayoutChild('scheme', [
                        'rows' => $rows_for_modern_skin,
                        'nordic_disable_reserved_filter' => true
                    ]);
                }
            ?>
        <?php } else { ?>
        <div class="nordic-shell nordic-shell--header-<?php html($shell_chrome['header_variant'] ?? 'classic'); ?> nordic-shell--footer-<?php html($shell_chrome['footer_variant'] ?? 'columns_4'); ?> nordic-shell--menu-<?php html($menu_placement); ?> nordic-shell--mobile-menu-<?php html($shell_chrome['mobile_menu_mode'] ?? 'drawer'); ?> nordic-shell--homepage-mode-<?php html($shell_chrome['homepage_shell_mode'] ?? 'inherit'); ?>"<?php if (!$nordic_use_modern_skin && !empty($lb_effective_shell_style)) { ?> style="<?php html($lb_effective_shell_style); ?>"<?php } ?>>

            <?php if (!$config->is_site_on) { ?>
                <div class="nordic-shell__notice">
                    <?php if (cmsUser::isAdmin()) { ?>
                        <?php printf(ERR_SITE_OFFLINE_FULL, href_to('admin', 'settings', 'siteon')); ?>
                    <?php } else { ?>
                        <?php echo ERR_SITE_OFFLINE; ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($isSlotEnabled('site_top')) { $renderSlot($resolveSlotPositions('site_top'), 'nordic-shell__site-top'); } ?>

            <header class="nordic-shell__header">
                <?php if ($isSlotEnabled('header_primary')) { $renderSlot($resolveSlotPositions('header_primary'), 'nordic-shell__header-primary'); } ?>
                <?php if ($isSlotEnabled('header_secondary')) { $renderSlot($resolveSlotPositions('header_secondary'), 'nordic-shell__header-secondary'); } ?>
            </header>

            <?php
                $lb_hero_html = $lbGetBuilderSlotHtml('hero');
                if ($lb_hero_html !== '') {
                    echo '<section class="nordic-shell__hero">' . $lb_hero_html . '</section>';
                } elseif (!$lb_takeover_active && $isSlotEnabled('hero')) {
                    $renderSlot($resolveSlotPositions('hero'), 'nordic-shell__hero');
                }

                $lb_before_html = $lbGetBuilderSlotHtml('before_content');
                if ($lb_before_html !== '') {
                    echo '<section class="nordic-shell__before-content">' . $lb_before_html . '</section>';
                } elseif (!$lb_takeover_active && $isSlotEnabled('before_content')) {
                    $renderSlot($resolveSlotPositions('before_content'), 'nordic-shell__before-content');
                }
            ?>

            <main class="nordic-shell__main">
                <div class="nordic-shell__content-frame" id="nordic-content-frame" data-slot="content_body">
                    <div class="<?php html($content_grid_class); ?>"<?php if ($content_grid_style !== '') { ?> style="<?php html($content_grid_style); ?>"<?php } ?>>
                        <?php if ($has_left_content_sidebar) { ?>
                            <aside class="nordic-shell__content-sidebar nordic-shell__content-sidebar--left" data-slot="content_sidebar_left">
                                <?php if ($lb_takeover_active) { ?>
                                    <?php echo $lb_left_sidebar_html; ?>
                                <?php } else { ?>
                                    <?php $renderPositionGroup($left_sidebar_positions, 'nordic-shell__content-sidebar-group', 'wrapper_plain', 'nordic-shell__content-sidebar-widget'); ?>
                                <?php } ?>
                            </aside>
                        <?php } ?>
                        <div class="nordic-shell__content-body-slot" data-slot="content_body">
                            <?php if ($has_content_body_widgets) { ?>
                                <?php $renderPositionGroup($content_body_positions, 'nordic-shell__content-body-widgets', 'wrapper_plain', 'nordic-shell__content-widget'); ?>
                            <?php } ?>
                            <div class="nordic-shell__content-body-runtime">
                                <?php
                                    if ($lb_takeover_active) {
                                        $content_slot_key = (string) (($lb_takeover_runtime['shell']['content_slot'] ?? '') ?: 'content_body');
                                        $resolved_content_slot_key = $content_slot_key;
                                        $lb_content_html = $lbGetBuilderSlotHtml($content_slot_key);
                                        if ($lb_content_html === '' && $content_slot_key !== 'content_body') {
                                            $resolved_content_slot_key = 'content_body';
                                            $lb_content_html = $lbGetBuilderSlotHtml('content_body');
                                        }

                                        if ($lb_content_html !== '') {
                                            echo $lb_content_html;
                                        } elseif ($lbIsNativeRuntimeSlot($resolved_content_slot_key) || (($lb_takeover_page['adapter_key'] ?? '') !== 'standalone_landing')) {
                                            $native_body_class = 'lb-native-body-runtime';
                                            if ($lb_native_body_fullwidth && !$has_left_content_sidebar && !$has_right_content_sidebar) {
                                                $native_body_class .= ' lb-native-body-runtime--autoscale';
                                            }
                                            $native_body_attrs = '';
                                            if (strpos($native_body_class, 'lb-native-body-runtime--autoscale') !== false) {
                                                $native_body_attrs = ' style="--lb-native-body-full-padding:' . (int) $lb_native_body_full_padding . 'px;"';
                                            }
                                            echo '<div class="' . html($native_body_class, false) . '"' . $native_body_attrs . '>';
                                            $this->body();
                                            echo '</div>';
                                        }
                                    } else {
                                        $this->body();
                                    }
                                ?>
                            </div>
                        </div>
                        <?php if ($has_right_content_sidebar) { ?>
                            <aside class="nordic-shell__content-sidebar nordic-shell__content-sidebar--right" data-slot="content_sidebar_right">
                                <?php if ($lb_takeover_active) { ?>
                                    <?php echo $lb_right_sidebar_html; ?>
                                <?php } else { ?>
                                    <?php $renderPositionGroup($right_sidebar_positions, 'nordic-shell__content-sidebar-group', 'wrapper_plain', 'nordic-shell__content-sidebar-widget'); ?>
                                <?php } ?>
                            </aside>
                        <?php } ?>
                    </div>
                </div>
            </main>

            <?php
                $lb_after_html = $lbGetBuilderSlotHtml('after_content');
                if ($lb_after_html !== '') {
                    echo '<section class="nordic-shell__after-content">' . $lb_after_html . '</section>';
                } elseif (!$lb_takeover_active && $isSlotEnabled('after_content')) {
                    $renderSlot($resolveSlotPositions('after_content'), 'nordic-shell__after-content');
                }
            ?>

            <footer class="nordic-shell__footer">
                <?php if ($isSlotEnabled('footer_primary')) { $renderSlot($resolveSlotPositions('footer_primary'), 'nordic-shell__footer-primary'); } ?>
                <?php if ($isSlotEnabled('footer_secondary')) { $renderSlot($resolveSlotPositions('footer_secondary'), 'nordic-shell__footer-secondary'); } ?>
            </footer>
        </div>

        <?php } ?>

        <?php if (!empty($this->options['show_top_btn'])) { ?>
            <a class="btn btn-secondary btn-lg" href="#<?php echo $device_type; ?>_device_type" id="scroll-top">
                <?php html_svg_icon('solid', 'chevron-up'); ?>
            </a>
        <?php } ?>
        <?php if (!empty($this->options['show_cookiealert'])) { ?>
            <div class="alert text-center py-3 border-0 rounded-0 m-0 position-fixed fixed-bottom icms-cookiealert" id="icms-cookiealert">
                <div class="container">
                    <?php echo $this->options['cookiealert_text']; ?>
                    <button type="button" class="ml-2 btn btn-primary btn-sm acceptcookies">
                        <?php echo LANG_MODERN_THEME_COOKIEALERT_AGREE; ?>
                    </button>
                </div>
            </div>
        <?php } ?>
        <?php if ($config->debug && cmsUser::isAdmin()) { ?>
            <?php $this->renderAsset('ui/debug', ['core' => $core]); ?>
        <?php } ?>
        <script nonce="<?php echo $this->nonce; ?>"><?php echo $this->getLangJS('LANG_LOADING', 'LANG_ALL', 'LANG_COLLAPSE', 'LANG_EXPAND'); ?></script>
        <?php if (empty($this->options['js_print_head'])) { ?>
            <?php $this->printJavascriptTags(); ?>
        <?php } ?>
        <?php $this->bottom(); ?>
        <?php $this->onDemandPrint(); ?>
    </body>
</html>