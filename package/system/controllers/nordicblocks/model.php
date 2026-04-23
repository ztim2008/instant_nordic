<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockPayloadHydrator.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/RenderCacheContext.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/ManagedScaffoldRegistry.php';

class modelNordicblocks extends cmsModel {

    private static $first_wave_block_types = ['hero', 'faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'];

    const TBL_PAGES  = 'nordicblocks_pages';
    const TBL_BLOCKS = 'nordicblocks_blocks';
    const TBL_DESIGN = 'nordicblocks_design';
    const TBL_CACHE  = 'nordicblocks_cache';
    const TBL_BLOCK_CSS = 'nordicblocks_block_css';
    const TBL_BLOCK_CSS_REVISION = 'nordicblocks_block_css_revision';

    // ── СТРАНИЦЫ ──────────────────────────────────────────────────

    public function getPages() {
        return $this->db->getRows(self::TBL_PAGES, '1', '*', 'created_at DESC') ?: [];
    }

    public function getPageByKey($key) {
        $key = $this->db->escape(trim((string) $key));
        $row = $this->db->getRow(self::TBL_PAGES, "`key` = '{$key}'");
        if (!$row) { return null; }
        $row['blocks'] = !empty($row['blocks_json']) ? (array) json_decode($row['blocks_json'], true) : [];
        return $row;
    }

    public function getPageById($id) {
        $id  = (int) $id;
        $row = $this->db->getRow(self::TBL_PAGES, "`id` = {$id}");
        if (!$row) { return null; }
        $row['blocks'] = !empty($row['blocks_json']) ? (array) json_decode($row['blocks_json'], true) : [];
        return $row;
    }

    public function createPage($key, $title) {
        $now = date('Y-m-d H:i:s');
        return $this->db->insert(self::TBL_PAGES, [
            'key'         => trim((string) $key),
            'title'       => trim((string) $title),
            'status'      => 'draft',
            'blocks_json' => '[]',
            'created_at'  => $now,
            'updated_at'  => $now,
        ], true);
    }

    public function savePage($id, array $blocks) {
        $id = (int) $id;
        $this->db->update(self::TBL_PAGES, "`id` = {$id}", [
            'blocks_json' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
            'updated_at'  => date('Y-m-d H:i:s'),
        ], true);
        $this->invalidatePageCache($id);
    }

