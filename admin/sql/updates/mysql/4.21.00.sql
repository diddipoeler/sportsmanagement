ALTER TABLE  `#__sportsmanagement_playground` ADD `max_visitors_int` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE  `#__sportsmanagement_playground_details` ADD `max_visitors_int` INT(11) NOT NULL DEFAULT 0;


--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_league_logos`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_league_logos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `league_id` int(11) NOT NULL DEFAULT 0,
  `season_id` int(11) NOT NULL DEFAULT 1,
  `logo_big` varchar(255) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clubseason` (`league_id`,`season_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_playground_logos`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_playground_logos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `playground_id` int(11) NOT NULL DEFAULT 0,
  `season_id` int(11) NOT NULL DEFAULT 1,
  `logo_big` varchar(255) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clubseason` (`playground_id`,`season_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
