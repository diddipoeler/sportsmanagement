ALTER TABLE `#__sportsmanagement_league` ADD `champions_complete` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_club` ADD `complete_address` VARCHAR(200) NOT NULL DEFAULT '';

ALTER TABLE `#__sportsmanagement_project` ADD `cr_project` varchar(255) DEFAULT NULL;

ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_points_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_neg_points_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_matches_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_won_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_draws_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_lost_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_homegoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_guestgoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_project_team` ADD `cache_diffgoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ;




INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2021-11-11', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-11-11', '3.9.00');
