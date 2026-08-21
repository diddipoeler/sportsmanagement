<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectpositionlistField;

if (!class_exists(ProjectpositionlistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectpositionlistField.php';
}

if (!class_exists('JFormFieldprojectpositionlist', false)) {
    class_alias(ProjectpositionlistField::class, 'JFormFieldprojectpositionlist');
}
