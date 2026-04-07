<?php

class formWidgetOnebuilderHeader3Options extends cmsForm{
    
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
                    new fieldImage('options:bg', array(
                        'title' => 'Фон секции <span class="badge bg-danger">!</span>',
                        'options' => array('sizes' => array('original')),
                        'rules' => array(array('required'))
                        )),
                    new fieldString('options:title', array(
                        'title' => 'Заголовок'
                        )),
                    new fieldText('options:desc', array(
                        'title' => 'Описание под заголовком'
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

                    new fieldList('options:typebutton2', array(
                        'title' => 'Тип действия на второй кнопке',
                        'hint' => '<mark>Выберите, какое действие необходимо сделат прик клике на кнопку</mark>',
                        'items' => array(
                            'scrollto' => 'Скролл к секции',
                            'linkto' => 'Открыть ссылку',
                            'none' => 'Не выводить кнопку')
                        )),
                    new fieldString('options:scrolllink2', array(
                        'title' => 'Куда скроллить?',
                        'hint' => '<mark>Укажите идентификатор блока, к которому нужно сделать прокрутку. Первый символ в ссылке - обязательно <strong>#</strong>. Например, <strong>#block-id</strong></mark>',
                        'visible_depend' => array('options:typebutton2' => array('show' => array('scrollto')))
                        )),
                    new fieldString('options:titlebutton2', array(
                        'title' => 'Заголовок кнопки',
                        'hint' => '<mark>Укажите заголовок кнопки</mark>',
                        'visible_depend' => array('options:typebutton2' => array('show' => array('scrollto', 'linkto', 'modal')))
                        )),
                    new fieldString('options:link2', array(
                        'title' => 'Ссылка',
                        'hint' => '<mark>Укажите ссылку. Для внешних ссылок указывайте полный адрес. Для внутренних - <strong>pages/about.html</strong></mark>',
                        'visible_depend' => array('options:typebutton2' => array('show' => array('linkto')))
                        )),
                    new fieldList('options:linkopt2', array(
                        'title' => 'Открыть ссылку',
                        'hint' => '<mark>Выберите, каким образом открыть ссылку</mark>',
                        'items' => array(
                            'self' => 'В текущем окне',
                            'blank' => 'В новом окне'),
                        'visible_depend' => array('options:typebutton2' => array('show' => array('linkto')))
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Свой CSS',
                'childs' => array(
                    new fieldHtml('options:css_section', array(
                        'title' => 'Стили для секции',
                        'hint' => '<mark>Пишите стили так: <strong>font-size: 18x;</strong></mark>',
                        'options' => array('editor' => 'ace')
                        )),
                    new fieldHtml('options:css_title', array(
                        'title' => 'Стили для заголовка',
                        'hint' => '<mark>Пишите стили так: <strong>font-size: 18x;</strong></mark>',
                        'options' => array('editor' => 'ace')
                        )),
                    new fieldHtml('options:css_desc', array(
                        'title' => 'Стили для описания',
                        'hint' => '<mark>Пишите стили так: <strong>font-size: 18x;</strong></mark>',
                        'options' => array('editor' => 'ace')
                        )),
                    new fieldHtml('options:css_button', array(
                        'title' => 'Стили для первой кнопки',
                        'hint' => '<mark>Пишите стили так: <strong>font-size: 18x;</strong></mark>',
                        'options' => array('editor' => 'ace')
                        )),
                    new fieldHtml('options:css_button2', array(
                        'title' => 'Стили для второй кнопки',
                        'hint' => '<mark>Пишите стили так: <strong>font-size: 18x;</strong></mark>',
                        'options' => array('editor' => 'ace')
                        )),
                    )
                ),
                );
            }
            
        }