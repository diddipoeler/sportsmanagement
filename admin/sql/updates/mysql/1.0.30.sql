-- ALTER TABLE  `#__sportsmanagement_match` ADD  `approved` TINYINT(1)  NOT NULL DEFAULT '0' ;
-- ALTER TABLE  `#__sportsmanagement_match` ADD  `approved_date` TIME NOT NULL DEFAULT  '00:00';
-- ALTER TABLE  `#__sportsmanagement_match` ADD  `approved_time` DATE NOT NULL DEFAULT  '0000-00-00';
-- ALTER TABLE  `#__sportsmanagement_match` ADD  `approved_user` INT( 11 ) NOT NULL DEFAULT  '0';

--
-- Tabellenstruktur für google kalender api Tabellen
--
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_gcalendar` (
  `id` int(11) NOT NULL auto_increment,
  `calendar_id` text NOT NULL,
  `name` text NOT NULL,
  `magic_cookie` text NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `color` text NOT NULL,
  `access` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `access_content` tinyint UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`)
) ;

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_gcalendarap` (
  `id` int(11) NOT NULL auto_increment,
  `gcalendar_id` int(11) NOT NULL,
  `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `access_comments` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `use_credentials` int NOT NULL DEFAULT 1,
  `event_output` text NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_gcalendarap_comment` (
  `id` int(11) NOT NULL auto_increment,
  `event_id` varchar(200) NOT NULL,
  `event_name` text NOT NULL,
  `event_gc_id` int(11) NOT NULL,  
  `ip` varchar(23) NOT NULL,
  `user_id` int(11) NOT NULL,  
  `title` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `added` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
);


-- TRUNCATE TABLE  `#__sportsmanagement_version_history`;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2014-04-13', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_04_13', '1.0.30');
