# NordicBlocks Design Block InstantCMS Integration Rules V1

Дата: 2026-04-20

## 1. Цель документа

Этот документ фиксирует жёсткую архитектурную границу для `design_block` внутри NordicBlocks.

Он нужен, чтобы на старте не допустить главную ошибку: раз `design_block` выбирается как обычный блок в InstantCMS, это ещё не делает его частью shared inspector runtime.

Каноническая формула:

1. на уровне CMS это обычный block type;
2. на уровне editor runtime это отдельный canvas engine;
3. на уровне SSR runtime это отдельный renderer;
4. на уровне platform lifecycle это всё ещё часть NordicBlocks.

## 2. Уровень CMS

`design_block` регистрируется как обычный block type:

1. хранится в общей таблице блоков;
2. виден в общем списке NordicBlocks;
3. открывается через тот же `block_edit/{id}` entrypoint;
4. размещается через тот же widget `nordicblocks_block` в стандартной схеме InstantCMS.

На этом уровне `design_block` не получает отдельный placement flow.

## 3. Уровень editor runtime

Если block type не `design_block`, работает shared inspector shell.

Если block type `design_block`, работает отдельный canvas editor engine.

Каноническое правило dispatch:

1. `design_block` не открывается через inspector shell;
2. `design_block` не собирает state через inspector registry builder;
3. `design_block` не использует shared panel renderer как основу UX;
4. `design_block` не притягивается к managed manifest-first vocabulary.

## 4. Что запрещено для design_block

На текущем architectural rail запрещено:

1. использовать shared entity registry как editor foundation;
2. использовать shared capability registry как editor foundation;
3. использовать shared panel registry как editor foundation;
4. рендерить canvas через shared inspector panels;
5. подменять scene-first editor shared inspector-driven state model.

Это не запрещает локальные capability rules внутри самого canvas engine. Запрет касается именно shared inspector runtime NordicBlocks.

## 5. Что такое design_block contract

`design_block` не является managed schema/manifest contract как `hero`, `faq` или `cards_slider`.

Но это не означает отсутствия контракта.

Правильная формулировка:

1. у `design_block` есть свой dedicated contract;
2. этот contract scene-first, а не inspector-first;
3. он не зависит от shared registry tabs, panels или capabilities;
4. он остаётся версионированным и normalizable.

В переходной фазе допустим wrapper-слой вокруг scene-first payload, но editor engine обязан мыслить сценой, а не inspector entities.

## 6. Canvas editor responsibilities

Canvas editor обязан решать локально:

1. node rendering;
2. selection engine;
3. drag, resize и zoom;
4. pointer capture;
5. world coordinates;
6. локальный inspector/sidebar;
7. layer tree.

Если inspector существует, он:

1. локален для canvas engine;
2. не использует shared NordicBlocks inspector registry как основу;
3. не обязан повторять managed tabs vocabulary.

## 7. SSR runtime

На публичной стороне `design_block` рендерится:

1. из scene-first payload;
2. через отдельный renderer;
3. без shared inspector runtime dependencies;
4. без editor JS на live странице.

Но при этом он всё ещё проходит через общий widget lifecycle NordicBlocks.

## 8. Граница систем

Есть две разные подсистемы:

1. Managed Blocks Engine;
2. Design Block Engine.

Они могут жить в одном компоненте и в одном пакете, но не должны делить editor runtime-логику.

Общее между ними:

1. block CRUD;
2. widget placement;
3. save lifecycle;
4. cache и invalidation contour;
5. preview/live discipline.

Разное между ними:

1. editor shell;
2. state model;
3. renderer pipeline;
4. interaction model;
5. internal contract vocabulary.

## 9. Канонический dispatch rule

Backend обязан мыслить так:

1. обычный managed block -> inspector editor engine;
2. `design_block` -> canvas editor engine.

Это правило должно быть выражено не только в docs, но и в code-path metadata:

1. block list;
2. `block_edit` dispatch;
3. state endpoints;
4. save/runtime boundaries.

## 10. Итоговое правило

`design_block` это не ещё один managed inspector block.

Это:

1. блок на уровне CMS;
2. отдельный editor engine на уровне runtime;
3. отдельный renderer на уровне SSR;
4. но не отдельный placement-продукт вне NordicBlocks.