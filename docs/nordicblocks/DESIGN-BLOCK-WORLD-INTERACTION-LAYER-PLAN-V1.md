# Design Block World Interaction Layer Plan V1

Дата: 2026-04-20

## 1. Цель этапа

Построить поверх доказанного geometry-core отдельный world-based interaction layer без захода в storage, normalizer и SSR.

Этап считается следующим после geometry gate:

1. geometry-core остаётся каноническим источником projection math;
2. interaction layer отвечает за pointer orchestration, selection semantics и resize handle policy;
3. scene storage остаётся вне текущего прохода.

## 2. Что входит в этап

Обязательные deliverables:

1. отдельный pure interaction-core модуль без DOM и без template logic;
2. selection engine как чистые функции;
3. world hit-test без чтения DOM box geometry;
4. resize handle policy как чистая функция от node type и props;
5. pointer capture в shell как thin DOM adapter над pure interaction-core;
6. Node tests на selection, hit-test, resize handles и pointer capture session.

## 3. Что не входит в этап

На этом этапе запрещено:

1. менять storage contract;
2. менять normalizer;
3. внедрять persistence scene/layout maps;
4. трогать SSR/runtime render path;
5. добавлять новый backend API.

## 4. Каноническая граница ответственности

Geometry core отвечает за:

1. world <-> screen conversion;
2. zoom anchor;
3. pure drag/resize math.

Interaction core отвечает за:

1. selection normalization;
2. root selection filtering;
3. world hit-test;
4. selection bounds;
5. resize handle policy;
6. pointer capture session state.

Shell отвечает только за:

1. DOM event intake;
2. viewport rect origin для pointer conversion;
3. реальный setPointerCapture/releasePointerCapture;
4. render/update вызовы.

## 5. Acceptance gate

Этап считается закрытым, если одновременно выполнено:

1. selection больше не живёт как ad hoc logic, а опирается на pure interaction-core;
2. hit-test идёт только через world point и scene boxes;
3. resize handles определяются pure policy, а не render-only условием в shell;
4. drag/resize/pan держатся на pointer capture, а не на неявном глобальном поведении мыши;
5. pure tests доказывают selection/hit-test/handle contract;
6. geometry-core не пришлось расширять DOM-связным кодом.

## 6. Что идёт после этапа

Только после world interaction layer:

1. selection toolbar hardening;
2. richer resize behavior для group/media constraints;
3. capability-driven node behaviors;
4. затем обсуждение scene storage и persistence boundary.