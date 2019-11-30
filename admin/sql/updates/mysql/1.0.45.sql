ALTER TABLE  `#__sportsmanagement_team` ADD `standard_playground` INT(11) NULL DEFAULT NULL ;
ALTER TABLE  `#__sportsmanagement_season_team_id` ADD `standard_playground` INT(11) NULL DEFAULT NULL ;

ALTER TABLE  `#__sportsmanagement_project_position` ADD `checked_out` INT(11) NOT NULL DEFAULT '0' ;
ALTER TABLE  `#__sportsmanagement_project_position` ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ;
ALTER TABLE  `#__sportsmanagement_project_position` ADD `jl_update` TINYINT(1) NOT NULL DEFAULT '0' ;

ALTER TABLE  `#__sportsmanagement_person` ADD `jl_update` TINYINT(1) NOT NULL DEFAULT '0' ;
ALTER TABLE  `#__sportsmanagement_season_team_person_id` ADD `jl_update` TINYINT(1) NOT NULL DEFAULT '0' ;

UPDATE  `#__sportsmanagement_club` SET  dissolved = '0000-00-00' WHERE dissolved IS NULL;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2014-10-06', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_10_06', '1.0.45');