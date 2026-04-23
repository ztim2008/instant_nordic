# NordicBlocks Changelog

## 0.2.0 - 2026-04-23

- create-flow собран в один вход: отдельный пользовательский экран каталога убран из основного маршрута, curated cards перенесены в существующий modal `Создать блок`;
- blocks screen получил верхний CTA `Собрать свой блок`, который сразу открывает create modal в режиме `design_block` с фокусом на naming + canvas start;
- новые `design_block` больше не стартуют пустыми: normalizer теперь гарантирует starter trio из object, text и button даже для пустого контракта;
- editor/runtime parity проверена live: для свежего блока state endpoint возвращает `3` элемента, а hydrated editor показывает `Слои • 3 элементов • Корень сцены`;
- для релиза `0.2.0` синхронизируются package version/manifest и собираются install/update архивы NordicBlocks.

## 0.1.1 - 2026-04-22

- добавлен новый element type `embed` (`Вставка`) для `design_block`;
- runtime переведён на безопасный sandbox iframe/srcdoc вместо raw HTML в DOM;
- добавлены provider-aware presets и нормализация URL для `Рутуб`, `VK Видео`, `Kinescope` и generic iframe;
- добавлены настройки `aspectRatio`, `hideScrollbars`, `allowFullscreen`, `sandboxProfile`, `referrerPolicy`;
- editor shell получил быстрые пресеты, разбор iframe-кода и локализованный embed inspector;
- для длинного HTML добавлен deferred draft flow: `Применить код`, `Сбросить`, `Ctrl+Enter`;
- исправлено визуальное несоответствие embed preview в редакторе по высоте;
- выполнен live smoke: валидный HTML embed сохранён в блоке `#179`, публичная главная страница рендерит обновлённый iframe-контент;
- собраны install и update архивы версии `0.1.1`.

## Шаблон следующей записи

```md
## X.Y.Z - YYYY-MM-DD

- кратко: что добавлено или изменено в продукте;
- runtime/admin: какие ключевые сценарии или контракты затронуты;
- editor UX: что улучшено в authoring flow или preview;
- fix: какой дефект закрыт для live/runtime/editor parity;
- smoke/release: какие проверки пройдены и какие артефакты собраны.
```