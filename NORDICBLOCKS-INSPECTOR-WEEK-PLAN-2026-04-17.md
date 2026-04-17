# NordicBlocks — Inspector Week Plan

> Дата: 2026-04-17
> Формат: недельный рабочий план по сборке одного канонического inspector-driven editor для NordicBlocks

---

## Легенда статусов

- 🟢 Сделано
- 🟡 В работе
- ⚪ Запланировано
- 🔴 Риск

---

## Цель недели

Собрать один канонический редактор NordicBlocks, где инспектор управляет блоком через единый контракт, а не через набор частных шаблонов и ручных исключений.

Жёсткая рамка недели:

1. В первой волне оставляем только `hero` и `faq`.
2. Не добавляем новые блоки.
3. Не плодим параллельные editor-shell ветки.
4. Не тащим новый framework как «решение само по себе».

---

## Статус на старт недели

### 🟢 Сделано до старта плана

1. Разобрана и удалена локальная грязь после неудачной попытки с чужим агентом.
2. Ветка возвращена к чистой базе `026fd84`.
3. Подтверждено, что foundation unified inspector уже есть в коде:
   - `InspectorRegistryBuilder`
   - `InspectorStateBuilder`
   - `block_editor_state`
4. Определена правильная проблема: не «не хватает Vue», а не завершён единый editor contract и generic inspector runtime.
5. Зафиксирована безопасная точка отката:
   - checkpoint commit `f5f6898`
   - snapshot `snapshot/20260417-151400`

### 🟡 В работе сейчас

1. День 1: freeze первой волны и закрытие старого block-flow editor path.

### ⚪ Ещё не начато

1. Сужение продукта до `hero` и `faq` в коде.
2. Выбор и фиксация одного канонического editor shell.
3. Единый block contract.
4. Manifest-driven inspector.
5. Control registry.
6. Generic panel renderer.

---

## План по дням

## День 1. Freeze и чистка продукта

**Статус:** 🟢 Сделано

### Что делаем

1. Фиксируем продуктовый скоуп первой волны: только `hero` и `faq`.
2. Убираем лишние блоки из пользовательского потока.
3. Подтверждаем один канонический entrypoint редактора.
4. Фиксируем один editor shell как единственный путь недели.

### Какие результаты должны быть к концу дня

1. В UI не мешаются лишние блоки.
2. `hero` и `faq` открываются через один и тот же editor shell.
3. В документации больше нет двусмысленности, какой editor считается каноном.

### Что сделано

1. Введена allowlist первой волны: только `hero` и `faq`.
2. Legacy-типы скрыты из списка блоков и из модального создания.
3. Прямое создание legacy-типов через POST закрыто на backend.
4. `block_edit` переведён на один активный shell для первой волны.
5. Старый editor-path убран из активного block-flow без удаления legacy-кода.

### Что трогаем

1. `NORDICBLOCKS-DEV.md`
2. `system/controllers/nordicblocks/backend/actions/block_edit.php`
3. список/источники типов блоков в `system/controllers/nordicblocks/`
4. `templates/admincoreui/controllers/nordicblocks/backend/editor_v2.tpl.php` или другой выбранный shell

### Риск дня

🔴 Если список блоков управляется из нескольких несвязанных мест, сначала собираем карту источников правды, а не начинаем точечные хаки.

---

## День 2. Единый block contract

**Статус:** ⚪ Запланировано

### Что делаем

1. Фиксируем каноническую форму contract для `content`, `design`, `layout`, `data`, `entities`, `bindings`.
2. Приводим `hero` и `faq` к одному shape.
3. Убираем лишние block-specific расхождения в нормализации.

### Какие результаты должны быть к концу дня

1. `hero` и `faq` отдаются в одном формате.
2. Editor state и runtime больше не спорят о shape данных.
3. Контракт можно описать отдельно от admin HTML.

### Что трогаем

