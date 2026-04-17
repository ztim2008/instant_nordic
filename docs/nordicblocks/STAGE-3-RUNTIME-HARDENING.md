# NordicBlocks — Этап 3: adapter-aware runtime hardening

> Статус: реализовано 2026-04-17 как отдельный runtime-слой после закрытия `hero + content_item` и общего UI вкладки `Данные`.

---

## 1. Зачем выносить Stage 3 отдельно

Этап 3 нужен не для добавления нового block type, а для стабилизации уже поднятого runtime.

На 2026-04-17 в коде уже доказано, что один hydration pipeline умеет обслуживать:

1. `manual` сценарий;
2. `content_item` сценарий для `hero`;
3. `content_list` сценарий для `faq`.

Но это ещё не означает, что runtime доведён до безопасного production-состояния.

Главный недожатый слой сейчас такой:

1. cache semantics ещё не привязаны формально к adapter context;
2. preview/live parity есть по hydration pipeline, но не оформлена как жёсткий runtime-контракт;
3. smoke пока подтверждают гидрацию, но не закрывают поведение cache key и изоляцию контекста.

Именно поэтому Этап 3 фиксируем отдельным документом, а не оставляем одной короткой строкой в общем плане.

---

## 2. Текущее подтверждённое состояние

### Что уже правильно

1. `backend/actions/block_canvas.php` использует `hydrateBlockForRender(..., ['mode' => 'backend_canvas'])`.
2. `system/widgets/nordicblocks_block/widget.php` использует `hydrateBlockForRender(..., ['mode' => 'widget'])`.
3. `actions/view.php` использует `hydrateBlockForRender(..., ['mode' => 'legacy_view'])`.
4. `DataSourceResolver + BindingMapper + BlockPayloadHydrator` уже работают для single-record и list-record сценариев.

### Что ещё не дожато

1. В `actions/view.php` для dynamic blocks пока используется безопасный обходной режим: cache reuse отключается вместо полноценного adapter-aware cache key.
2. В `system/widgets/nordicblocks_block/widget.php` нет отдельного зафиксированного слоя cache identity для adapter-driven блоков.
3. Для preview/live нет отдельного формального документа, который жёстко задаёт одинаковую последовательность merge и правила поведения для adapter context.

Итог: сейчас runtime уже рабочий, но ещё не формально hardened.

---

## 3. Цель Этапа 3

Этап 3 считался выполненным только тогда, когда runtime начинает одинаково и предсказуемо работать не только по hydration, но и по cache semantics.

В коде для этого были закрыты 3 результата одновременно:

1. cache key учитывает adapter context и не может переиспользовать чужие данные на другой странице;
2. preview и live проходят один и тот же merge pipeline без расхождения правил;
3. smoke закрывают single-record и list-record сценарии не только на hydration, но и на уровне runtime context.

### Что реально реализовано

1. Добавлен отдельный helper `RenderCacheContext`, который собирает adapter-aware context для `manual`, `content_item` и `content_list`.
2. `modelNordicblocks` получил единый `buildRenderCacheProfile()` с расчётом namespace, block fingerprint, design version и adapter context hash.
3. `system/widgets/nordicblocks_block/widget.php` переведён на persistent SSR-cache с тем же cache profile.
4. `system/controllers/nordicblocks/actions/view.php` больше не использует старый safe bypass как основную схему и теперь строит cache key через тот же profile.
5. Runtime metadata для adapter-блоков расширена:
   - `content_item` теперь отдаёт `resolverMode`;
   - `content_list` теперь отдаёт `itemIds`, `sort`, `limit`.
6. Добавлен smoke `scripts/nordicblocks-runtime-cache-smoke.php`, который проверяет cache-context isolation и отсутствие регрессии для manual-first блока.

---

## 4. Что именно должен закрыть Этап 3

### 4.1 Adapter context identity

Нужен единый нормализованный слой runtime-контекста адаптера.

Минимально он должен уметь описывать:

