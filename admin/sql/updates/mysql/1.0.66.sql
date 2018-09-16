ALTER TABLE  `#__sportsmanagement_agegroup` CHANGE `notes` `notes` TEXT NULL DEFAULT NULL;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2018-09-16', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2018_09_16', '1.0.66');
