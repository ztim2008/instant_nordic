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
