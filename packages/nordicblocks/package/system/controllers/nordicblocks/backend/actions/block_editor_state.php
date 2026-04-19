<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockEntityResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockCapabilityResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/InspectorStateBuilder.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DataSourceResolver.php';

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

        if (!NordicblocksBlockContractNormalizer::supportsContractType((string) ($block['type'] ?? ''))) {
            echo json_encode(['ok' => false, 'error' => 'unsupported_block_type']);
            exit;
        }

        $contract = (array) ($block['contract'] ?? NordicblocksBlockContractNormalizer::normalize($block));
        $registry = NordicblocksInspectorRegistryBuilder::build((string) ($block['type'] ?? ''));

        $resolved_entities = NordicblocksBlockEntityResolver::resolve((string) $block['type'], $contract, (array) $registry['entities']);
        $resolved_capabilities = NordicblocksBlockCapabilityResolver::resolve((string) $block['type'], (array) $registry['capabilityMatrix']);
        $contract_meta = [
            'contractVersion' => (int) ($contract['meta']['contractVersion'] ?? 0),
            'schemaVersion'   => (int) ($contract['meta']['schemaVersion'] ?? 0),
            'blockType'       => (string) ($contract['meta']['blockType'] ?? ($block['type'] ?? '')),
            'rootKeys'        => array_keys($contract),
            'dataKeys'        => array_keys((array) ($contract['data'] ?? [])),
            'itemFieldAliases'=> ((string) ($block['type'] ?? '') === 'faq')
                ? [
                    'primary' => ['title', 'text'],
                    'legacy'  => ['question', 'answer'],
                ]
                : null,
        ];
        $default_entity = !empty($resolved_entities['title'])
            ? 'title'
            : (!empty($resolved_entities['items']) ? 'items' : (string) array_key_first($resolved_entities));
        $ui_state = [
            'selectedEntity'      => $default_entity,
            'selectedRepeaterPath'=> null,
            'activeTab'           => 'content',
            'activeBreakpoint'    => 'desktop',
        ];
        $inspector = NordicblocksInspectorStateBuilder::build($registry, $resolved_entities, $resolved_capabilities, $ui_state);
        $css_overlay = $this->buildCssOverlayMeta($block);

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
                'controls'     => $registry['controls'] ?? $registry['controlPresets'],
                'controlPresets' => $registry['controlPresets'],
                'panels'       => $registry['panels'],
                'manifest'     => $registry['manifest'] ?? null,
            ],
            'contractMeta' => $contract_meta,
            'dataOptions' => NordicblocksDataSourceResolver::buildEditorOptions((string) ($block['type'] ?? '')),
            'resolved' => [
                'entities'     => $resolved_entities,
                'capabilities' => $resolved_capabilities,
            ],
            'cssOverlay' => $css_overlay,
            'ui'        => $ui_state,
            'inspector' => $inspector,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function buildCssOverlayMeta(array $block) {
        $block_type = (string) ($block['type'] ?? '');

        if ($block_type !== 'hero_panels_wide') {
            return [
                'enabled' => false,
                'mode'    => 'disabled',
            ];
        }

        return [
            'enabled'        => true,
            'mode'           => 'session',
            'scopeSelector'  => '[data-nb-block-root="hero_panels_wide"]',
            'allowedTargets' => ['title', 'body', 'accentSurface', 'bodySurface'],
            'targets'        => [
                'title' => [
                    'label'       => 'Заголовок',
                    'selector'    => '[data-nb-entity="title"]',
                    'placeholder' => "font-size: clamp(3rem, 5vw, 4.5rem);\nletter-spacing: -0.04em;",
                    'example'     => "font-size: clamp(3rem, 5vw, 4.5rem);\nletter-spacing: -0.04em;\ntext-wrap: balance;",
                ],
                'body' => [
                    'label'       => 'Основной текст',
                    'selector'    => '[data-nb-entity="body"]',
                    'placeholder' => "font-size: 1.125rem;\nline-height: 1.8;",
                    'example'     => "font-size: 1.125rem;\nline-height: 1.8;\nmax-width: 34ch;",
                ],
                'accentSurface' => [
                    'label'       => 'Акцентная панель',
                    'selector'    => '[data-nb-entity="accentSurface"]',
                    'placeholder' => "background: linear-gradient(135deg, #ff5a36, #c81e1e);",
                    'example'     => "background: linear-gradient(135deg, #ff5a36, #c81e1e);\nbox-shadow: 0 24px 60px rgba(200, 30, 30, 0.24);",
                ],
                'bodySurface' => [
                    'label'       => 'Темная панель',
                    'selector'    => '[data-nb-entity="bodySurface"]',
                    'placeholder' => "background: #111827;\ncolor: #f8fafc;",
                    'example'     => "background: #111827;\ncolor: #f8fafc;\nborder-top: 1px solid rgba(255,255,255,0.12);",
                ],
            ],
        ];
    }
}