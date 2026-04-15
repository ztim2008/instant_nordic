<?php
/**
 * Template Name: LANG_CP_LISTVIEW_STYLE_BASIC
 * Template Type: content
 */
if($ctype['options']['list_show_filter']) {
    $this->renderAsset('ui/filter-panel', [
        'css_prefix'   => $ctype['name'],
        'page_url'     => $page_url,
        'fields'       => $fields,
        'props_fields' => $props_fields,
        'props'        => $props,
        'filters'      => $filters,
        'ext_hidden_params' => $ext_hidden_params,
        'is_expanded'  => $ctype['options']['list_expand_filter']
    ]);
}
?>

<?php if (!$items){ ?>
    <p class="alert alert-info mt-4 alert-list-empty">
        <?php if(!empty($ctype['labels']['many'])){ ?>
            <?php echo sprintf(LANG_TARGET_LIST_EMPTY, $ctype['labels']['many']); ?>
        <?php } else { ?>
            <?php echo LANG_LIST_EMPTY; ?>
        <?php } ?>
    </p>
<?php return; } ?>

<?php
    $list_root_suffix = $ctype['name'] . '-' . (!empty($items[0]['id']) ? (int)$items[0]['id'] : 'empty') . '-' . count($items);
?>

<div class="content_list default_list <?php echo $ctype['name']; ?>_list mt-3 mt-lg-4" data-nordic-id="content-list-<?php html($list_root_suffix); ?>" data-nordic-role="content.list" data-nordic-label="Список материалов">

    <?php foreach($items as $item){ ?>
        <?php $item_nordic_base = 'content-list-item-' . $list_root_suffix . '-' . (int)$item['id']; ?>

        <div class="content_list_item <?php echo $ctype['name']; ?>_list_item clearfix" data-nordic-id="<?php html($item_nordic_base); ?>" data-nordic-role="content.list.item" data-nordic-label="<?php html(!empty($item['title']) ? ('Материал: ' . trim(strip_tags((string)$item['title']))) : 'Элемент списка'); ?>">
            <div class="icms-content-fields">
            <?php foreach($item['fields'] as $field){ ?>
                <div class="field ft_<?php echo $field['type']; ?> f_<?php echo $field['name']; ?> <?php echo $field['options']['wrap_type']; ?>_field <?php html($field['options']['wrap_style'].' '.$field['options']['wrap_style_list']); ?>" <?php if($field['options']['wrap_width']){ ?> style="width: <?php echo $field['options']['wrap_width']; ?>;"<?php } ?>>

                    <?php if ($field['label_pos'] !== 'none'){ ?>
                        <div class="title_<?php echo $field['label_pos']; ?>">
                            <?php echo string_replace_svg_icons($field['title']) . ($field['label_pos']==='left' ? ': ' : ''); ?>
                        </div>
                    <?php } ?>

                    <?php if ($field['name'] === 'title' && $ctype['options']['item_on']){ ?>
                        <h3 class="value" data-nordic-id="<?php html($item_nordic_base . '-title'); ?>" data-nordic-role="content.list.item.title" data-nordic-label="Заголовок материала">
                        <?php if (!empty($this->menus['list_actions_menu'])){ ?>
                            <div class="dropdown ml-2 float-right">
                                <button class="btn" type="button" data-toggle="dropdown">
                                    <?php html_svg_icon('solid', 'ellipsis-v'); ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <?php foreach($this->menus['list_actions_menu'] as $menu){ ?>
                                        <a class="dropdown-item <?php echo isset($menu['options']['class']) ? $menu['options']['class'] : ''; ?>" href="<?php echo string_replace_keys_values($menu['url'], $item); ?>" title="<?php html($menu['title']); ?>">
                                            <?php echo $menu['title']; ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
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
                        </h3>
                    <?php } else { ?>
                        <div class="value">
                            <?php echo $field['html']; ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            </div>

            <?php if (!empty($item['show_tags'])){ ?>
                <div class="tags_bar mt-3">
                    <?php echo html_tags_bar($item['tags'], 'content-'.$ctype['name'], 'btn btn-outline-secondary btn-sm icms-btn-tag', ''); ?>
                </div>
            <?php } ?>

            <?php if (!empty($item['info_bar'])){ ?>
            <div class="mobile-menu-wrapper mobile-menu-wrapper__info_bar">
                <div class="info_bar swipe-wrapper" data-nordic-id="<?php html($item_nordic_base . '-meta'); ?>" data-nordic-role="content.list.item.meta" data-nordic-label="Метаданные материала">
                    <?php foreach($item['info_bar'] as $bar){ ?>
                        <div class="bar_item swipe-item <?php echo !empty($bar['css']) ? $bar['css'] : ''; ?>" title="<?php html(!empty($bar['title']) ? $bar['title'] : ''); ?>">
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
            </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<?php echo html_pagebar($page, $perpage, $total, $page_url, $filter_query); ?>
