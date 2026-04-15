<?php
/** @var cmsTemplate $this */

$rules_url = $rules_url ?? '';
$iframe_url = $iframe_url ?? '';
$target_raw = $target_raw ?? '/';
$host_origin = $host_origin ?? '';
$csrf_token = $csrf_token ?? '';
?>

<h1>NordicStyl — live-редактор стиля</h1>

<p class="hint">
  Открой страницу в preview-окне, кликни по нужному элементу и меняй его стиль прямо там, поверх сайта. Справа остается только вспомогательная панель с текущим node target и переходом в rules.
</p>

<div style="display:flex; gap: 16px; align-items: flex-start;">

    <div style="flex: 0 0 340px;">
        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Страница для предпросмотра</label>
                <form method="get" action="">
                    <input class="input" type="text" name="target" value="<?php html($target_raw); ?>" placeholder="/ или https://<?php html(parse_url($host_origin, PHP_URL_HOST) ?: ''); ?>/">
                    <div class="hint">Можно указать путь (например <code>/news</code>) или абсолютный URL того же домена.</div>
                    <div style="margin-top: 8px;">
                        <button class="button" type="submit">Открыть</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Текущий target</label>
            <input id="nordicstyl-picker-target" class="input" type="text" value="" readonly>
            <div class="hint">После клика по элементу здесь появится node target вида node:shell-header. В preview уже откроется live-панель поверх страницы.</div>
            </div>
        </div>

        <div class="form-row">
              <a id="nordicstyl-picker-open-rules" class="button" href="<?php html($rules_url); ?>">Открыть CSS rules</a>
            <button id="nordicstyl-picker-copy" class="button" type="button">Копировать</button>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Как это работает</label>
                <div class="hint">
                  В preview можно кликать по любым элементам. Переходы и submit временно блокируются, чтобы страница работала как live-редактор, а не уводила вас по сайту.
                </div>
            </div>
        </div>
    </div>

    <div style="flex: 1 1 auto; min-width: 360px;">
        <div class="hint" style="margin-bottom:8px;">Внутри окна уже работает floating live-редактор поверх самой страницы.</div>
        <iframe
            id="nordicstyl-picker-iframe"
            src="<?php html($iframe_url); ?>"
            style="width: 100%; height: 70vh;"
            referrerpolicy="no-referrer"
        ></iframe>
    </div>

</div>

<script>
(function(){
  const allowedOrigin = <?php echo json_encode((string)$host_origin); ?>;
  const targetInput = document.getElementById('nordicstyl-picker-target');
  const openRules = document.getElementById('nordicstyl-picker-open-rules');
  const copyBtn = document.getElementById('nordicstyl-picker-copy');

  function setTarget(path, title){
    if (!path) return;
    targetInput.value = path;

    try {
      const url = new URL(openRules.getAttribute('href'), window.location.origin);
      url.searchParams.set('path', path);
      const t = String(title || '').trim();
      if (t) {
        url.searchParams.set('title', t);
      } else if (!url.searchParams.get('title')) {
        url.searchParams.set('title', 'picked');
      }
      openRules.setAttribute('href', url.toString());
    } catch (e) {}
  }

  window.addEventListener('message', function(ev){
    if (allowedOrigin && ev.origin !== allowedOrigin) {
      return;
    }

    const data = ev.data || {};
    if (!data || data.type !== 'nordicstyl-picker') {
      return;
    }

    setTarget(String(data.storage_path || '').trim(), String(data.title || '').trim() || 'picked');
  });

  copyBtn.addEventListener('click', function(){
    const val = targetInput.value || '';
    if (!val) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(val).catch(function(){});
      return;
    }

    targetInput.focus();
    targetInput.select();
    try { document.execCommand('copy'); } catch (e) {}
  });
})();
</script>
