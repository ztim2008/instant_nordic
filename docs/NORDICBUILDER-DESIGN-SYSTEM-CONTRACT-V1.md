# Nordic Builder Design System Contract V1

## Статус

- Версия: `v1.0.0`
- Дата: `2026-04-09`
- Назначение: единый контракт глобального дизайна для `canvas`, `preview` и `live runtime`.

## Цель

Design system V1 фиксирует один source of truth для визуального языка сайта.

Контракт должен одинаково читаться в трех средах:

1. admin canvas;
2. frontend preview;
3. live runtime.

## Scope V1

В V1 входят только управляемые пресеты и semantic roles.

1. template preset;
2. global style preset;
3. color preset;
4. typography preset;
5. container preset;
6. section spacing preset;
7. radius preset;
8. density preset;
9. contrast preset;
10. button preset;
11. card preset;
12. surface preset.

## Канонические ключи темы

```json
{
  "template_preset": "nordic_classic",
  "global_style_preset": "nordic_balanced",
  "color_preset": "nordic_day",
  "typography_preset": "editorial",
  "container_preset": "standard",
  "button_preset": "soft_accent",
  "card_preset": "quiet",
  "surface_preset": "neutral",
  "section_spacing": "comfortable",
  "radius_preset": "none",
  "density_preset": "balanced",
  "contrast_preset": "balanced"
}
```

## Inheritance Order

Порядок наследования обязателен для всех сред:

1. base vars;
2. template defaults;
3. site defaults;
4. page theme;
5. section overrides;
6. block overrides.

## Semantic Role Vars (V1)

V1 закрепляет обязательные role-переменные:

1. `--lb-role-text-primary`
2. `--lb-role-text-secondary`
3. `--lb-role-heading`
4. `--lb-role-border`
5. `--lb-role-surface-base`
6. `--lb-role-surface-soft`
7. `--lb-role-surface-contrast`
8. `--lb-role-action-primary`
9. `--lb-role-action-on-primary`
10. `--lb-role-card-bg`
11. `--lb-role-card-border`
12. `--lb-role-card-shadow`

## Surface Presets (V1)

Введены first-class пресеты поверхностей:

1. `neutral`;
2. `soft`;
3. `elevated`;
4. `contrast`.

Каждый preset обязан задавать минимум:

1. panel background;
2. panel border;
3. panel shadow;
4. card background;
5. card border;
6. card shadow.

## Runtime Parity Rule

Запрещено, чтобы canvas собирал vars по другому набору пресетов, чем runtime.

Обязательный merge-набор для canvas/runtime:

1. global style;
2. color;
3. typography;
4. radius;
5. density;
6. contrast;
7. button;
8. card;
9. surface.

## UX Rule

Пользователь видит только продуктовые названия пресетов.

Пользователь не должен работать с raw CSS vars и внутренними ключами как с основным интерфейсом.

## Совместимость

- Старые страницы без `surface_preset` считаются валидными.
- При отсутствии ключа включается fallback: `surface_preset = neutral`.

## DoD для следующих итераций

1. Любой новый preset добавляется и в runtime, и в canvas merge.
2. Любой новый role-var имеет fallback в `base_vars`.
3. Изменения проходят `php -l` на live и package mirrors.
4. В worklog фиксируется изменение контракта и риск-оценка.
