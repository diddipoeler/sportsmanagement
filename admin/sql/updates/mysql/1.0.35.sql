ALTER TABLE  `#__sportsmanagement_user_extra_fields` ADD `views_backend` VARCHAR(75) NOT NULL DEFAULT '' ;
ALTER TABLE  `#__sportsmanagement_user_extra_fields` ADD `fieldtyp` tinyint(1) NOT NULL default '1';
ALTER TABLE  `#__sportsmanagement_user_extra_fields` ADD `views_backend_field` VARCHAR(75) NOT NULL DEFAULT '';
ALTER TABLE  `#__sportsmanagement_user_extra_fields` ADD `select_columns` TEXT NULL ;
ALTER TABLE  `#__sportsmanagement_user_extra_fields` ADD `select_values` TEXT NULL ;
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2014-04-29', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_04_29', '1.0.35');