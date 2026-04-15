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
}
