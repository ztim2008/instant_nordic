<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/ManagedScaffoldRegistry.php';

class NordicblocksDataSourceResolver {

    private static $content_item_modes = [
        'current' => 'Текущая запись страницы',
        'by_id'   => 'Запись по ID',
        'latest'  => 'Последняя запись',
    ];

    private static $sort_options = [
        'date_pub_desc' => ['label' => 'Сначала новые', 'field' => 'date_pub', 'direction' => 'desc'],
        'date_pub_asc'  => ['label' => 'Сначала старые', 'field' => 'date_pub', 'direction' => 'asc'],
        'title_asc'     => ['label' => 'Заголовок A-Z', 'field' => 'title', 'direction' => 'asc'],
        'title_desc'    => ['label' => 'Заголовок Z-A', 'field' => 'title', 'direction' => 'desc'],
        'hits_desc'     => ['label' => 'По просмотрам', 'field' => 'hits_count', 'direction' => 'desc'],
        'hits_asc'      => ['label' => 'Просмотры по возрастанию', 'field' => 'hits_count', 'direction' => 'asc'],
        'comments_desc' => ['label' => 'По комментариям', 'field' => 'comments', 'direction' => 'desc'],
        'comments_asc'  => ['label' => 'Комментарии по возрастанию', 'field' => 'comments', 'direction' => 'asc'],
    ];

