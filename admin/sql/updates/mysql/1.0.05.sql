-- ALTER TABLE  `#__sportsmanagement_person` ADD  `season_ids` TEXT NULL ;
-- ALTER TABLE  `#__sportsmanagement_team` ADD  `season_ids` TEXT NULL ;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_season_team_id`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_season_team_id` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `team_id` INT(11) NOT NULL DEFAULT '0' ,
  `season_id` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `combi` (`team_id`,`season_id`) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_season_person_id`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_season_person_id` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  `season_id` INT(11) NOT NULL DEFAULT '0' ,
  `team_id` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `combi` (`person_id`,`season_id`,`team_id`) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

