# 2026-04-06 — Nordicbuilder: мастер создания страницы + bindings

## Контекст

Пользователь работает с экраном:

- `/admin/controllers/edit/nordicbuilder/pages`

Цель UX — как в inthemer: создавать «нулевой» (пустой) макет, затем сразу открывать чистый canvas; область применения задаётся простым выбором:

- Главная страница
- Выборочные страницы (URL-маски + исключения)
- Либо «не привязывать сейчас»

## Что сделано (сводка)

- Добавлен AJAX endpoint для создания binding rule из мастера создания страницы: `nordicbuilder/create_binding`.
- В action `nordicbuilder/pages` прокинут `create_binding_url` в шаблон.
- В админских шаблонах списка страниц добавлен мастер (модалка) вместо `prompt()`:
  - поля: title, page_key, apply mode, binding_key, url_masks, exclude_masks
  - подсказки `?` через нативный `title`
  - после создания страницы — опционально создаётся binding rule
- Добавлена явная кнопка **Новая страница** внутри шапки экрана (на случай, если тулбар админки не показывает toolbutton).

## Где (файлы)

Live:
- `system/controllers/nordicbuilder/backend/actions/create_binding.php`
- `system/controllers/nordicbuilder/backend/actions/pages.php`
- `templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php`

Package mirrors:
- `packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_binding.php`
- `packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php`
- `packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php`
- `packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php`

## Проверено

- `php -l` для action’ов и шаблонов (syntax OK).

## Smoke (ручной)

1) Открыть `/admin/controllers/edit/nordicbuilder/pages`
2) Убедиться, что видна кнопка **Новая страница** (внутри шапки)
3) Нажать — должна открыться модалка
4) Выбрать:
   - «Главная страница» → после создания проверить `page.homepage` в bindings
   - «Выборочные страницы» → заполнить положительные/отрицательные маски → проверить, что rule создался

## Риски / заметки

- Если в браузере виден старый UI, возможна задержка из-за OPCache/кэша: сделать hard refresh (Ctrl+F5) и/или подождать несколько секунд.
- Тулбар-иконка «Новая страница» может не отображаться в некоторых layout’ах админки — поэтому кнопка продублирована в шапке экрана.

## Точка отката

- Откатить шаблоны и action’ы через `git checkout -- <path>` для файлов из секции «Где».
