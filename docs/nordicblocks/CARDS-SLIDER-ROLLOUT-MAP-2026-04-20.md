# Cards Slider Rollout Map — 2026-04-20

## Цель

Этот документ фиксирует discovery-first rollout-map для `cards_slider` до scaffold и любых кодовых правок.

Его задача:

1. перевести решение `делаем все 3 slider blocks` в контролируемый первый implementation step;
2. отделить family-level требования от конкретного стартового блока;
3. заранее перечислить shared gaps, чтобы не сорваться в block-private implementation.

## Почему первым открывается именно cards_slider

`cards_slider` выбран первым block type slider family по следующим причинам:

1. лучше всего укладывается в уже доказанный card/content contract NordicBlocks;
2. безопаснее для первого slider rollout, чем hero-specific и stories-specific композиции;
3. даёт reusable runtime для navigation, pagination, track и swipe mechanics;
4. создаёт базу, на которой можно потом строить `hero_slider` и `stories_slider` без нового механического ядра.

## Жёсткие требования, уже принятые до кода

До любых implementation шагов для `cards_slider` уже приняты следующие правила:

1. блок должен быть full-width, с viewport `100%` ширины секции;
2. блок обязан иметь demo content по умолчанию;
3. блок обязан поддерживать данные InstantCMS через `content_list`;
4. mobile swipe является обязательным baseline behavior;
5. shared rollout path должен оставаться `spec -> shared vocabulary -> scaffold -> validator -> implementation -> smoke`.

## Что уже можно переиспользовать из текущей платформы

### 1. Shared product/platform contour

У платформы уже есть сильная основа, которую нужно переиспользовать, а не обходить:

1. manifest-first block architecture;
2. contract-first storage model;
3. shared editor shell `editor_hero_v2`;
4. shared content/design/layout/data tabs;
5. scaffold + validator process для managed block types;
6. family-first docs discipline.

### 2. Reusable vocabulary from existing families

Из уже выпущенных families для `cards_slider` особенно полезны:

1. news family card contract и repeater discipline;
2. shared entities `section`, `header`, `title`, `subtitle`, `primaryButton`, `secondaryButton`;
3. item typography patterns для карточек;
4. `manual + content_list` dual-mode data path;
5. shared CSS variable discipline для instance-scoped styling.

## Что ещё не готово и должно быть закрыто до scaffold

### 1. Shared slider vocabulary в inspector

До старта кода shared inspector ещё не зафиксировал family-wide vocabulary для slider mechanics.

Нужно заранее определить canonical entities и control groups для:

1. `viewport`;
2. `track`;
3. `slide`;
4. `navigation`;
5. `prevButton`;
6. `nextButton`;
7. `pagination`;
8. `progress`.

Без этого новый block type почти неизбежно потащит временные ручные ветки в shell.

### 2. Shared control vocabulary

До scaffold нужно определить, какие control groups будут общими для всей slider family:

1. slider layout controls;
2. navigation controls;
3. pagination controls;
4. motion/autoplay controls;
5. slide repeater/content controls;
6. data binding controls для `content_list`.

### 3. Runtime policy for full-width slider

До кода важно зафиксировать технически, что значит `full-width`:

1. section не получает boxed-width control в V1;
2. viewport рендерится как `100%` ширины блока;
3. rail mechanics не должны рассчитываться от произвольного narrow container;
4. public/runtime и backend canvas обязаны вести себя одинаково.

### 4. Runtime policy for mobile swipe

До кода нужно зафиксировать, что swipe:

1. не является experimental behavior;
2. входит в baseline runtime contract;
3. работает на backend canvas и public runtime одинаково по базовой механике;
4. не зависит от наличия autoplay.

## Что будет входить в первый implementation pass

Если идти дальше после закрытия discovery, первый implementation pass для `cards_slider` должен включать только один блок и только один управляемый контур.

### Входит:

1. отдельный implementation-grade spec для `cards_slider`;
2. shared slider vocabulary doc для inspector;
3. scaffold нового managed block type `cards_slider`;
4. validator pass;
5. реализация SSR/runtime/editor shell support;
6. editor smoke;
7. public smoke.

### Не входит:

1. одновременная реализация `hero_slider`;
2. одновременная реализация `stories_slider`;
3. новый private inspector shell;
4. тяжёлая animation studio логика;
5. video-first stories runtime.

## Предварительный порядок работ

Для `cards_slider` порядок должен быть таким:

1. считать family-level требования утверждёнными;
2. создать implementation-grade spec `CARDS-SLIDER-V1.md`;
3. зафиксировать shared slider vocabulary в отдельном документе;
4. сделать checkpoint перед кодом, потому что дальше пойдут изменения shell/runtime/contracts;
5. scaffold block type `cards_slider` через поддерживаемый profile;
6. прогнать validator;
7. только затем открывать controlled implementation и smoke.

## Что будет после cards_slider

После успешного rollout `cards_slider` открываем следующие два блока не с нуля, а как производные family wave:

1. `hero_slider` — следующий, если reusable slider mechanics подтвердятся в live;
2. `stories_slider` — третий, когда mobile-first path станет достаточно зрелым.

Это означает:

1. решение `делаем все 3 slider blocks` уже принято продуктово;
2. но implementation по-прежнему идёт по одному первому truthful block type, а не тремя параллельными незавершёнными ветками.