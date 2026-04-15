<?php

    $item_title_plain = trim(strip_tags((string)($item['title'] ?? 'Материал')));
    $item_nordic_id = 'content-item-' . $ctype['name'] . '-' . (int)$item['id'];

    $this->addHead('<link rel="canonical" href="'.href_to_abs($ctype['name'], $item['slug'] . '.html').'">');

?>

<div data-nordic-id="<?php html($item_nordic_id); ?>" data-nordic-role="content.item" data-nordic-label="<?php html($item_title_plain !== '' ? ('Материал: ' . $item_title_plain) : 'Материал'); ?>">

<?php

    $this->renderContentItem($ctype['name'], [
        'item'             => $item,
        'ctype'            => $ctype,
        'fields'           => $fields,
        'fields_fieldsets' => $fields_fieldsets,
        'props'            => $props,
        'props_values'     => $props_values,
        'props_fields'     => $props_fields,
        'props_fieldsets'  => $props_fieldsets,
    ]);

    if (!empty($childs['lists'])){
        foreach($childs['lists'] as $index => $list){
            ?>
            <div data-nordic-id="<?php html($item_nordic_id . '-child-list-' . (int)$index); ?>" data-nordic-role="content.item.child-list" data-nordic-label="<?php html(!empty($list['title']) ? ('Связанный список: ' . trim(strip_tags((string)$list['title']))) : 'Связанный список'); ?>">
            <?php if ($list['title']){ ?><h2><?php echo $list['title']; ?></h2><?php }
            echo $list['html'];
            ?>
            </div>
            <?php
        }
    }

?>

<?php if ($item['is_approved'] && $item['approved_by'] && ($user->is_admin || $user->id == $item['user_id'])){ ?>
    <div class="content_moderator_info small text-muted my-3 text-right" data-nordic-id="<?php html($item_nordic_id . '-moderation'); ?>" data-nordic-role="content.item.moderation" data-nordic-label="Модерация материала">
        <?php echo LANG_MODERATION_APPROVED_BY; ?>
        <a href="<?php echo href_to_profile($item['approved_by']); ?>">
            <?php echo $item['approved_by']['nickname']; ?>
        </a>
        <?php echo html_date_time($item['date_approved']); ?>
    </div>
<?php } ?>

<?php $this->block('after_content_item'); ?>

</div>

<?php if (!empty($item['comments_widget'])){ ?>
    <div data-nordic-id="<?php html($item_nordic_id . '-comments'); ?>" data-nordic-role="content.item.comments" data-nordic-label="Комментарии материала">
        <?php echo $item['comments_widget']; ?>
    </div>
<?php } ?>
