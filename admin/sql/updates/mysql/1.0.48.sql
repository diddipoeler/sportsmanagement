-- ALTER TABLE  `#__sportsmanagement_person` ADD  `hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_person` ADD  `modified_hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_playground` ADD  `hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_playground` ADD  `modified_hits` INT( 11 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__sportsmanagement_club` ADD  `hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_club` ADD  `modified_hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_team` ADD  `hits` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_team` ADD  `modified_hits` INT( 11 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__sportsmanagement_season_team_person_id` ADD  `position_id` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_season_person_id` ADD  `position_id` INT( 11 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__sportsmanagement_match_player` ADD `captain` TINYINT(1) NOT NULL DEFAULT '0' ;

-- ALTER TABLE  `#__sportsmanagement_match` ADD  `overtime` INT( 11 ) NOT NULL DEFAULT  '0';

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_person_project_position`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_person_project_position` (
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_position_id` INT(11) NOT NULL DEFAULT '0' ,
  `modified_by` INT NULL ,
  `modified` date NOT NULL DEFAULT '0000-00-00',
  `persontype` TINYINT(1) NOT NULL DEFAULT '0' ,
  UNIQUE KEY `combi` (`person_id`,`project_id`,`project_position_id`,`persontype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2015-02-28', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2015_02_28', '1.0.48');
