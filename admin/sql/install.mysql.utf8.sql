-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_associations`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_associations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL DEFAULT '',
  `alias` varchar(75) NOT NULL DEFAULT '',
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`country`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_club`
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
  `founded` DATE NULL DEFAULT NULL ,
  `phone` VARCHAR(20) NOT NULL DEFAULT '' ,
  `fax` VARCHAR(20) NOT NULL DEFAULT '' ,
  `email` VARCHAR(255) NOT NULL DEFAULT '' ,
  `website` VARCHAR(250) NOT NULL DEFAULT '' ,
  `president` VARCHAR(50) NOT NULL DEFAULT '' ,
  `manager` VARCHAR(50) NOT NULL DEFAULT '' ,
  `logo_big` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' ,
  `logo_middle` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_50.png' ,
  `logo_small` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `standard_playground` INT(11) NULL DEFAULT NULL ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `dissolved` DATE NULL DEFAULT NULL ,
  `dissolved_year` VARCHAR(4) NULL DEFAULT NULL,
  `founded_year` VARCHAR(4) NULL DEFAULT NULL,
  `unique_id` VARCHAR(20) NULL DEFAULT NULL ,
  `new_club_id` INT(11) NOT NULL DEFAULT '0' ,
  `enable_sb` TINYINT(4) NOT NULL DEFAULT '0' ,
  `sb_catid` INT(11) NOT NULL DEFAULT '0' ,
  `trikot_home` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `trikot_away` VARCHAR(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif' ,
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `associations` INT(11) NOT NULL DEFAULT '0',
  `merge_teams` VARCHAR(255) NOT NULL DEFAULT '' ,
  `extendeduser` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_countries`
-- -----------------------------------------------------

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`alpha3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_eventtype`
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
  PRIMARY KEY (`id`) ,
  KEY `sports_type_id` (`sports_type_id`),
  UNIQUE KEY `name` (`name`,`parent`,`sports_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_league`
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
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_21.png' ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `associations` INT(11) NOT NULL DEFAULT '0',
  `extendeduser` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;





-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_person`
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
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png' ,
  `show_pic` TINYINT(1) NOT NULL DEFAULT '1' ,
  `show_persdata` TINYINT(1) NOT NULL DEFAULT '1' ,
  `show_teamdata` TINYINT(1) NOT NULL DEFAULT '1' ,
  `show_on_frontend` TINYINT(1) NOT NULL DEFAULT '1' ,
  `info` VARCHAR(255) NOT NULL DEFAULT '' ,
  `notes` TEXT NOT NULL ,
  `phone` VARCHAR(20) NOT NULL DEFAULT '' ,
  `mobile` VARCHAR(20) NOT NULL DEFAULT '' ,
  `email` VARCHAR(50) NOT NULL ,
  `website` VARCHAR(250) NOT NULL ,
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
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `associations` INT(11) NOT NULL DEFAULT '0',
  `extendeduser` TEXT NULL ,
  
  `bank_code_number` VARCHAR(100) NOT NULL DEFAULT '' ,
  `bank_account_number` VARCHAR(100) NOT NULL DEFAULT '' ,
  `iban` VARCHAR(100) NOT NULL DEFAULT '' ,
  `bank_identifier_code` VARCHAR(100) NOT NULL DEFAULT '' ,
  
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `position_id` (`position_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_playground`
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
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png' ,
  `notes` TEXT NOT NULL ,
  `club_id` INT(11) NOT NULL DEFAULT '0' ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `extendeduser` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  KEY `club_id` (`club_id`),
  UNIQUE INDEX `name` (`name` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_position`
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
  PRIMARY KEY (`id`) ,
  KEY `parent_id` (`parent_id`),
  KEY `sports_type_id` (`sports_type_id`),
  UNIQUE KEY `name` (`name`,`parent_id`,`persontype`,`sports_type_id`)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_season`
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
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;


-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_sports_type`
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
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name` (`name` ASC) )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_statistic`
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
-- Tabellenstruktur für Tabelle `#__sportsmanagement_team`
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
  `notes` TEXT NOT NULL ,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png' ,
  `extended` TEXT NULL ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NULL ,
  `modified_by` INT NULL ,
  `image_copy` TINYINT(4) NOT NULL DEFAULT '0' ,
  `merge_clubs` VARCHAR(255) NOT NULL DEFAULT '' ,
  `extendeduser` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  KEY `club_id` (`club_id`),
  INDEX `fk_club` (`club_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;
