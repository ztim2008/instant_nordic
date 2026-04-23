# NordicBlocks V2.0 Roadmap

Дата: 2026-04-20

## 1. Зачем нужен отдельный план V2

Первая волна NordicBlocks доказала, что shared inspector, manifest-first блоки и contract-first runtime в InstantCMS жизнеспособны.

Но V1 также показал и реальные ограничения текущего слоя:

1. часть архитектуры всё ещё несёт legacy-совместимость, а не только чистый contract-first контур;
2. preview во многих сценариях зависит от autosave и reload iframe, а не от прямого state loop;
3. shared shell уже даёт сильную унификацию, но пока ещё держит block-kind исключения руками;
4. runtime CSS остаётся уязвимым к каскадным конфликтам, если block-level contract и shared styles расходятся;
5. family-first подход сработал, но нуждается в более жёстком продуктово-архитектурном стандарте перед следующими линейками.

V2 нужен не как косметический релиз, а как этап, который переводит NordicBlocks из рабочей эволюционной системы в устойчивую платформу для следующих групп блоков.

## 2. Цели версии 2.0

В V2 фиксируются такие цели:

1. убрать лишние промежуточные compatibility-мосты там, где они больше не нужны;
2. сделать manifest/contract единственным каноническим слоем для новых block families;
3. довести shared visual inspector до состояния, где новые блоки подключаются декларативно, а не через рост ручных веток;
4. выровнять preview, editor и public runtime вокруг одного предсказуемого rendering path;
5. перевести следующие линейки блоков на единый family-standard: `spec -> scaffold -> validator -> live smoke`;
6. подготовить платформу к более сложным композициям, включая slider family.

## 3. Что считаем главным наследием V1

В V2 не ломаем, а сохраняем следующие правильные решения V1:

1. `contract-first` хранение блока;
2. manifest-first декларацию сущностей, панелей и capabilities;
3. shared editor shell вместо отдельного inspector под каждый block type;
4. server-side render для public/runtime и backend canvas;
5. family-first rollout вместо случайного набора несвязанных блоков;
6. scaffold + validator как стандартный путь появления новых managed block types.

## 4. Что V2 должен исправить системно

### 4.1 Contract и persistence

Для новых линеек V2 вводит правило:

1. новый block type должен жить в одном canonical contract;
2. flat props разрешены только как временный bridge для legacy block types;
3. новые block families не должны проектироваться вокруг denormalize-обратного моста как основного механизма работы редактора;
4. editor state, saved state и runtime state должны читать один и тот же canonical document.

### 4.2 Shared inspector

V2 должен уменьшить ручную block-specific маршрутизацию в shell.

Цель:

1. family profile, content controls, design controls и layout controls должны выводиться из registry/manifest vocabulary;
2. ручные `if (blockType === ...)` допускаются только как временный compatibility слой для уже выпущенных V1 blocks;
3. новая family не должна требовать отдельного private editor shell.

### 4.3 Preview engine

Текущая модель autosave -> reload iframe остаётся допустимой в V1/V1.5, но для V2 это уже не конечное состояние.

Желаемый target:

1. быстрый preview refresh без тяжёлого reload всего редакторского контура;
2. ясное разделение draft-preview и published runtime;
3. transport canvas state должен быть частью платформы, а не побочным поведением конкретного shell.

### 4.4 Runtime CSS и design layer

V2 вводит жёсткое правило:

1. если visual параметр задаётся в contract, shared CSS обязан читать этот параметр через instance-scoped variables;
2. block-scoped hardcoded typography допускается только там, где contract-поля для этого не существуют по спецификации;
3. template copies, runtime assets и package mirror должны синхронизироваться как единая система, а не как побочный ручной шаг.

## 5. Технические принципы V2

Для NordicBlocks V2 фиксируются следующие принципы.

### 5.1 Single Source of Truth

1. у блока есть один canonical contract;
2. manifest описывает структуру этого контракта и допустимый UI vocabulary;
3. renderer, inspector и validator работают от одного смыслового словаря.

### 5.2 Family-first before block-first

1. сначала описывается группа из 3 родственных блоков;
2. затем выбирается только один первый блок реализации;
3. общие сущности, design language и data contract проектируются на уровне семьи, а не разово на уровне одного блока.

### 5.3 Shared-first before custom

1. новый block type сначала должен попытаться уложиться в shared shell;
2. private editor flow разрешён только если shared architecture доказуемо не закрывает сценарий;
3. любое block-private исключение должно быть зафиксировано в docs как осознанный долг, а не стихийный патч.

### 5.4 Runtime parity

