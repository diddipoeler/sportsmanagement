ALTER TABLE `#__sportsmanagement_gcalendar` ADD `params` text NOT NULL;
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `language` char(7) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `created_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `created_by_alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `version` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `modified_by` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__sportsmanagement_gcalendar` ADD `alias` varchar(255) NOT NULL DEFAULT '';
  
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-02-22', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-03-17', '1.0.71');
