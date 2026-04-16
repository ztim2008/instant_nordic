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
            'image_presets'  => $this->getImagePresetOptions(),
            'inline_css'     => $inline_css,
            'save_url'       => href_to($this->controller->root_url, 'editor_save', [$page_id]),
            'view_url'       => href_to('nordicblocks', $page['key']),
        ]);
    }

    /**
     * Сканирует /blocks/ и возвращает массив метаданных блоков.
     */
    private function loadBlockRegistry() {
        return $this->model->getBlockDefinitions();
    }

    private function getImagePresetOptions() {
        $presets = cmsCore::getModel('images')->getPresetsList(true);
        return ['original' => defined('LANG_PARSER_IMAGE_SIZE_ORIGINAL') ? LANG_PARSER_IMAGE_SIZE_ORIGINAL : 'Оригинал'] + $presets;
    }
}
