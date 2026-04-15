<?php
$templateWidgetType = (string)($widget->options['type'] ?? 'template');
$templateWidgetKey = isset($widget->id) ? ('widget-' . (int)$widget->id) : $templateWidgetType;
$templateWidgetKey = preg_replace('/[^a-z0-9\-_]+/i', '-', strtolower($templateWidgetKey)) ?: 'template';
?>
<?php if($widget->options['type'] === 'body'){ ?>
    <?php if($this->hasBlock('before_body')){ ?>
        <div class="icms-body-toolbox" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-before-body" data-nordic-role="system.before-body" data-nordic-label="Блок перед контентом">
            <?php $this->block('before_body'); ?>
        </div>
    <?php } ?>
    <div data-nordic-id="template-<?php echo $templateWidgetKey; ?>-body" data-nordic-role="system.body" data-nordic-label="Основное содержимое шаблона">
        <?php $this->body(); ?>
    </div>
    <div data-nordic-id="template-<?php echo $templateWidgetKey; ?>-after-body" data-nordic-role="system.after-body" data-nordic-label="Блок после контента">
        <?php $this->block('after_body'); ?>
    </div>
<?php } elseif($widget->options['type'] === 'breadcrumbs') { ?>
    <div data-nordic-id="template-<?php echo $templateWidgetKey; ?>-breadcrumbs" data-nordic-role="system.breadcrumbs" data-nordic-label="Хлебные крошки">
        <?php $this->breadcrumbs($widget->options['breadcrumbs']); ?>
    </div>
<?php } elseif($widget->options['type'] === 'smessages') { ?>
    <?php if ($messages){ foreach($messages as $message){ ?>
    <div class="alert alert-<?php echo str_replace(['error'], ['danger'], $message['class']); ?> alert-dismissible fade show" role="alert" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-message-<?php echo html(str_replace(['error'], ['danger'], $message['class']), false); ?>" data-nordic-role="system.message" data-nordic-label="Системное сообщение">
        <?php echo $message['text']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-message-close" data-nordic-role="system.message.close" data-nordic-label="Закрыть сообщение">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php } } ?>
<?php } elseif($widget->options['type'] === 'copyright') { ?>
    <?php $ownerUrl = $this->options['owner_url'] ?? ''; ?>
    <?php $ownerName = $this->options['owner_name'] ?? ''; ?>
    <?php $ownerYear = $this->options['owner_year'] ?? ''; ?>
    <div class="d-flex align-items-center text-muted icms-links-inherit-color" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-copyright" data-nordic-role="footer.copyright" data-nordic-label="Копирайт">
        <a href="<?php echo $ownerUrl ? $ownerUrl : href_to_home(); ?>" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-owner-link" data-nordic-role="footer.owner-link" data-nordic-label="Ссылка владельца сайта">
            <?php html($ownerName ? $ownerName : cmsConfig::get('sitename')); ?>
        </a>
        <span class="mx-2" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-owner-year" data-nordic-role="footer.owner-year" data-nordic-label="Год копирайта">
            &copy; <?php echo $ownerYear ? $ownerYear : date('Y'); ?>
        </span>
        <span class="d-none d-sm-block mr-2" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-powered-by" data-nordic-role="footer.powered-by" data-nordic-label="Подпись платформы">
            <?php echo LANG_POWERED_BY_INSTANTCMS; ?>
        </span>
        <?php if ($config->debug && cmsUser::isAdmin()){ ?>
            <a href="#debug_block" data-style="xl" title="<?php echo LANG_DEBUG; ?>" class="ajax-modal" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-debug-link" data-nordic-role="system.debug-link" data-nordic-label="Ссылка на debug">
                <?php echo LANG_DEBUG; ?>
            </a>
        <?php } ?>
    </div>
<?php } elseif($widget->options['type'] === 'site_closed') { ?>
    <div id="site_off_notice" class="py-2 icms-links-inherit-color" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-site-closed" data-nordic-role="system.site-closed" data-nordic-label="Уведомление о закрытии сайта">
        <?php echo html_svg_icon('solid', 'exclamation-triangle'); ?>
        <?php if (cmsUser::isAdmin()){ ?>
            <?php printf(ERR_SITE_OFFLINE_FULL, href_to('admin', 'settings', 'siteon')); ?>
        <?php } else { ?>
            <?php echo ERR_SITE_OFFLINE; ?>
        <?php } ?>
    </div>
<?php } elseif($widget->options['type'] === 'logo') { ?>
    <?php if($core->uri) { ?>
        <a class="navbar-brand mr-3 flex-shrink-0" href="<?php echo href_to_home(); ?>" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-logo" data-nordic-role="header.logo" data-nordic-label="Логотип сайта">
            <img src="<?php echo $logos['small_logo']; ?>" class="d-sm-none" alt="<?php html($config->sitename); ?>">
            <img src="<?php echo $logos['logo']; ?>" class="d-none d-sm-block" alt="<?php html($config->sitename); ?>">
        </a>
    <?php } else { ?>
        <span class="navbar-brand mr-3 flex-shrink-0" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-logo" data-nordic-role="header.logo" data-nordic-label="Логотип сайта">
            <img src="<?php echo $logos['small_logo']; ?>" class="d-sm-none" alt="<?php html($config->sitename); ?>">
            <img src="<?php echo $logos['logo']; ?>" class="d-none d-sm-block" alt="<?php html($config->sitename); ?>">
        </span>
    <?php } ?>
<?php } elseif($widget->options['type'] === 'lang_select') { ?>
        <ul class="nav nav-lang-select" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-lang-select" data-nordic-role="header.lang-select" data-nordic-label="Переключатель языка">
            <li class="nav-item dropdown" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-lang-dropdown" data-nordic-role="header.lang-select.dropdown" data-nordic-label="Выпадающий список языков">
                <a class="nav-link text-warning font-weight-bold dropdown-toggle" data-toggle="dropdown" href="#" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-lang-toggle" data-nordic-role="header.lang-select.toggle" data-nordic-label="Текущий язык">
                    <?php echo strtoupper($current_lang); ?>
                </a>
                <div class="dropdown-menu" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-lang-menu" data-nordic-role="header.lang-select.menu" data-nordic-label="Меню языков">
                    <?php foreach ($langs as $lang) { ?>
                        <a class="dropdown-item<?php if($lang === $current_lang){ ?> active<?php } ?>" href="<?php html($config->root . ($config->language === $lang ? '' : $lang.'/').$core->uri_before_remap.($core->uri_query ? '?'.http_build_query($core->uri_query) : '')); ?>" data-nordic-id="template-<?php echo $templateWidgetKey; ?>-lang-<?php echo html(strtolower($lang), false); ?>" data-nordic-role="header.lang-select.option" data-nordic-label="Язык <?php echo html(strtoupper($lang), false); ?>">
                            <?php echo strtoupper($lang); ?>
                        </a>
                    <?php } ?>
                </div>
            </li>
        </ul>
<?php } ?>
