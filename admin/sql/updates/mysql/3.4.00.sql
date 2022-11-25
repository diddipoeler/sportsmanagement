-- create additional table to enable individual points per tippround and different tipptimes 
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_tippround` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prediction_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `round_id` int(11) NOT NULL DEFAULT '0',
  `rien_ne_va_plus` enum('FIRSTMATCH_OF_TIPPGAME','FIRSTMATCH_OF_TIPPROUND','BEGIN_OF_MATCH') NOT NULL default 'BEGIN_OF_MATCH',
  `points_tipp` SMALLINT(6) NOT NULL DEFAULT '1' ,
  `points_correct_result` SMALLINT(6) NOT NULL DEFAULT '7' ,
  `points_correct_diff` SMALLINT(6) NOT NULL DEFAULT '5' ,
  `points_correct_draw` SMALLINT(6) NOT NULL DEFAULT '4' ,
  `points_correct_tendence` SMALLINT(6) NOT NULL DEFAULT '3' ,
  `checked_out` int(11) DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `project_id` (`project_id`),
  KEY `round_id` (`round_id`),
  UNIQUE INDEX `tippround` (`prediction_id` ASC, `project_id` ASC, `round_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

ALTER TABLE `#__sportsmanagement_prediction_project` ADD `final4` TINYINT(4) NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `points_tipp_final4` SMALLINT(6) NOT NULL DEFAULT '5';
ALTER TABLE `#__sportsmanagement_prediction_project` ADD `league_final4` VARCHAR(128) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_prediction_member` ADD `final4_tipp` VARCHAR(64) NOT NULL DEFAULT '';

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-04-18', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2020-04-20', '3.4.00');
