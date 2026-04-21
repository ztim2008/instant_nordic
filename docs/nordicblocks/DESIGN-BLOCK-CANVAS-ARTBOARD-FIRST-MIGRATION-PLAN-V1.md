# Design Block Canvas Artboard-First Migration Plan V1

Дата: 2026-04-21
Статус: canonical migration plan
Область: design_block canvas

## 1. Зачем нужен новый план

Текущий editor уже имеет сильную внутреннюю scene-модель:

1. world coordinates;
2. viewport zoom;
3. camera offsets;
4. transform-based projection.

Технически это работает, но пользовательски ощущается не как Tilda Zero Block, а как scene editor с камерой.

Для следующего product-прохода нужно перевести canvas в artboard-first UX, где главная сущность для пользователя это белый блок-artboard, а не камера над сценой.

Главный принцип этого плана:

1. внутреннюю математику можно частично сохранить;
2. пользовательскую модель нужно перестроить;
3. block geometry, overflow и height должны ощущаться как свойства artboard;
4. zoom и pan должны стать вторичными editor utilities, а не главной логикой взаимодействия.

---

## 2. Что считаем правильной моделью

Ориентир: Tilda Zero Block.

Целевая пользовательская модель:

1. серое поле редактора это workspace;
2. белый прямоугольник это сам block artboard;
3. внутри artboard живут Window Container и Grid Container;
4. высота меняется у artboard, потянув за нижний край;
5. объект можно свободно вынести за grid;
6. объект можно вынести за границу блока, а дальше результат определяется overflow policy;
7. breakpoint правится локально и вручную, а не через автоматическую camera-логику.

Что это означает для нашего editor:

1. камера остаётся внутренней реализацией, но перестаёт быть главной UX-сущностью;
2. акцент в UI переносится с viewport offsets на artboard settings;
3. stage height перестаёт ощущаться как world metric и начинает ощущаться как высота блока.

---

## 3. Главная продуктовая цель

Собрать canvas, в котором:

1. artboard блока является главным объектом редактирования;
2. grid container является рабочей зоной композиции;
3. overflow является явным свойством artboard;
4. объекты не зажаты только в пределах контента;
5. нижний край блока можно тянуть предсказуемо и независимо по breakpoint;
6. layers и sidebar описывают сам block, а не camera state.

---

## 4. Целевой Artboard Contract

### 4.1 Канонические пространства

Нужно явно развести три пространства:

1. editor workspace;
2. artboard block;
3. grid container.

Расшифровка:

1. editor workspace это серое поле вокруг блока и служебная рабочая зона редактора;
2. artboard block это белая область блока, у которой есть высота, фон, overflow и границы;
3. grid container это рабочая сетка внутри artboard, относительно которой строятся основные x-координаты.

### 4.2 Поля contract

В stage contract должны остаться и использоваться как минимум:

1. layout.stage.<breakpoint>.windowWidth
2. layout.stage.<breakpoint>.contentWidth
3. layout.stage.<breakpoint>.columns
4. layout.stage.<breakpoint>.gutter
5. layout.stage.<breakpoint>.columnWidth
6. layout.stage.<breakpoint>.outerMargin
7. layout.stage.<breakpoint>.bleedLeft
8. layout.stage.<breakpoint>.bleedRight
9. layout.stage.<breakpoint>.minHeight
10. layout.stage.<breakpoint>.overflowMode

Но в UI их нужно переосмыслить как:

1. ширина artboard window;
2. ширина grid container;
3. высота artboard;
4. модульная сетка;
5. рабочие поля слева и справа;
6. overflow behavior.

### 4.3 Координатная модель

Координаты элементов должны оставаться freeform, но восприниматься так:

1. x=0 это левая граница grid container;
2. отрицательный x это выход в левую свободную область блока;
3. x больше contentWidth это выход вправо за grid;
4. y=0 это верх artboard;
5. y больше artboardHeight допустим при overflow policy, а не должен автоматически считаться ошибкой.

### 4.4 Overflow Policy

Overflow должен стать реальным artboard behavior с понятным смыслом:

1. visible: объект виден за границами блока;
2. hidden: всё вне границ artboard обрезается;
3. auto: блок получает скролл по нужной оси.

Правило проектирования:

1. editor всегда должен позволять добраться до элемента;
2. runtime overflow и editor workarea не обязаны быть одно и то же;
3. editor usability важнее буквального повторения runtime clipping.

---

## 5. Что в текущей реализации мешает

### 5.1 Camera-first UX

Сейчас zoom и pan слишком заметны как главная модель редактора.

Проблемы:

1. появляются пустые зоны при zoom;
2. пользователь ощущает, что двигает камеру, а не блок;
3. высота блока зависит от экранного delta через zoom conversion;
4. offsetX и offsetY звучат как главные настройки, хотя это только состояние просмотра.

### 5.2 Смешение block geometry и viewport state

Сейчас в одном опыте смешаны:

1. реальная геометрия блока;
2. editor viewport;
3. scene camera.

Это нужно развести.

### 5.3 Непредсказуемая логика высоты

Пропорциональное изменение высоты сразу по всем breakpoint годится как вспомогательный automation path, но не как default UX.

Для tilda-like поведения правильнее:

1. меняешь height текущего breakpoint локально;
2. при необходимости отдельно жмёшь sync или apply to all.

---

## 6. Целевой UX

