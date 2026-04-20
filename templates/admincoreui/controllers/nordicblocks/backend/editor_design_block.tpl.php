<?php
$this->setPageTitle('NordicBlocks - Design Block');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Design Block');
$this->addMenuItems('admin_toolbar', $menu);
?>

<style>
.nbd-shell{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(360px,.95fr);gap:1.25rem;margin-top:1.5rem}
.nbd-card{background:#fff;border:1px solid #dbe4ef;border-radius:24px;box-shadow:0 10px 28px rgba(15,23,42,.06);overflow:hidden}
.nbd-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%)}
.nbd-topbar__meta{display:flex;flex-direction:column;gap:.25rem}
.nbd-topbar__title{font-size:1.05rem;font-weight:700;color:#0f172a}
.nbd-topbar__hint{font-size:.85rem;color:#475569}
.nbd-actions{display:flex;gap:.75rem;flex-wrap:wrap}
.nbd-button{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;padding:.72rem 1rem;border-radius:999px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-weight:600;text-decoration:none;cursor:pointer}
.nbd-button--primary{background:#0f172a;border-color:#0f172a;color:#fff}
.nbd-pane{padding:1rem 1.25rem}
.nbd-preview{padding:0;background:#f8fafc}
.nbd-preview iframe{display:block;width:100%;min-height:860px;border:0;background:#e2e8f0}
.nbd-field{display:flex;flex-direction:column;gap:.45rem;margin-bottom:1rem}
.nbd-label{font-size:.82rem;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.04em}
.nbd-input,.nbd-textarea{width:100%;border:1px solid #cbd5e1;border-radius:16px;padding:.8rem .95rem;font:inherit;color:#0f172a;background:#fff}
.nbd-textarea{min-height:620px;resize:vertical;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,monospace;font-size:.88rem;line-height:1.55}
.nbd-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem;margin-bottom:1rem}
.nbd-summary__item{padding:.85rem 1rem;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0}
.nbd-summary__label{font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b}
.nbd-summary__value{margin-top:.25rem;font-size:1rem;font-weight:700;color:#0f172a}
.nbd-status{margin-top:.9rem;padding:.85rem 1rem;border-radius:18px;background:#eff6ff;color:#1d4ed8;display:none}
.nbd-status.is-error{background:#fef2f2;color:#b91c1c}
.nbd-palette{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem}
.nbd-chip{padding:.4rem .65rem;border-radius:999px;background:#f1f5f9;border:1px solid #dbe4ef;color:#334155;font-size:.82rem}
@media (max-width: 1180px){.nbd-shell{grid-template-columns:1fr}.nbd-preview iframe{min-height:640px}.nbd-summary{grid-template-columns:1fr}}
</style>

<div class="nbd-shell">
    <section class="nbd-card nbd-preview">
        <div class="nbd-topbar">
            <div class="nbd-topbar__meta">
                <div class="nbd-topbar__title">Preview</div>
                <div class="nbd-topbar__hint">SSR canvas уже работает через отдельный design_block renderer.</div>
            </div>
            <div class="nbd-actions">
                <button class="nbd-button" type="button" id="nbd-reload-preview">Reload preview</button>
                <a class="nbd-button" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>">Place in widgets</a>
            </div>
        </div>
        <iframe id="nbd-preview-frame" src="<?= htmlspecialchars($canvas_url, ENT_QUOTES, 'UTF-8') ?>"></iframe>
    </section>

    <section class="nbd-card">
        <div class="nbd-topbar">
            <div class="nbd-topbar__meta">
                <div class="nbd-topbar__title">JSON Shell</div>
                <div class="nbd-topbar__hint">Переходный backend editor для Stage 1-3: contract edit, save и preview.</div>
            </div>
            <div class="nbd-actions">
                <a class="nbd-button" href="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') ?>">Back to blocks</a>
                <button class="nbd-button nbd-button--primary" type="button" id="nbd-save">Save</button>
            </div>
        </div>
        <div class="nbd-pane">
            <div class="nbd-summary">
                <div class="nbd-summary__item">
                    <div class="nbd-summary__label">Block ID</div>
                    <div class="nbd-summary__value"><?= (int) ($block['id'] ?? 0) ?></div>
                </div>
                <div class="nbd-summary__item">
                    <div class="nbd-summary__label">Elements</div>
                    <div class="nbd-summary__value" id="nbd-element-count">-</div>
                </div>
                <div class="nbd-summary__item">
                    <div class="nbd-summary__label">Desktop Stage</div>
                    <div class="nbd-summary__value" id="nbd-stage-desktop">-</div>
                </div>
            </div>

            <div class="nbd-field">
                <label class="nbd-label" for="nbd-title">Title</label>
                <input class="nbd-input" id="nbd-title" type="text" value="<?= htmlspecialchars((string) ($block['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="nbd-field">
                <div class="nbd-label">Palette v1</div>
                <div class="nbd-palette" id="nbd-palette"></div>
            </div>

            <div class="nbd-field">
                <label class="nbd-label" for="nbd-contract">Contract</label>
                <textarea class="nbd-textarea" id="nbd-contract" spellcheck="false"></textarea>
            </div>

            <div class="nbd-actions">
                <button class="nbd-button" type="button" id="nbd-reload-state">Reload JSON</button>
                <button class="nbd-button" type="button" id="nbd-format-json">Format JSON</button>
            </div>

            <div class="nbd-status" id="nbd-status"></div>
        </div>
    </section>
</div>

<script>
(function () {
    var stateUrl = <?= json_encode($state_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var saveUrl = <?= json_encode($save_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var canvasUrl = <?= json_encode($canvas_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var csrfToken = <?= json_encode(cmsForm::getCSRFToken(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var titleInput = document.getElementById('nbd-title');
    var contractArea = document.getElementById('nbd-contract');
    var statusNode = document.getElementById('nbd-status');
    var previewFrame = document.getElementById('nbd-preview-frame');
    var elementCountNode = document.getElementById('nbd-element-count');
    var stageDesktopNode = document.getElementById('nbd-stage-desktop');
    var paletteNode = document.getElementById('nbd-palette');

    function setStatus(message, isError) {
        statusNode.textContent = message || '';
        statusNode.style.display = message ? 'block' : 'none';
        statusNode.classList.toggle('is-error', !!isError);
    }

    function refreshPreview() {
        previewFrame.src = canvasUrl + (canvasUrl.indexOf('?') >= 0 ? '&' : '?') + '_ts=' + Date.now();
    }

    function renderSummary(state) {
        var summary = state && state.summary ? state.summary : {};
        var desktop = summary.stage && summary.stage.desktop ? summary.stage.desktop : {};
        elementCountNode.textContent = String(summary.elementCount || 0);
        stageDesktopNode.textContent = String(desktop.width || '-') + ' x ' + String(desktop.minHeight || '-');

        paletteNode.innerHTML = '';
        (state.palette && state.palette.items ? state.palette.items : []).forEach(function (item) {
            var chip = document.createElement('span');
            chip.className = 'nbd-chip';
            chip.textContent = item.label || item.type || 'Item';
            paletteNode.appendChild(chip);
        });
    }

    async function loadState() {
        setStatus('Loading state...', false);

        var response = await fetch(stateUrl, { credentials: 'same-origin' });
        var state = await response.json();
        if (!state || !state.ok) {
            throw new Error(state && state.error ? state.error : 'state_load_failed');
        }

        titleInput.value = state.block && state.block.title ? state.block.title : titleInput.value;
        contractArea.value = JSON.stringify(state.contract || {}, null, 2);
        renderSummary(state);
        setStatus('State loaded.', false);
    }

    async function saveState() {
        var contract;

        try {
            contract = JSON.parse(contractArea.value || '{}');
        } catch (error) {
            setStatus('JSON parse error: ' + error.message, true);
            return;
        }

        setStatus('Saving...', false);

        var response = await fetch(saveUrl + '?csrf_token=' + encodeURIComponent(csrfToken), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: titleInput.value || '',
                contract: contract,
                csrf_token: csrfToken
            })
        });

        var result = await response.json();
        if (!result || !result.ok) {
            throw new Error(result && result.error ? result.error : 'save_failed');
        }

        contractArea.value = JSON.stringify(result.contract || contract, null, 2);
        setStatus('Saved. Preview reloaded.', false);
        refreshPreview();
        await loadState();
    }

    document.getElementById('nbd-reload-preview').addEventListener('click', refreshPreview);
    document.getElementById('nbd-reload-state').addEventListener('click', function () {
        loadState().catch(function (error) {
            setStatus(error.message || 'state_reload_failed', true);
        });
    });
    document.getElementById('nbd-format-json').addEventListener('click', function () {
        try {
            contractArea.value = JSON.stringify(JSON.parse(contractArea.value || '{}'), null, 2);
            setStatus('JSON formatted.', false);
        } catch (error) {
            setStatus('JSON parse error: ' + error.message, true);
        }
    });
    document.getElementById('nbd-save').addEventListener('click', function () {
        saveState().catch(function (error) {
            setStatus(error.message || 'save_failed', true);
        });
    });

    loadState().catch(function (error) {
        setStatus(error.message || 'state_load_failed', true);
    });
})();
</script>