# NordicBlocks Block Design Manifest V1

Дата: 2026-04-17

## 1. Зачем нужен отдельный manifest

Сейчас глобальная дизайн-система NordicBlocks уже существует и это правильно.

Но локальный дизайн конкретного блока пока ещё слишком слабый:

1. настройки разрознены;
2. нет одного канонического design-manifest слоя;
3. пользователь не получает ощущение сильного контроля уровня Webflow;
4. инспектор легко превращается в длинную простыню.

Цель этого документа:

1. зафиксировать отдельный слой block-local design над глобальной foundation-системой;
2. не ломать global design, а дополнять его;
3. сфокусировать первую волну на реально важных для продукта настройках, а не на расползании по десяткам мелких полей.
4. жёстко зафиксировать, что inspector и design-manifest проектируются как универсальный слой для всех будущих блоков, а не как частное решение под один текущий block type.

Итоговое правило:

Global Design Foundation остаётся источником общей стилистики сайта, а Block Design Manifest даёт сильную точечную настройку каждого блока.

Критичное уточнение:

1. Block Design Manifest V1 не является FAQ-специфичной архитектурой;
2. это общий design/runtime/inspector слой для всех будущих блоков NordicBlocks;
3. `faq` используется только как первый pilot-носитель, потому что на нём удобно быстро проверить block-level и item-level сценарии.

---

## 2. Главный принцип

Нужен не набор новых полей, а отдельный design-manifest слой.

Он должен отвечать на 4 вопроса:

1. какие сущности дизайна есть у блока;
2. какие группы настроек разрешены для каждой сущности;
3. что наследуется из global design по умолчанию;
4. что можно переопределить локально для одного блока.

Это и есть путь к ощущению “как в Webflow”, но без ухода в freeform page-builder.

---

## 3. Иерархия дизайна

Для NordicBlocks фиксируем такую цепочку:

1. Global Foundation Tokens
2. Semantic style roles
3. Block default style slots
4. Local block overrides
5. Breakpoint overrides
6. State overrides

Практический смысл:

1. сайт не разваливается по стилю;
2. блок можно гибко донастроить локально;
3. локальные настройки не должны дублировать весь global contract целиком;
4. inspector знает, что показывать как наследуемое, а что как override.

---

## 4. Что считаем обязательным для каждого блока

Для первой взрослой версии design-manifest фиксируем жёсткое правило:

1. у каждого блока всегда есть сущность `title`;
2. у каждого блока всегда есть сущность `subtitle`.

Но это не означает, что они всегда обязаны показываться на экране.

Правило показа:

1. `title` и `subtitle` входят в канонический manifest каждого блока;
2. у них должен быть переключатель показа;
3. если элемент выключен, он не рендерится и не занимает место в блоке;
4. если элемент включён, он получает полный набор design-настроек своего уровня.

То есть это обязательные сущности контракта, но не обязательные визуальные элементы в каждом конкретном состоянии блока.

---

## 5. Первый реальный фокус дизайна

На первом этапе не нужно пытаться сделать одинаково глубокими все части блока.

Главный приоритет такой:

1. фон секции;
2. заголовок;
3. подзаголовок.

Именно они сильнее всего влияют на восприятие блока и продающую силу секции.

### 5.1 Фон секции должен быть максимально гибким

Минимальный взрослый набор:

1. solid color;
2. gradient;
3. image;
4. overlay color;
5. overlay opacity;
6. background position;
7. background size;
8. background repeat;
9. content/background split mode позже как расширение.

### 5.2 Заголовок и подзаголовок должны быть максимально гибкими

Минимальный взрослый набор:

1. show / hide;
2. font family role;
3. size desktop/mobile;
4. line-height;
5. letter-spacing;
6. weight;
7. text transform;
8. color;
9. max width;
10. align;
11. margin-bottom / gap rules;
12. heading tag для title;
13. opacity как дополнительная тонкая настройка.

### 5.3 Что делать с остальным

Остальные части блока на первой волне не нужно раздувать бесконечными локальными override-полями.

Правило:

1. сначала опираться на адаптивную верстку;
2. потом на global foundation;
3. и только затем добавлять локальные design overrides там, где это реально усиливает блок.

