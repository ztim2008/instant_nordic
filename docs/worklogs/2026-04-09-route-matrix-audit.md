# 2026-04-09 — P0 Audit: Route Matrix (effective page key)

## Цель

Собрать детерминированную матрицу выбора `effective page key` и зафиксировать текущий порядок ветвлений в runtime.

## Источники кода

1. `templates/nordic/main.tpl.php`
2. `system/controllers/landingbuilder/model.php`

## Текущий pipeline (по коду)

1. Front template проверяет route context и исключает full-takeover для overlay-маршрутов `content/category` и `users/profile`.
2. Для остальных маршрутов вызывается `resolveFullTakeoverPageKeyFromBindings(route_params, '')`.
3. Если bindings не дали ключ, применяется route-aware fallback по `page_type` (`homepage/content-list/content-item/generic`).
4. Для главной есть bootstrap-fallback: `homepage`, иначе единственная существующая страница.
5. После выбора ключа страница проходит runtime enrichment (`getRuntimePage`) и merge сквозных секций (`applyGlobalSectionsToPage`) при включенном inherit-флаге.

## Матрица маршрутов

| Route context | Ожидаемая ветка | Источник page key | Статус |
| --- | --- | --- | --- |
| `ctrl='' action='index' page_type='homepage'` | full-takeover pipeline | `page.*` binding -> fallback `homepage` -> fallback single page | static-verified |
| `page_type='content-list'` | full-takeover pipeline | `page.*` binding -> fallback `content-list` -> fallback `generic` | static-verified |
| `page_type='content-item'` | full-takeover pipeline | `page.*` binding -> fallback `content-item` -> fallback `generic` | static-verified |
| `ctrl='content' action='category'` | overlay-only branch | `overlay.content_category*` binding/page key, без full-takeover | static-verified |
| `ctrl='users' action='profile'` | overlay-only branch | `overlay.user_profile*` binding/page key, без full-takeover | static-verified |

## Приоритеты и guard-правила

1. Binding score выбирает лучший кандидат среди совпавших документов (`resolvePageKeyFromBindingsByPrefix`).
2. Если binding выдал `page_key`, но страницы не существует, применяется fallback.
3. Для `page.homepage` adapter не форсируется; для `page.*` (кроме homepage) adapter резолвится как `internal_content_generic`.
4. Сквозные секции применяются только если у страницы `layout.inherit_global_sections=true` и она не помечена как source.

## Риски, выявленные аудитом

1. Bootstrap-fallback на главной (единственная страница) может маскировать ошибку bindings в ранней конфигурации.
2. Политика fallback для `content-list/content-item/generic` полезна для UX, но усложняет диагностику route mismatch без трассировки.
3. Для полного закрытия P0 нужен runtime trace (debug-метка причины выбора ветки) и ручной smoke по маршрутам.

## Что нужно для закрытия P0

1. Добавить debug trace причины выбора `effective page key` (binding/fallback/overlay).
2. Пройти авторизованный smoke:
   - `/`
   - типовая страница списка контента
   - типовая страница записи контента
   - категория контента
   - профиль пользователя
3. Зафиксировать результат smoke в `docs/WORKLOG.md`.

## Update 2026-04-09 (runtime trace + авторизованный smoke)

### Что добавлено в runtime

1. В `system/controllers/landingbuilder/model.php` добавлен debug trace для резолвера `effective page key`:
   - trace по префиксу bindings (`page.*` / `overlay.*`),
   - фиксация кандидатов, причины fallback и итогового ключа,
   - доступ к событиям через `getLastEffectivePageKeyTrace()`.
2. В `templates/nordic/main.tpl.php` добавлен trace шаблонных веток:
   - route-context,
   - binding-resolution,
   - page_type/homepage fallback,
   - финальная ветка takeover/skip.
3. Для dev-диагностики trace отдается администратору по флагу `lb_trace=1` (или `lb_effective_trace=1`) через HTML-комментарий `lb-effective-page-trace`.

### Авторизованный smoke (admin cookie, `lb_trace=1`)

| URL | HTTP | Runtime route-context | Effective key | Итог ветки |
| --- | --- | --- | --- | --- |
| `/` | `200` | `ctrl='' action='index' page_type='homepage'` | `glav` | `template.takeover-applied` |
| `/board` | `200` | `ctrl='content' action='board' page_type='generic'` | `''` | `template.takeover-skip (empty-effective-page-key)` |
| `/board/7-prodam-kvartiru-v-novostroike.html` | `200` | `ctrl='content' action='board' page_type='generic'` | `''` | `template.takeover-skip (empty-effective-page-key)` |
| `/board/nedvizhimost` | `200` | `ctrl='content' action='board' page_type='generic'` | `''` | `template.takeover-skip (empty-effective-page-key)` |
| `/users/1` | `200` | `ctrl='users' action='1' page_type='generic'` | `''` | `template.takeover-skip (empty-effective-page-key)` |

### Вывод по P0

1. Runtime trace и авторизованный smoke выполнены; P0 больше не находится только в статусе `static-verified`.
2. Подтвержден production-факт: на текущем инстансе route-context для category/profile отличается от ожидаемых в статической матрице (`content/category`, `users/profile`).
3. Для окончательного закрытия overlay-ветки нужен отдельный cut по route classifier в `page_context`/template условиях, чтобы category/profile попадали в overlay branch детерминированно.

## Update 2026-04-09 (route classifier cut + финальный smoke)

### Что исправлено

1. В `templates/nordic/page_context.php` добавлена нормализация ctype-style маршрутов:
   - `/board` -> `content/index`;
   - `/board/<slug>` -> `content/category`;
   - `/board/<item>.html` -> `content/item`;
   - `/users/<id>` -> `users/profile`.
2. В `templates/nordic/main.tpl.php` overlay-guard усилен проверкой по `page_type` (`content-category`, `user-profile`) как safety-net.

### Повторный авторизованный smoke (admin cookie, `lb_trace=1`)

| URL | HTTP | Runtime route-context | Overlay guard | Итог ветки |
| --- | --- | --- | --- | --- |
| `/` | `200` | `ctrl='' action='index' page_type='homepage'` | `0` | `template.takeover-applied` |
| `/board` | `200` | `ctrl='content' action='index' page_type='content-list'` | `0` | `template.takeover-skip (empty-effective-page-key)` |
| `/board/7-prodam-kvartiru-v-novostroike.html` | `200` | `ctrl='content' action='item' page_type='content-item'` | `0` | `template.takeover-skip (empty-effective-page-key)` |
| `/board/nedvizhimost` | `200` | `ctrl='content' action='category' page_type='content-category'` | `1` | `template.takeover-skip (overlay-or-landingbuilder-route)` |
| `/users/1` | `200` | `ctrl='users' action='profile' page_type='user-profile'` | `1` | `template.takeover-skip (overlay-or-landingbuilder-route)` |

### Финальный статус

1. Все 5 веток route matrix подтверждены в runtime на реальных URL.
2. P0 по route-matrix (trace + авторизованный smoke) закрыт.
