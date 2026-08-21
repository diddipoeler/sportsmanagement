<?php
/** Compatibility bridge for the Joomla 5/6 project list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectlistField;

if (!class_exists(ProjectlistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectlistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ProjectlistField::class) && !class_exists('JFormFieldprojectlist', false)) {
    class_alias(ProjectlistField::class, 'JFormFieldprojectlist');
}
