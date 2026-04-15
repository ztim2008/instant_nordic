<div
    class="icms-widget mb-3 mb-md-4<?php if ($widget['class_wrap']) { ?> <?php echo $widget['class_wrap'];  } ?>"
    id="widget_wrapper_<?php echo $widget['id']; ?>"
    data-nordic-id="widget-<?php echo $widget['id']; ?>"
    data-widget-id="<?php echo $widget['id']; ?>"
    data-nordic-role="widget-wrapper"
    data-nordic-target="widget-plain"
    data-nordic-label="<?php html($widget['title'] ? trim(strip_tags(string_replace_svg_icons($widget['title']))) : 'Виджет'); ?>"
>
    <?php echo $widget['body']; ?>
</div>