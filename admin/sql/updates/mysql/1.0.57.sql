ALTER TABLE `#__sportsmanagement_club` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2016-05-01', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2016_05_01', '1.0.57');