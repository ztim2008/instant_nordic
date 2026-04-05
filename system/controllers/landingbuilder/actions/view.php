<?php

class actionLandingbuilderView extends cmsAction {

	public function run($page_key = 'homepage') {

		try {
			$page = $this->model->getPageByKey($page_key);
			if (!$page) {
				return cmsCore::error404();
			}

			$is_preview = ($page['status'] ?? 'draft') !== 'published';
			if ($is_preview && !cmsUser::isAdmin()) {
				return cmsCore::error404();
			}

			$runtime = $this->model->getRuntimePage($page);
			$runtime['device_type'] = cmsRequest::getDeviceType();
			$runtime['is_preview'] = $is_preview;
			$this->cms_template->addLayoutParams([
				'landingbuilder_shell_runtime' => $runtime['shell']
			]);

			$this->cms_template->setPageTitle($page['title'], 'Нордик');

			return $this->cms_template->render('view', [
				'page'    => $page,
				'runtime' => $runtime
			]);
		} catch (Throwable $exception) {
			return cmsCore::error('Не удалось открыть предпросмотр страницы. ' . $exception->getMessage());
		}
	}
}