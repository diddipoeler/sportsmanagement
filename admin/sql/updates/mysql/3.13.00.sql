ALTER TABLE  `#__sportsmanagement_match` CHANGE `decision_info` `decision_info` VARCHAR(128) NOT NULL DEFAULT NULL;
ALTER TABLE  `#__sportsmanagement_match` CHANGE `cancel_reason` `cancel_reason` VARCHAR(32) NOT NULL DEFAULT NULL;
ALTER TABLE  `#__sportsmanagement_match` CHANGE `match_result_detail` `match_result_detail` VARCHAR(64) NOT NULL DEFAULT NULL;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2022-03-30', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2022-03-30', '3.13.00');
