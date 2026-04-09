# ADR-0002: Commercial Overlay Without Core Changes

## Status

Accepted

## Date

2026-04-09

## Context

Проект зафиксировал архитектурный deadlock в bridge-слое canvas:

1. Нестабильный подбор страниц по маршрутам.
2. Часть inspector-controls не влияет на runtime предсказуемо.
3. Смешаны legacy fallback, bindings, overlay hooks и template fallback.

Прямая правка ядра InstantCMS ускоряет локальные фиксы, но ломает update-safe модель продукта.

## Decision

Развивать Nordic Builder как коммерческую надстройку (overlay component) без зависимости от patch-правок core.

Правила решения:

1. InstantCMS используется как стабильный backend/data provider.
2. Логика builder живет в компоненте: canvas, bindings, runtime contracts, publish pipeline.
3. Route-пайплайн выбора страницы должен быть детерминирован и трассируем:
   - route context;
   - explicit binding match;
   - overlay branch только для overlay-маршрутов;
   - controlled fallback;
   - запрет скрытых legacy-веток.
4. Для `content/category` и `users/profile` сохраняется native body/grid InstantCMS; builder подключается через overlay hooks.
5. Любой inspector-control должен иметь проверяемый runtime-эффект (active) или быть удален/помечен deprecated.

## Constraints

1. Не вносить продуктовые изменения через правки core InstantCMS.
2. Не менять `system/config/config.php` в рамках задач builder без прямого запроса.
3. Перед рискованными изменениями обязателен checkpoint; для SQL-рисков обязателен backup БД.

## Consequences

Плюсы:

1. Update-safe путь для релизов и миграций.
2. Снижение регрессий от legacy-пересечений.
3. Чистая ответственность слоев (InstantCMS core vs builder runtime).

Минусы:

1. Нужна дисциплина контрактов и route-аудитов.
2. Увеличивается объем начального refactor-а резолвера и inspector inventory.

## Implementation Notes

1. Route matrix и inspector inventory ведутся как отдельные P0-аудит-артефакты в `docs/worklogs/`.
2. Любое новое ветвление в resolver должно иметь объяснимый приоритет и проверку на smoke-матрице маршрутов.
3. Результат считается готовым только при preview/live parity и повторяемом поведении на ключевых маршрутах.
