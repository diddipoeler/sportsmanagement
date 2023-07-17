-- ALTER TABLE `#__sportsmanagement_division` CHANGE `rankingparams`  `rankingparams` TEXT NULL DEFAULT NULL ;

-- ALTER TABLE `#__sportsmanagement_federations` ADD `country` varchar(3) DEFAULT NULL; 
-- ALTER TABLE `#__sportsmanagement_federations` ADD `address` VARCHAR(100) NOT NULL DEFAULT '';
-- ALTER TABLE `#__sportsmanagement_federations` ADD `zipcode` VARCHAR(10) NOT NULL DEFAULT '';
-- ALTER TABLE `#__sportsmanagement_federations` ADD `location` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__sportsmanagement_federations` ADD `state` VARCHAR(50) NOT NULL DEFAULT '';

-- ALTER TABLE `#__sportsmanagement_associations` ADD `address` VARCHAR(100) NOT NULL DEFAULT '';
-- ALTER TABLE `#__sportsmanagement_associations` ADD `zipcode` VARCHAR(10) NOT NULL DEFAULT '';
-- ALTER TABLE `#__sportsmanagement_associations` ADD `location` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__sportsmanagement_associations` ADD `state` VARCHAR(50) NOT NULL DEFAULT '';

-- ALTER TABLE `#__sportsmanagement_team` ADD `team_number` INT(11) NOT NULL DEFAULT '0';
    
-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2016-12-31', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2016_12_31', '1.0.58');
