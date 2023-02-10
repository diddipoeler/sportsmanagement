-- ALTER TABLE  `#__sportsmanagement_project` ADD `modified_timestamp` INT( 11 ) NOT NULL DEFAULT  0 ;

-- ALTER TABLE  `#__sportsmanagement_project` ADD INDEX  `modified_timestamp` (  `modified_timestamp` );
-- ALTER TABLE  `#__sportsmanagement_match` ADD INDEX  `match_timestamp` (  `match_timestamp` );

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2015-11-31', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2016_02_01', '1.0.53');
