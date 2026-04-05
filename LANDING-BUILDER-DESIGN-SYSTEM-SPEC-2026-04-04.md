# Техспека: Design System Layer и Global Style Defaults Nordic

## Зачем этот документ

Этот документ фиксирует design system уже не как отдельный экран настроек, а как полноценный слой продукта.

Экран `Глобальные стили` остается, но теперь считается только вторичным UI над этим слоем.

Главная работа пользователя должна происходить на canvas.

## 1. Что такое design system в Нордик

Design system в Нордик это канонический слой токенов, presets и component defaults, который используется одновременно:

1. runtime шаблоном `nordic`;
2. visual builder workspace;
3. preview;
4. live frontend;
5. widget/data adapters.

То есть design system это не форма и не экран.

Это source of truth для site-wide visual defaults.

## 2. Место design system во взрослой архитектуре

Канонический порядок слоев такой:

1. `InstantCMS 2 backend`;
2. `nordic template runtime`;
3. `design system / global defaults`;
4. `visual builder workspace`;
5. `component library`;
6. `widget/data adapter layer`.

Роль design system в этой цепочке:

1. задавать глобальный visual language;
2. давать defaults для shell, pages, sections и blocks;
3. обеспечивать одинаковый результат между canvas, preview и live runtime.

## 3. Что design system должен контролировать

### 3.1. Colors

1. primary;
2. secondary;
3. accent;
4. background;
5. surface;
6. text;
7. muted text;
8. border.

### 3.2. Typography

1. heading font preset;
2. body font preset;
3. type scale preset;
4. text density mode;
5. heading contrast mode.

### 3.3. Spacing and containers

1. container width preset;
2. content width mode;
3. section spacing density;
4. vertical rhythm preset.

### 3.4. Surface language

1. radius preset;
2. border mode;
3. shadow preset;
4. surface contrast mode;
5. tone presets для светлых и темных секций.

### 3.5. Component defaults

1. buttons;
2. cards;
3. form controls позднее;
4. shell surfaces для header и footer;
5. section presets defaults.

## 4. Что design system не должен контролировать

Design system не должен отвечать за:

1. порядок секций страницы;
2. контент конкретной страницы;
3. локальные page-level и block-level решения текущего редактирования;
4. shell structure и slot composition;
5. raw CSS editor как базовый сценарий;
6. произвольный token graph editor в бета-MVP.

Если пользователь решает, где стоит hero, какая секция идет первой и как выглядит конкретный блок на текущей странице, это не design system.

Это canvas и live inspector.

## 5. Что такое экран `Глобальные стили`

Экран `Глобальные стили` это secondary screen для редких site-wide defaults.

Он нужен для короткого маршрута:

1. выбрать стартовый preset;
2. скорректировать базовые tokens;
3. вернуться на canvas;
4. увидеть тот же результат без расхождения.

Правильный маршрут теперь такой:

`открыть страницу -> менять на canvas -> при необходимости открыть глобальные стили -> вернуться на canvas`

Если пользователь вынужден идти сюда для типового page-level действия, значит границы слоя нарушены.

## 6. Каноническая модель данных

Для взрослой архитектуры source of truth должен быть JSON-based.

Даже если bridge-этап временно хранит данные через options текущего компонента, каноническая модель должна мыслиться так:

1. `design_preset`;
2. `token_set`;
3. `typography_set`;
4. `spacing_set`;
5. `surface_set`;
6. `component_defaults`;
7. `shell_defaults`.

### 6.1. Что важно для bridge-этапа

1. storage временно может оставаться option-backed;
2. но контракт не должен проектироваться как form-field dump;
3. итоговый контракт должен одинаково читаться canvas, preview и frontend runtime.

## 7. Порядок наследования

Канонический порядок наследования такой:

1. `core base tokens`;
2. `nordic base preset`;
3. `site design preset`;
4. `shell defaults`;
5. `page preset`;
6. `page overrides`;
7. `section overrides`;
8. `block overrides`.

Важно:

1. design system дает только исходную среду;
2. текущая визуальная работа завершается на canvas;
3. глобальные defaults не должны подменять live editing.

## 8. Что должен видеть пользователь вместо технических ключей

Пользователь должен видеть продуктовые названия:

1. `Широкий контейнер`;
2. `Стандартный контейнер`;
3. `Узкий текстовый контейнер`;
4. `Спокойный ритм`;
5. `Плотный ритм`;
6. `Основная кнопка`;
7. `Мягкая карточка`;
8. `Темный surface`;
9. `Акцентный фон`.

Пользователь не должен видеть как основу UX:

1. raw CSS variables;
2. внутренние token keys;
3. служебные class names;
4. технические IDs.

## 9. Бета-MVP экрана `Глобальные стили`

Для первой взрослой беты экран должен оставаться коротким.

Достаточно пяти групп:

1. `Цвета`;
2. `Типографика`;
3. `Контейнеры и ритм`;
4. `Кнопки`;
5. `Карточки`.

### 9.1. Пресетный старт

Экран не должен стартовать с пустой формы.

Нужны базовые стартовые наборы:

1. `Nordic Air`;
2. `Nordic Contrast`;
3. `Nordic Editorial`;
4. `Nordic Warm Market`;
5. `Nordic Compact`.

Пользователь сначала выбирает базовый preset, потом делает минимальные overrides.

## 10. Связь с Shell Builder

`Shell Builder` отвечает за структуру.

Design system отвечает за defaults внешнего вида.

Примеры разделения:

1. где живет главное меню: `Shell Builder`;
2. как по умолчанию выглядит header surface: `Design System`;
3. есть ли hero slot: `Shell Builder`;
4. какой у кнопок радиус и fill style: `Design System`.

## 11. Связь с Visual Builder Workspace

Canvas и inspector используют design system как базовую среду, но не подчиняются ему как daily-flow экрану.

Правильное разделение такое:

1. design system задает defaults;
2. canvas показывает живую страницу;
3. inspector меняет текущую страницу, секцию или блок;
4. runtime и preview читают тот же token contract.

## 12. Что не делать в бета-MVP

Не включать в первую взрослую версию:

1. raw CSS editor;
2. полный token graph editor;
3. детальный state editor для hover, active и focus;
4. настройку каждой микродетали shell через design system экран;
5. page tree и section tree внутри `Глобальных стилей`;
6. логику, которая по смыслу должна жить в canvas inspector.

## 13. Критерий готовности

Design system можно считать готовым для бета-коридора, если:

1. он зафиксирован как отдельный слой продукта, а не как главный экран конструктора;
2. `Глобальные стили` работают как secondary screen;
3. tokens одинаково читаются canvas, preview и live frontend;
4. пользователь не обязан заходить сюда для типовых ежедневных правок страницы;
5. глобальные defaults можно менять без знания CSS и внутренних ключей.
