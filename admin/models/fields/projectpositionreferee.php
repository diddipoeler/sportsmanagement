<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectpositionrefereeField;

if (!class_exists(ProjectpositionrefereeField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectpositionrefereeField.php';
}

if (!class_exists('JFormFieldprojectpositionreferee', false)) {
    class_alias(ProjectpositionrefereeField::class, 'JFormFieldprojectpositionreferee');
}
