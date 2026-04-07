<?php

class formWidgetOnebuilderContent1Options extends cmsForm{
    
    public function init($options=false) {

        $content_model = cmsCore::getModel('content');

        $field_generator = function ($item, $request) use($content_model) {
            $list     = ['' => ''];
            $ctype_id = is_array($item) ? array_value_recursive('options:ctype_id', $item) : false;
            if (!$ctype_id && $request) {
                $ctype_id = $request->get('options:ctype_id', 0);
            }
            if (!$ctype_id) {
                return $list;
            }
            $ctype = $content_model->getContentType($ctype_id);
            if (!$ctype) {
                return $list;
            }
            $fields = $content_model->getContentFields($ctype['name']);
            if ($fields) {
                $list = $list + array_collection_to_list($fields, 'name', 'title');
            }
            return $list;
        };

        return array( 

            array(
                'type' => 'fieldset',
                'title' => 'Общее',
                'childs' => array(
                    new fieldString('options:id', array(
                        'title' => 'Уникальный идентификатор блока <span class="badge bg-danger">!</span>',
                        'hint' => '<mark>Например my-firts-block</mark>',
                        'rules' => array(array('required'))
                        )),
                    new fieldNumber('options:ptop', array(
                        'title' => 'Внутренний отступ в секции сверху',
                        'units' => 'px'
                        )),
                    new fieldNumber('options:pbottom', array(
                        'title' => 'Внутренний отступ в секции снизу',
                        'units' => 'px'
                        )),
                    new fieldList('options:bgtype', array(
                        'title' => 'Тип фона в секции',
                        'items' => array(
                            '' => '',
                            'background-color' => 'Сплошной цвет',
                            'background-image' => 'Изображение'
                            )
                        )),
                    new fieldColor('options:bgcolor', array(
                        'title' => 'Цвет фона',
                        'visible_depend' => array('options:bgtype' => array('show' => array('background-color')))
                        )),
                    new fieldImage('options:bgimage', array(
                        'title' => 'Фоновое изображение',
                        'options' => array('sizes' => array('original')),
                        'visible_depend' => array('options:bgtype' => array('show' => array('background-image')))
                        )),
                    new fieldList('options:bgfixed', array(
                        'title' => 'Прикрепление фона',
                        'items' => array(
                            '' => '',
                            'fixed' => 'Зафиксировать',
                            'inherit' => 'Прокручивать вместе с содержимым'
                            ),
                        'visible_depend' => array('options:bgtype' => array('show' => array('background-image')))
                        )),
                    new fieldList('options:ctype_id', array(
                        'title'     => 'Тип контента',
                        'generator' => function($ctype) use($content_model) {
                            $tree = $content_model->getContentTypes();
                            $items = array(0 => 'Определять автоматически');
                            if ($tree) {
                                foreach ($tree as $item) {
                                    $items[$item['id']] = $item['title'];
                                }
                            }
                            return $items;
                        },
                    )),
                    new fieldList('options:category_id', array(
                        'title'     => 'Категория',
                        'parent'    => array(
                            'list' => 'options:ctype_id',
                            'url'  => href_to('content', 'widget_cats_ajax')
                        ),
                        'generator' => function($item, $request) use($content_model) {
                            $list     = ['' => ''];
                            $ctype_id = is_array($item) ? array_value_recursive('options:ctype_id', $item) : false;
                            if (!$ctype_id && $request) {
                                $ctype_id = $request->get('options:ctype_id', 0);
                            }
                            if (!$ctype_id) {
                                return $list;
                            }
                            $ctype = $content_model->getContentType($ctype_id);
                            if (!$ctype) {
                                return $list;
                            }
                            $cats = $content_model->getCategoriesTree($ctype['name']);
                            if ($cats) {
                                foreach ($cats as $cat) {
                                    if ($cat['ns_level'] > 1) {
                                        $cat['title'] = str_repeat('-', $cat['ns_level']) . ' ' . $cat['title'];
                                    }
                                    $list[$cat['id']] = $cat['title'];
                                }
                            }
                            return $list;
                        },
                        'visible_depend' => array('options:ctype_id' => array('hide' => array('0')))
                    )),
                    new fieldList('options:dataset', array(
                        'title'     =>  'Название набора',
                        'parent'    => array(
                            'list' => 'options:ctype_id',
                            'url'  => href_to('content', 'widget_datasets_ajax')
                        ),
                        'generator' => function($item, $request) use($content_model) {
                            $list     = ['0' => ''];
                            $ctype_id = is_array($item) ? array_value_recursive('options:ctype_id', $item) : false;
                            if (!$ctype_id && $request) {
                                $ctype_id = $request->get('options:ctype_id', 0);
                            }
                            if (!$ctype_id) {
                                return $list;
                            }
                            $datasets = $content_model->getContentDatasets($ctype_id);
                            if ($datasets) {
                                $list = $list + array_collection_to_list($datasets, 'id', 'title');
                            }
                            return $list;
                        },
                        'visible_depend' => array('options:ctype_id' => array('hide' => array('0')))
                    )),
                    new fieldList('options:image_field', array(
                        'title' => 'Поле изображения',
                        'rules' => array(
                            array('required')
                        ),
                        'parent' => array(
                            'list' => 'options:ctype_id',
                            'url' => href_to('content', 'widget_fields_ajax')
                        ),
                        'generator' => $field_generator
                    )),
                    
                    new fieldList('options:image_preset', array(
                        'title' => 'Пресет изображения',
                        'generator' => function($item) {
                            return cmsCore::getModel('images')->getPresetsList(true)+array('original' => LANG_PARSER_IMAGE_SIZE_ORIGINAL);
                        },
                    )),
                    new fieldList('options:teaser_field', array(
                        'title' => 'Поле краткого описания',
                        'parent' => array(
                            'list' => 'options:ctype_id',
                            'url' => href_to('content', 'widget_fields_ajax')
                        ),
                        'generator' => $field_generator
                    )),
                    new fieldNumber('options:limit', array(
                        'title' => 'Сколько записей выводить в виджете? <span class="badge bg-danger">!</span>',
                        'default' => 4,
                        'units' => 'шт',
                        'rules' => array(
                            array('required')
                        )
                    )),
                    new fieldString('options:view_item', array(
                        'title' => 'Заголовок кнопки "Читать полностью". По умолчанию - "Читать полностью" <span class="badge bg-danger">!</span>',
                        'default' => 'Читать полностью',
                        'rules' => array(
                            array('required')
                        )
                    )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Секция',
                'childs' => array(
                    new fieldString('options:title', array(
                        'title' => 'Заголовок секции',
                        'hint' => '<mark>Если выводить не нужно, оставьте поле пустым</mark>'
                        )),
                    new fieldList('options:title_preset', array(
                        'title' => 'Пресет заголовка',
                        'items' => array(
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6'
                            )
                        )),
                    new fieldText('options:desc', array(
                        'title' => 'Описание секции',
                        'hint' => '<mark>Если выводить не нужно, оставьте поле пустым</mark>'
                        )),
                    new fieldColor('options:dot_color', array(
                        'title' => 'Цвет активной точки переключения слайдов',
                        'default' => '#152CB9'
                        )),
                    new fieldList('options:dot_pos', array(
                        'title' => 'Положение точек для переключения слацдов',
                        'items' => array(
                            'top' => 'Над списком записей',
                            'bottom' => 'Под списком записей'
                        ),
                        )),
                    )
                )
                );
            }
            
        }