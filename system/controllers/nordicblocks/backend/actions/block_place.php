<?php

class actionNordicblocksBlockPlace extends cmsAction {

    public function run($block_id = 0) {
        $block_id = (int) $block_id;
        $block    = $this->model->getBlockById($block_id);

        if (!$block) {
            return cmsCore::error404();
        }

        if (($block['status'] ?? 'disabled') !== 'active') {
            cmsCore::addFlashMessage('error', 'Разместить можно только активный блок.');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        $template_name = (string) cmsConfig::get('template');
        $page_id       = 1;
        $position      = $this->resolvePosition($template_name);

        if ($position === '') {
            cmsCore::addFlashMessage('error', 'Не найдена доступная позиция в схеме шаблона.');
            return $this->redirect(href_to('admin', 'widgets') . '?template_name=' . urlencode($template_name));
        }

        $widget = $this->findBlockWidget();
        if (!$widget) {
            cmsCore::addFlashMessage('error', 'Виджет NordicBlocks: блок не зарегистрирован. Запустите nordicblocks-sync --apply.');
            return $this->redirect(href_to('admin', 'widgets') . '?template_name=' . urlencode($template_name));
        }

        $existing = $this->findExistingBinding((int) $widget['id'], $block_id, $template_name, $page_id, $position);
        if ($existing) {
            cmsCore::addFlashMessage('success', 'Блок уже размещён в этой позиции.');
            return $this->redirect(href_to('admin', 'widgets') . '?template_name=' . urlencode($template_name) . '&scroll_to=pos-' . urlencode($position));
        }

        $res = $this->model_backend_widgets->addWidgetBinding($widget, $page_id, $position, $template_name);
        if (!$res || empty($res['id'])) {
            cmsCore::addFlashMessage('error', 'Не удалось создать привязку виджета.');
            return $this->redirect(href_to('admin', 'widgets') . '?template_name=' . urlencode($template_name));
        }

        $this->model_backend_widgets->updateWidgetBinding((int) $res['id'], [
            'options' => [
                'block_id' => $block_id
            ]
        ]);

        cmsCore::addFlashMessage('success', 'Блок размещён через виджет NordicBlocks: блок.');

        return $this->redirect(
            href_to('admin', 'widgets') .
            '?template_name=' . urlencode($template_name) .
            '&scroll_to=pos-' . urlencode($position)
        );
    }

    private function resolvePosition($template_name) {
        $requested = preg_replace('/[^a-z0-9_\-]/i', '', (string) $this->request->get('position', ''));
        if ($requested !== '') {
            return $requested;
        }

        $db      = cmsDatabase::getInstance();
        $has_pos = $db->query(
            "SELECT `name` FROM `{#}layout_cols` lc
             INNER JOIN `{#}layout_rows` lr ON lr.id = lc.row_id
             WHERE lr.template = '%s' AND lc.name = 'pos_38'
             LIMIT 1",
            [$template_name],
            true
        );

        if ($has_pos && $has_pos->num_rows > 0) {
            return 'pos_38';
        }

        $rows = $this->model_backend_widgets->getLayoutRows($template_name);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                foreach ((array) ($row['positions'] ?? []) as $position) {
                    if (!is_string($position)) {
                        continue;
                    }
                    if ($position === '' || $position === '_unused' || $position === '_copy') {
                        continue;
                    }
                    return $position;
                }
            }
        }

        return '';
    }

    private function findBlockWidget() {
        $db = cmsDatabase::getInstance();

        $result = $db->query(
            "SELECT `id`, `title`
             FROM `{#}widgets`
             WHERE `name` = 'nordicblocks_block' AND (`controller` IS NULL OR `controller` = '')
             LIMIT 1",
            [],
            true
        );

        if (!$result || $result->num_rows < 1) {
            return null;
        }

        return $result->fetch_assoc();
    }

    private function findExistingBinding($widget_id, $block_id, $template_name, $page_id, $position) {
        $db = cmsDatabase::getInstance();

        $result = $db->query(
            "SELECT wb.`id`
             FROM `{#}widgets_bind` wb
             INNER JOIN `{#}widgets_bind_pages` bp ON bp.bind_id = wb.id
                         WHERE wb.`widget_id` = '%s'
               AND bp.`template` = '%s'
                             AND bp.`page_id` = '%s'
               AND bp.`position` = '%s'
               AND wb.`options` LIKE '%s'
             LIMIT 1",
                        [(string) $widget_id, $template_name, (string) $page_id, $position, '%block_id: ' . $block_id . '%'],
            true
        );

        if (!$result || $result->num_rows < 1) {
            return null;
        }

        return $result->fetch_assoc();
    }
}
