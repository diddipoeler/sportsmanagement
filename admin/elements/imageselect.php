<?php
/** Legacy compatibility bridge for the Joomla 5/6 image selector field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ImageselectField;

if (!class_exists(ImageselectField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ImageselectField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ImageselectField::class) && !class_exists('JFormFieldImageSelect', false)) {
    class_alias(ImageselectField::class, 'JFormFieldImageSelect');
}
