ALTER TABLE  `#__sportsmanagement_match` CHANGE  `approved_date`  `approved_date` DATE NOT NULL DEFAULT  '0000-00-00';
ALTER TABLE  `#__sportsmanagement_match` ADD  `match_timestamp` INT( 11 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `#__sportsmanagement_season_team_id` ADD  `kaderlink` VARCHAR( 250 ) NULL DEFAULT NULL ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2015-06-10', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2015_06_10', '1.0.50');