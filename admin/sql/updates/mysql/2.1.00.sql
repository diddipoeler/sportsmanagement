ALTER TABLE `#__sportsmanagement_person` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `email` `email` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `website` `website` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `#__sportsmanagement_person` CHANGE `injury_date` `injury_date` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `injury_end` `injury_end` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `injury_detail` `injury_detail` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `suspension_date` `suspension_date` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `suspension_end` `suspension_end` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `suspension_detail` `suspension_detail` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `away_date` `away_date` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `away_end` `away_end` INT(11) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` CHANGE `away_detail` `away_detail` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_team` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_project` CHANGE `projectinfo` `projectinfo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-10-04', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-11-06', '2.1.00');
