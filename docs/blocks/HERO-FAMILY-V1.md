# NordicBlocks Hero Family V1

## 1. Назначение семейства

Hero family в NordicBlocks отвечает за первые экранные секции, которые должны одновременно:

1. давать сильный визуальный вход в лендинг;
2. оставаться совместимыми с unified inspector и contract-first runtime;
3. не требовать отдельной block-specific архитектуры под каждый новый референс;
4. собираться из общих сущностей: `eyebrow`, `title`, `subtitle`, `body`, `primaryButton`, `media` и связанных design/layout controls.

## 2. Канонические правила

Для hero family v1 фиксируются правила:

1. каждый новый hero block type обязан жить на manifest-first contract и общем editor shell;
2. block type может иметь собственный SSR markup и свой visual language, но не отдельный private inspector stack;
3. глобальная дизайн-система остаётся базой для typography, spacing и theme-level переменных;
4. manual-first сценарий обязателен как стартовая точка даже если позже блок получит content bindings.

## 3. Состав семейства

В family уже находятся:

1. базовый `hero` как reference block;
2. donor-style manual variants вроде `hero-panels-01`, `hero-figma-light-10-11`, `hero-magazine-01`;
3. scaffold-managed derivatives, которые должны постепенно вытеснять ad-hoc block-specific подход.

## 4. Текущий stage для generator-managed hero

Stage 3 scaffold pipeline для hero family должен позволять:

1. создавать новый hero slug через generator;
2. автоматически синхронизировать checklist и family registry;
3. доводить render markup до production-уровня уже поверх scaffold baseline;
4. не патчить central runtime registry вручную под каждый новый hero.

## 5. Проверка нового hero block type

Для каждого нового hero block type обязательны:

1. scaffold dry-run;
2. scaffold apply с checkpoint;
3. existing-block validator;
4. php lint для live/package mirror;
5. ручной live smoke в editor и публичном runtime.

## Scaffold Registry
<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_START -->
Этот раздел обновляется автоматически scaffold apply pipeline и показывает текущие scaffold-managed block types этой family.

| Slug | Title | Status | Checkpoint | Live smoke |
| --- | --- | --- | --- | --- |
| hero_panels_editorial | Hero: editorial 12 колонок | in_progress | snapshot/20260419-163016 | yes |
| hero_panels_wide | Hero: широкие панели | in_progress | snapshot/20260419-160928 | yes |
<!-- NORDICBLOCKS_SCAFFOLD_REGISTRY_END -->
