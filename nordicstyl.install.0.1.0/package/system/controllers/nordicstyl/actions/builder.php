<?php

class actionNordicstylBuilder extends cmsAction {

    public function run() {

        if (!cmsUser::isLogged() || !cmsUser::isAllowed('admin', 'manage_nordicstyl_picker')) {
            return cmsCore::error404();
        }

        $this->cms_template->setPageTitle('Nordic Builder');
        $this->cms_template->setPageH1('Nordic Builder');

        $cssFile = $this->cms_template->getTplFilePath('controllers/nordicstyl/css/builder.css', false);
        $jsFile = $this->cms_template->getTplFilePath('controllers/nordicstyl/js/builder.js', false);

        if ($cssFile) {
            $this->cms_template->addCSS($this->appendAssetVersion($cssFile), false);
        }

        if ($jsFile) {
            $this->cms_template->addJS($this->appendAssetVersion($jsFile), '', false);
        }

        $builderPage = $this->getBuilderPage();

        return $this->cms_template->render('builder', [
            'page_title' => $builderPage['page_title'],
            'builder_state' => [
                'page' => $builderPage['page'],
                'rows' => $builderPage['rows'],
                'layout_state' => $builderPage['layout_state'],
                'page_targets' => $builderPage['page_targets'],
                'device_modes' => $builderPage['device_modes'],
                'widget_library' => $builderPage['widget_library'],
                'status_message' => $builderPage['status_message'],
                'rules_url' => href_to_abs('admin', 'controllers', ['edit', 'nordicstyl', 'rules']),
                'picker_url' => href_to_abs('admin', 'controllers', ['edit', 'nordicstyl', 'picker']),
                'state_url' => href_to_abs('nordicstyl', 'builder_state'),
                'publish_url' => href_to_abs('nordicstyl', 'builder_publish'),
                'csrf_token' => cmsForm::getCSRFToken()
            ]
        ], $this->request);
    }

    protected function appendAssetVersion(string $assetPath): string {

        $fullPath = cmsConfig::get('root_path') . ltrim($assetPath, '/');
        $version = is_file($fullPath) ? (string) filemtime($fullPath) : (string) time();

        return $assetPath . '?v=' . $version;
    }

    protected function getBuilderPage(): array {

        $activeTemplate = (string) $this->cms_template->getName();
        $targetUri = $this->resolveTargetUri();
        $currentDevice = $this->resolveDeviceMode();
        $layoutSource = $this->resolveLayoutSourceTemplate($activeTemplate);
        $widgetState = $this->getWidgetState($activeTemplate, $layoutSource, $targetUri);
        $pageTitle = $this->detectPageTitle($targetUri);
        $rows = $this->normalizeRows($this->getLayoutRows($layoutSource), $widgetState['items']);
        $initialLayoutState = $this->buildInitialLayoutState($rows);

        return [
            'page_title' => $pageTitle,
            'page' => [
                'title' => $pageTitle,
                'template' => $activeTemplate,
                'layout_source' => $layoutSource,
                'layout_source_label' => $this->buildLayoutSourceLabel($activeTemplate, $layoutSource),
                'widgets_source' => $widgetState['template'],
                'mode' => 'frontend-editor',
                'uri' => $this->getCurrentUriLabel($targetUri),
                'device' => $currentDevice,
                'device_label' => $this->buildDeviceLabel($currentDevice)
            ],
            'rows' => $rows,
            'layout_state' => $this->getStoredLayoutState($activeTemplate, $targetUri, $initialLayoutState),
            'page_targets' => $this->getPageTargets($targetUri, $currentDevice),
            'device_modes' => $this->getDeviceModes($targetUri, $currentDevice),
            'widget_library' => $this->getWidgetLibrary(),
            'status_message' => $this->buildStatusMessage($activeTemplate, $layoutSource, $widgetState['template'], count($rows), $pageTitle, $currentDevice)
        ];
    }

