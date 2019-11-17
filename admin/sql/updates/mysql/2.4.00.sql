ALTER TABLE `#__sportsmanagement_match` CHANGE `summary` `summary` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_match` CHANGE `preview` `preview` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-10-04', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-11-17', '2.4.00');
