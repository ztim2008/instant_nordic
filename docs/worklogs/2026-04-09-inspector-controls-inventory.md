# 2026-04-09 — P0 Audit: Inspector Controls Inventory

## Цель

Зафиксировать минимальный инвентарь inspector-controls и классифицировать их по статусу:

1. `active` — влияет на runtime/preview предсказуемо.
2. `review` — влияние есть, но требуется ручная проверка в smoke.
3. `candidate-deprecated` — не должен оставаться без внятного runtime-эффекта.

## Источник кода

1. `templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php`

## Page-level controls

| Control | Field/Action | Статус | Комментарий |
| --- | --- | --- | --- |
| Вариант каркаса | `layout.shell_variant` | review | Влияет на shell routing зон, требуется smoke на разных `shell_variant`. |
| Ширина native body | action `set-native-body-width-mode` -> `layout.native_body_width_mode` | active | Явно применяется в runtime через full/grid ветку. |
| Padding full width body | `layout.native_body_full_padding` | active | Применяется как CSS variable в runtime. |
| Режим body-колонок | `layout.body_columns_mode` | active | Влияет на 1/2/3 колонок layout. |
| Ширина левого/правого sidebar | `layout.body_left_span/body_right_span` | active | Применяется в runtime grid-колонках. |

## Section-level controls

| Control | Field/Action | Статус | Комментарий |
| --- | --- | --- | --- |
| Название секции | `title` | review | UI-метка; влияние на runtime визуально ограничено текстом. |
| Зона в shell | `settings.zone_key` | active | Определяет render slot секции. |
| Stack tablet/phone | `settings.stack_tablet/settings.stack_phone` | review | Влияет на адаптив, нужен smoke desktop/tablet/mobile. |
| Наследование ширин | `settings.width_inherit` | review | Нужна проверка междевайсного поведения. |
| Автоскейл A | `settings.autoscale_base_blocks` / action toggle | active | Работает с guard для sidebar-зон. |
| Автоскейл A+ | action `toggle-all-sections-autoscale-base-blocks` | active | Работает только на eligible секциях. |
| CSS class секции | `settings.css_class` | review | На P1 pass 2 скрыт в базовом режиме, доступен только в advanced mode (`lb_inspector_advanced=1`). |
| Background class секции | `settings.background_class` | review | На P1 pass 2 скрыт в базовом режиме, доступен только в advanced mode (`lb_inspector_advanced=1`). |
| Видимость по устройствам | action `toggle-section-device-visibility` / `visibility.*` | active | Влияет на device-level render. |
| Дублировать секцию | action `duplicate-section` | active | Корректно копирует структуру и генерирует новые uid. |

## Column-level controls

| Control | Field | Статус | Комментарий |
| --- | --- | --- | --- |
| Название колонки | `title` | review | UI-метка, runtime влияние ограничено контентом. |
| Alignment | `settings.align` | review | Нужна проверка на runtime шаблонах и адаптерах. |
| CSS class колонки | `settings.css_class` | review | На P1 pass 2 скрыт в базовом режиме, доступен только в advanced mode (`lb_inspector_advanced=1`). |

## Node-level controls

| Control | Field/Action | Статус | Комментарий |
| --- | --- | --- | --- |
| Label | `label` | review | Преимущественно редакторская метка. |
| Class name | `class_name` | review | На P1 pass 2 скрыт в базовом режиме, доступен только в advanced mode (`lb_inspector_advanced=1`). |
| Source key | `source_key` | review | Критичен для data adapters, нужен smoke на live-данных. |
| Notes | `notes` | deprecated-hidden | Поле скрыто из Node Inspector на P1 pass 1; runtime на него не опирается, данные оставлены для обратной совместимости. |
| Видимость по устройствам | action `toggle-node-device-visibility` / `device_visibility.*` | active | Влияет на device-level render. |
| Дублировать элемент | action `duplicate-node` | active | Корректно копирует node с новым uid. |

## Вывод по audit

1. Критическая часть controls уже в `active/review`, явных "мертвых" технических блоков осталось меньше.
2. `notes` переведен в `deprecated-hidden`, а технические CSS/class поля скрыты в базовом режиме и доступны только через advanced mode.
3. Следующий P1 шаг: cleanup remaining `review` controls по продуктовой ценности и короткий canvas-smoke (desktop/tablet/mobile).

## Checklist на закрытие P0

1. Проверить `shell_variant` + `zone_key` комбинации на 5 маршрутах матрицы.
2. Проверить responsive controls (`stack`, `width_inherit`) на desktop/tablet/mobile.
3. Проверить runtime-effect `class_name/css_class/background_class` в preview и live.
4. Подтвердить судьбу `notes`: либо использовать в runtime/contracts, либо скрыть как deprecated.

## Update 2026-04-09 (P1 pass 1)

1. В Node Inspector поле `notes` скрыто из UI как `deprecated-hidden`.
2. Техническое поле `notes` сохранено в схеме данных для backward compatibility и безопасного чтения старых документов.
3. Первый cleanup сделан без изменения runtime-контрактов и без SQL-изменений.

## Update 2026-04-09 (P1 pass 2)

1. В базовом Inspector-режиме скрыты технические поля оформления:
	- `settings.css_class` (section/column),
	- `settings.background_class` (section),
	- `class_name` (node).
2. Для опытного режима поля доступны через URL-флаг `lb_inspector_advanced=1`.
3. Runtime/контракты не изменялись, cleanup затронул только UX-слой inspector.
