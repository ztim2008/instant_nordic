<?php

class actionNordicblocksBlockCssState extends cmsAction {

    public function run($block_id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
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

        echo json_encode([
            'ok' => true,
            'block' => [
                'id'   => (int) ($block['id'] ?? 0),
                'type' => (string) ($block['type'] ?? ''),
            ],
            'cssOverlay' => $this->model->getBlockCssOverlayState((int) ($block['id'] ?? 0), (string) ($block['type'] ?? '')),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}