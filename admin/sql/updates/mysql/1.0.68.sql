CREATE TABLE IF NOT EXISTS `#__sportsmanagement_log_entries` (
  `priority` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  KEY `idx_category_date_priority` (`category`,`date`,`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2018-11-01', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2018_12_26', '1.0.68');
