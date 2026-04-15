<?php

class actionNordicblocksBlockCreate extends cmsAction {

    public function run() {
        if (!$this->request->isMethod('POST')) {
            return cmsCore::error404();
        }

        $type  = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim((string) $this->request->get('type', ''))));
        $title = trim((string) $this->request->get('title', ''));

        if (!$type || !$title) {
            cmsCore::addFlashMessage('error', 'Укажите тип и название блока');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        // Проверяем что такой тип блока существует
        $blocks_dir = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks';
        if (!is_dir("{$blocks_dir}/{$type}")) {
            cmsCore::addFlashMessage('error', 'Неизвестный тип блока: ' . htmlspecialchars($type));
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        $schema_file = "{$blocks_dir}/{$type}/schema.json";
        $schema = file_exists($schema_file) ? json_decode(file_get_contents($schema_file), true) : [];

        // Заполняем дефолтные props из схемы
        $default_props = [];
        if (!empty($schema['fields'])) {
            foreach ($schema['fields'] as $field) {
                $key = $field['key'] ?? null;
                if ($key !== null) {
                    $default_props[$key] = $field['default'] ?? '';
                }
            }
        }

        $block_id = $this->model->createBlock($type, $title);

        if ($block_id && $default_props) {
            $this->model->saveBlock($block_id, $title, $default_props);
        }

        return $this->redirect(href_to($this->controller->root_url, 'block_edit', $block_id));
    }
}
