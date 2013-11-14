--
-- Tabellenstruktur für Tabelle `#__joomleague_position_ringen`
--

CREATE TABLE IF NOT EXISTS `#__joomleague_position_ringen` (
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`,`country`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;

