<?php

/**
 * Редактор блоков страницы.
 * Центр: стек блоков + кнопки управления.
 * Справа: панель добавления блока (библиотека) или инспектор полей.
 */
class actionNordicblocksEditor extends cmsAction {

    public function run($page_id = 0) {
        $page_id = (int) $page_id;
        $page    = $this->model->getPageById($page_id);

        if (!$page) {
            return cmsCore::error404();
        }

        $block_registry = $this->loadBlockRegistry();
        $tokens         = $this->model->getDesignTokens();
        $inline_css     = $this->model->buildInlineCss($tokens);

        return $this->cms_template->render('backend/editor', [
            'menu'           => $this->controller->getBackendMenu(),
            'page'           => $page,
            'block_registry' => $block_registry,
            'inline_css'     => $inline_css,
            'save_url'       => href_to($this->controller->root_url, 'editor_save', [$page_id]),
            'view_url'       => href_to('nordicblocks', $page['key']),
        ]);
    }

    /**
     * Сканирует /blocks/ и возвращает массив метаданных блоков.
     */
    private function loadBlockRegistry() {
        $blocks_dir = __DIR__ . '/../../blocks';
        $registry   = [];

        if (!is_dir($blocks_dir)) {
            return $registry;
        }

        foreach (scandir($blocks_dir) as $block_name) {
            if ($block_name[0] === '.') {
                continue;
            }
            $schema_file = "{$blocks_dir}/{$block_name}/schema.json";
            if (!file_exists($schema_file)) {
                continue;
            }
            $schema = json_decode(file_get_contents($schema_file), true);
            if (!is_array($schema)) {
                continue;
            }
            $preview = href_to('nordicblocks', 'block_preview', $block_name);
            $registry[$block_name] = [
                'name'    => $block_name,
                'title'   => $schema['title']    ?? $block_name,
                'category'=> $schema['category'] ?? 'content',
                'preview' => $preview,
                'schema'  => $schema,
            ];
        }

        return $registry;
    }
}
