ALTER TABLE  `#__sportsmanagement_project` ADD `double_events` TINYINT(1) NOT NULL DEFAULT '0' ;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2022-04-10', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2022-04-10', '3.16.00');
