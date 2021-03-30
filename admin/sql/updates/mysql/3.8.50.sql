ALTER TABLE `#__sportsmanagement_rquote` ADD `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2021-03-06', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-03-06', '3.8.50');
