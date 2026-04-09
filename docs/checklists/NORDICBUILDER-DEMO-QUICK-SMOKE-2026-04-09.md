# Nordicbuilder: Demo Quick Smoke (Routes + Trace) — 2026-04-09

Короткий чеклист этапа 3 для режима `Quick Demo`.

## Цель

Подтвердить, что после установки demo-контента ключевые маршруты:

1. открываются без регрессий (`HTTP 200`);
2. сохраняют ожидаемую route-ветку (overlay vs non-overlay);
3. не ломают parity guest/admin на уровне базовой структуры.

## Подготовка

1. В админке `Страницы` установить `Quick Demo`.
2. Убедиться, что сайт работает на шаблоне `nordic`.
3. Подготовить admin cookie (`icms[auth]`) для trace-проверки.
4. Перейти в корень проекта.

## Маршруты для smoke

1. `/`
2. `/board`
3. `/board/nedvizhimost`
4. `/board/7-prodam-kvartiru-v-novostroike.html`
5. `/users/1`

## Автопрогон

```bash
ADMIN_COOKIE='icms[auth]=<token>' REQUIRE_ADMIN_TRACE=1 bash scripts/nordicbuilder-demo-quick-smoke.sh
```

Дополнительно:

1. Если нужен только guest smoke, можно запускать без `ADMIN_COOKIE`.
2. Для строгой проверки admin trace использовать `REQUIRE_ADMIN_TRACE=1`.

## Критерии PASS

1. Все 5 URL возвращают `HTTP 200` для `guest` и `admin`.
2. Для `/board/nedvizhimost` и `/users/1` в trace подтверждено:
   - `overlay=1`;
   - `reason=overlay-or-landingbuilder-route`.
3. Для `/board` и `/board/<item>.html` подтверждено отсутствие overlay-ветки (`overlay=0`).
4. Скрипт завершает выполнение со статусом `PASS`.

## Если FAIL

1. Сохранить stdout smoke-скрипта.
2. Зафиксировать URL, роль и trace summary из таблицы.
3. Добавить запись в `docs/WORKLOG.md` с пометкой `FAIL`.

## Результат выполнения (2026-04-09, auto-smoke)

Статус: PASS

Исполнитель: GitHub Copilot (автоматический smoke-run, guest+admin trace).

Сводка:

| Role | URL | HTTP | Trace summary |
| --- | --- | --- | --- |
| guest | `/` | `200` | `ok` |
| guest | `/board` | `200` | `ok` |
| guest | `/board/nedvizhimost` | `200` | `ok` |
| guest | `/board/7-prodam-kvartiru-v-novostroike.html` | `200` | `ok` |
| guest | `/users/1` | `200` | `ok` |
| admin | `/` | `200` | `overlay=0, branch=template.takeover-applied, effective=home` |
| admin | `/board` | `200` | `overlay=0, branch=template.takeover-applied, effective=e2e-pri-high` |
| admin | `/board/nedvizhimost` | `200` | `overlay=1, reason=overlay-or-landingbuilder-route, branch=template.takeover-skip` |
| admin | `/board/7-prodam-kvartiru-v-novostroike.html` | `200` | `overlay=0, branch=template.takeover-applied, effective=vse` |
| admin | `/users/1` | `200` | `overlay=1, reason=overlay-or-landingbuilder-route, branch=template.takeover-skip` |

Вывод:

1. Stage 3 demo quick-smoke закрыт: ключевые URL и trace-ветки валидированы.
2. Скрипт `scripts/nordicbuilder-demo-quick-smoke.sh` можно использовать как повторяемый regression-smoke для demo lifecycle.
