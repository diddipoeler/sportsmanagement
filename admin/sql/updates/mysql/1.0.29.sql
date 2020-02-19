ALTER TABLE  `#__sportsmanagement_club` CHANGE  `dissolved_year`  `dissolved_year` VARCHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
ALTER TABLE  `#__sportsmanagement_club` CHANGE  `founded_year`  `founded_year` VARCHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;

ALTER TABLE  `#__sportsmanagement_project_team` ADD  `import` TINYINT(1)  NOT NULL DEFAULT '0' ;
ALTER TABLE  `#__sportsmanagement_team` ADD INDEX  `sports_type_id` (  `sports_type_id` );

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_jl_tables`
--

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_jl_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `import` tinyint(1) NOT NULL DEFAULT '0',
  `import_data` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;