<?php

class NordicblocksDataSourceResolver {

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
            'listSource' => [],
            'listItems'  => [],
        ];

        $block_type = (string) ($contract['meta']['blockType'] ?? '');
        if ($block_type !== 'faq') {
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
            'listModes'    => [
                ['value' => 'manual', 'label' => 'Ручной список'],
                ['value' => 'content_list', 'label' => 'Список записей InstantCMS'],
            ],
            'sortOptions'  => [],
        ];

        if ((string) $block_type !== 'faq') {
            return $options;
        }

        foreach (self::$sort_options as $key => $sort) {
            $options['sortOptions'][] = ['value' => $key, 'label' => $sort['label']];
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
        return is_array($items) ? $items : [];
    }

    private static function buildFieldOptions($content_model, $ctype_name) {
        $fields = [
            [
                'name'  => 'title',
                'label' => 'Заголовок записи',
                'type'  => 'system',
            ],
            [
                'name'  => 'date_pub',
                'label' => 'Дата публикации',
                'type'  => 'system',
            ],
            [
                'name'  => 'hits_count',
                'label' => 'Просмотры',
                'type'  => 'system',
            ],
            [
                'name'  => 'comments_count',
                'label' => 'Комментарии',
                'type'  => 'system',
            ],
            [
                'name'  => 'category.title',
                'label' => 'Категория',
                'type'  => 'system',
            ],
            [
                'name'  => 'user.nickname',
                'label' => 'Автор',
                'type'  => 'system',
            ],
        ];

        foreach ((array) $content_model->getContentFields($ctype_name) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $name = trim((string) ($field['name'] ?? ''));
            if ($name === '' || !self::isTextLikeField((string) ($field['type'] ?? ''))) {
                continue;
            }

            $fields[] = [
                'name'  => $name,
                'label' => trim((string) ($field['title'] ?? $name)),
                'type'  => (string) ($field['type'] ?? 'text'),
            ];
        }

        return $fields;
    }

    private static function isTextLikeField($type) {
        $type = strtolower((string) $type);
        if ($type === '') {
            return false;
        }

        if (in_array($type, ['image', 'photo', 'photos', 'file', 'files', 'video', 'relation', 'parent'], true)) {
            return false;
        }

        return true;
    }
}