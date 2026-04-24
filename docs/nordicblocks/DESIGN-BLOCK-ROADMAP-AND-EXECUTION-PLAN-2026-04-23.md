# NordicBlocks Design Block: Roadmap And Execution Plan

Дата: 2026-04-23

## Цель документа

Этот документ фиксирует следующий рабочий план по `design_block` после закрытия критичного parity-дефекта между admin editor и public runtime.

Документ нужен не как абстрактный brainstorming, а как practical roadmap для дальнейшей разработки в репозитории.

## Исходная точка

На 2026-04-23 по `design_block` уже подтверждено:

1. editor/runtime parity по stage geometry закрыта на реальном block instance;
2. save path начал сохранять explicit stage fields editor-а;
3. runtime stage width semantics выровнена с editor;
4. zero-gap section behavior закреплён в runtime CSS;
5. persistent guides уже добавлены в editor workflow.

Опорный документ по закрытому parity-багу:

1. `docs/nordicblocks/WORKLOG-2026-04-23.md`

## Главный вывод

Следующий этап развития `design_block` должен идти не от добавления новых визуальных фич, а от трёх опор:

1. устойчивый editor workflow;
2. contract/release discipline;
3. controlled expansion после стабилизации ядра.

Иначе продукт быстро станет визуально сильнее, но архитектурно хрупче.

## Рабочие принципы

1. Любые изменения stage/grid/bleed semantics считаются high-risk, пока не доказана editor/runtime parity на одном и том же block id.
2. Любые изменения в `DesignBlockContractNormalizer` и `DesignBlockCssBuilder` должны идти синхронно в live и package mirror.
3. Новые UX-возможности editor-а не должны расширяться быстрее, чем quality-gates вокруг save/runtime path.
4. Sequence, responsive evolution и component library не должны обгонять базовую надёжность editor workflow.

## Roadmap

### Горизонт 1: ближайшие 2 недели

Цель: превратить `design_block` из мощного, но хрупкого инструмента в устойчивый рабочий редактор.

#### Приоритеты

1. `undo/redo`;
2. keyboard shortcuts;
3. visible multi-select workflow;
4. align/distribute operations;
5. базовый layer panel;
6. autosave/draft recovery decision;
7. schema versioning для contract;
8. release minimum для design block.

#### Ожидаемый результат

1. editor пригоден для регулярной ручной работы без страха потерять состояние;
2. сложные операции редактирования становятся воспроизводимыми и обратимыми;
3. stage-related changes больше не проходят в релиз без минимального safety contour.

### Горизонт 2: следующий месяц

Цель: укрепить delivery и открыть следующий уровень выразительности без слома ядра.

#### Приоритеты

1. legacy contract audit и backfill strategy;
2. полноценный Sequence V1 inspector workflow;
3. улучшение selection feedback, guide/grid priority и snap feedback;
4. отдельные smoke/tests для legacy contracts, stage CSS vars и sequence path.

#### Ожидаемый результат

1. `design_block` становится предсказуемым в сопровождении;
2. новые motion/composition-возможности развиваются на стабильной базе;
3. накопленный legacy risk перестаёт быть слепой зоной.

### Горизонт 3: квартал

Цель: перейти от сильной внутренней editor-feature к продуктовой capability внутри NordicBlocks.

#### Приоритеты

1. более зрелая responsive model;
2. reusable component presets/library поверх `design_block`;
3. visual regression discipline;
4. contract migration discipline как постоянная часть delivery.

#### Ожидаемый результат

1. `design_block` становится опорной продуктовой поверхностью;
2. новые block families и reusable patterns могут строиться поверх него системно, а не вручную.

## Статусы

Ниже статусная карта по состоянию на 2026-04-24. Она нужна как живая рабочая поверхность, а не как формальный отчёт.

### 🟢 Сделано

1. закрыта editor/runtime parity по stage geometry на реальном block instance;
2. save path начал сохранять explicit stage fields editor-а;
3. runtime stage width semantics выровнена с editor;
4. zero-gap section behavior закреплён в runtime CSS;
5. persistent guides добавлены в editor workflow;
6. roadmap-документ по `design_block` создан;
7. roadmap-документ включён в стартовый маршрут через `docs/nordicblocks/START-2026-04-22.md`.

