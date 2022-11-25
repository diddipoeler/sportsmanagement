--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_rquote`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_rquote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily_number` int(11) NOT NULL DEFAULT '0',
  `quote` text NOT NULL,
  `author` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notes` text NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `catid` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `createdate` datetime DEFAULT NULL,
  `picture` VARCHAR(128) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_150_3.png' ,
  `person_id` INT(11) NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_error_log`
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

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_dfbkey`
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- ALTER TABLE  `#__sportsmanagement_associations` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
-- ALTER TABLE  `#__sportsmanagement_associations` CHANGE  `alias`  `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
