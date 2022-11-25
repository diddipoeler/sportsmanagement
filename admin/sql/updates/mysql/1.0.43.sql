-- ALTER TABLE  `#__sportsmanagement_league` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_sports_type` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_season` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_club` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';

-- ALTER TABLE  `#__sportsmanagement_associations` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_federations` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';

-- ALTER TABLE  `#__sportsmanagement_countries` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_dfbkey` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';

-- ALTER TABLE  `#__sportsmanagement_match_commentary` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_match_event` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';

-- ALTER TABLE  `#__sportsmanagement_match_player` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_match_statistic` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_match_staff_statistic` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_match_referee` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_match_staff` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_playground` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_position_eventtype` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_position_statistic` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_project_position` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_rosterposition` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_project_team` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_season_team_id` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_season_person_id` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_team` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_team_trainingdata` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_treeto_match` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_agegroup` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_prediction_admin` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_prediction_groups` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_prediction_member` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_prediction_result` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_position_ringen` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_confidential` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_countries_gazetteer` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__sportsmanagement_countries_plz` ADD `published` TINYINT(1) NOT NULL DEFAULT '1';

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2014-07-28', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_09_24', '1.0.43');