### 🟡 В работе

1. сбор практических замечаний по editor UX из реальной сборки design blocks;
2. уточнение приоритета первого editor-спринта на основе живого authoring experience;
3. подготовка очередности задач внутри workflow core без преждевременного расширения feature scope;
4. фиксация отдельного Tilda-style reference для правой панели: `docs/nordicblocks/DESIGN-BLOCK-TILDA-STYLE-SIDEBAR-SPEC-2026-04-24.md`;
5. разбор реальных проблем text/group workflow по итогам практической сборки блоков.

### 🔵 Запланировано

1. `undo/redo`;
2. keyboard shortcuts;
3. visible multi-select workflow;
4. align/distribute operations;
5. базовый layer panel;
6. решение по autosave или draft recovery;
7. schema versioning для contract;
8. live/package parity script;
9. legacy contract smoke;
10. manual visual parity gate на 3 реальных блоках;
11. Sequence V1 inspector после стабилизации workflow core;
12. responsive evolution только после закрепления safety contour.

## Практические замечания из реальной сборки

Этот блок предназначен для ручного пополнения по мере живой работы в редакторе.

Правило простое:

1. записываем только реальные проблемы, замеченные во время сборки блоков;
2. не смешиваем сюда абстрактные идеи и wishlist без практического кейса;
3. каждое замечание должно быть коротким и проверяемым.

Рекомендуемый формат записи:

1. что именно делал в редакторе;
2. где возникла проблема;
3. какой ожидался результат;
4. что произошло фактически;
5. насколько это мешает работе.

Шаблон списка:

1. `[ ]` Сценарий:
	Ожидание:
	Факт:
	Приоритет:
2. `[ ]` Сценарий:
	Ожидание:
	Факт:
	Приоритет:
3. `[ ]` Сценарий:
	Ожидание:
	Факт:
	Приоритет:

### Текущие замечания по работе с текстом и группами

1. `[ ]` Дублирование быстрых действий в sidebar.
	Ожидание: действия `поднять`, `удалить`, `дублировать` живут в одном очевидном месте.
	Факт: те же операции видны и в правой панели, и в контекстном меню, из-за чего sidebar шумит и повторяет уже доступные действия.
	Приоритет: высокий.

2. `[ ]` Лишний блок `Контент` для текста.
	Ожидание: если текст уже редактируется прямо на холсте, правая панель должна в первую очередь давать geometry и style controls, а не повторять текстовое содержимое.
	Факт: справа дублируется текстовый content block, который воспринимается как шум.
	Приоритет: высокий.

3. `[ ]` Потеря удобного захвата при работе с большим текстом.
	Ожидание: при длинном тексте изменение размера или возврат к верхней кромке объекта должно оставаться простым и предсказуемым.
	Факт: при большом объёме текста пользователь уходит вниз по холсту и потом вынужден заново ловить верхнюю границу объекта, чтобы подтянуть текст обратно.
	Приоритет: высокий.

4. `[ ]` Непрактичный перебор всех значений font weight.
	Ожидание: насыщенность шрифта должна переключаться по реальным значениям толщины, а не по каждому числу подряд.
	Факт: после 800 приходится перебирать 799, 798 и другие промежуточные числа, что неудобно и не соответствует реальному typographic workflow.
	Приоритет: средний.

5. `[ ]` Нельзя нормально двигать группу из нескольких текстовых объектов.
	Ожидание: после объединения `1` и `2` группа должна вести себя как один объект, а вход внутрь группы должен быть отдельным действием.
	Факт: выделяется либо один, либо второй объект, а не группа как единое целое.
	Приоритет: высокий.

6. `[ ]` Дублирование группы ломает состав выделения.
	Ожидание: дубликат группы должен сохранять весь набор объектов внутри неё.
	Факт: при дублировании группы дублируется только один текст.
	Приоритет: высокий.

7. `[ ]` Группа `текст + объект` становится неподвижной.
	Ожидание: смешанная группа должна перемещаться по холсту как единый объект.
	Факт: после группировки `text + object` группа перестаёт двигаться по холсту.
	Приоритет: высокий.

