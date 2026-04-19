# NordicBlocks — Catalog Browser V1

Дата: 2026-04-19

## 1. Статус документа

Этот документ фиксирует жёсткое ТЗ для первого block type каталожной линейки NordicBlocks.

Его роль:

1. перевести family-level решение из `CATALOG-FAMILY-V1.md` в implementation-grade спецификацию;
2. зафиксировать минимально сильный v1 без расползания в half-ecommerce;
3. дать команде понятный порядок внедрения по слоям `block -> contract -> data -> editor -> runtime -> smoke`.

Каноническая связка для этого блока:

1. `docs/nordicblocks/CATALOG-FAMILY-V1.md`;
2. `docs/nordicblocks/GLOBAL-DESIGN-FOUNDATION-V2.md`;
3. этот документ.

## 2. Идентичность блока

Первый блок каталожной линейки фиксируем так:

1. пользовательское название: `Каталог`;
2. технический slug: `catalog_browser`;
3. product-role: базовый catalog explorer для услуг, работ, кейсов и mini-catalog сценариев;
4. архитектурная роль: первый системообразующий block type для всего catalog family.

`catalog_browser` обязан стать базой для:

1. card contract каталожных сущностей;
2. toolbar/filter/search модели;
3. media modal/gallery поведения;
4. manual/content_list data mapping;
5. последующих block types `Категории и каталог` и `Витрина каталога`.

## 3. Главная продуктовая задача

Этот блок нужен не для checkout и не для оформления заказа.

Его задача:

1. помочь пользователю быстро просматривать ассортимент или портфолио внутри одной секции;
2. позволить находить карточки по поиску, категории и цене;
3. давать быстрый переход в карточку или в messenger CTA;
4. раскрывать фото в fullscreen modal без ухода со страницы;
5. одинаково работать в demo/manual и в данных InstantCMS.

Итоговая позиция:

это `commerce-lite / catalog-explorer`, а не магазин.

## 4. Жёсткие правила v1

Для `catalog_browser` сразу фиксируем ограничения, которые нельзя размывать по ходу реализации.

### 4.1 Product rules

1. блок не занимается корзиной;
2. блок не занимается оплатой;
3. блок не хранит order state;
4. блок не превращается в большой каталог на сотни позиций с server-side faceting;
5. блок отвечает за discovery, comparison-light и lead generation.

### 4.2 Architectural rules

1. preview и live обязаны использовать один и тот же payload flow;
2. `render.php` не ходит в базу напрямую;
3. данные приходят через уже принятый NordicBlocks pipeline;
4. block type должен жить как отдельная директория в `system/.../blocks/catalog_browser` и в package mirror;
5. shared CSS хранится в общем runtime stylesheet, а не в preview-only слое.

### 4.3 UX rules

1. поиск и фильтры должны ощущаться как часть одного toolbar, а не как набор случайных полей;
2. mobile fullscreen modal для фото является частью канона, а не второстепенной опцией;
3. density по умолчанию должна быть плотной, landing-friendly и собранной;
4. категория является navigation/filter сущностью, а не только маленькой meta-меткой.

## 5. Что входит в v1

`catalog_browser` v1 обязан уметь:

1. выводить секционный header;
2. выводить toolbar каталога;
3. искать по карточкам на клиенте;
4. фильтровать по категориям;
5. фильтровать по цене `min/max`;
6. сортировать список;
7. показывать summary активных фильтров;
8. работать в `manual` и `content_list` режимах;
9. показывать карточки в grid;
10. поддерживать desktop grid в диапазоне `1..6` колонок и mobile grid `1..2` колонок;
11. поддерживать client-side режимы длинного каталога `all`, `load_more`, `pagination`;
12. позволять отключать отдельные поля из client-side search индекса в настройках блока;
13. открывать изображение карточки в media modal;
14. поддерживать gallery массива карточки;
15. поддерживать CTA типа `url`, `whatsapp`, `telegram`, `phone`, `none`.

## 6. Что сознательно не входит в v1

Чтобы блок не расползся, в первую волну сознательно не входят:

