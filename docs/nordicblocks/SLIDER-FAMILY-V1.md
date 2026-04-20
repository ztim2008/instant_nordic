# NordicBlocks — Slider Family V1

Дата: 2026-04-20

## 1. Зачем нужна slider family

После news и catalog families следующей продуктовой группой в NordicBlocks становится семейство слайдеров.

Эта линейка нужна, чтобы закрыть повторяющиеся InstantCMS-сценарии:

1. hero-слайдер на первом экране;
2. карусель карточек материалов или категорий;
3. витрина кейсов, продуктов или отзывов;
4. stories-like/mobile-first поток слайдов;
5. компактные контентные rail-блоки там, где обычный grid перегружает страницу.

Ключевое правило этой линейки:

1. slider не считается разовым визуальным эффектом;
2. slider family проектируется как отдельный reusable interaction language;
3. navigation, pagination, slide entities и data rules должны быть едины для нескольких block types.

## 1.1 Жёсткие product rules для всей slider family

Для всех трёх slider blocks сразу фиксируются обязательные правила первой волны:

1. каждый slider block рендерится на всю ширину блока, без узкого content-width режима по умолчанию;
2. viewport слайдера считается канонически `100%` ширины своей секции;
3. каждый slider block обязан поддерживать starter demo content;
4. каждый slider block обязан поддерживать данные InstantCMS через `content_list` path;
5. на мобильном swipe-листание является обязательным поведением, а не второстепенной опцией.

Уточнение:

1. full-width правило не запрещает внутренние content constraints внутри конкретного slide design;
2. но сама slider rail/viewport композиция не проектируется как boxed-component v1.

## 2. Family-first стандарт для slider line

Для slider family фиксируется тот же порядок, что и для остальных линеек:

1. сначала описывается мини-линейка из 3 родственных блоков;
2. затем выбирается только один стартовый block type;
3. до кода утверждается набор сущностей, вкладок и границ V1;
4. только потом начинается scaffold/implementation;
5. остальные два блока линии строятся поверх того же slider-runtime и того же navigation vocabulary.

## 3. Состав slider family

Базовая мини-линейка фиксируется так:

1. `Hero Slider`
2. `Cards Slider`
3. `Stories Slider`

Роли внутри линейки:

1. `Hero Slider` — крупный акцентный слайдер для первого экрана или section lead;
2. `Cards Slider` — универсальная карусель карточек для новостей, категорий, кейсов, отзывов;
3. `Stories Slider` — mobile-first линия компактных вертикальных или узких horizontal slides.

Для V1 первым кандидатом реализации рекомендуется `Cards Slider`, потому что:

1. он лучше всего повторно использует уже доказанный card/data contract;
2. он наиболее безопасен для shared shell rollout;
3. он даст reusable runtime для остальных двух slider blocks.

При этом family-level product scope фиксируется сразу для всей тройки:

1. `cards_slider` — первый implementation block;
2. `hero_slider` — второй block family после закрытия reusable mechanics;
3. `stories_slider` — третий block family, с самым сильным mobile accent.

## 4. Канонические сущности slider family

Для slider family фиксируется следующий общий словарь сущностей.

### 4.1 Section-level entities

1. `section` — внешняя секция блока, фон, paddings, theme;
2. `header` — верхняя зона с title/subtitle/actions;
3. `title` — заголовок секции;
4. `subtitle` — подзаголовок или вводный текст;
5. `primaryButton` — главная кнопка секции;
6. `secondaryButton` — вторичная кнопка секции.

### 4.2 Slider mechanics entities

1. `viewport` — видимая область слайдера;
2. `track` — движущаяся rail/лента слайдов;
3. `slide` — единица контента;
4. `navigation` — обёртка навигации;
5. `prevButton` — кнопка назад;
6. `nextButton` — кнопка вперёд;
7. `pagination` — пейджер/точки/фракция;
8. `progress` — progress bar или auto-progress индикатор.

### 4.3 Shared slide content entities

