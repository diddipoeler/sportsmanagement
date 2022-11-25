-- ALTER TABLE  `#__sportsmanagement_countries_plz` ADD UNIQUE  `schluessel1` (  `country_code` ,  `postal_code` ,  `place_name` ) ;

-- ALTER TABLE  `#__sportsmanagement_associations` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_federations` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- ALTER TABLE  `#__sportsmanagement_person` ADD `cr_picture` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_playground` ADD `cr_picture` varchar(255) DEFAULT NULL ;

-- ALTER TABLE  `#__sportsmanagement_club` ADD `cr_logo_big` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_club` ADD `cr_logo_middle` varchar(255) DEFAULT NULL ;
-- ALTER TABLE  `#__sportsmanagement_club` ADD `cr_logo_small` varchar(255) DEFAULT NULL ;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2014-11-17', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_11_17', '1.0.46');
