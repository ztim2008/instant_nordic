# NordicBlocks Design Block Sequence V1 Plan

Дата: 2026-04-23
Checkpoint: `snapshot/20260423-065644`

## Цель

Сделать tilda-like пошаговую анимацию как отдельный orchestration layer поверх уже существующей single-element motion модели.

Главное правило:

не смешивать sequence и базовую motion-анимацию в один режим.

---

## Статусы

### ✅ Сделано

1. Создан checkpoint перед sequence-веткой.
2. Зафиксировано решение, что sequence v1 живёт отдельно от базовой анимации.
3. Подготовлен отдельный contract doc `DESIGN-BLOCK-SEQUENCE-CONTRACT-V1.md`.
4. Подтверждена текущая runtime-база: normalizer, SSR motion attrs, CSS motion vars и public runtime activation уже существуют.
5. Добавлены sequence props в server-side normalizer.
6. Добавлены `data-sequence-*` SSR attrs в element renderer.
7. Public runtime в `design_block/render.php` расширен до sequence orchestration без удаления existing single-element motion.
8. Editor serializer allowlist дополнен sequence keys, чтобы save/reload не выкидывал новый contract до появления inspector UI.

### 🚧 Делаем

1. Проверяем, что old blocks без sequence работают как раньше.
2. Готовим live smoke для single motion, sequence group и mixed block.

### 📝 Запланировано

1. Добавить sequence vocabulary в inspector отдельной секцией после runtime readiness.
2. Сделать editor defaults и quick-fill сценарии для hero sequence.
3. Прогнать live smoke: single motion, sequence group, mixed block.
4. Отдельно решить v1.1 для group/container-driven sequencing.

---

## Порядок работ

### Stage 1. Contract

Цель:

1. завести allowlist sequence props;
2. не ломать старые payloads;
3. сохранить default-safe поведение `sequenceMode = none`.

Файлы:

1. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
2. package mirror

Критерий:

1. save/reload сохраняет sequence props без участия UI.

### Stage 2. SSR attrs

Цель:

1. добавить `data-sequence-*` attrs в runtime HTML;
2. не менять существующий motion data contract.

Файлы:

1. `system/controllers/nordicblocks/libs/DesignBlockElementRenderer.php`
2. package mirror

Критерий:

1. sequence metadata приходит в DOM и доступна runtime script.

### Stage 3. Runtime orchestration

Цель:

1. сгруппировать элементы по `sequenceId`;
2. вычислять delay по шагам;
3. запускать sequence как единую логическую пачку.

Файлы:

1. `system/controllers/nordicblocks/blocks/design_block/render.php`
2. package mirror

Критерий:

1. sequence и single motion работают параллельно без регрессий.

### Stage 4. Inspector UI

Цель:

1. добавить отдельную секцию `Последовательность`;
2. не смешивать её с `Анимация`;
3. дать пользователю понятный mental model.

Файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/design-block-editor.js`
2. package mirror

Критерий:

1. sequence можно настроить полностью через inspector.

---

## Guardrails

1. Не ломать existing `motionTrigger/motionPreset` path.
2. Не делать timeline editor в v1.
3. Не смешивать sequence trigger и base motion trigger в один field.
4. Не трогать current hover/runtime slice без необходимости.
5. После runtime-изменений обязателен live smoke на размещённом блоке.

---

## Definition of Done

### ✅ Готово, когда

1. element without sequence ведёт себя как раньше;
2. sequence group стартует по шагам в правильном порядке;
3. `motionDelay + sequenceStep * sequenceGap` реально отражается в runtime;
4. preview/live parity сохранена;
5. inspector показывает sequence как отдельный слой настроек.
