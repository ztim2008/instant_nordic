# NordicBlocks Unified Inspector Architecture

Дата: 2026-04-16

## 1. Зачем нужен отдельный план

Сейчас редактор блока уже работает, но логика инспектора пока слишком привязана к конкретному блоку.

Проблема не в том, что мало полей. Проблема в том, что у нас пока нет взрослой системы, которая отвечает на три базовых вопроса:

1. Какие элементы вообще есть у блока.
2. Какие общие настройки можно применить к этим элементам.
3. Какие панели нужно скрыть, если элемента в блоке нет.

Именно поэтому новый блок легко оказывается в состоянии:

1. есть рендер;
2. есть контент;
3. но нет взрослых настроек для заголовка, подзаголовка, поверхностей, кнопок, бейджей, рамок, карточек и повторяющихся элементов.

Цель этого документа: зафиксировать архитектуру, при которой инспектор перестает быть набором случайных полей из schema конкретного блока и становится единой системой управления блоками.

## 2. Продуктовая цель

Нужен UX уровня Webflow по ощущению контроля, но без ухода в freeform page-builder хаос.

Уточнение по зафиксированному product-вектору на 2026-04-17:

1. самые глубокие локальные настройки на первой волне нужны для фона секции, заголовка и подзаголовка;
2. остальные части блока по возможности сначала держим на adaptive layout + global foundation;
3. сильные панели inspector-а прячем в аккордеоны, а не разворачиваем длинной простынёй;
4. клик по сущности на canvas должен открывать именно её настройки в инспекторе.

Что это значит на практике:

1. Пользователь видит единый инспектор для всех блоков.
2. Блок не описывает весь UI инспектора вручную.
3. Блок только сообщает, какие сущности у него есть.
4. Инспектор сам собирает нужные панели по единому реестру.
5. Если в блоке нет subtitle, secondary button, media или item surface, пользователь не видит соответствующих настроек.
6. Если сущность есть, она получает взрослые настройки автоматически: типографика, spacing, surface, state, responsive overrides, bindings.

Итоговая формула должна быть такой:

не блок рисует инспектор, а инспектор рендерится из единого контракта и capability-модели.

## 3. Главная мысль

Нельзя дальше развивать NordicBlocks по модели:

1. сделали блок;
2. вручную добавили ему 20-40 новых полей;
3. потом для следующего блока снова сделали почти то же самое, но с другими ключами.

Это тупиковый путь.

Правильный путь:

1. есть глобальная foundation-система;
2. есть канонические сущности блока;
3. есть единый инспектор с общими секциями;
4. блок только декларирует присутствие сущностей и capability;
5. все остальное собирается из общего registry.

## 4. Что уже подсказывает текущий проект

Внутри текущих документов уже есть правильный каркас:

1. Block Contract v3: `meta / content / design / layout / data / entities`.
2. Inspector v2: `Контент / Дизайн / Макет / Данные`.
3. Canonical entity keys.
4. Repeaters вместо `item1/item2/item3`.
5. Slot bindings для данных.
6. Global foundation tokens для кнопок, радиусов, поверхностей и типографики.

Значит, задача не в том, чтобы придумать новое направление. Задача в том, чтобы довести до production-level уже намеченную архитектуру.

## 5. Почему сейчас больно

Текущее состояние нормально для старта, но неудобно для роста.

### 5.1 Блок диктует инспектор

Сейчас schema в основном перечисляет поля напрямую.

Следствие:

1. у двух похожих блоков легко появляются разные ключи для одинаковых сущностей;
2. title одного блока живет как `heading`, другого как `title`, третьего как `hero_title`;
3. настройки дизайна размазываются по частным ключам;
4. нельзя быстро собрать общий UI для всех блоков.

### 5.2 Нет capability-фильтра

У инспектора пока нет отдельного слоя, который говорит:

1. этот блок поддерживает title;
2. этот блок поддерживает subtitle;
3. этот блок поддерживает surfaces;
4. этот блок поддерживает repeater items;
5. этот блок не поддерживает media и secondary button.

Поэтому UI либо слишком бедный, либо превращается в длинную простыню.

### 5.3 Нет нормального bridge между canvas и inspector

Чтобы получить ощущение “как в Webflow”, мало показать формы справа.

