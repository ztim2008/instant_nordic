# NordicBlocks — Линейка Новости v1

## 1. Зачем нужна эта линейка

После `hero`-семейства первой продуктовой линейкой контентных блоков в NordicBlocks становится новостное семейство.

Его задача:

1. дать журналоподобные секции главной страницы без второго page-builder;
2. опереться на уже существующий `content_list` runtime InstantCMS, не ломая manual-first сценарий;
3. собрать один общий card-runtime, из которого потом вырастут следующие похожие блоки;
4. не копировать donor-референсы буквально, а превратить их в собственную управляемую систему.

Ключевое правило для этой линейки:

1. каждый блок обязан работать в двух режимах: `manual` и `content_list`;
2. ручной режим нужен не как временный обход, а как полноценный demo и starter-сценарий;
3. data-режим нужен для боевой привязки к данным InstantCMS;
4. внутри одного блока в v1 не смешиваем manual и dynamic карточки одновременно.

## 2. Family-first стандарт для этой и следующих линеек

Для каждой новой линейки действует один и тот же порядок:

1. сначала фиксируется мини-линейка из 3 родственных блоков;
2. затем выбирается только один стартовый блок;
3. до кода утверждается рабочее ТЗ по сущностям, вкладкам и границам v1;
4. только после этого начинается реализация;
5. остальные 2 блока линейки строятся уже поверх того же runtime и того же card-language.

## 3. Состав линейки Новости

Первая мини-линейка фиксируется так:

1. `Лента новостей`
2. `Рубрика с карточками`
3. `Главная статья и лента`

Роли внутри линейки:

1. `Лента новостей` — базовый блок-системообразователь;
2. `Рубрика с карточками` — секция категории / раздела на главной;
3. `Главная статья и лента` — акцентная журнальная подача с одним lead-материалом.

Для третьего блока линейки фиксируем и визуальный каркас, чтобы он не превратился в разовый кастом:

1. первый материал всегда выступает как lead-card;
2. остальные материалы остаются продолжением ленты в том же block type;
3. v1-реализация использует несколько готовых visual presets, а не свободную мозаичную сборку;
4. стартовые presets для этого блока: `split`, `stack`, `cover`.

Дополнение после сборки первой волны:

1. семейство может порождать отдельные derivative block types, если donor-pattern уже не укладывается в visual preset существующего блока;
2. первый подтверждённый derivative после базовой тройки: `swiss_grid`;
3. `swiss_grid` использует тот же card/data contract (`manual` + `content_list`), но имеет собственный block type, SSR render и visual language.

### 3.1 Reusable visual baseline для news-линии

После сборки `swiss_grid` фиксируем ещё одно правило для этой и следующих editorial-линеек:

1. `swiss_grid` считается первым подтверждённым reusable visual baseline для news/editorial направления;
2. канонический арт-документ для этого baseline: `docs/nordicblocks/NEWS-VISUAL-BASELINE-SWISS-V1.md`;
3. baseline наследуется не как копия одного блока, а как композиционный язык для следующих block types;
4. плотная сетка с `gap: 0`, строгая типографика и минималистичный контраст считаются дефолтной точкой старта для следующих news-блоков, пока новый spec не зафиксирует осознанное исключение.

## 4. Первый блок к реализации

Первым блоком линейки в коде становится:

1. пользовательское название: `Лента новостей`;
2. технический смысл: универсальный block для карточек материалов с двумя source-режимами `manual` и `content_list`;
3. технический slug рекомендуется держать нейтральным, а не жёстко новостным, например `content_feed`, чтобы runtime не привязывался к одному ctype;
4. в интерфейсе редактора и каталоге блоков название остаётся русским: `Лента новостей`.

## 5. Назначение блока `Лента новостей`

Блок показывает карточечную ленту материалов.

Что он должен уметь в продукте:

1. работать в режиме `manual` как стартовый демонстрационный и ручной блок;
2. работать в режиме `content_list` как блок, подключённый к данным InstantCMS;
3. показывать секционный заголовок и ссылку `Все материалы`;
4. отображать единый card-layout с фото, заголовком, анонсом и meta;
5. работать одинаково в preview и live через общий SSR runtime.

## 5.1 Режимы блока

Для `Ленты новостей` в v1 фиксируются два канонических режима:

1. `manual`
2. `content_list`

### `manual`

Используется когда:

1. блок нужно быстро поставить на страницу без подключения данных;
2. нужно показать demo-пример в каталоге или на тестовой сборке;
3. нужно собрать редакционную подборку руками.

### `content_list`

Используется когда:

1. блок должен брать записи из выбранного ctype;
2. блок должен автоматически обновляться по данным InstantCMS;
3. нужна одна и та же логика для `Новости`, `Статьи`, `Посты` и других совместимых типов.

## 5.2 Demo-изображения и стартовый контент

Для manual-режима в v1 разрешён и ожидается starter preset.

Правило:

1. блок не стартует с пустого списка карточек;
2. в блоке должны быть локальные demo-карточки по умолчанию;
3. demo-изображения используем из локального набора компонента, по текущему канону через пути вида `/upload/nordicblocks/demo/...`;
4. позже пользователь может заменить эти изображения через managed media / gallery flow.

## 6. Сущности блока

Для `Ленты новостей` фиксируем следующие сущности v1:

1. `section` — внешняя секция блока, фон, paddings, theme;
2. `header` — верхняя зона секции;
3. `heading` — заголовок секции;
4. `description` — короткое описание секции;
5. `moreLink` — ссылка `Все материалы`;
6. `feed` — контейнер карточек / grid-обёртка;
7. `cardSurface` — единая поверхность карточки;
8. `cardImage` — изображение карточки;
9. `cardCategory` — рубрика / метка;
10. `cardTitle` — заголовок карточки;
11. `cardExcerpt` — анонс / краткий текст;
12. `cardMeta` — дата, просмотры, комментарии.

Важно:

1. в v1 это единый стиль для всех карточек блока;
2. per-card overrides не входят;
3. lead-card логика для первой карточки не входит — это отдельный следующий блок линейки.

## 7. Вкладка `Контент`

Во вкладке `Контент` для v1 живут:

1. ручные поля секции;
2. semantic toggles;
3. manual-repeater карточек, который активен в режиме `manual`.

### 7.1 Поля `Контент`

1. `Заголовок секции`
2. `Описание секции`
3. `Текст ссылки`
4. `URL ссылки`

### 7.2 Переключатели видимости

1. `Показывать описание секции`
2. `Показывать ссылку`
3. `Показывать изображение карточки`
4. `Показывать рубрику`
5. `Показывать анонс`
6. `Показывать дату`
7. `Показывать просмотры`
8. `Показывать комментарии`

### 7.3 Ручной repeater карточек

В режиме `manual` блок должен иметь repeater `Карточки`.

Поля одной карточки v1:

1. `Рубрика`
2. `Заголовок`
3. `Анонс`
4. `Текст CTA`
5. `URL`
6. `Изображение`
7. `Alt-текст`
8. `Дата`
9. `Просмотры`
10. `Комментарии`

Важно:

1. это не per-card design editor, а только content-repeater;
2. все карточки используют общий visual style блока;
3. default-state блока должен содержать заполненные demo-карточки.

### 7.4 Что сознательно не входит в `Контент` v1

1. смешивание manual и dynamic карточек в одном списке;
2. рекламные вставки между карточками;
3. отдельные визуальные настройки для конкретной карточки;
4. ручное управление сложной мозаикой карточек.

## 8. Вкладка `Дизайн`

Во вкладке `Дизайн` для v1 фиксируем только те настройки, которые реально меняют визуальный язык блока без зоопарка полей.

### 8.1 Поля `Дизайн` v1

1. `Тема блока`
2. `Режим фона секции`
3. `Цвет / градиент / фото фона` через уже принятый section-background pattern
4. `Формат изображения карточки`
5. `Скругление изображения`
6. `Скругление карточки`
7. `Тень карточки`
8. `Стиль метки рубрики`

### 8.2 Типографика v1

1. `Заголовок секции` — size/weight/color desktop/mobile
2. `Описание секции` — size/color desktop/mobile
3. `Заголовок карточки` — size/weight/color desktop/mobile
4. `Анонс карточки` — size/color desktop/mobile
5. `Meta карточки` — size/color desktop/mobile

### 8.3 Что не входит в `Дизайн` v1

1. отдельный visual preset на каждую карточку;
2. отдельные настройки для первой, второй и последующих карточек;
3. сложные animation-паттерны карточек;
4. свободная раскраска каждого meta-элемента по отдельности.

## 9. Вкладка `Макет`

Во вкладке `Макет` должны жить только layout-controls секции и сетки, а не контент и не data-binding.

### 9.1 Поля `Макет` v1

1. `Ширина контейнера`
2. `Колонки desktop`
3. `Колонки mobile`
4. `Gap карточек`
5. `Padding top desktop/mobile`
6. `Padding bottom desktop/mobile`
7. `Отступ между header и grid`

### 9.2 Что не входит в `Макет` v1