1. checkout;
2. корзина;
3. backend faceted search;
4. remote pagination;
5. nested categories tree;
6. товарные вариации;
7. сравнение товаров;
8. избранное;
9. mixed manual + dynamic items внутри одной и той же выдачи;
10. сложный zoom engine уровня отдельной gallery app.

Уточнение:

client-side `load_more` и `pagination` внутри уже отрендеренного списка входят в v1 и не противоречат этому ограничению. Вне scope остаётся только server-side / remote pagination.

## 7. Сценарии использования

Блок v1 должен покрывать минимум такие сценарии:

1. каталог услуг с кнопкой связи;
2. каталог работ с fullscreen просмотром фото;
3. mini-catalog товаров без корзины;
4. каталог кейсов с фильтром по направлениям;
5. меню/прайс с категориями и ценой.

Важно:

один и тот же block type должен выдерживать все эти сценарии без форка архитектуры.

## 8. Композиция блока

Каноническая структура `catalog_browser` v1 такая:

1. `header`;
2. `toolbar`;
3. `active filters summary`;
4. `grid`;
5. `empty state`;
6. `optional section CTA`;
7. `media modal`.

### 8.1 Header

Header содержит:

1. заголовок секции;
2. подзаголовок секции;
3. optional CTA секции.

### 8.2 Toolbar

Toolbar содержит:

1. поле поиска;
2. category filter;
3. price range controls;
4. sort control.

### 8.3 Grid

Grid содержит карточки каталога с повторяемым contract shape.

### 8.4 Modal

Modal открывается по клику на cover image карточки и, если есть `gallery[]`, позволяет листать её содержимое.

## 9. Сущности блока

Для инспектора и runtime фиксируем такие сущности:

1. `section`;
2. `header`;
3. `title`;
4. `subtitle`;
5. `sectionLink`;
6. `toolbar`;
7. `searchField`;
8. `categoryFilter`;
9. `priceFilter`;
10. `sortControl`;
11. `activeFilters`;
12. `grid`;
13. `cardSurface`;
14. `cardImage`;
15. `cardBadge`;
16. `cardCategory`;
17. `cardTitle`;
18. `cardExcerpt`;
19. `cardPrice`;
20. `cardMeta`;
21. `cardPrimaryAction`;
22. `mediaModal`;
23. `mediaViewport`;
24. `mediaNavPrev`;
25. `mediaNavNext`;
26. `mediaClose`;
27. `mediaCounter`;
28. `emptyState`.

## 10. Contract модели карточки

Каждый item каталога в v1 обязан поддерживать единый payload shape.

### 10.1 Обязательные поля contract

1. `title`;
2. `url`;
3. `image`;
4. `imageAlt`;
5. `category`;
6. `categoryUrl`;
7. `excerpt`;
8. `price`;
9. `priceOld`;
10. `currency`;
11. `badge`;
12. `tags`;
13. `ctaLabel`;
14. `ctaKind`;
15. `ctaUrl`;
16. `messengerType`;
17. `availability`;
18. `gallery`.

### 10.2 Нормализация item payload

После нормализации runtime должен получать предсказуемую структуру:

1. строковые поля очищены и безопасны для SSR;
2. цена присутствует и как display text, и как numeric значение для фильтрации;
3. `tags` нормализованы в массив строк;
4. `gallery` нормализована в массив объектов;
5. все optional поля gracefully деградируют при отсутствии.

### 10.3 Gallery shape

Каждый элемент `gallery[]` в v1 поддерживает:

1. `src`;
2. `alt`;
3. `type`;
4. `caption`.

`type` в v1 должен минимум поддерживать `image`.

## 11. Data modes

`catalog_browser` v1 поддерживает только два канонических режима:

1. `manual`;
2. `content_list`.

### 11.1 Manual mode

Используется когда:

1. каталог собирается руками;
2. блок нужен как быстрый лендинговый модуль;
3. у пользователя ещё нет подходящего content type;
4. нужен demo-ready starter state.

Требование:

manual starter state обязателен и должен выглядеть как законченный рабочий каталог, а не пустой repeater.

### 11.2 Content list mode

