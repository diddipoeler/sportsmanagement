ALTER TABLE `#__sportsmanagement_club` ADD `instagram` VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_club` ADD `linkedin` VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_person` ADD `instagram` VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_person` ADD `linkedin` VARCHAR(250) NOT NULL DEFAULT '';

ALTER TABLE `#__sportsmanagement_project` ADD `twitter` VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_project` ADD `facebook` VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_project` ADD `instagram` VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_project` ADD `linkedin` VARCHAR(250) NOT NULL DEFAULT '';

ALTER TABLE `#__sportsmanagement_playground` ADD `playground_size` VARCHAR(200) NOT NULL DEFAULT '';

ALTER TABLE  `#__sportsmanagement_club` CHANGE  `name`  `name` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
ALTER TABLE  `#__sportsmanagement_club` CHANGE  `alias`  `alias` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';

ALTER TABLE  `#__sportsmanagement_project` CHANGE  `name`  `name` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
ALTER TABLE  `#__sportsmanagement_project` CHANGE  `alias`  `alias` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-09-12', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2020-09-12', '3.8.00');
