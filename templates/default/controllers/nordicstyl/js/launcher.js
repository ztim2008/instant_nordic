(function () {
    var launcher;
    var bootAttempts = 0;
    var maxBootAttempts = 20;

    function getConfig() {
        return window.NORDICSTYL_LAUNCHER_CONFIG || {};
    }

    function boot() {
        var body = document.body;
        var config = getConfig();

        if (document.querySelector('.ns-live-launcher')) {
            return;
        }

        if (!body || !config.enabled || !config.builder_url) {
            bootAttempts += 1;

            if (bootAttempts < maxBootAttempts) {
                window.setTimeout(boot, 250);
            }

            return;
        }

        launcher = document.createElement('a');
        launcher.className = 'ns-live-launcher';
        launcher.href = String(config.builder_url || '#');
        launcher.innerHTML = [
            '<span class="ns-live-launcher__badge">live</span>',
            '<span class="ns-live-launcher__content">',
            '<span class="ns-live-launcher__title">' + escapeHtml(String(config.label || 'Открыть live-редактор')) + '</span>',
            '<span class="ns-live-launcher__subtitle">' + escapeHtml(String(config.subtitle || 'Редактировать эту страницу')) + '</span>',
            '</span>'
        ].join('');

        body.appendChild(launcher);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
        return;
    }

    boot();
})();