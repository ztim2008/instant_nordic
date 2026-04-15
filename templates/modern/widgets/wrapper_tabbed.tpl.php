<?php

    if(!isset($widgets)){ $widgets = [$widget]; }

    $wrap_class = ['icms-widget__tabbed'];
    $widget_links = [];
    $widgets_id = 'icms-widget__tabbed';

    foreach($widgets as $widget) {
        $widgets_id .= '_'.$widget['id'];
        if ($widget['class_wrap'] && !in_array($widget['class_wrap'], $wrap_class)) {
            $wrap_class[] = $widget['class_wrap'];
        }
        if (!empty($widget['links'])) {
            $widget_links[] = [
                'widget_id' => (int)$widget['id'],
                'id' => 'widget-links-'.$widget['id'],
                'links' => string_parse_list($widget['links'])
            ];
        }
    }

?>
<div class="card mb-3 mb-md-4 <?php echo implode(' ', $wrap_class); ?>" id="<?php echo $widgets_id; ?>">
<div
    class="card mb-3 mb-md-4 <?php echo implode(' ', $wrap_class); ?>"
    id="<?php echo $widgets_id; ?>"
    data-nordic-id="widget-tabbed-<?php echo $widgets_id; ?>"
    data-nordic-role="widget-wrapper"
    data-nordic-target="widget-tabbed"
    data-nordic-label="Табы виджетов"
>
    <div
        class="card-header h5 py-0 pl-0 d-flex align-items-center<?php if ($widget['class_title']) { ?> <?php echo $widget['class_title'];  } ?>"
        data-nordic-id="widget-tabbed-<?php echo $widgets_id; ?>-header"
        data-nordic-target="widget-tabbed-header"
        data-nordic-label="Заголовок табового виджета"
    >
        <ul class="nav nav-tabs border-0" id="<?php echo $widgets_id; ?>_tabs" data-nordic-id="widget-tabbed-<?php echo $widgets_id; ?>-tabs" data-nordic-target="widget-tabbed-tabs" data-nordic-label="Табы виджета">
            <?php foreach($widgets as $index => $widget) { ?>
                <?php $widget_title_plain = trim(strip_tags($widget['title'] ? string_replace_svg_icons($widget['title']) : (string)($index + 1))); ?>
                <li
                    class="nav-item<?php if ($widget['class_title']) { ?> <?php echo $widget['class_title'];  } ?>"
                    data-nordic-id="widget-<?php echo $widget['id']; ?>-tab-item"
                    data-widget-id="<?php echo $widget['id']; ?>"
                    data-nordic-target="widget-tab-item"
                    data-nordic-label="<?php html($widget_title_plain !== '' ? ('Таб: ' . $widget_title_plain) : 'Таб'); ?>"
                >
                    <a class="nav-link px-2 px-lg-3<?php if ($index==0) { ?> active<?php } ?>" data-toggle="tab" data-id="<?php echo $widget['id']; ?>" data-nordic-id="widget-<?php echo $widget['id']; ?>-tab-link" data-widget-id="<?php echo $widget['id']; ?>" data-nordic-target="widget-tab-link" data-nordic-label="<?php html($widget_title_plain !== '' ? $widget_title_plain : 'Ссылка таба'); ?>" href="#widget-<?php echo $widget['id']; ?>">
                        <?php echo $widget['title'] ? string_replace_svg_icons($widget['title']) : ($index+1); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <?php if ($widget_links) { ?>
            <div class="links ml-auto" data-nordic-id="widget-tabbed-<?php echo $widgets_id; ?>-links" data-nordic-role="widget-tabbed.links" data-nordic-label="Быстрые ссылки табового виджета">
                <?php foreach($widget_links as $index => $widget_link) { ?>
                    <div class="links-wrap" id="<?php echo $widget_link['id']; ?>" data-nordic-id="widget-<?php echo $widget_link['widget_id']; ?>-links-wrap" data-nordic-role="widget-tabbed.links.wrap" data-nordic-label="Ссылки таба" <?php if ($index>0) { ?>style="display: none"<?php } ?>>
                    <?php if($device_type !== 'desktop'){ ?>
                        <div class="dropdown">
                            <button class="btn btn-light" type="button" data-toggle="dropdown" data-nordic-id="widget-<?php echo $widget_link['widget_id']; ?>-links-toggle" data-nordic-role="widget-tabbed.links.toggle" data-nordic-label="Меню ссылок таба">
                                <?php html_svg_icon('solid', 'ellipsis-v'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                            <?php foreach($widget_link['links'] as $link_index => $link){ ?>
                                <li class="nav-item">
                                    <a class="nav-link text-nowrap" href="<?php echo (strpos($link['value'], 'http') === 0) ? $link['value'] : href_to($link['value']); ?>" data-nordic-id="widget-<?php echo $widget_link['widget_id']; ?>-extra-link-<?php echo $link_index; ?>" data-nordic-role="widget-tabbed.links.item" data-nordic-label="<?php html(trim(strip_tags((string)$link['id'])), false); ?>">
                                        <?php echo $link['id']; ?>
                                    </a>
                                </li>
                            <?php } ?>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <?php foreach($widget_link['links'] as $link_index => $link){ ?>
                            <a class="btn btn-outline-info btn-sm" href="<?php echo (strpos($link['value'], 'http') === 0) ? $link['value'] : href_to($link['value']); ?>" data-nordic-id="widget-<?php echo $widget_link['widget_id']; ?>-extra-link-<?php echo $link_index; ?>" data-nordic-role="widget-tabbed.links.item" data-nordic-label="<?php html(trim(strip_tags((string)$link['id'])), false); ?>">
                                <?php echo $link['id']; ?>
                            </a>
                        <?php } ?>
                    <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <div class="icms-widgets tab-content">
        <?php foreach($widgets as $index=>$widget) { ?>
            <?php $widget_title_plain = trim(strip_tags($widget['title'] ? string_replace_svg_icons($widget['title']) : (string)($index + 1))); ?>
            <div id="widget-<?php echo $widget['id']; ?>" class="card-body tab-pane<?php if ($index==0) { ?> active<?php } ?><?php if ($widget['class']) { ?> <?php echo $widget['class'];  } ?>" role="tabpanel" data-nordic-id="widget-<?php echo $widget['id']; ?>-tab-panel" data-widget-id="<?php echo $widget['id']; ?>" data-nordic-target="widget-tab-panel" data-nordic-label="<?php html($widget_title_plain !== '' ? ('Панель: ' . $widget_title_plain) : 'Панель таба'); ?>">
                <?php echo $widget['body']; ?>
                <?php if(cmsUser::isAdmin()){ ?>
                    <?php $this->addTplJSName('widgets'); ?>
                    <?php include 'wrap_edit_links.tpl.php'; ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>