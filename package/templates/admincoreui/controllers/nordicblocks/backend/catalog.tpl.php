<?php
$this->setPageTitle('Каталог блоков — NordicBlocks');
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Каталог блоков');
$this->addMenuItems('admin_toolbar', $menu);

$selected = is_array($selected_entry) ? $selected_entry : [];
$selected_tags = is_array($selected['tags'] ?? null) ? $selected['tags'] : [];
$selected_editor = is_array($selected['editor'] ?? null) ? $selected['editor'] : [];
$selected_curation = is_array($selected['curation'] ?? null) ? $selected['curation'] : [];

$availability_label = function ($value) {
    switch ((string) $value) {
        case 'free':
            return 'Free';
        case 'coming_soon':
            return 'Скоро';
        case 'unavailable':
            return 'Недоступно';
        default:
            return 'Черновик';
    }
};

$availability_class = function ($value) {
    switch ((string) $value) {
        case 'free':
            return 'nb-catalog-badge--free';
        case 'coming_soon':
            return 'nb-catalog-badge--soon';
        case 'unavailable':
            return 'nb-catalog-badge--muted';
        default:
            return 'nb-catalog-badge--muted';
    }
};

$card_status = function ($entry, $existing_block) {
    if ($existing_block) {
        return ['label' => 'Установлен', 'text' => (string) ($existing_block['title'] ?? 'Живой block entry уже создан.')];
    }

    if ((string) ($entry['availability'] ?? '') === 'free') {
        return ['label' => 'Готов к установке', 'text' => 'Можно создать реальный block entry и сразу открыть editor.'];
    }

    return ['label' => 'Пока не ставим', 'text' => 'Для этого entry install flow появится позже.'];
};

$render_preview = function ($entry) use ($availability_label, $availability_class) {
    $preview = is_array($entry['preview'] ?? null) ? $entry['preview'] : [];
    $layout = (string) ($preview['layout'] ?? 'wide_panels');
    $theme = (string) ($preview['theme'] ?? 'sunstone');
    $title = (string) ($entry['title'] ?? 'Catalog entry');
    $availability = (string) ($entry['availability'] ?? 'free');
    $preview_type = (string) ($preview['type'] ?? 'schematic');
    $image_url = trim((string) ($preview['imageUrl'] ?? ''));
    $image_alt = trim((string) ($preview['alt'] ?? $title));
    ?>
    <div class="nb-catalog-card__art nb-catalog-card__art--<?= html($theme) ?>">
        <span class="nb-catalog-badge <?= $availability_class($availability) ?>"><?= html($availability_label($availability)) ?></span>
        <div class="nb-catalog-card__signal"></div>
        <?php if ($preview_type === 'image' && $image_url !== '') { ?>
            <img class="nb-catalog-card__image" src="<?= html($image_url) ?>" alt="<?= html($image_alt) ?>">
        <?php } elseif ($layout === 'editorial_split') { ?>
            <div class="nb-catalog-art nb-catalog-art--editorial">
                <div class="nb-catalog-art__column nb-catalog-art__column--type">
                    <span class="nb-catalog-art__eyebrow"></span>
                    <span class="nb-catalog-art__headline nb-catalog-art__headline--tall"></span>
                    <span class="nb-catalog-art__headline"></span>
                    <span class="nb-catalog-art__line nb-catalog-art__line--short"></span>
                </div>
                <div class="nb-catalog-art__column nb-catalog-art__column--visual">
                    <span class="nb-catalog-art__frame"></span>
                    <span class="nb-catalog-art__caption"></span>
                </div>
            </div>
        <?php } elseif ($layout === 'media_showcase') { ?>
            <div class="nb-catalog-art nb-catalog-art--media">
                <span class="nb-catalog-art__halo"></span>
                <span class="nb-catalog-art__device"></span>
                <span class="nb-catalog-art__caption nb-catalog-art__caption--left"></span>
                <span class="nb-catalog-art__caption nb-catalog-art__caption--right"></span>
            </div>
        <?php } else { ?>
            <div class="nb-catalog-art nb-catalog-art--panels">
                <div class="nb-catalog-art__stack nb-catalog-art__stack--content">
                    <span class="nb-catalog-art__eyebrow"></span>
                    <span class="nb-catalog-art__headline nb-catalog-art__headline--wide"></span>
                    <span class="nb-catalog-art__headline"></span>
                    <span class="nb-catalog-art__line"></span>
                    <span class="nb-catalog-art__line nb-catalog-art__line--short"></span>
                    <div class="nb-catalog-art__cta-row">
                        <span></span>
                        <span></span>
                    </div>
                </div>
                <div class="nb-catalog-art__stack nb-catalog-art__stack--visual">
                    <span class="nb-catalog-art__panel"></span>
                    <span class="nb-catalog-art__panel nb-catalog-art__panel--small"></span>
                </div>
            </div>
        <?php } ?>
        <div class="nb-catalog-card__titleplate"><?= html($title) ?></div>
    </div>
    <?php
};
?>