1. `sourceType`
2. `sourceConfigHash`
3. `resultIdentity`
4. `contextScope`
5. `isDynamic`
6. `cacheEligible`

Смысл полей:

1. `sourceType` — какой режим реально работает: `manual`, `content_item`, `content_list`.
2. `sourceConfigHash` — хэш конфигурации источника, а не итогового HTML.
3. `resultIdentity` — идентичность реально полученного результата adapter runtime.
4. `contextScope` — от какого runtime-контекста зависит блок: виджет, legacy page, текущая запись, конкретный item id, список id и т.д.
5. `isDynamic` — признак data-driven поведения.
6. `cacheEligible` — можно ли безопасно использовать persistent cache в текущем режиме.

### 4.2 Что должно входить в adapter context

#### Для `manual`

1. adapter context может быть пустым;
2. такой блок не должен усложняться лишней data-логикой.

#### Для `content_item/current`

В identity должны входить:

1. `ctype`;
2. режим resolver-а `current`;
3. identity текущей записи или текущего page/content context.

Если runtime не может надёжно определить текущую запись, лучше временно отключить persistent cache для этого кейса, чем допустить reuse чужих данных.

#### Для `content_item/by_id`

В identity должны входить:

1. `ctype`;
2. resolver mode `by_id`;
3. `item_id`.

#### Для `content_item/latest`

В identity должны входить:

1. `ctype`;
2. resolver mode `latest`;
3. идентичность реально выбранной записи;
4. подпись query-конфигурации, если позже появятся дополнительные фильтры.

#### Для `content_list`

В identity должны входить:

1. `ctype`;
2. `filter`;
3. `sort`;
4. `limit`;
5. `offset`, если он используется;
6. подпись реально полученного набора записей.

Практическое правило: для списка важно фиксировать не только конфиг, но и identity результата, иначе можно получить reuse HTML после смены состава выборки.

### 4.3 Cache key

Этап 3 должен довести cache contract до такого уровня:

`block_id + block_updated_at + design_version + adapter_context_hash + render_surface`

Где:

1. `block_id` — идентичность самого блока;
2. `block_updated_at` — защита от устаревшего HTML после save;
3. `design_version` — защита от устаревшей дизайн-сборки;
4. `adapter_context_hash` — защита от подмены данных между разными страницами и сценариями;
5. `render_surface` — разделение хотя бы по основным режимам runtime, если у них отличаются правила кэширования.

### 4.4 Поведение по runtime-поверхностям

#### Backend preview

Правило:

1. preview обязан использовать тот же hydration/merge pipeline;
2. но не обязан использовать долгоживущий persistent SSR-cache.

Для preview важнее точность и симметрия с live по данным, чем reuse HTML между разными редактированиями.

#### Widget runtime

Правило:

1. widget должен получить formal adapter-aware cache semantics;
2. manual-first блоки не должны терять простоту;
3. dynamic block должен кэшироваться только когда его context identity определён безопасно.

#### Legacy public view

Правило:

1. текущий safe bypass для dynamic blocks нужно заменить на нормальный adapter-aware cache contract;
2. если безопасный cache key собрать нельзя, fallback остаётся bypass, но уже как осознанное исключение, а не как основная стратегия.

### 4.5 Preview/live parity

Этап 3 должен явно зафиксировать одинаковый порядок сборки payload:

1. чтение block contract;
2. normalizer;
3. source resolver;
4. binding mapper;
5. merge manual/bound/mixed;
6. render payload;
7. только после этого cache decision.

Критичное правило:

1. cache не должен менять сам результат merge;
2. cache решает только reuse готового render payload или HTML;
3. preview и live не могут расходиться по fallback-логике или empty behavior.

---

## 5. Минимальный implementation scope

Чтобы Stage 3 не расползся, фиксируем минимальный рабочий объём.

### Обязательно

1. Вынести сбор adapter context identity в отдельный helper/model-layer, а не держать разрозненно по runtime-веткам.
2. Использовать этот слой как минимум в:
   - `system/widgets/nordicblocks_block/widget.php`
   - `system/controllers/nordicblocks/actions/view.php`
