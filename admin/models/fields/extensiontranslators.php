<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensiontranslatorsField;

if (!class_exists(ExtensiontranslatorsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensiontranslatorsField.php';
}

if (!class_exists('JFormFieldExtensionTranslators', false)) {
    class_alias(ExtensiontranslatorsField::class, 'JFormFieldExtensionTranslators');
}