<style>
.nb-catalog-shell {
    --paper: #f5efe3;
    --sand: #efe4d1;
    --ink: #172033;
    --muted: #6d7485;
    --border: rgba(23, 32, 51, .12);
    --card: rgba(255, 255, 255, .88);
    --shadow: 0 24px 60px rgba(23, 32, 51, .10);
    max-width: 1460px;
    padding-bottom: 2rem;
}
.nb-catalog-hero {
    position: relative;
    overflow: hidden;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(173, 93, 55, .24), transparent 30%),
        radial-gradient(circle at right center, rgba(75, 122, 112, .18), transparent 32%),
        linear-gradient(135deg, #f8f4ea 0%, #ede4d5 50%, #f9f6ef 100%);
    border: 1px solid rgba(97, 78, 56, .12);
    box-shadow: var(--shadow);
}
.nb-catalog-hero::after {
    content: '';
    position: absolute;
    inset: auto -80px -80px auto;
    width: 240px;
    height: 240px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .24);
    filter: blur(10px);
}
.nb-catalog-hero__grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(280px, .8fr);
    gap: 1.5rem;
    align-items: end;
}
.nb-catalog-kicker {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, .64);
    border: 1px solid rgba(23, 32, 51, .10);
    color: #6a4b38;
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.nb-catalog-headline {
    margin: .9rem 0 .85rem;
    max-width: 9.5em;
    color: var(--ink);
    font: 700 clamp(2.3rem, 4vw, 4.4rem)/.95 Georgia, 'Times New Roman', serif;
    letter-spacing: -.04em;
}
.nb-catalog-lead {
    max-width: 44rem;
    margin: 0;
    color: rgba(23, 32, 51, .78);
    font-size: 1rem;
    line-height: 1.7;
}
.nb-catalog-hero__meta {
    display: grid;
    gap: .85rem;
    align-self: stretch;
}
.nb-catalog-stat {
    padding: 1rem 1.1rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, .68);
    border: 1px solid rgba(23, 32, 51, .10);
    backdrop-filter: blur(10px);
}
.nb-catalog-stat__label {
    display: block;
    margin-bottom: .28rem;
    color: #7b8294;
    font-size: .74rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.nb-catalog-stat__value {
    color: var(--ink);
    font-size: 1rem;
    font-weight: 700;
}
.nb-catalog-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(340px, .8fr);
    gap: 1.4rem;
    margin-top: 1.4rem;
    align-items: start;
}
.nb-catalog-main,
.nb-catalog-side {
    min-width: 0;
}
.nb-catalog-panel {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 24px;
    box-shadow: var(--shadow);
}
.nb-catalog-panel--padded {
    padding: 1.35rem;
}
.nb-catalog-tabs {
    display: flex;
    gap: .65rem;
    flex-wrap: wrap;
    margin-bottom: 1.1rem;
}
.nb-catalog-tab {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .6rem .95rem;
    border-radius: 999px;
    border: 1px solid rgba(23, 32, 51, .10);
    background: rgba(255, 255, 255, .78);
    color: var(--ink);
    font-size: .82rem;
    font-weight: 700;
}
.nb-catalog-tab--active {
    background: #1d314f;
    border-color: #1d314f;
    color: #fff;
}
.nb-catalog-tab--muted {
    color: var(--muted);
}
.nb-catalog-tab__hint {
    font-size: .72rem;
    opacity: .8;
}
.nb-catalog-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}
.nb-catalog-card {
    display: flex;
    flex-direction: column;
    min-height: 100%;
    overflow: hidden;
    border-radius: 24px;
    background: rgba(255,255,255,.85);
    border: 1px solid rgba(23,32,51,.10);
    text-decoration: none;
    color: inherit;
    box-shadow: 0 10px 28px rgba(23, 32, 51, .08);
    transition: transform .18s ease, box-shadow .2s ease, border-color .2s ease;
}
.nb-catalog-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 36px rgba(23, 32, 51, .12);
}
.nb-catalog-card--selected {
    border-color: rgba(173, 93, 55, .42);
    box-shadow: 0 16px 42px rgba(173, 93, 55, .18);
}
.nb-catalog-card__art {
    position: relative;
    min-height: 238px;
    padding: 1rem;
    overflow: hidden;
}
.nb-catalog-card__art--sunstone {
    background: linear-gradient(135deg, #1b2e4d 0%, #8c4f33 52%, #e9d7bb 100%);
}
.nb-catalog-card__art--paper_ink {
    background: linear-gradient(135deg, #f3ebdf 0%, #d7c5af 45%, #40556c 100%);
}
.nb-catalog-card__art--sea_glass {
    background: linear-gradient(135deg, #173a4d 0%, #3f6c68 45%, #d8e6dc 100%);
}
.nb-catalog-card__signal {
    position: absolute;
    top: -48px;
    right: -48px;
    width: 160px;
    height: 160px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
}
.nb-catalog-card__image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.nb-catalog-card__titleplate {
    position: absolute;
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    padding-top: 2rem;
    color: #fff;
    font: 700 1.2rem/1.05 Georgia, 'Times New Roman', serif;
    letter-spacing: -.03em;
}
.nb-catalog-badge {
    position: relative;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    padding: .35rem .65rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.nb-catalog-badge--free {
    color: #113728;
    background: rgba(223, 252, 232, .92);
}
.nb-catalog-badge--soon {
    color: #5a3810;
    background: rgba(255, 229, 183, .94);
}
.nb-catalog-badge--muted {
    color: #445063;
    background: rgba(230, 236, 243, .9);
}
.nb-catalog-art {
    position: absolute;
    inset: 1.1rem 1.1rem 3.8rem 1.1rem;
    display: grid;
    gap: .7rem;
}
.nb-catalog-art--panels {
    grid-template-columns: 1.2fr .86fr;
}
.nb-catalog-art__stack {
    display: grid;
    gap: .55rem;
    padding: .95rem;
    border-radius: 20px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.16);
    backdrop-filter: blur(5px);
}
.nb-catalog-art__stack--visual {
    grid-template-rows: 1fr auto;
}
.nb-catalog-art__eyebrow,
.nb-catalog-art__headline,
.nb-catalog-art__line,
.nb-catalog-art__panel,
.nb-catalog-art__frame,
.nb-catalog-art__caption,
.nb-catalog-art__device,
.nb-catalog-art__halo,
.nb-catalog-art__cta-row span {
    display: block;
    background: rgba(255,255,255,.78);
    border: 1px solid rgba(255,255,255,.22);
    box-shadow: 0 14px 30px rgba(17, 19, 27, .10);
}
.nb-catalog-art__eyebrow {
    width: 38%;
    height: 12px;
    border-radius: 999px;
}
.nb-catalog-art__headline {
    height: 24px;
    border-radius: 12px;
}
.nb-catalog-art__headline--wide { width: 94%; }
.nb-catalog-art__headline--tall { height: 74px; width: 86%; }
.nb-catalog-art__line {
    height: 12px;
    border-radius: 999px;
}
.nb-catalog-art__line--short { width: 68%; }
.nb-catalog-art__cta-row {
    display: flex;
    gap: .45rem;
    margin-top: auto;
}
.nb-catalog-art__cta-row span {
    width: 88px;
    height: 34px;
    border-radius: 999px;
}
.nb-catalog-art__panel {
    min-height: 112px;
    border-radius: 22px;
}
.nb-catalog-art__panel--small {
    min-height: 78px;
}
.nb-catalog-art--editorial {
    grid-template-columns: 1.08fr .92fr;
}
.nb-catalog-art__column {
    display: grid;
    gap: .65rem;
    padding: .9rem;
    border-radius: 22px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
}
.nb-catalog-art__column--visual {
    align-content: stretch;
}
.nb-catalog-art__frame {
    min-height: 126px;
    border-radius: 26px;
}
.nb-catalog-art__caption {
    height: 18px;
    border-radius: 999px;
}
.nb-catalog-art--media {
    place-items: center;
}
.nb-catalog-art__halo {
    position: absolute;
    width: 180px;
    height: 180px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 0;
    box-shadow: none;
}
.nb-catalog-art__device {
    position: relative;
    z-index: 1;
    width: min(76%, 220px);
    height: min(66%, 170px);
    border-radius: 28px;
}
.nb-catalog-art__caption--left,
.nb-catalog-art__caption--right {
    position: absolute;
    z-index: 1;
    width: 92px;
    height: 36px;
    border-radius: 18px;
}
.nb-catalog-art__caption--left {
    left: 1.15rem;
    bottom: 1.3rem;
}
.nb-catalog-art__caption--right {
    right: 1.15rem;
    top: 1.8rem;
}
.nb-catalog-card__body {
    display: grid;
    gap: .85rem;
    padding: 1rem 1.05rem 1.1rem;
    flex: 1 1 auto;
}
.nb-catalog-card__topline {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
}
.nb-catalog-chip {
    display: inline-flex;
    align-items: center;
    padding: .24rem .55rem;
    border-radius: 999px;
    background: #eef1f5;
    color: #415268;
    font-size: .72rem;
    font-weight: 700;
}
.nb-catalog-chip--family {
    background: #e5eef7;
    color: #24456c;
}
.nb-catalog-card__subtitle {
    margin: 0;
    color: var(--ink);
    font-size: .96rem;
    font-weight: 700;
    line-height: 1.35;
}
.nb-catalog-card__summary {
    margin: 0;
    color: var(--muted);
    font-size: .84rem;
    line-height: 1.6;
}
.nb-catalog-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
    margin-top: auto;
    padding-top: .75rem;
    border-top: 1px solid rgba(23,32,51,.08);
}
.nb-catalog-card__status {
    color: #586377;
    font-size: .75rem;
    line-height: 1.45;
}
.nb-catalog-card__status strong {
    color: var(--ink);
}
.nb-catalog-card__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .7rem 1rem;
    border-radius: 999px;
    background: #1d314f;
    color: #fff;
    font-size: .8rem;
    font-weight: 700;
    text-decoration: none;
    transition: background .15s ease;
}
.nb-catalog-card__cta:hover {
    background: #162740;
    color: #fff;
}
.nb-catalog-side {
    position: sticky;
    top: 1rem;
}
.nb-catalog-detail {
    overflow: hidden;
}
.nb-catalog-detail__head {
    padding: 1.35rem 1.35rem 1rem;
    border-bottom: 1px solid rgba(23,32,51,.08);
}
.nb-catalog-detail__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: #7a634e;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.nb-catalog-detail__title {
    margin: .55rem 0 .45rem;
    color: var(--ink);
    font: 700 2rem/.98 Georgia, 'Times New Roman', serif;
    letter-spacing: -.04em;
}
.nb-catalog-detail__summary {
    margin: 0;
    color: var(--muted);
    line-height: 1.7;
}
.nb-catalog-detail__meta,
.nb-catalog-detail__tags,
.nb-catalog-detail__actions,
.nb-catalog-detail__surface {
    padding: 1rem 1.35rem 0;
}
.nb-catalog-detail__meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .7rem;
}
.nb-catalog-kv {
    padding: .8rem .85rem;
    border-radius: 16px;
    background: rgba(243, 245, 248, .95);
    border: 1px solid rgba(23,32,51,.06);
}
.nb-catalog-kv__key {
    display: block;
    margin-bottom: .22rem;
    color: #8490a2;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .07em;
    text-transform: uppercase;
}
.nb-catalog-kv__value {
    color: var(--ink);
    font-size: .92rem;
    font-weight: 700;
}
.nb-catalog-detail__tags {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.nb-catalog-tag {
    display: inline-flex;
    align-items: center;
    padding: .38rem .65rem;
    border-radius: 999px;
    background: #f0ece4;
    color: #5d564b;
    font-size: .75rem;
    font-weight: 700;
}
.nb-catalog-detail__actions {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
}
.nb-catalog-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 0 1rem;
    border-radius: 999px;
    border: 1px solid transparent;
    font-size: .82rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
.nb-catalog-btn--primary {
    background: #1d314f;
    color: #fff;
}
.nb-catalog-btn--ghost {
    background: #fff;
    border-color: rgba(23,32,51,.10);
    color: var(--ink);
}
.nb-catalog-btn--disabled,
.nb-catalog-btn[disabled] {
    opacity: .55;
    cursor: not-allowed;
}
.nb-catalog-code {
    padding: 1rem 1.35rem 1.35rem;
}
.nb-catalog-code__tabs {
    display: flex;
    gap: .55rem;
    margin-bottom: .8rem;
}
.nb-catalog-code__tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .5rem .8rem;
    border-radius: 999px;
    border: 1px solid rgba(23,32,51,.10);
    background: #fff;
    color: #4f5e73;
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
    cursor: pointer;
}
.nb-catalog-code__tab.is-active {
    background: #1d314f;
    border-color: #1d314f;
    color: #fff;
}
.nb-catalog-code__path {
    margin-bottom: .65rem;
    color: #758093;
    font-size: .76rem;
}
.nb-catalog-code__panel {
    display: none;
}
.nb-catalog-code__panel.is-active {
    display: block;
}
.nb-catalog-code pre {
    margin: 0;
    max-height: 430px;
    overflow: auto;
    padding: 1rem;
    border-radius: 20px;
    background: #0f1727;
    color: #d6dfed;
    font: 500 .75rem/1.65 'JetBrains Mono', 'SFMono-Regular', Consolas, monospace;
}
.nb-catalog-copy {
    margin-left: auto;
}
.nb-catalog-note {
    padding: 0 1.35rem 1.35rem;
    color: #7b8294;
    font-size: .78rem;
    line-height: 1.6;
}
@media (max-width: 1180px) {
    .nb-catalog-grid,
    .nb-catalog-hero__grid {
        grid-template-columns: 1fr;
    }
    .nb-catalog-side {
        position: static;
    }
}
@media (max-width: 700px) {
    .nb-catalog-hero,
    .nb-catalog-panel--padded,
    .nb-catalog-detail__head,
    .nb-catalog-detail__meta,
    .nb-catalog-detail__tags,
    .nb-catalog-detail__actions,
    .nb-catalog-detail__surface,
    .nb-catalog-code,
    .nb-catalog-note {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .nb-catalog-detail__meta-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="nb-catalog-shell">
    <section class="nb-catalog-hero">
        <div class="nb-catalog-hero__grid">
            <div>
                <span class="nb-catalog-kicker">Curated MVP · Design-block-first</span>
                <h1 class="nb-catalog-headline">Каталог блоков, который уже выглядит как продукт.</h1>
                <p class="nb-catalog-lead">Это первый живой admin-prototype внутреннего каталога NordicBlocks. Он показывает curated hero-линейку, читает draft JSON contract и даёт команде реальный экран для обсуждения browse и install semantics до внедрения install-логики.</p>
            </div>
            <div class="nb-catalog-hero__meta">
                <div class="nb-catalog-stat">
                    <span class="nb-catalog-stat__label">Категория v1</span>
                    <span class="nb-catalog-stat__value">Hero</span>
                </div>
                <div class="nb-catalog-stat">
                    <span class="nb-catalog-stat__label">Anchor block</span>
                    <span class="nb-catalog-stat__value">Hero: Wide Panels</span>
                </div>
                <div class="nb-catalog-stat">
                    <span class="nb-catalog-stat__label">Текущий режим</span>
                    <span class="nb-catalog-stat__value">Живой UI без install-логики</span>
                </div>
            </div>
        </div>
    </section>

    <div class="nb-catalog-grid">
        <main class="nb-catalog-main">
            <section class="nb-catalog-panel nb-catalog-panel--padded">
                <div class="nb-catalog-tabs">
                    <span class="nb-catalog-tab nb-catalog-tab--active">Hero <span class="nb-catalog-tab__hint">3 curated entries</span></span>
                    <span class="nb-catalog-tab nb-catalog-tab--muted">Features <span class="nb-catalog-tab__hint">позже</span></span>
                    <span class="nb-catalog-tab nb-catalog-tab--muted">FAQ <span class="nb-catalog-tab__hint">позже</span></span>
                    <span class="nb-catalog-tab nb-catalog-tab--muted">Feed <span class="nb-catalog-tab__hint">позже</span></span>
                </div>

                <div class="nb-catalog-card-grid">
                    <?php foreach ($cards as $card) {
                        $entry = $card['entry'];
                        $existing_block = $card['existing_block'];
                        $status = $card_status($entry, $existing_block);
                    ?>
                        <a class="nb-catalog-card<?= $card['is_selected'] ? ' nb-catalog-card--selected' : '' ?>" href="<?= html($card['select_url']) ?>">
                            <?php $render_preview($entry); ?>
                            <div class="nb-catalog-card__body">
                                <div class="nb-catalog-card__topline">
                                    <span class="nb-catalog-chip nb-catalog-chip--family"><?= html((string) ($entry['family'] ?? '')) ?></span>
                                    <span class="nb-catalog-chip"><?= html((string) ($entry['category'] ?? '')) ?></span>
                                </div>
                                <p class="nb-catalog-card__subtitle"><?= html((string) ($entry['subtitle'] ?? '')) ?></p>
                                <p class="nb-catalog-card__summary"><?= html((string) ($entry['summary'] ?? '')) ?></p>
                                <div class="nb-catalog-card__footer">
                                    <div class="nb-catalog-card__status">
                                        <strong><?= html((string) $status['label']) ?></strong><br>
                                        <?= html((string) $status['text']) ?>
                                    </div>
                                    <span class="nb-catalog-card__cta"><?= $card['is_selected'] ? 'Выбрано' : 'Открыть' ?></span>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </section>
        </main>

        <aside class="nb-catalog-side">
            <section class="nb-catalog-panel nb-catalog-detail">
                <div class="nb-catalog-detail__head">
                    <span class="nb-catalog-detail__eyebrow">Selected Entry · <?= html((string) ($selected['slug'] ?? '')) ?></span>
                    <h2 class="nb-catalog-detail__title"><?= html((string) ($selected['title'] ?? 'Каталог блоков')) ?></h2>
                    <p class="nb-catalog-detail__summary"><?= html((string) ($selected['summary'] ?? '')) ?></p>
                </div>

                <div class="nb-catalog-detail__meta">
                    <div class="nb-catalog-detail__meta-grid">
                        <div class="nb-catalog-kv">
                            <span class="nb-catalog-kv__key">Availability</span>
                            <span class="nb-catalog-kv__value"><?= html($availability_label((string) ($selected['availability'] ?? ''))) ?></span>
                        </div>
                        <div class="nb-catalog-kv">
                            <span class="nb-catalog-kv__key">Distribution</span>
                            <span class="nb-catalog-kv__value"><?= html((string) ($selected['distributionModel'] ?? '')) ?></span>
                        </div>
                        <div class="nb-catalog-kv">
                            <span class="nb-catalog-kv__key">Editor mode</span>
                            <span class="nb-catalog-kv__value"><?= html((string) ($selected_editor['mode'] ?? '')) ?></span>
                        </div>
                        <div class="nb-catalog-kv">
                            <span class="nb-catalog-kv__key">Curation</span>
                            <span class="nb-catalog-kv__value"><?= html((string) ($selected_curation['status'] ?? '')) ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($selected_tags) { ?>
                    <div class="nb-catalog-detail__tags">
                        <?php foreach ($selected_tags as $tag) { ?>
                            <span class="nb-catalog-tag"><?= html((string) $tag) ?></span>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="nb-catalog-detail__actions">
                    <?php if ($selected_block) { ?>
                        <a class="nb-catalog-btn nb-catalog-btn--primary" href="<?= html((string) ($selected_block['editor_url'] ?? '#')) ?>">Открыть существующий блок</a>
                    <?php } elseif ((string) ($selected['availability'] ?? '') === 'free') { ?>
                        <form method="post" action="<?= html($install_url) ?>" style="display:inline-flex;">
                            <input type="hidden" name="csrf_token" value="<?= html((string) $csrf_token) ?>">
                            <input type="hidden" name="slug" value="<?= html((string) ($selected['slug'] ?? '')) ?>">
                            <button class="nb-catalog-btn nb-catalog-btn--primary" type="submit">Установить и открыть editor</button>
                        </form>
                    <?php } else { ?>
                        <button class="nb-catalog-btn nb-catalog-btn--primary nb-catalog-btn--disabled" type="button" disabled>Установка появится позже</button>
                    <?php } ?>
                    <a class="nb-catalog-btn nb-catalog-btn--ghost" href="<?= html($back_url) ?>">Назад к списку блоков</a>
                </div>

                <div class="nb-catalog-code" data-catalog-code>
                    <div class="nb-catalog-code__tabs">
                        <button class="nb-catalog-code__tab is-active" type="button" data-code-tab="entry">Entry JSON</button>
                        <button class="nb-catalog-code__tab" type="button" data-code-tab="schema">Schema</button>
                        <button class="nb-catalog-btn nb-catalog-btn--ghost nb-catalog-copy" type="button" data-copy-json>Копировать</button>
                    </div>
                    <div class="nb-catalog-code__panel is-active" data-code-panel="entry">
                        <div class="nb-catalog-code__path"><?= html((string) $entry_path_prefix) ?><?= html(strtoupper(str_replace('-', '_', (string) ($selected['slug'] ?? '')))) ?>.json</div>
                        <pre data-code-content="entry"><?= html($selected_entry_json) ?></pre>
                    </div>
                    <div class="nb-catalog-code__panel" data-code-panel="schema">
                        <div class="nb-catalog-code__path"><?= html($schema_path) ?></div>
                        <pre data-code-content="schema"><?= html($selected_schema_json) ?></pre>
                    </div>
                </div>

                <div class="nb-catalog-note">
                    Для free entry install loop уже включён: он создаёт реальный block entry в NordicBlocks и сразу переводит в editor. Coming soon entries пока остаются только в catalog-preview режиме.
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
(function () {
    const root = document.querySelector('[data-catalog-code]');
    if (!root) {
        return;
    }

    const tabs = Array.from(root.querySelectorAll('[data-code-tab]'));
    const panels = Array.from(root.querySelectorAll('[data-code-panel]'));
    const copyButton = root.querySelector('[data-copy-json]');
    let activeName = 'entry';

    const syncTabs = function (name) {
        activeName = name;
        tabs.forEach(function (tab) {
            tab.classList.toggle('is-active', tab.getAttribute('data-code-tab') === name);
        });
        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-code-panel') === name);
        });
    };

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            syncTabs(tab.getAttribute('data-code-tab'));
        });
    });

    if (copyButton && navigator.clipboard) {
        copyButton.addEventListener('click', function () {
            const source = root.querySelector('[data-code-content="' + activeName + '"]');
            if (!source) {
                return;
            }
            navigator.clipboard.writeText(source.textContent || '').then(function () {
                const originalText = copyButton.textContent;
                copyButton.textContent = 'Скопировано';
                setTimeout(function () {
                    copyButton.textContent = originalText;
                }, 1200);
            });
        });
    }
})();
</script>