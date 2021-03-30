ALTER TABLE `#__sportsmanagement_project` ADD `use_smallcaliber` TINYINT(1)  NULL DEFAULT NULL  ;



INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2021-03-12', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-03-12', '3.8.80');
