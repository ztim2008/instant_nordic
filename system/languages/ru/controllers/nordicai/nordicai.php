<?php

define('LANG_NORDICAI_CONTROLLER', 'Nordic AI Agent');
define('LANG_NORDICAI_TITLE', 'Nordic AI Agent');

define('LANG_NORDICAI_CP_AGENT', 'Агент');
define('LANG_NORDICAI_CP_OPTIONS_AGENT', 'Настройки агента');

define('LANG_NORDICAI_OPT_DEEPSEEK', 'DeepSeek');
define('LANG_NORDICAI_OPT_API_KEY', 'API ключ DeepSeek');
define('LANG_NORDICAI_OPT_API_KEY_HINT', 'Ключ с https://platform.deepseek.com — хранится в options компонента.');
define('LANG_NORDICAI_OPT_BASE_URL', 'Base URL');
define('LANG_NORDICAI_OPT_BASE_URL_HINT', 'По умолчанию https://api.deepseek.com (OpenAI-compatible).');
define('LANG_NORDICAI_OPT_MODEL', 'Модель');
define('LANG_NORDICAI_OPT_TIMEOUT', 'Таймаут запроса');
define('LANG_NORDICAI_OPT_RULES', 'Правила гипотезы');
define('LANG_NORDICAI_OPT_STRICT_TOKENS', 'Сначала tokens');
define('LANG_NORDICAI_OPT_STRICT_TOKENS_HINT', 'AI должен предпочитать CSS variables, а не произвольный CSS.');
define('LANG_NORDICAI_OPT_ALLOW_CSS', 'Разрешить scoped CSS');
define('LANG_NORDICAI_OPT_ALLOW_CSS_HINT', 'Если выключено — только tokens, без css-поля.');

define('LANG_NORDICAI_AGENT_TITLE', 'AI Skin Agent (MVP)');
define('LANG_NORDICAI_AGENT_HINT', 'Гипотеза: промпт → безопасный patch (tokens / scoped CSS), без правок tpl и ядра.');
define('LANG_NORDICAI_AGENT_PROMPT', 'Промпт');
define('LANG_NORDICAI_AGENT_SELECTOR', 'Селектор (опционально)');
define('LANG_NORDICAI_AGENT_CONTEXT', 'Контекст DOM/CSS (опционально)');
define('LANG_NORDICAI_AGENT_GENERATE', 'Сгенерировать patch');
define('LANG_NORDICAI_AGENT_RESULT', 'Результат');
define('LANG_NORDICAI_AGENT_NO_KEY', 'Сначала укажите API ключ DeepSeek в настройках агента.');
define('LANG_NORDICAI_AGENT_OPEN_OPTIONS', 'Открыть настройки');
define('LANG_NORDICAI_AGENT_RECENT', 'Последние запуски');
define('LANG_NORDICAI_LIVE_OPEN', 'Открыть живой сайт с инспектором');
define('LANG_NORDICAI_LIVE_HINT', 'На сайте (под админом) справа внизу кнопка «Выбрать элемент» → клик по блоку → промпт → Save.');

define('LANG_NORDICAI_ERR_EMPTY_PROMPT', 'Пустой промпт');
define('LANG_NORDICAI_ERR_NO_KEY', 'Не задан DeepSeek API key');
define('LANG_NORDICAI_ERR_BAD_JSON', 'Модель вернула невалидный JSON');
define('LANG_NORDICAI_ERR_EMPTY_PATCH', 'Патч пустой после фильтрации');
