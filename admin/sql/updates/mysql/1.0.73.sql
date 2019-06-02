ALTER TABLE  `#__sportsmanagement_confidential` ADD INDEX `project_id` (  `project` );
ALTER TABLE  `#__sportsmanagement_user_extra_fields_values` ADD INDEX `jl_id` (  `jl_id` );
  
INSERT INTO `#__sportsmanagement_version_history` (`id`, `date`, `text`, `version`) VALUES
(NULL, '2019-06-02', 'COM_SPORTSMANAGEMENT_DB_UPDATE_2019-06-02', '1.0.73');
