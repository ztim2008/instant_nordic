<?php

class actionNordicbuilderInstant extends cmsAction {

    private function resolveDesignPreviewUrl($bridge_model) {
        $preview_page_key = '';
        $pages = $bridge_model->getPagesForAdmin();

        if ($pages) {
            foreach ($pages as $page) {
                $page_key = (string) ($page['key'] ?? '');
                if ($page_key === 'homepage') {
                    $preview_page_key = 'homepage';
                    break;
                }

                if ($page_key !== '') {
                    $preview_page_key = $page_key;
                }
            }
        }

        if ($preview_page_key === '') {
            return '/';
        }

        return href_to('nordicbuilder', 'view', [$preview_page_key]);
    }

    private function extractThemePayload(array $data) {
        $keys = [
            'template_preset',
            'global_style_preset',
            'color_preset',
            'typography_preset',
            'container_preset',
            'section_spacing'
        ];

        $payload = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $payload[$key] = (string) $data[$key];
            }
        }

        return $payload;
    }

    public function run() {
        $bridge_model = cmsCore::getModel('landingbuilder');
        $catalog = $bridge_model->getThemeOptionCatalog();
        $form_catalog = $bridge_model->getThemeFormCatalog();
        $theme = $bridge_model->getSiteThemeDefaults((array) cmsController::loadOptions('landingbuilder'));
        $form = $this->getForm('instant_global', [$form_catalog]);

        if ($this->request->has('submit')) {
            $data = $form->parse($this->request, true);
            $errors = $form->validate($this, $data);
            $payload = $this->extractThemePayload($data);

            if (!$errors) {
                $theme = $bridge_model->saveSiteThemeSettings(array_merge($theme, $payload));
                cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');
                return $this->redirectToAction('instant');
            }

            $theme = array_merge($theme, $payload);
            cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
        }

        $screen = $bridge_model->getDesignSystemScreen($theme, $catalog);
        $screen['preview_url'] = $this->resolveDesignPreviewUrl($bridge_model);
        $screen['page_variant'] = 'instant';
        $screen['page_title'] = 'Нордик: Instant глобально';
        $screen['page_heading'] = 'InstantCMS: глобальные настройки сайта';
        $screen['page_description'] = 'Здесь живут общесайтовые настройки Instant: шаблон, базовая палитра, типографика и контейнеры. Эти настройки применяются глобально после сохранения.';
        $screen['form_card_title'] = 'InstantCMS: глобальные настройки';
        $screen['switch_url'] = href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'tokens']);
        $screen['switch_title'] = 'Токены блоков';

        return $this->cms_template->render('backend/instant', [
            'menu'   => $this->controller->getBackendMenu(),
            'theme'  => $theme,
            'screen' => $screen,
            'form'   => $form,
            'errors' => $errors ?? false
        ]);
    }
}
