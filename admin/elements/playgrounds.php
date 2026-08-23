<?php
/** Legacy compatibility bridge for the Joomla 5/6 playgrounds field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PlaygroundsField;

if (!class_exists(PlaygroundsField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PlaygroundsField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(PlaygroundsField::class) && !class_exists('JFormFieldPlaygrounds', false)) {
    class_alias(PlaygroundsField::class, 'JFormFieldPlaygrounds');
}