## Отдельный sidebar reference

Для правой панели настроек вынесен отдельный документ:

1. `docs/nordicblocks/DESIGN-BLOCK-TILDA-STYLE-SIDEBAR-SPEC-2026-04-24.md`

Он нужен как отдельный reference-layer и не заменяет общий roadmap.

Роль этого документа:

1. зафиксировать желаемый top-to-bottom порядок панели;
2. удерживать inspector от лишнего шума;
3. отделить sidebar IA от общего execution plan по `design_block`.

## Первый implementation batch

Ниже первый взрослый пакет работ, который напрямую следует из практических замечаний.

### Batch 01. Text and Group Workflow Stabilization

Цель batch:

1. убрать явный шум из sidebar;
2. вернуть предсказуемость text workflow на холсте;
3. починить базовую механику групп как единого объекта.

### Статусы batch

1. 🔵 Запланировано: Batch 01 целиком;
2. 🔵 Запланировано: Batch 01.1 Sidebar noise cleanup;
3. 🔵 Запланировано: Batch 01.2 Text canvas handling fix;
4. 🔵 Запланировано: Batch 01.3 Group behavior fix;
5. 🔵 Запланировано: Batch 01.4 Focused validation pass.

### Порядок коммитов и проверок

Ниже порядок, в котором этот batch нужно вести в коде.

#### Batch 01.1

Статус:

1. 🔵 Запланировано

Название:

1. Sidebar noise cleanup

Что войдёт в коммит:

1. убрать дублирование `удалить / дублировать / поднять` между sidebar и context menu;
2. оставить эти действия либо в одном источнике, либо в компактном quick-actions row;
3. убрать или радикально сократить блок `Контент` для text element.

Главные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Проверка после коммита:

1. text element больше не показывает шумный повтор действий;
2. sidebar для текста начинается с `Position` и style-блоков;
3. context menu остаётся рабочим.

#### Batch 01.2

Статус:

1. 🔵 Запланировано

Название:

1. Text canvas handling fix

Что войдёт в коммит:

1. улучшение работы с длинным текстом на холсте;
2. исправление доступа к верхней кромке и resize-handles;
3. перевод font weight на реальные supported values.

Главные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Проверка после коммита:

1. длинный текст не ломает базовый authoring loop;
2. верхняя зона текста снова удобно захватывается;
3. weight selector не перебирает бессмысленные промежуточные числа.

#### Batch 01.3

Статус:

1. 🔵 Запланировано

Название:

1. Group behavior fix

Что войдёт в коммит:

1. single click выбирает группу как единый объект;
2. вход внутрь группы отделяется от обычного выбора;
3. duplicate group сохраняет всех children;
4. mixed group `text + object` остаётся movable.

Главные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. при необходимости `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Проверка после коммита:

1. группа двигается как один объект;
2. duplicate создаёт полный клон группы;
3. mixed group больше не зависает на холсте.

#### Batch 01.4

Статус:

1. 🔵 Запланировано

Название:

1. Focused validation pass

Что войдёт в шаг:

1. ручная проверка text workflow;
2. ручная проверка групп из `text + text` и `text + object`;
3. повторная проверка sidebar после cleanup.

Проверка завершения:

1. все три подэтапа Batch 01 можно переключить в `🟢 Готово`;
2. batch перестаёт быть просто планом и становится закрытым execution unit.

### Состав batch

#### 1. Sidebar noise cleanup

Задачи:

1. убрать дублирование `удалить / дублировать / поднять` между sidebar и context menu;
2. перевести эти действия либо в единый quick-actions row, либо оставить только в одном источнике;
3. убрать или радикально сократить блок `Контент` для text element, если текст уже редактируется на холсте.

Главные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Acceptance criteria:

1. действия над выделением больше не дублируются без причины;
2. text sidebar начинается с `Position` и style-блоков, а не с повтора текстового содержимого;
3. inspector визуально становится короче и чище для text element.

#### 2. Text canvas handling fix

Задачи:

1. улучшить работу с длинным текстом, чтобы верхняя граница и resize-handles не терялись;
2. сделать font weight выбором из реальных поддерживаемых значений, а не из всех чисел подряд;
3. сохранить primary-path редактирование текста прямо на холсте.

Главные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Acceptance criteria:

1. длинный текст не ломает базовый authoring loop;
2. пользователь может без лишнего скролла снова захватить текстовый объект за верхнюю зону или handles;
3. font weight переключается по реальным значениям thickness.

#### 3. Group behavior fix

Задачи:

1. после grouping группа выбирается и двигается как единый объект;
2. вход внутрь группы отделён от обычного single-click выбора;
3. duplicate group сохраняет все children;
4. mixed group `text + object` не становится неподвижной.

Главные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. при необходимости `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Acceptance criteria:

