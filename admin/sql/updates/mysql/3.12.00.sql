ALTER TABLE `#__sportsmanagement_associations` ADD `notes` TEXT NULL DEFAULT NULL ;
ALTER TABLE `#__sportsmanagement_club` ADD `notes` TEXT NULL DEFAULT NULL ;
ALTER TABLE `#__sportsmanagement_federations` ADD `notes` TEXT NULL DEFAULT NULL ;
ALTER TABLE `#__sportsmanagement_league` ADD `notes` TEXT NULL DEFAULT NULL ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2022-01-10', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2022-01-10', '3.12.00');
