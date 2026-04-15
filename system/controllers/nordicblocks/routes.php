<?php

function routes_nordicblocks() {
    return [
        // /nordicblocks/media_list  — список изображений для инспектора
        [
            'pattern' => '/^nordicblocks\/media_list$/i',
            'action'  => 'media_list'
        ],
        // /nordicblocks/media_upload  — загрузка изображения из инспектора
        [
            'pattern' => '/^nordicblocks\/media_upload$/i',
            'action'  => 'media_upload'
        ],
        // /nordicblocks/canvas/block/{id}  — превью одиночного блока
        [
            'pattern' => '/^nordicblocks\/canvas\/block\/(\d+)$/i',
            'action'  => 'canvas',
            1         => 'canvas_block_id'
        ],
        // /nordicblocks/canvas/{page_key}  — legacy: превью страницы
        [
            'pattern' => '/^nordicblocks\/canvas\/([a-z0-9\-_]+)$/i',
            'action'  => 'canvas',
            1         => 'canvas_page_key'
        ],
        // /nordicblocks/{page_key}  — фронтенд вид
        [
            'pattern' => '/^nordicblocks\/([a-z0-9\-_]+)$/i',
            'action'  => 'view',
            1         => 'page_key'
        ]
    ];
}
