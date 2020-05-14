ALTER TABLE `#__sportsmanagement_project_team` ADD `finaltablerank` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_project` ADD `fast_projektteam` tinyint(1) NOT NULL DEFAULT '0';


INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-04-18', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2020-05-14', '3.6.00');
