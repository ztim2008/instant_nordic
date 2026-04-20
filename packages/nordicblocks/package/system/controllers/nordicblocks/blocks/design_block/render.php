<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockElementRenderer.php';

$design_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'design_block'))
    ? $block_contract
    : NordicblocksDesignBlockContractNormalizer::normalize([
        'id'     => isset($block['id']) ? (int) $block['id'] : 0,
        'type'   => 'design_block',
        'title'  => (string) ($block['title'] ?? 'Design Block'),
        'status' => (string) ($block['status'] ?? 'active'),
        'props'  => is_array($props ?? null) ? $props : [],
    ]);

$design_payload = NordicblocksDesignBlockRenderPayloadBuilder::build($design_contract, [
    'blockId'  => isset($block['id']) ? (int) $block['id'] : 0,
    'blockUid' => isset($block_uid) ? (string) $block_uid : ('block_' . (isset($block['id']) ? (int) $block['id'] : 0)),
]);

$section_tag   = in_array((string) ($design_payload['section']['tag'] ?? 'section'), ['section', 'div'], true) ? (string) $design_payload['section']['tag'] : 'section';
$section_id    = htmlspecialchars((string) ($design_payload['sectionId'] ?? 'nb-design-block'), ENT_QUOTES, 'UTF-8');
$section_name  = htmlspecialchars((string) ($design_payload['section']['name'] ?? 'Design Block'), ENT_QUOTES, 'UTF-8');
$section_style = htmlspecialchars(NordicblocksDesignBlockCssBuilder::buildSectionInlineStyle($design_payload), ENT_QUOTES, 'UTF-8');
$section_css   = (string) ($design_payload['css']['all'] ?? '');

echo '<' . $section_tag . ' id="' . $section_id . '" class="nb-design-block" data-nb-block="design_block" aria-label="' . $section_name . '" style="' . $section_style . '">';
echo '<style>' . $section_css . '</style>';
echo '<div class="nb-design-block__stage">';
echo NordicblocksDesignBlockElementRenderer::render($design_payload);
echo '</div>';
echo '</' . $section_tag . '>';