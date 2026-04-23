<?php

class actionNordicblocksFlushSsrCache extends cmsAction {

    public function run() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->request->isMethod('POST')) {
            echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
            exit;
        }

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrf_token)) {
            echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
            exit;
        }

        $before = $this->model->getCacheStats();
        $this->model->clearAllCache();
        $after = $this->model->getCacheStats();

        echo json_encode([
            'ok'              => true,
            'before'          => $before,
            'after'           => $after,
            'flushedAt'       => date('c'),
            'designVersion'   => (string) $this->model->getDesignCacheVersion(),
            'rendererVersion' => (string) $this->model->getRenderCacheVersion('catalog_browser'),
        ]);
        exit;
    }
}