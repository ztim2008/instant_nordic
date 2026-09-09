<?php

class modelNordicai extends cmsModel {

    public function addRun(array $data) {
        return $this->insert('nordicai_runs', $data);
    }

    public function getRecentRuns($limit = 20) {
        return $this->orderBy('date_pub', 'desc')
            ->limit((int) $limit)
            ->get('nordicai_runs') ?: [];
    }

}
