<?php

class actionNordicaiIndex extends cmsAction {

    public function run() {
        // Публичный фронт пока не нужен — гипотеза тестируется в админке.
        return cmsCore::error404();
    }

}
