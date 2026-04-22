<?php

function routes_nordicblocks() {
    return [
        [
            'pattern' => '/^([a-z0-9\-_]+)$/i',
            'action'  => 'view',
            1         => 'page_key'
        ]
    ];
}
