<?php

$page_mode_titles = [
    'instant_content_body' => 'Нативная страница Instant (content_body)',
    'full_takeover'  => 'Полностью своя страница',
    'hybrid_overlay' => 'Поверх существующей страницы',
    'zone_injection' => 'Встраивание в зону страницы',
    'data_only'      => 'Только данные для блоков'
];

$page_status_titles = [
    'draft'     => 'Черновик',
    'prototype' => 'Прототип',
    'idea'      => 'Идея',
    'published' => 'Опубликовано'
];

$content_types = isset($content_types) && is_array($content_types) ? $content_types : [];

$this->setPageTitle('Нордик: страницы');
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы');
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'add lb-create-page',
    'title' => 'Новая страница',
    'href'  => $is_schema_installed ? '#' : 'javascript:void(0)',
    'icon'  => 'plus-circle'
]);

?>
<style>
    .lb-admin-ui {
        display: grid;
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .lb-admin-hero,
    .lb-admin-card,
    .lb-page-card {
        border: 1px solid #d8e2ea;
        border-radius: 26px;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
    }

    .lb-admin-hero {
        padding: 1.5rem 1.65rem;
    }
    .lb-admin-hero__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .lb-admin-eyebrow {
        margin-bottom: 0.4rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6a7d90;
    }

    .lb-admin-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #132236;
    }

    .lb-admin-copy {
        margin: 0.7rem 0 0;
        max-width: 54rem;
        color: #5f7284;
        line-height: 1.6;
    }

    .lb-pages-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }

    .lb-page-card {
        padding: 1.2rem 1.25rem;
    }

    .lb-page-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }

    .lb-page-card__title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #132236;
    }

    .lb-page-card__key {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.6rem;
        border-radius: 999px;
        background: #eef4f8;
        color: #355168;
        font-size: 12px;
        font-weight: 700;
    }

    .lb-page-card__meta {
        display: grid;
        gap: 0.7rem;
        margin-bottom: 1rem;
    }

    .lb-page-kv {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.7rem;
        border-bottom: 1px solid #edf2f7;
    }

    .lb-page-kv:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .lb-page-kv__label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #718499;
    }

    .lb-page-kv__value {
        color: #173042;
        text-align: right;
    }

    .lb-page-card__actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .lb-admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0.7rem 0.95rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
        white-space: nowrap;
    }

    .lb-admin-btn:hover,
    .lb-admin-btn:focus {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .lb-admin-btn--ghost {
        background: #f8fbff;
        border-color: #d8e2ea;
        color: #173042;
    }

    .lb-admin-btn--ghost:hover,
    .lb-admin-btn--ghost:focus {
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .lb-admin-btn--primary {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #fff8f3;
        box-shadow: 0 14px 30px rgba(234, 88, 12, 0.24);
    }

    .lb-admin-btn--primary:hover,
    .lb-admin-btn--primary:focus {
        color: #ffffff;
        box-shadow: 0 18px 34px rgba(234, 88, 12, 0.3);
    }

    .lb-help {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        margin-left: 6px;
        border-radius: 999px;
        border: 1px solid #d8e2ea;
        background: #f8fbff;
        color: #173042;
        font-size: 12px;
        font-weight: 800;
        cursor: help;
        user-select: none;
    }

    .lb-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(10, 20, 30, 0.55);
        z-index: 10000;
    }

    .lb-modal.is-open {
        display: flex;
    }

    .lb-modal__panel {
        width: min(720px, 100%);
        border: 1px solid #d8e2ea;
        border-radius: 26px;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    .lb-modal__head {
        padding: 16px 18px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .lb-modal__title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #132236;
    }

    .lb-modal__sub {
        margin: 6px 0 0;
        color: #5f7284;
        line-height: 1.5;
        font-size: 13px;
    }

    .lb-modal__close {
        border: 1px solid #d8e2ea;
        background: #ffffff;
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .lb-modal__body {
        padding: 16px 18px;
        display: grid;
        gap: 12px;
    }

    .lb-field {
        display: grid;
        gap: 6px;
    }

    .lb-label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #718499;
        display: flex;
        align-items: center;
    }

    .lb-input,
    .lb-textarea,
    .lb-select {
        width: 100%;
        border: 1px solid #d8e2ea;
        border-radius: 16px;
        padding: 10px 12px;
        background: #ffffff;
        color: #173042;
        font-size: 14px;
        outline: none;
    }

    .lb-textarea {
        min-height: 92px;
        resize: vertical;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 13px;
    }

    .lb-note {
        color: #5f7284;
        font-size: 12px;
        line-height: 1.45;
    }

    .lb-error {
        display: none;
        padding: 10px 12px;
        border-radius: 16px;
        border: 1px solid #f0c8c8;
        background: #fff5f5;
        color: #7a1e1e;
        font-size: 13px;
        white-space: pre-wrap;
    }

    .lb-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid #edf2f7;
        background: #ffffff;
    }

    .lb-ctype-grid {
        display: grid;
        gap: 8px;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .lb-ctype-check {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 36px;
        padding: 8px 10px;
        border: 1px solid #d8e2ea;
        border-radius: 12px;
        background: #ffffff;
        color: #173042;
        font-size: 13px;
    }

    .lb-ctype-check input {
        margin: 0;
    }
</style>

<div class="lb-admin-ui">
    <section class="lb-admin-hero">
        <div class="lb-admin-hero__top">
            <h3 class="lb-admin-title">Страницы сайта</h3>
            <?php if ($is_schema_installed) { ?>
                <button type="button" class="lb-admin-btn lb-admin-btn--primary" data-lb-create-page>Новая страница</button>
            <?php } ?>
        </div>
        <p class="lb-admin-copy">
            <?php if ($is_schema_installed) { ?>
                Каждая страница — это готовый макет. Открывайте редактор, меняйте секции и блоки прямо на холсте.
            <?php } else { ?>
                Страницы недоступны для редактирования до установки компонента.
            <?php } ?>
        </p>
    </section>

<?php if ($is_schema_installed) { ?>
    <div class="lb-modal" id="lb-create-modal" aria-hidden="true">
        <div class="lb-modal__panel" role="dialog" aria-modal="true" aria-labelledby="lb-create-title">
            <div class="lb-modal__head">
                <div>
                    <h4 class="lb-modal__title" id="lb-create-title">Новый макет страницы</h4>
                    <p class="lb-modal__sub">Создайте пустой (нулевой) макет и сразу, при желании, привяжите его к главной или к набору URL-масок.</p>
                </div>
                <button type="button" class="lb-modal__close" data-lb-close>×</button>
            </div>
            <div class="lb-modal__body">
                <div class="lb-error" id="lb-create-error"></div>

                <div class="lb-field">
                    <div class="lb-label">Название макета<span class="lb-help" title="Понятное имя для админки. Например: Главная (Nordic)">?</span></div>
                    <input class="lb-input" type="text" id="lb-title" placeholder="Главная страница" autocomplete="off">
                </div>

                <div class="lb-field">
                    <div class="lb-label">Ключ (page_key)<span class="lb-help" title="Системный ключ латиницей. Должен быть уникальным. Используется в bindings и в рендере.">?</span></div>
                    <input class="lb-input" type="text" id="lb-key" placeholder="homepage" autocomplete="off">
                    <div class="lb-note">Подсказка: ключ можно оставить как есть — он автосгенерируется из названия.</div>
                </div>

                <div class="lb-field">
                    <div class="lb-label">Где применять<span class="lb-help" title="Можно не привязывать сейчас: просто создастся макет и откроется канвас. Для внутренних страниц Instant доступны готовые пресеты.">?</span></div>
                    <select class="lb-select" id="lb-apply">
                        <option value="none">Не привязывать сейчас</option>
                        <option value="homepage">Главная страница</option>
                        <option value="all_except_homepage">Все внутренние страницы (кроме главной)</option>
                        <option value="overlay_content_category_single">Категория одного типа контента</option>
                        <option value="overlay_content_category_all">Категории контента (выбрать типы, без масок)</option>
                        <option value="overlay_user_profile">Профиль пользователя</option>
                        <option value="url">Выборочные страницы (URL-маски, экспертно)</option>
                        <option value="overlay_content_category_board">Категория объявлений (board, legacy)</option>
                    </select>
                </div>

                <div class="lb-field" id="lb-binding-key-wrap" style="display:none">
                    <div class="lb-label">binding_key<span class="lb-help" title="Уникальный ключ правила. Для full takeover используем префикс page.*">?</span></div>
                    <input class="lb-input" type="text" id="lb-binding-key" placeholder="page.homepage" autocomplete="off">
                </div>

                <div class="lb-field" id="lb-exclude-ctypes-wrap" style="display:none">
                    <div class="lb-label">Не показывать в типах контента<span class="lb-help" title="Отметьте типы, где макет НЕ должен применяться. Без ручного ввода масок.">?</span></div>
                    <?php if ($content_types) { ?>
                        <div class="lb-ctype-grid">
                            <?php foreach ($content_types as $ctype) { ?>
                                <?php $ctype_name = trim((string) ($ctype['name'] ?? '')); if ($ctype_name === '') { continue; } ?>
                                <label class="lb-ctype-check" for="lb-exclude-ctype-<?php html($ctype_name); ?>">
                                    <input type="checkbox" id="lb-exclude-ctype-<?php html($ctype_name); ?>" value="<?php html($ctype_name); ?>" data-lb-exclude-ctype="1">
                                    <span><?php html((string) ($ctype['title'] ?? $ctype_name)); ?></span>
                                </label>
                            <?php } ?>
                        </div>
                        <div class="lb-note">Если ничего не отмечено, макет будет применяться для всех типов контента.</div>
                    <?php } else { ?>
                        <div class="lb-note">Не удалось загрузить типы контента. Используйте режим URL-масок (экспертно).</div>
                    <?php } ?>
                </div>

                <div class="lb-field" id="lb-single-ctype-wrap" style="display:none">
                    <div class="lb-label">Тип контента<span class="lb-help" title="Выберите один тип контента, для которого будет работать страница категории.">?</span></div>
                    <?php if ($content_types) { ?>
                        <select class="lb-select" id="lb-single-ctype">
                            <option value="">Выберите тип контента</option>
                            <?php foreach ($content_types as $ctype) { ?>
                                <?php $ctype_name = trim((string) ($ctype['name'] ?? '')); if ($ctype_name === '') { continue; } ?>
                                <option value="<?php html($ctype_name); ?>"><?php html((string) ($ctype['title'] ?? $ctype_name)); ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <div class="lb-note">Не удалось загрузить типы контента. Используйте режим URL-масок (экспертно).</div>
                    <?php } ?>
                </div>

                <div class="lb-field" id="lb-url-masks-wrap" style="display:none">
                    <div class="lb-label">URL-маски<span class="lb-help" title="По одной маске на строку. Символ * означает любой хвост. Примеры: promo/*, landing/*">?</span></div>
                    <textarea class="lb-textarea" id="lb-url-masks" placeholder="promo/*\nlanding/*"></textarea>
                    <div class="lb-note">Это экспертный режим. Для простого сценария лучше выбрать «Категории контента (выбрать типы, без масок)».</div>
                    <div class="lb-note">Важно: правило начнёт работать для посетителей только если страница будет опубликована. Для админа предпросмотр доступен и в черновике.</div>
                </div>

                <div class="lb-field" id="lb-exclude-masks-wrap" style="display:none">
                    <div class="lb-label">Исключить URL (маски)<span class="lb-help" title="Если URL совпал с любой маской отсюда — правило НЕ применится. По одной маске на строку.">?</span></div>
                    <textarea class="lb-textarea" id="lb-exclude-masks" placeholder="admin/*\napi/*"></textarea>
                </div>

                <input type="hidden" id="lb-csrf" value="<?php echo cmsForm::getCSRFToken(); ?>">
            </div>
            <div class="lb-actions">
                <button type="button" class="lb-admin-btn lb-admin-btn--ghost" data-lb-close>Отмена</button>
                <button type="button" class="lb-admin-btn lb-admin-btn--primary" id="lb-create-submit">Создать и открыть канвас</button>
            </div>
        </div>
    </div>

    <?php ob_start(); ?>
    <script>
        (function () {
            const createUrl = <?php echo json_encode($create_page_url, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const createBindingUrl = <?php echo json_encode($create_binding_url ?? '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const deleteUrl = <?php echo json_encode($delete_page_url ?? '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const setStatusUrl = <?php echo json_encode($set_status_url ?? '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const publishPageUrl = <?php echo json_encode($publish_page_url ?? '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

            const modal = document.getElementById('lb-create-modal');
            const errorBox = document.getElementById('lb-create-error');
            const titleInput = document.getElementById('lb-title');
            const keyInput = document.getElementById('lb-key');
            const applySelect = document.getElementById('lb-apply');
            const bindingKeyWrap = document.getElementById('lb-binding-key-wrap');
            const bindingKeyInput = document.getElementById('lb-binding-key');
            const urlMasksWrap = document.getElementById('lb-url-masks-wrap');
            const urlMasksInput = document.getElementById('lb-url-masks');
            const excludeMasksWrap = document.getElementById('lb-exclude-masks-wrap');
            const excludeMasksInput = document.getElementById('lb-exclude-masks');
            const excludeCtypesWrap = document.getElementById('lb-exclude-ctypes-wrap');
            const excludeCtypeInputs = Array.from(document.querySelectorAll('[data-lb-exclude-ctype="1"]'));
            const singleCtypeWrap = document.getElementById('lb-single-ctype-wrap');
            const singleCtypeSelect = document.getElementById('lb-single-ctype');
            const csrfInput = document.getElementById('lb-csrf');
            const submitBtn = document.getElementById('lb-create-submit');

            function slugify(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 120);
            }

            function setError(message) {
                if (!message) {
                    errorBox.style.display = 'none';
                    errorBox.textContent = '';
                    return;
                }
                errorBox.style.display = 'block';
                errorBox.textContent = message;
            }

            function openModal() {
                setError('');
                applySelect.value = 'none';
                bindingKeyWrap.style.display = 'none';
                urlMasksWrap.style.display = 'none';
                excludeMasksWrap.style.display = 'none';
                excludeCtypesWrap.style.display = 'none';
                singleCtypeWrap.style.display = 'none';
                excludeCtypeInputs.forEach(function (input) {
                    input.checked = false;
                });
                if (singleCtypeSelect) {
                    singleCtypeSelect.value = '';
                }
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                setTimeout(function () {
                    titleInput.focus();
                }, 0);
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            function syncKeyDefaults() {
                const title = titleInput.value.trim();
                if (!keyInput.value.trim()) {
                    keyInput.value = slugify(title) || '';
                }
            }

            function syncPresetDefaults() {
                const apply = applySelect.value;

                if (apply === 'homepage') {
                    if (!titleInput.value.trim()) {
                        titleInput.value = 'Главная страница';
                    }
                    if (!keyInput.value.trim()) {
                        keyInput.value = 'homepage';
                    }
                    return;
                }

                if (apply === 'all_except_homepage') {
                    if (!titleInput.value.trim()) {
                        titleInput.value = 'Сквозные секции сайта';
                    }
                    if (!keyInput.value.trim()) {
                        keyInput.value = 'site-all';
                    }
                    return;
                }

                if (apply === 'overlay_content_category_single' && singleCtypeSelect) {
                    const ctype = String(singleCtypeSelect.value || '').trim();
                    if (ctype && !keyInput.value.trim()) {
                        keyInput.value = 'category-' + slugify(ctype);
                    }
                }
            }

            function syncBindingDefaults() {
                const pageKey = keyInput.value.trim();
                const apply = applySelect.value;

                if (apply === 'homepage') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = 'none';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = 'page.homepage';
                    }
                } else if (apply === 'all_except_homepage') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = 'none';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = 'page.all_internal';
                    }
                } else if (apply === 'url') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = '';
                    excludeMasksWrap.style.display = '';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = 'none';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = pageKey ? ('page.' + pageKey) : 'page.marketing';
                    }
                } else if (apply === 'overlay_content_category_single') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = '';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = 'overlay.content_category.single';
                    }
                } else if (apply === 'overlay_content_category_all') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = '';
                    singleCtypeWrap.style.display = 'none';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = 'overlay.content_category.default';
                    }
                } else if (apply === 'overlay_content_category_board') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = 'none';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = 'overlay.content_category.board';
                    }
                } else if (apply === 'overlay_user_profile') {
                    bindingKeyWrap.style.display = '';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = 'none';
                    if (!bindingKeyInput.value.trim()) {
                        bindingKeyInput.value = 'overlay.user_profile.default';
                    }
                } else {
                    bindingKeyWrap.style.display = 'none';
                    urlMasksWrap.style.display = 'none';
                    excludeMasksWrap.style.display = 'none';
                    excludeCtypesWrap.style.display = 'none';
                    singleCtypeWrap.style.display = 'none';
                }
            }

            function resolveAdapterKeyForApply(apply) {
                if (apply === 'homepage') {
                    return 'standalone_landing';
                }

                if (apply === 'all_except_homepage') {
                    return 'internal_content_generic';
                }

                if (apply === 'overlay_content_category_board') {
                    return 'content_category_generic';
                }

                if (apply === 'overlay_content_category_single') {
                    return 'content_category_generic';
                }

                if (apply === 'overlay_content_category_all') {
                    return 'content_category_generic';
                }

                if (apply === 'overlay_user_profile') {
                    return 'user_profile';
                }

                return '';
            }

            async function readJsonOrText(response) {
                const contentType = (response.headers && response.headers.get) ? (response.headers.get('content-type') || '') : '';
                const text = await response.text();
                if (contentType.indexOf('application/json') !== -1) {
                    try {
                        return { json: JSON.parse(text), text: text };
                    } catch (e) {
                        return { json: null, text: text };
                    }
                }
                try {
                    return { json: JSON.parse(text), text: text };
                } catch (e) {
                    return { json: null, text: text };
                }
            }

            function trimServerText(value, limit) {
                const str = String(value || '');
                const max = limit || 600;
                if (str.length <= max) {
                    return str;
                }
                return str.slice(0, max) + '\n…';
            }

            async function createPageAndMaybeBind() {
                const title = titleInput.value.trim();
                const key = keyInput.value.trim();
                const apply = applySelect.value;

                if (!title) {
                    setError('Укажите название макета.');
                    return;
                }

                if (!key) {
                    setError('Укажите ключ (page_key).');
                    return;
                }

                const body = new URLSearchParams();
                body.set('title', title);
                body.set('key', key);
                body.set('mode', 'instant_content_body');
                body.set('status', 'draft');
                body.set('template', 'nordic');
                const shouldUseStarterSeed = (apply === 'homepage' || apply === 'all_except_homepage');
                body.set('disable_starter_seed', shouldUseStarterSeed ? '0' : '1');
                body.set('inherit_global_sections', (apply === 'homepage' || apply === 'overlay_content_category_single' || apply === 'overlay_content_category_all' || apply === 'overlay_content_category_board' || apply === 'overlay_user_profile') ? '1' : '0');
                body.set('use_as_global_sections_source', apply === 'all_except_homepage' ? '1' : '0');
                body.set('csrf_token', (csrfInput && csrfInput.value) ? csrfInput.value : '');

                const adapterKey = resolveAdapterKeyForApply(apply);
                if (adapterKey) {
                    body.set('adapter_key', adapterKey);
                }

                const response = await fetch(createUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString(),
                    credentials: 'same-origin'
                });

                const parsed = await readJsonOrText(response);
                const result = parsed.json;

                if (!response.ok) {
                    const serverHint = result && result.message ? result.message : trimServerText(parsed.text);
                    setError('Ошибка сервера (' + response.status + ').\n' + (serverHint || '')); 
                    return;
                }

                if (!result || typeof result !== 'object') {
                    setError('Сервер вернул неожиданный ответ вместо JSON.\n' + trimServerText(parsed.text));
                    return;
                }

                if (result.error) {
                    setError(result.message || 'Не удалось создать страницу');
                    return;
                }

                if (apply !== 'none' && createBindingUrl) {
                    const bindingKey = bindingKeyInput.value.trim();
                    if (!bindingKey) {
                        setError('Укажите binding_key для правила применения.');
                        return;
                    }

                    const bindingBody = new URLSearchParams();
                    bindingBody.set('csrf_token', (csrfInput && csrfInput.value) ? csrfInput.value : '');
                    bindingBody.set('binding_key', bindingKey);
                    bindingBody.set('page_key', key);
                    bindingBody.set('title', title);

                    if (apply === 'homepage') {
                        bindingBody.set('route_params_json', JSON.stringify({ ctrl: '', action: 'index', page_type: 'homepage' }));
                    }

                    if (apply === 'all_except_homepage') {
                        bindingBody.set('route_params_json', JSON.stringify({ page_type: '!homepage' }));
                    }

                    if (apply === 'url') {
                        bindingBody.set('url_masks', urlMasksInput.value || '');
                        bindingBody.set('exclude_masks', excludeMasksInput.value || '');
                    }

                    if (apply === 'overlay_content_category_board') {
                        bindingBody.set('route_params_json', JSON.stringify({ overlay: 'content_category', ctype: 'board' }));
                    }

                    if (apply === 'overlay_content_category_single') {
                        const selectedCtype = singleCtypeSelect ? String(singleCtypeSelect.value || '').trim() : '';
                        if (!selectedCtype) {
                            setError('Выберите тип контента для страницы категории.');
                            return;
                        }
                        bindingBody.set('route_params_json', JSON.stringify({ overlay: 'content_category', ctype: selectedCtype }));
                    }

                    if (apply === 'overlay_content_category_all') {
                        const allCtypes = [];
                        const excludedCtypes = [];

                        excludeCtypeInputs.forEach(function (input) {
                            const value = String((input && input.value) ? input.value : '').trim();
                            if (!value) {
                                return;
                            }
                            allCtypes.push(value);
                            if (input.checked) {
                                excludedCtypes.push(value);
                            }
                        });

                        const allowedCtypes = allCtypes.filter(function (value) {
                            return excludedCtypes.indexOf(value) === -1;
                        });

                        if (allCtypes.length > 0 && allowedCtypes.length === 0) {
                            setError('Нельзя исключить все типы контента. Оставьте хотя бы один тип, где макет должен показываться.');
                            return;
                        }

                        const routeParams = { overlay: 'content_category' };
                        if (allCtypes.length > 0 && allowedCtypes.length < allCtypes.length) {
                            routeParams.ctype = allowedCtypes;
                        }

                        bindingBody.set('route_params_json', JSON.stringify(routeParams));
                    }

                    if (apply === 'overlay_user_profile') {
                        bindingBody.set('route_params_json', JSON.stringify({ overlay: 'user_profile' }));
                    }

                    try {
                        const bindResp = await fetch(createBindingUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body: bindingBody.toString(),
                            credentials: 'same-origin'
                        });

                        const bindParsed = await readJsonOrText(bindResp);
                        const bindResult = bindParsed.json;

                        if (!bindResp.ok) {
                            const hint = bindResult && bindResult.message ? bindResult.message : trimServerText(bindParsed.text);
                            window.alert('Ошибка сервера при создании правила (' + bindResp.status + ').\n' + (hint || ''));
                            return;
                        }

                        if (!bindResult || typeof bindResult !== 'object') {
                            window.alert('Сервер вернул неожиданный ответ вместо JSON при создании правила.\n' + trimServerText(bindParsed.text));
                            return;
                        }

                        if (bindResult && bindResult.error) {
                            window.alert(bindResult.message || 'Не удалось создать правило применения.');
                        }
                    } catch (e) {
                        console.error(e);
                        window.alert('Не удалось создать правило применения.');
                    }
                }

                closeModal();

                window.location.href = result.page.canvas_url;
            }

            async function deletePage(pageKey, pageTitle) {
                if (!deleteUrl) {
                    return;
                }

                const confirmed = window.confirm('Удалить страницу "' + (pageTitle || pageKey) + '"?\n\nЭто удалит макет и связанные данные.');
                if (!confirmed) {
                    return;
                }

                const body = new URLSearchParams();
                body.set('key', pageKey);
                body.set('csrf_token', (csrfInput && csrfInput.value) ? csrfInput.value : '');

                const response = await fetch(deleteUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString(),
                    credentials: 'same-origin'
                });

                const parsed = await readJsonOrText(response);
                const result = parsed.json;

                if (!response.ok) {
                    const serverHint = result && result.message ? result.message : trimServerText(parsed.text);
                    window.alert('Ошибка сервера (' + response.status + ').\n' + (serverHint || ''));
                    return;
                }

                if (!result || typeof result !== 'object') {
                    window.alert('Сервер вернул неожиданный ответ вместо JSON.\n' + trimServerText(parsed.text));
                    return;
                }

                if (result.error) {
                    window.alert(result.message || 'Не удалось удалить страницу');
                    return;
                }

                window.location.reload();
            }

            async function setPageStatus(pageKey, status) {
                if (!setStatusUrl) {
                    return;
                }

                const body = new URLSearchParams();
                body.set('key', pageKey);
                body.set('status', status);
                body.set('csrf_token', (csrfInput && csrfInput.value) ? csrfInput.value : '');

                const response = await fetch(setStatusUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString(),
                    credentials: 'same-origin'
                });

                const parsed = await readJsonOrText(response);
                const result = parsed.json;

                if (!response.ok) {
                    const serverHint = result && result.message ? result.message : trimServerText(parsed.text);
                    window.alert('Ошибка сервера (' + response.status + ').\n' + (serverHint || ''));
                    return;
                }

                if (!result || typeof result !== 'object') {
                    window.alert('Сервер вернул неожиданный ответ вместо JSON.\n' + trimServerText(parsed.text));
                    return;
                }

                if (result.error) {
                    window.alert(result.message || 'Не удалось обновить статус страницы');
                    return;
                }

                window.location.reload();
            }

            async function publishPage(pageKey) {
                if (!publishPageUrl) {
                    return;
                }

                const body = new URLSearchParams();
                body.set('key', pageKey);
                body.set('csrf_token', (csrfInput && csrfInput.value) ? csrfInput.value : '');

                const response = await fetch(publishPageUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString(),
                    credentials: 'same-origin'
                });

                const parsed = await readJsonOrText(response);
                const result = parsed.json;

                if (!response.ok) {
                    const serverHint = result && result.message ? result.message : trimServerText(parsed.text);
                    window.alert('Ошибка сервера (' + response.status + ').\n' + (serverHint || ''));
                    return;
                }

                if (!result || typeof result !== 'object') {
                    window.alert('Сервер вернул неожиданный ответ вместо JSON.\n' + trimServerText(parsed.text));
                    return;
                }

                if (result.error) {
                    window.alert(result.message || 'Не удалось опубликовать SSR');
                    return;
                }

                window.location.reload();
            }

            document.addEventListener('click', function (event) {
                const target = event.target.closest('[data-lb-create-page], a.lb-create-page');
                if (!target) {
                    return;
                }
                event.preventDefault();
                openModal();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('click', function (event) {
                const close = event.target.closest('[data-lb-close]');
                if (!close) {
                    return;
                }
                event.preventDefault();
                closeModal();
            });

            titleInput.addEventListener('input', function () {
                syncKeyDefaults();
                syncBindingDefaults();
            });

            keyInput.addEventListener('input', function () {
                syncBindingDefaults();
            });

            applySelect.addEventListener('change', function () {
                syncPresetDefaults();
                syncBindingDefaults();
            });

            if (singleCtypeSelect) {
                singleCtypeSelect.addEventListener('change', function () {
                    syncPresetDefaults();
                    syncBindingDefaults();
                });
            }

            submitBtn.addEventListener('click', function () {
                setError('');
                submitBtn.disabled = true;
                createPageAndMaybeBind().catch(function (error) {
                    console.error(error);
                    setError('Ошибка создания страницы.\n' + (error && error.message ? error.message : String(error || '')));
                }).finally(function () {
                    submitBtn.disabled = false;
                });
            });

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('[data-lb-delete-page]');
                if (!btn) {
                    return;
                }
                event.preventDefault();
                deletePage(btn.getAttribute('data-page-key') || '', btn.getAttribute('data-page-title') || '');
            });

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('[data-lb-set-status]');
                if (!btn) {
                    return;
                }

                if (!setStatusUrl) {
                    return;
                }

                event.preventDefault();

                const pageKey = btn.getAttribute('data-page-key') || '';
                const status = btn.getAttribute('data-lb-set-status') || '';
                const pageTitle = btn.getAttribute('data-page-title') || pageKey;

                if (!pageKey || !status) {
                    return;
                }

                const label = status === 'published' ? 'опубликовать' : 'снять с публикации';
                const confirmed = window.confirm('Подтвердить действие: ' + label + ' страницу "' + pageTitle + '"?');
                if (!confirmed) {
                    return;
                }

                setPageStatus(pageKey, status).catch(function (error) {
                    console.error(error);
                    window.alert('Не удалось обновить статус страницы.');
                });
            });

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('[data-nb-publish-page]');
                if (!btn) {
                    return;
                }

                if (!publishPageUrl) {
                    return;
                }

                event.preventDefault();

                const pageKey = btn.getAttribute('data-page-key') || '';
                const pageTitle = btn.getAttribute('data-page-title') || pageKey;

                if (!pageKey) {
                    return;
                }

                const confirmed = window.confirm('Опубликовать SSR для страницы "' + pageTitle + '"?\n\nЭто обновит контент для Live-виджета nordicbuilder_render.');
                if (!confirmed) {
                    return;
                }

                btn.disabled = true;
                publishPage(pageKey).catch(function (error) {
                    console.error(error);
                    window.alert('Не удалось опубликовать SSR.');
                }).finally(function () {
                    btn.disabled = false;
                });
            });
        })();
    </script>
    <?php $this->addBottom(ob_get_clean()); ?>
