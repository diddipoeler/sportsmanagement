ALTER TABLE `#__sportsmanagement_project` ADD `fav_team_send_mail` TINYINT(4) NOT NULL DEFAULT '1';

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2021-10-17', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-10-17', '3.8.86');
