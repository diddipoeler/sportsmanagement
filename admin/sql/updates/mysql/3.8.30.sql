ALTER TABLE `#__sportsmanagement_season_team_person_id` ADD `contract_from` DATE NOT NULL DEFAULT '0000-00-00' ;
ALTER TABLE `#__sportsmanagement_season_team_person_id` ADD `contract_to` DATE NOT NULL DEFAULT '0000-00-00';

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2020-10-06', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-01-01', '3.8.30');
