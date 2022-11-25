ALTER TABLE `#__sportsmanagement_project_team` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_project_team` CHANGE `reason` `reason` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_project_referee` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-10-04', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-11-06', '2.2.00');
