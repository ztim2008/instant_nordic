<?php if(!isset($ds_prefix)){ $ds_prefix = '/'; } ?>
<?php $active_filters_query = $this->controller->getActiveFiltersQuery(); ?>
<?php $nordic_base = isset($nordic_base) ? trim((string)$nordic_base) : ''; ?>
<div class="content_datasets mobile-menu-wrapper <?php if(isset($wrap_class)){ echo $wrap_class; } else { echo 'my-3 my-md-4'; } ?>"<?php if ($nordic_base !== '') { ?> data-nordic-id="<?php html($nordic_base); ?>" data-nordic-role="content.dataset.panel" data-nordic-label="Панель наборов данных"<?php } ?>>
    <ul class="nav nav-pills pills-menu dataset-pills"<?php if ($nordic_base !== '') { ?> data-nordic-id="<?php html($nordic_base . '-pills'); ?>" data-nordic-role="content.dataset.tabs" data-nordic-label="Переключатель наборов данных"<?php } ?>>
        <?php $ds_counter = 0; ?>
        <?php foreach($datasets as $set){ ?>
            <?php $ds_selected = ($dataset_name == $set['name'] || (!$dataset_name && $ds_counter==0)); ?>
            <li class="nav-item<?php if ($ds_selected){ ?> is-active<?php } ?> nav-item__<?php echo $set['name'].(!empty($set['target_controller']) ? '_'.$set['target_controller'] : ''); ?>"<?php if ($nordic_base !== '') { ?> data-nordic-id="<?php html($nordic_base . '-item-' . $ds_counter); ?>" data-nordic-role="content.dataset.tab" data-nordic-label="Набор данных: <?php html(trim(strip_tags((string)$set['title'])), false); ?>"<?php } ?>>

                <?php $ds_url = sprintf($base_ds_url, ($ds_counter > 0 ? $ds_prefix.$set['name'] : '')); ?>

                <?php if ($ds_selected){ ?>
                    <span class="nav-link active"<?php if ($nordic_base !== '') { ?> data-nordic-id="<?php html($nordic_base . '-link-' . $ds_counter); ?>" data-nordic-role="content.dataset.link" data-nordic-label="Активный набор: <?php html(trim(strip_tags((string)$set['title'])), false); ?>"<?php } ?>>
                        <?php echo $set['title']; ?>
                        <?php if (!empty($set['counter'])){ ?>
                            <span class="ml-1 counter badge"><?php html($set['counter']); ?></span>
                        <?php } ?>
                    </span>
                <?php } else { ?>
                    <a class="nav-link" href="<?php html($ds_url.($active_filters_query ? '?'.$active_filters_query : '')); ?>"<?php if ($nordic_base !== '') { ?> data-nordic-id="<?php html($nordic_base . '-link-' . $ds_counter); ?>" data-nordic-role="content.dataset.link" data-nordic-label="Набор данных: <?php html(trim(strip_tags((string)$set['title'])), false); ?>"<?php } ?>>
                        <?php echo $set['title']; ?>
                        <?php if (!empty($set['counter'])){ ?>
                            <span class="ml-1 counter badge"><?php html($set['counter']); ?></span>
                        <?php } ?>
                    </a>
                <?php } ?>
            </li>
            <?php $ds_counter++; ?>
        <?php } ?>
    </ul>
</div>
<?php if (!empty($current_dataset['description'])){ ?>
    <div class="content_datasets_description">
        <?php echo $current_dataset['description']; ?>
    </div>
<?php } ?>
