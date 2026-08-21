<?php
/**
 * Legacy compatibility bridge for the SportsManagement league-level field.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\LeaguelevelField;

if (!class_exists(LeaguelevelField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LeaguelevelField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(LeaguelevelField::class) && !class_exists('JFormFieldLeagueLevel', false)) {
    class_alias(LeaguelevelField::class, 'JFormFieldLeagueLevel');
}
