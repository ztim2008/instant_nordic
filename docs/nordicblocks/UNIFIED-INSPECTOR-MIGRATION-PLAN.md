# NordicBlocks Unified Inspector Migration Plan

Дата: 2026-04-17

## 1. Зачем нужен ещё один документ

Отдельный документ нужен не для повторения общей идеи unified inspector, а для фиксации практического перехода от текущей смешанной архитектуры к одной взрослой системе.

Сейчас в проекте уже одновременно существуют две реальности:

1. старая логика, где block editor местами всё ещё живёт как block-specific inspector;
2. новая логика, где уже появились registry, state builder и inspector shell v2;
3. документы, которые правильно задают направление, но ещё не сведены в один migration-план.

Из-за этого команда легко попадает в опасную серую зону:

1. кажется, что unified inspector уже есть;
2. но на практике новые блоки всё ещё могут тянуть в inspector свою частную логику;
3. а UX остаётся непредсказуемым от блока к блоку.

Этот документ фиксирует главное решение:

block-specific inspector не является целевой архитектурой NordicBlocks и должен считаться legacy-переходным слоем.

## 2. Главный вывод

Если NordicBlocks хочет UX уровня Figma или Webflow по ощущению управления, то у системы не может быть модели:

1. каждый блок рисует свой inspector;
2. каждый блок сам определяет свой layout полей;
3. каждая новая группа настроек появляется как частная логика под один block type.

Такая модель подходит только для раннего прототипа.

Для product-level builder-а правильная модель только одна:

1. есть один universal inspector shell;
2. есть один panel registry;
3. есть один набор canonical entities;
4. есть один набор control presets;
5. блок сообщает не UI inspector-а, а свой contract, entities и capabilities;
6. inspector сам собирает нужный интерфейс.

Именно это решение уже подтверждено текущими документами:

1. [docs/nordicblocks/UNIFIED-INSPECTOR-ARCHITECTURE.md](docs/nordicblocks/UNIFIED-INSPECTOR-ARCHITECTURE.md)
2. [docs/nordicblocks/INSPECTOR-V2-IMPLEMENTATION-REGISTRY.md](docs/nordicblocks/INSPECTOR-V2-IMPLEMENTATION-REGISTRY.md)
3. [docs/nordicblocks/BLOCK-DESIGN-MANIFEST-V1.md](docs/nordicblocks/BLOCK-DESIGN-MANIFEST-V1.md)

И это уже частично подтверждено кодом:

1. [system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php](system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php)
2. [system/controllers/nordicblocks/libs/InspectorStateBuilder.php](system/controllers/nordicblocks/libs/InspectorStateBuilder.php)
3. [system/controllers/nordicblocks/backend/actions/block_editor_state.php](system/controllers/nordicblocks/backend/actions/block_editor_state.php)

То есть проекту не нужно резко менять направление. Нужно довести до конца то направление, которое уже оказалось правильным.

## 3. Что именно сейчас неправильно

Проблема не в том, что у inspector-а пока мало полей.

Проблема в том, что исторически часть блоков думала о себе так:

1. у меня есть свой render;
2. у меня есть своя schema;
3. значит, у меня должен быть свой собственный inspector.

Это приводит к системным последствиям.

### 3.1 UX распадается

Пользователь не получает одного предсказуемого editor experience:

1. у одного блока настройки заголовка живут в одном месте;
2. у другого блока они называются иначе;
3. у третьего вообще отдельный шаблон редактора;
4. невозможно на уровне продукта обещать единый способ работы.

### 3.2 Архитектура не масштабируется

Каждый новый блок начинает тащить:

1. свой список полей;
2. свои исключения;
3. свои названия сущностей;
4. свою связку canvas и inspector.

Это убивает повторное использование.

### 3.3 Дизайн-система не может стать сильной

Если block inspector каждый раз уникален, то невозможно качественно построить:

1. один слой типографики;
2. один слой surfaces;
3. один слой spacing/layout;
4. одно поведение data bindings;
5. одну систему responsive overrides.

### 3.4 Команда всё время будет переписывать одно и то же

В block-specific модели каждый новый hero, faq, feature-list или promo-block будет заново решать уже решённые задачи:

1. как редактировать title;
2. как редактировать subtitle;
3. как показывать buttons;
4. как связывать items с repeater;
5. как выбирать active entity.

