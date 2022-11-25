ALTER TABLE  `#__sportsmanagement_gcalendar` CHANGE `calendar_id` `calendar_id` TEXT NULL DEFAULT NULL;
ALTER TABLE  `#__sportsmanagement_gcalendar` CHANGE `name` `name` TEXT NULL DEFAULT NULL;
ALTER TABLE  `#__sportsmanagement_gcalendar` CHANGE `magic_cookie` `magic_cookie` TEXT NULL DEFAULT NULL;

ALTER TABLE `#__sportsmanagement_prediction_result` ADD `yellow_cards` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_prediction_result` ADD `red_cards` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_prediction_result` ADD `penalties` int(11) DEFAULT NULL;
  
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-06-27', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-08-05', '1.0.75');
