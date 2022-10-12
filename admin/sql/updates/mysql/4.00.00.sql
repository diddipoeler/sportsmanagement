ALTER TABLE  `#__sportsmanagement_project_team` ADD `champion` TINYINT(1) NOT NULL DEFAULT '0' ;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2022-06-19', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2022-06-19', '4.00.00');