---

## 6. Иконки и фото

Отдельный кастомный менеджер иконок или медиа для NordicBlocks не нужен.

Фиксируем правило:

1. фото выбираются через штатный InstantCMS image picker;
2. иконки выбираются через штатный InstantCMS icon picker;
3. design-manifest хранит только нормализованное значение и UI hint;
4. contract не должен знать про DOM, JS callbacks или детали реализации модалки.

Практический смысл:

1. меньше самодельного UI;
2. меньше расхождений с InstantCMS;
3. одинаковое поведение для image и icon fallback во всех блоках.

---

## 7. Inspector UX: не простыня, а аккордеон + выбор сущности

Чтобы сильные настройки не превращались в хаос, фиксируем 2 уровня навигации в инспекторе.

### 7.1 Первый уровень: вкладки

Сохраняем верхнеуровневые вкладки:

1. Контент
2. Дизайн
3. Макет
4. Данные

### 7.2 Второй уровень: аккордеон

Внутри вкладок настройки живут не в длинной простыне, а в аккордеоне по сущностям и группам.

Минимальные секции для вкладки Дизайн:

1. Секция
2. Заголовок
3. Подзаголовок
4. Кнопки
5. Медиа
6. Surface / Карточки

### 7.3 Выбор сущности с canvas

При клике по элементу на canvas инспектор должен делать следующее:

1. понимать, какая сущность выбрана;
2. открывать нужную вкладку и нужный аккордеон;
3. подсовывать пользователю именно настройки выбранного элемента.

Пример:

1. клик по заголовку открывает настройки `title`;
2. клик по подзаголовку открывает настройки `subtitle`;
3. клик по карточке вопроса открывает группу item/question settings.

Именно это должно дать ощущение “редактирую конкретный элемент”, а не “ищу нужное поле в длинном списке”.

---

## 8. Каноническая структура Manifest V1

Ниже не финальный storage contract, а канонический логический слой.

```json
{
  "designManifest": {
    "version": 1,
    "entities": {
      "title": {
        "required": true,
        "visible": true,
        "accordion": "title",
        "styleSlot": "title",
        "inheritsFrom": "global.typography.title",
        "groups": ["visibility", "typography", "spacing"]
      },
      "subtitle": {
        "required": true,
        "visible": true,
        "accordion": "subtitle",
        "styleSlot": "subtitle",
        "inheritsFrom": "global.typography.subtitle",
        "groups": ["visibility", "typography", "spacing"]
      },
      "section": {
        "required": true,
        "accordion": "section",
        "styleSlot": "section",
        "inheritsFrom": "global.section",
        "groups": ["background", "container", "spacing"]
      },
      "icon": {
        "required": false,
        "picker": "instantcms_icon_modal",
        "groups": ["content", "spacing"]
      },
      "image": {
        "required": false,
        "picker": "instantcms_image_modal",
        "groups": ["content", "media"]
      }
    }
  }
}
```

### Что это даёт

1. inspector знает, какие сущности обязательны для блока;
2. inspector знает, какие аккордеоны и группы открыть;
3. глобальная система остаётся базой, а локальные override живут поверх неё;
4. один и тот же manifest-подход обязан применяться ко всем следующим block types без новой архитектуры под каждый блок.

### 8.1 Универсальное правило внедрения

Начиная с этого документа фиксируем жёсткое правило продукта:

1. новые блоки не получают собственный уникальный inspector-контур;
2. новые блоки подключаются к одному и тому же universal inspector runtime;
3. block type декларирует сущности и capability, а не рисует отдельный UI под себя;
4. любые новые hero, faq, features, grid, CTA и следующие блоки должны расширять один и тот же manifest-слой, а не создавать параллельные схемы настроек.

---

## 9. На чём обкатывать первую волну

Первый пилот лучше делать не на новом сложном block class, а на понятном существующем сценарии.

Но важно:

1. пилот не равен архитектуре;
2. `faq` не задаёт отдельные правила для себя;
3. `faq` только проверяет, что universal inspector и universal design-manifest действительно работают на реальном блоке.

