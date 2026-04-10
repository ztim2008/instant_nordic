<?php

class actionNordicstylPicker extends cmsAction {

    public function run() {

        $siteTemplate = trim((string)cmsConfig::get('template'));
        if ($siteTemplate === '') {
            $siteTemplate = 'modern';
        }

        $preferredSource = 'tpl_' . $siteTemplate . '_selector_map';
        $availableSources = $this->model->getAvailableSelectorMapSourcesForTemplate($siteTemplate);
        $mapSource = in_array($preferredSource, $availableSources, true)
            ? $preferredSource
            : (!empty($availableSources) ? (string)$availableSources[0] : $preferredSource);

        $mapFile = $this->getSelectorMapJsonFile();
        $mapQ = trim((string)$this->request->get('q', ''));
        $mapStats = $this->model->getSelectorMapStats($mapSource);

        if ($this->request->has('import_map')) {
            $csrf = (string)$this->request->get('csrf_token', '');
            if (!cmsForm::validateCSRFToken($csrf)) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
                return $this->redirectBack();
            }

            if (!$mapFile) {
                cmsUser::addSessionMessage('Не найден источник карты селекторов (selector-map.json).', 'error');
                return $this->redirectBack();
            }

            $result = $this->model->importSelectorMapFromJsonFile($mapFile, $mapSource);
            if (!empty($result['ok'])) {
                cmsUser::addSessionMessage(
                    'Карта селекторов импортирована: ' . (int)$result['total'] . ' (+' . (int)$result['inserted'] . ', ~' . (int)$result['updated'] . ').',
                    'success'
                );
            } else {
                cmsUser::addSessionMessage('Ошибка импорта карты: ' . (string)($result['error'] ?? 'unknown'), 'error');
            }

            return $this->redirectBack();
        }

        $defaultPath = '/';
        $rawTarget = trim((string)$this->request->get('target', $defaultPath));

        $targetUrl = $this->sanitizeTarget($rawTarget);
        if ($targetUrl === null) {
            cmsUser::addSessionMessage('Неверный URL/путь для предпросмотра.', 'error');
            $targetUrl = $this->sanitizeTarget($defaultPath);
        }

        $token = bin2hex(random_bytes(16));
        cmsUser::sessionSet('nordicstyl:picker_token', $token);
        cmsUser::sessionSet('nordicstyl:picker_ts', time());

        // Cookie fallback for iframe/frontend requests where PHP session might not be available.
        // Stored as HttpOnly, path '/', short-lived.
        cmsUser::setCookie('nordicstyl_picker_token', $token, 10 * 60, '/');
        cmsUser::setCookie('nordicstyl_picker_ts', (string)time(), 10 * 60, '/');

        $this->model->savePickerToken($token, 10 * 60);

        $iframeUrl = $this->appendPickerParams($targetUrl, [
            'nordicstyl_picker' => 1,
            'nordicstyl_picker_token' => $token
        ]);

        $rulesUrl = href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'rules']);

        $mapItems = $this->model->searchSelectorMap($mapQ, 200, $mapSource);

        return $this->cms_template->render('backend/picker', [
            'menu' => $this->controller->getBackendMenu(),
            'rules_url' => $rulesUrl,
            'iframe_url' => $iframeUrl,
            'target_raw' => $rawTarget,
            'csrf_token' => cmsForm::getCSRFToken(),
            'host_origin' => $this->getHostOrigin(),

            'map_q' => $mapQ,
            'map_items' => $mapItems,
            'map_stats' => $mapStats,
            'map_source_file' => $mapFile ? str_replace((string)cmsConfig::get('root_path'), '', $mapFile) : ''
        ]);
    }

    protected function getSelectorMapJsonFile(): ?string {

        $root = (string)cmsConfig::get('root_path');
        $siteTemplate = trim((string)cmsConfig::get('template'));
        $candidates = [
            $siteTemplate !== '' ? ($root . 'templates/' . $siteTemplate . '/selector-map.json') : null,
            $root . 'templates/nordics/selector-map.json',
            $root . 'templates/modern/selector-map.json'
        ];

        foreach ($candidates as $file) {
            if (!$file) {
                continue;
            }
            if (is_file($file) && is_readable($file)) {
                return $file;
            }
        }

        return null;
    }

    protected function sanitizeTarget(string $raw): ?string {

        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Allow relative path like /, /news, /board/...
        if ($raw[0] === '/') {
            return rtrim($this->getHostOrigin(), '/') . $raw;
        }

        // Allow absolute URL but only same host
        $parsed = parse_url($raw);
        if (!$parsed || empty($parsed['host'])) {
            return null;
        }

        $hostOrigin = $this->getHostOrigin();
        $hostParsed = parse_url($hostOrigin);
        if (!$hostParsed || empty($hostParsed['host'])) {
            return null;
        }

        $scheme = strtolower((string)($parsed['scheme'] ?? ''));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $host = strtolower((string)$parsed['host']);
        $allowedHost = strtolower((string)$hostParsed['host']);

        if ($host !== $allowedHost) {
            return null;
        }

        return $raw;
    }

    protected function appendPickerParams(string $url, array $params): string {

        $parts = parse_url($url);
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        foreach ($params as $k => $v) {
            $query[$k] = $v;
        }

        $newQuery = http_build_query($query);

        $base = $url;
        $hashPos = strpos($base, '#');
        $hash = '';
        if ($hashPos !== false) {
            $hash = substr($base, $hashPos);
            $base = substr($base, 0, $hashPos);
        }

        $base = preg_replace('/\?.*/', '', $base);

        return $base . '?' . $newQuery . $hash;
    }

    protected function getHostOrigin(): string {

        $host = rtrim((string)cmsConfig::get('host'), '/');
        if ($host === '') {
            $host = rtrim((string)cmsConfig::get('root'), '/');
        }

        $parsed = $host ? parse_url($host) : false;
        if ($parsed && !empty($parsed['scheme']) && !empty($parsed['host'])) {
            return rtrim($host, '/');
        }

        $httpHost = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '';
        $scheme = $this->request ? $this->request->getScheme() : 'http';
        if ($httpHost !== '') {
            return $scheme . '://' . $httpHost;
        }

        return rtrim((string)$host, '/');
    }
}
