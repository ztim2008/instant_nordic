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

    public function searchSelectorMap(string $source, string $query = '', int $limit = 200): array {

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

    public function publishLayoutState(string $templateName, string $targetUri, array $layoutState, string $sourceTemplate = ''): array {

        $templateName = trim($templateName);
        $targetUri = $this->normalizeLayoutStateUri($targetUri);

        if ($templateName === '') {
            return [
                'ok' => false,
                'message' => 'Не указан шаблон для публикации.'
            ];
        }

        if ($targetUri !== '/') {
            return [
                'ok' => false,
                'message' => 'Публикация native layout пока доступна только для главной страницы.'
            ];
        }

        $desktopRows = $this->extractDesktopRows($layoutState);
        if (!$desktopRows) {
            return [
                'ok' => false,
                'message' => 'В desktop state нет рядов для публикации.'
            ];
        }

        $backendWidgetsModel = cmsCore::getModel('backend_widgets', '_', false);
        if (!$backendWidgetsModel) {
            return [
                'ok' => false,
                'message' => 'Model backend_widgets недоступна для публикации.'
            ];
        }

        $autocommitWasEnabled = $this->db->isAutocommitOn();

        try {
            if ($autocommitWasEnabled) {
                $this->db->autocommitOff();
            }

            $this->db->beginTransaction();

            $preservedBindIds = $this->collectReferencedBindIds($desktopRows);
            $this->cleanupTemplatePageBindings($templateName, 1, $preservedBindIds);
            $this->deleteTemplateLayout($templateName);

            $publishContext = [
                'used_positions' => [],
                'column_positions' => [],
                'published_rows' => 0,
                'published_columns' => 0
            ];

            $this->insertPublishedRows($desktopRows, $templateName, null, $publishContext);
            $bindingsStats = $this->publishHomepageBindings($backendWidgetsModel, $templateName, $desktopRows, $publishContext['column_positions']);
            $inheritedBindings = $this->syncInheritedTemplateBindings(
                $backendWidgetsModel,
                $templateName,
                $sourceTemplate,
                array_values($publishContext['column_positions'])
            );

            $this->db->commit();

            if ($autocommitWasEnabled) {
                $this->db->autocommitOn();
            }

            return [
                'ok' => true,
                'message' => 'Desktop-схема опубликована в native layout для шаблона ' . $templateName . '.',
                'rows' => $publishContext['published_rows'],
                'columns' => $publishContext['published_columns'],
                'widgets' => $bindingsStats['widgets'] + $inheritedBindings
            ];
        } catch (Throwable $exception) {
            $this->db->rollback();

            if ($autocommitWasEnabled) {
                $this->db->autocommitOn();
            }

            return [
                'ok' => false,
                'message' => 'Не удалось опубликовать desktop-схему в native layout.',
                'details' => $exception->getMessage()
            ];
        }
    }

    protected function extractDesktopRows(array $layoutState): array {

        $rows = $layoutState['desktop']['rows'] ?? [];

        return is_array($rows) ? $rows : [];
    }

    protected function collectReferencedBindIds(array $rows): array {

        $bindIds = [];

        foreach ($rows as $row) {
            if (!empty($row['hidden'])) {
                continue;
            }

            $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
            foreach ($columns as $column) {
                if (!empty($column['hidden'])) {
                    continue;
                }

                $widgets = is_array($column['widgets'] ?? null) ? $column['widgets'] : [];
                foreach ($widgets as $widget) {
                    if (!empty($widget['hidden'])) {
                        continue;
                    }

                    $bindId = (int)($widget['bind_id'] ?? 0);
                    if ($bindId > 0) {
                        $bindIds[$bindId] = $bindId;
                    }
                }

                $nestedRows = is_array($column['nested_rows'] ?? null) ? $column['nested_rows'] : [];
                foreach ($this->collectReferencedBindIds($nestedRows) as $bindId) {
                    $bindIds[$bindId] = $bindId;
                }
            }
        }

        return array_values($bindIds);
    }

    protected function cleanupTemplatePageBindings(string $templateName, int $pageId, array $preservedBindIds): void {

        $this->resetFilters();
        $existingBindings = $this->filterEqual('template', $templateName)->filterEqual('page_id', $pageId)->get('widgets_bind_pages', false) ?: [];

        $bindIds = [];
        foreach ($existingBindings as $bindingPage) {
            $bindId = (int)($bindingPage['bind_id'] ?? 0);
            if ($bindId > 0) {
                $bindIds[$bindId] = $bindId;
            }
        }

        $this->resetFilters();
        $this->filterEqual('template', $templateName)->filterEqual('page_id', $pageId)->deleteFiltered('widgets_bind_pages');

        foreach ($bindIds as $bindId) {
            if (in_array($bindId, $preservedBindIds, true)) {
                continue;
            }

            $this->resetFilters();
            $bindPagesCount = (int)$this->filterEqual('bind_id', $bindId)->getCount('widgets_bind_pages', 'id', true);
            if ($bindPagesCount < 1) {
                $this->delete('widgets_bind', $bindId);
            }
        }
    }

    protected function deleteTemplateLayout(string $templateName): void {

        $this->resetFilters();
        $rows = $this->filterEqual('template', $templateName)->get('layout_rows', false) ?: [];

        $rowIds = [];
        foreach ($rows as $row) {
            $rowId = (int)($row['id'] ?? 0);
            if ($rowId > 0) {
                $rowIds[] = $rowId;
            }
        }

        if ($rowIds) {
            $this->resetFilters();
            $this->filterIn('row_id', $rowIds)->deleteFiltered('layout_cols');
        }

        $this->resetFilters();
        $this->filterEqual('template', $templateName)->deleteFiltered('layout_rows');
    }

    protected function insertPublishedRows(array $rows, string $templateName, ?int $parentColumnId, array &$publishContext): void {

        $ordering = 1;

        foreach ($rows as $row) {
            if (!is_array($row) || !empty($row['hidden'])) {
                continue;
            }

            $rowMeta = is_array($row['meta'] ?? null) ? $row['meta'] : [];
            $rowOptions = is_array($rowMeta['options'] ?? null) ? $rowMeta['options'] : $this->getDefaultPublishedRowOptions();
            $rowData = [
                'parent_id' => $parentColumnId,
                'title' => trim((string)($row['title'] ?? 'Ряд')),
                'tag' => trim((string)($rowMeta['tag'] ?? 'div')) ?: 'div',
                'template' => $templateName,
                'ordering' => $ordering,
                'nested_position' => $parentColumnId ? $this->normalizeNestedPosition((string)($rowMeta['nested_position'] ?? 'after')) : null,
                'class' => $this->normalizeNullableString($rowMeta['class'] ?? null),
                'options' => cmsModel::arrayToString($this->applyWidthModeToRowOptions($rowOptions, (string)($row['width_mode'] ?? 'grid')))
            ];

            $rowId = $this->insert('layout_rows', $rowData, true);
            $publishContext['published_rows']++;

            $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
            $columnOrdering = 1;

            foreach ($columns as $column) {
                if (!is_array($column) || !empty($column['hidden'])) {
                    continue;
                }

                $columnMeta = is_array($column['meta'] ?? null) ? $column['meta'] : [];
                $positionName = $this->generatePublishedPositionName($column, $publishContext['used_positions']);
                $columnOptions = is_array($columnMeta['options'] ?? null) ? $columnMeta['options'] : $this->getDefaultPublishedColumnOptions();
                $columnData = [
                    'row_id' => $rowId,
                    'title' => trim((string)($column['title'] ?? 'Колонка')),
                    'name' => $positionName,
                    'type' => $this->normalizeColumnType((string)($columnMeta['type'] ?? 'typical')),
                    'ordering' => $columnOrdering,
                    'tag' => trim((string)($columnMeta['tag'] ?? 'div')) ?: 'div',
                    'class' => $this->normalizeNullableString($columnMeta['class'] ?? null),
                    'wrapper' => $this->normalizeNullableString($columnMeta['wrapper'] ?? null),
                    'options' => cmsModel::arrayToString($this->applyWidthToColumnOptions($columnOptions, (int)($column['width'] ?? 12)))
                ];

                $columnId = $this->insert('layout_cols', $columnData, true);
                $publishContext['column_positions'][(string)($column['uid'] ?? '')] = $positionName;
                $publishContext['published_columns']++;

                $nestedRows = is_array($column['nested_rows'] ?? null) ? $column['nested_rows'] : [];
                if ($nestedRows) {
                    $this->insertPublishedRows($nestedRows, $templateName, (int)$columnId, $publishContext);
                }

                $columnOrdering++;
            }

            $ordering++;
        }
    }

    protected function publishHomepageBindings(modelBackendWidgets $backendWidgetsModel, string $templateName, array $rows, array $columnPositions): array {

        $publishedWidgets = 0;

        foreach ($rows as $row) {
            if (!is_array($row) || !empty($row['hidden'])) {
                continue;
            }

            $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
            foreach ($columns as $column) {
                if (!is_array($column) || !empty($column['hidden'])) {
                    continue;
                }

                $columnUid = (string)($column['uid'] ?? '');
                $positionName = $columnPositions[$columnUid] ?? '';
                if ($positionName === '') {
                    continue;
                }

                $widgets = is_array($column['widgets'] ?? null) ? $column['widgets'] : [];
                foreach ($widgets as $widget) {
                    if (!is_array($widget) || !empty($widget['hidden'])) {
                        continue;
                    }

                    $bindId = (int)($widget['bind_id'] ?? 0);
                    if ($bindId > 0 && $this->bindingExists($bindId)) {
                        $backendWidgetsModel->addWidgetBindPage($bindId, 1, $positionName, $templateName, null, 1);
                        $publishedWidgets++;
                        continue;
                    }

                    $widgetId = (int)($widget['widget_id'] ?? 0);
                    if ($widgetId < 1) {
                        $widgetId = $this->getWidgetDefinitionId((string)($widget['widget_controller'] ?? ''), (string)($widget['widget_name'] ?? ''));
                    }

                    if ($widgetId < 1) {
                        throw new RuntimeException('Не найден widget_id для виджета «' . trim((string)($widget['title'] ?? 'Виджет')) . '».');
                    }

                    $created = $backendWidgetsModel->addWidgetBinding([
                        'id' => $widgetId,
                        'title' => trim((string)($widget['title'] ?? 'Виджет')) ?: 'Виджет'
                    ], 1, $positionName, $templateName);

                    if (!$created || empty($created['id'])) {
                        throw new RuntimeException('Не удалось создать native binding для виджета «' . trim((string)($widget['title'] ?? 'Виджет')) . '».');
                    }

                    $publishedWidgets++;
                }

                $nestedRows = is_array($column['nested_rows'] ?? null) ? $column['nested_rows'] : [];
                if ($nestedRows) {
                    $nestedStats = $this->publishHomepageBindings($backendWidgetsModel, $templateName, $nestedRows, $columnPositions);
                    $publishedWidgets += (int)($nestedStats['widgets'] ?? 0);
                }
            }
        }

        return ['widgets' => $publishedWidgets];
    }

    protected function syncInheritedTemplateBindings(modelBackendWidgets $backendWidgetsModel, string $templateName, string $sourceTemplate, array $allowedPositions): int {

        $sourceTemplate = trim($sourceTemplate);
        $allowedPositions = array_values(array_unique(array_filter(array_map('strval', $allowedPositions))));

        if ($sourceTemplate === '' || $sourceTemplate === $templateName || !$allowedPositions) {
            return 0;
        }

        $this->clearTemplateBindings($backendWidgetsModel, $templateName, function (array $bindingPage): bool {
            return (int)($bindingPage['page_id'] ?? 0) !== 1;
        });

        return $this->copyTemplateBindingsFromSource($backendWidgetsModel, $sourceTemplate, $templateName, $allowedPositions, false);
    }

    protected function clearTemplateBindings(modelBackendWidgets $backendWidgetsModel, string $templateName, ?callable $shouldDelete = null): array {

        $this->resetFilters();
        $existingBindings = $this->filterEqual('template', $templateName)->get('widgets_bind_pages', false) ?: [];

        $bindIds = [];
        $deleted = 0;

        foreach ($existingBindings as $bindingPage) {
            if ($shouldDelete && !$shouldDelete($bindingPage)) {
                continue;
            }

            $bindingPageId = (int)($bindingPage['id'] ?? 0);
            if ($bindingPageId < 1) {
                continue;
            }

            $bindId = (int)($bindingPage['bind_id'] ?? 0);
            if ($bindId > 0) {
                $bindIds[$bindId] = $bindId;
            }

            $backendWidgetsModel->deleteWidgetPageBind($bindingPageId);
            $deleted++;
        }

        foreach ($bindIds as $bindId) {
            $this->resetFilters();
            $bindPagesCount = (int)$this->filterEqual('bind_id', $bindId)->getCount('widgets_bind_pages', 'id', true);
            if ($bindPagesCount < 1) {
                $this->delete('widgets_bind', $bindId);
            }
        }

        return [
            'bindings' => $deleted,
            'bind_ids' => array_values($bindIds)
        ];
    }

    protected function copyTemplateBindingsFromSource(modelBackendWidgets $backendWidgetsModel, string $sourceTemplate, string $targetTemplate, array $allowedPositions, bool $includeHomepage): int {

        $allowedPositions = array_values(array_unique(array_filter(array_map('strval', $allowedPositions))));
        if (!$allowedPositions) {
            return 0;
        }

        $this->resetFilters();
        $sourceBindings = $this->filterEqual('template', $sourceTemplate)->get('widgets_bind_pages', false) ?: [];

        usort($sourceBindings, function (array $left, array $right): int {
            $leftPageId = (int)($left['page_id'] ?? 0);
            $rightPageId = (int)($right['page_id'] ?? 0);
            if ($leftPageId !== $rightPageId) {
                return $leftPageId <=> $rightPageId;
            }

            $leftOrdering = (int)($left['ordering'] ?? 0);
            $rightOrdering = (int)($right['ordering'] ?? 0);

            return $leftOrdering <=> $rightOrdering;
        });

        $copied = 0;

        foreach ($sourceBindings as $bindingPage) {
            $pageId = (int)($bindingPage['page_id'] ?? 0);
            $position = trim((string)($bindingPage['position'] ?? ''));
            $bindId = (int)($bindingPage['bind_id'] ?? 0);

            if (!$includeHomepage && $pageId === 1) {
                continue;
            }

            if ($position === '' || !in_array($position, $allowedPositions, true)) {
                continue;
            }

            if ($bindId < 1 || !$this->bindingExists($bindId)) {
                continue;
            }

            $backendWidgetsModel->addWidgetBindPage(
                $bindId,
                $pageId,
                $position,
                $targetTemplate,
                isset($bindingPage['ordering']) ? (int)$bindingPage['ordering'] : null,
                (int)($bindingPage['is_enabled'] ?? 1)
            );
            $copied++;
        }

        return $copied;
    }

    protected function copyTemplateLayoutFromSource(string $sourceTemplate, string $targetTemplate): array {

        $this->resetFilters();
        $sourceRows = $this->filterEqual('template', $sourceTemplate)->get('layout_rows', false) ?: [];
        if (!$sourceRows) {
            return ['rows' => 0, 'columns' => 0, 'positions' => []];
        }

        $sourceRowIds = [];
        foreach ($sourceRows as $row) {
            $rowId = (int)($row['id'] ?? 0);
            if ($rowId > 0) {
                $sourceRowIds[] = $rowId;
            }
        }

        $this->resetFilters();
        $sourceCols = $sourceRowIds ? ($this->filterIn('row_id', $sourceRowIds)->get('layout_cols', false) ?: []) : [];

        $rowsByParentColumn = [];
        foreach ($sourceRows as $row) {
            $parentColumnId = (int)($row['parent_id'] ?? 0);
            $rowsByParentColumn[$parentColumnId][] = $row;
        }

        foreach ($rowsByParentColumn as &$rowsGroup) {
            usort($rowsGroup, function (array $left, array $right): int {
                $leftOrdering = (int)($left['ordering'] ?? 0);
                $rightOrdering = (int)($right['ordering'] ?? 0);
                if ($leftOrdering !== $rightOrdering) {
                    return $leftOrdering <=> $rightOrdering;
                }

                return (int)($left['id'] ?? 0) <=> (int)($right['id'] ?? 0);
            });
        }
        unset($rowsGroup);

        $colsByRowId = [];
        foreach ($sourceCols as $column) {
            $rowId = (int)($column['row_id'] ?? 0);
            $colsByRowId[$rowId][] = $column;
        }

        foreach ($colsByRowId as &$columnsGroup) {
            usort($columnsGroup, function (array $left, array $right): int {
                $leftOrdering = (int)($left['ordering'] ?? 0);
                $rightOrdering = (int)($right['ordering'] ?? 0);
                if ($leftOrdering !== $rightOrdering) {
                    return $leftOrdering <=> $rightOrdering;
                }

                return (int)($left['id'] ?? 0) <=> (int)($right['id'] ?? 0);
            });
        }
        unset($columnsGroup);

        $copiedRows = 0;
        $copiedColumns = 0;
        $positions = [];
        $newColumnIds = [];

        $copyRows = function (int $sourceParentColumnId, ?int $targetParentColumnId) use (&$copyRows, &$copiedRows, &$copiedColumns, &$positions, &$newColumnIds, $rowsByParentColumn, $colsByRowId, $targetTemplate) {
            $rows = $rowsByParentColumn[$sourceParentColumnId] ?? [];

            foreach ($rows as $row) {
                $sourceRowId = (int)($row['id'] ?? 0);
                $rowData = $row;
                unset($rowData['id']);
                $rowData['template'] = $targetTemplate;
                $rowData['parent_id'] = $targetParentColumnId;
                if (!$targetParentColumnId) {
                    $rowData['nested_position'] = null;
                }

                $newRowId = $this->insert('layout_rows', $rowData, true);
                $copiedRows++;

                $columns = $colsByRowId[$sourceRowId] ?? [];
                foreach ($columns as $column) {
                    $sourceColumnId = (int)($column['id'] ?? 0);
                    $columnData = $column;
                    unset($columnData['id']);
                    $columnData['row_id'] = $newRowId;

                    $newColumnId = $this->insert('layout_cols', $columnData, true);
                    $newColumnIds[$sourceColumnId] = $newColumnId;
                    $positions[] = trim((string)($column['name'] ?? ''));
                    $copiedColumns++;
                }

                foreach ($columns as $column) {
                    $sourceColumnId = (int)($column['id'] ?? 0);
                    if (!isset($rowsByParentColumn[$sourceColumnId])) {
                        continue;
                    }

                    $copyRows($sourceColumnId, (int)($newColumnIds[$sourceColumnId] ?? 0));
                }
            }
        };

        $copyRows(0, null);

        return [
            'rows' => $copiedRows,
            'columns' => $copiedColumns,
            'positions' => array_values(array_unique(array_filter($positions)))
        ];
    }

    protected function deleteLayoutStateByTemplate(string $templateName): void {

        if (!$this->ensureLayoutStateTableExists()) {
            return;
        }

        $templateSql = $this->db->escape(trim($templateName));
        if ($templateSql === '') {
            return;
        }

        $this->db->query("DELETE FROM {#}nordicstyl_layout_state WHERE template = '{$templateSql}'", false, true);
    }

    public function resetTemplateToDefault(string $templateName, string $sourceTemplate): array {

        $templateName = trim($templateName);
        $sourceTemplate = trim($sourceTemplate);

        if ($templateName === '' || $sourceTemplate === '') {
            return [
                'ok' => false,
                'message' => 'Не указан шаблон для reset-to-default.'
            ];
        }

        if ($templateName === $sourceTemplate) {
            return [
                'ok' => false,
                'message' => 'Текущий шаблон уже является default source.'
            ];
        }

        $backendWidgetsModel = cmsCore::getModel('backend_widgets', '_', false);
        if (!$backendWidgetsModel) {
            return [
                'ok' => false,
                'message' => 'Model backend_widgets недоступна для reset-to-default.'
            ];
        }

        $autocommitWasEnabled = $this->db->isAutocommitOn();

        try {
            if ($autocommitWasEnabled) {
                $this->db->autocommitOff();
            }

            $this->db->beginTransaction();

            $this->clearTemplateBindings($backendWidgetsModel, $templateName);
            $this->deleteTemplateLayout($templateName);

            $layoutStats = $this->copyTemplateLayoutFromSource($sourceTemplate, $templateName);
            if ((int)$layoutStats['rows'] < 1) {
                throw new RuntimeException('Не удалось скопировать default layout из шаблона ' . $sourceTemplate . '.');
            }

            $bindingsCount = $this->copyTemplateBindingsFromSource(
                $backendWidgetsModel,
                $sourceTemplate,
                $templateName,
                $layoutStats['positions'],
                true
            );

            $this->deleteLayoutStateByTemplate($templateName);

            $this->db->commit();

            if ($autocommitWasEnabled) {
                $this->db->autocommitOn();
            }

            return [
                'ok' => true,
                'message' => 'Шаблон ' . $templateName . ' возвращен к default-схеме из ' . $sourceTemplate . '.',
                'rows' => (int)$layoutStats['rows'],
                'columns' => (int)$layoutStats['columns'],
                'widgets' => $bindingsCount
            ];
        } catch (Throwable $exception) {
            $this->db->rollback();

            if ($autocommitWasEnabled) {
                $this->db->autocommitOn();
            }

            return [
                'ok' => false,
                'message' => 'Не удалось вернуть шаблон к default-схеме.',
                'details' => $exception->getMessage()
            ];
        }
    }

    protected function bindingExists(int $bindId): bool {

        if ($bindId <= 0) {
            return false;
        }

        return (bool)$this->getItemById('widgets_bind', $bindId);
    }

    protected function getWidgetDefinitionId(string $controller, string $widgetName): int {

        $controllerSql = $this->db->escape(trim($controller));
        $nameSql = $this->db->escape(trim($widgetName));

        if ($nameSql === '') {
            return 0;
        }

        $row = $this->db->getRow('widgets', "controller = '{$controllerSql}' AND name = '{$nameSql}'", 'id');

        return (int)($row['id'] ?? 0);
    }

    protected function applyWidthModeToRowOptions(array $options, string $widthMode): array {

        $options = $options ?: $this->getDefaultPublishedRowOptions();
        $widthMode = $widthMode === 'full' ? 'full' : 'grid';

        $options['container'] = $widthMode === 'full' ? '' : 'container';
        $options['container_tag'] = $options['container_tag'] ?? 'div';
        $options['container_tag_class'] = $options['container_tag_class'] ?? '';
        $options['parrent_tag'] = $options['parrent_tag'] ?? '';
        $options['parrent_tag_class'] = $options['parrent_tag_class'] ?? '';

        return $options;
    }

    protected function applyWidthToColumnOptions(array $options, int $width): array {

        $options = $options ?: $this->getDefaultPublishedColumnOptions();
        $width = max(2, min(12, $width));

        $options['default_col_class'] = 'col-12';
        $options['lg_col_class'] = 'col-lg-' . $width;
        $options['md_col_class'] = $options['md_col_class'] ?? '';
        $options['xl_col_class'] = $options['xl_col_class'] ?? '';
        $options['col_class'] = $options['col_class'] ?? '';

        return $options;
    }

    protected function generatePublishedPositionName(array $column, array &$usedPositionNames): string {

        $meta = is_array($column['meta'] ?? null) ? $column['meta'] : [];
        $candidate = trim((string)($meta['position_name'] ?? ''));
        $candidate = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', $candidate));
        $candidate = trim($candidate, '_');

        if ($candidate === '') {
            $candidate = 'pos_' . substr(md5((string)($column['uid'] ?? microtime(true))), 0, 10);
        }

        $positionName = $candidate;
        $suffix = 2;

        while (isset($usedPositionNames[$positionName])) {
            $positionName = $candidate . '_' . $suffix;
            $suffix++;
        }

        $usedPositionNames[$positionName] = true;

        return substr($positionName, 0, 32);
    }

    protected function normalizeNestedPosition(string $nestedPosition): string {

        return $nestedPosition === 'before' ? 'before' : 'after';
    }

    protected function normalizeColumnType(string $columnType): string {

        return $columnType === 'custom' ? 'custom' : 'typical';
    }

    protected function normalizeNullableString($value): ?string {

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    protected function getDefaultPublishedRowOptions(): array {

        return [
            'no_gutters' => null,
            'vertical_align' => '',
            'horizontal_align' => '',
            'container' => 'container',
            'container_tag' => 'div',
            'container_tag_class' => '',
            'parrent_tag' => '',
            'parrent_tag_class' => ''
        ];
    }

    protected function getDefaultPublishedColumnOptions(): array {

        return [
            'cut_before' => null,
            'default_col_class' => 'col-12',
            'md_col_class' => '',
            'lg_col_class' => 'col-lg-12',
            'xl_col_class' => '',
            'col_class' => '',
            'default_order' => 0,
            'sm_order' => 0,
            'md_order' => 0,
            'lg_order' => 0,
            'xl_order' => 0
        ];
    }

    public function getLayoutState(string $templateName, string $targetUri): ?array {

        if (!$this->ensureLayoutStateTableExists()) {
            return null;
        }

        $templateName = trim($templateName);
        $targetUri = $this->normalizeLayoutStateUri($targetUri);

        if ($templateName === '') {
            return null;
        }

        $templateSql = $this->db->escape($templateName);
        $uriSql = $this->db->escape($targetUri);
        $row = $this->db->getRow('nordicstyl_layout_state', "template = '{$templateSql}' AND target_uri = '{$uriSql}'", 'layout_state');
        $payload = trim((string)($row['layout_state'] ?? ''));

        if ($payload === '') {
            return null;
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function saveLayoutState(string $templateName, string $targetUri, array $layoutState): bool {

        if (!$this->ensureLayoutStateTableExists()) {
            return false;
        }

        $templateName = trim($templateName);
        $targetUri = $this->normalizeLayoutStateUri($targetUri);

        if ($templateName === '') {
            return false;
        }

        $payload = json_encode($layoutState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($payload) || $payload === '') {
            return false;
        }

        $templateSql = $this->db->escape($templateName);
        $uriSql = $this->db->escape($targetUri);
        $payloadSql = $this->db->escape($payload);

        return (bool)$this->db->query(
            "INSERT INTO {#}nordicstyl_layout_state (template, target_uri, layout_state, created_at, updated_at)
             VALUES ('{$templateSql}', '{$uriSql}', '{$payloadSql}', NOW(), NOW())
             ON DUPLICATE KEY UPDATE layout_state = VALUES(layout_state), updated_at = VALUES(updated_at)",
            false,
            true
        );
    }

    protected function ensureLayoutStateTableExists(): bool {

        $res = $this->db->query("SHOW TABLES LIKE '{#}nordicstyl_layout_state'", false, true);
        if ($res) {
            $exists = (int)$this->db->numRows($res) > 0;
            $this->db->freeResult($res);
            if ($exists) {
                return true;
            }
        }

        $created = $this->db->query(
            "CREATE TABLE IF NOT EXISTS {#}nordicstyl_layout_state (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                template varchar(64) NOT NULL,
                target_uri varchar(191) NOT NULL DEFAULT '/',
                layout_state mediumtext NOT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY template_uri (template, target_uri)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            false,
            true
        );

        return $created !== false;
    }

    protected function normalizeLayoutStateUri(string $targetUri): string {

        $targetUri = trim($targetUri);

        if ($targetUri === '' || $targetUri === '/') {
            return '/';
        }

        return '/' . ltrim($targetUri, '/');
    }
}