1. `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
2. `system/controllers/nordicblocks/backend/actions/block_editor_state.php`
3. `system/controllers/nordicblocks/libs/BindingMapper.php`

### Риск дня

🔴 Если для `hero` и `faq` нет честного минимального общего слоя, нельзя маскировать это условными ветками в шаблоне.

---

## День 3. Manifest-слой редактора

**Статус:** ⚪ Запланировано

### Что делаем

1. Вводим manifest как описание editable entities и controls.
2. Делаем manifest для `hero`.
3. Делаем manifest для `faq`.
4. Оставляем registry/state foundation центральной точкой правды.

### Какие результаты должны быть к концу дня

1. Блок описывается декларативно.
2. Новый UI-слой опирается на manifest, а не на частные шаблоны по типу блока.

### Что трогаем

1. `system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php`
2. `system/controllers/nordicblocks/libs/InspectorStateBuilder.php`
3. manifest-описания для `hero` и `faq`

### Риск дня

🔴 Если manifest начнёт дублировать низкоуровневую PHP-логику, значит мы описываем не тот уровень абстракции.

---

## День 4. Control registry

**Статус:** ⚪ Запланировано

### Что делаем

1. Собираем общий набор controls: `text`, `textarea`, `select`, `toggle`, `color`, `number`, `range`, `media`, `url`, `repeater`.
2. Убираем знания о конкретном block type из самих controls.
3. Выделяем слой control rendering внутри одного общего shell.

### Какие результаты должны быть к концу дня

1. Inspector умеет собирать controls по manifest.
2. В шаблоне не появляются ветки вида `if block_type === ...` для обычного рендеринга.

### Что трогаем

1. `templates/admincoreui/controllers/nordicblocks/backend/editor_v2.tpl.php`
2. клиентский runtime общего inspector shell
3. общие helper-слои editor UI

### Риск дня

🔴 Самая опасная точка дня — `repeater`. Если сделать его block-specific, архитектура снова расползётся.

---

## День 5. Generic panel renderer

**Статус:** ⚪ Запланировано

### Что делаем

1. Собираем общую механику вкладок `content`, `design`, `layout`, `data`.
2. Отделяем логику доступности/видимости панелей от их отрисовки.
3. Делаем shell тонким renderer-слоем.

### Какие результаты должны быть к концу дня

1. Inspector становится generic renderer.
2. Block logic приходит через contract + manifest.
3. Shell не знает деталей `hero` и `faq` напрямую.

### Что трогаем

1. `system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php`
2. `system/controllers/nordicblocks/libs/InspectorStateBuilder.php`
3. `templates/admincoreui/controllers/nordicblocks/backend/editor_v2.tpl.php`

### Риск дня

🔴 Если shell знает структуру блока глубже, чем через manifest, generic renderer ещё не собран.

---

## День 6. Полный перевод hero и faq

**Статус:** ⚪ Запланировано

### Что делаем

1. Полностью переводим `hero` на новый рельс.
2. Полностью переводим `faq` на тот же рельс.
3. Смотрим, какие block-specific admin templates становятся лишними.

### Какие результаты должны быть к концу дня

1. `hero` и `faq` реально живут на одной архитектуре.
2. Открытие, редактирование, сохранение и повторное открытие проходят по одному сценарию.
3. Лишние отдельные editor templates можно безопасно выводить из канона.

### Что трогаем

1. `hero`- и `faq`-специфичные editor definitions
2. `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`
3. общий shell и связанный runtime

### Риск дня

🔴 Если `faq` переводится только через новые исключения, значит проблема не в блоке, а в сыром control registry.

---

## День 7. Smoke, cleanup, freeze v1

**Статус:** ⚪ Запланировано

### Что делаем

1. Проходим полный smoke по `hero` и `faq`.
2. Вычищаем legacy-ветки, которые больше не участвуют в потоке.
3. Обновляем канонические документы как уже подтверждённый runtime-факт.
4. Фиксируем definition of done для первой волны.

### Какие результаты должны быть к концу дня

1. Можно безопасно добавлять третий блок без переписывания редактора.
2. Первая волна зафиксирована как стабильная продуктовая база.

### Что трогаем

1. `NORDICBLOCKS-DEV.md`
2. `docs/nordicblocks/UNIFIED-INSPECTOR-MIGRATION-PLAN.md`
3. legacy editor-ветки, которые реально вышли из потока

### Риск дня

🔴 Если после недели третий блок всё ещё требует отдельную архитектуру, значит v1 ещё не готов к расширению.

---

## Definition of Done недели

### 🟢 Неделя считается завершённой, когда одновременно выполнено всё ниже

1. В первой волне продукта реально остались только `hero` и `faq`.
2. Есть один канонический editor shell.
3. Есть один канонический block contract.
4. Inspector рендерится по manifest и control registry.
5. Нет обязательных block-specific admin веток для `hero` и `faq`.
6. Preview и повторное открытие показывают те же данные после сохранения.
7. После этого можно добавлять новый блок без новой редакторской архитектуры.

---

## Правило недели

### 🔴 Запрещено до завершения этой недели

1. Добавлять новые блоки.
2. Делать второй параллельный editor.
3. Раздувать shell условными ветками под каждый block type.
4. Подменять архитектурную задачу спором о framework.