<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockEntityResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockCapabilityResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php';

class actionNordicblocksBlockEditorState extends cmsAction {

    public function run($block_id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $block_id = (int) $block_id;
        $block    = $block_id ? $this->model->getBlockById($block_id) : null;

        if (!$block) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }

        $block['props'] = $this->model->normalizeImagePropsByType((string) ($block['type'] ?? ''), (array) ($block['props'] ?? []));

        if ((string) ($block['type'] ?? '') !== 'hero') {
            echo json_encode(['ok' => false, 'error' => 'unsupported_block_type']);
            exit;
        }

        $contract = NordicblocksBlockContractNormalizer::normalize($block);
        $registry = NordicblocksInspectorRegistryBuilder::build();

        $resolved_entities = NordicblocksBlockEntityResolver::resolve((string) $block['type'], $contract, (array) $registry['entities']);
        $resolved_capabilities = NordicblocksBlockCapabilityResolver::resolve((string) $block['type'], (array) $registry['capabilityMatrix']);

        echo json_encode([
            'ok' => true,
            'block' => [
                'id'    => (int) $block['id'],
                'type'  => (string) $block['type'],
                'title' => (string) $block['title'],
            ],
            'contract' => $contract,
            'registry' => [
                'tabs'         => $registry['tabs'],
                'entities'     => $registry['entities'],
                'capabilities' => $registry['capabilities'],
                'panels'       => $registry['panels'],
            ],
            'resolved' => [
                'entities'     => $resolved_entities,
                'capabilities' => $resolved_capabilities,
            ],
            'ui' => [
                'selectedEntity'      => 'title',
                'selectedRepeaterPath'=> null,
                'activeTab'           => 'content',
                'activeBreakpoint'    => 'desktop',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}