Используется когда:

1. карточки приходят из выбранного content type;
2. каталог должен жить на реальных данных InstantCMS;
3. блок должен автоматически обновляться при изменении записей.

Требование:

`content_list` подключается через существующий NordicBlocks data pipeline, без прямых запросов из `render.php`.

## 12. Mapping fields для content_list

Для `content_list` block обязан поддерживать mapping минимум таких полей:

1. `title`;
2. `excerpt`;
3. `image`;
4. `imageAlt`;
5. `category`;
6. `categoryUrl`;
7. `price`;
8. `priceOld`;
9. `currency`;
10. `badge`;
11. `tags`;
12. `url`;
13. `ctaLabel`;
14. `ctaUrl`;
15. `availability`;
16. `gallery`.

Если часть полей отсутствует в content type, блок обязан:

1. не падать;
2. использовать graceful fallback;
3. не показывать сломанные контролы и пустые декоративные оболочки.

## 13. Search / filter / sort model

### 13.1 Search

Поиск v1 работает по:

1. `title`;
2. `excerpt`;
3. `category`;
4. `tags`.

Поиск выполняется на клиенте по уже загруженному payload.

### 13.2 Category filter

Category filter v1 фиксируем так:

1. flat list категорий;
2. single-select как канонический UI для первого прохода;
3. multi-select не обязателен для первой реализации.

### 13.3 Price filter

Price filter v1:

1. поддерживает `min` и `max`;
2. работает только если в payload есть numeric price;
3. при отсутствии numeric price должен автоматически отключаться или скрываться.

### 13.4 Sort options

В первой версии поддерживаем:

1. `manual`;
2. `title_asc`;
3. `title_desc`;
4. `price_asc`;
5. `price_desc`;
6. `newest`, если это поддерживает источник.

## 14. Media modal model

Media modal является обязательной частью `catalog_browser` v1.

### 14.1 Поведение modal

1. клик по cover image открывает modal viewer;
2. если у карточки есть `gallery[]`, modal стартует с cover и даёт перейти к соседним кадрам;
3. если у карточки одно изображение, modal работает как single-image viewer;
4. на mobile режим по умолчанию: `fullscreen`;
5. на desktop разрешены `fullscreen` и `centered`, но fullscreen остаётся базовым сценарием.

### 14.2 Navigation

1. prev/next controls обязательны;
2. swipe left/right обязателен для touch;
3. close button должна быть явной и доступной;
4. modal обязан управлять body scroll lock;
5. modal обязан закрываться без ломания состояния страницы.

### 14.3 Контент modal

Внутри modal v1 допускаются:

1. изображение;
2. счётчик кадра;
3. заголовок карточки;
4. optional описание;
5. optional CTA карточки.

## 15. Вкладка Контент

Во вкладке `Контент` должны жить:

1. заголовок секции;
2. подзаголовок секции;
3. текст CTA секции;
4. URL CTA секции;
5. manual repeater карточек;
6. toggles видимости toolbar и частей карточки.

### 15.1 Visibility toggles

Обязательные toggles v1:

1. `Показывать поиск`;
2. `Показывать фильтр категорий`;
3. `Показывать фильтр цены`;
4. `Показывать сортировку`;
5. `Показывать summary фильтров`;
6. `Показывать изображение`;
7. `Показывать категорию`;
8. `Показывать badge`;
9. `Показывать цену`;
10. `Показывать старую цену`;
11. `Показывать анонс`;
12. `Показывать CTA карточки`;
13. `Включить modal по клику на фото`;
14. `Fullscreen modal по умолчанию`;
15. `Показывать navigation в modal`;
16. `Показывать счётчик изображений`.

### 15.2 Поля одной карточки manual repeater

1. `Категория`;
2. `URL категории`;
3. `Заголовок`;
4. `Описание`;
5. `Цена`;
6. `Старая цена`;
7. `Валюта`;
8. `Badge`;
9. `Теги`;
10. `URL карточки`;
11. `Текст CTA`;
12. `Тип CTA`;
13. `URL CTA`;
14. `Тип мессенджера`;
15. `Изображение`;
16. `Alt`;
17. `Галерея изображений`;
18. `Наличие`.