3. Оставить `backend/actions/block_canvas.php` на том же merge pipeline, даже если preview остаётся без persistent cache.
4. Проверить, что `manual-first` блоки продолжают рендериться без лишней adapter-нагрузки.

### Желательно, но не обязательно для закрытия Stage 3

1. Request-level memoization для повторного рендера одного и того же блока в одном запросе.
2. Отдельная служебная структура runtime debug/meta для удобной диагностики cache-context.

### Не входит в Этап 3

1. Новый list-output block.
2. Расширение библиотеки блоков.
3. Новые UI-вкладки или redesign inspector-а.
4. Полноценный performance-tuning всего NordicBlocks.

---

## 6. Какие файлы с высокой вероятностью затронет реализация

1. `system/controllers/nordicblocks/actions/view.php`
2. `system/widgets/nordicblocks_block/widget.php`
3. `system/controllers/nordicblocks/model.php`
4. один новый helper/lib слой для adapter context hash или render context identity
5. при необходимости `system/controllers/nordicblocks/backend/actions/block_canvas.php` только для выравнивания runtime metadata, а не для отдельной preview-логики

Техническое правило: Stage 3 не должен размазывать cache-логику по `render.php` блоков.

---

## 7. Smoke и проверка

На 2026-04-17 уже есть рабочая база:

1. `scripts/nordicblocks-hero-content-item-smoke.php`
2. `scripts/nordicblocks-faq-content-list-smoke.php`
3. `scripts/nordicblocks-flow-smoke.php`

Этап 3 должен либо расширить эти smoke, либо добавить отдельный cache-oriented smoke, который проверяет следующее:

1. `hero + content_item` продолжает гидрироваться теми же данными после hardening.
2. `faq + content_list` продолжает гидрироваться теми же данными после hardening.
3. один и тот же dynamic block не получает одинаковый cache identity для разных adapter context.
4. `manual-first` блок не ломается и не получает лишнюю зависимость от adapter metadata.
5. preview/live используют один и тот же hydrated payload shape.

Минимальный практический набор проверок:

1. single-record smoke;
2. list-record smoke;
3. cache-key isolation smoke;
4. общий flow smoke.

---

## 8. Основные риски

### Риск 1. Чужие данные из cache

Если `current item` или `content_list` не включить в context hash, один и тот же блок может показать неправильную запись на другой странице.

### Риск 2. Ложная parity

Если preview и live оба вызывают `hydrateBlockForRender`, но потом расходятся по merge/cache-веткам, внешне будет казаться, что runtime общий, хотя по факту он расходится.

### Риск 3. Перегрузка manual-first сценария

Если Stage 3 начнёт тащить сложную adapter-логику даже в обычные статичные блоки, это ухудшит простоту продукта без реальной пользы.

### Риск 4. Преждевременный уход в performance-микрооптимизации

На этом этапе важнее не максимальная скорость любой ценой, а корректная изоляция данных и предсказуемость runtime.

---

## 9. Definition of Done

Этап 3 можно считать закрытым только если одновременно выполнены условия:

1. `widget` и `legacy view` используют adapter-aware cache semantics вместо текущей частичной схемы.
2. Для `content_item` и `content_list` cache key учитывает реальный adapter context.
3. Preview и live формально проходят один и тот же merge pipeline.
4. `manual-first` блоки не регрессируют.
5. Smoke подтверждают single-record, list-record и cache-context isolation сценарии.
6. После этого можно без архитектурного долга переходить к отдельному content-list block class.

---

## 10. Порядок внедрения

1. Сначала вынести и зафиксировать единый adapter context builder.
2. Потом подключить его к `legacy view` и `widget`.
3. Затем дожать smoke на cache-context isolation.
4. Только после этого переходить к следующему block class для вывода списка контента.

Итоговое правило Stage 3:

сначала не расширяем библиотеку, а доводим ядро runtime до состояния, в котором один и тот же data-driven блок безопасно живёт в production без подмены данных и без скрытого расхождения preview/live.