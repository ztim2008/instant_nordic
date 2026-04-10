-- NordicStyl: backend permissions for InstantCMS admin controller
-- These rules are required because cmsBackend::before() checks:
-- cmsUser::isAllowed('admin', 'manage_<controller>_<action>')

INSERT IGNORE INTO `{#}perms_rules` (`controller`, `name`, `type`, `options`, `show_for_guest_group`) VALUES
('admin', 'manage_nordicstyl_index',  'flag', NULL, 0),
('admin', 'manage_nordicstyl_rules',  'flag', NULL, 0),
('admin', 'manage_nordicstyl_picker', 'flag', NULL, 0);
