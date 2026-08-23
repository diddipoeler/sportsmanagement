<?php
/** Legacy compatibility bridge for the Joomla 5/6 project selector field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectField;

if (!class_exists(ProjectField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ProjectField::class) && !class_exists('JFormFieldProject', false)) {
    class_alias(ProjectField::class, 'JFormFieldProject');
}
