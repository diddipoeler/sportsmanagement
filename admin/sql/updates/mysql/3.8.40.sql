ALTER TABLE `#__sportsmanagement_season_team_id` ADD `teamname` VARCHAR( 75 ) NULL DEFAULT NULL ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-10-06', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-02-15', '3.8.40');
