# NordicBlocks Block Scaffold + Validator Spec V1

Дата: 2026-04-19

## 1. Назначение документа

Этот документ фиксирует ТЗ на внутренний generator и validator новых block type для NordicBlocks именно под текущий репозиторий.

Цель не в том, чтобы сделать абстрактный no-code builder для любых блоков.

Цель в другом:

1. перестать собирать каждый новый block type вручную с нуля;
2. стандартизировать обязательный scaffold;
3. жёстко проверять интеграцию блока в manifest-first и contract-driven архитектуру;
4. не допускать нарушения Global Design Foundation уже на этапе генерации;
5. уменьшить количество ошибок класса "папку блока создали, а registry / normalizer / package mirror забыли".

Итоговый принцип:

generator создаёт только канонический каркас блока,
validator не пропускает scaffold, если он не соответствует архитектуре репозитория.

## 2. Архитектурная база, на которую должен опираться инструмент

Generator и validator обязаны работать поверх уже существующей архитектуры проекта, а не рядом с ней.

Канонические опоры:

1. `system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php`
2. `system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php`
3. `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
4. `system/controllers/nordicblocks/libs/BindingMapper.php`
5. `system/controllers/nordicblocks/libs/DataSourceResolver.php`
6. `system/controllers/nordicblocks/model.php`
7. `docs/nordicblocks/BLOCK-DESIGN-MANIFEST-V1.md`
8. `docs/nordicblocks/UNIFIED-INSPECTOR-ARCHITECTURE.md`
9. `docs/nordicblocks/INSPECTOR-V2-IMPLEMENTATION-REGISTRY.md`
10. `docs/nordicblocks/GLOBAL-DESIGN-FOUNDATION-V2.md`

Ключевое ограничение:

новый инструмент не имеет права вводить вторую параллельную схему block definition.

Он должен собирать блоки в рамках уже принятой модели:

1. `meta`
2. `content`
3. `design`
4. `layout`
5. `data`
6. `entities`
7. `runtime`

## 3. Scope V1

В первую версию входят:

1. scaffold нового block type первой волны;
2. генерация обязательных файлов блока в live-коде и package mirror;
3. генерация обязательных patch-точек в central registry файлах;
4. structural validation;
5. contract validation;
6. global design-system validation;
7. sync validation между live и package;
8. dry-run режим;
9. apply режим с обязательным checkpoint.

В первую версию не входят:

1. автогенерация уникального визуального языка блока;
2. автогенерация сложной SSR-логики на основании свободной схемы;
3. автогенерация новых inspector controls вне существующего registry;
4. SQL-миграции;
5. автоматическое внесение произвольных изменений в шаблоны сайта.

## 4. Формат инструмента

### 4.1 Обязательные сценарии

Нужно спроектировать два внутренних сценария:

1. `scripts/nordicblocks-scaffold-block.php`
2. `scripts/nordicblocks-validate-block.php`

Допускается один объединённый script с subcommand-режимами `scaffold` и `validate`, но логически это должны быть две независимые операции:

1. генерация;
2. проверка.

### 4.2 Режимы работы

Scaffold обязан поддерживать режимы:

1. `--dry-run`
2. `--apply`

Validate обязан поддерживать режимы:

1. `--block=<slug>`
2. `--strict`
3. `--json`
4. `--fail-on-warning`

### 4.3 Требование безопасности

Так как репозиторий живёт на рабочем сервере, `--apply` запрещён без checkpoint.

Минимальное правило V1:

1. scaffold в apply-режиме требует явный параметр checkpoint label;
2. если checkpoint не создан или не передан, scaffold завершается ошибкой до записи файлов;
3. рекомендованный путь интеграции: вызов `scripts/pre-change-checkpoint.sh` перед записью.

## 5. Входные параметры scaffold

Минимальный обязательный вход:

1. `slug` блока
2. `title` блока
3. `family` блока
4. `sourceModeProfile`
5. `entities`
6. `capabilities`
7. `panels`

### 5.1 Требования к `slug`

`slug`:

1. только `a-z`, `0-9`, `_` и `-`;
2. обязан быть уникальным в `system/controllers/nordicblocks/blocks/`;
3. обязан совпадать с `meta.blockType` в generated contract;
4. обязан совпадать между live и package mirror.

### 5.2 Требования к `family`

`family` не является декоративным полем.

Он нужен для:

1. связи с docs;
2. проверки статуса блока в чеклисте;
3. будущего family-level reuse.

Если family отсутствует в документации, generator не должен молча придумывать новую архитектуру.

Разрешённые режимы:

1. использовать уже существующую family doc;
2. создать family doc stub по отдельному флагу.

### 5.3 Требования к сущностям

Для всех новых блоков V1 обязательны сущности:

1. `title`
2. `subtitle`
3. `section`

Правила:

1. `title` и `subtitle` обязательны в manifest и contract всегда;
2. обе сущности обязаны поддерживать show / hide;
3. при `visible = false` элемент не должен занимать место в layout;
4. остальные сущности generator принимает только из канонического registry.

## 6. Что scaffold обязан создавать

### 6.1 Обязательные block files в live-коде

Для каждого нового блока scaffold обязан создавать:

1. `system/controllers/nordicblocks/blocks/<slug>/manifest.php`
2. `system/controllers/nordicblocks/blocks/<slug>/render.php`
3. `system/controllers/nordicblocks/blocks/<slug>/schema.json`

Эти три файла считаются минимальным каноническим набором block directory.

Если хотя бы один отсутствует, validator должен возвращать `FAIL`.

### 6.2 Обязательные mirror files в package

Scaffold обязан одновременно создавать mirror-копии:

1. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/<slug>/manifest.php`
2. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/<slug>/render.php`
3. `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/<slug>/schema.json`

Правило:

live block files и package mirror block files обязаны быть byte-to-byte эквивалентны сразу после генерации.

### 6.3 Обязательные central integration patch-points

Scaffold обязан не только создать папку блока, но и внести block type в центральные точки интеграции.

Минимальный обязательный список:

1. `system/controllers/nordicblocks/model.php`
2. `packages/nordicblocks/package/system/controllers/nordicblocks/model.php`
3. `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
4. `packages/nordicblocks/package/system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
5. `system/controllers/nordicblocks/libs/BindingMapper.php`
6. `packages/nordicblocks/package/system/controllers/nordicblocks/libs/BindingMapper.php`
7. `system/controllers/nordicblocks/libs/DataSourceResolver.php`
8. `packages/nordicblocks/package/system/controllers/nordicblocks/libs/DataSourceResolver.php`

Смысл patch-пойнтов:

1. block type должен появиться в first-wave registry;
2. schema должна читаться model-слоем;
3. contract normalizer должен поддерживать type;
4. denormalize path должен быть определён;
5. data bindings должны быть либо поддержаны, либо явно запрещены;
6. data source editor options должны быть корректны для данного профиля блока.

### 6.4 Обязательные docs-артефакты

Scaffold обязан обновлять documentation trail.

Минимальный обязательный набор:

1. запись в `docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json`
2. обновление или создание family-level документа в `docs/nordicblocks/`
3. запись в актуальный worklog, если генерация запускается в рамках активной сессии разработки

Правило:

если новый блок создаётся без отражения в checklist и docs, validator возвращает `FAIL`.

### 6.5 Что scaffold может создавать опционально

Опционально, по флагу:

1. stub секции в `system/controllers/nordicblocks/assets/blocks.css`
2. mirror секцию в `packages/nordicblocks/package/system/controllers/nordicblocks/assets/blocks.css`
3. mirror секции в `templates/*/css/nordicblocks_blocks.css`

Но это разрешено только если блоку реально нужен shared runtime CSS.

Если блок можно собрать существующими utility-классами и CSS variables, generator не должен без причины плодить новый CSS section.

## 7. Что именно должно быть внутри generated files

### 7.1 `manifest.php`

`manifest.php` обязан содержать:

1. `title`
2. `entities`
3. `entityGroups`
4. `capabilities`
5. `panels`

Правила валидации manifest:

1. все entity keys должны существовать в `InspectorDefinitionRegistry`;
2. все panel keys должны существовать в shared panel registry;
3. все capability keys должны существовать в shared capability registry;
4. manifest не может объявлять неизвестные сущности, панели или capability;
5. `title` и `subtitle` обязательны всегда;
6. если заявлена repeater-структура, должны быть валидные item-level entities.

### 7.2 `schema.json`

`schema.json` обязана быть служебным seed-слоем для default props, а не второй архитектурой блока.

Минимальные требования:

1. валидный JSON;
2. набор полей для безопасного initial create flow;
3. дефолты, совместимые с contract normalizer;
4. отсутствие полей, которые дублируют глобальные design tokens без нужды.

Запрещено использовать `schema.json` как свалку одноразовых legacy-полей.

### 7.3 `render.php`

Generated render обязан:

1. работать от нормализованного contract;
2. корректно падать обратно на безопасные defaults;
3. SSR-рендерить block markup без зависимости от editor-only данных;
4. использовать global helper-классы и CSS vars, где это возможно;
5. уважать `title.visible` и `subtitle.visible`;
6. не содержать жестко прибитых design-token значений для typographic, radii, card surface и button system.

## 8. Обязательные проверки validator

Validator должен работать минимум в 5 этапов.

### 8.1 Этап A. Preflight

До генерации validator обязан проверить:

1. slug не занят;
2. все целевые директории существуют или могут быть созданы;
3. family определена корректно;
4. checkpoint передан для apply-режима;
5. входные entities/panels/capabilities принадлежат каноническому registry;
6. не запрошены inspector controls, которых нет в проекте.

Если хотя бы один пункт не выполнен, запись файлов запрещена.

### 8.2 Этап B. Structure Validation

После генерации validator обязан проверить:

1. наличие всех обязательных live files;
2. наличие всех обязательных package mirror files;
3. синхронность содержимого live/package файлов;
4. корректную загрузку `schema.json`;
5. корректную загрузку `manifest.php`;
6. успешную сборку registry через `NordicblocksInspectorRegistryBuilder::build(<slug>)`.

### 8.3 Этап C. Contract Validation

Validator обязан проверить block contract lifecycle:

1. `BlockContractNormalizer::supportsContractType(<slug>) === true`;
2. normalizer умеет построить canonical contract;
3. в contract присутствуют все корневые ветки: `meta`, `content`, `design`, `layout`, `data`, `entities`, `runtime`;
4. `meta.contractVersion = 3`;
5. `meta.blockType = <slug>`;
6. `denormalizeProps(<slug>, contract)` выполняется без фаталов;
7. round-trip normalize -> denormalize -> normalize не теряет обязательные сущности.

### 8.4 Этап D. Registry / Runtime Integration Validation

Validator обязан проверить, что новый блок реально интегрирован в runtime цепочку, а не только имеет папку.

Минимальные проверки:

1. block type доступен через first-wave definitions в model;
2. create flow может прочитать schema defaults;
3. editor registry собирается из manifest;
4. если блок заявляет dynamic data profile, он зарегистрирован в `BindingMapper` и `DataSourceResolver`;
5. если блок не заявляет dynamic data profile, validator убеждается, что лишняя data-интеграция не включена.

### 8.5 Этап E. Global Design System Compliance

Это обязательный блокирующий этап.

Если блок нарушает глобальную дизайн-систему, validator обязан вернуть `FAIL` и не пропустить scaffold.

#### 8.5.1 Что считается нарушением

Нарушением считается хотя бы одно из следующего:

1. hardcoded `font-family` вместо `var(--nb-font-body)`, `var(--nb-font-head)` или `var(--nb-font-button)`;
2. hardcoded button colors и button border вместо глобальной button system;
3. hardcoded card/media radius вместо `var(--nb-radius-card)` и `var(--nb-radius-media)` там, где должен работать global foundation;
4. hardcoded card shadow вместо `var(--nb-shadow-card)` или эквивалентного global preset path;
5. прямое создание новых локальных color/radius/shadow полей, если ту же задачу уже решает global design system;
6. локальные overrides без механики наследования от global design;
7. добавление одноразовых design controls, не описанных в manifest-first архитектуре;
8. block CSS, который ломает или обходит shared helper-классы `.nb-btn`, `.nb-surface`, `.nb-card`, `.nb-media`.

#### 8.5.2 Что validator обязан требовать

Validator обязан требовать:

1. typography по умолчанию строится от global tokens;
2. surface и media styling по умолчанию строятся от global tokens;
3. local override допускается только как явный override поверх режима наследования;
4. если у сущности есть локальный override style, у неё должен быть явный флаг в духе `inheritGlobalStyle` или эквивалентная декларативная механика;
5. generated CSS section не должна прибивать дизайн блока к частным шрифтам, цветам и радиусам.

#### 8.5.3 Политика отказа

При нарушении global design system:

1. validator возвращает блокирующую ошибку;
2. scaffold в apply-режиме не пишет файлы или откатывает частично созданные файлы;
3. итоговый статус операции: `FAILED_DS_GUARD`.

## 9. Поддерживаемые профили блока

Scaffold V1 должен работать не с произвольными схемами, а с ограниченным набором blueprint-профилей.

Минимум нужны профили:

1. `hero_like`
2. `faq_like`
3. `card_collection`
4. `catalog_like`
5. `text_section`

Смысл profile-подхода:

1. reuse существующих contract-паттернов;
2. reuse нормализаторов и binding logic;
3. меньше случайных архитектурных расхождений;
4. generator не сочиняет новый block contract с нуля.

Если requested block не укладывается ни в один profile, scaffold должен завершаться неуспехом и требовать отдельной архитектурной спецификации.

## 10. Что generator обязан патчить в зависимости от profile

### 10.1 Для статических блоков без dynamic data

Обязательные действия:

1. создать block dir + package mirror;
2. зарегистрировать block type в model first-wave;
3. добавить поддержку type в contract normalizer;
4. не включать лишние data bindings.

### 10.2 Для блоков с `content_item`

Дополнительно:

1. зарегистрировать profile в `DataSourceResolver`;
2. зарегистрировать bindings в `BindingMapper`;
3. проверить editor options для content type fields.

### 10.3 Для блоков с `content_list`

Дополнительно:

1. зарегистрировать list-source profile;
2. зарегистрировать mapping item fields;
3. проверить item-level entities и repeater compatibility;
4. проверить fallback между manual и dynamic list режимом.

## 11. Выход validator

Validator обязан отдавать machine-readable и human-readable результат.

Минимальный формат статусов:

1. `PASS`
2. `PASS_WITH_WARNINGS`
3. `FAIL`
4. `FAILED_DS_GUARD`

Минимальные категории сообщений:

1. `structure`
2. `contract`
3. `registry`
4. `runtime`
5. `design_system`
6. `docs`
7. `sync`

## 12. Acceptance Criteria для V1

Решение считается готовым, если выполнены все условия:

1. новый block type создаётся одной командой в dry-run и apply режимах;
2. создаются live files и package mirrors;
3. central registries патчатся автоматически;
4. validator видит block через model, manifest builder и contract normalizer;
5. generator не пропускает scaffold при нарушении global design system;
6. docs/checklist trail обновляется автоматически;
7. на выходе нет scaffold, который выглядит созданным, но фактически не работает в editor/runtime.

## 13. Что считаем сознательно запрещённым

В рамках этого ТЗ запрещены следующие упрощения:

1. создать только папку блока и считать задачу выполненной;
2. генерировать блок без package mirror;
3. генерировать блок без обновления `NEW_BLOCK_CHECKLIST_STATUS.json`;
4. генерировать блок с локальными hardcoded typography/color/radius/shadow решениями в обход global foundation;
5. генерировать новые raw schema fields вместо использования канонических entities/capabilities;
6. пропускать scaffold, если manifest ссылается на неизвестные panel/entity keys;
7. разрешать apply без checkpoint.

## 14. Рекомендуемый порядок реализации

Реализовывать нужно в таком порядке:

1. зафиксировать blueprint profiles и input schema generator-а;
2. сделать preflight validator без записи файлов;
3. сделать file scaffold для block dir и package mirror;
4. сделать central registry patch layer;
5. сделать contract/runtime validation;
6. последним сделать DS guard и strict sync checks.

Такой порядок нужен, чтобы сначала получить предсказуемый scaffold, а уже потом ужесточать guardrails.

## 15. Итоговая позиция

Для этого репозитория нужен не "генератор блоков вообще", а дисциплинированный внутренний scaffold pipeline.

Его задача:

1. быстро поднимать канонический block skeleton;
2. не давать разъехаться live/package/docs;
3. принуждать новый блок жить внутри manifest-first, contract-driven и global design-system архитектуры;
4. останавливать создание блока, если он нарушает взрослые правила системы.

Именно в таком виде generator усилит NordicBlocks.

В более слабом виде он просто ускорит производство нового технического долга.