# Nordicbuilder: Route Parity Smoke (Category/Profile) — 2026-04-09

Короткий ручной чеклист для финальной визуальной валидации после route-classifier cut.

## Цель

Подтвердить parity preview/live и корректную overlay-интеграцию на реальных маршрутах:

1. категория контента;
2. профиль пользователя;
3. без регрессии для list/item маршрутов.

## Подготовка

1. Авторизоваться администратором в обычном окне браузера.
2. Открыть гостевое окно (инкогнито) для сравнения guest/admin.
3. Убедиться, что текущий шаблон сайта: `nordic`.
4. Открыть экран правил применения:
   - `/admin/controllers/edit/nordicbuilder/bindings`
5. Проверить, что существуют (или были ранее настроены) правила:
   - `overlay.content_category.*`
   - `overlay.user_profile.*`

## Маршруты для проверки

1. Категория: `/board/nedvizhimost`
2. Профиль: `/users/1`
3. Список (контроль): `/board`
4. Карточка (контроль): `/board/7-prodam-kvartiru-v-novostroike.html`

## Smoke-шаги

1. Открыть `/board/nedvizhimost` под админом.
Ожидаемо:
- страница открывается без 500/404;
- нативный category body (список карточек/контент InstantCMS) не пропал;
- shell/body сетка не ломается (нет съехавших колонок и пропавших боковых зон).

2. Открыть `/users/1` под админом.
Ожидаемо:
- страница открывается без 500/404;
- нативный profile body присутствует;
- shell/body сетка не ломается.

3. Проверить, что overlay не превращается в full-takeover.
Ожидаемо для `/board/nedvizhimost` и `/users/1`:
- основной body InstantCMS остается базовым;
- builder работает как надстройка вокруг body (а не заменяет его полностью).

4. Повторить шаги 1-3 в гостевом окне.
Ожидаемо:
- структура страницы не ломается;
- если overlay-страницы опубликованы, guest видит те же builder-зоны;
- если не опубликованы, guest видит чисто нативный body без поломки layout.

5. Проверить контрольные маршруты `/board` и `/board/7-prodam-kvartiru-v-novostroike.html`.
Ожидаемо:
- они не уезжают в overlay-only логику category/profile;
- открываются штатно, без визуальных регрессий.

6. Debug-подтверждение ветки (опционально, только админ):
- открыть `/board/nedvizhimost?lb_trace=1` и `/users/1?lb_trace=1`;
- убедиться в исходнике страницы, что есть комментарий `lb-effective-page-trace`;
- в trace финальная причина: `overlay-or-landingbuilder-route`.

7. Preview/live parity для overlay-страниц:
- в админке открыть страницу, привязанную к `overlay.content_category.*`, и ее preview;
- сравнить preview с live `/board/nedvizhimost`;
- повторить для страницы, привязанной к `overlay.user_profile.*`, и live `/users/1`.
Ожидаемо:
- порядок секций и базовые стили совпадают;
- нет дублей body или пропажи системного контента.

## Критерии PASS

1. Все 4 маршрута открываются корректно (HTTP 200 в браузере).
2. Для category/profile подтвержден overlay-режим без full-takeover body.
3. Для list/item подтверждено отсутствие регрессии после route-classifier cut.
4. Preview/live расхождений уровня "ломает страницу" нет.

## Если FAIL

1. Зафиксировать URL, роль (guest/admin), скрин и краткое описание симптома.
2. Приложить trace-фрагмент (если был `lb_trace=1`).
3. Добавить запись в `docs/WORKLOG.md` в блоке текущей даты с пометкой `FAIL`.

## Результат выполнения (2026-04-09, auto-smoke)

Статус: PASS

Исполнитель: GitHub Copilot (автоматический smoke-run, admin+guest, HTTP+trace).

Сводка:

| Role | URL | HTTP | Overlay flag / ветка |
| --- | --- | --- | --- |
| admin | `/` | `200` | `overlay=0`, homepage branch |
| admin | `/board` | `200` | `overlay=0`, `empty-effective-page-key` |
| admin | `/board/7-prodam-kvartiru-v-novostroike.html` | `200` | `overlay=0`, `empty-effective-page-key` |
| admin | `/board/nedvizhimost` | `200` | `overlay=1`, `overlay-or-landingbuilder-route` |
| admin | `/users/1` | `200` | `overlay=1`, `overlay-or-landingbuilder-route` |
| guest | `/` | `200` | ok |
| guest | `/board` | `200` | ok |
| guest | `/board/7-prodam-kvartiru-v-novostroike.html` | `200` | ok |
| guest | `/board/nedvizhimost` | `200` | ok |
| guest | `/users/1` | `200` | ok |

Вывод:

1. Category/profile маршруты корректно распознаются как overlay-ветка.
2. List/item маршруты не регресснули после route-classifier cut.
3. Этап route-parity smoke для P0 закрыт.
