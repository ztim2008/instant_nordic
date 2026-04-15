<?php
/**
 * Template Name: LANG_CP_LISTVIEW_STYLE_TABLE
 * Template Type: content
 */
if( $ctype['options']['list_show_filter'] ) {
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

<?php $first_item = reset($items); ?>
<?php $list_root_suffix = $ctype['name'] . '-' . (!empty($items[0]['id']) ? (int)$items[0]['id'] : 'empty') . '-' . count($items); ?>

<div class="content_list table <?php echo $ctype['name']; ?>_list table-responsive-md mt-3 mt-md-4" data-nordic-id="content-list-<?php html($list_root_suffix); ?>" data-nordic-role="content.list" data-nordic-label="Табличный список материалов">

    <table class="table table-hover">
        <thead>
            <tr>
                <?php foreach($first_item['fields_names'] as $field){ ?>
                    <th <?php if ($field['label_pos'] === 'none') { ?>class="d-none d-lg-table-cell"<?php } ?>>
                        <?php echo $field['label_pos'] !== 'none' ? string_replace_svg_icons($field['title']) : ''; ?>
                    </th>
                <?php } ?>
                <?php if (!empty($first_item['info_bar'])){ ?>
                    <th class="d-none d-lg-table-cell"></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $item){ ?>
            <?php $item_nordic_base = 'content-list-item-' . $list_root_suffix . '-' . (int)$item['id']; ?>
            <tr data-nordic-id="<?php html($item_nordic_base); ?>" data-nordic-role="content.list.item" data-nordic-label="<?php html(!empty($item['title']) ? ('Материал: ' . trim(strip_tags((string)$item['title']))) : 'Элемент списка'); ?>">
            <?php foreach($item['fields_names'] as $_field){ ?>
                <td class="align-middle field ft_<?php echo $_field['type']; ?> f_<?php echo $_field['name']; ?><?php if ($_field['label_pos'] === 'none') { ?> d-none d-lg-table-cell<?php } ?>">

                    <?php if (!isset($item['fields'][$_field['name']])) { continue; } ?>
                    <?php $field = $item['fields'][$_field['name']]; ?>

                    <?php if ($field['name'] === 'title' && $ctype['options']['item_on']){ ?>
                        <h3 class="h5 m-0" data-nordic-id="<?php html($item_nordic_base . '-title'); ?>" data-nordic-role="content.list.item.title" data-nordic-label="Заголовок материала">
                        <?php if ($item['parent_id']){ ?>
                            <a class="parent_title" href="<?php echo rel_to_href($item['parent_url']); ?>" style="color: inherit;">
                                <?php html($item['parent_title']); ?>
                            </a>
                            &rarr;
                        <?php } ?>
                        <a href="<?php echo href_to($ctype['name'], $item['slug'].'.html'); ?>" style="color: inherit;">
                            <?php html($item[$field['name']]); ?>
                        </a>
                        </h3>
                    <?php } else { ?>
                        <?php echo $field['html']; ?>
                    <?php } ?>
                </td>
            <?php } ?>
            <?php if (!empty($item['info_bar'])){ ?>
                <td class="align-middle d-none d-lg-table-cell">
                    <div class="info_bar" data-nordic-id="<?php html($item_nordic_base . '-meta'); ?>" data-nordic-role="content.list.item.meta" data-nordic-label="Метаданные материала">
                        <?php foreach($item['info_bar'] as $bar){ ?>
                            <div class="bar_item <?php echo !empty($bar['css']) ? $bar['css'] : ''; ?>" title="<?php html(!empty($bar['title']) ? $bar['title'] : ''); ?>">
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
                </td>
            <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>
<?php echo html_pagebar($page, $perpage, $total, $page_url, $filter_query); ?>