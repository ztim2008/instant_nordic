# NordicBlocks Universal Visual Inspector Go / No-Go Blueprint

Дата: 2026-04-19

## 1. Решение

Universal Visual Inspector имеет смысл делать только как основной product-level editor layer для NordicBlocks.

Его нельзя запускать как:

1. ещё одну узкую подстройку для одного block type;
2. selector-driven CSS editor поверх текущей системы;
3. декоративную imitation-версию SiteOrigin CSS без полного canvas-to-inspector цикла.

Если первая фаза не даёт ощущение одного сильного визуального редактора для нескольких разных блоков, проект нужно формально закрыть и не продолжать в режиме мелких патчей.

## 2. Продуктовый принцип

Целевой UX:

1. пользователь кликает по реальному элементу на canvas;
2. система выбирает canonical entity, а не CSS selector;
3. inspector открывает правильную русскую панель для этой сущности;
4. изменение применяется через contract-first controls;
5. selector-safe overlay допустим только как secondary fallback там, где contract пока не покрывает локальную настройку.

Главная формула:

не DOM управляет редактором, а contract и entity registry интерпретируют DOM.

## 3. Что обязано быть в Phase 1

Phase 1 считается жизнеспособной только если одновременно выполнены все условия ниже.

### 3.1 Universal canvas picking

Обязано работать единообразно для всех pilot block types:

1. hover outline;
2. active highlight;
3. click-to-select entity;
4. синхронизация canvas -> selectedEntity -> inspector;
5. обратная синхронизация inspector -> canvas focus.

### 3.2 Entity-first selection

Система обязана выбирать:

1. `title`, `subtitle`, `body`, `media`, `primaryButton`, `itemSurface`, `itemTitle`, `itemText` и другие canonical entities;
2. label overrides допустимы только на UX-уровне;
3. никаких selector lists, specificity ranking и raw CSS targeting в основном сценарии.

### 3.3 Один inspector UI

В первой фазе должен существовать один тип правой панели:

1. единые вкладки `Контент / Дизайн / Макет / Данные`;
2. единый русский язык интерфейса;
3. единая логика аккордеонов, chips, active panel focus и empty states;
4. отсутствие отдельного private shell под hero, faq, feed и другие блоки.

### 3.4 Registry and capability driven rendering

Inspector обязан строиться из:

1. entity registry;
2. capability matrix;
3. panel registry;
4. control presets.

Недопустимо:

1. хардкодить panel layout в block template;
2. вручную собирать inspector под block type;
3. размножать special-case UI под каждый новый блок.

### 3.5 Pilot coverage

Первая фаза не считается успешной на одном блоке.

Минимальный pilot coverage:

1. `hero`;
2. `faq`;
3. один feed/grid block;
4. ещё один block type с items/media surfaces.

Практическое правило: не меньше 4 block types и не меньше 12-15 реально кликабельных сущностей суммарно.

## 4. Что запрещено в Phase 1

Эти паттерны считаются блокирующими и автоматически переводят проект в no-go.

1. Raw CSS textarea как основной UX.
2. Selector builder как основной UX.
3. Разная логика inspector shell для разных block types.
4. Новый block-specific template ради одного блока вместо расширения registry.
5. Успех, доказанный только на `hero_panels_wide`.
6. Overlay-first развитие без contract-first editor path.
7. Английский служебный UI в пользовательском inspector, если вся остальная редакторская модель уже русская.

## 5. Роль Visual CSS overlay

Текущий overlay-слой не отменяется, но меняет статус.

Он допустим только как вспомогательный слой:

1. для selector-safe fine tune внутри разрешённых entity targets;
2. для случаев, где ещё нет contract control;
3. для временного bridge до появления нормального entity-level control.

Overlay не имеет права становиться главным интерфейсом visual editing.

## 6. Жёсткие go / no-go критерии

### Go

Проект продолжается только если после первой фазы можно честно сказать:

1. пользователь кликает по элементам нескольких разных блоков одинаковым способом;
2. inspector в большинстве случаев открывает правильную сущность без ручных обходов;
3. editor ощущается как одна система, а не как набор разных редакторов;
4. новый block type подключается через markup + manifest + registry, а не через новый shell;
5. минимум 80% pilot edit flows закрываются contract-first controls без raw CSS.

### No-Go

Проект нужно остановить, если проявляется хотя бы один из сигналов:

1. второй и третий блок требуют новых частных inspector шаблонов;
2. entity picking работает нестабильно и часто выбирает не ту сущность;
3. без CSS overlay нельзя нормально редактировать даже базовые title/body/button/media сценарии;
4. русская унифицированная панель не получается без слоя block-specific исключений;
5. rollout на 4 блока не даёт ощущения одного редактора.

## 7. Definition of Done for Phase 1

Phase 1 считается завершённой только если одновременно подтверждены:

1. manual live smoke в editor на pilot blocks;
2. reload-safe selected entity flow;
3. работающий canvas focus и inspector focus;
4. отсутствие новых block-specific inspector templates в diff;
5. зафиксированный список legacy gaps, которые остаются вне Phase 1;
6. решение команды, что universal path становится default и новые block types больше не идут по one-off inspector пути.

## 8. Финальное правило

Если первая фаза не доказывает universal visual inspector как реальный общий инструмент, проект не надо «дотягивать потом».

В этом случае его нужно закрыть, оставить полезные нижние слои вроде registry/state/overlay, но прекратить иллюзию, что из набора частных подстроек вырастет сильный visual editor.