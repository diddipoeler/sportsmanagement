ALTER TABLE `#__sportsmanagement_club` ADD `use_jl` tinyint(1) NOT NULL DEFAULT '0'; 
ALTER TABLE `#__sportsmanagement_club` ADD `use_jsm` tinyint(1) NOT NULL DEFAULT '0'; 
ALTER TABLE `#__sportsmanagement_eventtype` ADD `directionspoint` CHAR(4) NOT NULL DEFAULT 'DESC' ;
ALTER TABLE `#__sportsmanagement_eventtype` ADD `directionscounter` CHAR(4) NOT NULL DEFAULT 'DESC' ;
ALTER TABLE `#__sportsmanagement_project` ADD `use_nation` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `#__sportsmanagement_eventtype` ADD `directionspointpos` tinyint(1) NOT NULL DEFAULT '1' ;
ALTER TABLE `#__sportsmanagement_eventtype` ADD `directionscounterpos` tinyint(1) NOT NULL DEFAULT '2' ;

INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-02-12', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-02-12', '1.0.69');
