# Активный план: Нордик

## Зачем этот файл

Это текущий execution tracker по Нордик после решения стартовать не от локальной полировки bridge-canvas, а от взрослой архитектуры продукта.

Файл нужен, чтобы команда держала один коридор: сначала foundation layer, потом visual editor core, потом system integration layer.

## Легенда статусов

- ⚪ Не начато
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Каноническая формула продукта

`InstantCMS 2 backend -> nordic runtime template -> design system / global defaults -> visual builder workspace -> component library -> widget/data adapter layer`

## Что считаем зафиксированным на сейчас

1. Текущий `landingbuilder` больше не считается финальной продуктовой границей.
2. Он используется как переходный bridge-слой для runtime, preview и части editor UX.
3. Новый builder boundary `nordicbuilder` должен проектироваться отдельно, contract-first и schema-first.
4. Основная dev-опора для дисциплины структуры и contracts в этом workspace это `instantcms-mcp-main`.
5. `Visual Builder Workspace` это главный ежедневный экран.
6. `Глобальные стили` это secondary UI над `design system / global defaults`.
7. `Shell Builder` это expert/system layer, а не главный пользовательский маршрут.

## Канонические роли экранов

1. `Visual Builder Workspace`: ежедневная сборка страницы из page/section/block/element.
2. `Глобальные стили`: редкие site-wide defaults.
3. `Shell Builder`: header/footer/menu placement, shell rules и assignment.

Экранные роли больше не подменяют архитектурные слои продукта.

## Канонический MVP-коридор

### 1. Foundation Layer

Статус: 🔵 В работе

Это первый взрослый этап. Без него дальше нельзя масштабировать editor и runtime без расползания по bridge-логике.

- Что фиксируем на этом этапе:
  - канонический JSON schema contract;
  - единый runtime contract для canvas, preview и live frontend;
  - design token model и порядок наследования;
  - минимальное ядро component library;
  - boundary компонента `nordicbuilder`;
  - bridge-стратегию переиспользования текущего `landingbuilder` и `nordic`.
- Что уже запущено:
  - синхронизирован product canon под взрослую архитектуру;
  - добавляется отдельный foundation spec как опорный документ этапа;
  - active plan перестраивается под 3-этапный MVP, а не под локальный visual-first slice;
  - в `nordicbuilder` поднят file-based contract registry на 5 канонических contracts;
  - в backend `nordicbuilder` добавлен browser `Контракты`, который читает registry как source-of-truth;
  - поверх registry добавлены storage stub и detail screen для contract tooling следующего слоя.
  - в `nordicbuilder` добавлен минимальный SQL-backed persistence layer для `page-document`, `preset-token` и `binding-options`, плюс workspace summary по состоянию foundation storage.
  - workspace `nordicbuilder` начал жить как первый реальный page-document loop: список документов, JSON save/load, bridge import из `landingbuilder` и более строгая validation структуры page document.
  - bridge-слой `landingbuilder` начал читать и сохранять страницы через `nordicbuilder page-document`, если для page key уже существует новый contract document.
  - в workspace `nordicbuilder` добавлен массовый migration route из `landingbuilder`: pending/imported каталог legacy-страниц, bulk import с опцией overwrite existing и отчет по результатам миграции.
- Что должно выйти из этапа:
  - понятный набор contracts, от которых можно строить и PHP, и runtime payload, и editor UI;
  - ясная граница между bridge-слоем текущего кода и будущим builder core;
  - минимальный список first-class entities: page, section, block, element, preset, adapter.
- Что считаем готовностью этапа:
  - новый код можно писать от contract boundary, а не от хаотичных форм и payload-ов;
  - понятно, какие части живут в `nordicbuilder`, какие остаются в `nordic`, а какие временно сидят в bridge-слое;
  - Visual Builder Workspace не проектируется больше вслепую без token и schema модели;
  - foundation layer имеет уже не только registry, но и реальную persistence base, от которой можно строить save/load loop.

### 2. Visual Editor Core

Статус: ⚪ Не начато как отдельный взрослый этап

Bridge-прототипы canvas уже существуют, но взрослый editor core начнется только после стабилизации foundation layer.

- Что будет целью этапа:
  - live inspector с продуктовой, а не технической структурой;
  - page/section/block editing прямо на холсте;
  - устойчивый save/preview/publish loop на одном runtime contract;
  - page tree, outline и insertion flow на базе component library.
- Что нельзя делать на этом этапе:
  - продолжать расширять bridge-canvas без опоры на contracts и tokens;
  - подменять component library случайным набором ad hoc controls.

### 3. System Integration Layer

Статус: ⚪ Не начато

Этот этап подключает сильные стороны InstantCMS уже после того, как foundation и editor core перестанут быть moving target.

- Что войдет сюда:
  - widget/data adapters;
  - shell participation visibility внутри workspace;
  - reusable component packs и page templates;
  - финальная упрощенная роль secondary screen `Глобальные стили`.

## Что теперь считается правильным ежедневным сценарием

- пользователь открывает страницу в `Visual Builder Workspace`;
- работает с page/section/block через живой canvas и context inspector;
- site-wide defaults меняет редко и отдельно;
- shell-структуру трогает только когда реально меняет каркас сайта;
- preview и live runtime используют тот же contract, а не параллельную самодельную логику.

## Что считаем неправильным направлением

- шлифовать текущий bridge-canvas как будто он уже и есть финальный builder;
- развивать продукт как набор экранов `Дизайн сайта + Shell Builder + canvas`, не фиксируя слой contracts;
- смешивать design system, component library и adapters в один пользовательский сценарий;
- принимать payload или структуру editor-а без связи с каноническими schema contracts.

## Ближайший implementation order

1. Зафиксировать foundation layer в отдельной спеки: contracts, tokens, component library core, bridge strategy.
2. Определить boundary компонента `nordicbuilder` и его install/package discipline по опоре `instantcms-mcp-main`.
3. После этого сделать первый минимальный scaffold `nordicbuilder`.
4. Поднять contract registry и browser внутри `nordicbuilder`.
5. Затем расширять contract tooling уже от живого registry/detail/storage/persistence слоя.
6. Только затем возвращаться к взрослой реализации `Visual Builder Workspace`.
7. Shell Builder, `Глобальные стили` и widget/data adapters расширять уже поверх foundation, а не вместо него.

## Reference-документы

- [LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md](LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md)
- [LANDING-BUILDER-VISUAL-FIRST-PIVOT-2026-04-05.md](LANDING-BUILDER-VISUAL-FIRST-PIVOT-2026-04-05.md)
- [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
- [LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md](LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md)
- [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md)
- [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)

## Имя нового builder component

Зафиксированное имя нового отдельного builder component: `nordicbuilder`.
