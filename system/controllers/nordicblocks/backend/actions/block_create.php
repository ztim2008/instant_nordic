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

        $definition = $this->model->getBlockDefinition($type);
        if (!$definition) {
            cmsCore::addFlashMessage('error', 'Неизвестный тип блока: ' . htmlspecialchars($type));
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        // Заполняем дефолтные props из схемы
        $default_props = [];
        if (!empty($definition['schema']['fields'])) {
            foreach ($definition['schema']['fields'] as $field) {
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
