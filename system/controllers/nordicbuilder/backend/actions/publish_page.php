<?php

class actionNordicbuilderPublishPage extends cmsAction {

    public function run() {

        if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
            return cmsCore::error404();
        }

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrf_token)) {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Некорректный CSRF token.'
            ]);
        }

        $page_key = (string) $this->request->get('key', '');

        $model = cmsCore::getModel('nordicbuilder');
        if (!$model) {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Модель nordicbuilder не найдена.'
            ]);
        }

        $page_key = $model->sanitizePageKey($page_key);
        if ($page_key === '') {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Некорректный ключ страницы.'
            ]);
        }

        $stored = $model->getPageDocumentByKey($page_key);
        if (!$stored || empty($stored['document']) || !is_array($stored['document'])) {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Черновик PageDocument не найден.'
            ]);
        }

        $document = $stored['document'];

        $template = cmsTemplate::getInstance();
        $template_name = method_exists($template, 'getName') ? $template->getName() : 'default';
        $root = cmsConfig::get('root_path');
        $base = $root . 'templates/' . $template_name . '/controllers/landingbuilder/';
        $theme_file = $base . 'runtime_theme.php';
        $renderer_file = $base . 'runtime_renderer.php';

        if (!is_file($theme_file)) {
            $theme_file = $root . 'templates/default/controllers/landingbuilder/runtime_theme.php';
        }
        if (!is_file($renderer_file)) {
            $renderer_file = $root . 'templates/default/controllers/landingbuilder/runtime_renderer.php';
        }

        require_once $theme_file;
        require_once $renderer_file;

        $theme = isset($document['theme']) && is_array($document['theme']) ? $document['theme'] : [];
        $theme_context = function_exists('landingbuilder_get_runtime_theme_context_from_theme')
            ? landingbuilder_get_runtime_theme_context_from_theme($theme)
            : ['theme' => $theme, 'vars' => []];

        $zone = null;
        foreach ((array) ($document['zones'] ?? []) as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $zone_key = (string) ($candidate['key'] ?? ($candidate['zone_key'] ?? ''));
            if ($zone_key === 'content_body') {
                $zone = $candidate;
                if (empty($zone['key'])) {
                    $zone['key'] = $zone_key;
                }
                break;
            }
        }

        $html = '';
        if ($zone) {
            $html = (string) landingbuilder_render_runtime_zone_sections($zone, [
                'surface'      => 'site',
                'device_type'  => 'desktop',
                'theme_context'=> $theme_context
            ]);
        }

        $doc_meta = isset($document['meta']) && is_array($document['meta']) ? $document['meta'] : [];
        $meta = [
            'schema_version' => (string) ($document['schema_version'] ?? '1.0'),
            'title'          => (string) ($doc_meta['seo_title'] ?? $doc_meta['title'] ?? ($document['title'] ?? '')),
            'description'    => (string) ($doc_meta['seo_description'] ?? $doc_meta['description'] ?? ''),
            'keywords'       => (string) ($doc_meta['seo_keywords'] ?? $doc_meta['keywords'] ?? ''),
            'theme'          => $theme
        ];

        $saved = $model->savePublishedPageRender($page_key, $meta, $html, $this->cms_user->id);
        if (!$saved) {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Не удалось сохранить опубликованный SSR-рендер. Таблица nordicbuilder_page_renders отсутствует или не может быть создана автоматически (проверьте права БД на CREATE TABLE).'
            ]);
        }

        return $this->cms_template->renderJSON([
            'error' => false,
            'render' => [
                'page_key'      => $page_key,
                'title'         => $meta['title'],
                'content_hash'  => (string) ($saved['content_hash'] ?? ''),
                'published_at'  => (string) ($saved['published_at'] ?? ''),
                'updated_at'    => (string) ($saved['updated_at'] ?? '')
            ]
        ]);
    }
}
