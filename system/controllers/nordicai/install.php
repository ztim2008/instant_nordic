<?php

class installNordicai extends cmsInstaller {

    public function install() {

        $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->prefix}nordicai_runs` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int unsigned NOT NULL DEFAULT 0,
            `prompt` text NOT NULL,
            `selector` varchar(255) DEFAULT NULL,
            `response` mediumtext,
            `error` varchar(500) DEFAULT NULL,
            `is_ok` tinyint unsigned NOT NULL DEFAULT 0,
            `date_pub` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `date_pub` (`date_pub`),
            KEY `is_ok` (`is_ok`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        return true;
    }

}
