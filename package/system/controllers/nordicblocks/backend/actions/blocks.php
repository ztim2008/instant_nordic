<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/CatalogDraftRegistry.php';

class actionNordicblocksBlocks extends cmsAction {

    public function run() {
        $blocks                  = $this->model->getBlocks();
        $block_definitions       = $this->model->getEditorSupportedBlockDefinitions();
        $cache_stats             = $this->model->getCacheStats();
        $catalog_renderer_version = $this->model->getRenderCacheVersion('catalog_browser');
        $block_types             = [];
        $visible_blocks          = [];
        $hidden_legacy_count     = 0;
        $widgets_url             = href_to('admin', 'widgets');
        $template_name           = (string) cmsConfig::get('template');
        $catalog_entries         = $this->loadCreateCatalogEntries();
        $existing_blocks_by_type = [];
        $create_catalog_cards    = [];

        foreach ($block_definitions as $block_name => $definition) {
            $block_types[$block_name] = $definition['title'];
        }

        foreach ($blocks as $block) {
            $block_type = (string) ($block['type'] ?? '');

            if (!$this->model->isEditorSupportedBlockType($block_type)) {
                $hidden_legacy_count++;
                continue;
            }

            $block['editor_url'] = href_to($this->controller->root_url, 'block_edit', (int) $block['id']);
            $block['editor_mode'] = $this->model->getBlockEditorMode($block_type);
            $block['place_url']  = $widgets_url . '?' . http_build_query([
                'template_name'               => $template_name,
                'open_tab'                    => 'all-widgets',
                'highlight_widget'            => 'nordicblocks_block',
                'highlight_widget_controller' => '',
                'nb_block_id'                 => (int) ($block['id'] ?? 0),
                'nb_block_title'              => (string) ($block['title'] ?? ''),
            ]);
            $block['definition'] = $block_definitions[$block_type] ?? null;
            $visible_blocks[] = $block;

            if (!isset($existing_blocks_by_type[$block_type])) {
                $existing_blocks_by_type[$block_type] = [
                    'id'         => (int) ($block['id'] ?? 0),
                    'title'      => (string) ($block['title'] ?? ''),
                    'editor_url' => $block['editor_url'],
                ];
            }
        }

        foreach ($catalog_entries as $entry) {
            $slug = (string) ($entry['slug'] ?? '');

            if ($slug === '' || !$this->model->isEditorSupportedBlockType($slug)) {
                continue;
            }

            $definition = is_array($block_definitions[$slug] ?? null) ? $block_definitions[$slug] : [];
            $preview    = is_array($entry['preview'] ?? null) ? $entry['preview'] : [];
            $tags       = array_values(array_filter(array_map('trim', (array) ($entry['tags'] ?? []))));

            $create_catalog_cards[] = [
                'slug'           => $slug,
                'title'          => (string) ($entry['title'] ?? ($definition['title'] ?? $slug)),
                'subtitle'       => trim((string) ($entry['subtitle'] ?? '')),
                'summary'        => trim((string) ($entry['summary'] ?? ($definition['description'] ?? ''))),
                'category'       => trim((string) ($entry['category'] ?? ($definition['category'] ?? 'content'))),
                'preview_url'    => trim((string) ($preview['imageUrl'] ?? ($definition['preview'] ?? ''))),
                'preview_alt'    => trim((string) ($preview['alt'] ?? ($entry['title'] ?? $slug))),
                'availability'   => (string) ($entry['availability'] ?? 'free'),
                'tags'           => array_slice($tags, 0, 3),
                'existing_block' => $existing_blocks_by_type[$slug] ?? null,
            ];
        }

        return $this->cms_template->render('backend/blocks', [
            'menu'             => $this->controller->getBackendMenu(),
            'blocks'           => $visible_blocks,
            'block_types'      => $block_types,
            'create_catalog_cards' => $create_catalog_cards,
            'hidden_legacy_count' => $hidden_legacy_count,
            'cache_stats'      => $cache_stats,
            'catalog_renderer_version' => $catalog_renderer_version,
            'create_block_url' => href_to($this->controller->root_url, 'block_create'),
            'delete_block_url' => href_to($this->controller->root_url, 'block_delete'),
            'flush_cache_url'  => href_to($this->controller->root_url, 'flush_ssr_cache'),
            'widgets_url'      => $widgets_url,
        ]);
    }

    private function loadCreateCatalogEntries() {
        $root_path = rtrim((string) cmsConfig::get('root_path'), '/\\');
        $payload   = NordicblocksCatalogDraftRegistry::loadEntriesWithRaw($root_path);
        $entries   = is_array($payload['entries'] ?? null) ? $payload['entries'] : [];

        usort($entries, function ($left, $right) {
            $left_order  = (int) ($left['curation']['sortOrder'] ?? 9999);
            $right_order = (int) ($right['curation']['sortOrder'] ?? 9999);

            if ($left_order === $right_order) {
                return strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            }

            return $left_order <=> $right_order;
        });

        return $entries;
    }
}