    public static function resolve(array $contract, array $context = []) {
        $resolved = [
            'active'     => false,
            'source'     => [],
            'record'     => [],
            'listSource' => [],
            'listItems'  => [],
        ];

        $block_type = (string) ($contract['meta']['blockType'] ?? '');
        if ($block_type === 'hero' || NordicblocksManagedScaffoldRegistry::supportsContentItem($block_type)) {
            $source = is_array($contract['data']['source'] ?? null) ? $contract['data']['source'] : [];
            if (($source['type'] ?? 'manual') !== 'content_item' || empty($source['ctype'])) {
                return $resolved;
            }

            $resolved['active'] = true;
            $resolved['source'] = $source;
            $resolved['record'] = self::resolveContentItem($source, $context);

            return $resolved;
        }

        if (!in_array($block_type, ['faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            && !NordicblocksManagedScaffoldRegistry::supportsContentList($block_type)) {
            return $resolved;
        }

        $list_source = is_array($contract['data']['listSource'] ?? null) ? $contract['data']['listSource'] : [];
        if (($list_source['type'] ?? 'manual') !== 'content_list' || empty($list_source['ctype'])) {
            return $resolved;
        }

        $resolved['active'] = true;
        $resolved['listSource'] = $list_source;
        $resolved['listItems'] = self::resolveContentList($list_source, $context);

        return $resolved;
    }

    public static function buildEditorOptions($block_type) {
        $options = [
            'contentTypes' => [],
            'fieldsByType' => [],
            'sourceModes'  => [
                ['value' => 'manual', 'label' => 'Ручной контент'],
                ['value' => 'content_item', 'label' => 'Одна запись InstantCMS'],
            ],
            'itemResolverModes' => [],
            'listModes'    => [
                ['value' => 'manual', 'label' => 'Ручной список'],
                ['value' => 'content_list', 'label' => 'Список записей InstantCMS'],
            ],
            'sortOptions'  => [],
        ];

        $block_type = (string) $block_type;
        if (!in_array($block_type, ['hero', 'faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            && !NordicblocksManagedScaffoldRegistry::isManagedType($block_type)) {
            return $options;
        }

        if ($block_type === 'hero' || NordicblocksManagedScaffoldRegistry::supportsContentItem($block_type)) {
            foreach (self::$content_item_modes as $key => $label) {
                $options['itemResolverModes'][] = ['value' => $key, 'label' => $label];
            }
        }

        if (in_array($block_type, ['faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            || NordicblocksManagedScaffoldRegistry::supportsContentList($block_type)) {
            foreach (self::$sort_options as $key => $sort) {
                $options['sortOptions'][] = ['value' => $key, 'label' => $sort['label']];
            }
        }

        $content_model = cmsCore::getModel('content');
        if (!$content_model || !method_exists($content_model, 'getContentTypes')) {
            return $options;
        }

        foreach ((array) $content_model->getContentTypes() as $ctype) {
            $name = trim((string) ($ctype['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $title = trim((string) ($ctype['title'] ?? ''));
            if ($title === '' && !empty($ctype['labels']['one'])) {
                $title = trim((string) $ctype['labels']['one']);
            }

            $options['contentTypes'][] = [
                'name'  => $name,
                'title' => $title !== '' ? $title : $name,
            ];
            $options['fieldsByType'][$name] = self::buildFieldOptions($content_model, $name);
        }

        return $options;
    }

    private static function resolveContentList(array $config, array $context = []) {
        $content_model = cmsCore::getModel('content');
        if (!$content_model || !method_exists($content_model, 'getContentTypeByName')) {
            return [];
        }

        $ctype_name = (string) ($config['ctype'] ?? '');
        if ($ctype_name === '' || !$content_model->getContentTypeByName($ctype_name)) {
            return [];
        }

        $sort_key = (string) ($config['sort'] ?? 'date_pub_desc');
        $sort = self::$sort_options[$sort_key] ?? self::$sort_options['date_pub_desc'];

        $content_model->orderBy($sort['field'], $sort['direction']);
        $content_model->limit((int) ($config['limit'] ?? 3));

        $items = $content_model->getContentItems($ctype_name);
        if (!is_array($items)) {
            return [];
        }

        foreach ($items as $index => $item) {
            $items[$index] = self::normalizeResolvedContentItem($item, $ctype_name);
        }

        return $items;
    }

    private static function resolveContentItem(array $source, array $context = []) {
        $content_model = cmsCore::getModel('content');
        if (!$content_model || !method_exists($content_model, 'getContentTypeByName') || !method_exists($content_model, 'getContentItem')) {
            return [];
        }

        $ctype_name = (string) ($source['ctype'] ?? '');
        if ($ctype_name === '' || !$content_model->getContentTypeByName($ctype_name)) {
            return [];
        }

        $resolver = is_array($source['resolver'] ?? null) ? $source['resolver'] : [];
        $mode = (string) ($resolver['mode'] ?? 'current');

        if ($mode === 'by_id') {
            $item_id = (int) ($resolver['id'] ?? $resolver['itemId'] ?? $resolver['item_id'] ?? 0);
            if ($item_id <= 0) {
                return [];
            }

            $item = $content_model->getContentItem($ctype_name, $item_id);
            return self::normalizeResolvedContentItem($item, $ctype_name);
        }

        if ($mode === 'latest') {
            $content_model->orderBy('date_pub', 'desc');
            $content_model->limit(1);

            $items = $content_model->getContentItems($ctype_name);
            if (!is_array($items) || !$items) {
                return [];
            }

            $item = reset($items);
            return self::normalizeResolvedContentItem($item, $ctype_name);
        }

        $current = self::detectCurrentContentContext($context);
        if (empty($current['ctype']) || (string) $current['ctype'] !== $ctype_name || empty($current['itemId'])) {
            return [];
        }

        $item = $content_model->getContentItem($ctype_name, (int) $current['itemId']);
        return self::normalizeResolvedContentItem($item, $ctype_name);
    }

    private static function normalizeResolvedContentItem($item, $ctype_name) {
        if (!is_array($item)) {
            return [];
        }

        if (empty($item['ctype_name'])) {
            $item['ctype_name'] = $ctype_name;
        }

        return $item;
    }

    private static function buildFieldOptions($content_model, $ctype_name) {
        $fields = [
            [
                'name'  => 'title',
                'label' => 'Заголовок записи',
                'type'  => 'system',
                'kinds' => ['text'],
            ],
            [
                'name'  => 'date_pub',
                'label' => 'Дата публикации',
                'type'  => 'system',
                'kinds' => ['date', 'text'],
            ],
            [
                'name'  => 'hits_count',
                'label' => 'Просмотры',
                'type'  => 'system',
                'kinds' => ['number', 'text'],
            ],
            [
                'name'  => 'comments_count',
                'label' => 'Комментарии',
                'type'  => 'system',
                'kinds' => ['number', 'text'],
            ],
            [
                'name'  => 'record_url',
                'label' => 'URL записи',
                'type'  => 'runtime',
                'kinds' => ['url', 'text'],
            ],
            [
                'name'  => 'record_image_url',
                'label' => 'Главное изображение записи',
                'type'  => 'runtime',
                'kinds' => ['image'],
            ],
            [
                'name'  => 'category.title',
                'label' => 'Категория',
                'type'  => 'system',
                'kinds' => ['text'],
            ],
            [
                'name'  => 'category.url',
                'label' => 'URL категории',
                'type'  => 'system',
                'kinds' => ['url', 'text'],
            ],
            [
                'name'  => 'user.nickname',
                'label' => 'Автор',
                'type'  => 'system',
                'kinds' => ['text'],
            ],
        ];

        foreach ((array) $content_model->getContentFields($ctype_name) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $name = trim((string) ($field['name'] ?? ''));
            $field_type = (string) ($field['type'] ?? 'text');
            if ($name === '' || !self::isBindableField($field_type)) {
                continue;
            }

            $fields[] = [
                'name'  => $name,
                'label' => trim((string) ($field['title'] ?? $name)),
                'type'  => $field_type,
                'kinds' => self::buildFieldKinds($name, $field_type),
            ];
        }

        return $fields;
    }

    private static function isBindableField($type) {
        $type = strtolower((string) $type);
        if ($type === '') {
            return false;
        }

        if (in_array($type, ['relation', 'parent', 'video'], true)) {
            return false;
        }

        return true;
    }

    private static function buildFieldKinds($name, $type) {
        $name = strtolower((string) $name);
        $type = strtolower((string) $type);

        if (in_array($name, ['hits_count', 'comments_count'], true)) {
            return ['number', 'text'];
        }

        if (in_array($name, ['date_pub', 'date_updated'], true)) {
            return ['date', 'text'];
        }

        if (in_array($type, ['image', 'photo', 'photos', 'file', 'files'], true)) {
            return ['image'];
        }

        if (in_array($type, ['int', 'integer', 'number', 'float', 'double', 'price'], true)) {
            return ['number', 'text'];
        }

        if (in_array($type, ['url', 'link'], true)) {
            return ['url', 'text'];
        }

        return ['text'];
    }

    private static function detectCurrentContentContext(array $context = []) {
        $ctype = trim((string) ($context['current_ctype'] ?? $context['ctype'] ?? ''));
        $item_id = (int) ($context['current_item_id'] ?? $context['item_id'] ?? 0);

        if ($ctype !== '' && $item_id > 0) {
            return ['ctype' => $ctype, 'itemId' => $item_id];
        }

        $core = cmsCore::getInstance();
        if (!$core) {
            return ['ctype' => '', 'itemId' => 0];
        }

        $route = method_exists($core, 'getUriData') ? (array) $core->getUriData() : [];
        $params = isset($route['params']) && is_array($route['params']) ? $route['params'] : [];
        $segments = [];
        $uri = trim((string) ($core->uri ?? ''), '/');
        if ($uri !== '') {
            $segments = explode('/', $uri);
        }

        $controller = trim((string) ($route['controller'] ?? ''));
        $action = trim((string) ($route['action'] ?? ''));

        if ($controller !== 'content' || $action !== 'view') {
            return ['ctype' => '', 'itemId' => 0];
        }

        $ctype = trim((string) ($params[0] ?? ($segments[0] ?? '')));
        $item_id = (int) ($params[1] ?? 0);
        if ($item_id <= 0 && !empty($segments[1]) && ctype_digit((string) $segments[1])) {
            $item_id = (int) $segments[1];
        }

        return ['ctype' => $ctype, 'itemId' => $item_id];
    }
}