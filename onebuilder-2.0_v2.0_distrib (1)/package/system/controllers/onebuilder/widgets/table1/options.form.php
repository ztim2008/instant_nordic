<?php

class formWidgetOnebuilderTable1Options extends cmsForm{
    
    public function init() {
        return array( 

            array(
                'type' => 'fieldset',
                'title' => 'Секция',
                'childs' => array(
                new fieldString('options:id', array(
                    'title' => 'Уникальный идентификатор блока <span class="badge bg-danger">!</span>',
                    'hint' => '<mark>Например my-firts-block</mark>',
                    'rules' => array(array('required'))
                    )),
                new fieldString('options:title', array(
                    'title' => 'Заголовок секции',
                    'hint' => '<mark>Если не нужно выводить, оставьте поле пустым</mark>'
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
                'title' => 'Таблица',
                'childs' => array(
                new fieldText('options:htable', array(
                    'title' => 'Заголовки таблиц <span class="badge bg-danger">Каждый с новой строки</span>'
                    )),
                new fieldText('options:ptable', array(
                    'title' => 'Цены таблиц <span class="badge bg-danger">Каждый с новой строки</span>',
                    'hint' => '<mark>Можно указывать сразу со значением. Например, <strong>200 Руб</strong></mark>'
                    )),
                new fieldHtml('options:otable', array(
                    'title' => 'Строки таблиц <span class="badge bg-danger">Каждый элемент с новой строки, конец значений разделяется ***</span>',
                    'options' => array('editor' => 'ace'),
                    'default' => '<p class = "onebuilder-line">Значение 1</p>
<p class = "onebuilder-line">Значение 2</p>
<p class = "onebuilder-line">Значение 3</p> ***
<p class = "onebuilder-line">Значение 1</p>
<p class = "onebuilder-line">Значение 2</p>
<p class = "onebuilder-line">Значение 3</p> ***',
                    'hint' => '<mark>Используйте в этом поле готовый пресет, указанный ниже. Стили пресета можно отредактировать во вкладке "Стили"</mark><br>
                    <pre class="hljs" style="font-size: 14px; width: 100%; display: block; overflow-x: auto; padding: 0.5em; background: rgb(51, 51, 51) none repeat scroll 0% 0%; color: rgb(255, 255, 255);">&lt;p <span class="hljs-class"><span class="hljs-keyword" style="color: rgb(252, 194, 140);">class</span> </span>= <span class="hljs-string" style="color: rgb(162, 252, 162);">"onebuilder-line"</span>&gt;Значение&lt;/p&gt;</pre>'
                    )),
                    )
                ),
                array(
                    'type' => 'fieldset',
                    'title' => 'Стили',
                    'childs' => array(
                    new fieldHtml('options:line_style', array(
                        'title' => 'Стили для строк таблицы. Необходимые стили уже прописаны, вы можете их изменить или дополнить своими',
                        'options' => array('editor' => 'ace'),
                        'default' => 'text-align: left;
font-family: inherit;
font-size: 18px;
padding-bottom: 7px;
border-bottom: 1px solid #000;',
                        'hint' => '<mark style = "background-color: #ff0; font-size: 16px;">Не пишите сюда стили для других элементов!!!</mark>'
                        )),
                        )
                    )
                );
            }
            
        }