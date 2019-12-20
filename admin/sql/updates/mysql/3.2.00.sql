ALTER TABLE `#__sportsmanagement_treeto_node` ADD `roundcode` INT(11) NOT NULL DEFAULT '0';

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-12-20', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-12-20', '3.2.00');
