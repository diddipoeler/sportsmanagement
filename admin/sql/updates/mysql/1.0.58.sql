ALTER TABLE `#__sportsmanagement_division` CHANGE `rankingparams`  `rankingparams` TEXT NULL DEFAULT NULL ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2016-12-31', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2016_12_31', '1.0.58');