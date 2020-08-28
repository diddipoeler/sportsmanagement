-- -----------------------------------------------------
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_associations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_associations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(3) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `short_name` varchar(75) DEFAULT NULL,
  `middle_name` varchar(75) DEFAULT NULL,
  `website` varchar(250) DEFAULT NULL,
  `assocflag` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_flags.png' ,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' ,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `address` VARCHAR(100) NOT NULL DEFAULT '' ,
  `zipcode` VARCHAR(10) NOT NULL DEFAULT '' ,
  `location` VARCHAR(50) NOT NULL DEFAULT '' ,
  `state` VARCHAR(50) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`),
  KEY `country` (`country`),
  KEY `parent_id` (`parent_id`),
  UNIQUE KEY `name` (`name`,`country`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_federations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_federations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `sports_type_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `short_name` varchar(75) DEFAULT NULL,
  `middle_name` varchar(75) DEFAULT NULL,
  `website` varchar(250) DEFAULT NULL,
  `assocflag` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_flags.png' ,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' ,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `country` varchar(3) DEFAULT NULL, 
  `address` VARCHAR(100) NOT NULL DEFAULT '' ,
  `zipcode` VARCHAR(10) NOT NULL DEFAULT '' ,
  `location` VARCHAR(50) NOT NULL DEFAULT '' ,
  `state` VARCHAR(50) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_club`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_club` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(100) NOT NULL DEFAULT '' ,
  `admin` INT(11) NULL DEFAULT NULL ,
  `address` VARCHAR(100) NOT NULL DEFAULT '' ,
  `zipcode` VARCHAR(10) NOT NULL DEFAULT '' ,
  `location` VARCHAR(50) NOT NULL DEFAULT '' ,
  `state` VARCHAR(50) NOT NULL DEFAULT '' ,
  `country` VARCHAR(3) NULL DEFAULT NULL,
  `founded` DATE NOT NULL DEFAULT '0000-00-00' ,
  `phone` VARCHAR(20) NOT NULL DEFAULT '' ,
  `fax` VARCHAR(20) NOT NULL DEFAULT '' ,
  `email` VARCHAR(255) NOT NULL DEFAULT '' ,
  `website` VARCHAR(250) NOT NULL DEFAULT '' ,
  `president` VARCHAR(50) NOT NULL DEFAULT '' ,
  `manager` VARCHAR(50) NOT NULL DEFAULT '' ,
  `logo_big` VARCHAR(255) NOT NULL DEFAULT '' ,
  `logo_middle` VARCHAR(255) NOT NULL DEFAULT '' ,
  `logo_small` VARCHAR(255) NOT NULL DEFAULT '' ,
  `standard_playground` INT(11) NULL DEFAULT NULL ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `dissolved` DATE NOT NULL DEFAULT '0000-00-00' ,
  `dissolved_year` VARCHAR(4) NULL DEFAULT NULL,
  `founded_year` VARCHAR(4) NULL DEFAULT NULL,
  `unique_id` VARCHAR(20) NULL DEFAULT NULL ,
  `new_club_id` INT(11) NOT NULL DEFAULT '0' ,
  `enable_sb` TINYINT(4) NOT NULL DEFAULT '0' ,
  `sb_catid` INT(11) NOT NULL DEFAULT '0' ,
  `trikot_home` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `trikot_away` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `latitude` DECIMAL(12,8) NULL DEFAULT NULL,
  `longitude` DECIMAL(12,8) NULL DEFAULT NULL,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `associations` INT(11) NOT NULL DEFAULT '0',
  `merge_teams` VARCHAR(255) NOT NULL DEFAULT '' ,
  `extendeduser` TEXT NULL ,
  `twitter` VARCHAR(250) NOT NULL DEFAULT '' ,
  `facebook` VARCHAR(250) NOT NULL DEFAULT '' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `cr_logo_big` varchar(255) DEFAULT NULL,
  `cr_logo_middle` varchar(255) DEFAULT NULL,
  `cr_logo_small` varchar(255) DEFAULT NULL,
  `hits` INT(11) NOT NULL DEFAULT '0' ,
  `modified_hits` INT(11) NOT NULL DEFAULT '0' ,
  `import_id` INT(11) NOT NULL DEFAULT '0' ,
  `founded_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `dissolved_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `use_jl` tinyint(1) NOT NULL DEFAULT '0',
  `use_jsm` tinyint(1) NOT NULL DEFAULT '0',
  `country_geocode` VARCHAR(3) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `standard_playground` (`standard_playground`),
  KEY `country` (`country`),
  KEY `unique_id` (`unique_id`),
  KEY `new_club_id` (`new_club_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_club_names`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_club_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `country` varchar(3) DEFAULT NULL,
  `name_long` varchar(255) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `namecountry` (`name`,`country`),
  KEY `country` (`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_countries`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL DEFAULT '',
  `alpha2` varchar(2) DEFAULT NULL,
  `alpha3` varchar(3) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `itu` varchar(3) DEFAULT NULL,
  `fips` varchar(2) DEFAULT NULL,
  `ioc` varchar(3) DEFAULT NULL,
  `picture` varchar(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_flags.png',
  `fifa` varchar(3) DEFAULT NULL,
  `ds` varchar(3) DEFAULT NULL,
  `wmo` varchar(3) DEFAULT NULL,
  `federation` int(11) NOT NULL DEFAULT '0',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`alpha3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_dfbkey`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_dfbkey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schluessel` tinyint(3) unsigned DEFAULT '0',
  `spieltag` tinyint(3) unsigned DEFAULT '0',
  `paarung` varchar(5) DEFAULT NULL,
  `spielnummer` varchar(3) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `country` varchar(3) DEFAULT NULL,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_division`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_division` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `shortname` VARCHAR(10) NULL ,
  `notes` TEXT NULL DEFAULT NULL ,
  `parent_id` INT(11) NULL DEFAULT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `rankingparams` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
 KEY `project_id` (`project_id`),
 KEY `parent_id` (`parent_id`)
 )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_error_log`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_error_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT '0000-00-00',
  `time` time DEFAULT '00:00:00',
  `class` text,
  `file` text,
  `text` text,
  `function` text,
  `line` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_eventtype`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_eventtype` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `icon` VARCHAR(128) NOT NULL DEFAULT '' ,
  `parent` INT(11) NOT NULL DEFAULT '0' ,
  `splitt` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' ,
  `direction` CHAR(4) NOT NULL DEFAULT 'DESC' ,
  `double` TINYINT(3) NOT NULL DEFAULT '0' ,
  `sports_type_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `directionspoint` CHAR(4) NOT NULL DEFAULT 'DESC' ,
  `directionscounter` CHAR(4) NOT NULL DEFAULT 'DESC' ,
  `directionspointpos` tinyint(1) NOT NULL DEFAULT '1',
  `directionscounterpos` tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`) ,
  KEY `sports_type_id` (`sports_type_id`),
  UNIQUE KEY `name` (`name`,`parent`,`sports_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_league`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_league` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `short_name` VARCHAR(15) NOT NULL DEFAULT '' ,
  `middle_name` VARCHAR(25) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `country` VARCHAR(3) NULL DEFAULT NULL,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `associations` INT(11) NOT NULL DEFAULT '0',
  `extendeduser` TEXT NULL ,
  `sports_type_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `federation` int(11) NOT NULL DEFAULT '0',
  `website` VARCHAR(250) NOT NULL DEFAULT '' ,
  `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `published_act_season` TINYINT(1) NOT NULL DEFAULT '0' ,
  `league_level` INT( 11 ) NOT NULL DEFAULT  '0',
  `league_id_up` INT( 11 ) NOT NULL DEFAULT  '0',
  `league_id_down` INT( 11 ) NOT NULL DEFAULT  '0',
  `founded` DATE NOT NULL DEFAULT '0000-00-00',
`founded_year` VARCHAR(4) NULL DEFAULT NULL,
`dissolved` DATE NOT NULL DEFAULT '0000-00-00',
`dissolved_year` VARCHAR(4) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `country` (`country`),
  KEY `sports_type_id` (`sports_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_match`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_match` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `round_id` INT(11) NOT NULL DEFAULT '0' ,
  `match_number` VARCHAR(200) NULL DEFAULT NULL ,
  `projectteam1_id` INT(11) NOT NULL DEFAULT '0' ,
  `projectteam2_id` INT(11) NOT NULL DEFAULT '0' ,
  `playground_id` INT(11) NULL DEFAULT NULL ,
  `match_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `time_present` TIME NULL DEFAULT NULL ,
  `team1_result` FLOAT NULL DEFAULT NULL ,
  `team2_result` FLOAT NULL DEFAULT NULL ,
  `team1_bonus` INT(11) NULL DEFAULT NULL ,
  `team2_bonus` INT(11) NULL DEFAULT NULL ,
  `team1_legs` FLOAT NULL DEFAULT NULL ,
  `team2_legs` FLOAT NULL DEFAULT NULL ,
  `team1_result_split` VARCHAR(64) NULL DEFAULT NULL ,
  `team2_result_split` VARCHAR(64) NULL DEFAULT NULL ,
  `match_result_type` TINYINT(4) NOT NULL DEFAULT '0' ,
  `team_won` INT(11) NOT NULL DEFAULT '0' ,
  `team1_result_ot` FLOAT NULL DEFAULT NULL ,
  `team2_result_ot` FLOAT NULL DEFAULT NULL ,
  `team1_result_so` FLOAT NULL DEFAULT NULL ,
  `team2_result_so` FLOAT NULL DEFAULT NULL ,
  `alt_decision` TINYINT(4) NOT NULL DEFAULT '0' ,
  `team1_result_decision` FLOAT NULL DEFAULT NULL ,
  `team2_result_decision` FLOAT NULL DEFAULT NULL ,
  `decision_info` VARCHAR(128) NOT NULL DEFAULT '' ,
  `cancel` TINYINT(4) NOT NULL DEFAULT '0' ,
  `cancel_reason` VARCHAR(32) NOT NULL DEFAULT '' ,
  `count_result` TINYINT(4) NOT NULL DEFAULT '1' ,
  `crowd` INT(11) NOT NULL DEFAULT '0' ,
  `summary` TEXT NULL DEFAULT NULL  ,
  `show_report` TINYINT(4) NOT NULL DEFAULT '0' ,
  `preview` TEXT NULL DEFAULT NULL  ,
  `match_result_detail` VARCHAR(64) NOT NULL DEFAULT '' ,
  `new_match_id` INT(11) NOT NULL DEFAULT '0' ,
  `old_match_id` INT(11) NOT NULL DEFAULT '0' ,
  `extended` TEXT NULL ,
  `published` TINYINT(4) NOT NULL DEFAULT '1' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `division_id` INT(11) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  `pressebericht` TEXT NULL ,
  `team_lost` INT(11) NOT NULL DEFAULT '0' ,
  `import_match_id` INT(11) NOT NULL DEFAULT '0' ,
  `team1_single_matchpoint` DOUBLE NULL DEFAULT NULL ,
  `team2_single_matchpoint` DOUBLE NULL DEFAULT NULL ,
  `team1_single_sets` DOUBLE NULL DEFAULT NULL ,
  `team2_single_sets` DOUBLE NULL DEFAULT NULL ,
  `team1_single_games` DOUBLE NULL DEFAULT NULL ,
  `team2_single_games` DOUBLE NULL DEFAULT NULL ,
  `approved` TINYINT(1)  NOT NULL DEFAULT '0' ,
  `approved_date` DATE NOT NULL DEFAULT  '0000-00-00',
  `approved_time` TIME NOT NULL DEFAULT  '00:00',
  `approved_user` INT( 11 ) NOT NULL DEFAULT  '0',
  `gcal_event_id` VARCHAR(150) NULL DEFAULT NULL ,
  `result_type` INT(11) NOT NULL DEFAULT '0' ,
  `content_id` INT( 11 ) NOT NULL DEFAULT  '0',
  `overtime` INT( 11 ) NOT NULL DEFAULT  '0',
  `match_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `next_match_id` INT(11) NOT NULL DEFAULT '0' ,
  `openligaid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `round_id` (`round_id`),
  KEY `projectteam1_id` (`projectteam1_id`),
  KEY `projectteam2_id` (`projectteam2_id`),
  KEY `playground_id` (`playground_id`),
  KEY `new_match_id` (`new_match_id`),
  KEY `old_match_id` (`old_match_id`),
  KEY `match_timestamp` (`match_timestamp`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_match_commentary`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_match_commentary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `event_time` int(11) NOT NULL DEFAULT '0',
  `notes` text NULL DEFAULT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_match_event`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_match_event` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `match_id` INT(11) NOT NULL DEFAULT '0' ,
  `projectteam_id` INT(11) NOT NULL DEFAULT '0' ,
  `teamplayer_id` INT(11) NOT NULL DEFAULT '0' ,
  `teamplayer_id2` INT(11) NOT NULL DEFAULT '0' ,
  `event_time` int(11) NOT NULL DEFAULT '0' ,
  `event_type_id` INT(11) NOT NULL DEFAULT '0' ,
  `event_sum` DOUBLE NULL DEFAULT NULL ,
  `notice` VARCHAR(64) NOT NULL DEFAULT '' ,
  `notes` TEXT NULL DEFAULT NULL ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `penalty_points` INT(11) NOT NULL DEFAULT '0' ,
  `game_part` INT(11) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `projectteam_id` (`projectteam_id`),
  KEY `teamplayer_id` (`teamplayer_id`),
  KEY `teamplayer_id2` (`teamplayer_id2`),
  KEY `event_type_id` (`event_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_match_player`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_match_player` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `match_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `teamplayer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `project_position_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `came_in` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ,
  `in_for` INT(11) UNSIGNED NULL DEFAULT NULL ,
  `out` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ,
  `in_out_time` VARCHAR(15) NULL DEFAULT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `trikot_number` INT(11) NOT NULL DEFAULT '0' ,
  `game_part` INT(11) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `captain` TINYINT(1) NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `teamplayer_id` (`teamplayer_id`),
  KEY `in_for` (`in_for`),
  KEY `project_position_id` (`project_position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_match_statistic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_match_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL DEFAULT '0',
  `projectteam_id` int(11) NOT NULL,
  `teamplayer_id` int(11) NOT NULL DEFAULT '0',
  `statistic_id` int(11) NOT NULL DEFAULT '0',
  `value` DOUBLE NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `projectteam_id` (`projectteam_id`),
  KEY `teamplayer_id` (`teamplayer_id`),
  KEY `statistic_id` (`statistic_id`)
  )
ENGINE=MyISAM
DEFAULT CHARSET=utf8
AUTO_INCREMENT=1 ;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_match_staff_statistic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_match_staff_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL DEFAULT '0',
  `projectteam_id` int(11) NOT NULL,
  `team_staff_id` int(11) NOT NULL DEFAULT '0',
  `statistic_id` int(11) NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `projectteam_id` (`projectteam_id`),
  KEY `team_staff_id` (`team_staff_id`),
  KEY `statistic_id` (`statistic_id`)
  )
ENGINE=MyISAM
DEFAULT CHARSET=utf8
AUTO_INCREMENT=1 ;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_match_referee`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_match_referee` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `match_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_referee_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_position_id` INT(11) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `project_referee_id` (`project_referee_id`),
  KEY `project_position_id` (`project_position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_match_staff`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_match_staff` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `match_id` INT(11) NOT NULL DEFAULT '0' ,
  `team_staff_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_position_id` INT(11) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `team_staff_id` (`team_staff_id`),
  KEY `project_position_id` (`project_position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_person`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_person` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NULL DEFAULT NULL ,
  `firstname` VARCHAR(45) NOT NULL DEFAULT '' ,
  `lastname` VARCHAR(45) NOT NULL DEFAULT '' ,
  `nickname` VARCHAR(45) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(90) NOT NULL DEFAULT '' ,
  `country` VARCHAR(3) NULL DEFAULT NULL,
  `knvbnr` VARCHAR(10) NOT NULL DEFAULT '' ,
  `birthday` DATE NOT NULL DEFAULT '0000-00-00' ,
  `deathday` DATE NOT NULL DEFAULT '0000-00-00' ,
  `height` INT(3) NULL DEFAULT NULL ,
  `weight` INT(3) NULL DEFAULT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `show_pic` TINYINT(1) NOT NULL DEFAULT '1' ,
  `show_persdata` TINYINT(1) NOT NULL DEFAULT '1' ,
  `show_teamdata` TINYINT(1) NOT NULL DEFAULT '1' ,
  `show_on_frontend` TINYINT(1) NOT NULL DEFAULT '1' ,
  `info` VARCHAR(255) NOT NULL DEFAULT '' ,
  `notes` TEXT NULL DEFAULT NULL ,
  `phone` VARCHAR(20) NOT NULL DEFAULT '' ,
  `mobile` VARCHAR(20) NOT NULL DEFAULT '' ,
  `email` VARCHAR(50) NULL DEFAULT NULL ,
  `website` VARCHAR(250) NULL DEFAULT NULL ,
  `address` VARCHAR(100) NOT NULL DEFAULT '' ,
  `zipcode` VARCHAR(10) NOT NULL DEFAULT '' ,
  `location` VARCHAR(50) NOT NULL DEFAULT '' ,
  `state` VARCHAR(50) NOT NULL DEFAULT '' ,
  `address_country` VARCHAR(3) NULL DEFAULT NULL,
  `extended` TEXT NULL ,
  `position_id` INT(11) NULL,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `latitude` DECIMAL(12,8) NULL DEFAULT NULL,
  `longitude` DECIMAL(12,8) NULL DEFAULT NULL,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `associations` INT(11) NOT NULL DEFAULT '0',
  `extendeduser` TEXT NULL ,
  `season_ids` TEXT NULL ,
  `injury` TINYINT(4) NOT NULL DEFAULT '0' ,
  `injury_date` INT(11) NULL DEFAULT NULL ,
  `injury_end` INT(11) NULL DEFAULT NULL ,
  `injury_detail` VARCHAR(255) NULL DEFAULT NULL ,
  `injury_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `injury_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `suspension` TINYINT(4) NOT NULL DEFAULT '0' ,
  `suspension_date` INT(11) NULL DEFAULT NULL ,
  `suspension_end` INT(11) NULL DEFAULT NULL ,
  `suspension_detail` VARCHAR(255) NULL DEFAULT NULL ,
  `susp_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `susp_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `away` TINYINT(4) NOT NULL DEFAULT '0' ,
  `away_date` INT(11) NULL DEFAULT NULL ,
  `away_end` INT(11) NULL DEFAULT NULL ,
  `away_detail` VARCHAR(255) NULL DEFAULT NULL ,
  `away_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `away_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `twitter` VARCHAR(250) NOT NULL DEFAULT '' ,
  `facebook` VARCHAR(250) NOT NULL DEFAULT '' ,
  `unique_id` VARCHAR(100) NULL DEFAULT NULL ,
  `person_id1` INT( 11 ) NOT NULL DEFAULT  '0',
  `person_id2` INT( 11 ) NOT NULL DEFAULT  '0',
  `person_art` TINYINT( 4 ) NOT NULL DEFAULT  '1',
  `sports_type_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `contact_id` int(11) DEFAULT NULL,
  `jl_update` TINYINT(1) NOT NULL DEFAULT '0',
  `cr_picture` varchar(255) DEFAULT NULL,
  `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0',
  `hits` INT(11) NOT NULL DEFAULT '0' ,
  `modified_hits` INT(11) NOT NULL DEFAULT '0' ,
  `birthday_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `deathday_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `second_country` VARCHAR(3) NULL DEFAULT NULL,
  `gender` TINYINT(1) NOT NULL DEFAULT '0' ,
  `openligaid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `country` (`country`),
  KEY `position_id` (`position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_playground`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_playground` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `short_name` VARCHAR(15) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `address` VARCHAR(100) NOT NULL DEFAULT '' ,
  `zipcode` VARCHAR(10) NOT NULL DEFAULT '' ,
  `city` VARCHAR(64) NOT NULL DEFAULT '' ,
  `country` VARCHAR(3) NULL DEFAULT NULL,
  `max_visitors` INT(11) NULL DEFAULT NULL ,
  `website` VARCHAR(250) NOT NULL DEFAULT '' ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `notes` TEXT NULL DEFAULT NULL ,
  `club_id` INT(11) NOT NULL DEFAULT '0' ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `latitude` DECIMAL(12,8) NULL DEFAULT NULL,
  `longitude` DECIMAL(12,8) NULL DEFAULT NULL,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  `unique_id` VARCHAR(100) NULL DEFAULT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `hits` INT(11) NOT NULL DEFAULT '0' ,
  `modified_hits` INT(11) NOT NULL DEFAULT '0' ,
  `state` VARCHAR(50) NOT NULL DEFAULT '' ,
  `openligaid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `club_id` (`club_id`),
  KEY `country` (`country`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_position`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_position` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `parent_id` INT(11) NOT NULL DEFAULT '0' ,
  `persontype` TINYINT(4) NOT NULL DEFAULT '1' ,
  `sports_type_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `ordering` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `picture` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_21.png' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `parent_id` (`parent_id`),
  KEY `sports_type_id` (`sports_type_id`),
  UNIQUE KEY `name` (`name`,`parent_id`,`persontype`,`sports_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_position_eventtype`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_position_eventtype` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `position_id` INT(11) NOT NULL DEFAULT '0' ,
  `eventtype_id` INT(11) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) ,
  KEY `position_id` (`position_id`),
  KEY `eventtype_id` (`eventtype_id`),
  UNIQUE INDEX `pos_et` (`position_id` ASC, `eventtype_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_position_statistic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_position_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) NOT NULL DEFAULT '0',
  `statistic_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `position_id` (`position_id`),
  KEY `statistic_id` (`statistic_id`),
  UNIQUE KEY `pos_et` (`position_id`,`statistic_id`)
  )
ENGINE=MyISAM
DEFAULT CHARSET=utf8
AUTO_INCREMENT=1 ;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_project`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_project` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(100) NOT NULL DEFAULT '' ,
  `league_id` INT(11) NOT NULL DEFAULT '0' ,
  `season_id` INT(11) NOT NULL DEFAULT '0' ,
  `admin` INT(11) NOT NULL DEFAULT '0' ,
  `editor` INT(11) NOT NULL DEFAULT '0' ,
  `master_template` INT(11) NOT NULL DEFAULT '0' ,
  `sub_template_id` INT(11) NOT NULL DEFAULT '0' ,
  `extension` VARCHAR(80) NULL DEFAULT NULL ,
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'Europe/Amsterdam' ,
  `project_type` ENUM('SIMPLE_LEAGUE', 'DIVISIONS_LEAGUE', 'TOURNAMENT_MODE', 'FRIENDLY_MATCHES') NOT NULL DEFAULT 'SIMPLE_LEAGUE' ,
  `teams_as_referees` TINYINT(1) NOT NULL DEFAULT '0' ,
  `sports_type_id` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` DATE NOT NULL DEFAULT '0000-00-00' ,
  `start_time` VARCHAR(5) NOT NULL DEFAULT '15:30' ,
  `current_round_auto` TINYINT(4) NOT NULL DEFAULT '0' ,
  `current_round` VARCHAR(32) NOT NULL DEFAULT '0' ,
  `auto_time` INT(11) NULL DEFAULT NULL ,
  `game_regular_time` SMALLINT(6) NOT NULL DEFAULT '90' ,
  `game_parts` SMALLINT(6) NOT NULL DEFAULT '2' ,
  `halftime` SMALLINT(6) NOT NULL DEFAULT '15' ,
  `points_after_regular_time` VARCHAR(10) NOT NULL DEFAULT '3,1,0' ,
  `use_legs` TINYINT(1)  NULL DEFAULT NULL ,
  `allow_add_time` TINYINT(1) NOT NULL DEFAULT '0' ,
  `add_time` SMALLINT(6) NOT NULL DEFAULT '30' ,
  `points_after_add_time` VARCHAR(10) NOT NULL DEFAULT '3,1,0' ,
  `points_after_penalty` VARCHAR(10) NOT NULL DEFAULT '3,1,0' ,
  `fav_team` VARCHAR(64) NOT NULL DEFAULT '' ,
  `fav_team_highlight_type` VARCHAR(7) NOT NULL DEFAULT '' ,
  `fav_team_color` VARCHAR(7) NOT NULL DEFAULT '' ,
  `fav_team_text_color` VARCHAR(7) NOT NULL DEFAULT '' ,
  `fav_team_text_bold` VARCHAR(7) NOT NULL DEFAULT '' ,
  `template` VARCHAR(32) NOT NULL DEFAULT 'default' ,
  `enable_sb` TINYINT(4) NOT NULL DEFAULT '0' ,
  `sb_catid` INT(11) NOT NULL DEFAULT '0' ,
  `extended` TEXT NULL ,
  `picture` VARCHAR(128) NULL DEFAULT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `staffel_id` VARCHAR(100) NOT NULL DEFAULT '' ,
  `extendeduser` TEXT NULL ,
  `import_project_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_art_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `project_live_update` TINYINT(1) NOT NULL DEFAULT '0' ,
  `use_tie_break` TINYINT(1)  NULL DEFAULT '0' ,
  
  `tennis_single_matches` SMALLINT(6) NOT NULL DEFAULT '0' ,
  `tennis_double_matches` SMALLINT(6) NOT NULL DEFAULT '0' ,
  
  `approved_gcalendar` TINYINT(1)  NOT NULL DEFAULT '0' ,
  `gcalendar_id` INT( 11 ) NOT NULL DEFAULT  '0',
  
  `gcalendar_use_fav_teams` INT( 11 ) NOT NULL DEFAULT  '0',
  `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0',
  `projectinfo` TEXT NULL DEFAULT NULL ,
  `category_id` INT( 11 ) NOT NULL DEFAULT  '0',
  
  `hits` INT(11) NOT NULL DEFAULT '0' ,
  `modified_hits` INT(11) NOT NULL DEFAULT '0' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `modified_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `editorgroup` INT( 11 ) DEFAULT '0' ,
  `use_nation` tinyint(1) NOT NULL DEFAULT '0',
  `use_approved` tinyint(1) NOT NULL DEFAULT '0',
  `fast_projektteam` tinyint(1) NOT NULL DEFAULT '0',
  `openligaid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `league_id` (`league_id`),
  KEY `season_id` (`season_id`),
  KEY `sub_template_id` (`sub_template_id`),
  KEY `sports_type_id` (`sports_type_id`),
  KEY `modified_timestamp` (`modified_timestamp`),
  UNIQUE INDEX `name, league, season` (`name` ASC, `league_id` ASC, `season_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_project_position`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_project_position` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) NOT NULL ,
  `position_id` INT(11) NOT NULL ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `jl_update` TINYINT(1) NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`) ,
  KEY `project_id` (`project_id`),
  KEY `position_id` (`position_id`),
  UNIQUE INDEX `pos_proj` (`position_id` ASC, `project_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_project_referee`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_project_referee` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_position_id` INT(11) NULL DEFAULT NULL ,
  `notes` TEXT NULL DEFAULT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `published` INT(1) UNSIGNED NOT NULL DEFAULT '1' ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  `cr_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `person_id` (`person_id`),
  KEY `project_position_id` (`project_position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_project_team`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_project_team` (
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

--
-- Tabellenstruktur für Tabelle `jos_sportsmanagement_rosterposition`
--

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_rosterposition` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `alias` enum('HOME_POS','AWAY_POS') NOT NULL default 'HOME_POS',
  `country` varchar(3) default 'DEU',
  `ordering` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `short_name` enum('HOME_POS','AWAY_POS') NOT NULL default 'HOME_POS',
  `middle_name` varchar(25) NOT NULL default '',
  `extended` text,
  `picture` varchar(255) NOT NULL DEFAULT 'spielfeld_578x1050.png',
  `players` int(11) NOT NULL default '11',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`,`short_name`),
  KEY `country` (`country`)
) 
ENGINE=MyISAM  
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_round`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_round` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `roundcode` INT(11) NOT NULL DEFAULT '0' ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `round_date_first` DATE NOT NULL DEFAULT '0000-00-00' ,
  `round_date_last` DATE NOT NULL DEFAULT '0000-00-00' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `extendeduser` TEXT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' ,
  `tournement` TINYINT(1) NOT NULL DEFAULT '0' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `rdatefirst_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',  
  `rdatelast_timestamp` INT( 11 ) NOT NULL DEFAULT  '0',
  `openligaid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `project_id` (`project_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_season`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_season` (
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
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;



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
  `picture` VARCHAR(250) NULL ,
  `logo_big` VARCHAR(250) NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `standard_playground` INT(11) NULL DEFAULT NULL ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `cr_logo_big` varchar(255) DEFAULT NULL,
  `kaderlink` VARCHAR( 250 ) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `combi` (`team_id`,`season_id`) ,
  KEY `team_id` (`team_id`),
  KEY `season_id` (`season_id`)
  )
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
  `picture` VARCHAR(250) NULL ,
  `persontype` TINYINT(1) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `position_id` INT( 11 ) NOT NULL DEFAULT  '0',
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `combi` (`person_id`,`season_id`,`team_id`,`persontype`) ,
  KEY `team_id` (`team_id`),
  KEY `season_id` (`season_id`),
  KEY `person_id` (`person_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_season_team_person_id`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_season_team_person_id` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  `team_id` INT(11) NOT NULL DEFAULT '0' ,
  `season_id` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `picture` VARCHAR(250) NULL ,
  
  `project_position_id` INT(11) NULL DEFAULT NULL ,
  `active` TINYINT(1) NULL DEFAULT '1' ,
  `jerseynumber` INT(11) NULL DEFAULT NULL ,
  `notes` TEXT NULL DEFAULT NULL ,
  `extended` TEXT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `market_value` INT(11) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  `played_time` INT(11) NULL DEFAULT '0' ,
  
  `persontype` TINYINT(1) NOT NULL DEFAULT '0' ,
  `jl_update` TINYINT(1) NOT NULL DEFAULT '0',
  `cr_picture` varchar(255) DEFAULT NULL,
  `position_id` INT( 11 ) NOT NULL DEFAULT  '0',
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `combi` (`person_id`,`season_id`,`team_id`,`persontype`) ,
  KEY `team_id` (`team_id`),
  KEY `season_id` (`season_id`),
  KEY `person_id` (`person_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;







-- -----------------------------------------------------
-- Table `#__sportsmanagement_sports_type`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_sports_type` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL DEFAULT '' ,
  `icon` varchar(128) NOT NULL DEFAULT '',    
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `sportsart` TINYINT(1) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `eventtime` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_statistic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL DEFAULT '',
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `short` varchar(10) NOT NULL DEFAULT '',
  `icon` varchar(128) NOT NULL DEFAULT '',
  `class` varchar(50) NOT NULL COMMENT 'must be the name of the class handling it',
  `calculated` tinyint(4) NOT NULL,
  `params` text NOT NULL,
  `baseparams` text NOT NULL,
  `note` varchar(100) DEFAULT NULL,
  `sports_type_id` TINYINT(1) NOT NULL DEFAULT '1' ,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`) ,
  KEY `sports_type_id` (`sports_type_id`)
)
ENGINE=MyISAM
DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_team`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_team` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `club_id` INT(11) NULL DEFAULT NULL ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `short_name` VARCHAR(15) NOT NULL DEFAULT '' ,
  `middle_name` VARCHAR(25) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `website` VARCHAR(250) NOT NULL DEFAULT '' ,
  `info` VARCHAR(255) NOT NULL DEFAULT '' ,
  `notes` TEXT NULL DEFAULT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `merge_clubs` VARCHAR(255) NOT NULL DEFAULT '' ,
  `extendeduser` TEXT NULL ,
  `season_ids` TEXT NULL ,
  `unique_id` VARCHAR(100) NULL DEFAULT NULL ,
  `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0',
  `sports_type_id` INT( 11 ) NOT NULL DEFAULT  '0',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `standard_playground` INT(11) NULL DEFAULT NULL ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `hits` INT(11) NOT NULL DEFAULT '0' ,
  `modified_hits` INT(11) NOT NULL DEFAULT '0' ,
  `team_number` INT(11) NOT NULL DEFAULT '0' ,
  `team_stars` INT(11) NOT NULL DEFAULT '0' ,
  `email` VARCHAR(250) NULL ,
  `openligaid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `club_id` (`club_id`),
  KEY `sports_type_id` (`sports_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_team_player`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_team_player` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `projectteam_id` INT(11) NULL DEFAULT '0' ,
  `person_id` INT(11) NULL DEFAULT '0' ,
  `project_position_id` INT(11) NULL DEFAULT NULL ,
  `active` TINYINT(1) NULL DEFAULT '1' ,
  `jerseynumber` INT(11) NULL DEFAULT NULL ,
  `notes` TEXT NOT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `extended` TEXT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `market_value` INT(11) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  `played_time` INT(11) NULL DEFAULT '0' ,
  `injury` TINYINT(4) NOT NULL DEFAULT '0' ,
  `injury_date` INT(11) NOT NULL ,
  `injury_end` INT(11) NOT NULL ,
  `injury_detail` VARCHAR(255) NOT NULL ,
  `injury_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `injury_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `suspension` TINYINT(4) NOT NULL DEFAULT '0' ,
  `suspension_date` INT(11) NOT NULL ,
  `suspension_end` INT(11) NOT NULL ,
  `suspension_detail` VARCHAR(255) NOT NULL ,
  `susp_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `susp_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `away` TINYINT(4) NOT NULL DEFAULT '0' ,
  `away_date` INT(11) NOT NULL ,
  `away_end` INT(11) NOT NULL ,
  `away_detail` VARCHAR(255) NOT NULL ,
  `away_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `away_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  PRIMARY KEY (`id`),
  KEY `projectteam_id` (`projectteam_id`),
  KEY `person_id` (`person_id`),
  KEY `project_position_id` (`project_position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_team_staff`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_team_staff` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `projectteam_id` INT(11) NULL DEFAULT '0' ,
  `person_id` INT(11) NULL DEFAULT '0' ,
  `project_position_id` INT(11) NULL DEFAULT NULL ,
  `active` TINYINT(1) NULL DEFAULT '1' ,
  `notes` TEXT NOT NULL ,
  `injury` TINYINT(4) NOT NULL DEFAULT '0' ,
  `injury_date` INT(11) NOT NULL ,
  `injury_end` INT(11) NOT NULL ,
  `injury_detail` VARCHAR(255) NOT NULL ,
  `injury_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `injury_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `suspension` TINYINT(4) NOT NULL DEFAULT '0' ,
  `suspension_date` INT(11) NOT NULL ,
  `suspension_end` INT(11) NOT NULL ,
  `suspension_detail` VARCHAR(255) NOT NULL ,
  `susp_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `susp_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `away` TINYINT(4) NOT NULL DEFAULT '0' ,
  `away_date` INT(11) NOT NULL ,
  `away_end` INT(11) NOT NULL ,
  `away_detail` VARCHAR(255) NOT NULL ,
  `away_date_start` DATE NOT NULL DEFAULT '0000-00-00' ,
  `away_date_end` DATE NOT NULL DEFAULT '0000-00-00' ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `extended` TEXT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  PRIMARY KEY (`id`),
  KEY `projectteam_id` (`projectteam_id`),
  KEY `person_id` (`person_id`),
  KEY `project_position_id` (`project_position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_team_trainingdata`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_team_trainingdata` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `team_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_team_id` INT(11) NOT NULL DEFAULT '0' ,
  `dayofweek` TINYINT(1) NOT NULL DEFAULT '0' ,
  `time_start` INT(11) NOT NULL DEFAULT '0' ,
  `time_end` INT(11) NOT NULL DEFAULT '0' ,
  `place` VARCHAR(255) NOT NULL DEFAULT '' ,
  `notes` TEXT NULL DEFAULT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `team_id` (`team_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_template_config`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_template_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `template` VARCHAR(64) NOT NULL DEFAULT '' ,
  `func` VARCHAR(64) NOT NULL DEFAULT '' ,
  `title` VARCHAR(255) NOT NULL DEFAULT '' ,
  `params` TEXT NOT NULL ,
  `published` INT(1) UNSIGNED NOT NULL DEFAULT '1' ,
  `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_treeto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_treeto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL default '0',
  `division_id` int(11) NOT NULL default '0',
  `tree_i` int(11) NOT NULL default '0',
  `name` varchar(128) default NULL,
  `global_bestof` tinyint(1) NOT NULL default '0',
  `global_matchday` tinyint(1) NOT NULL default '0',
  `global_known` tinyint(1) NOT NULL default '0',
  `global_fake` tinyint(1) NOT NULL default '0',
  `leafed` tinyint(1) NOT NULL default '0',
  `mirror` tinyint(1) NOT NULL default '0',
  `hide` tinyint(1) NOT NULL default '0',
  `trophypic` varchar(128) default NULL,
  `extended` text,
  `published` tinyint(1) NOT NULL default '1',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,  PRIMARY KEY  (`id`)
  )
ENGINE=MyISAM
DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_treeto_match`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_treeto_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL default '0',
  `match_id` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `combi` (`node_id`,`match_id`)
  )
ENGINE=MyISAM
DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_treeto_node`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_treeto_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `treeto_id` int(11) NOT NULL default '0',
  `node` int(11) NOT NULL default '0',
  `row` int(11) NOT NULL default '0',
  `bestof` tinyint(1) NOT NULL default '1',
  `title` varchar(50) NOT NULL default '',
  `content` varchar(50) NOT NULL default '',
  `team_id` int(11) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '1',
  `is_leaf` tinyint(1) NOT NULL default '0',
  `is_lock` tinyint(1) NOT NULL default '0',
  `is_ready` tinyint(1) NOT NULL default '0',
  `got_lc` tinyint(1) NOT NULL default '0',
  `got_rc` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `roundcode` INT(11) NOT NULL DEFAULT '0' ,
  PRIMARY KEY  (`id`)
  )
ENGINE=MyISAM
DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_user_extra_fields`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_user_extra_fields` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `template_backend` VARCHAR(75) NOT NULL DEFAULT '' ,
  `template_frontend` VARCHAR(75) NOT NULL DEFAULT '' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` tinyint(1) NOT NULL default '1',
  `views_backend` VARCHAR(75) NOT NULL DEFAULT '' ,
  `fieldtyp` tinyint(1) NOT NULL default '1',
  `views_backend_field` VARCHAR(75) NOT NULL DEFAULT '' ,
  `select_columns` TEXT NULL ,
  `select_values` TEXT NULL ,
  `field_type` VARCHAR(15) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`) 
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_user_extra_fields_values`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_user_extra_fields_values` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `field_id` INT(11) NOT NULL DEFAULT '0' ,
  `jl_id` INT(11) NOT NULL DEFAULT '0' ,    
  `fieldvalue` VARCHAR(300) NOT NULL DEFAULT '' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` tinyint(1) NOT NULL default '1',
  PRIMARY KEY (`id`),
  KEY `jl_id` (`jl_id`),
  UNIQUE KEY `fields` (`field_id`,`jl_id`) 
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8; 

-- -----------------------------------------------------
-- Table `#__sportsmanagement_version`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_version` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `major` INT NOT NULL ,
  `minor` INT NOT NULL ,
  `build` INT NOT NULL ,
  `count` INT NOT NULL ,
  `revision` VARCHAR(128) NOT NULL ,
  `file` VARCHAR(255) NOT NULL DEFAULT '' ,
  `date` TIMESTAMP NOT NULL ,
  `version` VARCHAR(255) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_version_history`
--

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_version_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `text` text,
  `version` VARCHAR(255) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_agegroup`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_agegroup` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `sportstype_id` INT(11) NOT NULL DEFAULT 0,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `info` VARCHAR(255) NOT NULL DEFAULT '' ,
  `notes` TEXT NULL DEFAULT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_50.png' ,
  `age_from` TINYINT(4) NOT NULL DEFAULT 0 ,
  `age_to` TINYINT(4) NOT NULL DEFAULT 0 ,
  `deadline_day` DATETIME DEFAULT '0000-00-00',
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `country` VARCHAR(3) NULL DEFAULT NULL,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `cr_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) ,
  KEY `sportstype_id` (`sportstype_id`),
  INDEX `fk_sportstype` (`sportstype_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- --------------------------------------------------------
--
-- Table structure for table '#__sportsmanagement_match_single'
--
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_match_single` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `round_id` int(11) NOT NULL DEFAULT '0',
  `match_number` varchar(10) DEFAULT NULL,
  `projectteam1_id` int(11) NOT NULL DEFAULT '0',
  `projectteam2_id` int(11) NOT NULL DEFAULT '0',
  `playground_id` int(11) DEFAULT NULL,
  `match_id` int(11) NOT NULL,
  `teamplayer1_id` int(11) NOT NULL,
  `teamplayer2_id` int(11) NOT NULL,
  `match_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time_present` time DEFAULT NULL,
  `team1_result` float DEFAULT NULL,
  `team2_result` float DEFAULT NULL,
  `team1_bonus` int(11) DEFAULT NULL,
  `team2_bonus` int(11) DEFAULT NULL,
  `team1_legs` float DEFAULT NULL,
  `team2_legs` float DEFAULT NULL,
  `team1_result_split` varchar(64) DEFAULT NULL,
  `team2_result_split` varchar(64) DEFAULT NULL,
  `match_result_type` tinyint(4) NOT NULL DEFAULT '0',
  `team_won` tinyint(4) NOT NULL DEFAULT '0',
  `team1_result_ot` float DEFAULT NULL,
  `team2_result_ot` float DEFAULT NULL,
  `team1_result_so` float DEFAULT NULL,
  `team2_result_so` float DEFAULT NULL,
  `alt_decision` tinyint(4) NOT NULL DEFAULT '0',
  `team1_result_decision` float DEFAULT NULL,
  `team2_result_decision` float DEFAULT NULL,
  `decision_info` varchar(128) NOT NULL DEFAULT '',
  `cancel` tinyint(4) NOT NULL DEFAULT '0',
  `cancel_reason` varchar(32) NOT NULL DEFAULT '',
  `count_result` tinyint(4) NOT NULL DEFAULT '1',
  `crowd` int(11) NOT NULL DEFAULT '0',
  `summary` text NOT NULL,
  `show_report` tinyint(4) NOT NULL DEFAULT '0',
  `preview` text NOT NULL,
  `match_result_detail` varchar(64) NOT NULL DEFAULT '',
  `new_match_id` int(11) NOT NULL DEFAULT '0',
  `old_match_id` int(11) NOT NULL DEFAULT '0',
  `extended` text,
  `published` tinyint(4) NOT NULL DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `match_type` ENUM(  'SINGLE',  'DOUBLE' ) NOT NULL DEFAULT  'SINGLE',
  
  `double_team1_player1` int(11) NOT NULL DEFAULT '0',
  `double_team1_player2` int(11) NOT NULL DEFAULT '0',
  `double_team2_player1` int(11) NOT NULL DEFAULT '0',
  `double_team2_player2` int(11) NOT NULL DEFAULT '0',
  
  PRIMARY KEY (`id`),
  KEY `round_id` (`round_id`),
  KEY `projectteam1_id` (`projectteam1_id`),
  KEY `projectteam2_id` (`projectteam2_id`),
  KEY `playground_id` (`playground_id`),
  KEY `new_match_id` (`new_match_id`),
  KEY `old_match_id` (`old_match_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_admin`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_admin` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `prediction_id` INT(11) NOT NULL DEFAULT '0' ,
  `user_id` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `user_id` (`user_id`),
  UNIQUE INDEX `pred_user` (`prediction_id` ASC, `user_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_game`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_game` (
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
-- Table `#__sportsmanagement_prediction_groups`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_groups` (
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
  `countmembers` INT(11) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_member`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_member` (
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
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `final4_tipp` VARCHAR(64) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `user_id` (`user_id`),
  UNIQUE INDEX `member` (`prediction_id` ASC, `user_id` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_project`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_project` (
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
  
  `points_correct_yellow_cards` SMALLINT(6) NOT NULL DEFAULT '6' ,
  `points_correct_yellow_red_cards` SMALLINT(6) NOT NULL DEFAULT '6' ,
  `points_correct_red_cards` SMALLINT(6) NOT NULL DEFAULT '6' ,
  `points_correct_penalties` SMALLINT(6) NOT NULL DEFAULT '6' ,
  `points_correct_goals` SMALLINT(6) NOT NULL DEFAULT '6' ,
  
  `use_cards` TINYINT(4) NOT NULL DEFAULT '0' ,
  `use_penalties` TINYINT(4) NOT NULL DEFAULT '0' ,
  `use_goals` TINYINT(4) NOT NULL DEFAULT '0' ,
`final4` TINYINT(4) NOT NULL DEFAULT '0' ,
`points_tipp_final4` SMALLINT(6) NOT NULL DEFAULT '5' ,
`league_final4` VARCHAR(128) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`) ,
  KEY `prediction_id` (`prediction_id`),
  KEY `project_id` (`project_id`),
  UNIQUE INDEX `pred_proj` (`prediction_id` ASC, `project_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_result`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_result` (
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
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `yellow_cards` int(11) DEFAULT NULL,
  `red_cards` int(11) DEFAULT NULL,
  `penalties` int(11) DEFAULT NULL,
  `yellow_red_cards` int(11) DEFAULT NULL,
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
-- Table `#__sportsmanagement_prediction_tippround`
-- -----------------------------------------------------
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


-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_result_round`
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Table `#__sportsmanagement_prediction_template`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_prediction_template` (
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


--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_position_ringen`
--

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_position_ringen` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(75) NOT NULL default '',
  `alias` varchar(75) NOT NULL default '',
  `short_name` varchar(75) NOT NULL default '',
  `middle_name` varchar(75) NOT NULL default '',
  `extended` TEXT NULL ,
  `country` varchar(3) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`,`country`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;


--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_rquote`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_rquote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily_number` int(11) NOT NULL DEFAULT '0',
  `quote` text NOT NULL,
  `author` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notes` text NULL DEFAULT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `catid` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `createdate` datetime DEFAULT NULL,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150_3.png' ,
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `cr_picture` varchar(255) DEFAULT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_pictures`
--

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL DEFAULT '',
  `file` varchar(75) NOT NULL DEFAULT '',
  `directory` varchar(250) DEFAULT NULL,
  `folder` text,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_jl_tables`
--

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_jl_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `import` tinyint(1) NOT NULL DEFAULT '0',
  `import_data` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur f�r google kalender api Tabellen
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_gcalendar` (
  `id` int(11) NOT NULL auto_increment,
  `calendar_id` text NULL DEFAULT NULL,
  `name` text NULL DEFAULT NULL,
  `magic_cookie` text NULL DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `color` text NOT NULL,
  `access` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `access_content` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `version` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  
  PRIMARY KEY  (`id`)
) ;

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_gcalendarap` (
  `id` int(11) NOT NULL auto_increment,
  `gcalendar_id` int(11) NOT NULL,
  `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `access_comments` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `use_credentials` int NOT NULL DEFAULT 1,
  `event_output` text NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_gcalendarap_comment` (
  `id` int(11) NOT NULL auto_increment,
  `event_id` varchar(200) NOT NULL,
  `event_name` text NOT NULL,
  `event_gc_id` int(11) NOT NULL,  
  `ip` varchar(23) NOT NULL,
  `user_id` int(11) NOT NULL,  
  `title` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `added` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_confidential` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR( 250 ) DEFAULT NULL ,
 `link` VARCHAR( 250 ) DEFAULT NULL ,
 `nummer` VARCHAR( 50 ) DEFAULT NULL ,
 `project` INT( 11 ) DEFAULT NULL ,
 `country` VARCHAR( 3 ) DEFAULT NULL ,
 `teamart` VARCHAR( 30 ) DEFAULT NULL ,
 `team_id` INT( 11 ) NOT NULL DEFAULT  '0',
 `club_id` INT( 11 ) NOT NULL DEFAULT  '0',
 `person_id` INT( 11 ) NOT NULL DEFAULT  '0',
 `clublink` VARCHAR( 250 ) NULL DEFAULT NULL ,
 `clubnummer` VARCHAR( 100 ) NULL DEFAULT NULL,
 `published` TINYINT(1) NOT NULL DEFAULT '1' ,
PRIMARY KEY (  `id` ),
KEY `project_id` (`project`),
  UNIQUE KEY `schluessel` (`name`(150),`link`(150),`country`),
  UNIQUE KEY `schluessel2` (`name`(150),`nummer`(50)) 
) ENGINE = MYISAM DEFAULT CHARSET = utf8;


--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_countries_gazetteer`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_countries_gazetteer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `asciiname` varchar(200) DEFAULT NULL,
  `alternatenames` text,
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `feature_class` int(11) NOT NULL,
  `feature_code` varchar(10) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `cc2` varchar(60) DEFAULT NULL,
  `admin1_code` varchar(20) DEFAULT NULL,
  `admin2_code` varchar(80) DEFAULT NULL,
  `admin3_code` varchar(20) DEFAULT NULL,
  `admin4_code` varchar(20) DEFAULT NULL,
  `population` bigint(8) DEFAULT NULL,
  `elevation` int(11) NOT NULL,
  `dem` int(11) NOT NULL,
  `timezone` varchar(40) DEFAULT NULL,
  `modified` date NOT NULL DEFAULT '0000-00-00',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_countries_plz`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_countries_plz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `place_name` varchar(180) DEFAULT NULL,
  `admin_name1` varchar(100) DEFAULT NULL,
  `admin_code1` varchar(20) DEFAULT NULL,
  `admin_name2` varchar(100) DEFAULT NULL,
  `admin_code2` varchar(20) DEFAULT NULL,
  `admin_code3` varchar(20) DEFAULT NULL,
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `accuracy` int(1) DEFAULT NULL,
  `modified` date NOT NULL DEFAULT '0000-00-00',
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schluessel1` (`country_code`,`postal_code`,`place_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur f�r Tabelle `#__sportsmanagement_person_project_position`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_person_project_position` (
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_id` INT(11) NOT NULL DEFAULT '0' ,
  `project_position_id` INT(11) NOT NULL DEFAULT '0' ,
  `modified_by` INT NULL ,
  `modified` date NOT NULL DEFAULT '0000-00-00',
  `persontype` TINYINT(1) NOT NULL DEFAULT '0' ,
  `published` TINYINT(1) NOT NULL DEFAULT '1' ,
  UNIQUE KEY `combi` (`person_id`,`project_id`,`project_position_id`,`persontype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_log_entries` (
  `priority` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  KEY `idx_category_date_priority` (`category`,`date`,`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
