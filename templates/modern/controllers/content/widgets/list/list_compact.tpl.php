<?php
/**
 * Template Name: LANG_WD_CONTENT_LIST_STYLE_COMPACT
 * Template Type: widget
 */
?>
<?php
    $list_root_suffix = (isset($widget) && is_object($widget) && isset($widget->id)) ? ('widget-' . (int)$widget->id) : ($ctype['name'] . '-' . (!empty($items[0]['id']) ? (int)$items[0]['id'] : 'empty') . '-' . count($items));
?>
<div class="icms-widget__content_list content_list compact" data-nordic-id="content-list-<?php html($list_root_suffix); ?>" data-nordic-role="content.list" data-nordic-label="Компактный список материалов">
    <?php foreach($items as $index => $item) { ?>
        <?php $item_nordic_base = (isset($widget) && is_object($widget) && isset($widget->id)) ? ('content-list-widget-' . (int)$widget->id . '-item-' . $index) : ('content-list-item-' . $list_root_suffix . '-' . (int)$item['id']); ?>
        <div class="content_list_item <?php echo $ctype['name']; ?>_list_item clearfix" data-nordic-id="<?php html($item_nordic_base); ?>" data-nordic-role="content.list.item" data-nordic-label="<?php html(!empty($item['title']) ? ('Материал: ' . trim(strip_tags((string)$item['title']))) : 'Элемент списка'); ?>">
            <div class="icms-content-fields">
            <?php foreach($item['fields'] as $field){ ?>
                <div class="field ft_<?php echo $field['type']; ?> f_<?php echo $field['name']; ?> <?php echo $field['options']['wrap_type']; ?>_field" <?php if($field['options']['wrap_width']){ ?> style="width: <?php echo $field['options']['wrap_width']; ?>;"<?php } ?>>

                    <?php if ($field['label_pos'] !== 'none'){ ?>
                        <div class="title_<?php echo $field['label_pos']; ?>">
                            <?php echo $field['title'] . ($field['label_pos']==='left' ? ': ' : ''); ?>
                        </div>
                    <?php } ?>

                    <?php if ($field['name'] === 'title' && $ctype['options']['item_on']){ ?>
                        <h5 class="m-0 h6" data-nordic-id="<?php html($item_nordic_base . '-title'); ?>" data-nordic-role="content.list.item.title" data-nordic-label="Заголовок материала">
                        <?php if ($item['parent_id']){ ?>
                            <a class="parent_title" href="<?php echo rel_to_href($item['parent_url']); ?>" style="color: inherit;"><?php html($item['parent_title']); ?></a>
                            &rarr;
                        <?php } ?>

                        <?php if (!empty($item['is_private_item'])) { ?>
                            <?php html($item[$field['name']]); ?>
                            <span class="is_private text-secondary" title="<?php html($item['private_item_hint']); ?>">
                                <?php html_svg_icon('solid', 'lock'); ?>
                            </span>
                        <?php } else { ?>
                            <a href="<?php echo href_to($ctype['name'], $item['slug'].'.html'); ?>" style="color: inherit;">
                                <?php html($item[$field['name']]); ?>
                            </a>
                            <?php if ($item['is_private']) { ?>
                                <span class="is_private text-secondary" title="<?php echo LANG_PRIVACY_HINT; ?>">
                                    <?php html_svg_icon('solid', 'lock'); ?>
                                </span>
                            <?php } ?>
                        <?php } ?>
                        </h5>
                    <?php } else { ?>
                        <div class="value text-muted">
                            <?php echo $field['html']; ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            </div>

            <?php if (!empty($item['info_bar'])){ ?>
                <div class="info_bar p-0 pt-2 mt-2 bg-transparent" data-nordic-id="<?php html($item_nordic_base . '-meta'); ?>" data-nordic-role="content.list.item.meta" data-nordic-label="Метаданные материала">
                    <?php foreach($item['info_bar'] as $bar){ ?>
                        <div class="mr-2 bar_item <?php echo !empty($bar['css']) ? $bar['css'] : ''; ?>" title="<?php html(!empty($bar['title']) ? $bar['title'] : ''); ?>">
                            <?php if (!empty($bar['icon'])){ ?>
                                <?php html_svg_icon('solid', $bar['icon']); ?>
                            <?php } ?>
                            <?php if (!empty($bar['href'])){ ?>
                                <a class="stretched-link" href="<?php echo $bar['href']; ?>">
                                    <?php echo $bar['html']; ?>
                                </a>
                            <?php } else { ?>
                                <?php echo $bar['html']; ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>