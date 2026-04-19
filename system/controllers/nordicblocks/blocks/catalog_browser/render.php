<?php

require_once dirname(__DIR__) . '/render_helpers.php';

if (!function_exists('nb_catalog_browser_visible')) {
    function nb_catalog_browser_visible($value, $default = true) {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_catalog_browser_normalize_price')) {
    function nb_catalog_browser_normalize_price($price, $currency) {
        $value = trim((string) $price);
        if ($value === '' || $value === '0') {
            return '';
        }

        $suffix = trim((string) $currency);
        return trim($value . ($suffix !== '' ? ' ' . $suffix : ''));
    }
}

if (!function_exists('nb_catalog_browser_price_value')) {
    function nb_catalog_browser_price_value($price) {
        $value = preg_replace('/[^0-9,\.]/', '', (string) $price);
        if ($value === null || $value === '') {
            return 0;
        }

        $value = str_replace(',', '.', $value);
        return (float) $value;
    }
}

if (!function_exists('nb_catalog_browser_normalize_gallery')) {
    function nb_catalog_browser_normalize_gallery($gallery, $fallback_image, $fallback_alt) {
        if (is_string($gallery) && $gallery !== '') {
            $decoded = json_decode($gallery, true);
            if (is_array($decoded)) {
                $gallery = $decoded;
            }
        }

        if (!is_array($gallery)) {
            $gallery = [];
        }

        $slides = [];
        foreach ($gallery as $entry) {
            if (is_string($entry)) {
                $media = nb_block_extract_media($entry, $fallback_alt);
                $src = (string) ($media['display'] ?: $media['original']);
                if ($src === '') {
                    continue;
                }

                $slides[] = [
                    'src' => $src,
                    'alt' => (string) ($media['alt'] ?: $fallback_alt),
                    'caption' => '',
                ];
                continue;
            }

            if (!is_array($entry)) {
                continue;
            }

            $media = nb_block_extract_media($entry['src'] ?? ($entry['image'] ?? ''), $entry['alt'] ?? $fallback_alt);
            $src = (string) ($media['display'] ?: $media['original']);
            if ($src === '') {
                continue;
            }

            $slides[] = [
                'src' => $src,
                'alt' => (string) ($media['alt'] ?: $fallback_alt),
                'caption' => trim((string) ($entry['caption'] ?? '')),
            ];
        }

        if (!$slides && $fallback_image !== '') {
            $slides[] = [
                'src' => $fallback_image,
                'alt' => $fallback_alt,
                'caption' => '',
            ];
        }

        return $slides;
    }
}

if (!function_exists('nb_catalog_browser_build_search_text')) {
    function nb_catalog_browser_build_search_text(array $item, array $search_fields) {
        $index = (isset($item['searchIndex']) && is_array($item['searchIndex'])) ? $item['searchIndex'] : [];
        $parts = [];

        foreach ($search_fields as $field => $enabled) {
            if (!$enabled) {
                continue;
            }

            $value = trim((string) ($index[$field] ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return trim(implode(' ', $parts));
    }
}

if (!function_exists('nb_catalog_browser_normalize_item')) {
    function nb_catalog_browser_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ''));
        $category = trim((string) ($item['category'] ?? ''));
        $category_url = trim((string) ($item['categoryUrl'] ?? ($item['category_url'] ?? '')));
        $url = trim((string) ($item['url'] ?? ''));
        $cta_label = trim((string) ($item['ctaLabel'] ?? ($item['cta_label'] ?? '')));
        $cta_kind = trim((string) ($item['ctaKind'] ?? ($item['cta_kind'] ?? 'url')));
        $cta_url = trim((string) ($item['ctaUrl'] ?? ($item['cta_url'] ?? '')));
        $price = trim((string) ($item['price'] ?? ''));
        $price_old = trim((string) ($item['priceOld'] ?? ($item['price_old'] ?? '')));
        $currency = trim((string) ($item['currency'] ?? ''));
        $badge = trim((string) ($item['badge'] ?? ''));
        $availability = trim((string) ($item['availability'] ?? ''));
        $tags_value = $item['tags'] ?? '';
        if (is_array($tags_value)) {
            $tags_value = implode(', ', array_filter(array_map('trim', $tags_value)));
        }
        $tags = trim((string) $tags_value);
        $media = nb_block_extract_media($item['image'] ?? '', $item['imageAlt'] ?? ($item['alt'] ?? $title));
        $image_src = (string) ($media['display'] ?: $media['original']);
        $image_alt = (string) ($media['alt'] ?: $title);
        $gallery = nb_catalog_browser_normalize_gallery($item['gallery'] ?? [], $image_src, $image_alt);

        if ($title === '' && $excerpt === '' && $category === '' && $badge === '' && $price === '' && $image_src === '') {
            return null;
        }

        return [
            'category' => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
            'categoryUrl' => htmlspecialchars($category_url, ENT_QUOTES, 'UTF-8'),
            'categoryValue' => $category,
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'excerpt' => nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'ctaLabel' => htmlspecialchars($cta_label, ENT_QUOTES, 'UTF-8'),
            'ctaKind' => htmlspecialchars($cta_kind, ENT_QUOTES, 'UTF-8'),
            'ctaUrl' => htmlspecialchars($cta_url, ENT_QUOTES, 'UTF-8'),
            'price' => htmlspecialchars(nb_catalog_browser_normalize_price($price, $currency), ENT_QUOTES, 'UTF-8'),
            'priceOld' => htmlspecialchars(nb_catalog_browser_normalize_price($price_old, $currency), ENT_QUOTES, 'UTF-8'),
            'priceValue' => nb_catalog_browser_price_value($price),
            'badge' => htmlspecialchars($badge, ENT_QUOTES, 'UTF-8'),
            'availability' => htmlspecialchars($availability, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars($image_src, ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars($image_alt, ENT_QUOTES, 'UTF-8'),
            'gallery' => $gallery,
            'searchIndex' => [
                'title' => mb_strtolower($title, 'UTF-8'),
                'excerpt' => mb_strtolower($excerpt, 'UTF-8'),
                'category' => mb_strtolower($category, 'UTF-8'),
                'badge' => mb_strtolower($badge, 'UTF-8'),
                'tags' => mb_strtolower($tags, 'UTF-8'),
                'price' => mb_strtolower(nb_catalog_browser_normalize_price($price, $currency), 'UTF-8'),
                'availability' => mb_strtolower($availability, 'UTF-8'),
            ],
        ];
    }
}

$props = (array) ($props ?? []);
$heading = trim((string) ($props['heading'] ?? 'Каталог'));
$intro = trim((string) ($props['intro'] ?? ''));
$theme = trim((string) ($props['theme'] ?? 'light')) ?: 'light';
$align = trim((string) ($props['align'] ?? 'left')) ?: 'left';
$section_link_label = trim((string) ($props['section_link_label'] ?? ''));
$section_link_url = trim((string) ($props['section_link_url'] ?? ''));
$show_search = nb_catalog_browser_visible($props['show_search'] ?? '1', true);
$show_category_filter = nb_catalog_browser_visible($props['show_category_filter'] ?? '1', true);
$show_price_filter = nb_catalog_browser_visible($props['show_price_filter'] ?? '1', true);
$show_sort = nb_catalog_browser_visible($props['show_sort'] ?? '1', true);
$show_active_filters = nb_catalog_browser_visible($props['show_active_filters'] ?? '1', true);
$show_image = nb_catalog_browser_visible($props['show_image'] ?? '1', true);
$show_category = nb_catalog_browser_visible($props['show_category'] ?? '1', true);
$show_badge = nb_catalog_browser_visible($props['show_badge'] ?? '1', true);
$show_price = nb_catalog_browser_visible($props['show_price'] ?? '1', true);
$show_old_price = nb_catalog_browser_visible($props['show_old_price'] ?? '1', true);
$show_excerpt = nb_catalog_browser_visible($props['show_excerpt'] ?? '1', true);
$show_cta = nb_catalog_browser_visible($props['show_cta'] ?? '1', true);
$collection_mode = trim((string) ($props['collection_mode'] ?? 'all'));
if (!in_array($collection_mode, ['all', 'load_more', 'pagination'], true)) {
    $collection_mode = 'all';
}
$items_per_page = max(1, min(48, (int) ($props['items_per_page'] ?? 6)));
$show_results_count = nb_catalog_browser_visible($props['show_results_count'] ?? '1', true);
$search_fields = [
    'title' => nb_catalog_browser_visible($props['search_in_title'] ?? '1', true),
    'excerpt' => nb_catalog_browser_visible($props['search_in_excerpt'] ?? '1', true),
    'category' => nb_catalog_browser_visible($props['search_in_category'] ?? '1', true),
    'badge' => nb_catalog_browser_visible($props['search_in_badge'] ?? '1', true),
    'tags' => nb_catalog_browser_visible($props['search_in_tags'] ?? '1', true),
    'price' => nb_catalog_browser_visible($props['search_in_price'] ?? '0', false),
    'availability' => nb_catalog_browser_visible($props['search_in_availability'] ?? '1', true),
];
$content_width = max(320, (int) ($props['content_width'] ?? 1180));
$padding_top_desktop = max(0, (int) ($props['padding_top_desktop'] ?? 64));
$padding_bottom_desktop = max(0, (int) ($props['padding_bottom_desktop'] ?? 64));
$padding_top_mobile = max(0, (int) ($props['padding_top_mobile'] ?? 44));
$padding_bottom_mobile = max(0, (int) ($props['padding_bottom_mobile'] ?? 44));
$columns_desktop = max(1, min(6, (int) ($props['columns_desktop'] ?? 3)));
$columns_mobile = max(1, min(2, (int) ($props['columns_mobile'] ?? 1)));
$card_gap_desktop = max(0, (int) ($props['card_gap_desktop'] ?? 18));
$card_gap_mobile = max(0, (int) ($props['card_gap_mobile'] ?? 14));
$header_gap_desktop = max(0, (int) ($props['header_gap_desktop'] ?? 18));
$header_gap_mobile = max(0, (int) ($props['header_gap_mobile'] ?? 14));
$media_radius = max(0, (int) ($props['media_radius'] ?? 20));
$item_surface_radius = max(0, (int) ($props['item_surface_radius'] ?? 22));
$layout_variant_class = $columns_desktop >= 5 ? ' nb-catalog-browser--dense' : ($columns_desktop >= 4 ? ' nb-catalog-browser--compact' : '');

$items = [];
foreach ((array) ($props['items'] ?? []) as $item) {
    if (!is_array($item)) {
        continue;
    }

    $normalized_item = nb_catalog_browser_normalize_item($item);
    if ($normalized_item) {
        $normalized_item['searchText'] = nb_catalog_browser_build_search_text($normalized_item, $search_fields);
        $items[] = $normalized_item;
    }
}

$category_options = [];
foreach ($items as $item) {
    $category_value = trim((string) ($item['categoryValue'] ?? ''));
    if ($category_value === '') {
        continue;
    }

    $category_options[$category_value] = $category_value;
}
ksort($category_options, SORT_NATURAL | SORT_FLAG_CASE);

$section_style = sprintf(
    '--nb-catalog-width:%dpx;--nb-catalog-padding-top:%dpx;--nb-catalog-padding-bottom:%dpx;--nb-catalog-padding-top-mobile:%dpx;--nb-catalog-padding-bottom-mobile:%dpx;--nb-catalog-columns:%d;--nb-catalog-columns-mobile:%d;--nb-catalog-gap:%dpx;--nb-catalog-gap-mobile:%dpx;--nb-catalog-header-gap:%dpx;--nb-catalog-header-gap-mobile:%dpx;--nb-catalog-media-radius:%dpx;--nb-catalog-card-radius:%dpx;',
    $content_width,
    $padding_top_desktop,
    $padding_bottom_desktop,
    $padding_top_mobile,
    $padding_bottom_mobile,
    $columns_desktop,
    $columns_mobile,
    $card_gap_desktop,
    $card_gap_mobile,
    $header_gap_desktop,
    $header_gap_mobile,
    $media_radius,
    $item_surface_radius
);
?>
<section class="nb-section nb-catalog-browser nb-catalog-browser--align-<?= htmlspecialchars($align, ENT_QUOTES, 'UTF-8') ?><?= $layout_variant_class ?>" id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>" data-nb-block="catalog_browser" data-nb-theme="<?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?>" style="<?= htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') ?>">
    <div class="nb-container nb-catalog-browser__container">
        <?php if ($heading !== '' || $intro !== '' || ($section_link_label !== '' && $section_link_url !== '')): ?>
        <header class="nb-catalog-browser__header">
            <div class="nb-catalog-browser__header-main">
                <?php if ($heading !== ''): ?>
                <h2 class="nb-catalog-browser__title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php endif; ?>
                <?php if ($intro !== ''): ?>
                <div class="nb-catalog-browser__subtitle"><?= nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) ?></div>
                <?php endif; ?>
            </div>
            <?php if ($section_link_label !== '' && $section_link_url !== ''): ?>
            <a class="nb-catalog-browser__section-link" href="<?= htmlspecialchars($section_link_url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($section_link_label, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        </header>
        <?php endif; ?>

        <?php if ($items && ($show_search || $show_category_filter || $show_price_filter || $show_sort)): ?>
        <div class="nb-catalog-browser__toolbar" data-role="catalog-toolbar">
            <?php if ($show_search): ?>
            <label class="nb-catalog-browser__control nb-catalog-browser__control--search">
                <span class="nb-catalog-browser__control-label">Поиск</span>
                <input type="search" class="nb-catalog-browser__input" placeholder="Найти по названию или описанию" data-role="catalog-search">
            </label>
            <?php endif; ?>

            <?php if ($show_category_filter): ?>
            <label class="nb-catalog-browser__control">
                <span class="nb-catalog-browser__control-label">Категория</span>
                <select class="nb-catalog-browser__select" data-role="catalog-category">
                    <option value="">Все категории</option>
                    <?php foreach ($category_options as $category_option): ?>
                    <option value="<?= htmlspecialchars($category_option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category_option, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php endif; ?>

            <?php if ($show_price_filter): ?>
            <div class="nb-catalog-browser__control nb-catalog-browser__control--price">
                <span class="nb-catalog-browser__control-label">Цена</span>
                <div class="nb-catalog-browser__price-filters">
                    <input type="number" class="nb-catalog-browser__input" placeholder="от" data-role="catalog-price-min">
                    <input type="number" class="nb-catalog-browser__input" placeholder="до" data-role="catalog-price-max">
                </div>
            </div>
            <?php endif; ?>

            <?php if ($show_sort): ?>
            <label class="nb-catalog-browser__control">
                <span class="nb-catalog-browser__control-label">Сортировка</span>
                <select class="nb-catalog-browser__select" data-role="catalog-sort">
                    <option value="default">По порядку блока</option>
                    <option value="title-asc">По названию A-Z</option>
                    <option value="price-asc">Сначала дешевле</option>
                    <option value="price-desc">Сначала дороже</option>
                </select>
            </label>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($show_active_filters && $items): ?>
        <div class="nb-catalog-browser__active-filters" data-role="catalog-active" hidden></div>
        <?php endif; ?>

        <?php if ($items && $show_results_count): ?>
        <div class="nb-catalog-browser__results-row" data-role="catalog-results-row" hidden>
            <div class="nb-catalog-browser__results" data-role="catalog-results"></div>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-catalog-browser__grid" data-role="catalog-grid">
            <?php foreach ($items as $index => $item): ?>
            <?php $gallery_json = htmlspecialchars(json_encode($item['gallery'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>
            <article class="nb-catalog-browser__card" data-order="<?= $index ?>" data-search="<?= htmlspecialchars($item['searchText'], ENT_QUOTES, 'UTF-8') ?>" data-category="<?= htmlspecialchars((string) ($item['categoryValue'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-price="<?= htmlspecialchars((string) ($item['priceValue'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($show_image && $item['image'] !== ''): ?>
                <button type="button" class="nb-catalog-browser__media" data-role="media-open" data-gallery="<?= $gallery_json ?>" data-title="<?= $item['title'] ?>">
                    <img class="nb-catalog-browser__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                </button>
                <?php endif; ?>
                <div class="nb-catalog-browser__body">
                    <?php if ($show_badge && $item['badge'] !== ''): ?>
                    <div class="nb-catalog-browser__badge"><?= $item['badge'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_category && $item['category'] !== ''): ?>
                    <?php if ($item['categoryUrl'] !== ''): ?>
                    <a class="nb-catalog-browser__category" href="<?= $item['categoryUrl'] ?>"><?= $item['category'] ?></a>
                    <?php else: ?>
                    <div class="nb-catalog-browser__category"><?= $item['category'] ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($item['title'] !== ''): ?>
                    <h3 class="nb-catalog-browser__card-title">
                        <?php if ($item['url'] !== ''): ?>
                        <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                        <?php else: ?>
                        <?= $item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                    <div class="nb-catalog-browser__excerpt"><?= $item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_price && ($item['price'] !== '' || ($show_old_price && $item['priceOld'] !== ''))): ?>
                    <div class="nb-catalog-browser__price-line">
                        <?php if ($show_old_price && $item['priceOld'] !== ''): ?>
                        <span class="nb-catalog-browser__price-old"><?= $item['priceOld'] ?></span>
                        <?php endif; ?>
                        <?php if ($item['price'] !== ''): ?>
                        <span class="nb-catalog-browser__price"><?= $item['price'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($item['availability'] !== ''): ?>
                    <div class="nb-catalog-browser__availability"><?= $item['availability'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_cta && $item['ctaLabel'] !== ''): ?>
                    <?php $cta_href = $item['ctaUrl'] !== '' ? $item['ctaUrl'] : $item['url']; ?>
                    <?php if ($cta_href !== ''): ?>
                    <a class="nb-catalog-browser__cta" href="<?= $cta_href ?>"><?= $item['ctaLabel'] ?></a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="nb-catalog-browser__empty nb-catalog-browser__empty--filtered" data-role="catalog-empty" hidden>Ничего не найдено по текущим фильтрам.</div>
        <div class="nb-catalog-browser__footer" data-role="catalog-footer" hidden>
            <button type="button" class="nb-catalog-browser__more" data-role="catalog-more" hidden>Показать ещё</button>
            <div class="nb-catalog-browser__pagination" data-role="catalog-pagination" hidden></div>
        </div>
        <?php else: ?>
        <div class="nb-catalog-browser__empty">Каталог пока пуст. Добавьте первую карточку.</div>
        <?php endif; ?>
    </div>

    <?php if ($items): ?>
    <div class="nb-catalog-browser__modal" data-role="media-modal" hidden>
        <div class="nb-catalog-browser__modal-backdrop" data-role="media-close"></div>
        <div class="nb-catalog-browser__modal-dialog" role="dialog" aria-modal="true" aria-label="Просмотр изображений">
            <button type="button" class="nb-catalog-browser__modal-close" data-role="media-close" aria-label="Закрыть">&times;</button>
            <button type="button" class="nb-catalog-browser__modal-nav nb-catalog-browser__modal-nav--prev" data-role="media-prev" aria-label="Предыдущее изображение">&#8249;</button>
            <div class="nb-catalog-browser__modal-stage" data-role="media-stage">
                <img class="nb-catalog-browser__modal-image" data-role="media-image" alt="">
                <div class="nb-catalog-browser__modal-caption" data-role="media-caption"></div>
                <div class="nb-catalog-browser__modal-counter" data-role="media-counter"></div>
            </div>
            <button type="button" class="nb-catalog-browser__modal-nav nb-catalog-browser__modal-nav--next" data-role="media-next" aria-label="Следующее изображение">&#8250;</button>
        </div>
    </div>
    <script>
    (function() {
        var root = document.getElementById('block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>');
        if (!root) {
            return;
        }

        var grid = root.querySelector('[data-role="catalog-grid"]');
        var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('.nb-catalog-browser__card')) : [];
        var emptyState = root.querySelector('[data-role="catalog-empty"]');
        var activeFilters = root.querySelector('[data-role="catalog-active"]');
        var resultsRow = root.querySelector('[data-role="catalog-results-row"]');
        var resultsBar = root.querySelector('[data-role="catalog-results"]');
        var footer = root.querySelector('[data-role="catalog-footer"]');
        var moreButton = root.querySelector('[data-role="catalog-more"]');
        var pagination = root.querySelector('[data-role="catalog-pagination"]');
        var searchInput = root.querySelector('[data-role="catalog-search"]');
        var categorySelect = root.querySelector('[data-role="catalog-category"]');
        var priceMinInput = root.querySelector('[data-role="catalog-price-min"]');
        var priceMaxInput = root.querySelector('[data-role="catalog-price-max"]');
        var sortSelect = root.querySelector('[data-role="catalog-sort"]');
        var collectionMode = '<?= htmlspecialchars($collection_mode, ENT_QUOTES, 'UTF-8') ?>';
        var itemsPerPage = Math.max(1, parseInt('<?= (int) $items_per_page ?>', 10) || 1);
        var showResultsCount = <?= $show_results_count ? 'true' : 'false' ?>;
        var currentPage = 1;
        var visibleLimit = itemsPerPage;

        function sanitizeLabel(value) {
            return String(value || '').replace(/[&<>"']/g, '');
        }

        function renderActiveFilters(filters) {
            if (!activeFilters) {
                return;
            }

            var chips = [];
            if (filters.search) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Поиск: ' + sanitizeLabel(filters.search) + '</span>');
            }
            if (filters.category) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Категория: ' + sanitizeLabel(filters.category) + '</span>');
            }
            if (filters.minPrice) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Цена от ' + filters.minPrice + '</span>');
            }
            if (filters.maxPrice) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Цена до ' + filters.maxPrice + '</span>');
            }

            if (!chips.length) {
                activeFilters.hidden = true;
                activeFilters.innerHTML = '';
                return;
            }

            activeFilters.hidden = false;
            activeFilters.innerHTML = chips.join('') + '<button type="button" class="nb-catalog-browser__filter-reset" data-role="catalog-reset">Сбросить</button>';
        }

        function buildPaginationItems(totalPages, page) {
            var items = [];
            if (totalPages <= 7) {
                for (var index = 1; index <= totalPages; index++) {
                    items.push(index);
                }
                return items;
            }

            items.push(1);
            var start = Math.max(2, page - 1);
            var end = Math.min(totalPages - 1, page + 1);

            if (start > 2) {
                items.push('ellipsis-start');
            }

            for (var middle = start; middle <= end; middle++) {
                items.push(middle);
            }

            if (end < totalPages - 1) {
                items.push('ellipsis-end');
            }

            items.push(totalPages);
            return items;
        }

        function renderResults(totalMatches, displayedCount, totalPages) {
            if (!resultsBar || !resultsRow) {
                return;
            }

            if (!showResultsCount || totalMatches < 1) {
                resultsRow.hidden = true;
                resultsBar.textContent = '';
                return;
            }

            resultsRow.hidden = false;
            if (collectionMode === 'pagination' && totalPages > 1) {
                resultsBar.textContent = 'Страница ' + currentPage + ' из ' + totalPages + ' · ' + totalMatches + ' карточек';
                return;
            }

            if (collectionMode === 'load_more' && displayedCount < totalMatches) {
                resultsBar.textContent = 'Показано ' + displayedCount + ' из ' + totalMatches + ' карточек';
                return;
            }

            resultsBar.textContent = 'Найдено ' + totalMatches + ' карточек';
        }

        function renderCollectionNavigation(totalMatches, displayedCount, totalPages) {
            if (!footer) {
                return;
            }

            var hasControls = false;

            if (moreButton) {
                moreButton.hidden = true;
            }

            if (pagination) {
                pagination.hidden = true;
                pagination.innerHTML = '';
            }

            if (collectionMode === 'load_more' && moreButton && displayedCount < totalMatches) {
                hasControls = true;
                moreButton.hidden = false;
                moreButton.textContent = 'Показать ещё ' + Math.min(itemsPerPage, totalMatches - displayedCount);
            }

            if (collectionMode === 'pagination' && pagination && totalPages > 1) {
                hasControls = true;
                pagination.hidden = false;
                pagination.innerHTML = ''
                    + '<button type="button" class="nb-catalog-browser__page-control" data-role="catalog-page" data-page="' + Math.max(1, currentPage - 1) + '"' + (currentPage === 1 ? ' disabled' : '') + '>Назад</button>'
                    + buildPaginationItems(totalPages, currentPage).map(function(item) {
                        if (typeof item !== 'number') {
                            return '<span class="nb-catalog-browser__page-gap">…</span>';
                        }

                        return '<button type="button" class="nb-catalog-browser__page' + (item === currentPage ? ' is-active' : '') + '" data-role="catalog-page" data-page="' + item + '"' + (item === currentPage ? ' aria-current="page"' : '') + '>' + item + '</button>';
                    }).join('')
                    + '<button type="button" class="nb-catalog-browser__page-control" data-role="catalog-page" data-page="' + Math.min(totalPages, currentPage + 1) + '"' + (currentPage === totalPages ? ' disabled' : '') + '>Вперёд</button>';
            }

            footer.hidden = !hasControls || totalMatches < 1;
        }

        function applyFilters(options) {
            if (!grid) {
                return;
            }

            options = options || {};
            if (options.resetCollectionState !== false) {
                currentPage = 1;
                visibleLimit = itemsPerPage;
            }

            var filters = {
                search: searchInput ? String(searchInput.value || '').toLowerCase().trim() : '',
                category: categorySelect ? String(categorySelect.value || '').trim() : '',
                minPrice: priceMinInput ? parseFloat(priceMinInput.value || 0) || 0 : 0,
                maxPrice: priceMaxInput ? parseFloat(priceMaxInput.value || 0) || 0 : 0,
                sort: sortSelect ? String(sortSelect.value || 'default') : 'default'
            };

            var visibleCards = cards.filter(function(card) {
                var searchValue = String(card.getAttribute('data-search') || '').toLowerCase();
                var categoryValue = String(card.getAttribute('data-category') || '').trim();
                var priceValue = parseFloat(card.getAttribute('data-price') || '0') || 0;

                if (filters.search && searchValue.indexOf(filters.search) === -1) {
                    return false;
                }
                if (filters.category && categoryValue !== filters.category) {
                    return false;
                }
                if (filters.minPrice && (!priceValue || priceValue < filters.minPrice)) {
                    return false;
                }
                if (filters.maxPrice && priceValue > filters.maxPrice) {
                    return false;
                }
                return true;
            });

            var sortedCards = visibleCards.slice();
            var displayedCards = [];
            var totalPages = 1;
            if (filters.sort === 'title-asc') {
                sortedCards.sort(function(a, b) {
                    return String(a.getAttribute('data-search') || '').localeCompare(String(b.getAttribute('data-search') || ''), 'ru');
                });
            } else if (filters.sort === 'price-asc') {
                sortedCards.sort(function(a, b) {
                    return (parseFloat(a.getAttribute('data-price') || '0') || 0) - (parseFloat(b.getAttribute('data-price') || '0') || 0);
                });
            } else if (filters.sort === 'price-desc') {
                sortedCards.sort(function(a, b) {
                    return (parseFloat(b.getAttribute('data-price') || '0') || 0) - (parseFloat(a.getAttribute('data-price') || '0') || 0);
                });
            } else {
                sortedCards.sort(function(a, b) {
                    return (parseInt(a.getAttribute('data-order') || '0', 10) || 0) - (parseInt(b.getAttribute('data-order') || '0', 10) || 0);
                });
            }

            if (collectionMode === 'pagination') {
                totalPages = Math.max(1, Math.ceil(sortedCards.length / itemsPerPage));
                currentPage = Math.min(Math.max(1, currentPage), totalPages);
                displayedCards = sortedCards.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
            } else if (collectionMode === 'load_more') {
                visibleLimit = Math.max(itemsPerPage, visibleLimit);
                displayedCards = sortedCards.slice(0, Math.min(visibleLimit, sortedCards.length));
            } else {
                displayedCards = sortedCards.slice();
            }

            cards.forEach(function(card) {
                card.hidden = true;
            });
            displayedCards.forEach(function(card) {
                card.hidden = false;
                grid.appendChild(card);
            });

            if (emptyState) {
                emptyState.hidden = sortedCards.length > 0;
            }

            renderActiveFilters(filters);
            renderResults(sortedCards.length, displayedCards.length, totalPages);
            renderCollectionNavigation(sortedCards.length, displayedCards.length, totalPages);
        }

        root.addEventListener('click', function(event) {
            var reset = event.target.closest('[data-role="catalog-reset"]');
            if (reset) {
                if (searchInput) searchInput.value = '';
                if (categorySelect) categorySelect.value = '';
                if (priceMinInput) priceMinInput.value = '';
                if (priceMaxInput) priceMaxInput.value = '';
                if (sortSelect) sortSelect.value = 'default';
                applyFilters();
                return;
            }

            var moreTrigger = event.target.closest('[data-role="catalog-more"]');
            if (moreTrigger) {
                visibleLimit += itemsPerPage;
                applyFilters({ resetCollectionState: false });
                return;
            }

            var pageTrigger = event.target.closest('[data-role="catalog-page"]');
            if (pageTrigger && !pageTrigger.disabled) {
                currentPage = Math.max(1, parseInt(pageTrigger.getAttribute('data-page') || '1', 10) || 1);
                applyFilters({ resetCollectionState: false });
            }
        });

        [searchInput, categorySelect, priceMinInput, priceMaxInput, sortSelect].forEach(function(control) {
            if (!control) {
                return;
            }
            control.addEventListener('input', applyFilters);
            control.addEventListener('change', applyFilters);
        });

        applyFilters();

        var modal = root.querySelector('[data-role="media-modal"]');
        if (!modal) {
            return;
        }

        var modalImage = modal.querySelector('[data-role="media-image"]');
        var modalCaption = modal.querySelector('[data-role="media-caption"]');
        var modalCounter = modal.querySelector('[data-role="media-counter"]');
        var prevButton = modal.querySelector('[data-role="media-prev"]');
        var nextButton = modal.querySelector('[data-role="media-next"]');
        var stage = modal.querySelector('[data-role="media-stage"]');
        var slides = [];
        var currentSlide = 0;
        var touchStartX = 0;

        function renderSlide() {
            if (!slides.length) {
                return;
            }

            var slide = slides[currentSlide];
            modalImage.src = slide.src || '';
            modalImage.alt = slide.alt || '';
            modalCaption.textContent = slide.caption || slide.alt || '';
            modalCounter.textContent = slides.length > 1 ? (currentSlide + 1) + ' / ' + slides.length : '';
            prevButton.hidden = slides.length < 2;
            nextButton.hidden = slides.length < 2;
        }

        function openModal(gallery) {
            if (!Array.isArray(gallery) || !gallery.length) {
                return;
            }

            slides = gallery;
            currentSlide = 0;
            renderSlide();
            modal.hidden = false;
            document.documentElement.classList.add('nb-catalog-browser-modal-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.hidden = true;
            modalImage.removeAttribute('src');
            modalCaption.textContent = '';
            modalCounter.textContent = '';
            document.documentElement.classList.remove('nb-catalog-browser-modal-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        function stepModal(direction) {
            if (slides.length < 2) {
                return;
            }
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            renderSlide();
        }

        root.addEventListener('click', function(event) {
            var trigger = event.target.closest('[data-role="media-open"]');
            if (!trigger) {
                return;
            }

            event.preventDefault();
            var gallery = [];
            try {
                gallery = JSON.parse(trigger.getAttribute('data-gallery') || '[]');
            } catch (error) {
                gallery = [];
            }
            openModal(gallery);
        });

        modal.addEventListener('click', function(event) {
            if (event.target.closest('[data-role="media-close"]')) {
                closeModal();
            }
            if (event.target.closest('[data-role="media-prev"]')) {
                stepModal(-1);
            }
            if (event.target.closest('[data-role="media-next"]')) {
                stepModal(1);
            }
        });

        document.addEventListener('keydown', function(event) {
            if (modal.hidden) {
                return;
            }
            if (event.key === 'Escape') {
                closeModal();
            } else if (event.key === 'ArrowLeft') {
                stepModal(-1);
            } else if (event.key === 'ArrowRight') {
                stepModal(1);
            }
        });

        stage.addEventListener('touchstart', function(event) {
            touchStartX = event.changedTouches && event.changedTouches[0] ? event.changedTouches[0].clientX : 0;
        }, { passive: true });

        stage.addEventListener('touchend', function(event) {
            var endX = event.changedTouches && event.changedTouches[0] ? event.changedTouches[0].clientX : 0;
            var deltaX = endX - touchStartX;
            if (Math.abs(deltaX) < 40) {
                return;
            }
            stepModal(deltaX < 0 ? 1 : -1);
        }, { passive: true });
    })();
    </script>
    <?php endif; ?>
</section>