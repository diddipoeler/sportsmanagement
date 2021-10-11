ALTER TABLE `#__sportsmanagement_club` CHANGE `founded_timestamp` `founded_timestamp` BIGINT(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_club` CHANGE `dissolved_timestamp` `dissolved_timestamp` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `#__sportsmanagement_match` CHANGE `match_timestamp` `match_timestamp` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `#__sportsmanagement_person` CHANGE `birthday_timestamp` `birthday_timestamp` BIGINT(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_person` CHANGE `deathday_timestamp` `deathday_timestamp` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `#__sportsmanagement_project` CHANGE `modified_timestamp` `modified_timestamp` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `#__sportsmanagement_round` CHANGE `rdatefirst_timestamp` `rdatefirst_timestamp` BIGINT(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__sportsmanagement_round` CHANGE `rdatelast_timestamp` `rdatelast_timestamp` BIGINT(20) NOT NULL DEFAULT '0';

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2021-10-11', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2021-10-11', '3.8.85');
