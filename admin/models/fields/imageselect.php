<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ImageSelectField;

if (!class_exists(ImageSelectField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ImageSelectField.php';
}

if (!class_exists('JFormFieldImageSelect', false)) {
    class_alias(ImageSelectField::class, 'JFormFieldImageSelect');
}
