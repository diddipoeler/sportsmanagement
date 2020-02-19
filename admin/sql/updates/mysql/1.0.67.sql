ALTER TABLE  `#__sportsmanagement_club` ADD INDEX  `unique_id` (  `unique_id` );
ALTER TABLE  `#__sportsmanagement_club` ADD INDEX  `new_club_id` (  `new_club_id` );

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2018-11-01', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2018_11_01', '1.0.67');
