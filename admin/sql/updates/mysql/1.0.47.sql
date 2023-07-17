-- ALTER TABLE  `#__sportsmanagement_project` ADD  `hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_project` ADD  `modified_hits` INT( 11 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__sportsmanagement_person` ADD `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__sportsmanagement_league` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_division` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- ALTER TABLE  `#__sportsmanagement_position` ADD `picture` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_21.png';
-- ALTER TABLE  `#__sportsmanagement_position` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- ALTER TABLE  `#__sportsmanagement_project` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_project_referee` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_project_team` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_round` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- ALTER TABLE  `#__sportsmanagement_season_team_id` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_season_team_id` ADD `cr_logo_big` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_season_person_id` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_season_team_person_id` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- ALTER TABLE  `#__sportsmanagement_team` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_agegroup` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_rquote` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2015-01-17', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2015_01_17', '1.0.47');