Это технический налог, который будет только расти.

## 4. Что уже есть хорошего и почему это важно

Текущее состояние не нужно описывать как провал. Правильнее описывать его как переходный этап.

В проекте уже есть фундамент, который подтверждает правильный вектор.

### 4.1 Уже есть registry-first каркас

Сейчас backend уже умеет собирать unified payload для нового editor flow:

1. registry tabs;
2. canonical entities;
3. capability matrix;
4. control presets;
5. panel registry;
6. resolved entities;
7. resolved capabilities;
8. ui state.

Это означает, что базовый язык будущего inspector engine уже существует.

### 4.2 Уже есть panel filtering

В текущем runtime уже есть правильный продуктовый принцип:

1. панель должна показываться не потому, что кто-то руками её вставил в шаблон;
2. панель должна показываться только если у блока есть соответствующая capability и сущность.

Это и есть взрослый путь.

### 4.3 Уже есть разделение вкладок

Вкладки `Контент / Дизайн / Макет / Данные` уже зафиксированы и это правильно.

Именно они должны остаться общими для всех блоков. Не нужно изобретать block-specific tab model.

### 4.4 Уже есть документы, которые были правы

Фактически проект уже сам себя поправил через документацию:

1. universal inspector задуман верно;
2. design manifest задуман верно;
3. canonical entities задуман верно;
4. canvas-to-inspector focus задуман верно.

Нужно перестать относиться к этим документам как к факультативным заметкам. Это уже каноническая архитектура.

## 5. Целевое правило ответственности

Чтобы система не расползалась обратно, ответственность между слоями должна быть жёстко разделена.

### 5.1 Что имеет право делать блок

Блок имеет право:

1. отдать нормализуемый contract;
2. отдать block type;
3. отдать content/data/design/layout значения;
4. объявить, какие сущности у него реально есть;
5. объявить, какие capabilities он поддерживает;
6. отдать label overrides для UX-терминов, если это действительно нужно.

Блок не имеет права:

1. описывать layout inspector-а;
2. создавать свой отдельный tab model;
3. создавать свой отдельный набор control groups;
4. диктовать shell-у, как должен выглядеть UI справа.

### 5.2 Что обязан делать backend

Backend обязан:

1. нормализовать contract;
2. резолвить canonical entities;
3. резолвить capabilities;
4. построить registry payload;
5. передать shell уже чистое состояние;
6. не тащить raw legacy schema напрямую в frontend shell.

### 5.3 Что обязан делать inspector shell

Inspector shell обязан:

1. рендерить вкладки;
2. рендерить панели по registry;
3. фильтровать панели по entities и capabilities;
4. переключать entity selection;
5. управлять breakpoint state;
6. управлять dirty-state и autosave hooks;
7. открывать нужный аккордеон при клике на canvas.

### 5.4 Что обязан делать canvas bridge

Canvas bridge обязан:

1. сообщать selected entity;
2. сообщать состояние preview ready;
3. сообщать metrics для layout sync;
4. не содержать внутри себя block-specific inspector logic.

## 6. Целевой архитектурный принцип

Главная формула должна быть зафиксирована жёстко:

не блок рисует inspector, а inspector интерпретирует block contract.

Практически это означает следующее.

### 6.1 Блок описывается через канонические сущности

Не `heading`, `heroTitle`, `section_heading`, `faq_title_text` и другие частные ключи, а единый набор:

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
12. `items`

Если блоку нужны UX-термины типа `Вопрос` и `Ответ`, это решается label override, а не созданием новой сущности.

### 6.2 Панели описываются через registry

Не существует `faq inspector panel`, `hero inspector panel` или `special light hero inspector layout` как архитектурной нормы.

Существуют только:

1. canonical panels;
2. control presets;
3. capability conditions;
4. entity scope.

### 6.3 Исключения допустимы только как extension-point

Иногда у блока действительно может быть особая панель. Это допустимо только если:

1. блок не может выразить сценарий через существующие presets;
2. новая панель оформляется как расширение общего registry;
3. новая панель не ломает общий shell;
4. новая панель не превращается в отдельный inspector для одного блока.

Правило:

special panel допустима, special inspector недопустим.

## 7. Что считаем legacy-слоем

Чтобы не спорить каждый раз заново, legacy нужно назвать прямо.

