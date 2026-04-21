# NordicBlocks Design Block — Day Plan 2026-04-21

Дата: 2026-04-21

Связанный жёсткий runbook на утро и весь день:

1. [docs/nordicblocks/DESIGN-BLOCK-OPERATIONAL-CHECKLIST-2026-04-21.md](docs/nordicblocks/DESIGN-BLOCK-OPERATIONAL-CHECKLIST-2026-04-21.md)

## 1. Главная мысль дня

Завтра делаем не абстрактную «стабилизацию на потом», а один конкретный закрываемый deliverable дня.

Акцент дня:

1. поведение объектов на canvas должно стать предсказуемым и законченным для базового authoring loop;
2. добавление объектов на canvas должно стать реальным рабочим сценарием, а не ручной тех-демкой через существующий state;
3. редактор элемента должен получить понятный IA-контур в духе Tilda: выбрал объект -> видишь его настройки -> быстро меняешь content/layout/style.

Но важная рамка сохраняется: не клонируем Tilda целиком и не расползаемся в новый page-builder. Завтра закрываем только честный `v1` для element authoring внутри одной design section.

---

## 1.1 Главный deliverable дня

К вечеру должен существовать следующий сценарий без ручных костылей:

1. пользователь открывает `design_block`;
2. добавляет новый объект на canvas из понятного add-flow;
3. объект сразу появляется в слоях и на canvas в предсказуемой стартовой позиции;
4. объект можно выбрать, переместить, изменить размер, удалить, дублировать;
5. справа открывается редактор выбранного элемента с базовыми секциями `Контент`, `Макет`, `Стиль`;
6. изменения сразу видны на canvas;
7. save/reload возвращает ту же сцену без расхождения editor/runtime.

---

## 2. Цель дня

К концу дня `design_block` должен выглядеть не как canvas-эксперимент с доказанной геометрией, а как первый взрослый редактор свободной секции.

Итог дня считаем хорошим, если одновременно выполнены условия:

1. drag/resize/guides стабильно работают для базовых authoring-сценариев после добавления новых элементов;
2. есть рабочий add-object flow хотя бы для базовой палитры объектов;
3. element editor имеет фиксированную IA-структуру и не выглядит как временный набор случайных полей;
4. save/reload roundtrip подтверждён для сцены, собранной уже через новый add/edit flow;
5. widget/live smoke доказывает, что SSR/public runtime не расходится с editor canvas по ключевым layout cases.

---

## 3. Что точно не делаем завтра

Чтобы день не расползся, явно фиксируем non-goals:

1. не добавляем data bindings для `design_block`;
2. не начинаем animation system;
3. не делаем новый page-builder contour;
4. не добавляем новые block types;
5. не делаем «полную Тильду» с десятками панелей и режимов;
6. не уходим в большую косметическую чистку shell, если это не связано с object authoring flow.

Дополнительная жёсткая рамка scope:

1. палитра объектов завтра ограничена минимальным полезным набором;
2. editor IA делаем продуктовым, но не гигантским;
3. asset picker подключаем только если базовый add/edit/save loop уже зелёный.

---

## 4. Перед стартом дня

Перед первым рискованным backend/runtime проходом:

1. проверить рабочее дерево;
2. сделать checkpoint, если будут правки backend actions, renderer или save path;
3. выбрать один рабочий smoke block для live-проверок и один временный block для разрушительных сценариев;
4. зафиксировать текущий baseline проблем до первого edit-pass.
5. заранее зафиксировать минимальный состав палитры объектов и минимальный состав element editor, чтобы по ходу дня не расширять scope импульсивно.

---

## 5. Порядок работы на день

## Этап A. Baseline и список рисков

### Задача

Сначала не писать код, а собрать короткий честный список того, что ещё не доказано после сегодняшнего прохода.

### Что проверяем

1. nested parent alignment: snap только в пределах одного parent scope;
2. resize handles на всех направлениях: `n`, `s`, `e`, `w`, угловые кейсы;
3. guide positioning при nested offset и bleed;
4. текущее состояние add-flow: что уже умеет editor без ручного вмешательства в state;
5. что сейчас считается редактором элемента и какие зоны UI уже есть;
6. breakpoint switch после сохранения: desktop/tablet/mobile без потери grid contract;
7. live widget/runtime на реальной странице, а не только editor canvas.

