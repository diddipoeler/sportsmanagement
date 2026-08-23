<?php
/** Legacy compatibility bridge for the Joomla 5/6 active season field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ActseasonField;

if (!class_exists(ActseasonField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ActseasonField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ActseasonField::class) && !class_exists('JFormFieldactseason', false)) {
    class_alias(ActseasonField::class, 'JFormFieldactseason');
}
