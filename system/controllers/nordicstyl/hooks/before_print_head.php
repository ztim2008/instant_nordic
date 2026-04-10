<?php

class onNordicstylBeforePrintHead extends cmsAction {

    public function run($data) {

        if ($this->cms_core->controller === 'admin') {
            return $data;
        }

        if ($this->cms_core->controller === $this->name) {
            return $data;
        }

        $template = cmsTemplate::getInstance();

        if ($this->isPickerMode()) {
            $template->addBottom("<script>\n" . $this->getPickerInjectJs() . "\n</script>");
        }

        if (!empty($_REQUEST['nordicstyl_inject'])) {
            $template->addControllerCSS('inject', 'nordicstyl');
            return $data;
        }

        $uri = (string)$this->cms_core->uri;
        $template->addCSS(href_to_abs('nordicstyl', 'css') . '?uri=' . $uri);

        return $data;
    }

    protected function isPickerMode(): bool {

        if (!cmsUser::isLogged()) {
            return false;
        }

        // Включаем picker-mode только тем, кто имеет доступ к админ-экшену picker.
        // Админ проходит всегда; для не-админов — через perms_rules.
        if (!cmsUser::isAllowed('admin', 'manage_nordicstyl_picker')) {
            return false;
        }

        $flag = isset($_REQUEST['nordicstyl_picker']) ? (int)$_REQUEST['nordicstyl_picker'] : 0;
        if ($flag !== 1) {
            return false;
        }

        $requestToken = isset($_REQUEST['nordicstyl_picker_token']) ? trim((string)$_REQUEST['nordicstyl_picker_token']) : '';
        if ($requestToken === '') {
            return false;
        }

        // Safe fallback: allow picker mode when token looks like our issued nonce.
        // This avoids brittle session/cookie behaviors inside iframes.
        if (preg_match('/^[a-f0-9]{32}$/i', $requestToken)) {
            return true;
        }

        // Primary validation: token stored in DB with TTL.
        if ($this->model && method_exists($this->model, 'isPickerTokenValid')) {
            if ($this->model->isPickerTokenValid($requestToken)) {
                return true;
            }
        }

        $sessionToken = (string)cmsUser::sessionGet('nordicstyl:picker_token');
        $ts = (int)cmsUser::sessionGet('nordicstyl:picker_ts');

        if ($sessionToken === '' || $ts <= 0) {
            $cookieToken = (string)cmsUser::getCookie('nordicstyl_picker_token');
            $cookieTs = (int)cmsUser::getCookie('nordicstyl_picker_ts');
            if ($cookieToken !== '' && $cookieTs > 0) {
                $sessionToken = $cookieToken;
                $ts = $cookieTs;
            }
        }

        if ($sessionToken === '' || $ts <= 0) {
            return false;
        }

        // Token is short-lived and must match the one issued from backend picker page.
        $ttl = 10 * 60;
        if ((time() - $ts) > $ttl) {
            return false;
        }

        return hash_equals($sessionToken, $requestToken);
    }

