<?php

class formWidgetOnebuilderVideo1Options extends cmsForm{
    
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
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Видео',
                'childs' => array(
                    new fieldImage('options:cover', array(
                        'title' => 'Обложка видеоплеера <span class="badge bg-danger">!</span>',
                        'options' => array('sizes' => array('original')),
                        'rules' => array(array('required'))
                        )),
                    new fieldUrl('options:video', array(
                        'title' => 'Ссылка на видеоролик <span class="badge bg-danger">!</span>',
                        'hint' => '<mark>Например https://www.youtube.com/watch?v=HwMKTh-OEcE</mark>',
                        'rules' => array(array('required'))
                        )),
                    )
                ),
                );
            }
            
        }
