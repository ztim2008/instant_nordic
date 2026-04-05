<?php

function after_install_package() {

    $helper_file = cmsConfig::get('root_path') . 'templates/nordic/install_helpers/shell_migration.php';

    if (!is_readable($helper_file)) {
        return 'Nordic shell migration helper not found: ' . $helper_file;
    }

    include_once $helper_file;

    try {
        NordicShellMigration::apply(
            cmsDatabase::getInstance(),
            cmsConfig::getInstance(),
            cmsCache::getInstance(),
            'nordic'
        );
    } catch (Throwable $exception) {
        return 'Nordic shell migration failed: ' . $exception->getMessage();
    }

    return true;
}