    protected function buildInitialLayoutState(array $rows): array {

        return [
            'desktop' => [
                'rows' => $rows,
                'overrides' => []
            ],
            'tablet' => [
                'rows' => null,
                'overrides' => []
            ],
            'mobile' => [
                'rows' => null,
                'overrides' => []
            ]
        ];
    }

    protected function getStoredLayoutState(string $activeTemplate, string $targetUri, array $initialLayoutState): array {

        $model = cmsCore::getModel('nordicstyl', '_', false);
        if (!$model || !method_exists($model, 'getLayoutState')) {
            return $initialLayoutState;
        }

        $storedLayoutState = $model->getLayoutState($activeTemplate, $targetUri);
        if (!$storedLayoutState || !is_array($storedLayoutState)) {
            return $initialLayoutState;
        }

        return array_replace_recursive($initialLayoutState, $storedLayoutState);
    }

    protected function resolveDeviceMode(): string {

        $device = trim((string) $this->request->get('device', 'desktop'));

        return in_array($device, ['desktop', 'tablet', 'mobile'], true) ? $device : 'desktop';
    }

    protected function getPageTargets(string $targetUri, string $device): array {

        $targets = [[
            'key' => 'homepage',
            'title' => 'Главная страница',
            'uri' => '/',
            'url' => $this->buildBuilderUrl('/', $device),
            'is_active' => $targetUri === '/'
        ]];

        $pages = cmsCore::getModel('widgets')->getPages();
        $dynamicTargets = [];
        $seenUris = ['/'];

        foreach ($pages as $page) {
            if (!is_array($page)) {
                continue;
            }

            $uri = $this->buildWidgetsPagePreviewUri($page);
            if ($uri === '' || in_array($uri, $seenUris, true)) {
                continue;
            }

            $dynamicTargets[] = [
                'key' => trim((string) ($page['name'] ?? '')),
                'title' => $this->resolveWidgetsPageTitle($page),
                'uri' => $uri,
                'url' => $this->buildBuilderUrl($uri, $device),
                'is_active' => $targetUri === $uri
            ];

            $seenUris[] = $uri;
        }

        usort($dynamicTargets, function(array $left, array $right) {
            return strcmp($left['title'], $right['title']);
        });

        $targets = array_merge($targets, $dynamicTargets);

        if (!in_array($targetUri, $seenUris, true)) {
            $targets[] = [
                'key' => 'current-target',
                'title' => $this->detectPageTitle($targetUri),
                'uri' => $targetUri,
                'url' => $this->buildBuilderUrl($targetUri, $device),
                'is_active' => true
            ];
        }

        return $targets;
    }

    protected function getDeviceModes(string $targetUri, string $currentDevice): array {

        $modes = [];

        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            $modes[] = [
                'key' => $device,
                'title' => $this->buildDeviceLabel($device),
                'url' => $this->buildBuilderUrl($targetUri, $device),
                'is_active' => $device === $currentDevice
            ];
        }

