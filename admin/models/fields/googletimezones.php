<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GoogletimezonesField;

if (!class_exists(GoogletimezonesField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GoogletimezonesField.php';
}

if (!class_exists('JFormFieldGoogletimezones', false)) {
    class_alias(GoogletimezonesField::class, 'JFormFieldGoogletimezones');
}