Нужно, чтобы:

1. клик по элементу на canvas выбирал сущность;
2. инспектор открывал нужную секцию;
3. пользователь понимал, что он редактирует именно title, subtitle, item card или button.

### 5.4 Нет разделения уровней управления

Сейчас легко смешать в одном месте:

1. глобальные токены;
2. локальные block overrides;
3. layout;
4. content;
5. data bindings.

Это нужно жестко развести по слоям.

## 6. Целевой принцип: 3 уровня управления

### 6.1 Уровень A: Global Foundation

Это уже начато и это правильно.

Здесь живут:

1. базовые цвета;
2. общая типографика;
3. кнопочные state;
4. радиусы;
5. surface/shadow system;
6. section spacing presets.

Правило:

если блок можно адекватно оформить глобальной системой, новая одноразовая настройка в block schema не нужна.

### 6.2 Уровень B: Block Contract

Это контракт конкретного блока:

1. какой контент у него есть;
2. какие сущности присутствуют;
3. какие layout overrides он поддерживает;
4. какие data slots можно привязать.

### 6.3 Уровень C: Inspector Runtime

Это движок, который:

1. читает contract;
2. читает entity registry;
3. читает capability registry;
4. строит UI;
5. скрывает неактуальные секции;
6. синхронизирует canvas selection и preview.

Именно этот уровень делает систему взрослой.

## 7. Ключевая архитектурная ставка

### 7.1 Не “поля блока”, а “сущности блока”

Для нового блока первичным должен стать не список raw-полей, а список сущностей.

Минимальный канонический набор:

1. `eyebrow`
2. `title`
3. `subtitle`
4. `meta`
5. `body`
6. `primaryButton`
7. `secondaryButton`
8. `media`
9. `mediaSurface`
10. `itemSurface`
11. `itemTitle`
12. `itemText`
13. `itemBadge`
14. `icon`
15. `divider`
16. `items`

Для первой design-manifest волны дополнительно фиксируем:

1. `title` и `subtitle` входят в канонический набор каждого блока;
2. при этом они должны поддерживать show / hide без пустого места в layout;
3. icon/image-like значения используют штатные InstantCMS picker-механики.

Эти имена должны быть каноническими. Не `heading` тут, `heroTitle` там и `faq_question_title` в третьем месте.

### 7.2 Блок должен декларировать presence, а не UI

Новый блок должен говорить примерно следующее:

1. у меня есть `title`;
2. у меня есть `subtitle`;
3. у меня есть `meta`;
4. у меня есть `items[]`;
5. у item есть `itemTitle` и `itemText`;
6. у item есть `itemSurface`;
7. у меня нет `secondaryButton`;
8. у меня нет `media`.

После этого инспектор сам должен решить, какие группы показать.

## 8. Целевой Block Contract

Логическая форма блока должна быть такой:

```json
{
  "meta": {
    "contractVersion": 3,
    "blockType": "faq",
    "schemaVersion": 1,
    "label": "FAQ"
  },
  "content": {
    "eyebrow": "FAQ",
    "title": "Частые вопросы",
    "subtitle": "Короткое пояснение",
    "items": []
  },
  "design": {
    "section": {
      "theme": "inherit"
    },
    "entities": {
      "title": {},
      "subtitle": {},
      "itemSurface": {},
      "itemTitle": {},
      "itemText": {}
    }
  },
  "layout": {
    "desktop": {},
    "mobile": {}
  },
  "data": {
    "bindings": {},
    "fallbacks": {}
  },
  "entities": {
    "title": {
      "kind": "text",
      "styleSlot": "title"
    },
    "subtitle": {
      "kind": "text",
      "styleSlot": "subtitle"
    },
    "items": {
      "kind": "repeater",
      "itemEntities": {
        "itemTitle": {
          "kind": "text",
          "styleSlot": "itemTitle"
        },
        "itemText": {
          "kind": "text",
          "styleSlot": "itemText"
        },
        "itemSurface": {
          "kind": "surface",
          "styleSlot": "itemSurface"
        }
      }
    }
  },
  "runtime": {
    "renderMode": "ssr",
    "featureFlags": {
      "useResponsiveOverrides": true,
      "useAdapter": true
    }
  }
}
```

Важно:

