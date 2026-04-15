<div
    class="icms-widget card mb-3 mb-md-4 <?php if ($widget['class_wrap']) { ?> <?php echo $widget['class_wrap'];  } ?>"
    id="widget_wrapper_<?php echo $widget['id']; ?>"
    data-nordic-id="widget-<?php echo $widget['id']; ?>"
    data-widget-id="<?php echo $widget['id']; ?>"
    data-nordic-role="widget-wrapper"
    data-nordic-target="widget-card"
    data-nordic-label="<?php html($widget['title'] ? trim(strip_tags(string_replace_svg_icons($widget['title']))) : 'Виджет'); ?>"
>
    <?php if ($widget['title'] && $is_titles){ ?>
    <h3
        class="h5 card-header d-flex align-items-center<?php if ($widget['class_title']) { ?> <?php echo $widget['class_title'];  } ?>"
        data-nordic-id="widget-<?php echo $widget['id']; ?>-title"
        data-widget-id="<?php echo $widget['id']; ?>"
        data-nordic-target="widget-card-title"
        data-nordic-label="<?php html(trim(strip_tags(string_replace_svg_icons($widget['title'])))); ?>"
    >
        <span><?php echo string_replace_svg_icons($widget['title']); ?></span>
        <?php if (!empty($widget['links'])) { ?>
            <span class="links ml-auto">
                <?php $links = string_parse_list($widget['links']); ?>
                <?php foreach($links as $link){ ?>
                    <a class="btn btn-outline-info btn-sm" href="<?php html((strpos($link['value'], 'http') === 0) ? $link['value'] : href_to($link['value'])); ?>">
                        <?php html($link['id']); ?>
                    </a>
                <?php } ?>
            </span>
        <?php } ?>
    </h3>
    <?php } ?>
    <div
        class="card-body<?php if ($widget['class']) { ?> <?php echo $widget['class'];  } ?>"
        data-nordic-id="widget-<?php echo $widget['id']; ?>-body"
        data-widget-id="<?php echo $widget['id']; ?>"
        data-nordic-target="widget-card-body"
        data-nordic-label="<?php html($widget['title'] ? ('Тело: ' . trim(strip_tags(string_replace_svg_icons($widget['title'])))) : 'Тело виджета'); ?>"
    >
        <?php echo $widget['body']; ?>
    </div>
    <?php if(cmsUser::isAdmin()){ ?>
        <?php $this->addTplJSName('widgets'); ?>
        <?php include 'wrap_edit_links.tpl.php'; ?>
    <?php } ?>
</div>
