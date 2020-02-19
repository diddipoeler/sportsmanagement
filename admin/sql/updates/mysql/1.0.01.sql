-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_extra_fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_user_extra_fields` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `published` char(1) NOT NULL default '1',
  `type` char(1) NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  `checked_out` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  PRIMARY KEY  (`id`)
)
ENGINE = MyISAM
DEFAULT CHARSET = utf8;