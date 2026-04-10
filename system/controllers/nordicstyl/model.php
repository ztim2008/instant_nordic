<?php

class modelNordicstyl extends cmsModel {

    public function getAvailableSelectorMapSourcesForTemplate(string $templateName): array {

        if (!$this->selectorMapTableExists()) {
            return [];
        }

        $templateName = trim($templateName);
        if ($templateName === '') {
            return [];
        }

        $tplEsc = $this->db->escape($templateName);
        $where = "source LIKE 'tpl_{$tplEsc}_%' OR source LIKE '{$tplEsc}_%'";
        $sql = "SELECT DISTINCT source FROM {#}nordicstyl_selector_map WHERE {$where} ORDER BY source ASC";

        $res = $this->db->query($sql, false, true);
        if (!$res) {
            return [];
        }

        $rows = $this->db->fetchAll($res);
        $this->db->freeResult($res);

        if (!is_array($rows)) {
            return [];
        }

        $sources = [];
        foreach ($rows as $row) {
            $s = trim((string)($row['source'] ?? ''));
            if ($s !== '') {
                $sources[] = $s;
            }
        }

        return $sources;
    }

    public function selectorMapTableExists(): bool {

        $res = $this->db->query("SHOW TABLES LIKE '{#}nordicstyl_selector_map'", false, true);
        if (!$res) {
            return false;
        }

        $exists = (int)$this->db->numRows($res) > 0;
        $this->db->freeResult($res);

        return $exists;
    }

    public function getSelectorMapStats(string $source): array {

        if (!$this->selectorMapTableExists()) {
            return [
                'table_exists' => false,
                'count' => 0,
                'updated_at' => null,
                'source_hash' => ''
            ];
        }

        $sourceEsc = $this->db->escape($source);
        $count = (int)$this->db->getRowsCount('nordicstyl_selector_map', "source = '{$sourceEsc}'");
        $row = $this->db->getRow('nordicstyl_selector_map', "source = '{$sourceEsc}'", 'MAX(updated_at) AS updated_at, MAX(source_hash) AS source_hash');

        return [
            'table_exists' => true,
            'count' => $count,
            'updated_at' => $row['updated_at'] ?? null,
            'source_hash' => $row['source_hash'] ?? ''
        ];
    }

    public function searchSelectorMap(string $query = '', int $limit = 200, string $source): array {

        if (!$this->selectorMapTableExists()) {
            return [];
        }

        $limit = max(1, min(500, $limit));
        $sourceEsc = $this->db->escape($source);

        $where = "source = '{$sourceEsc}'";
        $query = trim($query);
        if ($query !== '') {
            // Escape for SQL + escape LIKE wildcards
            $qEsc = $this->db->escape($query);
            $qLike = str_replace(['%', '_'], ['\\%', '\\_'], $qEsc);
            $like = "'%{$qLike}%'";
            $where .= " AND (title LIKE {$like} ESCAPE '\\' OR group_path LIKE {$like} ESCAPE '\\' OR selector LIKE {$like} ESCAPE '\\')";
        }

        $sql = "SELECT uid, group_path, title, selector, ordering FROM {#}nordicstyl_selector_map WHERE {$where} ORDER BY ordering ASC, title ASC LIMIT {$limit}";
        $res = $this->db->query($sql, false, true);
        if (!$res) {
            return [];
        }

        $rows = $this->db->fetchAll($res);
        $this->db->freeResult($res);

        return is_array($rows) ? $rows : [];
    }

    public function importSelectorMapFromJsonFile(string $jsonFile, string $source): array {

        if (!$this->selectorMapTableExists()) {
            return ['ok' => false, 'error' => 'Selector map table does not exist'];
        }

        if (!is_file($jsonFile) || !is_readable($jsonFile)) {
            return ['ok' => false, 'error' => 'Selector map JSON not found: ' . $jsonFile];
        }

        $raw = file_get_contents($jsonFile);
        if ($raw === false || trim($raw) === '') {
            return ['ok' => false, 'error' => 'Selector map JSON is empty'];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Selector map JSON is not valid'];
        }

        $sourceHash = sha1($raw);
        $entries = [];
        $ordering = 0;
        $this->flattenSelectorMapTree($data, [], $entries, $ordering);

        if (!$entries) {
            return ['ok' => false, 'error' => 'No selectors found in selector map JSON'];
        }

        $ins = 0;
        $upd = 0;
        $same = 0;

        $now = date('Y-m-d H:i:s');
        $sourceEsc = $this->db->escape($source);
        $hashEsc = $this->db->escape($sourceHash);

        foreach ($entries as $e) {

            $groupEsc = $this->db->escape($e['group_path']);
            $titleEsc = $this->db->escape($e['title']);
            $selectorEsc = $this->db->escape($e['selector']);
            $ord = (int)$e['ordering'];

            $uid = sha1($source . "\n" . $e['group_path'] . "\n" . $e['title']);
            $uidEsc = $this->db->escape($uid);

            $sql = "INSERT INTO {#}nordicstyl_selector_map (uid, source, source_hash, group_path, title, selector, ordering, created_at, updated_at)
                    VALUES ('{$uidEsc}', '{$sourceEsc}', '{$hashEsc}', '{$groupEsc}', '{$titleEsc}', '{$selectorEsc}', {$ord}, '{$now}', '{$now}')
                    ON DUPLICATE KEY UPDATE
                        source_hash = VALUES(source_hash),
                        group_path = VALUES(group_path),
                        title = VALUES(title),
                        selector = VALUES(selector),
                        ordering = VALUES(ordering),
                        updated_at = VALUES(updated_at)";

            $ok = $this->db->query($sql, false, true);
            if ($ok === false) {
                continue;
            }

            $aff = (int)$this->db->affectedRows();
            if ($aff === 1) {
                $ins++;
            } elseif ($aff === 2) {
                $upd++;
            } else {
                $same++;
            }
        }

        return [
            'ok' => true,
            'source_hash' => $sourceHash,
            'total' => count($entries),
            'inserted' => $ins,
            'updated' => $upd,
            'unchanged' => $same
        ];
    }

