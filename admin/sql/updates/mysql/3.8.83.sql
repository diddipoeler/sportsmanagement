ALTER TABLE `#__sportsmanagement_project_team` ADD `picturenotes` TEXT NULL DEFAULT NULL ;

ALTER TABLE `#__sportsmanagement_associations` ADD `founded` DATE NOT NULL DEFAULT '0000-00-00' ;
ALTER TABLE `#__sportsmanagement_associations` ADD `dissolved` DATE NOT NULL DEFAULT '0000-00-00' ;
ALTER TABLE `#__sportsmanagement_associations` ADD `dissolved_year` VARCHAR(4) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_associations` ADD `founded_year` VARCHAR(4) NULL DEFAULT NULL;

ALTER TABLE `#__sportsmanagement_federations` ADD `founded` DATE NOT NULL DEFAULT '0000-00-00' ;
ALTER TABLE `#__sportsmanagement_federations` ADD `dissolved` DATE NOT NULL DEFAULT '0000-00-00' ;
ALTER TABLE `#__sportsmanagement_federations` ADD `dissolved_year` VARCHAR(4) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_federations` ADD `founded_year` VARCHAR(4) NULL DEFAULT NULL;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2021-03-30', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-04-25', '3.8.83');
