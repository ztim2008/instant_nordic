<?php

class actionNordicbuilderCanvas extends cmsAction {

	public function run($page_key = '') {
		$document_key = $this->model->resolvePrimaryVisualDocumentKey($this->request->get('document_key', ''));
		$workspace_summary = $this->model->getWorkspaceSummary();
		$bridge_model = cmsCore::getModel('landingbuilder');
		$missing_tables = isset($workspace_summary['persistence']['missing_tables']) && is_array($workspace_summary['persistence']['missing_tables'])
			? array_values(array_filter(array_map('strval', $workspace_summary['persistence']['missing_tables'])))
			: [];
		$blocking_missing_tables = array_values(array_diff($missing_tables, ['nordicbuilder_page_renders']));

		if ($blocking_missing_tables) {
			$message = 'Сначала нужно установить persistence-таблицы nordicbuilder, иначе visual workspace не сможет сохранять документы.';
			if ($blocking_missing_tables) {
				$message .= ' Отсутствуют таблицы: ' . implode(', ', $blocking_missing_tables) . '.';
			}
			$message .= ' Если таблицы не создаются автоматически, проверьте права БД на CREATE TABLE.';

			cmsUser::addSessionMessage($message, 'error');
			return $this->redirect(href_to($this->root_url, 'workspace'));
		}

		if (in_array('nordicbuilder_page_renders', $missing_tables, true)) {
			cmsUser::addSessionMessage('Таблица nordicbuilder_page_renders отсутствует: canvas и сохранение документов работают, но SSR publish будет недоступен до создания таблицы (права CREATE TABLE).', 'warning');
		}

		$page_key = trim((string) $page_key);
		if ($page_key === '') {
			$page_key = $document_key;
		}

		$page = $bridge_model->getPageByKey($page_key);
		if (!$page) {
			$page = $bridge_model->createPage([
				'key'      => $page_key,
				'title'    => $page_key === 'homepage' ? 'Главная' : $page_key,
				'mode'     => 'full_takeover',
				'status'   => 'draft',
				'template' => 'nordic'
			], $this->cms_user->id);

			if ($page) {
				cmsUser::addSessionMessage('Создана пустая страница для визуального редактора.', 'success');
			}
		}

		if (!$page) {
			return cmsCore::error404();
		}

		$document = $this->model->getPageDocumentByKey($page_key);
		if ($document && $page_key !== 'vertical-slice-home') {
			$document_status = (string) ($document['status'] ?? '');
			$page_status = (string) ($page['status'] ?? '');

			if ($document_status === 'prototype' && $page_status === 'draft') {
				$result = $this->model->ensureEmptyPageDocument($page_key, (string) ($page['title'] ?? ''), $this->cms_user->id);
				if (!empty($result['is_valid'])) {
					$document = $this->model->getPageDocumentByKey($page_key);
					cmsUser::addSessionMessage('Стартовый демо-макет убран: страница открыта как пустой холст.', 'success');
				}
			}
		}
		if (!$document) {
			if ($page_key === 'vertical-slice-home') {
				$result = $this->model->ensureVerticalSliceDocument($page_key, $this->cms_user->id);

				if (empty($result['is_valid'])) {
					cmsUser::addSessionMessage('Не удалось подготовить стартовый vertical slice для visual workspace.', 'error');
					return $this->redirect(href_to($this->root_url, 'workspace'));
				}

				if (!empty($result['is_seeded'])) {
					cmsUser::addSessionMessage('Создан готовый стартовый макет. Теперь страницу можно сразу править под свой продукт.', 'success');
				}
			} else {
				$result = $this->model->ensureEmptyPageDocument($page_key, (string) ($page['title'] ?? ''), $this->cms_user->id);

				if (empty($result['is_valid'])) {
					cmsUser::addSessionMessage('Не удалось подготовить пустой документ страницы для visual workspace.', 'error');
					return $this->redirect(href_to($this->root_url, 'workspace'));
				}
			}
		}

		$page = $bridge_model->getPageByKey($page_key);
		if (!$page) {
			return cmsCore::error404();
		}
		$screen = $bridge_model->getCanvasScreen($page, (array) cmsController::loadOptions('landingbuilder'));
		$screen['api'] = [
			'widgets_catalog_url' => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'widgets_catalog']),
			'widget_preview_url'  => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'widget_preview']),
			'block_preview_url'   => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'block_preview']),
			'widget_options_url'  => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'widget_options']),
			'canvas_save_url'     => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'canvas_save']),
			'versions_url'        => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'versions']),
			'version_restore_url' => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'version_restore']),
			'pages_url'           => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'pages'])
		];
		$screen['preview_url'] = href_to('nordicbuilder', 'view', [$page['key']]);
		$screen['design_url'] = href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'defaults']);

		return $this->cms_template->render('backend/canvas', [
			'menu'   => $this->controller->getBackendMenu(),
			'page'   => $page,
			'screen' => $screen
		]);
	}
}