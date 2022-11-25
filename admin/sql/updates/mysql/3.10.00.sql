-- -----------------------------------------------------
-- Table `#__sportsmanagement_project_team_division`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_project_team_division` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `team_id` INT(11) NOT NULL DEFAULT '0' ,
  `division_id` INT(11) NULL DEFAULT NULL ,
  `start_points` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `points_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `neg_points_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `matches_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `won_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `draws_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `lost_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `homegoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `guestgoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `diffgoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `is_in_score` TINYINT(1) NOT NULL DEFAULT '1' ,
  `use_finally` TINYINT(1) NOT NULL DEFAULT '0' ,
  `admin` INT(11) NOT NULL DEFAULT '0' ,
  `info` VARCHAR(255) NOT NULL DEFAULT '' ,
  `picture` VARCHAR(128) NULL DEFAULT NULL ,
  `notes` TEXT NULL DEFAULT NULL ,
  `standard_playground` INT(11) NULL DEFAULT NULL ,
  `reason` VARCHAR(150) NULL DEFAULT NULL ,
  `extended` TEXT NULL ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `trikot_home` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `trikot_away` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `extendeduser` TEXT NULL ,
  `penalty_points` INT(11) NOT NULL DEFAULT '0' ,
  `import` TINYINT(1) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `finaltablerank` tinyint(1) NOT NULL DEFAULT '0',
  `picturenotes` TEXT NULL DEFAULT NULL ,
  `cache_points_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_neg_points_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_matches_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_won_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_draws_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_lost_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_homegoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_guestgoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `cache_diffgoals_finally` SMALLINT(6) NOT NULL DEFAULT '0' ,
  
  PRIMARY KEY (`id`) ,
  KEY `project_id` (`project_id`),
  KEY `team_id` (`team_id`),
  KEY `division_id` (`division_id`),
  KEY `import` (`import`),
  KEY `standard_playground` (`standard_playground`),
  UNIQUE INDEX `combi` (`project_id` ASC, `team_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;




INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2022-01-09', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2022-01-09', '3.10.00');