1. masonry;
2. slider/carousel;
3. split-layout с одной большой карточкой;
4. ручное перетаскивание карточек внутри блока.

## 10. Вкладка `Данные`

Вкладка `Данные` для `Ленты новостей` управляет источником данных блока.

Важное правило v1:

1. `Данные` не заменяют `Контент`;
2. `Контент` отвечает за manual-карточки и секционный текст;
3. `Данные` переключают блок между `manual` и `content_list`.

### 10.1 Базовые поля `Данные` v1

1. `Источник`
2. `Тип контента`
3. `Лимит`
4. `Сортировка`
5. `Поведение при пустом списке`

Допустимые значения `Источник` в v1:

1. `Ручной контент`
2. `Список записей InstantCMS`

### 10.2 Минимальный field mapping v1

Чтобы блок был универсальным для разных ctype, но не расползался в arbitrary schema-builder, в v1 фиксируем только ограниченный mapping для стандартных слотов:

1. `Поле заголовка`
2. `Поле анонса`
3. `Поле изображения`
4. `Поле рубрики`
5. `Поле даты`
6. `Поле просмотров`
7. `Поле комментариев`

### 10.3 Стартовый mapping-профиль для типа `news`

На текущем сайте у типа контента `news` уже подтверждены реальные поля:

1. `title` — Заголовок новости
2. `date_pub` — Дата публикации
3. `user` — Автор
4. `content` — Текст новости
5. `teaser` — Краткое описание новости
6. `photo` — Фотография
7. `cats` — Категория

Стартовый рекомендуемый mapping для `Ленты новостей` при выборе ctype `news`:

1. `title` → `title`
2. `excerpt` → `teaser`
3. `image` → `photo`
4. `category` → `category.title`
5. `date` → `date_pub`
6. `views` → `hits_count`
7. `comments` → `comments_count`

Дополнительно:

1. `url` берётся из `record_url`;
2. при отсутствии `photo` допускается fallback на `record_image_url`, если runtime уже может его получить;
3. поле `content` в v1 не используется как основной источник карточечного анонса, если есть `teaser`.

Что не выносим в выбор в v1:

1. URL записи — берётся из стандартного record url/resolver;
2. произвольные дополнительные поля карточки;
3. пользовательские slot names;
4. сложные условия отображения на уровне одного record.

## 11. Что именно входит в первую реализацию

Первая реализация `Ленты новостей` включает только следующий контур:

1. один block class;
2. один card-runtime;
3. один grid-layout без lead-card логики;
4. два режима источника: `manual` и `content_list`;
5. starter demo-карточки в manual-режиме;
6. русские labels в inspector и каталоге блоков;
7. preview/live parity через тот же SSR render;
8. shared runtime CSS, а не локальный CSS-код на каждый экземпляр.

## 12. Что не входит в первую реализацию

Чтобы не расползаться, в v1 не входят:

1. `Рубрика с карточками` как отдельный block type;
2. `Главная статья и лента` как отдельный block type;
3. подрубрики над секцией;
4. mixed manual + dynamic card collection;
5. фильтрация по категории внутри самого блока, если для этого нужен отдельный новый adapter contract;
6. carousel / slider / masonry;
7. редактор ручной очередности динамических карточек;
8. рекламные или subscribe inserts между карточками;
9. infinite visual variants карточек.

## 13. Реализационный порядок

Порядок работы по коду фиксируем таким:

1. обобщить текущий `content_list` adapter от FAQ-специфики к generic card list;
2. спроектировать contract так, чтобы manual-карточки и dynamic feed жили в одном block type, но в разных source-режимах;
3. поднять новый block type для `Ленты новостей`;
4. собрать inspector tabs `Контент / Дизайн / Макет / Данные` с русскими labels;
5. реализовать SSR render и shared CSS runtime;
6. обеспечить starter demo state с локальными изображениями;
7. проверить live preview, save/reload, widget placement и cache invalidation;
8. только после этого переходить к следующему блоку линейки.

## 14. Definition of Done для `Ленты новостей` v1

Блок считается готовым для первой волны, если одновременно выполнено:

1. блок размещается через стандартный widget placement InstantCMS;
2. блок умеет работать и в `manual`, и в `content_list`;
3. блок умеет брать данные из любого выбранного ctype через `content_list`;
4. preview и live не расходятся;
5. на пустом результате блок не ломает страницу;
6. manual starter state не пустой и использует локальные demo-изображения;
7. inspector остаётся понятным и русифицированным;
8. runtime не дублирует FAQ-специфичную data-логику;
9. блок становится базой для двух следующих новостных блоков линейки, а не тупиковой веткой.

## 15. Технический план реализации по слоям

