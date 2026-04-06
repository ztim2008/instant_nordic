<?php

class uninstallLandingbuilder extends cmsInstaller {

    public function uninstall() {

        $this->db->query('DROP TABLE IF EXISTS `{#}landingbuilder_page_widgets`');
        $this->db->query('DROP TABLE IF EXISTS `{#}landingbuilder_page_versions`');
        $this->db->query('DROP TABLE IF EXISTS `{#}landingbuilder_pages`');

        return true;
    }
}