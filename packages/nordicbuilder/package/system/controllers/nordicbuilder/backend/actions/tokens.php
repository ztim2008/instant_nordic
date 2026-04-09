<?php

class actionNordicbuilderTokens extends cmsAction {

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
            'button_preset',
            'card_preset',
            'surface_preset',
            'radius_preset',
            'density_preset',
            'contrast_preset'
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
        $form = $this->getForm('tokens', [$form_catalog]);

        if ($this->request->has('submit')) {
            $data = $form->parse($this->request, true);
            $errors = $form->validate($this, $data);
            $payload = $this->extractThemePayload($data);

            if (!$errors) {
                $theme = $bridge_model->saveSiteThemeSettings(array_merge($theme, $payload));
                cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');
                return $this->redirectToAction('tokens');
            }

            $theme = array_merge($theme, $payload);
            cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
        }

        $screen = $bridge_model->getDesignSystemScreen($theme, $catalog);
        $screen['preview_url'] = $this->resolveDesignPreviewUrl($bridge_model);
        $screen['page_variant'] = 'tokens';
        $screen['page_title'] = 'Нордик: Токены блоков';
        $screen['page_heading'] = 'Токены будущих блоков';
        $screen['page_description'] = 'Эта страница управляет токенами нашей библиотеки блоков: кнопки, карточки, поверхности, скругления и контраст. Изменения сохраняются глобально.';
        $screen['form_card_title'] = 'Токены блоков: глобальные настройки';
        $screen['switch_url'] = href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'instant']);
        $screen['switch_title'] = 'Instant: глобальные';

        return $this->cms_template->render('backend/tokens', [
            'menu'   => $this->controller->getBackendMenu(),
            'theme'  => $theme,
            'screen' => $screen,
            'form'   => $form,
            'errors' => $errors ?? false
        ]);
    }
}
