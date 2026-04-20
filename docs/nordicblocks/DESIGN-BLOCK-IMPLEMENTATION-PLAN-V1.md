# NordicBlocks Design Block Implementation Plan V1

Дата: 2026-04-20

## 1. Цель плана

Этот план переводит design block docs в конкретный порядок внедрения.

Он опирается на 3 уже зафиксированных документа:

1. `DESIGN-BLOCK-MODE-V1.md`
2. `DESIGN-BLOCK-TECH-SPEC-V1.md`
3. `DESIGN-BLOCK-RUNTIME-CONTRACT-V1.md`
4. `DESIGN-BLOCK-EDITOR-SHELL-V1.md`
5. `DESIGN-BLOCK-INSTANTCMS-INTEGRATION-RULES-V1.md`

Главное правило плана:

design block внедряется как отдельная ветка внутри NordicBlocks, но не ломает current block CRUD, widget placement и SSR runtime.

На всех стадиях `design_block` нужно проверять как отдельный editor engine, а не как расширение shared inspector shell.

---

## 2. Scope первой реализации

Первая реализация должна закрыть только manual-first freeform секцию.

В scope входят:

1. новый block type `design_block`;
2. contract normalizer;
3. render payload builder;
4. `render.php` для `design_block`;
5. отдельный backend editor shell;
6. palette v1;
7. save + live canvas loop;
8. штатные image/icon/file picker integrations.

В scope не входят:

1. data bindings для design block;
2. complex animation system beyond CSS-only controls;
3. multi-section page builder;
4. template marketplace;
5. arbitrary custom code nodes.

---

## 3. Delivery guardrails

Перед каждым рискованным этапом, который затронет backend actions, renderer и contract save path, нужен checkpoint.

Особенно перед этапами:

1. расширение `BlockContractNormalizer`;
2. изменение `block_edit.php`;
3. изменение `block_save.php`;
4. добавление `blocks/design_block/render.php`.

SQL на первом этапе не нужен, потому что `design_block` живёт в существующей таблице `cms_nordicblocks_blocks`.

---

## 4. Порядок внедрения

### Stage 0. Freeze docs and file map

Задача:

1. закрыть spec stack;
2. зафиксировать file map;
3. не спорить о palette/runtime/editor shape в момент кода.

Результат:

1. docs считаются canonical source of truth.

### Stage 1. Contract support in backend core

Задача:

1. научить core contract dispatcher принимать `design_block`;
2. ввести dedicated normalizer.

Основные файлы:

1. `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
2. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
3. package mirror equivalents, если нужен mirror sync

Что делаем:

1. расширяем `supportsContractType()`;
2. добавляем dispatch ветку для `design_block`;
3. реализуем allowlist normalizer для stage + elements + common style props.

Критерий готовности:

1. `block_save` может принять и сохранить design block contract без schema-field path.

### Stage 2. Render payload and SSR renderer

Задача:

1. собрать server-side payload builder;
2. поднять `render.php` для freeform секции.

Основные файлы:

1. `system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php`
2. `system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php`
3. `system/controllers/nordicblocks/libs/DesignBlockElementRenderer.php`
4. `system/controllers/nordicblocks/blocks/design_block/render.php`

Что делаем:

1. строим payload из normalized contract;
2. генерируем HTML tree и CSS по breakpoints;
3. не тащим editor JS в public runtime.

Критерий готовности:

1. один и тот же design block можно отрендерить через widget и preview canvas.

### Stage 3. Backend actions and editor routing

Задача:

1. встроить freeform mode в existing backend flow;
2. не заводить новый пользовательский CRUD contour.

Основные файлы:

1. `system/controllers/nordicblocks/backend/actions/block_edit.php`
2. `system/controllers/nordicblocks/backend/actions/block_save.php`
3. `system/controllers/nordicblocks/backend/actions/block_design_state.php`
4. `system/controllers/nordicblocks/backend/actions/block_design_canvas.php`

Что делаем:

1. `block_edit` dispatch-ит тип `design_block` в dedicated shell;
2. `block_save` сохраняет title + contract;
3. `block_design_state` возвращает bootstrap JSON;
4. `block_design_canvas` отдаёт standalone preview через same renderer.

Критерий готовности:

1. весь backend flow работает через стандартные admin actions NordicBlocks.

### Stage 4. Admin template and JS shell

Задача:

1. поднять freeform editor shell;
2. завести JS state и sidebar donor-level структуры.

Основные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/editor_design_block.tpl.php`
2. `templates/admincoreui/controllers/nordicblocks/backend/js/design-block-editor.js`
3. `templates/admincoreui/controllers/nordicblocks/backend/css/design-block-editor.css`