Зафиксированный пилот:

1. `faq`;
2. сущности верхнего уровня: `title`, `subtitle`, `section`;
3. item-level сущности по UX-смыслу: `question`, `answer`.

Техническое правило для пилота:

1. в inspector-е пользователю показываем названия `Вопрос` и `Ответ`;
2. внутри канонического entity/runtime слоя продолжаем использовать общие ключи `itemTitle` и `itemText`;
3. FAQ не должен заводить отдельные уникальные ключи уровня `faq_question_title` или `faq_answer_text`.

Универсальное следствие для следующих блоков:

1. другой block type сможет показывать те же канонические сущности под своими пользовательскими label;
2. но под капотом сохранит тот же entity/model/runtime слой;
3. inspector от этого не должен менять архитектуру.

Почему именно FAQ:

1. вопрос и ответ легко читаются как отдельные сущности;
2. сразу можно проверить block-level и item-level design;
3. там уже есть реальный runtime и inspector контекст;
4. легче увидеть, не превращается ли UI в простыню.

### Что должно получиться на пилоте

1. можно скрыть title и subtitle без пустого воздуха в блоке;
2. можно гибко управлять фоном секции;
3. можно гибко управлять типографикой question/answer;
4. настройки раскрываются аккордеоном;
5. клик по question/answer на canvas открывает нужную группу в инспекторе.

### 9.1 Почему FAQ подходит лучше всего

У FAQ уже есть хороший реальный контур для взрослого пилота:

1. в текущем runtime уже есть `section`, `title`, `subtitle`, `itemSurface`, `itemTitle`, `itemText`;
2. блок уже SSR-рендерится и уже умеет visibility для `title` и `subtitle`;
3. блок уже data-driven и manual-first одновременно;
4. клик по `details/summary` уже живёт в canvas без слома нативного раскрытия.

То есть FAQ позволяет проверить сразу:

1. block-level design;
2. item-level design;
3. accordion UX в инспекторе;
4. canvas-to-inspector entity focus.

Но итоговая цель этого пилота шире:

1. после проверки на FAQ тот же слой без новой архитектуры переносится на hero;
2. затем на features / list / grid блоки;
3. и дальше на все будущие block types, которые появятся в NordicBlocks.

### 9.2 FAQ Pilot Spec V1: границы

Этот pilot-spec фиксирует не весь финальный дизайн-движок, а первую реально внедряемую волну универсального design-manifest слоя.

Его надо читать так:

1. универсальный слой проектируется один раз;
2. на FAQ он только проверяется в боевых условиях;
3. всё, что будет валидно в этой спецификации, должно потом переиспользоваться и в следующих блоках.

В неё входят только 5 ключевых сущностей:

1. `section`
2. `title`
3. `subtitle`
4. `itemTitle` с пользовательским label `Вопрос`
5. `itemText` с пользовательским label `Ответ`

Не входят в первую обязательную волну:

1. `eyebrow`
2. `primaryButton`
3. `secondaryButton`
4. сложные state-настройки для itemSurface
5. свободная кастомизация disclosure icon

Если иконка понадобится в FAQ-пилоте как отдельное поле, она должна идти только через штатный InstantCMS icon picker.

### 9.3 Design groups по сущностям

Ниже фиксируется не просто список полей, а именно группы inspector-а для первой волны.

#### Сущность `section`

Это главный design-аккордеон FAQ-пилота.

Группы:

1. `Фон`
   - режим: `theme | solid | gradient | image`
   - color
   - gradient from
   - gradient to
   - gradient angle
   - image
   - overlay color
   - overlay opacity
   - background position
   - background size
   - background repeat
2. `Контейнер`
   - content width preset
   - custom max width
   - align: `left | center`
3. `Отступы`
   - padding top desktop/mobile
   - padding bottom desktop/mobile

Что сознательно не добавляем сюда в первой волне:

1. сложный multi-layer background builder;
2. shape dividers;
3. animation composer для секции.

#### Сущность `title`

Это главный текстовый аккордеон верхнего уровня.

Группы:

1. `Показ`
   - show / hide
   - hide должен убирать элемент из layout без пустого зазора
