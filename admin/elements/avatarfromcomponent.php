<?php
/** Legacy compatibility bridge for the Joomla 5/6 avatar provider field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AvatarfromcomponentField;

if (!class_exists(AvatarfromcomponentField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AvatarfromcomponentField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(AvatarfromcomponentField::class) && !class_exists('JFormFieldAvatarFromComponent', false)) {
    class_alias(AvatarfromcomponentField::class, 'JFormFieldAvatarFromComponent');
}
