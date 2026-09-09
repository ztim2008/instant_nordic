<?php
/**
 * @var bool $has_api_key
 * @var string $generate_url
 * @var string $options_url
 * @var array $recent
 * @var array $known_tokens
 */
$this->setPageTitle(LANG_NORDICAI_AGENT_TITLE);
?>

<div class="card mb-3">
    <div class="card-body">
        <h1 class="h4 mb-2"><?php echo LANG_NORDICAI_AGENT_TITLE; ?></h1>
        <p class="text-muted mb-2"><?php echo LANG_NORDICAI_AGENT_HINT; ?></p>
        <a class="btn btn-sm btn-dark" href="<?php echo href_to_home(); ?>users" target="_blank" rel="noopener">
            Открыть живой сайт с инспектором
        </a>
        <div class="small text-muted mt-2">На сайте (под админом) справа внизу кнопка «Выбрать элемент» → клик по блоку → промпт → Save.</div>
    </div>
</div>

<?php if (!$has_api_key) { ?>
    <div class="alert alert-warning">
        <?php echo LANG_NORDICAI_AGENT_NO_KEY; ?>
        <a class="alert-link ml-2" href="<?php echo $options_url; ?>"><?php echo LANG_NORDICAI_AGENT_OPEN_OPTIONS; ?></a>
    </div>
<?php } ?>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="form-group">
                    <label for="nordicai-prompt"><?php echo LANG_NORDICAI_AGENT_PROMPT; ?></label>
                    <textarea id="nordicai-prompt" class="form-control" rows="5" placeholder="Сделай карточки пользователей плотнее, accent чуть темнее"></textarea>
                </div>
                <div class="form-group">
                    <label for="nordicai-selector"><?php echo LANG_NORDICAI_AGENT_SELECTOR; ?></label>
                    <input id="nordicai-selector" type="text" class="form-control" placeholder=".nb-users-card">
                </div>
                <div class="form-group">
                    <label for="nordicai-context"><?php echo LANG_NORDICAI_AGENT_CONTEXT; ?></label>
                    <textarea id="nordicai-context" class="form-control" rows="4" placeholder="Классы/текущие стили элемента"></textarea>
                </div>
                <button type="button" id="nordicai-generate" class="btn btn-primary" <?php if (!$has_api_key) { ?>disabled<?php } ?>>
                    <?php echo LANG_NORDICAI_AGENT_GENERATE; ?>
                </button>
                <small class="d-block text-muted mt-2">
                    Tokens: <?php echo htmlspecialchars(implode(', ', $known_tokens), ENT_QUOTES, 'UTF-8'); ?>
                </small>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><?php echo LANG_NORDICAI_AGENT_RESULT; ?></div>
            <div class="card-body">
                <pre id="nordicai-result" class="mb-0 small" style="white-space:pre-wrap;min-height:220px;">{}</pre>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recent)) { ?>
<div class="card">
    <div class="card-header"><?php echo LANG_NORDICAI_AGENT_RECENT; ?></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo LANG_NORDICAI_AGENT_PROMPT; ?></th>
                    <th>OK</th>
                    <th><?php echo LANG_DATE; ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $row) { ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><?php html(mb_substr((string) $row['prompt'], 0, 120)); ?></td>
                    <td><?php echo !empty($row['is_ok']) ? '✓' : '✗'; ?></td>
                    <td><?php echo html_date_time($row['date_pub']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<script>
(function () {
    var btn = document.getElementById('nordicai-generate');
    var out = document.getElementById('nordicai-result');
    if (!btn || !out) return;

    btn.addEventListener('click', function () {
        btn.disabled = true;
        out.textContent = 'Loading...';

        var body = new URLSearchParams();
        body.set('prompt', document.getElementById('nordicai-prompt').value || '');
        body.set('selector', document.getElementById('nordicai-selector').value || '');
        body.set('context', document.getElementById('nordicai-context').value || '');

        fetch(<?php echo json_encode($generate_url); ?>, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: body.toString()
        }).then(function (r) { return r.json(); }).then(function (data) {
            out.textContent = JSON.stringify(data, null, 2);
        }).catch(function (e) {
            out.textContent = JSON.stringify({ ok: false, error: String(e) }, null, 2);
        }).finally(function () {
            btn.disabled = <?php echo $has_api_key ? 'false' : 'true'; ?>;
        });
    });
})();
</script>
