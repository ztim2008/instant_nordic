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

            $tokens = $this->model->normalizeDesignTokens(array_replace_recursive($tokens, $input));
            cmsUser::addSessionMessage('Проверьте ошибки в форме', 'error');
        }

        $inline_css = $this->model->buildInlineCss($tokens);
        $presets    = $this->model->getThemePresets();
        $assets_dir = dirname(dirname(__DIR__)) . '/assets';
        $tokens_css = @file_get_contents($assets_dir . '/tokens.css') ?: '';
        $blocks_css = @file_get_contents($assets_dir . '/blocks.css') ?: '';

        return $this->cms_template->render('backend/design', [
            'menu'       => $this->controller->getBackendMenu(),
            'tokens'     => $tokens,
            'inline_css' => $inline_css,
            'tokens_css' => $tokens_css,
            'blocks_css' => $blocks_css,
            'errors'     => $errors,
            'presets'    => $presets,
        ]);
    }

    private function parseTokensFromRequest() {
        return [
            'version'    => 2,
            'colors'     => [
                'accent'                => (string) $this->request->get('color_accent', '#b42318'),
                'bg'                    => (string) $this->request->get('color_bg', '#ffffff'),
                'bg_alt'                => (string) $this->request->get('color_bg_alt', '#f7f7f6'),
                'surface'               => (string) $this->request->get('color_surface', '#ffffff'),
                'border'                => (string) $this->request->get('color_border', '#e5e7eb'),
                'text'                  => (string) $this->request->get('color_text', '#1a1a1a'),
                'text_muted'            => (string) $this->request->get('color_text_muted', '#6b7280'),
                'button_primary_bg'     => (string) $this->request->get('button_primary_bg', '#b42318'),
                'button_primary_bg_hover' => (string) $this->request->get('button_primary_bg_hover', '#8a1910'),
                'button_primary_text'   => (string) $this->request->get('button_primary_text', '#ffffff'),
                'button_primary_border' => (string) $this->request->get('button_primary_border', '#b42318'),
                'button_outline_text'   => (string) $this->request->get('button_outline_text', '#b42318'),
                'button_outline_border' => (string) $this->request->get('button_outline_border', '#b42318'),
                'button_ghost_text'     => (string) $this->request->get('button_ghost_text', '#1a1a1a'),
                'button_ghost_border'   => (string) $this->request->get('button_ghost_border', '#e5e7eb'),
            ],
            'typography' => [
                'font_body'   => (string) $this->request->get('font_body', 'sans'),
                'font_head'   => (string) $this->request->get('font_head', 'sans'),
                'font_button' => (string) $this->request->get('font_button', ''),
            ],
            'layout'     => [
                'section_spacing' => (string) $this->request->get('section_spacing', 'comfortable'),
            ],
            'radii'      => [
                'base'   => (string) $this->request->get('radius_preset', 'md'),
                'card'   => (string) $this->request->get('card_radius_preset', 'lg'),
                'button' => (string) $this->request->get('button_radius_preset', 'md'),
                'media'  => (string) $this->request->get('media_radius_preset', 'lg'),
            ],
            'buttons'    => [
                'style'           => (string) $this->request->get('btn_style', 'primary'),
                'size'            => (string) $this->request->get('btn_size', 'md'),
                'hover_animation' => (string) $this->request->get('btn_hover_animation', 'lift'),
                'glint_color'     => (string) $this->request->get('btn_glint_color', '#ffffff'),
                'glint_duration'  => (int) $this->request->get('btn_glint_duration', 900),
            ],
            'cards'      => [
                'border_width'   => (int) $this->request->get('card_border_width', 1),
                'shadow_preset'  => (string) $this->request->get('shadow_preset', 'md'),
                'surface_motion' => (string) $this->request->get('surface_motion', '1'),
            ],
        ];
    }

    private function validateTokens(array $t) {
        $errors = [];
        $color_fields = [
            ['colors', 'accent'],
            ['colors', 'bg'],
            ['colors', 'bg_alt'],
            ['colors', 'surface'],
            ['colors', 'border'],
            ['colors', 'text'],
            ['colors', 'text_muted'],
            ['colors', 'button_primary_bg'],
            ['colors', 'button_primary_bg_hover'],
            ['colors', 'button_primary_text'],
            ['colors', 'button_primary_border'],
            ['colors', 'button_outline_text'],
            ['colors', 'button_outline_border'],
            ['colors', 'button_ghost_text'],
            ['colors', 'button_ghost_border'],
            ['buttons', 'glint_color'],
        ];
        foreach ($color_fields as $path) {
            $val = trim((string) $this->getTokenPath($t, $path));
            if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $val)) {
                $errors[implode('.', $path)] = 'Некорректный HEX‑цвет';
            }
        }
        $allowed_radius  = ['none', 'sm', 'md', 'lg', 'xl', 'pill'];
        $allowed_shadow  = ['none', 'sm', 'md', 'lg'];
        $allowed_spacing = ['compact', 'comfortable', 'spacious'];
        $allowed_font    = ['sans', 'serif', 'mono', 'display', ''];
        $allowed_btn     = ['primary', 'outline', 'ghost'];
        $allowed_btn_size = ['sm', 'md', 'lg'];
        $allowed_hover    = ['none', 'lift', 'grow', 'glow', 'glint'];

        if (!in_array($this->getTokenPath($t, ['radii', 'base']), $allowed_radius, true)) { $errors['radii.base'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['radii', 'card']), $allowed_radius, true)) { $errors['radii.card'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['radii', 'button']), $allowed_radius, true)) { $errors['radii.button'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['radii', 'media']), $allowed_radius, true)) { $errors['radii.media'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['cards', 'shadow_preset']), $allowed_shadow, true)) { $errors['cards.shadow_preset'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['layout', 'section_spacing']), $allowed_spacing, true)) { $errors['layout.section_spacing'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['typography', 'font_body']), $allowed_font, true)) { $errors['typography.font_body'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['typography', 'font_head']), $allowed_font, true)) { $errors['typography.font_head'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['typography', 'font_button']), $allowed_font, true)) { $errors['typography.font_button'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['buttons', 'style']), $allowed_btn, true)) { $errors['buttons.style'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['buttons', 'size']), $allowed_btn_size, true)) { $errors['buttons.size'] = 'Неверное значение'; }
        if (!in_array($this->getTokenPath($t, ['buttons', 'hover_animation']), $allowed_hover, true)) { $errors['buttons.hover_animation'] = 'Неверное значение'; }

        $border_width = (int) $this->getTokenPath($t, ['cards', 'border_width'], 1);
        if ($border_width < 0 || $border_width > 6) {
            $errors['cards.border_width'] = 'Допустимо 0..6 px';
        }

        $glint_duration = (int) $this->getTokenPath($t, ['buttons', 'glint_duration'], 900);
        if ($glint_duration < 250 || $glint_duration > 3000) {
            $errors['buttons.glint_duration'] = 'Допустимо 250..3000 мс';
        }

        return $errors;
    }

    private function getTokenPath(array $tokens, array $path, $default = null) {
        $cursor = $tokens;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }
        return $cursor;
    }
}
