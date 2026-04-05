<?php

$shell_scheme = include __DIR__ . '/shell_scheme.php';
$slot_widths  = [
    'content_sidebar_left'  => 'nordic-admin-shell-col--quarter',
    'content_body'          => 'nordic-admin-shell-col--half',
    'content_sidebar_right' => 'nordic-admin-shell-col--quarter'
];

?>
<style>
    .nordic-admin-shell-preview {
        margin-bottom: 1.5rem;
        border: 1px solid #d7dee7;
        border-radius: 16px;
        background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(17, 24, 39, 0.06);
    }

    .nordic-admin-shell-preview__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #d7dee7;
        background: rgba(255, 255, 255, 0.72);
    }

    .nordic-admin-shell-preview__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #142033;
    }

    .nordic-admin-shell-preview__hint {
        margin: 0.35rem 0 0;
        color: #526277;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .nordic-admin-shell-preview__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: #142033;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .nordic-admin-shell-preview__body {
        padding: 1.25rem;
    }

    .nordic-admin-shell-row {
        margin-bottom: 0.9rem;
        padding: 1rem;
        border: 1px solid rgba(20, 32, 51, 0.09);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.88);
    }

    .nordic-admin-shell-row:last-child {
        margin-bottom: 0;
    }

    .nordic-admin-shell-row__title {
        margin: 0 0 0.75rem;
        color: #142033;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .nordic-admin-shell-row__cols {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .nordic-admin-shell-col {
        flex: 1 1 100%;
        min-width: 0;
    }

    .nordic-admin-shell-col--quarter {
        flex-basis: 220px;
    }

    .nordic-admin-shell-col--half {
        flex: 2 1 340px;
    }

    .nordic-admin-shell-slot {
        height: 100%;
        padding: 0.95rem;
        border: 1px dashed #98a6b8;
        border-radius: 12px;
        background: #f8fbfd;
    }

    .nordic-admin-shell-slot__title {
        margin: 0 0 0.35rem;
        color: #142033;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .nordic-admin-shell-slot__name {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        background: #dce7f2;
        color: #22405f;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .nordic-admin-shell-slot__note {
        margin: 0.55rem 0 0;
        color: #5d6d80;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    @media (max-width: 991px) {
        .nordic-admin-shell-preview__header {
            flex-direction: column;
        }
    }
</style>
<div class="nordic-admin-shell-preview">
    <div class="nordic-admin-shell-preview__header">
        <div>
            <h3 class="nordic-admin-shell-preview__title">Карта shell-слотов Nordic</h3>
            <p class="nordic-admin-shell-preview__hint">Верхняя схема показывает канонические shell slots шаблона Nordic. Живые drag-and-drop позиции и layout rows редактируются в блоке ниже.</p>
        </div>
        <span class="nordic-admin-shell-preview__badge"><?php echo htmlspecialchars((string) ($shell_scheme['key'] ?? 'nordic_shell_v1'), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <div class="nordic-admin-shell-preview__body">
        <?php foreach (($shell_scheme['layout_rows'] ?? []) as $row) { ?>
            <section class="nordic-admin-shell-row">
                <h4 class="nordic-admin-shell-row__title"><?php echo htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h4>
                <div class="nordic-admin-shell-row__cols">
                    <?php foreach (($row['cols'] ?? []) as $col) {
                        $slot_name  = (string) ($col['name'] ?? '');
                        $slot_class = $slot_widths[$slot_name] ?? '';
                    ?>
                        <div class="nordic-admin-shell-col <?php echo htmlspecialchars($slot_class, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="nordic-admin-shell-slot">
                                <div class="nordic-admin-shell-slot__title"><?php echo htmlspecialchars((string) ($col['title'] ?? $slot_name), ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="nordic-admin-shell-slot__name"><?php echo htmlspecialchars($slot_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                <p class="nordic-admin-shell-slot__note">Shell slot зарезервирован для Nordic и участвует в runtime bind map. Для изменения состава виджетов используйте editable positions ниже.</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    </div>
</div>
