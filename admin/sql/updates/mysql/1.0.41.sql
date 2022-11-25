--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_countries_gazetteer`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_countries_gazetteer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `asciiname` varchar(200) DEFAULT NULL,
  `alternatenames` text,
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `feature_class` int(11) NOT NULL,
  `feature_code` varchar(10) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `cc2` varchar(60) DEFAULT NULL,
  `admin1_code` varchar(20) DEFAULT NULL,
  `admin2_code` varchar(80) DEFAULT NULL,
  `admin3_code` varchar(20) DEFAULT NULL,
  `admin4_code` varchar(20) DEFAULT NULL,
  `population` bigint(8) DEFAULT NULL,
  `elevation` int(11) NOT NULL,
  `dem` int(11) NOT NULL,
  `timezone` varchar(40) DEFAULT NULL,
  `modified` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `#__sportsmanagement_countries_plz`
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_countries_plz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `place_name` varchar(180) DEFAULT NULL,
  `admin_name1` varchar(100) DEFAULT NULL,
  `admin_code1` varchar(20) DEFAULT NULL,
  `admin_name2` varchar(100) DEFAULT NULL,
  `admin_code2` varchar(20) DEFAULT NULL,
  `admin_code3` varchar(20) DEFAULT NULL,
  `latitude` float NOT NULL DEFAULT '255',
  `longitude` float NOT NULL DEFAULT '255',
  `accuracy` int(1) DEFAULT NULL,
  `modified` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `#__sportsmanagement_confidential` CHANGE  `nummer`  `nummer` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE  `#__sportsmanagement_confidential` ADD UNIQUE  `schluessel2` (  `name` ( 150 ) ,  `nummer` ( 50 ) ) ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2014-07-28', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_07_28', '1.0.41');