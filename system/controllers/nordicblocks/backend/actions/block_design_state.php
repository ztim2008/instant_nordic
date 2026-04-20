<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php';

class actionNordicblocksBlockDesignState extends cmsAction {

    public function run($block_id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $block = $this->model->getBlockById((int) $block_id);
        if (!$block) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }

        if (!$this->model->isDesignBlockType((string) ($block['type'] ?? ''))) {
            echo json_encode(['ok' => false, 'error' => 'unsupported_block_type']);
            exit;
        }

        $contract = is_array($block['contract'] ?? null) ? $block['contract'] : NordicblocksDesignBlockContractNormalizer::normalize($block);
        $payload  = NordicblocksDesignBlockRenderPayloadBuilder::build($contract, [
            'blockId'  => (int) ($block['id'] ?? 0),
            'blockUid' => 'block_' . (int) ($block['id'] ?? 0),
        ]);

        echo json_encode([
            'ok' => true,
            'block' => [
                'id'     => (int) ($block['id'] ?? 0),
                'title'  => (string) ($block['title'] ?? ''),
                'type'   => (string) ($block['type'] ?? ''),
                'status' => (string) ($block['status'] ?? 'active'),
            ],
            'contract' => $contract,
            'summary' => [
                'elementCount' => count((array) ($contract['content']['section']['elements'] ?? [])),
                'stage' => $payload['stage'],
            ],
            'palette' => [
                'version' => 1,
                'items' => [
                    ['type' => 'text', 'label' => 'Text'],
                    ['type' => 'image', 'label' => 'Image'],
                    ['type' => 'button', 'label' => 'Button'],
                    ['type' => 'shape', 'label' => 'Shape'],
                    ['type' => 'icon', 'label' => 'Icon'],
                    ['type' => 'container', 'label' => 'Container'],
                    ['type' => 'video', 'label' => 'Video'],
                    ['type' => 'divider', 'label' => 'Divider'],
                    ['type' => 'svg', 'label' => 'SVG'],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}