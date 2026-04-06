<?php
/**
 * Nordic Smart Grid — виджет-схема с поддержкой drag-resize.
 * Переопределяет modern/controllers/admin/widgets_scheme.tpl.php.
 * Добавляет data-атрибуты для nordic-smart-grid.js и использует
 * $col['class'] как CSS-класс колонки (наряду с options['col_*']).
 */
?>
<?php if(!$rows){ ?>
<p class="alert alert-warning mt-4" role="alert"><?php echo LANG_CP_WIDGETS_ROW_NONE; ?></p>
<?php } ?>
<?php foreach ($rows as $row) { ?>
<?php
    $row_vertical_align   = $row['options']['vertical_align'] ?? '';
    $row_horizontal_align = $row['options']['horizontal_align'] ?? '';
?>
<div class="row no-gutters widgets-layout-scheme align-items-center<?php if($row['parent_id']){ ?> disable-sortable<?php } ?>"
     id="row-<?php echo $row['id']; ?>"
     data-id="<?php echo $row['id']; ?>">

    <div class="layout-row-title <?php if(!$row['parent_id']){ ?>layout-row-parent<?php } ?> col-sm-12 <?php if(!$row['parent_id'] && $rows_titles_pos == 'left'){ ?>col-lg-2<?php } ?> filled p-2 <?php if($rows_titles_pos == 'hide'){ ?>d-none<?php } else { ?>d-flex<?php } ?> justify-content-between">
        {cell:<?php echo $row['title']; ?>}
        <div class="layout-scheme-actions align-items-center">
            <a class="add mr-1 text-success text-decoration-none add-scheme-row ajax-modal"
               data-toggle="tooltip" data-placement="top"
               href="<?php echo $this->href_to('widgets', ['col_add', $row['id']]); ?>"
               title="<?php echo LANG_CP_WIDGETS_ADD_COL; ?>">
                <i class="icon-plus icons"></i>
            </a>
            <a class="edit mr-1 text-decoration-none edit-scheme-row ajax-modal"
               data-toggle="tooltip" data-placement="top"
               href="<?php echo $this->href_to('widgets', ['row_edit', $row['id']]); ?>"
               title="<?php echo LANG_EDIT; ?>">
                <i class="icon-pencil icons"></i>
            </a>
            <a class="delete text-danger text-decoration-none icms-action-confirm"
               data-toggle="tooltip" data-placement="top"
               href="<?php echo $this->href_to('widgets', ['row_delete', $row['id']], ['csrf_token' => cmsForm::getCSRFToken()]); ?>"
               title="<?php echo LANG_DELETE; ?>"
               data-confirm="<?php echo LANG_CP_WIDGETS_ROW_DEL_CONFIRM; ?>">
                <i class="icon-close icons"></i>
            </a>
        </div>
    </div>

    <?php if(!$row['parent_id']){ ?>
        <div class="w-100 d-md-none"></div>
    <?php } ?>

    <div class="col-sm-12 layout-row-body <?php if(!$row['parent_id']){ ?>layout-row-parent<?php } ?> <?php if(!$row['parent_id'] && $rows_titles_pos == 'left'){ ?>col-lg-10<?php } ?> bg-white">
        <div class="row no-gutters widgets-layout-scheme-col-wrap <?php html($row_vertical_align); ?> <?php html($row_horizontal_align); ?>">
        <?php foreach ($row['cols'] as $col) { ?>
            <?php if(!empty($col['options']['cut_before'])){ ?>
                <div class="w-100"></div>
            <?php } ?>
            <?php
            $width_hint    = LANG_AUTO;
            $col_class     = '';
            $col_width_list = [];

            // Читаем ширину из options['col_*'] если есть (новый формат)
            foreach ($col['options'] as $key => $value) {
                if (strpos($key, 'col_') === false) { continue; }
                if (empty($col['options'][$key])) { continue; }
                $col_type = preg_replace('#(col\-[a-z]+\-)#ui', '', $value);
                if (is_numeric($col_type)) {
                    $width_hint      = round((8.333333333 * $col_type), 2) . '%';
                    $col_width_list[] = $width_hint;
                } elseif ($col_type === 'auto') {
                    $width_hint = LANG_CP_WIDGETS_COL_AUTO;
                }
                $col_class = $value;
            }

            // Если options пусты — используем поле class напрямую (nordic формат)
            if (empty($col_class) && !empty($col['class'])) {
                $col_class = $col['class'];
                // Пытаемся вычислить ширину-подсказку из 'col-xl-6' и т.п.
                if (preg_match('/col-(?:xl|lg|md|sm)-(\d+)/', $col['class'], $m)) {
                    $width_hint = round((8.333333333 * (int)$m[1]), 2) . '%';
                    $col_width_list[] = $width_hint;
                } elseif (preg_match('/col-(\d+)$/', $col['class'], $m)) {
                    $width_hint = round((8.333333333 * (int)$m[1]), 2) . '%';
                    $col_width_list[] = $width_hint;
                }
            }

            // Финальный CSS класс колонки
            $final_class = $col_class ?: 'col-sm';

            if (!empty($col['rows'])) {
                $final_class .= ' bg-gray-500';
            } else {
                $final_class .= ' bg-white';
            }

            // Данные для smart-grid JS
            $sg_class    = htmlspecialchars($col['class'] ?? '', ENT_QUOTES);
            $sg_title    = htmlspecialchars($col['title'] ?? '', ENT_QUOTES);
            $sg_name     = htmlspecialchars($col['name'] ?? '', ENT_QUOTES);
            $sg_type     = htmlspecialchars($col['type'] ?? 'typical', ENT_QUOTES);
            $sg_tag      = htmlspecialchars($col['tag'] ?? 'div', ENT_QUOTES);
            $sg_row_id   = (int)($col['row_id'] ?? 0);
            $sg_edit_url = $this->href_to('widgets', ['col_edit', $col['id']]);
            ?>
            <div class="<?php echo $final_class; ?> p-1 widgets-layout-scheme-col"
                 id="col-<?php echo $col['id']; ?>"
                 data-id="<?php echo $col['id']; ?>"
                 data-col-class="<?php echo $sg_class; ?>"
                 data-col-title="<?php echo $sg_title; ?>"
                 data-col-name="<?php echo $sg_name; ?>"
                 data-col-type="<?php echo $sg_type; ?>"
                 data-col-tag="<?php echo $sg_tag; ?>"
                 data-col-row-id="<?php echo $sg_row_id; ?>"
                 data-col-edit-url="<?php echo htmlspecialchars($sg_edit_url, ENT_QUOTES); ?>">

                <?php if(!empty($col['rows']['before'])){ ?>
                    <?php $this->renderChild('widgets_scheme', ['rows' => $col['rows']['before'], 'rows_titles_pos' => $rows_titles_pos]); ?>
                <?php } ?>

                <div class="layout-col-title d-flex justify-content-between"
                     data-toggle="tooltip" data-placement="top"
                     title="<?php html($col['name'] . ($col_width_list ? ': ' . implode(', ', $col_width_list) : '')); ?>">
                    <span><?php echo $col['title']; ?> (<?php echo $width_hint; ?>)</span>
                    <div class="layout-scheme-actions d-flex align-items-center">
                        <?php if(!$row['parent_id']){ ?>
                        <a class="add mr-2 text-white text-decoration-none add-scheme-col ajax-modal"
                           href="<?php echo $this->href_to('widgets', ['row_add_ns', $col['id']]); ?>"
                           title="<?php echo LANG_CP_WIDGETS_ADD_ROW_P; ?>">
                            <i class="icon-plus icons d-block"></i>
                        </a>
                        <?php } ?>
                        <a class="edit mr-2 text-white text-decoration-none edit-scheme-col ajax-modal"
                           href="<?php echo $sg_edit_url; ?>"
                           title="<?php echo LANG_EDIT; ?>">
                            <i class="icon-pencil icons d-block"></i>
                        </a>
                        <a class="delete text-warning text-decoration-none icms-action-confirm"
                           href="<?php echo $this->href_to('widgets', ['col_delete', $col['id']], ['csrf_token' => cmsForm::getCSRFToken()]); ?>"
                           title="<?php echo LANG_DELETE; ?>"
                           data-confirm="<?php echo LANG_CP_WIDGETS_COL_DEL_CONFIRM; ?>">
                            <i class="icon-close icons d-block"></i>
                        </a>
                    </div>
                </div>

                {position:<?php echo $col['name']; ?>}

                <?php if(!empty($col['rows']['after'])){ ?>
                    <?php $this->renderChild('widgets_scheme', ['rows' => $col['rows']['after'], 'rows_titles_pos' => $rows_titles_pos]); ?>
                <?php } ?>
            </div>
        <?php } ?>
        </div>
    </div>
</div>
<?php } ?>
