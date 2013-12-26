ALTER TABLE  `#__sportsmanagement_person` ADD  `unique_id` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE  `#__sportsmanagement_playground` ADD  `unique_id` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE  `#__sportsmanagement_team` ADD  `unique_id` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE  `#__sportsmanagement_club` CHANGE  `unique_id`  `unique_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;