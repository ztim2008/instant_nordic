(function () {
  'use strict';

  var cfg = window.NORDICAI_INSPECTOR;
  if (!cfg || window.__nordicaiInspectorReady) return;
  window.__nordicaiInspectorReady = true;

  var state = {
    picking: false,
    selected: null,
    selector: '',
    lastPatch: null,
    hoverEl: null
  };

  var ignoreSel = '#nordicai-fab, #nordicai-fab *, #nordicai-panel, #nordicai-panel *, #scroll-top, .icms-cookiealert, .icms-cookiealert *';

  function $(sel, root) { return (root || document).querySelector(sel); }

  function post(url, data) {
    var body = new URLSearchParams();
    Object.keys(data || {}).forEach(function (k) {
      body.set(k, data[k] == null ? '' : String(data[k]));
    });
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: body.toString()
    }).then(function (r) {
      return r.text().then(function (text) {
        var data;
        try {
          data = text ? JSON.parse(text) : null;
        } catch (e) {
          throw new Error('Сервер вернул не JSON (HTTP ' + r.status + '). Обновите страницу и войдите как админ.');
        }
        if (!r.ok && (!data || data.ok === undefined)) {
          throw new Error('HTTP ' + r.status);
        }
        return data;
      });
    });
  }

  function cssEscapeIdent(value) {
    if (window.CSS && CSS.escape) return CSS.escape(value);
    return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
  }

  function buildSelector(el) {
    if (!el || el === document.body || el === document.documentElement) return 'body';

    if (el.id) return '#' + cssEscapeIdent(el.id);

    var nb = el.getAttribute('data-nb-entity') || el.getAttribute('data-nb-override-key') || el.getAttribute('data-nb-block');
    if (nb) {
      var attr = el.hasAttribute('data-nb-entity') ? 'data-nb-entity'
        : (el.hasAttribute('data-nb-override-key') ? 'data-nb-override-key' : 'data-nb-block');
      return '[' + attr + '="' + String(nb).replace(/"/g, '\\"') + '"]';
    }

    var parts = [];
    var node = el;
    var depth = 0;
    while (node && node.nodeType === 1 && node !== document.body && depth < 5) {
      var part = node.tagName.toLowerCase();
      if (node.classList && node.classList.length) {
        var cls = Array.prototype.slice.call(node.classList)
          .filter(function (c) { return c && c.indexOf('nordicai-') !== 0; })
          .slice(0, 2)
          .map(cssEscapeIdent);
        if (cls.length) part += '.' + cls.join('.');
      }
      var parent = node.parentElement;
      if (parent) {
        var same = Array.prototype.filter.call(parent.children, function (ch) {
          return ch.tagName === node.tagName;
        });
        if (same.length > 1) {
          part += ':nth-of-type(' + (Array.prototype.indexOf.call(same, node) + 1) + ')';
        }
      }
      parts.unshift(part);
      if (node.id || (node.classList && node.classList.contains('nb-users-card'))) break;
      node = parent;
      depth++;
    }
    return parts.join(' > ');
  }

  function contextFor(el) {
    if (!el) return '';
    var cs = window.getComputedStyle(el);
    return [
      'tag=' + el.tagName.toLowerCase(),
      'class=' + (el.className || ''),
      'padding=' + cs.padding,
      'background=' + cs.backgroundColor,
      'color=' + cs.color,
      'borderRadius=' + cs.borderRadius,
      'boxShadow=' + cs.boxShadow
    ].join('; ');
  }

  function isIgnored(el) {
    if (!el || !el.closest) return true;
    return !!el.closest(ignoreSel);
  }

  function clearHover() {
    if (state.hoverEl) {
      state.hoverEl.classList.remove('nordicai-hover');
      state.hoverEl = null;
    }
  }

  function setSelected(el) {
    if (state.selected) state.selected.classList.remove('nordicai-selected');
    state.selected = el || null;
    state.selector = el ? buildSelector(el) : '';
    if (el) el.classList.add('nordicai-selected');
    var selInput = $('#nordicai-selector');
    if (selInput) selInput.value = state.selector;
    setStatus(el ? (cfg.i18n.selected + ': ' + state.selector) : cfg.i18n.hint);
    openPanel(true);
  }

  function setStatus(text) {
    var el = $('#nordicai-status');
    if (el) el.textContent = text || '';
  }

  function ensurePreviewStyle() {
    var style = $('#nordicai-preview-style');
    if (!style) {
      style = document.createElement('style');
      style.id = 'nordicai-preview-style';
      document.head.appendChild(style);
    }
    return style;
  }

  function applyPreview(cssText) {
    ensurePreviewStyle().textContent = cssText || '';
  }

  function setPicking(on) {
    state.picking = !!on;
    document.documentElement.classList.toggle('nordicai-pick-mode', state.picking);
    var btn = $('#nordicai-pick-btn');
    if (btn) {
      btn.classList.toggle('is-active', state.picking);
      btn.textContent = state.picking ? cfg.i18n.stop : cfg.i18n.pick;
    }
    if (!state.picking) clearHover();
    setStatus(state.picking ? cfg.i18n.hint : '');
  }

  function openPanel(open) {
    var panel = $('#nordicai-panel');
    if (!panel) return;
    panel.classList.toggle('is-open', !!open);
  }

  function buildUi() {
    if ($('#nordicai-fab')) return;

    var fab = document.createElement('div');
    fab.id = 'nordicai-fab';
    fab.innerHTML = '<button type="button" class="nordicai-fab-main" id="nordicai-pick-btn">' + cfg.i18n.pick + '</button>';
    document.body.appendChild(fab);

    var panel = document.createElement('div');
    panel.id = 'nordicai-panel';
    panel.innerHTML =
      '<div class="nordicai-panel__head"><span>' + cfg.i18n.title + '</span><button type="button" class="nordicai-close" id="nordicai-close" aria-label="close">×</button></div>' +
      '<div class="nordicai-panel__body">' +
        '<label>Selector</label>' +
        '<input class="nordicai-selector" id="nordicai-selector" readonly>' +
        '<label style="margin-top:10px">' + cfg.i18n.prompt + '</label>' +
        '<textarea id="nordicai-prompt" placeholder="Сделай плотнее, accent темнее"></textarea>' +
        '<div class="nordicai-actions">' +
          '<button type="button" class="btn-gen" id="nordicai-gen">' + cfg.i18n.generate + '</button>' +
          '<button type="button" class="btn-apply" id="nordicai-apply">' + cfg.i18n.apply + '</button>' +
          '<button type="button" class="btn-save" id="nordicai-save">' + cfg.i18n.save + '</button>' +
          '<button type="button" class="btn-clear" id="nordicai-clear">' + cfg.i18n.clear + '</button>' +
        '</div>' +
        '<div class="nordicai-status" id="nordicai-status"></div>' +
        '<label style="margin-top:10px">Patch JSON</label>' +
        '<pre id="nordicai-json">{}</pre>' +
      '</div>';
    document.body.appendChild(panel);

    $('#nordicai-pick-btn').addEventListener('click', function () {
      setPicking(!state.picking);
      if (state.picking) openPanel(true);
    });
    $('#nordicai-close').addEventListener('click', function () {
      setPicking(false);
      openPanel(false);
    });

    document.addEventListener('mousemove', function (e) {
      if (!state.picking) return;
      var el = e.target;
      if (isIgnored(el)) { clearHover(); return; }
      if (state.hoverEl === el) return;
      clearHover();
      state.hoverEl = el;
      el.classList.add('nordicai-hover');
    }, true);

    document.addEventListener('click', function (e) {
      if (!state.picking) return;
      if (isIgnored(e.target)) return;
      e.preventDefault();
      e.stopPropagation();
      clearHover();
      setSelected(e.target.closest('*'));
      setPicking(false);
    }, true);

    $('#nordicai-gen').addEventListener('click', function () {
      if (!cfg.hasApiKey) {
        setStatus(cfg.i18n.noKey);
        return;
      }
      var prompt = ($('#nordicai-prompt').value || '').trim();
      if (!prompt) {
        setStatus('Введите промпт');
        return;
      }
      setStatus('DeepSeek...');
      post(cfg.generateUrl, {
        prompt: prompt,
        selector: ($('#nordicai-selector').value || state.selector || ''),
        context: contextFor(state.selected)
      }).then(function (data) {
        $('#nordicai-json').textContent = JSON.stringify(data, null, 2);
        if (!data || !data.ok) {
          setStatus('Ошибка: ' + ((data && data.error) || 'unknown'));
          return;
        }
        state.lastPatch = data.patch || null;
        if (data.css_text) applyPreview(data.css_text);
        setStatus('Patch готов. Можно Save на сайт.');
      }).catch(function (err) {
        setStatus(String(err));
      });
    });

    $('#nordicai-apply').addEventListener('click', function () {
      if (!state.lastPatch) {
        setStatus('Сначала Generate');
        return;
      }
      var css = '';
      if (state.lastPatch.tokens) {
        css += ':root{\n';
        Object.keys(state.lastPatch.tokens).forEach(function (k) {
          css += '  ' + k + ': ' + state.lastPatch.tokens[k] + ';\n';
        });
        css += '}\n\n';
      }
      css += (state.lastPatch.css || '');
      applyPreview(css);
      setStatus('Preview применён (пока локально)');
    });

    $('#nordicai-save').addEventListener('click', function () {
      if (!state.lastPatch) {
        setStatus('Сначала Generate');
        return;
      }
      var css = (state.lastPatch.css || '');
      setStatus('Сохраняю...');
      post(cfg.overlaySaveUrl, {
        css: css,
        tokens: JSON.stringify(state.lastPatch.tokens || {})
      }).then(function (data) {
        if (!data || !data.ok) {
          setStatus('Save error: ' + ((data && data.error) || 'unknown'));
          return;
        }
        applyPreview('');
        var pub = $('#nordicai-site-overlay');
        if (!pub) {
          pub = document.createElement('style');
          pub.id = 'nordicai-site-overlay';
          document.head.appendChild(pub);
        }
        pub.textContent = data.css || '';
        cfg.hasOverlay = true;
        setStatus('Сохранено на сайт');
      }).catch(function (err) {
        setStatus(String(err));
      });
    });

    $('#nordicai-clear').addEventListener('click', function () {
      post(cfg.overlayClearUrl, {}).then(function (data) {
        if (!data || !data.ok) {
          setStatus('Clear error');
          return;
        }
        applyPreview('');
        var pub = $('#nordicai-site-overlay');
        if (pub) pub.textContent = '';
        cfg.hasOverlay = false;
        setStatus('Overlay сброшен');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', buildUi);
  } else {
    buildUi();
  }
})();
