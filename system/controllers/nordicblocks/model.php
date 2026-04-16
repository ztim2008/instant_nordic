<?php

class modelNordicblocks extends cmsModel {

    const TBL_PAGES  = 'nordicblocks_pages';
    const TBL_BLOCKS = 'nordicblocks_blocks';
    const TBL_DESIGN = 'nordicblocks_design';
    const TBL_CACHE  = 'nordicblocks_cache';

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
        $row['props'] = !empty($row['props_json']) ? (array) json_decode($row['props_json'], true) : [];
        return $row;
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
        $preview     = file_exists($block_dir . '/preview.png') ? '/nordicblocks/blocks/' . $type . '/preview.png' : '';

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
                'fields'      => $this->normalizeBlockSchemaFields($schema),
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
        $this->invalidateBlockCache($id);
    }

    public function deleteBlock($id) {
        $id = (int) $id;
        $this->db->delete(self::TBL_BLOCKS, "`id` = {$id}");
        $this->invalidateBlockCache($id);
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
        return is_array($tokens) ? array_merge($this->getDefaultTokens(), $tokens) : $this->getDefaultTokens();
    }

    public function saveDesignTokens(array $tokens) {
        $now  = date('Y-m-d H:i:s');
        $json = json_encode($tokens, JSON_UNESCAPED_UNICODE);
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
            'color_accent'     => '#b42318',
            'color_bg'         => '#ffffff',
            'color_bg_alt'     => '#f7f7f6',
            'color_surface'    => '#ffffff',
            'color_border'     => '#e5e7eb',
            'color_text'       => '#1a1a1a',
            'color_text_muted' => '#6b7280',
            'font_body'        => 'sans',
            'font_head'        => 'sans',
            'radius_preset'    => 'md',
            'shadow_preset'    => 'md',
            'section_spacing'  => 'comfortable',
            'btn_style'        => 'primary',
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
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'md',
                'shadow_preset'    => 'md',
                'section_spacing'  => 'comfortable',
                'btn_style'        => 'primary',
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
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'md',
                'shadow_preset'    => 'lg',
                'section_spacing'  => 'comfortable',
                'btn_style'        => 'primary',
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
                'font_body'        => 'sans',
                'font_head'        => 'serif',
                'radius_preset'    => 'sm',
                'shadow_preset'    => 'sm',
                'section_spacing'  => 'spacious',
                'btn_style'        => 'outline',
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
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'sm',
                'shadow_preset'    => 'sm',
                'section_spacing'  => 'comfortable',
                'btn_style'        => 'primary',
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
                'font_body'        => 'sans',
                'font_head'        => 'sans',
                'radius_preset'    => 'xl',
                'shadow_preset'    => 'md',
                'section_spacing'  => 'spacious',
                'btn_style'        => 'primary',
            ],
        ];
    }

    public function buildInlineCss(array $tokens) {
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

        $accent    = $this->sanitizeColor($tokens['color_accent']     ?? '#b42318');
        $bg        = $this->sanitizeColor($tokens['color_bg']         ?? '#ffffff');
        $bgAlt     = $this->sanitizeColor($tokens['color_bg_alt']     ?? '#f7f7f6');
        $surface   = $this->sanitizeColor($tokens['color_surface']    ?? '#ffffff');
        $border    = $this->sanitizeColor($tokens['color_border']     ?? '#e5e7eb');
        $text      = $this->sanitizeColor($tokens['color_text']       ?? '#1a1a1a');
        $muted     = $this->sanitizeColor($tokens['color_text_muted'] ?? '#6b7280');
        $radius    = $radius_map[$tokens['radius_preset']    ?? 'md']          ?? '8px';
        $shadow    = $shadow_map[$tokens['shadow_preset']    ?? 'md']          ?? '0 4px 12px 0 rgb(0 0 0 / .08)';
        $sectionPy = $section_py_map[$tokens['section_spacing'] ?? 'comfortable'] ?? 'clamp(3rem, 2rem + 5vw, 6rem)';

        $fontBody = ($tokens['font_body'] ?? 'sans') === 'serif'
            ? "'Playfair Display', Georgia, serif"
            : "'Inter', 'Helvetica Neue', Arial, sans-serif";
        $fontHead = ($tokens['font_head'] ?? 'sans') === 'serif'
            ? "'Playfair Display', Georgia, serif"
            : "'Inter', 'Helvetica Neue', Arial, sans-serif";

        $accentAlt = $this->darkenHex($accent, 15);

        return ":root{"
            . "--nb-color-accent:{$accent};"
            . "--nb-color-accent-alt:{$accentAlt};"
            . "--nb-color-bg:{$bg};"
            . "--nb-color-bg-alt:{$bgAlt};"
            . "--nb-color-surface:{$surface};"
            . "--nb-color-border:{$border};"
            . "--nb-color-text:{$text};"
            . "--nb-color-text-muted:{$muted};"
            . "--nb-radius:{$radius};"
            . "--nb-radius-card:{$radius};"
            . "--nb-radius-btn:{$radius};"
            . "--nb-shadow-card:{$shadow};"
            . "--nb-section-py:{$sectionPy};"
            . "--nb-font-body:{$fontBody};"
            . "--nb-font-head:{$fontHead};"
            . "}";
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
                $value = [
                    'original' => $trimmed,
                    'display'  => $trimmed,
                    'preset'   => 'original',
                    'alt'      => '',
                    'variants' => ['original' => $trimmed],
                ];
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

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
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
            foreach (['small', 'normal', 'content_list_small', 'content_list', 'big', 'original'] as $candidate) {
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
                'preview_url'       => $media['display'],
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

    // ── УТИЛИТЫ ───────────────────────────────────────────────────

    private function sanitizeColor($hex) {
        $hex = preg_replace('/[^0-9a-fA-F#]/', '', (string) $hex);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hex)) {
            return $hex;
        }
        return '#b42318';
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

    private function normalizeBlockSchemaFields(array $schema) {
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
                $fields[]     = $field;
            }

            return $fields;
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
            $fields[]     = $field;
        }

        return $fields;
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
