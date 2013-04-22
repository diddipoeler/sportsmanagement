-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_agegroup`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `#__sportsmanagement_agegroup` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `sportstype_id` INT(11) NOT NULL DEFAULT 0,
  `name` VARCHAR(75) NOT NULL DEFAULT '' ,
  `alias` VARCHAR(75) NOT NULL DEFAULT '' ,
  `info` VARCHAR(255) NOT NULL DEFAULT '' ,
  `notes` TEXT NOT NULL ,
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
  PRIMARY KEY (`id`) ,
  KEY `sportstype_id` (`sportstype_id`),
  INDEX `fk_sportstype` (`sportstype_id` ASC)
  )
ENGINE = MyISAM
DEFAULT CHARSET = utf8;