## 16. Вкладка Дизайн

Во вкладке `Дизайн` v1 должны жить:

1. тема блока;
2. section background;
3. toolbar surface;
4. style category chips;
5. style price line;
6. card surface;
7. media ratio;
8. typography title/subtitle/category/title/excerpt/price/cta;
9. modal surface;
10. typography modal title/description/counter;
11. density preset.

### 16.1 Density preset

Для `catalog_browser` фиксируем:

1. дефолтный preset: `dense`;
2. плотность должна быть лендинговой и собранной, без тяжёлого ecommerce-воздуха;
3. visual language должен быть совместим и с услугами, и с portfolio, и с mini-shop сценариями.

## 17. Вкладка Макет

Во вкладке `Макет` должны жить:

1. ширина контейнера;
2. колонки desktop/tablet/mobile;
3. gap карточек;
4. внутренний gap карточки;
5. padding секции desktop/mobile;
6. отступ между header и toolbar;
7. отступ между toolbar и grid;
8. sticky toolbar toggle;
9. modal mode;
10. modal max width для centered desktop режима.

## 18. Вкладка Данные

Во вкладке `Данные` должны жить:

1. `manual | content_list`;
2. выбор content type;
3. filter;
4. sort;
5. limit;
6. empty behavior;
7. field mapping каталога.

## 19. Runtime поведение

### 19.1 SSR baseline

Сервер обязан отрисовывать:

1. header;
2. toolbar оболочку;
3. список карточек;
4. empty state;
5. data attributes для клиентского filter runtime;
6. modal markup или контейнер для него.

### 19.2 Client enhancement

Клиентский runtime v1 отвечает за:

1. поиск по локальному payload;
2. category filtering;
3. price filtering;
4. sort;
5. active filters summary;
6. modal open/close;
7. modal prev/next;
8. swipe navigation.

Требование:

без enhancement блок не должен ломать SSR DOM и базовую навигацию.

## 20. Реализация по слоям

### 20.1 Block directory

Нужно создать новый block directory:

1. `system/controllers/nordicblocks/blocks/catalog_browser/`;
2. package mirror в `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/catalog_browser/`.

Минимальный состав:

1. `schema.json`;
2. `meta.json`;
3. `manifest.php`;
4. `render.php`.

### 20.2 First-wave registration

Нужно:

1. добавить `catalog_browser` в первую волну block types;
2. убедиться, что create/edit flow не выбрасывает этот тип из активного editor pipeline.

### 20.3 Contract/Data layer

Нужно:

1. добавить поддержку `catalog_browser` в contract normalizer;
2. определить, какой объём shared card pipeline можно безопасно переиспользовать;
3. добавить item payload normalization для price/category/gallery/cta полей;
4. добавить `content_list` mapping для catalog fields;
5. обеспечить preview/live parity через существующий hydration flow.

### 20.4 Editor shell

Нужно:

1. подключить manifest блока к unified inspector;
2. открыть для блока `Контент / Дизайн / Макет / Данные`;
3. добавить repeater editing для catalog card fields;
4. добавить data mapping controls для catalog fields;
5. не делать отдельный legacy editor path.

### 20.5 Runtime render

Нужно:

1. собрать SSR render секции;
2. выводить toolbar и карточки на одном payload;
3. поддержать graceful empty state;
4. подготовить data attributes для client-side runtime;
5. поддержать modal/gallery contract.

### 20.6 Shared CSS

Нужно:

1. добавить shared runtime CSS в общий `blocks.css`;
2. синхронизировать template copies;
3. не разводить preview/live styling.

### 20.7 Client-side runtime

Нужно:

1. добавить небольшой runtime для фильтров;
2. добавить runtime для modal/gallery;
3. обеспечить touch swipe и desktop navigation;
4. не тащить тяжёлую frontend архитектуру ради одного блока.

## 21. Минимальный порядок внедрения

Первый проход должен идти так:

