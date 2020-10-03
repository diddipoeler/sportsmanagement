ALTER TABLE `#__sportsmanagement_countries` ADD `countrymap_mapdata` TEXT NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_countries` ADD `countrymap_mapinfo` TEXT NULL DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_countries` ADD `country_picture` varchar(255) NOT NULL DEFAULT 'images/com_sportsmanagement/database/placeholders/placeholder_wappen_50.png';



INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-10-03', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2020-10-03', '3.8.10');
