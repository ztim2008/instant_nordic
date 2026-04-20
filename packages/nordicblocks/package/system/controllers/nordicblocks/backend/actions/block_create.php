<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';

class actionNordicblocksBlockCreate extends cmsAction {

    public function run() {
        if (!$this->request->isMethod('POST')) {
            return cmsCore::error404();
        }

        if (!$this->cms_user->is_admin) {
            return cmsCore::error404();
        }

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrf_token)) {
            cmsCore::addFlashMessage('error', 'Некорректный CSRF token');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        $type  = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim((string) $this->request->get('type', ''))));
        $title = trim((string) $this->request->get('title', ''));

        if (!$type || !$title) {
            cmsCore::addFlashMessage('error', 'Укажите тип и название блока');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        if (!$this->model->isEditorSupportedBlockType($type)) {
            cmsCore::addFlashMessage('error', 'Этот тип блока сейчас недоступен в активном backend-потоке NordicBlocks.');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        $definition = $this->model->getEditorSupportedBlockDefinitions()[$type] ?? null;
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

        if ($block_id) {
            if (NordicblocksBlockContractNormalizer::supportsContractType($type)) {
                $contract = NordicblocksBlockContractNormalizer::normalize([
                    'id'     => (int) $block_id,
                    'type'   => $type,
                    'title'  => $title,
                    'status' => 'active',
                    'props'  => $default_props,
                ]);
                $this->model->saveBlockContract($block_id, $title, $contract);
            } elseif ($default_props) {
                $this->model->saveBlock($block_id, $title, $default_props);
            }
        }

        return $this->redirect(href_to($this->controller->root_url, 'block_edit', $block_id));
    }
}