2. `Типографика`
   - font role
   - font size desktop/mobile
   - line-height
   - letter-spacing
   - weight
   - text transform
   - color
   - opacity
   - heading tag
   - text align
   - max width
3. `Отступы`
   - margin bottom desktop/mobile

Это максимальный приоритет первой волны.

#### Сущность `subtitle`

Это второй текстовый аккордеон верхнего уровня.

Группы:

1. `Показ`
   - show / hide
   - hide должен убирать элемент из layout без пустого зазора
2. `Типографика`
   - font role
   - font size desktop/mobile
   - line-height
   - letter-spacing
   - weight
   - color
   - opacity
   - text align
   - max width
3. `Отступы`
   - margin bottom desktop/mobile

Принцип:

subtitle по глубине почти не уступает title, потому что именно эти два слоя сильнее всего меняют характер секции.

#### Сущность `itemTitle` с label `Вопрос`

Это первый item-level pilot.

Группы:

1. `Типографика`
   - font role
   - font size desktop/mobile
   - line-height
   - letter-spacing
   - weight
   - color
2. `Отступы`
   - внутренний gap между question и answer задаётся layout/item-level правилами, а не ручным margin у каждого item
3. `Иконка` как опциональная группа второй очереди
   - icon picker = штатный InstantCMS picker
   - position: `before | after`
   - gap

Для первой обязательной реализации достаточно typography.

#### Сущность `itemText` с label `Ответ`

Это второй item-level pilot.

Группы:

1. `Типографика`
   - font role
   - font size desktop/mobile
   - line-height
   - letter-spacing
   - weight
   - color
   - max width
2. `Отступы`
   - top spacing answer area, если это не закрыто surface/layout логикой item container

Для первой обязательной реализации достаточно typography + controlled top spacing.

### 9.4 Что остаётся на адаптивной вёрстке

Чтобы pilot не расползался, фиксируем, что не уводим в inspector на первой волне.

На adaptive layout оставляем:

1. базовую раскладку FAQ-списка;
2. responsive stack самого списка;
3. базовый раскрывающийся ритм `details/summary`;
4. большую часть item container geometry.

То есть не пытаемся сразу превратить каждый item в мини-Webflow layout builder.

### 9.5 Inspector IA для FAQ Pilot

Ниже фиксируется взрослый, но компактный IA для первой волны.

Архитектурное правило:

1. это не отдельный FAQ inspector;
2. это universal inspector IA, проверяемый на FAQ;
3. в других блоках меняется только набор активных сущностей и label, а не сама система вкладок и аккордеонов.

#### Вкладка `Контент`

Аккордеоны:

1. `Заголовок`
2. `Подзаголовок`
3. `Вопросы`
4. `Ответы`

#### Вкладка `Дизайн`

Аккордеоны:

1. `Секция`
2. `Заголовок`
3. `Подзаголовок`
4. `Вопрос`
5. `Ответ`

#### Вкладка `Макет`

Аккордеоны:

1. `Секция`
2. `Список`

#### Вкладка `Данные`

Аккордеоны:

1. `Источник`
2. `Коллекция`
3. `Привязки`

Правило UX:

1. одновременно раскрыт только один главный аккордеон сущности;
2. внутри сущности уже могут жить вложенные design groups;
3. при клике на canvas inspector автоматически выбирает правильную вкладку и раскрывает правильный аккордеон.

### 9.6 Canvas focus mapping для FAQ

Фиксируем явную карту поведения.

1. клик по `data-nb-entity="title"` открывает вкладку `Дизайн` → аккордеон `Заголовок`;
2. клик по `data-nb-entity="subtitle"` открывает вкладку `Дизайн` → аккордеон `Подзаголовок`;
3. клик по `data-nb-entity="itemTitle"` открывает вкладку `Дизайн` → аккордеон `Вопрос`;
4. клик по `data-nb-entity="itemText"` открывает вкладку `Дизайн` → аккордеон `Ответ`;
5. клик по `data-nb-entity="section"` открывает вкладку `Дизайн` → аккордеон `Секция`.

Если выбрана item-level сущность, inspector дополнительно должен знать index текущего item.

