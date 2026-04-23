<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/CatalogDraftRegistry.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';

class actionNordicblocksCatalogInstall extends cmsAction {

    public function run() {
        if (!$this->request->isMethod('POST') || !$this->cms_user->is_admin) {
            return cmsCore::error404();
        }

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrf_token)) {
            cmsCore::addFlashMessage('error', 'Некорректный CSRF token');
            return $this->redirect(href_to($this->controller->root_url, 'catalog'));
        }

        $slug = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim((string) $this->request->get('slug', ''))));
        if ($slug === '') {
            cmsCore::addFlashMessage('error', 'Не указан catalog entry для установки.');
            return $this->redirect(href_to($this->controller->root_url, 'catalog'));
        }

        $root_path = rtrim((string) cmsConfig::get('root_path'), '/\\');
        $entry = NordicblocksCatalogDraftRegistry::findEntryBySlug($root_path, $slug);
        if (!$entry) {
            cmsCore::addFlashMessage('error', 'Catalog entry не найден: ' . htmlspecialchars($slug));
            return $this->redirect(href_to($this->controller->root_url, 'catalog') . '?entry=' . urlencode($slug));
        }

        if ((string) ($entry['availability'] ?? '') !== 'free') {
            cmsCore::addFlashMessage('error', 'Этот entry пока не доступен для установки.');
            return $this->redirect(href_to($this->controller->root_url, 'catalog') . '?entry=' . urlencode($slug));
        }

        if (!$this->model->isEditorSupportedBlockType($slug)) {
            cmsCore::addFlashMessage('error', 'Для этого entry нет поддерживаемого editor flow.');
            return $this->redirect(href_to($this->controller->root_url, 'catalog') . '?entry=' . urlencode($slug));
        }

        $existing = $this->findExistingBlockByType($slug);
        if ($existing) {
            cmsUser::addSessionMessage('Блок уже установлен. Открываю существующий entry.', 'success');
            return $this->redirect(href_to($this->controller->root_url, 'block_edit', (int) $existing['id']));
        }

        $definition = $this->model->getEditorSupportedBlockDefinitions()[$slug] ?? null;
        if (!$definition) {
            cmsCore::addFlashMessage('error', 'Не найдено определение блока для ' . htmlspecialchars($slug));
            return $this->redirect(href_to($this->controller->root_url, 'catalog') . '?entry=' . urlencode($slug));
        }

        $title = trim((string) ($entry['title'] ?? $slug));
        $default_props = [];
        if (!empty($definition['schema']['fields'])) {
            foreach ($definition['schema']['fields'] as $field) {
                $key = $field['key'] ?? null;
                if ($key !== null) {
                    $default_props[$key] = $field['default'] ?? '';
                }
            }
        }

        $block_id = (int) $this->model->createBlock($slug, $title);
        if ($block_id <= 0) {
            cmsCore::addFlashMessage('error', 'Не удалось создать block entry из каталога.');
            return $this->redirect(href_to($this->controller->root_url, 'catalog') . '?entry=' . urlencode($slug));
        }

        if (NordicblocksBlockContractNormalizer::supportsContractType($slug)) {
            $contract = NordicblocksBlockContractNormalizer::normalize([
                'id'     => $block_id,
                'type'   => $slug,
                'title'  => $title,
                'status' => 'active',
                'props'  => $default_props,
            ]);
            $this->model->saveBlockContract($block_id, $title, $contract);
        } else {
            $this->model->saveBlock($block_id, $title, $default_props);
        }

        cmsUser::addSessionMessage('Блок установлен из каталога и готов к настройке.', 'success');
        return $this->redirect(href_to($this->controller->root_url, 'block_edit', $block_id));
    }

    private function findExistingBlockByType($type) {
        foreach ($this->model->getBlocks() as $block) {
            if ((string) ($block['type'] ?? '') !== (string) $type) {
                continue;
            }

            if (!$this->model->isEditorSupportedBlockType((string) ($block['type'] ?? ''))) {
                continue;
            }

            return $block;
        }

        return null;
    }
}