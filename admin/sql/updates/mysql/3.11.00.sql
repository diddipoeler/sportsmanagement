ALTER TABLE `#__sportsmanagement_project_team_division` DROP INDEX `combi`, ADD UNIQUE `combi` (`project_id`, `team_id`, `division_id`) ;



INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2022-01-09', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2022-01-09', '3.11.00');
