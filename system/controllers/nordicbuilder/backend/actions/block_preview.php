<?php

class actionNordicbuilderBlockPreview extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            return cmsCore::error404();
        }

        $source_key = trim((string) $this->request->get('source_key', ''));
        if ($source_key === '') {
            return $this->cms_template->renderJSON(['error' => true, 'message' => 'Block not found']);
        }

        $options = $this->request->get('options', []);
        if (!is_array($options)) {
            $decoded = json_decode((string) $options, true);
            $options = is_array($decoded) ? $decoded : [];
        }
        $template_name = $this->request->get('template', '');

        $runtime_renderer = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_renderer.php';
        if (is_file($runtime_renderer)) {
            require_once $runtime_renderer;
        }

        if (!function_exists('landingbuilder_render_runtime_block')) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Runtime renderer not available'
            ]);
        }

        $available_templates = cmsCore::getTemplates();
        if (!$template_name || !in_array($template_name, $available_templates, true)) {
            $template_name = cmsConfig::get('template');
        }

        $template = cmsTemplate::getInstance();
        $original_template_name = method_exists($template, 'getName') ? $template->getName() : '';

        $node = [
            'type' => 'block',
            'label' => $source_key,
            'source_key' => $source_key,
            'options' => $options
        ];

        try {
            if ($template_name && $original_template_name && $template_name !== $original_template_name) {
                $template->setBaseTemplate($template_name);
            }

            $html = (string) landingbuilder_render_runtime_block($node, ['surface' => 'runtime']);
        } catch (Throwable $exception) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Ошибка рендера блока: ' . $exception->getMessage()
            ]);
        } finally {
            if ($original_template_name && $template->getName() !== $original_template_name) {
                $template->setBaseTemplate($original_template_name);
            }
        }

        return $this->cms_template->renderJSON([
            'error' => false,
            'html' => $html,
            'template' => $template_name
        ]);
    }
}
