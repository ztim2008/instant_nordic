<?php

class actionNordicbuilderContracts extends cmsAction {

    public function run() {
        $contracts = $this->model->getContractsRegistry();

        foreach ($contracts as &$contract) {
            $contract['detail_url'] = href_to($this->root_url, 'contract', [$contract['key']]);
        }

        unset($contract);

        return $this->cms_template->render([
            'page_title'      => 'Контракты',
            'page_note'       => 'File-based registry и storage stub для foundation layer `nordicbuilder`.',
            'contracts'       => $contracts,
            'contracts_count' => count($contracts)
        ]);
    }
}