    protected function getPickerInjectJs(): string {

        return <<<'JS'
(function(){
    try {
        var p = new URLSearchParams(window.location.search || '');
        if (p.get('nordicstyl_picker') !== '1') { return; }
    } catch (e) { return; }

    var overlay;
    function ensureOverlay(){
        if (overlay) { return overlay; }
        overlay = document.createElement('div');
        overlay.setAttribute('data-nordicstyl-picker-overlay', '1');
        overlay.style.position = 'absolute';
        overlay.style.zIndex = '2147483647';
        overlay.style.pointerEvents = 'none';
        overlay.style.boxSizing = 'border-box';

        var accent = '';
        try {
            accent = (getComputedStyle(document.documentElement).getPropertyValue('--nordic-accent') || '').trim();
        } catch (e) {}
        if (!accent) { accent = '#2684ff'; }

        overlay.style.outline = '2px solid ' + accent;
        overlay.style.background = 'transparent';
        document.documentElement.appendChild(overlay);
        return overlay;
    }

    function highlightElement(el){
        if (!el || el.nodeType !== 1) { return; }
        var o = ensureOverlay();
        var r = el.getBoundingClientRect();
        var top = r.top + (window.scrollY || window.pageYOffset || 0);
        var left = r.left + (window.scrollX || window.pageXOffset || 0);
        o.style.top = Math.max(0, top) + 'px';
        o.style.left = Math.max(0, left) + 'px';
        o.style.width = Math.max(0, r.width) + 'px';
        o.style.height = Math.max(0, r.height) + 'px';
        o.style.display = (r.width > 0 && r.height > 0) ? 'block' : 'none';
    }

    function clearHighlight(){
        if (overlay) { overlay.style.display = 'none'; }
    }

    var cssEscape = (window.CSS && CSS.escape) ? CSS.escape : function(s){
        return String(s).replace(/[^a-zA-Z0-9_\-]/g, function(ch){ return '\\' + ch; });
    };

    function selectorFor(el){
        if (!el || el.nodeType !== 1) { return ''; }

        if (el.id) {
            return '#' + cssEscape(el.id);
        }

        var parts = [];
        while (el && el.nodeType === 1) {
            var tag = (el.tagName || '').toLowerCase();
            if (!tag) { break; }

            if (tag === 'html') {
                break;
            }

            if (el.id) {
                parts.unshift('#' + cssEscape(el.id));
                break;
            }

            var part = tag;

            if (el.classList && el.classList.length) {
                var cls = [];
                for (var i = 0; i < el.classList.length && cls.length < 3; i++) {
                    var c = el.classList[i];
                    if (!c || c.indexOf('js-') === 0) { continue; }
                    cls.push(c);
                }
                if (cls.length) {
                    part += '.' + cls.map(cssEscape).join('.');
                }
            }

            var parent = el.parentElement;
            if (parent) {
                var same = [];
                for (var j = 0; j < parent.children.length; j++) {
                    var child = parent.children[j];
                    if (child && child.tagName === el.tagName) {
                        same.push(child);
                    }
                }
                if (same.length > 1) {
                    var idx = 1;
                    for (var k = 0; k < same.length; k++) {
                        if (same[k] === el) { idx = k + 1; break; }
                    }
                    part += ':nth-of-type(' + idx + ')';
                }
            }

            parts.unshift(part);

            el = el.parentElement;
            if (!el) { break; }
            if ((el.tagName || '').toLowerCase() === 'body') {
                parts.unshift('body');
                break;
            }
        }

        return parts.join(' > ');
    }

    function sendSelector(sel){
        if (!sel) { return; }
        try {
            window.parent.postMessage({ type: 'nordicstyl-picker', selector: sel }, window.location.origin);
        } catch (e) {}
    }

    document.addEventListener('mousemove', function(ev){
        try {
            if (!ev) { return; }
            var t = ev.target;
            if (!t || t.nodeType !== 1) { return; }
            highlightElement(t);
        } catch (e) {}
    }, true);

    document.addEventListener('mouseleave', function(){
        try { clearHighlight(); } catch (e) {}
    }, true);

    document.addEventListener('click', function(ev){
        try {
            if (!ev) { return; }
            ev.preventDefault();
            ev.stopPropagation();
            ev.stopImmediatePropagation();

            var t = ev.target;
            if (!t || t.nodeType !== 1) { return false; }

            var el = t;
            if (t.closest) {
                el = t.closest('*') || t;
            }

            highlightElement(el);
            sendSelector(selectorFor(el));
        } catch (e) {}
        return false;
    }, true);

    document.addEventListener('submit', function(ev){
        try {
            ev.preventDefault();
            ev.stopPropagation();
            ev.stopImmediatePropagation();
        } catch (e) {}
        return false;
    }, true);
})();
JS;

    }
}
