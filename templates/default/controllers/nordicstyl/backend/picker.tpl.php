<?php
/** @var cmsTemplate $this */

$rules_url = $rules_url ?? '';
$iframe_url = $iframe_url ?? '';
$target_raw = $target_raw ?? '/';
$host_origin = $host_origin ?? '';
$csrf_token = $csrf_token ?? '';

$map_q = $map_q ?? '';
$map_items = $map_items ?? [];
$map_stats = $map_stats ?? ['table_exists' => false, 'count' => 0, 'updated_at' => null, 'source_hash' => ''];
$map_source_file = $map_source_file ?? '';
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

        <div class="form-row">
          <div class="form-group" style="width: 100%;">
            <label>Карта селекторов (instyler.json)</label>

            <?php if (empty($map_stats['table_exists'])) { ?>
              <div class="hint">Таблица словаря селекторов ещё не создана. Установи миграцию <code>005_selector_map.sql</code> через инсталлер NordicStyl.</div>
            <?php } else { ?>
              <div class="hint">
                Записей: <b><?php echo (int)($map_stats['count'] ?? 0); ?></b>
                <?php if (!empty($map_source_file)) { ?> • источник: <code><?php html($map_source_file); ?></code><?php } ?>
              </div>

              <div style="margin: 8px 0; display:flex; gap:8px; align-items:center;">
                <form method="get" action="" style="display:flex; gap:8px; align-items:center; flex: 1 1 auto;">
                  <input class="input" type="text" name="q" value="<?php html($map_q); ?>" placeholder="Поиск: кнопка, header, .widget..." style="flex: 1 1 auto;">
                  <input type="hidden" name="target" value="<?php html($target_raw); ?>">
                  <button class="button" type="submit">Найти</button>
                </form>
                <form method="post" action="" style="margin:0;">
                  <input type="hidden" name="csrf_token" value="<?php html($csrf_token); ?>">
                  <input type="hidden" name="import_map" value="1">
                  <button class="button" type="submit">Импортировать</button>
                </form>
              </div>

              <select id="nordicstyl-picker-map" class="input" size="10" style="width:100%;">
                <?php if (!$map_items) { ?>
                  <option value="" disabled><?php echo ((int)($map_stats['count'] ?? 0) > 0) ? 'Ничего не найдено' : 'Словарь пуст — нажми «Импортировать»'; ?></option>
                <?php } else { foreach ($map_items as $it) {
                  $gp = trim((string)($it['group_path'] ?? ''));
                  $tt = trim((string)($it['title'] ?? ''));
                  $sel = (string)($it['selector'] ?? '');
                  $label = ($gp !== '' ? ($gp . ' → ') : '') . $tt;
                ?>
                  <option value="<?php html($sel, true); ?>" data-title="<?php html($tt, true); ?>"><?php html($label); ?></option>
                <?php } } ?>
              </select>
              <div class="hint">Выбери элемент — селектор подставится как будто ты кликнул по нему в iframe.</div>
            <?php } ?>
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
  const mapSelect = document.getElementById('nordicstyl-picker-map');

  function setSelector(sel, title){
    if (!sel) return;
    selectorInput.value = sel;

    try {
      const url = new URL(openRules.getAttribute('href'), window.location.origin);
      url.searchParams.set('path', sel);
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

    setSelector(String(data.selector || ''), 'picked');
  });

  if (mapSelect) {
    mapSelect.addEventListener('change', function(){
      const opt = mapSelect.options[mapSelect.selectedIndex];
      if (!opt) return;
      const sel = String(opt.value || '');
      const title = String(opt.getAttribute('data-title') || '');
      setSelector(sel, title || 'picked');
    });
  }

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
