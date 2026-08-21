<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\LanguagecharacterField;

if (!class_exists(LanguagecharacterField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LanguagecharacterField.php';
}

if (!class_exists('JFormFieldlanguagecharacter', false)) {
    class_alias(LanguagecharacterField::class, 'JFormFieldlanguagecharacter');
}