1. single click выбирает группу целиком;
2. группа двигается по холсту как один объект;
3. duplicate создаёт полный клон группы;
4. смешанная группа остаётся movable.

### Порядок выполнения

1. сначала `Sidebar noise cleanup`;
2. затем `Text canvas handling fix`;
3. затем `Group behavior fix`.

Причина порядка:

1. сначала убираем UI-шум;
2. затем стабилизируем основной text workflow;
3. после этого исправляем grouping как более чувствительный interaction layer.

### Что не входит в Batch 01

1. полный undo/redo pass;
2. full layer-panel rewrite;
3. sequence inspector;
4. responsive evolution;
5. animation/states expansion.

Это намеренно узкий пакет, чтобы закрыть самые болезненные практические проблемы без расползания scope.

## Execution Plan

Ниже не абстрактный roadmap, а порядок работ с опорой на текущие файлы репозитория.

### Фаза A. Workflow Core

Это первый обязательный проход.

#### A1. Undo/redo

Главная точка:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`

Что делать:

1. ввести историю состояний для осмысленных edit actions;
2. делать snapshot не на каждом render tick, а на завершении операций;
3. держать историю на ограниченной глубине.

Что проверять:

1. drag;
2. resize;
3. property edit;
4. duplicate;
5. delete.

Критерий готовности:

1. `undo/redo` возвращает не только geometry, но и корректный selection/inspector state.

#### A2. Keyboard shortcuts

Главная точка:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`

Что делать:

1. собрать единый shortcut registry;
2. реализовать минимум: undo, redo, delete, duplicate, copy, paste, escape;
3. отделить global shortcuts от text editing state.

Критерий готовности:

1. shortcuts не конфликтуют с inline text editing и transient UI modes.

#### A3. Visible multi-select и align/distribute

Главные точки:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Что делать:

1. сделать явный group selection outline;
2. добавить align/distribute как часть selection workflow;
3. сохранить selection после операции.

Критерий готовности:

1. multi-object workflow визуально понятен без чтения внутренней логики.

#### A4. Layer panel

Главные точки:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.css`

Что делать:

1. превратить summary panel в реальный стек слоёв;
2. синхронизировать canvas selection и layer selection;
3. дать reorder и visibility toggle.

Критерий готовности:

1. layer panel становится source of truth по структуре и z-order, а не вторичным summary.

#### A5. Autosave / draft recovery

Главная точка:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`

Что делать:

1. принять решение между полноценным autosave и draft-recovery через local backup;
2. не включать autosave без явной стратегии по dirty-state и failed save.

Критерий готовности:

1. пользователь не теряет важное состояние при случайном выходе, но editor не создаёт ложное ощущение сохранённости.

### Фаза B. Contract Safety

Этот слой может идти параллельно с Phase A.

#### B1. Schema versioning

Главные точки:

1. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
2. `packages/nordicblocks/package/system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`

Что делать:

1. ввести явную версию schema contract-а;
2. централизовать upgrade path;
3. исключить silent field loss при повторном save.

Критерий готовности:

1. старый contract проходит normalize без потери stage geometry;
2. новый contract не деградирует при round-trip save.

#### B2. Legacy contract audit

Главные точки:

1. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
2. `docs/nordicblocks/WORKLOG-2026-04-23.md`

Что делать:

1. сделать CLI audit path для старых `design_block` instances;
2. оценить масштаб legacy-only stage branch.

