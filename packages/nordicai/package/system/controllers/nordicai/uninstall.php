<?php

class uninstallNordicai extends cmsInstaller {

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `{$this->db->prefix}nordicai_runs`");
        return true;
    }

}
