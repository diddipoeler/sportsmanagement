ALTER TABLE `#__sportsmanagement_match` ADD `next_match_id` INT(11) NOT NULL DEFAULT '0';
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2018-03-30', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2018_03_30', '1.0.62');
