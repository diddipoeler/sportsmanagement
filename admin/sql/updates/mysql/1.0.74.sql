ALTER TABLE `#__sportsmanagement_sports_type` ADD `eventtime` TINYINT(1) NOT NULL DEFAULT '1';
  
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-06-27', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-06-27', '1.0.74');
