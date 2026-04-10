# NordicStyl Inspector Contract

## Зачем нужен этот документ

`nordicstyl` уже умеет:

- хранить selector-based правила
- собирать CSS через runtime endpoint `/nordicstyl/css`
- поддерживать состояния через YAML по `styles` и `custom`
- использовать `:root` как точку для токенов

Но следующему inspector-слою нужен более чёткий контракт:

- что именно редактируем
- как различаем состояние элемента и устройство
- как это хранится без поломки текущих правил
- как это компилируется в чистый CSS

## Что уже есть в коде

Опорные точки:

- текущий runtime compiler: `system/controllers/nordicstyl/frontend.php`
- текущий CSS action: `system/controllers/nordicstyl/actions/css.php`
- текущая backend-форма правил: `system/controllers/nordicstyl/backend/actions/rules.php`
- текущая модель и selector map: `system/controllers/nordicstyl/model.php`

Практический вывод:

- база для inspector-а уже существует
- не нужно делать вторую параллельную систему правил
- нужно расширять текущую rule-модель и compiler

## Текущее поведение rule-модели

Сейчас правило по сути состоит из:

- `path` как CSS selector
- `styles` как YAML-массив состояний
- `custom` как YAML-массив состояний
- `is_important`
- `is_enabled`

Сейчас compiler уже понимает состояния:

- `default`
- `hover`
- `active`

И уже умеет:

- превращать набор свойств в CSS
- добавлять `!important`
- компилировать `custom` прямо в блок правила

Что ещё не закрыто:

- `focus`, `focus-visible`, `visited`, `before`, `after`
- device-aware режимы
- нормальный контракт для builder-node target, а не только raw selector
- порядок компиляции для responsive branches

## Нормальная модель inspector-а

### 1. Target

Что именно редактируем.

Минимальный target contract:

- `target_type`: `selector` | `builder_node` | `token_scope`
- `selector`: итоговый CSS selector
- `title`: человеко-понятное имя
- `source`: `picker` | `selector_map` | `builder` | `manual`
- `template`: активный шаблон
- `uri_scope`: где правило должно применяться

Смысл:

- inspector может стартовать от builder-узла или от click-to-pick
- но на выходе всегда должен иметь нормализованный selector target

### 2. State

Состояние элемента, а не устройство.

Минимальный список для MVP:

- `default`
- `hover`
- `active`

Следующий слой:

- `focus`
- `focus-visible`
- `visited`

Отдельный расширенный слой:

- `before`
- `after`

Практическое правило:

- pseudo-elements лучше вводить как второй этап, не смешивая их сразу с обычными состояниями

### 3. Device

Устройство или breakpoint-ветка.

Рекомендуемый порядок:

- `base` как дефолт для всех устройств
- `mobile` как первый дополнительный режим
- потом при необходимости `tablet`
- `desktop` отдельно вводить только если реально нужен override от `base`

Практический вывод:

- для первого расширения нам хватит `base + mobile`
- не надо сразу плодить 5 брейкпоинтов, пока UI и compiler не устоялись

### 4. Token scope

Токены должны жить внутри текущей системы, а не отдельным механизмом.

Минимальная модель:

- `target_type = token_scope`
- `selector = :root`
- свойства хранятся как CSS custom properties

Примеры:

- `--nb-color-primary`
- `--nb-space-section`
- `--nb-radius-card`

Практический вывод:

- текущий сценарий `:root` уже подтверждает правильный путь

## Предлагаемый внутренний формат данных

### Внутренний normalized shape

Inspector должен мыслить так:

`devices -> states -> declarations`

Пример:

```yaml
styles:
  base:
    default:
      color: "#173042"
      background-color: "#ffffff"
    hover:
      color: "#1f6b7a"
  mobile:
    default:
      padding: "16px"

custom:
  base:
    default: "transition: all .2s ease;"
  mobile:
    default: "min-height: 44px;"
```

### Обратная совместимость

Сейчас legacy rules выглядят как:

```yaml
styles:
  default:
    color: "#111"
  hover:
    color: "#333"
```

Поэтому compiler и save/load слой должны поддерживать оба режима:

- legacy state-first
- new device-first

Нормализация при чтении:

- если верхний уровень содержит состояния, считаем это `base`
- если верхний уровень содержит устройства, читаем как новую схему

Это позволит не ломать текущие сохранённые правила.

## CSS compiler contract

### Порядок сборки

1. `base/default`
2. `base/hover`, `base/active`, другие состояния
3. `mobile/default`
4. `mobile/hover`, `mobile/active`

### Псевдоселекторы

Минимальное соответствие:

- `default` → `selector`
- `hover` → `selector:hover`
- `active` → `selector:active`
- `focus` → `selector:focus`
- `focus-visible` → `selector:focus-visible`

### Медиа-ветки

Для первого шага достаточно:

- `mobile` → `@media (max-width: 767.98px)`

Позже можно расширить до:

- `tablet`
- `desktop`

### Important

`is_important` должен остаться rule-level флагом, пока нет острой нужды опускать его до уровня отдельного свойства.

## UI contract для будущего inspector-а

### Левая часть

- target info
- selector source
- quick actions: pick, map, builder node

### Центральная часть

- визуальные controls по группам: typography, colors, background, border, spacing, sizing, effects

### Верхний переключатель

- state switcher
- device switcher

### Правая часть

- live preview summary
- raw selector
- advanced custom CSS
- reset / disable / duplicate

## Как это ложится на текущую реализацию

### Уже можно использовать без миграции

- selector-based target
- state-based styles
- `:root` tokens
- CSS runtime compiler

### Потребует расширения, но без слома базы

- device-aware branches внутри `styles/custom`
- нормализатор legacy/new format
- UI tabs для states/devices

### Потребует отдельного решения позже

- builder-node to selector mapping
- per-page visual presets
- pseudo-elements editor

## Практический порядок внедрения

1. Не трогать старую rule-модель снаружи, а добавить внутренний normalizer `legacy -> normalized`.
2. Расширить compiler до `base + mobile` без ломки текущих состояний.
3. Вынести states switcher из backend-формы в on-site inspector UI.
4. После этого подключить builder-node target как удобную точку входа в ту же систему.

## Короткий вывод

Следующий inspector не должен быть новым продуктом рядом с `nordicstyl`.

Он должен быть расширением уже существующего rule-runtime:

- тот же CSS endpoint
- тот же selector target
- те же rules
- но с более взрослым контрактом для states, devices и tokens