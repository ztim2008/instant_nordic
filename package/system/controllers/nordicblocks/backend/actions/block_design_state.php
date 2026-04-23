<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockTypography.php';

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
        $template_name = (string) cmsConfig::get('template');
        $place_url = href_to('admin', 'widgets') . '?' . http_build_query([
            'template_name'               => $template_name,
            'open_tab'                    => 'all-widgets',
            'highlight_widget'            => 'nordicblocks_block',
            'highlight_widget_controller' => '',
            'nb_block_id'                 => (int) ($block['id'] ?? 0),
            'nb_block_title'              => (string) ($block['title'] ?? ''),
        ]);
        $elements = (array) ($contract['content']['section']['elements'] ?? []);
        $first_element_id = '';

        foreach ($elements as $element) {
            if (is_array($element) && !empty($element['id'])) {
                $first_element_id = (string) $element['id'];
                break;
            }
        }

        echo json_encode([
            'ok' => true,
            'block' => [
                'id'     => (int) ($block['id'] ?? 0),
                'title'  => (string) ($block['title'] ?? ''),
                'type'   => (string) ($block['type'] ?? ''),
                'status' => (string) ($block['status'] ?? 'active'),
                'editorMode' => 'design_canvas',
                'editorEngine' => 'design_block_canvas',
            ],
            'contract' => $contract,
            'summary' => [
                'elementCount' => count((array) ($contract['content']['section']['elements'] ?? [])),
                'stage' => $payload['stage'],
            ],
            'editor' => [
                'mode'      => 'design_canvas',
                'engine'    => 'design_block_canvas',
                'saveUrl'   => href_to($this->controller->root_url, 'block_save', [(int) ($block['id'] ?? 0)]),
                'canvasUrl' => href_to($this->controller->root_url, 'block_design_canvas', (int) ($block['id'] ?? 0)),
                'placeUrl'  => $place_url,
                'backUrl'   => href_to($this->controller->root_url, 'blocks'),
                'csrfToken' => cmsForm::getCSRFToken(),
            ],
            'palette' => [
                'version' => 1,
                'items' => [
                    ['type' => 'text', 'label' => 'Текст', 'description' => 'Заголовки, подписи и абзацы'],
                    ['type' => 'button', 'label' => 'Кнопка', 'description' => 'CTA и ссылки'],
                    ['type' => 'object', 'label' => 'Объект', 'description' => 'Прямоугольники, круги, линии и базовые shape-объекты'],
                    ['type' => 'photo', 'label' => 'Фото', 'description' => 'Фотографии, обложки и медиа-поверхности'],
                    ['type' => 'embed', 'label' => 'Вставка', 'description' => 'HTML код, iframe, карты, формы и внешние сервисы'],
                ],
            ],
            'pickers' => [
                'image' => 'instantcms_image_modal',
                'icon'  => 'instantcms_icon_modal',
                'file'  => 'instantcms_file_modal',
            ],
            'typography' => [
                'fontFamilies' => NordicblocksDesignBlockTypography::getFontFamilies(),
                'fontFaceCss' => NordicblocksDesignBlockTypography::buildCatalogFontFaceCss(),
            ],
            'ui' => [
                'activeBreakpoint' => 'desktop',
                'selectedElementId' => $first_element_id !== '' ? $first_element_id : null,
                'selectedElementIds' => [],
                'sidebarSection' => 'properties',
            ],
            'engineBoundary' => [
                'usesSharedInspectorRegistry' => false,
                'usesSharedInspectorPanels' => false,
                'usesSharedInspectorCapabilities' => false,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}