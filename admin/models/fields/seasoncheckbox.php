<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SeasoncheckboxField;

if (!class_exists(SeasoncheckboxField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SeasoncheckboxField.php';
}

if (!class_exists('JFormFieldseasoncheckbox', false)) {
    class_alias(SeasoncheckboxField::class, 'JFormFieldseasoncheckbox');
}
