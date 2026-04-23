<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/CatalogDraftRegistry.php';

class actionNordicblocksCatalog extends cmsAction {

    public function run() {
        $root_path       = rtrim((string) cmsConfig::get('root_path'), '/\\');
        $entries_payload = NordicblocksCatalogDraftRegistry::loadEntriesWithRaw($root_path);
        $entries         = $entries_payload['entries'];
        $raw_json_map    = $entries_payload['raw'];

        usort($entries, function ($left, $right) {
            $left_order  = (int) (($left['curation']['sortOrder'] ?? 9999));
            $right_order = (int) (($right['curation']['sortOrder'] ?? 9999));

            if ($left_order === $right_order) {
                return strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
            }

            return $left_order <=> $right_order;
        });

        $selected_slug = (string) $this->request->get('entry', 'hero_panels_wide');
        $selected_entry = null;

        foreach ($entries as $entry) {
            if ((string) ($entry['slug'] ?? '') === $selected_slug) {
                $selected_entry = $entry;
                break;
            }
        }

        if (!$selected_entry && $entries) {
            $selected_entry = $entries[0];
            $selected_slug  = (string) ($selected_entry['slug'] ?? '');
        }

        $existing_blocks = $this->indexExistingBlocksByType();
        $cards           = [];

        foreach ($entries as $entry) {
            $slug           = (string) ($entry['slug'] ?? '');
            $existing_block = $existing_blocks[$slug] ?? null;
            $cards[]        = [
                'entry'         => $entry,
                'is_selected'   => $slug === $selected_slug,
                'existing_block'=> $existing_block,
                'select_url'    => href_to($this->controller->root_url, 'catalog') . '?entry=' . urlencode($slug),
            ];
        }

        $selected_block = null;

        if ($selected_entry) {
            $selected_block = $existing_blocks[(string) ($selected_entry['slug'] ?? '')] ?? null;
        }

        return $this->cms_template->render('backend/catalog', [
            'menu'                => $this->controller->getBackendMenu(),
            'cards'               => $cards,
            'selected_entry'      => $selected_entry,
            'selected_entry_json' => $selected_entry ? ($raw_json_map[(string) ($selected_entry['slug'] ?? '')] ?? '') : '',
            'selected_schema_json'=> $this->loadCatalogSchemaJson(),
            'selected_block'      => $selected_block,
            'schema_path'         => 'system/controllers/nordicblocks/catalog_drafts/CATALOG-ENTRY-SCHEMA-V1.json',
            'entry_path_prefix'   => 'system/controllers/nordicblocks/catalog_drafts/CATALOG-ENTRY-',
            'install_url'         => href_to($this->controller->root_url, 'catalog_install'),
            'csrf_token'          => cmsForm::getCSRFToken(),
            'back_url'            => href_to($this->controller->root_url, 'blocks'),
        ]);
    }

    private function indexExistingBlocksByType() {
        $blocks = $this->model->getBlocks();
        $index  = [];

        foreach ($blocks as $block) {
            $block_type = (string) ($block['type'] ?? '');

            if ($block_type === '' || isset($index[$block_type])) {
                continue;
            }

            if (!$this->model->isEditorSupportedBlockType($block_type)) {
                continue;
            }

            $index[$block_type] = [
                'id'         => (int) ($block['id'] ?? 0),
                'title'      => (string) ($block['title'] ?? ''),
                'editor_url' => href_to($this->controller->root_url, 'block_edit', (int) ($block['id'] ?? 0)),
            ];
        }

        return $index;
    }

    private function loadCatalogSchemaJson() {
        $root_path = rtrim((string) cmsConfig::get('root_path'), '/\\');
        return NordicblocksCatalogDraftRegistry::loadSchemaJson($root_path);
    }
}