Ниже фиксируется не абстрактное направление, а конкретный первый технический проход для блока `Лента новостей`.

### 15.1 Перед кодом: checkpoint и безопасная точка отката

Так как следующий этап затрагивает contract normalization, editor flow, SSR render и shared runtime, перед кодом нужен checkpoint.

Минимум перед стартом:

1. создать файловый checkpoint через текущий repo flow;
2. зафиксировать, что SQL-миграций в этом этапе нет;
3. считать точкой отката checkpoint до открытия нового block type в active editor flow.

Важно:

1. backup базы на этом этапе не обязателен, если не появляется SQL;
2. если по ходу реализации понадобится новая таблица, seed или storage contract outside current block payload, нужен отдельный backup базы до следующего шага.

### 15.2 Что создаём как новый block type

В основной ветке runtime создаётся новый блок с нейтральным техническим slug `content_feed` и пользовательским названием `Лента новостей`.

С апреля 2026 у `content_feed` есть дополнительный visual preset `swiss`: строгая сетка без gap, flat-рамки, контрастная типографика и desktop-варианты в 2, 3 или 4 колонки через стандартное поле `columns_desktop`. Для донорских swiss/grid-макетов не нужен новый block type, если достаточно равномерной карточечной сетки без lead-материала.

Новый комплект файлов v1:

1. `system/controllers/nordicblocks/blocks/content_feed/schema.json`
2. `system/controllers/nordicblocks/blocks/content_feed/manifest.php`
3. `system/controllers/nordicblocks/blocks/content_feed/render.php`
4. `system/controllers/nordicblocks/blocks/content_feed/meta.json`
5. preview asset для каталога блока, если хотим сразу показывать его в picker первой волны

Package mirror обязателен тем же составом:

1. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/content_feed/schema.json`
2. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/content_feed/manifest.php`
3. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/content_feed/render.php`
4. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/content_feed/meta.json`

### 15.3 Что должен содержать новый contract v1

Минимальный contract блока должен быть сразу двухрежимным, без второго параллельного runtime.

Ожидаемая структура:

1. `content.header.title`
2. `content.header.description`
3. `content.header.moreLink.text`
4. `content.header.moreLink.url`
5. `content.items[]` как manual starter/feed reserve
6. `design.entities.*` для section, header, feed, cardSurface, cardImage, cardCategory, cardTitle, cardExcerpt, cardMeta
7. `layout.*` для container, columns, gaps, paddings, header spacing
8. `data.listSource.*` для `manual | content_list`, ctype, limit, sort, map, emptyBehavior

Каждый manual item v1 должен содержать:

1. `category`
2. `title`
3. `excerpt`
4. `url`
5. `image`
6. `imageAlt`
7. `date`
8. `views`
9. `comments`

### 15.4 Какие существующие PHP-слои нужно обобщить

#### `BlockContractNormalizer`

Задача:

1. добавить поддержку `content_feed` в `supportsContractType()`;
2. нормализовать `content.items[]` в канонический card-shape;
3. нормализовать `data.listSource.map.*` для карточечных слотов;
4. сохранить manual items как fallback-слой при повторном сохранении блока;
5. не затирать уже сохранённые значения новыми дефолтами, если поле явно не пришло из legacy.

#### `DataSourceResolver`

Задача:

1. открыть `content_list` не только для FAQ, а для нового card-feed блока;
2. вернуть editor options для `content_feed`;
3. использовать уже существующие generic field options для `title`, `teaser`, `photo`, `category.title`, `date_pub`, `hits_count`, `comments_count`, `record_url`, `record_image_url`;
4. оставить `manual` полноценным первым режимом, а не временной заглушкой.

#### `BindingMapper`

Задача:

1. добавить новую ветку mapping для `content_feed`;
2. преобразовывать список records в единый card array;
3. поддержать mapping слотов `title`, `excerpt`, `image`, `category`, `date`, `views`, `comments`, `url`;
4. вернуть contract-friendly данные без FAQ-терминов вроде `question` и `answer`.

#### `BlockPayloadHydrator` и `RenderCacheContext`

На текущем этапе полной переделки не требуют, но должны быть проверены на двух вещах:

1. manual fallback не ломается при пустом `content_list`;
2. cache key корректно реагирует на `ctype`, `limit`, `sort` и mapping блока.

### 15.5 Какие editor-слои нужно обобщить

Здесь есть важный скрытый риск: current editor shell уже partially generic, но manual repeater всё ещё прошит под FAQ.

#### `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`

Нужно:

