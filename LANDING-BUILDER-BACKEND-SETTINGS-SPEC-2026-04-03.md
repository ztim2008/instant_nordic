# Техспека: backend-структура настроек конструктора

## Статусы

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Навигация

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Уточняющий документ по слоям продукта: [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)
- Уточняющий документ по shell-уровню: [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md)
- Предыдущий документ: [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
- Уточняющий документ: [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
- Следующий документ: [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)

## 1. Цель документа

Этот документ фиксирует, как конструктор должен выглядеть в админке InstantCMS как отдельный компонент внутри продукта, который также поставляет собственный frontend template `nordic`.

Документ отвечает на 4 вопроса:

1. Какие разделы будут в меню компонента.
2. Где лежат глобальные настройки конструктора.
3. Где управляются bindings для страниц и content types.
4. Как выглядит экран подключения builder к конкретной странице или типу контента.

## 2. Базовый принцип

### 🟢 Готово: принцип зафиксирован

Конструктор должен жить как отдельный backend-компонент InstantCMS.

Это значит:

- у него есть свой `backend.php`;
- у него есть своё backend-меню;
- у него есть свой раздел опций;
- у него есть свои экшены и формы;
- он открывается через стандартный механизм `/admin/controllers/edit/{component}`.

Конструктор не должен сводиться только к полю выбора темы сайта.

Template `nordic` выбирается в глобальных настройках как frontend template продукта.
Но backend-часть и вся логика builder должны оставаться в отдельном компоненте.

Тема сайта остаётся в глобальных настройках.
Конструктор управляет только:

- своими страницами;
- своими блоками;
- своими adapters;
- своими bindings;
- своими стилевыми preset-слоями.

## 3. Как это должно вписываться в backend InstantCMS

### 🟢 Готово: модель привязана к документации InstantCMS

По правилам InstantCMS:

- backend контроллер компонента располагается в `/system/controllers/{component}/backend.php`;
- меню компонента задаётся методом `getBackendMenu()`;
- стандартные опции компонента можно включить через `public $useDefaultOptionsAction = true;`;
- форма опций должна лежать в `/system/controllers/{component}/backend/forms/form_options.php`;
- URL экшенов должны строиться через `href_to($this->root_url, 'action')`.

Следовательно, конструктор должен проектироваться как обычный полноценный компонент с backend-структурой, а не как набор хаотичных системных хуков.

При этом продукт в целом должен состоять из двух installable сущностей:

1. frontend template `nordic`;
2. component `landingbuilder`.

## 4. Системное имя компонента

### 🔴 Запланировано

Рабочий вариант имени компонента:

- `landingbuilder`

Допустимые альтернативы:

- `builder`
- `pagebuilder`
- `landing_builder`

Текущая рекомендация:

- выбрать имя без конфликтов и без слишком общего смысла;
- предпочтительно `landingbuilder` или `landing_builder`.

Во всех примерах ниже используется имя `landingbuilder`.

## 5. Предлагаемая структура меню компонента

### 🔵 В работе: состав меню зафиксирован на уровне архитектуры

Меню должно быть не слишком плоским, но и не перегруженным.

Рекомендуемый набор разделов:

1. Страницы
2. Shell Builder
3. Глобальные стили
4. Bindings
5. Блоки
6. Block Packs
7. Adapters
8. Опции
9. Права доступа

### 5.1. `Страницы`

Назначение:

- список всех страниц конструктора;
- создание новой landing page;
- статусы draft/published;
- переход в canvas editor.

Это должен быть основной экран компонента.

При открытии конкретной страницы пользователь должен попадать в визуальный canvas, где доступны:

- секции и колонки страницы;
- вкладка builder blocks;
- вкладка системных widgets InstantCMS;
- inspector выбранного элемента;
- device toggles: desktop, tablet, mobile.

### 5.2. `Shell Builder`

Назначение:

- управление shell-слоями сайта;
- header variants;
- footer variants;
- menu placement;
- homepage shell layout;
- global slot composition для `nordic`.

Это отдельный экран каркаса сайта.

Он не должен быть спрятан внутри page canvas или `options`.

### 5.3. `Глобальные стили`

Назначение:

- global design tokens;
- colors;
- typography;
- containers;
- spacing scale;
- buttons, cards, forms;
- global style presets и component presets.

Это secondary screen над слоем `design system / global defaults`, а не главный ежедневный центр продукта.

### 5.4. `Bindings`

Назначение:

- централизованное управление, где именно конструктор участвует в сайте.

Здесь управляются привязки для:

- homepage;
- route bindings;
- page adapters;
- content item/list/category;
- user profile;
- других системных page types.

### 5.5. `Блоки`

Назначение:

- просмотр registry блоков;
- категории блоков;
- включение/выключение блоков;
- просмотр meta, previews и поддерживаемых режимов данных.

Это не магазин паков, а именно реестр доступных блоков внутри уже установленной системы.

### 5.6. `Block Packs`

Назначение:

- список установленных пакетов блоков;
- совместимость pack ↔ core;
- версия;
- статус активен/неактивен;
- источник пакета;
- обновления.

Именно этот раздел важен для будущей монетизации.

### 5.7. `Adapters`

Назначение:

- список page adapters;
- включение/выключение adapters;
- просмотр поддерживаемых zones;
- просмотр режима `styling overlay` / `structural overlay`.

Это системный экран для администратора и разработчика.

### 5.8. `Опции`

Назначение:

- глобальные настройки самого конструктора.

Этот экран должен использовать стандартный options action InstantCMS.

### 5.9. `Права доступа`

Назначение:

- кто может редактировать страницы конструктора;
- кто может публиковать;
- кто может управлять bindings;
- кто может управлять packs и library.

## 6. Рекомендуемая форма backend-меню

### 🔴 Запланировано

В `backend.php` конструктора рекомендуется такой каркас меню:

```php
public function getBackendMenu() {
    return [
        [
            'title' => 'Страницы',
            'url'   => href_to($this->root_url, 'pages'),
            'options' => ['icon' => 'file-alt']
        ],
        [
            'title' => 'Shell Builder',
            'url'   => href_to($this->root_url, 'shell'),
            'options' => ['icon' => 'window-maximize']
        ],
        [
            'title' => 'Глобальные стили',
            'url'   => href_to($this->root_url, 'design'),
            'options' => ['icon' => 'palette']
        ],
        [
            'title' => 'Bindings',
            'url'   => href_to($this->root_url, 'bindings'),
            'options' => ['icon' => 'link']
        ],
        [
            'title' => 'Блоки',
            'url'   => href_to($this->root_url, 'blocks'),
            'options' => ['icon' => 'th-large']
        ],
        [
            'title' => 'Block Packs',
            'url'   => href_to($this->root_url, 'packs'),
            'options' => ['icon' => 'archive']
        ],
        [
            'title' => 'Adapters',
            'url'   => href_to($this->root_url, 'adapters'),
            'options' => ['icon' => 'project-diagram']
        ],
        [
            'title' => LANG_OPTIONS,
            'url'   => href_to($this->root_url, 'options'),
            'options' => ['icon' => 'cog']
        ],
        [
            'title' => LANG_PERMISSIONS,
            'url'   => href_to($this->root_url, 'perms', 'landingbuilder'),
            'options' => ['icon' => 'key']
        ]
    ];
}
```

## 7. Где должны лежать глобальные настройки конструктора

### 🟢 Готово: общий принцип зафиксирован

Глобальные настройки конструктора должны лежать в самом компоненте, а не в настройках темы сайта.

То есть:

- не `admin/settings/theme`;
- а `admin/controllers/edit/landingbuilder/options`.

### 7.1. Технически

Рекомендуемый путь:

- `system/controllers/landingbuilder/backend.php`
- `system/controllers/landingbuilder/backend/forms/form_options.php`

В `backend.php`:

- `protected $useOptions = true;`
- `public $useDefaultOptionsAction = true;`

### 7.2. Что должно быть в глобальных опциях

Минимальный список:

1. Базовая тема рендера.
2. Разрешён ли `styling overlay`.
3. Разрешён ли `structural overlay`.
4. Разрешён ли builder для homepage.
5. Разрешён ли builder для custom content types.
6. Default adapter policy.
7. Default style preset policy.
8. Разрешена ли загрузка внешних block packs.
9. Стратегия registry refresh.
10. Preview mode settings.

### 7.3. Чего там быть не должно

В глобальных опциях компонента не должно быть:

1. Настроек конкретной landing page.
2. Настроек конкретного ctype.
3. Настроек конкретной страницы профиля или категории.
4. Ручного редактирования структуры блоков.

Для этого нужны отдельные экраны.

## 8. Где должны управляться bindings для страниц и ctype

### 🔵 В работе: логика разделена на центральные bindings и локальные opt-in точки

Bindings нельзя хранить в одном месте для всех сценариев.

Нужны два уровня управления.

### 8.1. Центральный экран `Bindings`

Этот экран должен показывать глобальную карту подключений конструктора.

Здесь управляются:

1. homepage binding;
2. route bindings;
3. системные adapters;
4. включение builder для profile/category/list/item page types;
5. fallback behavior.

То есть это центр маршрутизации и логики участия конструктора.

### 8.2. Локальный opt-in в настройках ctype

Для content types нужен второй уровень управления прямо в настройках типа контента.

Не в основном экране конструктора, а рядом с ctype, потому что пользователь думает о типе контента в его собственной админке.

Там должны появиться поля вроде:

1. `Участвует в конструкторе`;
2. `Поддержка страницы материала`;
3. `Поддержка списка`;
4. `Поддержка категории`;
5. `Adapter`;
6. `Style preset`.
7. `Participation mode по умолчанию`;
8. `Разрешены ли query collections`;
9. `Какие группы блоков доступны для этого ctype`.

### 8.3. Как это должно сочетаться

Правильная модель такая:

- глобальный экран `Bindings` показывает общую картину;
- настройки ctype дают opt-in и локальные правила для конкретного content type.

То есть:

- `Bindings` = orchestration layer;
- `ctype settings` = local participation layer.

## 9. Как должен выглядеть экран подключения builder к странице

### 🔴 Запланировано

Нужен отдельный экран подключения builder к конкретной сущности.

Это должен быть не просто checkbox, а мастер или форма с явным выбором режима.

### 9.1. Экран подключения к системной странице

Например для homepage, profile или category screen форма должна содержать:

1. Включить builder для этой страницы.
2. Режим:
    - `full_takeover`;
    - `hybrid_overlay`;
    - `zone_injection`.
3. Adapter.
4. Default style preset.
5. Editable zones policy.
6. Fallback behavior.

Для `hybrid_overlay` и `zone_injection` интерфейс обязан явно показывать, какие зоны остаются под управлением шаблона InstantCMS, а какие передаются builder-слою.

### 9.2. Экран подключения к ctype

Для content type форма должна содержать:

1. Включить участие ctype в конструкторе.
2. Какие режимы поддерживаются:
   - item;
   - list;
   - category.
3. Generic adapter или custom adapter.
4. Default token preset.
5. Разрешён ли structural overlay.
6. Разрешены ли dynamic blocks внутри страниц этого ctype.
7. Разрешены ли `query.collection` источники.
8. Participation mode по умолчанию для item/list/category сценариев.
9. Mapping route context в block data contract.

### 9.3. Экран подключения к конкретной странице конструктора

Для standalone landing page или route binding форма должна содержать:

1. Название страницы.
2. Slug / route.
3. Тип binding.
4. Template/render mode.
5. Публикация.
6. Style preset.
7. Preview settings.

## 10. Рекомендуемый UX экранов

### 🔴 Запланировано

### 10.1. Главный экран компонента

Открытие компонента должно вести не на `options`, а на `pages`.

То есть `actionIndex()` лучше редиректить на:

- `pages`

Почему:

- это самый понятный для пользователя вход;
- опции не должны быть первой страницей продукта;
- как и в других системах, пользователь сначала думает страницами, а не низкоуровневыми параметрами.

Следующее действие после списка страниц должно быть не открытие технической формы layout, а открытие visual canvas editor.

### 10.2. Экран `Bindings`

Он должен быть таблично-карточным, а не только form-based.

Нужно показывать:

- тип привязки;
- target;
- adapter;
- preset;
- режим участия;
- тип источника данных по умолчанию;
- статус;
- действия: редактировать, отключить, открыть preview.

### 10.3. Экран `Block Packs`

Он должен быть похож не на settings form, а на менеджер расширений:

- название пакета;
- версия;
- совместимость;
- источник;
- количество блоков;
- статус активен/неактивен.

## 11. Минимальная backend-структура папок

### 🔴 Запланировано

Для первой версии ожидается такая структура:

```text
system/controllers/landingbuilder/
  backend.php
  frontend.php
  backend/
    actions/
      pages.php
      page_edit.php
            shell.php
            shell_variant_edit.php
            design.php
      bindings.php
      binding_edit.php
      blocks.php
      packs.php
      adapters.php
    forms/
      form_options.php
      form_page.php
            form_shell_variant.php
            form_design_system.php
      form_binding.php
```

## 12. Что уже можно считать зафиксированным

### 🟢 Готово

1. Конструктор не подменяет тему сайта.
2. Конструктор живёт как отдельный backend-компонент.
3. Глобальные настройки лежат в `options` самого компонента.
4. Bindings управляются в отдельном разделе компонента плюс через opt-in в ctype.
5. Экран подключения должен быть отдельной формой привязки, а не просто одним чекбоксом.
6. Для страниц и ctype должен настраиваться participation mode.
7. Для ctype должны отдельно настраиваться dynamic sources и collection-доступ.
8. Внутри продукта должны существовать отдельные экранные роли `Shell Builder`, `Глобальные стили` и `Visual Builder Workspace`, при этом design system, component library и adapters не должны схлопываться в один сценарий.

## 13. Следующий логичный шаг

### 🔴 Запланировано

Следующий документ должен формализовать data model для:

1. pages;
2. bindings;
3. presets;
4. adapters registry;
5. packs registry.