<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SeasonlistField;

if (!class_exists(SeasonlistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SeasonlistField.php';
}

if (!class_exists('JFormFieldseasonlist', false)) {
    class_alias(SeasonlistField::class, 'JFormFieldseasonlist');
}
