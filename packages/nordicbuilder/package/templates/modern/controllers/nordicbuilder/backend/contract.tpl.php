<?php
$storage_status = $storage_stub['status'] ?? 'stub';
$storage_badge_class = 'badge-secondary';

if ($storage_status === 'planned') {
    $storage_badge_class = 'badge-warning';
} elseif ($storage_status === 'registry-stub') {
    $storage_badge_class = 'badge-info';
}
?>
<div class="content_list">
    <div class="mb-4">
        <a class="btn btn-light btn-sm mb-3" href="<?php echo html($back_url); ?>">Назад к реестру</a>
        <h1><?php echo html($page_title); ?></h1>
        <p class="text-muted mb-0"><?php echo html($page_note); ?></p>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">Metadata</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Key</dt>
                        <dd class="col-sm-8"><?php echo html($contract['key']); ?></dd>

                        <dt class="col-sm-4">Kind</dt>
                        <dd class="col-sm-8"><?php echo html($contract['kind'] ?: '—'); ?></dd>

                        <dt class="col-sm-4">Contract kind</dt>
                        <dd class="col-sm-8"><?php echo html($contract['contract_kind'] ?: '—'); ?></dd>

                        <dt class="col-sm-4">Bridge kind</dt>
                        <dd class="col-sm-8"><?php echo html($contract['bridge_kind'] ?: '—'); ?></dd>

                        <dt class="col-sm-4">Schema</dt>
                        <dd class="col-sm-8"><?php echo html($contract['schema_version'] ?: '—'); ?></dd>

                        <dt class="col-sm-4">Source</dt>
                        <dd class="col-sm-8"><?php echo html($contract['source_path']); ?></dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <?php if ($contract['is_valid']) { ?>
                                <span class="badge badge-success">valid</span>
                            <?php } else { ?>
                                <span class="badge badge-danger">invalid</span>
                            <?php } ?>
                        </dd>
                    </dl>

                    <?php if ($contract['description']) { ?>
                        <hr>
                        <div><?php echo html($contract['description']); ?></div>
                    <?php } ?>

                    <?php if ($contract['error']) { ?>
                        <div class="alert alert-danger mt-3 mb-0"><?php echo html($contract['error']); ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Storage Stub</span>
                    <span class="badge <?php echo html($storage_badge_class); ?>"><?php echo html($storage_status); ?></span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Target</dt>
                        <dd class="col-sm-7"><?php echo html($storage_stub['storage_target'] ?: '—'); ?></dd>

                        <dt class="col-sm-5">Driver</dt>
                        <dd class="col-sm-7"><?php echo html($storage_stub['driver']); ?></dd>

                        <dt class="col-sm-5">Repository</dt>
                        <dd class="col-sm-7"><?php echo html($storage_stub['repository']); ?></dd>

                        <dt class="col-sm-5">Bridge source</dt>
                        <dd class="col-sm-7"><?php echo html($storage_stub['bridge_source']); ?></dd>
                    </dl>

                    <hr>
                    <div class="small text-muted mb-2">Read model</div>
                    <div><?php echo html($storage_stub['read_model']); ?></div>

                    <div class="small text-muted mt-3 mb-2">Write model</div>
                    <div><?php echo html($storage_stub['write_model']); ?></div>

                    <?php if ($storage_stub['notes']) { ?>
                        <div class="small text-muted mt-3 mb-2">Notes</div>
                        <ul class="mb-0 pl-3">
                            <?php foreach ($storage_stub['notes'] as $note) { ?>
                                <li><?php echo html($note); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Required Fields</div>
                <div class="card-body">
                    <?php if ($contract['required_fields']) { ?>
                        <ul class="mb-0 pl-3">
                            <?php foreach ($contract['required_fields'] as $field) { ?>
                                <li><?php echo html($field); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <div class="text-muted">Required fields не описаны.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Contract Notes</div>
                <div class="card-body">
                    <?php if ($contract['notes']) { ?>
                        <ul class="mb-0 pl-3">
                            <?php foreach ($contract['notes'] as $note) { ?>
                                <li><?php echo html($note); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <div class="text-muted">Дополнительные notes не заданы.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Raw JSON</div>
        <div class="card-body p-0">
            <pre class="mb-0 p-3 bg-light" style="white-space: pre-wrap;"><?php echo html($raw_definition); ?></pre>
        </div>
    </div>
</div>