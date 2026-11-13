-- SportsManagement 4.24.03: add round notes and season images.
-- Existing installations receive the same columns as fresh installations.
-- The MEDIUMBLOB is reserved for binary image data and is not edited
-- through the season media-path field.

ALTER TABLE `#__sportsmanagement_round`
  ADD COLUMN `notes` TEXT NULL DEFAULT NULL;

ALTER TABLE `#__sportsmanagement_season`
  ADD COLUMN `picture_blob` MEDIUMBLOB NULL DEFAULT NULL,
  ADD COLUMN `picture` VARCHAR(250) NULL DEFAULT NULL;
