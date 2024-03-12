--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_playground_details`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_playground_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `playground_id` int(11) NOT NULL DEFAULT 0,
  `date_von` date NOT NULL DEFAULT '0000-00-00',
  `date_bis` date NOT NULL DEFAULT '0000-00-00',
  `name_visitors` enum('NAME','VISITORS') NOT NULL DEFAULT 'VISITORS',
  `notes` varchar(255) DEFAULT NULL,
  `max_visitors` int(11) NOT NULL DEFAULT 0,
  `picture` varchar(255) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `cr_picture` varchar(255) DEFAULT NULL,
  `timestamp_von` bigint(20) NOT NULL DEFAULT 0,
  `timestamp_bis` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `history` (`playground_id`,`date_von`,`date_bis`,`name_visitors`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_club_logos`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_club_logos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `club_id` int(11) NOT NULL DEFAULT 0,
  `season_id` int(11) NOT NULL DEFAULT 1,
  `logo_big` varchar(255) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `checked_out` int(11) NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clubseason` (`club_id`,`season_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