Legacy в рамках NordicBlocks:

1. шаблон, где inspector полностью собирается руками под один блок;
2. block editor, который читает только частную schema без normalizer/runtime слоя;
3. flat props без перехода в canonical contract;
4. private naming вместо canonical entities;
5. блок, который знает структуру правой панели лучше, чем inspector runtime.

Legacy разрешён только как временный мост, пока блок ещё не перенесён на unified inspector runtime.

## 8. Целевая модель данных

Unified inspector не может держаться на одних визуальных шаблонах. Ему нужен нормальный data model.

Минимальный набор слоёв должен быть таким:

1. `meta`
2. `content`
3. `design`
4. `layout`
5. `data`
6. `entities`

### 8.1 Content

Содержит смысловые значения:

1. тексты;
2. body;
3. CTA labels;
4. media references;
5. repeater items.

### 8.2 Design

Содержит локальные overrides для визуального слоя:

1. section background;
2. title typography;
3. subtitle typography;
4. item surface;
5. button style.

### 8.3 Layout

Содержит layout semantics, а не дизайн:

1. section spacing;
2. content width;
3. alignment;
4. min-height;
5. desktop/mobile overrides.

### 8.4 Data

Содержит bindings и fallback behavior:

1. single item source;
2. content list source;
3. field mapping;
4. empty behavior;
5. fallback content behavior.

### 8.5 Entities

Содержит описание присутствия сущностей:

1. какие сущности есть;
2. какого они уровня;
3. к какому style slot относятся;
4. какой data slot используют.

Именно этот слой должен стать основой inspector visibility.

## 9. Целевой UX inspector-а

Чтобы система ощущалась как один продукт, UX-правила должны быть общими.

### 9.1 Одна IA для всех блоков

Для всех block types сохраняются одни и те же вкладки:

1. Контент
2. Дизайн
3. Макет
4. Данные

### 9.2 Навигация через сущность, а не через список полей

Пользователь должен думать не так:

1. где поле размера заголовка;
2. где цвет подзаголовка;
3. где кнопка карточки.

А так:

1. я выбрал заголовок;
2. я выбрал подзаголовок;
3. я выбрал карточку;
4. я выбрал кнопку.

### 9.3 Аккордеон, а не простыня

Нужный UX для NordicBlocks:

1. сверху вкладки;
2. внутри вкладки аккордеоны по группам и сущностям;
3. canvas selection автоматически открывает нужную группу;
4. inspector не превращается в бесконечный scroll-form.

### 9.4 Visibility по capability, а не по if-else в шаблоне

Пользователь не должен видеть:

1. настройки secondary button, если secondary button нет;
2. настройки media, если media нет;
3. item panels, если блок не repeater;
4. binding panels, если блок не умеет data mode.

## 10. Migration strategy

Ниже фиксируется не просто “куда хотим прийти”, а реальный маршрут перехода.

### Фаза 0. Зафиксировать правило без отката назад

Нужно жёстко принять организационное решение:

1. новые блоки больше не получают свой уникальный inspector shell;
2. новые блоки подключаются только к universal inspector runtime;
3. любое отклонение допускается только как временный bridge с явной задачей на перенос.

Без этого решения система снова расползётся.

### Фаза 1. Довести canonical model до полного покрытия

Нужно добить базовые реестры:

1. canonical entity registry;
2. capability registry;
3. panel registry;
4. label override mechanism;
5. repeater-level selection model;
6. data binding presets.

Результат фазы:

1. inspector engine знает, как выразить большинство блоков без block-specific UI.

### Фаза 2. Добить один эталонный vertical slice

Нужен один reference-block, полностью живущий на unified inspector runtime.

Требования к vertical slice:

1. title;
2. subtitle;
3. buttons;
4. section background;
5. layout settings;
6. canvas selection;
7. autosave;
8. mobile/desktop;
9. при наличии данных ещё и data bindings.

Практически таким slice должен стать hero reference-block.

Не потому что hero особенный, а потому что он покрывает максимум типовых сценариев.

### Фаза 3. Ввести legacy adapter

Старые block-specific блоки не нужно переписывать одномоментно.

Нужен bridge-слой:

1. legacy schema -> contract normalizer;
2. legacy props -> canonical entities;
3. legacy block capabilities -> capability resolver;
4. legacy labels -> label override mapping.

