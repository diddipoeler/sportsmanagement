<?php
/** Legacy compatibility bridge for the Joomla 5/6 multi-list field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\MultilistField;

if (!class_exists(MultilistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/MultilistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(MultilistField::class) && !class_exists('JFormFieldMultiList', false)) {
    class_alias(MultilistField::class, 'JFormFieldMultiList');
}
