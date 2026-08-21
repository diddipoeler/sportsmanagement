<?php
/** Compatibility bridge for the Joomla 5/6 person list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\PersonlistField;

if (!class_exists(PersonlistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PersonlistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(PersonlistField::class) && !class_exists('JFormFieldpersonlist', false)) {
    class_alias(PersonlistField::class, 'JFormFieldpersonlist');
}
