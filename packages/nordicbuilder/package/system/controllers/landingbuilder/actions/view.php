<?php

class actionLandingbuilderView extends cmsAction {

	public function run($page_key = 'homepage') {

		require_once __DIR__ . '/../helpers/runtime_styles.php';

		try {
			$page = $this->model->getPageByKey($page_key);
			if (!$page) {
				return cmsCore::error404();
			}

			$is_preview = ($page['status'] ?? 'draft') !== 'published';

			$runtime = $this->model->getRuntimePage($page);
			$runtime['device_type'] = cmsRequest::getDeviceType();
			$runtime['is_preview'] = $is_preview;
			$debug = (bool) $this->request->get('debug', false);
			$runtime['compact'] = !($debug && cmsUser::isAdmin());
			$this->cms_template->addLayoutParams([
				'landingbuilder_shell_runtime' => $runtime['shell']
			]);

			if (function_exists('landingbuilder_inject_runtime_site_styles')) {
				landingbuilder_inject_runtime_site_styles($this->cms_template);
			}

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