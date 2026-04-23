# NordicBlocks Design Block Sequence Contract V1

Дата: 2026-04-23

## 1. Цель документа

Этот документ фиксирует отдельный sequence-layer для `design_block`.

Главное правило:

sequence v1 не заменяет и не переписывает текущую single-element motion модель. Он живёт поверх неё как orchestration layer и отвечает только за порядок, группировку и запуск нескольких уже существующих motion-анимаций.

Иными словами:

1. текущие поля `motionTrigger`, `motionPreset`, `motionDuration`, `motionDelay`, `motionEasing`, `motionAmount` остаются базовой анимацией элемента;
2. sequence-поля управляют тем, как несколько элементов запускаются вместе;
3. element motion и sequence не смешиваются в один набор свойств.

---

## 2. Граница ответственности

### 2.1 Что остаётся в базовой motion модели

Базовая motion модель по-прежнему отвечает за:

1. пресет входа конкретного элемента;
2. duration и easing конкретного элемента;
3. scroll или entry trigger конкретного элемента;
4. transform-from и opacity-from конкретного элемента.

### 2.2 Что добавляет sequence v1

Sequence v1 отвечает за:

1. объединение элементов в последовательность;
2. порядок шагов внутри последовательности;
3. общий trigger последовательности;
4. runtime offset между шагами;
5. способ вычисления delay без ручного выставления каждого элемента.

### 2.3 Что sequence v1 не делает

Sequence v1 в первой версии не делает:

1. свободный timeline editor;
2. overlap-полосы как в After Effects;
3. keyframe editor;
4. pinned scroll scenes;
5. scrub по scroll progress;
6. reverse timeline;
7. nested sequences с разными parent clocks.

---

## 3. Канонический принцип модели

Каждый элемент может:

1. не участвовать в sequence;
2. участвовать в sequence как шаг;
3. участвовать только в одной sequence в v1.

Sequence v1 model:

1. у sequence есть `sequenceId`;
2. у элемента внутри sequence есть `sequenceStep`;
3. runtime вычисляет итоговый delay как:

`resolvedDelay = baseMotionDelay + sequenceStepDelay`

4. сам motion preset остаётся element-level.

Это позволяет не ломать текущий CSS/runtime слой, а лишь добавлять orchestration delay и trigger routing.

---

## 4. Contract shape v1

### 4.1 Element props additions

В канонический allowlist element props добавляются поля:

1. `sequenceMode`
2. `sequenceId`
3. `sequenceRole`
4. `sequenceStep`
5. `sequenceGap`
6. `sequenceTrigger`
7. `sequenceScope`
8. `sequenceReplay`

### 4.2 Семантика полей

`sequenceMode`

1. `none`
2. `orchestrated`

`sequenceId`

1. строковый id группы последовательности;
2. одинаковый для всех элементов одной sequence;
3. пример: `hero_intro`.

`sequenceRole`

1. future-ready semantic label;
2. в v1 informational field;
3. примеры: `lead`, `support`, `cta`, `media`.

`sequenceStep`

1. целое число >= 0;
2. чем меньше число, тем раньше старт шага.

`sequenceGap`

1. gap между шагами в миллисекундах;
2. хранится на element-level, но runtime берёт его у первого шага sequence;
3. fallback = `80`.

`sequenceTrigger`

1. `inherit`
2. `entry`
3. `scroll`

В v1 sequence trigger либо наследует trigger первого шага, либо задаётся явно на всей sequence.

`sequenceScope`

1. `block`
2. `viewport-group`

V1 runtime работает только с `block`, но поле вводится сразу для предсказуемого расширения.

`sequenceReplay`

1. `once`
2. `repeat-on-reentry`

V1 runtime реально поддерживает только `once`, второе значение пока reserved.

---

## 5. Normalized defaults

Для всех поддерживаемых element types sequence defaults такие:

```php
[
    'sequenceMode' => 'none',
    'sequenceId' => '',
    'sequenceRole' => '',
    'sequenceStep' => 0,
    'sequenceGap' => 80,
    'sequenceTrigger' => 'inherit',
    'sequenceScope' => 'block',
    'sequenceReplay' => 'once',
]
```

---

## 6. SSR attribute contract

Если элемент участвует в sequence, SSR renderer добавляет атрибуты:

1. `data-sequence="1"`
2. `data-sequence-id`
3. `data-sequence-step`
4. `data-sequence-gap`
5. `data-sequence-trigger`
6. `data-sequence-replay`

Пример:

```html
<div
  class="nb-design-el"
  data-motion="1"
  data-motion-active-trigger="entry"
  data-sequence="1"
  data-sequence-id="hero_intro"
  data-sequence-step="2"
  data-sequence-gap="120"
  data-sequence-trigger="inherit"
  data-sequence-replay="once"
></div>
```

---

## 7. Runtime orchestration contract

### 7.1 Runtime pipeline v1

На клиенте orchestration идёт так:

1. runtime сначала собирает все motion-enabled элементы;
2. затем делит их на single elements и sequence groups;
3. внутри sequence groups сортирует элементы по `sequenceStep`, затем по координатам как secondary tiebreaker;
4. вычисляет runtime delay для каждого шага;
5. активирует sequence как одну логическую пачку.

### 7.2 Delay formula

```text
resolvedRuntimeDelay = motionDelay + (sequenceStep * sequenceGap)
```

### 7.3 Trigger resolution

Для sequence v1 trigger вычисляется так:

1. если `sequenceTrigger != inherit`, используется он;
2. иначе используется trigger первого шага sequence;
3. если после вычисления trigger не равен `entry` или `scroll`, sequence runtime не активируется.

### 7.4 Mixed element motion

Motion preset каждого элемента не подменяется sequence runtime.

То есть внутри одной sequence допустимы разные preset-ы:

1. title = `fade-up`
2. media = `zoom-in`
3. cta = `soft-pop`

Sequence управляет моментом старта, но не самим animation shape.

---

## 8. V1 UX vocabulary

В inspector sequence должен подаваться отдельным блоком, не внутри базовой motion-секции.

Рекомендуемая vocabulary:

1. `Базовая анимация`
2. `Последовательность`

Внутри `Последовательность`:

1. `Участвует в sequence`
2. `ID последовательности`
3. `Шаг`
4. `Интервал между шагами`
5. `Trigger sequence`

Это делает mental model очевидной:

элемент имеет собственную анимацию и отдельно может участвовать в общей последовательности.

---

## 9. V1 rollout rule

Последовательность внедряется в таком порядке:

1. contract normalizer;
2. SSR attrs;
3. public runtime orchestration;
4. editor UI.

Главный запрет:

нельзя сначала делать inspector UI, пока backend/runtime ещё не умеют sequence contract.

---

## 10. Критерий готовности v1

Sequence v1 считается готовым, когда:

1. sequence props сохраняются и переживают reload;
2. runtime запускает элементы одной группы в нужном порядке;
3. single-element motion без sequence продолжает работать без изменений;
4. mixed mode `single + sequence` внутри одного блока не ломает preview/live parity.
