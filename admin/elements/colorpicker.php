<?php
/** Legacy compatibility bridge for the Joomla 5/6 color picker field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ColorpickerField;

if (!class_exists(ColorpickerField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ColorpickerField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ColorpickerField::class) && !class_exists('JFormFieldColorpicker', false)) {
    class_alias(ColorpickerField::class, 'JFormFieldColorpicker');
}
