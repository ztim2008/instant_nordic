<?php

class formWidgetOnebuilderClients1Options extends cmsForm{
    
    public function init() {
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
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Секция',
                'childs' => array(
                    new fieldString('options:title', array(
                        'title' => 'Заголовок секции',
                        'hint' => '<mark>Если заголовок не требуется выводить, оставьте поле пустым</mark>'
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
                    new fieldtext('options:desc', array(
                        'title' => 'Описание секции',
                        'hint' => '<mark>Если описание не требуется выводить, оставьте поле пустым</mark>'
                        )),
                    new fieldList('options:p_preset', array(
                        'title' => 'Пресет текста',
                        'items' => array(
                            'onebuilder-p1' => 'Пресет 1',
                            'onebuilder-p2' => 'Пресет 2',
                            'onebuilder-p3' => 'Пресет 3'
                        ),
                        'default' => 'onebuilder-p1'
                        )),
                    new fieldImages('options:logo', array(
                        'title' => 'Логотипы клиентов',
                        'hint' => '<mark>Загрузите в этом поле логотипы клиентов. Старайтесь, чтобы все логотипы были одного размера, так как стили внутри этой секции не позволяют задать один размер для всех загружаемых фото</mark>',
                        'options' => array('sizes' => array('original'))
                        )),
                    )
                )
                );
            }
            
        }