1. физически это пока можно хранить в `props_json`;
2. логически это уже не просто props, а полный block contract;
3. старый flat-формат должен жить только как migration layer.

## 9. Целевой Inspector Runtime

### 9.1 Верхний уровень вкладок

Вкладки должны быть стабильны для любого блока:

1. Контент
2. Дизайн
3. Макет
4. Данные

### 9.2 Внутренние группы должны быть тоже стабильны

#### Контент

1. Тексты
2. Кнопки
3. Изображения
4. Список элементов
5. Иконки

#### Дизайн

1. Фон секции
2. Контейнер
3. Типографика
4. Surface / Карточки / Рамки
5. Кнопки
6. Эффекты и states

#### Макет

1. Width
2. Padding
3. Gap
4. Alignment
5. Visibility
6. Desktop / Mobile overrides

#### Данные

1. Source
2. Slot bindings
3. Repeater source
4. Empty state
5. Fallbacks

### 9.3 Главное правило показа

Секция видна не потому, что ее кто-то вручную добавил в schema, а потому что текущий block contract содержит нужную capability.

Примеры:

1. Если нет `subtitle`, группа subtitle typography скрыта.
2. Если нет `secondaryButton`, группа secondary button скрыта.
3. Если нет `media`, media settings скрыты.
4. Если нет `itemSurface`, card/surface section для repeaters скрыта.
5. Если блок не repeatable, section `items` не показывается вообще.

## 10. Что именно должен декларировать блок

Нужен не хаотичный список полей, а отдельный capability map.

Пример:

```json
{
  "capabilities": {
    "sectionBackground": true,
    "sectionContainer": true,
    "title": true,
    "subtitle": true,
    "body": false,
    "primaryButton": false,
    "secondaryButton": false,
    "media": false,
    "repeater": true,
    "itemSurface": true,
    "itemBadge": false,
    "responsiveTypography": true,
    "responsiveSpacing": true,
    "dataBinding": true
  }
}
```

Это может храниться:

1. либо в schema блока;
2. либо в отдельной нормализованной metadata-структуре, которую модель собирает из schema;
3. но не в виде случайной логики прямо в шаблоне редактора.

## 11. Как скрывать лишнее без костылей

Нужен visibility engine.

Он должен работать по трем правилам.

### 11.1 Presence rule

Если сущности нет в `entities`, UI не показывается.

### 11.2 Capability rule

Если block capability выключен, UI не показывается.

### 11.3 Context rule

Если сущность есть, но режим неактивен, UI частично скрывается.

Примеры:

1. data bindings скрыты, если блок пока manual-only.
2. mobile overrides скрыты, если у блока выключен responsive layer.
3. secondary button style section скрыта, если кнопка не включена в content.

## 12. Как получить ощущение Webflow

Самое важное: нужно перестать воспринимать инспектор как просто набор полей справа.

Нужен canvas bridge.

### 12.1 Все значимые DOM-узлы должны иметь entity marks

Например:

1. `data-nb-entity="title"`
2. `data-nb-entity="subtitle"`
3. `data-nb-entity="primaryButton"`
4. `data-nb-entity="itemTitle"`
5. `data-nb-entity="itemSurface"`

### 12.2 Клик по canvas должен выбирать сущность

Поведение:

1. пользователь кликает на заголовок;
2. редактор понимает, что это `title`;
3. справа открывается группа `Типографика → Title`;
4. если это repeater item, еще и активируется конкретный item index.

### 12.3 Hover outline и active outline

Для взрослого UX нужен минимум:

1. hover-outline элемента;
2. active-outline выбранной сущности;
3. breadcrumb вида `FAQ / Item 2 / Title`.

Именно это дает ощущение “контролирую элемент”, а не “ищу поле в длинной форме”.

## 13. Что не надо брать как основу

### 13.1 GrapesJS

Как основной foundation для NordicBlocks не рекомендую.

Почему:

1. он заточен под freeform page-builder, а не под SSR block library;
2. он тащит собственную модель HTML/CSS-документа;
3. он будет бороться с нашей схемой InstantCMS, SSR-рендером и canonical block contract;
4. появится риск нового takeover-слоя, от которого как раз хочется уйти.

Использовать его можно только как источник UX-идей, но не как ядро конструктора.

### 13.2 Большие React page-builder фреймворки

