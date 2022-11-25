-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_federations`
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;