1. `slideSurface` — общая поверхность слайда;
2. `slideMedia` — изображение/видео/media zone;
3. `slideEyebrow` — малая метка, категория, надпись;
4. `slideTitle` — заголовок слайда;
5. `slideText` — описание/анонс;
6. `slideMeta` — дата, label, счётчики, secondary facts;
7. `slidePrimaryAction` — основной CTA внутри слайда;
8. `slideSecondaryAction` — вторичный CTA внутри слайда.

Важно:

1. это общий family vocabulary;
2. не каждый slider block обязан использовать все сущности;
3. но новые slider blocks не должны изобретать альтернативные названия для тех же поверхностей.

## 5. Первый блок к реализации

Первым блоком slider family рекомендуется делать:

1. пользовательское название: `Карточки-слайдер`;
2. рабочий технический slug: `cards_slider`;
3. продуктовый смысл: универсальный блок-карусель, который может работать и как ручной demo/showcase блок, и как data-driven rail для InstantCMS.

Почему не `Hero Slider` первым:

1. он сильнее завязан на сложный first-screen composition language;
2. он быстрее разрастается в уникальные case-by-case layouts;
3. для первой волны лучше сначала доказать reusable slider mechanics на карточечной модели.

## 6. Режимы данных для V1

Для всей slider family в V1 фиксируются два канонических режима:

1. `manual`
2. `content_list`

Это правило относится к:

1. `cards_slider`
2. `hero_slider`
3. `stories_slider`

### `manual`

Используется когда:

1. нужно быстро собрать витрину руками;
2. нужен demo-предпросмотр в каталоге блоков;
3. нужен curated slider без связи с live данными.

### `content_list`

Используется когда:

1. slider должен тянуть записи выбранного ctype;
2. нужна карусель последних или отфильтрованных материалов;
3. один и тот же runtime должен работать для `articles`, `news`, `cases` и совместимых типов.

V1-правило:

1. внутри одного блока не смешиваем manual и dynamic slides одновременно.
2. каждый новый slider block стартует не с пустого состояния, а с готовым demo набором слайдов.

## 7. Вкладка Контент

Во вкладке `Контент` для slider family V1 живут:

1. секционный текст;
2. visibility toggles;
3. repeater ручных слайдов;
4. базовые interaction labels, если они user-visible.

### 7.1 Поля секции

1. `Заголовок секции`
2. `Описание секции`
3. `Текст главной кнопки`
4. `URL главной кнопки`
5. `Текст вторичной кнопки`
6. `URL вторичной кнопки`

### 7.2 Visibility toggles

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

### 7.3 Manual repeater slides

Поля одного ручного слайда V1:

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

1. это content-repeater, а не per-slide design studio;
2. все слайды используют общий visual style блока;
3. default-state должен содержать готовые demo slides.

## 8. Вкладка Дизайн

Во вкладке `Дизайн` для slider family V1 фиксируются только управляемые параметры, которые реально меняют язык блока.

### 8.1 Section and foundation

1. `Тема блока`
2. `Фон секции`
3. `Ширина контента` как свободное boxed-поле в первой волне не даётся;
4. `Отступ сверху` desktop/mobile
5. `Отступ снизу` desktop/mobile

Дополнительное правило:

1. family-level width policy для slider blocks — только full-width section/viewport;
2. если отдельному block type позже понадобится boxed variation, это должно быть отдельным осознанным V2/V3 решением, а не дефолтом V1.

### 8.2 Slide appearance

1. `Вид поверхности слайда`
2. `Скругление слайда`
3. `Тень слайда`
4. `Формат media`
5. `Скругление media`
6. `Gap между slide content zones`

### 8.3 Typography

1. `Заголовок секции`
2. `Описание секции`
3. `Заголовок слайда`
4. `Текст слайда`
5. `Meta`
6. `CTA внутри слайда`
7. `Pagination`, если она текстовая или fraction-based.

### 8.4 Controls design

1. `Стиль navigation кнопок`
2. `Размер navigation кнопок`
3. `Позиция navigation`
4. `Стиль pagination`
5. `Стиль progress bar`

