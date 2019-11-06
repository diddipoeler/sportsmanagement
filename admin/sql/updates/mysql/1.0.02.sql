-- -----------------------------------------------------
-- Tabellenstruktur für Tabelle `#__sportsmanagement_extra_values`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__sportsmanagement_user_extra_values` (
  `f_id` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `fvalue` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`f_id`,`uid`)
)
ENGINE = MyISAM
DEFAULT CHARSET = utf8;