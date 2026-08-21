<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SeasonteampersonField;

if (!class_exists(SeasonteampersonField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SeasonteampersonField.php';
}

if (!class_exists('JFormFieldseasonteamperson', false)) {
    class_alias(SeasonteampersonField::class, 'JFormFieldseasonteamperson');
}
