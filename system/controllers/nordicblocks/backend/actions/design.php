<?php

class actionNordicblocksDesign extends cmsAction {

    public function run() {
        // AJAX: вернуть пересчитанный CSS без сохранения (живой превью)
        if ($this->request->get('preview_tokens', false)) {
            $input = $this->parseTokensFromRequest();
            header('Content-Type: text/css; charset=utf-8');
            echo $this->model->buildInlineCss($input);
            exit;
        }

        $tokens = $this->model->getDesignTokens();
        $errors = [];

        if ($this->request->has('submit')) {
            $input = $this->parseTokensFromRequest();
            $errors = $this->validateTokens($input);

            if (!$errors) {
                $this->model->saveDesignTokens($input);
                cmsUser::addSessionMessage('Дизайн‑система сохранена', 'success');
                return $this->redirectToAction('design');
            }

            $tokens = array_merge($tokens, $input);
            cmsUser::addSessionMessage('Проверьте ошибки в форме', 'error');
        }

        $inline_css = $this->model->buildInlineCss($tokens);
        $presets    = $this->model->getThemePresets();

        return $this->cms_template->render('backend/design', [
            'menu'       => $this->controller->getBackendMenu(),
            'tokens'     => $tokens,
            'inline_css' => $inline_css,
            'errors'     => $errors,
            'presets'    => $presets,
        ]);
    }

    private function parseTokensFromRequest() {
        return [
            'color_accent'      => (string) $this->request->get('color_accent',     '#b42318'),
            'color_bg'          => (string) $this->request->get('color_bg',         '#ffffff'),
            'color_bg_alt'      => (string) $this->request->get('color_bg_alt',     '#f7f7f6'),
            'color_surface'     => (string) $this->request->get('color_surface',    '#ffffff'),
            'color_border'      => (string) $this->request->get('color_border',     '#e5e7eb'),
            'color_text'        => (string) $this->request->get('color_text',       '#1a1a1a'),
            'color_text_muted'  => (string) $this->request->get('color_text_muted', '#6b7280'),
            'font_body'         => (string) $this->request->get('font_body',        'sans'),
            'font_head'         => (string) $this->request->get('font_head',        'sans'),
            'radius_preset'     => (string) $this->request->get('radius_preset',    'md'),
            'shadow_preset'     => (string) $this->request->get('shadow_preset',    'md'),
            'section_spacing'   => (string) $this->request->get('section_spacing',  'comfortable'),
            'btn_style'         => (string) $this->request->get('btn_style',        'primary'),
        ];
    }

    private function validateTokens(array $t) {
        $errors = [];
        $color_fields = ['color_accent', 'color_bg', 'color_bg_alt', 'color_surface', 'color_border', 'color_text', 'color_text_muted'];
        foreach ($color_fields as $f) {
            $val = trim($t[$f] ?? '');
            if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $val)) {
                $errors[$f] = 'Некорректный HEX‑цвет';
            }
        }
        $allowed_radius  = ['none', 'sm', 'md', 'lg', 'xl', 'pill'];
        $allowed_shadow  = ['none', 'sm', 'md', 'lg'];
        $allowed_spacing = ['compact', 'comfortable', 'spacious'];
        $allowed_font    = ['sans', 'serif'];
        $allowed_btn     = ['primary', 'outline', 'ghost'];

        if (!in_array($t['radius_preset']   ?? '', $allowed_radius,  true)) { $errors['radius_preset']   = 'Неверное значение'; }
        if (!in_array($t['shadow_preset']   ?? '', $allowed_shadow,  true)) { $errors['shadow_preset']   = 'Неверное значение'; }
        if (!in_array($t['section_spacing'] ?? '', $allowed_spacing, true)) { $errors['section_spacing'] = 'Неверное значение'; }
        if (!in_array($t['font_body']       ?? '', $allowed_font,    true)) { $errors['font_body']       = 'Неверное значение'; }
        if (!in_array($t['font_head']       ?? '', $allowed_font,    true)) { $errors['font_head']       = 'Неверное значение'; }
        if (!in_array($t['btn_style']       ?? '', $allowed_btn,     true)) { $errors['btn_style']       = 'Неверное значение'; }

        return $errors;
    }
}
