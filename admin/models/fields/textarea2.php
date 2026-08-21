<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\Textarea2Field;

if (!class_exists(Textarea2Field::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/Textarea2Field.php';
}

if (!class_exists('JFormFieldTextarea2', false)) {
    class_alias(Textarea2Field::class, 'JFormFieldTextarea2');
}