Что делаем:

1. topbar с title/save/place/breakpoints;
2. canvas в том же DOM без iframe;
3. sidebar cards `Дизайн-блок`, `Холст`, `Секция`, `Слои`, `Свойства элемента`;
4. JS store с document/ui/transient state;
5. прямое inline-редактирование текста, drag/drop и 12-колоночную grid overlay.

Критерий готовности:

1. editor можно открыть, выделить элемент, изменить свойства и сохранить contract.

### Stage 5. Live canvas interaction loop

Задача:

1. стабилизировать живой canvas в одной странице редактора;
2. сохранить save/live parity при отсутствии iframe как главного UX-контура.

Основные файлы:

1. `editor_design_block.tpl.php`
2. `block_design_canvas.php`
3. `design-block-editor.js`

Что делаем:

1. local DOM render для stage и элементов;
2. selection sync между холстом, слоями и property panel;
3. drag/drop + grid snap + guide lines;
4. save loop без отдельного preview-этапа.

Критерий готовности:

1. изменение на холсте и в sidebar предсказуемо отражается сразу в editor canvas и потом в live после save.

### Stage 6. Picker integration and asset rules

Задача:

1. подключить штатные InstantCMS picker-механики;
2. не делать собственный media manager.

Основные файлы:

1. `design-block-editor.js`
2. template helpers / picker endpoints if needed

Что делаем:

1. image picker;
2. icon picker;
3. file picker for svg/video;
4. normalized path storage.

Критерий готовности:

1. картинки, иконки, SVG и видео вставляются через стандартный InstantCMS UX.

### Stage 7. Live smoke and hardening

Задача:

1. доказать, что mode рабочий не только в editor, но и в live widget placement.

Проверяем:

1. создание design block;
2. save contract;
3. preview canvas;
4. placement через `nordicblocks_block`;
5. публичный рендер без PHP warnings;
6. mobile/tablet/desktop CSS overrides;
7. image/icon/video/svg rendering.

Критерий готовности:

1. manual-first design block проходит end-to-end smoke в editor и live.

---

## 5. File map v1

### 5.1 New files

1. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
2. `system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php`
3. `system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php`
4. `system/controllers/nordicblocks/libs/DesignBlockElementRenderer.php`
5. `system/controllers/nordicblocks/backend/actions/block_design_state.php`
6. `system/controllers/nordicblocks/backend/actions/block_design_canvas.php`
7. `system/controllers/nordicblocks/blocks/design_block/render.php`
8. `templates/admincoreui/controllers/nordicblocks/backend/editor_design_block.tpl.php`
9. `templates/admincoreui/controllers/nordicblocks/backend/js/design-block-editor.js`
10. `templates/admincoreui/controllers/nordicblocks/backend/css/design-block-editor.css`

### 5.2 Existing files to update

1. `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
2. `system/controllers/nordicblocks/backend/actions/block_edit.php`
3. `system/controllers/nordicblocks/backend/actions/block_save.php`
4. `system/widgets/nordicblocks_block/widget.php` — только если потребуется minor wiring

---

## 6. Definition of Done

Implementation plan v1 считается принятым, если одновременно выполнены условия:

1. порядок внедрения не ломает current NordicBlocks CRUD;
2. сначала поднимается contract/runtime, потом editor shell, а не наоборот;
3. `design_block` не получает отдельный placement path;
4. manual-first stage можно довести до live smoke без data-binding слоя;
5. roadmap достаточно конкретен, чтобы переходить к коду по этапам без нового раунда продуктовых споров.