Смысл этой фазы:

1. даже старый блок начинает кормить новый inspector runtime;
2. перенос можно делать постепенно;
3. код не разваливается по двум редакторским мирам.

### Фаза 4. Перенести hero family

После reference-block нужно переносить не случайные блоки, а семейство похожих блоков.

Правильный порядок:

1. hero block family;
2. text/media CTA blocks;
3. cards/features blocks;
4. faq/repeater blocks;
5. data-bound list blocks.

Это даст повторное использование и быстро покажет, где registry ещё слаб.

### Фаза 5. Добить repeater и data mode

Именно здесь архитектура обычно ломается. Поэтому эту фазу нужно считать обязательной, а не дополнительной.

Нужно закрыть:

1. item-level entity selection;
2. selectedRepeaterPath;
3. item panel visibility;
4. data source mode single/list;
5. field binding UI;
6. fallback semantics.

Если эта фаза не закрыта, inspector остаётся полуединым.

### Фаза 6. Запретить новые block-specific editor templates

Когда universal inspector реально покрывает основные блоки, нужно формально закрыть старый путь.

После этого новые block-specific editor templates можно заводить только по отдельному архитектурному исключению.

## 11. Что нельзя делать в новой архитектуре

Чтобы migration не размывался, фиксируем прямые запреты.

### 11.1 Нельзя давать блоку собственный UI-контур inspector-а

Если блоку снова разрешить рендерить свои отдельные формы справа, migration уже проигран.

### 11.2 Нельзя плодить новые сущности для каждого блока

Нельзя заменять canonical entities такими ключами:

1. `heroTitleBig`
2. `promoHeading`
3. `faqQuestionHeading`
4. `sectionLeadText`

Правильный путь:

1. canonical entity;
2. block label override;
3. при необходимости special preset.

### 11.3 Нельзя вшивать visibility rules в шаблон editor-а

Условия вида “если block type такой-то, показать этот кусок inspector-а” должны жить не в template, а в registry/capability layer.

### 11.4 Нельзя мешать foundation и local overrides

Если global design token и block-local override редактируются как одна и та же абстракция, пользователю станет непонятно, что он меняет.

### 11.5 Нельзя считать hero-specific prototype финальной моделью

Hero может быть pilot-носителем, но не должен стать новой формой vendor lock для inspector-а.

## 12. Что считаем success criteria

Migration можно считать успешным только если выполнены все признаки ниже.

### 12.1 Для продукта

1. пользователь видит один тип inspector-а для всех новых блоков;
2. клик по canvas открывает правильную сущность;
3. вкладки и логика навигации одинаковы;
4. нет ощущения, что каждый блок открывает новый мини-редактор.

### 12.2 Для архитектуры

1. новый блок может быть подключён без нового inspector template;
2. новый блок описывается через contract + entities + capabilities;
3. UI панелей собирается из registry;
4. legacy blocks можно переводить по одному без пересборки архитектуры.

### 12.3 Для команды

1. добавление нового блока перестаёт быть задачей “изобрести новый inspector”; 
2. дизайнер и разработчик говорят на одном наборе сущностей;
3. документация и код больше не спорят между собой.

## 13. Практический план следующего этапа

Чтобы не застрять в абстракции, фиксируем ближайший рабочий порядок.

### Шаг 1

Формально признать текущие документы канонической архитектурой unified inspector.

### Шаг 2

Довести registry/runtime слой до состояния, где hero-block полностью живёт на нём без block-specific правой панели.

### Шаг 3

Добавить legacy adapter для старых block contracts и старых props.

### Шаг 4

Перенести первую семью блоков на один inspector engine.

### Шаг 5

После подтверждения запретить создавать новые block-specific inspector templates по умолчанию.

## 14. Короткий вывод для команды

Ваше новое ощущение архитектуры правильное.

Да, исходная модель “каждый блок рисует свои настройки” была неверной как целевая архитектура.

Но важная поправка:

проект уже не находится в нулевой точке.

Он уже начал движение в правильную сторону:

1. документы это зафиксировали;
2. runtime частично это реализовал;
3. теперь нужен не новый поворот, а жёсткое завершение migration.

Итоговое правило должно звучать так:

NordicBlocks строится вокруг одного universal inspector runtime, а block-specific inspector считается legacy-мостом, который постепенно вымывается из системы.