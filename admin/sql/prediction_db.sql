-- -----------------------------------------------------
-- Table `#__joomleague_prediction_admin`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_admin` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `prediction_id` INT(11) NOT NULL DEFAULT '0' ,
  `user_id` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `user_id` (`user_id`),
  UNIQUE INDEX `pred_user` (`prediction_id` ASC, `user_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__joomleague_prediction_game`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_game` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `auto_approve` TINYINT(4) NOT NULL DEFAULT '1' ,
  `only_favteams` TINYINT(4) NOT NULL DEFAULT '0' ,
  `admin_tipp` TINYINT(4) NOT NULL DEFAULT '0' ,
  `master_template` INT(11) NOT NULL DEFAULT '0' ,
  `sub_template_id` INT(11) NOT NULL DEFAULT '0' ,
  `extension` VARCHAR(80) NOT NULL DEFAULT 'default' ,
  `notify_to` TEXT NOT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`) ,
  KEY `sub_template_id` (`sub_template_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__joomleague_prediction_groups`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `extendeduser` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__joomleague_prediction_member`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_member` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `prediction_id` INT(11) NOT NULL DEFAULT '0' ,
  `user_id` INT(11) NOT NULL DEFAULT '0' ,
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `approved` TINYINT(4) NOT NULL DEFAULT '0' ,
  `show_profile` TINYINT(4) NOT NULL DEFAULT '1' ,
  `fav_team` VARCHAR(64) NOT NULL DEFAULT '' ,
  `champ_tipp` VARCHAR(64) NOT NULL DEFAULT '' ,
  `slogan` VARCHAR(255) NULL DEFAULT NULL ,
  `aliasName` VARCHAR(255) NULL DEFAULT NULL ,
  `reminder` TINYINT(4) NOT NULL DEFAULT '0' ,
  `receipt` TINYINT(4) NOT NULL DEFAULT '0' ,
  `admintipp` TINYINT(4) NOT NULL DEFAULT '0' ,
  `picture` VARCHAR(128) NULL DEFAULT NULL ,
  `last_tipp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `group_id` INT(11) NULL DEFAULT '0' ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `user_id` (`user_id`),
  UNIQUE INDEX `member` (`prediction_id` ASC, `user_id` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__joomleague_prediction_project`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_project` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `prediction_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `mode` TINYINT(4) NOT NULL DEFAULT '0' ,
  `overview` TINYINT(3) NOT NULL DEFAULT '0' ,
  `points_tipp` SMALLINT(6) NOT NULL DEFAULT '1' ,
  `points_tipp_joker` SMALLINT(6) NOT NULL DEFAULT '1' ,
  `points_tipp_champ` SMALLINT(6) NOT NULL DEFAULT '10' ,
  `points_correct_result` SMALLINT(6) NOT NULL DEFAULT '7' ,
  `points_correct_result_joker` SMALLINT(6) NOT NULL DEFAULT '7' ,
  `points_correct_diff` SMALLINT(6) NOT NULL DEFAULT '5' ,
  `points_correct_diff_joker` SMALLINT(6) NOT NULL DEFAULT '5' ,
  `points_correct_draw` SMALLINT(6) NOT NULL DEFAULT '4' ,
  `points_correct_draw_joker` SMALLINT(6) NOT NULL DEFAULT '4' ,
  `points_correct_tendence` SMALLINT(6) NOT NULL DEFAULT '3' ,
  `points_correct_tendence_joker` SMALLINT(6) NOT NULL DEFAULT '3' ,
  `joker` TINYINT(4) NOT NULL DEFAULT '0' ,
  `joker_limit` TINYINT(4) NOT NULL DEFAULT '0' ,
  `champ` TINYINT(4) NOT NULL DEFAULT '0' ,
  `picture` VARCHAR(128) NULL DEFAULT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `league_champ` INT(11) NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `project_id` (`project_id`),
  UNIQUE INDEX `pred_proj` (`prediction_id` ASC, `project_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__joomleague_prediction_result`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prediction_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `match_id` int(11) NOT NULL DEFAULT '0',
  `tipp` smallint(6) DEFAULT NULL,
  `tipp_home` smallint(6) DEFAULT NULL,
  `tipp_away` smallint(6) DEFAULT NULL,
  `joker` tinyint(4) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `top` tinyint(1) DEFAULT NULL,
  `diff` tinyint(1) DEFAULT NULL,
  `tend` tinyint(1) DEFAULT NULL,
  `checked_out` int(11) DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`),
  KEY `match_id` (`match_id`),
  UNIQUE INDEX `result` (`prediction_id` ASC, `user_id` ASC, `project_id` ASC, `match_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__joomleague_prediction_template`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__joomleague_prediction_template` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `prediction_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `title` VARCHAR(255) NOT NULL DEFAULT '' ,
  `template` VARCHAR(64) NOT NULL DEFAULT '' ,
  `params` TEXT NOT NULL ,
  `published` INT(1) UNSIGNED NOT NULL DEFAULT '1' ,
  `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`),
  KEY `prediction_id` (`prediction_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;