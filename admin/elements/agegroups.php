<?php
/** Legacy compatibility bridge for the Joomla 5/6 team age groups field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AgegroupsField;

if (!class_exists(AgegroupsField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AgegroupsField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(AgegroupsField::class) && !class_exists('JFormFieldagegroups', false)) {
    class_alias(AgegroupsField::class, 'JFormFieldagegroups');
}
