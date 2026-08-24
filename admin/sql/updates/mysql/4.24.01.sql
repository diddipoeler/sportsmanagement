-- Joomla 5/6 / MySQL strict-mode compatibility for project dates.
-- Legacy installations used NOT NULL zero dates, while modern MySQL/MariaDB
-- reject empty strings and often reject 0000-00-00 as DATE values.

SET @JSM_OLD_SQL_MODE = @@SESSION.sql_mode;
SET SESSION sql_mode = '';

ALTER TABLE `#__sportsmanagement_project`
  MODIFY `start_date` DATE NULL DEFAULT NULL,
  MODIFY `end_date` DATE NULL DEFAULT NULL;

UPDATE `#__sportsmanagement_project`
SET `start_date` = NULL
WHERE `start_date` = '0000-00-00';

UPDATE `#__sportsmanagement_project`
SET `end_date` = NULL
WHERE `end_date` = '0000-00-00';

SET SESSION sql_mode = @JSM_OLD_SQL_MODE;
