<?php

class actionNordicbuilderDefaults extends cmsAction {

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

    public function run() {
        $bridge_model = cmsCore::getModel('landingbuilder');
        $catalog = $bridge_model->getThemeOptionCatalog();
        $form_catalog = $bridge_model->getThemeFormCatalog();
        $theme = $bridge_model->getSiteThemeDefaults((array) cmsController::loadOptions('landingbuilder'));
        $form = $this->getControllerForm('landingbuilder', 'design', [$form_catalog], 'backend/');

        if ($this->request->has('submit')) {
            $data = $form->parse($this->request, true);
            $errors = $form->validate($this, $data);

            if (!$errors) {
                $theme = $bridge_model->saveSiteThemeSettings($data);
                cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');
                return $this->redirectToAction('defaults');
            }

            $theme = $bridge_model->getSiteThemeDefaults(array_merge((array) cmsController::loadOptions('landingbuilder'), [
                'default_template_preset' => $data['template_preset'] ?? '',
                'default_global_style_preset' => $data['global_style_preset'] ?? '',
                'default_color_preset' => $data['color_preset'] ?? '',
                'default_typography_preset' => $data['typography_preset'] ?? '',
                'default_container_preset' => $data['container_preset'] ?? '',
                'default_button_preset' => $data['button_preset'] ?? '',
                'default_card_preset' => $data['card_preset'] ?? '',
                'default_section_spacing' => $data['section_spacing'] ?? '',
                'default_radius_preset' => $data['radius_preset'] ?? '',
                'default_density_preset' => $data['density_preset'] ?? '',
                'default_contrast_preset' => $data['contrast_preset'] ?? ''
            ]));

            cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
        }

        $screen = $bridge_model->getDesignSystemScreen($theme, $catalog);
        $screen['preview_url'] = $this->resolveDesignPreviewUrl($bridge_model);

        return $this->cms_template->render('backend/defaults', [
            'menu'   => $this->controller->getBackendMenu(),
            'theme'  => $theme,
            'screen' => $screen,
            'form'   => $form,
            'errors' => $errors ?? false
        ]);
    }
}