1. убрать FAQ-специфичные helper'ы уровня `nbhBuildFaqItem()` и `nbhFaqItemValue()`;
2. заменить их на generic card item helpers для коллекционных блоков;
3. перестать использовать fallback-синонимы `question/answer` как основной shape для нового блока;
4. сделать `nbhAddRepeaterItem()` зависимым от block kind или repeater profile, чтобы для `content_feed` создавалась demo-card, а не FAQ item.

#### `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_ui_helpers.tpl.php`

Нужно:

1. вынести FAQ-only repeater UI в profile-driven или block-kind-driven режим;
2. для `content_feed` показывать карточку manual editor с полями рубрики, заголовка, анонса, URL, изображения и meta;
3. корректно подсвечивать, что manual cards остаются fallback-слоем, когда блок переключён в `content_list`.

#### `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_data.tpl.php`

Этот слой уже близок к reusable, но для `content_feed` нужно расширение.

Нужно:

1. добавить mapping-поля не только для `title/text`, а для полного card set;
2. не использовать FAQ-лексему `text` как универсальную замену анонса без явного профиля;
3. сохранить простой UX: сначала источник, потом ctype, потом mapping совместимых полей.

#### `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_design.tpl.php`

Проверить и при необходимости расширить profile для item typography, чтобы он описывал карточку новостей, а не вопрос/ответ.

Package mirror для editor templates обязателен синхронно с main copy.

### 15.6 Какие registry и editor gate нужно открыть

Чтобы новый блок вообще можно было редактировать через current active flow, нужно обновить две точки.

#### `system/controllers/nordicblocks/model.php`

Нужно:

1. добавить `content_feed` в первую волну block types;
2. убедиться, что каталог и preview block definition читают новый block directory без отдельной ручной регистрации.

#### `system/controllers/nordicblocks/backend/actions/block_edit.php`

Нужно:

1. открыть active editor flow для `content_feed`;
2. обновить текст ограничений первой волны, чтобы сообщение не врал после добавления нового типа.

### 15.7 SSR render и shared runtime CSS

Для `content_feed` нельзя делать отдельный preview-only runtime.

Нужно:

1. один SSR render path для preview и public;
2. shared block CSS в `system/controllers/nordicblocks/assets/blocks.css`;
3. синхронизация package copy и template CSS copies после добавления нового блока;
4. photo/image handling через уже принятый managed-media compatible flow;
5. корректный empty-state: либо manual fallback, либо пустой список без поломки DOM, в зависимости от `emptyBehavior`.

### 15.8 Demo assets и starter state

Для v1 manual starter state является обязательной частью продукта, а не декоративной опцией.

Нужно:

1. добавить локальные demo-изображения в текущий допустимый набор путей `/upload/nordicblocks/demo/...`;
2. собрать 3-4 дефолтные карточки с нормальными русскими заголовками и анонсами;
3. сделать так, чтобы блок после вставки выглядел как готовая новостная секция, а не как пустая заготовка.

## 16. Обязательная проверка после реализации

После первого кодового прохода проверка должна идти не по одному файлу, а по полному контуру.

### 16.1 Функциональная проверка

1. вставка блока в стандартный widget placement;
2. открытие блока в active editor без fallback на старый flow;
3. manual mode: карточки редактируются, сохраняются и переживают reload;
4. content_list mode: `news` подтягивается по реальным данным;
5. переключение `manual -> content_list -> manual` не теряет ручной starter list.

### 16.2 Preview/live parity

1. один и тот же набор карточек в editor iframe и на публичной странице;
2. одинаковая работа empty behavior;
3. одинаковая работа image fallback;
4. отсутствие расхождения после autosave и iframe reload.

### 16.3 Техническая проверка

1. php syntax check на все изменённые PHP-файлы;
2. smoke-проверка save/reload блока;
3. проверка cache invalidation после смены source settings;
4. проверка package mirror и template copies, чтобы runtime не разъехался между средами.

## Scaffold Registry
<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_START -->
Этот раздел обновляется автоматически scaffold apply pipeline и показывает текущие scaffold-managed block types этой family.

| Slug | Title | Status | Checkpoint | Live smoke |
| --- | --- | --- | --- | --- |
| bento_feed | Компактная бенто-лента | in_progress | snapshot/20260420-070131 | yes |
| category_cards | Рубрика с карточками | in_progress | snapshot/20260418-112025 | yes |
| content_feed | Лента новостей | in_progress | snapshot/20260418-102305 | yes |
| headline_feed | Главная статья и лента | in_progress | snapshot/20260418-171903 | yes |
| swiss_grid | Swiss Grid | in_progress | snapshot/20260419-064131 | yes |
<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_END -->
