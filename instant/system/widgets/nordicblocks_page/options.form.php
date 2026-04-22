<?php

class formWidgetNordicblocksPageOptions extends cmsForm {

    public function init() {

        $model = cmsCore::getModel('nordicblocks');
        $pages = $model ? $model->getPages() : [];

        $options = [];
        foreach ($pages as $p) {
            $label = htmlspecialchars($p['title'], ENT_QUOTES) . ' [' . $p['key'] . ']';
            if ($p['status'] !== 'published') {
                $label .= ' (черновик)';
            }
            $options[$p['key']] = $label;
        }

        return [
            [
                'type'   => 'fieldset',
                'title'  => LANG_OPTIONS,
                'childs' => [
                    new fieldList('options:page_key', [
                        'title'   => 'Страница NordicBlocks',
                        'hint'    => 'Выберите, какую страницу-коллекцию блоков показывать.',
                        'list'    => $options,
                        'is_null' => true,
                        'null_title' => '— не выбрано —',
                    ])
                ]
            ]
        ];
    }
}
