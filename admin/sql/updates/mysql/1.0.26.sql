--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_pictures`
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

