# 2026-04-08 — Native body: full width вместе с sidebars

## Контекст

На живых страницах (`/board`, `/news`, `/users/1`) проявлялся визуальный эффект "оверлея" в зоне контента. Первичная гипотеза про z-index/слои не подтвердилась: фактическая причина оказалась в autoscale/full-bleed логике, которая применялась к блокам в sidebar-слотах.

## Что решено

1. Runtime перестал применять base-autoscale в `content_sidebar_left` и `content_sidebar_right`.
2. В Canvas отключены A/A+ для sidebar-зон, добавлены защитные проверки от повторного включения через действия и изменение полей.
3. Введен явный режим ширины native body:
   - `grid` (`12/12` в сетке)
   - `full` (`100%`)
4. Добавлен отдельный параметр `native_body_full_padding` (0..60, default 20).
5. Убрана связка `full -> только 1 колонка`, из-за которой пропадали sidebars.
6. В runtime-шаблоне `templates/nordic/main.tpl.php` full width больше не отключает sidebars.
7. Для full width + sidebars добавлен отдельный layout-класс `lb-native-body-layout--autoscale` с CSS variable для паддинга.

## Ключевые комментарии по решению

- Full width теперь трактуется как режим ширины контейнера native body, а не как режим отключения колонок.
- Sidebars остаются частью body-layout и управляются только `body_columns_mode` (`1`, `2-left`, `2-right`, `3`).
- A/A+ для sidebar-зон специально исключены, чтобы не появлялся "ложный" full-bleed в боковых колонках.
- Поведение `12/12 <-> 100%` стало предсказуемым: переключение ширины не ломает выбранную структуру колонок.

## Что проверено

- `php -l` для измененных live/mirror файлов: без синтаксических ошибок.
- Diagnostics по затронутым файлам: без новых ошибок.
- Ручная функциональная проверка: режим `100%` работает с 0/1/2 sidebars, без скрытия колонок.

## Остаточные риски

- Нужен отдельный мобильный smoke на страницах с длинным контентом и кастомными legacy-виджетами.
- Возможен локальный overflow у отдельных старых виджетов с фиксированной внутренней шириной.

## Связанные файлы

- `templates/default/controllers/landingbuilder/runtime_renderer.php`
- `templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php`
- `system/controllers/landingbuilder/model.php`
- `templates/nordic/main.tpl.php`
- `system/controllers/landingbuilder/helpers/runtime_styles.php`
- package mirrors в `packages/landingbuilder/`, `packages/nordicbuilder/`, `packages/nordic/`

## Рекомендованный следующий шаг

- Зафиксировать checkpoint-коммит и пройти короткий visual smoke (`/`, `/board`, `/users/1`) на desktop и mobile viewport.
