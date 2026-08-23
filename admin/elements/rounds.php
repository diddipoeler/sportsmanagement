<?php
/** Legacy compatibility bridge for the Joomla 5/6 project rounds field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\RoundsField;

if (!class_exists(RoundsField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/RoundsField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(RoundsField::class) && !class_exists('JFormFieldRounds', false)) {
    class_alias(RoundsField::class, 'JFormFieldRounds');
}