1. зафиксировать spec и checklist;
2. создать block directory и first-wave registration;
3. поднять schema/meta/manifest;
4. реализовать contract/data normalization;
5. собрать SSR render без полного JS-комфорта;
6. добавить shared CSS;
7. добавить client-side filter/modal runtime;
8. прогнать manual и content_list smoke.

## 22. Definition of Done

`catalog_browser` v1 считается собранным, если выполнены все условия ниже.

### 22.1 Product DoD

1. блок вставляется из списка блоков;
2. manual starter state выглядит как рабочий каталог;
3. поиск, категория, цена и сортировка реально влияют на выдачу;
4. фото карточки открывается в modal;
5. на телефоне modal удобно листается.

### 22.2 Editor DoD

1. блок открывается в active unified editor;
2. все основные поля карточек доступны из repeater UI;
3. design/layout/data панели работают без fallback на старый поток;
4. сохранение и reload не теряют данные.

### 22.3 Data DoD

1. manual mode стабилен;
2. content_list mode подтягивает реальные записи;
3. mapping category/price/url/image работает;
4. graceful degradation соблюдается для неполного content type.

### 22.4 Runtime DoD

1. preview/live parity подтверждена;
2. shared CSS и package mirrors синхронизированы;
3. empty state корректен;
4. modal не ломает scroll и не зависает.

## 23. Обязательная проверка после первого кодового прохода

### 23.1 Функциональная проверка

1. create блока через админку;
2. открытие editor preview;
3. manual mode: add/edit/save/reload карточек;
4. content_list mode на реальном content type;
5. проверка поиска, категории, цены и сортировки;
6. проверка modal и gallery.

### 23.2 Техническая проверка

1. syntax check для всех изменённых PHP-файлов;
2. проверка общих CSS copies;
3. проверка package mirror;
4. smoke after save/reload.

## 24. Точка отсечения для следующих версий

Если после v1 потребуется усложнение, оно должно идти уже отдельными решениями:

1. `catalog_browser v1.1` для расширения filter UX;
2. `Категории и каталог` как category-first derivative;
3. `Витрина каталога` как featured/heroic derivative;
4. backend-scale и faceting только отдельным этапом.

Итоговое правило:

первый проход должен собрать сильный landing-grade catalog block, но не превращать NordicBlocks в ecommerce engine.

## 25. Текущий implementation snapshot

Ниже фиксируется не новое ТЗ, а текущее рабочее состояние `catalog_browser` на 2026-04-19, чтобы пользователи и следующие агенты не восстанавливали картину по коммитам и smoke-логам.

### 25.1 Где лежит source of truth

Для этого блока канонически важны такие точки:

1. runtime block directory: `system/controllers/nordicblocks/blocks/catalog_browser/`;
2. package mirror block directory: `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/catalog_browser/`;
3. shared editor shell: `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`;
4. package mirror editor shell: `packages/nordicblocks/package/templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`;
5. contract normalization: `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`;
6. rollout/checklist status: `docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json`.

Практическое правило:

если меняется UX редактора, contract карточки или import/export поток, сначала правим shared live template и runtime слой, затем синхронизируем package mirror.

### 25.2 Что блок реально умеет сейчас

На текущем этапе `catalog_browser` уже работает как production-grade блок первой волны, а не как черновой prototype.

Подтверждённые возможности:

1. SSR-first render с preview/live parity;
2. toolbar поиска, category filter, price filter и sort controls;
3. client-side long-list режимы `all`, `load_more`, `pagination`;
4. media modal и gallery просмотр карточек;
5. manual и `content_list` data modes;
6. desktop/mobile grid настройки;
7. unified inspector без legacy editor fallback;
8. package mirror parity для runtime и editor shell.

### 25.3 Канонический editor workflow

Для пользователей и агентов важно считать каноническими именно эти сценарии редактирования.

#### 25.3.1 Ручное редактирование карточек

Если каталог небольшой или лендинговый, основной путь такой:

1. открыть вкладку `Контент`;
2. работать с сущностью `Список карточек`;
3. редактировать repeater items прямо в unified inspector;
4. использовать быстрые media controls рядом с карточкой;
5. сохранять блок обычным editor flow без отдельного режима публикации.