### 6.1 Пустой клик

Пустой клик по artboard или рядом с ним должен открывать Canvas mode.

Canvas mode показывает:

1. высоту artboard;
2. overflow;
3. фон;
4. grid settings;
5. breakpoint overrides;
6. команды focus на grid и bleed zones.

### 6.2 Zoom

Zoom остаётся, но становится вторичным.

Правила:

1. zoom это utility просмотра редактора;
2. zoom не должен ощущаться как изменение блока;
3. camera offsets не должны доминировать в интерфейсе;
4. reset view должен быть editor utility, а не основная block action.

### 6.3 Height Resize

Изменение высоты должно ощущаться как drag нижней границы artboard.

Правила:

1. handle и edge относятся к блоку, а не к viewport;
2. drag работает стабильно без зависимости от маленькой hit area;
3. default behavior меняет только активный breakpoint;
4. отдельный action может синхронизировать высоту на остальные breakpoint.

### 6.4 Свобода объектов

Нужно явно считать нормальными следующие кейсы:

1. объект частично выходит за grid;
2. объект лежит в bleed-секции artboard;
3. объект частично выходит за блок;
4. layer selection и reveal всё равно гарантируют доступ к объекту.

---

## 7. Этапы внедрения

### Этап A. Freeze migration direction

Цель:

1. зафиксировать artboard-first migration как канонический курс;
2. не вносить новые camera-centric улучшения до прохождения первого artboard pass.

Checkpoint:

1. отдельный checkpoint перед первым кодовым проходом.

Smoke:

1. план согласован;
2. команды понимают, что artboard это главная сущность;
3. текущие поля contract сопоставлены с новым UX-языком.

### Этап B. Artboard shell pass

Цель:

1. визуально и логически отделить workspace от artboard.

Что сделать:

1. подчеркнуть белый artboard как основную сущность;
2. сделать нижний край явной границей блока;
3. перевести stage card в язык artboard settings;
4. убрать лишний акцент с camera stats из product UI;
5. перенести camera diagnostics под debug или secondary panel.

Checkpoint:

1. после первого green smoke по visual shell без ломки save/reload.

Риск:

1. случайно сломать существующую world math при визуальной перестройке.

Smoke:

1. пользователь визуально понимает, где блок;
2. пустое место вокруг больше воспринимается как workspace, а не как загадочный canvas bug.

### Этап C. Height semantics pass

Цель:

1. сделать высоту блока локальной и предсказуемой.

Что сделать:

1. текущий drag по нижнему краю привязать к artboard semantics;
2. по умолчанию менять только текущий breakpoint;
3. добавить явный action apply to all breakpoints;
4. в sidebar показать inherited и overridden height.

Checkpoint:

1. после ручного save/reload smoke по desktop, tablet и mobile.

Риск:

1. поломать предыдущую логику с пропорциональным распространением height.

Smoke:

1. desktop height меняется локально;
2. tablet и mobile не меняются сами без явной команды;
3. apply to all работает отдельно и предсказуемо.

### Этап D. Overflow pass

Цель:

1. превратить overflow из stage option в полноценное поведение блока.

Что сделать:

1. довести editor semantics для visible, hidden и auto;
2. развести editor workarea accessibility и runtime clipping;
3. определить, нужен ли axis-specific overflow уже в первой волне;
4. расширить runtime/public rendering под overflowMode.

Checkpoint:

1. после отдельного editor/runtime parity smoke.

Риск:

1. сделать editor удобным, но получить другой runtime;
2. сделать runtime честным, но испортить editor usability.

Smoke:

1. object за границей блока ведёт себя предсказуемо в editor;
2. runtime повторяет задуманную семантику overflow.

### Этап E. Layers and sidebar V2

Цель:

1. довести управляемость блока до тильдового уровня.

Что сделать:

1. collapse and expand tree;
2. drag reorder в layers;
3. групповые действия над selection;
4. быстрые artboard actions в canvas sidebar;
5. clearer override indicators по breakpoint.

Checkpoint:

1. после стабильного layer smoke без рассинхрона selection.

Smoke:

1. слой можно спрятать и заблокировать;
2. порядок слоёв меняется из списка;
3. выделение в layers и на artboard не расходится.

---

## 8. Что делаем первым в коде

Первый кодовый проход должен быть узким.

Порядок:

1. Artboard shell pass;
2. локальная height semantics;
3. затем overflow runtime/editor parity;
4. только потом layers drag-reorder и deeper sidebar UX.

Почему именно так:

1. пока не станет понятен сам блок, остальной UX будет восприниматься криво;
2. высота блока для пользователя важнее, чем reorder layers;
3. overflow нужно стабилизировать после shell, а не до него.

---

## 9. Что пока не делаем

Чтобы не расползтись, в первый artboard-first цикл не включаем:

1. новые element types;
2. сложную auto-layout систему;
3. smart responsive automation;
4. deep runtime animation work;
5. расширение палитры beyond text, button, object, photo.

---

## 10. Критерий готовности первой волны

Первая artboard-first волна считается успешной, если одновременно верны все пункты:

1. пользователь воспринимает белую область как блок, а не как кусок сцены;
2. высота блока меняется предсказуемо и локально по breakpoint;
3. объект можно свободно выносить за grid и управлять им;
4. overflow имеет понятную block semantics;
5. save/reload не теряет stage settings;
6. editor и runtime не спорят о границах блока.