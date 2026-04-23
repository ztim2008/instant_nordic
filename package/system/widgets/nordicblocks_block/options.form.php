<?php

class formWidgetNordicblocksBlockOptions extends cmsForm {

    public function init() {

        $model  = cmsCore::getModel('nordicblocks');
        $blocks = $model ? $model->getBlocks() : [];

        $options = [];
        foreach ($blocks as $b) {
            if ($b['status'] !== 'active') { continue; }
            $title = trim((string) ($b['title'] ?? ''));
            $type  = trim((string) ($b['type'] ?? 'block'));
            if ($title === '') {
                $title = 'Без названия';
            }
            $options[$b['id']] = '#' . (int) $b['id'] . ' - ' . $title . ' [' . $type . ']';
        }

        return [
            [
                'type'   => 'fieldset',
                'title'  => LANG_OPTIONS,
                'childs' => [
                    new fieldList('options:block_id', [
                        'title'      => 'Блок NordicBlocks',
                        'hint'       => 'Выберите блок для отображения на этой позиции.',
                        'items'      => $options,
                        'is_null'    => true,
                        'null_title' => '— не выбрано —',
                    ])
                ]
            ]
        ];
    }
}