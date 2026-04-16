<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockEntityResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockCapabilityResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/InspectorStateBuilder.php';

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

        if ((string) ($block['type'] ?? '') !== 'hero') {
            echo json_encode(['ok' => false, 'error' => 'unsupported_block_type']);
            exit;
        }

        $contract = (array) ($block['contract'] ?? NordicblocksBlockContractNormalizer::normalize($block));
        $registry = NordicblocksInspectorRegistryBuilder::build();

        $resolved_entities = NordicblocksBlockEntityResolver::resolve((string) $block['type'], $contract, (array) $registry['entities']);
        $resolved_capabilities = NordicblocksBlockCapabilityResolver::resolve((string) $block['type'], (array) $registry['capabilityMatrix']);
        $ui_state = [
            'selectedEntity'      => 'title',
            'selectedRepeaterPath'=> null,
            'activeTab'           => 'content',
            'activeBreakpoint'    => 'desktop',
        ];
        $inspector = NordicblocksInspectorStateBuilder::build($registry, $resolved_entities, $resolved_capabilities, $ui_state);

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
                'entityGroups' => $registry['entityGroups'],
                'capabilities' => $registry['capabilities'],
                'controlPresets' => $registry['controlPresets'],
                'panels'       => $registry['panels'],
            ],
            'resolved' => [
                'entities'     => $resolved_entities,
                'capabilities' => $resolved_capabilities,
            ],
            'ui'        => $ui_state,
            'inspector' => $inspector,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}