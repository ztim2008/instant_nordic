<?php

$page_mode_titles = [
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

$this->setPageTitle('Нордик: страницы');
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы');
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'add',
    'title' => 'Новая страница',
    'href'  => $is_schema_installed ? '#' : 'javascript:void(0)',
    'icon'  => 'plus-circle'
]);

?>
<div class="card mb-4">
    <div class="card-body">
        <h3 class="h5 mb-3">Нордик: реестр страниц</h3>
        <p class="text-muted mb-0">
            <?php if ($is_schema_installed) { ?>
                Компонент работает на собственных таблицах landingbuilder и уже готов хранить версии макетов и системные виджеты.
            <?php } else { ?>
                Таблицы компонента ещё не установлены, поэтому сейчас показан безопасный временный набор страниц из кода.
            <?php } ?>
        </p>
    </div>
</div>

<?php if ($is_schema_installed) { ?>
    <?php ob_start(); ?>
    <script>
        (function () {
            const createUrl = <?php echo json_encode($create_page_url, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

            async function createPage() {
                const title = window.prompt('Название страницы', 'Новая страница Нордик');
                if (!title) {
                    return;
                }

                const key = window.prompt('Ключ страницы латиницей', title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''));
                if (!key) {
                    return;
                }

                const body = new URLSearchParams();
                body.set('title', title);
                body.set('key', key);
                body.set('mode', 'full_takeover');
                body.set('status', 'draft');
                body.set('template', 'nordic');

                const response = await fetch(createUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body.toString(),
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (result.error) {
                    window.alert(result.message || 'Не удалось создать страницу');
                    return;
                }

                window.location.href = result.page.canvas_url;
            }

            document.addEventListener('click', function (event) {
                const target = event.target.closest('a.btn, a.tool_add, a.add');
                if (!target) {
                    return;
                }

                const title = (target.getAttribute('title') || '').trim();
                if (title !== 'Новая страница') {
                    return;
                }

                event.preventDefault();
                createPage().catch(function (error) {
                    console.error(error);
                    window.alert('Ошибка создания страницы');
                });
            });
        })();
    </script>
    <?php $this->addBottom(ob_get_clean()); ?>
<?php } ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Страница</th>
                        <th>Ключ</th>
                        <th>Режим</th>
                        <th>Статус</th>
                        <th>Обновлено</th>
                        <th class="text-right">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page) { ?>
                        <tr>
                            <td><?php html($page['title']); ?></td>
                            <td><code><?php html($page['key']); ?></code></td>
                            <td><?php html($page_mode_titles[$page['mode']] ?? $page['mode']); ?></td>
                            <td><?php html($page_status_titles[$page['status']] ?? $page['status']); ?></td>
                            <td><?php html($page['updated_at']); ?></td>
                            <td class="text-right">
                                <a class="btn btn-sm btn-primary" href="<?php html($page['canvas_url']); ?>">Открыть редактор</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>