        return $modes;
    }

    protected function getWidgetLibrary(): array {

        $model = cmsCore::getModel('backend_widgets', '_', false);
        if (!$model || !method_exists($model, 'getAvailableWidgets')) {
            return [
                'categories' => [
                    ['key' => 'all', 'title' => 'Все', 'count' => 0]
                ],
                'items' => []
            ];
        }

        $availableWidgets = $model->getAvailableWidgets();
        if (!$availableWidgets || !is_array($availableWidgets)) {
            return [
                'categories' => [
                    ['key' => 'all', 'title' => 'Все', 'count' => 0]
                ],
                'items' => []
            ];
        }

        $categories = [];
        $items = [];

        foreach ($availableWidgets as $controllerKey => $widgets) {
            if (!is_array($widgets)) {
                continue;
            }

            $categoryKey = $this->normalizeWidgetCategoryKey((string) $controllerKey);
            $categoryTitle = $this->buildWidgetCategoryTitle((string) $controllerKey, $widgets);
            $categoryCount = 0;

            foreach ($widgets as $widget) {
                if (!is_array($widget)) {
                    continue;
                }

                $widgetName = trim((string) ($widget['name'] ?? ''));
                $widgetController = trim((string) ($widget['controller'] ?? ''));
                $title = trim((string) ($widget['title'] ?? ''));

                if ($title === '') {
                    $title = $widgetName !== '' ? $this->humanizeLabel($widgetName) : 'Виджет';
                }

                $items[] = [
                    'uid' => 'lib-' . md5($categoryKey . '|' . $widgetController . '|' . $widgetName . '|' . $title),
                    'title' => $title,
                    'category_key' => $categoryKey,
                    'category_title' => $categoryTitle,
                    'source' => $widgetController === '' ? 'system' : $this->humanizeLabel($widgetController),
                    'widget_id' => (int)($widget['id'] ?? 0),
                    'widget_name' => $widgetName,
                    'widget_controller' => $widgetController,
                    'description' => trim((string) ($widget['description'] ?? '')),
                    'image_hint_path' => trim((string) ($widget['image_hint_path'] ?? '')),
                    'has_options' => !empty($widget['has_options']) || !empty($widget['options'])
                ];

                $categoryCount++;
            }

            if ($categoryCount > 0) {
                $categories[] = [
                    'key' => $categoryKey,
                    'title' => $categoryTitle,
                    'count' => $categoryCount
                ];
            }
        }

        usort($categories, function(array $left, array $right) {
            return strcmp($left['title'], $right['title']);
        });

        array_unshift($categories, [
            'key' => 'all',
            'title' => 'Все',
            'count' => count($items)
        ]);

        return [
            'categories' => $categories,
            'items' => $items
        ];
    }

    protected function normalizeWidgetCategoryKey(string $controllerKey): string {

        $controllerKey = trim($controllerKey);

        if ($controllerKey === '' || $controllerKey === '0') {
            return 'system';
        }

        return preg_replace('/[^a-z0-9\-_]+/i', '-', strtolower($controllerKey));
    }

    protected function buildWidgetCategoryTitle(string $controllerKey, array $widgets): string {

        $controllerKey = trim($controllerKey);

        if ($controllerKey === '' || $controllerKey === '0') {
            return 'Системные';
        }

        foreach ($widgets as $widget) {
            $controllerTitle = trim((string) ($widget['controller_title'] ?? ''));
            if ($controllerTitle !== '') {
                return $controllerTitle;
            }
        }

        return $this->humanizeLabel($controllerKey);
    }

    protected function resolveTargetUri(): string {

        $targetUri = trim((string) $this->request->get('uri', ''), '/');

        return $targetUri === '' ? '/' : '/' . $targetUri;
    }

    protected function resolveLayoutSourceTemplate(string $activeTemplate): string {

        $templateNames = $this->cms_template->getInheritNames();

        if (!in_array($activeTemplate, $templateNames, true)) {
            array_unshift($templateNames, $activeTemplate);
        }

        foreach ($templateNames as $templateName) {
            if (in_array($templateName, ['default', 'admincoreui'], true)) {
                continue;
            }

            if ($this->getLayoutRows($templateName)) {
                return $templateName;
            }
        }

        return $activeTemplate;
    }

    protected function getWidgetState(string $activeTemplate, string $layoutSource, string $targetUri): array {

        $templates = array_values(array_unique([$activeTemplate, $layoutSource]));

        foreach ($templates as $templateName) {
            $widgets = $this->getWidgetsByPosition($templateName, $targetUri);
            if ($widgets) {
                return [
                    'template' => $templateName,
                    'items' => $widgets
                ];
            }
        }

        return [
            'template' => $activeTemplate,
            'items' => []
        ];
    }

    protected function getLayoutRows(string $templateName): array {

        $rows = cmsCore::getModel('widgets')->getLayoutRows($templateName);

        return is_array($rows) ? $rows : [];
    }

    protected function getWidgetsByPosition(string $templateName, string $targetUri): array {

        $pageIds = $this->detectTargetPageIds($targetUri);

        if (!$pageIds) {
            return [];
        }

        $bindings = cmsCore::getModel('widgets')->getWidgetsForPages($pageIds, $templateName);
        $widgetsByPosition = [];

        foreach ($bindings as $binding) {

            $position = (string) ($binding['position'] ?? '');

            if ($position === '') {
                continue;
            }

            $widgetsByPosition[$position][] = $this->buildWidgetStateItem($binding, $position);
        }

        return $widgetsByPosition;
    }

    protected function detectTargetPageIds(string $targetUri): array {

        $matchedPages = $this->detectTargetPages($targetUri);

        return $matchedPages ? array_keys($matchedPages) : [];
    }

    protected function detectTargetPages(string $targetUri): array {

        $core = cmsCore::getInstance()->loadMatchedPages();
        $pages = $core->getWidgetsPages();

        if (!$pages) {
            return [];
        }

        $targetPath = $targetUri === '/' ? '' : ltrim($targetUri, '/');

        return $core->detectMatchedWidgetPages($pages, $targetPath);
    }

    protected function normalizeRows(array $rows, array $widgetsByPosition): array {

        $builderRows = [];
        $rowNumber = 1;

        foreach ($rows as $row) {
            $builderRows[] = $this->normalizeRow($row, $widgetsByPosition, $rowNumber);
            $rowNumber++;
        }

        return $builderRows;
    }

    protected function normalizeRow(array $row, array $widgetsByPosition, int $rowNumber): array {

        $columns = [];
        $rowColumns = array_values($row['cols'] ?? []);
        $columnsCount = count($rowColumns);
        $columnNumber = 1;

        foreach ($rowColumns as $column) {
            $columns[] = $this->normalizeColumn($column, $widgetsByPosition, $columnsCount, $columnNumber);
            $columnNumber++;
        }

        return [
            'uid' => 'row-' . ($row['id'] ?? md5(serialize($row))),
            'title' => $this->buildRowTitle($row, $rowNumber),
            'kind' => 'row',
            'width_mode' => $this->detectRowWidthMode($row),
            'meta' => [
                'row_id' => (int)($row['id'] ?? 0),
                'parent_id' => (int)($row['parent_id'] ?? 0),
                'nested_position' => trim((string)($row['nested_position'] ?? '')),
                'tag' => trim((string)($row['tag'] ?? '')),
                'class' => trim((string)($row['class'] ?? '')),
                'options' => is_array($row['options'] ?? null) ? $row['options'] : []
            ],
            'columns' => $columns
        ];
    }

    protected function normalizeColumn(array $column, array $widgetsByPosition, int $columnsCount, int $columnNumber): array {

        $positionName = (string) ($column['name'] ?? '');
        $widgets = $positionName && isset($widgetsByPosition[$positionName]) ? $widgetsByPosition[$positionName] : [];

        return [
            'uid' => 'column-' . ($column['id'] ?? md5(serialize($column))),
            'title' => $this->buildColumnTitle($column, $columnNumber),
            'width' => $this->detectColumnWidth($column, $columnsCount),
            'meta' => [
                'col_id' => (int)($column['id'] ?? 0),
                'position_name' => $positionName,
                'type' => trim((string)($column['type'] ?? 'typical')),
                'tag' => trim((string)($column['tag'] ?? '')),
                'class' => trim((string)($column['class'] ?? '')),
                'wrapper' => (string)($column['wrapper'] ?? ''),
                'options' => is_array($column['options'] ?? null) ? $column['options'] : []
            ],
            'widgets' => $widgets,
            'nested_rows' => $this->normalizeNestedRows($column['rows'] ?? [], $widgetsByPosition)
        ];
    }

    protected function normalizeNestedRows(array $nestedRows, array $widgetsByPosition): array {

        $builderRows = [];
        $rowNumber = 1;

        foreach ($nestedRows as $positionRows) {
            foreach ($positionRows as $row) {
                $builderRows[] = $this->normalizeRow($row, $widgetsByPosition, $rowNumber);
                $rowNumber++;
            }
        }

        return $builderRows;
    }

    protected function detectColumnWidth(array $column, int $columnsCount): int {

        $classCandidates = [];
        $options = is_array($column['options'] ?? null) ? $column['options'] : [];

        foreach (['default_col_class', 'md_col_class', 'lg_col_class', 'xl_col_class', 'col_class'] as $optionKey) {
            if (!empty($options[$optionKey])) {
                $classCandidates[] = (string) $options[$optionKey];
            }
        }

        if (!empty($column['class'])) {
            $classCandidates[] = (string) $column['class'];
        }

        foreach ($classCandidates as $classList) {
            if (preg_match('/\bcol(?:-(?:sm|md|lg|xl))?-(1[0-2]|[1-9])\b/', $classList, $matches)) {
                return (int) $matches[1];
            }
        }

        if ($columnsCount > 0) {
            return max(2, min(12, (int) round(12 / $columnsCount)));
        }

        return 12;
    }

    protected function detectRowWidthMode(array $row): string {

        $options = is_array($row['options'] ?? null) ? $row['options'] : [];
        $container = trim((string)($options['container'] ?? 'container'));

        return $container === '' ? 'full' : 'grid';
    }

    protected function buildRowTitle(array $row, int $rowNumber): string {

        $title = trim((string) ($row['title'] ?? ''));

        if ($title !== '') {
            return $title;
        }

        return 'Ряд ' . $rowNumber;
    }

    protected function buildColumnTitle(array $column, int $columnNumber): string {

        $title = trim((string) ($column['title'] ?? ''));

        if ($title !== '') {
            return $title;
        }

        $positionName = trim((string) ($column['name'] ?? ''));

        if ($positionName !== '') {
            return $this->humanizeLabel($positionName);
        }

        return 'Колонка ' . $columnNumber;
    }

    protected function buildWidgetTitle(array $binding): string {

        foreach (['title', 'name'] as $key) {
            $value = trim((string) ($binding[$key] ?? ''));
            if ($value !== '') {
                return $this->humanizeLabel($value);
            }
        }

        $controller = trim((string) ($binding['controller'] ?? ''));

        if ($controller !== '') {
            return $this->humanizeLabel($controller);
        }

        return 'Виджет';
    }

    protected function buildWidgetSource(array $binding): string {

        $controller = trim((string) ($binding['controller'] ?? ''));

        if ($controller === '' || $controller === 'core') {
            return 'system';
        }

        return $this->humanizeLabel($controller);
    }

    protected function buildWidgetStateItem(array $binding, string $position): array {

        return [
            'uid' => 'widget-' . ($binding['id'] ?? md5($position . '|' . serialize($binding))),
            'title' => $this->buildWidgetTitle($binding),
            'kind' => 'widget',
            'source' => $this->buildWidgetSource($binding),
            'widget_name' => trim((string)($binding['name'] ?? '')),
            'widget_controller' => trim((string)($binding['controller'] ?? '')),
            'widget_id' => (int)($binding['widget_id'] ?? 0),
            'bind_id' => (int)($binding['bind_id'] ?? 0),
            'binding_page_id' => (int)($binding['id'] ?? 0),
            'position_name' => $position,
            'is_enabled' => !empty($binding['is_enabled'])
        ];
    }

    protected function buildLayoutSourceLabel(string $activeTemplate, string $layoutSource): string {

        if ($activeTemplate === $layoutSource) {
            return 'схема текущего шаблона';
        }

        return 'унаследованная схема ' . $layoutSource;
    }

    protected function buildStatusMessage(string $activeTemplate, string $layoutSource, string $widgetsSource, int $rowsCount, string $pageTitle, string $device): string {

        if ($rowsCount < 1) {
            return 'Builder не нашел сохраненную layout-схему для шаблонов ' . $activeTemplate . ' и его родительской цепочки.';
        }

        $message = 'Canvas читает реальную схему InstantCMS: ' . $rowsCount . ' ряд(ов) из шаблона ' . $layoutSource . '.';
        $message .= ' Контекст: ' . $pageTitle . ' • ' . $this->buildDeviceLabel($device) . '.';

        if ($widgetsSource !== $layoutSource) {
            $message .= ' Текущие widget bindings подтянуты из ' . $widgetsSource . '.';
        }

        $message .= ' Следующий шаг: вставка и сохранение структуры прямо с фронта.';

        return $message;
    }

    protected function detectPageTitle(string $targetUri): string {

        $matchedPages = $this->detectTargetPages($targetUri);

        if ($matchedPages) {
            foreach ($matchedPages as $page) {
                if (!empty($page['title']) && (int) ($page['id'] ?? 0) > 1) {
                    return (string) $page['title'];
                }
            }

            foreach ($matchedPages as $page) {
                if (!empty($page['title']) && (int) ($page['id'] ?? 0) === 1) {
                    return (string) $page['title'];
                }
            }
        }

        return $targetUri === '/' ? 'Главная страница' : 'Страница ' . $targetUri;
    }

    protected function getCurrentUriLabel(string $targetUri): string {
        return $targetUri;
    }

    protected function buildDeviceLabel(string $device): string {

        $labels = [
            'desktop' => 'Desktop',
            'tablet' => 'Tablet',
            'mobile' => 'Mobile'
        ];

        return $labels[$device] ?? 'Desktop';
    }

    protected function resolveWidgetsPageTitle(array $page): string {

        if ((int) ($page['id'] ?? 0) === 1) {
            return 'Главная страница';
        }

        $title = trim((string) ($page['title'] ?? ''));
        if ($title !== '') {
            return $title;
        }

        $titleConst = trim((string) ($page['title_const'] ?? ''));
        $titleSubject = trim((string) ($page['title_subject'] ?? ''));

        if ($titleConst !== '' && defined($titleConst)) {
            $pattern = (string) constant($titleConst);

            if ($titleSubject !== '' && strpos($pattern, '%') !== false) {
                return sprintf($pattern, $titleSubject);
            }

            if (strpos($pattern, '%') === false) {
                return $pattern;
            }
        }

        $name = trim((string) ($page['name'] ?? ''));

        if ($name !== '') {
            return $this->humanizeLabel(str_replace('.', ' ', $name));
        }

        return 'Страница';
    }

    protected function buildWidgetsPagePreviewUri(array $page): string {

        if ((int) ($page['id'] ?? 0) === 1) {
            return '/';
        }

        $masks = $page['url_mask'] ?? [];
        if (is_string($masks)) {
            $masks = [$masks];
        }

        foreach ($masks as $mask) {
            $uri = $this->normalizePreviewMask((string) $mask);
            if ($uri !== '') {
                return $uri;
            }
        }

        return '';
    }

    protected function normalizePreviewMask(string $mask): string {

        $mask = trim($mask);

        if ($mask === '') {
            return '';
        }

        $mask = preg_replace('/\?.*$/', '', $mask);
        $mask = str_replace('%', 'demo', $mask);
        $mask = str_replace('*', 'demo', $mask);
        $mask = preg_replace('#/+#', '/', $mask);
        $mask = trim($mask, '/');

        return $mask === '' ? '/' : '/' . $mask;
    }

    protected function buildBuilderUrl(string $targetUri, string $device): string {

        $params = [];

        if ($targetUri !== '/') {
            $params['uri'] = ltrim($targetUri, '/');
        }

        if ($device !== 'desktop') {
            $params['device'] = $device;
        }

        $url = href_to_abs('nordicstyl', 'builder');

        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    protected function humanizeLabel(string $value): string {

        $value = trim(preg_replace('/[\-_]+/', ' ', $value));
        $value = trim(preg_replace('/\s+/', ' ', $value));

        if ($value === '') {
            return '';
        }

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords(strtolower($value));
    }
}