    public function setPageStatus($id, $status) {
        $allowed = ['draft', 'published'];
        $id      = (int) $id;
        $status  = in_array($status, $allowed, true) ? $status : 'draft';
        $this->db->update(self::TBL_PAGES, "`id` = {$id}", [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);
    }

    public function deletePage($id) {
        $id = (int) $id;
        $this->db->delete(self::TBL_PAGES, "`id` = {$id}");
        $this->invalidatePageCache($id);
    }

    public function pageKeyExists($key) {
        $key = $this->db->escape(trim((string) $key));
        return (bool) $this->db->getRow(self::TBL_PAGES, "`key` = '{$key}'", 'id');
    }

    // ── БЛОКИ (новая архитектура) ──────────────────────────────────

    public function getBlocks() {
        return $this->db->getRows(self::TBL_BLOCKS, '1', '*', 'created_at DESC') ?: [];
    }

    public function getBlockById($id) {
        $id  = (int) $id;
        $row = $this->db->getRow(self::TBL_BLOCKS, "`id` = {$id}");
        if (!$row) { return null; }

        $stored_payload = !empty($row['props_json']) ? (array) json_decode($row['props_json'], true) : [];
        $is_contract = NordicblocksBlockContractNormalizer::isContractPayload($stored_payload);

        if ($is_contract) {
            $row['contract'] = NordicblocksBlockContractNormalizer::normalize([
                'id'     => (int) ($row['id'] ?? 0),
                'type'   => (string) ($row['type'] ?? ''),
                'title'  => (string) ($row['title'] ?? ''),
                'status' => (string) ($row['status'] ?? 'active'),
                'props'  => $stored_payload,
            ]);
            $row['props'] = NordicblocksBlockContractNormalizer::denormalizeProps((string) ($row['type'] ?? ''), (array) $row['contract']);
        } else {
            $row['props'] = $stored_payload;
            $row['contract'] = NordicblocksBlockContractNormalizer::normalize([
                'id'     => (int) ($row['id'] ?? 0),
                'type'   => (string) ($row['type'] ?? ''),
                'title'  => (string) ($row['title'] ?? ''),
                'status' => (string) ($row['status'] ?? 'active'),
                'props'  => $stored_payload,
            ]);
        }

        $row['props'] = $this->normalizeImagePropsByType((string) ($row['type'] ?? ''), (array) ($row['props'] ?? []));
        $row['css_overlay'] = $this->getBlockCssOverlayState((int) ($row['id'] ?? 0), (string) ($row['type'] ?? ''));
        return $row;
    }

    public function hydrateBlockForRender(array $block, array $context = []) {
        $type = $this->normalizeBlockType((string) ($block['type'] ?? ''));
        if (!$type || !NordicblocksBlockContractNormalizer::supportsContractType($type)) {
            return $block;
        }

        $contract = isset($block['contract']) && is_array($block['contract'])
            ? $block['contract']
            : NordicblocksBlockContractNormalizer::normalize([
                'id'     => (int) ($block['id'] ?? 0),
                'type'   => $type,
                'title'  => (string) ($block['title'] ?? ''),
                'status' => (string) ($block['status'] ?? 'active'),
                'props'  => (array) ($block['props'] ?? []),
            ]);

        $block['contract'] = NordicblocksBlockPayloadHydrator::hydrate($contract, $context);
        $block['props'] = $this->normalizeImagePropsByType($type, (array) NordicblocksBlockContractNormalizer::denormalizeProps($type, (array) $block['contract']));
        $block['css_overlay'] = $this->getBlockCssOverlayState((int) ($block['id'] ?? 0), $type);

        return $block;
    }

    public function supportsBlockCssOverlay($type) {
        return $this->normalizeBlockType((string) $type) === 'hero_panels_wide';
    }

    public function hasBlockCssOverlayStorage() {
        return $this->db->isTableExists(self::TBL_BLOCK_CSS);
    }

    public function hasBlockCssOverlayPublishStorage() {
        return $this->hasBlockCssOverlayStorage()
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS, 'published_css_text')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS, 'published_version')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS, 'published_at')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS, 'published_by');
    }

    public function hasBlockCssOverlayRevisionStorage() {
        return $this->db->isTableExists(self::TBL_BLOCK_CSS_REVISION)
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS_REVISION, 'block_css_id')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS_REVISION, 'version')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS_REVISION, 'css_text')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS_REVISION, 'created_at')
            && $this->db->isFieldExists(self::TBL_BLOCK_CSS_REVISION, 'created_by');
    }

    public function buildBlockCssOverlayMeta($type, $scope_selector = null) {
        $config = $this->getBlockCssOverlayConfig($type);

        if (!$config) {
            return [
                'enabled' => false,
                'mode'    => 'disabled',
            ];
        }

        $scope_selector = trim((string) $scope_selector);
        if ($scope_selector === '') {
            $scope_selector = (string) ($config['scopeSelector'] ?? '');
        }

        return [
            'enabled'        => true,
            'mode'           => $this->hasBlockCssOverlayStorage() ? 'persistent' : 'session',
            'publishMode'    => $this->hasBlockCssOverlayPublishStorage() ? 'explicit' : 'live',
            'revisionsReady' => $this->hasBlockCssOverlayRevisionStorage(),
            'scopeSelector'  => $scope_selector,
            'allowedTargets' => array_values($config['allowedTargets']),
            'targets'        => $config['targets'],
            'presets'        => $this->normalizeBlockCssOverlayPresetList((string) $type, (array) ($config['presets'] ?? [])),
        ];
    }

    public function getBlockCssOverlayState($block_id, $block_type, $scope_selector = null) {
        $block_id = (int) $block_id;
        $meta = $this->buildBlockCssOverlayMeta($block_type, $scope_selector);
        $publish_ready = $this->hasBlockCssOverlayPublishStorage();

        if (empty($meta['enabled'])) {
            return $meta;
        }

        $target_css         = [];
        $version            = 0;
        $updated_at         = '';
        $updated_by         = 0;
        $published_target_css = [];
        $published_version  = 0;
        $published_at       = '';
        $published_by       = 0;

        if ($block_id > 0 && $this->hasBlockCssOverlayStorage()) {
            $row = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
            if ($row) {
                $target_css = $this->decodeBlockCssOverlayTargetCss((string) ($row['css_text'] ?? ''), (string) $block_type);
                $version    = max(0, (int) ($row['version'] ?? 0));
                $updated_at = (string) ($row['updated_at'] ?? '');
                $updated_by = max(0, (int) ($row['updated_by'] ?? 0));

                if ($publish_ready) {
                    $published_target_css = $this->decodeBlockCssOverlayTargetCss((string) ($row['published_css_text'] ?? ''), (string) $block_type);
                    $published_version    = max(0, (int) ($row['published_version'] ?? 0));
                    $published_at         = (string) ($row['published_at'] ?? '');
                    $published_by         = max(0, (int) ($row['published_by'] ?? 0));
                } else {
                    $published_target_css = $target_css;
                    $published_version    = $version;
                    $published_at         = $updated_at;
                    $published_by         = $updated_by;
                }
            }
        }

        $meta['targetCss']            = $target_css;
        $meta['cssText']              = $this->compileBlockCssOverlayCss((string) $block_type, $target_css, (string) ($meta['scopeSelector'] ?? ''));
        $meta['version']              = $version;
        $meta['updatedAt']            = $updated_at;
        $meta['updatedBy']            = $updated_by;
        $meta['persisted']            = ($version > 0);
        $meta['publishReady']         = $publish_ready;
        $meta['publishedTargetCss']   = $published_target_css;
        $meta['publishedCssText']     = $this->compileBlockCssOverlayCss((string) $block_type, $published_target_css, (string) ($meta['scopeSelector'] ?? ''));
        $meta['publishedVersion']     = $published_version;
        $meta['publishedAt']          = $published_at;
        $meta['publishedBy']          = $published_by;
        $meta['published']            = !empty($published_target_css);
        $meta['draftMatchesPublished']= $this->stableJsonEncode($target_css) === $this->stableJsonEncode($published_target_css);

        return $meta;
    }

    public function saveBlockCssOverlayState($block_id, $block_type, array $target_css, $expected_version = null, $updated_by = 0, $scope_selector = null) {
        $block_id    = (int) $block_id;
        $block_type  = $this->normalizeBlockType((string) $block_type);
        $updated_by  = (int) $updated_by;
        $publish_ready = $this->hasBlockCssOverlayPublishStorage();
        $revision_ready = $this->hasBlockCssOverlayRevisionStorage();

        if ($block_id < 1 || !$this->supportsBlockCssOverlay($block_type)) {
            return ['ok' => false, 'error' => 'unsupported_block_type'];
        }

        if (!$this->hasBlockCssOverlayStorage()) {
            return ['ok' => false, 'error' => 'schema_missing'];
        }

        $normalized_target_css = $this->normalizeBlockCssOverlayTargetCss($block_type, $target_css);
        $existing = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
        $current_version = $existing ? max(0, (int) ($existing['version'] ?? 0)) : 0;
        $stored_target_css = $existing ? $this->decodeBlockCssOverlayTargetCss((string) ($existing['css_text'] ?? ''), $block_type) : [];

        if ($expected_version !== null && (int) $expected_version !== $current_version) {
            return [
                'ok'         => false,
                'error'      => 'version_conflict',
                'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
            ];
        }

        if ($this->stableJsonEncode($stored_target_css) === $this->stableJsonEncode($normalized_target_css)) {
            return [
                'ok'         => true,
                'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
            ];
        }

        if (!$normalized_target_css) {
            if ($publish_ready && $existing) {
                $new_version = $current_version + 1;
                $this->db->update(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}", [
                    'css_text'   => '',
                    'version'    => $new_version,
                    'updated_by' => $updated_by > 0 ? $updated_by : null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], true);

                if ($revision_ready && !empty($existing['id'])) {
                    $this->insertBlockCssOverlayRevision((int) $existing['id'], $new_version, [], $updated_by);
                }
            } elseif ($existing) {
                $this->db->delete(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
                $this->invalidateBlockCache($block_id);
            }

            return [
                'ok'         => true,
                'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
            ];
        }

        $new_version = $current_version + 1;
        $payload = [
            'block_id'    => $block_id,
            'block_type'  => $block_type,
            'scope_type'  => 'block',
            'css_text'    => json_encode($normalized_target_css, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'version'     => $new_version,
            'updated_by'  => $updated_by > 0 ? $updated_by : null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        $block_css_id = $existing ? (int) ($existing['id'] ?? 0) : 0;

        if ($existing) {
            $this->db->update(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}", $payload, true);
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $insert_id = $this->db->insert(self::TBL_BLOCK_CSS, $payload, true);
            $block_css_id = $insert_id ? (int) $insert_id : 0;
        }

        if ($block_css_id < 1) {
            $saved_row = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
            $block_css_id = (int) ($saved_row['id'] ?? 0);
        }

        if ($revision_ready && $block_css_id > 0) {
            $this->insertBlockCssOverlayRevision($block_css_id, $new_version, $normalized_target_css, $updated_by);
        }

        if (!$publish_ready) {
            $this->invalidateBlockCache($block_id);
        }

        return [
            'ok'         => true,
            'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
        ];
    }

    public function listBlockCssOverlayRevisions($block_id, $block_type, $limit = 12) {
        $block_id   = (int) $block_id;
        $block_type = $this->normalizeBlockType((string) $block_type);
        $limit      = max(1, min(30, (int) $limit));

        if ($block_id < 1 || !$this->supportsBlockCssOverlay($block_type) || !$this->hasBlockCssOverlayRevisionStorage()) {
            return [];
        }

        $row = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
        $block_css_id = (int) ($row['id'] ?? 0);
        if ($block_css_id < 1) {
            return [];
        }

        $result = $this->db->query(
            "SELECT `id`, `version`, `css_text`, `created_by`, `created_at`
             FROM `{#}" . self::TBL_BLOCK_CSS_REVISION . "`
             WHERE `block_css_id` = '%s'
             ORDER BY `version` DESC, `id` DESC
             LIMIT {$limit}",
            [(string) $block_css_id],
            true
        );

        if (!$result || $result->num_rows < 1) {
            return [];
        }

        $revisions = [];

        while ($revision = $result->fetch_assoc()) {
            $target_css = $this->decodeBlockCssOverlayTargetCss((string) ($revision['css_text'] ?? ''), $block_type);

            $revisions[] = [
                'id'         => (int) ($revision['id'] ?? 0),
                'version'    => max(0, (int) ($revision['version'] ?? 0)),
                'targetCss'  => $target_css,
                'targetKeys' => array_values(array_keys($target_css)),
                'createdBy'  => max(0, (int) ($revision['created_by'] ?? 0)),
                'createdAt'  => (string) ($revision['created_at'] ?? ''),
            ];
        }

        return $revisions;
    }

    public function restoreBlockCssOverlayRevision($block_id, $block_type, $revision_id, $expected_version = null, $updated_by = 0, $scope_selector = null) {
        $block_id    = (int) $block_id;
        $block_type  = $this->normalizeBlockType((string) $block_type);
        $revision_id = (int) $revision_id;
        $updated_by  = (int) $updated_by;

        if ($block_id < 1 || $revision_id < 1 || !$this->supportsBlockCssOverlay($block_type)) {
            return ['ok' => false, 'error' => 'unsupported_block_type'];
        }

        if (!$this->hasBlockCssOverlayRevisionStorage()) {
            return ['ok' => false, 'error' => 'revisions_schema_missing'];
        }

        $row = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
        $block_css_id = (int) ($row['id'] ?? 0);
        if ($block_css_id < 1) {
            return ['ok' => false, 'error' => 'css_overlay_missing'];
        }

        $result = $this->db->query(
            "SELECT `id`, `css_text`
             FROM `{#}" . self::TBL_BLOCK_CSS_REVISION . "`
             WHERE `id` = '%s' AND `block_css_id` = '%s'
             LIMIT 1",
            [(string) $revision_id, (string) $block_css_id],
            true
        );

        if (!$result || $result->num_rows < 1) {
            return ['ok' => false, 'error' => 'revision_not_found'];
        }

        $revision = $result->fetch_assoc();
        $target_css = $this->decodeBlockCssOverlayTargetCss((string) ($revision['css_text'] ?? ''), $block_type);

        return $this->saveBlockCssOverlayState(
            $block_id,
            $block_type,
            $target_css,
            $expected_version,
            $updated_by,
            $scope_selector
        );
    }

    public function publishBlockCssOverlayState($block_id, $block_type, $expected_version = null, $updated_by = 0, $scope_selector = null) {
        $block_id      = (int) $block_id;
        $block_type    = $this->normalizeBlockType((string) $block_type);
        $updated_by    = (int) $updated_by;

        if ($block_id < 1 || !$this->supportsBlockCssOverlay($block_type)) {
            return ['ok' => false, 'error' => 'unsupported_block_type'];
        }

        if (!$this->hasBlockCssOverlayPublishStorage()) {
            return ['ok' => false, 'error' => 'publish_schema_missing'];
        }

        $existing = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
        $current_version = $existing ? max(0, (int) ($existing['version'] ?? 0)) : 0;

        if ($expected_version !== null && (int) $expected_version !== $current_version) {
            return [
                'ok'         => false,
                'error'      => 'version_conflict',
                'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
            ];
        }

        if (!$existing) {
            return [
                'ok'         => true,
                'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
            ];
        }

        $draft_target_css = $this->decodeBlockCssOverlayTargetCss((string) ($existing['css_text'] ?? ''), $block_type);
        $published_target_css = $this->decodeBlockCssOverlayTargetCss((string) ($existing['published_css_text'] ?? ''), $block_type);

        if ($this->stableJsonEncode($draft_target_css) === $this->stableJsonEncode($published_target_css)) {
            return [
                'ok'         => true,
                'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
            ];
        }

        $this->db->update(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}", [
            'published_css_text' => $draft_target_css ? json_encode($draft_target_css, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
            'published_version'  => max(0, (int) ($existing['published_version'] ?? 0)) + 1,
            'published_by'       => $updated_by > 0 ? $updated_by : null,
            'published_at'       => date('Y-m-d H:i:s'),
        ], true);

        $this->invalidateBlockCache($block_id);

        return [
            'ok'         => true,
            'cssOverlay' => $this->getBlockCssOverlayState($block_id, $block_type, $scope_selector),
        ];
    }

    public function deleteBlockCssOverlay($block_id) {
        $block_id = (int) $block_id;
        if ($block_id < 1 || !$this->hasBlockCssOverlayStorage()) {
            return;
        }

        $row = $this->db->getRow(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
        $block_css_id = (int) ($row['id'] ?? 0);

        if ($block_css_id > 0 && $this->hasBlockCssOverlayRevisionStorage()) {
            $this->db->delete(self::TBL_BLOCK_CSS_REVISION, "`block_css_id` = {$block_css_id}");
        }

        $this->db->delete(self::TBL_BLOCK_CSS, "`block_id` = {$block_id}");
    }

    private function insertBlockCssOverlayRevision($block_css_id, $version, array $target_css, $created_by = 0) {
        $block_css_id = (int) $block_css_id;
        $version      = (int) $version;
        $created_by   = (int) $created_by;

        if ($block_css_id < 1 || $version < 1 || !$this->hasBlockCssOverlayRevisionStorage()) {
            return 0;
        }

        $existing = $this->db->getRow(self::TBL_BLOCK_CSS_REVISION, "`block_css_id` = {$block_css_id} AND `version` = {$version}");
        if ($existing) {
            return (int) ($existing['id'] ?? 0);
        }

        return (int) $this->db->insert(self::TBL_BLOCK_CSS_REVISION, [
            'block_css_id' => $block_css_id,
            'version'      => $version,
            'css_text'     => $target_css ? json_encode($target_css, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
            'created_by'   => $created_by > 0 ? $created_by : null,
            'created_at'   => date('Y-m-d H:i:s'),
        ], true);
    }

    public function buildBlockCssOverlayRuntimeCss(array $block, $block_uid) {
        $block_type = $this->normalizeBlockType((string) ($block['type'] ?? ''));
        if (!$this->supportsBlockCssOverlay($block_type)) {
            return '';
        }

        $target_css = is_array($block['css_overlay']['publishedTargetCss'] ?? null)
            ? $block['css_overlay']['publishedTargetCss']
            : [];

        if (!$target_css && !empty($block['id'])) {
            $state = $this->getBlockCssOverlayState((int) $block['id'], $block_type);
            $target_css = is_array($state['publishedTargetCss'] ?? null) ? $state['publishedTargetCss'] : [];
        }

        if (!$target_css) {
            return '';
        }

        $safe_uid = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $block_uid);
        if ($safe_uid === '') {
            return '';
        }

        return $this->compileBlockCssOverlayCss($block_type, $target_css, '#block-' . $safe_uid);
    }

    public function buildBlockCssOverlayEditorCss(array $block, $block_uid) {
        $block_type = $this->normalizeBlockType((string) ($block['type'] ?? ''));
        if (!$this->supportsBlockCssOverlay($block_type)) {
            return '';
        }

        $target_css = is_array($block['css_overlay']['targetCss'] ?? null)
            ? $block['css_overlay']['targetCss']
            : [];

        if (!$target_css && !empty($block['id'])) {
            $state = $this->getBlockCssOverlayState((int) $block['id'], $block_type);
            $target_css = is_array($state['targetCss'] ?? null) ? $state['targetCss'] : [];
        }

        if (!$target_css) {
            return '';
        }

        $safe_uid = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $block_uid);
        if ($safe_uid === '') {
            return '';
        }

        return $this->compileBlockCssOverlayCss($block_type, $target_css, '#block-' . $safe_uid);
    }

    public function getDesignCacheVersion() {
        $row = $this->db->getRow(self::TBL_DESIGN, '1', 'updated_at, tokens_json', 'id ASC');
        if (!$row) {
            return 'default';
        }

        $updated_at = trim((string) ($row['updated_at'] ?? ''));
        if ($updated_at !== '') {
            return $updated_at;
        }

        return substr(md5((string) ($row['tokens_json'] ?? '')), 0, 16);
    }

    public function buildRenderCacheProfile(array $block, array $context = []) {
        $surface = trim((string) ($context['surface'] ?? $context['mode'] ?? 'runtime'));
        if ($surface === '') {
            $surface = 'runtime';
        }

        $adapter_context = NordicblocksRenderCacheContext::build($block, $context);
        $design_version = trim((string) ($context['design_version'] ?? ''));
        if ($design_version === '') {
            $design_version = $this->getDesignCacheVersion();
        }
        $renderer_version = $this->getRenderCacheVersion((string) ($block['type'] ?? ''));

        $namespace = $this->buildRenderCacheNamespace($block, $surface, $context);
        $payload = [
            'surface'        => $surface,
            'blockFingerprint'=> $this->buildRenderBlockFingerprint($block),
            'designVersion'  => $design_version,
            'rendererVersion'=> $renderer_version,
            'adapterHash'    => (string) ($adapter_context['hash'] ?? 'manual'),
        ];

        return [
            'surface'        => $surface,
            'namespace'      => $namespace,
            'cacheKey'       => $namespace . '_' . substr(md5($this->stableJsonEncode($payload)), 0, 16),
            'cacheEligible'  => $surface !== 'backend_canvas' && !empty($namespace) && !empty($adapter_context['cacheEligible']),
            'designVersion'  => $design_version,
            'adapterContext' => $adapter_context,
        ];
    }

    public function getBlockDefinition($type) {
        $type = $this->normalizeBlockType($type);
        if (!$type) { return null; }

        $blocks_dir = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks';
        $block_dir  = $blocks_dir . '/' . $type;
        if (!is_dir($block_dir)) { return null; }

        $schema_file = $block_dir . '/schema.json';
        if (!file_exists($schema_file)) { return null; }

        $schema = json_decode((string) file_get_contents($schema_file), true);
        if (!is_array($schema)) { return null; }

        $meta_file = $block_dir . '/meta.json';
        $meta      = file_exists($meta_file)
            ? json_decode((string) file_get_contents($meta_file), true)
            : [];

        if (!is_array($meta)) {
            $meta = [];
        }

        $title       = (string) ($meta['name'] ?? $schema['title'] ?? $type);
        $category    = (string) ($meta['category'] ?? $schema['category'] ?? 'content');
        $description = (string) ($meta['description'] ?? $schema['description'] ?? '');
        $preview     = '';

        foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $preview_ext) {
            if (file_exists($block_dir . '/preview.' . $preview_ext)) {
                $preview = href_to('nordicblocks', 'block_preview', $type);
                break;
            }
        }

        return [
            'name'        => $type,
            'title'       => $title,
            'category'    => $category,
            'description' => $description,
            'preview'     => $preview,
            'meta'        => $meta,
            'schema'      => [
                'title'       => $title,
                'category'    => $category,
                'description' => $description,
                'fields'      => $this->normalizeBlockSchemaFields($schema, $type),
            ],
        ];
    }

    public function getBlockDefinitions() {
        $blocks_dir  = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks';
        $definitions = [];

        if (!is_dir($blocks_dir)) {
            return $definitions;
        }

        foreach (scandir($blocks_dir) as $block_name) {
            if ($block_name[0] === '.') {
                continue;
            }

            $definition = $this->getBlockDefinition($block_name);
            if ($definition) {
                $definitions[$block_name] = $definition;
            }
        }

        ksort($definitions);

        return $definitions;
    }

    public function getFirstWaveBlockDefinitions() {
        $definitions = [];

        foreach ($this->getBlockDefinitions() as $block_name => $definition) {
            if (!$this->isFirstWaveBlockType($block_name)) {
                continue;
            }

            $definitions[$block_name] = $definition;
        }

        return $definitions;
    }

    public function getEditorSupportedBlockDefinitions() {
        $definitions = [];

        foreach ($this->getBlockDefinitions() as $block_name => $definition) {
            if (!$this->isEditorSupportedBlockType($block_name)) {
                continue;
            }

            $definitions[$block_name] = $definition;
        }

        return $definitions;
    }

    public function getFirstWaveBlockTypes() {
        return array_values(array_unique(array_merge(self::$first_wave_block_types, NordicblocksManagedScaffoldRegistry::getManagedTypes())));
    }

    public function isFirstWaveBlockType($type) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));
        return in_array($type, self::$first_wave_block_types, true) || NordicblocksManagedScaffoldRegistry::isManagedType($type);
    }

    public function isDesignBlockType($type) {
        return $this->normalizeBlockType((string) $type) === 'design_block';
    }

    public function getBlockEditorMode($type) {
        return $this->isDesignBlockType((string) $type) ? 'design_canvas' : 'inspector_shell';
    }

    public function isEditorSupportedBlockType($type) {
        return $this->isFirstWaveBlockType($type) || $this->isDesignBlockType($type);
    }

    public function createBlock($type, $title) {
        $now = date('Y-m-d H:i:s');
        return $this->db->insert(self::TBL_BLOCKS, [
            'type'       => preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim((string) $type))),
            'title'      => trim((string) $title),
            'props_json' => '{}',
            'status'     => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ], true);
    }

    public function saveBlock($id, $title, array $props) {
        $id   = (int) $id;
        $data = [
            'title'      => trim((string) $title),
            'props_json' => json_encode($props, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->update(self::TBL_BLOCKS, "`id` = {$id}", $data, true);
        $this->syncBlockWidgetBindingTitles($id, (string) $data['title']);
        $this->invalidateBlockCache($id);
    }

    public function saveBlockContract($id, $title, array $contract) {
        $id   = (int) $id;
        $data = [
            'title'      => trim((string) $title),
            'props_json' => json_encode($contract, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->update(self::TBL_BLOCKS, "`id` = {$id}", $data, true);
        $this->syncBlockWidgetBindingTitles($id, (string) $data['title']);
        $this->invalidateBlockCache($id);
    }

    public function deleteBlock($id) {
        $id = (int) $id;
        $removed_widget_binds = $this->deleteBlockWidgetBindings($id);
        $this->deleteBlockCssOverlay($id);
        $this->db->delete(self::TBL_BLOCKS, "`id` = {$id}");
        $this->invalidateBlockCache($id);

        return [
            'removed_widget_binds' => $removed_widget_binds,
        ];
    }

    private function syncBlockWidgetBindingTitles($block_id, $title) {
        $block_id = (int) $block_id;
        $title    = trim((string) $title);

        if ($block_id < 1 || $title === '') {
            return 0;
        }

        $bind_ids = $this->findBlockWidgetBindingIds($block_id);
        if (!$bind_ids) {
            return 0;
        }

        foreach ($bind_ids as $bind_id) {
            $this->db->update('widgets_bind', "`id` = {$bind_id}", ['title' => $title], true);
        }

        cmsCache::getInstance()->clean('widgets.bind');

        return count($bind_ids);
    }

    private function deleteBlockWidgetBindings($block_id) {
        $bind_ids = $this->findBlockWidgetBindingIds((int) $block_id);
        if (!$bind_ids) {
            return 0;
        }

        $bind_ids_sql = implode(',', array_map('intval', $bind_ids));

        $this->db->query("DELETE FROM `{#}widgets_bind_pages` WHERE `bind_id` IN ({$bind_ids_sql})");
        $this->db->query("DELETE FROM `{#}widgets_bind` WHERE `id` IN ({$bind_ids_sql})");

        $cache = cmsCache::getInstance();
        $cache->clean('widgets.bind_pages');
        $cache->clean('widgets.bind');
        $cache->clean('widgets.pages');

        return count($bind_ids);
    }

    private function findBlockWidgetBindingIds($block_id) {
        $block_id = (int) $block_id;
        if ($block_id < 1) {
            return [];
        }

        $result = $this->db->query(
            "SELECT wb.`id`, wb.`options`
             FROM `{#}widgets_bind` wb
             INNER JOIN `{#}widgets` w ON w.id = wb.widget_id
             WHERE w.`name` = 'nordicblocks_block' AND (w.`controller` IS NULL OR w.`controller` = '')",
            [],
            true
        );

        if (!$result || $result->num_rows < 1) {
            return [];
        }

        $bind_ids = [];

        while ($row = $result->fetch_assoc()) {
            $options = cmsModel::yamlToArray((string) ($row['options'] ?? ''));
            if ((int) ($options['block_id'] ?? 0) !== $block_id) {
                continue;
            }

            $bind_ids[] = (int) $row['id'];
        }

        return array_values(array_unique($bind_ids));
    }

    public function invalidateBlockCache($block_id) {
        $id = (int) $block_id;
        $this->db->query(
            "DELETE FROM `{#}" . self::TBL_CACHE . "` WHERE `cache_key` LIKE 'block\\_{$id}\\_%'"
        );
    }

    // ── ДИЗАЙН-СИСТЕМА ────────────────────────────────────────────

    public function getDesignTokens() {
        $row = $this->db->getRow(self::TBL_DESIGN, '1', 'tokens_json', 'id ASC');
        if (!$row || empty($row['tokens_json'])) {
            return $this->getDefaultTokens();
        }
        $tokens = json_decode($row['tokens_json'], true);
        return is_array($tokens) ? $this->normalizeDesignTokens($tokens) : $this->getDefaultTokens();
    }

    public function saveDesignTokens(array $tokens) {
        $now  = date('Y-m-d H:i:s');
        $json = json_encode($this->normalizeDesignTokens($tokens), JSON_UNESCAPED_UNICODE);
        $has  = $this->db->getRow(self::TBL_DESIGN, '1', 'id');

        if ($has) {
            $this->db->update(self::TBL_DESIGN, "`id` = {$has['id']}", [
                'tokens_json' => $json,
                'updated_at'  => $now,
            ], true);
        } else {
            $this->db->insert(self::TBL_DESIGN, [
                'tokens_json' => $json,
                'updated_at'  => $now,
            ], true);
        }
        $this->db->query("DELETE FROM `{#}" . self::TBL_CACHE . "`");
    }

    public function getDefaultTokens() {
        return [
            'version'    => 2,
            'colors'     => [
                'accent'               => '#b42318',
                'bg'                   => '#ffffff',
                'bg_alt'               => '#f7f7f6',
                'surface'              => '#ffffff',
                'border'               => '#e5e7eb',
                'text'                 => '#1a1a1a',
                'text_muted'           => '#6b7280',
                'button_primary_bg'    => '#b42318',
                'button_primary_bg_hover' => '#8a1910',
                'button_primary_text'  => '#ffffff',
                'button_primary_border'=> '#b42318',
                'button_outline_text'  => '#b42318',
                'button_outline_border'=> '#b42318',
                'button_ghost_text'    => '#1a1a1a',
                'button_ghost_border'  => '#e5e7eb',
            ],
            'typography' => [
                'font_body'   => 'sans',
                'font_head'   => 'sans',
                'font_button' => '',
            ],
            'layout'     => [
                'section_spacing' => 'comfortable',
            ],
            'radii'      => [
                'base'   => 'md',
                'card'   => 'lg',
                'button' => 'md',
                'media'  => 'lg',
            ],
            'buttons'    => [
                'style'           => 'primary',
                'size'            => 'md',
                'hover_animation' => 'lift',
                'glint_color'     => '#ffffff',
                'glint_duration'  => 900,
            ],
            'cards'      => [
                'border_width'   => 1,
                'shadow_preset'  => 'md',
                'surface_motion' => true,
            ],
        ];
    }

    public function getThemePresets() {
        return [
            'nordic-light' => [
                'name'             => 'Nordic Light',
                'color_accent'     => '#b42318',
                'color_bg'         => '#ffffff',
                'color_bg_alt'     => '#f7f7f6',
                'color_surface'    => '#ffffff',
                'color_border'     => '#e5e7eb',
                'color_text'       => '#111827',
                'color_text_muted' => '#6b7280',
                'button_primary_bg_hover' => '#8a1910',
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'md',
                'card_radius_preset' => 'lg',
                'button_radius_preset' => 'md',
                'media_radius_preset' => 'lg',
                'shadow_preset'    => 'md',
                'section_spacing'  => 'comfortable',
                'btn_style'        => 'primary',
                'btn_size'         => 'md',
                'btn_hover_animation' => 'lift',
            ],
            'nordic-dark' => [
                'name'             => 'Nordic Dark',
                'color_accent'     => '#e05c4e',
                'color_bg'         => '#0f1117',
                'color_bg_alt'     => '#1a1d24',
                'color_surface'    => '#1e2230',
                'color_border'     => '#2d3244',
                'color_text'       => '#f0f2f5',
                'color_text_muted' => '#8b95a7',
                'button_primary_bg_hover' => '#c94e41',
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'md',
                'card_radius_preset' => 'lg',
                'button_radius_preset' => 'md',
                'media_radius_preset' => 'lg',
                'shadow_preset'    => 'lg',
                'section_spacing'  => 'comfortable',
                'btn_style'        => 'primary',
                'btn_size'         => 'md',
                'btn_hover_animation' => 'glow',
            ],
            'warm-minimal' => [
                'name'             => 'Warm Minimal',
                'color_accent'     => '#c2622a',
                'color_bg'         => '#faf9f7',
                'color_bg_alt'     => '#f0ede8',
                'color_surface'    => '#ffffff',
                'color_border'     => '#e3ddd6',
                'color_text'       => '#1c1610',
                'color_text_muted' => '#7a6e64',
                'button_primary_bg_hover' => '#a95022',
                'font_body'        => 'sans',
                'font_head'        => 'serif',
                'radius_preset'    => 'sm',
                'card_radius_preset' => 'md',
                'button_radius_preset' => 'pill',
                'media_radius_preset' => 'md',
                'shadow_preset'    => 'sm',
                'section_spacing'  => 'spacious',
                'btn_style'        => 'outline',
                'btn_size'         => 'md',
                'btn_hover_animation' => 'lift',
            ],
            'corporate' => [
                'name'             => 'Corporate',
                'color_accent'     => '#1d4ed8',
                'color_bg'         => '#ffffff',
                'color_bg_alt'     => '#f8faff',
                'color_surface'    => '#ffffff',
                'color_border'     => '#dde4f0',
                'color_text'       => '#0f172a',
                'color_text_muted' => '#64748b',
                'button_primary_bg_hover' => '#1b43b8',
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'sm',
                'card_radius_preset' => 'md',
                'button_radius_preset' => 'sm',
                'media_radius_preset' => 'md',
                'shadow_preset'    => 'sm',
                'section_spacing'  => 'comfortable',
                'btn_style'        => 'primary',
                'btn_size'         => 'sm',
                'btn_hover_animation' => 'grow',
            ],
            'creative' => [
                'name'             => 'Creative',
                'color_accent'     => '#7c3aed',
                'color_bg'         => '#fdfcff',
                'color_bg_alt'     => '#f3f0ff',
                'color_surface'    => '#ffffff',
                'color_border'     => '#e0d9f8',
                'color_text'       => '#1e1b2e',
                'color_text_muted' => '#6b6485',
                'button_primary_bg_hover' => '#692ed1',
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'xl',
                'card_radius_preset' => 'xl',
                'button_radius_preset' => 'pill',
                'media_radius_preset' => 'xl',
                'shadow_preset'    => 'md',
                'section_spacing'  => 'spacious',
                'btn_style'        => 'primary',
                'btn_size'         => 'lg',
                'btn_hover_animation' => 'glint',
            ],
        ];
    }

    public function buildInlineCss(array $tokens) {
        $tokens = $this->normalizeDesignTokens($tokens);

        $radius_map = [
            'none' => '0px',  'sm' => '4px',  'md' => '8px',
            'lg'   => '16px', 'xl' => '24px', 'pill' => '9999px',
        ];
        $shadow_map = [
            'none' => 'none',
            'sm'   => '0 1px 2px 0 rgb(0 0 0 / .06)',
            'md'   => '0 4px 12px 0 rgb(0 0 0 / .08)',
            'lg'   => '0 8px 32px 0 rgb(0 0 0 / .12)',
        ];
        $section_py_map = [
            'compact'     => 'clamp(1.5rem, 1rem + 2.5vw, 3rem)',
            'comfortable' => 'clamp(3rem, 2rem + 5vw, 6rem)',
            'spacious'    => 'clamp(4rem, 2.5rem + 7.5vw, 9rem)',
        ];
        $button_size_map = [
            'sm' => ['font' => '0.9375rem', 'pad_y' => '0.55rem', 'pad_x' => '1.1rem'],
            'md' => ['font' => '1rem',      'pad_y' => '0.7rem',  'pad_x' => '1.5rem'],
            'lg' => ['font' => '1.125rem',  'pad_y' => '0.9rem',  'pad_x' => '1.9rem'],
        ];
        $button_animation_map = [
            'none'  => ['transform' => 'none', 'shadow' => 'none', 'glint' => '0'],
            'lift'  => ['transform' => 'translateY(-2px)', 'shadow' => $shadow_map['lg'], 'glint' => '0'],
            'grow'  => ['transform' => 'scale(1.03)', 'shadow' => $shadow_map['md'], 'glint' => '0'],
            'glow'  => ['transform' => 'none', 'shadow' => '0 0 0 4px ' . $this->hexToRgba($this->getTokenPath($tokens, ['colors', 'accent'], '#b42318'), 0.16), 'glint' => '0'],
            'glint' => ['transform' => 'none', 'shadow' => $shadow_map['md'], 'glint' => '1'],
        ];

        $accent    = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'accent'], '#b42318'));
        $bg        = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'bg'], '#ffffff'), '#ffffff');
        $bgAlt     = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'bg_alt'], '#f7f7f6'), '#f7f7f6');
        $surface   = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'surface'], '#ffffff'), '#ffffff');
        $border    = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'border'], '#e5e7eb'), '#e5e7eb');
        $text      = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'text'], '#1a1a1a'), '#1a1a1a');
        $muted     = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'text_muted'], '#6b7280'), '#6b7280');

        $base_radius   = $radius_map[$this->getTokenPath($tokens, ['radii', 'base'], 'md')]   ?? '8px';
        $card_radius   = $radius_map[$this->getTokenPath($tokens, ['radii', 'card'], 'lg')]   ?? '16px';
        $button_radius = $radius_map[$this->getTokenPath($tokens, ['radii', 'button'], 'md')] ?? '8px';
        $media_radius  = $radius_map[$this->getTokenPath($tokens, ['radii', 'media'], 'lg')]  ?? '16px';
        $card_shadow   = $shadow_map[$this->getTokenPath($tokens, ['cards', 'shadow_preset'], 'md')] ?? $shadow_map['md'];
        $section_py    = $section_py_map[$this->getTokenPath($tokens, ['layout', 'section_spacing'], 'comfortable')] ?? $section_py_map['comfortable'];

        $font_body   = $this->resolveFontFamilyCss($this->getTokenPath($tokens, ['typography', 'font_body'], 'sans'));
        $font_head   = $this->resolveFontFamilyCss($this->getTokenPath($tokens, ['typography', 'font_head'], 'sans'));
        $font_button = $this->resolveFontFamilyCss($this->getTokenPath($tokens, ['typography', 'font_button'], ''), $font_body);

        $button_size_key = $this->getTokenPath($tokens, ['buttons', 'size'], 'md');
        $button_size     = $button_size_map[$button_size_key] ?? $button_size_map['md'];
        $button_anim_key = $this->getTokenPath($tokens, ['buttons', 'hover_animation'], 'lift');
        $button_anim     = $button_animation_map[$button_anim_key] ?? $button_animation_map['lift'];

        $primary_bg            = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_primary_bg'], $accent), $accent);
        $primary_text          = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_primary_text'], '#ffffff'), '#ffffff');
        $primary_border        = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_primary_border'], $primary_bg), $primary_bg);
        $primary_bg_hover      = $this->sanitizeColor(
            $this->getTokenPath($tokens, ['colors', 'button_primary_bg_hover'], $this->darkenHex($primary_bg, 12)),
            $this->darkenHex($primary_bg, 12)
        );
        $primary_bg_active     = $this->darkenHex($primary_bg_hover, 10);
        $primary_border_hover  = $primary_bg_hover;
        $primary_border_active = $primary_bg_active;

        $outline_text          = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_outline_text'], $accent), $accent);
        $outline_border        = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_outline_border'], $accent), $accent);
        $outline_bg_hover      = $primary_bg_hover;
        $outline_bg_active     = $primary_bg_active;

        $ghost_text            = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_ghost_text'], $text), $text);
        $ghost_border          = $this->sanitizeColor($this->getTokenPath($tokens, ['colors', 'button_ghost_border'], $border), $border);
        $ghost_bg_hover        = $bgAlt;
        $ghost_bg_active       = $this->darkenHex($bgAlt, 8);

        $accent_alt            = $this->darkenHex($accent, 15);
        $glint_color           = $this->sanitizeColor($this->getTokenPath($tokens, ['buttons', 'glint_color'], '#ffffff'), '#ffffff');
        $glint_duration        = max(250, min(3000, (int) $this->getTokenPath($tokens, ['buttons', 'glint_duration'], 900)));
        $card_border_width     = max(0, min(6, (int) $this->getTokenPath($tokens, ['cards', 'border_width'], 1)));
        $surface_motion        = $this->toBool($this->getTokenPath($tokens, ['cards', 'surface_motion'], true));
        $surface_motion_speed  = $surface_motion ? '180ms' : '0ms';
        $focus_ring            = $this->hexToRgba($accent, 0.18);

        return ':root{'
            . '--nb-color-accent:' . $accent . ';'
            . '--nb-color-accent-alt:' . $accent_alt . ';'
            . '--nb-color-bg:' . $bg . ';'
            . '--nb-color-bg-alt:' . $bgAlt . ';'
            . '--nb-color-surface:' . $surface . ';'
            . '--nb-color-border:' . $border . ';'
            . '--nb-color-text:' . $text . ';'
            . '--nb-color-text-muted:' . $muted . ';'
            . '--nb-radius:' . $base_radius . ';'
            . '--nb-radius-card:' . $card_radius . ';'
            . '--nb-radius-btn:' . $button_radius . ';'
            . '--nb-radius-media:' . $media_radius . ';'
            . '--nb-shadow-card:' . $card_shadow . ';'
            . '--nb-border-width:' . $card_border_width . 'px;'
            . '--nb-section-py:' . $section_py . ';'
            . '--nb-section-padding:' . $section_py . ';'
            . '--nb-font-body:' . $font_body . ';'
            . '--nb-font-head:' . $font_head . ';'
            . '--nb-font-button:' . $font_button . ';'
            . '--nb-btn-font-size:' . $button_size['font'] . ';'
            . '--nb-btn-padding-y:' . $button_size['pad_y'] . ';'
            . '--nb-btn-padding-x:' . $button_size['pad_x'] . ';'
            . '--nb-btn-primary-bg:' . $primary_bg . ';'
            . '--nb-btn-primary-text:' . $primary_text . ';'
            . '--nb-btn-primary-border:' . $primary_border . ';'
            . '--nb-btn-primary-bg-hover:' . $primary_bg_hover . ';'
            . '--nb-btn-primary-text-hover:' . $primary_text . ';'
            . '--nb-btn-primary-border-hover:' . $primary_border_hover . ';'
            . '--nb-btn-primary-bg-active:' . $primary_bg_active . ';'
            . '--nb-btn-primary-text-active:' . $primary_text . ';'
            . '--nb-btn-primary-border-active:' . $primary_border_active . ';'
            . '--nb-btn-outline-bg:transparent;'
            . '--nb-btn-outline-text:' . $outline_text . ';'
            . '--nb-btn-outline-border:' . $outline_border . ';'
            . '--nb-btn-outline-bg-hover:' . $outline_bg_hover . ';'
            . '--nb-btn-outline-text-hover:' . $primary_text . ';'
            . '--nb-btn-outline-border-hover:' . $outline_border . ';'
            . '--nb-btn-outline-bg-active:' . $outline_bg_active . ';'
            . '--nb-btn-outline-text-active:' . $primary_text . ';'
            . '--nb-btn-outline-border-active:' . $outline_bg_active . ';'
            . '--nb-btn-ghost-bg:transparent;'
            . '--nb-btn-ghost-text:' . $ghost_text . ';'
            . '--nb-btn-ghost-border:' . $ghost_border . ';'
            . '--nb-btn-ghost-bg-hover:' . $ghost_bg_hover . ';'
            . '--nb-btn-ghost-text-hover:' . $ghost_text . ';'
            . '--nb-btn-ghost-border-hover:' . $ghost_border . ';'
            . '--nb-btn-ghost-bg-active:' . $ghost_bg_active . ';'
            . '--nb-btn-ghost-text-active:' . $ghost_text . ';'
            . '--nb-btn-ghost-border-active:' . $ghost_border . ';'
            . '--nb-btn-hover-transform:' . $button_anim['transform'] . ';'
            . '--nb-btn-hover-shadow:' . $button_anim['shadow'] . ';'
            . '--nb-btn-active-transform:scale(.98);'
            . '--nb-btn-glint-opacity:' . $button_anim['glint'] . ';'
            . '--nb-btn-glint-color:' . $glint_color . ';'
            . '--nb-btn-glint-duration:' . $glint_duration . 'ms;'
            . '--nb-surface-motion-duration:' . $surface_motion_speed . ';'
            . '--nb-focus-ring:' . $focus_ring . ';'
            . '}';
    }

    public function normalizeDesignTokens(array $tokens) {
        $defaults = $this->getDefaultTokens();
        $has_primary_hover = !empty($tokens['colors']) && is_array($tokens['colors']) && array_key_exists('button_primary_bg_hover', $tokens['colors']);

        $is_legacy_flat = isset($tokens['color_accent']) || isset($tokens['font_body']) || isset($tokens['radius_preset']);
        if ($is_legacy_flat) {
            $has_primary_hover = array_key_exists('button_primary_bg_hover', $tokens);
            $tokens = [
                'version'    => 2,
                'colors'     => [
                    'accent'                => $tokens['color_accent'] ?? null,
                    'bg'                    => $tokens['color_bg'] ?? null,
                    'bg_alt'                => $tokens['color_bg_alt'] ?? null,
                    'surface'               => $tokens['color_surface'] ?? null,
                    'border'                => $tokens['color_border'] ?? null,
                    'text'                  => $tokens['color_text'] ?? null,
                    'text_muted'            => $tokens['color_text_muted'] ?? null,
                    'button_primary_bg'     => $tokens['button_primary_bg'] ?? ($tokens['color_accent'] ?? null),
                    'button_primary_bg_hover' => $tokens['button_primary_bg_hover'] ?? null,
                    'button_primary_text'   => $tokens['button_primary_text'] ?? null,
                    'button_primary_border' => $tokens['button_primary_border'] ?? ($tokens['color_accent'] ?? null),
                    'button_outline_text'   => $tokens['button_outline_text'] ?? ($tokens['color_accent'] ?? null),
                    'button_outline_border' => $tokens['button_outline_border'] ?? ($tokens['color_accent'] ?? null),
                    'button_ghost_text'     => $tokens['button_ghost_text'] ?? ($tokens['color_text'] ?? null),
                    'button_ghost_border'   => $tokens['button_ghost_border'] ?? ($tokens['color_border'] ?? null),
                ],
                'typography' => [
                    'font_body'   => $tokens['font_body'] ?? null,
                    'font_head'   => $tokens['font_head'] ?? null,
                    'font_button' => $tokens['font_button'] ?? null,
                ],
                'layout'     => [
                    'section_spacing' => $tokens['section_spacing'] ?? null,
                ],
                'radii'      => [
                    'base'   => $tokens['radius_preset'] ?? null,
                    'card'   => $tokens['card_radius_preset'] ?? ($tokens['radius_preset'] ?? null),
                    'button' => $tokens['button_radius_preset'] ?? ($tokens['radius_preset'] ?? null),
                    'media'  => $tokens['media_radius_preset'] ?? ($tokens['radius_preset'] ?? null),
                ],
                'buttons'    => [
                    'style'           => $tokens['btn_style'] ?? null,
                    'size'            => $tokens['btn_size'] ?? null,
                    'hover_animation' => $tokens['btn_hover_animation'] ?? null,
                    'glint_color'     => $tokens['btn_glint_color'] ?? null,
                    'glint_duration'  => $tokens['btn_glint_duration'] ?? null,
                ],
                'cards'      => [
                    'border_width'   => $tokens['card_border_width'] ?? null,
                    'shadow_preset'  => $tokens['shadow_preset'] ?? null,
                    'surface_motion' => $tokens['surface_motion'] ?? null,
                ],
            ];
        }

        $merged = array_replace_recursive($defaults, $tokens);

        $merged['version'] = 2;

        $merged['colors']['accent']                 = $this->sanitizeColor($merged['colors']['accent'] ?? $defaults['colors']['accent']);
        $merged['colors']['bg']                     = $this->sanitizeColor($merged['colors']['bg'] ?? $defaults['colors']['bg'], $defaults['colors']['bg']);
        $merged['colors']['bg_alt']                 = $this->sanitizeColor($merged['colors']['bg_alt'] ?? $defaults['colors']['bg_alt'], $defaults['colors']['bg_alt']);
        $merged['colors']['surface']                = $this->sanitizeColor($merged['colors']['surface'] ?? $defaults['colors']['surface'], $defaults['colors']['surface']);
        $merged['colors']['border']                 = $this->sanitizeColor($merged['colors']['border'] ?? $defaults['colors']['border'], $defaults['colors']['border']);
        $merged['colors']['text']                   = $this->sanitizeColor($merged['colors']['text'] ?? $defaults['colors']['text'], $defaults['colors']['text']);
        $merged['colors']['text_muted']             = $this->sanitizeColor($merged['colors']['text_muted'] ?? $defaults['colors']['text_muted'], $defaults['colors']['text_muted']);
        $merged['colors']['button_primary_bg']      = $this->sanitizeColor($merged['colors']['button_primary_bg'] ?? $merged['colors']['accent'], $merged['colors']['accent']);
        if (!$has_primary_hover) {
            $merged['colors']['button_primary_bg_hover'] = $this->darkenHex($merged['colors']['button_primary_bg'], 12);
        }
        $merged['colors']['button_primary_bg_hover'] = $this->sanitizeColor(
            $merged['colors']['button_primary_bg_hover'] ?? $this->darkenHex($merged['colors']['button_primary_bg'], 12),
            $this->darkenHex($merged['colors']['button_primary_bg'], 12)
        );
        $merged['colors']['button_primary_text']    = $this->sanitizeColor($merged['colors']['button_primary_text'] ?? '#ffffff', '#ffffff');
        $merged['colors']['button_primary_border']  = $this->sanitizeColor($merged['colors']['button_primary_border'] ?? $merged['colors']['button_primary_bg'], $merged['colors']['button_primary_bg']);
        $merged['colors']['button_outline_text']    = $this->sanitizeColor($merged['colors']['button_outline_text'] ?? $merged['colors']['accent'], $merged['colors']['accent']);
        $merged['colors']['button_outline_border']  = $this->sanitizeColor($merged['colors']['button_outline_border'] ?? $merged['colors']['accent'], $merged['colors']['accent']);
        $merged['colors']['button_ghost_text']      = $this->sanitizeColor($merged['colors']['button_ghost_text'] ?? $merged['colors']['text'], $merged['colors']['text']);
        $merged['colors']['button_ghost_border']    = $this->sanitizeColor($merged['colors']['button_ghost_border'] ?? $merged['colors']['border'], $merged['colors']['border']);

        foreach (['font_body', 'font_head'] as $font_key) {
            $merged['typography'][$font_key] = $this->sanitizeFontToken($merged['typography'][$font_key] ?? $defaults['typography'][$font_key]);
        }

        $button_font = trim((string) ($merged['typography']['font_button'] ?? ''));
        $merged['typography']['font_button'] = $button_font !== '' ? $this->sanitizeFontToken($button_font) : '';

        $merged['layout']['section_spacing'] = $this->sanitizeEnum(
            $merged['layout']['section_spacing'] ?? $defaults['layout']['section_spacing'],
            ['compact', 'comfortable', 'spacious'],
            $defaults['layout']['section_spacing']
        );

        foreach (['base', 'card', 'button', 'media'] as $radius_key) {
            $merged['radii'][$radius_key] = $this->sanitizeEnum(
                $merged['radii'][$radius_key] ?? $defaults['radii'][$radius_key],
                ['none', 'sm', 'md', 'lg', 'xl', 'pill'],
                $defaults['radii'][$radius_key]
            );
        }

        $merged['buttons']['style'] = $this->sanitizeEnum(
            $merged['buttons']['style'] ?? $defaults['buttons']['style'],
            ['primary', 'outline', 'ghost'],
            $defaults['buttons']['style']
        );
        $merged['buttons']['size'] = $this->sanitizeEnum(
            $merged['buttons']['size'] ?? $defaults['buttons']['size'],
            ['sm', 'md', 'lg'],
            $defaults['buttons']['size']
        );
        $merged['buttons']['hover_animation'] = $this->sanitizeEnum(
            $merged['buttons']['hover_animation'] ?? $defaults['buttons']['hover_animation'],
            ['none', 'lift', 'grow', 'glow', 'glint'],
            $defaults['buttons']['hover_animation']
        );
        $merged['buttons']['glint_color'] = $this->sanitizeColor(
            $merged['buttons']['glint_color'] ?? $defaults['buttons']['glint_color'],
            $defaults['buttons']['glint_color']
        );
        $merged['buttons']['glint_duration'] = max(250, min(3000, (int) ($merged['buttons']['glint_duration'] ?? $defaults['buttons']['glint_duration'])));

        $merged['cards']['border_width'] = max(0, min(6, (int) ($merged['cards']['border_width'] ?? $defaults['cards']['border_width'])));
        $merged['cards']['shadow_preset'] = $this->sanitizeEnum(
            $merged['cards']['shadow_preset'] ?? $defaults['cards']['shadow_preset'],
            ['none', 'sm', 'md', 'lg'],
            $defaults['cards']['shadow_preset']
        );
        $merged['cards']['surface_motion'] = $this->toBool($merged['cards']['surface_motion'] ?? $defaults['cards']['surface_motion']);

        return $merged;
    }

    public function getTokenPath(array $tokens, array $path, $default = null) {
        $cursor = $tokens;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }
        return $cursor;
    }

    private function sanitizeFontToken($value) {
        return $this->sanitizeEnum($value, ['sans', 'serif', 'mono', 'display'], 'sans');
    }

    private function sanitizeEnum($value, array $allowed, $default) {
        $value = trim((string) $value);
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function resolveFontFamilyCss($value, $fallback_css = null) {
        $value = trim((string) $value);

        if ($value === 'serif') {
            return "'Playfair Display', Georgia, serif";
        }
        if ($value === 'mono') {
            return "'JetBrains Mono', 'Courier New', monospace";
        }
        if ($value === 'display') {
            return "'Montserrat', 'Arial', sans-serif";
        }
        if ($value === 'sans') {
            return "'Inter', 'Helvetica Neue', Arial, sans-serif";
        }

        return $fallback_css ?: "'Inter', 'Helvetica Neue', Arial, sans-serif";
    }

    private function toBool($value) {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function hexToRgba($hex, $alpha) {
        $hex = ltrim($this->sanitizeColor($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $alpha = max(0, min(1, (float) $alpha));

        return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . rtrim(rtrim(sprintf('%.3F', $alpha), '0'), '.') . ')';
    }

    public function getImagePresetOptions($with_params = true) {
        $presets = cmsCore::getModel('images')->getPresetsList($with_params);
        return ['original' => defined('LANG_PARSER_IMAGE_SIZE_ORIGINAL') ? LANG_PARSER_IMAGE_SIZE_ORIGINAL : 'Оригинал'] + $presets;
    }

    public function normalizeImagePropsByType($type, array $props) {
        $definition = $this->getBlockDefinition($type);
        if (!$definition || empty($definition['schema']['fields'])) {
            return $props;
        }

        foreach ($definition['schema']['fields'] as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '' || ($field['type'] ?? 'text') !== 'image' || !array_key_exists($key, $props)) {
                continue;
            }

            $normalized = $this->normalizeImageFieldValue($props[$key]);
            if ($normalized !== '') {
                $props[$key] = $normalized;
            }
        }

        return $props;
    }

    public function normalizeImageFieldValue($value) {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return '';
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $legacy_map = $this->normalizeLegacyImageMapString($trimmed);
                if (is_array($legacy_map)) {
                    $value = $legacy_map;
                } else {
                $value = [
                    'original' => $trimmed,
                    'display'  => $trimmed,
                    'preset'   => 'original',
                    'alt'      => '',
                    'variants' => ['original' => $trimmed],
                ];
                }
            }
        }

        if (!is_array($value)) {
            return '';
        }

        $allowed_presets = array_keys($this->getImagePresetOptions(false));
        $preset          = (string) ($value['preset'] ?? 'original');
        if (!in_array($preset, $allowed_presets, true)) {
            $preset = 'original';
        }

        $alt = $this->limitMediaText($value['alt'] ?? '', 255);

        $original_ref_candidate = trim((string) ($value['original_path'] ?? ''));
        $original_ref  = $original_ref_candidate !== '' ? $original_ref_candidate : (string) ($value['original'] ?? '');
        $original_path = $this->resolveExistingUploadRelativePath($original_ref);
        $original_url  = $original_path ? $this->buildUploadUrl($original_path) : $this->sanitizeExternalMediaUrl($value['original'] ?? '');

        if ($alt === '' && $original_path) {
            $alt = $this->humanizeMediaLabel(pathinfo($original_path, PATHINFO_FILENAME));
        }

        $variants = [];
        if ($original_url) {
            $variants['original'] = $original_url;
        }

        if (!empty($value['variants']) && is_array($value['variants'])) {
            foreach ($value['variants'] as $variant_preset => $variant_ref) {
                $variant_preset = (string) $variant_preset;
                if (!in_array($variant_preset, $allowed_presets, true)) {
                    continue;
                }

                $variant_path = $this->resolveExistingUploadRelativePath($variant_ref);
                $variant_url  = $variant_path ? $this->buildUploadUrl($variant_path) : $this->sanitizeExternalMediaUrl($variant_ref);
                if ($variant_url) {
                    $variants[$variant_preset] = $variant_url;
                }
            }
        }

        $display_ref_candidate = trim((string) ($value['display_path'] ?? ''));
        $display_ref  = $display_ref_candidate !== '' ? $display_ref_candidate : (string) ($value['display'] ?? '');
        $display_path = $this->resolveExistingUploadRelativePath($display_ref);
        $display_url  = $display_path ? $this->buildUploadUrl($display_path) : $this->sanitizeExternalMediaUrl($value['display'] ?? '');

        if ($original_path && $preset !== 'original') {
            $variant = $this->ensureImagePresetVariant($original_path, $preset);
            if ($variant) {
                $display_path       = $variant['path'];
                $display_url        = $variant['url'];
                $variants[$preset]  = $variant['url'];
            }
        }

        if (!$display_url) {
            if (!empty($variants[$preset])) {
                $display_url = $variants[$preset];
            } elseif ($original_url) {
                $display_url = $original_url;
            }
        }

        if (!$display_path && $display_url === $original_url) {
            $display_path = $original_path;
        }

        if (!$original_url && $display_url) {
            $original_url = $display_url;
        }

        if (!$original_url && !$display_url) {
            return '';
        }

        return [
            'mode'         => $original_path ? 'managed' : 'external',
            'original'     => $original_url,
            'original_path' => $original_path ?: '',
            'display'      => $display_url ?: $original_url,
            'display_path' => $display_path ?: '',
            'preset'       => $preset,
            'alt'          => $alt,
            'variants'     => $variants,
        ];
    }

    private function normalizeLegacyImageMapString($value) {
        $value = trim((string) $value);
        if ($value === '' || strpos($value, "\n") === false || strpos($value, ':') === false) {
            return null;
        }

        $variants = [];
        $active_key = '';

        foreach (preg_split('/\r\n|\r|\n/', $value) as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed === '' || $trimmed === '---') {
                continue;
            }

            if (preg_match('/^([a-z0-9_]+):\s*>?\s*$/i', $trimmed, $matches)) {
                $active_key = strtolower($matches[1]);
                continue;
            }

            if ($active_key !== '') {
                $variants[$active_key] = $trimmed;
                $active_key = '';
                continue;
            }

            if (preg_match('/^([a-z0-9_]+):\s*(.+)$/i', $trimmed, $matches)) {
                $variants[strtolower($matches[1])] = trim($matches[2], " \t\n\r\0\x0B\"'");
            }
        }

        if (!$variants) {
            return null;
        }

        $preferred = '';
        foreach (['content_item', 'content_list', 'content_list_small', 'normal', 'small', 'big', 'original'] as $candidate) {
            if (!empty($variants[$candidate])) {
                $preferred = (string) $variants[$candidate];
                break;
            }
        }

        if ($preferred === '') {
            $preferred = (string) reset($variants);
        }

        return [
            'original' => $preferred,
            'display'  => $preferred,
            'preset'   => 'original',
            'alt'      => '',
            'variants' => array_merge(['original' => $preferred], $variants),
        ];
    }

    public function getMediaLibraryItems($limit = 200) {
        $upload_path = rtrim((string) cmsConfig::get('upload_path'), '/\\');
        if (!$upload_path || !is_dir($upload_path)) {
            return [];
        }

        $upload_path  = str_replace('\\', '/', $upload_path);
        $preset_names = array_keys(cmsCore::getModel('images')->getPresetsList(false));
        usort($preset_names, function ($a, $b) {
            return strlen((string) $b) <=> strlen((string) $a);
        });

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'];
        $groups      = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($upload_path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file_info) {
            if (!$file_info->isFile()) {
                continue;
            }

            $ext = strtolower((string) pathinfo($file_info->getFilename(), PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext, true)) {
                continue;
            }

            $relative = $this->resolveUploadRelativePath($file_info->getPathname());
            if (!$relative) {
                continue;
            }

            $dirname = str_replace('\\', '/', dirname($relative));
            $dirname = $dirname === '.' ? '' : $dirname;
            $stem    = pathinfo($relative, PATHINFO_FILENAME);
            list($base_stem, $variant_preset) = $this->splitMediaStemPreset($stem, $preset_names);

            $group_key = ($dirname ? $dirname . '/' : '') . $this->normalizeMediaStem($base_stem);
            if (!isset($groups[$group_key])) {
                $title = $this->humanizeMediaLabel($base_stem);
                $groups[$group_key] = [
                    'title'         => $title,
                    'alt'           => $title,
                    'original_path' => '',
                    'variant_paths' => [],
                    'mtime'         => 0,
                ];
            }

            $groups[$group_key]['variant_paths'][$variant_preset] = $relative;
            if ($variant_preset === 'original' || !$groups[$group_key]['original_path']) {
                $groups[$group_key]['original_path'] = $relative;
            }
            if ((int) $file_info->getMTime() > $groups[$group_key]['mtime']) {
                $groups[$group_key]['mtime'] = (int) $file_info->getMTime();
            }
        }

        usort($groups, function ($a, $b) {
            return $b['mtime'] <=> $a['mtime'];
        });

        if ($limit > 0 && count($groups) > $limit) {
            $groups = array_slice($groups, 0, $limit);
        }

        $items = [];
        foreach ($groups as $group) {
            $variants = [];
            foreach ($group['variant_paths'] as $variant_preset => $variant_path) {
                $variants[$variant_preset] = $this->buildUploadUrl($variant_path);
            }

            $preview_preset = 'original';
            foreach (['normal', 'content_list_small', 'content_list', 'small', 'big', 'original'] as $candidate) {
                if (!empty($variants[$candidate])) {
                    $preview_preset = $candidate;
                    break;
                }
            }

            $media = $this->normalizeImageFieldValue([
                'original_path' => $group['original_path'],
                'preset'        => $preview_preset,
                'alt'           => $group['alt'],
                'variants'      => $variants,
            ]);

            if (!$media) {
                continue;
            }

            $generated_presets = [];
            foreach ($preset_names as $preset_name) {
                if ($preset_name !== 'original' && !empty($variants[$preset_name])) {
                    $generated_presets[] = $preset_name;
                }
            }

            $available_presets = ['original'];
            foreach ($generated_presets as $generated_preset) {
                $available_presets[] = $generated_preset;
            }

            $items[] = [
                'title'             => $group['title'],
                'alt'               => $group['alt'],
                'original_path'     => $group['original_path'],
                'preview_url'       => $variants[$preview_preset] ?? ($media['display'] ?: $media['original']),
                'preview_fallback_url' => $media['original'],
                'available_presets' => $available_presets,
                'generated_presets' => $generated_presets,
                'generated_count'   => count($generated_presets),
                'media'             => $media,
            ];
        }

        return $items;
    }

    public function ensureImagePresetVariant($original_relative_path, $preset_name) {
        $preset_name = (string) $preset_name;
        if ($preset_name === '' || $preset_name === 'original') {
            $original_relative_path = $this->resolveUploadRelativePath($original_relative_path);
            if (!$original_relative_path) {
                return null;
            }
            return [
                'path' => $original_relative_path,
                'url'  => $this->buildUploadUrl($original_relative_path),
            ];
        }

        $preset = cmsCore::getModel('images')->getPresetByName($preset_name);
        if (!$preset) {
            return null;
        }

        $original_relative_path = $this->resolveUploadRelativePath($original_relative_path);
        if (!$original_relative_path) {
            return null;
        }

        $original_abs = $this->buildUploadAbsolutePath($original_relative_path);
        if (!$original_abs || !is_file($original_abs)) {
            return null;
        }

        $dest_dir     = str_replace('\\', '/', dirname($original_abs)) . '/';
        if (!is_dir($dest_dir) || !is_writable($dest_dir)) {
            return null;
        }

        $base_name    = pathinfo($original_abs, PATHINFO_FILENAME) . ' ' . $preset['name'];
        $dest_ext     = !empty($preset['convert_format']) ? (string) $preset['convert_format'] : strtolower((string) pathinfo($original_abs, PATHINFO_EXTENSION));
        $expected_abs = $dest_dir . files_sanitize_name($base_name) . '.' . $dest_ext;

        if (is_file($expected_abs)) {
            $expected_rel = $this->resolveUploadRelativePath($expected_abs);
            if ($expected_rel) {
                return [
                    'path' => $expected_rel,
                    'url'  => $this->buildUploadUrl($expected_rel),
                ];
            }
        }

        try {
            $image = new cmsImages($original_abs);
        } catch (Exception $e) {
            return null;
        }

        $generated_abs = $image
            ->setDestinationDir($dest_dir)
            ->resizeByPreset($preset, $base_name);

        if (!$generated_abs || !is_file($generated_abs)) {
            return null;
        }

        $generated_rel = $this->resolveUploadRelativePath($generated_abs);
        if (!$generated_rel) {
            return null;
        }

        return [
            'path' => $generated_rel,
            'url'  => $this->buildUploadUrl($generated_rel),
        ];
    }

    // ── SSR КЭШ ───────────────────────────────────────────────────

    public function getCachedBlock($cache_key) {
        $key = $this->db->escape($cache_key);
        $row = $this->db->getRow(
            self::TBL_CACHE,
            "`cache_key` = '{$key}' AND `expires_at` > NOW()",
            'html'
        );
        return $row ? $row['html'] : null;
    }

    public function setCachedBlock($cache_key, $html, $ttl_seconds = 3600) {
        $key    = $this->db->escape($cache_key);
        $htmlE  = $this->db->escape($html);
        $expires = date('Y-m-d H:i:s', time() + $ttl_seconds);
        $this->db->query(
            "REPLACE INTO `{#}" . self::TBL_CACHE . "` (`cache_key`, `html`, `expires_at`)"
            . " VALUES ('{$key}', '{$htmlE}', '{$expires}')"
        );
    }

    public function invalidatePageCache($page_id) {
        $id = (int) $page_id;
        $this->db->query(
            "DELETE FROM `{#}" . self::TBL_CACHE . "` WHERE `cache_key` LIKE 'page\\_" . $id . "\\_%'"
        );
    }

    public function clearAllCache() {
        $this->db->query("DELETE FROM `{#}" . self::TBL_CACHE . "`");
    }

    public function getCacheStats() {
        $stats = [
            'total'   => 0,
            'active'  => 0,
            'expired' => 0,
            'page'    => 0,
            'block'   => 0,
            'runtime' => 0,
            'other'   => 0,
        ];

        $result = $this->db->query(
            "SELECT"
            . " COUNT(*) AS `total`,"
            . " SUM(`expires_at` > NOW()) AS `active`,"
            . " SUM(`expires_at` <= NOW()) AS `expired`,"
            . " SUM(`cache_key` LIKE 'page\\_%') AS `page_entries`,"
            . " SUM(`cache_key` LIKE 'block\\_%') AS `block_entries`,"
            . " SUM(`cache_key` LIKE 'runtime\\_%') AS `runtime_entries`"
            . " FROM `{#}" . self::TBL_CACHE . "`",
            [],
            true
        );

        if (!$result || $result->num_rows < 1) {
            return $stats;
        }

        $row = $result->fetch_assoc();

        $stats['total'] = (int) ($row['total'] ?? 0);
        $stats['active'] = (int) ($row['active'] ?? 0);
        $stats['expired'] = (int) ($row['expired'] ?? 0);
        $stats['page'] = (int) ($row['page_entries'] ?? 0);
        $stats['block'] = (int) ($row['block_entries'] ?? 0);
        $stats['runtime'] = (int) ($row['runtime_entries'] ?? 0);
        $stats['other'] = max(0, $stats['total'] - $stats['page'] - $stats['block'] - $stats['runtime']);

        return $stats;
    }

    // ── УТИЛИТЫ ───────────────────────────────────────────────────

    public function getRenderCacheVersion($block_type = '') {
        $root_path = cmsConfig::get('root_path');
        $parts = [
            'helpers:' . $this->getFileCacheStamp($root_path . 'system/controllers/nordicblocks/blocks/render_helpers.php'),
        ];

        $block_type = $this->normalizeBlockType((string) $block_type);
        if ($block_type !== '') {
            $parts[] = $block_type . ':' . $this->getFileCacheStamp($root_path . 'system/controllers/nordicblocks/blocks/' . $block_type . '/render.php');
        }

        return substr(md5(implode('|', $parts)), 0, 16);
    }

    private function buildRenderCacheNamespace(array $block, $surface, array $context = []) {
        $block_id = (int) ($block['id'] ?? 0);
        $page_id  = (int) ($context['page_id'] ?? 0);
        $uid      = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($context['uid'] ?? $block['uid'] ?? ''));
        $surface  = preg_replace('/[^a-z0-9_\-]/i', '', (string) $surface);

        if ($block_id > 0) {
            return 'block_' . $block_id . '_' . $surface;
        }

        if ($page_id > 0) {
            return 'page_' . $page_id . '_' . ($uid !== '' ? $uid : 'block');
        }

        return 'runtime_' . ($surface !== '' ? $surface : 'generic');
    }

    private function buildRenderBlockFingerprint(array $block) {
        $block_id = (int) ($block['id'] ?? 0);
        $updated_at = trim((string) ($block['updated_at'] ?? ''));

        if ($block_id > 0 && $updated_at !== '') {
            return [
                'blockId'    => $block_id,
                'type'       => (string) ($block['type'] ?? ''),
                'status'     => (string) ($block['status'] ?? 'active'),
                'updatedAt'  => $updated_at,
            ];
        }

        return [
            'type'        => (string) ($block['type'] ?? ''),
            'uid'         => (string) ($block['uid'] ?? ''),
            'payloadHash' => substr(md5($this->stableJsonEncode([
                'title'    => (string) ($block['title'] ?? ''),
                'status'   => (string) ($block['status'] ?? 'active'),
                'props'    => (array) ($block['props'] ?? []),
                'contract' => (array) ($block['contract'] ?? []),
            ])), 0, 16),
        ];
    }

    private function stableJsonEncode($value) {
        $normalized = $this->normalizeValueForCache($value);
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : '';
    }

    private function getFileCacheStamp($file_path) {
        if (!is_string($file_path) || $file_path === '' || !is_file($file_path)) {
            return 'missing';
        }

        $mtime = @filemtime($file_path);
        if ($mtime === false) {
            return 'unknown';
        }

        return (string) $mtime;
    }

    private function normalizeValueForCache($value) {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[$key] = $this->normalizeValueForCache($item);
        }

        if ($this->isAssocArray($normalized)) {
            ksort($normalized);
        }

        return $normalized;
    }

    private function isAssocArray(array $value) {
        if (!$value) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function getBlockCssOverlayConfig($type) {
        $type = $this->normalizeBlockType((string) $type);

        if ($type !== 'hero_panels_wide') {
            return [];
        }

        return [
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
            'presets'        => [
                'editorial-punch' => [
                    'label'       => 'Editorial Punch',
                    'description' => 'Быстрый старт для плотной журнальной типографики и более выразительных панелей.',
                    'targetCss'   => [
                        'title'         => "font-size: clamp(3.4rem, 5.4vw, 5.2rem);\nletter-spacing: -0.05em;\ntext-wrap: balance;",
                        'body'          => "font-size: 1.1rem;\nline-height: 1.82;\nmax-width: 34ch;",
                        'accentSurface' => "background: linear-gradient(135deg, #ff693d, #b91c1c);\nbox-shadow: 0 24px 60px rgba(185, 28, 28, 0.28);",
                    ],
                ],
                'quiet-luxe' => [
                    'label'       => 'Quiet Luxe',
                    'description' => 'Спокойный high-contrast режим с мягкой поверхностью и удлинённым ритмом текста.',
                    'targetCss'   => [
                        'title'       => "font-size: clamp(3rem, 4.8vw, 4.3rem);\nletter-spacing: -0.035em;",
                        'body'        => "font-size: 1.05rem;\nline-height: 1.9;\nmax-width: 38ch;",
                        'bodySurface' => "background: rgba(15, 23, 42, 0.96);\ncolor: #f8fafc;\nborder-top: 1px solid rgba(255,255,255,0.08);",
                    ],
                ],
                'poster-contrast' => [
                    'label'       => 'Poster Contrast',
                    'description' => 'Более агрессивный title lockup и контрастный accent surface для promo-first hero.',
                    'targetCss'   => [
                        'title'         => "font-size: clamp(3.6rem, 6vw, 5.6rem);\nletter-spacing: -0.06em;\nline-height: .96;",
                        'accentSurface' => "background: linear-gradient(160deg, #fb7185, #7c3aed);\ncolor: #fff;\nbox-shadow: 0 28px 70px rgba(124, 58, 237, 0.28);",
                        'bodySurface'   => "background: #020617;\ncolor: #e2e8f0;",
                    ],
                ],
            ],
        ];
    }

    private function normalizeBlockCssOverlayPresetList($block_type, array $presets) {
        $normalized_list = [];

        foreach ($presets as $preset_key => $preset) {
            if (!is_array($preset)) {
                continue;
            }

            $key = preg_replace('/[^a-z0-9_\-]/i', '', (string) ($preset['key'] ?? $preset_key));
            $target_css = $this->normalizeBlockCssOverlayTargetCss($block_type, (array) ($preset['targetCss'] ?? []));

            if ($key === '' || !$target_css) {
                continue;
            }

            $normalized_list[] = [
                'key'         => $key,
                'label'       => trim((string) ($preset['label'] ?? $key)),
                'description' => trim((string) ($preset['description'] ?? '')),
                'targetCss'   => $target_css,
            ];
        }

        return $normalized_list;
    }

    private function decodeBlockCssOverlayTargetCss($stored_payload, $block_type) {
        $decoded = json_decode((string) $stored_payload, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $this->normalizeBlockCssOverlayTargetCss($block_type, $decoded);
    }

    private function normalizeBlockCssOverlayTargetCss($block_type, array $target_css) {
        $config = $this->getBlockCssOverlayConfig($block_type);
        if (!$config) {
            return [];
        }

        $normalized_map = [];
        foreach ((array) ($config['allowedTargets'] ?? []) as $target_key) {
            if (!array_key_exists($target_key, $target_css)) {
                continue;
            }

            $normalized = $this->normalizeBlockCssOverlayDeclarations($target_css[$target_key] ?? '');
            if ($normalized !== '') {
                $normalized_map[$target_key] = $normalized;
            }
        }

        return $normalized_map;
    }

    private function normalizeBlockCssOverlayDeclarations($value) {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }

        $normalized = preg_replace('/<\/?style[^>]*>/i', '', (string) $value);
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($normalized));

        $open_brace = strpos($normalized, '{');
        $close_brace = strrpos($normalized, '}');
        if ($open_brace !== false && $close_brace !== false && $close_brace > $open_brace) {
            $normalized = substr($normalized, $open_brace + 1, $close_brace - $open_brace - 1);
        }

        if (function_exists('mb_substr')) {
            $normalized = mb_substr($normalized, 0, 16000);
        } else {
            $normalized = substr($normalized, 0, 16000);
        }

        return trim($normalized);
    }

    private function compileBlockCssOverlayCss($block_type, array $target_css, $scope_selector = null) {
        $meta = $this->buildBlockCssOverlayMeta($block_type, $scope_selector);
        if (empty($meta['enabled'])) {
            return '';
        }

        $scope_selector = trim((string) ($meta['scopeSelector'] ?? ''));
        if ($scope_selector === '') {
            return '';
        }

        $css_parts = [];
        foreach ((array) ($meta['allowedTargets'] ?? []) as $target_key) {
            $target_meta = (array) ($meta['targets'][$target_key] ?? []);
            $selector = trim((string) ($target_meta['selector'] ?? ''));
            $declarations = $this->normalizeBlockCssOverlayDeclarations($target_css[$target_key] ?? '');

            if ($selector === '' || $declarations === '') {
                continue;
            }

            $css_parts[] = $scope_selector . ' ' . $selector . ' {' . $declarations . '}';
        }

        return implode("\n\n", $css_parts);
    }

    private function sanitizeColor($hex, $fallback = '#b42318') {
        $hex = preg_replace('/[^0-9a-fA-F#]/', '', (string) $hex);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hex)) {
            return $hex;
        }
        return preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $fallback) ? $fallback : '#b42318';
    }

    private function darkenHex($hex, $amount = 20) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = max(0, hexdec(substr($hex, 0, 2)) - $amount);
        $g = max(0, hexdec(substr($hex, 2, 2)) - $amount);
        $b = max(0, hexdec(substr($hex, 4, 2)) - $amount);
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private function limitMediaText($value, $max) {
        $value = trim(strip_tags((string) $value));
        if ($max <= 0) {
            return '';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max);
        }
        return substr($value, 0, $max);
    }

    private function sanitizeMediaUrl($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('#^(\/|https?:\/\/)#i', $value)) {
            return $value;
        }
        return '';
    }

    private function sanitizeExternalMediaUrl($value) {
        $value = $this->sanitizeMediaUrl($value);
        if ($value === '') {
            return '';
        }

        return preg_match('#^https?://#i', $value) ? $value : '';
    }

    private function buildUploadUrl($relative_path) {
        $relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
        if ($relative_path === '') {
            return '';
        }
        $upload_host = rtrim((string) cmsConfig::get('upload_host'), '/');
        if ($upload_host) {
            return $upload_host . '/' . $relative_path;
        }
        return '/upload/' . $relative_path;
    }

    private function buildUploadAbsolutePath($relative_path) {
        $relative_path = ltrim(str_replace('\\', '/', (string) $relative_path), '/');
        if ($relative_path === '') {
            return null;
        }

        $upload_path = realpath((string) cmsConfig::get('upload_path'));
        if (!$upload_path) {
            return null;
        }

        return str_replace('\\', '/', $upload_path) . '/' . $relative_path;
    }

    private function resolveUploadRelativePath($ref) {
        $ref = trim((string) $ref);
        if ($ref === '') {
            return null;
        }

        $ref         = str_replace('\\', '/', $ref);
        $upload_path = realpath((string) cmsConfig::get('upload_path'));
        if (!$upload_path) {
            return null;
        }
        $upload_path = str_replace('\\', '/', $upload_path);
        $upload_host = rtrim((string) cmsConfig::get('upload_host'), '/');

        if ($upload_host && strpos($ref, $upload_host . '/') === 0) {
            $ref = substr($ref, strlen($upload_host) + 1);
        } elseif (strpos($ref, '/upload/') === 0) {
            $ref = substr($ref, 8);
        } elseif (strpos($ref, 'upload/') === 0) {
            $ref = substr($ref, 7);
        } elseif (strpos($ref, $upload_path . '/') === 0) {
            $ref = substr($ref, strlen($upload_path) + 1);
        }

        $ref = ltrim($ref, '/');
        if ($ref === '') {
            return null;
        }

        $absolute_guess = $upload_path . '/' . $ref;
        $absolute_real  = realpath($absolute_guess);
        if ($absolute_real) {
            $absolute_real = str_replace('\\', '/', $absolute_real);
            if (strpos($absolute_real, $upload_path . '/') === 0) {
                return ltrim(substr($absolute_real, strlen($upload_path)), '/');
            }
        }

        return $ref;
    }

    private function resolveExistingUploadRelativePath($ref) {
        $path = $this->resolveUploadRelativePath($ref);
        if (!$path) {
            return null;
        }

        $absolute_path = $this->buildUploadAbsolutePath($path);
        return ($absolute_path && is_file($absolute_path)) ? $path : null;
    }

    private function splitMediaStemPreset($stem, array $preset_names) {
        foreach ($preset_names as $preset_name) {
            if (!$preset_name) {
                continue;
            }

            $suffixes = [
                ' ' . $preset_name,
                '-' . $this->normalizeMediaStem($preset_name),
                '_' . str_replace('-', '_', $this->normalizeMediaStem($preset_name)),
            ];

            foreach ($suffixes as $suffix) {
                if ($suffix !== '' && substr($stem, -strlen($suffix)) === $suffix) {
                    return [substr($stem, 0, -strlen($suffix)), $preset_name];
                }
            }
        }

        return [$stem, 'original'];
    }

    private function humanizeMediaLabel($base_stem) {
        $label = str_replace(['-', '_'], ' ', (string) $base_stem);
        $label = preg_replace('/\s+/', ' ', $label);
        $label = trim($label);
        if ($label === '') {
            return 'Изображение';
        }
        return function_exists('mb_convert_case') ? mb_convert_case($label, MB_CASE_TITLE, 'UTF-8') : ucfirst($label);
    }

    private function normalizeMediaStem($value) {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[\s_]+/', '-', $value);
        $value = preg_replace('/[^a-z0-9\-]+/', '-', $value);
        $value = preg_replace('/-+/', '-', $value);
        return trim($value, '-');
    }

    private function normalizeBlockSchemaFields(array $schema, $block_type = '') {
        $fields = [];

        if (!empty($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $key = $this->normalizeFieldKey($field['key'] ?? '');
                if (!$key) {
                    continue;
                }

                $field['key'] = $key;
                $field = $this->normalizeSchemaEditorField($field);
                $fields[]     = $field;
            }

            return $this->enhanceBlockSchemaFields($fields, $block_type);
        }

        foreach ($schema as $raw_key => $field) {
            if (!is_array($field) || !isset($field['type'])) {
                continue;
            }

            $key = $this->normalizeFieldKey($raw_key);
            if (!$key) {
                continue;
            }

            $field['key'] = $key;
            $field = $this->normalizeSchemaEditorField($field);
            $fields[]     = $field;
        }

        return $this->enhanceBlockSchemaFields($fields, $block_type);
    }

    private function normalizeSchemaEditorField(array $field) {
        $field_type = strtolower((string) ($field['type'] ?? 'text'));
        if ($field_type === '') {
            $field_type = 'text';
        }

        if ($field_type === 'text' && $this->isLikelyIconField($field)) {
            $field_type = 'icon';
        }

        $field['type'] = $field_type;

        if ($field_type === 'repeater' && !empty($field['fields']) && is_array($field['fields'])) {
            $normalized_children = [];

            foreach ($field['fields'] as $child_field) {
                if (!is_array($child_field)) {
                    continue;
                }

                $child_key = $this->normalizeFieldKey($child_field['key'] ?? '');
                if (!$child_key) {
                    continue;
                }

                $child_field['key'] = $child_key;
                $normalized_children[] = $this->normalizeSchemaEditorField($child_field);
            }

            $field['fields'] = $normalized_children;
        }

        return $field;
    }

    private function isLikelyIconField(array $field) {
        $key = strtolower((string) ($field['key'] ?? ''));
        if ($key !== '' && preg_match('/(^|_)(icon|ico)$/', $key)) {
            return true;
        }

        $haystacks = [
            (string) ($field['label'] ?? ''),
            (string) ($field['help'] ?? ''),
            (string) ($field['placeholder'] ?? ''),
        ];

        foreach ($haystacks as $haystack) {
            $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);
            if ($haystack !== '' && (strpos($haystack, 'икон') !== false || strpos($haystack, 'font awesome') !== false || strpos($haystack, 'sprite:icon') !== false)) {
                return true;
            }
        }

        return false;
    }

    private function enhanceBlockSchemaFields(array $fields, $block_type = '') {
        foreach (['title', 'heading'] as $base_key) {
            $source_field = $this->findFieldByKey($fields, $base_key);
            if (!$source_field) {
                continue;
            }

            if (!$this->findFieldByKey($fields, $base_key . '_tag')) {
                $fields[] = $this->buildHeadingTagField($source_field, $block_type);
            }

            if (!$this->findFieldByKey($fields, $base_key . '_weight')) {
                $fields[] = $this->buildHeadingWeightField($source_field, $block_type);
            }
        }

        if (!$this->findFieldByKey($fields, 'block_animation')) {
            $fields[] = $this->buildBlockAnimationField();
        }

        if (!$this->findFieldByKey($fields, 'block_animation_delay')) {
            $fields[] = $this->buildBlockAnimationDelayField();
        }

        return $fields;
    }

    private function findFieldByKey(array $fields, $key) {
        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            if ((string) ($field['key'] ?? '') === (string) $key) {
                return $field;
            }
        }

        return null;
    }

    private function isHeroLikeBlockType($block_type) {
        $block_type = strtolower(trim((string) $block_type));

        return $block_type === 'hero'
            || $block_type === 'hero_classic'
            || strpos($block_type, 'hero-') === 0
            || strpos($block_type, 'hero_') === 0;
    }

    private function buildHeadingTagField(array $source_field, $block_type) {
        $base_key     = (string) ($source_field['key'] ?? 'heading');
        $field_label  = (string) ($source_field['label'] ?? 'Заголовок');
        $default_tag  = $this->isHeroLikeBlockType($block_type) ? 'h1' : 'h2';

        return [
            'key'           => $base_key . '_tag',
            'type'          => 'select',
            'label'         => $field_label . ' — HTML тег',
            'default'       => $default_tag,
            'options'       => [
                ['label' => 'DIV', 'value' => 'div'],
                ['label' => 'H1', 'value' => 'h1'],
                ['label' => 'H2', 'value' => 'h2'],
                ['label' => 'H3', 'value' => 'h3'],
            ],
            'section'       => 'typography',
            'section_label' => 'Типографика',
            'section_hint'  => 'Размеры, семантика заголовка, жирность и SEO-структура блока.',
            'help'          => 'Позволяет выбрать SEO-семантику: H1, H2, H3 или нейтральный DIV.',
        ];
    }

    private function buildHeadingWeightField(array $source_field, $block_type) {
        $base_key        = (string) ($source_field['key'] ?? 'heading');
        $field_label     = (string) ($source_field['label'] ?? 'Заголовок');
        $default_weight  = $this->isHeroLikeBlockType($block_type) ? '900' : '800';

        return [
            'key'           => $base_key . '_weight',
            'type'          => 'select',
            'label'         => $field_label . ' — жирность',
            'default'       => $default_weight,
            'options'       => [
                ['label' => '400 — Normal', 'value' => '400'],
                ['label' => '500 — Medium', 'value' => '500'],
                ['label' => '600 — SemiBold', 'value' => '600'],
                ['label' => '700 — Bold', 'value' => '700'],
                ['label' => '800 — ExtraBold', 'value' => '800'],
                ['label' => '900 — Black', 'value' => '900'],
            ],
            'section'       => 'typography',
            'section_label' => 'Типографика',
            'section_hint'  => 'Размеры, семантика заголовка, жирность и SEO-структура блока.',
            'help'          => 'Шрифтовая пара берется из дизайн-системы, а жирность можно регулировать прямо в блоке.',
        ];
    }

    private function buildBlockAnimationField() {
        return [
            'key'           => 'block_animation',
            'type'          => 'select',
            'label'         => 'Анимация появления',
            'default'       => 'none',
            'options'       => [
                ['label' => 'Без анимации', 'value' => 'none'],
                ['label' => 'Fade Up', 'value' => 'fade-up'],
                ['label' => 'Fade In', 'value' => 'fade-in'],
                ['label' => 'Zoom In', 'value' => 'zoom-in'],
            ],
            'section'       => 'effects',
            'section_label' => 'Появление',
            'section_hint'  => 'Легкая CSS-анимация блока без дополнительных зависимостей.',
        ];
    }

    private function buildBlockAnimationDelayField() {
        return [
            'key'           => 'block_animation_delay',
            'type'          => 'number',
            'label'         => 'Задержка анимации',
            'default'       => 0,
            'min'           => 0,
            'max'           => 1500,
            'step'          => 50,
            'unit'          => 'ms',
            'section'       => 'effects',
            'section_label' => 'Появление',
            'section_hint'  => 'Легкая CSS-анимация блока без дополнительных зависимостей.',
        ];
    }

    private function normalizeBlockType($type) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim((string) $type)));
    }

    private function normalizeFieldKey($key) {
        $key = preg_replace('/(?<!^)([A-Z])/', '_$1', (string) $key);
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9_]+/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);
        return trim($key, '_');
    }
}