1. backend canvas и public runtime должны использовать один и тот же SSR язык блока;
2. block author не должен поддерживать две почти одинаковые render-ветки;
3. различия editor/public разрешены только для service markers и editor-only helpers.

## 6. Скоуп V2 по слоям

### 6.1 Editor Platform

В V2 должны войти:

1. дальнейшая декларативная сборка inspector controls из manifest/registry;
2. нормализованный canvas state exchange;
3. единая vocabulary-система entity labels, tabs и selection hints;
4. подготовка к более насыщенным families, включая slider family и расширенные catalog/editorial patterns.

### 6.2 Contract Platform

В V2 должны войти:

1. версия контракта на уровне новых families;
2. более строгая validator-проверка contract drift и entity drift;
3. отказ от тихой потери неизвестных сущностей и свойств;
4. диагностический контур для contract/debug state в editor.

### 6.3 Runtime Styling Platform

В V2 должны войти:

1. строгая привязка visual controls к CSS vars;
2. audit shared CSS по всем families на hardcoded overrides;
3. более явная система block-surface, media, controls, pagination и navigation helpers;
4. подготовка foundation под motion/navigation patterns для slider blocks.

### 6.4 Product Families

Для V2 приоритетными families становятся:

1. news/editorial second wave;
2. slider family;
3. дальнейшее развитие catalog family;
4. выравнивание foundation между hero, news, catalog и upcoming slider blocks.

## 7. Slider Family как первая новая V2-линейка

Первая явно новая family после V1 news/catalog wave — это slider family.

Причины:

1. слайдеры часто нужны в InstantCMS-проектах как hero, showcase, testimonials, stories, category rails;
2. у slider-блоков есть повторяющийся shared vocabulary: track, slide, navigation, pagination, autoplay, viewport;
3. они достаточно близки между собой, чтобы оправдать family-first подход;
4. они достаточно отличаются от обычных feed/grid блоков, чтобы стать хорошей проверкой зрелости V2 architecture.

Подробный продуктовый spec для этой новой линейки вынесен в отдельный документ [docs/nordicblocks/SLIDER-FAMILY-V1.md](docs/nordicblocks/SLIDER-FAMILY-V1.md).

## 8. Фазы V2.0

### Phase 1. Platform hardening

1. сократить block-specific routing в shared shell;
2. выровнять contract-first round-trip для managed families;
3. провести CSS cascade audit для уже выпущенных V1 families;
4. стабилизировать canvas/entity/selection loop.

### Phase 2. Manifest-first maturity

1. расширить vocabulary shared entities и controls;
2. сделать validator строже к manifest drift и render drift;
3. подготовить reusable family profiles для news, catalog и slider.

### Phase 3. Slider family rollout

1. зафиксировать slider family spec;
2. выбрать один стартовый slider block;
3. пройти путь `spec -> scaffold -> validator -> implementation -> live smoke`;
4. только после этого открывать следующие 2 slider-производных блока.

### Phase 4. Preview/runtime refinement

1. снизить зависимость preview от полного reload;
2. укрепить draft/published разделение;
3. закрыть runtime parity gaps между editor canvas и public page.

## 9. Что сознательно не входит в V2.0

Чтобы V2 не расползся в бесконечную перестройку, вне scope оставляем:

1. полный переписывание всех legacy V1 blocks на новый contract в одном заходе;
2. новый page-builder поверх NordicBlocks;
3. визуальный no-code layout composer свободной формы;
4. массовую миграцию старых пользовательских данных без отдельного migration plan;
5. новый frontend framework вместо текущего SSR InstantCMS path.

## 10. Критерии готовности V2

NordicBlocks V2 считается состоявшимся, когда одновременно выполняются условия:

1. новая family может стартовать без private inspector shell;
2. contract является каноническим документом для новых blocks;
3. preview и public runtime показывают один и тот же visual result без drift по typography/layout;
4. shared validator ловит entity/manifest/runtime drift до live smoke;
5. slider family проходит полный rollout как доказательство зрелости платформы.

## 11. Ближайшие практические шаги

На ближайший следующий этап фиксируем такой порядок:

1. считать этот roadmap базовым V2-документом;
2. считать slider family первой осознанно новой продуктовой линейкой V2;
3. считать для slider family уже принятыми product rules: full-width `100%`, demo content + InstantCMS data path, обязательный mobile swipe;
4. не начинать код по slider blocks, пока не утверждён family spec, rollout-map первого блока и shared slider vocabulary;
5. первым truthful implementation block type считать `cards_slider`;
6. после утверждения первого slider block пройти под него стандартный path: `rollout-map -> spec -> checkpoint -> scaffold -> validator -> implementation -> smoke`.