Тоже плохой fit.

Причины те же:

1. другой runtime;
2. другой state model;
3. высокая цена интеграции;
4. мало пользы для SSR + InstantCMS contract-driven подхода.

## 14. Что я рекомендую как стек

### 14.1 Рекомендация

Лучший путь: свой inspector engine + легкий, но сильный frontend shell.

Рекомендуемый стек:

1. Vue 3 как UI shell инспектора.
2. Pinia как store для block contract, selection state и dirty state.
3. SortableJS для reorder repeaters и nested item lists.
4. Floating UI для popovers, floating toolbars и контекстных меню.
5. Tiptap только для rich text слоя, когда дойдем до него.

### 14.2 Почему именно так

1. Vue легко встраивается в текущую PHP-админку как mounted app.
2. Не нужно тащить полноценный page-builder framework.
3. Можно сохранить SSR runtime и текущий backend на PHP.
4. Можно постепенно мигрировать инспектор без переписывания всего сразу.
5. Реестр панелей, сущностей и capability будет проще собрать как нормальную reactive app, чем как разросшийся template + jQuery-style JS.

### 14.3 Что не рекомендую

Не рекомендую пытаться довести этот уровень UX чистым шаблоном PHP + разрастающимся plain JS.

Для маленьких фич это терпимо.

Для взрослого инспектора с:

1. entity selection;
2. tab logic;
3. conditional visibility;
4. nested repeaters;
5. responsive overrides;
6. bindings;
7. future quick edit from canvas

это начнет слишком быстро ломаться и замедлять развитие.

## 15. Какая часть должна остаться на PHP

Фронтенд shell не должен забирать архитектурную правду у backend.

На PHP должны остаться:

1. нормализация block contract;
2. миграция legacy flat props в v3 contract;
3. серверная валидация и sanitize;
4. canonical registry загрузки block schema;
5. SSR render;
6. preview/live parity;
7. data adapter integration с InstantCMS.

На frontend должны уйти:

1. отрисовка инспектора;
2. selection state;
3. visibility logic;
4. repeater editing UX;
5. responsive tabs;
6. canvas bridge.

## 16. Предлагаемая структура реестров

Нужны три реестра.

### 16.1 Entity Registry

Описывает канонические сущности:

1. `title`
2. `subtitle`
3. `body`
4. `primaryButton`
5. `secondaryButton`
6. `media`
7. `itemSurface`
8. `itemTitle`
9. `itemText`
10. `itemBadge`

Для каждой сущности хранятся:

1. `kind`
2. `styleSlot`
3. допустимые control groups
4. bindable slot type

### 16.2 Inspector Panel Registry

Описывает панели и условия показа.

Пример:

1. panel `titleTypography`
2. tab `design`
3. requires entity `title`
4. requires capability `title`
5. renders standard controls for typography group

### 16.3 Capability Registry

Описывает крупные возможности блока:

1. section background
2. section container
3. title typography
4. subtitle typography
5. buttons
6. repeater
7. card surface
8. media
9. bindings
10. responsive overrides

## 17. Новый принцип schema

Schema блока должна перестать описывать весь инспектор вручную.

Она должна описывать:

1. content fields;
2. entities;
3. capabilities;
4. layout support;
5. data slot support.

То есть не так:

1. `title_font_size`
2. `title_color`
3. `title_letter_spacing`
4. `title_weight`
5. `subtitle_font_size`

а так:

1. есть сущность `title`;
2. entity registry знает, какие controls ей положены;
3. inspector runtime строит нужную панель автоматически.

## 18. Правило для новых блоков

С этого этапа новый блок должен считаться нормальным только если он проходит такой checklist:

1. контент вынесен в `content`;
2. layout вынесен в `layout.desktop/mobile`;
3. design не размазан по raw ключам;
4. declared canonical entities;
5. declared capabilities;
6. repeater не использует `item1/item2/item3`;
7. canvas markup размечен `data-nb-entity`;
8. инспектор показывает только релевантные секции.

## 19. Первый референс-набор сущностей

Для быстрой стабилизации предлагаю утвердить первый production набор:

1. `eyebrow`
2. `title`
3. `subtitle`
4. `body`
5. `primaryButton`
6. `secondaryButton`
7. `media`
8. `mediaSurface`
9. `itemSurface`
10. `itemTitle`
11. `itemText`
12. `itemIcon`

