# NordicBlocks Block Scaffold Pipeline Stage 3

## 0. Текущий рабочий статус

На текущем этапе этот pipeline принят как стандартный рабочий путь для новых NordicBlocks managed block types в рамках поддерживаемых scaffold profiles и manifest-first family.

Это означает:

1. новый поддерживаемый block type по умолчанию создаётся через scaffold, а не ручным копированием donor-блока;
2. validator обязателен сразу после apply и считается частью штатного процесса, а не опциональной проверкой;
3. ручная сборка с нуля остаётся допустимой только как исключение для неподдерживаемых или явно нестандартных блоков;
4. сам pipeline не отменяет ручную продуктовую доводку и live smoke.

## 1. Что закрывает этот pipeline

Pipeline нужен для того, чтобы новый NordicBlocks block type не начинался каждый раз с ручного копирования существующего блока и ручного патча central registries.

На stage 3 pipeline делает три вещи:

1. валидирует blueprint будущего блока до записи файлов;
2. в apply-режиме пишет block files в live и package mirror;
3. автоматически подключает новый managed scaffold block к central runtime integration и docs trail.

## 2. Команды

Dry-run blueprint validation:

```bash
/opt/php84/bin/php scripts/nordicblocks-scaffold-block.php \
  --slug=my_block \
  --title="Мой блок" \
  --family=news-family-v1 \
  --profile=card_collection
```

Apply mode:

```bash
/opt/php84/bin/php scripts/nordicblocks-scaffold-block.php \
  --slug=my_block \
  --title="Мой блок" \
  --family=news-family-v1 \
  --profile=card_collection \
  --apply \
  --checkpoint=snapshot/YYYYMMDD-HHMMSS
```

Validation existing block:

```bash
/opt/php84/bin/php scripts/nordicblocks-validate-block.php --block=my_block
```

## 3. Что считается входом

Обязательные параметры:

1. `slug`
2. `title`
3. `family`
4. `profile`

Поддерживаемые scaffold profiles:

1. `hero_like`
2. `faq_like`
3. `card_collection`
4. `catalog_like`
5. `text_section`

Profile определяет стартовый набор:

1. сущностей;
2. capabilities;
3. inspector panels;
4. source-mode profile.

## 4. Что создаётся в apply-режиме

Pipeline пишет:

1. `manifest.php`
2. `schema.json`
3. `render.php`

Файлы создаются одновременно в двух местах:

1. live block directory;
2. package mirror.

Generated files маркируются generator metadata, чтобы validator и runtime могли отличать managed scaffold block от legacy block types.

## 5. Что stage 3 подключает автоматически

После apply новый managed scaffold block не требует ручного расширения central arrays в runtime.

Stage 3 использует `ManagedScaffoldRegistry` и автоматически подхватывает block type в:

1. first-wave detection в `model.php`;
2. contract support в `BlockContractNormalizer.php`;
3. dynamic source resolution в `DataSourceResolver.php`;
4. binding mapping в `BindingMapper.php`.

Это означает, что новый scaffold block после apply должен проходить existing-block validator без ручного patch round по этим файлам.

## 6. Docs trail

Apply mode дополнительно синхронизирует:

1. `docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json`
2. auto-generated `Scaffold Registry` section внутри family doc

Checklist entry создаётся или обновляется автоматически. `live_smoke` по умолчанию остаётся `false`, пока блок не проверен вручную в реальном editor/runtime потоке.

Family doc получает auto-generated registry section с текущими block types этой family. Этот section нужен как операционный реестр, а не как замена канонического family spec.

## 7. Ограничения

Pipeline не отменяет продуктовую дисциплину.

Он не заменяет:

1. family-level spec;
2. live smoke в админке и на runtime;
3. подтверждение UX и visual language;
4. осознанную доработку render markup и schema defaults под реальный блок.

Generated render остаётся stub-реализацией и нужен как безопасная стартовая точка, а не как финальный production markup.

Отдельно важно зафиксировать текущую границу stage 3, чтобы следующий агент не искал проблему не в том слое:

1. stage 3 закрывает structural bootstrap и runtime registration, но не гарантирует мгновенную draft-перерисовку preview в editor shell;
2. текущий hero v2 shell работает через server-backed iframe preview: draft меняется локально, затем autosave отправляет contract на сервер, после успешного save iframe перезагружается через новый `src`;
3. если preview визуально обновляется только после autosave и reload iframe, это не признак того, что scaffold не создал block type, а признак текущей preview-архитектуры shell;
4. `BlockContractNormalizer` для managed scaffold сейчас выполняет роль compatibility bridge между legacy props storage и contract-first runtime, а не должен восприниматься как финальный источник editor preview UX.

Практический вывод: validator `PASS` в текущей архитектуре означает, что block type корректно встроен в managed runtime, но не означает, что editor draft-preview уже доведён до instant rerender parity.

## 7.1 Minimal-change workflow для managed blocks

Чтобы не плодить ручные правки вслепую, для managed scaffold block types используем следующий порядок:

1. scaffold + validator использовать как базовый structural старт, а не как обещание production-complete блока;
2. сразу после apply подтвердить entity map блока: какие сущности реально есть в manifest, какие из них должны попасть в shared inspector, render markup, CSS vars и save-cycle;
3. все новые визуальные зоны заводить как явные entity или surface entity, а не как hardcoded render-исключения;
4. если блок должен обновлять preview мгновенно от draft, это надо чинить в editor-shell transport между draft и iframe canvas, а не маскировать дополнительными patch в `render.php`;
5. `BlockContractNormalizer` считать временным persistence adapter слоем: через него допустимо поддерживать round-trip contract <-> props, но не стоит строить на нём новые block-specific UX-механики;
6. ручная сборка с нуля оправдана только когда новый блок не укладывается в supported scaffold profile или требует отдельной editor/runtime mechanics, которую shared shell принципиально не поддерживает.

Архитектурное правило для следующих сессий: если проблема проявляется как `draft не отражается мгновенно в iframe`, сначала проверять editor shell и transport draft -> canvas. Если проблема проявляется как `после save значения теряются или возвращаются не в том виде`, тогда проверять `BlockContractNormalizer` и managed entity round-trip.

## 8. Rollback и безопасный порядок работы

Перед apply обязателен checkpoint tag.

Рекомендуемый порядок:

1. создать checkpoint;
2. прогнать scaffold dry-run;
3. выполнить apply;
4. прогнать existing-block validator;
5. доработать runtime/editor markup;
6. выполнить manual live smoke;
7. обновить checklist status и family docs, если появился новый продуктовый статус beyond scaffold baseline.

Операционное правило для следующих сессий: если новый block type укладывается в поддерживаемые profiles и family rules, агент не должен начинать работу с ручного копирования block directory. Базовый стартовый путь здесь именно scaffold + validator.

## 9. Критерий завершения

Новый block type считается корректно поднятым scaffold pipeline, если одновременно выполнены условия:

1. `scripts/nordicblocks-scaffold-block.php --apply ...` завершился без ошибок;
2. `scripts/nordicblocks-validate-block.php --block=<slug>` возвращает `PASS` или ожидаемый `PASS_WITH_WARNINGS` для legacy context;
3. live/package mirror находятся в паритете;
4. checklist entry создан;
5. family doc registry section обновлён;
6. ручной live smoke выполнен отдельно и зафиксирован в docs/checklist.