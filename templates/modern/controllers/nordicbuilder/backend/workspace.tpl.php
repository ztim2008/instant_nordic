<?php /** @var array $workspace_summary */ ?>
<?php $migration = $migration ?? ['total_pages' => 0, 'imported_count' => 0, 'pending_count' => 0]; ?>
<?php
$document_status_labels = [
    'draft'     => 'Черновик',
    'prototype' => 'Прототип',
    'published' => 'Опубликовано'
];
$page_type_labels = [
    'standalone'     => 'Отдельная',
    'system_overlay' => 'Системное встраивание',
    'ctype_overlay'  => 'Встраивание в тип контента'
];
$editor_mode_labels = [
    'canvas'  => 'Холст',
    'overlay' => 'Встраивание'
];
?>
<div class="content_list">
    <div class="mb-4">
        <h1><?php echo html($page_title); ?></h1>
        <p class="text-muted mb-0"><?php echo html($page_note); ?></p>
    </div>

    <div class="card mb-4 border-primary">
        <div class="card-body d-md-flex justify-content-between align-items-center">
            <div class="pr-md-4 mb-3 mb-md-0">
                <div class="small text-uppercase text-muted font-weight-bold mb-2">Primary user flow</div>
                <h2 class="h4 mb-2">Главный экран теперь визуальный</h2>
                <div class="text-muted">Обычная работа со страницей должна идти через canvas. Эта панель остается только для импорта, диагностики и внутренней проверки документов.</div>
                <div class="small text-muted mt-2">Текущий ключ visual workspace: <?php echo html($primary_document_key); ?></div>
            </div>
            <div class="text-md-right">
                <a class="btn btn-primary mb-2 mb-md-0" href="<?php echo html($visual_workspace_url); ?>">Открыть главный визуальный экран</a>
                <a class="btn btn-outline-secondary ml-md-2" href="<?php echo html($starter_canvas_url); ?>">Открыть starter vertical slice</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Контракты</div>
                <div class="card-body">
                    <div class="display-4 mb-2"><?php echo (int) $workspace_summary['contracts_count']; ?></div>
                    <div class="text-muted">Всего контрактов базового слоя</div>
                    <div class="mt-3">Корректных: <?php echo (int) $workspace_summary['valid_contracts_count']; ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Таблицы хранения</div>
                <div class="card-body">
                    <div class="display-4 mb-2"><?php echo (int) $persistence['installed_tables_count']; ?>/<?php echo (int) $persistence['total_tables_count']; ?></div>
                    <div class="text-muted">Установленных SQL-хранилищ</div>
                    <div class="mt-3">
                        <?php if ($persistence['is_installed']) { ?>
                            <span class="badge badge-success">готово</span>
                        <?php } else { ?>
                            <span class="badge badge-warning">нужна установка</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Состояние рабочей области</div>
                <div class="card-body">
                    <?php if ($persistence['is_installed']) { ?>
                        <div class="text-success">Foundation persistence layer готов к подключению save/load flow.</div>
                    <?php } else { ?>
                        <div class="text-muted">После установки пакета рабочая область сможет читать и сохранять документы страниц, токены пресетов и параметры привязок.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Карта хранения</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 bg-white">
                    <thead>
                        <tr>
                            <th>Хранилище</th>
                            <th>Контракт</th>
                            <th>Цель хранения</th>
                            <th>Записей</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($persistence['tables'] as $table) { ?>
                            <tr>
                                <td>
                                    <strong><?php echo html($table['title']); ?></strong><br>
                                    <span class="text-muted"><?php echo html($table['table_name']); ?></span>
                                </td>
                                <td><?php echo html($table['contract_key']); ?></td>
                                <td><?php echo html($table['storage_target']); ?></td>
                                <td><?php echo (int) $table['count']; ?></td>
                                <td>
                                    <?php if ($table['is_installed']) { ?>
                                        <span class="badge badge-success">установлено</span>
                                    <?php } else { ?>
                                        <span class="badge badge-warning">ожидает</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">Документы страниц</div>
                <div class="card-body p-0">
                    <?php if ($page_documents) { ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($page_documents as $document) { ?>
                                <a class="list-group-item list-group-item-action <?php echo $active_document_key === $document['key'] ? 'active' : ''; ?>" href="<?php echo html($workspace_url . '?document_key=' . urlencode($document['key'])); ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong><?php echo html($document['title']); ?></strong>
                                        <span><?php echo html($document_status_labels[$document['status']] ?? $document['status']); ?></span>
                                    </div>
                                    <div class="small <?php echo $active_document_key === $document['key'] ? 'text-white-50' : 'text-muted'; ?> mt-1">
                                        <?php echo html($document['key']); ?> | <?php echo html($page_type_labels[$document['page_type']] ?? $document['page_type']); ?> | <?php echo html($editor_mode_labels[$document['editor_mode']] ?? $document['editor_mode']); ?>
                                    </div>
                                    <div class="small mt-2 <?php echo $active_document_key === $document['key'] ? 'text-white-50' : 'text-primary'; ?>">Открыть на холсте: <?php echo html(href_to('admin', 'controllers', ['edit', 'nordicbuilder', 'canvas']) . '?document_key=' . urlencode($document['key'])); ?></div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="p-3 text-muted">Сохраненных документов страниц пока нет. Кнопка выше создаст стартовый vertical slice автоматически.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card mb-4">
                <div class="card-header">Импорт и миграция из landingbuilder</div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="h2 mb-1"><?php echo (int) ($migration['total_pages'] ?? 0); ?></div>
                            <div class="text-muted small">Всего старых страниц</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="h2 mb-1 text-success"><?php echo (int) ($migration['imported_count'] ?? 0); ?></div>
                            <div class="text-muted small">Уже в nordicbuilder</div>
                        </div>
                        <div class="col-md-4">
                            <div class="h2 mb-1 text-primary"><?php echo (int) ($migration['pending_count'] ?? 0); ?></div>
                            <div class="text-muted small">Ожидают миграции</div>
                        </div>
                    </div>

                    <form action="<?php echo html($workspace_url); ?>" method="post">
                        <?php echo html_csrf_token(); ?>
                        <div class="form-row align-items-end">
                            <div class="col-md-8 mb-3">
                                <label class="mb-1">Страница landingbuilder</label>
                                <select name="landingbuilder_page_key" class="form-control">
                                    <?php foreach ($landing_pages as $page) { ?>
                                        <option value="<?php echo html($page['key']); ?>"><?php echo html($page['title'] . ' [' . $page['key'] . ']' . (!empty($page['is_imported']) ? ' | уже импортирована' : '')); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="submit" name="import_bridge_page" value="1" class="btn btn-outline-primary btn-block">Импортировать в Нордик</button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <form action="<?php echo html($workspace_url); ?>" method="post">
                        <?php echo html_csrf_token(); ?>
                        <div class="form-row align-items-end">
                            <div class="col-md-8 mb-3">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="overwrite_existing_documents" name="overwrite_existing_documents" <?php echo !empty($bulk_overwrite_existing) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="overwrite_existing_documents">
                                        Перезаписать уже импортированные документы свежими данными из landingbuilder
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="submit" name="import_bridge_pages_bulk" value="1" class="btn btn-primary btn-block" <?php echo empty($landing_pages) ? 'disabled' : ''; ?>>Импортировать все ожидающие</button>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($migration_report['summary'])) { ?>
                        <div class="alert <?php echo empty($migration_report['summary']['failed_count']) ? 'alert-success' : 'alert-warning'; ?> mt-4 mb-4">
                            <strong>Отчет по миграции:</strong>
                            обработано <?php echo (int) $migration_report['summary']['requested_count']; ?>,
                            импортировано <?php echo (int) $migration_report['summary']['imported_count']; ?>,
                            пропущено <?php echo (int) $migration_report['summary']['skipped_count']; ?>,
                            ошибок <?php echo (int) $migration_report['summary']['failed_count']; ?>.
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered bg-white mb-0">
                                <thead>
                                    <tr>
                                        <th>Страница</th>
                                        <th>Результат</th>
                                        <th>Сообщение</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($migration_report['items'] ?? []) as $item) { ?>
                                        <tr>
                                            <td><strong><?php echo html($item['title']); ?></strong><br><span class="text-muted small"><?php echo html($item['key']); ?></span></td>
                                            <td>
                                                <?php if (($item['status'] ?? '') === 'imported') { ?>
                                                    <span class="badge badge-success">импортировано</span>
                                                <?php } elseif (($item['status'] ?? '') === 'skipped') { ?>
                                                    <span class="badge badge-secondary">пропущено</span>
                                                <?php } else { ?>
                                                    <span class="badge badge-danger">ошибка</span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo html($item['message'] ?? ''); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-0">
                            <thead>
                                <tr>
                                    <th>Страница</th>
                                    <th>Тип страницы</th>
                                    <th>Статус legacy</th>
                                    <th>Состояние миграции</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($landing_pages as $page) { ?>
                                    <tr>
                                        <td><strong><?php echo html($page['title']); ?></strong><br><span class="text-muted small"><?php echo html($page['key']); ?></span></td>
                                        <td><?php echo html($page_type_labels[$page['page_type'] ?: 'standalone'] ?? ($page['page_type'] ?: 'standalone')); ?></td>
                                        <td><?php echo html($document_status_labels[$page['status']] ?? $page['status']); ?></td>
                                        <td>
                                            <?php if (!empty($page['is_imported'])) { ?>
                                                <span class="badge badge-success">импортировано</span>
                                                <span class="text-muted small d-block mt-1"><?php echo html($document_status_labels[$page['document_status'] ?: 'draft'] ?? ($page['document_status'] ?: 'draft')); ?></span>
                                            <?php } else { ?>
                                                <span class="badge badge-primary">ожидает</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Редактор документа страницы</div>
                <div class="card-body">
                    <div class="alert alert-secondary">
                        Этот редактор остается как служебный fallback для команды. Основной сценарий редактирования страницы теперь должен идти через visual workspace.
                    </div>
                    <?php if ($form_errors) { ?>
                        <div class="alert alert-danger">
                            <strong>Сохранение не выполнено.</strong>
                            <ul class="mb-0 pl-3 mt-2">
                                <?php foreach ($form_errors as $field => $error) { ?>
                                    <li><?php echo html($field . ': ' . $error); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>

                    <form action="<?php echo html($workspace_url . ($active_document_key ? '?document_key=' . urlencode($active_document_key) : '')); ?>" method="post">
                        <?php echo html_csrf_token(); ?>
                        <div class="form-row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label class="mb-1">Статус документа</label>
                                <select name="document_status" class="form-control">
                                    <?php $current_status = $active_document['status'] ?? 'draft'; ?>
                                    <?php foreach (['draft' => 'draft', 'prototype' => 'prototype', 'published' => 'published'] as $value => $label) { ?>
                                        <option value="<?php echo html($value); ?>" <?php echo $current_status === $value ? 'selected' : ''; ?>><?php echo html($document_status_labels[$label] ?? $label); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-9 mb-3 text-md-right">
                                <button type="submit" name="save_document" value="1" class="btn btn-primary">Сохранить документ страницы</button>
                            </div>
                        </div>

                        <label class="mb-1">JSON документа</label>
                        <textarea name="document_json" class="form-control" rows="28" spellcheck="false"><?php echo html($form_document_json); ?></textarea>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>