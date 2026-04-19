<?php

class actionNordicblocksBlockCssRestore extends cmsAction {

    public function run($block_id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->request->isMethod('POST')) {
            echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
            exit;
        }

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if ($csrf_token === '' && is_array($data)) {
            $csrf_token = (string) ($data['csrf_token'] ?? '');
        }

        if (!cmsForm::validateCSRFToken($csrf_token)) {
            echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
            exit;
        }

        $block_id = (int) $block_id;
        $block = $block_id ? $this->model->getBlockById($block_id) : null;
        if (!$block) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }

        if (!$this->model->supportsBlockCssOverlay((string) ($block['type'] ?? ''))) {
            echo json_encode(['ok' => false, 'error' => 'unsupported_block_type']);
            exit;
        }

        $expected_version = isset($data['version']) ? (int) $data['version'] : null;
        $revision_id = isset($data['revisionId']) ? (int) $data['revisionId'] : 0;

        $restore_result = $this->model->restoreBlockCssOverlayRevision(
            (int) ($block['id'] ?? 0),
            (string) ($block['type'] ?? ''),
            $revision_id,
            $expected_version,
            (int) ($this->cms_user->id ?? 0)
        );

        echo json_encode($restore_result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}