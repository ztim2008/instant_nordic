<?php

class actionNordicbuilderContract extends cmsAction {

	public function run($key) {
		$contract = $this->model->getContractDetails($key);

		if (!$contract) {
			return cmsCore::error404();
		}

		$this->breadcrumbs = [
			[
				'title' => 'Контракты',
				'url'   => href_to($this->root_url, 'contracts')
			],
			[
				'title' => $contract['title']
			]
		];

		return $this->cms_template->render([
			'page_title'    => $contract['title'],
			'page_note'     => 'Детальный просмотр контракта и его схемы хранения.',
			'back_url'      => href_to($this->root_url, 'contracts'),
			'contract'      => $contract,
			'storage_stub'  => $contract['storage_stub'],
			'raw_definition'=> $contract['raw_definition']
		]);
	}
}