ALTER TABLE  `#__sportsmanagement_season_team_person_id` ADD `tt_startpoints` INT( 11 ) NOT NULL DEFAULT  '0';


ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_startpoints_teamplayer1_id` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_startpoints_teamplayer2_id` INT( 11 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer1_id_normal_won` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer1_id_normal_lost` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer1_id_anormal_won` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer1_id_anormal_lost` INT( 11 ) NOT NULL DEFAULT  '0';
  
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer2_id_normal_won` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer2_id_normal_lost` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer2_id_anormal_won` INT( 11 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `#__sportsmanagement_match_single` ADD `tt_teamplayer2_id_anormal_lost` INT( 11 ) NOT NULL DEFAULT  '0';


  
  
  