### Результат этапа

1. есть короткий список конкретных дыр, а не общее ощущение «нужно ещё пошлифовать».

---

## Этап B. Object palette и add-to-canvas flow

### Задача

Сделать добавление объектов на canvas основным рабочим сценарием завтрашнего дня.

### Что закрываем завтра как минимум

1. `Текст`
2. `Кнопка`
3. `Изображение` или image placeholder
4. `Shape` / прямоугольник
5. при необходимости `Container`, только если без него не закрывается nested editing loop

### Какой UX считаем правильным

1. есть одна явная точка входа `Добавить`;
2. пользователь выбирает тип объекта без ручного JSON/state вмешательства;
3. новый объект получает предсказуемый default contract;
4. объект появляется в разумной стартовой позиции и сразу выбирается;
5. layers panel синхронно показывает новый узел.

### Что делаем

1. фиксируем minimal palette contract для базовых object types;
2. добавляем create-element actions в editor store/runtime;
3. задаём predictable insertion rules для root и, если успеем, для selected container;
4. добавляем delete/duplicate как обязательные операции базового authoring loop;
5. проверяем, что save path принимает новые объекты без ручных правок contract.

### Критерий готовности

1. editor сам умеет породить рабочую сцену из новых объектов;
2. больше не нужно вручную подсовывать элементы через endpoint или существующий bootstrap.

---

## Этап C. Interaction hardening

### Задача

Укрепить canvas interaction уже на фоне реального add/edit flow, а не на заранее подготовленной сцене.

### Что делаем

1. проверить sibling candidates для nested/container children и при необходимости сузить/исправить parent-scope filtering;
2. проверить `localGuideToWorld` на nested offsets и container padding;
3. прогнать resize logic для edge/center/end snapping по обоим осям;
4. убедиться, что guide fallback не затирает alignment guide после resize;
5. отдельно проверить negative `x` и bleed при drag/resize;
6. проверить поведение сразу после вставки нового элемента: selection, drag-start, guides, layers sync.