Критерий готовности:

1. до следующего крупного изменения stage semantics есть список legacy-risk blocks или понятный отчёт об их отсутствии.

### Фаза C. Runtime / Release Safety

Это обязательный набор перед любыми заметными `design_block` changes.

#### C1. Live/package parity

Главные точки:

1. `system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php`
2. `packages/nordicblocks/package/system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php`
3. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
4. `packages/nordicblocks/package/system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`

Что делать:

1. ввести автоматический diff-check или parity script;
2. не выпускать design_block changes при расхождении live и package mirror.

Критерий готовности:

1. нулевой diff между live и package версиями design block libs.

#### C2. Legacy/runtime smoke

Опорная зона:

1. `tests/nordicblocks/`

Что делать:

1. добавить smoke на legacy contract;
2. добавить smoke на stage CSS vars и runtime fallback chain;
3. при развитии sequence добавить отдельный smoke на sequence path.

Критерий готовности:

1. stage-related changes не выпускаются без узкого smoke.

#### C3. Manual visual parity

Опорный документ:

1. `docs/nordicblocks/WORKLOG-2026-04-23.md`

Что делать:

1. закрепить ручной gate на 3 реальных `design_block` instances;
2. сравнивать editor geometry и public runtime на одном block id;
3. обязательно проверять full-section соседство и отсутствие лишнего gap.

Критерий готовности:

1. если хотя бы один из трёх блоков визуально расходится с editor, релиз по `design_block` не идёт.

### Фаза D. Controlled Expansion

Эти работы не должны обгонять Phase A-C.

#### D1. Sequence V1 inspector

Главная точка:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`

Причина отложенного приоритета:

1. sequence без stable workflow и contract discipline увеличит вероятность hidden regressions.

#### D2. Responsive evolution

Главные точки:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php`

Причина отложенного приоритета:

1. stage/responsive layer уже была источником критичного parity-дефекта;
2. менять её повторно без safety contour нельзя.

#### D3. Component presets / library

Причина отложенного приоритета:

1. сначала editor должен стать надёжным низкоуровневым инструментом;
2. только после этого стоит делать reusable надстройки поверх него.

## Release Gates

Перед заметным `design_block` release/change-set должны выполняться все пункты ниже.

### Обязательный минимум

1. `php -l` для live и package mirror design block libs;
2. live/package parity diff для `DesignBlockCssBuilder` и `DesignBlockContractNormalizer`;
3. узкий smoke на stage/runtime contract path;
4. manual visual parity check на 3 реальных блоках;
5. cache clear и повторная runtime-проверка;
6. checkpoint перед заметным change-set.

### Дополнительно при touched sequence/motion path

1. отдельный smoke на sequence/motion path;
2. проверка, что блоки без sequence props продолжают рендериться как раньше.

## Первый практический пакет работ

Если брать самый сильный пакет по соотношению риск/выигрыш, он выглядит так:

1. `undo/redo`;
2. keyboard shortcuts;
3. visible multi-select + align/distribute;
4. layer panel;
5. schema versioning;
6. live/package parity script;
7. legacy contract smoke.

Именно этот пакет одновременно:

1. улучшает реальную работу в editor;
2. снижает риск повторного parity-регресса;
3. создаёт опору для Sequence V1 и responsive evolution.

## Что не делать раньше времени

До завершения базового stabilization слоя не стоит делать следующим приоритетом:

1. marketplace-layer вокруг `design_block`;
2. headless export;
3. collaboration;
4. aggressive responsive redesign;
5. расширение component library без quality gates.

Эти направления важны, но они должны идти после стабилизации editor и release discipline, а не вместо них.

## Критерий закрытия следующего этапа

Следующий этап по `design_block` можно считать закрытым, если одновременно выполнено всё ниже:

1. editor поддерживает базовый профессиональный workflow без потери состояния;
2. contract evolution перестаёт быть silent-risk зоной;
3. release path по `design_block` получает обязательные parity-gates;
4. Sequence V1 и дальнейшие responsive changes опираются на устойчивое ядро, а не на набор локальных фиксов.