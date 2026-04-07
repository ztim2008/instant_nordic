<?php

class formWidgetOnebuilderTestimonials1Options extends cmsForm{
    
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
                'title' => 'Отзывы',
                'childs' => array(
                    new fieldImages('options:photo', array(
                        'title' => 'Фотографии клиентов',
                        'hint' => '<mark>Загрузите в этом поле фотографии клиентов.</mark>',
                        'options' => array('sizes' => array('original'))
                        )),
                    new fieldText('options:name', array(
                        'title' => 'Имена клиентов <span class="badge bg-danger">каждый с новой строки</span>'
                        )),
                    new fieldText('options:company', array(
                        'title' => 'Компании клиентов <span class="badge bg-danger">каждый с новой строки</span>',
                        'hint' => '<mark>Обратите внимание - количество строк названий компаний должно быть равно количеству ключей в полях выше, иначе получите ошибку</mark>',
                        )),
                    new fieldText('options:rev', array(
                        'title' => 'Отзывы клиентов <span class="badge bg-danger">каждый отзыв разделяется тремя звездочками ***</span>',
                        'hint' => '<mark>Обратите внимание - количество отзывов компаний должно быть равно количеству ключей в полях выше, иначе получите ошибку</mark>',
                        'default' => 'Отзыв 1 *** Отзыв 2 *** Отзыв 3 ***'
                        )),
                    new fieldList('options:p_preset', array(
                        'title' => 'Пресет для текста отзывов',
                        'items' => array(
                            'onebuilder-p1' => 'Пресет 1',
                            'onebuilder-p2' => 'Пресет 2',
                            'onebuilder-p3' => 'Пресет 3'
                        ),
                        'default' => 'onebuilder-p1'
                        )),
                    )
                ),
                );
            }
            
        }