<?php } ?>

    <section class="lb-pages-grid">
        <?php foreach ($pages as $page) { ?>
            <article class="lb-page-card">
                <div class="lb-page-card__top">
                    <div>
                        <h4 class="lb-page-card__title"><?php html($page['title']); ?></h4>
                    </div>
                    <?php if (!in_array($page['key'], ['homepage', 'ads-category', 'profile-cover'])) { ?><span class="lb-page-card__key"><?php html($page['key']); ?></span><?php } ?>
                </div>
                <div class="lb-page-card__meta">
                    <div class="lb-page-kv">
                        <span class="lb-page-kv__label">Режим</span>
                        <span class="lb-page-kv__value"><?php html($page_mode_titles[$page['mode']] ?? $page['mode']); ?></span>
                    </div>
                    <div class="lb-page-kv">
                        <span class="lb-page-kv__label">Статус</span>
                        <span class="lb-page-kv__value"><?php html($page_status_titles[$page['status']] ?? $page['status']); ?></span>
                    </div>
                    <div class="lb-page-kv">
                        <span class="lb-page-kv__label">Обновлено</span>
                        <span class="lb-page-kv__value"><?php html($page['updated_at']); ?></span>
                    </div>
                </div>
                <div class="lb-page-card__actions">
                    <a class="lb-admin-btn lb-admin-btn--ghost" href="<?php html($page['view_url']); ?>" target="_blank" rel="noopener">Предпросмотр</a>
                    <a class="lb-admin-btn lb-admin-btn--primary" href="<?php html($page['canvas_url']); ?>">Открыть редактор</a>
                    <?php if (!empty($bindings_url)) { ?>
                        <a class="lb-admin-btn lb-admin-btn--ghost" href="<?php html($bindings_url . '?page_key=' . urlencode((string) ($page['key'] ?? ''))); ?>">Правила применения</a>
                    <?php } ?>
                    <?php if (!empty($publish_page_url) && !empty($is_schema_installed)) { ?>
                        <button class="lb-admin-btn lb-admin-btn--ghost" type="button" data-nb-publish-page="1" data-page-key="<?php html($page['key']); ?>" data-page-title="<?php html($page['title']); ?>">Опубликовать SSR</button>
                    <?php } ?>
                    <?php if (!empty($set_status_url)) { ?>
                        <?php if (($page['status'] ?? 'draft') !== 'published') { ?>
                            <button class="lb-admin-btn lb-admin-btn--ghost" type="button" data-lb-set-status="published" data-page-key="<?php html($page['key']); ?>" data-page-title="<?php html($page['title']); ?>">Опубликовать</button>
                        <?php } else { ?>
                            <button class="lb-admin-btn lb-admin-btn--ghost" type="button" data-lb-set-status="draft" data-page-key="<?php html($page['key']); ?>" data-page-title="<?php html($page['title']); ?>">Снять с публикации</button>
                        <?php } ?>
                    <?php } ?>
                    <?php if (!empty($delete_page_url) && !empty($is_schema_installed)) { ?>
						<button class="lb-admin-btn lb-admin-btn--ghost" type="button" data-lb-delete-page="1" data-page-key="<?php html($page['key']); ?>" data-page-title="<?php html($page['title']); ?>">Удалить</button>
					<?php } ?>
                </div>
            </article>
        <?php } ?>
    </section>
</div>
