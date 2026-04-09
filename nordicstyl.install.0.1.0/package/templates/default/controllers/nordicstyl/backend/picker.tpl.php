<?php
/** @var cmsTemplate $this */

$rules_url = $rules_url ?? '';
$iframe_url = $iframe_url ?? '';
$target_raw = $target_raw ?? '/';
$host_origin = $host_origin ?? '';
?>

<h1>NordicStyl — пикер селектора</h1>

<p class="hint">
    Открой страницу в iframe, кликни по элементу — селектор появится справа. Затем нажми «Открыть правила».
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
                <label>Выбранный селектор</label>
                <input id="nordicstyl-picker-selector" class="input" type="text" value="" readonly>
                <div class="hint">После клика по элементу в iframe тут появится селектор.</div>
            </div>
        </div>

        <div class="form-row">
            <a id="nordicstyl-picker-open-rules" class="button" href="<?php html($rules_url); ?>">Открыть правила</a>
            <button id="nordicstyl-picker-copy" class="button" type="button">Копировать</button>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Подсказка</label>
                <div class="hint">
                    В iframe можно кликать по любым элементам. На время пикера клики не выполняют переходы.
                </div>
            </div>
        </div>
    </div>

    <div style="flex: 1 1 auto; min-width: 360px;">
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
  const selectorInput = document.getElementById('nordicstyl-picker-selector');
  const openRules = document.getElementById('nordicstyl-picker-open-rules');
  const copyBtn = document.getElementById('nordicstyl-picker-copy');

  function setSelector(sel){
    if (!sel) return;
    selectorInput.value = sel;

    try {
      const url = new URL(openRules.getAttribute('href'), window.location.origin);
      url.searchParams.set('path', sel);
      if (!url.searchParams.get('title')) {
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

    setSelector(String(data.selector || ''));
  });

  copyBtn.addEventListener('click', function(){
    const val = selectorInput.value || '';
    if (!val) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(val).catch(function(){});
      return;
    }

    selectorInput.focus();
    selectorInput.select();
    try { document.execCommand('copy'); } catch (e) {}
  });
})();
</script>
