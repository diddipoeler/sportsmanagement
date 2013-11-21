--
-- Tabellenstruktur für Tabelle `jos_joomleague_associations`
--

CREATE TABLE IF NOT EXISTS `#__joomleague_associations` (
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
  `assocflag` VARCHAR(255) NOT NULL DEFAULT 'images/com_joomleague/database/placeholders/placeholder_flags.png' ,
  `picture` VARCHAR(128) NOT NULL DEFAULT '' ,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`country`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;