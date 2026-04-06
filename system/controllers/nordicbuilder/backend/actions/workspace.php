<?php

class actionNordicbuilderWorkspace extends cmsAction {

    public function run() {
        $active_document_key = $this->request->get('document_key', '');
        $form_document_json = '';
        $form_errors = [];
        $active_document = false;
        $migration_report = null;
        $bulk_overwrite_existing = (bool) $this->request->get('overwrite_existing_documents', 0);

        if ($this->request->has('save_document') || $this->request->has('import_bridge_page') || $this->request->has('import_bridge_pages_bulk')) {
            if (!cmsForm::validateCSRFToken($this->request->get('csrf_token', ''))) {
                $form_errors['csrf_token'] = 'Некорректный CSRF token.';
            } elseif ($this->request->has('save_document')) {
                $document_json = trim((string) $this->request->get('document_json', ''));
                $form_document_json = $document_json;

                $decoded = json_decode($document_json, true);

                if (!is_array($decoded)) {
                    $form_errors['document_json'] = 'JSON страницы не удалось разобрать.';
                } else {
                    $result = $this->model->savePageDocument($decoded, $this->cms_user->id, $this->request->get('document_status', 'draft'));

                    if (!empty($result['is_valid'])) {
                        $saved_key = (string) ($decoded['key'] ?? '');
                        cmsUser::addSessionMessage('Документ страницы сохранен.', 'success');
                        return $this->redirect(href_to($this->root_url, 'workspace') . '?document_key=' . urlencode($saved_key));
                    }

                    $form_errors = (array) ($result['errors'] ?? []);
                }
            } elseif ($this->request->has('import_bridge_page')) {
                $import_page_key = (string) $this->request->get('landingbuilder_page_key', '');
                $result = $this->model->importLandingbuilderPage($import_page_key, $this->cms_user->id);

                if (!empty($result['is_valid'])) {
                    cmsUser::addSessionMessage('Страница импортирована из landingbuilder.', 'success');
                    return $this->redirect(href_to($this->root_url, 'workspace') . '?document_key=' . urlencode($import_page_key));
                }

                $form_errors = (array) ($result['errors'] ?? []);
            } elseif ($this->request->has('import_bridge_pages_bulk')) {
                $migration_report = $this->model->importLandingbuilderPagesBulk([], $this->cms_user->id, [
                    'overwrite_existing' => $bulk_overwrite_existing
                ]);

                if (empty($migration_report['summary']['requested_count'])) {
                    cmsUser::addSessionMessage('Для миграции не найдено подходящих страниц landingbuilder.', 'info');
                } elseif (empty($migration_report['summary']['failed_count'])) {
                    cmsUser::addSessionMessage('Массовая миграция landingbuilder -> nordicbuilder завершена без ошибок.', 'success');
                }
            }
        }

        $workspace_summary = $this->model->getWorkspaceSummary();

        if ($active_document_key !== '') {
            $active_document = $this->model->getPageDocumentByKey($active_document_key);
        }

        if (!$active_document && !empty($workspace_summary['page_documents'][0]['key'])) {
            $active_document = $this->model->getPageDocumentByKey($workspace_summary['page_documents'][0]['key']);
            $active_document_key = $workspace_summary['page_documents'][0]['key'];
        }

        if ($form_document_json === '' && $active_document && !empty($active_document['document'])) {
            $form_document_json = json_encode($active_document['document'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $primary_document_key = $this->model->resolvePrimaryVisualDocumentKey($active_document_key);
        $visual_workspace_url = href_to_abs('admin', 'controllers', ['edit', $this->controller->root_url, 'canvas']) . '?document_key=' . urlencode($primary_document_key);
        $starter_canvas_url = href_to_abs('admin', 'controllers', ['edit', $this->controller->root_url, 'canvas']) . '?document_key=' . urlencode('vertical-slice-home');

        return $this->cms_template->render([
            'page_title'        => 'Служебная панель',
            'page_note'         => 'Внутренний dev/system слой: документы, миграция и диагностика. Основная пользовательская работа должна идти через живой canvas.',
            'workspace_url'     => href_to($this->root_url, 'workspace'),
            'visual_workspace_url' => $visual_workspace_url,
            'starter_canvas_url' => $starter_canvas_url,
            'workspace_summary' => $workspace_summary,
            'persistence'       => $workspace_summary['persistence'],
            'migration'         => $workspace_summary['migration'],
            'page_documents'    => $workspace_summary['page_documents'],
            'landing_pages'     => $this->model->getLandingbuilderPagesForImport(),
            'active_document'   => $active_document,
            'active_document_key' => $active_document_key,
            'primary_document_key' => $primary_document_key,
            'form_document_json'=> $form_document_json,
            'form_errors'       => $form_errors,
            'migration_report'  => $migration_report,
            'bulk_overwrite_existing' => $bulk_overwrite_existing
        ]);
    }
}