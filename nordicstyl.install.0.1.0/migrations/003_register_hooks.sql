-- NordicStyl: register required hooks in InstantCMS events table

INSERT IGNORE INTO `{#}events` (`event`, `listener`, `ordering`, `is_enabled`) VALUES
('before_print_head', 'nordicstyl', 0, 1);
