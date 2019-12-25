CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_result_round` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prediction_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `round_id` int(11) NOT NULL DEFAULT '0',
  `points` int(11) DEFAULT NULL,
  `top` tinyint(1) DEFAULT NULL,
  `diff` tinyint(1) DEFAULT NULL,
  `tend` tinyint(1) DEFAULT NULL,
  `checked_out` int(11) DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `yellow_cards` int(11) DEFAULT NULL,
  `yellow_red_cards` int(11) DEFAULT NULL,
  `red_cards` int(11) DEFAULT NULL,
  `penalties` int(11) DEFAULT NULL,
  `goals` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`),
  KEY `round_id` (`round_id`),
  UNIQUE INDEX `result` (`prediction_id` ASC, `user_id` ASC, `project_id` ASC, `round_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

ALTER TABLE `#__sportsmanagement_prediction_result` ADD `yellow_red_cards` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `points_correct_yellow_cards` SMALLINT(6) NOT NULL DEFAULT '6' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `points_correct_yellow_red_cards` SMALLINT(6) NOT NULL DEFAULT '6' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `points_correct_red_cards` SMALLINT(6) NOT NULL DEFAULT '6' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `points_correct_penalties` SMALLINT(6) NOT NULL DEFAULT '6' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `points_correct_goals` SMALLINT(6) NOT NULL DEFAULT '6' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `use_cards` TINYINT(4) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `use_penalties` TINYINT(4) NOT NULL DEFAULT '0' ;
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `use_goals` TINYINT(4) NOT NULL DEFAULT '0' ;
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-12-25', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-12-25', '3.3.00');
