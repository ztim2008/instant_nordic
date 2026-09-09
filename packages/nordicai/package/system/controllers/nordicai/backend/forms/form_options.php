<?php

class formNordicaiOptions extends cmsForm {

    public $is_tabbed = true;

    public function init() {

        return [
            [
                'type'  => 'fieldset',
                'title' => LANG_NORDICAI_OPT_DEEPSEEK,
                'childs' => [
                    new fieldString('deepseek_api_key', [
                        'title'       => LANG_NORDICAI_OPT_API_KEY,
                        'hint'        => LANG_NORDICAI_OPT_API_KEY_HINT,
                        'is_password' => true,
                        'options'     => ['max_length' => 256],
                    ]),
                    new fieldString('deepseek_base_url', [
                        'title'   => LANG_NORDICAI_OPT_BASE_URL,
                        'default' => 'https://api.deepseek.com',
                        'hint'    => LANG_NORDICAI_OPT_BASE_URL_HINT,
                    ]),
                    new fieldList('deepseek_model', [
                        'title'   => LANG_NORDICAI_OPT_MODEL,
                        'default' => 'deepseek-chat',
                        'items'   => [
                            'deepseek-chat'     => 'deepseek-chat',
                            'deepseek-reasoner' => 'deepseek-reasoner',
                            'deepseek-v4-flash' => 'deepseek-v4-flash',
                            'deepseek-v4-pro'   => 'deepseek-v4-pro',
                        ],
                    ]),
                    new fieldNumber('deepseek_timeout', [
                        'title'   => LANG_NORDICAI_OPT_TIMEOUT,
                        'default' => 60,
                        'units'   => LANG_SECONDS,
                        'rules'   => [['min', 10], ['max', 180]],
                    ]),
                ],
            ],
            [
                'type'  => 'fieldset',
                'title' => LANG_NORDICAI_OPT_RULES,
                'childs' => [
                    new fieldCheckbox('strict_tokens_only', [
                        'title'   => LANG_NORDICAI_OPT_STRICT_TOKENS,
                        'hint'    => LANG_NORDICAI_OPT_STRICT_TOKENS_HINT,
                        'default' => 1,
                    ]),
                    new fieldCheckbox('allow_scoped_css', [
                        'title'   => LANG_NORDICAI_OPT_ALLOW_CSS,
                        'hint'    => LANG_NORDICAI_OPT_ALLOW_CSS_HINT,
                        'default' => 1,
                    ]),
                ],
            ],
        ];
    }

}