#### 25.3.2 JSON workflow

JSON остаётся техническим, но поддерживаемым сценарием для power users и агентов.

Сейчас доступны:

1. `Демо JSON` для получения рабочего примера;
2. `Экспорт JSON` для roundtrip текущих карточек;
3. `Импорт JSON` для обратной загрузки;
4. stable item ids и skip-on-reimport semantics.

Это значит:

если в импортируемом payload уже есть карточка с тем же стабильным ID, редактор пропускает дубликат, а не плодит копии.

#### 25.3.3 Табличный режим

Для не технических пользователей канонический сценарий теперь другой:

1. открыть `Таблица` в topbar или кнопку `Открыть табличный режим` в панели повторов;
2. увидеть spreadsheet-like grid editor, а не plain textarea;
3. вставить диапазон из Excel или Google Sheets прямо в ячейки;
4. проверить preview справа по строкам;
5. импортировать только после проверки статусов `Импорт / Пропуск / Ошибка`.

Текущие свойства этого режима:

1. сетка поддерживает редактирование ячеек напрямую;
2. Enter переводит фокус вниз по колонке;
3. можно добавлять и удалять строки;
4. вставка диапазона в первую ячейку заменяет таблицу целиком;
5. preview справа показывает, сколько строк будет импортировано, пропущено или отбраковано.

#### 25.3.4 Прямой импорт Excel

Без промежуточного copy-paste поддержан прямой импорт настоящего файла `xlsx/xls`.

Канонический сценарий:

1. нажать `Импорт XLSX` в верхней панели или `Загрузить XLSX` в modal;
2. выбрать файл Excel;
3. дождаться загрузки первого листа в grid editor;
4. проверить строки в preview;
5. импортировать в карточки блока.

Текущее ограничение этой версии:

используется первый лист книги Excel; отдельный sheet-picker пока не входит в v1.

### 25.4 Нормализация и import semantics

Чтобы блок оставался предсказуемым при повторных загрузках, фиксируем текущие правила.

1. каждая карточка получает стабильный `id/itemId/item_id`;
2. повторный импорт не дублирует уже существующие карточки с тем же ID;
3. при отсутствии ID он генерируется автоматически;
4. JSON/XLSX/табличный import больше не подтягивает demo-default значения в пустые поля;
5. import preview показывает строковые ошибки до фактического применения изменений.

Практическое следствие:

для операторов безопаснее повторно импортировать каталог с сохранёнными ID, чем удалять и собирать его заново.

### 25.5 Поведение unified inspector

На текущем этапе исправлены важные UX-особенности инспектора, которые нужно считать каноническими.

1. вкладки `Контент / Дизайн / Макет / Данные` работают в одном shell и не должны вести на пустые панели;
2. для `Контент` блок предпочитает сущность `items`, а не произвольную последнюю selection state;
3. если editor автоматически выбрал полезную сущность, пользователь видит заметный explanatory notice;
4. color controls показываются как реальные color swatches, а не как малозаметные текстовые inputs;
5. media aspect ratio и object-fit доступны рядом с карточечными настройками как быстрые controls.

### 25.6 Что рекомендовать пользователю, а что агенту

Чтобы не путать workflows, фиксируем простое правило выбора режима.

Для обычного пользователя рекомендовать:

1. ручное редактирование в repeater для малого каталога;
2. табличный режим для bulk-правок;
3. прямой xlsx import для загрузки из Excel.

Для агента рекомендовать:

1. JSON export/import при массовом контрактном редактировании;
2. табличный режим, если задача ближе к операторскому UX;
3. проверку stable IDs перед повторным импортом.

### 25.7 Что ещё не считать частью v1

Несмотря на текущую зрелость, следующие ожидания пока не считаются реализованным каноном:

1. выбор листа Excel из multi-sheet workbook;
2. импорт настоящего `xlsx` в `content_list` mapping flow как отдельный источник данных;
3. полноценная excel-like multi-cell selection мышью;
4. backend faceting и remote pagination;
5. ecommerce features уровня корзины и checkout.