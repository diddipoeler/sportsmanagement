<?php
/** Compatibility bridge for the Joomla 5/6 team list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamlistField;

if (!class_exists(TeamlistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamlistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(TeamlistField::class) && !class_exists('JFormFieldTeamlist', false)) {
    class_alias(TeamlistField::class, 'JFormFieldTeamlist');
}
