<?php
    if( $this->controller->options['is_filter'] ) {
        $this->renderAsset('ui/filter-panel', array(
            'css_prefix' => 'users',
            'page_url'   => $page_url,
            'fields'     => $fields,
            'filters'    => $filters
        ));
    }
?>

<?php if ($profiles){ ?>

    <?php
        $index_first = $page * $perpage - $perpage + 1;
        $index = 0;
        $pos_colors = ['text-muted', 'text-warning','text-info', 'text-secondary'];
        $avatar_size = 'normal';
    ?>

    <div id="users_profiles_list" class="nb-users-catalog content_list mt-3 mt-md-4">

        <?php foreach($profiles as $profile){ ?>

            <article class="nb-users-card item <?php if (!empty($profile['item_css_class'])) { ?> <?php echo implode(' ', $profile['item_css_class']); ?><?php } ?>">

                <?php if ($dataset_name == 'rating') { ?>
                    <?php $position = $index_first + $index; ?>
                    <div class="nb-users-card__rank position icms-svg-icon text-center <?php echo isset($pos_colors[$position]) ? $pos_colors[$position] : $pos_colors[0]; ?>">
                        <?php if (in_array($position, range(1, 3))){ ?>
                            <?php html_svg_icon('solid', 'medal', 20); ?>
                            <span class="nb-users-card__rank-num"><?php echo $position; ?></span>
                        <?php } else {  ?>
                            <span class="nb-users-card__rank-num"><?php echo $position; ?></span>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (!empty($fields['avatar']) && $fields['avatar']['is_in_list']){ ?>
                    <a href="<?php echo href_to_profile($profile); ?>" class="nb-users-card__avatar icms-user-avatar <?php if (!empty($profile['is_online'])){ ?>peer_online<?php } else { ?>peer_no_online<?php } ?>">
                    <?php if($profile['avatar']){ ?>
                        <?php echo html_avatar_image($profile['avatar'], $avatar_size, $profile['nickname']); ?>
                    <?php } else { ?>
                        <?php echo html_avatar_image_empty($profile['nickname'], 'avatar__incatalog'); ?>
                    <?php } ?>
                    </a>
                <?php } ?>

                <div class="nb-users-card__body">
                    <?php if (!empty($fields['nickname']) && $fields['nickname']['is_in_list']){ ?>
                        <h5 class="nb-users-card__name">
                            <a href="<?php echo href_to_profile($profile); ?>">
                                <?php html($profile['nickname']); ?>
                            </a>
                        </h5>
                    <?php } ?>

                    <?php if (!empty($profile['fields'])){ ?>
                    <div class="nb-users-card__fields fields">
                        <?php foreach($profile['fields'] as $field){ ?>
                            <div class="field ft_<?php echo $field['type']; ?> f_<?php echo $field['name']; ?>">
                                <?php if ($field['label_pos'] != 'none'){ ?>
                                    <div class="title_<?php echo $field['label_pos']; ?>">
                                        <?php echo $field['title'] . ($field['label_pos']=='left' ? ': ' : ''); ?>
                                    </div>
                                <?php } ?>
                                <div class="value">
                                    <?php echo $field['html']; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <div class="nb-users-card__meta actions text-muted" <?php if (!empty($profile['notice_title'])) { ?>data-notice_title="<?php echo implode(', ', $profile['notice_title']); ?>"<?php } ?>>
                        <?php if ($dataset_name == 'popular') { ?>

                            <?php echo $profile['friends_count'] ? html_spellcount($profile['friends_count'], LANG_USERS_FRIENDS_SPELLCOUNT) : '&mdash;'; ?>

                        <?php } elseif ($dataset_name == 'rating') { ?>

                            <span class="rate_value karma <?php echo html_signed_class($profile['karma']); ?>" title="<?php echo LANG_KARMA; ?>"><?php echo html_signed_num($profile['karma']); ?></span>
                            <span class="nb-users-card__meta-sep" aria-hidden="true">·</span>
                            <span class="rate_value rating" title="<?php echo LANG_RATING; ?>"><?php echo $profile['rating']; ?></span>

                        <?php } else { ?>

                            <?php if (!$profile['is_online']){ ?>
                                <span><?php echo string_date_age_max($profile['date_log'], true); ?></span>
                            <?php } else { ?>
                                <span class="text-success is_online"><?php echo LANG_ONLINE; ?></span>
                            <?php } ?>

                        <?php } ?>
                    </div>
                </div>

                <?php if (!empty($profile['actions'])){ ?>
                <div class="nb-users-card__menu dropdown">
                    <button class="btn btn-dylan" type="button" data-toggle="dropdown" aria-label="<?php echo LANG_MORE; ?>">
                        <?php html_svg_icon('solid', 'ellipsis-v'); ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php foreach($profile['actions'] as $action){ ?>
                            <a class="dropdown-item <?php echo $action['class']; ?>" href="<?php echo $action['href']; ?>" title="<?php html($action['title']); ?>">
                                <?php echo $action['title']; ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

            </article>

            <?php $index++; ?>

        <?php } ?>

    </div>

    <?php echo html_pagebar($page, $perpage, $total, $page_url, $filters); ?>

<?php } else { ?>
    <div class="alert alert-info mt-4" role="alert">
        <?php echo sprintf(LANG_TARGET_LIST_EMPTY, LANG_USERS_GEN); ?>
    </div>
<?php } ?>
