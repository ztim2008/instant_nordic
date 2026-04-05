<?php

$shared_template = cmsConfig::get('root_path') . 'templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php';
if (is_readable($shared_template)) {
	include $shared_template;
	return;
}

$this->setPageTitle('Нордик: Дизайн сайта');
$this->setMenuItems('backend', $menu);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Дизайн сайта');

?>
<div class="padded">
	<h3>Дизайн сайта</h3>
	<p>Глобальные пресеты Нордик для shell, runtime и стартового состояния Page Builder.</p>
	<?php $this->renderForm($form, $theme, ['action' => '', 'method' => 'post'], $errors); ?>
</div>