### Какие файлы вероятнее всего будут в работе

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/design-block-interaction-core.js`
3. package mirror equivalents
4. `tests/nordicblocks/design-block-interaction-core.test.cjs`
5. при необходимости `tests/nordicblocks/design-block-geometry-core.test.cjs`

### Критерий готовности

1. interaction core остаётся чистым;
2. editor shell не обрастает новыми хаотичными локальными special-case ветками;
3. unit smoke закрывает найденные реальные кейсы, а не только happy path.

---

## Этап D. Element editor IA в духе Tilda, но в нашем scope

### Задача

Продумать и собрать не «набор полей справа», а понятный редактор выбранного элемента.

### Что именно берём из логики Tilda

1. у выбранного элемента есть один очевидный центр управления;
2. настройки сгруппированы по смыслу, а не по внутренней структуре JSON;
3. быстрые частые правки не спрятаны глубоко;
4. у текста, кнопки, картинки и shape есть узнаваемый базовый edit-flow.

### Что не копируем 1:1

1. не строим отдельные floating toolbars и десятки модальных режимов;
2. не плодим разные editor-shell ветки под каждый тип элемента;
3. не делаем element settings сложнее, чем текущий `v1` реально требует.

### Какая IA-структура нужна завтра

1. `Контент`
2. `Макет`
3. `Стиль`

### Минимальный состав по типам

1. текст: content, typography basics, color, width/height, x/y, layer order;
2. кнопка: label, url placeholder, radius, padding, colors, box metrics;
3. изображение: source placeholder, object fit basics, radius, box metrics;
4. shape: fill, radius, stroke basics, box metrics.

### Что делаем

1. фиксируем один canonical element inspector layout;
2. выделяем shared fields и type-specific fields без хаотичных special cases;
3. проверяем синхронизацию `canvas -> layers -> inspector` и обратно;
4. добавляем, если нужно, quick actions уровня `Дублировать`, `Удалить`, `Поднять`, `Опустить`.

### Критерий готовности

1. редактор элемента выглядит как первая взрослая версия, а не как временный debug-panel;
2. новая IA читается одинаково для нескольких object types.

---

## Этап E. Save/reload and state parity hardening

### Задача

После interaction fixes доказать, что state не ломается на полном цикле `edit -> save -> reload -> re-open`.

### Что делаем

1. поднять временный `design_block` для roundtrip smoke, если это потребуется для чистого эксперимента;
2. сохранить сцену, собранную через новый add-object flow, с nested element alignment, bleed и отличающимися breakpoint grids;
3. перезагрузить editor и сверить state endpoint с ожидаемым contract shape;
4. убедиться, что guide-related редакторская transient state не попадает в persistent contract;
5. убедиться, что normalizer не съедает ни `grid.*`, ни scene box values, ни nested placement.

### Критерий готовности

1. сервер возвращает именно тот contract, который ожидает canvas engine;
2. после reload поведение не деградирует до «примерно похоже».

---

## Этап F. Widget/live parity smoke

### Задача

Завтра нужно закрыть главный прикладной вопрос: не только editor живёт, но и block placement/runtime на живой странице.

### Что делаем

1. разместить рабочий `design_block` через штатный widget flow;
2. открыть публичную страницу и сверить layout ключевых элементов;
3. проверить desktop/mobile хотя бы на двух breakpoint scenarios;
4. проверить, что bleed и отрицательные координаты не ломают SSR/public вывод;
5. убедиться, что editor assets не протекают в public runtime.

### Критерий готовности

1. есть честный live smoke, который подтверждает parity editor/runtime по layout, а не только факт отсутствия 500-ошибки.

---

## Этап G. Asset picker start, только если A-F зелёные

### Задача

Если interaction и runtime parity уже устойчивы, начать следующий логичный слой — штатный picker flow.

### Минимальный scope

1. сначала только image picker для image node или image-like property;
2. использовать стандартный InstantCMS UX, без самодельного asset manager;
3. сохранить нормализованный path и сразу проверить save/reload;
4. icon picker начинать только если image path уже чисто прошёл.

### Почему именно так

1. picker — это полезное усиление режима;
2. но он не должен маскировать непрочный canvas/runtime base.

---

## 6. Приоритеты по времени

Если день идёт без срывов, порядок такой:

1. утро — baseline + checkpoint + palette/add-flow;
2. позднее утро и середина дня — interaction hardening + element editor IA;
3. вторая половина дня — unit tests + save/reload roundtrip + live parity smoke;
4. остаток — image picker start или doc/result fixation.

Если день идёт тяжело, режем scope так:

1. сначала add-object flow;
2. потом element editor IA;
3. потом save/reload parity;
4. picker переносим, если live parity ещё не доказана.

---

## 6.1 Что именно считаем закрытым завтра

Чтобы не было двусмысленности, завтра считаем задачу закрытой, если есть такой минимальный product slice:

1. можно добавить текст, кнопку и shape без ручной правки state;
2. можно выбрать и редактировать эти элементы через один понятный inspector;
3. можно удалить и дублировать элемент;
4. scene сохраняется и возвращается после reload;
5. live runtime не разваливает созданную сцену.

Если сверху успеют image object и container flow, это плюс, но не ценой развала базового slice.

---

## 7. Итог дня, который считаем честным успехом

День считаем закрытым, если к вечеру можно сказать следующее:

1. `design_block` стабилен не только на одиночном root drag smoke, но и на nested layout cases;
2. новые объекты можно добавлять на canvas через реальный UI-flow;
3. выбранный объект получает взрослый редактор элемента с ясной IA;
4. state contract подтверждён повторным save/reload;
5. live widget/public runtime не расходится с editor по ключевой layout-геометрии;
6. следующий шаг после этого уже понятен: asset picker integration и расширение palette, а не повторная переделка element authoring base.

---

## 8. Точка отката

Перед завтрашним рискованным проходом использовать свежий checkpoint.

Если стартуем прямо от текущего состояния, базовая опорная точка на сейчас:

1. snapshot `snapshot/20260420-205305`
2. commit `77f9373`

Завтра при первом backend/runtime заходе лучше сделать новый checkpoint поверх этой базы.