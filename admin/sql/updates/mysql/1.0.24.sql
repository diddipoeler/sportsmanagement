-- -- ALTER TABLE  `#__sportsmanagement_project` ADD  `use_tie_break` TINYINT(1)  NULL DEFAULT NULL ;
-- -- ALTER TABLE  `#__sportsmanagement_project` ADD  `tennis_single_matches` SMALLINT(6) NOT NULL DEFAULT '0';
-- -- ALTER TABLE  `#__sportsmanagement_project` ADD  `tennis_double_matches` SMALLINT(6) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__sportsmanagement_match_single` ADD  `match_type` ENUM(  'SINGLE',  'DOUBLE' ) NOT NULL DEFAULT  'SINGLE';
-- ALTER TABLE  `#__sportsmanagement_match_single` ADD `double_team1_player1` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__sportsmanagement_match_single` ADD `double_team1_player2` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__sportsmanagement_match_single` ADD `double_team2_player1` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__sportsmanagement_match_single` ADD `double_team2_player2` int(11) NOT NULL DEFAULT '0';
