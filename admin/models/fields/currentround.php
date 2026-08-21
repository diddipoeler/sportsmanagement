<?php
/** Compatibility bridge for the Joomla 5/6 current round field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\CurrentroundField;

if (!class_exists(CurrentroundField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CurrentroundField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(CurrentroundField::class) && !class_exists('JFormFieldCurrentround', false)) {
    class_alias(CurrentroundField::class, 'JFormFieldCurrentround');
}
