<!DOCTYPE html>
<html>
	<head>
		<title><?php $this->title(); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php $this->addMainCSS("inthemer/css/{$layout['id']}"); ?>
        <?php if ($config->debug && cmsUser::isAdmin()){ ?>
            <?php $this->addTplCSSName('debug'); ?>
        <?php } ?>
		<?php $this->addMainJS("templates/default/js/jquery.js"); ?>
		<?php $this->addMainJS("templates/default/js/jquery-modal.js"); ?>
		<?php $this->addMainJS("templates/default/js/modal.js"); ?>
		<?php $this->addMainJS("templates/default/js/messages.js"); ?>
        <?php if (cmsUser::isAdmin()) { ?>
            <?php $this->addTplJSName('widgets'); ?>
        <?php } ?>
		<?php $this->addMainJS("templates/{$this->name}/builder/js/vendor/jquery/jquery.js"); ?>
		<?php $this->addMainJS("templates/{$this->name}/js/core.js"); ?>
		<?php $this->addMainJS("templates/{$this->name}/js/inthemer.js"); ?>
		<?php $this->head(); ?>
		<meta name="csrf-token" content="<?php echo cmsForm::getCSRFToken(); ?>" />
		<?php echo $themeHead; ?>
	</head>
	<body id="body">
		<div>
			<?php echo $themeHTML; ?>
		</div>
		<?php if (method_exists($this, 'bottom')) { $this->bottom(); } ?>
	</body>
</html>
