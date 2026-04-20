# NordicBlocks — Cards Slider V1

Дата: 2026-04-20

## 1. Статус документа

Этот документ фиксирует implementation-grade спецификацию для первого block type slider family.

Его роль:

1. перевести family-level решение из [docs/nordicblocks/SLIDER-FAMILY-V1.md](docs/nordicblocks/SLIDER-FAMILY-V1.md) в спецификацию уровня первого кодового прохода;
2. зафиксировать границы v1 до scaffold;
3. дать понятный порядок внедрения по слоям `block -> contract -> editor -> runtime -> smoke`.

Каноническая связка для этого блока:

1. [docs/nordicblocks/SLIDER-FAMILY-V1.md](docs/nordicblocks/SLIDER-FAMILY-V1.md)
2. [docs/nordicblocks/NORDICBLOCKS-V2-ROADMAP.md](docs/nordicblocks/NORDICBLOCKS-V2-ROADMAP.md)
3. этот документ.

## 2. Идентичность блока

Первый slider block фиксируется так:

1. пользовательское название: `Карточки-слайдер`;
2. технический slug: `cards_slider`;
3. product-role: универсальная full-width карусель карточек для новостей, категорий, кейсов, отзывов и showcase-сценариев;
4. архитектурная роль: системообразующий block type для всей slider family.

`cards_slider` обязан стать базой для:

1. shared slider track/navigation mechanics;
2. mobile swipe baseline;
3. card-slide contract;
4. demo/manual и `content_list` dual-mode path;
5. следующих производных `hero_slider` и `stories_slider`.

## 3. Главная продуктовая задача

`cards_slider` нужен не как декоративная анимация, а как reusable content rail.

Его задача:

1. давать полновесную full-width секцию со слайдящейся лентой карточек;
2. одинаково работать с demo content и с данными InstantCMS;
3. поддерживать desktop navigation и обязательный mobile swipe;
4. оставаться управляемым через shared inspector, без block-private editor shell;
5. быть достаточно универсальным, чтобы потом стать общей базой для других slider block types.

## 4. Жёсткие правила V1

### 4.1 Product rules

1. блок всегда проектируется как full-width section;
2. viewport блока всегда `100%` ширины секции;
3. starter demo content обязателен;
4. `content_list` режим обязателен;
5. mobile swipe обязателен;
6. внутри одного блока не смешиваются manual и dynamic slides.

### 4.2 Architectural rules

1. backend canvas и public runtime используют один и тот же SSR markup language;
2. block type живёт как отдельная директория в `system/.../blocks/cards_slider` и в package mirror;
3. data path остаётся в общепринятом NordicBlocks pipeline, без прямых SQL вызовов из `render.php`;
4. shared inspector vocabulary должен закрывать slider entities до начала implementation;
5. runtime CSS не должен прятать contract-driven typography/layout за hardcoded overrides.

### 4.3 UX rules

1. на desktop блок должен ощущаться как крупная section rail, а не как маленький boxed carousel widget;
2. на mobile блок должен честно листаться свайпом;
3. navigation/pagination не должны быть purely decorative, а должны отражать реальное состояние active slide group;
4. при выключенной JS блок обязан оставаться читаемым и не ломать страницу.

## 5. Что входит в V1

`cards_slider` v1 обязан уметь:

1. выводить full-width section header;
2. выводить slider viewport и track;
3. поддерживать manual slides через repeater;
4. поддерживать `content_list` source;
5. поддерживать desktop/mobile число карточек в кадре;
6. поддерживать gap между карточками desktop/mobile;
7. поддерживать navigation кнопки;
8. поддерживать pagination;
9. поддерживать autoplay и loop в controlled виде;
10. поддерживать обязательный mobile swipe;
11. поддерживать starter demo content с локальными demo images;
12. поддерживать карточечные CTA и meta-поля;
13. одинаково работать в preview и public runtime.

## 6. Что сознательно не входит в V1

Чтобы первый slider block не расползся, вне scope оставляем:

1. nested sliders;
2. video-first timeline slider;
3. slide-specific design overrides;
4. сложную 3D/cinematic animation model;
5. remote pagination;
6. смешанные ручные и dynamic slides;
7. отдельный stories-like vertical UI внутри этого block type.

## 7. Композиция блока

Каноническая структура `cards_slider` такая:

1. `section`;
2. `header`;
3. `slider viewport`;
4. `slider track`;
5. `slides`;
6. `navigation`;
7. `pagination`;
8. `progress` optional;
9. optional section CTA.

## 8. Сущности блока

Для inspector и runtime фиксируем такие canonical entities:

1. `section`
2. `header`
3. `title`
4. `subtitle`
5. `primaryButton`
6. `secondaryButton`
7. `viewport`
8. `track`
9. `slide`
10. `slideSurface`
11. `slideMedia`
12. `slideEyebrow`
13. `slideTitle`
14. `slideText`
15. `slideMeta`
16. `slidePrimaryAction`
17. `slideSecondaryAction`
18. `navigation`
19. `prevButton`
20. `nextButton`
21. `pagination`
22. `progress`

