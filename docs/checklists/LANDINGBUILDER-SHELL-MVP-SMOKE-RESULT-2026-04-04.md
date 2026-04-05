# Shell Builder MVP Smoke Result

Дата: 2026-04-04

Формат проверки: публичный живой сайт без админской сессии.

## Что удалось подтвердить на живом сайте

### PASS

1. Главная страница открывается по адресу `/` и отдает `200`.
2. Профиль пользователя открывается по адресу `/users/1` и отдает `200`.
3. Category page объявлений открывается по адресу `/board/nedvizhimost` и отдает `200`.
4. Все три страницы рендерятся через шаблон `nordic` и содержат shell-разметку `nordic-shell__*`.
5. Админский маршрут `admin/landingbuilder/shell` закрыт для гостя и отдает `403`, то есть backend shell-настройки не торчат наружу публично.

### BLOCKED

1. Открыть экран `Shell Builder` и проверить backend-список variant-ов нельзя без админской сессии.
2. Сохранение shell variant нельзя проверить без админской сессии.
3. Все пункты checklist, где нужно руками переключать variant или toggle, сейчас заблокированы отсутствием доступа в backend.

### NOT CONFIRMED PUBLICLY

1. На `/users/1` не обнаружены публичные HTML-маркеры `landingbuilder` overlay: нет `lb-overlay-zone`, `lb-overlay-note`, `data-shell-variant`, `data-shell-layout`.
2. На `/board/nedvizhimost` тоже не обнаружены публичные HTML-маркеры `landingbuilder` overlay.
3. Публичный маршрут `/landingbuilder/view/homepage` возвращает `404` для гостя, поэтому draft/prototype preview-поведение удалось подтвердить только косвенно: публичного доступа к preview нет.
4. Для `homepage shell mode` как backend-переключателя нет живого подтверждения без входа в админку и смены режима на самой странице shell variant.

## Практический вывод

По живому сайту уже подтверждено, что `nordic` shell реально работает на frontend и публичные страницы не падают.

Но именно shell-level сценарии `landingbuilder` сейчас нельзя закрыть полностью публичной проверкой, потому что:

1. backend-часть защищена и это нормально;
2. overlay/runtime для profile и category не виден гостю в текущем публичном HTML;
3. переключаемые shell-сценарии требуют админской сессии для честной ручной проверки.

## Что уже можно считать пройденным

1. Базовая публичная доступность shell frontend.
2. Корректный рендер nordic shell на главной, профиле и category route.
3. Корректная защита backend shell-экрана от гостя.

## Что нужно для полного закрытия smoke-checklist

1. Войти в админку и пройти shell variant save/toggle checks вручную.
2. Проверить `menu placement` на frontend после реального переключения.
3. Проверить `homepage shell mode` в трех состояниях: `shell_hero`, `page_hero`, `mixed`.
4. Проверить, опубликованы ли нужные builder pages для category/profile overlay, если они должны быть видны не только администратору.