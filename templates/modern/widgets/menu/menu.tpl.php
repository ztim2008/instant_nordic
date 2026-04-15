<?php
$options = (isset($widget->options) && is_array($widget->options)) ? $widget->options : [];
$menu_type = $options['menu_type'] ?? 'navbar';
$menu_key = isset($widget->id) ? ('widget-' . (int)$widget->id) : trim((string)($options['menu'] ?? 'menu'));
$menu_key = preg_replace('/[^a-z0-9\-_]+/i', '-', strtolower($menu_key)) ?: 'menu';
?>
<?php if ($menu_type == 'navbar') { ?>
    <?php
        $nav_class = ['navbar p-0'];
        if (!empty($options['navbar_expand'])) {
            $nav_class[] = $options['navbar_expand'];
        }
        if (!empty($options['navbar_color_scheme'])) {
            $nav_class[] = $options['navbar_color_scheme'];
        }
        $site_name = html(cmsConfig::get('sitename'), false);
    ?>
    <nav class="<?php echo implode(' ', $nav_class); ?>" data-nordic-id="menu-<?php echo $menu_key; ?>" data-nordic-role="header.menu" data-nordic-label="Меню">
        <?php if (!empty($options['toggler_icon'])) { ?>
            <?php if (!empty($options['toggler_show_sitename']) && empty($options['toggler_show_logo'])) { ?>
                <span class="navbar-brand icms-navbar-brand__show_on_hide" data-nordic-id="menu-<?php echo $menu_key; ?>-brand" data-nordic-role="header.logo" data-nordic-label="Логотип / название сайта">
                    <?php echo $site_name; ?>
                </span>
            <?php } ?>
            <?php if (!empty($options['toggler_show_logo'])) { ?>
                <<?php if($core->uri) { ?>a href="<?php echo href_to_home(); ?>"<?php } else { ?>span<?php } ?> class="navbar-brand flex-shrink-0" data-nordic-id="menu-<?php echo $menu_key; ?>-logo" data-nordic-role="header.logo" data-nordic-label="Логотип сайта">
                    <img src="<?php echo $logos['small_logo']; ?>" class="d-sm-none" alt="<?php echo $site_name; ?>">
                    <img src="<?php echo $logos['logo']; ?>" class="d-none d-sm-block" alt="<?php echo $site_name; ?>">
                    <?php if (!empty($options['toggler_show_sitename'])) { ?>
                        <?php echo $site_name; ?>
                    <?php } ?>
                </<?php if($core->uri) { ?>a<?php } else { ?>span<?php } ?>>
            <?php } ?>
            <button class="navbar-toggler" type="button" aria-label="<?php echo LANG_MENU; ?>" data-toggle="collapse" data-target="#target-<?php echo html((string)($options['menu'] ?? '')); ?>" data-nordic-id="menu-<?php echo $menu_key; ?>-toggle" data-nordic-role="header.menu.toggle" data-nordic-label="Кнопка меню">
                <span class="navbar-toggler-icon"></span>
            </button>
        <?php } ?>
        <div class="collapse<?php if (!empty($options['toggler_right_menu'])) { ?> ml-auto flex-grow-0<?php } ?> navbar-collapse<?php if (empty($options['navbar_expand'])) { ?> show<?php } ?>" id="target-<?php echo html((string)($options['menu'] ?? '')); ?>" data-nordic-id="menu-<?php echo $menu_key; ?>-panel" data-nordic-role="header.menu.panel" data-nordic-label="Панель меню">
            <?php
                $navbar_class = ['navbar-nav'];
                if (!empty($options['menu_nav_style'])) {
                    $navbar_class[] = $options['menu_nav_style']. ' w-100';
                }
                if (!empty($options['menu_nav_style_add'])) {
                    $navbar_class[] = $options['menu_nav_style_add'];
                }
                if (!empty($options['class'])) {
                    $navbar_class[] = $options['class'];
                }
                if (empty($options['navbar_expand'])) {
                    $navbar_class[] = 'flex-row icms-navbar-expanded';
                }

                $this->menu(
                    $options['menu'] ?? '',
                    !empty($options['is_detect']),
                    implode(' ', $navbar_class),
                    (int)($options['max_items'] ?? 0), empty($options['is_detect_strict']),
                    (!empty($options['template']) ? $options['template'] : 'menu'),
                    $widget->title
                );
            ?>
            <?php if (!empty($options['show_search_form'])) { ?>
                <form class="form-inline<?php if ((int)($options['show_search_form'] ?? 0) == 2) { ?> icms-navbar-form__show_on_hide<?php } ?> ml-auto my-2 my-lg-0" action="<?php echo href_to('search'); ?>" method="get" data-nordic-id="menu-<?php echo $menu_key; ?>-search" data-nordic-role="header.search" data-nordic-label="Форма поиска">
                    <div class="input-group">
                        <?php echo html_input('text', 'q', '', ['placeholder'=>ERR_SEARCH_TITLE, 'autocomplete' => 'off']); ?>
                        <div class="input-group-append">
                            <button class="btn" type="submit">
                                <?php html_svg_icon('solid', 'search'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            <?php } ?>
        </div>
    </nav>
<?php } elseif($menu_type == 'nav'){ ?>
    <?php

        $nav_class = ['nav'];
        if (!empty($options['navbar_color_scheme'])) {
            $nav_class[] = $options['navbar_color_scheme'];
        }
        if (!empty($options['menu_nav_style'])) {
            $nav_class[] = $options['menu_nav_style'];
        }
        if (!empty($options['menu_nav_style_add'])) {
            $nav_class[] = $options['menu_nav_style_add'];
        }
        if (!empty($options['class'])) {
            $nav_class[] = $options['class'];
        }
        if (!empty($options['menu_is_pills'])) {
            $nav_class[] = 'nav-pills';
        }
        if (!empty($options['menu_is_fill'])) {
            $nav_class[] = $options['menu_is_fill'];
        }

        echo '<div data-nordic-id="menu-' . html($menu_key, false) . '" data-nordic-role="header.menu" data-nordic-label="Меню">';
        $this->menu(
            $options['menu'] ?? '',
            !empty($options['is_detect']),
            implode(' ', $nav_class),
            (int)($options['max_items'] ?? 0), empty($options['is_detect_strict']),
            (!empty($options['template']) ? $options['template'] : 'menu'),
            $widget->title
        );
        echo '</div>';
    ?>
<?php } ?>
