<?php

class modelNordicstyl extends cmsModel {

    public function getRules(bool $enabledOnly = true): array {

        $this->resetFilters();

        if ($enabledOnly) {
            $this->filterEqual('is_enabled', 1);
        }

        $this->orderBy('ordering', 'asc');
        $this->orderBy('id', 'asc');

        $rules = $this->get('nordicstyl_styles');

        return is_array($rules) ? $rules : [];
    }
}
