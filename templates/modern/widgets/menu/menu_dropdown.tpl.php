<?php
$dropdown_key = isset($widget->id) ? ('widget-' . (int)$widget->id) : trim((string)($widget->title ?? 'menu-dropdown'));
$dropdown_key = preg_replace('/[^a-z0-9\-_]+/i', '-', strtolower($dropdown_key)) ?: 'menu-dropdown';
?>
<div class="dropdown" data-nordic-id="menu-dropdown-<?php echo $dropdown_key; ?>" data-nordic-role="header.menu.dropdown" data-nordic-label="Выпадающее меню">
    <button class="btn btn-light" type="button" data-toggle="dropdown" data-nordic-id="menu-dropdown-<?php echo $dropdown_key; ?>-toggle" data-nordic-role="header.menu.toggle" data-nordic-label="Кнопка выпадающего меню">
        <?php if($widget->is_title){ ?>
            <span class="d-none d-md-inline-block"><?php echo $widget->title; ?></span>
        <?php } ?>
        <?php html_svg_icon('solid', 'ellipsis-v'); ?>
    </button>
    <?php
        $this->menu(
            $widget->options['menu'],
            $widget->options['is_detect'],
            (!empty($widget->options['class']) ? $widget->options['class'] : 'dropdown-menu dropdown-menu-right'),
            $widget->options['max_items'], empty($widget->options['is_detect_strict']),
            (!empty($widget->options['template']) ? $widget->options['template'] : 'menu'),
            $widget->title
        );
    ?>
</div>
