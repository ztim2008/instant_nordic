<?php

$this->setPageTitle('Нордик: страницы');
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы');
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'add',
    'title' => 'Новая страница',
    'href'  => '#',
    'icon'  => 'plus-circle'
]);

?>
<div class="card mb-4">
    <div class="card-body">
        <h3 class="h5 mb-3">Skeleton backend для landingbuilder</h3>
        <p class="text-muted mb-0">Это первый безопасный каркас. Здесь уже виден главный вход в редактор страниц и точка перехода в visual canvas.</p>
    </div>
</div>

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
                            <td><code><?php html($page['mode']); ?></code></td>
                            <td><?php html($page['status']); ?></td>
                            <td><?php html($page['updated_at']); ?></td>
                            <td class="text-right">
                                <a class="btn btn-sm btn-primary" href="<?php html($page['canvas_url']); ?>">Открыть canvas</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>