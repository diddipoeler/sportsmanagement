ALTER TABLE `#__sportsmanagement_club` ADD `country_geocode` VARCHAR(3) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_league` ADD `founded` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `#__sportsmanagement_league` ADD `founded_year` VARCHAR(4) NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_league` ADD `dissolved` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `#__sportsmanagement_league` ADD `dissolved_year` VARCHAR(4) NULL DEFAULT NULL;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-04-18', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2020-05-03', '3.5.00');