## 9. Contract модели слайда

Каждый slide v1 должен поддерживать единый payload shape.

### 9.1 Поля slide contract

1. `eyebrow`
2. `title`
3. `text`
4. `primary_cta_label`
5. `primary_cta_url`
6. `secondary_cta_label`
7. `secondary_cta_url`
8. `image`
9. `image_alt`
10. `date`
11. `meta_label`
12. `record_url`

### 9.2 Правила нормализации

1. все строки безопасны для SSR;
2. optional поля gracefully деградируют при отсутствии;
3. изображения нормализуются к предсказуемому media shape;
4. CTA поля нормализуются отдельно, а не прячутся внутрь заголовка.

## 10. Вкладка Контент

Во вкладке `Контент` в V1 живут:

1. секционный заголовок;
2. секционное описание;
3. главная и вторичная кнопки секции;
4. visibility toggles;
5. repeater ручных slides.

### 10.1 Поля секции

1. `Заголовок секции`
2. `Описание секции`
3. `Текст главной кнопки`
4. `URL главной кнопки`
5. `Текст вторичной кнопки`
6. `URL вторичной кнопки`

### 10.2 Visibility toggles

1. `Показывать описание`
2. `Показывать главную кнопку`
3. `Показывать вторичную кнопку`
4. `Показывать navigation`
5. `Показывать pagination`
6. `Показывать progress`
7. `Показывать media`
8. `Показывать eyebrow`
9. `Показывать текст`
10. `Показывать meta`
11. `Показывать CTA внутри слайда`

### 10.3 Repeater slides

Поля одного ручного slide:

1. `Eyebrow`
2. `Заголовок`
3. `Текст`
4. `Основной CTA текст`
5. `Основной CTA URL`
6. `Вторичный CTA текст`
7. `Вторичный CTA URL`
8. `Изображение`
9. `Alt-текст`
10. `Дата`
11. `Meta label`

Правила:

1. starter demo slides обязательны;
2. per-slide design overrides не входят;
3. все slides используют общий design language блока.

## 11. Вкладка Дизайн

Во вкладке `Дизайн` для `cards_slider` фиксируем:

1. `Тема блока`
2. `Фон секции`
3. `Отступ сверху` desktop/mobile
4. `Отступ снизу` desktop/mobile
5. `Стиль поверхности слайда`
6. `Скругление слайда`
7. `Тень слайда`
8. `Формат media`
9. `Скругление media`
10. `Заголовок секции` typography
11. `Описание секции` typography
12. `Заголовок слайда` typography
13. `Текст слайда` typography
14. `Meta` typography
15. `CTA внутри слайда` typography
16. `Навигация` design
17. `Pagination` design
18. `Progress` design

Важно:

1. поля свободной boxed-width настройки здесь не даются;
2. full-width policy считается канонической и не настраивается в V1.

## 12. Вкладка Макет

Во вкладке `Макет` для `cards_slider` фиксируем:

1. `Количество карточек в кадре` desktop/mobile
2. `Gap между карточками` desktop/mobile
3. `Выравнивание header`
4. `Минимальная высота слайда`
5. `Autoplay`
6. `Интервал autoplay`
7. `Пауза при hover`
8. `Loop`
9. `Desktop drag`
10. `Keyboard navigation`

Правила:

1. mobile swipe входит в baseline runtime и не выносится как отключаемая опция первой волны;
2. viewport width не выносится в control и остаётся `100%`;
3. if needed later, free-scroll mode обсуждается отдельно и не является частью обязательного V1.

## 13. Вкладка Данные

В режиме `content_list` фиксируются такие поля:

1. `Источник данных`
2. `Тип контента`
3. `Категория`
4. `Лимит записей`
5. `Сортировка`
6. `Только с изображением`

Вне scope первой волны:

1. смешанные data sources;
2. remote pagination;
3. drag-and-drop reorder поверх remote data;
4. multiple ctype query в одном block type.

## 14. Demo content и media

Для `manual` режима V1 фиксируется:

1. блок стартует с заполненными demo slides;
2. demo изображения берутся из локального набора NordicBlocks через `/upload/nordicblocks/demo/...` path;
3. demo content должен выглядеть правдоподобно как showcase/новости/кейсы, а не как lorem ipsum stub.

## 15. Runtime-поведение

В runtime `cards_slider` обязан:

1. рендериться как full-width section;
2. иметь честные DOM markers для slider entities;
3. поддерживать desktop navigation;
4. поддерживать mobile swipe;
5. не ломать layout без JS;
6. одинаково вести себя в backend canvas и public runtime.

## 16. Критерий готовности V1

`cards_slider` считается готовым для первой волны, когда одновременно выполняются условия:

1. block scaffold создан и проходит validator;
2. editor shell видит canonical slider entities без private routing;
3. block имеет demo content и `content_list` режим;
4. full-width layout реально подтверждён на preview/public;
5. mobile swipe подтверждён в live behavior;
6. editor smoke и public smoke проходят без runtime drift.