-- ALTER TABLE  `#__sportsmanagement_associations` ADD `modified` DATETIME NULL ;
-- ALTER TABLE  `#__sportsmanagement_associations` ADD `modified_by` INT NULL ;

-- ALTER TABLE  `#__sportsmanagement_federations` ADD `modified` DATETIME NULL ;
-- ALTER TABLE  `#__sportsmanagement_federations` ADD `modified_by` INT NULL ;

-- ALTER TABLE  `#__sportsmanagement_countries` ADD `modified` DATETIME NULL ;
-- ALTER TABLE  `#__sportsmanagement_countries` ADD `modified_by` INT NULL ;

-- ALTER TABLE  `#__sportsmanagement_rosterposition` ADD `modified` DATETIME NULL ;
-- ALTER TABLE  `#__sportsmanagement_rosterposition` ADD `modified_by` INT NULL ;

-- ALTER TABLE  `#__sportsmanagement_person` ADD `contact_id` int(11) DEFAULT NULL;

-- ALTER TABLE  `#__sportsmanagement_position_ringen` ADD `modified` DATETIME NULL ;
-- ALTER TABLE  `#__sportsmanagement_position_ringen` ADD `modified_by` INT NULL ;

-- ALTER TABLE  `#__sportsmanagement_rquote` ADD `modified` DATETIME NULL ;
-- ALTER TABLE  `#__sportsmanagement_rquote` ADD `modified_by` INT NULL ;

-- ALTER TABLE  `#__sportsmanagement_countries_gazetteer` ADD `modified_by` INT NULL ;
-- ALTER TABLE  `#__sportsmanagement_countries_plz` ADD `modified_by` INT NULL ;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2014-09-25', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_09_25', '1.0.44');
