-- ALTER TABLE  `#__sportsmanagement_match` CHANGE  `published`  `published` TINYINT( 4 ) NOT NULL DEFAULT  '1'    ;
-- ALTER TABLE  `#__sportsmanagement_match` CHANGE  `division_id`  `division_id` INT(11) NOT NULL DEFAULT  '0'      ;

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_club_names`
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


-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2015-11-10', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2015_11_10', '1.0.51');
