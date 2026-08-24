-- Joomla 5/6 / MySQL strict-mode compatibility for round dates.
-- Round calendar fields are stored as SQL DATE values and optional dates must
-- not rely on the legacy 0000-00-00 sentinel.

SET @JSM_OLD_SQL_MODE = @@SESSION.sql_mode;
SET SESSION sql_mode = '';

ALTER TABLE `#__sportsmanagement_round`
  MODIFY `round_date_first` DATE NULL DEFAULT NULL,
  MODIFY `round_date_last` DATE NULL DEFAULT NULL;

UPDATE `#__sportsmanagement_round`
SET `round_date_first` = NULL
WHERE `round_date_first` = '0000-00-00';

UPDATE `#__sportsmanagement_round`
SET `round_date_last` = NULL
WHERE `round_date_last` = '0000-00-00';

SET SESSION sql_mode = @JSM_OLD_SQL_MODE;
