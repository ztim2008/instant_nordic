<?php

class formWidgetOnebuilderHeader1Options extends cmsForm{
    
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
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Секция',
                'childs' => array(
                    new fieldString('options:left', array(
                        'title' => 'Фон левой колонки<span class="badge bg-danger">Формат RGBA</span>',
                        'default' => '65, 0, 46, 0.8',
                        'hint' => '<mark>Значения цвета указываются в формате RGBA, например <strong>65, 0, 46, 0.8</strong>. Подобрать цвет можно <a href = "https://csscolor.ru/hex/" target = "_blank">Здесь</a></mark>',
                        )),
                    new fieldImage('options:bg', array(
                        'title' => 'Фон секции <span class="badge bg-danger">!</span>',
                        'options' => array('sizes' => array('original')),
                        'rules' => array(array('required'))
                        )),
                    new fieldString('options:title', array(
                        'title' => 'Заголовок левой колонки',
                        )),
                    new fieldList('options:typebutton', array(
                        'title' => 'Тип действия на кнопке',
                        'hint' => '<mark>Выберите, какое действие необходимо сделат прик клике на кнопку</mark>',
                        'items' => array(
                            'scrollto' => 'Скролл к секции',
                            'linkto' => 'Открыть ссылку',
                            'none' => 'Не выводить кнопку')
                        )),
                    new fieldString('options:scrolllink', array(
                        'title' => 'Куда скроллить?',
                        'hint' => '<mark>Укажите идентификатор блока, к которому нужно сделать прокрутку. Первый символ в ссылке - обязательно <strong>#</strong>. Например, <strong>#block-id</strong></mark>',
                        'visible_depend' => array('options:typebutton' => array('show' => array('scrollto')))
                        )),
                    new fieldString('options:titlebutton', array(
                        'title' => 'Заголовок кнопки',
                        'hint' => '<mark>Укажите заголовок кнопки</mark>',
                        'visible_depend' => array('options:typebutton' => array('show' => array('scrollto', 'linkto', 'modal')))
                        )),
                    new fieldString('options:link', array(
                        'title' => 'Ссылка',
                        'hint' => '<mark>Укажите ссылку. Для внешних ссылок указывайте полный адрес. Для внутренних - <strong>pages/about.html</strong></mark>',
                        'visible_depend' => array('options:typebutton' => array('show' => array('linkto')))
                        )),
                    new fieldList('options:linkopt', array(
                        'title' => 'Открыть ссылку',
                        'hint' => '<mark>Выберите, каким образом открыть ссылку</mark>',
                        'items' => array(
                            'self' => 'В текущем окне',
                            'blank' => 'В новом окне'),
                        'visible_depend' => array('options:typebutton' => array('show' => array('linkto')))
                        )),
                    new fieldList('options:bpreset', array(
                        'title' => 'Пресет кнопки',
                        'default' => 'onebuilder-button1 obbtn1',
                        'hint' => '<mark>Выберите, какой пресет кнопки выводить в этой секции. По умолчанию выводится родная кнопка OneBuilder.</mark>',
                        'items' => array(
                            'onebuilder-button1 obbtn1' => 'Пресет 1',
                            'onebuilder-button2 obbtn2' => 'Пресет 2'
                        )
                        )),
                    )
                ),
                );
            }
            
        }