## 9. Вкладка Макет

Во вкладке `Макет` для V1 фиксируем только базовую механику карусели.

### 9.1 Layout fields

1. `Количество слайдов в кадре` desktop/mobile
2. `Gap между слайдами` desktop/mobile
3. `Выравнивание header`
4. `Ширина viewport` в первой волне считается фиксированной как `100%` и не выносится в свободный layout control;
5. `Минимальная высота слайда`, если тип блока это допускает

### 9.2 Motion and interaction fields

1. `Autoplay` on/off
2. `Интервал autoplay`
3. `Пауза при hover`
4. `Loop` on/off
5. `Desktop drag` on/off
6. `Keyboard navigation` on/off
7. `Snap mode` or `free scroll` — только если это не усложняет V1 сверх меры.

V1-ограничение:

1. если `snap mode` заметно усложняет runtime и preview, в первой волне оставляем только controlled snap slider.
2. mobile swipe не является отключаемой luxury-опцией и входит в baseline всех slider blocks.

## 10. Вкладка Данные

Для `cards_slider` в режиме `content_list` фиксируются такие поля:

1. `Источник данных`
2. `Тип контента`
3. `Категория`
4. `Лимит записей`
5. `Сортировка`
6. `Только с изображением`
7. `Смещение / offset`, если оно уже поддерживается shared data path.

Для V1 сознательно не входят:

1. сложные смешанные data sources;
2. внутриблочная ручная пересортировка dynamic slides;
3. комбинированный запрос из нескольких ctype одновременно.

## 11. Runtime-поведение slider family V1

Для первой волны фиксируются правила runtime:

1. backend canvas и public runtime используют один и тот же SSR markup language;
2. navigation и pagination обязаны быть управляемыми через canonical entities;
3. interaction JS не должен владеть контентом, а только переключением slides и состоянием active index;
4. при выключенной JS деградация должна оставаться читабельной: слайды не пропадают полностью и не ломают layout.
5. mobile swipe должен быть частью baseline runtime behavior для всех slider blocks;
6. full-width viewport должен быть baseline layout behavior для всех slider blocks.

## 12. Что сознательно не входит в slider family V1

Чтобы первая волна не превратилась в бесконечный scope creep, вне V1 оставляем:

1. per-slide design overrides;
2. произвольные переходы уровня cinematic animation studio;
3. сложный video timeline control внутри общего slider runtime;
4. nested sliders;
5. редактирование порядка dynamic slides drag-and-drop поверх remote data source;
6. комбинированный hero + stories + testimonials monster-block в одном type.

## 13. Рекомендуемый rollout order

Для slider family фиксируется такой порядок:

1. утвердить этот family spec;
2. отдельно зафиксировать rollout-map для `cards_slider`;
3. отдельно зафиксировать implementation-grade spec для `cards_slider`;
4. отдельно зафиксировать shared slider vocabulary для inspector до старта кода;
5. пройти `checkpoint -> scaffold -> validator -> implementation -> live smoke` для `cards_slider`;
6. только после этого открывать `hero_slider`;
7. `stories_slider` оставлять третьим, так как он likely потребует самые сильные mobile-specific исключения.

## 14. Критерий успеха первой slider волны

Slider family считается успешно открытой, когда:

1. `cards_slider` проходит live editor smoke;
2. `cards_slider` проходит public runtime smoke;
3. navigation/pagination/slide entities честно видны в shared inspector;
4. typography и layout не расходятся между saved contract и preview/public runtime;
5. следующий slider block можно проектировать поверх того же slider vocabulary, а не заново с нуля.

## Scaffold Registry
<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_START -->
Этот раздел обновляется автоматически scaffold apply pipeline и показывает текущие scaffold-managed block types этой family.

| Slug | Title | Status | Checkpoint | Live smoke |
| --- | --- | --- | --- | --- |
| cards_slider | Карточки-слайдер | in_progress | snapshot/20260420-095924 | no |
<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_END -->