Этого уже хватит, чтобы сделать взрослыми:

1. hero;
2. hero classic;
3. features;
4. faq;
5. cta;
6. text section.

## 20. Первый референс-набор панелей

### Контент

1. Text content
2. Buttons content
3. Media content
4. Repeater items

### Дизайн

1. Section background
2. Container width and padding
3. Title typography
4. Subtitle typography
5. Body typography
6. Surface / card style
7. Button style

### Макет

1. Spacing
2. Alignment
3. Gap
4. Desktop/mobile

### Данные

1. Source
2. Slot bindings
3. Repeater bindings
4. Empty state

## 21. Этапы внедрения

### Этап 1. Формализовать contract и registry

Нужно сделать:

1. утвердить canonical entity names;
2. утвердить capability list;
3. утвердить inspector panel registry;
4. зафиксировать Block Contract v3 как основной путь.

Результат:

редактор перестает зависеть от случайных имен полей.

### Этап 2. Сделать Inspector Shell v2

Нужно сделать:

1. вынести инспектор в отдельный frontend shell;
2. реализовать вкладки `Контент / Дизайн / Макет / Данные`;
3. реализовать visibility engine;
4. подключить store для contract state.

Результат:

один UI на все блоки.

### Этап 3. Внедрить canvas bridge

Нужно сделать:

1. `data-nb-entity` в SSR markup;
2. hover/active outlines;
3. click-to-select;
4. entity breadcrumbs;
5. focus inspector section.

Результат:

ощущение управления конкретным элементом, а не формой справа.

### Этап 4. Перевести 3 эталонных блока

Рекомендую первыми перевести:

1. hero classic;
2. features;
3. faq.

Почему:

1. hero закрывает text + buttons + layout;
2. features закрывает cards + repeaters + item surface;
3. faq закрывает repeaters + item title/text + accordion surface.

### Этап 5. Поднять Data Layer v1

После стабилизации инспектора:

1. current content item;
2. content list;
3. slot bindings;
4. repeater bindings.

Результат:

не просто красивый блок, а взрослый InstantCMS-aware block system.

## 22. Приоритет на ближайшую итерацию

Если идти без распыления, я бы делал именно так:

1. зафиксировать registry сущностей и capability;
2. спроектировать JSON shape нового contract;
3. спроектировать visibility engine;
4. собрать Vue shell инспектора;
5. перевести hero + faq + features на новый слой;
6. только потом дожимать quick edit, bindings и richer controls.

## 23. Моя итоговая позиция

### 23.1 Что делать точно нужно

1. Уходить от raw field-per-block модели.
2. Вводить canonical entities.
3. Вводить capability-driven inspector.
4. Разделять `content / design / layout / data`.
5. Делать canvas bridge с выбором сущности.

### 23.2 Что не нужно делать

1. Не строить новый хаотичный takeover builder.
2. Не тащить тяжелый freeform page-builder framework как ядро.
3. Не плодить новые block-specific key names без канона.
4. Не расширять plain JS-шаблон до бесконечности.

### 23.3 Что рекомендую как взрослое решение

Взрослое решение для NordicBlocks выглядит так:

1. PHP остается источником contract, SSR и sanitize.
2. Inspector становится отдельным Vue 3 shell.
3. Все блоки переходят на canonical entities и capability registry.
4. Canvas получает entity-selection слой.
5. Новые блоки больше не проектируются через “какие поля добавить”, а через “какие сущности и слоты у блока есть”.

Именно это даст систему, где:

1. новые блоки подключаются быстрее;
2. инспектор остается единым;
3. пользователь реально управляет элементами;
4. live и preview не расходятся;
5. конструкция становится коммерчески взрослой, а не набором частных форм.

## 24. Следующий практический документ

После утверждения этого плана следующий полезный шаг — отдельный формальный контракт:

1. canonical entity registry;
2. inspector panel registry;
3. capability matrix;
4. migration rules: flat props -> Block Contract v3.

Это уже должен быть не обзорный, а почти implementation-ready документ.

Актуальный документ этого уровня:

`docs/nordicblocks/INSPECTOR-V2-IMPLEMENTATION-REGISTRY.md`