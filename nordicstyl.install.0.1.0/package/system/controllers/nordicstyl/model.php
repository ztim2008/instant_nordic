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

    public function getRule(int $id): ?array {
        if ($id <= 0) {
            return null;
        }

        $rule = $this->getItemById('nordicstyl_styles', $id);

        return $rule && is_array($rule) ? $rule : null;
    }

    public function addRule(array $rule) {

        if (!isset($rule['ordering']) || (int)$rule['ordering'] < 0) {
            $rule['ordering'] = $this->getMaxRuleOrdering() + 1;
        }

        unset($rule['id']);

        return $this->insert('nordicstyl_styles', $rule);
    }

    public function updateRule(int $id, array $rule): bool {
        if ($id <= 0) {
            return false;
        }

        unset($rule['id']);

        return (bool)$this->update('nordicstyl_styles', $id, $rule);
    }

    public function deleteRule(int $id): bool {
        if ($id <= 0) {
            return false;
        }

        $rule = $this->getRule($id);
        if ($rule && isset($rule['ordering'])) {
            $this->filterGt('ordering', (int)$rule['ordering'])->decrement('nordicstyl_styles', 'ordering');
        }

        return (bool)$this->delete('nordicstyl_styles', $id);
    }

    public function getMaxRuleOrdering(): int {
        return (int)$this->getMaxOrdering('nordicstyl_styles');
    }
}
