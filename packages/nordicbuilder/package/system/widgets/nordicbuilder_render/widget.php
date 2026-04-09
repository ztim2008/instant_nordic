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

        // Backward-compatible mode: explicit page_key -> use published standalone render.
        $page_key = trim((string) ($this->options['page_key'] ?? ''));
        if ($page_key !== '') {
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

        // Foundation mode: resolve binding for current request and use runtime context cache.
        $runtime_context = method_exists($model, 'buildRuntimeContextFromGlobals')
            ? (array) $model->buildRuntimeContextFromGlobals()
            : [];

        $binding = method_exists($model, 'resolveBindingForRuntimeContext')
            ? $model->resolveBindingForRuntimeContext($runtime_context)
            : false;

        if (!$binding || empty($binding['page_key']) || empty($binding['binding_key'])) {
            return [
                'html'       => '',
                'style_vars' => ''
            ];
        }

        $binding_key = (string) $binding['binding_key'];
        $page_key = (string) $binding['page_key'];
        $context_key = method_exists($model, 'buildRuntimeContextKey')
            ? (string) $model->buildRuntimeContextKey($runtime_context)
            : '';

        if ($context_key === '') {
            return [
                'html'       => '',
                'style_vars' => ''
            ];
        }

        $stored = $model->getPageDocumentByKey($page_key);
        if (!$stored || empty($stored['document']) || !is_array($stored['document'])) {
            return [
                'html'       => '',
                'style_vars' => ''
            ];
        }

        $document = $stored['document'];

        $theme = isset($document['theme']) && is_array($document['theme']) ? $document['theme'] : [];
        $theme_signature = hash('sha256', json_encode([
            'template' => method_exists($template, 'getName') ? $template->getName() : 'default',
            'theme' => $theme
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // Cache only for guests by default.
        $is_guest = empty($this->cms_user) || empty($this->cms_user->id);
        if ($is_guest && method_exists($model, 'getPublishedRuntimeRender')) {
            $cached = $model->getPublishedRuntimeRender($binding_key, $context_key, $theme_signature);
            if ($cached) {
                $meta = isset($cached['meta']) && is_array($cached['meta']) ? $cached['meta'] : [];
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

                $style_vars = $this->buildStyleVarsFromTheme($template, $theme);

                return [
                    'html'         => (string) ($cached['html'] ?? ''),
                    'style_vars'   => (string) $style_vars,
                    'binding_key'  => $binding_key,
                    'page_key'     => $page_key,
                    'context_key'  => $context_key,
                    'hash'         => (string) ($cached['content_hash'] ?? '')
                ];
            }
        }

        $style_vars = $this->buildStyleVarsFromTheme($template, $theme);

        $root = cmsConfig::get('root_path');
        $template_name = method_exists($template, 'getName') ? $template->getName() : 'default';
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
        if ($zone && function_exists('landingbuilder_render_runtime_zone_sections')) {
            $html = (string) landingbuilder_render_runtime_zone_sections($zone, [
                'surface'       => 'site',
                'device_type'   => 'desktop',
                'theme_context' => $theme_context
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

        if ($is_guest && method_exists($model, 'savePublishedRuntimeRender')) {
            $model->savePublishedRuntimeRender($binding_key, $context_key, $theme_signature, $meta, $html, 0);
        }

        return [
            'html'         => (string) $html,
            'style_vars'   => (string) $style_vars,
            'binding_key'  => $binding_key,
            'page_key'     => $page_key,
            'context_key'  => $context_key,
            'hash'         => hash('sha256', $binding_key . "\n" . $context_key . "\n" . $theme_signature . "\n" . json_encode($meta) . "\n" . $html)
        ];

    }

    protected function buildStyleVarsFromTheme($template, array $theme) {
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

        return (string) $style_vars;
    }
}