Пример UX-хлебных крошек:

1. `FAQ / Вопрос 2 / Вопрос`
2. `FAQ / Вопрос 2 / Ответ`

### 9.7 FAQ Pilot Storage Shape

Ниже не финальный код, а прикладная форма для реализации.

```json
{
  "design": {
    "section": {
      "background": {
        "mode": "theme",
        "color": "",
        "gradientFrom": "",
        "gradientTo": "",
        "gradientAngle": 135,
        "image": "",
        "overlayColor": "#0f172a",
        "overlayOpacity": 45,
        "position": "center center",
        "size": "cover",
        "repeat": "no-repeat"
      }
    },
    "entities": {
      "title": {
        "visible": true,
        "tag": "h2",
        "desktop": {
          "fontSize": 48,
          "lineHeight": 1.05,
          "letterSpacing": -0.02,
          "marginBottom": 0,
          "maxWidth": 840
        },
        "mobile": {
          "fontSize": 32,
          "marginBottom": 0
        },
        "weight": 800,
        "color": "inherit",
        "align": "center"
      },
      "subtitle": {
        "visible": true,
        "desktop": {
          "fontSize": 18,
          "lineHeight": 1.6,
          "marginBottom": 32,
          "maxWidth": 760
        },
        "mobile": {
          "fontSize": 16,
          "marginBottom": 24
        },
        "weight": 400,
        "color": "inherit",
        "align": "center"
      },
      "itemTitle": {
        "desktop": {
          "fontSize": 18,
          "lineHeight": 1.35
        },
        "mobile": {
          "fontSize": 17
        },
        "weight": 700,
        "color": "inherit"
      },
      "itemText": {
        "desktop": {
          "fontSize": 16,
          "lineHeight": 1.65,
          "maxWidth": 680
        },
        "mobile": {
          "fontSize": 15
        },
        "weight": 400,
        "color": "inherit"
      }
    }
  },
  "layout": {
    "desktop": {
      "contentWidth": 760,
      "paddingTop": 88,
      "paddingBottom": 88,
      "align": "center"
    },
    "mobile": {
      "paddingTop": 56,
      "paddingBottom": 56
    }
  }
}
```

### 9.8 Definition of Done для FAQ Pilot

FAQ pilot считается взрослым первым шагом только если одновременно выполнены условия:

1. `title` и `subtitle` можно выключить без пустого воздуха в блоке;
2. фон секции настраивается не только темой, но и через полноценный background group;
3. у `title` и `subtitle` есть отдельные аккордеоны с сильной typography-группой;
4. у `Вопроса` и `Ответа` есть отдельные item-level design-настройки;
5. inspector не превращается в простыню и работает через аккордеоны;
6. canvas click открывает правильную сущность;
7. FAQ не заводит частные нестандартные entity keys поверх каноники.

Дополнительное обязательное условие:

8. после чтения спецификации должно быть очевидно, как этот же inspector/design-слой переносится на другие блоки без новой архитектуры.

---

## 10. Что не делаем в первой волне

Чтобы не расползтись, фиксируем ограничения.

Не делаем сразу:

1. полный visual CSS editor;
2. свободное позиционирование всего подряд;
3. десятки состояний для каждой сущности;
4. локальные overrides вообще для всех элементов блока;
5. отдельный кастомный media manager;
6. отдельный кастомный icon manager.

Первый шаг должен доказать другое:

1. сильный фон;
2. сильные заголовки и подзаголовки;
3. вменяемый inspector UX;
4. корректное наследование global → local.

---

## 11. Следующий практический шаг

Архитектурно следующим этапом логично считать не новый block type, а pilot-слой:

1. formal entity-based Block Design Manifest V1;
2. accordion inspector UX;
3. canvas-to-inspector entity focus;
4. pilot на `faq` с `question` / `answer`.

После этого уже можно решать, какой block class поднимать следующим, потому что у него будет взрослая универсальная дизайн-основа, а не временный набор полей.

Финальная формула этого документа:

1. строим universal inspector и universal block design layer;
2. обкатываем его на FAQ;
3. затем переносим тот же слой на все следующие блоки без смены архитектуры.