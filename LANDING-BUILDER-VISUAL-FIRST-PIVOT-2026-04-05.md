# Product Pivot: Visual-First Nordic Builder

## Зачем этот документ

Этот документ фиксирует смену курса разработки.

Он нужен, чтобы дальше не спорить о том, куда должен развиваться Нордик: в еще один экран настроек шаблона или в настоящий визуальный конструктор сайтов.

Ответ зафиксирован:

Нордик дальше развивается как `visual-first builder`, а не как `form-first theme configurator`.

Дополнительная фиксация после продуктовой проверки:

финальная взрослая архитектура строится не вокруг экрана `Дизайн сайта` и не вокруг текущего `landingbuilder` как конечной формы продукта.

Канонический курс теперь такой:

`InstantCMS 2 backend -> nordic runtime template -> design system / global defaults -> visual builder workspace -> component library -> widget/data adapter layer`

Текущий `landingbuilder` в этом репозитории дальше считается переходным мостом и полигоном для проверки runtime, canvas, theme vars и schema-loop, а не окончательной продуктовой границей.

## 1. Что оказалось ошибочным в предыдущем повороте

Предыдущий курс слишком рано сделал центром продукта отдельный экран `Дизайн сайта`.

Из-за этого получилось следующее:

1. пользователь сначала меняет пресеты в форме, а результат видит только после сохранения;
2. центр тяжести уходит из страницы в экран настроек;
3. визуальная работа заменяется выбором готовых preset-ов;
4. builder начинает ощущаться как re-skinned `modern/options`, а не как конструктор сайта;
5. сайты рискуют становиться похожими друг на друга из-за работы только через глобальные пресеты.

## 2. Новый главный принцип

Пользователь должен менять то, что он видит, прямо там, где он это видит.

Канонический маршрут:

`открыть страницу -> видеть preview -> кликнуть в нужную область -> изменить стиль -> сразу увидеть результат -> сохранить`

Если для типового действия нужен переход на отдельный экран настроек, значит маршрут выбран неправильно.

## 3. Новый порядок ответственности экранов

### 3.1. Canvas

Canvas теперь главный продуктовый экран.

Он отвечает за:

1. визуальное редактирование страницы;
2. выбор страницы, секции, колонки и элемента кликом по preview;
3. live styling page-level, section-level и block-level;
4. device preview;
5. сохранение того же результата в preview и frontend.

### 3.2. Live Inspector

Inspector становится главным инструментом работы со стилем.

Он должен быть не формой настроек компонента, а context sidebar.

Базовые режимы:

1. `Страница`;
2. `Секция`;
3. `Элемент`;
4. `Виджет`;
5. позже `Shell zone`.

### 3.3. Global Style Defaults

Экран `Дизайн сайта` сохраняется, но его роль меняется.

Он больше не главный маршрут.

Он нужен только для редких site-wide defaults:

1. стартовая палитра;
2. дефолтная типографика;
3. контейнеры по умолчанию;
4. базовые кнопки и карточки;
5. возможно import/export preset-ов.

### 3.4. Shell Builder

Shell Builder остается продуктом, но уходит в advanced/system слой.

Он отвечает за структуру, а не за ежедневную стилизацию.

То есть:

1. header/footer/menu placement;
2. hero slot;
3. before/after content zones;
4. sidebar composition;
5. assignment rules.

### 3.5. Взрослая архитектура поверх InstantCMS

После дополнительной продуктовой оценки фиксируется такая взрослая модель:

1. `InstantCMS 2` остается backend-основанием: routing, content types, users, permissions, widgets, SEO и системные страницы;
2. `nordic` становится runtime template-слоем, который рендерит shell, header, footer, slot-зоны и итоговый frontend;
3. `Design System / Global Defaults` становится каноническим token-layer, а не главным ежедневным экраном;
4. `Visual Builder Workspace` становится главным продуктовым экраном ежедневной работы;
5. `Component Library` становится источником контролируемых sections, blocks и patterns;
6. `Widget/Data Adapter Layer` переводит системные widgets и контент InstantCMS в предсказуемые builder nodes.

Ключевой вывод:

мы строим не улучшенный configurator шаблона, а отдельный visual-builder слой над InstantCMS.

## 4. Что берем из старых референсов

### 4.1. Из instyler

Берем:

1. click-to-pick модель;
2. grouped field panels;
3. live preview mindset;
4. responsive editing modes;
5. field registry thinking.

Не берем:

1. raw selector storage как основную модель;
2. YAML persistence;
3. iframe-centric architecture как основной editing loop;
4. глобальную CSS-свалку как единственный output.

### 4.2. Из inthemer

Берем только служебные паттерны:

1. actions organization;
2. language separation;
3. revision thinking.

Не берем:

1. form-first UX;
2. tree/form editor как центр продукта;
3. server-rendered slow loop как основной режим работы.

## 5. Как теперь должна выглядеть первая бета-линия

### Phase 1. Live page/section styling

Минимум:

1. page-like canvas preview;
2. page selection по холсту;
3. section selection по холсту;
4. page-level live theme vars;
5. section-level background, spacing, container, tone и style preset с мгновенным ответом на canvas.

### Phase 2. Block-level styling

Минимум:

1. hero block;
2. text block;
3. button block;
4. card block.

### Phase 3. Global defaults simplification

Минимум:

1. сократить экран `Дизайн сайта`;
2. убрать из него повседневные ожидания пользователя;
3. оставить только global defaults.

### Phase 4. Shell participation visibility

Минимум:

1. показывать effective shell внутри canvas;
2. визуально объяснить shell zones;
3. оставить отдельный Shell Builder только для advanced случаев.

## 6. Что теперь считается успешным продуктовым результатом

Успех теперь определяется не количеством отдельных экранов и не числом пресетов.

Успех определяется тем, что пользователь:

1. быстро понимает, куда нажать;
2. меняет страницу, видя её перед собой;
3. не теряется между shell, design и canvas;
4. получает тот же результат в canvas, preview и live runtime;
5. может собрать лендинг без ощущения, что он работает в настройках CMS.

## 7. Жесткое ограничение на следующие решения

Если следующая идея снова двигает пользователя в отдельную форму вместо работы на canvas, такую идею считаем подозрительной по умолчанию.

Если следующий control нельзя объяснить фразой `ты видишь это здесь и меняешь это здесь же`, значит он не должен быть центральным в продукте.

## 8. Что это означает для текущего репозитория

1. текущий `landingbuilder` не надо дальше раздувать как финальный form-first продукт;
2. из текущей реализации нужно переиспользовать только уже доказавшие пользу части: schema persistence, preview/runtime loop, theme vars pipeline, package discipline и canvas interaction model;
3. новые документы и новые кодовые решения должны вести к boundary отдельного visual-builder компонента, даже если переходный код пока живет в текущей папке;
4. `Дизайн сайта` и другие form-first экраны можно держать только как secondary или bridge-слой;
5. любое новое решение должно усиливать guided visual assembly: `страница -> секции -> блоки -> элементы`.