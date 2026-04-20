# Design Block Geometry Stabilization Plan V1

Дата: 2026-04-20

## 1. Цель текущего этапа

Зафиксировать физику редактора как изолированное world/viewport ядро до любых изменений storage, normalizer и SSR.

Текущий stop-rule:

1. сначала доказываем математику;
2. потом переносим interaction engine целиком на стабильное ядро;
3. только после этого обсуждаем scene storage.

## 2. Что входит в этап

Обязательные deliverables текущего прохода:

1. отдельный geometry-core модуль без DOM и без шаблонов;
2. автономные tests на pure functions;
3. инварианты zoom/pointer/drag/resize, зафиксированные тестами;
4. debug surface только под dev flag;
5. editor shell использует это ядро как adapter, а не как источник новой математики внутри DOM.

## 3. Что не входит в этап

На этом этапе запрещено:

1. менять backend endpoints;
2. менять normalizer;
3. менять storage contract;
4. внедрять canonical scene/layout persistence;
5. трогать SSR/runtime рендер;
6. вводить breakpoint layout maps как persisted contract.

## 4. Каноническая модель геометрии

Источник истины:

1. world coordinates;
2. viewport camera;
3. pure projection world -> screen;
4. pure reverse conversion screen -> world.

Канонические формулы:

$$
screenX = (worldX - offsetX) \cdot zoom
$$

$$
screenY = (worldY - offsetY) \cdot zoom
$$

$$
worldX = \frac{screenX}{zoom} + offsetX
$$

$$
worldY = \frac{screenY}{zoom} + offsetY
$$

Правило zoom:

1. zoom влияет только на renderer и pointer conversion;
2. zoom не должен менять world coordinates узлов;
3. drag и resize обязаны работать через world delta, а не через screen pixels.

## 5. Обязательные тесты

Минимальный geometry smoke suite:

1. world -> screen;
2. screen -> world;
3. drag при zoom = 1;
4. drag при zoom = 0.5;
5. drag при zoom = 2;
6. resize при zoom != 1.

## 6. Критические инварианты

Модель считается корректной только если тестами подтверждены все три условия:

1. после drag world-координаты не прыгают при смене zoom;
2. screen pointer после zoom, anchored в ту же screen point, попадает в тот же world point;
3. world point элемента не дрейфует при масштабировании, если zoom anchored к его screen-проекции.

Практический смысл:

1. drag должен давать одинаковую физику на $zoom = 0.5$, $1$, $2$;
2. resize должен зависеть от world delta, а не от raw screen px;
3. pointer hit-test обязан сначала конвертироваться в world.

## 7. Текущий implementation contour

На текущем проходе зафиксирован следующий контур:

1. pure module: design-block-geometry-core.js;
2. pure tests: Node built-in test runner;
3. editor shell использует geometry-core через thin wrappers;
4. debug API включается только через dev flag `nb_debug_geometry=1`.

## 8. Acceptance gate

Этап считается закрытым только если одновременно выполнено:

1. drag одинаково предсказуем при любом zoom;
2. resize не зависит от raw screen scale;
3. pointer conversion в world доказана тестами;
4. debug surface не торчит в обычном admin runtime;
5. никаких backend/storage/SSR изменений не потребовалось.

## 9. Что идёт после стабилизации

Следующие шаги, но не в этом проходе:

1. добить interaction adapters вокруг стабильного geometry-core;
2. собрать selection engine поверх world hit-test;
3. ввести NodeType + capability map;
4. только после этого обсуждать scene storage.

## 10. Entity system direction

После geometry gate editor должен развиваться как:

1. scene graph;
2. behavior system;
3. UI layer.

Это означает:

1. Node-объект первичен, а не DOM узел;
2. type определяет capabilities и renderer, но не координатную модель;
3. inspector должен читать capabilities, а не разрастаться через хаотичный switch по type.