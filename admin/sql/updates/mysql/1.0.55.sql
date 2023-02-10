-- ALTER TABLE `#__sportsmanagement_playground` ADD `state` VARCHAR(50) NULL DEFAULT NULL ;

-- ALTER TABLE `#__sportsmanagement_club` CHANGE `latitude` `latitude` DECIMAL(12,8) NULL DEFAULT NULL;
-- ALTER TABLE `#__sportsmanagement_club` CHANGE `longitude` `longitude` DECIMAL(12,8) NULL DEFAULT NULL;

-- ALTER TABLE `#__sportsmanagement_playground` CHANGE `latitude` `latitude` DECIMAL(12,8) NULL DEFAULT NULL;
-- ALTER TABLE `#__sportsmanagement_playground` CHANGE `longitude` `longitude` DECIMAL(12,8) NULL DEFAULT NULL;

-- ALTER TABLE `#__sportsmanagement_person` CHANGE `latitude` `latitude` DECIMAL(12,8) NULL DEFAULT NULL;
-- ALTER TABLE `#__sportsmanagement_person` CHANGE `longitude` `longitude` DECIMAL(12,8) NULL DEFAULT NULL;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2016-03-01', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2016_03_01', '1.0.55');
