<?php

class widgetNordicbuilderRender extends cmsWidget {

    public function run() {

        $template = cmsTemplate::getInstance();
        $template->addTplCSSName('nordicbuilder_runtime');

        $model = cmsCore::getModel('nordicbuilder');
        if (!$model) {
            return [
                'html'       => '',
                'style_vars' => ''
            ];
        }

        $page_key = trim((string) ($this->options['page_key'] ?? ''));

        if ($page_key === '') {
            $core = cmsCore::getInstance();
            $uri = trim((string) ($core->uri ?? ''), '/');
            $page_key = $uri !== '' ? $uri : 'homepage';
        }

        $page_key = $model->sanitizePageKey($page_key);
        if ($page_key === '') {
            return [
                'html'       => '',
                'style_vars' => ''
            ];
        }

        $render = $model->getPublishedPageRenderByKey($page_key);
        if (!$render) {
            return [
                'html'       => '',
                'style_vars' => '',
                'page_key'   => $page_key,
                'hash'       => ''
            ];
        }

        $meta = isset($render['meta']) && is_array($render['meta']) ? $render['meta'] : [];

        $title = trim((string) ($meta['title'] ?? ''));
        if ($title !== '' && method_exists($template, 'setPageTitle')) {
            $template->setPageTitle($title);
        }

        $description = trim((string) ($meta['description'] ?? ''));
        if ($description !== '' && method_exists($template, 'setPageDescription')) {
            $template->setPageDescription($description);
        }

        $keywords = trim((string) ($meta['keywords'] ?? ''));
        if ($keywords !== '' && method_exists($template, 'setPageKeywords')) {
            $template->setPageKeywords($keywords);
        }

        $theme = isset($meta['theme']) && is_array($meta['theme']) ? $meta['theme'] : [];
        $style_vars = '';

        $root = cmsConfig::get('root_path');
        $template_name = method_exists($template, 'getName') ? $template->getName() : 'default';
        $theme_file = $root . 'templates/' . $template_name . '/controllers/landingbuilder/runtime_theme.php';

        if (!is_file($theme_file)) {
            $theme_file = $root . 'templates/default/controllers/landingbuilder/runtime_theme.php';
        }

        require_once $theme_file;

        if (function_exists('landingbuilder_get_runtime_theme_context_from_theme') && function_exists('landingbuilder_render_css_vars')) {
            $theme_context = landingbuilder_get_runtime_theme_context_from_theme($theme);
            $style_vars = landingbuilder_render_css_vars($theme_context['vars'] ?? []);
        }

        return [
            'html'       => (string) ($render['html'] ?? ''),
            'style_vars' => (string) $style_vars,
            'page_key'   => $page_key,
            'hash'       => (string) ($render['content_hash'] ?? '')
        ];
    }
}
