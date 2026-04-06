<?php /** @var array $contracts */ ?>
<div class="content_list">
    <div class="mb-4">
        <h1><?php echo html($page_title); ?></h1>
        <p class="text-muted mb-1"><?php echo html($page_note); ?></p>
        <p class="text-muted mb-0">Всего контрактов: <?php echo (int) $contracts_count; ?></p>
    </div>

    <?php if (!$contracts) { ?>
        <div class="alert alert-warning mb-0">Реестр пуст. Контракты еще не добавлены.</div>
    <?php return; } ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead>
                <tr>
                    <th>Контракт</th>
                            <th>Тип</th>
                            <th>Источник bridge</th>
                    <th>Хранилище</th>
                            <th>Обязательные поля</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $contract) { ?>
                    <tr>
                        <td>
                            <a href="<?php echo html($contract['detail_url']); ?>"><strong><?php echo html($contract['title']); ?></strong></a><br>
                            <span class="text-muted"><?php echo html($contract['key']); ?></span>
                            <?php if ($contract['description']) { ?>
                                <div class="text-muted mt-2"><?php echo html($contract['description']); ?></div>
                            <?php } ?>
                            <div class="mt-2">
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo html($contract['detail_url']); ?>">Открыть детали</a>
                            </div>
                            <?php if ($contract['notes']) { ?>
                                <div class="small text-muted mt-2">
                                    <?php echo html(implode(' | ', $contract['notes'])); ?>
                                </div>
                            <?php } ?>
                            <?php if ($contract['error']) { ?>
                                <div class="small text-danger mt-2"><?php echo html($contract['error']); ?></div>
                            <?php } ?>
                        </td>
                        <td>
                            <div><?php echo html($contract['contract_kind']); ?></div>
                            <div class="small text-muted">схема <?php echo html($contract['schema_version']); ?></div>
                        </td>
                        <td><?php echo html($contract['bridge_kind'] ?: '—'); ?></td>
                        <td><?php echo html($contract['storage_target'] ?: '—'); ?></td>
                        <td>
                            <?php if ($contract['required_fields']) { ?>
                                <?php echo html(implode(', ', $contract['required_fields'])); ?>
                            <?php } else { ?>
                                —
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($contract['is_valid']) { ?>
                                <span class="badge badge-success">корректен</span>
                            <?php } else { ?>
                                <span class="badge badge-danger">ошибка</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>