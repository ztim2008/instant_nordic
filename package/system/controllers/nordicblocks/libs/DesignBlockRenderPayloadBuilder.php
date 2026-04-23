<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php';

class NordicblocksDesignBlockRenderPayloadBuilder {

    public static function build(array $contract, array $context = []) {
        $block_id   = (int) ($context['blockId'] ?? 0);
        $block_uid  = trim((string) ($context['blockUid'] ?? ('block_' . $block_id)));
        $section_id = 'nb-design-' . preg_replace('/[^a-z0-9_\-]/', '-', strtolower($block_uid));
        $elements   = is_array($contract['content']['section']['elements'] ?? null) ? $contract['content']['section']['elements'] : [];
        $tree       = self::buildTree($elements);

        $payload = [
            'sectionId' => $section_id,
            'blockId'   => $block_id,
            'blockUid'  => $block_uid,
            'section'   => [
                'name'       => (string) ($contract['content']['section']['name'] ?? 'Design Block'),
                'tag'        => (string) ($contract['content']['section']['tag'] ?? 'section'),
                'background' => is_array($contract['design']['section']['background'] ?? null) ? $contract['design']['section']['background'] : [],
            ],
            'stage'     => [
                'desktop' => is_array($contract['layout']['stage']['desktop'] ?? null) ? $contract['layout']['stage']['desktop'] : [],
                'tablet'  => is_array($contract['layout']['stage']['tablet'] ?? null) ? $contract['layout']['stage']['tablet'] : [],
                'mobile'  => is_array($contract['layout']['stage']['mobile'] ?? null) ? $contract['layout']['stage']['mobile'] : [],
            ],
            'elements'     => $tree,
            'flatElements' => $elements,
        ];

        $payload['css'] = NordicblocksDesignBlockCssBuilder::build($payload);

        return $payload;
    }

    private static function buildTree(array $elements) {
        $indexed = [];

        foreach ($elements as $element) {
            if (!is_array($element) || empty($element['id'])) {
                continue;
            }

            $element['children'] = [];
            $indexed[(string) $element['id']] = $element;
        }

        $tree = [];

        foreach ($indexed as $id => $element) {
            $parent_id = (string) ($element['parentId'] ?? '');

            if ($parent_id !== '' && isset($indexed[$parent_id])) {
                $indexed[$parent_id]['children'][] = $element;
                continue;
            }

            $tree[] = $element;
        }

        return self::injectChildren($tree, $indexed);
    }

    private static function injectChildren(array $elements, array $indexed) {
        $result = [];

        foreach ($elements as $element) {
            $children = [];

            foreach ((array) ($indexed[(string) ($element['id'] ?? '')]['children'] ?? []) as $child) {
                $children[] = $child;
            }

            $element['children'] = self::injectChildren($children, $indexed);
            $result[] = $element;
        }

        return $result;
    }
}