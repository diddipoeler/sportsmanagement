-- ALTER TABLE  `#__sportsmanagement_league` ADD  `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_project` ADD  `agegroup_id` INT( 11 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__sportsmanagement_user_extra_fields` ADD  `field_type` VARCHAR(15) NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS `#__sportsmanagement_confidential` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR( 250 ) DEFAULT NULL ,
 `link` VARCHAR( 250 ) DEFAULT NULL ,
 `nummer` VARCHAR( 10 ) DEFAULT NULL ,
 `project` INT( 11 ) DEFAULT NULL ,
 `country` VARCHAR( 3 ) DEFAULT NULL ,
 `teamart` VARCHAR( 30 ) DEFAULT NULL ,
 `team_id` INT( 11 ) NOT NULL DEFAULT  '0',
 `club_id` INT( 11 ) NOT NULL DEFAULT  '0',
 `person_id` INT( 11 ) NOT NULL DEFAULT  '0',
PRIMARY KEY (  `id` ),
  UNIQUE KEY `schluessel` (`name`(150),`link`(150)) 
) ENGINE = MYISAM DEFAULT CHARSET = utf8;

-- INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES (NULL, '2014-04-30', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2014_04_30', '1.0.36');
