# Nordic Builder Design System Split Smoke - 2026-04-09

## Scope

- Проверка двух страниц дизайн-системы после split:
  - `/admin/controllers/edit/nordicbuilder/instant`
  - `/admin/controllers/edit/nordicbuilder/tokens`
- Проверка базовых переходов и технической готовности к manual smoke.

## Preconditions

- Текущая browser-сессия инструмента не авторизована в админке `nordic-builder.store`.
- Визуальная проверка форм и live iframe требует действующей admin-сессии.

## Checklist

1. Открытие страницы Instant в инструменте browser.
   - Результат: BLOCKED
   - Факт: ответ "Доступ запрещён" (требуется авторизация).

2. Открытие страницы Tokens в инструменте browser.
   - Результат: BLOCKED
   - Факт: ответ "Доступ запрещён" (требуется авторизация).

3. Проверка, что split-маршруты и формы присутствуют в коде.
   - Результат: PASS
   - Факт:
     - `actionNordicbuilderInstant` и `actionNordicbuilderTokens` созданы;
     - `form_instant_global` и `form_tokens` созданы;
     - menu и canvas ссылки ведут на новые split-route.

4. Техническая проверка синтаксиса и mirror parity.
   - Результат: PASS
   - Факт:
     - `php -l` без ошибок для live/mirror файлов split;
     - `cmp -s` подтверждает parity live -> package mirrors.

## Overall Status

- BLOCKED (manual UI smoke)
- Причина: отсутствует авторизованная admin-сессия в инструменте browser.

## Next Step

1. Повторить пункты 1-2 в авторизованной сессии админки.
2. Прогнать интерактивный smoke:
   - смена 2-3 пресетов на каждой странице;
   - проверка live iframe реакции без сохранения;
   - сохранение и reload с проверкой persistence.
3. Обновить этот чеклист до PASS/FAIL с деталями.
