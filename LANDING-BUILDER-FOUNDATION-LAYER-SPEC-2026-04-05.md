# Техспека: Foundation Layer Нордик

## Зачем этот документ

Это стартовый документ взрослой фазы Нордик.

Он фиксирует не очередной UX-срез bridge-canvas, а фундамент, без которого новый компонент `nordicbuilder` нельзя строить стабильно.

Foundation layer нужен, чтобы:

1. перестать проектировать editor и runtime от случайных payload-ов;
2. связать canvas, preview и live frontend одним contract-first подходом;
3. зафиксировать границу между текущим `landingbuilder` как bridge-слоем и будущим builder core.

## На что опирается foundation layer

1. [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
2. [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
3. [LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md](LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md)
4. `instantcms-mcp-main/src/data/schemas.ts`

Из `instantcms-mcp-main` здесь берем не конкретный продуктовый UX, а рабочую дисциплину:

1. schema-first стиль описания структуры;
2. явные required fields и enum там, где область конечна;
3. разделение contracts, tooling knowledge и runtime responsibility;
4. ясную addon/package structure для компонента `nordicbuilder`.

## Что такое foundation layer в Нордик

Foundation layer это набор договоренностей, которые становятся источником истины раньше UI и раньше локальных PHP-реализаций.

Он состоит из шести опор:

1. canonical JSON schema contract;
2. unified runtime contract;
3. design token model;
4. component library core;
5. `nordicbuilder` boundary и package discipline;
6. bridge strategy для текущего кода.

## 1. Канонический набор contracts

Foundation layer должен держать как минимум пять независимых, но связанных contracts.

### 1.1. `page document contract`

Описывает структуру редактируемой страницы.

Он должен хранить:

1. page meta;
2. section tree;
3. block instances;
4. element props;
5. page-level preset и overrides;
6. runtime hints;
7. adapter participation context.

### 1.2. `runtime payload contract`

Описывает то, что одновременно понимают:

1. visual workspace;
2. preview runtime;
3. live frontend runtime.

Этот contract не должен повторять editor state один в один.

Он должен содержать только то, что реально нужно для render pipeline.

### 1.3. `global defaults contract`

Описывает site-wide defaults для слоя `design system / global defaults`.

Это источник для secondary UI `Глобальные стили`, а не сама форма экрана.

### 1.4. `component definition contract`

Описывает секции, блоки и элементы как first-class nodes component library.

Внутри него должны жить:

1. stable key;
2. kind;
3. slots;
4. props schema;
5. default content;
6. allowed children;
7. token exposure;
8. adapter compatibility.

### 1.5. `adapter binding contract`

Описывает, как InstantCMS route, widget или content context подключается к builder runtime.

Он не должен быть спрятан внутри page JSON как неструктурированный blob.

## 2. Канонические сущности foundation layer

Начиная с этого этапа, в продукте считаем first-class сущностями:

1. `PageDocument`;
2. `SectionNode`;
3. `BlockNode`;
4. `ElementNode`;
5. `PresetDocument`;
6. `GlobalDefaultsDocument`;
7. `ComponentDefinition`;
8. `AdapterBinding`.

Если новая фича не раскладывается хотя бы в одну из этих сущностей, значит она пока не дотянута до foundation discipline.

## 3. Порядок наследования токенов

Foundation layer обязан использовать уже зафиксированный канонический порядок:

1. `core base tokens`;
2. `nordic base preset`;
3. `site design preset`;
4. `shell defaults`;
5. `page preset`;
6. `page overrides`;
7. `section overrides`;
8. `block overrides`.

Нельзя перепрыгивать через этот порядок в editor UI или runtime shortcuts.

## 4. Граница `nordicbuilder`

На этапе foundation имя нового компонента уже зафиксировано: `nordicbuilder`.

Компонент `nordicbuilder` должен владеть:

1. contracts;
2. component library registry;
3. workspace orchestration;
4. editor-to-runtime serialization;
5. adapter registry API.

Он не должен владеть напрямую:

1. всем frontend template `nordic`;
2. core InstantCMS internals;
3. случайной логикой старых bridge-form screens.

## 5. Bridge strategy для текущего репозитория

Текущий код не выбрасываем, но меняем его статус.

### 5.1. Что остается в bridge-слое `landingbuilder`

1. текущий canvas как переходный proving ground;
2. preview/runtime loop;
3. packaging mirror `packages/landingbuilder/package/`;
4. временная сериализация и runtime adapter glue.

### 5.2. Что остается в `nordic`

1. runtime template layer;
2. shell rendering;
3. token application;
4. frontend composition surface.

### 5.3. Что нельзя делать дальше

1. считать текущий `landingbuilder` окончательным builder core;
2. продолжать строить структуру данных только от текущих PHP forms;
3. тащить новые product decisions в `system/core` без крайней необходимости.

## 6. Практический первый scope foundation layer

Первый взрослый scope этапа ограничиваем пятью deliverables.

### 6.1. Contract pack

Нужно собрать связанный набор contracts для:

1. page document;
2. runtime payload;
3. global defaults;
4. component definition;
5. adapter binding.

### 6.2. Token pack

Нужно зафиксировать минимальные token domains:

1. color;
2. typography;
3. spacing;
4. radius;
5. shadow;
6. container;
7. interactive component defaults.

### 6.3. Component library core

Для старта достаточно ограниченного набора:

1. hero;
2. text content;
3. buttons/cta;
4. cards/listing;
5. media;
6. system widget wrapper.

### 6.4. `nordicbuilder` boundary draft

Нужно определить:

1. где живут source contracts;
2. как выглядит install/package structure;
3. как bridge-слой читает эти contracts до появления полного нового компонента.

### 6.5. Migration rule

Нужно сразу считать, что bridge JSON и будущий builder JSON могут отличаться, поэтому migration path должен быть предусмотрен заранее.

## 7. Что считается готовностью foundation layer

Foundation layer считаем готовым только если одновременно выполняется все ниже:

1. понятно, какой contract является источником истины для editor и runtime;
2. token inheritance не спорит между docs, runtime и UI;
3. component library описана как contract, а не как список случайных шаблонов;
4. bridge-роль текущего `landingbuilder` явно ограничена;
5. можно начинать scaffold `nordicbuilder` без новой архитектурной дискуссии.

## 8. Что делать сразу после foundation layer

После этого этапа следующий взрослый код идет в таком порядке:

1. минимальный scaffold `nordicbuilder`;
2. contract-aware `Visual Builder Workspace`;
3. упрощенный secondary UI `Глобальные стили`;
4. shell participation и widget/data adapters поверх уже стабильных contracts.

До этого шлифовать только текущий bridge-canvas считаем локальной поддержкой, а не основным курсом продукта.
