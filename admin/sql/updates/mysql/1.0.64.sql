CREATE TABLE IF NOT EXISTS `#__sportsmanagement_log_entries` (
  `priority` int(11) DEFAULT NULL,
  `message` varchar(512) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  KEY `idx_category_date_priority` (`category`,`date`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE  `#__sportsmanagement_person_project_position` ADD `published` TINYINT(1) NOT NULL DEFAULT '1' ;
ALTER TABLE  `#__sportsmanagement_person` ADD `gender` TINYINT(1) NOT NULL DEFAULT '0' ;
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2018-07-07', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2018_07_07', '1.0.64');
