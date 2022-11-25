ALTER TABLE `#__sportsmanagement_project` ADD `use_approved` tinyint(1) NOT NULL DEFAULT '0';

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-02-22', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-02-22', '1.0.70');
