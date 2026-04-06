<?php

// Bridge: переиспользуем форму из landingbuilder без дублирования логики
require_once cmsConfig::get('root_path') . 'system/controllers/landingbuilder/backend/forms/form_shell_variant.php';

class formNordicbuilderShellVariant extends formLandingbuilderShellVariant {
	// Наследует всю структуру формы из landingbuilder
}
