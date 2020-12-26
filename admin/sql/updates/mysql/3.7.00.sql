ALTER TABLE `#__sportsmanagement_project` ADD `openligaid` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_round` ADD `openligaid` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_match` ADD `openligaid` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_team` ADD `openligaid` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_person` ADD `openligaid` int(11) DEFAULT NULL;
ALTER TABLE `#__sportsmanagement_playground` ADD `openligaid` int(11) DEFAULT NULL;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-04-18', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2020-08-27', '3.7.00');
