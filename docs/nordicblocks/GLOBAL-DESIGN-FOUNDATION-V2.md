# NordicBlocks Global Design Foundation V2

Дата: 2026-04-16

## 1. Зачем это введено

Новая foundation-система нужна, чтобы блоки перестали тащить собственные стили кнопок, карточек, радиусов и hover-поведения внутрь каждого render.

Цель:

1. Один глобальный источник правды для шрифтов, кнопок, поверхностей и ритма.
2. Единый инспектор глобального дизайна вместо разрозненных настроек по блокам.
3. Предсказуемая механика для следующих блоков: block author опирается на готовые CSS vars и helper-классы.

## 2. Канонический контракт

Дизайн-система хранится в `cms_nordicblocks_design.tokens_json` в canonical JSON contract версии 2.

Структура:

```json
{
  "version": 2,
  "colors": {
    "accent": "#b42318",
    "bg": "#ffffff",
    "bg_alt": "#f7f7f6",
    "surface": "#ffffff",
    "border": "#e5e7eb",
    "text": "#1a1a1a",
    "text_muted": "#6b7280",
    "button_primary_bg": "#b42318",
    "button_primary_text": "#ffffff",
    "button_primary_border": "#b42318",
    "button_outline_text": "#b42318",
    "button_outline_border": "#b42318",
    "button_ghost_text": "#1a1a1a",
    "button_ghost_border": "#e5e7eb"
  },
  "typography": {
    "font_body": "sans",
    "font_head": "sans",
    "font_button": ""
  },
  "layout": {
    "section_spacing": "comfortable"
  },
  "radii": {
    "base": "md",
    "card": "lg",
    "button": "md",
    "media": "lg"
  },
  "buttons": {
    "style": "primary",
    "size": "md",
    "hover_animation": "lift",
    "glint_color": "#ffffff",
    "glint_duration": 900
  },
  "cards": {
    "border_width": 1,
    "shadow_preset": "md",
    "surface_motion": true
  }
}
```

## 3. Backward Compatibility

`modelNordicblocks::normalizeDesignTokens()` автоматически переводит старый плоский формат в version 2.

Это значит:

1. Существующие сохраненные токены не нужно пересохранять вручную.
2. Preview и live продолжают работать на старых данных.
3. Новые сохранения уже уходят в canonical nested JSON.

## 4. Runtime Файлы

Главные файлы foundation:

1. `system/controllers/nordicblocks/model.php`
   Здесь canonical contract, миграция legacy flat tokens и генерация inline CSS vars.
2. `system/controllers/nordicblocks/backend/actions/design.php`
   Здесь сбор формы, валидация и сохранение design tokens.
3. `templates/admincoreui/controllers/nordicblocks/backend/design.tpl.php`
   Здесь взрослый инспектор глобального дизайна.
4. `system/controllers/nordicblocks/assets/tokens.css`
   Здесь общая token-механика шрифтов, кнопок и radii.
5. `system/controllers/nordicblocks/assets/blocks.css`
   Здесь helper-классы поверхностей и block-level fallback rules.

## 5. Что теперь считается глобальным

Глобально управляются:

1. Шрифт текста.
2. Шрифт заголовков.
3. Отдельный шрифт кнопок.
4. Базовые цвета системы.
5. Global button states:
   normal, hover, pressed.
6. Global button size.
7. Global button hover animation.
8. Card/surface radius.
9. Media radius.
10. Border width и shadow preset карточек.
11. Section spacing.

## 6. Что остается локальным в блоках

Локально в блоке оставляем только то, что действительно относится к контенту или композиции блока:

1. SEO tag заголовка.
2. Локальная жирность заголовка.
3. Локальные индивидуальные override-цвета, если блок сознательно должен выбиваться из системы.
4. Локальная анимация появления блока.

Правило:

Если задачу можно решить через глобальную систему, не добавлять новую одноразовую настройку в schema конкретного блока.

## 7. Helper-правила для новых блоков

Новые блоки должны по умолчанию использовать:

1. `.nb-btn`, `.nb-btn--primary`, `.nb-btn--outline`, `.nb-btn--ghost`.
2. `var(--nb-radius-card)`, `var(--nb-radius-media)`, `var(--nb-shadow-card)`.
3. `.nb-surface`, `.nb-card`, `.nb-media` там, где это подходит по семантике.
4. `var(--nb-font-body)`, `var(--nb-font-head)`, `var(--nb-font-button)` вместо локальных font-family.

## 8. Sync Правило

После изменения canonical runtime-файлов нужно синхронизировать зеркала:

1. `packages/nordicblocks/package/system/controllers/nordicblocks/...`
2. `templates/*/css/nordicblocks_tokens.css`
3. `templates/*/css/nordicblocks_blocks.css`

Иначе live, package и installer начнут расходиться.

## 9. Проверка после изменений

Минимальный smoke:

1. Открыть `/admin/controllers/edit/nordicblocks/design`.
2. Проверить live preview кнопок и карточек.
3. Проверить block editor preview.
4. Проверить live homepage, что существующие блоки не потеряли стили.
5. Проверить package-файлы на syntax errors.

## 10. Актуальная точка отката

Перед foundation v2 создан checkpoint:

`snapshot/20260416-115952`

Использовать его как безопасную точку возврата для этого этапа.