    protected function flattenSelectorMapTree($node, array $crumbs, array &$out, int &$ordering): void {

        if (!is_array($node)) {
            return;
        }

        foreach ($node as $key => $value) {

            $key = trim((string)$key);
            if ($key === '') {
                continue;
            }

            if (is_string($value)) {
                $selector = trim($value);
                if ($selector === '') {
                    continue;
                }

                $groupPath = implode(' / ', $crumbs);
                $title = $key;

                $ordering++;
                $out[] = [
                    'group_path' => $groupPath,
                    'title' => $title,
                    'selector' => $selector,
                    'ordering' => $ordering
                ];

                continue;
            }

            if (is_array($value)) {
                $nextCrumbs = $crumbs;
                $nextCrumbs[] = $key;
                $this->flattenSelectorMapTree($value, $nextCrumbs, $out, $ordering);
            }
        }
    }


    public function purgeExpiredPickerTokens(): void {
        $this->db->query("DELETE FROM {#}nordicstyl_picker_tokens WHERE expires_at < NOW()", false, true);
    }

    public function savePickerToken(string $token, int $ttlSeconds): bool {

        $token = trim($token);
        if ($token === '' || $ttlSeconds <= 0) {
            return false;
        }

        $this->purgeExpiredPickerTokens();

        $tokenSql = $this->db->escape($token);
        $ttlSeconds = (int)$ttlSeconds;

        return (bool)$this->db->query(
            "REPLACE INTO {#}nordicstyl_picker_tokens (token, created_at, expires_at) VALUES ('{$tokenSql}', NOW(), DATE_ADD(NOW(), INTERVAL {$ttlSeconds} SECOND))",
            false,
            true
        );
    }

    public function isPickerTokenValid(string $token): bool {

        $token = trim($token);
        if ($token === '') {
            return false;
        }

        $this->purgeExpiredPickerTokens();

        $tokenSql = $this->db->escape($token);
        $row = $this->db->getRow('nordicstyl_picker_tokens', "token = '{$tokenSql}' AND expires_at >= NOW()", 'token');

        return !empty($row['token']);
    }

    public function getRules(bool $enabledOnly = true): array {

        $this->resetFilters();

        if ($enabledOnly) {
            $this->filterEqual('is_enabled', 1);
        }

        $this->orderBy('ordering', 'asc');
        $this->orderBy('id', 'asc');

        $rules = $this->get('nordicstyl_styles');

        return is_array($rules) ? $rules : [];
    }

    public function getRule(int $id): ?array {
        if ($id <= 0) {
            return null;
        }

        $rule = $this->getItemById('nordicstyl_styles', $id);

        return $rule && is_array($rule) ? $rule : null;
    }

    public function addRule(array $rule) {

        if (!isset($rule['ordering']) || (int)$rule['ordering'] < 0) {
            $rule['ordering'] = $this->getMaxRuleOrdering() + 1;
        }

        unset($rule['id']);

        return $this->insert('nordicstyl_styles', $rule);
    }

    public function updateRule(int $id, array $rule): bool {
        if ($id <= 0) {
            return false;
        }

        unset($rule['id']);

        return (bool)$this->update('nordicstyl_styles', $id, $rule);
    }

    public function deleteRule(int $id): bool {
        if ($id <= 0) {
            return false;
        }

        $rule = $this->getRule($id);
        if ($rule && isset($rule['ordering'])) {
            $this->filterGt('ordering', (int)$rule['ordering'])->decrement('nordicstyl_styles', 'ordering');
        }

        return (bool)$this->delete('nordicstyl_styles', $id);
    }

    public function getMaxRuleOrdering(): int {
        return (int)$this->getMaxOrdering('nordicstyl_styles');
    }
}
